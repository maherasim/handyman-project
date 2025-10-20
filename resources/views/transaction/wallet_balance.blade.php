<x-master-layout>

    <head>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    </head>
    <div class="container-fluid">
         <div class="row">
            <div class="col-lg-12">
     
        <div class="card card-block card-stretch">
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                    <h5 class="fw-bold">Wallet Balance</h5>
                    <div class="d-flex align-items-center gap-3">
                        <span class="fw-bold">{{ __('messages.wallet_balance') }}: {{ isset($walletBalance) ? getPriceFormat($walletBalance) : getPriceFormat(0) }}</span>
                        <a href="{{ route('wallet.create') }}" class="float-end me-1 btn btn-sm btn-primary">
                            <i class="fa fa-plus-circle"></i> 
                            {{ trans('messages.add_form_title', ['form' => trans('messages.wallet')]) }}
                        </a>
                    </div>
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
                    "url": '{{ route('wallet_balance.index_data') }}',
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
                    ,
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
                        data: 'title',
                        name: 'title',
                        title: "Title",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'user_id',
                        name: 'user_id',
                        title: "User Name"
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        title: "Amount",
                        orderable: false,
                    },
                      {
                        data: 'new_amount',
                        name: 'new_amount',
                        title: "Top-up Amount",
                        orderable: false,
                    },
                    
                    {
                        data: 'created_at',
                        name: 'created_at',
                        title: "Created At",
                        orderable: true,
                    },
                   
                    {
                        data: 'status',
                        name: 'status',
                        title: "{{ __('messages.status') }}",
                        orderable: false,
                        searchable: false,
                    },

                   
                   

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
    

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</x-master-layout>
