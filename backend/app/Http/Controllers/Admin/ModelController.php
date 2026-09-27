<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\ProductModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModelController extends Controller
{
    public function filteredQuery(Request $request): Builder
    {
        $query = ProductModel::with('brand')->withCount('attributes');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->string('search')->trim()}%");
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->integer('brand_id'));
        }

        return $query->latest();
    }

    public function index(Request $request): View
    {
        $models = $this->filteredQuery($request)->paginate(15)->withQueryString();
        $brands = Brand::orderBy('name')->get(['id', 'name']);

        return view('admin.models.index', compact('models', 'brands'));
    }

    public function create(): View
    {
        $brands = Brand::orderBy('name')->get(['id', 'name']);
        $attributes = Attribute::orderBy('name')->get(['id', 'name']);

        return view('admin.models.form', ['brands' => $brands, 'attributes' => $attributes, 'model' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'brand_id' => ['required', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:255'],
            'attribute_ids' => ['nullable', 'array'],
            'attribute_ids.*' => ['exists:attributes,id'],
        ]);

        $attributeIds = $validated['attribute_ids'] ?? [];
        unset($validated['attribute_ids']);

        $model = ProductModel::create($validated);
        $model->attributes()->sync($attributeIds);

        return redirect()->route('admin.models.index')->with('status', 'Model created.');
    }

    public function edit(ProductModel $model): View
    {
        $model->load('attributes');

        $brands = Brand::orderBy('name')->get(['id', 'name']);
        $attributes = Attribute::orderBy('name')->get(['id', 'name']);

        return view('admin.models.form', compact('model', 'brands', 'attributes'));
    }

    public function update(Request $request, ProductModel $model): RedirectResponse
    {
        $validated = $request->validate([
            'brand_id' => ['required', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:255'],
            'attribute_ids' => ['nullable', 'array'],
            'attribute_ids.*' => ['exists:attributes,id'],
        ]);

        $attributeIds = $validated['attribute_ids'] ?? [];
        unset($validated['attribute_ids']);

        $model->update($validated);
        $model->attributes()->sync($attributeIds);

        return redirect()->route('admin.models.index')->with('status', 'Model updated.');
    }

    public function destroy(ProductModel $model): RedirectResponse
    {
        $model->delete();

        return redirect()->route('admin.models.index')->with('status', 'Model deleted.');
    }
}
