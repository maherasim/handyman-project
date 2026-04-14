<x-master-layout>
    <head>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        <style>
            /* Red-Blue Gradient for Primary Colors */
            .btn-primary,
            button.btn-primary,
            a.btn-primary {
                background: #3333ff !important;
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
                background: #3333ff;
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            .bg-primary,
            .badge.bg-primary {
                background: #3333ff !important;
                color: #fff !important;
            }
            .table thead th,
            #datatable thead th,
            table thead th {
                background: #3333ff !important;
                color: #fff !important;
                border-color: transparent !important;
            }
            /* DataTables pagination */
            .dataTables_wrapper .dataTables_paginate .paginate_button.current,
            .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
                background: #3333ff !important;
                border: none !important;
                color: #fff !important;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
                background: linear-gradient(135deg, rgba(255, 0, 0, 0.1) 0%, rgba(95, 96, 185, 0.1) 100%) !important;
                border: none !important;
            }
            /* Select2 primary colors */
            .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background: #3333ff !important;
                color: #fff !important;
            }
        </style>
    </head>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card card-block card-stretch">
                            <div class="d-flex card-body flex-wrap align-items-baseline gap-2">
                                <h5 class="card-title me-1 mb-0">{{ __('messages.earning') }}</h5>
                                @if(($earningScope ?? 'all') === 'booking')
                                    <span class="text-muted small">({{ __('messages.booking') }})</span>
                                @elseif(($earningScope ?? 'all') === 'post_job')
                                    <span class="text-muted small">({{ __('messages.post_job_request') }})</span>
                                @endif
                                <span class="">({{ __('messages.tax_not_included') }})</span>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="row justify-content-end">
                                    <div class="col-md-3">
                                        <div class="d-flex justify-content-end">

                                            <div class="input-group input-group-search ml-auto">
                                                <span class="input-group-text" id="addon-wrapping"><i
                                                        class="fas fa-search"></i></span>
                                                <input type="text" class="form-control dt-search"
                                                    placeholder="Search..." aria-label="Search"
                                                    aria-describedby="addon-wrapping" aria-controls="dataTableBuilder">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="datatable" class="table table-striped border">

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                    "url": '{{ route('earningData', [], false) }}',
                    "data": function(d) {
                        d.search = {
                            value: $('.dt-search').val()
                        };
                        d.filter = {
                            column_status: $('#column_status').val()
                        };
                        d.scope = @json($earningScope ?? 'all');
                    },
                },
                columns: [{
                        data: 'provider_name',
                        name: 'provider_name',
                        title: "{{ __('messages.provider') }}"
                    },
                    {
                        data: 'total_bookings',
                        name: 'total_bookings',
                        title: @if(($earningScope ?? 'all') === 'booking')
                            "{{ __('messages.booking') }}"
                        @elseif(($earningScope ?? 'all') === 'post_job')
                            "{{ __('messages.post_job_request') }}"
                        @else
                            "{{ __('messages.booking') }} / {{ __('messages.post_job_request') }}"
                        @endif,
                        orderable: false,
                    },
                    {
                        data: 'total_earning',
                        name: 'total_earning',
                        title: "{{ __('messages.total_earning') }}",
                        orderable: false,
                    },

                    {
                        data: 'admin_earning',
                        name: 'admin_earning',
                        title: "{{ __('messages.admin_earning') }}",
                        orderable: false,
                    },
                    {
                        data: 'provider_earning',
                        name: 'provider_earning',
                        title: "{{ __('messages.provider_due_earning') }}",
                        orderable: false,
                    },

                    {
                        data: 'provider_paid_earning',
                        name: 'provider_paid_earning',
                        title: "{{ __('messages.provider_paid_earning') }}",
                        orderable: false, // Disable sorting
                    },
                    {
                        data: 'handyman_total_earning',
                        name: 'handyman_total_earning',
                        title: "{{ __('messages.hadyman_total_earning') }}",
                        orderable: false, // Disable sorting
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        title: "{{ __('messages.action') }}"
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                language: {
                    processing: "{{ __('messages.processing') }}" // Set your custom processing text
                }
            });
        });



        $(document).on('click', '[data-ajax="true"]', function(e) {
            e.preventDefault();
            const button = $(this);
            const confirmation = button.data('confirmation');

            if (confirmation === 'true') {
                const message = button.data('message');
                if (confirm(message)) {
                    const submitUrl = button.data('submit');
                    const form = button.closest('form');
                    form.attr('action', submitUrl);
                    form.submit();
                }
            } else {
                const submitUrl = button.data('submit');
                const form = button.closest('form');
                form.attr('action', submitUrl);
                form.submit();
            }
        });
    </script>
</x-master-layout>
