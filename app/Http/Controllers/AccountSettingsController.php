<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PhotoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
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
        ], [
            'name.min' => 'Name must be at least 3 characters.',
        ]);

        $user->update(['name' => $data['name'], 'email' => $data['email']]);

        return back()->with('status', 'Profile details updated successfully!');
    }

    /**
     * Save a new profile picture (sent on its own as soon as one is chosen).
     */
    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'photo.mimes' => 'Only JPG or PNG images are allowed.',
            'photo.max' => 'File size must not exceed 2MB.',
        ]);

        $this->photoService->replace($request->user(), $request->file('photo'));

        return back()->with('status', 'Profile picture updated!');
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
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [
            'password.different' => 'The new password must be different from your current password.',
        ]);

        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', 'Password updated successfully!');
    }

    /**
     * How complete the profile is, with the items still missing.
     *
     * @return array{percent: int, items: array<string, bool>}
     */
    private function completeness(User $user): array
    {
        $items = [
            'Full name' => mb_strlen(trim((string) $user->name)) >= 3,
            'Valid email address' => filter_var($user->email, FILTER_VALIDATE_EMAIL) !== false,
            'Profile photo' => $user->photo !== null,
            'Password protected' => filled($user->password),
        ];

        return [
            'percent' => (int) round(count(array_filter($items)) / count($items) * 100),
            'items' => $items,
        ];
    }
}
