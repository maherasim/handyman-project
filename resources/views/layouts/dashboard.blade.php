<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="baseUrl" content="{{env('APP_URL')}}" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    @include('partials._head')
    
    <!-- Red-Blue Gradient Styles - Loaded after all CSS -->
    <style>
        /* Force Red-Blue Gradient on Dashboard Cards - Maximum Specificity */
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
            background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
            background-image: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
            background-color: transparent !important;
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
        // Red-Blue Gradient Primary Color - set globally for admin dashboard
        const root = document.documentElement;
        
        // Red color (from gradient)
        const redHex = '#FF0000';
        const redR = 255;
        const redG = 0;
        const redB = 0;
        
        // Blue color (from gradient)
        const blueHex = '#5F60B9';
        const blueR = 95;
        const blueG = 96;
        const blueB = 185;
        
        // Set gradient as primary
        root.style.setProperty('--bs-primary-gradient', 'linear-gradient(135deg, #FF0000 0%, #5F60B9 100%)');
        root.style.setProperty('--bs-primary', blueHex); // Fallback for non-gradient support
        root.style.setProperty('--bs-primary-rgb', `${blueR}, ${blueG}, ${blueB}`);
        root.style.setProperty('--bs-primary-bg-subtle', `linear-gradient(135deg, rgba(255, 0, 0, 0.09) 0%, rgba(95, 96, 185, 0.09) 100%)`);
        root.style.setProperty('--bs-primary-border-subtle', `linear-gradient(135deg, rgba(255, 0, 0, 0.09) 0%, rgba(95, 96, 185, 0.09) 100%)`);
        root.style.setProperty('--bs-primary-hover-bg', `linear-gradient(135deg, rgba(255, 0, 0, 0.75) 0%, rgba(95, 96, 185, 0.75) 100%)`);
        root.style.setProperty('--bs-primary-hover-border', `linear-gradient(135deg, rgba(255, 0, 0, 0.75) 0%, rgba(95, 96, 185, 0.75) 100%)`);
        root.style.setProperty('--bs-primary-active-bg', `linear-gradient(135deg, rgba(255, 0, 0, 0.75) 0%, rgba(95, 96, 185, 0.75) 100%)`);
        root.style.setProperty('--bs-primary-active-border', `linear-gradient(135deg, rgba(255, 0, 0, 0.75) 0%, rgba(95, 96, 185, 0.75) 100%)`);
    </script>
    
    <style>
        /* Global Red-Blue Gradient Styles - Admin Dashboard */
        :root {
            --red-blue-gradient: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%);
            --red-blue-gradient-hover: linear-gradient(135deg, #cc0000 0%, #4a4d94 100%);
            --red-blue-gradient-light: linear-gradient(135deg, rgba(255, 0, 0, 0.1) 0%, rgba(95, 96, 185, 0.1) 100%);
        }

        /* Apply gradient to all primary buttons */
        .btn-primary,
        button.btn-primary,
        input[type="submit"].btn-primary,
        a.btn-primary {
            background: var(--red-blue-gradient) !important;
            border: none !important;
            color: #fff !important;
        }

        .btn-primary:hover,
        button.btn-primary:hover,
        input[type="submit"].btn-primary:hover,
        a.btn-primary:hover {
            background: var(--red-blue-gradient-hover) !important;
            color: #fff !important;
        }

        /* Sidebar - Active menu items */
        .iq-sidebar .nav-link.active,
        .iq-sidebar .nav-item.active > .nav-link,
        .iq-sidebar .nav-link[aria-expanded="true"],
        .iq-sidebar .side-menu .nav-item.active > a,
        .iq-sidebar .side-menu .nav-link.active {
            background: var(--red-blue-gradient) !important;
            color: #fff !important;
        }

        .iq-sidebar .nav-link:hover,
        .iq-sidebar .side-menu .nav-link:hover {
            background: var(--red-blue-gradient-light) !important;
            color: #333 !important;
        }

        /* Sidebar - Logo area */
        .iq-sidebar-logo {
            background: var(--red-blue-gradient) !important;
        }

        /* Sidebar - Submenu active items */
        .iq-sidebar .nav-item .nav-link.active,
        .iq-sidebar .dropdown-menu .nav-link.active {
            background: var(--red-blue-gradient) !important;
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
            background: var(--red-blue-gradient) !important;
        }

        .iq-top-navbar .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        .iq-top-navbar .navbar-nav .nav-link:hover {
            color: #fff !important;
        }

        /* Footer */
        .iq-footer {
            background: var(--red-blue-gradient) !important;
            color: #fff !important;
        }

        /* Primary backgrounds */
        .bg-primary {
            background: var(--red-blue-gradient) !important;
        }

        /* Primary text */
        .text-primary {
            background: var(--red-blue-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Badges and alerts */
        .badge-primary,
        .alert-primary {
            background: var(--red-blue-gradient) !important;
            color: #fff !important;
        }

        /* Progress bars */
        .progress-bar.bg-primary {
            background: var(--red-blue-gradient) !important;
        }

        /* Pagination */
        .page-item.active .page-link {
            background: var(--red-blue-gradient) !important;
            border-color: transparent !important;
        }

        /* Form controls focus */
        .form-control:focus,
        .form-select:focus {
            border-color: #5F60B9 !important;
            box-shadow: 0 0 0 0.25rem rgba(95, 96, 185, 0.25) !important;
        }

        /* Cards with primary color */
        .card-primary {
            background: var(--red-blue-gradient) !important;
            color: #fff !important;
        }

        /* Table primary elements */
        .table-primary {
            background: var(--red-blue-gradient) !important;
            color: #fff !important;
        }

        /* Dropdown menu active */
        .dropdown-item.active,
        .dropdown-item:active {
            background: var(--red-blue-gradient) !important;
            color: #fff !important;
        }


        /* Chart colors */
        .apexcharts-series path,
        .apexcharts-series line {
            stroke: #5F60B9 !important;
        }

        .apexcharts-series rect {
            fill: #5F60B9 !important;
        }

        /* Badges with primary color */
        .badge.bg-primary,
        .badge.bg-primary-subtle {
            background: var(--red-blue-gradient) !important;
            color: #fff !important;
        }

        /* Override any inline styles */
        [style*="background: var(--bs-primary)"],
        [style*="background-color: var(--bs-primary)"] {
            background: var(--red-blue-gradient) !important;
        }
    </style>
</head>
<body class="" id="app">
@include('partials._body')
</body>
</html>
