<x-master-layout>

    <head>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        <style>
            /* Red-Blue Gradient for Primary Colors */
            .btn-primary,
            button.btn-primary,
            a.btn-primary {
                background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
                border: none !important;
                color: #fff !important;
            }
            .btn-primary:hover,
            button.btn-primary:hover,
            a.btn-primary:hover {
                background: linear-gradient(135deg, #cc0000 0%, #4a4d94 100%) !important;
            }
            .text-primary,
            a.text-primary {
                background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            .bg-primary,
            .badge.bg-primary {
                background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
                color: #fff !important;
            }
            .table thead th,
            #datatable thead th,
            table thead th {
                background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
                color: #fff !important;
                border-color: transparent !important;
            }
            /* DataTables pagination */
            .dataTables_wrapper .dataTables_paginate .paginate_button.current,
            .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
                background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
                border: none !important;
                color: #fff !important;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
                background: linear-gradient(135deg, rgba(255, 0, 0, 0.1) 0%, rgba(95, 96, 185, 0.1) 100%) !important;
                border: none !important;
            }
            /* Select2 primary colors */
            .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
                color: #fff !important;
            }
        </style>
    </head>
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
    </div>
    <div class="card">
        <div class="card-body">
             <div class="row justify-content-end mb-3">
    <div class="col-md-4 col-lg-3 ms-auto">
        <div class="input-group input-group-search">
            <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control dt-search" placeholder="Search..."
                aria-label="Search" aria-describedby="addon-wrapping" aria-controls="dataTableBuilder">
        </div>
    </div>
</div>

<div class="table-responsive">
    <table id="datatable" class="table table-striped border">
    </table>
</div>

        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {

            window.renderedDataTable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: true,
                dom: '<"row align-items-center"><"table-responsive my-3 mt-3 mb-2 pb-1" rt><"row align-items-center data_table_widgets" <"col-md-6" <"d-flex align-items-center flex-wrap gap-3" l i>><"col-md-6" p>><"clear">',
                ajax: {
                    "type": "GET",
                    "url": '{{ route('transaction-request.index_data') }}',
                    "data": function(d) {
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
                    @endif () {
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
                        name: 'user.username',
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
                        title: "Transaction Type",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        title: "Request Date",
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
                    processing: "{{ __('messages.processing') }}" // Set your custom processing text
                }

            });
        });

        
    </script>
    <script>
    $(document).on('click', '.confirm-btn', function () {
        var id = $(this).data('id');

        $.ajax({
            url: '/transaction-request/' + id + '/confirm',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                alert(response.message);
                renderedDataTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert('Error confirming request');
            }
        });
    });
</script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</x-master-layout>
