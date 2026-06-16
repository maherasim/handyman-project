 @extends('landing-page.layouts.default')

 @section('content')

     <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

     <link rel="stylesheet" type="text/css"
         href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
     <style>
         .job-card .dropdown-toggle-no-caret::after { display: none !important; }
         @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap');
         body {
             font-family: "Montserrat", sans-serif;
         }

         .heading_text {
             font-weight: 600;
         }

         .filter-section {
             background-color: #fff;
             padding: 20px;
             display: flex;
             justify-content: center;
             border-bottom: 1px solid #ccc;
         }

         .filters select {
             margin: 0 10px;
             padding: 10px;
             border: 1px solid #ccc;
             border-radius: 4px;
         }

         .card {
             border-radius: 10px;
             box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
         }

         .card-body {
             text-align: center;
         }

         .card-img-top {
             height: 150px;
             object-fit: cover;
         }

         .star-rating {
             color: #f39c12;
         }

         .card-footer {
             background-color: white;
         }

         .icon-heart {
             color: #bbb;
         }

         .icon-heart:hover {
             color: #f39c12;
         }

         .review-count {
             color: #777;
         }

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

         /* Filter Select Styling */
         .filter-select {
             box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
         }

         .filter-select:hover {
             transform: translateY(-2px);
             box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
         }

         .filter-select:focus {
             outline: none;
             box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
             transform: translateY(-2px);
         }

         /* Enhanced typography */
         .job-title {
             font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
         }

         .price-badge {
             font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
             letter-spacing: 0.5px;
         }

         .status-badge {
             font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
         }
     </style>
         <div class="container mt-5">
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-3">
                <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-filter me-2"></i> {{ __('landingpage.jd_find_perfect_job') }}</h5>
            </div>
            <div class="card-body p-4">
                <form method="GET" action="{{ route('job.data') }}" id="jobFilterForm">
                    <div class="row g-3">
                        <!-- Category -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">{{ __('messages.category') }}</label>
                            <select name="category_id" id="category-select" class="form-select bg-light w-100">
                                <option value="">{{ __('landingpage.jd_all_categories') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subcategory -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">{{ __('landingpage.jd_sub_category') }}</label>
                            <select name="subcategory_id" id="subcategory-select" class="form-select bg-light w-100">
                                <option value="">{{ __('landingpage.jd_all_sub_categories') }}</option>
                                @foreach ($subcategories as $subcategory)
                                    <option value="{{ $subcategory->id }}" {{ request('subcategory_id') == $subcategory->id ? 'selected' : '' }}>
                                        {{ $subcategory->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Customer -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">{{ __('messages.customer') }}</label>
                            <select name="customer_id" id="customer-select" class="form-select bg-light w-100">
                                <option value="">{{ __('landingpage.jd_all_customers') }}</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->display_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                         <!-- Country -->
                         <div class="col-lg-3 col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">{{ __('messages.country') }}</label>
                            <select name="country_id" id="country-select" class="form-select bg-light w-100">
                                <option value="">{{ __('landingpage.jd_all_countries') }}</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}" {{ request('country_id') == $country->id ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- City -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">{{ __('messages.city') }}</label>
                            <select name="city_id" id="city-select" class="form-select bg-light w-100">
                                <option value="">{{ __('landingpage.jd_all_cities') }}</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sort By -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">{{ __('landingpage.jd_sort_by') }}</label>
                            <select name="sort" id="sort-select" class="form-select bg-light w-100">
                                <option value="">{{ __('landingpage.jd_default') }}</option>
                                <option value="1" {{ request('sort') == '1' ? 'selected' : '' }}>{{ __('landingpage.jd_price_low_high') }}</option>
                                <option value="2" {{ request('sort') == '2' ? 'selected' : '' }}>{{ __('landingpage.jd_price_high_low') }}</option>
                            </select>
                        </div>
                        
                         <!-- Buttons -->
                         <div class="col-lg-6 col-md-12 d-flex align-items-end">
                            <div class="d-flex gap-2 w-100">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="fas fa-filter me-2"></i>{{ __('landingpage.jd_apply_filters') }}
                                </button>
                                <a href="{{ route('job.data') }}" class="btn btn-light border flex-grow-1">
                                    <i class="fas fa-times me-2"></i>{{ __('landingpage.jd_clear') }}
                                </a>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>

         <div class="container mt-5">
             <div class="row">
                 @if ($jobrequest->isEmpty())
                     <div class="col-12">
                         <div class="text-center py-5 bg-white rounded shadow-sm">
                             <img src="{{ asset('assets/search-no-data.png') }}" class="img-fluid mb-3" style="max-width: 200px;" alt="{{ __('landingpage.jd_no_jobs_found') }}">
                             <h4 class="text-muted">{{ __('landingpage.jd_no_jobs_found') }}</h4>
                             <p class="text-muted mb-0">{{ __('landingpage.jd_try_adjusting_filters') }}</p>
                         </div>
                     </div>
                 @else
                     @foreach ($jobrequest as $jobRequest)
                         @php
                             $posterId = $jobRequest->customer_id;
                             $canJobUgc = auth()->check()
                                 && $posterId
                                 && \App\Support\UgcListing::canReportPostJob(auth()->user(), $jobRequest);
                         @endphp
                         <div class="col-lg-3 col-md-6 col-12 mb-3">
                                 <div class="job-card h-100"
                                     style="
                             background: #FFFFFF;
                             border: 1px solid #E8E9EC;
                             border-radius: 16px;
                             box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                             transition: all 0.3s ease;
                             position: relative;
                         ">
                                     <a href="{{ route('job.details', $jobRequest->id) }}" class="text-decoration-none text-reset d-block">
                                     <!-- Card Image Container -->
                                     <div class="image-container"
                                         style="position: relative; height: 120px; overflow: hidden;">
                                         @if (!empty($jobRequest->image))
                                             <img src="{{ asset('storage/' . $jobRequest->image) }}" alt="{{ __('landingpage.jd_job_image') }}"
                                                 style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                                         @else
                                             <img class="default-image"
                                                 src="{{ asset('images/post-job/ac_refresh_and_revive.png') }}"
                                                 alt="{{ __('landingpage.jd_default_image') }}"
                                                 style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                                         @endif

                                         <!-- Price Badge -->
                                         <div class="price-badge"
                                             style="
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
                                            {{ getPriceFormat($jobRequest->price) }} /
                                             {{ ucfirst($jobRequest->price_type ?? 'fixed') }}
                                         </div>

                                         <!-- Heart Icon -->
                                         <div class="heart-icon"
                                             style="
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
                                         <h5 class="job-title"
                                             style="
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
                                                 <span
                                                     style="
                                             font-size: 12px;
                                             color: #666;
                                             font-weight: 500;
                                         ">
                                                     {{ $jobRequest->city ? $jobRequest->city->name : __('messages.city') }},
                                                     {{ $jobRequest->country ? $jobRequest->country->name : __('messages.country') }}
                                                 </span>
                                             </div>
                                         </div>

                                         <!-- Published Date -->
                                         <div class="published-info" style="margin-bottom: 8px;">
                                             <div class="d-flex align-items-center" style="gap: 5px;">
                                                 <i class='bx bx-calendar' style="color: #8e8e93; font-size: 11px;"></i>
                                                 <span
                                                     style="
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
                                                 <span
                                                     style="
                                             font-size: 11px;
                                             color: #8e8e93;
                                             font-weight: 400;
                                         ">
                                                     {{ number_format($jobRequest->total_views ?? 0) }} {{ __('landingpage.jd_views') }}
                                                 </span>
                                             </div>
                                         </div>
                                     </div>
                                     </a>
                                     {{-- UGC ⋮ menu must NOT be inside <a href> (invalid HTML: interactive in interactive — breaks clicks) --}}
                                     <div class="card-content" style="padding: 0 12px 12px;">
                                         <!-- Customer Info -->
                                         <div class="customer-info" style="margin-bottom: 8px;">
                                             <div class="d-flex align-items-center justify-content-between">
                                                 <div class="d-flex align-items-center" style="gap: 6px;">
                                                     <div class="customer-avatar"
                                                         style="
                                                 width: 28px;
                                                 height: 28px;
                                                 border-radius: 50%;
                                                 overflow: hidden;
                                                 border: 2px solid #f0f0f0;
                                             ">
                                                         <img src="{{ getSingleMedia($jobRequest->customer, 'profile_image', null) ?? 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQnnM0ib-pYCZg4DbbB_T5_mfxpqrDHYXFLy208bjvHjIM5q1FF4lzLvNFp2qZ5Eo11orA&usqp=CAU' }}"
                                                             alt="Customer"
                                                             style="width: 100%; height: 100%; object-fit: cover;">
                                                     </div>
                                                     <div class="customer-details">
                                                         <div
                                                             style="
                                                     font-size: 12px;
                                                     font-weight: 600;
                                                     color: #1a1a1a;
                                                     margin-bottom: 1px;
                                                 ">
                                                             {{ $jobRequest->customer->display_name ?? ($jobRequest->customer->username ?? __('landingpage.jd_unknown')) }}
                                                         </div>
                                                         <div
                                                             style="
                                                     font-size: 10px;
                                                     color: #8e8e93;
                                                     font-weight: 400;
                                                 ">
                                                             {{ data_get($jobRequest, 'customer.city.name', 'Unknown') }} -
                                                             {{ data_get($jobRequest, 'customer.country.name', 'Unknown') }}

                                                         </div>
                                                     </div>
                                                 </div>
                                                 
                                                 @if($canJobUgc)
                                                 <div class="position-relative ugc-job-actions" style="z-index: 50;">
                                                    <button type="button" class="btn btn-link text-muted text-decoration-none border-0 p-1 bg-transparent" aria-label="Report job" onclick="
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
                                                                <i class="fas fa-flag fa-fw me-2"></i>Report job
                                                             </a>
                                                         </li>
                                                         <li>
                                                             <a class="dropdown-item py-2 text-danger px-3 d-block" href="javascript:void(0)" onclick="event.preventDefault(); event.stopPropagation(); this.closest('.ugc-dropdown-menu-job').style.display='none'; if(window.triggerUgcBlock) window.triggerUgcBlock({{ $posterId }}, this, 'customer');">
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
                                                 <span
                                                     style="
                                             font-size: 10px;
                                             color: #8e8e93;
                                             font-weight: 500;
                                             text-transform: uppercase;
                                             letter-spacing: 0.5px;
                                         ">
{{ __('messages.status') }}
                                                </span>
                                                @php
                                                    $statusKey = strtolower((string)($jobRequest->status ?? ''));
                                                    $isCompleted = in_array($statusKey, ['confirm_done', 'completed']);
                                                    $isGreen = $isCompleted || $jobRequest->status === 'active';
                                                    $statusLabel = $isCompleted ? __('landingpage.jd_completed') : ucfirst($jobRequest->status ?? __('landingpage.jd_pending'));
                                                @endphp
                                                <span class="status-badge"
                                                     style="
                                             padding: 3px 6px;
                                             border-radius: 8px;
                                             font-size: 9px;
                                             font-weight: 600;
                                             text-transform: uppercase;
                                             letter-spacing: 0.5px;
                                             background: {{ $isGreen ? '#e8f5e8' : '#fff3cd' }};
                                             color: {{ $isGreen ? '#2d5a2d' : '#856404' }};
                                         ">
                                                     {{ $statusLabel }}
                                                 </span>
                                             </div>
                                         </div>
                                     </div>
                                        <!-- Social Icons -->
                                        <div class="social-icons"
                                             style="
                                     display: flex;
                                     align-items: center;
                                     gap: 6px;
                                     padding: 8px 12px 12px;
                                     border-top: 1px solid #f0f0f0;
                                 ">
                                            <span role="button" tabindex="0" class="social-link share-link" data-platform="facebook" data-job-id="{{ $jobRequest->id }}" data-share-url="{{ route('job.details', $jobRequest->id) }}?v={{ optional($jobRequest->updated_at)->timestamp ?? time() }}" data-quote="{{ $jobRequest->title }} • {{ getPriceFormat($jobRequest->price) }} • {{ ucfirst($jobRequest->price_type ?? 'fixed') }} • {{ data_get($jobRequest,'city.name','City') }}, {{ data_get($jobRequest,'country.name','Country') }}" onclick="return window.__shareClickHandler(event, this);"
                                                 style="
                                         width: 24px;
                                         height: 24px;
                                         border-radius: 5px;
                                         background: #f8f9fa;
                                         display: flex;
                                         align-items: center;
                                         justify-content: center;
                                         transition: all 0.3s ease;
                                          text-decoration: none; cursor: pointer;
                                     ">
                                                <img src="{{ asset('assets/fb.png') }}?v=20260303"
                                                    alt="{{ __('landingpage.pd_alt_facebook') }}" style="width: 24px; height: 24px; object-fit: contain; transform: scale(1.16);">
                                            </span>
                                            <span role="button" tabindex="0" class="social-link share-link" data-platform="telegram" data-share-url="{{ route('job.details', $jobRequest->id) }}?v={{ optional($jobRequest->updated_at)->timestamp ?? time() }}" data-quote="{{ $jobRequest->title }} • {{ getPriceFormat($jobRequest->price) }} • {{ ucfirst($jobRequest->price_type ?? 'fixed') }} • {{ data_get($jobRequest,'city.name','City') }}, {{ data_get($jobRequest,'country.name','Country') }}" onclick="return window.__shareClickHandler(event, this);"
                                                 style="
                                         width: 24px;
                                         height: 24px;
                                         border-radius: 5px;
                                         background: #f8f9fa;
                                         display: flex;
                                         align-items: center;
                                         justify-content: center;
                                         transition: all 0.3s ease;
                                          text-decoration: none; cursor: pointer;
                                     ">
                                                 <img src="{{ asset('assets/telegram.png') }}?v=20260303"
                                                     alt="{{ __('landingpage.pd_alt_telegram') }}" style="width: 24px; height: 24px; object-fit: contain;">
                                            </span>
                                            <span role="button" tabindex="0" class="social-link share-link" data-platform="twitter" data-share-url="{{ route('job.details', $jobRequest->id) }}?v={{ optional($jobRequest->updated_at)->timestamp ?? time() }}" data-text="{{ $jobRequest->title }} • {{ getPriceFormat($jobRequest->price) }} • {{ ucfirst($jobRequest->price_type ?? 'fixed') }} • {{ data_get($jobRequest,'city.name','City') }}, {{ data_get($jobRequest,'country.name','Country') }}" onclick="return window.__shareClickHandler(event, this);"
                                                 style="
                                         width: 24px;
                                         height: 24px;
                                         border-radius: 5px;
                                         background: #f8f9fa;
                                         display: flex;
                                         align-items: center;
                                         justify-content: center;
                                         transition: all 0.3s ease;
                                          text-decoration: none; cursor: pointer;
                                     ">
                                                 <img src="{{ asset('assets/twiter.png') }}?v=20260303"
                                                     alt="{{ __('landingpage.pd_alt_twitter') }}" style="width: 24px; height: 24px; object-fit: contain;">
                                            </span>
                                            <span role="button" tabindex="0" class="social-link share-link" data-platform="linkedin" data-share-url="{{ route('job.details', $jobRequest->id) }}?v={{ optional($jobRequest->updated_at)->timestamp ?? time() }}" onclick="return window.__shareClickHandler(event, this);"
                                                 style="
                                         width: 24px;
                                         height: 24px;
                                         border-radius: 5px;
                                         background: #f8f9fa;
                                         transition: all 0.3s ease;
                                          text-decoration: none; cursor: pointer;
                                         display: flex;
                                         align-items: center;
                                     ">
                                                 <img src="{{ asset('assets/linkedIn.png') }}?v=20260303"
                                                     alt="{{ __('landingpage.pd_alt_linkedin') }}" style="width: 24px; height: 24px; object-fit: contain;">
                                             </span>
                                         </div>
                                 </div>
                         </div>
                     @endforeach
                 @endif
             </div>
         </div>


         <!-- Bootstrap 4 dropdowns require Popper.js v1 (not @popperjs/core v2 — otherwise "n is not a constructor" in dropdown.js). -->
         <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
         <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
         <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

        <script>
             $(document).ready(function() {
                 
                 // Dependent Dropdowns Logic
                 $('#category-select').on('change', function() {
                     var categoryId = $(this).val();
                     $('#subcategory-select').empty().append('<option value="">{{ __("messages.all_sub_categories") }}</option>');
                     
                     if (categoryId) {
                         $.ajax({
                             url: '{{ route('subcategory.listforgood') }}',
                             method: 'GET',
                             data: { category_id: categoryId },
                             success: function(response) {
                                 $.each(response, function(key, subcategory) {
                                     $('#subcategory-select').append('<option value="' + subcategory.id + '">' + subcategory.name + '</option>');
                                 });
                             }
                         });
                     }
                 });

                 $('#country-select').on('change', function() {
                     var countryId = $(this).val();
                     $('#city-select').empty().append('<option value="">' + @json(__('landingpage.jd_all_cities')) + '</option>');
                     
                     if (countryId) {
                         $.ajax({
                             url: '{{ route('ajax.list.cities') }}',
                             method: 'GET',
                             data: { country_id: countryId },
                             success: function(response) {
                                 $.each(response, function(key, city) {
                                     $('#city-select').append('<option value="' + city.id + '">' + city.text + '</option>');
                                 });
                             }
                         });
                     }
                 });
             });
         </script>

        <script>
            window.__shareClickHandler = function(e, el) {
                try {
                    e.preventDefault();
                    e.stopPropagation();
                } catch (_) {}

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
        </script>
 @endsection
