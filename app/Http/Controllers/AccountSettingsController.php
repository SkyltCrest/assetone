<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PhotoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    public function __construct(private readonly PhotoService $photoService) {}

    public function index(Request $request): View
    {
        $user = $request->user()->load('photo');

        return view('settings.account', [
            'user' => $user,
            'completeness' => $this->completeness($user),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'photo' => ['nullable', ...PhotoService::RULES],
        ], [
            'name.min' => 'Name must be at least 3 characters.',
        ]);

        $user->update(['name' => $data['name'], 'email' => $data['email']]);

        if ($request->hasFile('photo')) {
            $this->photoService->replace($user, $request->file('photo'));
        }

        return back()->with('status', 'Profile updated successfully.');
    }

    public function removePhoto(Request $request): RedirectResponse
    {
        $request->user()->photos()->delete();

        return back()->with('status', 'Profile photo removed.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'password.different' => 'The new password must be different from your current password.',
        ]);

        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', 'Password changed successfully.');
    }

    /**
     * How complete the profile is, with the items still missing.
     *
     * @return array{percent: int, items: array<string, bool>}
     */
    private function completeness(User $user): array
    {
        $items = [
            'Full name' => filled($user->name),
            'Email address' => filled($user->email),
            'Department' => filled($user->department),
            'Profile photo' => $user->photo !== null,
        ];

        return [
            'percent' => (int) round(count(array_filter($items)) / count($items) * 100),
            'items' => $items,
        ];
    }
}
