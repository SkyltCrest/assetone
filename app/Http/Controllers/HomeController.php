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
            ->take(5)
            ->get();

        return view('home', [
            'assetTotal' => $assetTotal,
            'categoryTotal' => AssetCategory::count(),
            'locationTotal' => AssetLocation::count(),
            'userTotal' => User::count(),
            'statusBreakdown' => $statusBreakdown,
            'recentAssets' => $recentAssets,
            'recentMaintenance' => $recentMaintenance,
        ]);
    }
}
