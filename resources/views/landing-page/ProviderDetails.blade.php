@extends('landing-page.layouts.default')

@php
    $p = $providerData['data'] ?? [];
    $shareTitle = $p['display_name'] ?? __('landingpage.pd_provider_fallback');
    $cityName = data_get($p, 'city.name') ?? ($p['city_name'] ?? '');
    $countryName = data_get($p, 'country.name') ?? ($p['country_name'] ?? '');
    $location = trim(implode(', ', array_filter([$cityName, $countryName])));
    $designation = trim((string)($p['designation'] ?? ''));
    $aboutRaw = $p['about_me'] ?? null;
    $aboutSnippet = ($aboutRaw !== '' && $aboutRaw !== null) ? \Illuminate\Support\Str::limit(strip_tags((string)$aboutRaw), 200) : '';
    $appName = config('app.display_name', 'Frobster');
    $parts = [];
    if ($designation !== '') {
        $parts[] = 'Designation: ' . $designation;
    }
    if ($location !== '') {
        $parts[] = 'Location: ' . $location;
    }
    if ($aboutSnippet !== '') {
        $parts[] = 'About: ' . $aboutSnippet;
    }
    $shareDescription = $parts !== [] ? implode('. ', $parts) : ($shareTitle . ' - ' . __('landingpage.pd_service_provider_on') . ' ' . $appName);
    $shareDescription = \Illuminate\Support\Str::limit($shareDescription, 297);
    $shareDescriptionOg = \Illuminate\Support\Str::limit($shareDescription, 256);
    $shareDescriptionOg = str_replace(["\xE2\x80\xA6", "\xE2\x80\x94", "\xE2\x80\x93"], ['...', '-', '-'], $shareDescriptionOg);
    $shareUrl = route('provider.detail', $p['id'] ?? 0);
    $shareImage = !empty($p['profile_image'])
        ? (str_starts_with($p['profile_image'], 'http') ? $p['profile_image'] : url($p['profile_image']))
        : url('images/post-job/ac_refresh_and_revive.png');
    $shareImageSecure = str_starts_with($shareImage, 'http://') ? 'https://' . substr($shareImage, 7) : $shareImage;
@endphp
@section('before_head')
    <meta name="description" content="{{ $shareDescription }}" />
    <meta property="og:type" content="profile" />
    <meta property="og:title" content="{{ $shareTitle }}" />
    <meta property="og:description" content="{{ $shareDescriptionOg }}" />
    <meta property="og:url" content="{{ $shareUrl }}" />
    <meta property="og:image" content="{{ $shareImageSecure }}" />
    <meta property="og:image:secure_url" content="{{ $shareImageSecure }}" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:site_name" content="{{ config('app.display_name', 'Frobster') }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $shareTitle }}" />
    <meta name="twitter:description" content="{{ $shareDescriptionOg }}" />
    <meta name="twitter:image" content="{{ $shareImageSecure }}" />
    <meta name="twitter:image:alt" content="{{ $shareTitle }} - {{ $designation ?: 'Provider' }}" />
@endsection

@section('after_head')
    <style>
        .table {
            table-layout: fixed;
            width: 100%;
        }

        .table td {
            overflow-wrap: break-word;
            word-wrap: break-word;
            white-space: normal;
        }

        /* Modern Provider Details Cards */
        .provider-details-grid {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .info-card-modern {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .info-card-modern:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .info-card-header-modern,
        .provider-details-grid .info-card-modern .info-card-header-modern,
        .info-card-modern .info-card-header-modern {
            background: #3333ff !important;
            background-size: 200% 200% !important;
            animation: gradient-shift 8s ease infinite !important;
            color: #ffffff !important;
            padding: 20px 20px !important; /* Adjusted padding */
            display: flex !important;
            align-items: center !important;
            justify-content: center !important; /* Center content */
            font-weight: 700 !important;
            font-size: 15px !important; /* Adjusted font size */
            position: relative !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
            border-bottom: none !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            border-radius: 16px 16px 0 0 !important;
            min-height: 60px !important; /* Adjusted height */
            opacity: 1 !important;
            visibility: visible !important;
        }

        .info-card-header-modern i {
            font-size: 20px !important;
            flex-shrink: 0 !important;
            margin-right: 10px !important;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2)) !important;
            opacity: 0.95 !important;
            position: relative;
            z-index: 1;
            visibility: visible !important;
        }

        .info-card-header-modern h5 {
            margin: 0 !important;
            font-size: 14px !important;
            font-weight: 700 !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
            color: #fff !important;
            position: relative;
            z-index: 1;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .info-card-body-modern {
            padding: 24px;
        }

        .skills-badge-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .skill-badge-modern {
            display: inline-block;
            padding: 10px 18px;
            background: linear-gradient(135deg, rgba(255, 0, 0, 0.1) 0%, rgba(95, 96, 185, 0.1) 100%);
            border: 1px solid rgba(255, 0, 0, 0.2);
            border-radius: 25px;
            color: #333;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .provider-experience-prose {
            line-height: 1.65;
            color: #333;
            font-size: 15px;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .skill-badge-modern:hover {
            background: linear-gradient(135deg, rgba(255, 0, 0, 0.15) 0%, rgba(95, 96, 185, 0.15) 100%);
            border-color: rgba(255, 0, 0, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 0, 0, 0.15);
        }

        .info-value-modern {
            font-size: 15px;
            color: #495057;
            line-height: 1.6;
            display: flex;
            align-items: center;
        }

        .badge-modern {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .bg-info-modern {
            background: linear-gradient(135deg, rgba(13, 202, 240, 0.15) 0%, rgba(13, 202, 240, 0.25) 100%);
            color: #0dcaf0;
            border: 1px solid rgba(13, 202, 240, 0.3);
        }

        .about-me-content {
            color: #495057;
            line-height: 1.8;
            font-size: 15px;
            text-align: justify;
        }

        .about-me-content p {
            margin-bottom: 12px;
        }

        .about-me-content p:last-child {
            margin-bottom: 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .info-card-header-modern {
                padding: 14px 18px;
                font-size: 15px;
            }

            .info-card-body-modern {
                padding: 18px;
            }

            .skill-badge-modern {
                padding: 8px 14px;
                font-size: 13px;
            }
        }

        /* Prevent flash/blink on provider detail card headers */
        .info-card-modern {
            opacity: 1 !important;
            visibility: visible !important;
            transition: none !important;
            animation: none !important;
        }

        .info-card-header-modern {
            background: #3333ff !important;
            background-size: 200% 200% !important;
            animation: gradient-shift 8s ease infinite !important;
            opacity: 1 !important;
            visibility: visible !important;
            display: flex !important;
            transition: none !important;
            will-change: auto !important;
            color: #fff !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            padding: 16px 20px !important;
            border-radius: 16px 16px 0 0 !important;
            min-height: 52px !important;
        }

        .info-card-header-modern * {
            opacity: 1 !important;
            visibility: visible !important;
        }

        /* Ensure no parent elements are hiding it */
        .provider-details-grid,
        .provider-details-grid > * {
            opacity: 1 !important;
            visibility: visible !important;
        }
    </style>
@endsection

@section('content')
    <div class="section-padding position-relative px-0">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="sticky">
                        <div class="position-relative bg-primary p-5 provider-profile-card">
                            <div class="position-absolute start-0 top-0 h-100 w-100">
                                <img src="{{ asset('landing-images/Vector-bg-1.png') }}"class="img-fluid h-100 w-100"
                                    alt="image">
                            </div>
                            <div class="mt-3 text-center position-relative">
                                <img src="{{ $providerData['data']['profile_image'] }}" alt="{{ __('landingpage.pd_alt_provider') }}"
                                    class="avatar-180 img-fluid rounded-circle object-cover border border-5 border-white bg-primary-subtle">
                                <div class="d-flex align-items-center justify-content-center gap-2 mt-3">
                                    <img src="{{ asset($imagePath) }}" alt="icon" style="width: 14%; height: 23%;">

                                    <h5 class="m-0 text-white text-capitalize">{{ $providerData['data']['display_name'] }}
                                    </h5>

                                    <span class="text-primary">
                                        @php
                                            $providerDocuments = $providerData['document_detail'] ?? null;
                                            $verifiedDisplayed = false; // Boolean flag to check if the verified icon has been displayed
                                        @endphp

                                        @if ($providerDocuments)
                                            @foreach ($providerDocuments as $document)
                                                @if (isset($document['is_verified']) && $document['is_verified'] && !$verifiedDisplayed)
                                                    @php
                                                        $verifiedDisplayed = true; // Set the flag to true after displaying the icon
                                                    @endphp
                                                    <svg width="24" height="25" viewBox="0 0 24 25" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M21.1871 10.507C20.8623 10.3182 20.5705 10.0777 20.3231 9.795C20.3481 9.40141 20.4418 9.01525 20.6001 8.654C20.8911 7.833 21.2201 6.903 20.6921 6.18C20.1641 5.457 19.1671 5.48 18.2921 5.5C17.9054 5.53978 17.5148 5.5134 17.1371 5.422C16.9358 5.09451 16.7921 4.73498 16.7121 4.359C16.4641 3.514 16.1811 2.559 15.3121 2.273C14.4741 2.003 13.6981 2.597 13.0121 3.119C12.7161 3.38932 12.3731 3.60317 12.0001 3.75C11.6232 3.60437 11.2764 3.39046 10.9771 3.119C10.2931 2.6 9.52007 2 8.67807 2.274C7.81107 2.556 7.52807 3.514 7.27807 4.359C7.1982 4.73376 7.05588 5.09243 6.85707 5.42C6.47859 5.51116 6.0875 5.5382 5.70007 5.5C4.82207 5.476 3.83307 5.45 3.30007 6.18C2.76707 6.91 3.10007 7.833 3.39207 8.653C3.55251 9.01371 3.64765 9.40003 3.67307 9.794C3.42615 10.0771 3.13464 10.3179 2.81007 10.507C2.07807 11.007 1.24707 11.576 1.24707 12.5C1.24707 13.424 2.07807 13.991 2.81007 14.493C3.13457 14.6818 3.42607 14.9223 3.67307 15.205C3.65033 15.5988 3.55789 15.9855 3.40007 16.347C3.11007 17.167 2.78207 18.097 3.30907 18.82C3.83607 19.543 4.83007 19.52 5.70907 19.5C6.09604 19.4602 6.48696 19.4866 6.86507 19.578C7.06545 19.9058 7.20881 20.2653 7.28907 20.641C7.53707 21.486 7.82007 22.441 8.68907 22.727C8.82839 22.7717 8.97376 22.7946 9.12007 22.795C9.82328 22.6941 10.4769 22.3743 10.9881 21.881C11.2841 21.6107 11.6271 21.3968 12.0001 21.25C12.377 21.3956 12.7238 21.6095 13.0231 21.881C13.7081 22.404 14.4841 23.001 15.3231 22.726C16.1901 22.444 16.4731 21.486 16.7231 20.642C16.8032 20.2665 16.9466 19.9074 17.1471 19.58C17.5241 19.4882 17.914 19.4612 18.3001 19.5C19.1781 19.521 20.1671 19.55 20.7001 18.82C21.2331 18.09 20.9001 17.167 20.6081 16.346C20.4487 15.9856 20.3536 15.6001 20.3271 15.207C20.5741 14.9237 20.866 14.6828 21.1911 14.494C21.9231 13.994 22.7541 13.424 22.7541 12.5C22.7541 11.576 21.9201 11.008 21.1871 10.507Z"
                                                            fill="#EFEFF8" />
                                                        <path
                                                            d="M11.0001 15.25C10.9016 15.2502 10.804 15.2308 10.7131 15.1931C10.6221 15.1553 10.5395 15.0999 10.4701 15.03L8.47009 13.03C8.33761 12.8878 8.26549 12.6998 8.26892 12.5055C8.27234 12.3112 8.35106 12.1258 8.48847 11.9884C8.62588 11.851 8.81127 11.7723 9.00557 11.7688C9.19987 11.7654 9.38792 11.8375 9.53009 11.97L11.0701 13.51L14.5501 10.9C14.7092 10.7807 14.9092 10.7294 15.1062 10.7575C15.3031 10.7857 15.4807 10.8909 15.6001 11.05C15.7194 11.2091 15.7707 11.4092 15.7426 11.6061C15.7144 11.803 15.6092 11.9807 15.4501 12.1L11.4501 15.1C11.3202 15.1973 11.1624 15.2499 11.0001 15.25Z"
                                                            fill="currentColor" />
                                                    </svg>
                                                @endif
                                            @endforeach
                                        @endif
                                    </span>
                                    @php
                                        $allVerified = function_exists('verify_provider_document') ? verify_provider_document($providerData['data']['id']) : false;
                                    @endphp

                                    @if ($allVerified)
                                        <img src="{{ asset('images/icon/verifiedpng.png') }}" alt="Verified Icon"
                                            style="width: 14%; height: 23%; margin-right: 10px;">
                                    @else
                                        <img src="{{ asset('images/icon/notverifiedpng.png') }}" alt="{{ __('landingpage.pd_alt_not_verified_icon') }}"
                                            style="width: 14%; height: 23%; margin-right: 10px;">
                                    @endif
                                </div>
                                <div class="d-flex align-items-center justify-content-center gap-1 mt-2">
                                    <div>
                                        <rating-component :readonly="true" :showrating="false"
                                            :ratingvalue="{{ $providerData['data']['providers_service_rating'] }}" />
                                    </div>
                                    <h6 class="text-white">{{ round($providerData['data']['providers_service_rating'], 1) }}
                                    </h6>
                                    @if ($providerData['data']['total_service_rating'] > 1)
                                        <span class="h6 text-white">({{ $providerData['data']['total_service_rating'] }}
                                            {{ __('messages.reviews') }})</span>
                                    @else
                                        <span class="h6 text-white">({{ $providerData['data']['total_service_rating'] }}
                                            {{ __('messages.review') }})</span>
                                    @endif
                                </div>
                                @if (isset($why_choose_me))
                                    @if (isset($why_choose_me['about_description']) || isset($why_choose_me['reason']))
                                        <div class="mt-2">
                                            <a href="javascript:void(0);" class="btn btn-primary fw-bold"
                                                data-bs-toggle="modal"
                                                data-bs-target="#chooseme">{{ __('landingpage.why_choose_me') }}</a>
                                        </div>
                                    @endif
                                @endif
                                <div class="table-responsive mt-5" style="overflow-x: auto;">

                                    <table class="table table-borderless text-start mb-0"
                                        style="table-layout: fixed; width: 100%;">


                                        <tbody>

                                            <tr class="px-0">
                                                <img src="" style="width: 110px;" alt="">
                                            </tr>
                                            <tr>
                                                <td class="px-0">
                                                    <h6 class="text-white m-0 lh-base">{{ __('landingpage.projects') }}:
                                                    </h6>
                                                </td>
                                                <td class=" pe-0">

                                                    <span class="text-white">{{ $completed_services }}
                                                        {{ __('landingpage.project_completed') }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="px-0">
                                                    <h6 class="text-white m-0 lh-base">{{ __('messages.customer') }}:</h6>
                                                </td>
                                                <td class=" pe-0">

                                                    <span class="text-white">{{ $satisfy_customers }}
                                                        {{ __('landingpage.satisfy_customers') }}</span>
                                                </td>
                                            </tr>
                                            <tr class=" pe-0">
                                                @php
                                                    $detailShareUrl = route('provider.detail', $p['id'] ?? 0) . '?v=' . time();
                                                    $cityName = data_get($p, 'city.name') ?: ($p['city_name'] ?? '');
                                                    $countryName = data_get($p, 'country.name') ?: ($p['country_name'] ?? '');
                                                    $location = trim(implode(', ', array_filter([$cityName, $countryName])));
                                                    $skillsRaw = $p['skills'] ?? null;
                                                    $skillsStr = '';
                                                    if ($skillsRaw !== null && $skillsRaw !== '') {
                                                        if (is_string($skillsRaw)) {
                                                            $decoded = json_decode($skillsRaw, true);
                                                            $skillsStr = is_array($decoded) ? implode(', ', array_slice($decoded, 0, 5)) : \Illuminate\Support\Str::limit($skillsRaw, 80);
                                                        } elseif (is_array($skillsRaw)) {
                                                            $skillsStr = implode(', ', array_slice($skillsRaw, 0, 5));
                                                        }
                                                    }
                                                    $whyChoose = $p['why_choose_me'] ?? null;
                                                    $benefitStr = '';
                                                    if (is_string($whyChoose)) {
                                                        $whyChoose = json_decode($whyChoose, true);
                                                    }
                                                    if (is_array($whyChoose)) {
                                                        $title = $whyChoose['why_choose_me_title'] ?? '';
                                                        $reasons = $whyChoose['why_choose_me_reason'] ?? [];
                                                        $firstReason = is_array($reasons) ? (reset($reasons) ?: '') : (string) $reasons;
                                                        $benefitStr = trim($title . ($firstReason ? ' — ' . \Illuminate\Support\Str::limit($firstReason, 60) : ''));
                                                    }
                                                    $careerLevel = $p['career_level'] ?? null;
                                                    $careerStr = '';
                                                    if ($careerLevel && is_string($careerLevel)) {
                                                        $ck = str_replace(' ', '_', strtolower(trim($careerLevel)));
                                                        $ct = __('messages.career_level_' . $ck);
                                                        $careerStr = ($ct === 'messages.career_level_' . $ck) ? ucwords(str_replace('_', ' ', $ck)) : $ct;
                                                    }
                                                    $experienceRaw = $p['experience'] ?? null;
                                                    $experienceStr = '';
                                                    if ($experienceRaw !== null && $experienceRaw !== '') {
                                                        if (is_string($experienceRaw)) {
                                                            $decoded = json_decode($experienceRaw, true);
                                                            $experienceStr = is_array($decoded) ? implode(', ', array_slice($decoded, 0, 3)) : \Illuminate\Support\Str::limit($experienceRaw, 60);
                                                        } elseif (is_array($experienceRaw)) {
                                                            $experienceStr = implode(', ', array_slice($experienceRaw, 0, 3));
                                                        }
                                                    }
                                                    $aboutMe = isset($p['about_me']) && $p['about_me'] !== '' ? \Illuminate\Support\Str::limit(strip_tags($p['about_me']), 100) : '';
                                                    $parts = array_filter([
                                                        $p['display_name'] ?? '',
                                                        $p['designation'] ?? '',
                                                        $location ? 'Location: ' . $location : null,
                                                        $skillsStr ? 'Skills: ' . $skillsStr : null,
                                                        $aboutMe ? 'About: ' . $aboutMe : null,
                                                        'View profile on ' . config('app.name')
                                                    ]);
                                                    $detailQuote = implode(' | ', $parts);
                                                @endphp
                                                <div class="d-flex align-items-center justify-content-center gap-3 mt-3">
                                                    <span role="button" tabindex="0" class="social-link share-link" data-platform="facebook" data-share-url="{{ $detailShareUrl }}" data-quote="{{ $detailQuote }}" onclick="return typeof window.__shareClickHandler === 'function' && window.__shareClickHandler(event, this);" style="cursor: pointer;">
                                                        <img src="{{ asset('assets/fb.png') }}?v=20260303" style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt="Facebook">
                                                    </span>
                                                    <span role="button" tabindex="0" class="social-link share-link" data-platform="telegram" data-share-url="{{ $detailShareUrl }}" data-quote="{{ $detailQuote }}" onclick="return typeof window.__shareClickHandler === 'function' && window.__shareClickHandler(event, this);" style="cursor: pointer;">
                                                        <img src="{{ asset('assets/telegram.png') }}?v=20260303" style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt="{{ __('landingpage.pd_alt_telegram') }}">
                                                    </span>
                                                    <span role="button" tabindex="0" class="social-link share-link" data-platform="twitter" data-share-url="{{ $detailShareUrl }}" data-text="{{ $detailQuote }}" onclick="return typeof window.__shareClickHandler === 'function' && window.__shareClickHandler(event, this);" style="cursor: pointer;">
                                                        <img src="{{ asset('assets/twiter.png') }}?v=20260303" style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt="Twitter">
                                                    </span>
                                                    <span role="button" tabindex="0" class="social-link share-link" data-platform="linkedin" data-share-url="{{ $detailShareUrl }}" onclick="return typeof window.__shareClickHandler === 'function' && window.__shareClickHandler(event, this);" style="cursor: pointer;">
                                                        <img src="{{ asset('assets/linkedIn.png') }}?v=20260303" style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt="{{ __('landingpage.pd_alt_linkedin') }}">
                                                    </span>
                                                </div>
                                            </tr>
                                            <tr>
                                                <td class="px-0">
                                                    <h6 class="text-white m-0 lh-base">
                                                        {{ __('landingpage.member_since') }}:</h6>
                                                </td>
                                                <td class=" pe-0">

                                                    <span class="text-white">
                                                        {{ date($datetime->date_format ?? 'Y-m-d', strtotime($providerData['data']['created_at'])) }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="px-0">
                                                    <h6 class="text-white m-0 lh-base">{{ __('landingpage.designation') }}:
                                                    </h6>
                                                </td>
                                                <td class=" pe-0">

                                                    <span class="text-white">
                                                        {{ $providerData['data']['designation'] }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="px-0">
                                                    <h6 class="text-white m-0 lh-base">{{ __('landingpage.pd_language') }}:</h6>
                                                </td>
                                                <td class=" pe-0">
                                                    @if (isset($providerData['data']['languages']) && is_array($providerData['data']['languages']))
                                                        <span class="text-white">
                                                            {{ implode(', ', $providerData['data']['languages']) }}
                                                        </span>
                                                    @else
                                                        <span class="text-white">{{ __('messages.na') }}</span>
                                                    @endif
                                                </td>
                                            </tr>


                                            <tr>
                                                <td class="px-0">
                                                    <h6 class="text-white m-0 lh-base">{{ __('landingpage.sd_availability') }}:</h6>
                                                </td>
                                                <td class=" pe-0">
                                                    @if (isset($providerData['data']['availability']))
                                                        @php
                                                            $avKey = $providerData['data']['availability'];
                                                            $avLabel = in_array($avKey, ['full_time', 'part_time'], true)
                                                                ? __('messages.availability_' . $avKey)
                                                                : ucwords(str_replace('_', ' ', $avKey));
                                                        @endphp
                                                        <span
                                                            class="text-white">{{ $avLabel }}</span>
                                                    @else
                                                        <span class="text-white">{{ __('messages.availability') }}</span>
                                                        <!-- Optional: Provide a default message if the language_option is not available -->
                                                    @endif
                                                </td>
                                            </tr>



                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 mt-lg-0 mt-5">
                    <h3 class="text-capitalize mb-4" style="background: #3333ff; -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-weight: 700;">{{ __('landingpage.provider_personal_info') }}</h3>
                   
                    <!-- Professional Provider Information Cards -->
                    <div class="provider-details-grid">
                        <!-- Full Skills Card -->
                        @if(!empty($providerData['data']['skills']))
                        <div class="info-card-modern mb-4">
                            <div class="info-card-header-modern" style="opacity: 1 !important; visibility: visible !important; display: flex !important; justify-content: center !important; background: #3333ff !important; padding: 6px 15px !important; min-height: 40px !important;">
                                <i class="ri-tools-line me-2" style="opacity: 1 !important; visibility: visible !important; font-size: 16px;"></i>
                                <h5 class="mb-0" style="opacity: 1 !important; visibility: visible !important; color: #ffffff !important; font-size: 13px; font-weight: 700;">{{ __('landingpage.pd_full_skills') }}</h5>
                            </div>
                            <div class="info-card-body-modern">
                                <div class="skills-badge-container">
                                    @php
                                        $skillsData = $providerData['data']['skills'];
                                        
                                        // Try to decode JSON first
                                        if (is_string($skillsData)) {
                                            $decoded = json_decode($skillsData, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                $skills = $decoded;
                                            } else {
                                                // Not JSON, try comma-separated string
                                                $skills = array_map('trim', explode(',', $skillsData));
                                            }
                                        } elseif (is_array($skillsData)) {
                                            $skills = $skillsData;
                                        } else {
                                            $skills = [trim($skillsData)];
                                        }
                                    @endphp
                                    @foreach($skills as $skill)
                                        <span class="skill-badge-modern">{{ ucwords(trim($skill)) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Location Card -->
                        @if(isset($providerData['data']['city_name']) || isset($providerData['data']['country_name']))
                        <div class="info-card-modern mb-4">
                            <div class="info-card-header-modern" style="opacity: 1 !important; visibility: visible !important; display: flex !important; justify-content: center !important; background: #3333ff !important; padding: 6px 15px !important; min-height: 40px !important;">
                                <i class="ri-map-pin-line me-2" style="opacity: 1 !important; visibility: visible !important; font-size: 16px;"></i>
                                <h5 class="mb-0" style="opacity: 1 !important; visibility: visible !important; color: #ffffff !important; font-size: 13px; font-weight: 700;">{{ __('landingpage.pd_location') }}</h5>
                            </div>
                            <div class="info-card-body-modern">
                                <div class="info-value-modern">
                                    <i class="ri-map-pin-fill me-2 text-primary"></i>
                                    @if(isset($providerData['data']['city_name']) && isset($providerData['data']['country_name']))
                                        {{ $providerData['data']['city_name'] }}, {{ $providerData['data']['country_name'] }}
                                    @elseif(isset($providerData['data']['city_name']))
                                        {{ $providerData['data']['city_name'] }}
                                    @elseif(isset($providerData['data']['country_name']))
                                        {{ $providerData['data']['country_name'] }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Years of Experience Card -->
                        @if(!empty($providerData['data']['years_of_experience']))
                        @php
                            $yearsKey = $providerData['data']['years_of_experience'];
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
                        <div class="info-card-modern mb-4">
                            <div class="info-card-header-modern" style="opacity: 1 !important; visibility: visible !important; display: flex !important; justify-content: center !important; background: #3333ff !important; padding: 6px 15px !important; min-height: 40px !important;">
                                <i class="ri-time-line me-2" style="opacity: 1 !important; visibility: visible !important; font-size: 16px;"></i>
                                <h5 class="mb-0" style="opacity: 1 !important; visibility: visible !important; color: #ffffff !important; font-size: 13px; font-weight: 700;">{{ __('messages.years_of_experience') }}</h5>
                            </div>
                            <div class="info-card-body-modern">
                                <div class="info-value-modern">
                                    <span class="badge-modern bg-info-modern">{{ $yearsLabel }}</span>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Experience Card -->
                        @if(!empty($providerData['data']['experience']))
                        <div class="info-card-modern mb-4">
                            <div class="info-card-header-modern" style="opacity: 1 !important; visibility: visible !important; display: flex !important; justify-content: center !important; background: #3333ff !important; padding: 6px 15px !important; min-height: 40px !important;">
                                <i class="ri-briefcase-4-line me-2" style="opacity: 1 !important; visibility: visible !important; font-size: 16px;"></i>
                                <h5 class="mb-0" style="opacity: 1 !important; visibility: visible !important; color: #ffffff !important; font-size: 13px; font-weight: 700;">{{ __('landingpage.pd_experience') }}</h5>
                            </div>
                            <div class="info-card-body-modern">
                                @php
                                    $experienceData = $providerData['data']['experience'];
                                    $experienceText = '';

                                    if (is_string($experienceData)) {
                                        $decoded = json_decode($experienceData, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                            $experienceText = implode("\n\n", array_filter(array_map('trim', $decoded)));
                                        } else {
                                            // Free-text paragraph: do not split on commas (that broke prose into pills)
                                            $experienceText = trim($experienceData);
                                        }
                                    } elseif (is_array($experienceData)) {
                                        $experienceText = implode("\n\n", array_filter(array_map('trim', $experienceData)));
                                    } else {
                                        $experienceText = trim((string) $experienceData);
                                    }
                                @endphp
                                @if($experienceText !== '')
                                    <p class="provider-experience-prose mb-0">{!! nl2br(e($experienceText)) !!}</p>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Mobility Card -->
                        @if(!empty($providerData['data']['mobility']))
                        <div class="info-card-modern mb-4">
                            <div class="info-card-header-modern" style="opacity: 1 !important; visibility: visible !important; display: flex !important; justify-content: center !important; background: #3333ff !important; padding: 6px 15px !important; min-height: 40px !important;">
                                <i class="ri-car-line me-2" style="opacity: 1 !important; visibility: visible !important; font-size: 16px;"></i>
                                <h5 class="mb-0" style="opacity: 1 !important; visibility: visible !important; color: #ffffff !important; font-size: 13px; font-weight: 700;">{{ __('landingpage.sd_mobility') }}</h5>
                            </div>
                            <div class="info-card-body-modern">
                                <div class="info-value-modern">
                                    <span class="badge-modern bg-info-modern">{{ ucfirst(trim($providerData['data']['mobility'])) }}</span>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Education Card -->
                        @if(!empty($providerData['data']['education']))
                        <div class="info-card-modern mb-4">
                            <div class="info-card-header-modern" style="opacity: 1 !important; visibility: visible !important; display: flex !important; justify-content: center !important; background: #3333ff !important; padding: 6px 15px !important; min-height: 40px !important;">
                                <i class="ri-graduation-cap-line me-2" style="opacity: 1 !important; visibility: visible !important; font-size: 16px;"></i>
                                <h5 class="mb-0" style="opacity: 1 !important; visibility: visible !important; color: #ffffff !important; font-size: 13px; font-weight: 700;">{{ __('landingpage.pd_education') }}</h5>
                            </div>
                            <div class="info-card-body-modern">
                                <div class="skills-badge-container">
                                    @php
                                        $educationData = $providerData['data']['education'];
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
                                            $educationItems = [trim($educationData)];
                                        }
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
                                        <span class="skill-badge-modern">{{ $eduLabel }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Certificate Card -->
                        @if(!empty($providerData['data']['certification']))
                        <div class="info-card-modern mb-4">
                            <div class="info-card-header-modern" style="opacity: 1 !important; visibility: visible !important; display: flex !important; justify-content: center !important; background: #3333ff !important; padding: 6px 15px !important; min-height: 40px !important;">
                                <i class="ri-file-certificate-2-line me-2" style="opacity: 1 !important; visibility: visible !important; font-size: 16px;"></i>
                                <h5 class="mb-0" style="opacity: 1 !important; visibility: visible !important; color: #ffffff !important; font-size: 13px; font-weight: 700;">{{ __('landingpage.pd_certificate') }}</h5>
                            </div>
                            <div class="info-card-body-modern">
                                <div class="skills-badge-container">
                                    @php
                                        $certificationData = $providerData['data']['certification'];
                                        
                                        // Try to decode JSON first
                                        if (is_string($certificationData)) {
                                            $decoded = json_decode($certificationData, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                $certifications = $decoded;
                                            } else {
                                                // Not JSON, try comma-separated string
                                                $certifications = array_map('trim', explode(',', $certificationData));
                                            }
                                        } elseif (is_array($certificationData)) {
                                            $certifications = $certificationData;
                                        } else {
                                            $certifications = [trim($certificationData)];
                                        }
                                    @endphp
                                    @foreach($certifications as $cert)
                                        <span class="skill-badge-modern">{{ ucwords(trim($cert)) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- About Me Card -->
                        @if(!empty($providerData['data']['about_me']))
                        <div class="info-card-modern mb-4">
                            <div class="info-card-header-modern" style="opacity: 1 !important; visibility: visible !important; display: flex !important; justify-content: center !important; background: #3333ff !important; padding: 6px 15px !important; min-height: 40px !important;">
                                <i class="ri-user-heart-line me-2" style="opacity: 1 !important; visibility: visible !important; font-size: 16px;"></i>
                                <h5 class="mb-0" style="opacity: 1 !important; visibility: visible !important; color: #ffffff !important; font-size: 13px; font-weight: 700;">About Me</h5>
                            </div>
                            <div class="info-card-body-modern">
                                <div class="about-me-content">
                                    {!! $providerData['data']['about_me'] !!}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>





                    @if (!empty($providerData['service']))
                        <div class="row align-items-center pt-3 mb-5">
                            <div class="col-sm-9">
                                <h4 class="text-capitalize mb-0">{{ $providerData['data']['display_name'] }}
                                    {{ __('messages.services') }}</h4>
                            </div>
                            <div class="col-sm-3 mt-sm-0 mt-3 text-sm-end">
                                <a
                                    href="{{ route('service.list', ['provider_id' => $providerData['data']['id']]) }}">{{ __('messages.view_all') }}</a>
                            </div>
                        </div>
                        <remote-service-page
                            link="{{ route('category-detail-service.data', ['provider_id' => $providerData['data']['id'], 'limit' => 3]) }}"></remote-service-page>

                        <!-- <service-list-section :user_id="{{ json_encode($auth_user_id) }}"
                                        :service="{{ json_encode($providerData['service']) }}" :is_provider_detail={{ true }}
                                        :max_records="6" :favourite="{{ json_encode($favourite) }}"></service-list-section> -->
                    @endif

                    <div class="row align-items-center mb-5 mt-5">
                        <div class="col-sm-9">
                            @if ($providerData['data']['total_service_rating'] > 1)
                                <h4 class="mb-0">{{ $providerData['data']['total_service_rating'] }}
                                    {{ __('landingpage.reviews_for') }} {{ $providerData['data']['display_name'] }}
                                    {{ __('landingpage.services') }}</h4>
                            @elseif($providerData['data']['total_service_rating'] == 1)
                                <h4 class="mb-0">
                                    {{ $providerData['data']['total_service_rating'] }}{{ __('landingpage.reviews_for') }}
                                    {{ $providerData['data']['display_name'] }} {{ __('landingpage.services') }}</h4>
                            @endif
                        </div>
                        @if ($providerData['data']['total_service_rating'] !== 0)
                            <div class="col-sm-3 mt-sm-0 mt-3 text-sm-end">
                                <a
                                    href="{{ route('rating.all', ['provider_id' => $providerData['data']['id']]) }}">{{ __('messages.view_all') }}</a>
                            </div>
                        @endif
                    </div>

                    <ul class="comment-list list-inline m-0">
                        @forelse ($providerReviews as $ratingData)
                            <li class="comment mb-5 pb-5 border-bottom">
                                <div class="comment-box">
                                    <div
                                        class="d-flex align-items-sm-center align-items-start flex-sm-row flex-column justify-content-between gap-3">
                                        <div
                                            class="d-inline-flex align-items-sm-center align-items-start flex-sm-row flex-column gap-3">
                                            <div class="user-image flex-shrink-0">
                                                @php $cust = $ratingData->customer; @endphp
                                                <img src="{{ $cust ? (optional($cust)->login_type ? (optional($cust)->social_image ?? getSingleMedia($cust, 'profile_image', null)) : getSingleMedia($cust, 'profile_image', null)) : asset('images/default.png') }}"
                                                    class="avatar-70 object-cover rounded-circle"
                                                    alt="comment-user" />
                                            </div>
                                            <div class="comment-user-info">
                                                <h6 class="font-size-18 text-capitalize mb-2">
                                                    {{ optional($ratingData->customer)->display_name ?? __('messages.customer') }}</h6>
                                                <span class="text-primary">
                                                    <rating-component :readonly="true" :showrating="false"
                                                        :ratingvalue="{{ $ratingData->rating ?? 0 }}" />
                                                </span>
                                            </div>
                                        </div>
                                        <div class="date text-capitalize">
                                            {{ date($datetime->date_format ?? 'Y-m-d', strtotime($ratingData->created_at)) }}
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <p class="commnet-content m-0">
                                            {{ $ratingData->review ?: '-' }}
                                        </p>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="comment mb-5 pb-5">
                                <p class="text-muted m-0">{{ __('messages.no_reviews_yet') }}</p>
                            </li>
                        @endforelse
                    </ul>

                </div>
            </div>
        </div>

    </div>
    <div class="modal fade" id="chooseme" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content overflow-visible">
                <span class="text-primary custom-btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="41" viewBox="0 0 40 41"
                        fill="none">
                        <rect x="12" y="11.8381" width="17" height="17" fill="white"></rect>
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M12.783 4.17017H27.233C32.883 4.17017 36.6663 8.13683 36.6663 14.0368V27.6552C36.6663 33.5385 32.883 37.5035 27.233 37.5035H12.783C7.13301 37.5035 3.33301 33.5385 3.33301 27.6552V14.0368C3.33301 8.13683 7.13301 4.17017 12.783 4.17017ZM25.0163 25.8368C25.583 25.2718 25.583 24.3552 25.0163 23.7885L22.0497 20.8218L25.0163 17.8535C25.583 17.2885 25.583 16.3552 25.0163 15.7885C24.4497 15.2202 23.533 15.2202 22.9497 15.7885L19.9997 18.7535L17.033 15.7885C16.4497 15.2202 15.533 15.2202 14.9663 15.7885C14.3997 16.3552 14.3997 17.2885 14.9663 17.8535L17.933 20.8218L14.9663 23.7718C14.3997 24.3552 14.3997 25.2718 14.9663 25.8368C15.2497 26.1202 15.633 26.2718 15.9997 26.2718C16.383 26.2718 16.7497 26.1202 17.033 25.8368L19.9997 22.8885L22.9663 25.8368C23.2497 26.1385 23.6163 26.2718 23.983 26.2718C24.3663 26.2718 24.733 26.1202 25.0163 25.8368Z"
                            fill="currentColor">
                        </path>
                    </svg>
                </span>
                @if (isset($why_choose_me))
                    @if (isset($why_choose_me['about_description']))
                        <div class="modal-body">
                            <h6 class="text-capitalize mb-2">{{ __('landingpage.why_choose_me_title') }}</h6>
                            <p class="m-0">
                                {{ $why_choose_me['about_description'] }}
                            </p>
                    @endif
                    @if (isset($why_choose_me['reason']))
                        <h6 class="mt-3">{{ __('landingpage.reason') }}</h6>
                        <ul class="list-inline mt-2 mb-0 p-0">
                            @foreach ($why_choose_me['reason'] as $reason)
                                <li>
                                    <div class="d-flex gap-2">
                                        <span class="text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 16 16" fill="none">
                                                <g>
                                                    <path d="M5.5 8.5L7 10L10.5 6.5" stroke="currentColor"
                                                        stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round"></path>
                                                    <path
                                                        d="M8 14C11.3137 14 14 11.3137 14 8C14 4.68629 11.3137 2 8 2C4.68629 2 2 4.68629 2 8C2 11.3137 4.68629 14 8 14Z"
                                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round"></path>
                                                </g>
                                            </svg>
                                        </span>
                                        <span>{{ $reason }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
            </div>
            @endif
        </div>
    </div>
    </div>
    </div>
@endsection

@section('after_script')
<script>
window.__shareClickHandler = function(e, el) {
    try { e.preventDefault(); e.stopPropagation(); } catch (_) {}
    function openPopup(url) { window.open(url, '_blank', 'noopener,noreferrer,width=600,height=600'); }
    var platform = el.getAttribute('data-platform');
    var shareUrl = el.getAttribute('data-share-url') || window.location.href;
    if (platform === 'facebook') {
        var fbUrl = encodeURIComponent(shareUrl);
        var quote = encodeURIComponent(el.getAttribute('data-quote') || '');
        openPopup('https://www.facebook.com/sharer/sharer.php?u=' + fbUrl + (quote ? '&quote=' + quote : ''));
    } else if (platform === 'twitter') {
        var text = encodeURIComponent(el.getAttribute('data-text') || el.getAttribute('data-quote') || '');
        var url = encodeURIComponent(shareUrl);
        openPopup('https://twitter.com/intent/tweet?url=' + url + '&text=' + text);
    } else if (platform === 'linkedin') {
        openPopup('https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(shareUrl));
    } else if (platform === 'telegram') {
        var url = encodeURIComponent(shareUrl || window.location.href);
        var text = encodeURIComponent(el.getAttribute('data-quote') || '');
        openPopup('https://t.me/share/url?url=' + url + (text ? '&text=' + text : ''));
    }
    return false;
};
</script>
@endsection
