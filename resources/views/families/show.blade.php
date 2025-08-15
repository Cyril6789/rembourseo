@extends('layouts.app')

@section('content')
@if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1>{{ $family->name }}</h1>
  <div>
    <a class="btn btn-outline-secondary" href="{{ route('families.share.show', $family) }}">Partager</a>
    <a class="btn btn-outline-primary" href="{{ route('families.edit', $family) }}">Modifier</a>
  </div>
</div>

<h3 class="mt-4">Membres</h3>
<table class="table">
  <thead><tr><th>Nom</th><th>Rôle</th><th>Email</th><th>Naissance</th><th class="text-end">Actions</th></tr></thead>
  <tbody>
    @forelse($family->members as $m)
    <tr>
      <td>{{ $m->full_name }}</td>
      <td>{{ $m->role === 'parent' ? 'Parent' : 'Enfant' }}</td>
      <td>{{ $m->email }}</td>
      <td>{{ optional($m->birthdate)->format('d/m/Y') }}</td>
      <td class="text-end">
        <a href="{{ route('members.edit', $m) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
        <form action="{{ route('members.destroy', $m) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
          @csrf @method('DELETE')
          <button class="btn btn-sm btn-outline-danger">Supprimer</button>
        </form>
      </td>
    </tr>
    @empty
    <tr><td colspan="5" class="text-center text-muted">Aucun membre</td></tr>
    @endforelse
  </tbody>
</table>

<h4 class="mt-4">Ajouter un membre</h4>
<form method="POST" action="{{ route('families.members.store', $family) }}" class="row g-3">
  @csrf
  <div class="col-md-3"><input name="first_name" class="form-control" placeholder="Prénom" value="{{ old('first_name') }}" required></div>
  <div class="col-md-3"><input name="last_name" class="form-control" placeholder="Nom" value="{{ old('last_name') }}" required></div>
  <div class="col-md-2"><input name="birthdate" type="date" class="form-control" value="{{ old('birthdate') }}"></div>
  <div class="col-md-1">
    <select name="role" class="form-select" required>
      <option value="parent" @selected(old('role')==='parent')>Parent</option>
      <option value="child" @selected(old('role')==='child')>Enfant</option>
    </select>
  </div>
  <div class="col-12">
    <button class="btn btn-primary">Ajouter</button>
  </div>
</form>
@endsection
