<!-- resources/views/layouts/auth.blade.php -->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body class="bg-gray-100 flex flex-col min-h-screen">

    <main class="grow">
        @yield('content')


    </main>

    <footer class="text-center py-4 small text-muted">
        &copy; {{ date('Y') }} Golden Gym. Todos los derechos reservados.
    </footer>

    <script>
        window.app = {
            baseUrl: "{{ url('/') }}" // genera http://localhost/golden_gym/public automáticamente
        };

        // 🔧 Función global que usa esa base URL
        const apiUrl = (path) => `${window.app.baseUrl}/${path.replace(/^\/+/, '')}`;
    </script>
</body>

</html>
