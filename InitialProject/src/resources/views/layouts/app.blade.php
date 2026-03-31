<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&family=Prompt:wght@300;400;500&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 (เดียวกับ layout.blade.php) -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">

    <!-- Shared CSS Design Tokens (ใช้ร่วมกันทุก layout) -->
    <link rel="stylesheet" href="{{ asset('css/shared.css') }}">

    <!-- Page-specific styles -->
    @stack('styles')

    <style>
        /* Auth page base styles */
        body {
            background-color: var(--surface-50);
            min-height: 100vh;
        }

        /* Navbar */
        .navbar-auth {
            background-color: var(--surface-0);
            box-shadow: var(--shadow-xs);
            padding: 0.75rem 0;
        }

        .navbar-auth .navbar-brand {
            font-family: var(--heading-font);
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--brand-700);
            text-decoration: none;
        }

        .navbar-auth .navbar-brand:hover {
            color: var(--brand-600);
        }

        .navbar-auth .nav-link {
            color: var(--text-700);
            font-size: 0.9rem;
            padding: 0.4rem 0.85rem;
            border-radius: var(--radius-sm);
            transition: background-color var(--transition-fast), color var(--transition-fast);
        }

        .navbar-auth .nav-link:hover {
            background-color: var(--brand-100);
            color: var(--brand-700);
        }

        /* Auth card */
        .auth-wrapper {
            min-height: calc(100vh - 64px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .auth-card {
            background: var(--surface-0);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(0, 0, 0, 0.06);
            padding: 2.5rem;
            width: 100%;
            max-width: 460px;
        }

        .auth-card .auth-title {
            font-family: var(--heading-font);
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-900);
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .auth-card .auth-subtitle {
            font-size: 0.875rem;
            color: var(--text-500);
            text-align: center;
            margin-bottom: 2rem;
        }

        /* Form elements */
        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-700);
        }

        .form-control {
            border: 1.5px solid #d1d5db;
            border-radius: var(--radius-sm);
            padding: 0.55rem 0.875rem;
            font-family: var(--body-font);
            font-size: 0.9rem;
            color: var(--text-900);
            transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
        }

        .form-control:focus {
            border-color: var(--brand-700);
            box-shadow: 0 0 0 3px rgba(16, 117, 187, 0.15);
            outline: none;
        }

        .form-control.is-invalid {
            border-color: var(--danger-600);
        }

        .invalid-feedback {
            font-size: 0.8rem;
            color: var(--danger-600);
        }

        /* Auth button */
        .btn-auth {
            background-color: var(--brand-700);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            padding: 0.65rem 1.5rem;
            font-family: var(--body-font);
            font-weight: 600;
            font-size: 0.95rem;
            width: 100%;
            cursor: pointer;
            transition: background-color var(--transition-fast), box-shadow var(--transition-fast);
        }

        .btn-auth:hover,
        .btn-auth:focus {
            background-color: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(16, 117, 187, 0.35);
            color: #fff;
        }

        .btn-auth:active {
            background-color: var(--brand-900);
        }

        /* Auth links */
        .auth-link {
            font-size: 0.875rem;
            color: var(--brand-700);
            text-decoration: none;
        }

        .auth-link:hover {
            color: var(--brand-600);
            text-decoration: underline;
        }

        /* Divider */
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.25rem 0;
            color: var(--text-500);
            font-size: 0.8rem;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        /* Remember me checkbox */
        .form-check-input:checked {
            background-color: var(--brand-700);
            border-color: var(--brand-700);
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(16, 117, 187, 0.15);
            border-color: var(--brand-700);
        }

        .form-check-label {
            font-size: 0.875rem;
            color: var(--text-700);
        }

        /* Footer */
        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.8rem;
            color: var(--text-500);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-md navbar-auth">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'ระบบข้อมูลงานวิจัย') }}
            </a>

            <button class="navbar-toggler border-0" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarAuthContent"
                    aria-controls="navbarAuthContent"
                    aria-expanded="false"
                    aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarAuthContent">
                <ul class="navbar-nav ms-auto">
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                        @endif
                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown"
                               class="nav-link dropdown-toggle"
                               href="#"
                               role="button"
                               data-bs-toggle="dropdown"
                               aria-expanded="false">
                                {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>
                                </li>
                            </ul>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <div class="auth-footer pb-4">
        <p>&copy; {{ date('Y') }} College of Computing, Khon Kaen University</p>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    @stack('scripts')
</body>
</html>
