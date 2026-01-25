@extends('landing-page.layouts.default')

@section('content')
<div class="section-padding bg-light">
    <div class="container">
        <!-- Top Filters -->
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-3">
                <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-filter me-2"></i> Filters</h5>
            </div>
            <div class="card-body p-4">
                <form method="GET" id="serviceFilterForm">
                    <input type="hidden" name="mode" value="simple">
                    <div class="row g-3">
                        <!-- Search -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 bg-light" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search services...">
                            </div>
                        </div>

                        <!-- Sort -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Sort By</label>
                            <select name="sort" class="form-select bg-light">
                                <option value="newest" {{ ($filters['sort'] ?? 'newest')==='newest' ? 'selected' : '' }}>Most Recent</option>
                                <option value="price_asc" {{ ($filters['sort'] ?? '')==='price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_desc" {{ ($filters['sort'] ?? '')==='price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="col-lg-6 col-md-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Price Range</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" step="0.01" class="form-control bg-light" name="price_min" value="{{ $filters['price_min'] ?? '' }}" placeholder="Min Price">
                                </div>
                                <div class="col-6">
                                    <input type="number" step="0.01" class="form-control bg-light" name="price_max" value="{{ $filters['price_max'] ?? '' }}" placeholder="Max Price">
                                </div>
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Category</label>
                            <select name="category_id" id="categorySelect" class="form-select bg-light w-100">
                                <option value="">All Categories</option>
                                @foreach($categories as $c)
                                <option value="{{ $c->id }}" {{ (string)($filters['category_id'] ?? '') === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subcategory -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Subcategory</label>
                            <select name="subcategory_id" id="subcategorySelect" class="form-select bg-light w-100">
                                <option value="">All Subcategories</option>
                                @foreach($subcategories as $sc)
                                <option value="{{ $sc->id }}" data-category="{{ $sc->category_id }}" {{ (string)($filters['subcategory_id'] ?? '') === (string)$sc->id ? 'selected' : '' }}>{{ $sc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Provider -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Provider</label>
                            <select name="provider_id" id="providerSelect" class="form-select bg-light w-100">
                                <option value="">All Providers</option>
                                @foreach($providers as $p)
                                <option value="{{ $p->id }}" {{ (string)($filters['provider_id'] ?? '') === (string)$p->id ? 'selected' : '' }}>{{ $p->display_name }}</option>
                                @endforeach
                            </select>
                        </div>

                         <!-- Location (Combined for compactness) -->
                        <div class="col-lg-3 col-md-6">
                             <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Country</label>
                                    <select name="country_id" id="countrySelect" class="form-select bg-light mb-2 w-100">
                                        <option value="">All</option>
                                        @foreach($countries as $ct)
                                        <option value="{{ $ct->id }}" {{ (string)($filters['country_id'] ?? '') === (string)$ct->id ? 'selected' : '' }}>{{ $ct->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">City</label>
                                    <select name="city_id" id="citySelect" class="form-select bg-light w-100">
                                        <option value="">All</option>
                                        @foreach($cities as $cy)
                                        <option value="{{ $cy->id }}" data-country="{{ $cy->country_id }}" data-state="{{ $cy->state_id ?? '' }}" {{ (string)($filters['city_id'] ?? '') === (string)$cy->id ? 'selected' : '' }}>{{ $cy->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                             </div>
                        </div>

                        <!-- Buttons -->
                        <div class="col-12 mt-4 text-end">
                            <a href="{{ route('service.list') }}" class="btn btn-outline-secondary px-4 me-2">Clear All</a>
                            <button id="applyBtn" type="submit" class="btn btn-primary px-4 fw-bold">Apply Filters</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row g-4">
            @forelse($services as $data)
                <div class="col-lg-3 col-md-6">
                    @php
                        $totalReviews = \App\Models\BookingRating::where('service_id', $data->id)->count();
                        $totalRating = $data->serviceRating ? (float)number_format(max($data->serviceRating->avg('rating'), 0), 2) : 0;
                        $completedBookingCount = \App\Models\Booking::where('service_id', $data->id)->where('status','completed')->count();
                        $plan_icon = asset('images/freepng.png');
                        $provider = $data->providers;
                        if ($provider && $provider->providerSubscription) {
                            $rawPlan = strtolower(trim($provider->providerSubscription->plan_type ?? $provider->providerSubscription->title ?? ''));
                            if (str_contains($rawPlan, 'silver')) { $plan_icon = asset('images/icon/silverpng.png'); }
                            elseif (str_contains($rawPlan, 'gold')) { $plan_icon = asset('images/goldpng.png'); }
                        }
                        // Fetch favorite service for logged-in user
                        if (!empty(auth()->user()) && auth()->user()->hasRole('user')) {
                            $favouriteService = $data->getUserFavouriteService()->where('user_id', auth()->user()->id)->get();
                        } else {
                            $favouriteService = collect();
                        }
                    @endphp
                    @include('service.datatable-card', [
                        'data' => $data,
                        'totalReviews' => $totalReviews,
                        'totalRating' => $totalRating,
                        'favouriteService' => $favouriteService,
                        'completedBookingCount' => $completedBookingCount,
                        'plan_icon' => $plan_icon,
                    ])
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5 bg-white rounded shadow-sm">
                        <img src="{{ asset('assets/search-no-data.png') }}" class="img-fluid mb-3" style="max-width: 200px;" alt="No services found">
                        <h4 class="text-muted">No services found</h4>
                        <p class="text-muted mb-0">Try adjusting your filters used to find what you're looking for.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $services->links() }}
        </div>
    </div>
</div>

@section('after_head')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

@section('after_script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Initialize Select2
    $('#categorySelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'All Categories',
        allowClear: true,
        ajax: {
            url: '{{ url("/api/category-list") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term, // search term
                    per_page: 'all' // or use pagination, but user asked for "fetch all"
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data.data, function (item) {
                        return { text: item.name, id: item.id }
                    })
                };
            },
            cache: true
        }
    });

    $('#subcategorySelect').select2({
        theme: 'bootstrap-5', 
        placeholder: 'All Subcategories',
        allowClear: true,
        ajax: {
            url: '{{ url("/api/subcategory-list") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term,
                    category_id: $('#categorySelect').val(),
                    per_page: 'all'
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data.data, function (item) {
                        return { text: item.name, id: item.id }
                    })
                };
            },
            cache: true
        }
    });

    $('#providerSelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'All Providers', 
        allowClear: true,
        ajax: {
            url: '{{ url("/api/user-list") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    keyword: params.term,
                    user_type: 'provider',
                    status: 1,
                    per_page: 'all' 
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data.data, function (item) {
                        return { text: item.display_name, id: item.id }
                    })
                };
            },
            cache: true
        }
    });
    
    // For countries, we can fetch all at once since it's small (200) or use Select2
    // Since api/country-list is POST, Select2 ajax type needs 'POST' or use 'transport'
    let countryData = [];
    $.post('{{ url("/api/country-list") }}', { _token: '{{ csrf_token() }}' }, function(data) {
        // data is array of objects
        // Populate select options manually or reinit Select2
        // We can just use standard select2 with data array
        countryData = data.map(c => ({ id: c.id, text: c.name }));
        
        // Check if there is a selected value from server rendering
        let selectedCountry = '{{ $filters['country_id'] ?? '' }}';
        
        $('#countrySelect').empty().select2({
            theme: 'bootstrap-5',
            placeholder: 'All Countries',
            allowClear: true,
            data: countryData
        });
        
        if(selectedCountry) {
            $('#countrySelect').val(selectedCountry).trigger('change');
        } else {
             $('#countrySelect').val(null).trigger('change');
        }
    });

    $('#citySelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'All Cities',
        allowClear: true,
        ajax: {
            url: '{{ route("ajax.list.cities") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    country_id: $('#countrySelect').val()
                };
            },
            processResults: function (data) {
                return {
                    results: data // data is already [{id, text}]
                };
            },
            cache: true
        }
    });

    // Event listeners
    $('#categorySelect').on('select2:select', function (e) {
         $('#subcategorySelect').val(null).trigger('change'); // Clear subcat
    });
    
    $('#countrySelect').on('select2:select', function(e) {
         $('#citySelect').val(null).trigger('change');
    });

    // Handle clearing
    $('#categorySelect').on('select2:clear', function (e) {
         $('#subcategorySelect').val(null).trigger('change');
    });
    $('#countrySelect').on('select2:clear', function(e) {
         $('#citySelect').val(null).trigger('change');
    });

    // Favorite functionality
    const baseUrl = document.querySelector('meta[name="baseUrl"]')?.getAttribute('content') || '{{ env("APP_URL") }}';

    // Use event delegation to handle dynamically added favorite buttons
    $(document).off('click', '.save_fav').on('click', '.save_fav', function (e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const serviceId = form.find('.service_id').data('service-id');
        const userId = form.find('#user_id').val() || $('input[name="user_id"]').first().val();

        if (!userId) {
            console.error('User ID not found');
            Swal.fire({
                title: 'Error',
                text: 'Please log in to add favorites',
                icon: 'error'
            });
            return;
        }

        $.ajax({
            url: '{{ route("save-favourite") }}',
            type: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            data: {
                _token: '{{ csrf_token() }}',
                service_id: serviceId,
                user_id: userId,
            },
            success: function (response) {
                Swal.fire({
                    title: 'Done',
                    text: response.message || 'Favorite saved successfully',
                    icon: 'success',
                    iconColor: '#5F60B9'
                }).then((result) => {
                    window.location.reload();
                });
            },
            error: function (xhr, status, error) {
                console.error('Error:', error, xhr);
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    Swal.fire({
                        title: 'Error',
                        text: xhr.responseJSON.message,
                        icon: 'error'
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to save favorite. Please try again.',
                        icon: 'error'
                    });
                }
            }
        });
    });

    $(document).off('click', '.delete_fav').on('click', '.delete_fav', function (e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const serviceId = form.find('.service_id').data('service-id');
        const userId = form.find('#user_id').val() || $('input[name="user_id"]').first().val();

        if (!userId) {
            console.error('User ID not found');
            Swal.fire({
                title: 'Error',
                text: 'Please log in to remove favorites',
                icon: 'error'
            });
            return;
        }

        $.ajax({
            url: '{{ route("delete-favourite") }}',
            type: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            data: {
                _token: '{{ csrf_token() }}',
                service_id: serviceId,
                user_id: userId,
            },
            success: function (response) {
                Swal.fire({
                    title: 'Done',
                    text: response.message || 'Favorite removed successfully',
                    icon: 'success',
                    iconColor: '#5F60B9'
                }).then((result) => {
                    window.location.reload();
                });
            },
            error: function (xhr, status, error) {
                console.error('Error:', error, xhr);
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    Swal.fire({
                        title: 'Error',
                        text: xhr.responseJSON.message,
                        icon: 'error'
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to delete favorite. Please try again.',
                        icon: 'error'
                    });
                }
            }
        });
    });

    // Initialize tooltips
    if (window.bootstrap && bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Social Media Share Handler
    window.__shareClickHandler = function(e, el) {
        try { 
            if (e) {
                e.preventDefault(); 
                e.stopPropagation(); 
            }
        } catch (_) {}

        function openPopup(url) {
            if (url) {
                window.open(url, '_blank', 'noopener,noreferrer,width=600,height=600');
            }
        }

        if (!el) {
            console.error('Share handler: element not found');
            return false;
        }

        var platform = el.getAttribute('data-platform');
        var shareUrl = el.getAttribute('data-share-url');

        if (!platform) {
            console.error('Share handler: platform not found');
            return false;
        }

        if (platform === 'facebook') {
            var fbUrl = encodeURIComponent(shareUrl || window.location.href);
            var quote = encodeURIComponent(el.getAttribute('data-quote') || '');
            var shareLink = 'https://www.facebook.com/sharer/sharer.php?u=' + fbUrl + (quote ? '&quote=' + quote : '');
            openPopup(shareLink);
        } else if (platform === 'twitter') {
            var text = encodeURIComponent(el.getAttribute('data-text') || '');
            var url = encodeURIComponent(shareUrl || window.location.href);
            var shareLink = 'https://twitter.com/intent/tweet?url=' + url + '&text=' + text;
            openPopup(shareLink);
        } else if (platform === 'linkedin') {
            var liUrl = encodeURIComponent(shareUrl || window.location.href);
            var shareLink = 'https://www.linkedin.com/sharing/share-offsite/?url=' + liUrl;
            openPopup(shareLink);
        } else if (platform === 'instagram') {
            var quoteText = el.getAttribute('data-quote') || '';
            if (navigator.share) {
                try {
                    navigator.share({ text: quoteText, url: shareUrl || window.location.href })
                        .catch(function() {
                            openPopup('https://www.instagram.com/');
                        });
                } catch (_) {
                    openPopup('https://www.instagram.com/');
                }
            } else {
                openPopup('https://www.instagram.com/');
            }
        }

        return false;
    };

    // Event delegation for dynamically loaded content
    $(document).on('click', '.social-link.share-link', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (window.__shareClickHandler) {
            return window.__shareClickHandler(e, this);
        }
        return false;
    });
});
</script>
@endsection
@endsection


