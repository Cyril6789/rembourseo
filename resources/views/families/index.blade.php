@extends('layouts.app')

@section('title', 'Mes familles')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1>Familles</h1>
  <a class="btn btn-primary" href="{{ route('families.create') }}">+ Nouvelle famille</a>
</div>

@if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif

<table class="table">
  <thead><tr><th>Nom</th><th>Membres</th><th></th></tr></thead>
  <tbody>
    @foreach($families as $family)
      <tr>
        <td>{{ $family->name }}</td>
        <td>{{ $family->members_count }}</td>
        <td class="text-end">
          <a href="{{ route('families.show', $family) }}" class="btn btn-sm btn-outline-secondary">Ouvrir</a>
          <a href="{{ route('families.edit', $family) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
          <form action="{{ route('families.destroy', $family) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
          </form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

{{ $families->links() }}
@endsection
