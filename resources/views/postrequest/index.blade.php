<x-master-layout>

    <head>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.tiny.cloud/1/m5d82gd2rwdlg96hsxpx0e5wwmfrl2zzkcw35ys8o3glilgq/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
     </head>
    <div class="container-fluid">
        <div class="row">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? trans('messages.list') }}</h5>
                            @if (auth()->user()->user_type == 'user' || auth()->user()->user_type == 'admin')
                                <a href="{{ route('post-job-request.create') }}"
                                    class="float-right mr-1 btn btn-sm btn-primary">
                                    <i class="fa fa-plus-circle"></i>
                                    {{ trans('messages.add_form_title', ['form' => trans(' Post Request')]) }}
                                </a>
                            @endif

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row justify-content-between">
                <div>
                    <div class="col-md-12">
                        <form action="{{ route('post-job.bulk-action') }}" id="quick-action-form"
                            class="form-disabled d-flex gap-3 align-items-center">
                            @csrf
                            {{-- <select name="action_type" class="form-control select2" id="quick-action-type"
                                style="width:100%" disabled>
                                <option value="">{{ __('messages.no_action') }}</option>
                                <!-- <option value="change-status">{{ __('messages.status') }}</option> -->
                                <option value="delete">{{ __('messages.delete') }}</option>
                            </select> --}}

                            <div class="select-status d-none quick-action-field" id="change-status-action"
                                style="width:100%">
                                <select name="status" class="form-control select2" id="status" style="width:100%">
                                    <option value="1">{{ __('messages.active') }}</option>
                                    <option value="0">{{ __('messages.inactive') }}</option>
                                </select>
                            </div>
                            {{-- <button id="quick-action-apply" class="btn btn-primary" data-ajax="true"
                                data--submit="{{ route('post-job.bulk-action') }}" data-datatable="reload"
                                data-confirmation='true' data-title="{{ __('post-job', ['form' => __('post-job')]) }}"
                                title="{{ __('post-job', ['form' => __('post-job')]) }}"
                                data-message='{{ __('Do you want to perform this action?') }}'
                                disabled>{{ __('messages.apply') }}</button> --}}
                    </div>

                    </form>
                </div>
                <div class="d-flex justify-content-end">
                    <div class="datatable-filter ml-auto">
                        <!-- <select name="column_status" id="column_status" class="select2 form-control" data-filter="select" style="width: 100%">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="0" {{ $filter['status'] == '0' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                    <option value="1" {{ $filter['status'] == '1' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                  </select> -->
                    </div>
                    <div class="input-group ml-2">
                        <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control dt-search" placeholder="Search..." aria-label="Search"
                            aria-describedby="addon-wrapping" aria-controls="dataTableBuilder">
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="datatable" class="table table-striped border">

                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="bidModal" tabindex="-1" role="dialog" aria-labelledby="bidModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="bidModalLabel">Place Bid</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <input type="hidden" class="postrequestid">
                <input type="hidden" id="bidId" name="bidId" value="">

                <div class="modal-body">

                    <!-- Bid Amount -->
                    <label for="bidAmount">Bid Amount:</label>
                    <input type="number" id="bidAmount" name="bidAmount" class="form-control" required>
                    <div id="bidAmountError"></div>

                    <!-- Why Choose Me -->
                    <label for="why_choose_me" class="mt-3">Why Choose Me:</label>
                    <textarea id="why_choose_me" name="why_choose_me" class="form-control"></textarea>
                    <div id="whyChooseMeError"></div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary bid-button-submit" onclick="submitBid()">Submit
                        Bid</button>
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
                    "url": '{{ route('post-job.index_data') }}',
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
                        title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                        exportable: false,
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'title',
                        name: 'title',
                        title: "{{ __('messages.title') }}",
                        render: function(data, type, row) {
                            const nonClickable = ['requested', 'cancelled'];
                            const statusKey = String(row.status_key || row.status || '').toLowerCase();
                            const isProvider = {{ auth()->user()->user_type == 'provider' ? 'true' : 'false' }};
                            const providerCan = Boolean(row.provider_can_access);

                            // Rule 1: For provider and user: requested/cancelled => not clickable
                            if (nonClickable.includes(statusKey)) {
                                return data;
                            }

                            // Rule 2: For provider only => clickable only if provider_can_access
                            if (isProvider && !providerCan) {
                                return data;
                            }

                            return `<a href="{{ url('post-job-bid') }}/${row.id}" class="job-bid-link">${data}</a>`;
                        }
                    },
                    {
                        data: 'accepted_for_current_provider',
                        name: 'accepted_for_current_provider',
                        visible: false,
                        searchable: false
                    },

                    {
                        data: 'created_at',
                        name: 'created_at',
                        title: "{{ __('Posted At') }}"
                    },
                    {
                        data: 'start_date',
                        name: 'start_date',
                        title: "{{ __('Start Date') }}"
                    },
                    {
                        data: 'end_date',
                        name: 'end_date',
                        title: "{{ __('End Date') }}"
                    },

                    @if (auth()->user()->user_type == 'provider')
                        {
                            data: 'customer_id',
                            name: 'customer_id',
                            title: "{{ __('messages.customer') }}"
                        },
                    @endif {
                        data: 'applicants',
                        name: 'applicants',
                        title: "{{ __('Proposal') }}"
                    },
                    {
                        data: 'status',
                        name: 'status',
                        title: "{{ __('messages.status') }}",
                        render: function(data) {
                            return data;
                        }
                    },
                    {
                        data: 'total_budget',
                        name: 'total_budget',
                        title: "{{ __('Max Budget') }}"
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        title: "{{ __('messages.action') }}"
                    }

                ]

            });
        });

        var authToken = "{{ auth()->user()->createToken('auth_token')->plainTextToken }}";

        function submitBid() {
            var bidAmount = $('#bidAmount').val();
            var why_choose_me;
            if (window.tinymce && tinymce.get('why_choose_me')) {
                why_choose_me = tinymce.get('why_choose_me').getContent();
            } else {
                why_choose_me = $('#why_choose_me').val();
            }
            var postRequestId = $(".postrequestid").val();
            var bidId = $('#bidId').val();

            clearErrorMessages();

            if (!bidAmount) {
                displayErrorMessage('Bid Amount is required.', 'bidAmountError');
                return;
            }

            if (!why_choose_me || (typeof why_choose_me === 'string' && why_choose_me.replace(/<[^>]*>/g, '').trim() === '')) {
                displayErrorMessage('Why Choose Me is required.', 'whyChooseMeError');
                return;
            }

            $.ajax({
                url: 'api/save-bid',
                type: 'POST',
                dataType: 'json',
                headers: {
                    'Authorization': `Bearer ${authToken}`,
                    'Accept': 'application/json'
                },
                data: {
                    post_request_id: postRequestId,
                    price: bidAmount,
                    why_choose_me: why_choose_me,
                    id: bidId
                },
                success: function(response) {
                    $('#bidModal').modal('hide');

                    if (response.hasBid) {
                        // User already bid
                        $('#datatable').DataTable().ajax.reload();
                        Swal.fire({
                            icon: 'info',
                            title: 'Notice',
                            text: 'You have already placed a bid on this post.',
                        });
                    } else {
                        // Successful bid
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Your bid has been submitted successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload the page or refresh the table
                            window.location.reload();
                        });
                    }
                },
                error: function(error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong while submitting your bid.',
                    });
                }
            });
        }

        function openBidModal(postRequestId, authUserId) {
            $('.postrequestid').val(postRequestId);

            // Store the postRequestId in the modal for later use
            $('#bidModal').data('post-request-id', postRequestId);

            // Make an AJAX call here
            $.ajax({
                url: 'api/get-post-job-bid-data',
                type: 'GET',
                dataType: 'json',
                headers: {
                    'Authorization': `Bearer ${authToken}`
                },
                data: {
                    user_id: authUserId,
                    post_request_id: postRequestId,
                },
                success: function(response) {
                    // Populate form for update or create
                    if (response && response.price !== undefined) {
                        $('#bidId').val(response.id || '');
                        $('#bidAmount').val(response.price);
                        $('#bidAmount').prop('disabled', false);
                        $('.bid-button-submit').prop('disabled', false).text('Update Bid');
                        $('#bidModalLabel').text('Update Bid');
                    } else {
                        $('#bidId').val('');
                        $('#bidAmount').val('');
                        $('#bidAmount').prop('disabled', false);
                        $('.bid-button-submit').prop('disabled', false).text('Submit Bid');
                        $('#bidModalLabel').text('Place Bid');
                    }
                },
                error: function(error) {
                    console.error('Error:', error);
                }
            });

            // Open the modal manually
            $('#bidModal').modal('show');
        }


        $('#bidModal').on('hide.bs.modal', function() {
            // Clear the stored postRequestId and bid amount when the modal is closed
            $(this).removeData('post-request-id');
            $('#bidAmount').val('');
            $('#bidAmount').prop('disabled', false);
            $('#bidId').val('');
            $('.bid-button-submit').text('Submit Bid');
            $('#bidModalLabel').text('Place Bid');
            if (window.tinymce && tinymce.get('why_choose_me')) {
                tinymce.get('why_choose_me').remove();
            }
            $('#why_choose_me').val('');
            $('#whyChooseMeError').html('');
        });

        function displayErrorMessage(message, elementId) {
            var errorMessageElement = document.createElement('div');
            errorMessageElement.innerHTML = message;
            errorMessageElement.className = 'text-danger';
            document.getElementById(elementId).appendChild(errorMessageElement);
        }

        function clearErrorMessages() {
            document.getElementById('bidAmountError').innerHTML = '';
            // document.getElementById('bidDurationError').innerHTML = '';
        }



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
    </script>

    <script>
        $(document).ready(function() {
            function initTinyWhy() {
                if (window.tinymce && !tinymce.get('why_choose_me')) {
                    tinymce.init({
                        selector: '#why_choose_me',
                        height: 180,
                        menubar: false,
                        plugins: 'lists link image preview code',
                        toolbar: 'formatselect | bold italic underline | bullist numlist | link image | code | removeformat',
                    });
                }
            }
            $('#bidModal').on('shown.bs.modal', function(){
                initTinyWhy();
            });
            window.initTinyWhy = initTinyWhy;
        });
    </script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</x-master-layout>
