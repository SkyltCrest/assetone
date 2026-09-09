<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\IssueReport;
use App\Models\User;
use App\Notifications\IssueReported;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

/**
 * "Report Issues" - the staff side of the damaged-asset workflow.
 *
 * A staff member reports a fault on an asset they hold; the complaint is
 * routed to the asset officers, who verify it (see IssueVerificationController).
 */
class IssueReportController extends Controller
{
    public function index(Request $request): View
    {
        $reports = IssueReport::with(['asset', 'verifier'])
            ->where('reported_by', $request->user()->id)
            ->orderByRaw("FIELD(status, 'pending_verification', 'accepted', 'rejected', 'resolved')")
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('issues.index', [
            'reports' => $reports,
            'reportableAssets' => $this->reportableAssets($request),
            'openCount' => IssueReport::where('reported_by', $request->user()->id)
                ->whereIn('status', [IssueReport::STATUS_PENDING, IssueReport::STATUS_ACCEPTED])
                ->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $reportable = $this->reportableAssets($request);

        $data = $request->validate([
            'asset_id' => ['required', 'integer', 'in:'.$reportable->pluck('id')->implode(',')],
            'description' => ['required', 'string', 'max:2000'],
        ], [
            'asset_id.in' => 'You can only report an issue for an asset assigned to you.',
        ]);

        $report = IssueReport::create([
            'report_code' => IssueReport::nextCode(),
            'asset_id' => $data['asset_id'],
            'reported_by' => $request->user()->id,
            'description' => $data['description'],
            'status' => IssueReport::STATUS_PENDING,
        ]);

        $this->notifyOfficers($report);

        return redirect()->route('issues.index')
            ->with('status', "Issue {$report->report_code} submitted. An asset officer will verify it shortly.");
    }

    public function show(Request $request, IssueReport $issue): View
    {
        abort_unless($issue->reported_by === $request->user()->id, 403,
            'This report was submitted by someone else.');

        $issue->load(['asset.category', 'asset.assetStatus', 'verifier', 'maintenance']);

        return view('issues.show', ['report' => $issue]);
    }

    /**
     * Assets the current user may raise an issue against: the ones they
     * currently hold as custodian.
     *
     * @return \Illuminate\Support\Collection<int, Asset>
     */
    private function reportableAssets(Request $request): \Illuminate\Support\Collection
    {
        return Asset::where('custodian_id', $request->user()->id)
            ->orderBy('asset_code')
            ->get(['id', 'asset_code', 'name']);
    }

    private function notifyOfficers(IssueReport $report): void
    {
        // The complaint is routed to the asset officers. If there are none,
        // fall back to administrators so it is never left unassigned.
        $officers = User::where('status', 'active')->where('role', 'asset_officer')->get();

        if ($officers->isEmpty()) {
            $officers = User::where('status', 'active')->where('role', 'administrator')->get();
        }

        if ($officers->isNotEmpty()) {
            Notification::send($officers, new IssueReported($report->load('asset', 'reporter')));
        }
    }
}
