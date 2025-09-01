<x-master-layout>
<div class="container">
    <div class="row">

        <!-- Title Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Title</h6>
                    <p>{{ $bid->postrequest->title ?? $bid->title }}</p>
                </div>
            </div>
        </div>

        <!-- Location Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Location</h6>
                    <p>{{ $bid->postrequest->city->name ?? '-' }}, {{ $bid->postrequest->country->name ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Job Type Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Job Type</h6>
                    <p>{{ $bid->postrequest->type ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Start Date Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Start Date</h6>
                    <p>{{ $bid->postrequest->start_date ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- End Date Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">End Date</h6>
                    <p>{{ $bid->postrequest->end_date ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Total Budget Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Total Budget</h6>
                    <p>{{ $bid->postrequest->total_budget ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Applications Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Applications</h6>
                    <p>{{ $bid->postrequest->postBidList->count() ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Provider Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Provider</h6>
                    <p>{{ $bid->provider->display_name ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Customer Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Customer</h6>
                    <p>{{ $bid->customer->display_name ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Bid Price Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Bid</h6>
                    <p>{{ $bid->price }}</p>
                </div>
            </div>
        </div>

        <!-- Status Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Status</h6>
                    @switch($bid->status)
                        @case('pending')
                            <span class="badge px-3 py-2" style="background-color:#FFC107; color:black;">{{ $bid->status }}</span>
                            @break
                        @case('accepted')
                            <span class="badge px-3 py-2" style="background-color:#20c997; color:black;">{{ $bid->status }}</span>
                            @break
                        @case('in_progress')
                            <span class="badge px-3 py-2" style="background-color:#007bff; color:black;">In Progress</span>
                            @break
                        @default
                            <span class="badge px-3 py-2" style="background-color:#6c757d; color:black;">{{ $bid->status }}</span>
                    @endswitch
                </div>
            </div>
        </div>

    </div>
</div>
</x-master-layout>
