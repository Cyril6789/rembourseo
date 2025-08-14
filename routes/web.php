<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\DashboardController;

// Page d'accueil -> dashboard (protégé)
Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// Déconnexion
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')->name('logout');

// Zone protégée
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Familles
    Route::resource('families', FamilyController::class);
    Route::post('families/{family}/switch', [FamilyController::class, 'switch'])->name('families.switch');

    // Invitations (scopées à la famille)
    Route::get('families/{family}/invitations', [InvitationController::class, 'index'])->name('invitations.index');
    Route::post('families/{family}/invitations', [InvitationController::class, 'store'])->name('invitations.store');
    Route::delete('families/{family}/invitations/{invitation}', [InvitationController::class, 'destroy'])->name('invitations.destroy');
    Route::get('invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');        // page d’acceptation
    Route::post('invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');

    // QR (affichage image inline)
    Route::get('families/{family}/invitations/{invitation}/qr', [InvitationController::class, 'qr'])->name('invitations.qr');

    // Membres (parents/enfants)
    Route::resource('families.members', MemberController::class)->shallow();

    // Mutuelles
    Route::resource('families.insurers', InsurerController::class)->shallow();

    // Moyens de paiement
    Route::resource('families.payment-methods', PaymentMethodController::class)->parameters([
        'payment-methods' => 'paymentMethod'
    ])->shallow();


});
