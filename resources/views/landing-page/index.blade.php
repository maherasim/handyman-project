@extends('landing-page.layouts.default')
@section('content')


    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap');

        body {
            font-family: "Montserrat", sans-serif;
        }

        .heading_text {
            font-weight: 600;
        }

        .service {
            margin-top: -30px;
        }

        .service-asim {
            height: 10.5rem !important;
        }
        .stats-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px 8px;
            margin: 12px 0;
            border: 1px solid #eef0f2;
        }
        .stats-item {
            text-align: center;
            padding: 4px;
        }
        .stats-icon {
            width: 16px;
            height: 16px;
            margin-right: 4px;
        }
        .stats-value {
            font-weight: 600;
            font-size: 14px;
            color: #2c3e50;
        }
        .stats-label {
            font-size: 11px;
            color: #6c757d;
            margin-top: 2px;
        }
        
<style>
   .price-box {
      background-color: #007bff; /* Blue background */
      color: white; /* White text for better visibility */
      font-size: 18px; /* Increased text size */
      font-weight: bold;
      color: red; /* Red text for price */
      text-align: center;
      padding: 10px 15px; /* Added consistent padding */
      border-radius: 10px; /* Rounded corners */
      display: inline-block;
      radius: 15%;
      width: 180px; /* Increased width */
      margin: 5px 0; /* Optional: Adds spacing around the box */
   }
   .service-asim {
            height: 10.5rem !important;
            object-fit: cover;
        }
        .provider-info {
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.provider-name {
    display: inline-block;
    line-height: 1.2;
    word-break: break-word;
}

.provider-name span {
    display: block;
}

/* Card polish */
.service-box-card { 
    border: 1px solid #eef0f2; 
    transition: box-shadow .2s ease, transform .2s ease; 
    background: #fff; 
    padding: 15px;
    border-radius: 12px;
}
.service-box-card:hover { 
    box-shadow: 0 10px 24px rgba(18,38,63,.08); 
    transform: translateY(-2px); 
}
.social-share img, .social-share svg { 
    width: 28px; 
    height: 28px; 
    border-radius: 6px; 
}

/* Statistics section improvements */
.stats-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 12px 8px;
    margin: 12px 0;
}

.stats-item {
    text-align: center;
    padding: 4px;
}

.stats-icon {
    width: 16px;
    height: 16px;
    margin-right: 4px;
}

.stats-value {
    font-weight: 600;
    font-size: 14px;
    color: #2c3e50;
}

.stats-label {
    font-size: 11px;
    color: #6c757d;
    margin-top: 2px;
}

</style>
    </style>
    <!-- Banner -->
    <div class="padding-top-bottom-90 bg-light">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-xl-6">
                    <div class="me-0 pe-0 me-xl-5 pe-xl-5">
                        @if ($sectionData && isset($sectionData['section_1']) && $sectionData['section_1']['section_1'] == 1)
                            <div class="iq-title-box mb-5">
                                <div class="iq-title-box">
                                    <h2 class="text-capitalize line-count-3">
                                        {{ $sectionData['section_1']['title'] }}
                                        <!-- Your Instant Connection to Right -->
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
                                    </h2>
                                    <p class="iq-title-desc line-count-3 text-body mt-3 mb-0">
                                        {{ $sectionData['section_1']['description'] ?? null }}
                                    </p>
                                </div>
                            </div>
                            <location-search :user_id="{{ json_encode($auth_user_id) }}"
                                :postjobservice="{{ $postjobservice }}"></location-search>
                        @endif

                    </div>
                </div>

                <div class="col-xl-6 px-xl-0 mt-xl-0 mt-5">
                    <div class="position-relative swiper iq-team-slider overflow-hidden mySwiper">
                        <div class="swiper-wrapper">
                            @foreach ($sectionData['section_1']['provider_id'] as $providerId)
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
                                        <div class="mt-5 justify-content-center service-slide-items-4">
                                            <div class="col">
                                                <div class="iq-banner-img position-relative">
                                                    <img src="{{ getSingleMedia($user, 'profile_image', null) }}"
                                                        alt="provider-image"
                                                        class="img-fluid border-radius-12 position-relative">
                                                    <div class="position-relative d-flex justify-content-center card-box">
                                                        <div class="card-description d-inline-block text-center rounded-3">
                                                            <div class="cart-content">
                                                                <h6 class="heading text-capitalize fw-500">
                                                                    {{ $user->display_name ?? null }}</h6>
                                                                <span
                                                                    class="desc text-white d-flex align-items-center justify-content-center mt-2">
                                                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                                                        <div class="star-rating">
                                                                            <rating-component :readonly="true"
                                                                                :showrating="false"
                                                                                :ratingvalue="{{ $providers_service_rating }}" />
                                                                        </div>
                                                                        <h6 class="m-0 font-size-12 rating-text lh-1">
                                                                            ({{ round($providers_service_rating, 1) }})
                                                                        </h6>
                                                                    </div>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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



                <div style="display: flex; flex-wrap: wrap; gap: 30px; justify-content: center; margin-top: 40px;">
                    @foreach ($categoryrequest as $items)
                        <a href="{{ route('category.detail', $items->id) }}"
                            style="flex: 1 1 22%; max-width: 22%; text-decoration: none;">
                            <div
                                style="padding: 30px; text-align: center; background-color: #f8f9fa; border-radius: 12px; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease, box-shadow 0.3s ease;">
                                <div
                                    style="width: 100%; height: 200px; background-color: #e0e0e0; border-radius: 12px; display: flex; justify-content: center; align-items: center;">
                                    <img src="{{ getSingleMedia($items, 'category_image', null) }}" alt="icon"
                                        class="img-fluid avatar-100" style="max-width: 100px; max-height: 100px;">
                                </div>
                                <h5 class="categories-name text-capitalize mt-4 mb-2 line-count-1"
                                    style="font-size: 20px; font-weight: bold; color: #333;">{{ $items->name }}</h5>
                                <p class="categories-desc mb-0 text-capitalize line-count-2"
                                    style="font-size: 14px; color: #555;">{{ $items->description }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>





            </div>
        </div>
    @endif

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
                        <div class="col-lg-3 col-md-6 col-12 mb-3">
                            <a href="{{ route('job.details', $jobRequest->id) }}" class="card-link text-decoration-none">
                                <div class="job-card h-100" style="
                                    background: #FFFFFF;
                                    border: 1px solid #E8E9EC;
                                    border-radius: 16px;
                                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                                    transition: all 0.3s ease;
                                    overflow: hidden;
                                    position: relative;
                                ">
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
                                        
                                        <!-- Customer Info -->
                                        <div class="customer-info" style="margin-bottom: 8px;">
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
                                            <a href="#" class="social-link" style="
                                                width: 24px;
                                                height: 24px;
                                                border-radius: 5px;
                                                background: #f8f9fa;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                                transition: all 0.3s ease;
                                                text-decoration: none;
                                            ">
                                                <img src="https://static.vecteezy.com/system/resources/previews/016/716/481/original/facebook-icon-free-png.png"
                                                    alt="Facebook" style="width: 12px; height: 12px;">
                                            </a>
                                            <a href="#" class="social-link" style="
                                                width: 24px;
                                                height: 24px;
                                                border-radius: 5px;
                                                background: #f8f9fa;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                                transition: all 0.3s ease;
                                                text-decoration: none;
                                            ">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/9/95/Instagram_logo_2022.svg"
                                                    alt="Instagram" style="width: 12px; height: 12px;">
                                            </a>
                                            <a href="#" class="social-link" style="
                                                width: 24px;
                                                height: 24px;
                                                border-radius: 5px;
                                                background: #f8f9fa;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                                transition: all 0.3s ease;
                                                text-decoration: none;
                                            ">
                                                <img src="https://cdn.pixabay.com/photo/2015/03/10/17/30/twitter-667462_640.png"
                                                    alt="Twitter" style="width: 12px; height: 12px;">
                                            </a>
                                            <a href="#" class="social-link" style="
                                                width: 24px;
                                                height: 24px;
                                                border-radius: 5px;
                                                background: #f8f9fa;
                                                transition: all 0.3s ease;
                                                text-decoration: none;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                            ">
                                                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRokEYt0yyh6uNDKL8uksVLlhZ35laKNQgZ9g&s"
                                                    alt="LinkedIn" style="width: 12px; height: 12px;">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif









    <!-- Service -->
    <div class="section-padding bg-light our-service">
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
                                        <div class="iq-image position-relative">
                                            @if ($data->visit_type == 'ONLINE')
                                                <span class="online-service"></span>
                                            @endif
                                            <a href="{{ route('service.detail', $data->id) }}" class="service-img">
                                                <img src="{{ getSingleMedia($data, 'service_attachment', null) }}"
                                                    alt="service"
                                                    class="service-asim w-100 object-cover img-fluid rounded-3">
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
                                                    action="{{ route('user.login') }}">
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
                                        </div>
                                        <ul>
                                            <div class="service d-flex justify-content-center" style="position:relative; z-index:1111; margin:auto; background-image: url('{{ asset('images/icon/banner2.jpg') }}'); background-size: cover; width:85% ; margin-top:-32px;  background-repeat: no-repeat; background-position: center; padding: 10px 20px; color: white; font-weight: 600; font-size: 18px; border-radius: 10px; border: 3px solid #E1DCDD;">
                                                @if($data->price==0)
                                                    <li class="text-primary fw-500 d-inline-block position-relative font-size-18">Free</li>
                                                @else
                                                    <li class="text-white fw-500 d-inline-block position-relative font-size-18">{{ getPriceFormat($data->price) }} @if(!empty($data->type)) / {{ $data->type }} @endif</li>
                                                @endif
                                            </div>
                                        </ul>

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
                                                    src="https://static.vecteezy.com/system/resources/previews/016/716/481/original/facebook-icon-free-png.png"
                                                    style="width: 30px; border-radius: 8px;" alt=""></a>
                                            <a href="#"><img
                                                    src="https://upload.wikimedia.org/wikipedia/commons/9/95/Instagram_logo_2022.svg"
                                                    style="width: 30px; border-radius: 8px;" alt=""></a>
                                            <a href="#"><img
                                                    src="https://cdn.pixabay.com/photo/2015/03/10/17/30/twitter-667462_640.png"
                                                    style="width: 30px; border-radius: 8px;" alt=""></a>
                                            <a href="#"><img
                                                    src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRokEYt0yyh6uNDKL8uksVLlhZ35laKNQgZ9g&s"
                                                    style="width: 30px; border-radius: 8px;" alt=""></a>
                                        </div>

                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>





                </div>
            @endif












            {{-- //Featured Service --}}
            @if ($sectionData && isset($sectionData['section_4']) && $sectionData['section_4']['section_4'] == 1)
            <div>
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
                <div class="container">
                    <div class="row">
                        @foreach ($featuredrequest  as $data)
                            <div class="col-md-3"> <!-- Changed from col-md-4 to col-md-3 -->
                                <div class="service-box-card bg-white rounded-3 mb-5 shadow-sm"
                                    data-service-id="{{ $data->id }}">
                                    <div class="iq-image position-relative">
                                        @if ($data->visit_type == 'ONLINE')
                                            <span class="online-service"></span>
                                        @endif
                                        <a href="{{ route('service.detail', $data->id) }}" class="service-img">
                                            <img src="{{ getSingleMedia($data, 'service_attachment', null) }}"
                                                alt="service"
                                                class="service-asim w-100 object-cover img-fluid rounded-3">
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
                                                action="{{ route('user.login') }}">
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
                                                        src="https://static.vecteezy.com/system/resources/previews/016/716/481/original/facebook-icon-free-png.png"
                                                        style="width: 30px; border-radius: 8px;" alt=""></a>
                                                <a href="#"><img
                                                        src="https://upload.wikimedia.org/wikipedia/commons/9/95/Instagram_logo_2022.svg"
                                                        style="width: 30px; border-radius: 8px;" alt=""></a>
                                                <a href="#"><img
                                                        src="https://cdn.pixabay.com/photo/2015/03/10/17/30/twitter-667462_640.png"
                                                        style="width: 30px; border-radius: 8px;" alt=""></a>
                                                <a href="#"><img
                                                        src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRokEYt0yyh6uNDKL8uksVLlhZ35laKNQgZ9g&s"
                                                        style="width: 30px; border-radius: 8px;" alt=""></a>
                                            </div>
    
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
                                    <h3 class="text-capitalize line-count-1">{{ $sectionData['title'] }}
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

    <!-- Provider -->

    @if ($sectionData && isset($sectionData['section_5']) && $sectionData['section_5']['section_5'] == 1)
        <div class="bg-primary-subtle overflow-hidden">
            <div class="container provider-section position-relative">
                @php
                    $images = Spatie\MediaLibrary\MediaCollections\Models\Media::where(
                        'collection_name',
                        'section5_attachment',
                    )->get();
                @endphp

                @if (isset($images[0]))
                    <img src="{{ $images[0]->getUrl() }}" alt="service"
                        class="img-fluid position-absolute provider provider-1">
                @else
                    <img src="{{ asset('landing-images/service/1.webp') }}" alt="service"
                        class="img-fluid position-absolute provider provider-1">
                @endif

                @if (isset($images[1]))
                    <img src="{{ $images[1]->getUrl() }}" alt="service"
                        class="img-fluid position-absolute provider provider-6">
                @else
                    <img src="{{ asset('landing-images/service/2.webp') }}" alt="service"
                        class="img-fluid position-absolute provider provider-6">
                @endif

                <div class="row align-items-center">
                    <div class="col-md-2"></div>
                    <div class="col-lg-8 col-md-12">
                        <div class="iq-title-box mb-5 text-center px-3">
                            <h2 class="text-capitalize line-count-2">{{ $sectionData['section_5']['title'] }}</h2>
                            <p class="iq-title-desc line-count-3 text-body mt-3 mb-0">
                                {{ $sectionData['section_5']['description'] ?? null }}</p>
                        </div>
                        <div
                            class="text-center d-flex justify-content-center align-items-center pt-3 flex-column flex-md-row px-3">
                            <a class="bg-primary py-3 px-5 fw-bolder text-white rounded-3 letter-spacing-64"
                                href="mailto:{{ $sectionData['section_5']['email'] }}">{{ $sectionData['section_5']['email'] }}</a>
                            <span class="px-3">Or</span>
                            <a href="tel:{{ $sectionData['section_5']['contact_number'] }}">
                                <h6 class="text-decoration-underline">{{ $sectionData['section_5']['contact_number'] }}
                                </h6>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-2"></div>
                </div>

                @if (isset($images[2]))
                    <img src="{{ $images[2]->getUrl() }}" alt="service"
                        class="img-fluid position-absolute provider provider-5">
                @else
                    <img src="{{ asset('landing-images/service/5.webp') }}" alt="service"
                        class="img-fluid position-absolute provider provider-5">
                @endif

                @if (isset($images[3]))
                    <img src="{{ $images[3]->getUrl() }}" alt="service"
                        class="img-fluid position-absolute provider provider-3">
                @else
                    <img src="{{ asset('landing-images/service/3.webp') }}" alt="service"
                        class="img-fluid position-absolute provider provider-3">
                @endif

                @if (isset($images[4]))
                    <img src="{{ $images[4]->getUrl() }}" alt="service"
                        class="img-fluid position-absolute provider provider-4">
                @else
                    <img src="{{ asset('landing-images/service/4.webp') }}" alt="service"
                        class="img-fluid position-absolute provider provider-4">
                @endif
            </div>
        </div>
    @endif

    @if ($sectionData && isset($sectionData['section_9']) && $sectionData['section_9']['section_9'] == 1)
        <div class="section-padding bg-light px-0">
            <div class="container-fluid px-xxl-3">
                <div class="row">
                    <div class="col-12">
                        <div class="iq-title-box text-center center mb-2">
                            <h3 class="text-capitalize line-count-1">{{ $sectionData['section_9']['title'] }}
                                <span class="highlighted-text">
                                    <!-- <span class="highlighted-text-swipe">our trusted clients</span> -->
                                    <span class="highlighted-image">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="130" height="11"
                                            viewBox="0 0 130 11" fill="none">
                                            <path d="M2 9C2.5625 8.76081 66.125 -2.95948 128 4.4554" stroke="currentColor"
                                                stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </span>
                            </h3>
                        </div>


                        <div class="text-center mb-5">
                            <div
                                class="d-inline-flex align-items-center flex-sm-row flex-column bg-body py-3 px-5 rounded-5 gap-2">
                                <div class="vertical-center lh-1">
                                    <rating-component :readonly="true" :showrating="false"
                                        :ratingvalue="{{ $totalRating }}" />
                                    {{-- {{>components/widgets/filter-rating rating="4"}} --}}
                                </div>
                                @if (isset($sectionData['section_9']['overall_rating']) && $sectionData['section_9']['overall_rating'] == 'on')
                                    <h5>{{ round($totalRating, 1) }}</h5>
                                    <h6>{{ __('landingpage.overall_rating') }}</h6>
                                @endif
                            </div>
                            <h6 class="mt-4"> {{ $sectionData['section_9']['description'] ?? null }}</h6>
                        </div>
                    </div>
                    <div class="col-12">
                        <testimonial-section />
                    </div>
                </div>
            </div>
        </div>
    @endif

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

    @if ($sectionData && isset($sectionData['section_7']) && $sectionData['section_7']['section_7'] == 1)
        <div class="section-padding pt-0">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-5">
                        <div class="iq-title-box mb-0">
                            <h3 class="text-capitalize line-count-2">{{ $sectionData['section_7']['title'] }}
                                <span class="highlighted-text">
                                    <span class="highlighted-text-swipe"></span>
                                    <span class="highlighted-image">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="164" height="12"
                                            viewBox="0 0 164 12" fill="none">
                                            <path d="M2 9.5C2.71429 9.26081 83.4286 -2.45948 162 4.9554"
                                                stroke="currentColor" stroke-width="4" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </span>
                            </h3>
                        </div>
                    </div>
                    <div class="col-lg-7 mt-lg-0 mt-3">
                        <p class="m-0 line-count-3">{{ $sectionData['section_7']['description'] ?? null }}</p>
                    </div>
                </div>
                @php
                    $mediaVimage = Spatie\MediaLibrary\MediaCollections\Models\Media::where(
                        'collection_name',
                        'vimage',
                    )->first();
                @endphp

                <div class="row align-items-center mt-5 pt-lg-5">
                    <div class="col-lg-6 pe-xl-5 position-relative">
                        @if ($mediaVimage)
                            <img src="{{ url('storage/' . $mediaVimage->id . '/' . $mediaVimage->file_name) }}"
                                alt="video-popup" class="img-fluid w-100 rounded">
                        @else
                            <img src="{{ asset('landing-images/general/popup.webp') }}" alt="video-popup"
                                class="img-fluid w-100 rounded">
                        @endif
                        @include('landing-page.components.widgets.video-popup', [
                            'videoLinkUrl' => $sectionData['section_7']['url'],
                        ])

                    </div>
                    <div class="col-lg-6 mt-lg-0 mt-5 ps-xl-5">
                        @if (isset($sectionData['section_7']['subtitle']) && isset($sectionData['section_7']['subdescription']))
                            @for ($i = 0; $i < min(count($sectionData['section_7']['subtitle']), count($sectionData['section_7']['subdescription'])); $i++)
                                <div class="mb-4 pb-4 border-bottom">
                                    @include('landing-page.components.widgets.icon-box', [
                                        'iconboxNumber' => $i + 1,
                                        'iconboxTitle' => $sectionData['section_7']['subtitle'][$i],
                                        'iconboxDescription' => $sectionData['section_7']['subdescription'][$i],
                                    ])
                                </div>
                            @endfor
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif


@endsection
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
        });
    </script>
@endsection
