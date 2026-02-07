<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="baseUrl" content="{{env('APP_URL')}}" />

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="shortcut icon" class="site_favicon_preview" href="{{ getSingleMedia(imageSession('get'),'favicon',null) }}" />

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/backend.css') }}">
        <link rel="stylesheet" href="{{ asset('css/fronted-custom.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/@fortawesome/fontawesome-free/css/all.min.css')}}">

        <script>
            // Primary color #3333ff - same as theme
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
            :root {
                --bs-primary-solid: #3333ff;
                --bs-primary-solid-hover: #2929e6;
            }
            .btn-primary, button.btn-primary, input[type="submit"].btn-primary, a.btn-primary {
                background: #3333ff !important;
                border: none !important;
                color: #fff !important;
            }
            .btn-primary:hover, button.btn-primary:hover, input[type="submit"].btn-primary:hover, a.btn-primary:hover {
                background: var(--bs-primary-solid-hover) !important;
                color: #fff !important;
            }
            a.text-primary, .text-primary { color: #3333ff !important; }
            .bg-primary { background: #3333ff !important; }
            .border-primary { border-color: #3333ff !important; }
            .card-primary, .badge-primary, .alert-primary {
                background: #3333ff !important;
                color: #fff !important;
            }
            .nav-link.active, .nav-pills .nav-link.active {
                background: #3333ff !important;
                color: #fff !important;
            }
            .progress-bar.bg-primary { background: #3333ff !important; }
            .page-item.active .page-link {
                background: #3333ff !important;
                border-color: #3333ff !important;
            }
            .form-control:focus, .form-select:focus {
                border-color: #3333ff !important;
                box-shadow: 0 0 0 0.25rem rgba(51, 51, 255, 0.25) !important;
            }
        </style>

    </head>
    <body class=" " >

        <div class="wrapper">
            {{ $slot }}
        </div>
         @include('partials._scripts')
    </body>
</html>
