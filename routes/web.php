<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\DashboardController;

// Page d'accueil -> dashboard (protégé)
Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

// ----- Auth email/mot de passe -----
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ----- SSO (Google / Facebook / Apple) -----
Route::get('/auth/{provider}', [OAuthController::class, 'redirect'])
    ->whereIn('provider', ['google','facebook','apple'])
    ->name('oauth.redirect');

Route::match(['get', 'post'], '/auth/{provider}/callback', [OAuthController::class, 'callback'])
    ->whereIn('provider', ['google','facebook','apple'])
    ->name('oauth.callback');

// ----- Zone protégée -----
Route::middleware(['auth', 'set.family'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // … ici tu ajoutes tes autres routes (familles, membres, dépenses, etc.)
    // Route::resource('expenses', ExpenseController::class);
    // Route::resource('claims', ClaimController::class)->only(['index','show']);
});

