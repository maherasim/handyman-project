<x-master-layout>
<div class="container">
    <div class="row" id="bidsContainer">
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h6 class="fw-bold mb-2">{{ $bid->postrequest->title ?? $bid->title }}</h6>

                    <p class="text-muted mb-1"><i class="fas fa-map-marker-alt"></i>
                        Location: {{ $bid->postrequest->city->name ?? '-' }}, {{ $bid->postrequest->country->name ?? '-' }}
                    </p>

                    <p class="text-muted mb-1"><i class="fas fa-briefcase"></i>
                        Job Type: {{ $bid->postrequest->type ?? '-' }}
                    </p>

                    <p class="text-muted mb-1"><i class="far fa-calendar-check"></i>
                        Start: <span class="fw-bold">{{ $bid->postrequest->start_date ?? '-' }}</span>
                    </p>

                    <p class="text-muted mb-1"><i class="far fa-calendar-times"></i>
                        End: <span class="fw-bold">{{ $bid->postrequest->end_date ?? '-' }}</span>
                    </p>

                    <p class="text-muted mb-1"><i class="fas fa-wallet"></i>
                        Total Budget: <span class="fw-bold">{{ $bid->postrequest->total_budget ?? '-' }}</span>
                    </p>

                    <p class="text-muted mb-1"><i class="fas fa-users"></i>
                        Applications: {{ $bid->postrequest->postBidList->count() ?? 0 }}
                    </p>

                    <p class="text-muted mb-1"><i class="fas fa-user"></i> Provider: {{ $bid->provider->display_name ?? '-' }}</p>
                    <p class="text-muted mb-1"><i class="fas fa-user-tie"></i> Customer: {{ $bid->customer->display_name ?? '-' }}</p>
                    <p class="mb-1"><i class="fas fa-dollar-sign"></i> Bid: <span class="fw-bold">{{ $bid->price }}</span></p>

                    <p class="mb-3"><i class="fas fa-flag"></i>
                        Status:
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
                    </p>

                    <div class="mt-auto">
                        @if($bid->status === 'accepted')
                            <span class="badge bg-success">Accepted</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
  </x-master-layout>
  