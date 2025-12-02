 @extends('landing-page.layouts.default')

 @section('content')

     <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

     <link rel="stylesheet" type="text/css"
         href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
     <style>
         <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"><link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap');

         <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>body {
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
     </head>

     <body>
         <div class="container mt-5">
             <div class="filter-section"
                 style="
                 background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                 border-radius: 20px;
                 padding: 30px;
                 margin-bottom: 40px;
                 box-shadow: 0 8px 32px rgba(102, 126, 234, 0.2);
             ">
                 <h4 class="text-white text-center mb-4"
                     style="
                     font-weight: 700;
                     font-size: 24px;
                     margin-bottom: 30px;
                     text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                 ">
                     <i class='bx bx-filter-alt' style="margin-right: 10px;"></i>
                     Find Your Perfect Job
                 </h4>

                 <div class="row g-3">
                     <div class="col-lg-2 col-md-4 col-6">
                         <select class="form-select filter-select" id="category-select" aria-label="Filter by Category"
                             style="
                             border: none;
                             border-radius: 12px;
                             padding: 12px 16px;
                             font-weight: 500;
                             background: rgba(255, 255, 255, 0.95);
                             backdrop-filter: blur(10px);
                             transition: all 0.3s ease;
                         ">
                             <option selected>Category</option>
                             @foreach ($categories as $category)
                                 <option value="{{ $category->id }}">{{ $category->name }}</option>
                             @endforeach
                         </select>
                     </div>

                     <div class="col-lg-2 col-md-4 col-6">
                         <select class="form-select filter-select" id="subcategory-select"
                             aria-label="Filter by Sub-Category"
                             style="
                             border: none;
                             border-radius: 12px;
                             padding: 12px 16px;
                             font-weight: 500;
                             background: rgba(255, 255, 255, 0.95);
                             backdrop-filter: blur(10px);
                             transition: all 0.3s ease;
                         ">
                             <option selected>Sub-Category</option>
                             @foreach ($subcategories as $subcategory)
                                 <option value="{{ $subcategory->id }}">{{ $subcategory->name }}</option>
                             @endforeach
                         </select>
                     </div>

                     <div class="col-lg-2 col-md-4 col-6">
                         <select class="form-select filter-select" id="customer-select" aria-label="Filter by Customer"
                             style="
                             border: none;
                             border-radius: 12px;
                             padding: 12px 16px;
                             font-weight: 500;
                             background: rgba(255, 255, 255, 0.95);
                             backdrop-filter: blur(10px);
                             transition: all 0.3s ease;
                         ">
                             <option selected>Customer</option>
                             @foreach ($customers as $customer)
                                 <option value="{{ $customer->id }}">{{ $customer->display_name }}</option>
                             @endforeach
                         </select>
                     </div>

                     <div class="col-lg-2 col-md-4 col-6">
                         <select class="form-select filter-select" id="country-select" aria-label="Filter by Country"
                             style="
                             border: none;
                             border-radius: 12px;
                             padding: 12px 16px;
                             font-weight: 500;
                             background: rgba(255, 255, 255, 0.95);
                             backdrop-filter: blur(10px);
                             transition: all 0.3s ease;
                         ">
                             <option selected>Country</option>
                             @foreach ($countries as $country)
                                 <option value="{{ $country->id }}">{{ $country->name }}</option>
                             @endforeach
                         </select>
                     </div>

                     <div class="col-lg-2 col-md-4 col-6">
                         <select class="form-select filter-select" id="city-select" aria-label="Filter by City"
                             style="
                             border: none;
                             border-radius: 12px;
                             padding: 12px 16px;
                             font-weight: 500;
                             background: rgba(255, 255, 255, 0.95);
                             backdrop-filter: blur(10px);
                             transition: all 0.3s ease;
                         ">
                             <option selected>City</option>
                         </select>
                     </div>

                     <div class="col-lg-2 col-md-4 col-6">
                         <select class="form-select filter-select" id="sort-select" aria-label="Sort by Price"
                             style="
                             border: none;
                             border-radius: 12px;
                             padding: 12px 16px;
                             font-weight: 500;
                             background: rgba(255, 255, 255, 0.95);
                             backdrop-filter: blur(10px);
                             transition: all 0.3s ease;
                         ">
                             <option selected>Sort by</option>
                             <option value="1">Price: Low to High</option>
                             <option value="2">Price: High to Low</option>
                         </select>
                     </div>

                     <!-- Clear Filters Button -->
                     <div class="col-lg-2 col-md-4 col-6">
                         <button class="form-select filter-select" id="clear-filters-btn"
                             style="
                             border: none;
                             border-radius: 12px;
                             padding: 12px 16px;
                             font-weight: 500;
                             background: rgba(255, 255, 255, 0.95);
                             backdrop-filter: blur(10px);
                             transition: all 0.3s ease;
                             cursor: pointer;
                             text-align: left;
                         ">
                             <i class="fas fa-times me-2"></i>Clear Filters
                         </button>
                     </div>
                 </div>
             </div>
         </div>

         <div class="container mt-5">
             <div class="row">
                 @if ($jobrequest->isEmpty())
                     <div class="col-12">
                         <div class="alert alert-warning text-center"
                             style="border-radius: 10px; background-color: #fff3cd; color: #856404;">
                             <strong>Notice:</strong> No data exists based on the selected filters.
                         </div>
                     </div>
                 @else
                     @foreach ($jobrequest as $jobRequest)
                         <div class="col-lg-3 col-md-6 col-12 mb-3">
                             <a href="{{ route('job.details', $jobRequest->id) }}" class="card-link text-decoration-none">
                                 <div class="job-card h-100"
                                     style="
                             background: #FFFFFF;
                             border: 1px solid #E8E9EC;
                             border-radius: 16px;
                             box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                             transition: all 0.3s ease;
                             overflow: hidden;
                             position: relative;
                         ">
                                     <!-- Card Image Container -->
                                     <div class="image-container"
                                         style="position: relative; height: 120px; overflow: hidden;">
                                         @if (!empty($jobRequest->image))
                                             <img src="{{ asset('storage/' . $jobRequest->image) }}" alt="Job Image"
                                                 style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                                         @else
                                             <img class="default-image"
                                                 src="{{ asset('images/post-job/ac_refresh_and_revive.png') }}"
                                                 alt="Default Image"
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
                                                     {{ $jobRequest->city ? $jobRequest->city->name : 'City' }},
                                                     {{ $jobRequest->country ? $jobRequest->country->name : 'Country' }}
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
                                                     {{ number_format($jobRequest->total_views ?? 0) }} views
                                                 </span>
                                             </div>
                                         </div>

                                         <!-- Customer Info -->
                                         <div class="customer-info" style="margin-bottom: 8px;">
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
                                                         {{ $jobRequest->customer->display_name ?? ($jobRequest->customer->username ?? 'Unknown') }}
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
                                                     Status
                                                 </span>
                                                 <span class="status-badge"
                                                     style="
                                             padding: 3px 6px;
                                             border-radius: 8px;
                                             font-size: 9px;
                                             font-weight: 600;
                                             text-transform: uppercase;
                                             letter-spacing: 0.5px;
                                             background: {{ $jobRequest->status === 'active' ? '#e8f5e8' : '#fff3cd' }};
                                             color: {{ $jobRequest->status === 'active' ? '#2d5a2d' : '#856404' }};
                                         ">
                                                     {{ ucfirst($jobRequest->status ?? 'Pending') }}
                                                 </span>
                                             </div>
                                         </div>

                                         <!-- Social Icons -->
                                        <div class="social-icons"
                                             style="
                                     display: flex;
                                     align-items: center;
                                     gap: 6px;
                                     padding-top: 8px;
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
                                                 <img src="https://static.vecteezy.com/system/resources/previews/016/716/481/original/facebook-icon-free-png.png"
                                                     alt="Facebook" style="width: 12px; height: 12px;">
                                            </span>
                                            <span role="button" tabindex="0" class="social-link share-link" data-platform="instagram" data-job-id="{{ $jobRequest->id }}" data-quote="{{ $jobRequest->title }} • {{ getPriceFormat($jobRequest->price) }} • {{ ucfirst($jobRequest->price_type ?? 'fixed') }} • {{ data_get($jobRequest,'city.name','City') }}, {{ data_get($jobRequest,'country.name','Country') }} — {{ route('job.details', $jobRequest->id) }}" data-image-url="{{ !empty($jobRequest->image) ? asset('storage/' . ltrim($jobRequest->image, '/')) : asset('images/post-job/ac_refresh_and_revive.png') }}" onclick="return window.__shareClickHandler(event, this);"
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
                                                 <img src="https://upload.wikimedia.org/wikipedia/commons/9/95/Instagram_logo_2022.svg"
                                                     alt="Instagram" style="width: 12px; height: 12px;">
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
                                                 <img src="https://cdn.pixabay.com/photo/2015/03/10/17/30/twitter-667462_640.png"
                                                     alt="Twitter" style="width: 12px; height: 12px;">
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
                                         justify-content: center;
                                     ">
                                                 <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRokEYt0yyh6uNDKL8uksVLlhZ35laKNQgZ9g&s"
                                                     alt="LinkedIn" style="width: 12px; height: 12px;">
                                            </span>
                                         </div>
                                     </div>
                                 </div>
                             </a>
                         </div>
                     @endforeach
                 @endif
             </div>
         </div>


         <!-- Include Bootstrap JS and jQuery -->
         <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
         <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
         <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

        <script>
             $(document).ready(function() {
                 // When the category dropdown changes - ONLY load subcategories, don't apply filters
                 $('#category-select').on('change', function() {
                     var categoryId = $(this).val();

                     // Reset subcategory dropdown
                     $('#subcategory-select').empty();
                     $('#subcategory-select').append('<option selected>Sub-Category</option>');

                     if (categoryId && categoryId !== 'Category') {
                         // Make AJAX request to fetch subcategories
                         $.ajax({
                             url: '{{ route('subcategory.listforgood') }}',
                             method: 'GET',
                             data: {
                                 category_id: categoryId
                             },
                             success: function(response) {
                                 // Populate the subcategory dropdown with the new options
                                 $.each(response, function(key, subcategory) {
                                     $('#subcategory-select').append('<option value="' +
                                         subcategory.id + '">' + subcategory.name +
                                         '</option>');
                                 });
                             },
                             error: function(xhr, status, error) {
                                 console.error('AJAX request failed:', status, error);
                             }
                         });
                     }
                 });

                 // When the country dropdown changes - ONLY load cities, don't apply filters
                 $('#country-select').on('change', function() {
                     var countryId = $(this).val();

                     // Reset city dropdown
                     $('#city-select').empty();
                     $('#city-select').append('<option selected>City</option>');

                     if (countryId && countryId !== 'Country') {
                         // Make AJAX request to fetch cities based on the selected country
                         $.ajax({
                             url: '/ajax-cities/' + countryId,
                             method: 'GET',
                             success: function(response) {
                                 // Populate the city dropdown with the new options
                                 $.each(response, function(key, city) {
                                     $('#city-select').append('<option value="' + city.id +
                                         '">' + city.name + '</option>');
                                 });
                             },
                             error: function(xhr, status, error) {
                                 console.error('AJAX request failed:', status, error);
                             }
                         });
                     }
                 });

                 // Apply filters only when final selections are made
                 $('#subcategory-select, #customer-select, #city-select, #sort-select').on('change', function() {
                     // Small delay to ensure all dropdowns are updated
                     setTimeout(function() {
                         applyFilters();
                     }, 100);
                 });

                 // Clear Filters functionality
                 $('#clear-filters-btn').on('click', function() {
                     clearAllFilters();
                 });

                 // Function to apply filters
                 function applyFilters() {
                     const params = new URLSearchParams(window.location.search);

                     // Get all filter values
                     const categoryId = $('#category-select').val();
                     const subcategoryId = $('#subcategory-select').val();
                     const customerId = $('#customer-select').val();
                     const countryId = $('#country-select').val();
                     const cityId = $('#city-select').val();
                     const sortValue = $('#sort-select').val();

                     // Set parameters only if they have actual values (not default text)
                     if (categoryId && categoryId !== 'Category') {
                         params.set('category_id', categoryId);
                     } else {
                         params.delete('category_id');
                     }

                     if (subcategoryId && subcategoryId !== 'Sub-Category') {
                         params.set('subcategory_id', subcategoryId);
                     } else {
                         params.delete('subcategory_id');
                     }

                     if (customerId && customerId !== 'Customer') {
                         params.set('customer_id', customerId);
                     } else {
                         params.delete('customer_id');
                     }

                     if (countryId && countryId !== 'Country') {
                         params.set('country_id', countryId);
                     } else {
                         params.delete('country_id');
                     }

                     if (cityId && cityId !== 'City') {
                         params.set('city_id', cityId);
                     } else {
                         params.delete('city_id');
                     }

                     if (sortValue && sortValue !== 'Sort by') {
                         params.set('sort', sortValue);
                     } else {
                         params.delete('sort');
                     }

                     // Reload page with new parameters
                     window.location.search = params.toString();
                 }

                 // Function to clear all filters
                 function clearAllFilters() {
                     // Reset all dropdowns to default
                     $('#category-select').val('Category');
                     $('#subcategory-select').empty().append('<option selected>Sub-Category</option>');
                     $('#customer-select').val('Customer');
                     $('#country-select').val('Country');
                     $('#city-select').empty().append('<option selected>City</option>');
                     $('#sort-select').val('Sort by');

                     // Clear URL parameters
                     window.location.search = '';
                 }
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
                } else if (platform === 'instagram') {
                    // Instagram does not support link share with prefilled text via web.
                    // On mobile, try the Web Share API as a graceful fallback.
                    var quote = el.getAttribute('data-quote') || '';
                    var imageUrl = el.getAttribute('data-image-url') || '';
                    var shareText = quote;

                    if (navigator.share) {
                        try {
                            navigator.share({ text: shareText, url: shareUrl || window.location.href })
                                .catch(function() { /* ignore */ });
                        } catch (_) {
                            // fallback
                            openPopup('https://www.instagram.com/');
                        }
                    } else {
                        // fallback open
                        openPopup('https://www.instagram.com/');
                    }
                }

                return false;
            };
        </script>
     </body>

     </html>


     </html>
 @endsection
