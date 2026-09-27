<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttributeController extends Controller
{
    public function filteredQuery(Request $request): Builder
    {
        $query = Attribute::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->string('search')->trim()}%");
        }

        return $query->latest();
    }

    public function index(Request $request): View
    {
        $attributes = $this->filteredQuery($request)->paginate(15)->withQueryString();

        return view('admin.attributes.index', compact('attributes'));
    }

    public function create(): View
    {
        return view('admin.attributes.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:attributes,name'],
        ]);

        Attribute::create($validated);

        return redirect()->route('admin.attributes.index')->with('status', 'Attribute created.');
    }

    public function edit(Attribute $attribute): View
    {
        return view('admin.attributes.form', compact('attribute'));
    }

    public function update(Request $request, Attribute $attribute): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:attributes,name,'.$attribute->id],
        ]);

        $attribute->update($validated);

        return redirect()->route('admin.attributes.index')->with('status', 'Attribute updated.');
    }

    public function destroy(Attribute $attribute): RedirectResponse
    {
        $attribute->delete();

        return redirect()->route('admin.attributes.index')->with('status', 'Attribute deleted.');
    }
}
