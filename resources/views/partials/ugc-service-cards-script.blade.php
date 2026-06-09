{{-- Report / Block for service list cards: MUST live in layout, not in DataTables cell HTML (scripts there never run). --}}
{{-- Load SweetAlert2 here so it always exists before handlers run (some pages omit it from @section('after_script')). --}}
@php
    $ugcReasonOptionsForJs = ugc_reason_options_for_js();
@endphp
@auth
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>
<script>
(function () {
    var $ = window.jQuery || window.$;
    if (!$) {
        return;
    }
    function reloadServiceCardTables() {
        ['datatable', 'remote-service-datatable'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el && $.fn.DataTable && $.fn.DataTable.isDataTable(el)) {
                try {
                    $(el).DataTable().ajax.reload(null, false);
                } catch (e) { /* ignore */ }
            }
        });
    }

    $(function () {
        if (window.__ugcServiceCardsBound) {
            return;
        }
        window.__ugcServiceCardsBound = true;

        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var ugcToken = csrfMeta ? (csrfMeta.getAttribute('content') || '') : '';

        var ugcReportUrl = @json(route('ugc.report'));
        var ugcReportReviewUrl = @json(route('ugc.report.review'));
        var ugcReportProfileUrl = @json(route('ugc.report.profile'));
        var ugcReportPostJobUrl = @json(route('ugc.report.post_job'));
        var ugcBlockUrl = @json(route('ugc.block'));
        var ugcReasonOptions = @json($ugcReasonOptionsForJs);
        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }
        function buildReasonOptionsHtml() {
            return ugcReasonOptions.map(function (opt) {
                return '<option value="' + escapeHtml(opt.value) + '">' + escapeHtml(opt.label) + '</option>';
            }).join('');
        }

        window.triggerUgcReport = function (serviceId, btnElement) {
            if (typeof Swal === 'undefined' || !Swal.fire) {
                return;
            }
            Swal.fire({
                title: '<div class="d-flex align-items-center gap-2"><div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-flag"></i></div> <span style="font-size: 1.25rem;">' + @json(__('messages.ugc_report_title')) + '</span></div>',
                html:
                    '<div class="text-start mt-4">' +
                    '<label class="form-label fw-bold text-muted small text-uppercase" style="letter-spacing: 0.5px;">' + @json(__('messages.ugc_report_reason')) + '</label>' +
                    '<select id="ugc-reason" class="form-select mb-4" style="border-radius: 10px; font-size: 1rem; font-weight: 500; border: 1px solid #ced4da; box-shadow: none; padding: 12px 36px 12px 16px; cursor: pointer; color: #495057; transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;">' +
                    buildReasonOptionsHtml() +
                    '</select>' +
                    '<label class="form-label fw-bold text-muted small text-uppercase" style="letter-spacing: 0.5px;">' + @json(__('messages.ugc_report_details')) + '</label>' +
                    '<textarea id="ugc-details" class="form-control" style="border-radius: 10px; resize: none; font-size: 0.95rem; border: 1px solid #ced4da; box-shadow: none; padding: 12px 16px; color: #495057; transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;" rows="4" placeholder="' + @json(__('messages.ugc_report_details_placeholder')) + '" maxlength="2000"></textarea>' +
                    '</div>',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: @json(__('messages.ugc_report_submit')),
                cancelButtonText: @json(__('messages.cancel')),
                customClass: {
                    confirmButton: 'btn btn-primary px-4 py-2 fw-bold me-2',
                    cancelButton: 'btn btn-light px-4 py-2 fw-bold text-dark border',
                    popup: 'rounded-4 shadow-lg border-0 py-3 px-2',
                    title: 'fs-4 fw-bold text-start w-100 m-0 p-0',
                    htmlContainer: 'm-0 p-0',
                    actions: 'mt-4 w-100 justify-content-end'
                },
                buttonsStyling: false,
                preConfirm: function () {
                    return {
                        reason: document.getElementById('ugc-reason').value,
                        details: document.getElementById('ugc-details').value
                    };
                }
            }).then(function (result) {
                if (!result.isConfirmed || !result.value) {
                    return;
                }
                $.ajax({
                    url: ugcReportUrl,
                    type: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    data: {
                        _token: ugcToken,
                        service_id: serviceId,
                        reason: result.value.reason,
                        details: result.value.details
                    },
                    success: function (res) {
                        Swal.fire({ icon: 'success', title: res.message || 'OK', text: res.policy || '' });
                        reloadServiceCardTables();
                        if (document.getElementById('jobFilterForm')) {
                            window.location.reload();
                        }
                    },
                    error: function (xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error';
                        Swal.fire({ icon: 'error', title: '{{ __("messages.error") }}', text: msg });
                    }
                });
            });
        };

        window.triggerUgcReportReview = function (reviewId, btnElement, reviewType) {
            if (typeof Swal === 'undefined' || !Swal.fire) {
                return;
            }
            reviewType = reviewType || 'booking_rating';
            Swal.fire({
                title: '<div class="d-flex align-items-center gap-2"><div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-flag"></i></div> <span style="font-size: 1.25rem;">' + @json(__('messages.ugc_report_title')) + '</span></div>',
                html:
                    '<div class="text-start mt-4">' +
                    '<label class="form-label fw-bold text-muted small text-uppercase" style="letter-spacing: 0.5px;">' + @json(__('messages.ugc_report_reason')) + '</label>' +
                    '<select id="ugc-reason-review" class="form-select mb-4" style="border-radius: 10px; font-size: 1rem; font-weight: 500; border: 1px solid #ced4da; box-shadow: none; padding: 12px 36px 12px 16px; cursor: pointer; color: #495057; transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;">' +
                    buildReasonOptionsHtml() +
                    '</select>' +
                    '<label class="form-label fw-bold text-muted small text-uppercase" style="letter-spacing: 0.5px;">' + @json(__('messages.ugc_report_details')) + '</label>' +
                    '<textarea id="ugc-details-review" class="form-control" style="border-radius: 10px; resize: none; font-size: 0.95rem; border: 1px solid #ced4da; box-shadow: none; padding: 12px 16px; color: #495057; transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;" rows="4" placeholder="' + @json(__('messages.ugc_report_details_placeholder')) + '" maxlength="2000"></textarea>' +
                    '</div>',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: @json(__('messages.ugc_report_submit')),
                cancelButtonText: @json(__('messages.cancel')),
                customClass: {
                    confirmButton: 'btn btn-primary px-4 py-2 fw-bold me-2',
                    cancelButton: 'btn btn-light px-4 py-2 fw-bold text-dark border',
                    popup: 'rounded-4 shadow-lg border-0 py-3 px-2',
                    title: 'fs-4 fw-bold text-start w-100 m-0 p-0',
                    htmlContainer: 'm-0 p-0',
                    actions: 'mt-4 w-100 justify-content-end'
                },
                buttonsStyling: false,
                preConfirm: function () {
                    return {
                        reason: document.getElementById('ugc-reason-review').value,
                        details: document.getElementById('ugc-details-review').value
                    };
                }
            }).then(function (result) {
                if (!result.isConfirmed || !result.value) {
                    return;
                }
                $.ajax({
                    url: ugcReportReviewUrl,
                    type: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    data: {
                        _token: ugcToken,
                        review_type: reviewType,
                        review_id: reviewId,
                        reason: result.value.reason,
                        details: result.value.details
                    },
                    success: function (res) {
                        Swal.fire({ icon: 'success', title: res.message || 'OK', text: res.policy || '' });
                    },
                    error: function (xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error';
                        Swal.fire({ icon: 'error', title: '{{ __("messages.error") }}', text: msg });
                    }
                });
            });
        };

        window.triggerUgcReportProvider = function (providerId, btnElement) {
            if (typeof Swal === 'undefined' || !Swal.fire) {
                return;
            }
            Swal.fire({
                title: '<div class="d-flex align-items-center gap-2"><div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-flag"></i></div> <span style="font-size: 1.25rem;">' + @json(__('messages.ugc_report')) + '</span></div>',
                html:
                    '<div class="text-start mt-4">' +
                    '<label class="form-label fw-bold text-muted small text-uppercase" style="letter-spacing: 0.5px;">' + @json(__('messages.ugc_report_reason')) + '</label>' +
                    '<select id="ugc-reason-provider" class="form-select mb-4" style="border-radius: 10px; font-size: 1rem; font-weight: 500; border: 1px solid #ced4da; box-shadow: none; padding: 12px 36px 12px 16px; cursor: pointer; color: #495057; transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;">' +
                    buildReasonOptionsHtml() +
                    '</select>' +
                    '<label class="form-label fw-bold text-muted small text-uppercase" style="letter-spacing: 0.5px;">' + @json(__('messages.ugc_report_details')) + '</label>' +
                    '<textarea id="ugc-details-provider" class="form-control" style="border-radius: 10px; resize: none; font-size: 0.95rem; border: 1px solid #ced4da; box-shadow: none; padding: 12px 16px; color: #495057; transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;" rows="4" placeholder="' + @json(__('messages.ugc_report_details_placeholder')) + '" maxlength="2000"></textarea>' +
                    '</div>',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: @json(__('messages.ugc_report_submit')),
                cancelButtonText: @json(__('messages.cancel')),
                customClass: {
                    confirmButton: 'btn btn-primary px-4 py-2 fw-bold me-2',
                    cancelButton: 'btn btn-light px-4 py-2 fw-bold text-dark border',
                    popup: 'rounded-4 shadow-lg border-0 py-3 px-2',
                    title: 'fs-4 fw-bold text-start w-100 m-0 p-0',
                    htmlContainer: 'm-0 p-0',
                    actions: 'mt-4 w-100 justify-content-end'
                },
                buttonsStyling: false,
                preConfirm: function () {
                    return {
                        reason: document.getElementById('ugc-reason-provider').value,
                        details: document.getElementById('ugc-details-provider').value
                    };
                }
            }).then(function (result) {
                if (!result.isConfirmed || !result.value) {
                    return;
                }
                // Profile report (ProfileReport), not content moderation (ContentReport)
                $.ajax({
                    url: ugcReportProfileUrl,
                    type: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': ugcToken
                    },
                    data: {
                        _token: ugcToken,
                        reported_user_id: providerId,
                        reason: result.value.reason,
                        details: result.value.details
                    },
                    success: function (res) {
                        Swal.fire({ icon: 'success', title: res.message || 'OK', text: res.policy || '' });
                    },
                    error: function (xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error';
                        Swal.fire({ icon: 'error', title: '{{ __("messages.error") }}', text: msg });
                    }
                });
            });
        };

        window.triggerUgcBlock = function (blockedUserId, btnElement, partyKind) {
            if (typeof Swal === 'undefined' || !Swal.fire) {
                return;
            }
            partyKind = partyKind || 'employer';
            var blockTitle = partyKind === 'customer'
                ? @json(__('messages.ugc_block_confirm_title_customer'))
                : @json(__('messages.ugc_block_confirm_title_employer'));
            var blockText = partyKind === 'customer'
                ? @json(__('messages.ugc_block_confirm_text_customer'))
                : @json(__('messages.ugc_block_confirm_text_employer'));
            Swal.fire({
                title: '<div class="d-flex align-items-center gap-2"><div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-ban"></i></div> <span style="font-size: 1.25rem;">' + blockTitle + '</span></div>',
                html: '<div class="text-start mt-3 mb-2" style="font-size: 1.05rem; color: #4b5563; line-height: 1.5;">' + blockText + '</div>',
                showCancelButton: true,
                confirmButtonText: @json(__('messages.ugc_block_confirm_yes')),
                cancelButtonText: @json(__('messages.cancel')),
                customClass: {
                    confirmButton: 'btn btn-danger px-4 py-2 fw-bold me-2',
                    cancelButton: 'btn btn-light px-4 py-2 fw-bold text-dark border',
                    popup: 'rounded-4 shadow-lg border-0 py-3 px-2',
                    title: 'fs-4 fw-bold text-start w-100 m-0 p-0',
                    htmlContainer: 'm-0 p-0',
                    actions: 'mt-4 w-100 justify-content-end'
                },
                buttonsStyling: false
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }
                $.ajax({
                    url: ugcBlockUrl,
                    type: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    data: { _token: ugcToken, blocked_user_id: blockedUserId },
                    success: function (res) {
                        Swal.fire({ icon: 'success', text: res.message || 'OK' }).then(function () {
                            reloadServiceCardTables();
                            if (document.getElementById('jobFilterForm') || document.getElementById('job-detail-ugc-marker')) {
                                window.location.reload();
                            }
                        });
                    },
                    error: function (xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error';
                        Swal.fire({ icon: 'error', text: msg });
                    }
                });
            });
        };

        window.triggerUgcReportPostJob = function (postJobId, btnElement) {
            if (typeof Swal === 'undefined' || !Swal.fire) {
                return;
            }
            Swal.fire({
                title: '<div class="d-flex align-items-center gap-2"><div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-flag"></i></div> <span style="font-size: 1.25rem;">' + @json(__('messages.ugc_report_title_job')) + '</span></div>',
                html:
                    '<div class="text-start mt-4">' +
                    '<label class="form-label fw-bold text-muted small text-uppercase" style="letter-spacing: 0.5px;">' + @json(__('messages.ugc_report_reason')) + '</label>' +
                    '<select id="ugc-reason-job" class="form-select mb-4" style="border-radius: 10px; font-size: 1rem; font-weight: 500; border: 1px solid #ced4da; box-shadow: none; padding: 12px 36px 12px 16px; cursor: pointer; color: #495057; transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;">' +
                    buildReasonOptionsHtml() +
                    '</select>' +
                    '<label class="form-label fw-bold text-muted small text-uppercase" style="letter-spacing: 0.5px;">' + @json(__('messages.ugc_report_details')) + '</label>' +
                    '<textarea id="ugc-details-job" class="form-control" style="border-radius: 10px; resize: none; font-size: 0.95rem; border: 1px solid #ced4da; box-shadow: none; padding: 12px 16px; color: #495057; transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;" rows="4" placeholder="' + @json(__('messages.ugc_report_details_placeholder')) + '" maxlength="2000"></textarea>' +
                    '</div>',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: @json(__('messages.ugc_report_submit')),
                cancelButtonText: @json(__('messages.cancel')),
                customClass: {
                    confirmButton: 'btn btn-primary px-4 py-2 fw-bold me-2',
                    cancelButton: 'btn btn-light px-4 py-2 fw-bold text-dark border',
                    popup: 'rounded-4 shadow-lg border-0 py-3 px-2',
                    title: 'fs-4 fw-bold text-start w-100 m-0 p-0',
                    htmlContainer: 'm-0 p-0',
                    actions: 'mt-4 w-100 justify-content-end'
                },
                buttonsStyling: false,
                preConfirm: function () {
                    return {
                        reason: document.getElementById('ugc-reason-job').value,
                        details: document.getElementById('ugc-details-job').value
                    };
                }
            }).then(function (result) {
                if (!result.isConfirmed || !result.value) {
                    return;
                }
                $.ajax({
                    url: ugcReportPostJobUrl,
                    type: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    data: {
                        _token: ugcToken,
                        post_job_id: postJobId,
                        reason: result.value.reason,
                        details: result.value.details
                    },
                    success: function (res) {
                        Swal.fire({ icon: 'success', title: res.message || 'OK', text: res.policy || '' });
                        reloadServiceCardTables();
                        if (document.getElementById('jobFilterForm') || document.getElementById('job-detail-ugc-marker')) {
                            window.location.reload();
                        }
                    },
                    error: function (xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error';
                        Swal.fire({ icon: 'error', title: '{{ __("messages.error") }}', text: msg });
                    }
                });
            });
        };
    });
})();
</script>
@endauth
