<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Mail\InviteToFamily;

class FamilyShareController extends Controller
{
    public function show(Family $family)
    {
        // Page avec formulaire d'invitation + QR code
        $inviteUrl = $this->ensureActiveInviteAndGetUrl($family);
        return view('families.share', compact('family', 'inviteUrl'));
    }

    public function inviteByEmail(Request $request, Family $family)
    {
        $data = $request->validate([
            'email' => ['required','email'],
        ]);

        $inviteUrl = $this->ensureActiveInviteAndGetUrl($family);

        Mail::to($data['email'])->send(new InviteToFamily($family, $inviteUrl));

        return back()->with('status', "Invitation envoyée à {$data['email']}.");
    }

    public function qrcode(Family $family)
    {
        $inviteUrl = $this->ensureActiveInviteAndGetUrl($family);
        $svg = QrCode::format('svg')->size(256)->margin(1)->generate($inviteUrl);

        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }

    public function accept(string $token, Request $request)
    {
        $invitation = Invitation::where('token', $token)->whereNull('accepted_at')->firstOrFail();

        // Ici tu peux rattacher l'utilisateur connecté à la famille
        $request->user()->families()->syncWithoutDetaching([
            $invitation->family_id => ['role' => 'member']
        ]);

        $invitation->forceFill(['accepted_at' => now()])->save();

        return redirect()->route('families.show', $invitation->family_id)->with('status', 'Invitation acceptée.');
    }

    private function ensureActiveInviteAndGetUrl(Family $family): string
    {
        // Réutilise une invitation active, sinon en crée une
        $invitation = $family->invitations()->whereNull('accepted_at')->latest()->first();

        if (! $invitation) {
            $invitation = $family->invitations()->create([
                'email' => null,
                'token' => Str::uuid()->toString(),
                'expires_at' => now()->addDays(7),
            ]);
        } elseif ($invitation->expires_at && $invitation->expires_at->isPast()) {
            $invitation->update([
                'token' => Str::uuid()->toString(),
                'expires_at' => now()->addDays(7),
            ]);
        }

        return route('families.share.accept', $invitation->token);
    }
}
