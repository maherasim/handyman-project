<x-master-layout>
    <style>
        .star-rating {
            font-size: 2rem;
            color: #ccc;
            cursor: pointer;
        }
        .star-rating .star.selected,
        .star-rating .star:hover,
        .star-rating .star:hover ~ .star {
            color: #fbc02d;
        }
        /* Red-Blue Gradient for Primary Colors */
        .btn-primary,
        button.btn-primary,
        a.btn-primary,
        .btn-success {
            background: #3333ff !important;
            border: none !important;
            color: #fff !important;
        }
        .btn-primary:hover,
        button.btn-primary:hover,
        a.btn-primary:hover,
        .btn-success:hover {
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
        .modal-header.bg-primary {
            background: #3333ff !important;
            color: #fff !important;
        }
        .nav-link.active {
            background: #3333ff !important;
            color: #fff !important;
        }
    </style>

    <div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0 p-4">{{__('messages.booking_info')}}</h3>
                <ul class="nav nav-tabs pay-tabs tabslink payment-view-tabs mb-0" id="tab-text" role="tablist">
                    <li class="nav-item payment-link">
                        <a href="javascript:void(0)"
                           data-href="{{ route('booking_layout_page',$bookingdata->id) }}?tabpage=status"
                           data-toggle="modal"
                           data-target="#breakdownModal"
                           class="nav-link active"
                           rel="tooltip"
                           style="min-width: 150px; text-align: center;">{{__('messages.view_status')}}</a>
                    </li>
                </ul>
            </div>
        </div>
            <!-- <div class="card">
                <div class="card-body"> -->
                    <div class="tab-content" id="pills-tabContent-1">
                        <div class="tab-pane active">
                            <div class="payment_paste_here"></div>
                        </div>
                    </div>
                <!-- </div>
            </div> -->
    </div>

    <div class="modal fade modal-lg" id="breakdownModal" tabindex="-1" aria-labelledby="breakdownModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">

                    <div class="modal-body">
                        <div class="status-content">
                            <!-- Status data will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="reasonModal" tabindex="-1" aria-labelledby="reasonModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reasonModalLabel">Do you want to hole this service?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="reasonForm">
                        <input type="hidden" id="bookingId">
                        <input type="hidden" id="newStatus">
                        <div class="mb-3">
                            <label for="reasonInput" class="form-label">{{__('messages.reason')}}</label>
                            <textarea type="text" class="form-control" id="reasonInput" placeholder="Enter reason" required> </textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="reasonForm" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="extraChargesModal" tabindex="-1" aria-labelledby="extraChargesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="extraChargesForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="extraChargesModalLabel">Add Extra Charges</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="bookingId">
                        <div id="extraChargesWrapper">
                            <!-- Dynamic rows will be added here -->
                        </div>
                        <button type="button" class="btn btn-outline-secondary mt-3" id="addChargeRow">+ Add More</button>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Submit Charges</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="ratingForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ratingModalLabel">Rate the Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <input type="hidden" id="ratingBookingId">

                        <!-- Star Rating -->
                        <div class="mb-3">
                            <div class="star-rating">
                                <!-- Stars will be generated dynamically -->
                                <span class="star" data-value="1">&#9733;</span>
                                <span class="star" data-value="2">&#9733;</span>
                                <span class="star" data-value="3">&#9733;</span>
                                <span class="star" data-value="4">&#9733;</span>
                                <span class="star" data-value="5">&#9733;</span>
                            </div>
                        </div>

                        <!-- Review Text -->
                        <div class="mb-3">
                            <textarea class="form-control" id="reviewText" placeholder="Write your review..." rows="3"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Submit Rating</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


        <div class="modal fade" id="serviceProofModal" tabindex="-1" aria-labelledby="serviceProofLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form id="serviceProofForm" enctype="multipart/form-data">
                    <div class="modal-content border-0 rounded-4">
                        <div class="modal-header bg-primary text-white rounded-top-4">
                            <h5 class="modal-title" id="serviceProofLabel">
                                <i class="las la-clipboard-list me-2"></i> {{ __('messages.submit_service_proof') }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <input type="hidden" name="booking_id" id="booking_id">
                            <input type="hidden" name="service_id" id="service_id">
                            <input type="hidden" name="user_id" id="user_id">

                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" id="title" placeholder="e.g. Completed Electrical Repair" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" class="form-control" id="description" rows="3" placeholder="Describe what was done..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="attachments" class="form-label">Attachments (up to 5 files)</label>
                                <input type="file" name="attachments[]" id="attachments" class="form-control" multiple accept="image/*,application/pdf">
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 px-4 pb-4">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="las la-paper-plane me-1"></i> Submit Proof
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

</div>
    @section('bottom_script')
    <!-- <script src="{{ asset('js/bootstrap.bundle.js') }}"></script> -->
    <script>
        $(document).ready(function(event) {
            var $this = $('.payment-link').find('a.active');
            loadurl = "{{route('booking_layout_page',$bookingdata->id)}}?tabpage={{$tabpage}}";
            targ = '.payment_paste_here';

            id = this.id || '';

            $.post(loadurl, {
                '_token': $('meta[name=csrf-token]').attr('content')
            }, function(data) {
                $(targ).html(data);
            });

            $this.tab('show');
        });
         $('.payment_paste_here').on('change','.booking-Status',function(){
            $.post("{{ route('bookingStatus.update') }}", {
                '_token': $('meta[name=csrf-token]').attr('content'),
                bookingId:"{{ request()->booking }}",
                status: $(this).val(),
                type: $(this).attr("type"),
            }, function(data) {
             window.location.reload();
            });
        });
        $(document).ready(function() {
        // Load status data when modal is opened
        $('#breakdownModal').on('show.bs.modal', function (e) {
            var loadurl = $(e.relatedTarget).data('href');

            $.post(loadurl, {
                '_token': $('meta[name=csrf-token]').attr('content')
            }, function(data) {
                $('.status-content').html(data);
            });
        });

        // Handle booking status changes
        $('.status-content').on('change', '.booking-Status', function(){
            $.post("{{ route('bookingStatus.update') }}", {
                '_token': $('meta[name=csrf-token]').attr('content'),
                bookingId: "{{ request()->booking }}",
                status: $(this).val(),
                type: $(this).attr("type"),
            }, function(data) {
                window.location.reload();
            });
        });
    });

    </script>
    @endsection
</x-master-layout>
