<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PhotoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Support\CsvExport;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public const ROLES = [
        'administrator' => 'Administrator',
        'asset_officer' => 'Asset Officer',
        'department_staff' => 'Department Staff',
    ];

    /** Sort options: label and the column/direction pairs to order by. */
    private const SORTS = [
        'name' => ['Name A-Z', [['name', 'asc']]],
        'name_desc' => ['Name Z-A', [['name', 'desc']]],
        'role' => ['Role', [['role', 'asc'], ['name', 'asc']]],
        'department' => ['Department', [['department', 'asc'], ['name', 'asc']]],
        'status' => ['Status', [['status', 'asc'], ['name', 'asc']]],
        'newest' => ['Newest first', [['created_at', 'desc']]],
    ];

    public function __construct(private readonly PhotoService $photoService) {}

    public function index(Request $request): View|StreamedResponse
    {
        $search = $request->query('search');
        $role = $request->query('role');
        $status = $request->query('status');
        $department = $request->query('department');
        $sort = array_key_exists($request->query('sort'), self::SORTS) ? $request->query('sort') : 'name';

        $users = User::with(['photo', 'assets.photo'])
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when($role, fn ($q) => $q->where('role', $role))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($department, fn ($q) => $q->where('department', $department));

        foreach (self::SORTS[$sort][1] as [$column, $direction]) {
            $users->orderBy($column, $direction);
        }

        // Export every user that matches the filters, not just the page on screen.
        if ($request->boolean('export')) {
            return CsvExport::download('users',
                ['Name', 'Username', 'Email', 'Role', 'Department', 'Status', 'Assets Held'],
                $users->get()->map(fn (User $u) => [
                    $u->name,
                    $u->username,
                    $u->email,
                    self::ROLES[$u->role] ?? ucwords(str_replace('_', ' ', $u->role)),
                    $u->department,
                    ucfirst($u->status),
                    $u->assets->count(),
                ]));
        }

        return view('users.index', [
            'users' => $users->paginate(10)->withQueryString(),
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'department' => $department,
            'sort' => $sort,
            'sorts' => array_map(fn ($option) => 'Sort: '.$option[0], self::SORTS),
            'roles' => self::ROLES,
            'departments' => config('assetone.departments'),
            'totalUsers' => User::count(),
            'activeUsers' => User::where('status', 'active')->count(),
            'inactiveUsers' => User::where('status', 'inactive')->count(),
            'departmentCount' => User::whereNotNull('department')->distinct()->count('department'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['password'] = Hash::make($data['password']);

        $user = DB::transaction(function () use ($data, $request) {
            $user = User::create($data);

            if ($request->hasFile('photo')) {
                $this->photoService->attach($user, $request->file('photo'));
            }

            return $user;
        });

        return back()->with('status', "User \"{$user->name}\" has been added successfully.");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        DB::transaction(function () use ($user, $data, $request) {
            $user->update($data);

            if ($request->hasFile('photo')) {
                $this->photoService->replace($user, $request->file('photo'));
            }
        });

        return back()->with('status', "User \"{$user->name}\" has been updated.");
    }

    /**
     * Switch one account between active and inactive.
     */
    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'You cannot deactivate your own account while logged in.']);
        }

        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);

        return back()->with('status', "User status for \"{$user->name}\" set to ".ucfirst($user->status).'.');
    }

    /**
     * Activate or deactivate several accounts at once.
     */
    public function bulkStatus(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,inactive'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ]);

        $changed = User::whereIn('id', $data['ids'])
            ->where('id', '!=', $request->user()->id)
            ->where('status', '!=', $data['status'])
            ->get()
            ->each(fn (User $user) => $user->update(['status' => $data['status']]))
            ->count();

        return back()->with('status', "{$changed} user(s) set to ".ucfirst($data['status']).'.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'You cannot delete your own account while logged in.']);
        }

        // A person in charge of assets cannot simply disappear.
        if ($user->assets()->exists()) {
            return back()->withErrors(['user' => "Cannot delete \"{$user->name}\" because they are still the PIC of one or more assets. Reassign those assets first."]);
        }

        $user->delete();

        return back()->with('status', "User \"{$user->name}\" has been deleted successfully.");
    }

    private function validated(Request $request, ?User $user = null): array
    {
        // An account may keep a role that is no longer offered in the list.
        $roles = array_keys(self::ROLES);
        if ($user) {
            $roles[] = $user->role;
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => ['required', Rule::in($roles)],
            'department' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'photo' => ['nullable', ...PhotoService::RULES],
        ]);

        unset($data['photo']);

        return $data;
    }
}
