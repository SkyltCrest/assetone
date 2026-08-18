<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetMaintenanceController extends Controller
{
    public const TYPES = [
        'service' => 'Routine Maintenance',
        'repair' => 'Repair',
        'inspection' => 'Inspection',
        'upgrade' => 'Software Update',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
    ];

    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $type = $request->query('type');

        $maintenances = AssetMaintenance::with('asset')
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('maintenance_code', 'like', "%{$search}%")
                ->orWhereHas('asset', fn ($q3) => $q3->where('name', 'like', "%{$search}%")->orWhere('asset_code', 'like', "%{$search}%"))))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderByDesc('maintenance_date')
            ->paginate(10)
            ->withQueryString();

        return view('maintenance.index', [
            'maintenances' => $maintenances,
            'search' => $search,
            'status' => $status,
            'type' => $type,
            'types' => self::TYPES,
            'statuses' => self::STATUSES,
            'totalCount' => AssetMaintenance::count(),
            'pendingCount' => AssetMaintenance::where('status', 'pending')->count(),
            'inProgressCount' => AssetMaintenance::where('status', 'in_progress')->count(),
            'completedCount' => AssetMaintenance::where('status', 'completed')->count(),
            'assets' => Asset::orderBy('asset_code')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['maintenance_code'] = $this->nextCode();

        AssetMaintenance::create($data);

        return back()->with('status', 'Maintenance record added successfully.');
    }

    public function update(Request $request, AssetMaintenance $maintenance): RedirectResponse
    {
        $maintenance->update($this->validated($request));

        return back()->with('status', 'Maintenance record updated successfully.');
    }

    public function destroy(AssetMaintenance $maintenance): RedirectResponse
    {
        $maintenance->delete();

        return back()->with('status', 'Maintenance record has been deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'type' => ['required', 'in:'.implode(',', array_keys(self::TYPES))],
            'maintenance_date' => ['required', 'date'],
            'service_provider' => ['required', 'string', 'max:255'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:'.implode(',', array_keys(self::STATUSES))],
            'description' => ['nullable', 'string'],
        ]);
    }

    private function nextCode(): string
    {
        $last = AssetMaintenance::orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->maintenance_code, 4)) + 1 : 1;

        return 'MNT-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
