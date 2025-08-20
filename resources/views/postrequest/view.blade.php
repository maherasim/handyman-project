<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">

                <!-- Page Header -->
                <div class="card card-block card-stretch mb-3">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">{{ $pageTitle }}</h5>

                        @if(isset($assignedPost) && auth()->id() === $assignedPost->provider_id)
                            <button class="btn btn-primary startWorkBtn" data-post-id="{{ $assignedPost->id }}">
                                <i class="fas fa-play"></i> Start Work
                            </button>
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

    <!-- Hidden DataTable (only for fetching data) -->
    <table id="datatable" class="d-none"></table>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let bidsContainer = $("#bidsContainer");

            // Helpers
            function getStatusColor(status) {
                switch (status) {
                    case 'pending': return 'secondary';
                    case 'assigned': return 'info';
                    case 'in_progress': return 'warning';
                    case 'completed': return 'success';
                    case 'cancelled': return 'danger';
                    default: return 'dark';
                }
            }

            function capitalize(str) {
                return str ? str.charAt(0).toUpperCase() + str.slice(1).replace('_',' ') : '';
            }

            // DataTable init
            let table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                paging: true,
                pageLength: 6,
                ajax: {
                    url: '{{ route("bidsshowjson") }}',
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
                    { data: 'duration', name: 'duration' },
                    { data: 'status', name: 'status' }, // 👈 include status
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                drawCallback: function(settings) {
                    bidsContainer.empty();

                    let data = this.api().rows({ page: 'current' }).data();

                    data.each(function(row) {
                        let card = `
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card shadow-sm h-100 border-0">
                                <div class="card-body d-flex flex-column">

                                    <!-- Title & Status -->
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold mb-0">${row.post_title}</h6>
                                        <span class="badge bg-${getStatusColor(row.status)} text-white px-3 py-2">
                                            ${capitalize(row.status)}
                                        </span>
                                    </div>

                                    <!-- Details -->
                                    <p class="text-muted mb-1"><i class="fas fa-user"></i> Provider: ${row.provider_name}</p>
                                    <p class="text-muted mb-1"><i class="fas fa-user-tie"></i> Customer: ${row.customer_name}</p>
                                    <p class="mb-1"><i class="fas fa-dollar-sign"></i> Bid: <span class="fw-bold">${row.price}</span></p>
                                    <p class="mb-3"><i class="fas fa-clock"></i> Duration: ${row.duration}</p>

                                    <!-- Actions -->
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

    @include('postrequest.partials.bid-actions')
</x-master-layout>
