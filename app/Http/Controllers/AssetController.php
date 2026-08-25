<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function __construct(private readonly QrCodeService $qrCodeService) {}

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

        $assets = Asset::with(['category', 'location', 'assetStatus', 'custodian'])
            ->search($search)
            ->when($categoryId, fn ($q) => $q->where('asset_category_id', $categoryId))
            ->when($locationId, fn ($q) => $q->where('asset_location_id', $locationId))
            ->when($statusId, fn ($q) => $q->where('asset_status_id', $statusId))
            ->when($department, fn ($q) => $q->where('department', $department))
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
        ]);
    }

    /**
     * Show the new asset form.
     */
    public function create(): View
    {
        return view('assets.create', [
            'asset' => new Asset(),
            'categories' => AssetCategory::where('status', 'active')->orderBy('name')->get(),
            'departments' => $this->departments(),
            'custodians' => User::where('status', 'active')->orderBy('name')->get(),
            'statuses' => AssetStatus::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['asset_code'] = $this->nextCode();

        $asset = Asset::create($data);

        $qrPath = $this->qrCodeService->generateForAsset(
            $asset->asset_code,
            route('assets.show', $asset)
        );

        if ($qrPath) {
            $asset->update(['qr_code_path' => $qrPath]);
        }

        return redirect()->route('assets.show', $asset)->with('status', "Asset \"{$asset->name}\" registered successfully.");
    }

    public function edit(Asset $asset): View
    {
        return view('assets.edit', [
            'asset' => $asset,
            'categories' => AssetCategory::where('status', 'active')->orderBy('name')->get(),
            'departments' => $this->departments(),
            'custodians' => User::where('status', 'active')->orderBy('name')->get(),
            'statuses' => AssetStatus::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $asset->update($this->validated($request));

        return redirect()->route('assets.show', $asset)->with('status', "Asset \"{$asset->name}\" has been updated.");
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $this->qrCodeService->delete($asset->qr_code_path);
        $asset->delete();

        return back()->with('status', 'Asset has been deleted.');
    }

    public function show(Asset $asset): View
    {
        $asset->load(['category', 'location', 'assetStatus', 'custodian', 'assignments.custodian', 'maintenances']);

        return view('assets.show', ['asset' => $asset]);
    }

    /**
     * Serve the asset's QR code image.
     */
    public function qr(Asset $asset)
    {
        abort_unless($asset->qr_code_path && Storage::disk('public')->exists($asset->qr_code_path), 404);

        return Storage::disk('public')->response($asset->qr_code_path);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'asset_category_id' => ['required', 'exists:asset_categories,id'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'warranty_expiry_date' => ['nullable', 'date'],
            'department' => ['required', 'string', 'max:255'],
            'location_detail' => ['required', 'string', 'max:255'],
            'asset_location_id' => ['nullable', 'exists:asset_locations,id'],
            'custodian_id' => ['nullable', 'exists:users,id'],
            'asset_status_id' => ['required', 'exists:asset_statuses,id'],
        ]);
    }

    private function nextCode(): string
    {
        $last = Asset::orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->asset_code, 4)) + 1 : 1;

        return 'AST-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function departments(): array
    {
        return [
            'Administration Department',
            'Finance Department',
            'Information Technology Department',
            'Human Resources Department',
        ];
    }
}
