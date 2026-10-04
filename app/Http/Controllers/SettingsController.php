<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use App\Models\AssetType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $categorySearch = $request->query('category_search');
        $locationSearch = $request->query('location_search');
        $statusSearch = $request->query('status_search');
        $locationDepartment = $request->query('location_department');
        $locationStatus = $request->query('location_status');

        $categories = AssetCategory::withCount('assets')
            ->with(['types' => fn ($q) => $q->withCount('assets')])
            ->when($categorySearch, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$categorySearch}%")
                ->orWhere('code', 'like', "%{$categorySearch}%")
                ->orWhere('short_code', 'like', "%{$categorySearch}%")
                ->orWhereHas('types', fn ($q3) => $q3->where('name', 'like', "%{$categorySearch}%")->orWhere('code', 'like', "%{$categorySearch}%"))))
            ->orderBy('code')
            ->paginate(10, ['*'], 'categoriesPage')
            ->withQueryString();

        $locations = AssetLocation::withCount('assets')
            ->when($locationSearch, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$locationSearch}%")
                ->orWhere('code', 'like', "%{$locationSearch}%")
                ->orWhere('department', 'like', "%{$locationSearch}%")
                ->orWhere('building', 'like', "%{$locationSearch}%")
                ->orWhere('floor', 'like', "%{$locationSearch}%")
                ->orWhere('room', 'like', "%{$locationSearch}%")))
            ->when($locationDepartment, fn ($q) => $q->where('department', $locationDepartment))
            ->when(in_array($locationStatus, ['active', 'inactive'], true), fn ($q) => $q->where('status', $locationStatus))
            ->orderBy('code')
            ->paginate(10, ['*'], 'locationsPage')
            ->withQueryString();

        $statuses = AssetStatus::withCount('assets')
            ->when($statusSearch, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$statusSearch}%")
                ->orWhere('code', 'like', "%{$statusSearch}%")))
            ->orderBy('code')
            ->paginate(10, ['*'], 'statusesPage')
            ->withQueryString();

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
