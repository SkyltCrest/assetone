<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetReportController extends Controller
{
    public function index(Request $request): View
    {
        $statusId = $request->query('status');

        $assets = Asset::with(['category', 'location', 'assetStatus', 'custodian'])
            ->when($statusId, fn ($q) => $q->where('asset_status_id', $statusId))
            ->orderBy('asset_code')
            ->get();

        return view('reports.index', [
            'assets' => $assets,
            'statuses' => AssetStatus::orderBy('name')->get(),
            'selectedStatus' => $statusId,
            'totalAssets' => Asset::count(),
            'assignedAssets' => Asset::whereNotNull('custodian_id')->count(),
            'unassignedAssets' => Asset::whereNull('custodian_id')->count(),
        ]);
    }
}
