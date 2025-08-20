<x-master-layout>
    @php 
    $advancePayment = \App\Models\Payment::where('post_job_request_id', $assignedPost->id)
                                        ->where('payment_type','advance')
                                        ->first();
@endphp
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">

                <!-- Page Header -->
                <!-- Page Header -->


                <div class="card card-block card-stretch mb-3">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">{{ $pageTitle }}</h5>

                        @if (isset($assignedPost) && auth()->id() === $assignedPost->provider_id)
                            <div class="d-flex align-items-center gap-2">

                                @if(!$assignedPost->payment || $assignedPost->payment->status !== 'advance_paid')
                                    {{-- Advance terms form --}}
                                    <form id="advanceForm" class="d-flex align-items-center gap-2">
                                        <div>
                                            <label class="form-label mb-0 small">Advance %</label>
                                            <input type="number" name="advance_percent" id="advancePercent" class="form-control form-control-sm" value="40" min="1" max="100" required>
                                        </div>
                                        <div>
                                            <label class="form-label mb-0 small">Remaining</label>
                                            <input type="text" id="remainingAmount" class="form-control form-control-sm" readonly>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-sm">Submit Terms</button>
                                    </form>
                                @elseif($assignedPost->payment && $assignedPost->payment->status === 'advance_paid')
                                    {{-- After payment --}}
                                    <button class="btn btn-primary startWorkBtn" data-post-id="{{ $assignedPost->id }}">
                                        <i class="fas fa-play"></i> Start Work
                                    </button>
                                @endif

                            </div>
                        @endif
                    </div>
               </div>


@if(!$advancePayment)
    {{-- Advance terms form --}}
    <form id="advanceForm" class="d-flex align-items-center gap-2">
        <div>
            <label class="form-label mb-0 small">Advance %</label>
            <input type="number" name="advance_percent" id="advancePercent" 
                   class="form-control form-control-sm" value="40" min="1" max="100" required>
        </div>
        <div>
            <label class="form-label mb-0 small">Remaining</label>
            <input type="text" id="remainingAmount" class="form-control form-control-sm" readonly>
        </div>
        <button type="submit" class="btn btn-success btn-sm">Submit Terms</button>
    </form>
@else
    @if($advancePayment->payment_status === 'pending' && auth()->id() === $assignedPost->customer_id)
        <a href="{{ route('payment.checkout', $advancePayment->id) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-credit-card"></i> Pay Advance ({{ $advancePayment->total_amount }})
        </a>
    @elseif($advancePayment->payment_status === 'advance_paid' && auth()->id() === $assignedPost->provider_id)
        <button class="btn btn-primary startWorkBtn" data-post-id="{{ $assignedPost->id }}">
            <i class="fas fa-play"></i> Start Work
        </button>
    @endif
@endif
                <!-- Search Bar -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-end">
                            <div class="input-group w-25">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control dt-search" placeholder="Search bids...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bids Container -->
                <div id="bidsContainer" class="row">
                    <!-- Cards will be loaded here dynamically from DataTable -->
                </div>

            </div>
        </div>
    </div>

    <!-- Hidden DataTable (acts as data source) -->
    <table id="datatable" class="d-none"></table>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let bidsContainer = $("#bidsContainer");

            let table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                paging: true,
                pageLength: 6,
                ajax: {
                    url: '{{ route('bidsshowjson') }}',
                    type: "GET",
                    data: function(d) {
                        d.search = {
                            value: $('.dt-search').val()
                        }
                    }
                },
                columns: [{
                        data: 'post_title',
                        name: 'post_title'
                    },
                    {
                        data: 'provider_name',
                        name: 'provider_name'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    }, // <-- status from post_job_requests
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                drawCallback: function(settings) {
                    bidsContainer.empty();

                    let data = this.api().rows({
                        page: 'current'
                    }).data();

                    data.each(function(row) {
                        // Status Badge (graceful colors)
                        let statusBadge = '';
                        switch (row.status) {
                            case 'pending':
                                statusBadge =
                                    `<span class="badge bg-warning text-dark px-3 py-2">${row.status}</span>`;
                                break;
                            case 'assigned':
                                statusBadge =
                                    `<span class="badge bg-info text-white px-3 py-2">${row.status}</span>`;
                                break;
                            case 'in_progress':
                                statusBadge =
                                    `<span class="badge bg-primary text-white px-3 py-2">In Progress</span>`;
                                break;
                            case 'completed':
                                statusBadge =
                                    `<span class="badge bg-success text-white px-3 py-2">${row.status}</span>`;
                                break;
                            case 'cancelled':
                                statusBadge =
                                    `<span class="badge bg-danger text-white px-3 py-2">${row.status}</span>`;
                                break;
                            default:
                                statusBadge =
                                    `<span class="badge bg-secondary text-white px-3 py-2">${row.status}</span>`;
                        }

                        let card = `
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body d-flex flex-column">
                                    <h6 class="fw-bold mb-2">${row.post_title}</h6>
                                    <p class="text-muted mb-1"><i class="fas fa-user"></i> Provider: ${row.provider_name}</p>
                                    <p class="text-muted mb-1"><i class="fas fa-user-tie"></i> Customer: ${row.customer_name}</p>
                                    <p class="mb-1"><i class="fas fa-dollar-sign"></i> Bid: <span class="fw-bold">${row.price}</span></p>
                                    <p class="mb-3"><i class="fas fa-flag"></i> Status: ${statusBadge}</p>
                                    <div class="mt-auto">
                                        ${row.action}
                                    </div>
                                </div>
                            </div>
                        </div>`;
                        bidsContainer.append(card);
                    });
                }
            });

            // Live search
            $('.dt-search').on('keyup', function() {
                table.ajax.reload();
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $(document).on('click', '.acceptBid', function() {
                let bidId = $(this).data('id');
                Swal.fire({
                    title: "Are you sure?",
                    text: "Do you want to accept this bid?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#28a745",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, accept it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url('/bids/accept') }}/' + bidId,
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire("Accepted!", response.message, "success");
                                    $('#datatable').DataTable().ajax.reload();
                                } else {
                                    Swal.fire("Error!", response.message, "error");
                                }
                            },
                            error: function() {
                                Swal.fire("Error!", "Something went wrong!", "error");
                            }
                        });
                    }
                });
            });
            $(document).on('click', '.startWorkBtn', function() {
                const postId = $(this).data('post-id');
                Swal.fire({
                    title: "Start work?",
                    text: "This will move the job to in progress.",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#0d6efd",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Yes, start!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url('/post-job-request') }}/' + postId + '/start-work',
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire("Started!", response.message ||
                                        "Work started.", "success");
                                    $('#datatable').DataTable().ajax.reload();
                                } else {
                                    Swal.fire("Error!", response.message ||
                                        "Unable to start.", "error");
                                }
                            },
                            error: function() {
                                Swal.fire("Error!", "Something went wrong!", "error");
                            }
                        });
                    }
                });
            });
        });
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const advancePercentInput = document.getElementById('advancePercent');
    const remainingInput = document.getElementById('remainingAmount');
    const totalAmount = {{ $assignedPost->price ?? 0 }};

    function updateRemaining() {
        let advancePercent = parseFloat(advancePercentInput.value) || 0;
        let advanceAmount = (totalAmount * advancePercent) / 100;
        let remaining = totalAmount - advanceAmount;
        remainingInput.value = remaining.toFixed(2);
    }

    if (advancePercentInput) {
        advancePercentInput.addEventListener('input', updateRemaining);
        updateRemaining();
    }

    // Submit terms AJAX
    $('#advanceForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url('/post-job-request') }}/{{ $assignedPost->id }}/set-advance',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                advance_percent: $('#advancePercent').val()
            },
            success: function(response) {
                if(response.status){
                    Swal.fire("Saved!", response.message, "success").then(() => location.reload());
                } else {
                    Swal.fire("Error!", response.message, "error");
                }
            },
            error: function() {
                Swal.fire("Error!", "Something went wrong!", "error");
            }
        });
    });
});
</script>

</x-master-layout>
