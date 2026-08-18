<?php

namespace App\Http\Controllers;

use App\Models\AssetStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssetStatusController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'badge_color' => ['required', 'in:success,warning,danger,secondary,info,dark'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $data['code'] = $this->nextCode();

        AssetStatus::create($data);

        return back()->with('status', "Status \"{$data['name']}\" has been added successfully.");
    }

    public function update(Request $request, AssetStatus $status): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'badge_color' => ['required', 'in:success,warning,danger,secondary,info,dark'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $status->update($data);

        return back()->with('status', "Status \"{$status->name}\" has been updated.");
    }

    public function destroy(AssetStatus $status): RedirectResponse
    {
        if ($status->assets()->exists()) {
            return back()->withErrors([
                'status' => "Cannot delete \"{$status->name}\" — it still has assets assigned to it. Reassign those assets first.",
            ]);
        }

        $status->delete();

        return back()->with('status', 'Status has been deleted.');
    }

    private function nextCode(): string
    {
        $last = AssetStatus::orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->code, strrpos($last->code, '-') + 1)) + 1 : 1;

        return 'STAT-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
