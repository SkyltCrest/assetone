<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;
use App\Notifications\AssignmentAwaitingVerification;
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
            'pendingCount' => AssetAssignment::where('status', AssetAssignment::STATUS_PENDING)->count(),
            'assignedCount' => AssetAssignment::where('status', AssetAssignment::STATUS_ASSIGNED)->count(),
            'rejectedCount' => AssetAssignment::where('status', AssetAssignment::STATUS_REJECTED)->count(),
            'assets' => Asset::orderBy('asset_code')->get(),
            'custodians' => User::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $asset = Asset::findOrFail($data['asset_id']);

        $assignment = AssetAssignment::create([
            'asset_id' => $asset->id,
            'custodian_id' => $data['custodian_id'],
            'assigned_by' => $request->user()->id,
            'department' => $asset->department ?? 'Unassigned',
            'assigned_date' => $data['assigned_date'],
            'status' => AssetAssignment::STATUS_PENDING,
            'notes' => $data['notes'] ?? null,
        ]);

        $assignment->custodian->notify(new AssignmentAwaitingVerification($assignment->load('asset', 'assignedBy')));

        return back()->with('status', "Assignment created. Waiting for {$assignment->custodian->name} to verify.");
    }

    public function update(Request $request, AssetAssignment $assignment): RedirectResponse
    {
        $data = $this->validated($request);

        $asset = Asset::findOrFail($data['asset_id']);
        $custodianChanged = (int) $data['custodian_id'] !== (int) $assignment->custodian_id;

        $assignment->update([
            'asset_id' => $asset->id,
            'custodian_id' => $data['custodian_id'],
            'department' => $asset->department ?? 'Unassigned',
            'assigned_date' => $data['assigned_date'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        // Re-open verification if the officer put it back into a pending state,
        // or reassigned a still-pending record to a different person.
        if ($assignment->isPending() && ($custodianChanged || $assignment->wasChanged('status'))) {
            $assignment->update(['verified_at' => null, 'rejection_reason' => null]);
            $assignment->custodian->notify(new AssignmentAwaitingVerification($assignment->load('asset', 'assignedBy')));
        }

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
            'status' => ['sometimes', 'required', 'in:pending_verification,assigned,rejected,unassigned'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    /**
     * Keep the asset's current custodian in step with an accepted assignment.
     */
    private function syncAssetCustodian(AssetAssignment $assignment): void
    {
        $asset = $assignment->asset;

        if ($assignment->status === AssetAssignment::STATUS_ASSIGNED) {
            $asset->update(['custodian_id' => $assignment->custodian_id]);

            return;
        }

        if ($asset->custodian_id === $assignment->custodian_id) {
            $asset->update(['custodian_id' => null]);
        }
    }
}
