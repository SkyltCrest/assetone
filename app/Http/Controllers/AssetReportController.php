<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetReportController extends Controller
{
    /**
     * Every column that can appear in the report, keyed by the value used in
     * the `columns` query parameter. The order here is the order they render.
     *
     * @var array<string, string>
     */
    private const COLUMNS = [
        'asset_code' => 'Asset ID',
        'name' => 'Asset Name',
        'serial_number' => 'Serial Number',
        'category' => 'Category',
        'type' => 'Asset Type',
        'location' => 'Location',
        'department' => 'Department',
        'custodian' => 'Custodian',
        'status' => 'Status',
        'purchase_date' => 'Purchase Date',
        'purchase_price' => 'Purchase Price',
        'supplier' => 'Supplier',
        'po_reference' => 'PO / Reference No.',
        'warranty_expiry_date' => 'Warranty Expiry',
        'photo' => 'Photo',
    ];

    /**
     * Columns the report can be grouped by, keyed by the `group` query value.
     *
     * @var array<string, string>
     */
    private const GROUPS = [
        'category' => 'Category',
        'type' => 'Asset Type',
        'location' => 'Location',
        'department' => 'Department',
        'custodian' => 'Custodian',
        'status' => 'Status',
    ];

    /** @var list<string> */
    private const DEFAULT_COLUMNS = ['asset_code', 'name', 'category', 'location', 'custodian', 'status'];

    /** @var list<string> */
    private const SORTABLE = ['asset_code', 'name', 'purchase_date', 'purchase_price'];

    public function index(Request $request): View
    {
        [$assets, $filters] = $this->query($request);

        return view('reports.index', $this->viewData($request, $assets, $filters));
    }

    public function print(Request $request): View
    {
        [$assets, $filters] = $this->query($request);

        return view('reports.print', $this->viewData($request, $assets, $filters) + [
            'generatedAt' => now(),
            'generatedBy' => $request->user(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$assets] = $this->query($request);
        $columns = array_values(array_diff($this->selectedColumns($request), ['photo']));   // pictures cannot go in a CSV

        $filename = 'asset-report-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($assets, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_map(fn ($c) => self::COLUMNS[$c], $columns));

            foreach ($assets as $asset) {
                fputcsv($handle, array_map(fn ($c) => $this->cell($asset, $c), $columns));
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Build the filtered asset collection and a human-readable description of
     * every filter that was applied (used as the printout's header).
     *
     * @return array{0: Collection<int, Asset>, 1: array<string, string>}
     */
    private function query(Request $request): array
    {
        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category');
        $locationId = $request->query('location');
        $statusId = $request->query('status');
        $department = $request->query('department');
        $custodianId = $request->query('custodian');
        $assignment = $request->query('assignment'); // assigned | unassigned
        $from = $request->query('purchase_from');
        $to = $request->query('purchase_to');

        $assets = Asset::with(['category', 'type', 'location', 'assetStatus', 'custodian', 'photo'])
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('asset_code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('serial_number', 'like', "%{$search}%")
                ->orWhere('department', 'like', "%{$search}%")
                ->orWhere('supplier', 'like', "%{$search}%")
                ->orWhereHas('category', fn ($r) => $r->where('name', 'like', "%{$search}%"))
                ->orWhereHas('location', fn ($r) => $r->where('name', 'like', "%{$search}%"))
                ->orWhereHas('assetStatus', fn ($r) => $r->where('name', 'like', "%{$search}%"))
                ->orWhereHas('custodian', fn ($r) => $r->where('name', 'like', "%{$search}%"))))
            ->when($categoryId, fn ($q) => $q->where('asset_category_id', $categoryId))
            ->when($locationId, fn ($q) => $q->where('asset_location_id', $locationId))
            ->when($statusId, fn ($q) => $q->where('asset_status_id', $statusId))
            ->when($department, fn ($q) => $q->where('department', $department))
            ->when($custodianId, fn ($q) => $q->where('custodian_id', $custodianId))
            ->when($assignment === 'assigned', fn ($q) => $q->whereNotNull('custodian_id'))
            ->when($assignment === 'unassigned', fn ($q) => $q->whereNull('custodian_id'))
            ->when($from, fn ($q) => $q->whereDate('purchase_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('purchase_date', '<=', $to))
            ->orderBy($this->sort($request), $this->dir($request))
            ->get();

        // Grouping keeps the chosen sort order inside each group.
        if ($group = $this->group($request)) {
            $assets = $assets->sortBy(fn (Asset $asset) => $this->cell($asset, $group), SORT_NATURAL | SORT_FLAG_CASE)->values();
        }

        $filters = array_filter([
            'Search' => $search ?: null,
            'Category' => $categoryId ? optional(AssetCategory::find($categoryId))->name : null,
            'Location' => $locationId ? optional(AssetLocation::find($locationId))->name : null,
            'Status' => $statusId ? optional(AssetStatus::find($statusId))->name : null,
            'Department' => $department ?: null,
            'Custodian' => $custodianId ? optional(User::find($custodianId))->name : null,
            'Assignment' => $assignment ? ucfirst($assignment) : null,
            'Purchased from' => $from ?: null,
            'Purchased until' => $to ?: null,
            'Grouped by' => $group ? self::GROUPS[$group] : null,
        ]);

        return [$assets, $filters];
    }

    /**
     * @param  Collection<int, Asset>  $assets
     * @param  array<string, string>  $filters
     * @return array<string, mixed>
     */
    private function viewData(Request $request, Collection $assets, array $filters): array
    {
        $group = $this->group($request);

        // Per-asset group label plus a count and value for each group heading.
        $groupLabels = $group ? $assets->mapWithKeys(fn (Asset $a) => [$a->id => $this->cell($a, $group)]) : collect();
        $groupSummary = $group
            ? $assets->groupBy(fn (Asset $a) => $groupLabels[$a->id])->map(fn (Collection $items) => [
                'count' => $items->count(),
                'value' => $items->sum(fn (Asset $a) => (float) $a->purchase_price),
            ])
            : collect();

        return [
            'assets' => $assets,
            'filters' => $filters,
            'group' => $group,
            'groupOptions' => self::GROUPS,
            'groupLabels' => $groupLabels,
            'groupSummary' => $groupSummary,
            'allColumns' => self::COLUMNS,
            'selectedColumns' => $this->selectedColumns($request),
            'statuses' => AssetStatus::orderBy('name')->get(),
            'categories' => AssetCategory::orderBy('name')->get(),
            'locations' => AssetLocation::orderBy('name')->get(),
            'custodians' => User::orderBy('name')->get(),
            'departments' => Asset::whereNotNull('department')->distinct()->orderBy('department')->pluck('department'),
            'query' => $request->query(),
            'reportCount' => $assets->count(),
            'assignedCount' => $assets->whereNotNull('custodian_id')->count(),
            'unassignedCount' => $assets->whereNull('custodian_id')->count(),
            'totalValue' => $assets->sum(fn (Asset $a) => (float) $a->purchase_price),
            'sort' => $this->sort($request),
            'dir' => $this->dir($request),
            'sortable' => self::SORTABLE,
        ];
    }

    private function sort(Request $request): string
    {
        return in_array($request->query('sort'), self::SORTABLE, true)
            ? $request->query('sort')
            : 'asset_code';
    }

    private function group(Request $request): ?string
    {
        $group = $request->query('group');

        return is_string($group) && array_key_exists($group, self::GROUPS) ? $group : null;
    }

    private function dir(Request $request): string
    {
        return $request->query('dir') === 'desc' ? 'desc' : 'asc';
    }

    /**
     * @return list<string>
     */
    private function selectedColumns(Request $request): array
    {
        if ($request->boolean('reset')) {
            $request->session()->forget('report_columns');
        }

        $requested = array_filter((array) $request->query('columns', []), 'is_string');
        $valid = array_values(array_intersect(array_keys(self::COLUMNS), $requested));

        // The last column choice is remembered for the rest of the session.
        if ($valid) {
            $request->session()->put('report_columns', $valid);

            return $valid;
        }

        $remembered = array_values(array_intersect(array_keys(self::COLUMNS), (array) $request->session()->get('report_columns', [])));

        return $remembered ?: self::DEFAULT_COLUMNS;
    }

    private function cell(Asset $asset, string $column): string
    {
        return match ($column) {
            'asset_code' => (string) $asset->asset_code,
            'name' => (string) $asset->name,
            'serial_number' => $asset->serial_number ?? '-',
            'category' => $asset->category->name ?? '-',
            'type' => $asset->type->name ?? '-',
            'location' => $asset->location->name ?? $asset->location_detail ?? '-',
            'department' => $asset->department ?? '-',
            'custodian' => $asset->custodian->name ?? 'Unassigned',
            'status' => $asset->assetStatus->name ?? 'Unknown',
            'purchase_date' => optional($asset->purchase_date)->format('Y-m-d') ?? '-',
            'purchase_price' => $asset->purchase_price !== null ? number_format((float) $asset->purchase_price, 2, '.', '') : '',
            'supplier' => $asset->supplier ?? '-',
            'po_reference' => $asset->po_reference ?? '-',
            'warranty_expiry_date' => optional($asset->warranty_expiry_date)->format('Y-m-d') ?? '-',
            default => '',
        };
    }
}
