<?php

namespace App\Http\Controllers;

use App\Models\AssetMaintenance;
use App\Models\IssueReport;
use App\Notifications\IssueReportAccepted;
use App\Notifications\IssueReportRejected;
use App\Observers\ActivityObserver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * "Issue Verification" - the asset officer side of the damaged-asset workflow.
 *
 * The officer receives a notification, tests the asset, then either rejects
 * the complaint (asset is fine) or accepts it, which opens a maintenance
 * record and flips the asset to "Under Maintenance". When that maintenance
 * is completed the asset returns to "Active" and the report is auto-resolved
 * (see AssetMaintenanceObserver).
 */
class IssueVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = $request->query('status');

        $reports = IssueReport::with(['asset', 'reporter', 'verifier', 'photo'])
            ->withCount('photos')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('report_code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('asset', fn ($a) => $a->where('name', 'like', "%{$search}%")->orWhere('asset_code', 'like', "%{$search}%"))
                ->orWhereHas('reporter', fn ($u) => $u->where('name', 'like', "%{$search}%"))))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByRaw("CASE status WHEN 'pending_verification' THEN 0 WHEN 'accepted' THEN 1 WHEN 'rejected' THEN 2 WHEN 'resolved' THEN 3 ELSE 4 END")
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('issue-verifications.index', [
            'reports' => $reports,
            'totalCount' => IssueReport::count(),
            'pendingCount' => IssueReport::where('status', IssueReport::STATUS_PENDING)->count(),
            'maintenanceCount' => IssueReport::where('status', IssueReport::STATUS_ACCEPTED)->count(),
            'resolvedCount' => IssueReport::where('status', IssueReport::STATUS_RESOLVED)->count(),
        ]);
    }

    public function show(Request $request, IssueReport $issue): View
    {
        $issue->load(['asset.category', 'asset.location', 'asset.assetStatus', 'asset.custodian', 'reporter', 'verifier', 'maintenance', 'photos']);

        return view('issue-verifications.show', [
            'report' => $issue,
            'maintenanceTypes' => AssetMaintenanceController::TYPES,
        ]);
    }

    public function accept(Request $request, IssueReport $issue): RedirectResponse
    {
        $this->ensurePending($issue);

        $data = $request->validate([
            'service_provider' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:'.implode(',', array_keys(AssetMaintenanceController::TYPES))],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($issue, $data, $request) {
            $maintenance = AssetMaintenance::create([
                'maintenance_code' => AssetMaintenance::nextCode(),
                'asset_id' => $issue->asset_id,
                'type' => $data['type'],
                'maintenance_date' => now()->toDateString(),
                'service_provider' => $data['service_provider'],
                'status' => AssetMaintenance::STATUS_IN_PROGRESS,
                'description' => $this->maintenanceDescription($issue, $data['note'] ?? null),
            ]);

            // Opening the maintenance record flips the asset to "Under Maintenance"
            // through AssetMaintenanceObserver.

            $issue->update([
                'status' => IssueReport::STATUS_ACCEPTED,
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'rejection_reason' => null,
                'asset_maintenance_id' => $maintenance->id,
            ]);
        });

        $issue->reporter?->notify(new IssueReportAccepted($issue->fresh()->load('asset', 'verifier')));

        return redirect()->route('issue-verifications.index')
            ->with('status', "Issue {$issue->report_code} accepted. A maintenance record was opened and the asset is now Under Maintenance.");
    }

    public function reject(Request $request, IssueReport $issue): RedirectResponse
    {
        $this->ensurePending($issue);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        $issue->update([
            'status' => IssueReport::STATUS_REJECTED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'rejection_reason' => $data['rejection_reason'],
        ]);

        $issue->reporter?->notify(new IssueReportRejected($issue->fresh()->load('asset', 'verifier')));

        return redirect()->route('issue-verifications.index')
            ->with('status', "Issue {$issue->report_code} rejected. The staff member has been notified.");
    }

    /**
     * Close an accepted report by hand: its maintenance record is completed,
     * which returns the asset to Active and resolves the report
     * (see AssetMaintenanceObserver).
     */
    public function resolve(Request $request, IssueReport $issue): RedirectResponse
    {
        abort_unless($issue->status === IssueReport::STATUS_ACCEPTED && $issue->maintenance, 403, 'Only a report that is under maintenance can be resolved.');

        $issue->maintenance->update(['status' => AssetMaintenance::STATUS_COMPLETED]);

        // In case nothing else resolved it, close the report directly.
        if ($issue->fresh()->status !== IssueReport::STATUS_RESOLVED) {
            $issue->update(['status' => IssueReport::STATUS_RESOLVED]);
        }

        return redirect()->route('issue-verifications.index')
            ->with('status', "Issue {$issue->report_code} marked as resolved.");
    }

    private function ensurePending(IssueReport $issue): void
    {
        abort_unless($issue->isPending(), 403, 'This report has already been verified.');
    }

    private function maintenanceDescription(IssueReport $issue, ?string $note): string
    {
        $text = "Reported issue {$issue->report_code}: ".trim($issue->description);

        if ($note !== null && trim($note) !== '') {
            $text .= "\n\nOfficer note: ".trim($note);
        }

        return $text;
    }
}
