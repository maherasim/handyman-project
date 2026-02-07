<x-master-layout>

    <head>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        <style>
            /* Red-Blue Gradient for Primary Colors */
            .btn-primary,
            button.btn-primary,
            a.btn-primary,
            .btn-outline-primary.active,
            .btn-outline-primary:active,
            .btn-outline-primary:focus {
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
            .badge.bg-primary,
            .badge.bg-primary-subtle {
                background: #3333ff !important;
                color: #fff !important;
            }
            .table-primary,
            .table > :not(caption) > * > * {
                background-color: transparent !important;
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
                            <h5 class="fw-bold">{{ $pageTitle ?? trans('messages.list') }}</h5>
                            @if ($auth_user->can('service add') && Route::currentRouteName() !== 'servicepackage.service')
                                <a href="{{ route('service.create') }}" class="float-end me-1 btn btn-sm btn-primary "><i
                                        class="fa fa-plus-circle"></i>
                                    {{ __('messages.add_form_title', ['form' => __('messages.service')]) }}</a>
                            @endif
                        </div>
                        {{-- {{ $dataTable->table(['class' => 'table  w-100'],false) }} --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row justify-content-between gy-3">
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="col-md-12">
                            <form action="{{ route('service.bulk-action') }}" id="quick-action-form"
                                class="form-disabled d-flex gap-3 align-items-center">
                                @csrf
                                <select name="action_type" class="form-control select2" id="quick-action-type"
                                    style="width:100%" disabled>
                                    <option value="">{{ __('messages.no_action') }}</option>
                                    <option value="change-status">{{ __('messages.status') }}</option>
                                    @if ($auth_user->can('service delete'))
                                        <option value="delete">{{ __('messages.delete') }}</option>
                                        <option value="restore">{{ __('messages.restore') }}</option>
                                        <option value="permanently-delete">{{ __('messages.permanent_dlt') }}</option>
                                    @endif
                                </select>

                                <div class="select-status d-none quick-action-field" id="change-status-action"
                                    style="width:100%">
                                    <select name="status" class="form-control select2" id="status">
                                        <option value="1">{{ __('messages.active') }}</option>
                                        <option value="0">{{ __('messages.inactive') }}</option>
                                    </select>
                                </div>
                                <button id="quick-action-apply" class="btn btn-primary" data-ajax="true"
                                    data--submit="{{ route('service.bulk-action') }}" data-datatable="reload"
                                    data-confirmation='true'
                                    data-title="{{ __('service', ['form' => __('service')]) }}"
                                    title="{{ __('service', ['form' => __('service')]) }}"
                                    data-message='{{ __('Do you want to perform this action?') }}'
                                    disabled>{{ __('messages.apply') }}</button>
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
                                        <option value="0" {{ $filter['status'] == '0' ? 'selected' : '' }}>
                                            {{ __('messages.inactive') }}</option>
                                        <option value="1" {{ $filter['status'] == '1' ? 'selected' : '' }}>
                                            {{ __('messages.active') }}</option>
                                    </select>
                                </div>
                                <div class="input-group input-group-search ms-2">
                                    <span class="input-group-text" id="addon-wrapping"><i
                                            class="fas fa-search"></i></span>
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
    </div>
    <script>
        // Define share handler - must be available globally
        window.__shareClickHandler = function(e, el) {
            try { 
                if (e) {
                    e.preventDefault(); 
                    e.stopPropagation(); 
                }
            } catch (_) {}

            function openPopup(url) {
                if (url) {
                    window.open(url, '_blank', 'noopener,noreferrer,width=600,height=600');
                }
            }

            if (!el) {
                console.error('Share handler: element not found');
                return false;
            }

            var platform = el.getAttribute('data-platform');
            var shareUrl = el.getAttribute('data-share-url');

            if (!platform) {
                console.error('Share handler: platform not found');
                return false;
            }

            if (platform === 'facebook') {
                var fbUrl = encodeURIComponent(shareUrl || window.location.href);
                var quote = encodeURIComponent(el.getAttribute('data-quote') || '');
                var shareLink = 'https://www.facebook.com/sharer/sharer.php?u=' + fbUrl + (quote ? '&quote=' + quote : '');
                openPopup(shareLink);
            } else if (platform === 'twitter') {
                var text = encodeURIComponent(el.getAttribute('data-text') || '');
                var url = encodeURIComponent(shareUrl || window.location.href);
                var shareLink = 'https://twitter.com/intent/tweet?url=' + url + '&text=' + text;
                openPopup(shareLink);
            } else if (platform === 'linkedin') {
                var liUrl = encodeURIComponent(shareUrl || window.location.href);
                var shareLink = 'https://www.linkedin.com/sharing/share-offsite/?url=' + liUrl;
                openPopup(shareLink);
            } else if (platform === 'instagram') {
                var quoteText = el.getAttribute('data-quote') || '';
                if (navigator.share) {
                    try {
                        navigator.share({ text: quoteText, url: shareUrl || window.location.href })
                            .catch(function() {
                                openPopup('https://www.instagram.com/');
                            });
                    } catch (_) {
                        openPopup('https://www.instagram.com/');
                    }
                } else {
                    openPopup('https://www.instagram.com/');
                }
            }

            return false;
        };

        // Event delegation for dynamically loaded content (works with AJAX-loaded cards)
        $(document).ready(function() {
            $(document).on('click', '.social-link.share-link', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (window.__shareClickHandler) {
                    return window.__shareClickHandler(e, this);
                }
                return false;
            });
        });

        document.addEventListener('DOMContentLoaded', (event) => {

            window.renderedDataTable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: true,
                dom: '<"row align-items-center"><"table-responsive my-3 mt-3 mb-2 pb-1" rt><"row align-items-center data_table_widgets" <"col-md-6" <"d-flex align-items-center flex-wrap gap-3" l i>><"col-md-6" p>><"clear">',
                ajax: {
                    "type": "GET",
                    "url": '{{ route('service.service-index-data', ['postrequestid' => $postrequestid, 'servicepackage' => $servicepackage]) }}',
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
                        name: 'check',
                        data: 'check',
                        title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" data-type="service" onclick="selectAllTable(this)">',
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
                        data: 'name',
                        name: 'name',
                        title: "{{ __('messages.name') }}"
                    },
                    @if ($postrequestid)
                        {
                            data: 'provider_id',
                            name: 'provider_id',
                            title: "{{ __('messages.user') }}"
                        },
                    @else
                        {
                            data: 'provider_id',
                            name: 'provider_id',
                            title: "{{ __('messages.provider') }}"
                        },
                    @endif {
                        data: 'category_id',
                        name: 'category_id',
                        title: "{{ __('messages.category') }}"
                    },
                    {
                        data: 'price',
                        name: 'price',
                        title: "{{ __('messages.price') }}"
                    },
                    @if (!$postrequestid)
                        {
                            data: 'status',
                            name: 'status',
                            title: "{{ __('messages.status') }}"
                        },
                    @endif {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        title: "{{ __('messages.action') }}",
                        className: 'text-end'
                    }

                ],
                order: [
                    [1, 'desc']
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
