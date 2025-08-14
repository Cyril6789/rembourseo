<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        // Générer un captcha simple (A + B) et le stocker en session
        session([
            'captcha_a' => $a = random_int(1, 9),
            'captcha_b' => $b = random_int(1, 9),
            'captcha_answer' => $a + $b,
        ]);

        return view('auth.login');
    }

    public function store(Request $request)
    {
        // Honeypot: doit être vide
        $honeypot = $request->input('website');

        $data = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
            'captcha' => ['required','numeric'],
        ]);

        if (! empty($honeypot)) {
            // soupçon de bot
            throw ValidationException::withMessages(['email' => __('Requête invalide.')]);
        }

        // Vérif captcha
        if ((int)$data['captcha'] !== (int) session('captcha_answer')) {
            // régénérer un nouveau challenge
            session([
                'captcha_a' => $a = random_int(1, 9),
                'captcha_b' => $b = random_int(1, 9),
                'captcha_answer' => $a + $b,
            ]);
            throw ValidationException::withMessages(['captcha' => __('Captcha incorrect, réessayez.')]);
        }

        $remember = (bool) $request->boolean('remember');

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $remember)) {
            // régénérer un challenge pour éviter la réutilisation
            session([
                'captcha_a' => $a = random_int(1, 9),
                'captcha_b' => $b = random_int(1, 9),
                'captcha_answer' => $a + $b,
            ]);
            throw ValidationException::withMessages([
                'email' => __('Identifiants invalides.'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
