<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AssetCategoryController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('asset-management.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $category = DB::transaction(function () use ($data) {
            $category = AssetCategory::create([
                'code' => $this->nextCode(),
                'short_code' => $data['short_code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
            ]);

            $this->syncTypes($category, $data['types']);

            return $category;
        });

        return back()->with('status', "Category \"{$category->name}\" has been added successfully.");
    }

    public function update(Request $request, AssetCategory $category): RedirectResponse
    {
        $data = $this->validated($request, $category);

        DB::transaction(function () use ($category, $data) {
            $category->update([
                'short_code' => $data['short_code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
            ]);

            $this->syncTypes($category, $data['types']);
        });

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

    private function validated(Request $request, ?AssetCategory $category = null): array
    {
        $request->merge([
            'short_code' => strtoupper(trim((string) $request->input('short_code'))),
            'types' => collect($request->input('types', []))
                ->map(fn ($type) => [
                    'id' => $type['id'] ?? null,
                    'name' => trim((string) ($type['name'] ?? '')),
                    'code' => strtoupper(trim((string) ($type['code'] ?? ''))),
                ])
                ->values()
                ->all(),
        ]);

        // Codes already in use may contain digits; only new or changed codes must be letters.
        $knownTypeCodes = $category ? $category->types()->pluck('code', 'id')->map(fn ($code) => strtoupper((string) $code))->all() : [];
        $lettersOnly = function (?string $current) {
            return function (string $attribute, mixed $value, \Closure $fail) use ($current) {
                if ($value !== $current && ! preg_match('/^[A-Za-z]+$/', (string) $value)) {
                    $fail('Codes must contain letters only (A-Z).');
                }
            };
        };

        $typeRules = [];
        foreach ($request->input('types', []) as $i => $type) {
            $typeRules["types.{$i}.code"] = ['required', 'max:5', 'distinct:ignore_case', $lettersOnly($knownTypeCodes[(int) ($type['id'] ?? 0)] ?? null)];
        }

        return $request->validate($typeRules + [
            'name' => ['required', 'string', 'max:255'],
            'short_code' => ['required', 'max:5', $lettersOnly($category ? strtoupper((string) $category->short_code) : null), Rule::unique('asset_categories', 'short_code')->ignore($category?->id)],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
            'types' => ['required', 'array', 'min:1'],
            'types.*.id' => ['nullable', 'integer'],
            'types.*.name' => ['required', 'string', 'max:255', 'distinct:ignore_case'],
        ], [
            'short_code.unique' => 'That category code is already used by another category.',
            'types.required' => 'Add at least one asset type.',
            'types.*.name.distinct' => 'Each asset type name must be different.',
            'types.*.code.distinct' => 'Each asset type code must be different.',
        ]);
    }

    /**
     * Make the category's asset types match the submitted list: update the
     * ones that carry an id, create the new ones and remove the rest.
     *
     * @param  array<int, array{id: int|string|null, name: string, code: string}>  $types
     */
    private function syncTypes(AssetCategory $category, array $types): void
    {
        $existing = $category->types()->withCount('assets')->get()->keyBy('id');
        $kept = [];

        foreach ($types as $type) {
            $current = $type['id'] ? $existing->get((int) $type['id']) : null;

            if ($current) {
                $current->update(['name' => $type['name'], 'code' => $type['code']]);
                $kept[] = $current->id;
            } else {
                $kept[] = $category->types()->create(['name' => $type['name'], 'code' => $type['code']])->id;
            }
        }

        foreach ($existing->except($kept) as $removed) {
            if ($removed->assets_count > 0) {
                throw ValidationException::withMessages([
                    'types' => "Asset type \"{$removed->name}\" still has assets and cannot be removed.",
                ]);
            }

            $removed->delete();
        }
    }

    private function nextCode(): string
    {
        $last = AssetCategory::orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->code, 4)) + 1 : 1;

        return 'CAT-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
