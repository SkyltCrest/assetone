<?php

namespace App\Http\Controllers;

use App\Models\AssetLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssetLocationController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('asset-management.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $data['code'] = $this->nextCode();

        AssetLocation::create($data);

        return back()->with('status', "Location \"{$data['name']}\" has been added successfully.");
    }

    public function update(Request $request, AssetLocation $location): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $location->update($data);

        return back()->with('status', "Location \"{$location->name}\" has been updated.");
    }

    public function destroy(AssetLocation $location): RedirectResponse
    {
        if ($location->assets()->exists()) {
            return back()->withErrors([
                'location' => "Cannot delete \"{$location->name}\" — it still has assets assigned to it.",
            ]);
        }

        $location->delete();

        return back()->with('status', 'Location has been deleted.');
    }

    private function nextCode(): string
    {
        $last = AssetLocation::orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->code, 4)) + 1 : 1;

        return 'LOC-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
