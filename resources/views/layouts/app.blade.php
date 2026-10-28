<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Raspored učionica')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    @stack('styles')
</head>
<body class="bg-light">
    @auth
        <nav class="navbar navbar-dark bg-primary mb-3">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ route('home') }}">Raspored</a>

                <div class="d-flex gap-3 me-auto ms-3">
                    <a class="nav-link text-white" href="{{ route('raspored.ucionica') }}">Raspored učionice</a>

                    @if (auth()->user()->jeAdmin())
                        <a class="nav-link text-white" href="{{ route('raspored.nastavnik') }}">Raspored nastavnika</a>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-3">
                    <span class="navbar-text text-white">
                        {{ auth()->user()->name }}
                        <span class="badge bg-light text-primary">{{ auth()->user()->uloga }}</span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">Odjavi se</button>
                    </form>
                </div>
            </div>
        </nav>
    @endauth

    <main class="container-fluid">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
