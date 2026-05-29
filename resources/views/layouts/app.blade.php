<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'IMPACTO URBANIZACIONES')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
@auth
    <div class="shell">
        @include('layouts.partials.sidebar')
        <main class="main">
            @yield('content')
            <footer class="footer">Version piloto - MVP funcional.</footer>
        </main>
    </div>
@else
    @yield('content')
@endauth
<script src="{{ asset('js/sidebar-menu.js') }}"></script>
</body>
</html>
