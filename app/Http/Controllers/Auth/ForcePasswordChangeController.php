<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ForcePasswordChangeController extends Controller
{
    public function show()
    {
        if (! auth()->user()->must_change_password) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.force-password-change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ]);

        $user = $request->user();

        // The temporary password must not simply be re-used.
        if (Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'The new password must be different from your temporary password.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->input('password')),
            'must_change_password' => false,
        ]);

        $request->session()->regenerate();
        AuditLog::record('admin.password_changed', $user);

        return redirect()->route('admin.dashboard')->with('success', 'Password updated.');
    }
}
