<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inicio') · {{ config('app.name', 'Gestión RRHH') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --sidebar-w: 250px; }
        body { background: #f4f6f9; }
        .sidebar {
            width: var(--sidebar-w); min-height: 100vh; background: #1e293b;
            position: fixed; top: 0; left: 0; z-index: 1030; transition: transform .2s;
            overflow-y: auto; max-height: 100vh;
        }
        .sidebar .brand { color: #fff; font-weight: 600; padding: 1rem 1.25rem; border-bottom: 1px solid #334155; }
        .sidebar .nav-link { color: #cbd5e1; padding: .6rem 1.25rem; border-radius: 0; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #334155; color: #fff; }
        .sidebar .nav-title { color: #64748b; font-size: .75rem; text-transform: uppercase; padding: 1rem 1.25rem .25rem; }
        .main { margin-left: var(--sidebar-w); }
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>
@php($u = auth()->user())

<aside class="sidebar" id="sidebar">
    <div class="brand">
        <i class="bi bi-people-fill me-2"></i>{{ config('app.name', 'Gestión RRHH') }}
        @if($u->empresa)
            <div class="small fw-normal text-secondary text-truncate">{{ $u->empresa->nombre }}</div>
        @endif
    </div>
    <nav class="nav flex-column py-2">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 me-2"></i>Inicio
        </a>

        {{-- Estructura de la empresa (admin RRHH) --}}
        @if($u->esAdmin())
            <div class="nav-title">Personal</div>
            <a href="{{ route('empleados.index') }}" class="nav-link {{ request()->routeIs('empleados.*') ? 'active' : '' }}">
                <i class="bi bi-person-vcard me-2"></i>Legajos
            </a>

            <div class="nav-title">Estructura</div>
            <a href="{{ route('areas.index') }}" class="nav-link {{ request()->routeIs('areas.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3 me-2"></i>Áreas
            </a>
            <a href="{{ route('puestos.index') }}" class="nav-link {{ request()->routeIs('puestos.*') ? 'active' : '' }}">
                <i class="bi bi-briefcase me-2"></i>Puestos
            </a>
            <a href="{{ route('convenios.index') }}" class="nav-link {{ request()->routeIs('convenios.*', 'categorias.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text me-2"></i>Convenios y categorías
            </a>
        @endif

        {{-- Los menús de los próximos módulos se agregan acá --}}

        @if($u->esSuperadmin() || $u->esAdmin())
            <div class="nav-title">Administración</div>

            @if($u->esSuperadmin())
                <a href="{{ route('empresas.index') }}" class="nav-link {{ request()->routeIs('empresas.*') ? 'active' : '' }}">
                    <i class="bi bi-building me-2"></i>Empresas
                </a>
            @endif

            <a href="{{ route('usuarios.index') }}" class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                <i class="bi bi-person-gear me-2"></i>Usuarios
            </a>
        @endif
    </nav>
</aside>

<div class="main">
    <nav class="navbar navbar-light bg-white border-bottom px-3">
        <button class="btn btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')">
            <i class="bi bi-list"></i>
        </button>
        <span class="navbar-text fw-semibold">@yield('title', 'Inicio')</span>
        <div class="dropdown ms-auto">
            <a href="#" class="text-decoration-none text-dark dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-1"></i>{{ $u->name }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><span class="dropdown-item-text small text-muted">{{ $u->rol_nombre }}</span></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <main class="p-3 p-md-4">
        @if(session('ok'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('ok') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
