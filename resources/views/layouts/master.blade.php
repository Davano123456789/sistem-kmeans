<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('tamplate-dashboard/assets/img/apple-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('tamplate-dashboard/assets/img/favicon.png') }}">
    <title>
        @yield('title', 'Dashboard') - Sistem K-Means
    </title>
    <!--     Fonts and icons     -->
    <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
    <!-- Nucleo Icons -->
    <link href="{{ asset('tamplate-dashboard/assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('tamplate-dashboard/assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- CSS Files -->
    <link id="pagestyle" href="{{ asset('tamplate-dashboard/assets/css/material-dashboard.css?v=3.0.0') }}"
        rel="stylesheet" />

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --bs-primary: #000;
            --bs-primary-rgb: 0, 0, 0;
        }

        /* Fix for material icons not loading or being overridden */
        .material-icons,
        .material-icons-round {
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            -moz-osx-font-smoothing: grayscale;
            font-feature-settings: 'liga';
        }

        /* Aggressive fix for all pink elements in Material Dashboard */
        .input-group.input-group-outline.is-focused .form-label,
        .input-group.input-group-outline.is-filled .form-label,
        .input-group.input-group-static.is-focused label,
        .input-group.input-group-static.is-filled label {
            color: #000 !important;
        }

        /* Surgical fix for the outline border sides */
        .input-group.input-group-outline.is-focused .form-label+.form-control,
        .input-group.input-group-outline.is-filled .form-label+.form-control {
            border-color: #000 !important;
            border-top-color: transparent !important; /* This creates the gap for the label */
            box-shadow: inset 1px 0 #000, inset -1px 0 #000, inset 0 -1px #000 !important;
        }

        /* Surgical fix for the outline top border (pseudo-elements) */
        .input-group.input-group-outline.is-focused .form-label:before,
        .input-group.input-group-outline.is-focused .form-label:after,
        .input-group.input-group-outline.is-filled .form-label:before,
        .input-group.input-group-outline.is-filled .form-label:after {
            border-top-color: #000 !important;
            box-shadow: inset 0 1px #000 !important;
        }

        /* Surgical fix for the static line under inputs (Role) */
        .input-group.input-group-static.is-focused .form-control,
        .input-group.input-group-static.is-filled .form-control,
        .input-group.input-group-static .form-control:focus {
            background-image: linear-gradient(0deg, #000 2px, rgba(156, 39, 176, 0) 0), linear-gradient(0deg, #d2d2d2 1px, rgba(156, 39, 176, 0) 0) !important;
            background-size: 100% 100%, 100% 100% !important;
        }

        /* Ensure labels don't get covered and stay visible, but don't block clicks */
        .input-group.input-group-outline.is-focused .form-label,
        .input-group.input-group-outline.is-filled .form-label {
            display: flex !important;
            line-height: 1 !important;
            top: -10px !important;
            z-index: 5 !important;
            pointer-events: none !important; /* This allows clicks to pass through to the input */
        }

        /* Fix for Sidebar staying on top of SweetAlert overlay */
        .sidenav {
            z-index: 1000 !important;
        }
        .swal2-container {
            z-index: 9999 !important;
        }
    </style>
    @stack('styles')
</head>

<body class="g-sidenav-show  bg-gray-200">

    @include('layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">

        @include('layouts.topbar')

        <div class="container-fluid py-4">

            @yield('content')


        </div>
    </main>

    <!--   Core JS Files   -->
    <script src="{{ asset('tamplate-dashboard/assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('tamplate-dashboard/assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('tamplate-dashboard/assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('tamplate-dashboard/assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Material Dashboard -->
    <script src="{{ asset('tamplate-dashboard/assets/js/material-dashboard.min.js?v=3.0.0') }}"></script>

    <!-- Global SweetAlert2 Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    confirmButtonColor: '#262626',
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#262626',
                });
            @endif

            @if (session('warning'))
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: "{{ session('warning') }}",
                    confirmButtonColor: '#262626',
                });
            @endif
        });
    </script>

    @stack('scripts')
</body>

</html>