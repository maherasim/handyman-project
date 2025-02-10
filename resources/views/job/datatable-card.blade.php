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
     </style>
     </head>

     <body>
         <div class="container mt-5">
             <div class="d-flex justify-content-between">
                 <!-- Filter by Category -->
                 <!-- Category Filter -->
                 <select class="form-select" id="category-select" aria-label="Filter by Category">
                     <option selected>Filter by Category</option>
                     @foreach ($categories as $category)
                         <option value="{{ $category->id }}">{{ $category->name }}</option>
                     @endforeach
                 </select>

                 <!-- Sub-Category Filter -->
                 <select class="form-select" id="subcategory-select" aria-label="Filter by Sub-Category">
                     <option selected>Filter by Sub-Category</option>
                     @foreach ($subcategories as $subcategory)
                         <option value="{{ $subcategory->id }}">{{ $subcategory->name }}</option>
                     @endforeach
                 </select>

                 <!-- Customer Filter -->
                 <select class="form-select" id="customer-select" aria-label="Filter by Customer">
                     <option selected>Filter by Customer</option>
                     @foreach ($customers as $customer)
                         <option value="{{ $customer->id }}">{{ $customer->display_name }}</option>
                     @endforeach
                 </select>

                 <!-- Country Filter -->
                 <select class="form-select" id="country-select" aria-label="Filter by Country">
                     <option selected>Filter by Country</option>
                     @foreach ($countries as $country)
                         <option value="{{ $country->id }}">{{ $country->name }}</option>
                     @endforeach
                 </select>

                 <!-- City Filter -->
                 <select class="form-select" id="city-select" aria-label="Filter by City">
                     <option selected>Filter by City</option>
                 </select>


                 <!-- Sort Filter (Optional) -->
                 <select class="form-select" id="sort-select" aria-label="Sort by Price">
                     <option selected>Sort by Price</option>
                     <option value="1">Price: Low to High</option>
                     <option value="2">Price: High to Low</option>
                 </select>




             </div>
             <hr class="my-4">
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
                                     style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); width:68%; background-image: url('{{ asset('images/icon/bannerbg.jpg') }}'); background-size: cover; background-repeat: no-repeat; background-position: center; padding: 10px 20px; color: #fff; font-weight: 600; font-size: 18px; border-radius: 10px; border: 3px solid #E1DCDD;">
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
