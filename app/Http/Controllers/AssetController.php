<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use App\Models\AssetType;
use App\Models\IssueReport;
use App\Models\Photo;
use App\Models\User;
use App\Services\PhotoService;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function __construct(
        private readonly QrCodeService $qrCodeService,
        private readonly PhotoService $photoService,
    ) {}

    /**
     * List and filter assets.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $categoryId = $request->query('category');
        $locationId = $request->query('location');
        $statusId = $request->query('status');
        $department = $request->query('department');
        $purchaseFrom = $request->query('purchase_from');
        $purchaseTo = $request->query('purchase_to');

        $assets = Asset::with(['category', 'type', 'location', 'assetStatus', 'custodian', 'photo'])
            ->search($search)
            ->when($categoryId, fn ($q) => $q->where('asset_category_id', $categoryId))
            ->when($locationId, fn ($q) => $q->where('asset_location_id', $locationId))
            ->when($statusId, fn ($q) => $q->where('asset_status_id', $statusId))
            ->when($department, fn ($q) => $q->where('department', $department))
            ->when($purchaseFrom, fn ($q) => $q->whereDate('purchase_date', '>=', $purchaseFrom))
            ->when($purchaseTo, fn ($q) => $q->whereDate('purchase_date', '<=', $purchaseTo))
            ->orderBy('asset_code')
            ->paginate(10)
            ->withQueryString();

        return view('assets.index', [
            'assets' => $assets,
            'search' => $search,
            'categories' => AssetCategory::orderBy('name')->get(),
            'locations' => AssetLocation::orderBy('name')->get(),
            'statuses' => AssetStatus::orderBy('name')->get(),
            'departments' => $this->departments(),
            'selectedCategory' => $categoryId,
            'selectedLocation' => $locationId,
            'selectedStatus' => $statusId,
            'selectedDepartment' => $department,
            'purchaseFrom' => $purchaseFrom,
            'purchaseTo' => $purchaseTo,
            'totalCount' => Asset::count(),
            'availableCount' => Asset::whereNull('custodian_id')->count(),
            'assignedCount' => Asset::whereNotNull('custodian_id')->count(),
            'maintenanceCount' => Asset::whereHas('assetStatus', fn ($q) => $q->where('name', Asset::STATUS_UNDER_MAINTENANCE))->count(),
        ]);
    }

    /**
     * Show the new asset form.
     */
    public function create(): View
    {
        return view('assets.create', $this->formData(new Asset()));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, new Asset());
        $data['asset_status_id'] = AssetStatus::where('name', Asset::STATUS_ACTIVE)->value('id');

        $asset = DB::transaction(function () use ($data, $request) {
            $data['asset_code'] = $this->nextCode(AssetType::findOrFail($data['asset_type_id']), $data['purchase_date']);
            $asset = Asset::create($data);
            $this->photoService->attach($asset, $request->file('photo'));

            return $asset;
        });

        return redirect()->route('assets.show', $asset)->with('status', "Asset \"{$asset->name}\" registered successfully.");
    }

    public function edit(Asset $asset): View
    {
        return view('assets.edit', $this->formData($asset->load('photo')));
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $data = $this->validated($request, $asset);

        DB::transaction(function () use ($asset, $data, $request) {
            // A generated code follows its category, type and purchase year.
            // Codes from before that scheme (e.g. AST-001) are left as they are.
            $type = AssetType::findOrFail($data['asset_type_id']);
            $prefix = $this->codePrefix($type, $data['purchase_date']);
            if ($this->isGeneratedCode($asset->asset_code) && ! str_starts_with($asset->asset_code, $prefix)) {
                $data['asset_code'] = $this->nextCode($type, $data['purchase_date']);
            }

            $asset->update($data);

            if ($request->hasFile('photo')) {
                $this->photoService->replace($asset, $request->file('photo'));
            }
        });

        return redirect()->route('assets.show', $asset)->with('status', "Asset \"{$asset->name}\" has been updated.");
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $name = $asset->name;

        DB::transaction(function () use ($asset) {
            // The database removes the asset's issue reports with it; their
            // damage photos are not linked by a foreign key, so clear them here.
            Photo::where('photoable_type', (new IssueReport())->getMorphClass())
                ->whereIn('photoable_id', IssueReport::where('asset_id', $asset->id)->select('id'))
                ->delete();

            $asset->delete();
        });

        return redirect()->route('assets.index')->with('status', "Asset \"{$name}\" has been deleted.");
    }

    public function show(Asset $asset): View
    {
        $asset->load(['category', 'type', 'location', 'assetStatus', 'custodian', 'assignments.custodian', 'maintenances', 'photo']);

        return view('assets.show', ['asset' => $asset]);
    }

    /**
     * Serve the asset's QR code image, generated on the fly.
     */
    public function qr(Asset $asset)
    {
        $svg = $this->qrCodeService->svgFor(route('assets.show', $asset));

        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', "inline; filename=\"{$asset->asset_code}.svg\"");
    }

    /**
     * The code a new asset of this type and purchase date would receive.
     * Lets the form preview the code before saving.
     */
    public function nextCodePreview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'exists:asset_types,id'],
            'purchase_date' => ['required', 'date'],
        ]);

        return response()->json([
            'code' => $this->nextCode(AssetType::findOrFail($data['type']), $data['purchase_date']),
        ]);
    }

    private function formData(Asset $asset): array
    {
        return [
            'asset' => $asset,
            'categories' => AssetCategory::with('types')->where('status', 'active')->orderBy('name')->get(),
            'locations' => AssetLocation::where('status', 'active')->orderBy('name')->get(),
            'departments' => $this->departments(),
            'custodians' => User::where('status', 'active')->orderBy('name')->get(),
            'statuses' => AssetStatus::where('status', 'active')->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, Asset $asset): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['required', 'string', 'max:255', Rule::unique('assets', 'serial_number')->ignore($asset->id)],
            'description' => ['nullable', 'string'],
            'asset_category_id' => ['required', 'exists:asset_categories,id'],
            'asset_type_id' => ['required', Rule::exists('asset_types', 'id')->where('asset_category_id', $request->input('asset_category_id'))],
            'purchase_date' => ['required', 'date'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'po_reference' => ['nullable', 'string', 'max:255'],
            'warranty_expiry_date' => ['nullable', 'date', 'after_or_equal:purchase_date'],
            'department' => ['required', 'string', 'max:255'],
            'location_detail' => ['required', 'string', 'max:255'],
            'asset_location_id' => ['nullable', 'exists:asset_locations,id'],
            'custodian_id' => ['nullable', 'exists:users,id'],
            'assigned_date' => ['nullable', 'date', 'after_or_equal:purchase_date'],
            'asset_status_id' => [$asset->exists ? 'required' : 'nullable', 'exists:asset_statuses,id'],
            // A picture is compulsory, but an asset that already has one may keep it.
            'photo' => [$asset->exists && $asset->photo ? 'nullable' : 'required', ...PhotoService::RULES],
        ], [
            'serial_number.unique' => 'This serial number is already registered to another asset.',
            'asset_type_id.exists' => 'Choose an asset type that belongs to the selected category.',
            'warranty_expiry_date.after_or_equal' => 'Warranty expiry date cannot be earlier than the purchase date.',
            'assigned_date.after_or_equal' => 'Assigned date cannot be earlier than the purchase date.',
            'photo.required' => 'Please add a photo of the asset.',
        ]);

        unset($data['photo']);

        if (empty($data['custodian_id'])) {
            $data['assigned_date'] = null;
        }

        return $data;
    }

    /**
     * "C-LAP-2026-" : category code, type code and purchase year.
     */
    private function codePrefix(AssetType $type, string $purchaseDate): string
    {
        $categoryCode = $type->category->short_code ?: 'A';

        return strtoupper($categoryCode.'-'.$type->code).'-'.substr($purchaseDate, 0, 4).'-';
    }

    /**
     * Next free code for that prefix, e.g. C-LAP-2026-003.
     */
    private function nextCode(AssetType $type, string $purchaseDate): string
    {
        $prefix = $this->codePrefix($type, $purchaseDate);
        $taken = Asset::where('asset_code', 'like', $prefix.'%')->pluck('asset_code')->all();

        $number = count($taken) + 1;
        do {
            $code = $prefix.str_pad((string) $number++, 3, '0', STR_PAD_LEFT);
        } while (in_array($code, $taken, true));

        return $code;
    }

    private function isGeneratedCode(string $code): bool
    {
        return preg_match('/^[A-Z0-9]+-[A-Z0-9]+-\d{4}-\d{3,}$/', $code) === 1;
    }

    private function departments(): array
    {
        return config('assetone.departments');
    }
}
