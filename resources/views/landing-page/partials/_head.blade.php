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
<link rel="stylesheet" href="{{ asset('vendor/remixicon/fonts/remixicon.css')}}">
<meta name="csrf-token" content="{{ csrf_token() }}">

<meta name="assert_url" content="{{ URL::to('') }}" />

<meta name="baseUrl" content="{{env('APP_URL')}}" />

<script>
    const root = document.documentElement;
    const primaryHex = '#3333ff';
    const primaryR = 51, primaryG = 51, primaryB = 255;
    root.style.setProperty('--bs-primary', primaryHex);
    root.style.setProperty('--bs-primary-rgb', `${primaryR}, ${primaryG}, ${primaryB}`);
    root.style.setProperty('--bs-primary-bg-subtle', `rgba(${primaryR}, ${primaryG}, ${primaryB}, 0.09)`);
    root.style.setProperty('--bs-primary-border-subtle', `rgba(${primaryR}, ${primaryG}, ${primaryB}, 0.2)`);
    root.style.setProperty('--bs-primary-hover-bg', `rgba(${primaryR}, ${primaryG}, ${primaryB}, 0.85)`);
    root.style.setProperty('--bs-primary-hover-border', `rgba(${primaryR}, ${primaryG}, ${primaryB}, 0.9)`);
    root.style.setProperty('--bs-primary-active-bg', `rgba(${primaryR}, ${primaryG}, ${primaryB}, 0.85)`);
    root.style.setProperty('--bs-primary-active-border', `rgba(${primaryR}, ${primaryG}, ${primaryB}, 0.9)`);
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
    window.currentcolor = '#3333ff';
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
    .btn-primary:hover, button.btn-primary:hover,
    input[type="submit"].btn-primary:hover,
    a.btn-primary:hover {
        background: var(--bs-primary-solid-hover) !important;
        color: #fff !important;
    }

    /* Apply gradient to primary links */
    a.text-primary,
    .text-primary {
        background: #3333ff;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Apply gradient to primary backgrounds */
    .bg-primary {
        background: #3333ff !important;
    }

    /* Apply gradient to primary borders */
    .border-primary {
        border-color: transparent !important;
        background: #3333ff !important;
        background-clip: padding-box, border-box;
        background-origin: padding-box, border-box;
        border: 2px solid transparent;
    }

    /* Apply gradient to cards and components with primary color */
    .card-primary,
    .badge-primary,
    .alert-primary {
        background: #3333ff !important;
        color: #fff !important;
    }

    /* Apply gradient to navigation items */
    /* Apply gradient to nav pills only */
    .nav-pills .nav-link.active {
        background: #3333ff !important;
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
        background: #3333ff !important;
        color: #fff !important;
        -webkit-background-clip: border-box !important;
        -webkit-text-fill-color: #fff !important;
        background-clip: border-box !important;
    }

    /* Apply gradient to progress bars */
    .progress-bar.bg-primary {
        background: #3333ff !important;
    }

    /* Apply gradient to pagination */
    .page-item.active .page-link {
        background: #3333ff !important;
        border-color: transparent !important;
    }

    /* Apply gradient to form controls */
    .form-control:focus,
    .form-select:focus {
        border-color: #3333ff !important;
        box-shadow: 0 0 0 0.25rem rgba(51, 51, 255, 0.25) !important;
    }

    /* Apply gradient to custom elements using var(--bs-primary) */
    [style*="--bs-primary"],
    [style*="background-color: var(--bs-primary)"] {
        background: #3333ff !important;
    }

    /* Apply gradient to banner backgrounds */
    .bg-light.banner-gradient,
    .padding-top-bottom-90 {
        background: rgba(51, 51, 255, 0.1) !important;
    }

    /* Apply gradient to highlighted text */
    .highlight-skill,
    .highlighted-text {
        background: #3333ff;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>




