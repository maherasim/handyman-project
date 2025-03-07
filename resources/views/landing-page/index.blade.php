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

                <!-- Display Job Requests Cards Similar to Category Section -->
                <div class="row">
                    @foreach ($jobRequests as $jobRequest)
                        <div class="col-lg-3 col-md-4 col-12 mb-0">
                            <a href="{{ route('job.details', $jobRequest->id) }}" class="card-link">
                                <div class="card mt-5 p-3"
                                    style="position: relative; background: #FAF9FF; border-radius: 10px; height: 400px; display: flex; flex-direction: column; ">
                                    <!-- Card Image -->
                                    <div class="card-imgd" style="position: relative;">
                                        <img class="card-img-top"
                                            src="{{ asset('images/post-job/ac_refresh_and_revive.png') }}"
                                            alt="Card image cap"
                                            style="border-radius: 10px; width: 100%; height: 200px; object-fit: cover;">
                                        <!-- Price Overlay -->
                                        <div
                                            style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); width:68%; background-image: url('{{ asset('images/icon/newbanner.jpg') }}'); background-size: cover; background-repeat: no-repeat; background-position: center; padding: 10px 20px; color: #fff; font-weight: 600; font-size: 18px; border-radius: 10px; border: 3px solid #E1DCDD;">
                                            € {{ $jobRequest->price }}  / {{ $jobRequest->type }}
                                        </div>
                                        <!-- Heart Icon -->
                                        <i class='bx bx-heart'
                                            style="position: absolute; top: 10px; right: 10px; padding: 7px; color: #8384AE; border-radius: 50%;"></i>
                                    </div>
                                    <!-- Card Content -->
                                    <div class="card-body p-2"
                                        style="flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; p">
                                        <!-- Title and Social Icons -->
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <h5 class="categories-name text-capitalize " style="font-size: 15px;">
                                                <b>{{ $jobRequest->title }}</b>
                                            </h5>
                                           
                                        </div>
                                        <!-- Location -->
                                        <h5 class="mt-0 mb-0 text-truncate">
                                            <span style="font-size: 12px;">
                                                @if ($jobRequest->city && $jobRequest->country)
                                                    {{ $jobRequest->city->name }} - {{ $jobRequest->country->name }}
                                                @else
                                                    N/A
                                                @endif
                                            </span>
                                        </h5>
                                        <!-- Published Info -->
                                        <p class="mb-0" style="font-weight: 60;">Published at:
                                            {{ $jobRequest->created_at->toDateString() }}</p>


                                        <div class="d-flex align-items-center gap-2">
                                            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQnnM0ib-pYCZg4DbbB_T5_mfxpqrDHYXFLy208bjvHjIM5q1FF4lzLvNFp2qZ5Eo11orA&usqp=CAU"
                                                alt="Provider" style="width: 35px; border-radius: 50%;">
                                            <p style="margin: 0; color: #8081dc;">
                                                {{ $jobRequest->provider->username ?? 'Unknown' }}</p>
                                        </div>
                                        <!-- Status -->
                                        <h6 style="font-weight: 100;">Status: <span
                                                style="font-weight: 100; font-size: 13px;">{{ $jobRequest->status }}</span>
                                        </h6>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="#"><img
                                                    src="https://cdn.pixabay.com/photo/2021/06/15/12/51/facebook-6338507_1280.png"
                                                    alt="Facebook"
                                                    style="width: 20px; height: 20px; border-radius: 8px;"></a>
                                            <a href="#"><img
                                                    src="https://upload.wikimedia.org/wikipedia/commons/9/95/Instagram_logo_2022.svg"
                                                    alt="Instagram"
                                                    style="width: 20px; height: 20px; border-radius: 8px;"></a>
                                            <a href="#"><img
                                                    src="https://cdn.pixabay.com/photo/2015/03/10/17/30/twitter-667462_640.png"
                                                    alt="Twitter"
                                                    style="width: 20px; height: 20px; border-radius: 8px;"></a>
                                            <a href="#"><img
                                                    src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRokEYt0yyh6uNDKL8uksVLlhZ35laKNQgZ9g&s"
                                                    alt="LinkedIn"
                                                    style="width: 20px; height: 20px; border-radius: 8px;"></a>
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
                                    <div class="service-box-card bg-light rounded-3 mb-3"
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
                                        <ul class="list-inline p-0 mt-0 mb-0 price-content">
                                            <div class="service d-flex justify-content-center "
                                                style="position:relative; z-index:1111; margin:auto; background-image: url('{{ asset('images/icon/banner2.jpg') }}'); background-size: cover; width:68% ; margin-top:-32px;  background-repeat: no-repeat; background-position: center; padding: 10px 20px; color: white; font-weight: 600; font-size: 18px; border-radius: 10px; border: 3px solid #E1DCDD;">

                                                @if ($data->price == 0)
                                                    <li
                                                        class="text-white fw-500 d-inline-block position-relative font-size-18">
                                                        Free</li>
                                                @else
                                                    <li
                                                        class="text-white fw-500 d-inline-block position-relative font-size-18">
                                                        {{ getPriceFormat($data->price) }}/{{ $data->type }}
                                                    </li>
                                                @endif
                                            </div>
                                        </ul>


                                        <a href="{{ route('service.detail', $data->id) }}"
                                            class="service-heading mt-2 d-block p-0">
                                            <h5 class="service-heading text-capitalize"style="font-size:15px">
                                                <b>{{ $data->name }}</b> </h5>

                                                
                                        </a>


                                        <p class="mt-0 mb-0" style="font-size: 10;  ">
                                            {{ $data->city ? $data->city->name : 'City' }}-{{ $data->country ? $data->country->name : 'Country' }}
                                        </p>


                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <div class="d-flex align-items-center flex-nowrap">
                                                <img src="{{ getSingleMedia($data->providers, 'profile_image', null) }}"
                                                    alt="service" class="img-fluid rounded-3 object-cover avatar-24">
                                                <a href="{{ route('provider.detail', $data->providers->id) }}" class="ml-2">
                                                    <span class="font-size-14 service-user-name" style="white-space: nowrap;">
                                                        {{ $data->providers->display_name }}
                                                    </span>
                                                </a>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-end">
                                                <img src="{{ asset('images/icon/freeicon.jpg') }}" alt="icon"
                                                    style="width: 20px; height: 20px; margin-right: 10px;">
                                                <img src="{{ asset('images/icon/verified.jpg') }}" alt="icon"
                                                    style="width: 20px; height: 20px;">
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex align-items-center mt-2 gap-3"> 
                                            <div class="d-flex align-items-center gap-2 flex-wrap ml-2">
                                                <div class="star-rating">
                                                    <rating-component :readonly="true" :showrating="false" :ratingvalue="1" />
                                                </div>
                                                <h6 class="lh-sm mb-0">{{  $data->avg_rating }}</h6>
                                                <a href="{{ route('rating.all', ['service_id' => $data->id]) }}">({{  $data->total_reviews }} {{ __('messages.reviews') }})</a>
                                            </div>
                                            <p class="mb-0">{{ $data->booking_count }} Bookings</p>
                                        </div>
                                        


                                        <div class="d-flex mt-0 " style="gap: 18px; justify-content: center;">
                                            <a href="#"><img
                                                    src="https://cdn.pixabay.com/photo/2021/06/15/12/51/facebook-6338507_1280.png"
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
                                <div class="service-box-card bg-light rounded-3 mb-3"
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
                                    <ul class="list-inline p-0 mt-0 mb-0 price-content">
                                        <div class="service d-flex justify-content-center "
                                            style="position:relative; z-index:1111; margin:auto; background-image: url('{{ asset('images/icon/newbanner.jpg') }}'); background-size: cover; width:68% ; margin-top:-32px;  background-repeat: no-repeat; background-position: center; padding: 10px 20px; color: white; font-weight: 600; font-size: 18px; border-radius: 10px; border: 3px solid #E1DCDD;">

                                            @if ($data->price == 0)
                                                <li
                                                    class="text-white fw-500 d-inline-block position-relative font-size-18">
                                                    Free</li>
                                            @else
                                                <li
                                                    class="text-white fw-500 d-inline-block position-relative font-size-18">
                                                    {{ getPriceFormat($data->price) }}/{{ $data->type }}
                                                </li>
                                            @endif
                                        </div>
                                    </ul>


                                    <a href="{{ route('service.detail', $data->id) }}"
                                        class="service-heading mt-2 d-block p-0">
                                        <h5 class="service-heading text-capitalize"style="font-size:15px">
                                           <b>{{ $data->name }}</b> </h5>

                                           




                                    </a>


                                    <p class="mt-0 mb-0" style="font-size: 10;  ">
                                        {{ $data->city ? $data->city->name : 'City' }}-{{ $data->country ? $data->country->name : 'Country' }}
                                    </p>


                                    <div class="d-flex align-items-center justify-content-between w-100">
                                        <div class="d-flex align-items-center flex-nowrap">
                                            <img src="{{ getSingleMedia($data->providers, 'profile_image', null) }}"
                                                alt="service" class="img-fluid rounded-3 object-cover avatar-24">
                                            <a href="{{ route('provider.detail', $data->providers->id) }}" class="ml-2">
                                                <span class="font-size-14 service-user-name" style="white-space: nowrap;">
                                                    {{ $data->providers->display_name }}
                                                </span>
                                            </a>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-end">
                                            <img src="{{ asset('images/icon/freeicon.jpg') }}" alt="icon"
                                                style="width: 20px; height: 20px; margin-right: 10px;">
                                            <img src="{{ asset('images/icon/verified.jpg') }}" alt="icon"
                                                style="width: 20px; height: 20px;">
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center mt-2 gap-3"> 
                                        <div class="d-flex align-items-center gap-2 flex-wrap ml-2">
                                            <div class="star-rating">
                                                <rating-component :readonly="true" :showrating="false" :ratingvalue="1" />
                                            </div>
                                            <h6 class="lh-sm mb-0">1</h6>
                                            <a href="#">(1 {{ __('messages.reviews') }})</a>
                                        </div>
                                        <p class="mb-0">{{ $completedBookingCount }} Bookings</p>
                                    </div>


                                    <div class="d-flex mt-0 " style="gap: 18px; justify-content: center;">
                                        <a href="#"><img
                                                src="https://cdn.pixabay.com/photo/2021/06/15/12/51/facebook-6338507_1280.png"
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
