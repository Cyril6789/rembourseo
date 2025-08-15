<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Rembourséo'))</title>

    <style>
        :root{
            --bg:#0f1216; --bg-elev:#151a21; --text:#e6e9ef; --muted:#9aa3af;
            --border:#202733; --link:#6ea8fe; --danger:#ff6b6b;
            --radius:12px; --shadow:0 10px 30px rgba(0,0,0,.35);
        }
        *{box-sizing:border-box}
        html,body{margin:0;min-height:100%;background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Arial}
        a{color:var(--link);text-decoration:none}
        a:hover{text-decoration:underline}

        /* HEADER */
        .app-header{position:sticky;top:0;z-index:40;display:flex;align-items:center;gap:.75rem;justify-content:space-between;padding:.75rem 1rem;background:rgba(21,26,33,.9);backdrop-filter:blur(8px);border-bottom:1px solid var(--border)}
        .brand{display:flex;align-items:center;gap:.6rem}
        .brand .logo{width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#6ea8fe,#3e7bfb)}
        .brand .name{font-weight:700}
        .burger{appearance:none;border:1px solid var(--border);background:var(--bg-elev);color:var(--text);padding:.55rem .7rem;border-radius:10px;cursor:pointer}
        .burger:focus{outline:2px solid #3e7bfb;outline-offset:2px}

        /* LAYOUT GRID (sidebar + contenu) */
        .layout{display:block}
        @media (min-width:900px){
            .layout{
                display:grid;
                grid-template-columns:260px 1fr;   /* 1: drawer | 2: content */
                min-height:calc(100vh - 56px);      /* ~ hauteur header */
            }
        }

        /* DRAWER (menu) */
        .drawer-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.45);opacity:0;pointer-events:none;transition:opacity .2s ease}
        .drawer{
            position:fixed;inset:56px 30% 0 0;      /* mobile: overlay (sous le header) */
            max-width:320px;background:var(--bg-elev);
            border-right:1px solid var(--border);
            transform:translateX(-100%);transition:transform .2s ease;
            box-shadow:var(--shadow);padding:1rem;display:flex;flex-direction:column;gap:.5rem;z-index:30;
        }
        .nav-group{margin-top:.5rem}
        .nav-title{color:var(--muted);font-size:.75rem;text-transform:uppercase;margin:.5rem 0}
        .nav-item{display:flex;align-items:center;gap:.6rem;padding:.6rem .7rem;border-radius:10px;color:var(--text);border:1px solid transparent}
        .nav-item:hover{background:#1b2129}
        .nav-item.active{background:linear-gradient(135deg,rgba(110,168,254,.12),rgba(62,123,251,.05));border-color:rgba(110,168,254,.25)}
        .drawer-footer{margin-top:auto;color:var(--muted);font-size:.8rem}

        /* Desktop: le drawer devient la 1ʳᵉ colonne, sticky */
        @media (min-width:900px){
            .drawer-backdrop{display:none}
            .drawer{
                position:sticky; inset:auto; top:0; transform:none; z-index:auto;
                height:calc(100vh - 56px); /* sous le header */
            }
        }

        /* CONTENT */
        .container{padding:1rem}
        .content{max-width:1100px;margin:0 auto}
        .card{background:var(--bg-elev);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);padding:1rem}
        .alert{padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem}
        .alert.ok{background:rgba(46,204,113,.12);border:1px solid rgba(46,204,113,.3)}
        .alert.danger{background:rgba(255,107,107,.12);border:1px solid rgba(255,107,107,.3)}
        .page-header{display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap;margin:.6rem 0 1rem}
        .page-title{font-size:1.15rem;font-weight:700}

        /* STATE (ouvert/fermé) — togglé par JS en ajoutant .nav-open sur body */
        body.nav-open .drawer{transform:translateX(0)}
        body.nav-open .drawer-backdrop{opacity:1;pointer-events:auto}
    </style>

    @stack('styles')
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    <div class="app-header" role="banner">
        <button class="burger" id="burgerBtn" aria-label="Ouvrir le menu" aria-controls="appDrawer" aria-expanded="false">☰</button>
        <div class="brand">
            <div class="logo" aria-hidden="true"></div>
            <div class="name">{{ config('app.name','Rembourséo') }}</div>
        </div>
        <div>
            @auth
                <form method="POST" action="{{ route('logout') }}" style="display:inline">
                    @csrf
                    <button type="submit" class="burger" aria-label="Se déconnecter">Déconnexion</button>
                </form>
            @else
                <a class="burger" href="{{ route('login') }}">Connexion</a>
            @endauth
        </div>
    </div>

    {{-- Backdrop (mobile) --}}
    <div class="drawer-backdrop" id="drawerBackdrop" tabindex="-1" aria-hidden="true"></div>

    {{-- Grille principale : drawer EN PREMIÈRE COLONNE + contenu --}}
    <div class="layout">
        <nav id="appDrawer" class="drawer" aria-label="Navigation principale">
            <div class="brand" style="margin-bottom:.25rem">
                <div class="logo" aria-hidden="true"></div>
                <div class="name">{{ config('app.name','Rembourséo') }}</div>
            </div>

            @auth
            <div class="nav-group">
                <div class="nav-title">Navigation</div>
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">🏠 Tableau de bord</a>
                {{-- Exemple de liens métiers :
                <a href="{{ route('expenses.index') }}" class="nav-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">💳 Dépenses</a>
                <a href="{{ route('claims.index') }}"   class="nav-item {{ request()->routeIs('claims.*') ? 'active' : '' }}">💶 Remboursements</a>
                <a href="{{ route('insurers.index') }}" class="nav-item {{ request()->routeIs('insurers.*') ? 'active' : '' }}">🏥 Mutuelles</a>
                --}}
                <a href="{{ route('families.manage') }}"  class="nav-item {{ request()->routeIs('families.*') ? 'active' : '' }}">👪 Familles</a>
                
            </div>
            @endauth

            <div class="nav-group">
                <div class="nav-title">Compte</div>
                @auth
                    <span class="nav-item" style="opacity:.85">👤 {{ auth()->user()->name }}</span>
                @else
                    <a href="{{ route('register') }}" class="nav-item {{ request()->routeIs('register') ? 'active' : '' }}">➕ Créer un compte</a>
                @endauth
            </div>

            <div class="drawer-footer">© {{ now()->year }} {{ config('app.name','Laravel') }}</div>
        </nav>

        <div class="container">
            <div class="content">
                @if (session('success')) <div class="alert ok">{{ session('success') }}</div> @endif
                @if (session('error'))   <div class="alert danger">{{ session('error') }}</div> @endif
                @if ($errors->any())
                    <div class="alert danger">
                        <strong>Oups :</strong>
                        <ul style="margin:.4rem 0 0 1rem">
                            @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <div class="page-header">
                    <h1 class="page-title">@yield('title', 'Accueil')</h1>
                    <div>@yield('page-actions')</div>
                </div>

                <section class="card">
                    {{-- pour Livewire v3 --}}
                        {{ $slot ?? '' }}
                    @yield('content')
                </section>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const body = document.body;
            const btn  = document.getElementById('burgerBtn');
            const drawer = document.getElementById('appDrawer');
            const backdrop = document.getElementById('drawerBackdrop');

            function openNav() {
                body.classList.add('nav-open');
                btn.setAttribute('aria-expanded', 'true');
                drawer.setAttribute('tabindex','-1');
                drawer.focus({preventScroll:true});
            }
            function closeNav() {
                body.classList.remove('nav-open');
                btn.setAttribute('aria-expanded', 'false');
                btn.focus({preventScroll:true});
            }

            btn?.addEventListener('click', () => {
                body.classList.contains('nav-open') ? closeNav() : openNav();
            });
            backdrop?.addEventListener('click', closeNav);
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && body.classList.contains('nav-open')) closeNav();
            });

            // Fermer auto en desktop lors d'un resize
            const mq = window.matchMedia('(min-width: 900px)');
            if (mq.addEventListener) mq.addEventListener('change', () => { if (mq.matches) closeNav(); });
        })();
    </script>

    @stack('scripts')
    @livewireScripts
</body>
</html>
