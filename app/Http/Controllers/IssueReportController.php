<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\IssueReport;
use App\Models\User;
use App\Notifications\IssueReported;
use App\Services\PhotoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    /** Most damage photos a single report may carry. */
    public const MAX_PHOTOS = 4;

    public function __construct(private readonly PhotoService $photoService) {}

    public function index(Request $request): View
    {
        $mine = fn () => IssueReport::where('reported_by', $request->user()->id);

        $search = trim((string) $request->query('search'));
        $status = $request->query('status');

        $reports = IssueReport::with(['asset', 'verifier', 'photo'])
            ->withCount('photos')
            ->where('reported_by', $request->user()->id)
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('report_code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('asset', fn ($a) => $a->where('name', 'like', "%{$search}%")->orWhere('asset_code', 'like', "%{$search}%"))))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByRaw("FIELD(status, 'pending_verification', 'accepted', 'rejected', 'resolved')")
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('issues.index', [
            'reports' => $reports,
            'reportableAssets' => $this->reportableAssets($request),
            'totalCount' => $mine()->count(),
            'pendingCount' => $mine()->where('status', IssueReport::STATUS_PENDING)->count(),
            'maintenanceCount' => $mine()->where('status', IssueReport::STATUS_ACCEPTED)->count(),
            'rejectedCount' => $mine()->where('status', IssueReport::STATUS_REJECTED)->count(),
            'maxPhotos' => self::MAX_PHOTOS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $reportable = $this->reportableAssets($request);

        $data = $request->validate([
            'asset_id' => ['required', 'integer', 'in:'.$reportable->pluck('id')->implode(',')],
            'description' => ['required', 'string', 'max:600'],
            'photos' => ['required', 'array', 'min:1', 'max:'.self::MAX_PHOTOS],
            'photos.*' => PhotoService::RULES,
        ], [
            'asset_id.in' => 'You can only report an issue for an asset assigned to you.',
            'photos.required' => 'Please add at least one photo of the damage.',
            'photos.max' => 'You can attach up to '.self::MAX_PHOTOS.' photos.',
        ]);

        $report = DB::transaction(function () use ($data, $request) {
            $report = IssueReport::create([
                'report_code' => IssueReport::nextCode(),
                'asset_id' => $data['asset_id'],
                'reported_by' => $request->user()->id,
                'description' => $data['description'],
                'status' => IssueReport::STATUS_PENDING,
            ]);

            foreach ($request->file('photos') as $file) {
                $this->photoService->attach($report, $file, 'photos');
            }

            return $report;
        });

        $this->notifyOfficers($report);

        return redirect()->route('issues.index')
            ->with('status', "Issue {$report->report_code} submitted. An asset officer will verify it shortly.");
    }

    public function show(Request $request, IssueReport $issue): View
    {
        abort_unless($issue->reported_by === $request->user()->id, 403,
            'This report was submitted by someone else.');

        $issue->load(['asset.category', 'asset.assetStatus', 'verifier', 'maintenance', 'photos']);

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
