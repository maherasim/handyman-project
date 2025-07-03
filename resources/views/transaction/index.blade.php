<x-master-layout>
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
    @endpush

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

        <div class="card mt-3">
            <div class="card-body">
                <div class="row justify-content-between gy-3">
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <form action="{{ route('transaction-request.bulk-action') }}" id="quick-action-form"
                            class="form-disabled d-flex gap-3 align-items-center">
                            @csrf
                            @if (auth()->user()->hasAnyRole(['admin']))
                                <select name="action_type" class="form-control select2" id="quick-action-type"
                                    style="width:100%" disabled>
                                    <option value="">{{ __('messages.no_action') }}</option>
                                    <option value="change-status">{{ __('messages.status') }}</option>
                                    <option value="delete">{{ __('messages.delete') }}</option>
                                </select>

                                <div class="select-status d-none quick-action-field" id="change-status-action"
                                    style="width:100%">
                                    <select name="status" class="form-control select2" id="status">
                                        <option value="1">{{ __('messages.approve-transaction') }}</option>
                                    </select>
                                </div>

                                <button id="quick-action-apply" class="btn btn-primary" data-ajax="true"
                                    data--submit="{{ route('transaction-request.bulk-action') }}"
                                    data-datatable="reload" data-confirmation='true'
                                    data-title="{{ __('Transaction Request') }}"
                                    data-message='{{ __('Do you want to perform this action?') }}'
                                    disabled>{{ __('messages.apply') }}</button>
                            @endif
                        </form>
                    </div>

                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="d-flex align-items-center gap-3 justify-content-end">
                            <div class="datatable-filter ml-auto">
                                <select name="column_status" id="column_status" class="select2 form-control"
                                    data-filter="select" style="width: 100%">
                                    <option value="">{{ __('messages.all') }}</option>
                                    <option value="advanced_paid">{{ __('messages.advanced_paid') }}</option>
                                    <option value="paid">{{ __('messages.paid') }}</option>
                                    <option value="pending_by_admin">{{ __('messages.pending_by_admin') }}</option>
                                    <option value="approved_by_admin">{{ __('messages.approved_by_admin') }}</option>
                                    <option value="approved_by_provider">{{ __('messages.approved_by_provider') }}</option>
                                    <option value="pending_by_provider">{{ __('messages.pending_by_provider') }}</option>
                                    <option value="send_to_provider">{{ __('messages.send_to_provider') }}</option>
                                    <option value="approved_by_handyman">{{ __('messages.approved_by_handyman') }}</option>
                                </select>
                            </div>

                            <div class="input-group input-group-search ms-2">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control dt-search" placeholder="Search...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <table id="datatable" class="table table-striped border w-100">
                        <thead>
                            <tr>
                                @if (auth()->user()->hasAnyRole(['admin']))
                                    <th><input type="checkbox" class="form-check-input" id="select-all-table"></th>
                                @endif
                                <th>ID</th>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Status</th>
                                @if (auth()->user()->hasAnyRole(['admin']))
                                    <th>Action</th>
                                @endif
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.renderedDataTable = $('#datatable').DataTable({
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    responsive: true,
                    ajax: {
                        type: 'GET',
                        url: '{{ route('transaction-request.index_data') }}',
                        data: function (d) {
                            d.search = {
                                value: $('.dt-search').val()
                            };
                            d.filter = {
                                column_status: $('#column_status').val()
                            }
                        }
                    },
                    columns: [
                        @if (auth()->user()->hasAnyRole(['admin']))
                            {
                                name: 'check',
                                data: 'check',
                                title: '',
                                orderable: false,
                                searchable: false
                            },
                        @endif
                        { data: 'id', name: 'id' },
                        { data: 'user_id', name: 'user_id' },
                        { data: 'amount', name: 'amount' },
                        { data: 'transaction_type', name: 'transaction_type' },
                        { data: 'status', name: 'status' },
                        @if (auth()->user()->hasAnyRole(['admin']))
                            {
                                data: 'action',
                                name: 'action',
                                orderable: false,
                                searchable: false
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

            $('#quick-action-type').change(function () {
                const actionValue = $(this).val();
                if (actionValue) {
                    $('#quick-action-apply').removeAttr('disabled');
                    if (actionValue === 'change-status') {
                        $('.quick-action-field').addClass('d-none');
                        $('#change-status-action').removeClass('d-none');
                    } else {
                        $('.quick-action-field').addClass('d-none');
                    }
                } else {
                    $('#quick-action-apply').attr('disabled', true);
                    $('.quick-action-field').addClass('d-none');
                }
            });

            $(document).on('click', '[data-ajax="true"]', function (e) {
                e.preventDefault();
                const button = $(this);
                if (button.data('confirmation') === 'true') {
                    if (confirm(button.data('message'))) {
                        const form = button.closest('form');
                        form.attr('action', button.data('submit'));
                        form.submit();
                    }
                } else {
                    const form = button.closest('form');
                    form.attr('action', button.data('submit'));
                    form.submit();
                }
            });

            $('#column_status, .dt-search').on('change keyup', function () {
                renderedDataTable.ajax.reload();
            });
        </script>
    @endpush
</x-master-layout>
