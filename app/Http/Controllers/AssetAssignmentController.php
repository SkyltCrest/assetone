<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $assignments = AssetAssignment::with(['asset', 'custodian'])
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->whereHas('asset', fn ($q3) => $q3->where('name', 'like', "%{$search}%")->orWhere('asset_code', 'like', "%{$search}%"))
                ->orWhereHas('custodian', fn ($q3) => $q3->where('name', 'like', "%{$search}%"))))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('assigned_date')
            ->paginate(10)
            ->withQueryString();

        return view('assignments.index', [
            'assignments' => $assignments,
            'search' => $search,
            'status' => $status,
            'totalCount' => AssetAssignment::count(),
            'assignedCount' => AssetAssignment::where('status', 'assigned')->count(),
            'returnedCount' => AssetAssignment::where('status', 'unassigned')->count(),
            'assets' => Asset::orderBy('asset_code')->get(),
            'custodians' => User::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $asset = Asset::findOrFail($data['asset_id']);
        $data['department'] = $asset->department ?? 'Unassigned';

        $assignment = AssetAssignment::create($data);

        $this->syncAssetCustodian($assignment);

        return back()->with('status', 'Asset assigned successfully.');
    }

    public function update(Request $request, AssetAssignment $assignment): RedirectResponse
    {
        $data = $this->validated($request);

        $asset = Asset::findOrFail($data['asset_id']);
        $data['department'] = $asset->department ?? 'Unassigned';

        $assignment->update($data);

        $this->syncAssetCustodian($assignment);

        return back()->with('status', 'Assignment updated successfully.');
    }

    public function destroy(AssetAssignment $assignment): RedirectResponse
    {
        $assignment->delete();

        return back()->with('status', 'Assignment has been deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'custodian_id' => ['required', 'exists:users,id'],
            'assigned_date' => ['required', 'date'],
            'status' => ['required', 'in:assigned,unassigned'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    /**
     * Sync the asset's custodian with this assignment.
     */
    private function syncAssetCustodian(AssetAssignment $assignment): void
    {
        $asset = $assignment->asset;

        if ($assignment->status === 'assigned') {
            $asset->update(['custodian_id' => $assignment->custodian_id]);

            return;
        }

        if ($asset->custodian_id === $assignment->custodian_id) {
            $asset->update(['custodian_id' => null]);
        }
    }
}
