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
            // Red-Blue Gradient Primary Color - set globally
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
            /* Global Red-Blue Gradient Styles - Applied Throughout Project */
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

            /* Apply gradient to primary links */
            a.text-primary,
            .text-primary {
                background: var(--red-blue-gradient);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            /* Apply gradient to primary backgrounds */
            .bg-primary {
                background: var(--red-blue-gradient) !important;
            }

            /* Apply gradient to primary borders */
            .border-primary {
                border-color: transparent !important;
                background: var(--red-blue-gradient) !important;
                background-clip: padding-box, border-box;
                background-origin: padding-box, border-box;
                border: 2px solid transparent;
            }

            /* Apply gradient to cards and components with primary color */
            .card-primary,
            .badge-primary,
            .alert-primary {
                background: var(--red-blue-gradient) !important;
                color: #fff !important;
            }

            /* Apply gradient to navigation items */
            .nav-link.active,
            .nav-pills .nav-link.active {
                background: var(--red-blue-gradient) !important;
                color: #fff !important;
            }

            /* Apply gradient to progress bars */
            .progress-bar.bg-primary {
                background: var(--red-blue-gradient) !important;
            }

            /* Apply gradient to pagination */
            .page-item.active .page-link {
                background: var(--red-blue-gradient) !important;
                border-color: transparent !important;
            }

            /* Apply gradient to form controls */
            .form-control:focus,
            .form-select:focus {
                border-color: #5F60B9 !important;
                box-shadow: 0 0 0 0.25rem rgba(95, 96, 185, 0.25) !important;
            }

            /* Apply gradient to custom elements using var(--bs-primary) */
            [style*="--bs-primary"],
            [style*="background-color: var(--bs-primary)"] {
                background: var(--red-blue-gradient) !important;
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
