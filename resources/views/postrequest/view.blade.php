<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">

                <!-- Page Header -->
                <div class="card card-block card-stretch mb-3">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">{{ $pageTitle }}</h5>

                        @if (isset($assignedPost) && auth()->id() === $assignedPost->provider_id)
                            <div class="d-flex align-items-center gap-2">
                                {{-- Start Work button --}}
                                <button class="btn btn-primary startWorkBtn" data-post-id="{{ $assignedPost->id }}">
                                    <i class="fas fa-play"></i> Start Work
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

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

    <!-- Hidden DataTable -->
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
                        d.search = { value: $('.dt-search').val() }
                    }
                },
                columns: [
                    { data: 'post_title', name: 'post_title' },
                    { data: 'provider_name', name: 'provider_name' },
                    { data: 'customer_name', name: 'customer_name' },
                    { data: 'price', name: 'price' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                drawCallback: function(settings) {
                    bidsContainer.empty();
                    let data = this.api().rows({ page: 'current' }).data();

                    data.each(function(row) {
                        // Status Badge
                        let statusBadge = '';
                        switch (row.status) {
                            case 'pending':    statusBadge = `<span class="badge bg-warning text-dark px-3 py-2">${row.status}</span>`; break;
                            case 'assigned':   statusBadge = `<span class="badge bg-info text-white px-3 py-2">${row.status}</span>`; break;
                            case 'in_progress':statusBadge = `<span class="badge bg-primary text-white px-3 py-2">In Progress</span>`; break;
                            case 'completed':  statusBadge = `<span class="badge bg-success text-white px-3 py-2">${row.status}</span>`; break;
                            case 'cancelled':  statusBadge = `<span class="badge bg-danger text-white px-3 py-2">${row.status}</span>`; break;
                            default:           statusBadge = `<span class="badge bg-secondary text-white px-3 py-2">${row.status}</span>`;
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

            // Accept Bid
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
                            data: { _token: '{{ csrf_token() }}' },
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

            // Start Work with Payment Split
            $(document).on('click', '.startWorkBtn', function() {
                const postId = $(this).data('post-id');

                Swal.fire({
                    title: "Set Payment Split",
                    html: `
                        <div class="mb-3 text-start">
                            <label class="form-label fw-bold">Advance Percentage</label>
                            <input type="number" id="advanceInput" class="form-control" placeholder="Enter advance %" min="0" max="100" />
                        </div>
                        <div class="text-start">
                            <label class="form-label fw-bold">Remaining Percentage</label>
                            <input type="number" id="remainingInput" class="form-control" readonly />
                        </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: "Save & Start Work",
                    preConfirm: () => {
                        const advance = document.getElementById('advanceInput').value;
                        const remaining = document.getElementById('remainingInput').value;

                        if (!advance || advance < 0 || advance > 100) {
                            Swal.showValidationMessage("Please enter a valid advance percentage (0-100)");
                            return false;
                        }
                        return { advance, remaining };
                    },
                    didOpen: () => {
                        const advanceInput = document.getElementById('advanceInput');
                        const remainingInput = document.getElementById('remainingInput');

                        advanceInput.addEventListener('input', function() {
                            let val = parseInt(this.value) || 0;
                            if (val > 100) val = 100;
                            remainingInput.value = 100 - val;
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const { advance, remaining } = result.value;

                        $.ajax({
                            url: '{{ url('/post-job-request') }}/' + postId + '/set-advance',
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}',
                                advance_percent: advance,
                                remaining_percent: remaining
                            },
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire("Saved!", response.message || "Payment split set & work started.", "success");
                                    $('#datatable').DataTable().ajax.reload();
                                } else {
                                    Swal.fire("Error!", response.message || "Unable to save.", "error");
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
</x-master-layout>
