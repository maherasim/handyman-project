<x-master-layout>
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    @endpush

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <h5 class="fw-bold">Transaction Request</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <div class="row justify-content-between gy-3">
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="d-flex align-items-center gap-3 justify-content-end">
                            <div class="input-group input-group-search ms-2">
                                <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control dt-search" placeholder="Search..."
                                    aria-label="Search" aria-describedby="addon-wrapping"
                                    aria-controls="dataTableBuilder">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <table id="datatable" class="table table-striped border w-100"></table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.renderedDataTable = $('#datatable').DataTable({
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    responsive: true,
                    ajax: {
                        type: 'GET',
                        url: '{{ route('transaction-request.index_data') }}',
                        data: function(d) {
                            d.search = {
                                value: $('.dt-search').val()
                            };
                            d.filter = {
                                column_status: $('#column_status').val()
                            }
                        },
                    },
                    columns: [
                        @if (auth()->user()->hasAnyRole(['admin']))
                        {
                            name: 'check',
                            data: 'check',
                            title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                            exportable: false,
                            orderable: false,
                            searchable: false,
                        },
                        @endif
                        {
                            data: 'updated_at',
                            name: 'updated_at',
                            title: "{{ __('product.lbl_update_at') }}",
                            orderable: true,
                            visible: false,
                        },
                        {
                            data: 'id',
                            name: 'id',
                            title: "{{ __('messages.id') }}"
                        },
                        {
                            data: 'user_id',
                            name: 'user_id',
                            title: "Name"
                        },
                        {
                            data: 'amount',
                            name: 'amount',
                            title: "Amount",
                            orderable: false,
                        },
                        {
                            data: 'transaction_type',
                            name: 'transaction_type',
                            title: "{{ __('messages.transaction_type') }}",
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'created_at',
                            name: 'created_at',
                            title: "{{ __('messages.created_at') }}",
                            orderable: true,
                        },
                        {
                            data: 'status',
                            name: 'status',
                            title: "{{ __('messages.status') }}",
                            orderable: false,
                            searchable: false,
                        },
                        @if (auth()->user()->hasAnyRole(['admin']))
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            title: "{{ __('messages.action') }}"
                        }
                        @endif
                    ],
                    order: [
                        @if (auth()->user()->hasAnyRole(['admin']))
                            [5, 'desc']
                        @else
                            [4, 'desc']
                        @endif
                    ],
                    language: {
                        processing: "{{ __('messages.processing') }}"
                    }
                });
            });
        </script>
    @endpush
</x-master-layout>
