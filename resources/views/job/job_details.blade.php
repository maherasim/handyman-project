@extends('landing-page.layouts.default')

@section('content')

    <body>
        <div class="container">
            <div class="row">
                <div class="col-md-8 mt-3">
                    <h2 class="heading_text">{{ $jobrequest->title }}</h2>
                    <div class="d-flex justify-content-between">
                        <p class="mb-0 text-dark"><b>
                                {{ optional($jobrequest->city)->name ?? 'N/A' }} -
                                {{ optional($jobrequest->country)->name ?? 'N/A' }}
                            </b></p>
                    </div>
                    @if (!empty($jobrequest->image))
                    <img src="{{ asset('storage/' . $jobrequest->image) }}" 
                         alt="Job Image" 
                         class="img-fluid" 
                         style="border-radius: 12px;">
                         
                    <img src="{{ asset('storage/' . $jobrequest->image) }}" 
                         style="width: 100px; border-radius: 10px;" 
                         class="mt-3" 
                         alt="Job Image">
                @else
                    <img src="{{ asset('images/post-job/ac_refresh_and_revive.png') }}" 
                         alt="Default Image" 
                         class="img-fluid" 
                         style="border-radius: 12px;">
                         
                    <img src="{{ asset('images/post-job/ac_refresh_and_revive.png') }}" 
                         style="width: 100px; border-radius: 10px;" 
                         class="mt-3" 
                         alt="Default Image">
                @endif
                


                    <div>
                        <br>

                        <b class="heading_text">About Job Request</b> <br>
                        <p> {{ strip_tags($jobrequest->description) }}</p>
                        </b>
                    </div>

                    <br>
                    <div>
                        <br>

                        <b class="heading_text">Requirnments</b> <br>
                        <p> {{ strip_tags($jobrequest->requirement) }}</p>
                        </b>
                    </div>
                </div>
                <div class="col-md-4 mt-3">
                    <h5 class="mt-4">$ {{ $jobrequest->price }} <span class="text-dark"><b>/ Hour</b></span> </h5>
                    <p class="mb-0" id="description">
                        {{ Str::words(strip_tags($jobrequest->description), 10, '...') }}
                    </p>

                    <button id="readMoreBtn" class="btn btn-link p-0" style="display: none;">Read More</button>


                    <p id="fullDescription" class="mb-0" style="display: none;">
                        {{ $jobrequest->description }}
                    </p>

                    <hr>
                    <button id="continueBtn" class="btn btn-cont text-white col-md-12"
                    style="background: #5F60BA; padding: 10px;">Continue</button>
                
                    <div class="d-flex align-items-center justify-content-center gap-3 mt-3 mb-3">
                        <a href="#">
                            <img src="https://cdn.pixabay.com/photo/2021/06/15/12/51/facebook-6338507_1280.png"
                                style="width: 30px; border-radius: 8px;" alt="">
                        </a>
                        <a href="#">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/9/95/Instagram_logo_2022.svg"
                                style="width: 30px; border-radius: 8px;" alt="">
                        </a>
                        <a href="#">
                            <img src="https://cdn.pixabay.com/photo/2015/03/10/17/30/twitter-667462_640.png"
                                style="width: 30px; border-radius: 8px;" alt="">
                        </a>
                        <a href="#">
                            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRokEYt0yyh6uNDKL8uksVLlhZ35laKNQgZ9g&s"
                                style="width: 30px; border-radius: 8px;" alt="">
                        </a>
                    </div>
                    <button class="btn btn-job col-md-12"
                        style="background: #018E27; border-radius: 50px; color: white;padding: 10px;">About Job
                        Request</button>
                    <ul class="pl-0 mt-3" style="list-style: none; font-weight: 500; ">
                        <li>
                            <a href="#" class="text-dark">
                                <b>Published at: </b>
                                {{ $jobrequest->created_at ? $jobrequest->created_at->format('Y-m-d') : 'N/A' }}
                            </a>
                        </li>

                        <li><a href="#" class="text-dark"> <b>Customer :</b>
                                {{ $jobrequest->customer->username ?? 'N/A' }}
                            </a></li>

                        <li><a href="#" class="text-dark"> <b>Location:</b> Germany
                            </a></li>
                        <li><a href="#" class="text-dark"> <b>Category: </b>{{ $jobrequest->category->name ?? 'N/A' }}
                            </a></li>
                        <li><a class="text-dark"> <b>Type:</b> <span> {{ $jobrequest->type ?? 'N/A' }}</span>
                            </a></li>
                        <li>
                            <a class="text-dark">
                                <b>Start Date:</b>
                                @dd($jobrequest->start_date);
                                {{ optional($jobrequest->start_date)->format('Y-m-d') ?? 'N/A' }}
                            </a>
                        </li>


                        <li>
                            <a class="text-dark">
                                <b>End Date:</b>
                                {{ optional($jobrequest->end_date)->format('Y-m-d') ?? 'N/A' }}
                            </a>
                        </li>

                        <li><a class="text-dark"><b>Status:</b> {{ $jobrequest->status ?? 'N/A' }}
                            </a></li>
                        <li><a class="text-dark"> <b>Total Bids:</b> {{ $totalBids }}
                            </a></li>
                        <li><a class="text-dark"> <b>Total Views:</b>{{ rand(1, 5) }} </a></li>

                    </ul>
                    <a href="{{ auth()->check() ? route('post-job-request.index') : route('login') }}"
                        class="btn btn-green"
                        style="background: #59E054; color: #fff; border: 5px solid #F0F0F0; padding: 10px 30px; border-radius: 50px; font-weight: 800; display: flex; margin: auto; text-decoration: none; text-align: center;">
                        APPLY NOW
                    </a>



                    <p class="text-dark text-center"><b>(can apply only if Status "Open")</b></p>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const description = document.getElementById('description');
                const readMoreBtn = document.getElementById('readMoreBtn');
                const fullDescription = document.getElementById('fullDescription');

                // Check if the description has more than 20 words
                if (description.textContent.length < fullDescription.textContent.length) {
                    readMoreBtn.style.display = 'inline'; // Show the "Read More" button
                }

                // When "Read More" is clicked
                readMoreBtn.addEventListener('click', function() {
                    description.style.display = 'none'; // Hide the short description
                    fullDescription.style.display = 'block'; // Show the full description
                    readMoreBtn.style.display = 'none'; // Hide the "Read More" button
                });
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const continueBtn = document.getElementById('continueBtn');
        
                continueBtn.addEventListener('click', function () {
                    window.scrollTo({
                        top: document.body.scrollHeight,
                        behavior: 'smooth' // Enables smooth scrolling
                    });
                });
            });
        </script>
        
    @endsection
