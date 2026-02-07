<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="baseUrl" content="{{env('APP_URL')}}" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    @include('partials._head')
    
    <!-- Primary color #3333ff - Dashboard Cards -->
    <style>
        /* Primary #3333ff on Dashboard Cards - same as theme */
        body .container-fluid .row .col-lg-3 .card.total-booking-card,
        body .container-fluid .row .col-lg-3 .card.total-service-card,
        body .container-fluid .row .col-lg-3 .card.total-provider-card,
        body .container-fluid .row .col-lg-3 .card.total-revenue,
        body .container-fluid .row .col-md-6 .card.total-booking-card,
        body .container-fluid .row .col-md-6 .card.total-service-card,
        body .container-fluid .row .col-md-6 .card.total-provider-card,
        body .container-fluid .row .col-md-6 .card.total-revenue,
        body .card.total-booking-card,
        body .card.total-service-card,
        body .card.total-provider-card,
        body .card.total-revenue {
            background: #3333ff !important;
            background-image: none !important;
            background-color: #3333ff !important;
            color: #fff !important;
        }

        body .card.total-booking-card::after,
        body .card.total-service-card::after,
        body .card.total-provider-card::after,
        body .card.total-revenue::after {
            display: none !important; /* Remove overlay to keep gradient clear and vibrant */
        }

        body .iq-card-icon,
        body .iq-card-icon-booking,
        body .iq-card-icon-service,
        body .iq-card-icon-provider,
        body .iq-card-icon-revenue {
            background-color: rgba(255, 255, 255, 0.2) !important;
            background: rgba(255, 255, 255, 0.2) !important;
        }
    </style>
    
    <script>
        // Primary color #3333ff - same as theme (backend/dashboard)
        const root = document.documentElement;
        
        const primaryHex = '#3333ff';
        const primaryR = 51;
        const primaryG = 51;
        const primaryB = 255;
        
        root.style.setProperty('--bs-primary', primaryHex);
        root.style.setProperty('--bs-primary-rgb', `${primaryR}, ${primaryG}, ${primaryB}`);
        root.style.setProperty('--bs-primary-bg-subtle', `rgba(${primaryR}, ${primaryG}, ${primaryB}, 0.09)`);
        root.style.setProperty('--bs-primary-border-subtle', `rgba(${primaryR}, ${primaryG}, ${primaryB}, 0.2)`);
        root.style.setProperty('--bs-primary-hover-bg', `rgba(${primaryR}, ${primaryG}, ${primaryB}, 0.85)`);
        root.style.setProperty('--bs-primary-hover-border', `rgba(${primaryR}, ${primaryG}, ${primaryB}, 0.9)`);
        root.style.setProperty('--bs-primary-active-bg', `rgba(${primaryR}, ${primaryG}, ${primaryB}, 0.85)`);
        root.style.setProperty('--bs-primary-active-border', `rgba(${primaryR}, ${primaryG}, ${primaryB}, 0.9)`);
    </script>
    
    <style>
        /* Primary #3333ff - same as theme */
        :root {
            --bs-primary-solid: #3333ff;
            --bs-primary-solid-hover: #2929e6;
            --bs-primary-solid-light: rgba(51, 51, 255, 0.1);
        }

        .btn-primary,
        button.btn-primary,
        input[type="submit"].btn-primary,
        a.btn-primary {
            background: #3333ff !important;
            border: none !important;
            color: #fff !important;
        }

        .btn-primary:hover,
        button.btn-primary:hover,
        input[type="submit"].btn-primary:hover,
        a.btn-primary:hover {
            background: var(--bs-primary-solid-hover) !important;
            color: #fff !important;
        }

        /* Sidebar - Active menu items */
        .iq-sidebar .nav-link.active,
        .iq-sidebar .nav-item.active > .nav-link,
        .iq-sidebar .nav-link[aria-expanded="true"],
        .iq-sidebar .side-menu .nav-item.active > a,
        .iq-sidebar .side-menu .nav-link.active {
            background: #3333ff !important;
            color: #fff !important;
        }

        .iq-sidebar .nav-link:hover,
        .iq-sidebar .side-menu .nav-link:hover {
            background: var(--bs-primary-solid-light) !important;
            color: #333 !important;
        }

        /* Sidebar - Logo area */
        .iq-sidebar-logo {
            background: #3333ff !important;
        }

        /* Sidebar - Submenu active items */
        .iq-sidebar .nav-item .nav-link.active,
        .iq-sidebar .dropdown-menu .nav-link.active {
            background: #3333ff !important;
            color: #fff !important;
        }

        /* Sidebar - Category headers */
        .iq-sidebar .category-main {
            color: #666 !important;
            font-weight: 600;
        }

        /* Header/Navbar */
        .iq-top-navbar,
        .iq-navbar-custom,
        .iq-navbar-custom .navbar {
            background: #3333ff !important;
        }

        .iq-top-navbar .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        .iq-top-navbar .navbar-nav .nav-link:hover {
            color: #fff !important;
        }

        /* Footer */
        .iq-footer {
            background: #3333ff !important;
            color: #fff !important;
        }

        /* Primary backgrounds */
        .bg-primary {
            background: #3333ff !important;
        }

        /* Primary text */
        .text-primary {
            background: #3333ff;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Badges and alerts */
        .badge-primary,
        .alert-primary {
            background: #3333ff !important;
            color: #fff !important;
        }

        /* Progress bars */
        .progress-bar.bg-primary {
            background: #3333ff !important;
        }

        /* Pagination */
        .page-item.active .page-link {
            background: #3333ff !important;
            border-color: transparent !important;
        }

        /* Form controls focus */
        .form-control:focus,
        .form-select:focus {
            border-color: #3333ff !important;
            box-shadow: 0 0 0 0.25rem rgba(95, 96, 185, 0.25) !important;
        }

        /* Cards with primary color */
        .card-primary {
            background: #3333ff !important;
            color: #fff !important;
        }

        /* Table primary elements */
        .table-primary {
            background: #3333ff !important;
            color: #fff !important;
        }

        /* Dropdown menu active */
        .dropdown-item.active,
        .dropdown-item:active {
            background: #3333ff !important;
            color: #fff !important;
        }


        /* Chart colors */
        .apexcharts-series path,
        .apexcharts-series line {
            stroke: #3333ff !important;
        }

        .apexcharts-series rect {
            fill: #3333ff !important;
        }

        /* Badges with primary color */
        .badge.bg-primary,
        .badge.bg-primary-subtle {
            background: #3333ff !important;
            color: #fff !important;
        }

        /* Override any inline styles */
        [style*="background: var(--bs-primary)"],
        [style*="background-color: var(--bs-primary)"] {
            background: #3333ff !important;
        }
    </style>
</head>
<body class="" id="app">
@include('partials._body')
</body>
</html>
