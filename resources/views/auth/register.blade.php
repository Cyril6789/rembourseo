@extends('layouts.app')

@section('title','Créer un compte')

@section('content')
<div style="max-width:520px;margin:0 auto;">
  <h2 style="font-size:22px;font-weight:700;margin-bottom:12px;">Créer un compte</h2>

  @if ($errors->any())
    <div class="alert danger" style="margin-bottom:14px;">
      <strong>Oups :</strong>
      <ul style="margin:8px 0 0 18px;">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('register') }}" class="card body" style="display:grid;gap:10px;">
    @csrf

    {{-- HONEYPOT (doit rester vide) --}}
    <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;height:0;width:0;">

    <label>
      <span style="display:block;margin-bottom:6px;color:var(--muted);">Nom complet</span>
      <input type="text" name="name" value="{{ old('name') }}" required
             style="width:100%;background:var(--bg-elev);color:var(--text);border:1px solid var(--border);border-radius:10px;padding:10px;">
    </label>

    <label>
      <span style="display:block;margin-bottom:6px;color:var(--muted);">Email</span>
      <input type="email" name="email" value="{{ old('email') }}" required
             style="width:100%;background:var(--bg-elev);color:var(--text);border:1px solid var(--border);border-radius:10px;padding:10px;">
    </label>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
      <label>
        <span style="display:block;margin-bottom:6px;color:var(--muted);">Mot de passe</span>
        <input type="password" name="password" required
               style="width:100%;background:var(--bg-elev);color:var(--text);border:1px solid var(--border);border-radius:10px;padding:10px;">
      </label>
      <label>
        <span style="display:block;margin-bottom:6px;color:var(--muted);">Confirmation</span>
        <input type="password" name="password_confirmation" required
               style="width:100%;background:var(--bg-elev);color:var(--text);border:1px solid var(--border);border-radius:10px;padding:10px;">
      </label>
    </div>

    <label>
      <span style="display:block;margin-bottom:6px;color:var(--muted);">
        Captcha : combien font {{ session('captcha_a') }} + {{ session('captcha_b') }} ?
      </span>
      <input type="number" name="captcha" inputmode="numeric" required
             style="width:100%;background:var(--bg-elev);color:var(--text);border:1px solid var(--border);border-radius:10px;padding:10px;">
    </label>

    <button type="submit"
      style="padding:12px;border-radius:10px;border:1px solid rgba(110,168,254,.25);background:linear-gradient(135deg, rgba(110,168,254,.15), rgba(62,123,251,.05));color:var(--text);font-weight:600;">
      Créer mon compte
    </button>

    <div style="text-align:center;color:var(--muted);margin-top:6px;">
      Déjà un compte ? <a href="{{ route('login') }}" style="text-decoration:underline;">Se connecter</a>
    </div>
  </form>
</div>
@endsection
