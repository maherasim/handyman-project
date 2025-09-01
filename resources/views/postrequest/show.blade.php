<x-master-layout>
    <div class="d-flex justify-content-center flex-wrap gap-2">

    @php
        $auth_user = auth()->user();
    @endphp

    {{-- Provider Actions --}}
@if($auth_user->user_type === 'provider' && $auth_user->id == $bid->provider_id)
    @if($bid->status === 'accepted')
        <button class="btn btn-primary updateStatusBtn" data-id="{{ $bid->id }}" data-status="in_process">Start Work</button>
        <button class="btn btn-success updateStatusBtn" data-id="{{ $bid->id }}" data-status="cancelled">Cancel</button>
    @elseif($bid->status === 'advance_paid')
        <button class="btn btn-primary updateStatusBtn" data-id="{{ $bid->id }}" data-status="in_process">Start Work</button>
    @elseif($bid->status === 'in_process')
        <button class="btn btn-warning holdBidBtn" data-id="{{ $bid->id }}">Hold</button>
        <button class="btn btn-success updateStatusBtn" data-id="{{ $bid->id }}" data-status="done">Done</button>
    @elseif($bid->status === 'hold')
        <button class="btn btn-primary updateStatusBtn" data-id="{{ $bid->id }}" data-status="in_process">Resume Work</button>
    @elseif($bid->status === 'confirm_done')
        <button class="btn btn-primary updateStatusBtn" data-id="{{ $bid->id }}" data-status="completed">Completed</button>
        <button class="btn btn-outline-secondary">Extra Charges</button>
    @endif
@endif

    {{-- Customer Actions --}}
   @if($auth_user->user_type === 'user' && $auth_user->id == $bid->customer_id)
    @if($bid->status === 'requested')
        <button class="btn btn-success acceptBid" data-id="{{ $bid->id }}">Accept</button>
    @elseif($bid->status === 'in_progress')
        <button class="btn btn-info updateStatusBtn" data-id="{{ $bid->id }}" data-status="in_process">Let's Start Work</button>
    @elseif($bid->status === 'done')
        <button class="btn btn-info updateStatusBtn" data-id="{{ $bid->id }}" data-status="confirm_done">Confirm Work Done</button>
    @elseif($bid->status === 'accepted')
        <button class="btn btn-info updateStatusBtn" data-id="{{ $bid->id }}" data-status="cancelled">Cancel</button>
    @elseif($bid->status === 'completed' && !$bid->has_advance_paid)
        <button class="btn btn-primary payRemainingBtn" data-post-id="{{ $bid->id }}" data-amount="{{ $bid->remaining_amount ?? 0 }}">Pay Remaining</button>
    @elseif($bid->status === 'advance_payment')
        <button class="btn btn-success payAdvanceBtn" data-post-id="{{ $bid->id }}" data-amount="{{ $bid->advance_amount ?? 0 }}">Pay Advance</button>
    @endif
@endif

</div>

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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Accept Bid
    document.querySelectorAll('.acceptBid').forEach(btn => {
        btn.addEventListener('click', function() {
            let bidId = this.dataset.id;
            Swal.fire({
                title: "Are you sure?",
                text: "Do you want to accept this bid?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, accept it!"
            }).then(result => {
                if (!result.isConfirmed) return;
                fetch(`/bids/accept/${bidId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).then(res => res.json())
                  .then(response => {
                      Swal.fire(response.status ? "Accepted!" : "Error!", response.message, response.status ? "success" : "error")
                      .then(() => location.reload());
                  }).catch(() => Swal.fire("Error!", "Something went wrong!", "error"));
            });
        });
    });

    // Update Status Button
    document.querySelectorAll('.updateStatusBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const bidId = this.dataset.id;
            const nextStatus = this.dataset.status;
            Swal.fire({
                title: 'Confirm',
                text: 'Do you want to update status to ' + nextStatus.replace('_',' ') + '?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, update',
            }).then(result => {
                if (!result.isConfirmed) return;
                fetch(`{{ route('postjob.updateStatus', ':id') }}`.replace(':id', bidId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ status: nextStatus })
                })
                .then(res => res.json())
                .then(response => {
                    Swal.fire(response.status ? 'Updated' : 'Error', response.message || 'Status updated', response.status ? 'success' : 'error')
                    .then(() => location.reload());
                }).catch(() => Swal.fire('Error', 'Something went wrong', 'error'));
            });
        });
    });

    // Pay Advance / Remaining
    document.querySelectorAll('.payAdvanceBtn, .payRemainingBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const amount = this.dataset.amount;
            const isRemaining = btn.classList.contains('payRemainingBtn');
            Swal.fire({
                title: isRemaining ? "Confirm Remaining Payment" : "Confirm Advance Payment",
                text: amount ? `Pay amount: ${amount}. Proceed?` : "Proceed?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#d33",
                confirmButtonText: isRemaining ? "Yes, pay remaining" : "Yes, pay advance"
            }).then(result => {
                if (!result.isConfirmed) return;
                fetch(`{{ route('post-job-request.pay-advance', ':id') }}`.replace(':id', postId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ amount: amount, type: isRemaining ? 'remaining' : 'advance' })
                }).then(res => res.json())
                  .then(response => {
                      Swal.fire(response.status ? "Success" : "Error", response.message, response.status ? "success" : "error")
                      .then(() => location.reload());
                  }).catch(() => Swal.fire("Error", "Something went wrong!", "error"));
            });
        });
    });

    // Hold Bid Button
    document.querySelectorAll('.holdBidBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const bidId = this.dataset.id;
            Swal.fire({
                title: 'Put on Hold',
                input: 'textarea',
                inputLabel: 'Provide hold reason',
                inputPlaceholder: 'Type your reason here... (max 500 chars)',
                inputAttributes: { 'aria-label': 'Hold reason' },
                showCancelButton: true,
                confirmButtonText: 'Submit',
                preConfirm: value => {
                    if (!value || value.trim().length === 0) Swal.showValidationMessage('Hold reason is required');
                    else if (value.length > 500) Swal.showValidationMessage('Reason too long (max 500 chars)');
                    else return value;
                }
            }).then(result => {
                if (!result.isConfirmed) return;
                fetch(`{{ route('postjob.updateStatus', ':id') }}`.replace(':id', bidId), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ status: 'hold', hold_reason: result.value })
                })
                .then(res => res.json())
                .then(response => Swal.fire(response.status ? 'On Hold' : 'Error', response.message, response.status ? 'success' : 'error')
                      .then(() => location.reload()))
                .catch(() => Swal.fire('Error', 'Something went wrong!', 'error'));
            });
        });
    });

});
</script>

</x-master-layout>
