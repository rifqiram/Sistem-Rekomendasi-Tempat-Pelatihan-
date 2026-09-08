<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem Rekomendasi Tempat Pelatihan')</title>

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="{{ asset('css/sweetalert-modern.css') }}" rel="stylesheet">

    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --bg-color: #f8fafc;
            --surface-color: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        /* Global SweetAlert Modernization */
        .swal2-popup {
            border-radius: 1rem !important;
            padding: 1.5rem !important;
        }
        .swal2-title {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 1.5rem !important;
            font-weight: 700 !important;
            color: var(--text-main) !important;
        }
        .swal2-html-container {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            color: var(--text-muted) !important;
            font-size: 0.95rem !important;
        }
        .swal2-confirm {
            border-radius: 0.5rem !important;
            font-weight: 600 !important;
            padding: 0.75rem 2rem !important;
            background-color: var(--primary-color) !important;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2) !important;
        }
        .swal2-confirm:hover {
            background-color: var(--primary-hover) !important;
        }
    </style>

    @stack('styles')
</head>
<body>

    @yield('content')

    <!-- Global API JS -->
    <script src="{{ asset('js/api.js') }}"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="{{ asset('js/utils/sweetalert.js?v=' . time()) }}"></script>

    <!-- Global Utility Script for Auth Alerts -->
    <script>
        

        

        
    </script>

    @stack('scripts')
</body>
</html>