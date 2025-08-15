<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Insurer;
use Illuminate\Http\Request;

class InsurerController extends Controller
{
    public function index(Family $family)
    {
        $this->authFamily($family);
        $insurers = $family->insurers()->orderBy('name')->get();
        return view('insurers.index', compact('family','insurers'));
    }

    public function create(Family $family)
    {
        $this->authFamily($family);
        return view('insurers.create', compact('family'));
    }

    public function store(Request $request, Family $family)
    {
        $this->authFamily($family);
        $data = $request->validate([
            'name' => ['required','string','max:160'],
            'contact_info' => ['nullable','array'],
        ]);
        $family->insurers()->create($data);
        return redirect()->route('families.insurers.index', $family)->with('success','Mutuelle créée.');
    }

    public function edit(Insurer $insurer)
    {
        $this->authFamily($insurer->family);
        return view('insurers.edit', ['family'=>$insurer->family,'insurer'=>$insurer]);
    }

    public function update(Request $request, Insurer $insurer)
    {
        $this->authFamily($insurer->family);
        $data = $request->validate([
            'name' => ['required','string','max:160'],
            'contact_info' => ['nullable','array'],
        ]);
        $insurer->update($data);
        return back()->with('success','Mutuelle mise à jour.');
    }

    public function destroy(Insurer $insurer)
    {
        $this->authFamily($insurer->family, 'owner');
        $insurer->delete();
        return back()->with('success','Mutuelle supprimée.');
    }

    private function authFamily(Family $family, string $minRole = 'member'): void
    {
        $role = optional($family->users()->where('user_id', auth()->id())->first())?->pivot?->role;
        abort_unless($role, 403);
        if ($minRole === 'owner') abort_unless($role === 'owner', 403);
    }
}
