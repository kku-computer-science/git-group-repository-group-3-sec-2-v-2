<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ระบบข้อมูลงานวิจัย วิทยาลัยการคอมพิวเตอร์</title>
    <base href="{{ \URL::to('/') }}">
    <link href="img/Newlogo.png" rel="shortcut icon" type="image/x-icon" />

    <!-- CSS Files -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/load-more-button.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="{{ asset('vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/typicons/typicons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/simple-line-icons/css/simple-line-icons.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/css/flag-icon.min.css">

    <!-- DataTable CSS -->
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.1/css/bootstrap.css" />
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.css" />
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.css" />

    <!-- JS Files -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <style>
        /* Content Wrapper */
        .content-wrapper {
            padding: 2rem 1rem;
            max-width: 1280px;
            margin: 0 auto;
        }

        @media (min-width: 1400px) {
            .content-wrapper {
                max-width: 1440px;
                /* เพิ่มขนาดความกว้างสำหรับหน้าจอใหญ่ */
                padding: 2.5rem 2rem;
                /* เพิ่มระยะห่างในหน้าจอใหญ่ */
            }
        }

        @media (min-width: 1600px) {
            .content-wrapper {
                max-width: 1600px;
                /* เพิ่มขนาดความกว้างอีกสำหรับจอใหญ่มาก */
                padding: 3rem 2rem;
                /* เพิ่ม Padding อีกสำหรับความสมดุล */
            }
        }


        /* Footer */
        .footer {
            background-color: white;
            padding: 2rem 0;
            margin-top: 4rem;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav id="navbar" class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand logo-image" href="#"><img src="{{ asset('img/logo2.png') }}" alt="alternative"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="collapsibleNavbar">
                <ul class="navbar-nav ms-auto navbar-nav-scroll">
                    <li class="nav-item {{ request()->is('/') ? 'active' : '' }}">
                        <a class="nav-link" href="/">{{ trans('message.Home') }}</a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('researchers.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('researchers.index') }}">
                            {{ trans('message.Researchers') }}
                        </a>
                    </li>
                    <li class="nav-item {{ request()->is('researchproject') ? 'active' : '' }}">
                        <a class="nav-link" href="/researchproject">{{ trans('message.ResearchProj') }}</a>
                    </li>
                    <li class="nav-item {{ request()->is('researchgroup') ? 'active' : '' }}">
                        <a class="nav-link" href="/researchgroup">{{ trans('message.ResearchGroup') }}</a>
                    </li>
                    <li class="nav-item {{ request()->is('reports') ? 'active' : '' }}">
                        <a class="nav-link" href="/reports">{{ trans('message.Report') }}</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-bs-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <span
                                class="flag-icon flag-icon-{{ Config::get('languages')[App::getLocale()]['flag-icon'] }}"></span>
                            {{ Config::get('languages')[App::getLocale()]['display'] }}
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            @foreach (Config::get('languages') as $lang => $language)
                            @if ($lang != App::getLocale())
                            <a class="dropdown-item" href="{{ route('langswitch', $lang) }}">
                                <span class="flag-icon flag-icon-{{ $language['flag-icon'] }}"></span>
                                {{ $language['display'] }}
                            </a>
                            @endif
                            @endforeach
                        </div>
                    </li>
                </ul>
                @if (Route::has('login'))
                @auth
                <span class="nav-item">
                    <a class="btn-solid-sm" href="/dashboard" target="_blank">Dashboard</a>
                </span>
                @else
                <span class="nav-item">
                    <a class="btn-solid-sm" href="/login" target="_blank">Login</a>
                </span>
                @endauth
                @endif

            </div> <!-- end of navbar-collapse -->
        </div> <!-- end of container -->
    </nav>

    <!-- end of navbar -->
    <!-- end of navigation -->

    <div class="content-wrapper">
        @yield('content')
    </div>
    @yield('javascript')

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="text-center">
                <p class="mb-0">&copy; {{ date('Y') }} College of Computing, Khon Kaen University. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>

</html>