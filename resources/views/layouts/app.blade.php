<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta
        name="description"
        content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5" />
    <meta name="author" content="Golden Gym" />
    <meta
        name="keywords"
        content="Golden Gym, gimnasio, administración, panel, membresías, clientes, ventas, dashboard, Bootstrap 5, AdminKit" />
    <meta name="description" content="Panel de administración de Golden Gym. Gestiona clientes, membresías, ventas y métricas del gimnasio de manera rápida y segura." />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link rel="shortcut icon" href="../img/icons/golden_gym.png" />

    <title>@yield('title', 'Golden Gym')</title>

    <link href="{{ asset('css/app.css') }}" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <div class="wrapper">
        {{-- Sidebar --}}
        @include('layouts.sidebar')
        <div class="main">
            {{-- Navbar --}}
            @include('layouts.navbar')
            {{-- Contenido dinámico --}}
            <main class="p-4">
                @yield('content')
            </main>
            {{-- Footer --}}
            @include('layouts.footer')
        </div>
    </div>



    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
