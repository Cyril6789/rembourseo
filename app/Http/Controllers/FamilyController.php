<?php

namespace App\Http\Controllers;

use App\Models\Family;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    public function index()
    {
        $families = Family::withCount('members')->orderBy('name')->paginate(15);
        return view('families.index', compact('families'));
    }

    public function create()
    {
        return view('families.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $family = Family::create($data);

   

        return redirect()->route('families.show', $family)->with('status', 'Famille créée.');
    }

    public function show(Family $family)
    {
        $family->load(['members' => function ($q) { $q->orderBy('role')->orderBy('last_name'); }]);
        return view('families.show', compact('family'));
    }

    public function edit(Family $family)
    {
        return view('families.edit', compact('family'));
    }

    public function update(Request $request, Family $family)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $family->update($data);

        return redirect()->route('families.show', $family)->with('status', 'Famille mise à jour.');
    }

    public function destroy(Family $family)
    {
        $family->delete();
        return redirect()->route('families.index')->with('status', 'Famille supprimée.');
    }
}
