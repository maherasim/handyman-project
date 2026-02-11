@extends('landing-page.layouts.default')

@section('before_head')
    @php
        $shareTitle = $jobrequest->title;
        $shareDescription = trim(Str::limit(strip_tags($jobrequest->description ?? ''), 150));
        $shareUrl = route('job.details', $jobrequest->id);
        $shareImage = !empty($attachments) && count($attachments) > 0 ? $attachments[0] : asset('images/post-job/ac_refresh_and_revive.png');
        $priceType = $jobrequest->price_type ?: 'fixed';
        $locationText = trim((optional($jobrequest->city)->name ?? 'City') . ', ' . (optional($jobrequest->country)->name ?? 'Country'));
        $priceFormatted = getPriceFormat($jobrequest->price);
        $enrichedTitle = $shareTitle . ' • ' . $priceFormatted . ' • ' . ucfirst($priceType) . ' • ' . $locationText;
    @endphp
    <meta property="og:type" content="article" />
    <meta property="og:title" content="{{ $enrichedTitle }}" />
    <meta property="og:description" content="{{ $shareDescription }}" />
    <meta property="og:url" content="{{ $shareUrl }}" />
    <meta property="og:image" content="{{ $shareImage }}" />
    <meta property="og:image:secure_url" content="{{ $shareImage }}" />
    <meta property="og:site_name" content="{{ config('app.name') }}" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:image:alt" content="{{ $shareTitle }}" />
    <meta name="description" content="{{ $enrichedTitle }} — {{ $shareDescription }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $enrichedTitle }}" />
    <meta name="twitter:description" content="{{ $shareDescription }}" />
    <meta name="twitter:image" content="{{ $shareImage }}" />
@endsection

@section('after_head')
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

        /* Client-attractive job CTA card */
        .job-cta-card {
            background: linear-gradient(145deg, #5F60BA 0%, #4a4b9e 50%, #3d3e85 100%);
            border-radius: 16px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(95, 96, 186, 0.35), 0 4px 12px rgba(0,0,0,0.08);
            border: none;
            position: relative;
        }
        .job-cta-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.6;
            pointer-events: none;
        }
        .job-cta-card .cta-inner { position: relative; z-index: 1; padding: 1.5rem 1.35rem; }
        .job-cta-card .cta-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,0.2); color: #fff; font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.05em; padding: 6px 12px; border-radius: 20px; margin-bottom: 1rem;
        }
        .job-cta-card .cta-headline { color: #fff; font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; line-height: 1.35; text-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .job-cta-card .cta-stats { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 1.1rem; }
        .job-cta-card .cta-stat {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,0.18); color: #fff; font-size: 12px; font-weight: 500;
            padding: 8px 12px; border-radius: 10px; backdrop-filter: blur(6px);
        }
        .job-cta-card .cta-stat i { opacity: 0.95; font-size: 13px; }
        .job-cta-card .cta-trust { color: rgba(255,255,255,0.88); font-size: 12px; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .job-cta-card .cta-trust span { display: inline-flex; align-items: center; gap: 4px; }
        .job-cta-card .cta-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
            padding: 14px 20px; background: #fff; color: #5F60BA; font-weight: 700; font-size: 15px;
            border-radius: 12px; text-decoration: none; border: none; box-shadow: 0 4px 14px rgba(0,0,0,0.15);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .job-cta-card .cta-btn:hover { color: #4a4b9e; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); }
        .job-cta-card .cta-btn:active { transform: translateY(0); }
        
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
@endsection

@section('content')
    @php
        // Format label-like values (e.g. high_school → High School, onsite → Onsite)
        if (!function_exists('formatJobDetailLabel')) {
            function formatJobDetailLabel($value) {
                if ($value === null || $value === '') return 'N/A';
                $v = (string) $value;
                if (strtolower(trim($v)) === 'n/a') return 'N/A';
                return ucwords(str_replace('_', ' ', strtolower($v)));
            }
        }
    @endphp

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

                    @if (strtolower((string)($jobrequest->status ?? '')) !== 'requested')
                        <div class="text-center mt-4">
                            @php $statusLabel = ucfirst((string)($jobrequest->status ?? 'unknown')); @endphp
                            <div class="alert alert-info d-inline-block text-start" role="alert" style="max-width: 640px; border-radius: 12px;">
                                This job is currently <strong>Reserved</strong>. New applications are not being accepted at this time.
                            </div>
                        </div>
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
                        <h4 class="text-primary mb-2">{{ getPriceFormat($jobrequest->price) }} <span class="text-dark"><b>{{ $priceLabel }}</b></span></h4>
                        <p class="mb-0 text-muted" id="description">
                            {{ Str::words(strip_tags($jobrequest->description), 10, '...') }}
                        </p>
                        
                        <button id="readMoreBtn" class="btn btn-link p-0 mt-2" style="display: none;">Read More</button>
                        <p id="fullDescription" class="mb-0 mt-2" style="display: none;">
                            {{ Str::words(strip_tags($jobrequest->description), 10, '...') }}
                        </p>
                    </div>

                    <!-- Client-attractive CTA card -->
                    @php
                        $priceLabelShort = $jobrequest->price_type === 'fixed' ? 'Fixed' : ($jobrequest->price_type === 'daily' ? '/day' : '/hr');
                    @endphp
                    <div class="job-cta-card mb-4">
                        <div class="cta-inner">
                            <div class="cta-badge">
                                <i class="fas fa-star"></i>
                                <span>Great opportunity</span>
                            </div>
                            <h3 class="cta-headline mb-0">
                                Ready to win this project?<br>
                                <span style="font-size: 0.92em; opacity: 0.95;">Sign in and place your bid in minutes.</span>
                            </h3>
                            <div class="cta-stats">
                                <span class="cta-stat">
                                    <i class="fas fa-map-marker-alt"></i>
                                    {{ optional($jobrequest->city)->name ?? '—' }}, {{ optional($jobrequest->country)->name ?? '—' }}
                                </span>
                                <span class="cta-stat">
                                    <i class="fas fa-wallet"></i>
                                    {{ getPriceFormat($jobrequest->price) }} {{ $priceLabelShort }}
                                </span>
                                @if($jobrequest->start_date)
                                <span class="cta-stat">
                                    <i class="fas fa-calendar-check"></i>
                                    {{ \Carbon\Carbon::parse($jobrequest->start_date)->format('M j') }} – {{ $jobrequest->end_date ? \Carbon\Carbon::parse($jobrequest->end_date)->format('M j') : '—' }}
                                </span>
                                @endif
                                <span class="cta-stat">
                                    <i class="fas fa-briefcase"></i>
                                    {{ formatJobDetailLabel($jobrequest->type ?? null) }}
                                </span>
                            </div>
                            <div class="cta-trust">
                                <span><i class="fas fa-shield-alt"></i> Secure</span>
                                <span><i class="fas fa-comments"></i> Direct contact</span>
                                <span><i class="fas fa-paper-plane"></i> Easy apply</span>
                            </div>
                            <div class="cta-btn-wrap">
                                <a href="{{ route('login') }}" class="cta-btn">
                                    <i class="fas fa-rocket"></i>
                                    Apply now – Place your bid
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Social Sharing -->
                    <div class="d-flex align-items-center justify-content-center gap-3 mb-4 social-icons">
                        <span role="button" tabindex="0" class="social-link share-link" data-platform="facebook" data-share-url="{{ route('job.details', $jobrequest->id) }}?v={{ optional($jobrequest->updated_at)->timestamp ?? time() }}" data-quote="{{ $jobrequest->title }} • {{ getPriceFormat($jobrequest->price) }} • {{ ucfirst($jobrequest->price_type ?? 'fixed') }} • {{ data_get($jobrequest,'city.name','City') }}, {{ data_get($jobrequest,'country.name','Country') }}" onclick="return window.__shareClickHandler(event, this);">
                            <img src="https://static.vecteezy.com/system/resources/previews/016/716/447/non_2x/facebook-icon-free-png.png" 
                                 style="width: 30px; border-radius: 8px;" alt="Facebook">
                        </span>
                        <span role="button" tabindex="0" class="social-link share-link" data-platform="instagram" data-image-url="{{ !empty($attachments) && count($attachments) > 0 ? $attachments[0] : asset('images/post-job/ac_refresh_and_revive.png') }}" data-quote="{{ $jobrequest->title }} • {{ getPriceFormat($jobrequest->price) }} • {{ ucfirst($jobrequest->price_type ?? 'fixed') }} • {{ data_get($jobrequest,'city.name','City') }}, {{ data_get($jobrequest,'country.name','Country') }} — {{ route('job.details', $jobrequest->id) }}" onclick="return window.__shareClickHandler(event, this);">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/9/95/Instagram_logo_2022.svg" 
                                 style="width: 30px; border-radius: 8px;" alt="Instagram">
                        </span>
                        <span role="button" tabindex="0" class="social-link share-link" data-platform="twitter" data-share-url="{{ route('job.details', $jobrequest->id) }}?v={{ optional($jobrequest->updated_at)->timestamp ?? time() }}" data-text="{{ $jobrequest->title }} • {{ getPriceFormat($jobrequest->price) }} • {{ ucfirst($jobrequest->price_type ?? 'fixed') }} • {{ data_get($jobrequest,'city.name','City') }}, {{ data_get($jobrequest,'country.name','Country') }}" onclick="return window.__shareClickHandler(event, this);">
                            <img src="https://cdn.pixabay.com/photo/2015/03/10/17/30/twitter-667462_640.png" 
                                 style="width: 30px; border-radius: 8px;" alt="Twitter">
                        </span>
                        <span role="button" tabindex="0" class="social-link share-link" data-platform="linkedin" data-share-url="{{ route('job.details', $jobrequest->id) }}?v={{ optional($jobrequest->updated_at)->timestamp ?? time() }}" onclick="return window.__shareClickHandler(event, this);">
                            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRokEYt0yyh6uNDKL8uksVLlhZ35laKNQgZ9g&s" 
                                 style="width: 30px; border-radius: 8px;" alt="LinkedIn">
                        </span>
                    </div>

                    <script>
                        if (!window.__shareClickHandler) {
                            window.__shareClickHandler = function(e, el) {
                                try { e.preventDefault(); e.stopPropagation(); } catch (_) {}

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
                                    var quoteText = el.getAttribute('data-quote') || '';
                                    if (navigator.share) {
                                        try {
                                            navigator.share({ text: quoteText, url: shareUrl || window.location.href })
                                                .catch(function() {});
                                        } catch (_) {
                                            openPopup('https://www.instagram.com/');
                                        }
                                    } else {
                                        openPopup('https://www.instagram.com/');
                                    }
                                }

                                return false;
                            };
                        }
                    </script>

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
                                        <span class="detail-value"> {{ $jobrequest->created_at ? $jobrequest->created_at->format('M d, Y') : 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Location:</b></span>
                                        <span class="detail-value"> {{ optional($jobrequest->city)->name ?? 'N/A' }} - {{ optional($jobrequest->country)->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Category:</b></span>
                                        <span class="detail-value"> {{ $jobrequest->category->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total Budget:</b></span>
                                        <span class="detail-value"> {{ getPriceFormat($jobrequest->total_budget ?? $jobrequest->price) }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Start Date:</b></span>
                                        <span class="detail-value"> {{ $jobrequest->start_date ? \Carbon\Carbon::parse($jobrequest->start_date)->format('Y-m-d') : 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>End Date:</b></span>
                                        <span class="detail-value"> {{ $jobrequest->end_date ? \Carbon\Carbon::parse($jobrequest->end_date)->format('Y-m-d') : 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total Hours:</b></span>
                                        <span class="detail-value"> {{ $jobrequest->total_hours ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total Days:</b></span>
                                        <span class="detail-value"> {{ $jobrequest->total_days ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Type:</b></span>
                                        <span class="detail-value"> {{ formatJobDetailLabel($jobrequest->type ?? null) }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Remote Level:</b></span>
                                        @php
                                            $remoteLevelRaw = $jobrequest->remote_work_level ?? null;
                                            $remoteLevelPercent = $remoteLevelRaw ? (int) preg_replace('/\D+/', '', $remoteLevelRaw) : null;
                                            $remoteLevelDisplay = is_null($remoteLevelPercent) ? 'N/A' : ($remoteLevelPercent === 100 ? 'Remote 100%' : "{$remoteLevelPercent}% Remote");
                                        @endphp
                                        <span class="detail-value"> {{ $remoteLevelDisplay }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Career Level:</b></span>
                                        <span class="detail-value"> {{ formatJobDetailLabel($jobrequest->career_level ?? null) }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Travel Required:</b></span>
                                        <span class="detail-value"> {{ $jobrequest->travel_required ? 'Yes' : 'No' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Education Level:</b></span>
                                        <span class="detail-value"> {{ formatJobDetailLabel($jobrequest->education_level ?? null) }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Status:</b></span>
                                        <span class="detail-value">
                                            @php
                                                $statusKey = strtolower((string)($jobrequest->status ?? ''));
                                                $statusDisplay = in_array($statusKey, ['confirm_done', 'completed']) ? 'Completed' : formatJobDetailLabel($jobrequest->status ?? null);
                                                $statusBg = in_array($statusKey, ['confirm_done', 'completed']) ? 'success' : ($jobrequest->status === 'active' ? 'success' : 'warning');
                                            @endphp
                                            <span class="badge bg-{{ $statusBg }} text-dark">{{ $statusDisplay }}</span>
                                        </span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total Bids:</b></span>
                                        <span class="detail-value"> {{ $totalBids }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total Views:</b></span>
                                        <span class="detail-value"> {{ $jobrequest->total_views ?? 0 }}</span>
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
                                        <span class="detail-value"> {{ $jobrequest->customer->display_name ?? $jobrequest->customer->username ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Position:</b></span>
                                        <span class="detail-value"> {{ $jobrequest->customer->designation ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Member Since:</b></span>
                                        <span class="detail-value"> {{ optional($jobrequest->customer->created_at)->format('M d, Y') ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Languages:</b></span>
                                        <span class="detail-value"> {{ is_array($jobrequest->customer->languages ?? null) ? implode(', ', $jobrequest->customer->languages) : ($jobrequest->customer->languages ?? 'N/A') }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Location:</b></span>
                                        <span class="detail-value"> {{ optional($jobrequest->customer->city)->name ?? 'N/A' }} - {{ optional($jobrequest->customer->country)->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Jobs Published:</b></span>
                                        <span class="detail-value"> {{ $jobsPublishedCount ?? 0 }}</span>
                                    </div>
                                    {{-- <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Location:</b></span>
                                        <span class="detail-value"> {{ $jobrequest->city->name ?? 'N/A' }}-{{ $jobrequest->country->name ?? 'N/A' }}</span>
                                    </div> --}}
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Category:</b></span>
                                        <span class="detail-value"> {{ $jobrequest->category->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total budget:</b></span>
                                        <span class="detail-value"> {{ getPriceFormat($jobrequest->total_budget ?? $jobrequest->price) }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Start date:</b></span>
                                        <span class="detail-value"> {{ $jobrequest->start_date ? \Carbon\Carbon::parse($jobrequest->start_date)->format('Y-m-d') : 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>End date:</b></span>
                                        <span class="detail-value"> {{ $jobrequest->end_date ? \Carbon\Carbon::parse($jobrequest->end_date)->format('Y-m-d') : 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total hours:</b></span>
                                        <span class="detail-value"> {{ $jobrequest->total_hours ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total days:</b></span>
                                        <span class="detail-value"> {{ $jobrequest->total_days ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Type:</b></span>
                                        <span class="detail-value"> {{ formatJobDetailLabel($jobrequest->type ?? null) }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Remote level:</b></span>
                                        @php
                                            $remoteLevelRaw = $jobrequest->remote_work_level ?? null;
                                            $remoteLevelPercent = $remoteLevelRaw ? (int) preg_replace('/\D+/', '', $remoteLevelRaw) : null;
                                            $remoteLevelDisplay = is_null($remoteLevelPercent) ? 'N/A' : ($remoteLevelPercent === 100 ? 'Remote 100%' : "{$remoteLevelPercent}% Remote");
                                        @endphp
                                        <span class="detail-value"> {{ $remoteLevelDisplay }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Career level:</b></span>
                                        <span class="detail-value"> {{ formatJobDetailLabel($jobrequest->career_level ?? null) }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Travel required:</b></span>
                                        <span class="detail-value"> {{ $jobrequest->travel_required ? 'Yes' : 'No' }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Education level:</b></span>
                                        <span class="detail-value"> {{ formatJobDetailLabel($jobrequest->education_level ?? null) }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Status:</b></span>
                                        <span class="detail-value">
                                            @php
                                                $statusKey = strtolower((string)($jobrequest->status ?? ''));
                                                $statusDisplay = in_array($statusKey, ['confirm_done', 'completed']) ? 'Completed' : formatJobDetailLabel($jobrequest->status ?? null);
                                                $statusBg = in_array($statusKey, ['confirm_done', 'completed']) ? 'success' : ($jobrequest->status === 'active' ? 'success' : 'warning');
                                            @endphp
                                            <span class="badge bg-{{ $statusBg }} text-dark">{{ $statusDisplay }}</span>
                                        </span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total bids:</b></span>
                                        <span class="detail-value"> {{ $totalBids }}</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label"><b>Total views:</b></span>
                                        <span class="detail-value"> {{ $jobrequest->total_views ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Apply Now Button -->
                    @if (strtolower((string)($jobrequest->status ?? '')) === 'requested')
                        <div class="text-center mt-4">
                            <a href="{{ auth()->check() ? route('post-job-request.index') : route('login') }}"
                               class="btn btn-success btn-lg"
                               style="background: #59E054; color: #fff; border: 5px solid #F0F0F0; padding: 12px 30px; border-radius: 50px; font-weight: 800; text-decoration: none;">
                                APPLY NOW
                            </a>
                        </div>
                    @endif
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



@endsection
