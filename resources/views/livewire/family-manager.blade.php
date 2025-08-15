<div class="mx-auto max-w-7xl px-6 py-8">
    {{-- Toast --}}
    @if (session('ok'))
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,2500)"
             class="mb-4 rounded-md border border-emerald-300 bg-emerald-50 px-4 py-3 text-emerald-900">
            {{ session('ok') }}
        </div>
    @endif

    {{-- En‑tête + actions --}}
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-100 md:text-2xl">Familles</h1>
        <div class="flex items-center gap-2">
            <div class="hidden sm:block">
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Rechercher…"
                       class="w-64 rounded-md border border-gray-700/60 bg-gray-900/40 px-3 py-2 text-sm text-gray-200 placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500" />
            </div>
            <button type="button" wire:click="openCreate"
                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                ➕ Nouvelle famille
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- LISTE --}}
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-lg border border-gray-700/50 bg-gray-900/40 shadow-soft">
                <div class="flex items-center justify-between border-b border-gray-700/50 px-4 py-3">
                    <div class="sm:hidden">
                        <input type="text" wire:model.live.debounce.300ms="search"
                               placeholder="Rechercher…"
                               class="w-full rounded-md border border-gray-700/60 bg-gray-900/40 px-3 py-2 text-sm text-gray-200 placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-300">
                        <input type="checkbox" wire:model.live="showTrashed"
                               class="rounded border-gray-600 text-indigo-500 focus:ring-indigo-500">
                        Corbeille
                    </label>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700/60">
                        <thead class="bg-gray-900/60">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Famille</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Membres</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                        @forelse($families as $f)
                            <tr class="{{ $selectedId===$f->id ? 'bg-indigo-500/10' : '' }}">
                                <td class="px-4 py-3">
                                    <button wire:click="select({{ $f->id }})"
                                            class="text-sm font-medium text-gray-100 hover:underline">
                                        {{ $f->name }}
                                    </button>
                                    @if($f->trashed())
                                        <span class="ml-2 rounded bg-rose-500/20 px-2 py-0.5 text-xs text-rose-300">🗑️ supprimée</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded bg-gray-700/50 px-2 py-1 text-xs text-gray-300">
                                        👥 {{ $f->members_count }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="select({{ $f->id }})"
                                            class="rounded border border-gray-700 bg-gray-900 px-3 py-1.5 text-xs text-gray-200 hover:bg-gray-800">👁️ Ouvrir</button>
                                        @if(!$f->trashed())
                                            <button wire:click="trash({{ $f->id }})"
                                                class="rounded border border-rose-600/50 bg-gray-900 px-3 py-1.5 text-xs text-rose-300 hover:bg-rose-600/20">🗑️ Corbeille</button>
                                        @else
                                            <button wire:click="restore({{ $f->id }})"
                                                class="rounded border border-emerald-600/50 bg-gray-900 px-3 py-1.5 text-xs text-emerald-300 hover:bg-emerald-600/20">♻️ Restaurer</button>
                                            <button x-data @click="if(confirm('Supprimer définitivement ?')) $wire.forceDelete({{ $f->id }})"
                                                class="rounded bg-rose-600 px-3 py-1.5 text-xs text-white hover:bg-rose-500">❌ Supprimer</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-10 text-center text-gray-400">
                                    Aucun résultat. Clique sur <span class="font-medium">“➕ Nouvelle famille”</span> pour commencer.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-800 px-4 py-3">
                    {{ $families->links() }}
                </div>
            </div>
        </div>

        {{-- PANNEAU DÉTAIL --}}
        <div class="lg:col-span-1">
            <div class="rounded-lg border border-gray-700/50 bg-gray-900/40 p-4 shadow-soft">
                @if($selectedFamily)
                    <div class="mb-4">
                        <h2 class="mb-2 text-sm font-semibold text-gray-300">📄 Détails</h2>
                        <label class="mb-1 block text-xs text-gray-400">Nom</label>
                        <div class="flex items-center gap-2">
                            <input type="text" wire:model.defer="editableName"
                                   class="w-full rounded-md border border-gray-700/60 bg-gray-900/60 px-3 py-2 text-sm text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                            <button wire:click="saveName"
                                    class="rounded bg-indigo-600 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-500">💾</button>
                        </div>
                    </div>

                    <div>
                        <h3 class="mb-2 text-sm font-semibold text-gray-300">👥 Membres</h3>

                        {{-- Ajout rapide --}}
                        <div class="mb-3 grid grid-cols-2 gap-2">
                            <input type="text" wire:model.defer="memberForm.last_name"  placeholder="Nom"
                                   class="rounded-md border border-gray-700/60 bg-gray-900/60 px-3 py-2 text-sm text-gray-200 focus:border-teal-500 focus:ring-teal-500">
                            <input type="text" wire:model.defer="memberForm.first_name" placeholder="Prénom"
                                   class="rounded-md border border-gray-700/60 bg-gray-900/60 px-3 py-2 text-sm text-gray-200 focus:border-teal-500 focus:ring-teal-500">
                            <select wire:model.defer="memberForm.role"
                                    class="rounded-md border border-gray-700/60 bg-gray-900/60 px-3 py-2 text-sm text-gray-200 focus:border-teal-500 focus:ring-teal-500">
                                <option value="parent">👨‍👩‍👧 Parent</option>
                                <option value="enfant">🧒 Enfant</option>
                                <option value="aidant">🧑‍🤝‍🧑 Aidant</option>
                            </select>
                            <input type="date" wire:model.defer="memberForm.birthdate"
                                   class="rounded-md border border-gray-700/60 bg-gray-900/60 px-3 py-2 text-sm text-gray-200 focus:border-teal-500 focus:ring-teal-500">
                        </div>
                        <div class="mb-4 flex justify-end">
                            <button wire:click="addMember"
                                    class="rounded bg-teal-600 px-3 py-2 text-xs font-medium text-white hover:bg-teal-500">➕ Ajouter</button>
                        </div>

                        {{-- Liste membres --}}
                        <div class="overflow-hidden rounded-md border border-gray-700/50">
                            <table class="min-w-full divide-y divide-gray-800">
                                <thead class="bg-gray-900/60">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Nom</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Rôle</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800">
                                @foreach($members as $m)
                                    <tr>
                                        <td class="px-3 py-2 text-sm text-gray-100">
                                            @if($editingMemberId === $m->id)
                                                <div class="grid grid-cols-2 gap-2">
                                                    <input type="text" wire:model.defer="editingMember.last_name"  class="rounded border border-gray-700/60 bg-gray-900/60 px-2 py-1.5 text-sm text-gray-200">
                                                    <input type="text" wire:model.defer="editingMember.first_name" class="rounded border border-gray-700/60 bg-gray-900/60 px-2 py-1.5 text-sm text-gray-200">
                                                    <select wire:model.defer="editingMember.role" class="rounded border border-gray-700/60 bg-gray-900/60 px-2 py-1.5 text-sm text-gray-200">
                                                        <option value="parent">👨‍👩‍👧 Parent</option>
                                                        <option value="enfant">🧒 Enfant</option>
                                                        <option value="aidant">🧑‍🤝‍🧑 Aidant</option>
                                                    </select>
                                                    <input type="date" wire:model.defer="editingMember.birthdate" class="rounded border border-gray-700/60 bg-gray-900/60 px-2 py-1.5 text-sm text-gray-200">
                                                </div>
                                            @else
                                                <div class="font-medium">{{ $m->last_name }} {{ $m->first_name }}</div>
                                                <div class="text-xs text-gray-400">
                                                    {{ optional($m->birthdate)->format('d/m/Y') ?: '—' }}
                                                    @if($m->trashed()) <span class="ml-1 text-rose-300">🗑️</span> @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-sm">
                                            @php $roleEmoji = ['parent'=>'👨‍👩‍👧','enfant'=>'🧒','aidant'=>'🧑‍🤝‍🧑'][$m->role] ?? '👤'; @endphp
                                            <span class="rounded bg-gray-700/40 px-2 py-0.5 text-xs text-gray-200">
                                                {{ $roleEmoji }} {{ ucfirst($m->role) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex justify-end gap-2">
                                                @if($editingMemberId === $m->id)
                                                    <button wire:click="saveMember" class="rounded bg-indigo-600 px-3 py-1.5 text-xs text-white hover:bg-indigo-500">💾</button>
                                                    <button wire:click="cancelEditMember" class="rounded border border-gray-700 px-3 py-1.5 text-xs text-gray-200">↩️</button>
                                                @else
                                                    <button wire:click="editMember({{ $m->id }})" class="rounded border border-gray-700 px-3 py-1.5 text-xs text-gray-200 hover:bg-gray-800">✏️</button>
                                                    @if(!$m->trashed())
                                                        <button wire:click="trashMember({{ $m->id }})" class="rounded border border-rose-600/50 px-3 py-1.5 text-xs text-rose-300 hover:bg-rose-600/20">🗑️</button>
                                                    @else
                                                        <button wire:click="restoreMember({{ $m->id }})" class="rounded border border-emerald-600/50 px-3 py-1.5 text-xs text-emerald-300 hover:bg-emerald-600/20">♻️</button>
                                                        <button x-data @click="if(confirm('Supprimer définitivement ?')) $wire.forceDeleteMember({{ $m->id }})"
                                                                class="rounded bg-rose-600 px-3 py-1.5 text-xs text-white hover:bg-rose-500">❌</button>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="text-sm text-gray-400">👉 Sélectionne une famille dans la liste pour afficher le détail et gérer ses membres.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- MODALE CRÉATION --}}
    <div
        x-data="{ open: @entangle('showCreateModal') }"
        x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="open=false"></div>
        <div class="relative w-full max-w-md rounded-lg border border-gray-700 bg-gray-900 p-6 shadow-xl">
            <h3 class="mb-4 text-base font-semibold text-gray-100">➕ Nouvelle famille</h3>
            <label class="mb-1 block text-xs text-gray-400">Nom</label>
            <input type="text" wire:model.defer="newFamilyName"
                   class="mb-4 w-full rounded-md border border-gray-700/60 bg-gray-900/60 px-3 py-2 text-sm text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
            @error('newFamilyName') <div class="mb-2 text-sm text-rose-300">{{ $message }}</div> @enderror

            <div class="flex justify-end gap-2">
                <button @click="open=false"
                    class="rounded border border-gray-700 px-3 py-2 text-sm text-gray-200">Annuler</button>
                <button wire:click="create"
                    class="rounded bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">Créer</button>
            </div>
        </div>
    </div>
</div>
