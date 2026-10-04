<?php

namespace App\Http\Controllers;

use App\Models\AssetLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetLocationController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('asset-management.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        AssetLocation::create($data);

        return back()->with('status', "Location \"{$data['name']}\" has been added successfully.");
    }

    public function update(Request $request, AssetLocation $location): RedirectResponse
    {
        $data = $this->validated($request, $location);

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

    private function validated(Request $request, ?AssetLocation $location = null): array
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);

        return $request->validate([
            'name' => [
                'required', 'string', 'min:3', 'max:255',
                Rule::unique('asset_locations', 'name')
                    ->where('department', $request->input('department'))
                    ->ignore($location?->id),
            ],
            'code' => ['required', 'string', 'max:15', 'regex:/^[A-Z0-9-]+$/', Rule::unique('asset_locations', 'code')->ignore($location?->id)],
            'department' => ['required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:255'],
            'room' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'name.min' => 'Location name must contain at least 3 characters.',
            'name.unique' => 'This location name already exists under the selected department.',
            'code.regex' => 'Location code can only contain letters, numbers and dashes.',
            'code.unique' => 'This location code already exists.',
        ]);
    }

    private function nextCode(): string
    {
        $last = AssetLocation::orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->code, 4)) + 1 : 1;

        return 'LOC-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
