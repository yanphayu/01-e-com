<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubcategoryController extends Controller
{
    public function filteredQuery(Request $request): Builder
    {
        $query = Subcategory::with('category')->withCount('products');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->string('search')->trim()}%");
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        return $query->latest();
    }

    public function index(Request $request): View
    {
        $subcategories = $this->filteredQuery($request)->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.subcategories.index', compact('subcategories', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.subcategories.form', ['categories' => $categories, 'subcategory' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'has_brand' => ['sometimes', 'boolean'],
            'has_model' => ['sometimes', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Subcategory::create($validated);

        return redirect()->route('admin.subcategories.index')->with('status', 'Subcategory created.');
    }

    public function edit(Subcategory $subcategory): View
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.subcategories.form', compact('categories', 'subcategory'));
    }

    public function update(Request $request, Subcategory $subcategory): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'has_brand' => ['sometimes', 'boolean'],
            'has_model' => ['sometimes', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $subcategory->update($validated);

        return redirect()->route('admin.subcategories.index')->with('status', 'Subcategory updated.');
    }

    public function destroy(Subcategory $subcategory): RedirectResponse
    {
        $subcategory->delete();

        return redirect()->route('admin.subcategories.index')->with('status', 'Subcategory deleted.');
    }

    public function toggleActive(Subcategory $subcategory): RedirectResponse
    {
        $subcategory->update(['is_active' => ! $subcategory->is_active]);

        return back()->with('status', 'Subcategory status updated.');
    }
}
