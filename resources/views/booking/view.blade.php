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
                    <h5 class="modal-title" id="reasonModalLabel">{{ __('messages.booking_hold_service_confirm') }}</h5>
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
                    <button type="submit" form="reasonForm" class="btn btn-primary">{{ __('messages.submit') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="extraChargesModal" tabindex="-1" aria-labelledby="extraChargesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="extraChargesForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="extraChargesModalLabel">{{ __('messages.modal_add_extra_charges_title') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="bookingId">
                        <div id="extraChargesWrapper">
                            <!-- Dynamic rows will be added here -->
                        </div>
                        <button type="button" class="btn btn-outline-secondary mt-3" id="addChargeRow">{{ __('messages.add_more') }}</button>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">{{ __('messages.modal_submit_charges') }}</button>
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
                        <h5 class="modal-title" id="ratingModalLabel">{{ __('messages.rate_the_service') }}</h5>
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
                            <textarea class="form-control" id="reviewText" placeholder="{{ __('messages.write_your_review') }}" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">{{ __('messages.submit_rating') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Customer rates worker (handyman) — same API as mobile: save-handyman-rating --}}
    <div class="modal fade" id="handymanRatingModal" tabindex="-1" aria-labelledby="handymanRatingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="handymanRatingForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="handymanRatingModalLabel">{{ __('messages.rate_worker') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <input type="hidden" id="handymanRatingHandymanId" value="">
                        <div class="mb-2 text-muted small" id="handymanRatingWorkerName"></div>
                        <div class="mb-3">
                            <div class="star-rating" id="handymanStarRating">
                                <span class="star handyman-star" data-value="1">&#9733;</span>
                                <span class="star handyman-star" data-value="2">&#9733;</span>
                                <span class="star handyman-star" data-value="3">&#9733;</span>
                                <span class="star handyman-star" data-value="4">&#9733;</span>
                                <span class="star handyman-star" data-value="5">&#9733;</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" id="handymanReviewText" placeholder="{{ __('messages.write_your_review') }}" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">{{ __('messages.submit_rating') }}</button>
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
                                <label for="description" class="form-label">{{ __('messages.description') }}</label>
                                <textarea name="description" class="form-control" id="description" rows="3" placeholder="{{ __('messages.describe_what_done') }}" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="attachments" class="form-label">{{ __('messages.attachments_up_to_five') }}</label>
                                <input type="file" name="attachments[]" id="attachments" class="form-control" multiple accept="image/*,application/pdf">
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 px-4 pb-4">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="las la-paper-plane me-1"></i> {{ __('messages.submit_proof') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

</div>
    @php
        $ugcReasonOptionsForJs = ugc_reason_options_for_js();
    @endphp
    @include('partials.ugc-service-cards-script')
    @section('bottom_script')
    <!-- <script src="{{ asset('js/bootstrap.bundle.js') }}"></script> -->
    <script>
        if (typeof window.triggerUgcReportReview !== 'function') {
            window.triggerUgcReportReview = function (reviewId, btnElement, reviewType) {
                if (typeof Swal === 'undefined' || !Swal.fire) {
                    return;
                }
                reviewType = reviewType || 'booking_rating';

                var reasonOptions = @json($ugcReasonOptionsForJs);
                function escapeHtmlUgc(str) {
                    return String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;');
                }
                var optionsHtml = reasonOptions.map(function (opt) {
                    return '<option value="' + escapeHtmlUgc(opt.value) + '">' + escapeHtmlUgc(opt.label) + '</option>';
                }).join('');

                Swal.fire({
                    title: @json(__('messages.ugc_report_title')),
                    html:
                        '<div class="text-start mt-2">' +
                        '<label class="form-label fw-bold small text-uppercase">' + @json(__('messages.ugc_report_reason')) + '</label>' +
                        '<select id="ugc-reason-review-fallback" class="form-select mb-3">' + optionsHtml + '</select>' +
                        '<label class="form-label fw-bold small text-uppercase">' + @json(__('messages.ugc_report_details')) + '</label>' +
                        '<textarea id="ugc-details-review-fallback" class="form-control" rows="4" maxlength="2000" placeholder="' + @json(__('messages.ugc_report_details_placeholder')) + '"></textarea>' +
                        '</div>',
                    showCancelButton: true,
                    confirmButtonText: @json(__('messages.ugc_report_submit')),
                    cancelButtonText: @json(__('messages.cancel')),
                    preConfirm: function () {
                        return {
                            reason: document.getElementById('ugc-reason-review-fallback').value,
                            details: document.getElementById('ugc-details-review-fallback').value
                        };
                    }
                }).then(function (result) {
                    if (!result.isConfirmed || !result.value) {
                        return;
                    }

                    fetch(@json(route('ugc.report.review')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token())
                        },
                        body: JSON.stringify({
                            review_type: reviewType,
                            review_id: reviewId,
                            reason: result.value.reason,
                            details: result.value.details
                        })
                    })
                        .then(function (response) {
                            return response.json().then(function (data) {
                                return { ok: response.ok, data: data };
                            });
                        })
                        .then(function (payload) {
                            if (!payload.ok) {
                                throw new Error((payload.data && payload.data.message) ? payload.data.message : 'Error');
                            }
                            Swal.fire({ icon: 'success', title: payload.data.message || 'OK', text: payload.data.policy || '' });
                        })
                        .catch(function (error) {
                            Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'Error' });
                        });
                });
            };
        }

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
