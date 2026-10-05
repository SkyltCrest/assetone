<?php

namespace App\Http\Controllers;

use App\Models\AssetAssignment;
use App\Models\AssetMaintenance;
use App\Models\IssueReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Prints the Treasury (Pekeliling Perbendaharaan AM 2.4) forms, filled in
 * from the records that match a set of filters:
 *   KEW.PA-9   movement / loan application   <- asset assignments
 *   KEW.PA-10  damage complaint              <- issue reports
 */
class OfficialFormController extends Controller
{
    /**
     * The forms that can be printed, keyed by the `form` query value.
     *
     * @var array<string, array{code: string, title: string, about: string}>
     */
    private const FORMS = [
        'pa9' => [
            'code' => 'KEW.PA-9',
            'title' => 'Borang Permohonan Pergerakan / Pinjaman Aset Alih',
            'about' => 'One form per loan application, listing every asset a person borrowed that day.',
        ],
        'pa10' => [
            'code' => 'KEW.PA-10',
            'title' => 'Borang Aduan Kerosakan Aset Alih',
            'about' => 'One form per reported issue.',
        ],
    ];

    /** The printed KEW.PA-9 has six asset rows; shorter loans are padded to match. */
    private const PA9_MIN_ROWS = 6;

    public function index(Request $request): View
    {
        $form = $this->form($request);
        [$forms, $filters] = $form === 'pa9' ? $this->loanForms($request) : $this->complaintForms($request);
        $canManage = $request->user()->canManageAssets();

        return view('forms.index', [
            'form' => $form,
            'formOptions' => self::FORMS,
            'forms' => $forms,
            'filters' => $filters,
            'canManage' => $canManage,
            'statusOptions' => $form === 'pa9' ? $this->loanStatuses() : $this->complaintStatuses(),
            'people' => $canManage ? User::orderBy('name')->get(['id', 'name']) : collect(),
            'departments' => $form === 'pa9' && $canManage
                ? AssetAssignment::distinct()->orderBy('department')->pluck('department')
                : collect(),
        ]);
    }

    public function print(Request $request): View
    {
        $form = $this->form($request);
        [$forms] = $form === 'pa9' ? $this->loanForms($request) : $this->complaintForms($request);

        return view('forms.print', [
            'form' => $form,
            'meta' => self::FORMS[$form],
            'forms' => $forms,
            'minRows' => self::PA9_MIN_ROWS,
        ]);
    }

    private function form(Request $request): string
    {
        $form = $request->query('form');

        return is_string($form) && array_key_exists($form, self::FORMS) ? $form : 'pa9';
    }

    /**
     * KEW.PA-9: matching assignments, grouped into one form per loan application.
     *
     * @return array{0: Collection<int, array<string, mixed>>, 1: array<string, string>}
     */
    private function loanForms(Request $request): array
    {
        $user = $request->user();
        $search = trim((string) $request->query('search', ''));
        $personId = $user->canManageAssets() ? $request->query('person') : null;
        $department = $request->query('department');
        $status = $request->query('status');
        $from = $request->query('from');
        $to = $request->query('to');
        $only = AssetAssignment::find($request->query('only'));

        $assignments = AssetAssignment::with(['asset', 'custodian', 'assignedBy'])
            // Department staff only ever see their own loans.
            ->when(! $user->canManageAssets(), fn ($q) => $q->where('custodian_id', $user->id))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('department', 'like', "%{$search}%")
                ->orWhere('application_no', 'like', "%{$search}%")
                ->orWhereHas('asset', fn ($r) => $r->where('asset_code', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"))
                ->orWhereHas('custodian', fn ($r) => $r->where('name', 'like', "%{$search}%"))))
            ->when($personId, fn ($q) => $q->where('custodian_id', $personId))
            ->when($department, fn ($q) => $q->where('department', $department))
            ->when($status === 'overdue', fn ($q) => $q->overdue())
            ->when($status && $status !== 'overdue', fn ($q) => $q->where('status', $status))
            ->when($from, fn ($q) => $q->whereDate('assigned_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('assigned_date', '<=', $to))
            // A single form picked from the list: the application that assignment belongs to.
            ->when($only, fn ($q) => $only->application_no
                ? $q->where('application_no', $only->application_no)
                : $q->whereKey($only->id))
            ->orderBy('assigned_date')
            ->orderBy('id')
            ->get();

        $forms = $assignments
            ->groupBy(fn (AssetAssignment $a) => $a->application_no ?? 'loan-'.$a->id)
            ->map(function (Collection $items) {
                $first = $items->first();
                $allReturned = $items->every(fn (AssetAssignment $a) => $a->returned_date !== null);

                return [
                    'key' => $first->id,
                    'number' => (string) $first->application_no,
                    'applicant' => $first->custodian->name ?? '',
                    'applicantPosition' => (string) $first->custodian?->position,
                    'department' => $first->department,
                    'purpose' => $items->pluck('notes')->filter()->unique()->implode('; '),
                    'place' => $items->pluck('place_of_use')->filter()->unique()->implode('; '),
                    'issuer' => $first->assignedBy->name ?? '',
                    'issuerPosition' => (string) $first->assignedBy?->position,
                    'borrowed' => $first->assigned_date,
                    'approved' => $first->created_at,
                    'returned' => $allReturned ? $items->max('returned_date') : null,
                    'items' => $items,
                ];
            })
            ->values();

        $filters = array_filter([
            'Search' => $search ?: null,
            'Borrower' => $personId ? optional(User::find($personId))->name : null,
            'Department' => $department ?: null,
            'Status' => $status ? ($this->loanStatuses()[$status] ?? null) : null,
            'Loaned from' => $from ?: null,
            'Loaned until' => $to ?: null,
        ]);

        return [$forms, $filters];
    }

    /**
     * KEW.PA-10: one form per matching issue report.
     *
     * @return array{0: Collection<int, array<string, mixed>>, 1: array<string, string>}
     */
    private function complaintForms(Request $request): array
    {
        $user = $request->user();
        $search = trim((string) $request->query('search', ''));
        $personId = $user->canManageAssets() ? $request->query('person') : null;
        $status = $request->query('status');
        $from = $request->query('from');
        $to = $request->query('to');
        $only = $request->query('only');

        $reports = IssueReport::with(['asset.type', 'asset.category', 'asset.custodian', 'reporter', 'verifier', 'maintenance'])
            // Department staff only ever see the issues they reported.
            ->when(! $user->canManageAssets(), fn ($q) => $q->where('reported_by', $user->id))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('report_code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('asset', fn ($r) => $r->where('asset_code', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"))
                ->orWhereHas('reporter', fn ($r) => $r->where('name', 'like', "%{$search}%"))))
            ->when($personId, fn ($q) => $q->where('reported_by', $personId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->when($only, fn ($q) => $q->whereKey($only))
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        // Earlier maintenance spend on each asset (item 8 of the form).
        $history = AssetMaintenance::whereIn('asset_id', $reports->pluck('asset_id')->unique())
            ->where('status', AssetMaintenance::STATUS_COMPLETED)
            ->whereNotNull('cost')
            ->get(['id', 'asset_id', 'maintenance_date', 'cost'])
            ->groupBy('asset_id');

        $forms = $reports->map(function (IssueReport $report) use ($history) {
            $previous = ($history[$report->asset_id] ?? collect())
                ->filter(fn (AssetMaintenance $m) => $m->id !== $report->asset_maintenance_id
                    && $m->maintenance_date !== null
                    && $m->maintenance_date->lte($report->created_at));

            return [
                'key' => $report->id,
                'report' => $report,
                'previousCost' => $previous->sum(fn (AssetMaintenance $m) => (float) $m->cost),
                'estimate' => $report->maintenance?->cost,
                'recommendation' => $report->resolution_note ?: ($report->rejection_reason ?: ($report->maintenance->description ?? '')),
            ];
        });

        $filters = array_filter([
            'Search' => $search ?: null,
            'Reported by' => $personId ? optional(User::find($personId))->name : null,
            'Status' => $status ? ($this->complaintStatuses()[$status] ?? null) : null,
            'Reported from' => $from ?: null,
            'Reported until' => $to ?: null,
        ]);

        return [$forms, $filters];
    }

    /**
     * @return array<string, string>
     */
    private function loanStatuses(): array
    {
        return [
            AssetAssignment::STATUS_PENDING => 'Pending Verification',
            AssetAssignment::STATUS_ASSIGNED => 'Assigned',
            'overdue' => 'Overdue',
            AssetAssignment::STATUS_UNASSIGNED => 'Returned',
            AssetAssignment::STATUS_REJECTED => 'Rejected',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function complaintStatuses(): array
    {
        return [
            IssueReport::STATUS_PENDING => 'Pending Verification',
            IssueReport::STATUS_ACCEPTED => 'Accepted — Under Maintenance',
            IssueReport::STATUS_REJECTED => 'Rejected',
            IssueReport::STATUS_RESOLVED => 'Resolved',
        ];
    }
}
