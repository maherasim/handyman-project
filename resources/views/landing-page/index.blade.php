@extends('landing-page.layouts.default')
@section('content')

    <!-- Banner -->
    <div class="padding-top-bottom-90 landing-hero-wrap">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-xl-6">
                    <div class="me-0 pe-0 me-xl-5 pe-xl-5">
                        @if ($sectionData && isset($sectionData['section_1']) && $sectionData['section_1']['section_1'] == 1)
                            @php
                                $heroTitle = $sectionData['section_1']['title'] ?? 'Repairs Done Right. Book in 60 Seconds.';
                                $heroDesc  = $sectionData['section_1']['description'] ?? 'Connect with vetted local pros for repairs, maintenance and home improvement. Transparent pricing, easy booking, quality guaranteed.';
                            @endphp
                            <div class="iq-title-box mb-4">
                                <span class="landing-hero-badge">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                    {{ __('landingpage.trusted_by_thousands') }}
                                </span>
                                <h1 class="landing-hero-title text-capitalize line-count-3 mb-0">
                                    {{ $heroTitle }}
                                    <span class="highlighted-text">
                                        <span class="highlighted-text-swipe"></span>
                                        <span class="highlighted-image">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="254" height="11"
                                                viewBox="0 0 254 11" fill="none">
                                                <path d="M2 9C3.11607 8.76081 129.232 -2.95948 252 4.4554"
                                                    stroke="currentColor" stroke-width="4" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                    </span>
                                </h1>
                                <p class="landing-hero-desc iq-title-desc line-count-3 mb-0">
                                    {{ $heroDesc }}
                                </p>
                                <!-- Premium Trust Stats Bar -->
                                <div class="trust-stats-row">
                                    <div class="trust-stat-card">
                                        <div class="trust-stat-icon-wrap" style="background: linear-gradient(135deg, rgba(255,193,7,0.15) 0%, rgba(255,193,7,0.08) 100%);">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="#f59e0b"/>
                                            </svg>
                                        </div>
                                        <div class="trust-stat-body">
                                            <span class="trust-stat-number">{{ number_format((float) $totalRating, 1) }}<span class="trust-stat-suffix">★</span></span>
                                            <span class="trust-stat-label">{{ __('landingpage.overall_rating') }}</span>
                                        </div>
                                    </div>

                                    <div class="trust-stat-divider"></div>

                                    <div class="trust-stat-card">
                                        <div class="trust-stat-icon-wrap" style="background: linear-gradient(135deg, rgba(34,197,94,0.15) 0%, rgba(34,197,94,0.08) 100%);">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="#22c55e" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                        <div class="trust-stat-body">
                                            @php $completedJobsRandom = rand(100, 200); @endphp
                                            <span class="trust-stat-number">{{ number_format($completedJobsRandom) }}<span class="trust-stat-suffix">+</span></span>
                                            <span class="trust-stat-label">{{ __('landingpage.completed_jobs') }}</span>
                                        </div>
                                    </div>

                                    <div class="trust-stat-divider"></div>

                                    <div class="trust-stat-card">
                                        <div class="trust-stat-icon-wrap" style="background: linear-gradient(135deg, rgba(51,51,255,0.15) 0%, rgba(99,102,241,0.08) 100%);">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="#3333ff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <circle cx="9" cy="7" r="4" stroke="#3333ff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" stroke="#3333ff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75" stroke="#3333ff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                        <div class="trust-stat-body">
                                            @php $localProsRandom = rand(200, 500); @endphp
                                            <span class="trust-stat-number">{{ number_format($localProsRandom) }}<span class="trust-stat-suffix">+</span></span>
                                            <span class="trust-stat-label">{{ __('landingpage.local_pros') }}</span>
                                        </div>
                                    </div>

                                    <div class="trust-stat-divider"></div>

                                    <div class="trust-stat-card">
                                        <div class="trust-stat-icon-wrap" style="background: linear-gradient(135deg, rgba(168,85,247,0.15) 0%, rgba(168,85,247,0.08) 100%);">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 2C6.48 2 2 5.82 2 10.5c0 2.61 1.35 4.95 3.5 6.52V21l4.5-2.5a13.9 13.9 0 0 0 2 .15c5.52 0 10-3.82 10-8.5S17.52 2 12 2z" fill="#a855f7" opacity="0.9"/>
                                            </svg>
                                        </div>
                                        <div class="trust-stat-body">
                                            <span class="trust-stat-number trust-stat-free">{{ __('landingpage.free_quotes') }}</span>
                                            <span class="trust-stat-label">{{ __('landingpage.no_obligation') }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Dynamic count (commented): uncomment and remove random block below to restore real booking count --}}
                                                            @php $liveCueCount = rand(80, 100); @endphp
                                <div class="landing-live-cue-pill mt-2">
                                    <span class="live-dot"></span>
                                    <span>{{ __('landingpage.live_cue_booked', ['count' => $liveCueCount]) }}</span>
                                </div>
                            </div>
                            <location-search :user_id="{{ json_encode($auth_user_id) }}"
                                :postjobservice="{{ $postjobservice }}"></location-search>
                        @endif

                    </div>
                </div>

                <div class="col-xl-6 px-xl-0 mt-xl-0 mt-5">
                    <div class="position-relative swiper provider-banner-slider overflow-hidden">
                        <div class="swiper-wrapper">
                            @foreach (($sectionData['section_1']['provider_id'] ?? []) as $providerId)
                                @php
                                    $user = App\Models\User::with('getServiceRating')
                                        ->where('user_type', 'provider')
                                        ->where('id', $providerId)
                                        ->where('status', 1)
                                        ->first();
                                    $providers_service_rating =
                                        isset($user->getServiceRating) && count($user->getServiceRating) > 0
                                            ? (float) number_format(max($user->getServiceRating->avg('rating'), 0), 2)
                                            : 0;
                                @endphp
                                @if ($user)
                                    <div class="swiper-slide">
                                        <div class="provider-banner-item position-relative">
                                            <img src="{{ getSingleMedia($user, 'profile_image', null) }}"
                                                alt="{{ $user->display_name ?? 'Provider' }}"
                                                class="provider-banner-image">
                                            <div class="provider-banner-name position-absolute bottom-0 start-0 end-0 text-center p-3" style="background: transparent !important; padding: 25px 15px !important;">
                                                <h5 class="provider-name-text text-white mb-0 fw-bold" style="display: inline-block !important; background: linear-gradient(135deg, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0.75) 100%) !important; padding: 12px 30px !important; border-radius: 50px !important; font-size: 1.4rem !important; letter-spacing: 0.8px !important; font-weight: 600 !important; text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6) !important; backdrop-filter: blur(8px) !important; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.1) inset !important; border: 1px solid rgba(255, 255, 255, 0.15) !important; color: #ffffff !important; margin: 0 !important;">{{ $user->display_name ?? 'Provider' }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <div class="swiper-button-next provider-nav-next"></div>
                        <div class="swiper-button-prev provider-nav-prev"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- @if (!empty($firstBookingCoupon))
        <div class="container py-3">
            <a href="{{ route('user.register') }}" class="d-block text-decoration-none">
                <div class="landing-first-booking-banner d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <span class="fw-semibold">{{ __('landingpage.first_booking_off', ['discount' => $firstBookingCoupon->discount_type === 'percentage' ? $firstBookingCoupon->discount . '%' : getPriceFormat($firstBookingCoupon->discount)]) }}</span>
                    <span class="btn btn-light btn-sm">{{ __('landingpage.first_booking_cta') }}</span>
                </div>
            </a>
        </div>
    @endif --}}

    <!-- Categories -->
    @if ($sectionData && isset($sectionData['section_2']) && $sectionData['section_2']['section_2'] == 1)
        <div class="section-padding">
            <div class="container">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="iq-title-box mb-0">
                        <h3 class="text-capitalize line-count-1">{{ $sectionData['section_2']['title'] }}
                            <div class="highlighted-text">
                                <span class="highlighted-text-swipe"></span>
                                <span class="highlighted-image">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="155" height="12"
                                        viewBox="0 0 155 12" fill="none">
                                        <path d="M2.5 9.5C3.16964 9.26081 78.8393 -2.45948 152.5 4.9554"
                                            stroke="currentColor" stroke-width="4" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                        </h3>
                    </div>
                    <a href="{{ route('category.list') }}"
                        class="btn btn-link p-0 text-capitalize flex-shrink-0 font-size-14">{{ __('messages.view_all') }}</a>
                </div>



                <div class="category-grid-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-top: 30px;">
                    @foreach ($categoryrequest as $items)
                        <a href="{{ route('category.detail', $items->id) }}" class="category-card-link" style="text-decoration: none;">
                            <div class="category-card-item"
                                style="padding: 18px; text-align: center; background-color: #fff; border: 1px solid #e9ecef; border-radius: 10px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06); transition: all 0.2s ease; height: 100%; display: flex; flex-direction: column;">
                                <div class="category-image-wrapper"
                                    style="width: 100%; height: 130px; background: linear-gradient(135deg, rgba(255, 0, 0, 0.05) 0%, rgba(95, 96, 185, 0.05) 100%); border-radius: 8px; display: flex; justify-content: center; align-items: center; margin-bottom: 12px; flex-shrink: 0;">
                                    <img src="{{ getSingleMedia($items, 'category_image', null) }}" alt="icon"
                                        class="img-fluid" style="max-width: 70px; max-height: 70px; object-fit: contain;">
                                </div>
                                <h5 class="categories-name text-capitalize mb-1 line-count-1"
                                    style="font-size: 15px; font-weight: 600; color: #2c3e50; margin: 0; flex-grow: 0;">{{ $items->name }}</h5>
                                <p class="categories-desc mb-0 text-capitalize line-count-2"
                                    style="font-size: 12px; color: #6c757d; line-height: 1.4; margin-top: 6px; flex-grow: 1;">{{ $items->description }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>

                <style>
                    .category-card-link:hover .category-card-item {
                        transform: translateY(-4px);
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
                        border-color: transparent;
                        background: linear-gradient(135deg, rgba(255, 0, 0, 0.02) 0%, rgba(95, 96, 185, 0.02) 100%);
                    }

                    .category-card-link:hover .category-image-wrapper {
                        background: linear-gradient(135deg, rgba(255, 0, 0, 0.1) 0%, rgba(95, 96, 185, 0.1) 100%);
                    }

                    @media (max-width: 768px) {
                        .category-grid-container {
                            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                            gap: 15px;
                        }
                        .category-card-item {
                            padding: 15px !important;
                        }
                        .category-image-wrapper {
                            height: 110px !important;
                        }
                    }

                    @media (max-width: 480px) {
                        .category-grid-container {
                            grid-template-columns: repeat(2, 1fr);
                            gap: 12px;
                        }
                    }
                </style>





            </div>
        </div>
    @endif
 <!-- Two paths: Book a service / Post a job -->
 <div class="section-padding bg-light landing-two-path-wrap landing-animate-in">
    <div class="container">
        <p class="landing-two-path-eyebrow">{{ __('landingpage.two_path_eyebrow') }}</p>
        @if (isset($categoryrequest) && $categoryrequest->count() > 0)
            <p class="landing-two-path-browse-label">{{ __('landingpage.popular_services_browse') }}</p>
            <div class="landing-category-pills d-flex flex-wrap justify-content-center gap-2 mb-4">
                @foreach ($categoryrequest->take(6) as $cat)
                    <a href="{{ route('category.detail', $cat->id) }}" class="landing-pill">{{ $cat->name }}</a>
                @endforeach
            </div>
        @endif
        <div class="row g-4">
            <div class="col-md-6">
                <a href="{{ route('service.list') }}" class="text-decoration-none d-block h-100">
                    <div class="landing-two-path-card card-popular">
                        <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
                            <div class="landing-two-path-icon-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </div>
                            <span class="landing-two-path-badge">{{ __('landingpage.most_popular') }}</span>
                            @php $bookedLast24hRandom = rand(80, 100); @endphp
                            <span class="landing-two-path-live ms-auto">{{ __('landingpage.booked_last_24h', ['count' => $bookedLast24hRandom]) }}</span>
                        </div>
                        <h3 class="landing-two-path-card-title">{{ __('landingpage.book_a_service') }}</h3>
                        <p class="landing-two-path-desc">{{ __('landingpage.book_a_service_lead') }}</p>
                        <span class="btn btn-primary landing-two-path-btn">{{ __('landingpage.browse_services') }}</span>
                    </div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ route('post.job.list') }}" class="text-decoration-none d-block h-100">
                    <div class="landing-two-path-card">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="landing-two-path-icon-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                            </div>
                        </div>
                        <h3 class="landing-two-path-card-title">{{ __('landingpage.post_a_job') }}</h3>
                        <p class="landing-two-path-desc">{{ __('landingpage.post_a_job_lead') }}</p>
                        <span class="btn btn-primary landing-two-path-btn">{{ __('landingpage.post_a_job') }}</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
    {{-- JOb Requests --}}
    @if ($sectionData && isset($sectionData['section_10']) && $sectionData['section_10']['section_10'] == 1)
        <style>
            /* Professional Job Card Styling */
            .job-card {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .job-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
                border-color: #667eea;
            }

            .job-card:hover .image-container img {
                transform: scale(1.05);
            }

            .heart-icon:hover {
                background: #667eea !important;
                transform: scale(1.1);
            }

            .heart-icon:hover i {
                color: white !important;
            }

            .social-link:hover {
                background: #667eea !important;
                transform: translateY(-2px);
            }

            .social-link:hover img {
                filter: brightness(0) invert(1);
            }

            .price-badge {
                animation: fadeInUp 0.6s ease-out;
            }

            .status-badge {
                animation: fadeInRight 0.6s ease-out;
            }

            .job-card .dropdown-toggle-no-caret::after {
                display: none !important;
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes fadeInRight {
                from {
                    opacity: 0;
                    transform: translateX(20px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                .job-card {
                    margin-bottom: 20px;
                }
                
                .card-content {
                    padding: 20px !important;
                }
                
                .job-title {
                    font-size: 16px !important;
                    min-height: 44px !important;
                }
            }

            @media (max-width: 576px) {
                .col-12 {
                    padding: 0 10px;
                }
                
                .card-content {
                    padding: 16px !important;
                }
            }
        </style>
        <div class="section-padding">
            <div class="container">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="iq-title-box mb-0">
                        <h3 class="text-capitalize line-count-1">{{ $sectionData['section_10']['title'] }}</h3>
                        <div class="highlighted-text">
                            <span class="highlighted-text-swipe"></span>
                            <span class="highlighted-image">
                                <svg xmlns="http://www.w3.org/2000/svg" width="155" height="12" viewBox="0 0 155 12"
                                    fill="none">
                                    <path d="M2.5 9.5C3.16964 9.26081 78.8393 -2.45948 152.5 4.9554" stroke="currentColor"
                                        stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('job.data') }}"
                        class="btn btn-link p-0 text-capitalize flex-shrink-0 font-size-14">{{ __('messages.view_all') }}</a>
                </div>

                <!-- Display Job Requests Cards with Professional Design -->
                <div class="row">
                    @foreach ($jobRequests as $jobRequest)
                        @php
                            $homePosterId = $jobRequest->customer_id;
                            $canHomeJobUgc = auth()->check()
                                && $homePosterId
                                && \App\Support\UgcListing::canReportPostJob(auth()->user(), $jobRequest);
                        @endphp
                        <div class="col-lg-3 col-md-6 col-12 mb-3">
                                <div class="job-card h-100" style="
                                    background: #FFFFFF;
                                    border: 1px solid #E8E9EC;
                                    border-radius: 16px;
                                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                                    transition: all 0.3s ease;
                                    overflow: hidden;
                                    position: relative;
                                ">
                                    <a href="{{ route('job.details', $jobRequest->id) }}" class="text-decoration-none text-reset d-block">
                                    <!-- Card Image Container -->
                                    <div class="image-container" style="position: relative; height: 120px; overflow: hidden;">
                                        @if(!empty($jobRequest->image))
                                            <img src="{{ asset('storage/' . $jobRequest->image) }}" alt="Job Image"
                                                 style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                                        @else
                                            <img class="default-image" 
                                            src="{{ asset('images/post-job/ac_refresh_and_revive.png') }}"
                                                 alt="Default Image"
                                                 style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                                        @endif
                                        
                                        <!-- Price Badge -->
                                        <div class="price-badge" style="
                                            position: absolute;
                                            bottom: 8px;
                                            left: 8px;
                                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                            color: white;
                                            padding: 4px 8px;
                                            border-radius: 12px;
                                            font-weight: 600;
                                            font-size: 15px;
                                            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
                                            backdrop-filter: blur(10px);
                                        ">
                                            €{{ number_format($jobRequest->price) }} / {{ ucfirst($jobRequest->price_type ?? 'fixed') }}
                                        </div>
                                        
                                        <!-- Heart Icon -->
                                        <div class="heart-icon" style="
                                            position: absolute;
                                            top: 8px;
                                            right: 8px;
                                            width: 28px;
                                            height: 28px;
                                            background: rgba(255, 255, 255, 0.9);
                                            border-radius: 50%;
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;
                                            backdrop-filter: blur(10px);
                                            transition: all 0.3s ease;
                                        ">
                                            <i class='bx bx-heart' style="color: #667eea; font-size: 14px;"></i>
                                    </div>
                                    </div>
                                    
                                    <!-- Card Content -->
                                    <div class="card-content" style="padding: 12px;">
                                        <!-- Job Title -->
                                        <h5 class="job-title" style="
                                            font-size: 14px;
                                            font-weight: 700;
                                            color: #1a1a1a;
                                            margin-bottom: 6px;
                                            line-height: 1.2;
                                            display: -webkit-box;
                                            -webkit-line-clamp: 2;
                                            -webkit-box-orient: vertical;
                                            overflow: hidden;
                                            min-height: 34px;
                                        ">
                                            {{ $jobRequest->title }}
                                            </h5>
                                           
                                        <!-- Location -->
                                        <div class="location-info" style="margin-bottom: 8px;">
                                            <div class="d-flex align-items-center" style="gap: 5px;">
                                                <i class='bx bx-map-pin' style="color: #667eea; font-size: 12px;"></i>
                                                <span style="
                                                    font-size: 12px;
                                                    color: #666;
                                                    font-weight: 500;
                                                ">
                                                    {{ $jobRequest->city ? $jobRequest->city->name : 'City' }}, {{ $jobRequest->country ? $jobRequest->country->name : 'Country' }}
                                            </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Published Date -->
                                        <div class="published-info" style="margin-bottom: 8px;">
                                            <div class="d-flex align-items-center" style="gap: 5px;">
                                                <i class='bx bx-calendar' style="color: #8e8e93; font-size: 11px;"></i>
                                                <span style="
                                                    font-size: 11px;
                                                    color: #8e8e93;
                                                    font-weight: 400;
                                                ">
                                                    {{ $jobRequest->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Views Count -->
                                        <div class="views-info" style="margin-bottom: 8px;">
                                            <div class="d-flex align-items-center" style="gap: 5px;">
                                                <i class='bx bx-show' style="color: #8e8e93; font-size: 11px;"></i>
                                                <span style="
                                                    font-size: 11px;
                                                    color: #8e8e93;
                                                    font-weight: 400;
                                                ">
                                                    {{ number_format($jobRequest->total_views ?? 0) }} views
                                                </span>
                                        </div>
                                        </div>
                                    </div>
                                    </a>
                                    <div class="card-content" style="padding: 0 12px 12px;">
                                        <!-- Customer Info -->
                                        <div class="customer-info" style="margin-bottom: 8px;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center" style="gap: 6px;">
                                                <div class="customer-avatar" style="
                                                    width: 28px;
                                                    height: 28px;
                                                    border-radius: 50%;
                                                    overflow: hidden;
                                                    border: 2px solid #f0f0f0;
                                                ">
                                                    <img src="{{ getSingleMedia($jobRequest->customer,'profile_image', null) ?? 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQnnM0ib-pYCZg4DbbB_T5_mfxpqrDHYXFLy208bjvHjIM5q1FF4lzLvNFp2qZ5Eo11orA&usqp=CAU' }}"
                                                        alt="Customer" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                                <div class="customer-details">
                                                    <div style="
                                                        font-size: 12px;
                                                        font-weight: 600;
                                                        color: #1a1a1a;
                                                        margin-bottom: 1px;
                                                    ">
                                                        {{ $jobRequest->customer->display_name ?? $jobRequest->customer->username ?? 'Unknown' }}
                                                    </div>
                                                    <div style="
                                                        font-size: 10px;
                                                        color: #8e8e93;
                                                        font-weight: 400;
                                                    ">
                                                         {{ optional($jobRequest->customer->city)->name ?? 'Unknown' }}
                                                         - {{ optional($jobRequest->customer->country)->name ?? 'Unknown' }}
                                                    </div>
                                                </div>
                                                </div>
                                                @if($canHomeJobUgc)
                                                <div class="position-relative ugc-job-actions flex-shrink-0" style="z-index: 50;">
                                                    <button type="button" class="btn btn-link text-muted text-decoration-none border-0 p-1 bg-transparent" aria-label="{{ __('messages.ugc_report') }}" onclick="
                                                         event.preventDefault();
                                                         event.stopPropagation();
                                                         var wrap = this.closest('.ugc-job-actions');
                                                         var menu = wrap ? wrap.querySelector('.ugc-dropdown-menu-job') : null;
                                                         if (!menu) { return; }
                                                         var isVisible = (menu.style.display === 'block') || (window.getComputedStyle(menu).display === 'block');
                                                         document.querySelectorAll('.ugc-dropdown-menu-job').forEach(function(el) { el.style.display = 'none'; });
                                                         menu.style.display = isVisible ? 'none' : 'block';
                                                     " onmousedown="event.stopPropagation();">
                                                         <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                            <circle cx="5" cy="12" r="2" fill="currentColor"/>
                                                            <circle cx="12" cy="12" r="2" fill="currentColor"/>
                                                            <circle cx="19" cy="12" r="2" fill="currentColor"/>
                                                         </svg>
                                                     </button>
                                                     <ul class="dropdown-menu shadow border-0 ugc-dropdown-menu-job" style="display: none; position: absolute; right: 0; top: 100%; z-index: 1000; min-width: 150px; font-size: 14px; margin-top: 5px; background: white; border-radius: 8px; list-style: none; padding: 0.5rem 0; text-align: left;">
                                                         <li>
                                                            <a class="dropdown-item py-2 text-secondary px-3 d-block" href="javascript:void(0)" onclick="event.preventDefault(); event.stopPropagation(); this.closest('.ugc-dropdown-menu-job').style.display='none'; if(window.triggerUgcReportPostJob) window.triggerUgcReportPostJob({{ $jobRequest->id }}, this);">
                                                                <i class="fas fa-flag fa-fw me-2"></i>{{ __('messages.ugc_report') }}
                                                             </a>
                                                         </li>
                                                         <li>
                                                             <a class="dropdown-item py-2 text-danger px-3 d-block" href="javascript:void(0)" onclick="event.preventDefault(); event.stopPropagation(); this.closest('.ugc-dropdown-menu-job').style.display='none'; if(window.triggerUgcBlock) window.triggerUgcBlock({{ $homePosterId }}, this, 'customer');">
                                                                 <i class="fas fa-ban fa-fw me-2"></i>{{ __('messages.ugc_block_customer') }}
                                                             </a>
                                                         </li>
                                                     </ul>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Status Badge -->
                                        <div class="status-section" style="margin-bottom: 8px;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span style="
                                                    font-size: 10px;
                                                    color: #8e8e93;
                                                    font-weight: 500;
                                                    text-transform: uppercase;
                                                    letter-spacing: 0.5px;
                                                ">
                                                    Job Type 
                                                </span>
                                                <span class="status-badge" style="
                                                padding: 3px 6px;
                                                border-radius: 8px;
                                                font-size: 9px;
                                                font-weight: 600;
                                                text-transform: capitalize;
                                                letter-spacing: 0.5px;
                                                background: #e8f5e8;
                                                color: #2d5a2d;
                                            ">
                                                {{ ucfirst($jobRequest->type ?? 'N/A') }}
                                            </span>
                                            
                                            </div>
                                        </div>
                                        
                                        <!-- Social Icons -->
                                        <div class="social-icons" style="
                                            display: flex;
                                            align-items: center;
                                            gap: 6px;
                                            padding-top: 8px;
                                            border-top: 1px solid #f0f0f0;
                                        ">
                                            <span role="button" tabindex="0" class="social-link share-link"
                                                  data-platform="facebook"
                                                  data-share-url="{{ route('job.details', $jobRequest->id) }}?v={{ optional($jobRequest->updated_at)->timestamp ?? time() }}"
                                                  data-quote="{{ $jobRequest->title }} • €{{ number_format($jobRequest->price) }} • {{ ucfirst($jobRequest->price_type ?? 'fixed') }} • {{ data_get($jobRequest,'city.name','City') }}, {{ data_get($jobRequest,'country.name','Country') }}"
                                                  onclick="return window.__shareClickHandler(event, this);"
                                                  style="width: 24px; height: 24px; border-radius: 5px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; cursor: pointer;">
                                                <img src="{{ asset('assets/fb.png') }}?v=20260303"
                                                    alt="Facebook" style="width: 24px; height: 24px;">
                                            </span>
                                            <span role="button" tabindex="0" class="social-link share-link"
                                                  data-platform="telegram"
                                                  data-share-url="{{ route('job.details', $jobRequest->id) }}?v={{ optional($jobRequest->updated_at)->timestamp ?? time() }}"
                                                  data-quote="{{ $jobRequest->title }} • €{{ number_format($jobRequest->price) }} • {{ ucfirst($jobRequest->price_type ?? 'fixed') }} • {{ data_get($jobRequest,'city.name','City') }}, {{ data_get($jobRequest,'country.name','Country') }}"
                                                  onclick="return window.__shareClickHandler(event, this);"
                                                  style="width: 24px; height: 24px; border-radius: 5px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; cursor: pointer;">
                                                <img src="{{ asset('assets/telegram.png') }}?v=20260303"
                                                    alt="{{ __('landingpage.pd_alt_telegram') }}" style="width: 24px; height: 24px; object-fit: contain;">
                                            </span>
                                            <span role="button" tabindex="0" class="social-link share-link"
                                                  data-platform="twitter"
                                                  data-share-url="{{ route('job.details', $jobRequest->id) }}?v={{ optional($jobRequest->updated_at)->timestamp ?? time() }}"
                                                  data-text="{{ $jobRequest->title }} • €{{ number_format($jobRequest->price) }} • {{ ucfirst($jobRequest->price_type ?? 'fixed') }} • {{ data_get($jobRequest,'city.name','City') }}, {{ data_get($jobRequest,'country.name','Country') }}"
                                                  onclick="return window.__shareClickHandler(event, this);"
                                                  style="width: 24px; height: 24px; border-radius: 5px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; cursor: pointer;">
                                                <img src="{{ asset('assets/twiter.png') }}?v=20260303"
                                                    alt="Twitter" style="width: 24px; height: 24px; object-fit: contain;">
                                            </span>
                                            <span role="button" tabindex="0" class="social-link share-link"
                                                  data-platform="linkedin"
                                                  data-share-url="{{ route('job.details', $jobRequest->id) }}?v={{ optional($jobRequest->updated_at)->timestamp ?? time() }}"
                                                  onclick="return window.__shareClickHandler(event, this);"
                                                  style="width: 24px; height: 24px; border-radius: 5px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; cursor: pointer;">
                                                <img src="{{ asset('assets/linkedIn.png') }}?v=20260303"
                                                    alt="LinkedIn" style="width: 24px; height: 24px; object-fit: contain;">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

  <!-- How it works -->
  <div class="section-padding bg-white landing-how-wrap landing-animate-in">
    <div class="container position-relative">
        <h2 class="landing-how-title">{{ __('landingpage.how_it_works_title') }}</h2>
        <p class="landing-how-lead">{{ __('landingpage.how_it_works_lead') }}</p>
        <div class="landing-how-connector d-none d-lg-block" aria-hidden="true"></div>
        <div class="row g-0">
            <div class="col-6 col-lg-3">
                <div class="landing-how-step">
                    <div class="step-num">1</div>
                    <h4 class="landing-how-step-title">{{ __('landingpage.how_step_1') }}</h4>
                    <p class="landing-how-step-desc">{{ __('landingpage.how_step_1_desc') }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="landing-how-step">
                    <div class="step-num">2</div>
                    <h4 class="landing-how-step-title">{{ __('landingpage.how_step_2') }}</h4>
                    <p class="landing-how-step-desc">{{ __('landingpage.how_step_2_desc') }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="landing-how-step">
                    <div class="step-num">3</div>
                    <h4 class="landing-how-step-title">{{ __('landingpage.how_step_3') }}</h4>
                    <p class="landing-how-step-desc">{{ __('landingpage.how_step_3_desc') }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="landing-how-step">
                    <div class="step-num">4</div>
                    <h4 class="landing-how-step-title">{{ __('landingpage.how_step_4') }}</h4>
                    <p class="landing-how-step-desc">{{ __('landingpage.how_step_4_desc') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>







    <!-- Service -->
    <div class="section-padding section-padding--less-top bg-light our-service">
        <div class="container">
            @if ($sectionData && isset($sectionData['section_3']) && $sectionData['section_3']['section_3'] == 1)
                <div>
                    <div class="service-img d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="iq-title-box mb-0">
                            <h3 class="text-capitalize line-count-1">{{ $sectionData['section_3']['title'] }}
                                <div class="highlighted-text">
                                    <span class="highlighted-text-swipe"></span>
                                    <span class="highlighted-image">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="155" height="12"
                                            viewBox="0 0 155 12" fill="none">
                                            <path d="M2.5 9.5C3.16964 9.26081 78.8393 -2.45948 152.5 4.9554"
                                                stroke="currentColor" stroke-width="4" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </div>
                            </h3>
                        </div>
                        <a href="{{ route('service.list') }}"
                            class="btn btn-link p-0 flex-shrink-0">{{ __('messages.view_all') }}</a>
                    </div> 
                    <div class="container">
                        <div class="row">
                            @foreach ($servicerequest as $data)
                                <div class="col-md-3"> <!-- Changed from col-md-4 to col-md-3 -->
                                    <div class="service-box-card bg-white rounded-3 mb-5 shadow-sm"
                                        data-service-id="{{ $data->id }}">
                                        <div class="iq-image position-relative" style="height: 200px; width: 100%; overflow: hidden; border-radius: 0.5rem;">
                                            @if ($data->visit_type == 'ONLINE')
                                                <span class="online-service"></span>
                                            @endif
                                            <a href="{{ route('service.detail', $data->id) }}" class="service-img d-block w-100 h-100">
                                                <img src="{{ getSingleMedia($data, 'service_attachment', null) }}"
                                                    alt="service"
                                                    class="w-100 h-100" style="object-fit: cover;" onerror="this.src='{{ asset('images/default.png') }}'">
                                            </a>

                                            @if (auth()->check() && auth()->user()->hasRole('user'))
                                                @if ($servicerequest->isEmpty())
                                                    <form method="POST" id="favoriteForm">
                                                        @csrf
                                                        <input type="hidden" name="service_id" class="service_id"
                                                            value="{{ $data->id }}">
                                                        <input type="hidden" name="user_id" id="user_id"
                                                            value="{{ Auth::user()->id }}">
                                                        <button type="button"
                                                            class="btn-link serv-whishlist text-primary save_fav">
                                                            <svg width="12" height="13" viewBox="0 0 12 13"
                                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                                    d="M1.43593 6.29916C0.899433 4.62416 1.52643 2.70966 3.28493 2.14316C4.20993 1.84466 5.23093 2.02066 5.99993 2.59916C6.72743 2.03666 7.78593 1.84666 8.70993 2.14316C10.4684 2.70966 11.0994 4.62416 10.5634 6.29916C9.72843 8.95416 5.99993 10.9992 5.99993 10.9992C5.99993 10.9992 2.29893 8.98516 1.43593 6.29916Z"
                                                                    stroke="currentColor" stroke-width="1.5"
                                                                    stroke-linecap="round" stroke-linejoin="round" />
                                                                <path
                                                                    d="M8 3.84998C8.535 4.02298 8.913 4.50048 8.9585 5.06098"
                                                                    stroke="currentColor" stroke-width="1.5"
                                                                    stroke-linecap="round" stroke-linejoin="round" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" id="favoriteForm">
                                                        @csrf
                                                        <input type="hidden" name="service_id" class="service_id"
                                                            value="{{ $data->id }}">
                                                        <input type="hidden" name="user_id" id="user_id"
                                                            value="{{ Auth::user()->id }}">
                                                        <button type="button"
                                                            class="btn-link serv-whishlist text-primary delete_fav">
                                                            <svg width="12" height="13" viewBox="0 0 12 13"
                                                                fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                                    d="M1.43593 6.29916C0.899433 4.62416 1.52643 2.70966 3.28493 2.14316C4.20993 1.84466 5.23093 2.02066 5.99993 2.59916C6.72743 2.03666 7.78593 1.84666 8.70993 2.14316C10.4684 2.70966 11.0994 4.62416 10.5634 6.29916C9.72843 8.95416 5.99993 10.9992 5.99993 10.9992C5.99993 10.9992 2.29893 8.98516 1.43593 6.29916Z"
                                                                    stroke="currentColor" stroke-width="1.5"
                                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                                                <path
                                                                    d="M8 3.84998C8.535 4.02298 8.913 4.50048 8.9585 5.06098"
                                                                    stroke="currentColor" stroke-width="1.5"
                                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                <form method="GET" id="favoriteForm"
                                                    action="{{ route('login') }}">
                                                    @csrf
                                                    <button type="submit" class="btn-link serv-whishlist text-primary">
                                                        <svg width="12" height="13" viewBox="0 0 12 13"
                                                            fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                                d="M1.43593 6.29916C0.899433 4.62416 1.52643 2.70966 3.28493 2.14316C4.20993 1.84466 5.23093 2.02066 5.99993 2.59916C6.72743 2.03666 7.78593 1.84666 8.70993 2.14316C10.4684 2.70966 11.0994 4.62416 10.5634 6.29916C9.72843 8.95416 5.99993 10.9992 5.99993 10.9992C5.99993 10.9992 2.29893 8.98516 1.43593 6.29916Z"
                                                                stroke="currentColor" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                            <path d="M8 3.84998C8.535 4.02298 8.913 4.50048 8.9585 5.06098"
                                                                stroke="currentColor" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                            <!-- Reworked Price/Type Badge (removed image background) -->
                                            <div style="
                                                position: absolute;
                                                bottom: 10px;
                                                left: 10px;
                                                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                                color: #fff;
                                                padding: 6px 12px;
                                                border-radius: 12px;
                                                font-weight: 700;
                                                font-size: 13px;
                                                box-shadow: 0 6px 18px rgba(102, 126, 234, 0.35);
                                                backdrop-filter: blur(8px);
                                            ">
                                                @if($data->price==0)
                                                    Free
                                                @else
                                                    {{ getPriceFormat($data->price) }} @if(!empty($data->type)) / {{ ucfirst($data->type) }} @endif
                                                @endif
                                            </div>
                                        </div>

                                        {{-- @php
                                            $fullTitle = trim($data->name ?? '');
                                            $words = preg_split('/\s+/', $fullTitle, -1, PREG_SPLIT_NO_EMPTY);
                                            $shortTitle = implode(' ', array_slice($words, 0, 4));
                                            $hasMoreTitle = count($words) > 4;
                                        @endphp --}}
                                        <a href="{{ route('service.detail', $data->id) }}" class="service-heading mt-2 d-block p-0 text-decoration-none">
                                            
                                            <h5 class="job-title" style="
                                            font-size: 14px;
                                            font-weight: 700;
                                            color: #1a1a1a;
                                            margin-bottom: 6px;
                                            line-height: 1.2;
                                            display: -webkit-box;
                                            -webkit-line-clamp: 2;
                                            -webkit-box-orient: vertical;
                                            overflow: hidden;
                                            min-height: 34px;
                                        ">
                                            {{ $data->name }}
                                            </h5>
                                        </a>
                                        {{-- @if($hasMoreTitle)
                                            <button type="button" class="btn btn-link p-0 ms-1 see-more-title" data-target="#s3-title-{{ $data->id }}">{{ __('See more') }}</button>
                                        @endif --}}
                                        <h5 class="mt-0 mb-0 text-truncate" style="font-size:12px;">
                                            <span style="font-size: 12px;">{{ optional($data->city)->name ?? 'City' }}, {{ optional($data->country)->name ?? 'Country' }}</span>
                                        </h5>
                                         




                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ getSingleMedia($data->providers, 'profile_image', null) }}" alt="service" class="img-fluid rounded-3 object-cover avatar-24">
                                                <a href="{{ route('provider.detail', $data->providers->id) }}">
                                                    <span class="font-size-14 service-user-name">{{ $data->providers->display_name }}</span>
                                                </a>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-end">
                                                @php
                                                    $plan_icon = asset('images/freepng.png');
                                                    $provider = $data->providers;
                                                    if ($provider && $provider->providerSubscription) {
                                                        $rawPlan = strtolower(trim($provider->providerSubscription->plan_type ?? $provider->providerSubscription->title ?? ''));
                                                        if (str_contains($rawPlan, 'silver')) {
                                                            $plan_icon = asset('images/icon/silverpng.png');
                                                        } elseif (str_contains($rawPlan, 'gold')) {
                                                            $plan_icon = asset('images/goldpng.png');
                                                        }
                                                    }

                                                    $providerId = optional($data->providers)->id;
                                                    $isAllVerified = $providerId && function_exists('verify_provider_document') ? verify_provider_document($providerId) : false;
                                                @endphp
                                                <img src="{{ $plan_icon }}" alt="plan" style="width: 26px; height: 26px; margin-right: 10px;">
                                                @if ($isAllVerified)
                                                    <img src="{{ asset('images/icon/verifiedpng.png') }}" alt="verified" style="width: 26px; height: 26px;">
                                                @else
                                                    <img src="{{ asset('images/icon/notverifiedpng.png') }}" alt="not verified" style="width: 26px; height: 26px;">
                                                @endif
                                            </div>
                                        </div>
                                        @include('partials.landing-service-ugc-dropdown', ['service' => $data])
                                        
                                        <div class="stats-section">
                                            <div class="row g-2 text-center">
                                                <div class="col-4">
                                                    <div class="stats-item">
                                                        <div class="d-flex align-items-center justify-content-center mb-1">
                                                            <svg  style="padding-right: 3px" width="16" height="16" viewBox="0 0 24 24" fill="none" class="stats-icon text-warning" xmlns=" "><path d="M12 2L15.09 8.26L22 9L17 14L18.18 21L12 17.77L5.82 21L7 14L2 9L8.91 8.26L12 2Z" fill="currentColor"/></svg>
                                                            <span class="stats-value" style="font-size: 11px;">{{ round($data->avg_rating ?? 0, 1) }}</span>
                                                        </div>
                                                        <div class="stats-label" style="font-size: 11px;">
                                                            <a href="{{ route('rating.all', ['service_id' => $data->id]) }}" class="text-decoration-none text-muted">({{ $data->total_reviews ?? 0 }} {{ ($data->total_reviews ?? 0) > 1 ? __('messages.reviews') : __('messages.review') }})</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="stats-item">
                                                        <div class="d-flex align-items-center justify-content-center mb-1">
                                                            <svg  style="padding-right: 3px" width="16" height="16" viewBox="0 0 24 24" fill="none" class="stats-icon text-success" xmlns=" "><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                            <span class="stats-value" style="font-size: 11px;">{{ $data->booking_count ?? 0 }}</span>
                                                        </div>
                                                        <div class="stats-label" style="font-size: 11px;">Bookings</div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="stats-item">
                                                        <div class="d-flex align-items-center justify-content-center mb-1">
                                                            <svg  style="padding-right: 3px" width="16" height="16" viewBox="0 0 24 24" fill="none" class="stats-icon text-info" xmlns=" "><path d="M12 5c-7.633 0-10 7-10 7s2.367 7 10 7 10-7 10-7-2.367-7-10-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Zm0-8a3 3 0 1 0 .002 6.002A3 3 0 0 0 12 9Z" fill="currentColor"/></svg>
                                                            <span class="stats-value" style="font-size: 11px;">{{ $data->total_views ?? 0 }}</span>
                                                        </div>
                                                        <div class="stats-label" style="font-size: 11px;">Views</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        


                                        <div class="d-flex mt-3 " style="gap: 18px; justify-content: center;">
                                            <span role="button" tabindex="0" class="social-link share-link"
                                                  data-platform="facebook"
                                                  data-share-url="{{ route('service.detail', $data->id) }}?v={{ optional($data->updated_at)->timestamp ?? time() }}"
                                                  data-quote="{{ Str::limit($data->name, 80) }} • {{ getPriceFormat($data->price) }} • {{ ucfirst($data->type) }} • {{ $data->city->name ?? 'City' }}, {{ $data->country->name ?? 'Country' }}"
                                                  onclick="return window.__shareClickHandler(event, this);">
                                                <img src="{{ asset('assets/fb.png') }}?v=20260303" style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt="Facebook">
                                            </span>
                                            <span role="button" tabindex="0" class="social-link share-link"
                                                  data-platform="telegram"
                                                  data-share-url="{{ route('service.detail', $data->id) }}?v={{ optional($data->updated_at)->timestamp ?? time() }}"
                                                  data-quote="{{ Str::limit($data->name, 80) }} • {{ getPriceFormat($data->price) }} • {{ ucfirst($data->type) }} • {{ $data->city->name ?? 'City' }}, {{ $data->country->name ?? 'Country' }}"
                                                  onclick="return window.__shareClickHandler(event, this);">
                                                <img src="{{ asset('assets/telegram.png') }}?v=20260303" style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt="Telegram">
                                            </span>
                                            <span role="button" tabindex="0" class="social-link share-link"
                                                  data-platform="twitter"
                                                  data-share-url="{{ route('service.detail', $data->id) }}?v={{ optional($data->updated_at)->timestamp ?? time() }}"
                                                  data-text="{{ Str::limit($data->name, 80) }} • {{ getPriceFormat($data->price) }} • {{ ucfirst($data->type) }} • {{ $data->city->name ?? 'City' }}, {{ $data->country->name ?? 'Country' }}"
                                                  onclick="return window.__shareClickHandler(event, this);">
                                                <img src="{{ asset('assets/twiter.png') }}?v=20260303" style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt="Twitter">
                                            </span>
                                            <span role="button" tabindex="0" class="social-link share-link"
                                                  data-platform="linkedin"
                                                  data-share-url="{{ route('service.detail', $data->id) }}?v={{ optional($data->updated_at)->timestamp ?? time() }}"
                                                  onclick="return window.__shareClickHandler(event, this);">
                                                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRokEYt0yyh6uNDKL8uksVLlhZ35laKNQgZ9g&s" style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt="LinkedIn">
                                            </span>
                                        </div>

                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>





                </div>
            @endif
        </div>
    </div>

    <!-- Why us -->
    <div class="section-padding bg-white landing-why-wrap landing-animate-in">
    <div class="container">
        <h2 class="landing-why-title">{{ __('landingpage.why_us_title') }}</h2>
        <p class="landing-why-lead">{{ __('landingpage.why_us_lead') }}</p>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="landing-why-item">
                    <div class="landing-why-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
                    <h4 class="landing-why-item-title">{{ __('landingpage.why_vetted') }}</h4>
                    <p class="landing-why-item-desc">{{ __('landingpage.why_vetted_desc') }}</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="landing-why-item">
                    <div class="landing-why-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
                    <h4 class="landing-why-item-title">{{ __('landingpage.why_pricing') }}</h4>
                    <p class="landing-why-item-desc">{{ __('landingpage.why_pricing_desc') }}</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="landing-why-item">
                    <div class="landing-why-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg></div>
                    <h4 class="landing-why-item-title">{{ __('landingpage.why_secure') }}</h4>
                    <p class="landing-why-item-desc">{{ __('landingpage.why_secure_desc') }}</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="landing-why-item">
                    <div class="landing-why-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg></div>
                    <h4 class="landing-why-item-title">{{ __('landingpage.why_rebook') }}</h4>
                    <p class="landing-why-item-desc">{{ __('landingpage.why_rebook_desc') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

            {{-- Featured Services (separate section) --}}
            @if ($sectionData && isset($sectionData['section_4']) && $sectionData['section_4']['section_4'] == 1)
            <div class="section-padding landing-featured-section landing-section-alt">
                <div class="container">
                <div class="service-img d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="iq-title-box mb-0">
                        <h3 class="text-capitalize line-count-1">{{ $sectionData['section_4']['title'] }}
                            <div class="highlighted-text">
                                <span class="highlighted-text-swipe"></span>
                                <span class="highlighted-image">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="155" height="12"
                                        viewBox="0 0 155 12" fill="none">
                                        <path d="M2.5 9.5C3.16964 9.26081 78.8393 -2.45948 152.5 4.9554"
                                            stroke="currentColor" stroke-width="4" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                        </h3>
                    </div>
                    <a href="{{ route('service.list') }}"
                        class="btn btn-link p-0 flex-shrink-0">{{ __('messages.view_all') }}</a>
                </div>
                <div class="row">
                        @foreach ($featuredrequest  as $data)
                            <div class="col-md-3"> <!-- Changed from col-md-4 to col-md-3 -->
                                <div class="service-box-card bg-white rounded-3 mb-5 shadow-sm"
                                    data-service-id="{{ $data->id }}">
                                    <div class="iq-image position-relative" style="height: 200px; width: 100%; overflow: hidden; border-radius: 0.5rem;">
                                        @if ($data->visit_type == 'ONLINE')
                                            <span class="online-service"></span>
                                        @endif
                                        <a href="{{ route('service.detail', $data->id) }}" class="service-img d-block w-100 h-100">
                                            <img src="{{ getSingleMedia($data, 'service_attachment', null) }}"
                                                alt="service"
                                                class="w-100 h-100" style="object-fit: cover;" onerror="this.src='{{ asset('images/default.png') }}'">
                                        </a>

                                        @if (auth()->check() && auth()->user()->hasRole('user'))
                                            @if ($servicerequest->isEmpty())
                                                <form method="POST" id="favoriteForm">
                                                    @csrf
                                                    <input type="hidden" name="service_id" class="service_id"
                                                        value="{{ $data->id }}">
                                                    <input type="hidden" name="user_id" id="user_id"
                                                        value="{{ Auth::user()->id }}">
                                                    <button type="button"
                                                        class="btn-link serv-whishlist text-primary save_fav">
                                                        <svg width="12" height="13" viewBox="0 0 12 13"
                                                            fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                                d="M1.43593 6.29916C0.899433 4.62416 1.52643 2.70966 3.28493 2.14316C4.20993 1.84466 5.23093 2.02066 5.99993 2.59916C6.72743 2.03666 7.78593 1.84666 8.70993 2.14316C10.4684 2.70966 11.0994 4.62416 10.5634 6.29916C9.72843 8.95416 5.99993 10.9992 5.99993 10.9992C5.99993 10.9992 2.29893 8.98516 1.43593 6.29916Z"
                                                                stroke="currentColor" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                            <path
                                                                d="M8 3.84998C8.535 4.02298 8.913 4.50048 8.9585 5.06098"
                                                                stroke="currentColor" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" id="favoriteForm">
                                                    @csrf
                                                    <input type="hidden" name="service_id" class="service_id"
                                                        value="{{ $data->id }}">
                                                    <input type="hidden" name="user_id" id="user_id"
                                                        value="{{ Auth::user()->id }}">
                                                    <button type="button"
                                                        class="btn-link serv-whishlist text-primary delete_fav">
                                                        <svg width="12" height="13" viewBox="0 0 12 13"
                                                            fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                                d="M1.43593 6.29916C0.899433 4.62416 1.52643 2.70966 3.28493 2.14316C4.20993 1.84466 5.23093 2.02066 5.99993 2.59916C6.72743 2.03666 7.78593 1.84666 8.70993 2.14316C10.4684 2.70966 11.0994 4.62416 10.5634 6.29916C9.72843 8.95416 5.99993 10.9992 5.99993 10.9992C5.99993 10.9992 2.29893 8.98516 1.43593 6.29916Z"
                                                                stroke="currentColor" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path
                                                                d="M8 3.84998C8.535 4.02298 8.913 4.50048 8.9585 5.06098"
                                                                stroke="currentColor" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <form method="GET" id="favoriteForm"
                                                action="{{ route('login') }}">
                                                @csrf
                                                <button type="submit" class="btn-link serv-whishlist text-primary">
                                                    <svg width="12" height="13" viewBox="0 0 12 13"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                                            d="M1.43593 6.29916C0.899433 4.62416 1.52643 2.70966 3.28493 2.14316C4.20993 1.84466 5.23093 2.02066 5.99993 2.59916C6.72743 2.03666 7.78593 1.84666 8.70993 2.14316C10.4684 2.70966 11.0994 4.62416 10.5634 6.29916C9.72843 8.95416 5.99993 10.9992 5.99993 10.9992C5.99993 10.9992 2.29893 8.98516 1.43593 6.29916Z"
                                                            stroke="currentColor" stroke-width="1.5"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M8 3.84998C8.535 4.02298 8.913 4.50048 8.9585 5.06098"
                                                            stroke="currentColor" stroke-width="1.5"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                        <div style="
                                        position: absolute;
                                        bottom: 10px;
                                        left: 10px;
                                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                        color: #fff;
                                        padding: 6px 12px;
                                        border-radius: 12px;
                                        font-weight: 700;
                                        font-size: 13px;
                                        box-shadow: 0 6px 18px rgba(102, 126, 234, 0.35);
                                        backdrop-filter: blur(8px);
                                    ">
                                        @if($data->price==0)
                                            Free
                                        @else
                                            {{ getPriceFormat($data->price) }} @if(!empty($data->type)) / {{ ucfirst($data->type) }} @endif
                                        @endif
                                    </div>
                                    </div>
                                    

                                    {{-- @php
                                        $fullTitle = trim($data->name ?? '');
                                        $words = preg_split('/\s+/', $fullTitle, -1, PREG_SPLIT_NO_EMPTY);
                                        $shortTitle = implode(' ', array_slice($words, 0, 4));
                                        $hasMoreTitle = count($words) > 4;
                                    @endphp --}}
                                    <a href="{{ route('service.detail', $data->id) }}" class="service-heading mt-2 d-block p-0 text-decoration-none">
                                        <h5 class="job-title" style="
                                            font-size: 14px;
                                            font-weight: 700;
                                            color: #1a1a1a;
                                            margin-bottom: 6px;
                                            line-height: 1.2;
                                            display: -webkit-box;
                                            -webkit-line-clamp: 2;
                                            -webkit-box-orient: vertical;
                                            overflow: hidden;
                                            min-height: 34px;
                                        ">
                                            {{ $data->name }}
                                            </h5>
                                    </a>
                                    {{-- @if($hasMoreTitle)
                                        <button type="button" class="btn btn-link p-0 ms-1 see-more-title" data-target="#s4-title-{{ $data->id }}">{{ __('See more') }}</button>
                                    @endif --}}
                                    <h5 class="mt-0 mb-0 text-truncate" style="font-size:12px;">
                                        <span style="font-size: 12px;">{{ optional($data->city)->name ?? 'City' }}, {{ optional($data->country)->name ?? 'Country' }}</span>
                                    </h5>
                                    


                                        <div class="mt-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ getSingleMedia($data->providers, 'profile_image', null) }}" alt="service" class="img-fluid rounded-3 object-cover avatar-24">
                                                <a href="{{ route('provider.detail', $data->providers->id) }}">
                                                    <span class="font-size-14 service-user-name">{{ $data->providers->display_name }}</span>
                                                </a>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-end">
                                                @php
                                                    $plan_icon = asset('images/freepng.png');
                                                    $provider = $data->providers;
                                                    if ($provider && $provider->providerSubscription) {
                                                        $rawPlan = strtolower(trim($provider->providerSubscription->plan_type ?? $provider->providerSubscription->title ?? ''));
                                                        if (str_contains($rawPlan, 'silver')) {
                                                            $plan_icon = asset('images/icon/silverpng.png');
                                                        } elseif (str_contains($rawPlan, 'gold')) {
                                                            $plan_icon = asset('images/goldpng.png');
                                                        }
                                                    }

                                                    $providerId = optional($data->providers)->id;
                                                    $isAllVerified = $providerId && function_exists('verify_provider_document') ? verify_provider_document($providerId) : false;
                                                @endphp
                                                <img src="{{ $plan_icon }}" alt="plan" style="width: 26px; height: 26px; margin-right: 10px;">
                                                @if ($isAllVerified)
                                                    <img src="{{ asset('images/icon/verifiedpng.png') }}" alt="verified" style="width: 26px; height: 26px;">
                                                @else
                                                    <img src="{{ asset('images/icon/notverifiedpng.png') }}" alt="not verified" style="width: 26px; height: 26px;">
                                                @endif
                                            </div>
                                            @include('partials.landing-service-ugc-dropdown', ['service' => $data])
                                            <div class="stats-section">
                                                <div class="row g-2 text-center">
                                                    <div class="col-4">
                                                        <div class="stats-item">
                                                            <div class="d-flex align-items-center justify-content-center mb-1">
                                                                <svg  style="padding-right: 3px" width="16" height="16" viewBox="0 0 24 24" fill="none" class="stats-icon text-warning" xmlns=" "><path d="M12 2L15.09 8.26L22 9L17 14L18.18 21L12 17.77L5.82 21L7 14L2 9L8.91 8.26L12 2Z" fill="currentColor"/></svg>
                                                                <span class="stats-value" style="font-size: 11px;">{{ round($data->avg_rating ?? 0, 1) }}</span>
                                                            </div>
                                                            <div class="stats-label" style="font-size: 11px;">
                                                                <a href="{{ route('rating.all', ['service_id' => $data->id]) }}" class="text-decoration-none text-muted">({{ $data->total_reviews ?? 0 }} {{ ($data->total_reviews ?? 0) > 1 ? __('messages.reviews') : __('messages.review') }})</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="stats-item">
                                                            <div class="d-flex align-items-center justify-content-center mb-1">
                                                                <svg  style="padding-right: 3px" width="16" height="16" viewBox="0 0 24 24" fill="none" class="stats-icon text-success" xmlns=" "><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                                <span class="stats-value" style="font-size: 11px;">{{ $data->booking_count ?? 0 }}</span>
                                                            </div>
                                                            <div class="stats-label" style="font-size: 11px;">Bookings</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="stats-item">
                                                            <div class="d-flex align-items-center justify-content-center mb-1">
                                                                <svg  style="padding-right: 3px" width="16" height="16" viewBox="0 0 24 24" fill="none" class="stats-icon text-info" xmlns=" "><path d="M12 5c-7.633 0-10 7-10 7s2.367 7 10 7 10-7 10-7-2.367-7-10-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Zm0-8a3 3 0 1 0 .002 6.002A3 3 0 0 0 12 9Z" fill="currentColor"/></svg>
                                                                <span class="stats-value" style="font-size: 11px;">{{ $data->total_views ?? 0 }}</span>
                                                            </div>
                                                            <div class="stats-label" style="font-size: 11px;">Views</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex mt-3 " style="gap: 18px; justify-content: center;">
                                                <a href="#"><img
                                                        src="{{ asset('assets/fb.png') }}?v=20260303"
                                                        style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt=""></a>
                                                <a href="#"><img
                                                        src="{{ asset('assets/telegram.png') }}?v=20260303"
                                                        style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt="Telegram"></a>
                                                <a href="#"><img
                                                        src="{{ asset('assets/twiter.png') }}?v=20260303"
                                                        style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt=""></a>
                                                <a href="#"><img
                                                        src="{{ asset('assets/linkedIn.png') }}?v=20260303"
                                                        style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt=""></a>
                                            </div>
    
                                        </div>

                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    @if ($sectionData && isset($sectionData['section_7']) && (int) ($sectionData['section_7']['section_7'] ?? 0) === 1)
        @php
            $s7 = $sectionData['section_7'];
            $s7Url = $s7['url'] ?? '';
            $s7YoutubeId = null;
            if ($s7Url !== '' && preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{6,})/i', $s7Url, $s7m)) {
                $s7YoutubeId = $s7m[1];
            }
            $s7Subtitles = $s7['subtitle'] ?? [];
            $s7Subdescs = $s7['subdescription'] ?? [];
            $s7HasVimage = !empty($section7Setting) && getMediaFileExit($section7Setting, 'vimage');
            $s7HasMedia = $s7YoutubeId || $s7HasVimage;
            $s7YoutubeWatchUrl = $s7YoutubeId
                ? 'https://www.youtube.com/watch?v=' . $s7YoutubeId
                : (is_string($s7Url) && preg_match('#^https?://#i', $s7Url) ? $s7Url : null);
        @endphp
        <div class="section-padding landing-s7-workflow">
            <div class="container">
                <div class="row align-items-start g-4 g-lg-5 mb-4 mb-lg-5">
                    <div class="col-lg-6">
                        <h2 class="landing-s7-title mb-0">{{ $s7['title'] ?? '' }}</h2>
                    </div>
                    <div class="col-lg-6">
                        @if (!empty($s7['description']))
                            <p class="landing-s7-lead mb-0">{{ $s7['description'] }}</p>
                        @endif
                    </div>
                </div>
                <div class="row align-items-start g-4 g-lg-5">
                    @if ($s7HasMedia)
                        <div class="col-lg-6">
                            @if ($s7YoutubeId && $s7YoutubeWatchUrl)
                                <div class="landing-s7-video rounded-4 overflow-hidden shadow-sm position-relative">
                                    <a href="{{ $s7YoutubeWatchUrl }}" target="_blank" rel="noopener noreferrer"
                                        class="landing-s7-video-poster position-relative landing-s7-poster-clickable d-block text-decoration-none">
                                        <img src="https://img.youtube.com/vi/{{ $s7YoutubeId }}/maxresdefault.jpg"
                                            alt="{{ __('landingpage.play_video') }}"
                                            class="landing-s7-poster-img w-100 d-block"
                                            loading="lazy"
                                            draggable="false"
                                            onerror="this.onerror=null;this.src='https://img.youtube.com/vi/{{ $s7YoutubeId }}/hqdefault.jpg'">
                                        <span class="landing-s7-play d-inline-flex border-0 p-0 rounded-circle" aria-hidden="true">
                                            <span class="landing-s7-play-inner d-flex align-items-center justify-content-center rounded-circle">
                                                <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                            </span>
                                        </span>
                                        <span class="visually-hidden">{{ __('landingpage.open_on_youtube') }}</span>
                                    </a>
                                </div>
                            @elseif ($s7HasVimage)
                                <div class="landing-s7-video rounded-4 overflow-hidden shadow-sm">
                                    <img src="{{ getSingleMedia($section7Setting, 'vimage') }}"
                                        alt="{{ $s7['title'] ?? '' }}"
                                        class="landing-s7-poster-img w-100 d-block">
                                </div>
                            @endif
                        </div>
                    @endif
                    <div class="{{ $s7HasMedia ? 'col-lg-6' : 'col-12' }}">
                        <div class="landing-s7-steps">
                            @foreach ($s7Subtitles as $s7i => $s7Sub)
                                @php
                                    $s7StepTitle = is_array($s7Sub) ? ($s7Sub[0] ?? '') : $s7Sub;
                                    $s7RawDesc = $s7Subdescs[$s7i] ?? '';
                                    $s7StepDesc = is_array($s7RawDesc) ? ($s7RawDesc[0] ?? '') : $s7RawDesc;
                                @endphp
                                @if ($s7StepTitle !== '' || $s7StepDesc !== '')
                                    <div class="landing-s7-step d-flex gap-3 gap-md-4">
                                        <div class="landing-s7-step-num flex-shrink-0">{{ $loop->iteration }}</div>
                                        <div class="landing-s7-step-body flex-grow-1 min-w-0">
                                            @if ($s7StepTitle !== '')
                                                <h5 class="landing-s7-step-title mb-2">{{ $s7StepTitle }}</h5>
                                            @endif
                                            @if ($s7StepDesc !== '')
                                                <p class="landing-s7-step-desc mb-0">{{ $s7StepDesc }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <style>
            .landing-s7-workflow { background: var(--bs-body-bg, #fff); }
            .landing-s7-title {
                font-size: clamp(1.5rem, 2.5vw, 2.125rem);
                font-weight: 700;
                color: #0f172a;
                letter-spacing: -0.02em;
                line-height: 1.25;
            }
            .landing-s7-lead {
                font-size: 1.0625rem;
                line-height: 1.65;
                color: #64748b;
                font-weight: 400;
            }
            .landing-s7-video-poster { isolation: isolate; }
            .landing-s7-poster-clickable { cursor: pointer; outline: none; }
            .landing-s7-poster-clickable:focus-visible {
                box-shadow: 0 0 0 3px rgba(51, 51, 255, 0.45);
                border-radius: 0.5rem;
            }
            .landing-s7-poster-img {
                aspect-ratio: 16 / 9;
                object-fit: cover;
                vertical-align: middle;
                user-select: none;
            }
            .landing-s7-video-poster:hover .landing-s7-poster-img { opacity: 0.92; }
            .landing-s7-play {
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                z-index: 50;
                cursor: pointer;
                transition: transform 0.2s ease;
            }
            .landing-s7-video-poster:hover .landing-s7-play { transform: translate(-50%, -50%) scale(1.06); }
            .landing-s7-play:focus-visible { outline: 2px solid #3333ff; outline-offset: 4px; }
            .landing-s7-play-inner {
                width: 4.5rem;
                height: 4.5rem;
                background: #3333ff;
                color: #fff;
                box-shadow: 0 12px 40px rgba(51, 51, 255, 0.45);
                padding-left: 4px;
            }
            .landing-s7-step {
                padding: 1.25rem 0;
                border-bottom: 1px solid #e2e8f0;
            }
            .landing-s7-step:last-child { border-bottom: none; padding-bottom: 0; }
            .landing-s7-step:first-child { padding-top: 0; }
            .landing-s7-step-num {
                width: 2.5rem;
                height: 2.5rem;
                border-radius: 50%;
                background: #f1f5f9;
                color: #64748b;
                font-weight: 600;
                font-size: 0.9375rem;
                display: flex;
                align-items: center;
                justify-content: center;
                line-height: 1;
            }
            .landing-s7-step-title {
                font-size: 1.0625rem;
                font-weight: 700;
                color: #0f172a;
            }
            .landing-s7-step-desc {
                font-size: 0.96875rem;
                line-height: 1.65;
                color: #64748b;
            }
            @media (max-width: 991.98px) {
                .landing-s7-title { margin-bottom: 0.5rem; }
            }
        </style>
    @endif

    @if ($auth_user_id)
        <!-- Recently Viewed Service -->
        @if ($sectionData && isset($sectionData['section_8']) && $sectionData['section_8']['section_8'] == 1)
            @php
                $recentlyViewed = session()->get('recently_viewed:' . $auth_user_id, []);
                session(['recently_viewed:' . $auth_user_id => $recentlyViewed]);
            @endphp
            @if (!empty($recentlyViewed))
                <div class="section-padding">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-2 col-md-none"></div>
                            <div class="col-lg-8 col-md-12">
                                <div class="iq-title-box text-center center">
                                    <h3 class="text-capitalize line-count-1">{{ $sectionData['section_8']['title'] ?? '' }}
                                        <span class="highlighted-text">
                                            <span class="highlighted-text-swipe"></span>
                                            <span class="highlighted-image">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="130" height="11"
                                                    viewBox="0 0 130 11" fill="none">
                                                    <path d="M2 9C2.5625 8.76081 66.125 -2.95948 128 4.4554"
                                                        stroke="currentColor" stroke-width="4" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                            </span>
                                        </span>
                                    </h3>
                                    <p class="iq-title-desc line-count-3 text-body mt-3 mb-0">
                                        {{ $sectionData['section_8']['description'] ?? null }}</p>

                                </div>
                            </div>
                            <div class="col-lg-2 col-md-none"></div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <service-slider-section :user_id="{{ json_encode($auth_user_id) }}"
                                    :favourite="{{ json_encode($favourite) }}" :type="'recently_view'" />
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    @endif

    <!-- Provider Contact Section - Modern Design -->
    @if ($sectionData && isset($sectionData['section_5']) && $sectionData['section_5']['section_5'] == 1)
    <div class="provider-contact-section py-5" style="background: linear-gradient(135deg, rgba(255, 0, 0, 0.05) 0%, rgba(95, 96, 185, 0.05) 100%) !important; padding: 3rem 0 !important;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <div class="provider-contact-card text-center p-5 rounded-4 shadow-lg" style="background: #ffffff !important; border: 1px solid #e9ecef !important; position: relative !important; overflow: hidden !important;">
                        <div class="mb-4">
                            <div class="contact-icon-wrapper mb-4" style="display: flex !important; justify-content: center !important;">
                                <div class="contact-icon-circle" style="width: 100px !important; height: 100px !important; border-radius: 50% !important; background: #3333ff !important; display: flex !important; align-items: center !important; justify-content: center !important; box-shadow: 0 10px 30px rgba(255, 0, 0, 0.2) !important;">
                                    <i class="ri-customer-service-2-line" style="font-size: 2.5rem !important; color: #fff !important;"></i>
                                </div>
                            </div>
                            <h2 class="fw-bold mb-3" style="font-size: 2.5rem !important; color: #333 !important;">
                                {{ __('landingpage.contact_need_help') }}
                            </h2>
                            <p class="text-muted mb-4" style="font-size: 1.1rem !important; line-height: 1.6 !important;">
                                {{ __('landingpage.contact_lead') }}
                            </p>
                        </div>
                        
                        <div class="contact-buttons-wrapper d-flex flex-column flex-md-row gap-3 justify-content-center align-items-center" style="margin-top: 2rem !important;">
                            <a href="mailto:{{ $sectionData['section_5']['email'] ?? 'support@example.com' }}" 
                               class="contact-btn contact-btn-email" style="display: inline-flex !important; align-items: center !important; padding: 15px 35px !important; border-radius: 50px !important; text-decoration: none !important; font-weight: 600 !important; font-size: 1.1rem !important; background: #3333ff !important; color: #fff !important; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important; transition: all 0.3s ease !important;">
                                <i class="ri-mail-line me-2"></i>
                                <span>{{ $sectionData['section_5']['email'] ?? 'support@example.com' }}</span>
                            </a>
                            
                            <span class="contact-divider d-none d-md-inline" style="color: #6c757d !important; font-weight: 500 !important; font-size: 1rem !important;">{{ __('landingpage.contact_or') }}</span>
                            
                            <a href="tel:{{ $sectionData['section_5']['contact_number'] ?? '+1234567890' }}" 
                               class="contact-btn contact-btn-phone" style="display: inline-flex !important; align-items: center !important; padding: 15px 35px !important; border-radius: 50px !important; text-decoration: none !important; font-weight: 600 !important; font-size: 1.1rem !important; background: #fff !important; color: #333 !important; border: 2px solid #e9ecef !important; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important; transition: all 0.3s ease !important;">
                                <i class="ri-phone-line me-2"></i>
                                <span>{{ $sectionData['section_5']['contact_number'] ?? '+1234567890' }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        /* Provider Contact Section - High Specificity with !important */
        body .provider-contact-section {
            background: linear-gradient(135deg, rgba(255, 0, 0, 0.05) 0%, rgba(95, 96, 185, 0.05) 100%) !important;
            padding: 3rem 0 !important;
        }
        
        body .provider-contact-card {
            background: #ffffff !important;
            border: 1px solid #e9ecef !important;
            position: relative !important;
            overflow: hidden !important;
        }
        
        body .provider-contact-card::before {
            content: '' !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            height: 5px !important;
            background: #3333ff !important;
            z-index: 1 !important;
        }
        
        body .contact-icon-wrapper {
            display: flex !important;
            justify-content: center !important;
        }
        
        body .contact-icon-circle {
            width: 100px !important;
            height: 100px !important;
            border-radius: 50% !important;
            background: #3333ff !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 10px 30px rgba(255, 0, 0, 0.2) !important;
        }
        
        body .contact-icon-circle i {
            font-size: 2.5rem !important;
            color: #fff !important;
        }
        
        body .contact-buttons-wrapper {
            margin-top: 2rem !important;
        }
        
        body .contact-btn {
            display: inline-flex !important;
            align-items: center !important;
            padding: 15px 35px !important;
            border-radius: 50px !important;
            text-decoration: none !important;
            font-weight: 600 !important;
            font-size: 1.1rem !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
        }
        
        body .contact-btn-email {
            background: #3333ff !important;
            color: #fff !important;
        }
        
        body .contact-btn-email:hover {
            background: linear-gradient(135deg, #cc0000 0%, #4a4d94 100%) !important;
            color: #fff !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(255, 0, 0, 0.3) !important;
        }
        
        body .contact-btn-phone {
            background: #fff !important;
            color: #333 !important;
            border: 2px solid #e9ecef !important;
        }
        
        body .contact-btn-phone:hover {
            background: linear-gradient(135deg, rgba(255, 0, 0, 0.05) 0%, rgba(95, 96, 185, 0.05) 100%) !important;
            border-color: #3333ff !important;
            color: #3333ff !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1) !important;
        }
        
        body .contact-divider {
            color: #6c757d !important;
            font-weight: 500 !important;
            font-size: 1rem !important;
        }
        
        @media (max-width: 768px) {
            body .provider-contact-section {
                padding: 2rem 0 !important;
            }
            
            body .provider-contact-card {
                padding: 2.5rem 1.5rem !important;
            }
            
            body .contact-icon-circle {
                width: 80px !important;
                height: 80px !important;
            }
            
            body .contact-icon-circle i {
                font-size: 2rem !important;
            }
            
            body .provider-contact-card h2 {
                font-size: 2rem !important;
            } 
            
            body .contact-btn {
                padding: 12px 25px !important;
                font-size: 1rem !important;
                width: 100% !important;
                justify-content: center !important;
            }
        }
    </style>
@endif
   

  

   
    <!-- What customers say (testimonials) -->
    @if (isset($landingTestimonials) && count($landingTestimonials) > 0)
    <div class="section-padding bg-white landing-testimonials-wrap landing-animate-in">
        <div class="container">
            <h2 class="landing-testimonials-title">{{ __('landingpage.testimonials_title') }}</h2>
            <p class="landing-testimonials-lead">{{ __('landingpage.testimonials_lead') }}</p>
            <div class="row g-4">
                @foreach ($landingTestimonials as $t)
                <div class="col-md-6 col-lg-3">
                    <div class="landing-testimonial-card">
                        <div class="landing-testimonial-stars">
                            @for ($i = 1; $i <= 5; $i++)
                                <span class="landing-testimonial-star {{ $i <= ($t['rating'] ?? 0) ? 'filled' : '' }}">★</span>
                            @endfor
                        </div>
                        <p class="landing-testimonial-quote">"{{ $t['review'] }}"</p>
                        <div class="landing-testimonial-meta">
                            <span class="landing-testimonial-name">{{ $t['customer_name'] }}</span>
                            @if (!empty($t['service_name']))
                                <span class="landing-testimonial-service">{{ $t['service_name'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Recently booked + Areas we cover -->
    @if (count($recentBookings ?? []) > 0 || count($areasWeCover ?? []) > 0)
    <div class="section-padding bg-white landing-recent-areas-wrap landing-animate-in">
        <div class="container">
            <div class="row g-4 align-items-stretch">
                @if (count($recentBookings ?? []) > 0)
                    <div class="col-lg-6">
                        <h3 class="landing-section-head">{{ __('landingpage.recently_booked_title') }}</h3>
                        <p class="landing-section-lead mb-3">{{ __('landingpage.recently_booked_lead') }}</p>
                        <div class="landing-recent-card h-100">
                            @foreach ($recentBookings as $rb)
                                <div class="landing-recent-job-item">
                                    <span class="landing-recent-dot" aria-hidden="true"></span>
                                    @if(!empty($rb['service_id']))
                                        <a href="{{ route('service.detail', $rb['service_id']) }}" class="landing-recent-service-name text-decoration-none">{{ $rb['service_name'] }}</a>
                                    @else
                                        <span class="landing-recent-service-name">{{ $rb['service_name'] }}</span>
                                    @endif
                                    <span class="landing-recent-time">{{ $rb['created_at']->diffForHumans() }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if (count($areasWeCover ?? []) > 0)
                    <div class="col-lg-6">
                        <h3 class="landing-section-head">{{ __('landingpage.areas_we_cover_title') }}</h3>
                        <p class="landing-section-lead mb-3">{{ __('landingpage.areas_we_cover_lead') }}</p>
                        <div class="landing-areas-card">
                            <p class="landing-areas-sub">{{ __('landingpage.areas_find_near_you') }}</p>
                            <div class="landing-areas-list">
                                @foreach ($areasWeCover as $area)
                                    <span class="landing-areas-tag">{{ $area }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    

    @if ($sectionData && isset($sectionData['section_9']) && $sectionData['section_9']['section_9'] == 1)
        <section class="t9-section" aria-labelledby="t9-title">
            <div class="container">
                <header class="t9-header">
                    <h2 id="t9-title" class="t9-title">{{ $sectionData['section_9']['title'] }}</h2>
                    @if (!empty($sectionData['section_9']['description']))
                        <p class="t9-desc">{{ $sectionData['section_9']['description'] }}</p>
                    @endif
                </header>

                <div class="t9-trust-strip" role="region" aria-label="{{ __('landingpage.overall_rating') }}">
                    <div class="t9-trust-item">
                        <span class="t9-trust-value">
                            <rating-component :readonly="true" :showrating="false" :ratingvalue="{{ $totalRating }}" />
                            <span class="t9-trust-num">{{ number_format((float) $totalRating, 1) }}</span>
                        </span>
                        <span class="t9-trust-label">{{ __('landingpage.overall_rating') }}</span>
                    </div>
                    <span class="t9-trust-divider" aria-hidden="true"></span>
                    <div class="t9-trust-item">
                        <span class="t9-trust-value">{{ number_format($totalBookings ?? 0) }}+</span>
                        <span class="t9-trust-label">{{ __('landingpage.completed_jobs') }}</span>
                    </div>
                    <span class="t9-trust-divider" aria-hidden="true"></span>
                    <div class="t9-trust-item">
                        <span class="t9-trust-value">{{ $totalProviders ?? 0 }}+</span>
                        <span class="t9-trust-label">{{ __('landingpage.local_pros') }}</span>
                    </div>
                    <span class="t9-trust-divider" aria-hidden="true"></span>
                    <div class="t9-trust-item t9-trust-item-accent">
                        <span class="t9-trust-value t9-trust-value-tag">{{ __('landingpage.free_quotes') }}</span>
                        <span class="t9-trust-label">{{ __('landingpage.trusted_by_thousands') }}</span>
                    </div>
                </div>

                {{-- <div class="t9-testimonial-wrap">
                    <testimonial-section />
                </div> --}}
            </div>
        </section>
    @endif

    <!-- For pros / providers -->
    <div class="section-padding">
        <div class="container">
            <div class="landing-for-pros-banner">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h2 class="h4 mb-2 text-white">{{ __('landingpage.for_pros_title') }}</h2>
                        <p class="mb-0 text-white opacity-90">{{ __('landingpage.for_pros_lead') }}</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('auth.register') }}" class="btn btn-primary btn-lg">{{ __('landingpage.register_as_provider') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- For customers -->
    <div class="section-padding pt-0">
        <div class="container">
            <div class="landing-for-customer-banner">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h2 class="h4 mb-2 text-white">{{ __('landingpage.for_customer_title') }}</h2>
                        <p class="mb-0 text-white opacity-90">{{ __('landingpage.for_customer_lead') }}</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('auth.register') }}" class="btn btn-light btn-lg">{{ __('landingpage.register_as_customer') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($sectionData && isset($sectionData['section_6']) && $sectionData['section_6']['section_6'] == 1)
        <div class="section-padding">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-12">
                        <div class="px-5 bg-primary rounded-3 position-relative overflow-hidden">
                            <div class="position-absolute top-0 end-0">
                                <img src="{{ asset('landing-images/general/pattern-bg-1.webp') }}" alt="pattern"
                                    class="img-fluid">
                            </div>
                            <div class="px-xl-5">
                                <div class="px-xl-3">
                                    <div class="row align-items-center">
                                        <div class="col-lg-6 position-relative my-5">
                                            <div class="iq-title-box">
                                                <h2 class="text-capitalize text-white line-count-2">
                                                    {{ $sectionData['section_6']['title'] }}</h2>
                                                <p class="mt-3 mb-0 text-white line-count-3">
                                                    {{ $sectionData['section_6']['description'] ?? null }}
                                                </p>
                                            </div>
                                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                                @php
                                                    $mediaGooglePay = Spatie\MediaLibrary\MediaCollections\Models\Media::where(
                                                        'collection_name',
                                                        'google_play',
                                                    )->first();
                                                    $mediaAppStore = Spatie\MediaLibrary\MediaCollections\Models\Media::where(
                                                        'collection_name',
                                                        'app_store',
                                                    )->first();
                                                    $mediaMainImage = Spatie\MediaLibrary\MediaCollections\Models\Media::where(
                                                        'collection_name',
                                                        'main_image',
                                                    )->first();
                                                    $sitesetup = App\Models\Setting::where('type', 'site-setup')
                                                        ->where('key', 'site-setup')
                                                        ->first();
                                                    $appDownload = $sitesetup ? json_decode($sitesetup->value) : null;
                                                    $playStoreUrl =
                                                        $appDownload && $appDownload->playstore_url
                                                            ? $appDownload->playstore_url
                                                            : 'https://play.google.com/';
                                                    $appStoreUrl =
                                                        $appDownload && $appDownload->appstore_url
                                                            ? $appDownload->appstore_url
                                                            : 'https://apps.apple.com/';
                                                @endphp
                                                <a href="{{ $playStoreUrl }}" target="_blank" class="app-link">
                                                    @if ($mediaGooglePay)
                                                        <img src="{{ url('storage/' . $mediaGooglePay->id . '/' . $mediaGooglePay->file_name) }}"
                                                            alt="googleplay" class="img-fluid">
                                                    @else
                                                        <img src="{{ asset('landing-images/general/googleplay.webp') }}"
                                                            alt="googleplay" class="img-fluid">
                                                    @endif
                                                </a>
                                                <a href="{{ $appStoreUrl }}" target="_blank" class="app-link">
                                                    @if ($mediaAppStore)
                                                        <img src="{{ url('storage/' . $mediaAppStore->id . '/' . $mediaAppStore->file_name) }}"
                                                            alt="appstore" class="img-fluid">
                                                    @else
                                                        <img src="{{ asset('landing-images/general/appstore.webp') }}"
                                                            alt="appstore" class="img-fluid">
                                                    @endif
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 mt-lg-0 mt-5 position-relative align-self-end text-center">
                                            @if ($mediaMainImage)
                                                <img src="{{ url('storage/' . $mediaMainImage->id . '/' . $mediaMainImage->file_name) }}"
                                                    alt="phone" class="img-fluid">
                                            @else
                                                <img src="{{ asset('landing-images/general/phone.webp') }}"
                                                    alt="phone" class="img-fluid">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- <!-- Sticky CTA bar (appears on scroll) -->
    <div id="landing-sticky-cta" class="landing-sticky-cta" aria-hidden="true">
        <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3 py-2">
            <span class="landing-sticky-cta-text">{{ __('landingpage.sticky_cta_ready') }}</span>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('service.list') }}" class="btn btn-primary btn-sm">{{ __('landingpage.sticky_cta_book') }}</a>
                <a href="{{ route('post.job.list') }}" class="btn btn-outline-primary btn-sm">{{ __('landingpage.sticky_cta_post_job') }}</a>
            </div>
        </div>
    </div> --}}

@endsection

<script>
    if (!window.__shareClickHandler) {
        window.__shareClickHandler = function(e, el) {
            try { e.preventDefault(); e.stopPropagation(); } catch (_) {}

            function openPopup(url) {
                window.open(url, '_blank', 'noopener,noreferrer,width=600,height=600');
            }

            var platform = el.getAttribute('data-platform');
            var shareUrl = el.getAttribute('data-share-url');

            if (platform === 'facebook') {
                var fbUrl = encodeURIComponent(shareUrl || window.location.href);
                var quote = encodeURIComponent(el.getAttribute('data-quote') || '');
                openPopup('https://www.facebook.com/sharer/sharer.php?u=' + fbUrl + (quote ? '&quote=' + quote : ''));
            } else if (platform === 'twitter') {
                var text = encodeURIComponent(el.getAttribute('data-text') || '');
                var url = encodeURIComponent(shareUrl || window.location.href);
                openPopup('https://twitter.com/intent/tweet?url=' + url + '&text=' + text);
            } else if (platform === 'linkedin') {
                var liUrl = encodeURIComponent(shareUrl || window.location.href);
                openPopup('https://www.linkedin.com/sharing/share-offsite/?url=' + liUrl);
            } else if (platform === 'telegram') {
                var url = encodeURIComponent(shareUrl || window.location.href);
                var text = encodeURIComponent(el.getAttribute('data-quote') || '');
                openPopup('https://t.me/share/url?url=' + url + (text ? '&text=' + text : ''));
            }

            return false;
        };
    }
</script>
@section('bottom_script')
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Toggle service title see more/less without navigating
            jQuery(document).on('click', '.see-more-title', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const target = jQuery(this).data('target');
                const $el = jQuery(target);
                if ($el.length === 0) return;
                const expanded = $el.data('expanded') === 1;
                const shortTitle = $el.data('short-title');
                const fullTitle = $el.data('full-title');
                if (expanded) {
                    $el.text(shortTitle);
                    $el.data('expanded', 0);
                    jQuery(this).text("{{ __('See more') }}");
                } else {
                    $el.text(fullTitle);
                    $el.data('expanded', 1);
                    jQuery(this).text("{{ __('See less') }}");
                }
            });
            var $sliders = jQuery(document).find('.iq-team-slider');
            if ($sliders.length > 0) {
                $sliders.each(function() {
                    let slider = jQuery(this);
                    var navNext = (slider.data('navnext')) ? "#" + slider.data('navnext') : "";
                    var navPrev = (slider.data('navprev')) ? "#" + slider.data('navprev') : "";
                    var pagination = (slider.data('pagination')) ? "#" + slider.data('pagination') : "";
                    var sliderAutoplay = slider.data('autoplay');
                    if (sliderAutoplay) {
                        sliderAutoplay = {
                            delay: slider.data('autoplay')
                        };
                    } else {
                        sliderAutoplay = false;
                    }
                    var iqonicPagination = {
                        el: pagination,
                        clickable: true,
                        dynamicBullets: true,
                    };
                    var swSpace = {
                        1200: 30,
                        1500: 30
                    };
                    var breakpoint = {
                        0: {
                            slidesPerView: 1,
                            centeredSlides: false,
                            virtualTranslate: false
                        },
                        576: {
                            slidesPerView: 1,
                            centeredSlides: false,
                            virtualTranslate: false
                        },
                        768: {
                            slidesPerView: 2,
                            centeredSlides: false,
                            virtualTranslate: false
                        },
                        1200: {
                            slidesPerView: 3,
                            spaceBetween: swSpace["1200"],
                        },
                        1500: {
                            slidesPerView: 3,
                            spaceBetween: swSpace["1500"],
                        },
                    };
                    var sw_config = {
                        loop: true,
                        speed: 1000,
                        loopedSlides: 3,
                        spaceBetween: 30,
                        slidesPerView: 3,
                        centeredSlides: false,
                        autoplay: true,
                        virtualTranslate: false,
                        navigation: {
                            nextEl: navNext,
                            prevEl: navPrev
                        },
                        on: {
                            slideChangeTransitionStart: function() {
                                var currentElement = jQuery(this.el);
                                var lastBullet = currentElement.find(
                                    ".swiper-pagination-bullet:last");
                                if (this.slides.length - (this.loopedSlides + 1) === this
                                    .activeIndex) {
                                    lastBullet.addClass("js_prefix-disable-bullate");
                                } else {
                                    lastBullet.removeClass("js_prefix-disable-bullate");
                                }
                                if (jQuery(window).width() > 1199) {
                                    var innerTranslate = -(160 + swSpace[this.currentBreakpoint]) *
                                        (this.activeIndex);
                                    currentElement.find(".swiper-wrapper").css({
                                        "transform": "translate3d(" + innerTranslate +
                                            "px, 0, 0)"
                                    });
                                    currentElement.find('.swiper-slide:not(.swiper-slide-active)')
                                        .css({
                                            width: "160px"
                                        });
                                    currentElement.find('.swiper-slide.swiper-slide-active').css({
                                        width: "476px"
                                    });
                                }
                            },
                            resize: function() {
                                var currentElement = jQuery(this.el);
                                if (jQuery(window).width() > 1199) {
                                    if (currentElement.data("loop")) {
                                        var innerTranslate = -(160 + swSpace[this
                                            .currentBreakpoint]) * this.loopedSlides;
                                        currentElement.find(".swiper-wrapper").css({
                                            "transform": "translate3d(" + innerTranslate +
                                                "px, 0, 0)"
                                        });
                                    }
                                    currentElement.find('.swiper-slide:not(.swiper-slide-active)')
                                        .css({
                                            width: "160px"
                                        });
                                    currentElement.find('.swiper-slide.swiper-slide-active').css({
                                        width: "476px"
                                    });
                                }
                            },
                            init: function() {
                                var currentElement = jQuery(this.el);
                                currentElement.find('.swiper-slide').css({
                                    'max-width': 'auto'
                                });
                            }
                        },
                        pagination: (slider.data('pagination')) ? iqonicPagination : "",
                        breakpoints: breakpoint,
                    };
                    var swiper = new Swiper(slider[0], sw_config);
                });
                jQuery(document).trigger('after_slider_init');
            }

            // Initialize Provider Banner Slider - One at a time with navigation arrows + auto-rotate
            if (document.querySelector('.provider-banner-slider')) {
                var providerBannerSwiper = new Swiper('.provider-banner-slider', {
                    slidesPerView: 1,
                    spaceBetween: 0,
                    loop: true,
                    autoplay: {
                        delay: 3000,
                        disableOnInteraction: false,
                    },
                    navigation: {
                        nextEl: '.provider-nav-next',
                        prevEl: '.provider-nav-prev',
                    },
                });
            }
        });
    </script>

    <style>
        /* Provider Banner Slider Styles */
        .provider-banner-slider {
            width: 100%;
            position: relative;
        }

        .provider-banner-item {
            width: 100%;
            height: 100%;
        }

        .provider-banner-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .provider-banner-name {
            padding: 25px 15px !important;
            background: transparent !important;
        }
        
        .provider-banner-name h5.provider-name-text,
        .provider-name-text {
            display: inline-block !important;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0.75) 100%) !important;
            padding: 12px 30px !important;
            border-radius: 50px !important;
            font-size: 1.4rem !important;
            letter-spacing: 0.8px !important;
            font-weight: 600 !important;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6) !important;
            backdrop-filter: blur(8px) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.1) inset !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            transition: all 0.3s ease !important;
            color: #ffffff !important;
            margin: 0 !important;
        }
        
        .provider-banner-name h5.provider-name-text:hover,
        .provider-name-text:hover {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.8) 100%) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.2) inset !important;
        }

        /* Navigation Arrows - Red-Blue Gradient */
        .provider-nav-next,
        .provider-nav-prev {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            background: #3333ff;
            color: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .provider-nav-next::after,
        .provider-nav-prev::after {
            font-size: 20px;
            font-weight: bold;
        }

        .provider-nav-next:hover,
        .provider-nav-prev:hover {
            background: linear-gradient(135deg, #cc0000 0%, #4a4d94 100%);
            color: #fff;
            transform: scale(1.1);
        }

        .provider-nav-next {
            right: 20px;
        }

        .provider-nav-prev {
            left: 20px;
        }

        @media (max-width: 767px) {
            .provider-banner-image {
                height: 350px;
            }

            .provider-nav-next,
            .provider-nav-prev {
                width: 40px;
                height: 40px;
            }

            .provider-nav-next::after,
            .provider-nav-prev::after {
                font-size: 16px;
            }

            .provider-nav-next {
                right: 10px;
            }

            .provider-nav-prev {
                left: 10px;
            }
        }
    </style>

@section('after_script')
<script>
(function() {
    var stickyCta = document.getElementById('landing-sticky-cta');
    var animateEls = document.querySelectorAll('.landing-animate-in');
    var scrollThreshold = 380;
    var ticking = false;

    function onScroll() {
        if (!ticking) {
            requestAnimationFrame(function() {
                var y = window.pageYOffset || document.documentElement.scrollTop;
                if (stickyCta) {
                    if (y > scrollThreshold) {
                        stickyCta.classList.add('is-visible');
                        stickyCta.setAttribute('aria-hidden', 'false');
                    } else {
                        stickyCta.classList.remove('is-visible');
                        stickyCta.setAttribute('aria-hidden', 'true');
                    }
                }
                if (animateEls.length) {
                    var viewH = window.innerHeight;
                    animateEls.forEach(function(el) {
                        var rect = el.getBoundingClientRect();
                        if (rect.top < viewH - 80) el.classList.add('is-visible');
                    });
                }
                ticking = false;
            });
            ticking = true;
        }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('load', onScroll);
})();
</script>
@endsection
