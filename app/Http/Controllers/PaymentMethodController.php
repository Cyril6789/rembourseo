<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(Family $family)
    {
        $this->authFamily($family);
        $methods = $family->paymentMethods()->orderBy('label')->get();
        return view('payments.index', compact('family','methods'));
    }

    public function create(Family $family)
    {
        $this->authFamily($family);
        return view('payments.create', compact('family'));
    }

    public function store(Request $request, Family $family)
    {
        $this->authFamily($family);
        $data = $request->validate([
            'type' => ['required','in:carte,cheque,virement,especes'],
            'label' => ['required','string','max:120'],
            'owner_user_id' => ['nullable','exists:users,id'],
            'last4' => ['nullable','string','max:4'],
            'iban_mask' => ['nullable','string','max:34'],
            'is_active' => ['boolean'],
        ]);
        $family->paymentMethods()->create($data);
        return redirect()->route('families.payment-methods.index', $family)->with('success','Moyen de paiement créé.');
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        $this->authFamily($paymentMethod->family);
        $family = $paymentMethod->family;
        return view('payments.edit', compact('family','paymentMethod'));
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $this->authFamily($paymentMethod->family);
        $data = $request->validate([
            'type' => ['required','in:carte,cheque,virement,especes'],
            'label' => ['required','string','max:120'],
            'owner_user_id' => ['nullable','exists:users,id'],
            'last4' => ['nullable','string','max:4'],
            'iban_mask' => ['nullable','string','max:34'],
            'is_active' => ['boolean'],
        ]);
        $paymentMethod->update($data);
        return back()->with('success','Moyen de paiement mis à jour.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $this->authFamily($paymentMethod->family, 'owner');
        $paymentMethod->delete();
        return back()->with('success','Moyen de paiement supprimé.');
    }

    private function authFamily(Family $family, string $minRole = 'member'): void
    {
        $role = optional($family->users()->where('user_id', auth()->id())->first())?->pivot?->role;
        abort_unless($role, 403);
        if ($minRole === 'owner') abort_unless($role === 'owner', 403);
    }
}
