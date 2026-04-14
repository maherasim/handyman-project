<x-master-layout>
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
    .handydata-table thead th,
    table thead th,
    .table-color-heading th {
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
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-block card-stretch">
                        <div class="card-body">
                        <h5 class="card-title">{{__('messages.earning')}}</h5>
                            <div class="table-responsive">
                                <table class="table handydata-table mb-0">
                                    <thead class="table-color-heading">
                                        <tr class="text-secondary">
                                        <th scope="col">{{__('messages.handyman')}}</th>
                                        <th scope="col">{{__('messages.booking')}}</th>
                                        <th scope="col">{{__('messages.handyman_due_earning')}}</th>
                                        <th scope="col">{{__('messages.handyman_paid_earning')}}</th>
                                        <th scope="col">{{__('messages.provider_total_earning')}}</th>
                                        <th scope="col">{{__('messages.admin_earning')}}</th>
                                        <th scope="col">{{__('messages.total_earning')}}</th>
                                        @if(auth()->user()->hasAnyRole(['provider']))
                                        <th scope="col">{{__('messages.action')}}</th>
                                        @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@section('bottom_script')
<script type="text/javascript">
var table = $('.handydata-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: "{{ route('handymanEarningData', [], false) }}",
    columns: [
        {data: 'handyman_name', name: 'handyman_name'},
        {data: 'total_bookings', name: 'total_bookings', orderable: false},
        {data: 'handyman_earning', name: 'handyman_earning', orderable: false},

        {data: 'handyman_paid_earning', name: 'handyman_paid_earning', orderable: false},
        {data: 'provider_earning', name: 'provider_earning', orderable: false},
        {data: 'admin_earning', name: 'admin_earning', orderable: false},
        {data: 'total_earning', name: 'total_earning', orderable: false},
        @if(auth()->user()->hasAnyRole(['provider']))
        {data: 'action', name: 'action', orderable: false, searchable: false},
        @endif
    ],
    language: {
          processing: "{{ __('messages.processing') }}" // Set your custom processing text
        }
});
</script>
@endsection
</x-master-layout>