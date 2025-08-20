<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <h5 class="font-weight-bold">{{ $pageTitle }}</h5>
                        </div>

                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="float-right ">
                            <div class="d-flex justify-content-end">

                                <div class="input-group ml-auto">
                                    <span class="input-group-text" id="addon-wrapping"><i
                                            class="fas fa-search"></i></span>
                                    <input type="text" class="form-control dt-search" placeholder="Search..."
                                        aria-label="Search" aria-describedby="addon-wrapping"
                                        aria-controls="dataTableBuilder">
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
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {

            window.renderedDataTable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: true,
                dom: '<"row align-items-center"><"table-responsive my-3" rt><"row align-items-center" <"col-md-6" l><"col-md-6" p>><"clear">',
                ajax: {
                    "type": "GET",
                    "url": '{{ route('bidsshowjson') }}',
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
                { data: 'DT_RowIndex', name: 'DT_RowIndex', title: "#", orderable: false, searchable: false },
                { data: 'post_title', name: 'post_title', title: "Job Post" },
                { data: 'provider_name', name: 'provider_name', title: "Provider" },
                { data: 'customer_name', name: 'customer_name', title: "Customer" },
                { data: 'price', name: 'price', title: "Bid Price" },
                { data: 'duration', name: 'duration', title: "Duration" },
                {
                    data: 'action',
                    name: 'action',
                    title: "Action",
                    orderable: false,
                    searchable: false
                }
            ]


            });
        });
    </script>
</x-master-layout>
