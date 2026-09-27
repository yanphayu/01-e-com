<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdController extends Controller
{
    public function index(): View
    {
        $ads = Ad::query()->orderBy('sort_order')->orderByDesc('id')->paginate(15);

        return view('admin.ads.index', compact('ads'));
    }

    public function create(): View
    {
        return view('admin.ads.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'headline' => ['nullable', 'string', 'max:160'],
            'subtext' => ['nullable', 'string', 'max:255'],
            'button_label' => ['nullable', 'string', 'max:60'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['placement'] = Ad::PLACEMENT_HOME;
        $validated['image'] = $request->file('image')->store('ads', 'public');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? ((int) Ad::query()->max('sort_order')) + 1;

        Ad::create($validated);

        return redirect()->route('admin.ads.index')->with('status', 'Ad created.');
    }

    public function edit(Ad $ad): View
    {
        return view('admin.ads.form', compact('ad'));
    }

    public function update(Request $request, Ad $ad): RedirectResponse
    {
        $validated = $request->validate([
            'headline' => ['nullable', 'string', 'max:160'],
            'subtext' => ['nullable', 'string', 'max:255'],
            'button_label' => ['nullable', 'string', 'max:60'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $replacedImage = null;

        if ($request->hasFile('image')) {
            $replacedImage = $ad->image;
            $validated['image'] = $request->file('image')->store('ads', 'public');
        } else {
            unset($validated['image']);
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? $ad->sort_order;

        $ad->update($validated);

        if ($replacedImage !== null) {
            Storage::disk('public')->delete($replacedImage);
        }

        return redirect()->route('admin.ads.index')->with('status', 'Ad updated.');
    }

    public function destroy(Ad $ad): RedirectResponse
    {
        Storage::disk('public')->delete($ad->image);
        $ad->delete();

        return redirect()->route('admin.ads.index')->with('status', 'Ad deleted.');
    }

    public function toggleActive(Ad $ad): RedirectResponse
    {
        $ad->update(['is_active' => ! $ad->is_active]);

        return back()->with('status', 'Ad status updated.');
    }
}
