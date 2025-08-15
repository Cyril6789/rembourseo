@extends('layouts.app')
@section('title', 'Modifier une famille')
@section('content')
<h1>Modifier la famille</h1>
<form method="POST" action="{{ route('families.update', $family) }}">
  @csrf @method('PUT')
  <div class="mb-3">
    <label class="form-label">Nom</label>
    <input name="name" class="form-control" value="{{ old('name',$family->name) }}" required>
    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>
  <button class="btn btn-primary">Enregistrer</button>
</form>
@endsection
