<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'User Dashboard') | Sistem Rekomendasi</title>

    <!-- Google Font: Plus Jakarta Sans (Modern Look for Users) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="{{ asset('css/sweetalert-modern.css') }}" rel="stylesheet">

    <style>
        /* CSS Variables Global - Enterprise Standard */
        :root {
            /* Palette Utama */
            --primary-color: #4f46e5;       /* Indigo 600 */
            --primary-hover: #4338ca;       /* Indigo 700 */
            --secondary-color: #10b981;     /* Emerald 500 */
            --warning-color: #f59e0b;       /* Amber 500 */
            --danger-color: #ef4444;        /* Red 500 */
            --info-color: #3b82f6;          /* Blue 500 */

            /* Backgrounds & Surfaces */
            --bg-color: #f8fafc;            /* Slate 50 - Soft Background */
            --surface-color: #ffffff;
            --border-color: #e2e8f0;        /* Slate 200 */

            /* Typography */
            --text-main: #1e293b;           /* Slate 800 */
            --text-muted: #64748b;          /* Slate 500 */

            /* Shadows & Radius */
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        /* Glassmorphism Navbar */
        .navbar-glass {
            background-color: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            box-shadow: var(--shadow-sm);
            padding: 0.75rem 0;
        }

        .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--primary-color) !important;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        

        /* Modern Nav Links */
        .nav-link {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-muted);
            padding: 0.5rem 1rem !important;
            border-radius: 0.5rem;
            transition: all 0.25s ease;
            margin: 0 0.15rem;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-color);
            background-color: rgba(79, 70, 229, 0.08);
        }

        /* Improved Dropdown User */
        .dropdown-menu {
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
            border-radius: var(--radius-md);
            padding: 0.5rem;
            min-width: 200px;
        }

        .dropdown-item {
            border-radius: 0.375rem;
            padding: 0.6rem 1rem;
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--text-main);
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }

        .dropdown-item i {
            width: 20px;
            color: var(--text-muted);
            transition: color 0.2s;
        }

        .dropdown-item:hover {
            background-color: rgba(79, 70, 229, 0.08);
            color: var(--primary-color);
        }

        .dropdown-item:hover i {
            color: var(--primary-color);
        }

        .dropdown-item.text-danger:hover {
            background-color: rgba(239, 68, 68, 0.08);
            color: var(--danger-color) !important;
        }

        .dropdown-item.text-danger:hover i {
            color: var(--danger-color);
        }

        .user-avatar {
            background: linear-gradient(135deg, var(--primary-color), #818cf8);
            color: white;
            font-weight: 700;
            border-radius: 50%;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);
            border: 2px solid white;
        }

        /* Main & Footer */
        .main-content {
            min-height: calc(100vh - 140px);
            padding: 40px 0;
        }

        .footer-user {
            background-color: var(--surface-color);
            border-top: 1px solid var(--border-color);
            padding: 24px 0;
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* SweetAlert styles moved to sweetalert-modern.css */
    </style>

    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-glass sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('user.dashboard') }}">
                <img src="{{ asset('images/logoSRTP.png') }}" alt="Logo SRTP" style="height: 120px; width: auto; object-fit: contain; margin-top: -35px; margin-bottom: -35px; margin-right: 10px; position: relative; z-index: 1050;">
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="userNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}" href="{{ route('user.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.profile') ? 'active' : '' }}" href="{{ route('user.profile') }}">Profil Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.questionnaire') ? 'active' : '' }}" href="{{ route('user.questionnaire') }}">Kuesioner</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.recommendations') ? 'active' : '' }}" href="{{ route('user.recommendations') }}">Rekomendasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.enrollments') ? 'active' : '' }}" href="{{ route('user.enrollments') }}">Riwayat</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center mt-3 mt-lg-0">
                    <div class="dropdown w-100">
                        <a class="nav-link dropdown-toggle d-flex align-items-center p-0 border-0 bg-transparent text-start w-100" href="#" role="button" data-bs-toggle="dropdown" style="color: var(--text-main);">
                            <div class="user-avatar me-2" id="user-avatar">
                                U
                            </div>
                            <div class="d-flex flex-column me-2">
                                <span id="user-name" class="fw-bold" style="font-size: 0.9rem; line-height: 1.2;">Pengguna</span>
                                <span class="text-muted" style="font-size: 0.75rem;" id="user-role-label">Pengguna</span>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end mt-2 animate slideIn">
                            <li class="px-3 py-2 border-bottom mb-1">
                                <div class="fw-bold text-dark" id="dropdown-user-name">Pengguna</div>
                                <div class="text-muted small" id="user-email">user@example.com</div>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('user.profile') }}"><i class="fas fa-user-edit me-2"></i> Pengaturan Akun</a></li>
                            <li><button class="dropdown-item text-danger" onclick="logout('/user/login')"><i class="fas fa-sign-out-alt me-2"></i> Keluar</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer-user text-center">
        <div class="container">
            &copy; 2026 Sistem Rekomendasi Tempat Pelatihan. Hak Cipta Dilindungi.
        </div>
    </footer>

    <!-- Global API JS -->
    <script src="{{ asset('js/api.js') }}"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="{{ asset('js/utils/sweetalert.js?v=' . time()) }}"></script>

    <script>
        // Global SweetAlert Helpers
        

        

        

        

        document.addEventListener('DOMContentLoaded', () => {
            const user = window.getApiUser();

            // Skip protection logic on auth pages
            if (window.location.pathname === '/user/login' || window.location.pathname === '/user/register') {
                return;
            }

            // Protect User Layout
            if (!window.getApiToken() || !user) {
                window.location.href = '/user/login';
            } else if (user.role === 'admin') {
                window.location.href = '/admin/dashboard';
            } else {
                // Populate User Data
                const displayName = user.name || 'Pengguna';
                const userNameEl = document.getElementById('user-name');
                const dropdownUserNameEl = document.getElementById('dropdown-user-name');
                const userEmailEl = document.getElementById('user-email');
                const userAvatarEl = document.getElementById('user-avatar');

                if (userNameEl) userNameEl.textContent = displayName;
                if (dropdownUserNameEl) dropdownUserNameEl.textContent = displayName;
                if (userEmailEl) userEmailEl.textContent = user.email || '';
                if (userAvatarEl) userAvatarEl.textContent = displayName.charAt(0).toUpperCase();
            }
        });
    </script>

    @stack('scripts')
</body>
</html>