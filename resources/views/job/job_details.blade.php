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
                    @if (!empty($attachments) && count($attachments) > 0)
                        <div class="mt-3">
                            <section-thumbnail-section :attachments="{{ json_encode($attachments) }}" main-fit="contain"></section-thumbnail-section>
                        </div>
                    @else
                        <img src="{{ asset('images/post-job/ac_refresh_and_revive.png') }}" 
                             alt="Default Image" 
                             class="img-fluid" 
                             style="border-radius: 12px;">
                    @endif
                
                    <div class="mt-2">
                        <b class="heading_text">Job Description</b>
                        <p>{{ strip_tags($jobrequest->description) }}</p>
                    </div>

                    <div class="mt-4">
                        <b class="heading_text">Duties & Responsibilities</b>
                        <p>{{ strip_tags($jobrequest->duties) }}</p>
                    </div>

                    <div class="mt-4">
                        <b class="heading_text">Skills & Requirements</b>
                        <p>{{ strip_tags($jobrequest->requirement) }}</p>
                    </div>

                    <div class="mt-4">
                        <b class="heading_text">Benefits</b>
                        <p>{{ strip_tags($jobrequest->benefits) }}</p>
                    </div>
                </div>

                <div class="col-md-4 mt-3">
                    @php
                        $priceLabel = $jobrequest->price_type === 'fixed' ? 'Fixed Price' : ($jobrequest->price_type === 'daily' ? '/ Day' : '/ Hour');
                    @endphp
                    <h5 class="mt-4">$ {{ $jobrequest->price }} <span class="text-dark"><b>{{ $priceLabel }}</b></span> </h5>
                    <p class="mb-0" id="description">
                        {{ Str::words(strip_tags($jobrequest->description), 10, '...') }}
                    </p>

                    <button id="readMoreBtn" class="btn btn-link p-0" style="display: none;">Read More</button>

                    <p id="fullDescription" class="mb-0" style="display: none;">
                        {!! $jobrequest->description !!}
                    </p>

                    <hr>
                    <button id="continueBtn" class="btn btn-cont text-white col-md-12"
                            style="background: #5F60BA; padding: 10px;">Continue</button>

                    <div class="d-flex align-items-center justify-content-center gap-3 mt-3 mb-3">
                        <a href="#"><img src="https://cdn.pixabay.com/photo/2021/06/15/12/51/facebook-6338507_1280.png" style="width: 30px; border-radius: 8px;" alt=""></a>
                        <a href="#"><img src="https://upload.wikimedia.org/wikipedia/commons/9/95/Instagram_logo_2022.svg" style="width: 30px; border-radius: 8px;" alt=""></a>
                        <a href="#"><img src="https://cdn.pixabay.com/photo/2015/03/10/17/30/twitter-667462_640.png" style="width: 30px; border-radius: 8px;" alt=""></a>
                        <a href="#"><img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRokEYt0yyh6uNDKL8uksVLlhZ35laKNQgZ9g&s" style="width: 30px; border-radius: 8px;" alt=""></a>
                    </div>

                    <button class="btn btn-job col-md-12"
                            style="background: #018E27; border-radius: 50px; color: white;padding: 10px;">About Job Request</button>

                    <ul class="pl-0 mt-3" style="list-style: none; font-weight: 500;">
                        <li>
                            <a href="#" class="text-dark"><b>Published at: </b>{{ $jobrequest->created_at ? $jobrequest->created_at->format('Y-m-d') : 'N/A' }}</a>
                        </li>
                        <li><a href="#" class="text-dark"><b>Customer :</b> {{ $jobrequest->customer->username ?? 'N/A' }}</a></li>
                        <li><a href="#" class="text-dark"><b>Location:</b> {{ optional($jobrequest->city)->name ?? 'N/A' }} - {{ optional($jobrequest->country)->name ?? 'N/A' }}</a></li>
                        <li><a href="#" class="text-dark"><b>Category: </b>{{ $jobrequest->category->name ?? 'N/A' }}</a></li>
                        <li><a class="text-dark"><b>Type:</b> <span> {{ $jobrequest->type ?? 'N/A' }}</span></a></li>
                        <li><a class="text-dark"><b>Start Date:</b> {{ $jobrequest->start_date ? \Carbon\Carbon::parse($jobrequest->start_date)->format('Y-m-d') : 'N/A' }}</a></li>
                        <li><a class="text-dark"><b>End Date:</b> {{ $jobrequest->end_date ? \Carbon\Carbon::parse($jobrequest->end_date)->format('Y-m-d') : 'N/A' }}</a></li>
                        <li><a class="text-dark"><b>Status:</b> {{ $jobrequest->status ?? 'N/A' }}</a></li>
                        <li><a class="text-dark"><b>Total Bids:</b> {{ $totalBids }}</a></li>
                        <li><a class="text-dark"><b>Total Views:</b>{{ $jobrequest->total_views ?? 'N/A' }}</a></li>
                    </ul>

                    <a href="{{ auth()->check() ? route('post-job-request.index') : route('login') }}"
                       class="btn btn-green"
                       style="background: #59E054; color: #fff; border: 5px solid #F0F0F0; padding: 10px 30px; border-radius: 50px; font-weight: 800; display: flex; margin: auto; text-decoration: none; text-align: center;">
                        APPLY NOW
                    </a>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h6 class="mb-3 text-center">Customer Details</h6>
                            <div class="text-center mb-3">
                                <img src="{{ getSingleMedia($jobrequest->customer,'profile_image', null) }}" alt="photo" style="width:120px;height:120px;border-radius:50%;object-fit:cover;margin:0 auto;">
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <div><b>Full Name:</b> {{ $jobrequest->customer->display_name ?? $jobrequest->customer->username ?? 'N/A' }}</div>
                                    <div><b>Position:</b> {{ $jobrequest->customer->designation ?? 'N/A' }}</div>
                                    <div><b>Member since:</b> {{ optional($jobrequest->customer->created_at)->format('Y-m-d') ?? 'N/A' }}</div>
                                    <div><b>Languages:</b> {{ is_array($jobrequest->customer->languages ?? null) ? implode(', ', $jobrequest->customer->languages) : ($jobrequest->customer->languages ?? 'N/A') }}</div>
                                    <div><b>Location:</b> {{ optional($jobrequest->customer->city)->name ?? 'N/A' }} - {{ optional($jobrequest->customer->country)->name ?? 'N/A' }}</div>
                                    <div><b>Jobs published:</b> {{ $jobsPublishedCount ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const description = document.getElementById('description');
                const readMoreBtn = document.getElementById('readMoreBtn');
                const fullDescription = document.getElementById('fullDescription');

                if (description.textContent.length < fullDescription.textContent.length) {
                    readMoreBtn.style.display = 'inline';
                }

                readMoreBtn.addEventListener('click', function () {
                    description.style.display = 'none';
                    fullDescription.style.display = 'block';
                    readMoreBtn.style.display = 'none';
                });
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const continueBtn = document.getElementById('continueBtn');
                continueBtn.addEventListener('click', function () {
                    window.scrollTo({
                        top: document.body.scrollHeight,
                        behavior: 'smooth'
                    });
                });
            });
        </script>

    </body>

@endsection
