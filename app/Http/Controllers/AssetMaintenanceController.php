<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Support\CsvExport;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
        'cancelled' => 'Cancelled',
    ];

    public function index(Request $request): View|StreamedResponse
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $type = $request->query('type');
        $perPage = in_array((int) $request->query('per_page'), [5, 10, 25, 50], true) ? (int) $request->query('per_page') : 10;

        $query = AssetMaintenance::with('asset.photo')
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('maintenance_code', 'like', "%{$search}%")
                ->orWhereHas('asset', fn ($q3) => $q3->where('name', 'like', "%{$search}%")->orWhere('asset_code', 'like', "%{$search}%"))))
            ->when($status === 'overdue', fn ($q) => $q->overdue())
            ->when($status && $status !== 'overdue', fn ($q) => $q->where('status', $status))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderByDesc('maintenance_date')
            ->orderByDesc('id');

        // Export every record that matches the filters, not just the page on screen.
        if ($request->boolean('export')) {
            return CsvExport::download('asset-maintenance',
                ['Maintenance ID', 'Asset ID', 'Asset Name', 'Type', 'Maintenance Date', 'Next Maintenance', 'Service Provider', 'Cost (RM)', 'Status', 'Due Status', 'Notes'],
                $query->get()->map(fn (AssetMaintenance $m) => [
                    $m->maintenance_code,
                    $m->asset->asset_code ?? '',
                    $m->asset->name ?? '',
                    self::TYPES[$m->type] ?? $m->type,
                    $m->maintenance_date->format('Y-m-d'),
                    optional($m->next_maintenance_date)->format('Y-m-d'),
                    $m->service_provider,
                    $m->cost,
                    self::STATUSES[$m->status] ?? $m->status,
                    $m->dueStatus()[0],
                    $m->description,
                ]));
        }

        $maintenances = $query->paginate($perPage)->withQueryString();

        // Open records with a next maintenance date, nearest (or most overdue) first.
        $upcoming = AssetMaintenance::with('asset')
            ->whereNotIn('status', [AssetMaintenance::STATUS_COMPLETED, AssetMaintenance::STATUS_CANCELLED])
            ->whereNotNull('next_maintenance_date')
            ->orderBy('next_maintenance_date')
            ->take(10)
            ->get();

        return view('maintenance.index', [
            'maintenances' => $maintenances,
            'upcoming' => $upcoming,
            'search' => $search,
            'status' => $status,
            'type' => $type,
            'perPage' => $perPage,
            'chips' => [
                '' => ['All', AssetMaintenance::count()],
                'pending' => ['Pending', AssetMaintenance::where('status', 'pending')->count()],
                'in_progress' => ['In Progress', AssetMaintenance::where('status', 'in_progress')->count()],
                'completed' => ['Completed', AssetMaintenance::where('status', 'completed')->count()],
                'cancelled' => ['Cancelled', AssetMaintenance::where('status', 'cancelled')->count()],
                'overdue' => ['Overdue', AssetMaintenance::overdue()->count()],
            ],
            'assetsServiced' => AssetMaintenance::distinct()->count('asset_id'),
            'types' => self::TYPES,
            'statuses' => self::STATUSES,
            'totalCount' => AssetMaintenance::count(),
            'pendingCount' => AssetMaintenance::where('status', 'pending')->count(),
            'inProgressCount' => AssetMaintenance::where('status', 'in_progress')->count(),
            'overdueCount' => AssetMaintenance::overdue()->count(),
            'assets' => Asset::orderBy('asset_code')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['maintenance_code'] = AssetMaintenance::nextCode();

        AssetMaintenance::create($data);

        return back()->with('status', 'Maintenance record added successfully.');
    }

    public function update(Request $request, AssetMaintenance $maintenance): RedirectResponse
    {
        $maintenance->update($this->validated($request));

        return back()->with('status', 'Maintenance record updated successfully.');
    }

    /**
     * Change only the status (used when a card is moved on the board).
     */
    public function updateStatus(Request $request, AssetMaintenance $maintenance): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(self::STATUSES))],
        ]);

        $maintenance->update(['status' => $data['status']]);

        return response()->json([
            'ok' => true,
            'message' => "{$maintenance->maintenance_code} moved to ".self::STATUSES[$data['status']],
        ]);
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
            'next_maintenance_date' => ['nullable', 'date', 'after:maintenance_date'],
            'service_provider' => ['required', 'string', 'max:255'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:'.implode(',', array_keys(self::STATUSES))],
            'description' => ['nullable', 'string'],
        ], [
            'next_maintenance_date.after' => 'The next maintenance date must be after the maintenance date.',
        ]);
    }
}
