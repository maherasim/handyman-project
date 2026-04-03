{{-- Report / Block for service list cards: MUST live in layout, not in DataTables cell HTML (scripts there never run). --}}
@auth
@if(\App\Support\UgcListing::isCustomer(auth()->user()))
<script>
(function ($) {
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

        var ugcReportUrl = @json(route('ugc.report'));
        var ugcBlockUrl = @json(route('ugc.block'));
        var ugcToken = @json(csrf_token());

        $(document).on('click', '.ugc-report-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var serviceId = $(this).data('service-id');
            if (typeof Swal === 'undefined') {
                return;
            }
            Swal.fire({
                title: @json(__('messages.ugc_report_title')),
                html:
                    '<label class="form-label d-block text-start small">' + @json(__('messages.ugc_report_reason')) + '</label>' +
                    '<select id="ugc-reason" class="swal2-input form-select mb-2">' +
                    '<option value="spam">spam</option>' +
                    '<option value="harassment">harassment</option>' +
                    '<option value="inappropriate">inappropriate</option>' +
                    '<option value="fraud">fraud</option>' +
                    '<option value="other">other</option>' +
                    '</select>' +
                    '<label class="form-label d-block text-start small">' + @json(__('messages.ugc_report_details')) + '</label>' +
                    '<textarea id="ugc-details" class="swal2-textarea form-control" rows="3" maxlength="2000"></textarea>',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: @json(__('messages.ugc_report_submit')),
                cancelButtonText: @json(__('messages.cancel')),
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
                    },
                    error: function (xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error';
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    }
                });
            });
        });

        $(document).on('click', '.ugc-block-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var providerId = $(this).data('provider-id');
            if (typeof Swal === 'undefined') {
                return;
            }
            Swal.fire({
                title: @json(__('messages.ugc_block_confirm_title')),
                text: @json(__('messages.ugc_block_confirm_text')),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: @json(__('messages.ugc_block_confirm_yes')),
                cancelButtonText: @json(__('messages.cancel'))
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }
                $.ajax({
                    url: ugcBlockUrl,
                    type: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    data: { _token: ugcToken, blocked_user_id: providerId },
                    success: function (res) {
                        Swal.fire({ icon: 'success', text: res.message || 'OK' }).then(function () {
                            reloadServiceCardTables();
                        });
                    },
                    error: function (xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error';
                        Swal.fire({ icon: 'error', text: msg });
                    }
                });
            });
        });
    });
})(window.jQuery);
</script>
@endif
@endauth
