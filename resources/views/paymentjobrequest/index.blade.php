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
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <h5 class="fw-bold">Payment Job Request</h5>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row justify-content-between gy-3">
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="col-md-12">
                        <form action="{{ route('payment.bulk-action') }}" id="quick-action-form"
                            class="form-disabled d-flex gap-3 align-items-center">
                            @csrf
                            @if (auth()->user()->hasAnyRole(['admin']))
                                <select name="action_type" class="form-control select2" id="quick-action-type"
                                    style="width:100%" disabled>
                                    <option value="">{{ __('messages.no_action') }}</option>
                                    <option value="delete">{{ __('messages.delete') }}</option>
                                </select>


                                {{-- <button id="quick-action-apply" class="btn btn-primary" data-ajax="true"
                                    data--submit="{{ route('payment.bulk-action') }}" data-datatable="reload"
                                    data-confirmation='true' data-title="{{ __('payment', ['form' => __('payment')]) }}"
                                    title="{{ __('payment', ['form' => __('payment')]) }}"
                                    data-message='{{ __('Do you want to perform this action?') }}'
                                    disabled>{{ __('messages.apply') }}</button> --}}
                            @endif
                    </div>

                    </form>
                </div>
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="d-flex align-items-center gap-3 justify-content-end">
                        <div class="d-flex justify-content-end gap-3">
                            <div class="datatable-filter ml-auto">
                                <select name="column_status" id="column_status" class="select2 form-control"
                                    data-filter="select" style="width: 100%">
                                    <option value="">{{ __('messages.all') }}</option>
                                    <option value="advanced_paid">{{ __('messages.advanced_paid') }}</option>
                                    <option value="paid">{{ __('messages.paid') }}</option>
                                    <option value="pending_by_admin">{{ __('messages.pending_by_admin') }}</option>
                                    <option value="approved_by_admin">{{ __('messages.approved_by_admin') }}</option>
                                    <option value="approved_by_provider">{{ __('messages.approved_by_provider') }}
                                    </option>
                                    <option value="pending_by_provider">{{ __('messages.pending_by_provider') }}
                                    </option>
                                    <option value="send_to_provider">{{ __('messages.send_to_provider') }}</option>
                                    <option value="approved_by_handyman">{{ __('messages.approved_by_handyman') }}
                                    </option>

                                </select>
                            </div>
                            <div class="input-group input-group-search ms-2">
                                <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control dt-search" placeholder="{{ __("messages.search_placeholder") }}"
                                    aria-label="Search" aria-describedby="addon-wrapping"
                                    aria-controls="dataTableBuilder">
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            @if (auth()->user()->hasAnyRole(['provider', 'user', 'admin', 'demo_admin']))
                // Init DataTable for Admin / Provider / User
                $('#datatable').DataTable({
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    responsive: true,
                    dom: '<"row align-items-center"><"table-responsive my-3 mt-3 mb-2 pb-1" rt><"row align-items-center data_table_widgets" <"col-md-6" <"d-flex align-items-center flex-wrap gap-3" l i>><"col-md-6" p>><"clear">',
                    ajax: {
                        type: "GET",
                        url: '{{ route('paymentjobrequest.index_data') }}',
                        data: function(d) {
                            d.search = {
                                value: $('.dt-search').val()
                            };
                            d.filter = {
                                column_status: $('#column_status').val()
                            };
                        }
                    },
                    columns: [
                        @if (auth()->user()->hasRole('admin'))
                            {
                                name: 'check',
                                data: 'check',
                                title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                                exportable: false,
                                orderable: false,
                                searchable: false,
                            },
                        @endif {
                            data: 'updated_at',
                            name: 'updated_at',
                            title: "{{ __('messages.update_at') }}",
                            orderable: true,
                            visible: false
                        },
                        {
                            data: 'post_job_bid_request_id',
                            name: 'post_job_bid_request_id',
                            title: "{{ __('messages.id') }}"
                        },
                       
                        {
                            data: 'post_job',
                            name: 'post_job',
                            title: "Post Job Request"
                        },
                        {
                            data: 'customer_id',
                            name: 'customer_id',
                            title: "{{ __('messages.user') }}"
                        },
                       {
                        data:'history',
                        name:'history',
                        title:"View History",
                        orderable: false,
                        searchable: false
                       },
                        {
                            data: 'payment_type',
                            name: 'payment_type',
                            title: "{{ __('messages.payment_type') }}"
                        },
                        {
                            data: 'payment_status',
                            name: 'payment_status',
                            title: "{{ __('messages.status') }}"
                        },
                        {
                            data: 'datetime',
                            name: 'datetime',
                            title: "{{ __('messages.datetime') }}"
                        },
                        {
                            data: 'total_amount',
                            name: 'total_amount',
                            title: "{{ __('messages.total_paid_amount') }}"
                        }
                       
                    ],
                    order: [
                        @if (auth()->user()->hasRole('admin'))
                            [7, 'desc']
                        @else
                            [6, 'desc']
                        @endif
                    ],
                    language: {
                        processing: "{{ __('messages.processing') }}"
                    }
                });
            @elseif (auth()->user()->hasRole('handyman'))
                // Init DataTable for Handyman (Commission Earning view)
                $('#datatable').DataTable({
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    responsive: true,
                    dom: '<"row align-items-center"><"table-responsive my-3 mt-3 mb-2 pb-1" rt><"row align-items-center data_table_widgets" <"col-md-6" <"d-flex align-items-center flex-wrap gap-3" l i>><"col-md-6" p>><"clear">',
                    ajax: {
                        type: "GET",
                        url: '{{ route('handyman.earnings.index_data') }}', // <-- New route for handyman commission earnings
                        data: function(d) {
                            d.search = {
                                value: $('.dt-search').val()
                            };
                            d.filter = {
                                column_status: $('#column_status').val()
                            };
                        }
                    },
                    columns: [{
                            data: 'updated_at',
                            name: 'updated_at',
                            title: "{{ __('product.lbl_update_at') }}",
                            orderable: true,
                            visible: false
                        },
                        {
                            data: 'id',
                            name: 'id',
                            title: "{{ __('messages.id') }}"
                        },
                        {
                            data: 'booking_id',
                            name: 'booking_id',
                            title: "{{ __('messages.service') }}"
                        },
                        {
                            data: 'customer_id',
                            name: 'customer_id',
                            title: "{{ __('messages.user') }}"
                        },
                        {
                            data: 'payment_type',
                            name: 'payment_type',
                            title: "{{ __('messages.payment_type') }}"
                        },
                        {
                            data: 'payment_status',
                            name: 'payment_status',
                            title: "{{ __('messages.status') }}",
                            render: function(data, type, row, meta) {
                                return '<span class="badge bg-primary text-white">Paid</span>';
                            }
                        },

                        {
                            data: 'datetime',
                            name: 'datetime',
                            title: "{{ __('messages.datetime') }}"
                        },
                        {
                            data: 'handyman_earning',
                            name: 'handyman_earning',
                            title: "My Earning"
                        }
                    ],
                    order: [
                        [6, 'desc']
                    ],
                    language: {
                        processing: "{{ __('messages.processing') }}"
                    }
                });
            @endif
        });

        // Quick action reset
        function resetQuickAction() {
            const actionValue = $('#quick-action-type').val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');
                if (actionValue == 'change-status') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-status-action').removeClass('d-none');
                } else {
                    $('.quick-action-field').addClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        }

        $('#quick-action-type').change(function() {
            resetQuickAction();
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


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</x-master-layout>
