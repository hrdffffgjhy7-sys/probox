<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        abort_unless(in_array($locale, ['en', 'ur'], true), 404);
        $request->session()->put('locale', $locale);

        if (auth()->check()) {
            auth()->user()->forceFill(['locale' => $locale])->saveQuietly();
        }

        return back();
    }
}
