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
    var commissionUrl = '{{ url("handyman") }}';
    var $select      = $('#handyman_id');
    var $field       = $('#booking_handyman_commission');
    var $hint        = $('#commission_hint');

    // Init select2 for the handyman dropdown
    $select.select2({ width: '100%', placeholder: "{{ __('messages.select_name', ['select' => __('messages.handyman')]) }}" });

    // Auto-fill commission when a handyman is chosen
    $select.on('select2:select', function () {
        var handymanId = $(this).val();
        if (!handymanId || !handymanId[0]) return;

        fetch(commissionUrl + '/' + handymanId[0] + '/commission')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if ($field.val() === '') {
                    $field.val(data.commission);
                }
                $hint.text('Default commission for this freelancer: ' + data.commission + '%');
            })
            .catch(function () { $hint.text(''); });
    });

    // If editing (handyman already assigned), auto-fill on load if field is empty
    @if($defaultHandymanId && $existingCommission === '')
    fetch(commissionUrl + '/{{ $defaultHandymanId }}/commission')
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if ($field.val() === '') {
                $field.val(data.commission);
                $hint.text('Default commission for this freelancer: ' + data.commission + '%');
            }
        });
    @endif
})();
</script>
