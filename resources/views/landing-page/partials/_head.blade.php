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

<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<style>
    /* =========================================================
       Landing page custom component styles
       Moved here so they load in <head> on every refresh.
    ========================================================= */
    @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap');

    body { font-family: "Montserrat", sans-serif; }
    .heading_text { font-weight: 600; }
    .service { margin-top: -30px; }
    .service-asim { height: 10.5rem !important; object-fit: cover; }

    /* Provider info */
    .provider-info { display: flex; align-items: center; gap: 8px; white-space: nowrap; }
    .provider-name { display: inline-block; line-height: 1.2; word-break: break-word; }
    .provider-name span { display: block; }

    /* Price box */
    .price-box {
        background-color: #007bff;
        font-size: 18px;
        font-weight: bold;
        color: red;
        text-align: center;
        padding: 10px 15px;
        border-radius: 10px;
        display: inline-block;
        width: 180px;
        margin: 5px 0;
    }

    /* Card polish */
    .service-box-card {
        border: 1px solid #eef0f2;
        transition: box-shadow .2s ease, transform .2s ease;
        background: #fff;
        padding: 15px;
        border-radius: 12px;
    }
    .service-box-card:hover { box-shadow: 0 10px 24px rgba(18,38,63,.08); transform: translateY(-2px); }
    .social-share img, .social-share svg { width: 28px; height: 28px; border-radius: 6px; }

    /* Stats section */
    .stats-section { background: #f8f9fa; border-radius: 8px; padding: 12px 8px; margin: 12px 0; border: 1px solid #eef0f2; }
    .stats-item { text-align: center; padding: 4px; }
    .stats-icon { width: 16px; height: 16px; margin-right: 4px; }
    .stats-value { font-weight: 600; font-size: 14px; color: #2c3e50; }
    .stats-label { font-size: 11px; color: #6c757d; margin-top: 2px; }

    /* Hero upgrade */
    .landing-hero-wrap { background: linear-gradient(160deg, #f8f9ff 0%, #f0f4ff 35%, #e8eeff 70%, #f5f7fa 100%); position: relative; overflow: hidden; }
    .landing-hero-wrap::before { content: ''; position: absolute; top: -40%; right: -20%; width: 60%; height: 80%; background: radial-gradient(ellipse, rgba(51, 51, 255, 0.06) 0%, transparent 70%); pointer-events: none; }
    .landing-hero-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 600; color: #3333ff; background: rgba(51, 51, 255, 0.1); border: 1px solid rgba(51, 51, 255, 0.2); padding: 6px 14px; border-radius: 50px; margin-bottom: 1rem; letter-spacing: 0.3px; }
    .landing-hero-title { font-size: clamp(1.75rem, 4vw, 2.5rem); font-weight: 700; line-height: 1.2; color: #1a1a2e; letter-spacing: -0.02em; }
    .landing-hero-desc { font-size: 1.05rem; line-height: 1.6; color: #4a5568; margin-top: 1rem; margin-bottom: 1.5rem; }
    .landing-hero-trust { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem 1.5rem; margin-bottom: 1.5rem; font-size: 0.9rem; color: #5a6578; }
    .landing-hero-trust span { display: inline-flex; align-items: center; gap: 6px; }
    .landing-hero-trust .trust-rating { font-weight: 700; color: #1a1a2e; }
    .landing-hero-trust .trust-dot { width: 4px; height: 4px; border-radius: 50%; background: #3333ff; opacity: 0.6; }
    .landing-live-cue { font-size: 0.85rem; }

    /* Two-path cards */
    .landing-two-path-wrap { position: relative; }
    .landing-two-path-card { border: none; border-radius: 20px; padding: 2rem; height: 100%; transition: all 0.35s cubic-bezier(0.4,0,0.2,1); background: #fff; display: flex; flex-direction: column; position: relative; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.06); }
    .landing-two-path-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #3333ff 0%, #6366f1 50%, #8b5cf6 100%); opacity: 0; transition: opacity 0.3s ease; }
    .landing-two-path-card.card-popular::before { opacity: 1; }
    .landing-two-path-card:hover { box-shadow: 0 20px 48px rgba(51,51,255,.12); transform: translateY(-6px) scale(1.01); }
    .landing-two-path-card:hover::before { opacity: 1; }
    .landing-two-path-icon-wrap { width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, rgba(51,51,255,.15) 0%, rgba(99,102,241,.12) 100%); display: inline-flex; align-items: center; justify-content: center; transition: transform 0.3s ease; }
    .landing-two-path-card:hover .landing-two-path-icon-wrap { transform: scale(1.08) rotate(-2deg); }
    .landing-two-path-badge { background: linear-gradient(135deg, #3333ff 0%, #6366f1 100%); color: #fff !important; font-weight: 600; letter-spacing: 0.5px; padding: 0.4rem 0.9rem; border-radius: 50px; box-shadow: 0 4px 14px rgba(51,51,255,.35); }
    .landing-two-path-card .btn { border-radius: 12px; padding: 0.6rem 1.5rem; font-weight: 600; transition: all 0.25s ease; }
    .landing-two-path-card:hover .btn { box-shadow: 0 8px 24px rgba(51,51,255,.3); }

    /* How it works */
    .landing-how-wrap { position: relative; }
    .landing-how-title { font-weight: 700; letter-spacing: -0.02em; color: #1a1a2e; }
    .landing-how-step { text-align: center; padding: 1.5rem 0.75rem; position: relative; z-index: 1; }
    .landing-how-step .step-num { width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(145deg, #3333ff 0%, #6366f1 100%); color: #fff; font-weight: 800; font-size: 1.25rem; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem; box-shadow: 0 8px 24px rgba(51,51,255,.35); transition: transform 0.3s ease; }
    .landing-how-step:hover .step-num { transform: scale(1.1); }
    .landing-how-step h4 { font-weight: 700; color: #1a1a2e; margin-bottom: 0.35rem; }
    .landing-how-connector { position: absolute; top: 28px; left: 50%; width: 100%; height: 3px; background: linear-gradient(90deg, transparent, rgba(51,51,255,.25), rgba(51,51,255,.25), transparent); border-radius: 2px; transform: translateX(-50%); pointer-events: none; }
    @media (max-width: 991px) { .landing-how-connector { display: none; } }

    /* Why us */
    .landing-why-wrap { position: relative; }
    .landing-why-title { font-weight: 700; letter-spacing: -0.02em; color: #1a1a2e; }
    .landing-why-item { padding: 1.5rem; border-radius: 18px; background: #fff; border: 1px solid #e8ecf0; height: 100%; transition: all 0.35s ease; position: relative; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.04); }
    .landing-why-item::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #3333ff, #6366f1); transform: scaleX(0); transform-origin: left; transition: transform 0.35s ease; border-radius: 0 0 18px 18px; }
    .landing-why-item:hover { border-color: rgba(51,51,255,.2); box-shadow: 0 12px 40px rgba(51,51,255,.1); transform: translateY(-4px); }
    .landing-why-item:hover::after { transform: scaleX(1); }
    .landing-why-item .landing-why-icon { width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, rgba(51,51,255,.12) 0%, rgba(99,102,241,.08) 100%); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem; }
    .landing-why-item h4 { font-weight: 700; color: #1a1a2e; }

    /* Recently booked + Areas we cover */
    .landing-recent-areas-wrap { position: relative; }
    .landing-recent-card { background: #fff; border-radius: 20px; padding: 1.5rem; border: 1px solid #e8ecf0; box-shadow: 0 4px 20px rgba(0,0,0,.05); }
    .landing-recent-job-item { font-size: 0.95rem; color: #374151; padding: 0.6rem 0; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; transition: background 0.2s ease; border-radius: 10px; }
    .landing-recent-job-item:last-child { border-bottom: none; }
    .landing-recent-job-item:hover { background: rgba(51,51,255,.04); }
    .landing-recent-job-item .time-ago { font-size: 0.8rem; color: #3333ff; font-weight: 600; }
    .landing-areas-card { background: #fff; border-radius: 20px; padding: 1.5rem; border: 1px solid #e8ecf0; box-shadow: 0 4px 20px rgba(0,0,0,.05); }
    .landing-areas-list { display: flex; flex-wrap: wrap; gap: 0.5rem 0.75rem; }
    .landing-areas-list span { background: linear-gradient(135deg, rgba(51,51,255,.08) 0%, rgba(99,102,241,.06) 100%); color: #3333ff; padding: 0.5rem 1rem; border-radius: 50px; font-size: 0.9rem; font-weight: 600; border: 1px solid rgba(51,51,255,.15); transition: all 0.25s ease; }
    .landing-areas-list span:hover { background: linear-gradient(135deg, #3333ff 0%, #6366f1 100%); color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(51,51,255,.25); }
    .landing-section-head { font-weight: 700; letter-spacing: -0.02em; color: #1a1a2e; margin-bottom: 0.5rem; }
    .landing-for-pros-banner { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: #fff; border-radius: 16px; padding: 2.5rem; }
    .landing-first-booking-banner { background: linear-gradient(135deg, #3333ff 0%, #2929e6 100%); color: #fff; border-radius: 12px; padding: 1rem 1.5rem; }
</style>

