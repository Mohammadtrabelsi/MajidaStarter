<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        if (array_key_exists($locale, config('app.available_locales', []))) {
            $request->session()->put('locale', $locale);
        }

        return redirect()->back();
    }
}
