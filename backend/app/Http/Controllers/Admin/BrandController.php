<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function filteredQuery(Request $request): Builder
    {
        $query = Brand::with('subcategory')->withCount('models');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->string('search')->trim()}%");
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->integer('subcategory_id'));
        }

        return $query->latest();
    }

    public function index(Request $request): View
    {
        $brands = $this->filteredQuery($request)->paginate(15)->withQueryString();
        $subcategories = Subcategory::orderBy('name')->get(['id', 'name']);

        return view('admin.brands.index', compact('brands', 'subcategories'));
    }

    public function create(): View
    {
        $subcategories = Subcategory::orderBy('name')->get(['id', 'name']);

        return view('admin.brands.form', ['subcategories' => $subcategories, 'brand' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subcategory_id' => ['required', 'exists:subcategories,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        Brand::create($validated);

        return redirect()->route('admin.brands.index')->with('status', 'Brand created.');
    }

    public function edit(Brand $brand): View
    {
        $subcategories = Subcategory::orderBy('name')->get(['id', 'name']);

        return view('admin.brands.form', compact('subcategories', 'brand'));
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $validated = $request->validate([
            'subcategory_id' => ['required', 'exists:subcategories,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $brand->update($validated);

        return redirect()->route('admin.brands.index')->with('status', 'Brand updated.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $brand->delete();

        return redirect()->route('admin.brands.index')->with('status', 'Brand deleted.');
    }
}
