@extends('layouts.app')
@section('title', 'Créer une nouvelle famille')
@section('content')
<h1>Nouvelle famille</h1>
<form method="POST" action="{{ route('families.store') }}">
  @csrf
  <div class="mb-3">
    <label class="form-label">Nom</label>
    <input name="name" class="form-control" value="{{ old('name') }}" required>
    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>
  <button class="btn btn-primary">Créer</button>
</form>
@endsection
