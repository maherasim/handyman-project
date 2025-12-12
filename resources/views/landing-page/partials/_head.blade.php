<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>{{env('APP_NAME')}} Service - On-Demand Home Service Flutter App with Complete Solution</title>
<link rel="shortcut icon" class="favicon_preview" href="{{ getSingleMedia(imageSession('get'),'favicon',null) }}" />
<link rel="stylesheet" href="{{ asset('css/landing-page.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/landing-page-rtl.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/landing-page.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/landing-page-custom.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/@fortawesome/fontawesome-free/css/all.min.css')}}">
<meta name="csrf-token" content="{{ csrf_token() }}">

<meta name="assert_url" content="{{ URL::to('') }}" />

<meta name="baseUrl" content="{{env('APP_URL')}}" />

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



@php
        $currentLang = app()->getLocale();
        $langFolderPath = resource_path("lang/$currentLang");
        $filePaths = \File::files($langFolderPath);
        $sitesetup = App\Models\Setting::where('type','site-setup')->where('key', 'site-setup')->first();
        $date_time = $sitesetup ? json_decode($sitesetup->value, true) : null;

        $dateformate = $date_time ? $date_time['date_format'] : 'Y-m-d';
        $serviceconfig = App\Models\Setting::getValueByKey('service-configurations', 'service-configurations');
    @endphp

    @foreach ($filePaths as $filePath)
        @php
            $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        @endphp
        <script>
            window.localMessagesUpdate = {
                ...window.localMessagesUpdate,
                "{{ $fileName }}": @json(require($filePath))
            };

            window.dateformate = @json($dateformate);
            window.cancellationCharge = @json($serviceconfig);
        </script>
    @endforeach
    <script>
        window.cancellationCharge = @json($serviceconfig);
    </script>
<script>
    // Static primary color - Red-Blue Gradient
    window.currentcolor = '#5F60B9';
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
    /* Apply gradient to nav pills only */
    .nav-pills .nav-link.active {
        background: var(--red-blue-gradient) !important;
        color: #fff !important;
    }
    
    /* Apply consistent padding to all navbar items to prevent jumping */
    .navbar-nav .nav-link {
        padding: 0.5rem 1rem !important;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    /* Apply gradient text to navbar items */
    .navbar-nav .nav-link.active {
        background: var(--red-blue-gradient) !important;
        color: #fff !important;
        -webkit-background-clip: border-box !important;
        -webkit-text-fill-color: #fff !important;
        background-clip: border-box !important;
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

    /* Apply gradient to banner backgrounds */
    .bg-light.banner-gradient,
    .padding-top-bottom-90 {
        background: var(--red-blue-gradient-light) !important;
    }

    /* Apply gradient to highlighted text */
    .highlight-skill,
    .highlighted-text {
        background: var(--red-blue-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>




