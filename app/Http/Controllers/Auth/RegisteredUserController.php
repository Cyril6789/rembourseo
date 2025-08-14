<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    public function create()
    {
        session([
            'captcha_a' => $a = random_int(1, 9),
            'captcha_b' => $b = random_int(1, 9),
            'captcha_answer' => $a + $b,
        ]);

        return view('auth.register');
    }

    public function store(Request $request)
    {
        $honeypot = $request->input('website');

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255','unique:users,email'],
            'password' => ['required', Password::defaults(), 'confirmed'],
            'captcha' => ['required','numeric'],
        ]);

        if (! empty($honeypot)) {
            throw ValidationException::withMessages(['email' => __('Requête invalide.')]);
        }

        if ((int)$data['captcha'] !== (int) session('captcha_answer')) {
            session([
                'captcha_a' => $a = random_int(1, 9),
                'captcha_b' => $b = random_int(1, 9),
                'captcha_answer' => $a + $b,
            ]);
            throw ValidationException::withMessages(['captcha' => __('Captcha incorrect, réessayez.')]);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
