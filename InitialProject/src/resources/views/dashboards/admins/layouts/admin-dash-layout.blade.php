<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard')</title>
    <base href="{{ \URL::to('/') }}">
    <link href="img/Newlogo.png" rel="shortcut icon" type="image/x-icon" />

    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/ijaboCropTool/ijaboCropTool.min.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/typicons/typicons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/simple-line-icons/css/simple-line-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('js/select.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/styleadmin.css') }}">

    <style>
        .admin-page-shell .content-wrapper {
            min-height: calc(100vh - 140px);
        }

        .admin-page-shell .navbar .welcome-text {
            font-size: 1.25rem;
        }

        .admin-page-shell .navbar .welcome-sub-text {
            margin-bottom: 0;
            color: #6c757d;
        }

        .admin-page-shell .nav .nav-link.active {
            background: linear-gradient(90deg, rgba(78, 115, 223, 0.14), rgba(78, 115, 223, 0.04));
        }

        .admin-page-shell .btn-block {
            display: block;
            width: 100%;
        }

        .admin-page-shell .mr-1 {
            margin-right: .25rem !important;
        }

        .admin-page-shell .mr-2 {
            margin-right: .5rem !important;
        }

        .admin-page-shell .mr-3 {
            margin-right: 1rem !important;
        }

        .admin-page-shell .ml-2 {
            margin-left: .5rem !important;
        }

        .admin-page-shell .ml-3 {
            margin-left: 1rem !important;
        }

        .admin-page-shell .text-monospace {
            font-family: var(--bs-font-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace) !important;
        }

        .admin-page-shell .badge-pill {
            border-radius: 50rem !important;
        }

        .admin-page-shell .custom-range,
        .admin-page-shell .form-range {
            width: 100%;
        }

        .admin-page-shell .input-group-prepend,
        .admin-page-shell .input-group-append {
            display: flex;
            align-items: stretch;
        }

        .admin-page-shell .close {
            border: 0;
            background: transparent;
            color: inherit;
            font-size: 1.5rem;
            line-height: 1;
            padding: 0;
            opacity: .75;
        }

        .admin-page-shell .close:hover {
            opacity: 1;
        }
    </style>
    @stack('styles')
</head>

<body class="admin-page-shell">
    <div class="container-scroller sidebar-dark">
        <nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
                <div class="me-3">
                    <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
                        <span class="icon-menu"></span>
                    </button>
                </div>
            </div>
            <div class="navbar-menu-wrapper d-flex align-items-top">
                <ul class="navbar-nav">
                    <li class="nav-item font-weight-semibold d-none d-lg-block ms-0">
                        <h1 class="welcome-text">Admin Console</h1>
                        <p class="welcome-sub-text">{{ Auth::user()->fname_en ?? Auth::user()->fname ?? '' }} {{ Auth::user()->lname_en ?? Auth::user()->lname ?? '' }}</p>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item d-none d-sm-inline-block">
                        <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('Logout') }} <i class="mdi mdi-logout"></i>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-bs-toggle="offcanvas">
                    <span class="mdi mdi-menu"></span>
                </button>
            </div>
        </nav>

        <div class="container-fluid page-body-wrapper">
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="menu-icon mdi mdi-view-dashboard"></i>
                            <span class="menu-title">Admin Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item nav-category">Monitoring</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.activities') || request()->routeIs('admin.activity*') ? 'active' : '' }}" href="{{ route('admin.activities') }}">
                            <i class="menu-icon mdi mdi-history"></i>
                            <span class="menu-title">Activity Logs</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.errors') || request()->routeIs('admin.error*') ? 'active' : '' }}" href="{{ route('admin.errors') }}">
                            <i class="menu-icon mdi mdi-alert-circle"></i>
                            <span class="menu-title">Error Logs</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.system') ? 'active' : '' }}" href="{{ route('admin.system') }}">
                            <i class="menu-icon mdi mdi-server"></i>
                            <span class="menu-title">System Info</span>
                        </a>
                    </li>
                    <li class="nav-item nav-category">Security</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.security.events') ? 'active' : '' }}" href="{{ route('admin.security.events') }}">
                            <i class="menu-icon mdi mdi-shield-alert"></i>
                            <span class="menu-title">Security Events</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.security.blocked-ips') ? 'active' : '' }}" href="{{ route('admin.security.blocked-ips') }}">
                            <i class="menu-icon mdi mdi-shield-lock"></i>
                            <span class="menu-title">Blocked IPs</span>
                        </a>
                    </li>
                    <li class="nav-item nav-category">Navigation</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="menu-icon mdi mdi-home-outline"></i>
                            <span class="menu-title">Main Dashboard</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="main-panel">
                <div class="content-wrapper">
                    @yield('content')
                </div>
                <footer class="footer">
                    <div class="text-center">
                        <span class="text-muted">v1.0.0</span>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('vendors/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('vendors/progressbar.js/progressbar.min.js') }}"></script>
    <script src="{{ asset('js/off-canvas.js') }}"></script>
    <script src="{{ asset('js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('js/template.js') }}"></script>
    <script src="{{ asset('js/settings.js') }}"></script>
    <script src="{{ asset('js/todolist.js') }}"></script>
    <script src="{{ asset('plugins/ijaboCropTool/ijaboCropTool.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-toggle]').forEach(function(element) {
                element.setAttribute('data-bs-toggle', element.getAttribute('data-toggle'));
            });

            document.querySelectorAll('[data-target]').forEach(function(element) {
                element.setAttribute('data-bs-target', element.getAttribute('data-target'));
            });

            document.querySelectorAll('[data-dismiss]').forEach(function(element) {
                element.setAttribute('data-bs-dismiss', element.getAttribute('data-dismiss'));
            });
        });
    </script>
    @stack('scripts')
    @yield('javascript')
</body>

</html>
