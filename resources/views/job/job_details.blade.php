@extends('landing-page.layouts.default')

@section('content')

    <style>
        /* Professional Job Details Tab Styling */
        .job-details-tabs {
            margin-top: 2rem;
        }
        
        .tab-navigation {
            background: #f8f9fa;
            border-radius: 8px 8px 0 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .tab-btn {
            position: relative;
            z-index: 1;
            border-radius: 0 !important;
        }
        
        .tab-btn:hover {
            background: #e9ecef !important;
            color: #495057 !important;
            border-bottom-color: #007bff !important;
        }
        
        .tab-btn.active {
            background: #fff !important;
            color: #007bff !important;
            border-bottom-color: #007bff !important;
            box-shadow: 0 -2px 4px rgba(0,0,0,0.1);
        }
        
        .tab-content {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .job-content {
            line-height: 1.7;
            color: #495057;
            font-size: 15px;
        }
        
        .job-content p {
            margin-bottom: 1.2rem;
        }
        
        .job-content ul, .job-content ol {
            margin-bottom: 1.2rem;
            padding-left: 1.8rem;
        }
        
        .job-content li {
            margin-bottom: 0.6rem;
        }
        
        .job-content h1, .job-content h2, .job-content h3, .job-content h4, .job-content h5, .job-content h6 {
            color: #212529;
            margin-bottom: 1rem;
            margin-top: 1.5rem;
        }
        
        .job-content h1:first-child, .job-content h2:first-child, .job-content h3:first-child {
            margin-top: 0;
        }
        
        .no-content {
            text-align: center;
            padding: 3rem 2rem;
            font-style: italic;
            color: #6c757d;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
        }
        
        .no-content i {
            font-size: 2rem;
            margin-bottom: 1rem;
            display: block;
            color: #adb5bd;
        }
        
        /* Detail Items Styling */
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            flex: 0 0 45%;
            font-size: 13px;
            color: #6c757d;
            font-weight: 500;
        }
        
        .detail-value {
            flex: 1;
            font-size: 13px;
            color: #212529;
            text-align: right;
            font-weight: 400;
        }
        
        .badge {
            font-size: 11px;
            padding: 4px 8px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .tab-btn {
                font-size: 12px !important;
                padding: 10px 6px !important;
            }
            
            .tab-content-container {
                padding: 16px !important;
            }
            
            .job-content {
                font-size: 14px;
            }
            
            .detail-label {
                font-size: 12px;
            }
            
            .detail-value {
                font-size: 12px;
            }
        }
        
        @media (max-width: 576px) {
            .tab-navigation .col-6 {
                flex: 0 0 50%;
                max-width: 50%;
            }
            
            .tab-btn {
                font-size: 11px !important;
                padding: 8px 4px !important;
            }
            
            .tab-content-container {
                padding: 12px !important;
            }
            
            .job-content {
                font-size: 13px;
            }
            
            .detail-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .detail-label {
                margin-bottom: 2px;
            }
            
            .detail-value {
                text-align: left;
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
                
                    <!-- Tabbed Job Details Section -->
                    <div class="job-details-tabs mt-4">
                        <!-- Tab Navigation -->
                        <div class="tab-navigation mb-4">
                            <div class="row g-0">
                                <div class="col-3">
                                    <button class="tab-btn active" data-tab="description" style="
                                        width: 100%;
                                        padding: 14px 12px;
                                        border: none;
                                        border-bottom: 3px solid #007bff;
                                        background: #fff;
                                        color: #007bff;
                                        font-weight: 600;
                                        font-size: 14px;
                                        transition: all 0.3s ease;
                                        cursor: pointer;
                                        position: relative;
                                    ">
                                        Job Description
                                    </button>
                                </div>
                                <div class="col-3">
                                    <button class="tab-btn" data-tab="duties" style="
                                        width: 100%;
                                        padding: 14px 12px;
                                        border: none;
                                        border-bottom: 3px solid transparent;
                                        background: #f8f9fa;
                                        color: #6c757d;
                                        font-weight: 500;
                                        font-size: 14px;
                                        transition: all 0.3s ease;
                                        cursor: pointer;
                                        position: relative;
                                    ">
                                        Duties & Responsibilities
                                    </button>
                                </div>
                                <div class="col-3">
                                    <button class="tab-btn" data-tab="skills" style="
                                        width: 100%;
                                        padding: 14px 12px;
                                        border: none;
                                        border-bottom: 3px solid transparent;
                                        background: #f8f9fa;
                                        color: #6c757d;
                                        font-weight: 500;
                                        font-size: 14px;
                                        transition: all 0.3s ease;
                                        cursor: pointer;
                                        position: relative;
                                    ">
                                        Skills & Requirements
                                    </button>
                                </div>
                                <div class="col-3">
                                    <button class="tab-btn" data-tab="benefits" style="
                                        width: 100%;
                                        padding: 14px 12px;
                                        border: none;
                                        border-bottom: 3px solid transparent;
                                        background: #f8f9fa;
                                        color: #6c757d;
                                        font-weight: 500;
                                        font-size: 14px;
                                        transition: all 0.3s ease;
                                        cursor: pointer;
                                        position: relative;
                                    ">
                                        Benefits
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content-container" style="
                            border: 1px solid #e9ecef;
                            border-top: none;
                            border-radius: 0 0 8px 8px;
                            padding: 24px;
                            background: #fff;
                            min-height: 200px;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                        ">
                            <!-- Job Description Tab -->
                            <div class="tab-content active" id="description-content">
                                <div class="content-section">
                                    @if(!empty($jobrequest->description))
                                        <div class="job-content" id="fullDescriptionSection">
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
                    <!-- Job Price Section -->
                    <div class="bg-light p-4 rounded-3 mb-4">
                        @php
                            $priceLabel = $jobrequest->price_type === 'fixed' ? 'Fixed Price' : ($jobrequest->price_type === 'daily' ? '/ Day' : '/ Hour');
                        @endphp
                        <h4 class="text-primary mb-2">€ {{ number_format($jobrequest->price, 2) }} <span class="text-dark"><b>{{ $priceLabel }}</b></span></h4>
                        <p class="mb-0 text-muted" id="description">
                            {{ Str::words(strip_tags($jobrequest->descraiption), 10, '...') }}
                        </p>
                        
                        <button id="readMoreBtn" class="btn btn-link p-0 mt-2" style="display: none;">Read More</button>
                        <p id="fullDescription" class="mb-0 mt-2" style="display: none;">
                            {!! $jobrequest->description !!}
                        </p>
                    </div>

                    <!-- Continue Button -->
                    <button id="continueBtn" class="btn btn-primary w-100 mb-4" style="background: #5F60BA; padding: 12px;">
                        Continue as
                    </button>

                    <!-- Social Sharing -->
                    <div class="d-flex align-items-center justify-content-center gap-3 mb-4">
                        <a href="#" class="text-decoration-none">
                            <img src="https://static.vecteezy.com/system/resources/previews/016/716/447/non_2x/facebook-icon-free-png.png" 
                                 style="width: 30px; border-radius: 8px;" alt="Facebook">
                        </a>
                        <a href="#" class="text-decoration-none">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/9/95/Instagram_logo_2022.svg" 
                                 style="width: 30px; border-radius: 8px;" alt="Instagram">
                        </a>
                        <a href="#" class="text-decoration-none">
                            <img src="https://cdn.pixabay.com/photo/2015/03/10/17/30/twitter-667462_640.png" 
                                 style="width: 30px; border-radius: 8px;" alt="Twitter">
                        </a>
                        <a href="#" class="text-decoration-none">
                            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRokEYt0yyh6uNDKL8uksVLlhZ35laKNQgZ9g&s" 
                                 style="width: 30px; border-radius: 8px;" alt="LinkedIn">
                        </a>
                    </div>

                    <!-- Tabbed Job Details Section -->
                    <div class="bg-light rounded-3">
                        <!-- Tab Navigation -->
                        <div class="tab-navigation">
                            <div class="row g-0">
                                <div class="col-6">
                                    <button class="tab-btn active" data-tab="job-details" style="
                                        width: 100%;
                                        padding: 12px 8px;
                                        border: none;
                                        border-bottom: 3px solid #dc3545;
                                        background: #fff;
                                        color: #dc3545;
                                        font-weight: 600;
                                        font-size: 13px;
                                        transition: all 0.3s ease;
                                        cursor: pointer;
                                    ">
                                        Job Details
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="tab-btn" data-tab="customer-details" style="
                                        width: 100%;
                                        padding: 12px 8px;
                                        border: none;
                                        border-bottom: 3px solid transparent;
                                        background: #f8f9fa;
                                        color: #6c757d;
                                        font-weight: 500;
                                        font-size: 13px;
                                        transition: all 0.3s ease;
                                        cursor: pointer;
                                    ">
                                        Customer Details
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content-container" style="padding: 20px;">
                            <!-- Job Details Tab -->
                            <div class="tab-content active" id="job-details-content">
                                <div class="job-details-list">
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Published at:</b></span>
                                        <span class="detail-value">{{ $jobrequest->created_at ? $jobrequest->created_at->format('M d, Y') : 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Location:</b></span>
                                        <span class="detail-value">{{ optional($jobrequest->city)->name ?? 'N/A' }} - {{ optional($jobrequest->country)->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Category:</b></span>
                                        <span class="detail-value">{{ $jobrequest->category->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total Budget:</b></span>
                                        <span class="detail-value">€{{ number_format($jobrequest->total_budget ?? $jobrequest->price, 2) }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Start Date:</b></span>
                                        <span class="detail-value">{{ $jobrequest->start_date ? \Carbon\Carbon::parse($jobrequest->start_date)->format('Y-m-d') : 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>End Date:</b></span>
                                        <span class="detail-value">{{ $jobrequest->end_date ? \Carbon\Carbon::parse($jobrequest->end_date)->format('Y-m-d') : 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total Hours:</b></span>
                                        <span class="detail-value">{{ $jobrequest->total_hours ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total Days:</b></span>
                                        <span class="detail-value">{{ $jobrequest->total_days ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Type:</b></span>
                                        <span class="detail-value">{{ ucfirst($jobrequest->type ?? 'N/A') }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Remote Level:</b></span>
                                        <span class="detail-value">{{ ucfirst($jobrequest->remote_work_level ?? 'N/A') }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Career Level:</b></span>
                                        <span class="detail-value">{{ ucfirst($jobrequest->career_level ?? 'N/A') }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Travel Required:</b></span>
                                        <span class="detail-value">{{ $jobrequest->travel_required ? 'Yes' : 'No' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Education Level:</b></span>
                                        <span class="detail-value">{{ ucfirst($jobrequest->education_level ?? 'N/A') }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Status:</b></span>
                                        <span class="detail-value">
                                            <span class="badge bg-{{ $jobrequest->status === 'active' ? 'success' : ($jobrequest->status === 'completed' ? 'info' : 'warning') }}">
                                                {{ ucfirst($jobrequest->status ?? 'N/A') }}
                                            </span>
                                        </span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total Bids:</b></span>
                                        <span class="detail-value">{{ $totalBids }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total Views:</b></span>
                                        <span class="detail-value">{{ $jobrequest->total_views ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Details Tab -->
                            <div class="tab-content" id="customer-details-content" style="display: none;">
                                <div class="text-center mb-3">
                                    <img src="{{ getSingleMedia($jobrequest->customer,'profile_image', asset('images/default.png')) }}" 
                                         alt="Customer Photo" 
                                         style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin: 0 auto;">
                                </div>
                                <div class="customer-details-list">
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Full Name:</b></span>
                                        <span class="detail-value">{{ $jobrequest->customer->display_name ?? $jobrequest->customer->username ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Position:</b></span>
                                        <span class="detail-value">{{ $jobrequest->customer->designation ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Member Since:</b></span>
                                        <span class="detail-value">{{ optional($jobrequest->customer->created_at)->format('M d, Y') ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Languages:</b></span>
                                        <span class="detail-value">{{ is_array($jobrequest->customer->languages ?? null) ? implode(', ', $jobrequest->customer->languages) : ($jobrequest->customer->languages ?? 'N/A') }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Location:</b></span>
                                        <span class="detail-value">{{ optional($jobrequest->customer->city)->name ?? 'N/A' }} - {{ optional($jobrequest->customer->country)->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Jobs Published:</b></span>
                                        <span class="detail-value">{{ $jobsPublishedCount ?? 0 }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Location:</b></span>
                                        <span class="detail-value">{{ $jobrequest->city->name ?? 'N/A' }}-{{ $jobrequest->country->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Category:</b></span>
                                        <span class="detail-value">{{ $jobrequest->category->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total budget:</b></span>
                                        <span class="detail-value">€{{ number_format($jobrequest->total_budget ?? $jobrequest->price, 2) }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Start date:</b></span>
                                        <span class="detail-value">{{ $jobrequest->start_date ? \Carbon\Carbon::parse($jobrequest->start_date)->format('Y-m-d') : 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>End date:</b></span>
                                        <span class="detail-value">{{ $jobrequest->end_date ? \Carbon\Carbon::parse($jobrequest->end_date)->format('Y-m-d') : 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total hours:</b></span>
                                        <span class="detail-value">{{ $jobrequest->total_hours ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total days:</b></span>
                                        <span class="detail-value">{{ $jobrequest->total_days ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Type:</b></span>
                                        <span class="detail-value">{{ $jobrequest->type ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Remote level:</b></span>
                                        <span class="detail-value">{{ $jobrequest->remote_work_level ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Career level:</b></span>
                                        <span class="detail-value">{{ $jobrequest->career_level ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Travel required:</b></span>
                                        <span class="detail-value">{{ $jobrequest->travel_required ? 'Yes' : 'No' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Education level:</b></span>
                                        <span class="detail-value">{{ $jobrequest->education_level ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Status:</b></span>
                                        <span class="detail-value">
                                            <span class="badge bg-{{ $jobrequest->status === 'active' ? 'success' : ($jobrequest->status === 'completed' ? 'info' : 'warning') }}">
                                                {{ ucfirst($jobrequest->status ?? 'N/A') }}
                                            </span>
                                        </span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total bids:</b></span>
                                        <span class="detail-value">{{ $totalBids }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total views:</b></span>
                                        <span class="detail-value">{{ $jobrequest->total_views ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Apply Now Button -->
                    <div class="text-center mt-4">
                        <a href="{{ auth()->check() ? route('post-job-request.index') : route('login') }}"
                           class="btn btn-success btn-lg"
                           style="background: #59E054; color: #fff; border: 5px solid #F0F0F0; padding: 12px 30px; border-radius: 50px; font-weight: 800; text-decoration: none;">
                            APPLY NOW
                        </a>
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
            document.addEventListener('DOMContentLoaded', function() {
                const continueBtn = document.getElementById('continueBtn');
                const descriptionTabBtn = document.querySelector('[data-tab="description"]');
                const descriptionSection = document.getElementById('description-content');
                if (continueBtn && descriptionTabBtn && descriptionSection) {
                    continueBtn.addEventListener('click', function() {
                        // Activate Description tab
                        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                        descriptionTabBtn.classList.add('active');
                        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
                        descriptionSection.style.display = 'block';
                        descriptionSection.classList.add('active');
                        // Smooth scroll to description tab
                        descriptionSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                }
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
                // Job Details Tabs (Description, Duties, Skills, Benefits)
                const jobDetailTabButtons = document.querySelectorAll('.job-details-tabs .tab-btn');
                const jobDetailTabContents = document.querySelectorAll('.job-details-tabs .tab-content');

                jobDetailTabButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        const targetTab = this.getAttribute('data-tab');
                        
                        // Remove active class from all job detail buttons
                        jobDetailTabButtons.forEach(btn => {
                            btn.classList.remove('active');
                            btn.style.border = 'none';
                            btn.style.borderBottom = '3px solid transparent';
                            btn.style.background = '#f8f9fa';
                            btn.style.color = '#6c757d';
                            btn.style.fontWeight = '500';
                        });
                        
                        // Add active class to clicked button
                        this.classList.add('active');
                        this.style.border = 'none';
                        this.style.borderBottom = '3px solid #007bff';
                        this.style.background = '#fff';
                        this.style.color = '#007bff';
                        this.style.fontWeight = '600';
                        
                        // Hide all job detail tab contents
                        jobDetailTabContents.forEach(content => {
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

                // Customer Details Tabs (Job Details, Customer Details)
                const customerDetailTabButtons = document.querySelectorAll('.bg-light .tab-btn');
                const customerDetailTabContents = document.querySelectorAll('.bg-light .tab-content');

                customerDetailTabButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        const targetTab = this.getAttribute('data-tab');
                        
                        // Remove active class from all customer detail buttons
                        customerDetailTabButtons.forEach(btn => {
                            btn.classList.remove('active');
                            btn.style.border = 'none';
                            btn.style.borderBottom = '3px solid transparent';
                            btn.style.background = '#f8f9fa';
                            btn.style.color = '#6c757d';
                            btn.style.fontWeight = '500';
                        });
                        
                        // Add active class to clicked button
                        this.classList.add('active');
                        this.style.border = 'none';
                        this.style.borderBottom = '3px solid #dc3545';
                        this.style.background = '#fff';
                        this.style.color = '#dc3545';
                        this.style.fontWeight = '600';
                        
                        // Hide all customer detail tab contents
                        customerDetailTabContents.forEach(content => {
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
