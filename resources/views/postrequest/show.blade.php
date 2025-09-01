<x-master-layout>
<div class="container py-4">
    <div class="row g-3">

        <!-- Title Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-primary shadow-sm h-100 hover-shadow">
                <div class="card-body text-center">
                    <i class="fas fa-heading fa-2x text-primary mb-2"></i>
                    <h6 class="fw-bold mb-1">Title</h6>
                    <p class="mb-0">{{ $bid->postrequest->title ?? $bid->title }}</p>
                </div>
            </div>
        </div>

        <!-- Location Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-success shadow-sm h-100 hover-shadow">
                <div class="card-body text-center">
                    <i class="fas fa-map-marker-alt fa-2x text-success mb-2"></i>
                    <h6 class="fw-bold mb-1">Location</h6>
                    <p class="mb-0">{{ $bid->postrequest->city->name ?? '-' }}, {{ $bid->postrequest->country->name ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Job Type Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-warning shadow-sm h-100 hover-shadow">
                <div class="card-body text-center">
                    <i class="fas fa-briefcase fa-2x text-warning mb-2"></i>
                    <h6 class="fw-bold mb-1">Job Type</h6>
                    <p class="mb-0">{{ $bid->postrequest->type ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Start Date Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-info shadow-sm h-100 hover-shadow">
                <div class="card-body text-center">
                    <i class="far fa-calendar-check fa-2x text-info mb-2"></i>
                    <h6 class="fw-bold mb-1">Start Date</h6>
                    <p class="mb-0">{{ $bid->postrequest->start_date ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- End Date Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-danger shadow-sm h-100 hover-shadow">
                <div class="card-body text-center">
                    <i class="far fa-calendar-times fa-2x text-danger mb-2"></i>
                    <h6 class="fw-bold mb-1">End Date</h6>
                    <p class="mb-0">{{ $bid->postrequest->end_date ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Total Budget Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-secondary shadow-sm h-100 hover-shadow">
                <div class="card-body text-center">
                    <i class="fas fa-wallet fa-2x text-secondary mb-2"></i>
                    <h6 class="fw-bold mb-1">Total Budget</h6>
                    <p class="mb-0">{{ $bid->postrequest->total_budget ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Applications Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-dark shadow-sm h-100 hover-shadow">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x text-dark mb-2"></i>
                    <h6 class="fw-bold mb-1">Applications</h6>
                    <p class="mb-0">{{ $bid->postrequest->postBidList->count() ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Provider Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-primary shadow-sm h-100 hover-shadow">
                <div class="card-body text-center">
                    <i class="fas fa-user fa-2x text-primary mb-2"></i>
                    <h6 class="fw-bold mb-1">Provider</h6>
                    <p class="mb-0">{{ $bid->provider->display_name ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Customer Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-success shadow-sm h-100 hover-shadow">
                <div class="card-body text-center">
                    <i class="fas fa-user-tie fa-2x text-success mb-2"></i>
                    <h6 class="fw-bold mb-1">Customer</h6>
                    <p class="mb-0">{{ $bid->customer->display_name ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Bid Price Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-warning shadow-sm h-100 hover-shadow">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign fa-2x text-warning mb-2"></i>
                    <h6 class="fw-bold mb-1">Bid</h6>
                    <p class="mb-0">{{ $bid->price }}</p>
                </div>
            </div>
        </div>

        <!-- Status Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-info shadow-sm h-100 hover-shadow">
                <div class="card-body text-center">
                    <i class="fas fa-flag fa-2x text-info mb-2"></i>
                    <h6 class="fw-bold mb-1">Status</h6>
                    @switch($bid->status)
                        @case('pending')
                            <span class="badge px-3 py-2 bg-warning text-dark">{{ $bid->status }}</span>
                            @break
                        @case('accepted')
                            <span class="badge px-3 py-2 bg-success">{{ $bid->status }}</span>
                            @break
                        @case('in_progress')
                            <span class="badge px-3 py-2 bg-primary">In Progress</span>
                            @break
                        @default
                            <span class="badge px-3 py-2 bg-secondary">{{ $bid->status }}</span>
                    @endswitch
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Optional CSS for hover effect -->
<style>
    .hover-shadow:hover {
        transform: translateY(-3px);
        transition: 0.3s;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    }
</style>
</x-master-layout>
