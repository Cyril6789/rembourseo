<?php

namespace App\Http\Controllers;

use App\Models\Facade\Str;
use App\Models\Family;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    public function index()
    {
        $families = auth()->user()->families()->latest()->get();
        return view('families.index', compact('families'));
    }

    public function create()
    {
        return view('families.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required','string','max:120']]);

        $family = Family::create($data);
        // attacher le créateur comme owner
        $family->users()->attach(auth()->id(), ['role' => 'owner']);

        // famille active (simple partage via session)
        session(['current_family_id' => $family->id]);

        return redirect()->route('families.show', $family)->with('success', 'Famille créée.');
    }

    public function show(Family $family)
    {
        $this->authorizeFamily($family);
        return view('families.show', compact('family'));
    }

    public function edit(Family $family)
    {
        $this->authorizeFamily($family, 'owner');
        return view('families.edit', compact('family'));
    }

    public function update(Request $request, Family $family)
    {
        $this->authorizeFamily($family, 'owner');
        $data = $request->validate(['name' => ['required','string','max:120']]);
        $family->update($data);
        return back()->with('success','Famille mise à jour.');
    }

    public function destroy(Family $family)
    {
        $this->authorizeFamily($family, 'owner');
        $family->delete();
        return redirect()->route('families.index')->with('success','Famille supprimée.');
    }

    public function switch(Family $family)
    {
        $this->authorizeFamily($family);
        session(['current_family_id' => $family->id]);
        return redirect()->route('families.show', $family)->with('success','Famille active changée.');
    }

    private function authorizeFamily(Family $family, string $minRole = 'member'): void
    {
        $role = optional(
            $family->users()->where('user_id', auth()->id())->first()
        )?->pivot?->role;

        abort_unless($role, 403);
        if ($minRole === 'owner') {
            abort_unless($role === 'owner', 403);
        }
    }
}
