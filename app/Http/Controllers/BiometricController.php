<?php

namespace App\Http\Controllers;

use App\Models\BiometricToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BiometricController extends Controller
{
    /**
     * Issue a device token for the signed-in user; the app keeps it behind the fingerprint.
     */
    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'token' => BiometricToken::issueFor($request->user()),
        ]);
    }

    /**
     * Sign in with a device token the app unlocked with a fingerprint.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        $biometricToken = BiometricToken::findByToken($validated['token']);

        if (! $biometricToken) {
            return back()->withErrors([
                'biometric' => __('Fingerprint sign-in is no longer valid. Sign in with your password.'),
            ]);
        }

        $biometricToken->forceFill(['last_used_at' => now()])->save();

        Auth::login($biometricToken->user, remember: true);

        $request->session()->regenerate();

        return redirect()->route('budget');
    }
}
