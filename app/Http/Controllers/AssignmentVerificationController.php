<?php

namespace App\Http\Controllers;

use App\Models\AssetAssignment;
use App\Notifications\AssignmentAccepted;
use App\Notifications\AssignmentRejected;
use App\Observers\ActivityObserver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssignmentVerificationController extends Controller
{
    /**
     * "My Assignments" - assignments where the current user is the custodian.
     */
    public function index(Request $request): View
    {
        $mine = fn () => AssetAssignment::where('custodian_id', $request->user()->id);

        $search = trim((string) $request->query('search'));
        $status = $request->query('status');

        $assignments = AssetAssignment::with(['asset.photo', 'assignedBy'])
            ->where('custodian_id', $request->user()->id)
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->whereHas('asset', fn ($a) => $a->where('name', 'like', "%{$search}%")->orWhere('asset_code', 'like', "%{$search}%"))
                ->orWhereHas('assignedBy', fn ($u) => $u->where('name', 'like', "%{$search}%"))))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByRaw("FIELD(status, 'pending_verification', 'assigned', 'rejected', 'unassigned')")
            ->orderByDesc('assigned_date')
            ->paginate(10)
            ->withQueryString();

        return view('my-assignments.index', [
            'assignments' => $assignments,
            'totalCount' => $mine()->count(),
            'pendingCount' => $mine()->where('status', AssetAssignment::STATUS_PENDING)->count(),
            'acceptedCount' => $mine()->where('status', AssetAssignment::STATUS_ASSIGNED)->count(),
            'rejectedCount' => $mine()->where('status', AssetAssignment::STATUS_REJECTED)->count(),
        ]);
    }

    /**
     * Verification screen for a single pending assignment.
     */
    public function show(Request $request, AssetAssignment $assignment): View
    {
        $this->authorizeCustodian($request, $assignment);

        $assignment->load(['asset.category', 'asset.type', 'asset.location', 'asset.assetStatus', 'asset.photo', 'assignedBy']);

        return view('my-assignments.show', [
            'assignment' => $assignment,
        ]);
    }

    public function accept(Request $request, AssetAssignment $assignment): RedirectResponse
    {
        $this->authorizeCustodian($request, $assignment);
        $this->ensurePending($assignment);

        DB::transaction(function () use ($assignment, $request) {
            // A reassignment: whoever held the asset until now hands it over.
            AssetAssignment::where('asset_id', $assignment->asset_id)
                ->where('id', '!=', $assignment->id)
                ->where('status', AssetAssignment::STATUS_ASSIGNED)
                ->get()
                ->each(fn (AssetAssignment $previous) => $previous->update([
                    'status' => AssetAssignment::STATUS_UNASSIGNED,
                    'returned_date' => today(),
                    'return_note' => 'Reassigned to '.$request->user()->name,
                ]));

            $assignment->update([
                'status' => AssetAssignment::STATUS_ASSIGNED,
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);

            ActivityObserver::silently(fn () => $assignment->asset->update([
                'custodian_id' => $assignment->custodian_id,
                'assigned_date' => $assignment->assigned_date,
            ]));
        });

        $assignment->assignedBy?->notify(new AssignmentAccepted($assignment->load('asset', 'custodian')));

        return redirect()->route('my-assignments.index')
            ->with('status', 'Assignment accepted. The asset is now officially assigned to you.');
    }

    public function reject(Request $request, AssetAssignment $assignment): RedirectResponse
    {
        $this->authorizeCustodian($request, $assignment);
        $this->ensurePending($assignment);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Please say why you are rejecting this assignment.',
        ]);

        $assignment->update([
            'status' => AssetAssignment::STATUS_REJECTED,
            'verified_at' => now(),
            'rejection_reason' => $data['rejection_reason'],
        ]);

        $assignment->assignedBy?->notify(new AssignmentRejected($assignment->load('asset', 'custodian')));

        return redirect()->route('my-assignments.index')
            ->with('status', 'Assignment rejected. The asset officer has been notified.');
    }

    private function authorizeCustodian(Request $request, AssetAssignment $assignment): void
    {
        abort_unless($assignment->custodian_id === $request->user()->id, 403,
            'This assignment is not addressed to you.');
    }

    private function ensurePending(AssetAssignment $assignment): void
    {
        abort_unless($assignment->isPending(), 403,
            'This assignment has already been verified.');
    }
}
