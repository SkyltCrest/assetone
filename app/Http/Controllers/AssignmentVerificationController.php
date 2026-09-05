<?php

namespace App\Http\Controllers;

use App\Models\AssetAssignment;
use App\Notifications\AssignmentAccepted;
use App\Notifications\AssignmentRejected;
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
        $assignments = AssetAssignment::with(['asset', 'assignedBy'])
            ->where('custodian_id', $request->user()->id)
            ->orderByRaw("FIELD(status, 'pending_verification', 'assigned', 'rejected', 'unassigned')")
            ->orderByDesc('assigned_date')
            ->paginate(10);

        return view('my-assignments.index', [
            'assignments' => $assignments,
            'pendingCount' => AssetAssignment::where('custodian_id', $request->user()->id)
                ->where('status', AssetAssignment::STATUS_PENDING)
                ->count(),
        ]);
    }

    /**
     * Verification screen for a single pending assignment.
     */
    public function show(Request $request, AssetAssignment $assignment): View
    {
        $this->authorizeCustodian($request, $assignment);

        $assignment->load(['asset.category', 'asset.location', 'asset.assetStatus', 'assignedBy']);

        return view('my-assignments.show', [
            'assignment' => $assignment,
        ]);
    }

    public function accept(Request $request, AssetAssignment $assignment): RedirectResponse
    {
        $this->authorizeCustodian($request, $assignment);
        $this->ensurePending($assignment);

        DB::transaction(function () use ($assignment) {
            $assignment->update([
                'status' => AssetAssignment::STATUS_ASSIGNED,
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);

            $assignment->asset->update(['custodian_id' => $assignment->custodian_id]);
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
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $assignment->update([
            'status' => AssetAssignment::STATUS_REJECTED,
            'verified_at' => now(),
            'rejection_reason' => $data['rejection_reason'] ?? null,
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
