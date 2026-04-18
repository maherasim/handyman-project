<x-master-layout>

    <head>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        <style>
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
            .table thead th,
            #datatable thead th,
            table thead th {
                background: #3333ff !important;
                color: #fff !important;
                border-color: transparent !important;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button.current,
            .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
                background: #3333ff !important;
                border: none !important;
                color: #fff !important;
            }
        </style>
    </head>
    <div class="container-fluid">
        {{-- <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <div>
                                <h5 class="fw-bold mb-1">{{ $pageTitle }}</h5>
                                <p class="text-muted small mb-0">{{ __('messages.customer_received_ratings_subtitle') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row justify-content-between gy-3">
                @if($isAdmin)
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="col-md-12">
                        <form action="{{ route('customer-rating.bulk-action') }}" id="quick-action-form"
                            class="form-disabled d-flex gap-3 align-items-center">
                            @csrf
                            <select name="action_type" class="form-control select2" id="quick-action-type"
                                style="width:100%" disabled>
                                <option value="">{{ __('messages.no_action') }}</option>
                                <option value="delete">{{ __('messages.delete') }}</option>
                            </select>

                            <button id="quick-action-apply" class="btn btn-primary" data-ajax="true"
                                data--submit="{{ route('customer-rating.bulk-action') }}" data-datatable="reload"
                                data-confirmation='true'
                                data-title="{{ __('messages.delete') }}"
                                data-message='{{ __('Do you want to perform this action?') }}'
                                disabled>{{ __('messages.apply') }}</button>
                    </div>
                    </form>
                </div>
                @endif
                <div class="col-md-6 col-lg-4 col-xl-3 ms-auto">
                    <div class="d-flex align-items-center gap-3 justify-content-end">
                        <div class="input-group input-group-search ms-2">
                            <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..."
                                aria-label="Search" aria-describedby="addon-wrapping"
                                aria-controls="dataTableBuilder">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="datatable" class="table table-striped border"></table>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const isAdmin = @json($isAdmin);
            const columns = isAdmin ? [
                {
                    name: 'check',
                    data: 'check',
                    title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                    exportable: false,
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'updated_at',
                    name: 'updated_at',
                    title: "{{ __('product.lbl_update_at') }}",
                    orderable: true,
                    visible: false,
                },
                {
                    data: 'customer_id',
                    name: 'customer_id',
                    title: "{{ __('messages.rating_col_customer') }}"
                },
                {
                    data: 'provider_id',
                    name: 'provider_id',
                    title: "{{ __('messages.rating_col_employer') }}"
                },
                {
                    data: 'booking_id',
                    name: 'booking_id',
                    title: "{{ __('messages.booking') }}"
                },
                {
                    data: 'rating',
                    name: 'rating',
                    title: "{{ __('messages.rating') }}"
                },
                {
                    data: 'review',
                    name: 'review',
                    title: "{{ __('messages.review') }}"
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    title: "{{ __('messages.action') }}"
                }
            ] : [
                {
                    data: 'updated_at',
                    name: 'updated_at',
                    title: "{{ __('product.lbl_update_at') }}",
                    orderable: true,
                    visible: false,
                },
                {
                    data: 'provider_id',
                    name: 'provider_id',
                    title: "{{ __('messages.rating_col_employer') }}"
                },
                {
                    data: 'booking_id',
                    name: 'booking_id',
                    title: "{{ __('messages.booking') }}"
                },
                {
                    data: 'rating',
                    name: 'rating',
                    title: "{{ __('messages.rating') }}"
                },
                {
                    data: 'review',
                    name: 'review',
                    title: "{{ __('messages.review') }}"
                }
            ];

            window.renderedDataTable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: true,
                dom: '<"row align-items-center"><"table-responsive my-3 mt-3 mb-2 pb-1" rt><"row align-items-center data_table_widgets" <"col-md-6" <"d-flex align-items-center flex-wrap gap-3" l i>><"col-md-6" p>><"clear">',
                ajax: {
                    type: 'GET',
                    url: '{{ route('customer-rating.index_data') }}',
                    data: function(d) {
                        d.search = { value: $('.dt-search').val() };
                        d.filter = { column_status: $('#column_status').val() };
                    },
                },
                columns: columns,
                order: isAdmin ? [[1, 'desc']] : [[0, 'desc']],
                language: {
                    processing: "{{ __('messages.processing') }}"
                }
            });
        });

        function resetQuickAction() {
            const actionValue = $('#quick-action-type').val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');
            } else {
                $('#quick-action-apply').attr('disabled', true);
            }
        }

        $('#quick-action-type').change(function() {
            resetQuickAction()
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
