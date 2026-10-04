<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;
use App\Notifications\AssignmentAwaitingVerification;
use App\Notifications\AssignmentOverdueReminder;
use App\Observers\ActivityObserver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Support\CsvExport;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class AssetAssignmentController extends Controller
{
    public function index(Request $request): View|StreamedResponse
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $query = AssetAssignment::with(['asset.photo', 'asset.assignments.custodian', 'custodian'])
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->whereHas('asset', fn ($q3) => $q3->where('name', 'like', "%{$search}%")->orWhere('asset_code', 'like', "%{$search}%"))
                ->orWhereHas('custodian', fn ($q3) => $q3->where('name', 'like', "%{$search}%"))))
            ->when($status === 'overdue', fn ($q) => $q->overdue())
            ->when($status && $status !== 'overdue', fn ($q) => $q->where('status', $status))
            ->orderByDesc('assigned_date')
            ->orderByDesc('id');

        // Export every record that matches the filters, not just the page on screen.
        if ($request->boolean('export')) {
            return CsvExport::download('asset-assignments',
                ['Asset Code', 'Asset Name', 'PIC', 'Role', 'Department', 'Assigned Date', 'Due Date', 'Returned Date', 'Status', 'Note'],
                $query->get()->map(fn (AssetAssignment $a) => [
                    $a->asset->asset_code ?? '',
                    $a->asset->name ?? '',
                    $a->custodian->name ?? '',
                    $a->custodian ? ucwords(str_replace('_', ' ', $a->custodian->role)) : '',
                    $a->department,
                    $a->assigned_date->format('Y-m-d'),
                    optional($a->due_date)->format('Y-m-d'),
                    optional($a->returned_date)->format('Y-m-d'),
                    $a->isOverdue() ? 'Overdue' : $a->statusLabel(),
                    $a->return_note ?: $a->notes,
                ]));
        }

        $assignments = $query->paginate(10)->withQueryString();

        $counts = [
            'all' => AssetAssignment::count(),
            'pending' => AssetAssignment::where('status', AssetAssignment::STATUS_PENDING)->count(),
            'assigned' => AssetAssignment::where('status', AssetAssignment::STATUS_ASSIGNED)->count(),
            'overdue' => AssetAssignment::overdue()->count(),
            'rejected' => AssetAssignment::where('status', AssetAssignment::STATUS_REJECTED)->count(),
            'returned' => AssetAssignment::where('status', AssetAssignment::STATUS_UNASSIGNED)->count(),
        ];

        // Assets that already have a live (assigned or awaiting verification) assignment.
        $busyAssetIds = AssetAssignment::whereIn('status', [AssetAssignment::STATUS_ASSIGNED, AssetAssignment::STATUS_PENDING])
            ->pluck('asset_id')->unique()->values()->all();

        return view('assignments.index', [
            'chips' => [
                '' => ['All', $counts['all']],
                'pending_verification' => ['Pending Verification', $counts['pending']],
                'assigned' => ['Assigned', $counts['assigned']],
                'overdue' => ['Overdue', $counts['overdue']],
                'rejected' => ['Rejected', $counts['rejected']],
                'unassigned' => ['Returned', $counts['returned']],
            ],
            'busyAssetIds' => $busyAssetIds,
            'assetTotal' => Asset::count(),
            'availableCount' => Asset::whereNotIn('id', $busyAssetIds)->count(),
            'assignments' => $assignments,
            'search' => $search,
            'status' => $status,
            'totalCount' => AssetAssignment::count(),
            'pendingCount' => AssetAssignment::where('status', AssetAssignment::STATUS_PENDING)->count(),
            'assignedCount' => AssetAssignment::where('status', AssetAssignment::STATUS_ASSIGNED)->count(),
            'overdueCount' => AssetAssignment::overdue()->count(),
            'returnedCount' => AssetAssignment::where('status', AssetAssignment::STATUS_UNASSIGNED)->count(),
            'assets' => Asset::with('photo')->orderBy('asset_code')->get(),
            'custodians' => User::where('status', 'active')->orderBy('name')->get(),
            'loanDurations' => config('assetone.loan_durations'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, requireLoan: true);

        $asset = Asset::findOrFail($data['asset_id']);
        $assignment = AssetAssignment::create([
            'asset_id' => $asset->id,
            'custodian_id' => $data['custodian_id'],
            'assigned_by' => $request->user()->id,
            'department' => $asset->department ?? 'Unassigned',
            'assigned_date' => $data['assigned_date'],
            'loan_days' => $data['loan_days'],
            'due_date' => $this->dueDate($data['assigned_date'], $data['loan_days']),
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
        $loanDays = $data['loan_days'] ?? null;

        // "Returned" needs a return date; any other status must not carry one.
        $returnedDate = $data['status'] === AssetAssignment::STATUS_UNASSIGNED
            ? ($assignment->returned_date ?? today())
            : null;

        $assignment->update([
            'asset_id' => $asset->id,
            'custodian_id' => $data['custodian_id'],
            'department' => $asset->department ?? 'Unassigned',
            'assigned_date' => $data['assigned_date'],
            'loan_days' => $loanDays,
            'due_date' => $this->dueDate($data['assigned_date'], $loanDays),
            'returned_date' => $returnedDate,
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

    /**
     * Take an asset back from its custodian.
     */
    public function returnAsset(Request $request, AssetAssignment $assignment): RedirectResponse
    {
        abort_unless($assignment->status === AssetAssignment::STATUS_ASSIGNED, 403,
            'Only an asset that is currently assigned can be returned.');

        $data = $request->validate([
            'returned_date' => ['nullable', 'date', 'after_or_equal:'.$assignment->assigned_date->toDateString(), 'before_or_equal:today'],
            'return_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'returned_date.after_or_equal' => 'The return date cannot be earlier than the assigned date.',
            'returned_date.before_or_equal' => 'The return date cannot be in the future.',
        ]);

        $assignment->update([
            'status' => AssetAssignment::STATUS_UNASSIGNED,
            'returned_date' => $data['returned_date'] ?? today(),
            'return_note' => $data['return_note'] ?? null,
        ]);

        $this->syncAssetCustodian($assignment);

        return back()->with('status', "{$assignment->asset->asset_code} has been returned and is available again.");
    }

    /**
     * Remind the custodian that an asset is overdue for return.
     */
    public function remind(Request $request, AssetAssignment $assignment): RedirectResponse
    {
        abort_unless($assignment->isOverdue() && $assignment->custodian, 403, 'Only an overdue assignment can be reminded.');

        $assignment->custodian->notify(new AssignmentOverdueReminder($assignment->load('asset'), $request->user()->name));

        $daysLate = (int) $assignment->due_date->diffInDays(today());

        return back()->with('status', "Reminder sent to {$assignment->custodian->name} — {$daysLate} day(s) overdue.");
    }

    public function destroy(AssetAssignment $assignment): RedirectResponse
    {
        $assignment->delete();

        return back()->with('status', 'Assignment has been deleted.');
    }

    private function validated(Request $request, bool $requireLoan = false): array
    {
        return $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'custodian_id' => ['required', 'exists:users,id'],
            'assigned_date' => ['required', 'date'],
            'loan_days' => [$requireLoan ? 'required' : 'nullable', 'integer', 'min:1', $requireLoan ? 'max:365' : 'max:3650'],
            'status' => ['sometimes', 'required', 'in:pending_verification,assigned,rejected,unassigned'],
            'notes' => ['nullable', 'string'],
        ], [
            'loan_days.required' => 'Choose how long the asset is on loan.',
        ]);
    }

    private function dueDate(string $assignedDate, int|string|null $loanDays): ?Carbon
    {
        return $loanDays ? Carbon::parse($assignedDate)->addDays((int) $loanDays) : null;
    }

    /**
     * Keep the asset's current custodian in step with an accepted assignment.
     */
    private function syncAssetCustodian(AssetAssignment $assignment): void
    {
        // The assignment record itself is the reported activity; keeping the
        // asset's custodian in step is an internal side effect.
        ActivityObserver::silently(function () use ($assignment) {
            $asset = $assignment->asset;

            if ($assignment->status === AssetAssignment::STATUS_ASSIGNED) {
                $asset->update(['custodian_id' => $assignment->custodian_id, 'assigned_date' => $assignment->assigned_date]);

                return;
            }

            if ($asset->custodian_id === $assignment->custodian_id) {
                $asset->update(['custodian_id' => null, 'assigned_date' => null]);
            }
        });
    }
}
