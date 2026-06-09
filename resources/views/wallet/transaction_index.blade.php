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
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="fw-bold">{{ __('messages.provider_withdrawal_requests') }}</h5>
                            @if(auth()->check() && (auth()->user()->hasRole('provider') || auth()->user()->hasRole('handyman')))
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                @if(isset($walletBalance))
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted">{{ __('messages.available_balance') }}:</span>
                                    <span class="fw-bold text-success fs-6">{{ getPriceFormat($walletBalance ?? 0) }}</span>
                                </div>
                                @endif
                                <a href="{{ route('providerpayout.create', ['id' => auth()->user()->id]) }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-money-bill-wave"></i> {{ __('messages.withdraw_money') ?? 'Withdraw Money' }}
                                </a>
                            </div>
                            @endif
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
                        <form action="{{ route('wallet.bulk-action') }}" id="quick-action-form"
                            class="form-disabled d-flex gap-3 align-items-center">
                            @csrf

                    </div>

                    </form>
                </div>
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="d-flex align-items-center gap-3 justify-content-end">
                        <div class="d-flex justify-content-end">
                            <div class="datatable-filter ml-auto">

                            </div>
                            <div class="input-group input-group-search ms-2">
                                <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control dt-search" placeholder="Search..."
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
        document.addEventListener('DOMContentLoaded', (event) => {

            window.renderedDataTable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: true,
                dom: '<"row align-items-center"><"table-responsive my-3 mt-3 mb-2 pb-1" rt><"row align-items-center data_table_widgets" <"col-md-6" <"d-flex align-items-center flex-wrap gap-3" l i>><"col-md-6" p>><"clear">',
                ajax: {
                    "type": "GET",
                    "url": '{{ route('wallet_transaction.index_data') }}',
                    "data": function(d) {
                        d.search = {
                            value: $('.dt-search').val()
                        };
                        d.filter = {
                            column_status: $('#column_status').val()
                        }
                    },
                },
                columns: [{
                        data: 'updated_at',
                        name: 'updated_at',
                        title: "{{ __('product.lbl_update_at') }}",
                        orderable: true,
                        visible: false,
                    },
                    {
                        data: 'bank_id',
                        name: 'bank_id',
                        title: "{{ __('messages.bank_name') }}"
                    },
                    {
                        data: 'bank_details',
                        name: 'bank_details',
                        title: "{{ __('messages.bank_details') }}",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        title: "{{ __('messages.amount') }}"
                    },
                    {
                        data: 'payment_type',
                        name: 'payment_type',
                        title: "{{ __('messages.payment_type') }}"
                    },
                    {
                        data: 'datetime',
                        name: 'datetime',
                        title: "{{ __('messages.date') }}"
                    },
                    {
                        data: 'status',
                        name: 'status',
                        title: "{{ __('messages.status') }}"
                    },
                    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))
                    {
                        data: 'user_id',
                        name: 'user_id',
                        title: "{{ __('messages.name') }}"
                    },
                    @endif
                    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))
                    {
                        data: 'user_type',
                        name: 'user_type',
                        title: "{{ __('messages.user_type') }}",
                        orderable: false,
                    },
                    @endif
                    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))
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
                    [0, 'desc']
                ],
                language: {
                    processing: "{{ __('messages.processing') }}" // Set your custom processing text
                }
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

        // Bank Details Modal Handler
        $(document).on('click', '.view-bank-details', function(e) {
            e.preventDefault();
            const withdrawalId = $(this).data('id');
            const bankId = $(this).data('bank-id');
            
            if (!withdrawalId) {
                Swal.fire('{{ __("messages.error") }}', 'Withdrawal request ID not found', 'error');
                return;
            }

            // Show loading
            Swal.fire({
                title: '{{ __("messages.loading") }}',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Fetch bank details using withdrawal ID (backend will fetch bank from relationship)
            $.ajax({
                url: '{{ route("wallet.withdrawal_bank_details", ":id") }}'.replace(':id', withdrawalId),
                type: 'GET',
                success: function(response) {
                    Swal.close();
                    if (response.success && response.data) {
                        const bank = response.data;
                        const bankDetailsHtml = `
                            <div class="text-start">
                                <div class="mb-3">
                                    <h6 class="text-primary mb-3"><i class="fas fa-university"></i> {{ __("messages.bank_information") }}</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <strong>{{ __("messages.bank_name") }}:</strong>
                                        <p class="mb-2">${bank.bank_name}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>{{ __("messages.branch_name") }}:</strong>
                                        <p class="mb-2">${bank.branch_name}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>{{ __("messages.account_holder") }}:</strong>
                                        <p class="mb-2">${bank.account_holder}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>{{ __("messages.account_number") }}:</strong>
                                        <p class="mb-2">${bank.account_no}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>{{ __("messages.iban_code") }}:</strong>
                                        <p class="mb-2">${bank.iban_no}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>{{ __("messages.swift_bic_code") }}:</strong>
                                        <p class="mb-2">${bank.bic_number}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>{{ __("messages.mobile_number") }}:</strong>
                                        <p class="mb-2">${bank.mobile_no}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>{{ __("messages.status") }}:</strong>
                                        <p class="mb-2">
                                            <span class="badge ${bank.status === 'Active' ? 'bg-success' : 'bg-danger'}">
                                                ${bank.status}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;

                        Swal.fire({
                            title: '{{ __("messages.bank_details") }}',
                            html: bankDetailsHtml,
                            width: '600px',
                            confirmButtonText: '{{ __("messages.close") }}',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        Swal.fire('{{ __("messages.error") }}', response.error || 'Failed to load bank details', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    const errorMsg = xhr.responseJSON?.error || 'Failed to load bank details';
                    Swal.fire('{{ __("messages.error") }}', errorMsg, 'error');
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</x-master-layout>
