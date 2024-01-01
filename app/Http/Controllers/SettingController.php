<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use App\Repositories\SettingRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        $settings = SettingRepository::first();
        return view('settings.edit', ['settings' => $settings]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        SettingRepository::update(SettingRepository::first(), $request->validated());

        return Redirect::route('settings.edit')->with('status', 'settings-updated');
    }
}
