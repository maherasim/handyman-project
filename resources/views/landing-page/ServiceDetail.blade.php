@extends('landing-page.layouts.default')

@php
    $sd = $serviceData['service_detail'] ?? [];
    $ogName = \Illuminate\Support\Str::limit($sd['name'] ?? __('landingpage.service'), 60);
    $ogPrice = getPriceFormat($sd['price'] ?? 0);
    $ogType = ucfirst(trim($sd['type'] ?? 'fixed'));
    $ogCity = trim($sd['city_name'] ?? '');
    $ogCountry = trim($sd['country_name'] ?? '');
    $ogLocation = trim(implode(', ', array_filter([$ogCity, $ogCountry])));
    $ogTitle = $ogName . ' | ' . $ogPrice . ' | ' . $ogType . ($ogLocation !== '' ? ' | ' . $ogLocation : '');
    $ogTitle = \Illuminate\Support\Str::limit($ogTitle, 100);
    $ogDescRaw = $sd['description'] ?? '';
    $ogDescSnippet = $ogDescRaw ? \Illuminate\Support\Str::limit(strip_tags($ogDescRaw), 100) : '';
    $ogDescription = 'Price: ' . $ogPrice . ' | Type: ' . $ogType . ($ogLocation !== '' ? ' | Location: ' . $ogLocation : '') . ($ogDescSnippet !== '' ? '. ' . $ogDescSnippet : '');
    $ogDescription = \Illuminate\Support\Str::limit($ogDescription, 256);
    $ogUrl = !empty($sd['id']) ? route('service.detail', $sd['id']) : url()->current();
    $imgUrl = null;
    if (!empty($sd['attchments'][0])) {
        $imgUrl = is_string($sd['attchments'][0]) ? $sd['attchments'][0] : ($sd['attchments'][0]['url'] ?? null);
    }
    if (!$imgUrl && !empty($sd['attchments_array'][0]['url'])) {
        $imgUrl = $sd['attchments_array'][0]['url'];
    }
    $ogImage = $imgUrl && $imgUrl !== '' ? (str_starts_with($imgUrl, 'http') ? $imgUrl : url($imgUrl)) : url('images/default.png');
    $ogImage = str_starts_with($ogImage, 'http://') ? 'https://' . substr($ogImage, 7) : $ogImage;
@endphp
@section('before_head')
    <title>{{ $ogName }} - {{ config('app.display_name', 'Frobster') }}</title>
    <meta name="description" content="{{ $ogDescription }}" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{{ $ogTitle }}" />
    <meta property="og:description" content="{{ $ogDescription }}" />
    <meta property="og:url" content="{{ $ogUrl }}" />
    <meta property="og:image" content="{{ $ogImage }}" />
    <meta property="og:image:secure_url" content="{{ $ogImage }}" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:site_name" content="{{ config('app.display_name', 'Frobster') }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $ogTitle }}" />
    <meta name="twitter:description" content="{{ $ogDescription }}" />
    <meta name="twitter:image" content="{{ $ogImage }}" />
@endsection

@section('after_head')
 
<style>
    .mt-2 {
    margin-top: 2.5rem !important;
}
    .readmore-text {
        max-height: 120px;
        overflow: hidden;
    }
    .readmore-text.expanded {
        max-height: none;
    }

    /* Service Detail Tabs Styling - Matching Job Details Design */
    .service-details-tabs {
        margin-top: 2rem;
    }
    
    .tab-navigation {
        background: #f8f9fa;
        border-radius: 8px 8px 0 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .tab-btn {
        position: relative;
        z-index: 1;
        border-radius: 0 !important;
        width: 100%;
        padding: 14px 12px;
        border: none !important;
        border-bottom: 3px solid transparent !important;
        background: #f8f9fa !important;
        color: #6c757d !important;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s ease;
        cursor: pointer;
        outline: none !important;
        box-shadow: none !important;
    }
    
    .tab-btn:focus {
        outline: none !important;
        box-shadow: none !important;
        border: none !important;
        border-bottom: 3px solid transparent !important;
    }
    
    .tab-btn:active {
        outline: none !important;
        box-shadow: none !important;
        border: none !important;
        border-bottom: 3px solid transparent !important;
    }
    
    .tab-btn:hover {
        background: #e9ecef !important;
        color: #495057 !important;
        border-bottom-color: #007bff !important;
        outline: none !important;
        box-shadow: none !important;
    }
    
    .tab-btn.active {
        background: #fff !important;
        color: #007bff !important;
        border-bottom-color: #007bff !important;
        font-weight: 600;
        box-shadow: 0 -2px 4px rgba(0,0,0,0.1) !important;
        outline: none !important;
    }
    
    .tab-btn.active:focus {
        outline: none !important;
        box-shadow: 0 -2px 4px rgba(0,0,0,0.1) !important;
    }
    
    .tab-content {
        display: none;
        background: #fff;
        padding: 24px 0;
        animation: fadeIn 0.3s ease-in-out;
    }
    
    .tab-content.active {
        display: block;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .tab-content-container {
        background: #fff;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
        border-top: none;
    }
    
    .service-content, .cancellation-content {
        line-height: 1.7;
        color: #495057;
        font-size: 15px;
    }
    
    .service-content p, .cancellation-content p {
        margin-bottom: 1.2rem;
    }
    
    .service-content ul, .service-content ol, .cancellation-content ul, .cancellation-content ol {
        margin-bottom: 1.2rem;
        padding-left: 1.8rem;
    }
    
    .service-content li, .cancellation-content li {
        margin-bottom: 0.6rem;
    }
    
    .service-content h1, .service-content h2, .service-content h3, .service-content h4, .service-content h5, .service-content h6,
    .cancellation-content h1, .cancellation-content h2, .cancellation-content h3, .cancellation-content h4, .cancellation-content h5, .cancellation-content h6 {
        color: #212529;
        margin-bottom: 1rem;
        margin-top: 1.5rem;
    }
    
    .service-content h1:first-child, .service-content h2:first-child, .service-content h3:first-child,
    .cancellation-content h1:first-child, .cancellation-content h2:first-child, .cancellation-content h3:first-child {
        margin-top: 0;
    }
    
    .no-content {
        text-align: center;
        color: #6c757d;
        font-style: italic;
        padding: 2rem 0;
    }
    
    /* Override Bootstrap button defaults */
    .service-details-tabs button.tab-btn {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        background: #f8f9fa !important;
        color: #6c757d !important;
    }
    
    .service-details-tabs button.tab-btn:focus,
    .service-details-tabs button.tab-btn:active,
    .service-details-tabs button.tab-btn:focus-visible {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        background: #f8f9fa !important;
        color: #6c757d !important;
    }
    
    .service-details-tabs button.tab-btn.active {
        background: #fff !important;
        color: #007bff !important;
        border-bottom: 3px solid #007bff !important;
        box-shadow: 0 -2px 4px rgba(0,0,0,0.1) !important;
    }


                            /* Prevent flash/blink on provider details heading */
                            .provider-details-header-wrapper {
                                opacity: 1 !important;
                                visibility: visible !important;
                                display: flex !important;
                                transition: none !important;
                                animation: none !important;
                            }
                            
                            .provider-details-heading {
                                opacity: 1 !important;
                                visibility: visible !important;
                                display: block !important;
                                transition: none !important;
                                animation: none !important;
                                will-change: auto !important;
                            }
                            
                            /* Ensure no parent elements are hiding it */
                            .bg-light.pl-5.pr-5,
                            .bg-light.pl-5.pr-5 > * {
                                opacity: 1 !important;
                                visibility: visible !important;
                            }

                            .provider-info-section {
                                margin-top: 1.5rem;
                                width: 100%;
                                max-width: 100%;
                                overflow: hidden;
                                box-sizing: border-box;
                            }

        .info-card {
            background: #fff;
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.05);
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        .info-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        /* ... header styles ... */

        .info-card-body {
            padding: 20px !important;
            background: #fff !important;
            border-radius: 0 0 16px 16px !important;
            flex: 1;
            word-wrap: break-word;
            overflow-wrap: break-word;
            overflow: hidden;
            box-sizing: border-box;
            color: #495057;
        }

        .info-row {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 8px 12px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 140px;
            max-width: 100%;
            flex: 0 1 auto;
            display: flex;
            align-items: center;
            font-size: 14px;
        }

        .info-label i {
            color: #6c757d;
            margin-right: 6px;
        }

        .info-value {
            color: #212529;
            flex: 1 1 200px;
            min-width: 0;
            max-width: 100%;
            font-size: 14px;
            word-break: break-word;
            overflow-wrap: anywhere;
            line-height: 1.55;
        }

        /* Long provider fields (mobility, availability) — not single-line pills */
        .info-value-long {
            display: block;
            width: 100%;
            flex: 1 1 100%;
        }

        .info-value .badge.bg-info,
        .info-value .badge.bg-primary {
            white-space: normal;
            word-break: break-word;
            overflow-wrap: anywhere;
            max-width: 100%;
            text-align: left;
            line-height: 1.5;
            font-weight: 500;
        }

        .provider-field-prose {
            display: block;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            word-break: break-word;
            overflow-wrap: anywhere;
            line-height: 1.55;
            font-size: 14px;
        }

        .location-badge {
            display: inline-block;
            padding: 6px 12px;
            background: linear-gradient(135deg, rgba(255, 0, 0, 0.1) 0%, rgba(95, 96, 185, 0.1) 100%);
            border: 1px solid rgba(255, 0, 0, 0.2);
            border-radius: 20px;
            color: #495057;
            font-size: 13px;
            font-weight: 500;
        }

        .language-badge {
            display: inline-block;
            padding: 6px 12px;
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 20px;
            color: #0066cc;
            font-size: 13px;
            font-weight: 500;
        }

        .skill-badge {
            display: inline-block;
            padding: 6px 12px;
            background: linear-gradient(135deg, rgba(255, 0, 0, 0.1) 0%, rgba(95, 96, 185, 0.1) 100%);
            border: 1px solid rgba(255, 0, 0, 0.2);
            border-radius: 20px;
            color: #495057;
            font-size: 13px;
            font-weight: 500;
            margin: 4px 4px 4px 0;
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            max-width: 100%;
            min-width: 0;
            line-height: 1.4;
            box-sizing: border-box;
            overflow: hidden;
            hyphens: auto;
            -webkit-hyphens: auto;
            -ms-hyphens: auto;
            flex-shrink: 1;
        }

        .skill-badge:hover {
            background: linear-gradient(135deg, rgba(255, 0, 0, 0.15) 0%, rgba(95, 96, 185, 0.15) 100%);
            border-color: rgba(255, 0, 0, 0.3);
            transform: translateY(-1px);
        }

        .diploma-content {
            color: #e0e0e0;
            line-height: 1.6;
            font-size: 14px;
            padding: 12px;
            background: #2b2d3c; /* Darker inner background */
            border-radius: 8px;
            border-left: 3px solid #3333ff;
        }

                            .skills-list {
                                display: flex;
                                flex-wrap: wrap;
                                gap: 8px;
                                word-wrap: break-word;
                                overflow-wrap: break-word;
                                overflow: hidden;
                                width: 100%;
                                box-sizing: border-box;
                            }

                            @media (max-width: 768px) {
                                .info-row {
                                    flex-direction: column;
                                    gap: 4px;
                                }

                                .info-label {
                                    min-width: auto;
                                }

                                .skill-badge {
                                    font-size: 12px;
                                    padding: 5px 10px;
                                    max-width: calc(100% - 8px);
                                }

                                .skills-list {
                                    gap: 6px;
                                }
                            }

    /* Service CTA card - same style as job details "Ready to win this project?" */
    .service-cta-card {
        background: linear-gradient(145deg, #5F60BA 0%, #4a4b9e 50%, #3d3e85 100%);
        border-radius: 16px;
        padding: 0;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(95, 96, 186, 0.35), 0 4px 12px rgba(0,0,0,0.08);
        border: none;
        position: relative;
    }
    .service-cta-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.6;
        pointer-events: none;
    }
    .service-cta-card .cta-inner { position: relative; z-index: 1; padding: 1.5rem 1.35rem; }
    .service-cta-card .cta-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,0.2); color: #fff; font-size: 11px; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.05em; padding: 6px 12px; border-radius: 20px; margin-bottom: 1rem;
    }
    .service-cta-card .cta-headline { color: #fff; font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; line-height: 1.35; text-shadow: 0 1px 2px rgba(0,0,0,0.1); }
    .service-cta-card .cta-stats { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 1.1rem; }
    .service-cta-card .cta-stat {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,0.18); color: #fff; font-size: 12px; font-weight: 500;
        padding: 8px 12px; border-radius: 10px; backdrop-filter: blur(6px);
    }
    .service-cta-card .cta-stat i { opacity: 0.95; font-size: 13px; }
    .service-cta-card .cta-trust { color: rgba(255,255,255,0.88); font-size: 12px; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .service-cta-card .cta-trust span { display: inline-flex; align-items: center; gap: 4px; }
    .service-cta-card .cta-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
        padding: 14px 20px; background: #fff; color: #5F60BA; font-weight: 700; font-size: 15px;
        border-radius: 12px; text-decoration: none; border: none; box-shadow: 0 4px 14px rgba(0,0,0,0.15);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .service-cta-card .cta-btn:hover { color: #4a4b9e; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); }
    .service-cta-card .cta-btn:active { transform: translateY(0); }

</style>
@endsection

@section('content')

    @if (!isset($serviceData['service_detail']) || empty($serviceData['service_detail']))
        <div class="section-padding">
            <div class="container">
                <div class="alert alert-danger">
                    <h4>{{ __('landingpage.sd_service_not_found') }}</h4>
                    <p>{{ __('landingpage.sd_service_not_found_desc') }}</p>
                    <a href="{{ route('service.list') }}" class="btn btn-primary">{{ __('landingpage.sd_back_to_services') }}</a>
                </div>
            </div>
        </div>
    @else
    <div class="section-padding service-detail">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 pe-xxl-5">
                    <h3 class="text-capitalize mb-2">{{ $serviceData['service_detail']['name'] ?? __('landingpage.service') }}</h3>
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <ul class="service-meta-list list-inline m-0 d-flex align-items-center flex-wrap">
                            <li>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($serviceData['service_detail']['total_rating'] > 0 && $serviceData['service_detail']['total_review'] > 0)
                                        <span class="ratting">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14"
                                                viewBox="0 0 16 14" fill="none">
                                                <path
                                                    d="M8.3819 0.452137L10.0821 3.74897C10.2074 3.98783 10.4465 4.1537 10.7222 4.19056L14.5412 4.72726C14.7642 4.75748 14.9666 4.87102 15.1033 5.04426C15.2385 5.2153 15.2966 5.43204 15.2637 5.64509C15.237 5.82202 15.1507 5.98569 15.0186 6.11101L12.2513 8.69938C12.0489 8.88 11.9573 9.14761 12.0061 9.40932L12.6874 13.0482C12.76 13.4876 12.4583 13.9019 12.0061 13.9852C11.8198 14.014 11.6288 13.9838 11.4608 13.9012L8.05423 12.1886C7.80141 12.0655 7.50277 12.0655 7.24995 12.1886L3.8434 13.9012C3.42484 14.1157 2.90622 13.9697 2.67326 13.5716C2.58695 13.4131 2.5564 13.2325 2.58466 13.0563L3.26597 9.41669C3.31485 9.15572 3.22243 8.88664 3.02079 8.70602L0.25354 6.11912C-0.0756579 5.81244 -0.0855873 5.30745 0.23139 4.98971C0.238264 4.98307 0.245902 4.9757 0.25354 4.96833C0.384914 4.83931 0.557533 4.75748 0.7439 4.7361L4.5629 4.19867C4.83787 4.16108 5.07694 3.99668 5.20297 3.75634L6.84208 0.452137C6.98797 0.169045 7.29043 -0.00714949 7.61887 0.000222678H7.72122C8.00611 0.0333974 8.25435 0.203695 8.3819 0.452137Z"
                                                    fill="currentColor" />
                                            </svg>
                                        </span>
                                        <h6>{{ round($serviceData['service_detail']['total_rating'], 1) }}<span
                                                class="text-body"> <a
                                                    href="{{ route('rating.all', ['service_id' => $serviceData['service_detail']['id']]) }}">
                                                    ({{ $serviceData['service_detail']['total_review'] }}
                                                    {{ __('messages.reviews') }})</span></a></h6>
                                    @endif

                                    <span class="ms-3 d-inline-flex align-items-center" title="{{ __('landingpage.sl_views') }}">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns=" "><path d="M12 5c-7.633 0-10 7-10 7s2.367 7 10 7 10-7 10-7-2.367-7-10-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Zm0-8a3 3 0 1 0 .002 6.002A3 3 0 0 0 12 9Z" fill="currentColor"/></svg>
                                        <span class="ms-1">{{ $serviceData['service_detail']['total_views'] ?? 0 }}</span>
                                    </span>
                                </div>
                            </li>
                            @if (!empty($serviceData['service_detail']['duration']))
                                <li>
                                    <h6 class="text-body">
                                        
                                        <div>
                                            <div>
                                                {{ $serviceData['service_detail']['city_name'] ?? 'City' }} -
                                                {{ $serviceData['service_detail']['country_name'] ?? 'Country' }}
                                            </div>
                                            
                                        </div>
                                    </h6>
                                </li>
                            @endif
                        </ul>
                        <div>
                            <span class="text-capitalize">{{ __('landingpage.created_by') }}: </span>
                            <a class="d-inline-block text-capitalize m-0"
                                href="{{ route('provider.detail', $serviceData['provider']['id']) }}">{{ $serviceData['provider']['display_name'] }}</a>
                        </div>
                    </div>
                    @if (!empty($serviceData['service_detail']['attchments']))
                        <div class="mt-5">
                            <section-thumbnail-section
                                :attachments="{{ json_encode($serviceData['service_detail']['attchments']) }}"></section-thumbnail-section>
                        </div>
                    @else
                        <img src="{{ asset('images/default.png') }}" alt=""
                            class="img-fluid object-cover rounded-3 mt-4 w-100" />
                    @endif
                    @if (!empty($serviceData['service_detail']['description']))
                        <div class="mt-5 pt-lg-5 pt-3">
                            <h5 class="mb-3">{{ __('landingpage.sd_minimum_booking') }}</h5>
                            <p class="m-0">
                                {{ $serviceData['service_detail']['minimum_booking'] }}

                            </p>
                        </div>
                    @endif

                    <!-- Tabbed Service Information Section -->
                    <div class="service-details-tabs mt-4">
                        <!-- Tab Navigation -->
                        <div class="tab-navigation mb-4">
                            <div class="row g-0">
                                <div class="col-4">
                                    <button class="tab-btn active" data-tab="about-services">
                                        {{ __('landingpage.sd_tab_about_services') }}
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button class="tab-btn" data-tab="about-provider">
                                        {{ __('landingpage.sd_tab_about_provider') }}
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button class="tab-btn" data-tab="cancellation-policy">
                                        {{ __('landingpage.sd_tab_cancellation_policy') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content-container">
                            <!-- About Services Tab -->
                            <div class="tab-content active" id="about-services-content">
                                <div class="px-4">
                    @if (!empty($serviceData['service_detail']['description']))
                                        <div class="service-content">
                                {!! $serviceData['service_detail']['description'] !!}
                                        </div>
                                    @else
                                        <div class="no-content text-muted text-center py-4">
                                            <i class="fas fa-info-circle me-2"></i>
                                            {{ __('landingpage.sd_no_description') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- About Provider Tab -->
                            <div class="tab-content" id="about-provider-content" style="display: none;">
                                <div class="px-4">
                                    <div class="about-provider-box">
                                        <div class="mb-4 pb-4 border-bottom d-flex align-items-sm-center aling-items-start flex-sm-row flex-column gap-5">
                                            <div class="flex-shrink-0 provider-image-container">
                                                <img src="{{ $serviceData['provider']['profile_image'] }}" alt="provider-user"
                                                    class="img-fluid w-100">
                                            </div>
                                            <div>
                                                <a href="{{ route('provider.detail', $serviceData['provider']['id']) }}">
                                                    <h5 class="text-capitalize mb-1">{{ $serviceData['provider']['display_name'] }}</h5>
                                                </a>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ asset($providerPlanIcon) }}" alt="plan" style="width: 26px; height: 26px;">
                                                    @php
                                                        $aboutVerified = function_exists('verify_provider_document') ? verify_provider_document($serviceData['provider']['id']) : false;
                                                    @endphp
                                                    @if ($aboutVerified)
                                                        <img src="{{ asset('images/icon/verifiedpng.png') }}" alt="verified" style="width: 26px; height: 26px;">
                                                    @else
                                                        <img src="{{ asset('images/icon/notverifiedpng.png') }}" alt="not verified" style="width: 26px; height: 26px;">
                                                    @endif
                                                </div>

                                                @php
                                                    $provDesignation = $serviceData['provider']['designation'] ?? '';
                                                    $provCity = $serviceData['provider']['city_name'] ?? data_get($serviceData['provider'], 'city.name');
                                                    $provCountry = $serviceData['provider']['country_name'] ?? data_get($serviceData['provider'], 'country.name');
                                                @endphp
                                                <div class="d-flex align-items-center gap-2 mt-1">
                                                    @if(!empty($provDesignation))
                                                        <span class="text-body">{{ $provDesignation }}</span>
                                                    @endif
                                                    @if(!empty($provCity) || !empty($provCountry))
                                                        <span class="text-body">• {{ $provCity }}{{ !empty($provCity) && !empty($provCountry) ? ', ' : '' }}{{ $provCountry }}</span>
                                                    @endif
                                                </div>

                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <div class="star-rating">
                                                        <rating-component :readonly="true" :showrating="false" :ratingvalue="{{ $serviceData['provider']['providers_service_rating'] }}" />
                                                    </div>
                                                    <h6 class="lh-sm">{{ round($serviceData['provider']['providers_service_rating'], 1) }}</h6>
                                                    <a href="{{ route('rating.all', ['provider_id' => $serviceData['provider']['id']]) }}">({{ $serviceData['provider']['total_service_rating'] }} {{ __('messages.reviews') }})</a>
                                                </div>

                                                <p class="mt-3 mb-0">
                                                    {{ $serviceData['provider']['description'] }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-sm-6">
                                                <h6 class="mb-1">{{ __('landingpage.member_since') }}:</h6>
                                                <p class="m-0">{{ date("$date_time->date_format", strtotime($serviceData['provider']['created_at'])) }}</p>
                                            </div>
                                            <div class="col-md-4 col-sm-6 mt-sm-0 mt-3">
                                                <h6 class="mb-1">{{ __('landingpage.total_services') }}:</h6>
                                                <p class="m-0">{{ $serviceData['provider']['total_services'] ?? 0 }}</p>
                                            </div>
                                            <div class="col-md-4 col-sm-6 mt-sm-0 mt-3">
                                                <h6 class="mb-1">{{ __('landingpage.total_bookings') }}:</h6>
                                                <p class="m-0">{{ $serviceData['provider']['total_bookings'] ?? 0 }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cancellation Policy Tab -->
                            <div class="tab-content" id="cancellation-policy-content" style="display: none;">
                                <div class="px-4">
                                    @if (!empty($serviceData['service_detail']['cancellation_policy']))
                                        <div class="cancellation-content">
                                            {!! $serviceData['service_detail']['cancellation_policy'] !!}
                                        </div>
                                    @else
                                        <div class="no-content text-muted text-center py-4">
                                            <i class="fas fa-file-contract me-2"></i>
                                            No cancellation policy available.
                        </div>
                    @endif
                                </div>
                            </div>
                        </div>
                    </div>



                    @if ($serviceData['serviceaddon'])
                        <div class="mt-5 pt-lg-5 pt-3">
                            <h5 class="mb-3">{{ __('landingpage.Add-ons') }}</h5>
                            @foreach ($serviceData['serviceaddon'] as $serviceaddon)
                                <div
                                    class="mb-4 pb-4 border-bottom d-flex align-items-sm-center aling-items-center justify-content-between flex-sm-row flex-column gap-2">
                                    <div class="d-flex align-items-sm-center flex-sm-row flex-column gap-3">
                                        <div class="flex-shrink-0 provider-image-container">
                                            @if (isset($serviceaddon['serviceaddon_image']) && $serviceaddon['serviceaddon_image'])
                                                <img src="{{ $serviceaddon['serviceaddon_image'] }}" alt="service-image"
                                                    class="img-fluid object-fit-cover" style="width: 100px; height:100px;">
                                            @else
                                                <img src="{{ asset('images/default.png') }}" alt="placeholder-image"
                                                    class="img-fluid object-fit-cover" style="width: 100px; height:100px;">
                                            @endif
                                        </div>
                                        <div>
                                            <h5 class="text-capitalize mb-1">{{ $serviceaddon['name'] }}</h5>
                                            <h6>{{ getPriceFormat($serviceaddon['price']) }}</h6>
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input service-addon-checkbox" type="checkbox"
                                            value="" id="serviceaddon" data-addon-id="{{ $serviceaddon['id'] }}"
                                            style="width: 20px; height: 20px; background-color: white; border: 2px solid #000;">

                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if ($serviceData['service_detail']['price'] > 0)
                        <div class="mt-5 pt-lg-5 pt-3">
                            <h5 class="mb-3 text-capitalize">{{ __('landingpage.order_detail') }}</h5>
                            <div class="p-5 border rounded-3">
                                <h6 class="mb-1">{{ __('messages.service') }}</h6>
                                <p class="m-0 text-capitalize">{{ $serviceData['service_detail']['name'] }}
                                </p>

                                <div class="mt-5 border-top">
                                    <div class="table-responsive">
                                        <table class="table mb-5">
                                            <tbody>
                                                <tr>
                                                    <td class="ps-0 py-2">
                                                        <label class="text-capitalize">
                                                            <h6>{{ __('messages.price') }}</h6>
                                                        </label>
                                                    </td>
                                                    <td class="pe-0 py-2 text-end">
                                                        <h6 class="text-primary">
                                                            +{{ getPriceFormat($serviceData['service_detail']['price']) }}
                                                        </h6>
                                                    </td>
                                                </tr>
                                                @if (!empty($serviceData['service_detail']['discount']))
                                                    <tr>
                                                        <td class="ps-0 py-2">
                                                            <label class="text-capitalize">
                                                                <h6>{{ __('messages.discount') }} <span
                                                                        class="text-success">({{ $serviceData['service_detail']['discount'] }}%
                                                                        Off)</span></h6>
                                                            </label>
                                                        </td>
                                                        <td class="pe-0 py-2 text-end">
                                                            <span
                                                                class="text-success">-{{ getPriceFormat(($serviceData['service_detail']['price'] * $serviceData['service_detail']['discount']) / 100) }}</span>
                                                        </td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td class="ps-0 py-2">
                                                        <h6 class="text-capitalize m-0">{{ __('messages.Subtotal') }}</h6>
                                                    </td>
                                                    <td class="pe-0 py-2 text-end">

                                                        <h6 class="text-primary">{{ getPriceFormat($subtotal) }}</h6>
                                                    </td>
                                                </tr>
                                                @php

                                                    $total = $subtotal;
                                                @endphp

                                                @php
                                                    $totalTaxAmount = 0;
                                                @endphp

                                                @if (!empty($serviceData['taxes']))
                                                    @php

                                                        foreach ($serviceData['taxes'] as $tax) {
                                                            if ($tax['type'] == 'percent') {
                                                                $totalTaxAmount += ($subtotal * $tax['value']) / 100;
                                                            } else {
                                                                $totalTaxAmount += $tax['value'];
                                                            }
                                                        }
                                                        $total = $subtotal + $totalTaxAmount;
                                                    @endphp
                                                @endif

                                                <tr>
                                                    <td class="ps-0 py-2">
                                                        <label class="text-capitalize">
                                                            <h6>{{ __('messages.tax') }}</h6>
                                                        </label>
                                                    </td>
                                                    <td class="pe-0 py-2 text-end">
                                                        @if ($totalTaxAmount > 0)
                                                            <span class="text-danger"><i type="button"
                                                                    class="fa fa-info-circle text-body" aria-hidden="true"
                                                                    data-bs-toggle="modal" data-bs-target="#taxModal"></i>
                                                                +{{ getPriceFormat($totalTaxAmount) }}</span>
                                                        @else
                                                            <span class="text-danger"> +{{ getPriceFormat(0) }}</span>
                                                        @endif
                                                    </td>
                                                </tr>


                                                <div class="modal fade" id="taxModal" aria-labelledby="taxModalLabel"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title text-capitalize" id="taxModalLabel">
                                                                    {{ __('messages.applied_taxes') }}</h5>
                                                                <span class="text-primary custom-btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="40"
                                                                        height="41" viewBox="0 0 40 41" fill="none">
                                                                        <rect x="12" y="11.8381" width="17"
                                                                            height="17" fill="white"></rect>
                                                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                                                            d="M12.783 4.17017H27.233C32.883 4.17017 36.6663 8.13683 36.6663 14.0368V27.6552C36.6663 33.5385 32.883 37.5035 27.233 37.5035H12.783C7.13301 37.5035 3.33301 33.5385 3.33301 27.6552V14.0368C3.33301 8.13683 7.13301 4.17017 12.783 4.17017ZM25.0163 25.8368C25.583 25.2718 25.583 24.3552 25.0163 23.7885L22.0497 20.8218L25.0163 17.8535C25.583 17.2885 25.583 16.3552 25.0163 15.7885C24.4497 15.2202 23.533 15.2202 22.9497 15.7885L19.9997 18.7535L17.033 15.7885C16.4497 15.2202 15.533 15.2202 14.9663 15.7885C14.3997 16.3552 14.3997 17.2885 14.9663 17.8535L17.933 20.8218L14.9663 23.7718C14.3997 24.3552 14.3997 25.2718 14.9663 25.8368C15.2497 26.1202 15.633 26.2718 15.9997 26.2718C16.383 26.2718 16.7497 26.1202 17.033 25.8368L19.9997 22.8885L22.9663 25.8368C23.2497 26.1385 23.6163 26.2718 23.983 26.2718C24.3663 26.2718 24.733 26.1202 25.0163 25.8368Z"
                                                                            fill="currentColor">
                                                                        </path>
                                                                    </svg>
                                                                </span>
                                                            </div>
                                                            <div class="modal-body">
                                                                @if (!empty($serviceData['taxes']))
                                                                    @foreach ($serviceData['taxes'] as $tax)
                                                                        <div class="d-flex justify-content-between">
                                                                            <p>{{ $tax['title'] }}</p>
                                                                            @if ($tax['type'] == 'percent')
                                                                                <p>{{ getPriceFormat(($tax['value'] * $subtotal) / 100) }}
                                                                                </p>
                                                                            @else
                                                                                <p>{{ getPriceFormat($tax['value']) }}</p>
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-end">
                                        @if (auth()->check() && auth()->user()->user_type == 'user')
                                            <a href="{{ route('book.service', ['id' => $serviceData['service_detail']['id']]) }}"
                                                class="btn btn-lg btn-primary continue-button">{{ __('messages.continue') }}({{ getPriceFormat($total) }})</a>
                                        @else
                                            <a href="{{ route('login', ['service_id' => $serviceData['service_detail']['id']]) }}"
                                                class="btn btn-lg btn-primary">{{ __('messages.continue') }}({{ getPriceFormat($total) }})</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (!empty($serviceData['service_detail']['servicePackage']))
                        <div class="mt-5 pt-lg-5 pt-3">
                            <div class="row align-item-center">
                                <div class="col-sm-6">
                                    <h5 class="text-capitalize">{{ __('landingpage.select_service_package') }}</h5>
                                </div>
                                @if (count($serviceData['service_detail']['servicePackage']) > 3)
                                    <div class="col-sm-6 mt-sm-0 mt-3 text-sm-end">
                                        <a
                                            href="{{ route('service.package', ['id' => $serviceData['service_detail']['id']]) }}">{{ __('messages.view_all') }}</a>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-5">
                                <div class="position-relative overflow-hidden swiper swiper-general our-service">
                                    @auth
                                        <service-package-section
                                            :servicepackage="{{ json_encode($serviceData['service_detail']['servicePackage']) }}"
                                            :service_id="{{ $serviceData['service_detail']['id'] }}"
                                            :auth_user_id="{{ auth()->id() }}"></service-package-section>
                                    @else
                                        <service-package-section
                                            :servicepackage="{{ json_encode($serviceData['service_detail']['servicePackage']) }}"
                                            :service_id="{{ $serviceData['service_detail']['id'] }}"
                                            :auth_user_id="null"></service-package-section>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (!empty($serviceData['service_faq']))
                        <div class="mt-5 pt-lg-5 pt-3">
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="text-capitalize">{{ __('landingpage.any_question') }}</h5>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="service-accordian accordion" id="service-accordion">
                                        @foreach ($serviceData['service_faq'] as $service_faq)
                                            <div class="accordion-item">
                                                <div class="accrodion-title collapsed" data-bs-toggle="collapse"
                                                    data-bs-target="#q-{{ $service_faq['id'] }}" aria-expanded="false"
                                                    aria-controls="q-{{ $service_faq['id'] }}">
                                                    <div class="d-flex gap-2">
                                                        <h6 class="question text-primary flex-shrink-0">Q:</h6>
                                                        <h6 class="title m-0">{{ $service_faq['title'] }}</h6>
                                                    </div>
                                                    <span class="icon-accrodion icon-inactive">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="14"
                                                            width="14" viewBox="0 0 448 512">
                                                            <path
                                                                d="M416 208H272V64c0-17.7-14.3-32-32-32h-32c-17.7 0-32 14.3-32 32v144H32c-17.7 0-32 14.3-32 32v32c0 17.7 14.3 32 32 32h144v144c0 17.7 14.3 32 32 32h32c17.7 0 32-14.3 32-32V304h144c17.7 0 32-14.3 32-32v-32c0-17.7-14.3-32-32-32z"
                                                                fill="currentColor" />
                                                        </svg>
                                                    </span>
                                                    <span class="icon-accrodion icon-active">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="14"
                                                            width="14" viewBox="0 0 448 512">
                                                            <path
                                                                d="M416 208H32c-17.7 0-32 14.3-32 32v32c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32v-32c0-17.7-14.3-32-32-32z"
                                                                fill="currentColor" />
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div id="q-{{ $service_faq['id'] }}" class="accordion-collapse collapse"
                                                    data-bs-parent="#service-accordion">
                                                    <div class="accordion-body">{{ $service_faq['description'] }}</div>
                                                </div>
                                            </div>
                                        @endforeach


                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($total_ratings_count > 0)
                        <div class="section-padding px-0 pb-0">
                            <div class="row align-items-center mb-5">
                                <div class="col-sm-9">
                                    <h5 class="mb-0">{{ $total_ratings_count }} {{ __('landingpage.reviews_for') }}
                                        {{ $serviceData['service_detail']['name'] }}</h5>
                                </div>
                                @if ($total_ratings_count !== 0)
                                    <div class="col-sm-3 mt-sm-0 mt-3 text-sm-end">
                                        <a
                                            href="{{ route('rating.all', ['service_id' => $serviceData['service_detail']['id']]) }}">{{ __('messages.view_all') }}</a>
                                    </div>
                                @endif
                            </div>

                            <ul class="comment-list list-inline m-0">
                                @foreach ($serviceData['rating_data'] as $ratingData)
                                    <li class="comment mb-5 pb-5 border-bottom">
                                        <div class="comment-box">
                                            <div
                                                class="d-flex align-items-sm-center align-items-start flex-sm-row flex-column justify-content-between gap-3">
                                                <div
                                                    class="d-inline-flex align-items-sm-center align-items-start flex-sm-row flex-column gap-3">
                                                    <div class="user-image flex-shrink-0">
                                                        <img src="{{ $ratingData['profile_image'] }}"
                                                            class="avatar-70 object-cover rounded-circle"
                                                            alt="comment-user" />
                                                    </div>
                                                    <div class="comment-user-info">
                                                        <h6 class="font-size-18 text-capitalize mb-2">
                                                            {{ $ratingData['customer_name'] }}</h6>
                                                        <span class="text-primary">
                                                            <rating-component :readonly=true :showrating="false"
                                                                :ratingvalue="{{ $ratingData['rating'] }}" />
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="date text-capitalize">
                                                    {{ date("$date_time->date_format", strtotime($ratingData['created_at'])) }}
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <p class="commnet-content m-0">
                                                    {{ $ratingData['review'] }}
                                                </p>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach

                            </ul>
                        </div>
                    @endif
                    

                    <div class="mt-5 pt-lg-5 pt-3">
                        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                            <h5 class="text-capitalize">{{ __('landingpage.related_services') }}</h5>
                            <a
                                href="{{ route('service.list', ['category_id' => $serviceData['service_detail']['category_id']]) }}">{{ __('messages.view_all') }}</a>

                        </div>
                        <div class="position-relative overflow-hidden swiper swiper-general our-service" data-slide="2"
                            data-laptop="2" data-tab="2" data-mobile="1" data-mobile-sm="1" data-autoplay="true"
                            data-loop="true" data-navigation="false" data-pagination="false">

                            <remote-service-page
                                link="{{ route('category-detail-service.data', ['id' => $serviceData['service_detail']['category_id'], 'limit' => 3]) }}"></remote-service-page>

                            <!-- <landing-servicedetailsection-section
                                        :service="{{ json_encode($serviceData['related_service']) }}"
                                        :user_id="{{ $userId }}"
                                        :favourite="{{ json_encode($favouriteServiceData) }}"></landing-servicedetailsection-section> -->
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 ps-xxl-5 mt-lg-0 mt-5">
                    <div class="bg-light p-5 rounded-3">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                            <div class="div">
                                {{-- <h5 class="font-size-14 m-0">{{$serviceData['service_detail']['name']}}</h5> --}}

                                <h4 class="mt-2 text-primary">
                                    {{ getPriceFormat($serviceData['service_detail']['price']) }} /
                                    {{ $serviceData['service_detail']['type'] }}</h4>

                            </div>
                            <div class="flex-shrink-0">
                                @if (auth()->check() && auth()->user()->hasRole('user'))
                                    @if (empty($favouriteService))
                                        <form method="POST" id="favoriteForm">
                                            @csrf

                                            <input type="hidden" name="service_id" class="service_id"
                                                value="{{ $serviceData['service_detail']['id'] }}"
                                                data-service-id="{{ $serviceData['service_detail']['id'] }}">
                                            @if (!empty(auth()->user()))
                                                <input type="hidden" name="user_id" id="user_id"
                                                    value="{{ Auth::user()->id }}">
                                            @endif

                                            <button type="button"
                                                class="btn btn-light bg-white rounded-circle serv-whishlist text-primary p-0 avatar-30 save_fav">
                                                <svg width="16" height="16" viewBox="0 0 12 13" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M1.43593 6.29916C0.899433 4.62416 1.52643 2.70966 3.28493 2.14316C4.20993 1.84466 5.23093 2.02066 5.99993 2.59916C6.72743 2.03666 7.78593 1.84666 8.70993 2.14316C10.4684 2.70966 11.0994 4.62416 10.5634 6.29916C9.72843 8.95416 5.99993 10.9992 5.99993 10.9992C5.99993 10.9992 2.29893 8.98516 1.43593 6.29916Z"
                                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path d="M8 3.84998C8.535 4.02298 8.913 4.50048 8.9585 5.06098"
                                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" id="favoriteForm">
                                            @csrf

                                            <input type="hidden" name="service_id" class="service_id"
                                                value="{{ $serviceData['service_detail']['id'] }}"
                                                data-service-id="{{ $serviceData['service_detail']['id'] }}">
                                            @if (!empty(auth()->user()))
                                                <input type="hidden" name="user_id" id="user_id"
                                                    value="{{ Auth::user()->id }}">
                                            @endif
                                            <button type="button"
                                                class="btn btn-light bg-white rounded-circle serv-whishlist text-primary p-0 avatar-30 delete_fav">
                                                <svg width="16" height="16" viewBox="0 0 12 13"
                                                    fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M1.43593 6.29916C0.899433 4.62416 1.52643 2.70966 3.28493 2.14316C4.20993 1.84466 5.23093 2.02066 5.99993 2.59916C6.72743 2.03666 7.78593 1.84666 8.70993 2.14316C10.4684 2.70966 11.0994 4.62416 10.5634 6.29916C9.72843 8.95416 5.99993 10.9992 5.99993 10.9992C5.99993 10.9992 2.29893 8.98516 1.43593 6.29916Z"
                                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round"></path>
                                                    <path d="M8 3.84998C8.535 4.02298 8.913 4.50048 8.9585 5.06098"
                                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <form method="GET" id="favoriteForm" action="{{ route('login') }}">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-light bg-white rounded-circle serv-whishlist text-primary p-0 avatar-30">
                                            <svg width="16" height="16" viewBox="0 0 12 13" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M1.43593 6.29916C0.899433 4.62416 1.52643 2.70966 3.28493 2.14316C4.20993 1.84466 5.23093 2.02066 5.99993 2.59916C6.72743 2.03666 7.78593 1.84666 8.70993 2.14316C10.4684 2.70966 11.0994 4.62416 10.5634 6.29916C9.72843 8.95416 5.99993 10.9992 5.99993 10.9992C5.99993 10.9992 2.29893 8.98516 1.43593 6.29916Z"
                                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                                <path d="M8 3.84998C8.535 4.02298 8.913 4.50048 8.9585 5.06098"
                                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif


                            </div>
                        </div>
                        @if (!empty($serviceData['service_detail']['description']))
                            <p class="m-0 readmore-text">
                                {{ html_entity_decode(strip_tags($serviceData['service_detail']['description'])) }}
                            </p>

                            <a href="javascript:void(0);" class="readmore-btn">{{ __('landingpage.read_more') }}</a>
                        @endif

                        <div class="mt-4 pt-4 border-top">
                            {{-- CTA card in place of Continue button --}}
                            <div class="service-cta-card mb-0">
                                <div class="cta-inner">
                                    <div class="cta-badge">
                                        <i class="fas fa-star"></i>
                                        <span>{{ __('landingpage.sd_great_opportunity') }}</span>
                                    </div>
                                    <h3 class="cta-headline mb-0">
                                        {{ __('landingpage.sd_ready_to_book') }}<br>
                                        <span style="font-size: 0.92em; opacity: 0.95;">{{ __('landingpage.sd_sign_in_book_minutes') }}</span>
                                    </h3>
                                    <div class="cta-stats">
                                        <span class="cta-stat">
                                            <i class="fas fa-map-marker-alt"></i>
                                            {{ $serviceData['service_detail']['city_name'] ?? '—' }}, {{ $serviceData['service_detail']['country_name'] ?? '—' }}
                                        </span>
                                        <span class="cta-stat">
                                            <i class="fas fa-wallet"></i>
                                            {{ getPriceFormat($serviceData['service_detail']['price']) }} / {{ $serviceData['service_detail']['type'] }}
                                        </span>
                                        @if(!empty($serviceData['service_detail']['duration']))
                                        <span class="cta-stat">
                                            <i class="fas fa-clock"></i>
                                            {{ $serviceData['service_detail']['duration'] }}
                                        </span>
                                        @endif
                                        <span class="cta-stat">
                                            <i class="fas fa-tag"></i>
                                            {{ $serviceData['service_detail']['type'] ?? __('messages.service') }}
                                        </span>
                                    </div>
                                    <div class="cta-trust">
                                        <span><i class="fas fa-shield-alt"></i> {{ __('landingpage.sd_secure') }}</span>
                                        <span><i class="fas fa-comments"></i> {{ __('landingpage.sd_direct_contact') }}</span>
                                        <span><i class="fas fa-calendar-check"></i> {{ __('landingpage.sd_easy_booking') }}</span>
                                    </div>
                                    <div class="cta-btn-wrap">
                                        <a href="{{ auth()->check() && auth()->user()->user_type == 'user' ? route('book.service', ['id' => $serviceData['service_detail']['id']]) : route('login', ['service_id' => $serviceData['service_detail']['id']]) }}" class="cta-btn continue-button">
                                            <i class="fas fa-rocket"></i>
                                            {{ auth()->check() && auth()->user()->user_type == 'user' ? __('landingpage.sd_book_now') : __('landingpage.sd_sign_in_and_book') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-center gap-3 mt-3">
                            @php
                                $serviceId = $serviceData['service_detail']['id'] ?? null;
                                $serviceName = Str::limit($serviceData['service_detail']['name'] ?? __('landingpage.service'), 80);
                                $servicePrice = getPriceFormat($serviceData['service_detail']['price'] ?? 0);
                                $serviceType = ucfirst($serviceData['service_detail']['type'] ?? 'service');
                                $cityName = $serviceData['service_detail']['city_name'] ?? __('landingpage.sl_city_fallback');
                                $countryName = $serviceData['service_detail']['country_name'] ?? __('landingpage.sl_country_fallback');
                                $locationText = $cityName . ', ' . $countryName;
                                $shareUrl = $serviceId ? route('service.detail', $serviceId) : url()->current();
                                $shareQuote = $serviceName . ' • ' . $servicePrice . ' • ' . $serviceType . ' • ' . $locationText;
                                $serviceImage = !empty($serviceData['service_detail']['attchments']) && isset($serviceData['service_detail']['attchments'][0]) 
                                    ? $serviceData['service_detail']['attchments'][0] 
                                    : asset('images/default.png');
                            @endphp
                            <span role="button" tabindex="0" class="social-link share-link"
                                  data-platform="facebook"
                                  data-share-url="{{ $shareUrl }}"
                                  data-quote="{{ $shareQuote }}"
                                  onclick="return window.__shareClickHandler(event, this);"
                                  style="cursor: pointer;">
                                <img src="{{ asset('assets/fb.png') }}?v=20260303"
                                    style="width: 30px; border-radius: 8px;" alt="Share on Facebook">
                            </span>
                            <span role="button" tabindex="0" class="social-link share-link"
                                  data-platform="twitter"
                                  data-share-url="{{ $shareUrl }}"
                                  data-text="{{ $shareQuote }}"
                                  onclick="return window.__shareClickHandler(event, this);"
                                  style="cursor: pointer;">
                                <img src="{{ asset('assets/twiter.png') }}?v=20260303"
                                    style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt="Share on Twitter">
                            </span>
                            <span role="button" tabindex="0" class="social-link share-link"
                                  data-platform="linkedin"
                                  data-share-url="{{ $shareUrl }}"
                                  onclick="return window.__shareClickHandler(event, this);"
                                  style="cursor: pointer;">
                                <img src="{{ asset('assets/linkedIn.png') }}?v=20260303"
                                    style="width: 30px; border-radius: 8px;" alt="Share on LinkedIn">
                            </span>
                            <span role="button" tabindex="0" class="social-link share-link"
                                  data-platform="telegram"
                                  data-share-url="{{ $shareUrl }}"
                                  data-quote="{{ $shareQuote }}"
                                  onclick="return window.__shareClickHandler(event, this);"
                                  style="cursor: pointer;">
                                <img src="{{ asset('assets/telegram.png') }}?v=20260303"
                                    style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt="Share on Telegram">
                            </span>
                        </div>
                    </div>

                    {{-- @if ($serviceData['service_detail']['is_slot'] == 1)
                        <div class="bg-light p-5 rounded-3 mt-5">
                            <h5 class="mb-2">{{ __('landingpage.available_days') }}</h5>
                            <ul class="list-inline m-0 p-0 d-flex align-items-center gap-2 flex-wrap">
                                @foreach ($serviceData['service_detail']['slots'] as $slots)
                                    <li>
                                        <span
                                            class="btn btn-sm btn-outline-primary text-capitalize cursor-default">{{ $slots['day'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif --}}


                    <div class="bg-light pl-5 pr-5 pb-0 rounded-3 mt-0 pt-0">
                        <div class="position-relative d-flex justify-content-center provider-details-header-wrapper" style="margin: auto; width: 60%;">
                            <div class="provider-details-heading" style="
                                background: #3333ff;
                                color: #fff;
                                padding: 10px 16px;
                                border-radius: 12px;
                                font-weight: 700;
                                font-size: 14px;
                                box-shadow: 0 6px 18px rgba(255, 0, 0, 0.2);
                                backdrop-filter: blur(8px);
                                text-align: center;
                                width: 100%;
                                max-width: 480px;
                                opacity: 1;
                                visibility: visible;
                                display: block;
                            ">
                                {{ __('landingpage.sd_provider_details') }}
                            </div>
                        </div>



                        

                        {{-- <img src="{{ asset('images/frame_img.jpg') }}" alt="" class="d-flex m-auto" style="width: 164px;height:140px"> --}}
                        <div class="position-relative d-flex m-auto" style="width: 164px; height: 140px;">
                            <img src="{{ asset('images/frame_img.jpg') }}" alt="Frame"
                                style="width: 100%; height: 100%; position: absolute; z-index: 2;">
                            <img src="{{ asset($serviceData['provider']['profile_image']) }}" alt="{{ __('landingpage.sd_provider_details') }}"
                                style="width: 80%; height: 80%; object-fit: cover; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 2;">
                        </div>

                        {{-- Provider Location --}}
                        <div class="text-center mt-2">
                            <p class="mb-1" style="color: red; font-size: 14px; font-weight: 500;">
                                @if(isset($serviceData['provider']['city']['name']) && isset($serviceData['provider']['country']['name']))
                                    {{ $serviceData['provider']['city']['name'] }} - {{ $serviceData['provider']['country']['name'] }}
                                @elseif(isset($serviceData['provider']['city']['name']))
                                    {{ $serviceData['provider']['city']['name'] }}
                                @elseif(isset($serviceData['provider']['country']['name']))
                                    {{ $serviceData['provider']['country']['name'] }}
                                @else
                                    City - Country
                                @endif
                            </p>
                        </div>

                        <div class="d-flex align-items-center  mt-2 justify-content-evenly">
                            @php
                                $providerId = $serviceData['provider']['id'] ?? null;
                                $isAllVerified = $providerId && function_exists('verify_provider_document') ? verify_provider_document($providerId) : false;
                            @endphp
                            @if ($isAllVerified)
                                <img src="{{ asset('images/icon/verifiedpng.png') }}" alt="icon"
                                    style="width: 26px; height: 26px; margin-right: 10px;">
                            @else
                                <img src="{{ asset('images/icon/notverifiedpng.png') }}" alt="icon"
                                    style="width: 26px; height: 26px; margin-right: 10px;">
                            @endif


                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="star-rating">
                                    <rating-component :readonly="true" :showrating="false"
                                        :ratingvalue="{{ $serviceData['provider']['providers_service_rating'] }}" />
                                </div>
                                <h6 class="lh-sm">
                                    {{ round($serviceData['provider']['providers_service_rating'], 1) }}</h6><a
                                    href="{{ route('rating.all', ['provider_id' => $serviceData['provider']['id']]) }}">({{ $serviceData['provider']['total_service_rating'] }}
                                    {{ __('messages.reviews') }})</a>
                            </div>




                            <img src="{{ asset($providerPlanIcon) }}" alt="icon"
                                style="width: 26px; height: 26px;">


                        </div>

                        <!-- Provider Information Cards -->
                        <div class="provider-info-section mt-4">
                        <!-- Location (City & Country) Card -->
                            @php
                                $serviceCity = $serviceData['service_detail']['city_name'] ?? null;
                                $serviceCountry = $serviceData['service_detail']['country_name'] ?? null;
                            @endphp
                            @if($serviceCity || $serviceCountry)
                            <div class="info-card mb-3">
                                <div class="info-card-header" style="background: #3333ff !important; color: #fff !important; display: flex !important; justify-content: center !important; align-items: center !important; padding: 6px 15px !important; min-height: 40px !important;">
                                    <i class="ri-map-pin-line me-2" style="font-size: 16px;"></i>
                                    <h6 class="mb-0" style="color: #fff !important; font-size: 13px; font-weight: 700;">{{ __('landingpage.available_location') }}</h6>
                                </div>
                                <div class="info-card-body">
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        @if($serviceCity && $serviceCountry)
                                            <span class="location-badge">{{ $serviceCity }}, {{ $serviceCountry }}</span>
                                        @elseif($serviceCity)
                                            <span class="location-badge">{{ $serviceCity }}</span>
                                        @else
                                            <span class="location-badge">{{ $serviceCountry }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Basic Information Card -->
                            <div class="info-card mb-3">
                                <div class="info-card-header" style="background: #3333ff !important; color: #fff !important; display: flex !important; justify-content: center !important; align-items: center !important; padding: 6px 15px !important; min-height: 40px !important;">
                                    <i class="ri-user-line me-2" style="font-size: 16px;"></i>
                                    <h6 class="mb-0" style="color: #fff !important; font-size: 13px; font-weight: 700;">{{ __('landingpage.sd_basic_information') }}</h6>
                                </div>
                                <div class="info-card-body">
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ri-calendar-line me-1"></i> Member Since:
                                        </span>
                                        <span class="info-value">{{ \Carbon\Carbon::parse($serviceData['provider']['created_at'])->format('d M Y') }}</span>
                                    </div>
                                    @if(!empty($serviceData['provider']['designation']))
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ri-briefcase-line me-1"></i> {{ __('landingpage.designation') }}:
                                        </span>
                                        <span class="info-value">{{ ucfirst(trim($serviceData['provider']['designation'])) }}</span>
                                    </div>
                                    @endif
                                    @if(!empty($serviceData['provider']['career_level']))
                                    @php
                                        $careerKey = is_string($serviceData['provider']['career_level']) ? str_replace(' ', '_', strtolower(trim($serviceData['provider']['career_level']))) : '';
                                        $careerTransKey = 'messages.career_level_' . $careerKey;
                                        $careerLabel = __($careerTransKey);
                                        if ($careerLabel === $careerTransKey) {
                                            $careerLabel = ucwords(str_replace('_', ' ', $careerKey));
                                        }
                                    @endphp
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ri-briefcase-4-line me-1"></i> {{ __('landingpage.jdd_career_level') }}
                                        </span>
                                        <span class="info-value">{{ $careerLabel }}</span>
                                    </div>
                                    @endif
                                    @if(!empty($serviceData['provider']['years_of_experience']))
                                    @php
                                        $yearsKey = $serviceData['provider']['years_of_experience'];
                                        $yearsLabels = [
                                            'less_than_1' => __('messages.years_of_experience_less_than_1'),
                                            '1_to_3' => __('messages.years_of_experience_1_to_3'),
                                            '3_to_5' => __('messages.years_of_experience_3_to_5'),
                                            '5_to_8' => __('messages.years_of_experience_5_to_8'),
                                            '8_to_10' => __('messages.years_of_experience_8_to_10'),
                                            'more_than_10' => __('messages.years_of_experience_more_than_10'),
                                        ];
                                        $yearsLabel = $yearsLabels[$yearsKey] ?? $yearsKey;
                                    @endphp
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ri-time-line me-1"></i> {{ __('messages.years_of_experience') }}:
                                        </span>
                                        <span class="info-value">{{ $yearsLabel }}</span>
                                    </div>
                                    @endif
                                    @if(!empty($completed_services))
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ri-checkbox-circle-line me-1"></i> Jobs Completed:
                                        </span>
                                        <span class="info-value badge bg-success">{{ $completed_services }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Availability & Mobility Card -->
                            @if(!empty($serviceData['provider']['availability']) || !empty($serviceData['provider']['mobility']))
                            <div class="info-card mb-3">
                                <div class="info-card-header" style="background: #3333ff !important; color: #fff !important; display: flex !important; justify-content: center !important; align-items: center !important; padding: 6px 15px !important; min-height: 40px !important;">
                                    <i class="ri-time-line me-2" style="font-size: 16px;"></i>
                                    <h6 class="mb-0" style="color: #fff !important; font-size: 13px; font-weight: 700;">{{ __('landingpage.sd_availability_mobility') }}</h6>
                                </div>
                                <div class="info-card-body">
                                    @if(!empty($serviceData['provider']['availability']))
                                    @php
                                        $availData = $serviceData['provider']['availability'];
                                        $availabilityText = '';
                                        if (is_string($availData)) {
                                            $decA = json_decode($availData, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decA)) {
                                                $availabilityText = implode("\n\n", array_filter(array_map('trim', $decA)));
                                            } else {
                                                $availabilityText = trim($availData);
                                            }
                                        } elseif (is_array($availData)) {
                                            $availabilityText = implode("\n\n", array_filter(array_map('trim', $availData)));
                                        } else {
                                            $availabilityText = trim((string) $availData);
                                        }
                                    @endphp
                                    @if($availabilityText !== '')
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ri-calendar-check-line me-1"></i> {{ __('landingpage.sd_availability') }}:
                                        </span>
                                        <span class="info-value info-value-long">
                                            <span class="provider-field-prose border rounded p-2 bg-primary bg-opacity-10 text-dark">{!! nl2br(e($availabilityText)) !!}</span>
                                        </span>
                                    </div>
                                    @endif
                                    @endif
                                    @if(!empty($serviceData['provider']['mobility']))
                                    @php
                                        $mobilityData = $serviceData['provider']['mobility'];
                                        $mobilityText = '';
                                        if (is_string($mobilityData)) {
                                            $decM = json_decode($mobilityData, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decM)) {
                                                $mobilityText = implode("\n\n", array_filter(array_map('trim', $decM)));
                                            } else {
                                                $mobilityText = trim($mobilityData);
                                            }
                                        } elseif (is_array($mobilityData)) {
                                            $mobilityText = implode("\n\n", array_filter(array_map('trim', $mobilityData)));
                                        } else {
                                            $mobilityText = trim((string) $mobilityData);
                                        }
                                    @endphp
                                    @if($mobilityText !== '')
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ri-car-line me-1"></i> {{ __('landingpage.sd_mobility') }}:
                                        </span>
                                        <span class="info-value info-value-long">
                                            <span class="provider-field-prose border rounded p-2 bg-info bg-opacity-10 text-dark">{!! nl2br(e($mobilityText)) !!}</span>
                                        </span>
                                    </div>
                                    @endif
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Languages Card -->
                            @php
                                // Check if the 'languages' field is a JSON string
                                $languages = is_string($serviceData['provider']['languages'])
                                    ? json_decode($serviceData['provider']['languages'], true)
                                    : $serviceData['provider']['languages']; // Use as-is if it's already an array
                                $languagesList = is_array($languages) ? $languages : (!empty($languages) ? [$languages] : []);
                            @endphp
                            @if(!empty($languagesList))
                            <div class="info-card mb-3">
                                <div class="info-card-header" style="background: #3333ff !important; color: #fff !important; display: flex !important; justify-content: center !important; align-items: center !important; padding: 6px 15px !important; min-height: 40px !important;">
                                    <i class="ri-global-line me-2" style="font-size: 16px;"></i>
                                    <h6 class="mb-0" style="color: #fff !important; font-size: 13px; font-weight: 700;">{{ __('landingpage.sd_languages') }}</h6>
                                </div>
                                <div class="info-card-body">
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($languagesList as $lang)
                                            <span class="language-badge">{{ ucfirst(trim($lang)) }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Education & Skills Card -->
                            @if(!empty($serviceData['provider']['education']))
                            <div class="info-card mb-3">
                                <div class="info-card-header" style="background: #3333ff !important; color: #fff !important; display: flex !important; justify-content: center !important; align-items: center !important; padding: 6px 15px !important; min-height: 40px !important;">
                                    <i class="ri-graduation-cap-line me-2" style="font-size: 16px;"></i>
                                    <h6 class="mb-0" style="color: #fff !important; font-size: 13px; font-weight: 700;">{{ __('landingpage.sd_education_skills') }}</h6>
                                </div>
                                <div class="info-card-body">
                                    <div class="skills-list">
                                        @php
                                            $educationData = $serviceData['provider']['education'];
                                            // Try to decode JSON first
                                            if (is_string($educationData)) {
                                                $decoded = json_decode($educationData, true);
                                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                    $educationItems = $decoded;
                                                } else {
                                                    $educationItems = array_map('trim', explode(',', $educationData));
                                                }
                                            } elseif (is_array($educationData)) {
                                                $educationItems = $educationData;
                                            } else {
                                                $educationItems = [$educationData];
                                            }
                                            // Map DB keys to readable labels (messages.education_*)
                                            $educationLabels = [];
                                            foreach ($educationItems as $edu) {
                                                $key = is_string($edu) ? str_replace(' ', '_', strtolower(trim($edu))) : (string) $edu;
                                                if ($key === '') continue;
                                                $transKey = 'messages.education_' . $key;
                                                $label = __($transKey);
                                                $educationLabels[] = ($label === $transKey) ? ucwords(str_replace('_', ' ', $key)) : $label;
                                            }
                                        @endphp
                                        @foreach($educationLabels as $eduLabel)
                                            <span class="skill-badge">{{ $eduLabel }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Diploma Card -->
                            @if(!empty($serviceData['provider']['skills']))
                            <div class="info-card mb-3">
                                <div class="info-card-header" style="background: #3333ff !important; color: #fff !important; display: flex !important; justify-content: center !important; align-items: center !important; padding: 6px 15px !important; min-height: 40px !important;">
                                    <i class="ri-file-certificate-line me-2" style="font-size: 16px;"></i>
                                    <h6 class="mb-0" style="color: #fff !important; font-size: 13px; font-weight: 700;">{{ __('landingpage.sd_diploma_certifications') }}</h6>
                                </div>
                                <div class="info-card-body">
                                    <div class="skills-list">
                                        @php
                                            $skillsData = $serviceData['provider']['skills'];
                                            
                                            // Try to decode JSON first
                                            if (is_string($skillsData)) {
                                                $decoded = json_decode($skillsData, true);
                                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                    // Successfully decoded JSON array
                                                    $skills = $decoded;
                                                } else {
                                                    // Not JSON, try comma-separated string
                                                    $skills = explode(',', $skillsData);
                                                }
                                            } elseif (is_array($skillsData)) {
                                                // Already an array
                                                $skills = $skillsData;
                                            } else {
                                                // Single value, convert to array
                                                $skills = [$skillsData];
                                            }
                                        @endphp
                                        @foreach($skills as $skill)
                                            <span class="skill-badge">{{ trim($skill) }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Additional Skills/Certifications Card -->
                            @if(!empty($serviceData['provider']['certification']))
                            <div class="info-card mb-3">
                                <div class="info-card-header" style="background: #3333ff !important; color: #fff !important; display: flex !important; justify-content: center !important; align-items: center !important; padding: 6px 15px !important; min-height: 40px !important;">
                                    <i class="ri-award-line me-2" style="font-size: 16px;"></i>
                                    <h6 class="mb-0" style="color: #fff !important; font-size: 13px; font-weight: 700;">{{ __('landingpage.sd_certifications_skills') }}</h6>
                                </div>
                                <div class="info-card-body">
                                    <div class="skills-list">
                                        @php
                                            $certifications = is_string($serviceData['provider']['certification']) 
                                                ? explode(',', $serviceData['provider']['certification']) 
                                                : (is_array($serviceData['provider']['certification']) ? $serviceData['provider']['certification'] : [$serviceData['provider']['certification']]);
                                        @endphp
                                        @foreach($certifications as $cert)
                                            <span class="skill-badge">{{ trim($cert) }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Share Modal -->
    <div class="modal fade" id="share-modal" tabindex="-1" aria-labelledby="share-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-capitalize">{{ __('landingpage.share_this_service') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group copy-link-form">
                        <input id="copy-link-input" type="text" class="form-control copy-link-input" readonly>
                        <button id="copy-link-btn"
                            class="btn btn-primary copy-link-btn">{{ __('landingpage.copy_link') }}</button>
                    </div>
                    <div class="social-login mt-3">
                        <ul class="list-inline d-flex flex-wrap align-items-center justify-content-center gap-3 m-0">
                            <li>
                                <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 16 16" fill="none">
                                        <g>
                                            <path
                                                d="M16 8C16 3.58172 12.4183 0 8 0C3.58172 0 0 3.58172 0 8C0 11.993 2.92547 15.3027 6.75 15.9028V10.3125H4.71875V8H6.75V6.2375C6.75 4.2325 7.94438 3.125 9.77172 3.125C10.6467 3.125 11.5625 3.28125 11.5625 3.28125V5.25H10.5538C9.56 5.25 9.25 5.86672 9.25 6.5V8H11.4688L11.1141 10.3125H9.25V15.9028C13.0745 15.3027 16 11.993 16 8Z"
                                                fill="#3F53A5" />
                                        </g>
                                    </svg>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="17"
                                        viewBox="0 0 16 17" fill="none">
                                        <path
                                            d="M14.5377 7.19425H14.0007V7.16659H8.00065V9.83325H11.7683C11.2187 11.3856 9.74165 12.4999 8.00065 12.4999C5.79165 12.4999 4.00065 10.7089 4.00065 8.49992C4.00065 6.29092 5.79165 4.49992 8.00065 4.49992C9.02032 4.49992 9.94798 4.88459 10.6543 5.51292L12.54 3.62725C11.3493 2.51759 9.75665 1.83325 8.00065 1.83325C4.31898 1.83325 1.33398 4.81825 1.33398 8.49992C1.33398 12.1816 4.31898 15.1666 8.00065 15.1666C11.6823 15.1666 14.6673 12.1816 14.6673 8.49992C14.6673 8.05292 14.6213 7.61659 14.5377 7.19425Z"
                                            fill="#FFC107" />
                                        <path
                                            d="M2.10156 5.39692L4.2919 7.00325C4.88456 5.53592 6.3199 4.49992 7.99956 4.49992C9.01923 4.49992 9.9469 4.88459 10.6532 5.51292L12.5389 3.62725C11.3482 2.51759 9.75556 1.83325 7.99956 1.83325C5.4389 1.83325 3.21823 3.27892 2.10156 5.39692Z"
                                            fill="#FF3D00" />
                                        <path
                                            d="M7.99945 15.1667C9.72145 15.1667 11.2861 14.5077 12.4691 13.436L10.4058 11.69C9.73645 12.197 8.90445 12.5 7.99945 12.5C6.26545 12.5 4.79312 11.3943 4.23845 9.85132L2.06445 11.5263C3.16779 13.6853 5.40845 15.1667 7.99945 15.1667Z"
                                            fill="#4CAF50" />
                                        <path
                                            d="M14.537 7.19441H14V7.16675H8V9.83341H11.7677C11.5037 10.5791 11.024 11.2221 10.4053 11.6904C10.4057 11.6901 10.406 11.6901 10.4063 11.6897L12.4697 13.4357C12.3237 13.5684 14.6667 11.8334 14.6667 8.50008C14.6667 8.05308 14.6207 7.61675 14.537 7.19441Z"
                                            fill="#1976D2" />
                                    </svg>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 16 16" fill="none">
                                        <g>
                                            <path
                                                d="M16 8C16 3.58172 12.4183 0 8 0C3.58172 0 0 3.58172 0 8C0 11.993 2.92547 15.3027 6.75 15.9028V10.3125H4.71875V8H6.75V6.2375C6.75 4.2325 7.94438 3.125 9.77172 3.125C10.6467 3.125 11.5625 3.28125 11.5625 3.28125V5.25H10.5538C9.56 5.25 9.25 5.86672 9.25 6.5V8H11.4688L11.1141 10.3125H9.25V15.9028C13.0745 15.3027 16 11.993 16 8Z"
                                                fill="#3F53A5" />
                                        </g>
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

@endsection

@section('after_script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>
    <script>

        
        $(document).ready(function() {
            // Initialize tab buttons with clean styling
            $('.service-details-tabs .tab-btn').each(function() {
                if (!$(this).hasClass('active')) {
                    $(this).css({
                        'border': 'none',
                        'border-bottom': '3px solid transparent',
                        'background': '#f8f9fa',
                        'color': '#6c757d',
                        'font-weight': '500',
                        'outline': 'none',
                        'box-shadow': 'none'
                    });
                }
            });
            
            // Tab switching functionality - matching job details style
            $('.service-details-tabs .tab-btn').on('click', function() {
                const targetTab = $(this).data('tab');
                
                // Remove active class from all service detail buttons
                $('.service-details-tabs .tab-btn').each(function() {
                    $(this).removeClass('active');
                    $(this).css({
                        'border': 'none',
                        'border-bottom': '3px solid transparent',
                        'background': '#f8f9fa',
                        'color': '#6c757d',
                        'font-weight': '500',
                        'outline': 'none',
                        'box-shadow': 'none'
                    });
                });
                
                // Add active class to clicked button
                $(this).addClass('active');
                $(this).css({
                    'border': 'none',
                    'border-bottom': '3px solid #007bff',
                    'background': '#fff',
                    'color': '#007bff',
                    'font-weight': '600',
                    'outline': 'none',
                    'box-shadow': '0 -2px 4px rgba(0,0,0,0.1)'
                });
                
                // Hide all service detail tab contents
                $('.service-details-tabs .tab-content').each(function() {
                    $(this).hide().removeClass('active');
                });
                
                // Show target tab content
                $('#' + targetTab + '-content').show().addClass('active');
            });

            $('.service-addon-checkbox').on('change', function() {
                updateContinueButtonHref();
            });

            function updateContinueButtonHref() {
                var selectedAddonIds = $('.service-addon-checkbox:checked').map(function() {
                    return $(this).data('addon-id');
                }).get();

                var baseUrl = `{{ route('book.service', ['id' => $serviceData['service_detail']['id']]) }}`;

                var updatedHref = baseUrl + '&addons=' + selectedAddonIds.join(',');

                $('.continue-button').attr('href', updatedHref);
            }
            const baseUrl = document.querySelector('meta[name="baseUrl"]').getAttribute('content');
            $('.save_fav').off('click').on('click', function() {

                const form = $(this).closest('form');

                const serviceId = form.find('.service_id').data('service-id');
                const userId = $('#user_id').val();

                $.ajax({
                    url: baseUrl + '/api/save-favourite',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        service_id: serviceId,
                        user_id: userId,
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Done',
                            text: response.message,
                            icon: 'success',
                            iconColor: '#3333ff'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        })
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });

            $('.delete_fav').off('click').on('click', function() {
                const form = $(this).closest('form');

                const serviceId = form.find('.service_id').data('service-id');
                const userId = $('#user_id').val();

                $.ajax({
                    url: baseUrl + '/api/delete-favourite',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        service_id: serviceId,
                        user_id: userId,
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Done',
                            text: response.message,
                            icon: 'success',
                            iconColor: '#3333ff'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        })
                    },
                    error: function(error) {
                        console.error('Error', error);
                    }
                });
            });
            // Read More button functionality
            $('.readmore-btn').on('click', function(e) {
                e.preventDefault();
                
                // Trigger click on the "About Services" tab
                var aboutTabBtn = $('.service-details-tabs .tab-btn[data-tab="about-services"]');
                if (aboutTabBtn.length) {
                    aboutTabBtn.trigger('click');
                    
                    // Method 1: jQuery animate (standard)
                    $('html, body').animate({
                        scrollTop: $('.service-details-tabs').offset().top - 100
                    }, 800);
                }
            });

            // Social Media Share Handler
            window.__shareClickHandler = function(e, el) {
                try { 
                    if (e) {
                        e.preventDefault(); 
                        e.stopPropagation(); 
                    }
                } catch (_) {}

                function openPopup(url) {
                    if (url) {
                        window.open(url, '_blank', 'noopener,noreferrer,width=600,height=600');
                    }
                }

                if (!el) {
                    console.error('Share handler: element not found');
                    return false;
                }

                var platform = el.getAttribute('data-platform');
                var shareUrl = el.getAttribute('data-share-url');

                if (!platform) {
                    console.error('Share handler: platform not found');
                    return false;
                }

                if (platform === 'facebook') {
                    var fbUrl = encodeURIComponent(shareUrl || window.location.href);
                    var quote = encodeURIComponent(el.getAttribute('data-quote') || '');
                    var shareLink = 'https://www.facebook.com/sharer/sharer.php?u=' + fbUrl + (quote ? '&quote=' + quote : '');
                    openPopup(shareLink);
                } else if (platform === 'twitter') {
                    var text = encodeURIComponent(el.getAttribute('data-text') || '');
                    var url = encodeURIComponent(shareUrl || window.location.href);
                    var shareLink = 'https://twitter.com/intent/tweet?url=' + url + '&text=' + text;
                    openPopup(shareLink);
                } else if (platform === 'linkedin') {
                    var liUrl = encodeURIComponent(shareUrl || window.location.href);
                    var shareLink = 'https://www.linkedin.com/sharing/share-offsite/?url=' + liUrl;
                    openPopup(shareLink);
                } else if (platform === 'telegram') {
                    var url = encodeURIComponent(shareUrl || window.location.href);
                    var text = encodeURIComponent((el.getAttribute('data-quote') || '').trim());
                    openPopup('https://t.me/share/url?url=' + url + (text ? '&text=' + text : ''));
                }

                return false;
            };

            // Event delegation for social media links
            $(document).on('click', '.social-link.share-link', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (window.__shareClickHandler) {
                    return window.__shareClickHandler(e, this);
                }
                return false;
            });
        });
    </script>
@endsection
