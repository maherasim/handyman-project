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
                            <h5 class="fw-bold">{{ $pageTitle ?? trans('messages.notification_list') }}</h5>
                        </div>
                        <div class="col-lg-3 justify-content-end align-items-center">
                            <div class="input-group ">
                                <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control dt-search" placeholder="Search..." aria-label="Search" aria-describedby="addon-wrapping" aria-controls="dataTableBuilder">
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
<script>
        document.addEventListener('DOMContentLoaded', (event) => {

            window.renderedDataTable = $('#datatable').DataTable({
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    responsive: true,
                    dom: '<"row align-items-center"><"table-responsive my-3" rt><"row align-items-center" <"col-md-6" l><"col-md-6" p><"col-md-6" i>><"clear">',
                    ajax: {
                    "type"   : "GET",
                    "url"    : '{{ route("notification.index_data") }}',
                    "data"   : function( d ) {
                        d.search = {
                        value: $('.dt-search').val()
                        };
                        d.filter = {
                        column_status: $('#column_status').val()
                        }
                    },
                    },
                    columns: [
                        {
                            name: 'DT_RowIndex',
                            data: 'DT_RowIndex',
                            title: "{{__('messages.srno')}}",
                            exportable: false,
                            orderable: false,
                            searchable: false,
                        },
                        {
                            data: 'type',
                            name: 'type',
                            title: "{{ __('messages.type') }}"
                        },
                        {
                            data: 'message',
                            name: 'message',
                            title: "{{ __('messages.messages') }}"
                        },
                        {
                            data: 'created_at',
                            name: 'created_at',
                            title: "{{ __('messages.created_at') }}"
                        },
                        {
                            data: 'updated_at',
                            name: 'updated_at',
                            title: "{{ __('messages.updated_at') }}"
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            title: "{{ __('messages.action') }}",
className: 'text-end'
                        }
                        
                    ],
                    language: {
          processing: "{{ __('messages.processing') }}" // Set your custom processing text
        }
                    
            });
      });
</script>
</x-master-layout>