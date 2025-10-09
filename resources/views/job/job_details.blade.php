@extends('landing-page.layouts.default')

@section('content')

    <style>
        /* Simple Professional Tabs */
        .tab-btn {
            padding: 12px 20px;
            border: none;
            background: transparent;
            color: #6c757d;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
            flex: 1;
        }
        
        .tab-btn:hover {
            color: #007bff;
            background: #f8f9fa;
        }
        
        .tab-btn.active {
            color: #007bff;
            border-bottom-color: #007bff;
            font-weight: 600;
        }
        
        .tab-content {
            padding: 20px 0;
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .job-content {
            line-height: 1.6;
            color: #333;
        }
        
        .job-content p {
            margin-bottom: 1rem;
        }
        
        .job-content ul, .job-content ol {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
        }
        
        .job-content li {
            margin-bottom: 0.5rem;
        }
        
        .no-content {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
            font-style: italic;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .tab-btn {
                padding: 10px 12px;
                font-size: 13px;
            }
        }
        
        @media (max-width: 576px) {
            .tab-btn {
                padding: 8px 6px;
                font-size: 12px;
            }
        }
    </style>

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
                            <section-thumbnail-section :attachments="{{ json_encode($attachments) }}" main-fit="cover"></section-thumbnail-section>
                        </div>
                    @else
                        <img src="{{ asset('images/post-job/ac_refresh_and_revive.png') }}" 
                             alt="Default Image" 
                             class="img-fluid" 
                             style="border-radius: 12px; width: 100%; height: auto; max-height: 400px; object-fit: cover;">
                    @endif
                
                    <!-- Simple Professional Tabs -->
                    <div class="job-details-tabs mt-4">
                        <!-- Tab Navigation -->
                        <div class="tab-navigation mb-0">
                            <div class="d-flex border-bottom">
                                <button class="tab-btn active" data-tab="description">
                                    Job Description
                                </button>
                                <button class="tab-btn" data-tab="duties">
                                    Duties & Responsibilities
                                </button>
                                <button class="tab-btn" data-tab="skills">
                                    Skills & Requirements
                                </button>
                                <button class="tab-btn" data-tab="benefits">
                                    Benefits
                                </button>
                            </div>
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content-container mt-0">
                            <!-- Job Description Tab -->
                            <div class="tab-content active" id="description-content">
                                <div class="content-section">
                                    @if(!empty($jobrequest->description))
                                        <div class="job-content">
                                            {!! $jobrequest->description !!}
                                        </div>
                                    @else
                                        <div class="no-content text-muted">
                                            <i class="fas fa-info-circle me-2"></i>
                                            No job description provided.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Duties & Responsibilities Tab -->
                            <div class="tab-content" id="duties-content" style="display: none;">
                                <div class="content-section">
                                    @if(!empty($jobrequest->duties))
                                        <div class="job-content">
                                            {!! $jobrequest->duties !!}
                                        </div>
                                    @else
                                        <div class="no-content text-muted">
                                            <i class="fas fa-tasks me-2"></i>
                                            No duties and responsibilities specified.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Skills & Requirements Tab -->
                            <div class="tab-content" id="skills-content" style="display: none;">
                                <div class="content-section">
                                    @if(!empty($jobrequest->requirement))
                                        <div class="job-content">
                                            {!! $jobrequest->requirement !!}
                                        </div>
                                    @else
                                        <div class="no-content text-muted">
                                            <i class="fas fa-cogs me-2"></i>
                                            No skills and requirements specified.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Benefits Tab -->
                            <div class="tab-content" id="benefits-content" style="display: none;">
                                <div class="content-section">
                                    @if(!empty($jobrequest->benefits))
                                        <div class="job-content">
                                            {!! $jobrequest->benefits !!}
                                        </div>
                                    @else
                                        <div class="no-content text-muted">
                                            <i class="fas fa-gift me-2"></i>
                                            No benefits specified.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
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

        <!-- Tab Switching Script -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tabButtons = document.querySelectorAll('.tab-btn');
                const tabContents = document.querySelectorAll('.tab-content');

                tabButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        const targetTab = this.getAttribute('data-tab');
                        
                        // Remove active class from all buttons
                        tabButtons.forEach(btn => {
                            btn.classList.remove('active');
                        });
                        
                        // Add active class to clicked button
                        this.classList.add('active');
                        
                        // Hide all tab contents
                        tabContents.forEach(content => {
                            content.style.display = 'none';
                            content.classList.remove('active');
                        });
                        
                        // Show target tab content
                        const targetContent = document.getElementById(targetTab + '-content');
                        if (targetContent) {
                            targetContent.style.display = 'block';
                            targetContent.classList.add('active');
                        }
                    });
                });
            });
        </script>

    </body>

@endsection
