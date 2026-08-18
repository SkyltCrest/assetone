<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssetCategoryController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('asset-management.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $data['code'] = $this->nextCode();

        AssetCategory::create($data);

        return back()->with('status', "Category \"{$data['name']}\" has been added successfully.");
    }

    public function update(Request $request, AssetCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $category->update($data);

        return back()->with('status', "Category \"{$category->name}\" has been updated.");
    }

    public function destroy(AssetCategory $category): RedirectResponse
    {
        if ($category->assets()->exists()) {
            return back()->withErrors([
                'category' => "Cannot delete \"{$category->name}\" — it still has assets assigned to it. Reassign or remove those assets first.",
            ]);
        }

        $category->delete();

        return back()->with('status', 'Category has been deleted.');
    }

    private function nextCode(): string
    {
        $last = AssetCategory::orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->code, 4)) + 1 : 1;

        return 'CAT-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
