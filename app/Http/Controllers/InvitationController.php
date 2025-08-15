<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InvitationController extends Controller
{
    public function index(Family $family)
    {
        $this->authorizeFamily($family, 'owner');
        $invitations = $family->invitations()->latest()->get();
        return view('invitations.index', compact('family','invitations'));
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeFamily($family, 'owner');

        $data = $request->validate([
            'email' => ['nullable','email'],
            'expires_at' => ['nullable','date','after:now'],
        ]);

        $inv = Invitation::create([
            'family_id'  => $family->id,
            'inviter_id' => auth()->id(),
            'email'      => $data['email'] ?? null,
            'token'      => Str::random(40),
            'expires_at' => $data['expires_at'] ?? now()->addDays(7),
        ]);

        return back()->with('success','Invitation créée.')->with('invitation_id', $inv->id);
    }

    public function destroy(Family $family, Invitation $invitation)
    {
        $this->authorizeFamily($family, 'owner');
        abort_unless($invitation->family_id === $family->id, 404);
        $invitation->delete();
        return back()->with('success','Invitation supprimée.');
    }

    public function show(string $token)
    {
        $inv = Invitation::where('token', $token)->firstOrFail();

        if ($inv->expires_at && now()->gt($inv->expires_at)) {
            abort(410, 'Invitation expirée.');
        }

        return view('invitations.accept', ['invitation' => $inv]);
    }

    public function accept(string $token)
    {
        $inv = Invitation::where('token', $token)->firstOrFail();

        if ($inv->expires_at && now()->gt($inv->expires_at)) {
            abort(410, 'Invitation expirée.');
        }

        // attacher l’utilisateur connecté
        $inv->family->users()->syncWithoutDetaching([auth()->id() => ['role' => 'member']]);
        $inv->accepted_at = now();
        $inv->save();

        session(['current_family_id' => $inv->family_id]);

        return redirect()->route('families.show', $inv->family)->with('success','Invitation acceptée.');
    }

    public function qr(Family $family, Invitation $invitation)
    {
        $this->authorizeFamily($family, 'owner');
        abort_unless($invitation->family_id === $family->id, 404);

        $url = route('invitations.show', $invitation->token);
        $png = QrCode::format('png')->size(220)->margin(1)->generate($url);

        return response($png)->header('Content-Type','image/png');
    }

    private function authorizeFamily(Family $family, string $minRole = 'member'): void
    {
        $role = optional($family->users()->where('user_id', auth()->id())->first())?->pivot?->role;
        abort_unless($role, 403);
        if ($minRole === 'owner') abort_unless($role === 'owner', 403);
    }
}
