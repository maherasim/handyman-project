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
                     @foreach ($jobrequest as $jobRequest)
                         <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                             <a href="{{ route('job.details', $jobRequest->id) }}" class="text-decoration-none">
                                 <div class="card mt-5 p-3" style="background: #FAF9FF;">
                                     <div class="card-imgd">
                                         <img class="card-img-top"
                                             src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTpIfGmXhZx9-emF4uqhWXj-YgTNxlMz692LXcfGxC0TyZ8sXMilKktiOwWwzSsw2R4aG8&usqp=CAU"
                                             alt="Card image cap">
                                         <div class="img-icon">
                                             @if (auth()->check() && auth()->user()->hasRole('user'))
                                                 @if ($favouriteService->isEmpty())
                                                     <form method="POST" id="favoriteForm"
                                                         action="">
                                                         @csrf
                                                         <input type="hidden" name="jobrequest_id"
                                                             value="{{ $jobRequest->id }}">
                                                         @if (auth()->user())
                                                             <input type="hidden" name="user_id"
                                                                 value="{{ Auth::user()->id }}">
                                                         @endif
                                                         <button type="submit" class="btn-link"
                                                             style="background: transparent; border: none; cursor: pointer;">
                                                             <i class='bx bx-heart'
                                                                 style="position: absolute; top: 0; right: 0; transform: translate(-32px, 30px); background: #fff; padding: 7px; color: #8384AE; border-radius: 50px;"></i>
                                                         </button>
                                                     </form>
                                                 @else
                                                     <form method="POST" id="favoriteForm"
                                                         action="{{ route('favorite.destroy', $favouriteService->id) }}">
                                                         @csrf
                                                         @method('DELETE')
                                                         <input type="hidden" name="jobrequest_id"
                                                             value="{{ $jobRequest->id }}">
                                                         @if (auth()->user())
                                                             <input type="hidden" name="user_id"
                                                                 value="{{ Auth::user()->id }}">
                                                         @endif
                                                         <button type="submit" class="btn-link"
                                                             style="background: transparent; border: none; cursor: pointer;">
                                                             <i class='bx bx-heart'
                                                                 style="position: absolute; top: 0; right: 0; transform: translate(-32px, 30px); background: #fff; padding: 7px; color: #ff0000; border-radius: 50px;"></i>
                                                         </button>
                                                     </form>
                                                 @endif
                                             @else
                                                 <form method="GET" action="{{ route('user.login') }}">
                                                     <button type="submit" class="btn-link"
                                                         style="background: transparent; border: none; cursor: pointer;">
                                                         <i class='bx bx-heart'
                                                             style="position: absolute; top: 0; right: 0; transform: translate(-32px, 30px); background: #fff; padding: 7px; color: #8384AE; border-radius: 50px;"></i>
                                                     </button>
                                                 </form>
                                             @endif
                                         </div>
                                     </div>
                                     <button class="btn btn-fix col-md-8 d-flex m-auto justify-content-center"
                                         style="background: #a52828; color: #fff; font-weight: 600; font-size: 18px; border-radius: 15px; border: 17px solid #E1DCDD; margin-top: -40px !important;">€
                                         {{ $jobRequest->price }} </button>
                                     <div class="card-body pl-0">
                                         <div class="d-flex align-items-center justify-content-between flex-wrap">
                                            <h5 class="card-title mb-0" style="font-weight: 300;">
                                                @foreach (explode(' ', $jobRequest->title) as $word)
                                                    {{ \Illuminate\Support\Str::limit($word, 4) }} 
                                                @endforeach
                                            </h5>
                                            
                                             <div class="d-flex" style="gap: 5px;">
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
                                         <h5 class="mb-0" style="font-weight: 400;">
                                             @if ($jobRequest->city)
                                                 {{ \Illuminate\Support\Str::limit($jobRequest->city->name, 5) }} -
                                             @endif
                                             @if ($jobRequest->country)
                                                 {{ \Illuminate\Support\Str::limit($jobRequest->country->name, 5) }}
                                             @endif
                                         </h5>

                                         <h5 class="mb-0" style="font-weight: 600;">Published at:</h5>
                                         <div class="d-flex align-items-center" style="gap: 10px;">
                                             <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQnnM0ib-pYCZg4DbbB_T5_mfxpqrDHYXFLy208bjvHjIM5q1FF4lzLvNFp2qZ5Eo11orA&usqp=CAU"
                                                 class="img-fluid" style="width: 35px;" alt="">
                                             <p class="mb-0" style="color: #8081dc;">
                                                 @if ($jobRequest->provider)
                                                     {{ $jobRequest->provider->username }}
                                                 @endif
                                             </p>
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
