<?php

namespace App\Livewire;

use App\Models\Family;
use App\Models\Member;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]

class FamilyManager extends Component
{
    use WithPagination;

    // Liste familles
    public string $search = '';
    public bool $showTrashed = false;

    // Sélection + détail
    public ?int $selectedId = null;
    public ?string $editableName = null;

    // Modale "nouvelle famille"
    public bool $showCreateModal = false;
    public string $newFamilyName = '';

    // Membres (formulaire rapide dans le panneau)
    public array $memberForm = [
        'first_name' => '',
        'last_name'  => '',
        'role'       => 'parent', // parent | enfant | aidant
        'birthdate'  => null,
    ];

    // Ligne membre en cours d’édition (inline)
    public ?int $editingMemberId = null;
    public array $editingMember = [
        'first_name' => '',
        'last_name'  => '',
        'role'       => 'parent',
        'birthdate'  => null,
    ];

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    /* -------- LISTE -------- */

    public function getFamiliesProperty()
    {
        $q = Family::query()
            ->when($this->showTrashed, fn($qq) => $qq->withTrashed())
            ->when($this->search !== '', function ($qq) {
                $s = '%'.$this->search.'%';
                $qq->where('name', 'like', $s);
            })
            ->withCount('members')
            ->orderBy('name');

        return $q->paginate(12);
    }

    public function select(int $id): void
    {
        $fam = Family::withTrashed()->findOrFail($id);
        $this->selectedId   = $fam->id;
        $this->editableName = $fam->name;
        $this->resetMemberForms();
    }

    public function openCreate(): void
    {
        $this->newFamilyName = '';
        $this->showCreateModal = true;
    }

    public function create(): void
    {
        $data = $this->validate([
            'newFamilyName' => ['required','string','max:255'],
        ]);

        $f = Family::create(['name' => $data['newFamilyName']]);
        $this->showCreateModal = false;
        $this->select($f->id);
        session()->flash('ok', 'Famille créée ✅');
    }

    public function saveName(): void
    {
        if (!$this->selectedId) return;

        $data = $this->validate([
            'editableName' => ['required','string','max:255'],
        ]);

        $f = Family::withTrashed()->findOrFail($this->selectedId);
        $f->update(['name' => $data['editableName']]);
        session()->flash('ok', 'Nom mis à jour ✏️');
    }

    public function trash(int $id): void
    {
        $f = Family::findOrFail($id);
        $f->delete();
        if ($this->selectedId === $id) {
            $this->selectedId = null;
        }
        session()->flash('ok', 'Famille envoyée à la corbeille 🗑️');
    }

    public function restore(int $id): void
    {
        $f = Family::withTrashed()->findOrFail($id);
        $f->restore();
        session()->flash('ok', 'Famille restaurée ♻️');
    }

    public function forceDelete(int $id): void
    {
        $f = Family::withTrashed()->findOrFail($id);
        $f->forceDelete();
        if ($this->selectedId === $id) $this->selectedId = null;
        session()->flash('ok', 'Famille supprimée définitivement ❌');
    }

    /* -------- MEMBRES (panneau) -------- */

    public function getSelectedFamilyProperty(): ?Family
    {
        return $this->selectedId ? Family::withTrashed()->find($this->selectedId) : null;
    }

    public function getMembersProperty()
    {
        return $this->selectedFamily
            ? $this->selectedFamily->members()->withTrashed()->orderByRaw("
                CASE role WHEN 'parent' THEN 0 WHEN 'aidant' THEN 1 ELSE 2 END
            ")->orderBy('last_name')->orderBy('first_name')->get()
            : collect();
    }

    public function addMember(): void
    {
        if (!$this->selectedId) return;

        $data = $this->validate([
            'memberForm.first_name' => ['required','string','max:100'],
            'memberForm.last_name'  => ['required','string','max:100'],
            'memberForm.role'       => ['required', Rule::in(['parent','enfant','aidant'])],
            'memberForm.birthdate'  => ['nullable','date'],
        ])['memberForm'];

        Member::create($data + ['family_id' => $this->selectedId]);

        $this->resetMemberForms();
        session()->flash('ok', 'Membre ajouté ➕');
    }

    public function editMember(int $id): void
    {
        $m = Member::withTrashed()->findOrFail($id);
        $this->editingMemberId = $m->id;
        $this->editingMember = [
            'first_name' => $m->first_name,
            'last_name'  => $m->last_name,
            'role'       => $m->role,
            'birthdate'  => optional($m->birthdate)?->format('Y-m-d'),
        ];
    }

    public function saveMember(): void
    {
        if (!$this->editingMemberId) return;

        $data = $this->validate([
            'editingMember.first_name' => ['required','string','max:100'],
            'editingMember.last_name'  => ['required','string','max:100'],
            'editingMember.role'       => ['required', Rule::in(['parent','enfant','aidant'])],
            'editingMember.birthdate'  => ['nullable','date'],
        ])['editingMember'];

        Member::withTrashed()->findOrFail($this->editingMemberId)->update($data);
        $this->editingMemberId = null;
        session()->flash('ok', 'Membre modifié ✏️');
    }

    public function cancelEditMember(): void
    {
        $this->editingMemberId = null;
    }

    public function trashMember(int $id): void
    {
        Member::findOrFail($id)->delete();
        session()->flash('ok', 'Membre à la corbeille 🗑️');
    }

    public function restoreMember(int $id): void
    {
        Member::withTrashed()->findOrFail($id)->restore();
        session()->flash('ok', 'Membre restauré ♻️');
    }

    public function forceDeleteMember(int $id): void
    {
        Member::withTrashed()->findOrFail($id)->forceDelete();
        session()->flash('ok', 'Membre supprimé ❌');
    }

    protected function resetMemberForms(): void
    {
        $this->memberForm = [
            'first_name' => '',
            'last_name'  => '',
            'role'       => 'parent',
            'birthdate'  => null,
        ];
        $this->editingMemberId = null;
        $this->editingMember = [
            'first_name' => '',
            'last_name'  => '',
            'role'       => 'parent',
            'birthdate'  => null,
        ];
    }

    public function render()
    {
        return view('livewire.family-manager', [
            'families' => $this->families,
            'selectedFamily' => $this->selectedFamily,
            'members' => $this->members,
        ]);
    }
}
