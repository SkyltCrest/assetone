<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use App\Models\AssetType;
use Illuminate\Http\Request;
use App\Support\CsvExport;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View|StreamedResponse
    {
        $categorySearch = $request->query('category_search');
        $locationSearch = $request->query('location_search');
        $statusSearch = $request->query('status_search');
        $locationDepartment = $request->query('location_department');
        $locationStatus = $request->query('location_status');

        $categoriesQuery = AssetCategory::withCount('assets')
            ->with(['types' => fn ($q) => $q->withCount('assets')])
            ->when($categorySearch, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$categorySearch}%")
                ->orWhere('code', 'like', "%{$categorySearch}%")
                ->orWhere('short_code', 'like', "%{$categorySearch}%")
                ->orWhereHas('types', fn ($q3) => $q3->where('name', 'like', "%{$categorySearch}%")->orWhere('code', 'like', "%{$categorySearch}%"))))
            ->orderBy('code');
        $categories = (clone $categoriesQuery)->paginate(10, ['*'], 'categoriesPage')->withQueryString();

        $locationsQuery = AssetLocation::withCount('assets')
            ->when($locationSearch, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$locationSearch}%")
                ->orWhere('code', 'like', "%{$locationSearch}%")
                ->orWhere('department', 'like', "%{$locationSearch}%")
                ->orWhere('building', 'like', "%{$locationSearch}%")
                ->orWhere('floor', 'like', "%{$locationSearch}%")
                ->orWhere('room', 'like', "%{$locationSearch}%")))
            ->when($locationDepartment, fn ($q) => $q->where('department', $locationDepartment))
            ->when(in_array($locationStatus, ['active', 'inactive'], true), fn ($q) => $q->where('status', $locationStatus))
            ->orderBy('code');
        $locations = (clone $locationsQuery)->paginate(10, ['*'], 'locationsPage')->withQueryString();

        $statusesQuery = AssetStatus::withCount('assets')
            ->when($statusSearch, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$statusSearch}%")
                ->orWhere('code', 'like', "%{$statusSearch}%")))
            ->orderBy('code');
        $statuses = (clone $statusesQuery)->paginate(10, ['*'], 'statusesPage')->withQueryString();

        // "Export CSV" on a tab downloads every row that matches that tab's search.
        $export = $request->query('export');
        if ($export === 'category') {
            return CsvExport::download('asset-categories', ['Category ID', 'Code', 'Category Name', 'Description', 'Asset Types', 'Total Assets', 'Status'],
                $categoriesQuery->get()->map(fn ($c) => [$c->code, $c->short_code, $c->name, $c->description, $c->types->map(fn ($t) => "{$t->name} ({$t->code})")->implode(', '), $c->assets_count, ucfirst($c->status)]));
        }
        if ($export === 'location') {
            return CsvExport::download('asset-locations', ['Code', 'Location Name', 'Department', 'Building', 'Floor', 'Room', 'Total Assets', 'Status'],
                $locationsQuery->get()->map(fn ($l) => [$l->code, $l->name, $l->department, $l->building, $l->floor, $l->room, $l->assets_count, ucfirst($l->status)]));
        }
        if ($export === 'status') {
            return CsvExport::download('asset-statuses', ['Status ID', 'Status Name', 'Description', 'Total Assets', 'Status'],
                $statusesQuery->get()->map(fn ($s) => [$s->code, $s->name, $s->description, $s->assets_count, ucfirst($s->status)]));
        }

        return view('settings.index', [
            'categories' => $categories,
            'categorySearch' => $categorySearch,
            'locations' => $locations,
            'locationSearch' => $locationSearch,
            'locationDepartment' => $locationDepartment,
            'locationStatus' => $locationStatus,
            'activeCounts' => [
                'categories' => AssetCategory::where('status', 'active')->count(),
                'locations' => AssetLocation::where('status', 'active')->count(),
                'statuses' => AssetStatus::where('status', 'active')->count(),
            ],
            'statuses' => $statuses,
            'statusSearch' => $statusSearch,
            'categoryTotal' => AssetCategory::count(),
            'typeTotal' => AssetType::count(),
            'locationTotal' => AssetLocation::count(),
            'statusTotal' => AssetStatus::count(),
        ]);
    }
}
