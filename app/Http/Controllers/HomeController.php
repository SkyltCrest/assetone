<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetMaintenance;
use App\Models\AssetStatus;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $assetTotal = Asset::count();

        $statusBreakdown = AssetStatus::withCount('assets')
            ->where('status', 'active')
            ->orderByDesc('assets_count')
            ->get();

        $recentAssets = Asset::with(['category', 'location', 'assetStatus'])
            ->latest()
            ->take(5)
            ->get();

        $recentMaintenance = AssetMaintenance::with('asset')
            ->latest('maintenance_date')
            ->take(4)
            ->get();

        $categoryBreakdown = AssetCategory::withCount('assets')
            ->orderByDesc('assets_count')
            ->get(['id', 'name']);

        $users = User::with('photo')->orderByDesc('created_at')->get(['id', 'name', 'email', 'role', 'status', 'created_at']);

        return view('home', [
            'assetTotal' => $assetTotal,
            'categoryTotal' => AssetCategory::count(),
            'locationTotal' => AssetLocation::count(),
            'userTotal' => $users->count(),
            'activeUserTotal' => $users->where('status', 'active')->count(),
            'roleBreakdown' => $users->countBy('role')->sortDesc(),
            // Enough rows for the Active / Deactivated chips to filter from; the page shows five at a time.
            'recentUsers' => $users->take(30),
            'categoryBreakdown' => $categoryBreakdown,
            'statusBreakdown' => $statusBreakdown,
            'recentAssets' => $recentAssets,
            'recentMaintenance' => $recentMaintenance,
        ]);
    }
}
