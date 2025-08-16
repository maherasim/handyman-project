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
             <div class="filter-section" style="
                 background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                 border-radius: 20px;
                 padding: 30px;
                 margin-bottom: 40px;
                 box-shadow: 0 8px 32px rgba(102, 126, 234, 0.2);
             ">
                 <h4 class="text-white text-center mb-4" style="
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
                         <select class="form-select filter-select" id="category-select" aria-label="Filter by Category" style="
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
                         <select class="form-select filter-select" id="subcategory-select" aria-label="Filter by Sub-Category" style="
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
                         <select class="form-select filter-select" id="customer-select" aria-label="Filter by Customer" style="
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
                         <select class="form-select filter-select" id="country-select" aria-label="Filter by Country" style="
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
                         <select class="form-select filter-select" id="city-select" aria-label="Filter by City" style="
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
                         <select class="form-select filter-select" id="sort-select" aria-label="Sort by Price" style="
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
                 <div class="col-lg-4 col-md-6 col-12 mb-4">
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
                             <div class="image-container" style="position: relative; height: 160px; overflow: hidden;">
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
                                     bottom: 12px;
                                     left: 12px;
                                     background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                     color: white;
                                     padding: 6px 12px;
                                     border-radius: 16px;
                                     font-weight: 600;
                                     font-size: 12px;
                                     box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
                                     backdrop-filter: blur(10px);
                                 ">
                                     €{{ number_format($jobRequest->price) }} / {{ ucfirst($jobRequest->price_type ?? 'fixed') }}
                                 </div>
                                 
                                 <!-- Heart Icon -->
                                 <div class="heart-icon" style="
                                     position: absolute;
                                     top: 12px;
                                     right: 12px;
                                     width: 32px;
                                     height: 32px;
                                     background: rgba(255, 255, 255, 0.9);
                                     border-radius: 50%;
                                     display: flex;
                                     align-items: center;
                                     justify-content: center;
                                     backdrop-filter: blur(10px);
                                     transition: all 0.3s ease;
                                 ">
                                     <i class='bx bx-heart' style="color: #667eea; font-size: 16px;"></i>
                                 </div>
                             </div>
                             
                             <!-- Card Content -->
                             <div class="card-content" style="padding: 16px;">
                                 <!-- Job Title -->
                                 <h5 class="job-title" style="
                                     font-size: 16px;
                                     font-weight: 700;
                                     color: #1a1a1a;
                                     margin-bottom: 8px;
                                     line-height: 1.3;
                                     display: -webkit-box;
                                     -webkit-line-clamp: 2;
                                     -webkit-box-orient: vertical;
                                     overflow: hidden;
                                     min-height: 42px;
                                 ">
                                     {{ $jobRequest->title }}
                                 </h5>
                                 
                                 <!-- Location -->
                                 <div class="location-info" style="margin-bottom: 10px;">
                                     <div class="d-flex align-items-center" style="gap: 6px;">
                                         <i class='bx bx-map-pin' style="color: #667eea; font-size: 14px;"></i>
                                         <span style="
                                             font-size: 13px;
                                             color: #666;
                                             font-weight: 500;
                                         ">
                                             {{ $jobRequest->city ? $jobRequest->city->name : 'City' }}, {{ $jobRequest->country ? $jobRequest->country->name : 'Country' }}
                                         </span>
                                     </div>
                                 </div>
                                 
                                 <!-- Published Date -->
                                 <div class="published-info" style="margin-bottom: 12px;">
                                     <div class="d-flex align-items-center" style="gap: 6px;">
                                         <i class='bx bx-calendar' style="color: #8e8e93; font-size: 12px;"></i>
                                         <span style="
                                             font-size: 12px;
                                             color: #8e8e93;
                                             font-weight: 400;
                                         ">
                                             {{ $jobRequest->created_at->diffForHumans() }}
                                         </span>
                                     </div>
                                 </div>
                                 
                                 <!-- Customer Info -->
                                 <div class="customer-info" style="margin-bottom: 12px;">
                                     <div class="d-flex align-items-center" style="gap: 8px;">
                                         <div class="customer-avatar" style="
                                             width: 32px;
                                             height: 32px;
                                             border-radius: 50%;
                                             overflow: hidden;
                                             border: 2px solid #f0f0f0;
                                         ">
                                             <img src="{{ getSingleMedia($jobRequest->customer,'profile_image', null) ?? 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQnnM0ib-pYCZg4DbbB_T5_mfxpqrDHYXFLy208bjvHjIM5q1FF4lzLvNFp2qZ5Eo11orA&usqp=CAU' }}"
                                                 alt="Customer" style="width: 100%; height: 100%; object-fit: cover;">
                                         </div>
                                         <div class="customer-details">
                                             <div style="
                                                 font-size: 13px;
                                                 font-weight: 600;
                                                 color: #1a1a1a;
                                                 margin-bottom: 1px;
                                             ">
                                                 {{ $jobRequest->customer->display_name ?? $jobRequest->customer->username ?? 'Unknown' }}
                                             </div>
                                             <div style="
                                                 font-size: 11px;
                                                 color: #8e8e93;
                                                 font-weight: 400;
                                             ">
                                                 Job Poster
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                                 
                                 <!-- Status Badge -->
                                 <div class="status-section" style="margin-bottom: 12px;">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <span style="
                                             font-size: 11px;
                                             color: #8e8e93;
                                             font-weight: 500;
                                             text-transform: uppercase;
                                             letter-spacing: 0.5px;
                                         ">
                                             Status
                                         </span>
                                         <span class="status-badge" style="
                                             padding: 4px 8px;
                                             border-radius: 10px;
                                             font-size: 10px;
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
                                 <div class="social-icons" style="
                                     display: flex;
                                     align-items: center;
                                     gap: 8px;
                                     padding-top: 12px;
                                     border-top: 1px solid #f0f0f0;
                                 ">
                                     <a href="#" class="social-link" style="
                                         width: 28px;
                                         height: 28px;
                                         border-radius: 6px;
                                         background: #f8f9fa;
                                         display: flex;
                                         align-items: center;
                                         justify-content: center;
                                         transition: all 0.3s ease;
                                         text-decoration: none;
                                     ">
                                         <img src="https://static.vecteezy.com/system/resources/previews/016/716/481/original/facebook-icon-free-png.png"
                                             alt="Facebook" style="width: 14px; height: 14px;">
                                     </a>
                                     <a href="#" class="social-link" style="
                                         width: 28px;
                                         height: 28px;
                                         border-radius: 6px;
                                         background: #f8f9fa;
                                         display: flex;
                                         align-items: center;
                                         justify-content: center;
                                         transition: all 0.3s ease;
                                         text-decoration: none;
                                     ">
                                         <img src="https://upload.wikimedia.org/wikipedia/commons/9/95/Instagram_logo_2022.svg"
                                             alt="Instagram" style="width: 14px; height: 14px;">
                                     </a>
                                     <a href="#" class="social-link" style="
                                         width: 28px;
                                         height: 28px;
                                         border-radius: 6px;
                                         background: #f8f9fa;
                                         display: flex;
                                         align-items: center;
                                         justify-content: center;
                                         transition: all 0.3s ease;
                                         text-decoration: none;
                                     ">
                                         <img src="https://cdn.pixabay.com/photo/2015/03/10/17/30/twitter-667462_640.png"
                                             alt="Twitter" style="width: 14px; height: 14px;">
                                     </a>
                                     <a href="#" class="social-link" style="
                                         width: 28px;
                                         height: 28px;
                                         border-radius: 6px;
                                         background: #f8f9fa;
                                         transition: all 0.3s ease;
                                         text-decoration: none;
                                         display: flex;
                                         align-items: center;
                                         justify-content: center;
                                     ">
                                         <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRokEYt0yyh6uNDKL8uksVLlhZ35laKNQgZ9g&s"
                                             alt="LinkedIn" style="width: 14px; height: 14px;">
                                     </a>
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
                 // When the category dropdown changes
                 $('#category-select').on('change', function() {
                     var categoryId = $(this).val(); // Get the selected category ID

                     // Make AJAX request to fetch subcategories
                     $.ajax({
                         url: '{{ route('subcategory.listforgood') }}',
                         method: 'GET',
                         data: {
                             category_id: categoryId
                         },
                         success: function(response) {
                             // Clear the existing options in the subcategory dropdown
                             $('#subcategory-select').empty();
                             $('#subcategory-select').append(
                                 '<option selected>Filter by Sub-Category</option>');

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
                 });

             });

             document.addEventListener('DOMContentLoaded', function() {
                 // Add event listeners to dropdowns
                 const dropdowns = ['category-select', 'subcategory-select', 'customer-select', 'country-select',
                     'city-select', 'sort-select'
                 ];

                 dropdowns.forEach(id => {
                     const dropdown = document.getElementById(id);
                     dropdown.addEventListener('change', function() {
                         // Submit the form or reload the page with selected filters
                         const params = new URLSearchParams(window.location.search);
                         params.set(id.replace('-select', '_id'), dropdown.value);
                         window.location.search = params.toString();
                     });
                 });
             });
             $(document).ready(function() {
                 // When the country dropdown changes
                 $('#country-select').on('change', function() {
                     var countryId = $(this).val(); // Get the selected country ID

                     if (countryId) {
                         // Make AJAX request to fetch cities based on the selected country
                         $.ajax({
                             url: '/ajax-cities/' + countryId,
                             method: 'GET',
                             success: function(response) {
                                 // Clear the existing options in the city dropdown
                                 $('#city-select').empty();
                                 $('#city-select').append(
                                 '<option selected>Filter by City</option>');

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
                     } else {
                         // If no country is selected, reset the city dropdown
                         $('#city-select').empty();
                         $('#city-select').append('<option selected>Filter by City</option>');
                     }
                 });
             });
         </script>
     </body>

     </html>


     </html>
 @endsection
