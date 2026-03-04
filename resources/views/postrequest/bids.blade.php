<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">

                <div class="card card-block card-stretch mb-3">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">{{ $pageTitle }}</h5>
                        <div>
                            <a href="{{ route('bidsshow') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> {{ __('messages.pjr_back_to_all_bids') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        @if (session('info'))
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ session('info') }}
                            </div>
                        @endif
                        <div class="d-flex justify-content-end">
                            <div class="input-group w-25">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control dt-search" placeholder="{{ __('messages.pjr_search_bids') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="postBidsTable" class="table table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('messages.pjr_post') }}</th>
                                <th>{{ __('messages.pjr_provider') }}</th>
                                <th>{{ __('messages.customer') }}</th>
                                <th>{{ __('messages.pjr_budget') }}</th>
                                <th>{{ __('messages.pjr_duration') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.pjr_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = $('#postBidsTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                paging: true,
                pageLength: 10,
                ajax: {
                    url: @json(route('postrequest.index_data', $id)),
                    type: 'GET',
                    data: function(d) {
                        d.search = { value: $('.dt-search').val() };
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'post_request_id', name: 'post_request_id' },
                    { data: 'provider_id', name: 'provider_id' },
                    { data: 'customer_id', name: 'customer_id' },
                    { data: 'price', name: 'price' },
                    { data: 'duration', name: 'duration' },
                    { data: 'status', name: 'status', defaultContent: '-' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, defaultContent: '-' }
                ]
            });

            $('.dt-search').on('keyup', function() {
                table.ajax.reload();
            });
        });
    </script>
</x-master-layout>
