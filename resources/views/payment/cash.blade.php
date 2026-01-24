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
                            <h5 class="fw-bold">{{ $pageTitle ?? trans('messages.list') }}</h5>
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
                            <input type="hidden" name="rowIds" id="rowIds" value="">
                            @if (auth()->user()->hasAnyRole(['admin']))
                                <select name="action_type" class="form-control select2" id="quick-action-type"
                                    style="width:100%">
                                    <option value="">{{ __('messages.no_action') }}</option>
                                    <option value="change-status">{{ __('messages.status') }}</option>
                                    <option value="delete">{{ __('messages.delete') }}</option>
                                </select>
                                <div class="select-status d-none quick-action-field" id="change-status-action"
                                    style="width:100%">
                                    <select name="status" class="form-control select2" id="status"
                                        style="width:auto">
                                        <option value="1" class="m-2">{{ __('messages.approvecash') }}</option>
                                    </select>
                                </div>

                                <button id="quick-action-apply" class="btn btn-primary" data-ajax="true"
                                    data-submit="{{ route('payment.bulk-action') }}" data-datatable="reload"
                                    data-confirmation='true'
                                    data-title="{{ __('cash payment list', ['form' => __('cash payment list')]) }}"
                                    title="{{ __('cash payment list', ['form' => __('cash payment list')]) }}"
                                    data-message='{{ __('Do you want to perform this action?') }}'
                                    disabled>{{ __('messages.apply') }}</button>
                            @endif
                    </div>

                    </form>
                </div>
                {{-- <div class="col-md-6 col-lg-4 col-xl-3">
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
                                <input type="text" class="form-control dt-search" placeholder="Search..."
                                    aria-label="Search" aria-describedby="addon-wrapping"
                                    aria-controls="dataTableBuilder">
                            </div>
                        </div>
                    </div>
                </div> --}}
                <div class="table-responsive">
                    <table id="datatable" class="table table-striped border">
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {

            var tableEl = $('#datatable');
            window.renderedDataTable = tableEl.DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: true,
                dom: '<"row align-items-center"><"table-responsive my-3 mt-3 mb-2 pb-1" rt><"row align-items-center data_table_widgets" <"col-md-6" <"d-flex align-items-center flex-wrap gap-3" l i>><"col-md-6" p>><"clear">',
                ajax: {
                    "type": "GET",
                    "url": '{{ route('cash.index_data') }}',
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
                    @endif
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
                        title: "{{ __('messages.user') }}",
                        orderable: false,
                    },
                    {
                        data: 'datetime',
                        name: 'datetime',
                        title: "{{ __('messages.datetime') }}"
                    },
                    {
                        data: 'history',
                        name: 'history',
                        title: "{{ __('messages.history') }}",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
                        title: "{{ __('messages.status') }}",
                        orderable: false,
                        searchable: false,
                    },
                    @if (!auth()->user()->hasRole('handyman'))
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        title: "{{ __('messages.price') }}"
                    },
                    @endif
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
                        [6, 'desc']
                    @else
                        [5, 'desc']
                    @endif
                ],
                language: {
                    processing: "{{ __('messages.processing') }}" // Set your custom processing text
                }

            });
        });

        $(document).ready(function() {
            $('#statusSelect').change(function() {
                var selectedValue = $(this).val();
                var selectedOption = $('#statusSelect option:selected');
                var route = selectedOption.data('route');

                if (selectedValue === 'cash' && route) {
                    window.location.href = route;
                }
                window.location.href = route;
            });
        });

        function resetQuickAction() {
            const actionValue = $('#quick-action-type').val();
            console.log(actionValue)
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

        function collectSelectedRowIds(){
            var ids = [];
            $('#datatable').find('tbody input[type="checkbox"]').each(function(){
                var $cb = $(this);
                if($cb.is(':checked')){
                    var val = $cb.val() || $cb.data('id') || $cb.data('row-id');
                    if(val){ ids.push(val); }
                }
            });
            $('#rowIds').val(ids.join(','));
        }

        function updateBulkControls(){
            collectSelectedRowIds();
            var anyChecked = $('#rowIds').val().length > 0;
            $('#quick-action-type').prop('disabled', !anyChecked);
            if(!anyChecked){
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
                $('#quick-action-type').val('');
            }
        }

        window.selectAllTable = function(el){
            var checked = $(el).is(':checked');
            $('#datatable').find('tbody input[type="checkbox"]').prop('checked', checked);
            updateBulkControls();
        }

        $(document).on('change', '#datatable tbody input[type="checkbox"]', function(){
            updateBulkControls();
        });

        // After table redraw, ensure events and state
        window.renderedDataTable.on('draw', function(){
            updateBulkControls();
        });

        $('#quick-action-type').change(function() {
            resetQuickAction()
        });

        $(document).on('update_quick_action', function() {

        })

        $(document).on('click', '[data-ajax="true"]', function(e) {
            e.preventDefault();
            const button = $(this);
            const confirmation = button.data('confirmation');

            if (confirmation === 'true') {
                const message = button.data('message');
                if (confirm(message)) {
                    collectSelectedRowIds();
                    const submitUrl = button.data('submit');
                    const form = button.closest('form');
                    form.attr('action', submitUrl);
                    form.submit();
                }
            } else {
                collectSelectedRowIds();
                const submitUrl = button.data('submit');
                const form = button.closest('form');
                form.attr('action', submitUrl);
                form.submit();
            }
        });

        // Intercept Approve Cash links with SweetAlert confirm
        $(document).on('click', 'a[data-approve-cash="1"]', function(e){
            e.preventDefault();
            const href = $(this).attr('href');
            Swal.fire({
                title: 'Approve Cash Payment?',
                text: 'This will mark the payment as approved and update related records.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, approve',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if(result.isConfirmed){
                    window.location.href = href;
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</x-master-layout>
