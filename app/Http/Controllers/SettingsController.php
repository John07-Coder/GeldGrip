<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('settings');
    }

    public function update(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)]]);
        $request->user()->update($data);

        return back()->with('status', 'Je profiel is bijgewerkt.');
    }

    public function password(Request $request)
    {
        $data = $request->validate(['current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)]]);
        $request->user()->update(['password' => $data['password']]);

        return back()->with('status', 'Je wachtwoord is gewijzigd.');
    }

    public function destroy(Request $request)
    {
        $request->validate(['current_password' => ['required', 'current_password']]);
        $user = $request->user();
        Auth::logout();
        $user->delete(); // Foreign keys remove all financial records in the same database operation.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('status', 'Je account en financiële gegevens zijn verwijderd.');
    }
}
