<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $categorySearch = $request->query('category_search');
        $locationSearch = $request->query('location_search');
        $statusSearch = $request->query('status_search');

        $categories = AssetCategory::withCount('assets')
            ->when($categorySearch, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$categorySearch}%")
                ->orWhere('code', 'like', "%{$categorySearch}%")))
            ->orderBy('code')
            ->paginate(10, ['*'], 'categoriesPage')
            ->withQueryString();

        $locations = AssetLocation::withCount('assets')
            ->when($locationSearch, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$locationSearch}%")
                ->orWhere('code', 'like', "%{$locationSearch}%")
                ->orWhere('department', 'like', "%{$locationSearch}%")))
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
            'statuses' => $statuses,
            'statusSearch' => $statusSearch,
        ]);
    }
}
