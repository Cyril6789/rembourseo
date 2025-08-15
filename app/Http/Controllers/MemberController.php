<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function store(Request $request, Family $family)
    {
        $data = $request->validate([
            'first_name' => ['required','string','max:100'],
            'last_name'  => ['required','string','max:100'],
            'email'      => ['nullable','email','max:255'],
            'birthdate'  => ['nullable','date'],
            'role'       => ['required','in:parent,child'],
        ]);

        $family->members()->create($data);

        return back()->with('status', 'Membre ajouté.');
    }

    public function edit(Member $member)
    {
        $family = $member->family;
        return view('members.edit', compact('member', 'family'));
    }

    public function update(Request $request, Member $member)
    {
        $data = $request->validate([
            'first_name' => ['required','string','max:100'],
            'last_name'  => ['required','string','max:100'],
            'birthdate'  => ['nullable','date'],
            'role'       => ['required','in:parent,child'],
        ]);

        $member->update($data);

        return redirect()->route('families.show', $member->family)->with('status', 'Membre modifié.');
    }

    public function destroy(Member $member)
    {
        $family = $member->family;
        $member->delete();

        return redirect()->route('families.show', $family)->with('status', 'Membre supprimé.');
    }
}
