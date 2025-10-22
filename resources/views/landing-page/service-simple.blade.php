@extends('landing-page.layouts.default')

@section('content')
<div class="section-padding">
    <div class="container">
        <form method="GET" class="card shadow-sm mb-4 p-3" id="serviceFilterForm">
            <input type="hidden" name="mode" value="simple">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" id="categorySelect" class="form-select">
                        <option value="">All</option>
                        @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ (string)($filters['category_id'] ?? '') === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Subcategory</label>
                    <select name="subcategory_id" id="subcategorySelect" class="form-select">
                        <option value="">All</option>
                        @foreach($subcategories as $sc)
                        <option value="{{ $sc->id }}" data-category="{{ $sc->category_id }}" {{ (string)($filters['subcategory_id'] ?? '') === (string)$sc->id ? 'selected' : '' }}>{{ $sc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Provider</label>
                    <select name="provider_id" class="form-select" id="providerSelect">
                        <option value="">All</option>
                        @foreach($providers as $p)
                        <option value="{{ $p->id }}" {{ (string)($filters['provider_id'] ?? '') === (string)$p->id ? 'selected' : '' }}>{{ $p->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search services">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Country</label>
                    <select name="country_id" id="countrySelect" class="form-select">
                        <option value="">All</option>
                        @foreach($countries as $ct)
                        <option value="{{ $ct->id }}" {{ (string)($filters['country_id'] ?? '') === (string)$ct->id ? 'selected' : '' }}>{{ $ct->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">City</label>
                    <select name="city_id" id="citySelect" class="form-select">
                        <option value="">All</option>
                        @foreach($cities as $cy)
                        <option value="{{ $cy->id }}" data-country="{{ $cy->country_id }}" data-state="{{ $cy->state_id ?? '' }}" {{ (string)($filters['city_id'] ?? '') === (string)$cy->id ? 'selected' : '' }}>{{ $cy->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Min price</label>
                    <input type="number" step="0.01" class="form-control" name="price_min" value="{{ $filters['price_min'] ?? '' }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Max price</label>
                    <input type="number" step="0.01" class="form-control" name="price_max" value="{{ $filters['price_max'] ?? '' }}">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Sort</label>
                    <select name="sort" class="form-select">
                        <option value="newest" {{ ($filters['sort'] ?? 'newest')==='newest' ? 'selected' : '' }}>Newest</option>
                        <option value="price_asc" {{ ($filters['sort'] ?? '')==='price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ ($filters['sort'] ?? '')==='price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 text-md-end">
                    <button id="applyBtn" type="submit" class="btn btn-primary w-100">Apply</button>
                </div>
            </div>
        </form>

        <div class="row">
            @forelse($services as $data)
                <div class="col-md-3">
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
                    @endphp
                    @include('service.datatable-card', [
                        'data' => $data,
                        'totalReviews' => $totalReviews,
                        'totalRating' => $totalRating,
                        'favouriteService' => collect(),
                        'completedBookingCount' => $completedBookingCount,
                        'plan_icon' => $plan_icon,
                    ])
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light border">No services found.</div>
                </div>
            @endforelse
        </div>

        <div class="mt-3">
            {{ $services->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const categorySelect = document.getElementById('categorySelect');
    const subcategorySelect = document.getElementById('subcategorySelect');
    const countrySelect = document.getElementById('countrySelect');
    const citySelect = document.getElementById('citySelect');
    const filterForm = document.getElementById('serviceFilterForm');

    function filterDependent(child, attr, parentValue){
        const valueToKeep = '{{ $filters['subcategory_id'] ?? '' }}';
        [...child.options].forEach((opt, idx) => {
            if(idx===0) return; // keep placeholder
            const match = String(opt.getAttribute(attr)) === String(parentValue || '');
            opt.hidden = !match;
            if (!match && opt.selected) { opt.selected = false; }
        });
    }

    function applyCategory(){
        filterDependent(subcategorySelect, 'data-category', categorySelect.value);
    }
    function applyCountry(){
        filterDependent(citySelect, 'data-country', countrySelect.value);
    }

    // Update dependent options only; do not submit automatically
    categorySelect.addEventListener('change', () => { applyCategory(); });
    countrySelect.addEventListener('change', () => { applyCountry(); });
    subcategorySelect.addEventListener('change', () => {});
    citySelect.addEventListener('change', () => {});
    document.getElementById('providerSelect').addEventListener('change', () => {});

    applyCategory();
    applyCountry();

    // Prevent implicit submits (Enter key or plugin behaviors)
    filterForm.addEventListener('keydown', function(e){
        if(e.key === 'Enter') { e.preventDefault(); }
    });

    // Only allow submit via the Apply button
    filterForm.addEventListener('submit', function(e){
        const submitter = e.submitter || null;
        if(!submitter || submitter.id !== 'applyBtn') {
            e.preventDefault();
        }
    });
});
</script>
@endsection


