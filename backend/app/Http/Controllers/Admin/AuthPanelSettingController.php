<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AuthPanelContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthPanelSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.auth-panel', [
            'content' => AuthPanelContent::resolve(),
            'defaults' => AuthPanelContent::defaults(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if ($request->boolean('reset')) {
            AuthPanelContent::forget();

            return redirect()
                ->route('admin.settings.auth-panel')
                ->with('status', 'Sign-in panel content restored to defaults.');
        }

        $validated = $request->validate([
            'headline_1' => ['nullable', 'string', 'max:120'],
            'headline_2' => ['nullable', 'string', 'max:120'],
            'sub' => ['nullable', 'string', 'max:400'],
            'bullet_1' => ['nullable', 'string', 'max:160'],
            'bullet_2' => ['nullable', 'string', 'max:160'],
            'bullet_3' => ['nullable', 'string', 'max:160'],
            'footer' => ['nullable', 'string', 'max:200'],
        ]);

        AuthPanelContent::persist($validated);

        return redirect()
            ->route('admin.settings.auth-panel')
            ->with('status', 'Sign-in panel content updated.');
    }
}
