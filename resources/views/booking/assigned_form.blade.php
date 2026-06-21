<!-- Assign Handyman Modal -->
<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">{{ $pageTitle }}</h5>
            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
        </div>

        {{ html()->form('POST', route('booking.assigned'))->attribute('data-toggle', 'validator')->open() }}
        <div class="modal-body">
            {{ html()->hidden('id', $bookingdata->id ?? null) }}

            {{-- Handyman select --}}
            <div class="row">
                <div class="col-md-12 form-group">
                    {{ html()->label(__('messages.select_name', ['select' => __('messages.handyman')]) . ' <span class="text-danger">*</span>', 'handyman_id')->class('form-control-label') }}
                    <br>
                    @php
                        if ($bookingdata->booking_address_id != null) {
                            $route = route('ajax-list', ['type' => 'handyman', 'provider_id' => $bookingdata->provider_id, 'booking_id' => $bookingdata->id]);
                        } else {
                            $route = route('ajax-list', ['type' => 'handyman', 'provider_id' => $bookingdata->provider_id]);
                        }
                        $assigned_handyman = [];
                        if ($bookingdata->handymanAdded && $bookingdata->handymanAdded->count() > 0) {
                            $assigned_handyman = $bookingdata->handymanAdded->mapWithKeys(function ($item) {
                                return [$item->handyman_id => optional($item->handyman)->display_name ?? 'N/A'];
                            })->toArray();
                        }
                        $existingCommission = $bookingdata->handyman_commission ?? '';
                        $defaultHandymanId  = $bookingdata->handymanAdded && $bookingdata->handymanAdded->count() > 0
                            ? $bookingdata->handymanAdded->first()->handyman_id : null;
                    @endphp

                    {{ html()->select('handyman_id[]', $assigned_handyman ? array_keys($assigned_handyman) : [], $assigned_handyman)
                        ->class('select2js form-group')
                        ->id('handyman_id')
                        ->required()
                        ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.handyman')]))
                        ->attribute('data-ajax--url', $route) }}
                </div>
            </div>

            {{-- Per-booking commission --}}
            <div class="row mt-3">
                <div class="col-md-12 form-group">
                    <label class="form-control-label" for="booking_handyman_commission">
                        {{ __('messages.handyman_commission') }} (%) <span class="text-danger">*</span>
                        <small class="text-muted ms-1">— {{ __('messages.booking_commission_hint') ?? 'overrides handyman default for this booking only' }}</small>
                    </label>
                    <div class="input-group">
                        <input type="number"
                               id="booking_handyman_commission"
                               name="handyman_commission"
                               class="form-control"
                               min="1" max="99" step="0.01"
                               value="{{ $existingCommission }}"
                               placeholder="{{ __('messages.handyman_commission') }}"
                               required>
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted" id="commission_hint"></small>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-md btn-secondary" data-dismiss="modal">{{ trans('messages.close') }}</button>
            <button type="submit" class="btn btn-md btn-primary" id="btn_submit" data-form="ajax">{{ trans('messages.save') }}</button>
        </div>
        {{ html()->form()->close() }}
    </div>
</div>

<script>
(function () {
    var commissionUrl = '{{ route("handyman.commission", ["id" => "__ID__"]) }}';
    var $field = $('#booking_handyman_commission');
    var $hint  = $('#commission_hint');
    var userEdited = false; // track if provider manually changed the value

    function fetchCommission(handymanId) {
        if (!handymanId) return;
        var url = commissionUrl.replace('__ID__', handymanId);
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var pct = parseFloat(data.commission) || 0;
                // Always update field unless provider already typed a custom value
                if (!userEdited) {
                    $field.val(pct > 0 ? pct : '');
                }
                $hint.text('{{ __("messages.handyman") }} {{ __("messages.handyman_commission") }}: ' + pct + '%');
            })
            .catch(function () { $hint.text(''); });
    }

    // Mark as user-edited only when they actually type
    $field.on('input', function () { userEdited = true; });

    // Re-init select2 on the modal element (modal is loaded via AJAX so parent init missed it)
    var $select = $('#handyman_id');
    if ($select.hasClass('select2-hidden-accessible')) {
        $select.select2('destroy');
    }
    $select.select2({
        width: '100%',
        placeholder: "{{ __('messages.select_name', ['select' => __('messages.handyman')]) }}"
        // data-ajax--url attribute on the element is picked up automatically by select2
    });

    // Fire when a handyman is selected from the dropdown
    $select.on('select2:select', function (e) {
        var id = e.params && e.params.data ? e.params.data.id : $(this).val();
        if (Array.isArray(id)) id = id[0];
        userEdited = false; // reset so new handyman always fills the field
        fetchCommission(id);
    });

    // On load: if editing (handyman already assigned and no booking commission yet)
    @if($defaultHandymanId && $existingCommission === '')
    fetchCommission({{ $defaultHandymanId }});
    @endif
})();
</script>
