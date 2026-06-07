<x-master-layout>
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <style>
        .ql-editor { min-height: 80px; }
        /* Add spacing so Quill editors don't touch adjacent rows */
        .ql-container { margin-bottom: 1rem; }
        /* Ensure spacing even when wrapped in .form-group */
        .quill-group { margin-bottom: 5.25rem; }
    </style>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="fw-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                            <a href="{{ route('service.index') }}" class=" float-end btn btn-sm btn-primary"><i
                                    class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                            @if ($auth_user->can('service list'))
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ html()->form('POST', route('service.store'))->attribute('enctype', 'multipart/form-data')->attribute('data-toggle', 'validator')->id('service')->open() }}
                        {{ html()->hidden('id', $servicedata->id ?? null) }}

                        <div class="row">
                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.name') . ' <span class="text-danger">*</span>', 'name')->class('form-control-label') }}
                                {{ html()->text('name', old('name', $servicedata->name))->placeholder(__('messages.name'))->class('form-control')->required()->attributes(['title' => 'Please enter alphabetic characters and spaces only']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.category')]) . ' <span class="text-danger">*</span>', 'name')->class('form-control-label') }}
                                <br />
                                {{ html()->select(
                                        'category_id',
                                        [optional($servicedata->category)->id => optional($servicedata->category)->name],
                                        old('category_id', optional($servicedata->category)->id),
                                    )->class('select2js form-group category')->required()->id('category_id')->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.category')]))->attribute('data-ajax--url', route('ajax-list', ['type' => 'category'])) }}

                            </div>
                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.subcategory')]) . ' <span class="text-danger">*</span>', 'subcategory_id')->class('form-control-label') }}
                                <br />
                                {{ html()->select('subcategory_id', [])->class('select2js form-group subcategory_id')->required()->id('subcategory_id')->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.subcategory')])) }}
                            </div>



                            <div class="col-md-3">
                                <label
                                    for="country_id">{{ __('messages.select_name', ['select' => __('messages.country')]) }} <span class="text-danger">*</span></label>
                                <br />
                                <select name="country_id" id="country_id" class="select2js country" required
                                    data-placeholder="{{ __('messages.select_name', ['select' => __('messages.country')]) }}"
                                    data-ajax--url="{{ route('ajax-list', ['type' => 'country']) }}">
                                    <option value="{{ optional($servicedata->country)->id }}" selected>
                                        {{ optional($servicedata->country)->name }}
                                    </option>
                                </select>
                            </div>
                            <div class=" col-md-3">
                                <label
                                    for="country_id">{{ __('messages.select_name', ['select' => __('messages.tax_country')]) }}</label>
                                {{ html()->select(
                                        'tax_country_id_display',
                                        optional($servicedata->tax_country)
                                            ? [optional($servicedata->tax_country)->id => optional($servicedata->tax_country)->name]
                                            : [],
                                    )->class('form-group select2js tax_country')->attribute('data-placeholder', __('messages.select_name', ['select' => __('Tax Country')]))->attribute('data-ajax--url', route('ajax-list', ['type' => 'country']))->attribute('disabled', true)->id('tax_country_id_display') }}
                            </div>

                            <div class="form-group col-md-3">
                                <label
                                    for="state_id">{{ __('messages.select_name', ['select' => __('messages.state')]) }} <span class="text-danger">*</span></label>
                                <select name="state_id" id="state_id" class="select2js form-group category" required
                                    data-placeholder="{{ __('messages.select_name', ['select' => __('messages.state')]) }}">
                                    <!-- State options will be populated dynamically -->
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label
                                    for="city_id">{{ __('messages.select_name', ['select' => __('messages.city')]) }} <span class="text-danger">*</span></label>
                                <select name="city_id" id="city_id" class="select2js form-group category" required
                                    data-placeholder="{{ __('messages.select_name', ['select' => __('messages.city')]) }}">
                                    <!-- City options will be populated dynamically -->
                                </select>
                            </div>


                            <input type="hidden" name="tax_country_id" id="tax_country_id"
                                value="{{ old('tax_country_id', $servicedata->tax_country_id) }}">






                            @if (auth()->user()->hasAnyRole(['admin', 'demo_admin']))
                                <div class="form-group col-md-3">
                                    {{ html()->label(__('messages.select_name', ['select' => __('messages.provider')]) . ' <span class="text-danger">*</span>', 'name')->class('form-control-label') }}
                                    <br />
                                    {{ html()->select(
                                            'provider_id',
                                            [optional($servicedata->providers)->id => optional($servicedata->providers)->display_name],
                                            old('provider_id', optional($servicedata->providers)->id),
                                        )->class('select2js form-group')->id('provider_id')->attribute('onchange', 'selectprovider(this)')->required()->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.provider')]))->attribute('data-ajax--url', route('ajax-list', ['type' => 'provider'])) }}
                                </div>
                            @endif
                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.provider_address')]) . ' <span class="text-danger">*</span>', 'provider_address_id')->class('form-control-label') }}
                                <br />
                                {{ html()->select('provider_address_id[]', [], old('provider_address_id'))->class('select2js form-group provider_address_id')->id('provider_address_id')->multiple()->required()->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.provider_address')])) }}

                                @if (auth()->user()->hasAnyRole(['provider']))
                                    <a href="{{ route('provideraddress.create', ['provideraddress' => auth()->id()]) }}"
                                        id="add_provider_address_link" class=""><i
                                            class="fa fa-plus-circle mt-2"></i>
                                        {{ trans('messages.add_form_title', ['form' => trans('messages.provider_address')]) }}</a>
                                @else
                                    <a href="{{ route('provideraddress.create', ['provideraddress' => auth()->id()]) }}"
                                        id="add_provider_address_link" class=""><i
                                            class="fa fa-plus-circle mt-2"></i>
                                        {{ trans('messages.add_form_title', ['form' => trans('messages.provider_address')]) }}</a>
                                @endif
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.price_type') . ' <span class="text-danger">*</span>', 'type')->class('form-control-label') }}
                                {{ html()->select(
                                        'type',
                                        [
                                            'fixed' => __('messages.fixed'),
                                            'hourly' => __('messages.hourly'),
                                            'Daily' => __('messages.daily'),
                                        ],
                                        old('type', $servicedata->type),
                                    )->class('form-control select2js')->required()->id('price_type') }}
                            </div>
                            <div class="form-group col-md-3" id="price_div">
                                {{ html()->label(__('messages.price') . ' <span class="text-danger">*</span>', 'price')->class('form-control-label') }}
                                {{ html()->text('price', old('price', $servicedata->price))->attributes(['min' => 1, 'step' => 'any', 'pattern' => '^\\d+(\\.\\d{1,2})?$'])->placeholder(__('messages.price'))->class('form-control')->required()->id('price') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>


                            <div class="form-group col-md-3" id="minimum_booking_div">
                                {{ html()->label(__('Minimum Booking'), 'minimum_booking')->class('form-control-label') }}
                                {{ html()->text('minimum_booking', old('minimum_booking', $servicedata->minimum_booking))->placeholder(__('minimum booking'))->class('form-control')->required()->id('minimum_booking') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>


                            <div class="form-group col-md-3" id="discount_div">
                                {{ html()->label(__('messages.discount') . ' %', 'discount')->class('form-control-label') }}
                                {{ html()->number('discount', old('discount', $servicedata->discount))->attributes(['min' => 0, 'max' => 99, 'step' => 'any'])->placeholder(__('messages.discount'))->class('form-control')->id('discount') }}

                                <span id="discount-error" class="text-danger"></span>
                            </div>


                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.duration') . ' (hours) <span class="text-danger">*</span>', 'duration')->class('form-control-label') }}
                                {{ html()->text('duration', old('duration', $servicedata->duration))->placeholder(__('messages.duration_placeholder'))->class('form-control duration-input')->id('duration')->required()->attribute('autocomplete', 'off') }}
                                <small class="help-block with-errors text-danger"></small>
                                <small id="duration-error" class="text-danger"></small>
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.status') . ' <span class="text-danger">*</span>', 'status')->class('form-control-label') }}
                                {{ html()->select('status', ['1' => __('messages.active'), '0' => __('messages.inactive')], old('status', $servicedata->status))->class('form-control select2js')->required() }}
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.visit_type') . ' <span class="text-danger">*</span>', 'visit_type')->class('form-control-label') }}
                                <br />
                                {{ html()->select('visit_type', $visittype, old('visit_type', $servicedata->visit_type))->id('visit_type')->class('form-control select2js')->required() }}
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.remote_work_level') . ' <span class="text-danger">*</span>', 'remote_work_level')->class('form-control-label') }}
                                {{ html()->select(
                                        'remote_work_level',
                                        [
                                            'onsite' => __('messages.onsite_100'),
                                            '25_remote' => __('messages.remote_25'),
                                            '50_remote' => __('messages.remote_50'),
                                            '75_remote' => __('messages.remote_75'),
                                            '100_remote' => __('messages.remote_100'),
                                        ],
                                        old('remote_work_level', $servicedata->remote_work_level ?? 'onsite'),
                                    )->class('form-control select2js')->required()->id('remote_work_level') }}
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.career_level') . ' <span class="text-danger">*</span>', 'career_level')->class('form-control-label') }}
                                {{ html()->select(
                                        'career_level',
                                        [
                                            'not_specified' => __('Not Specified'),
                                            'entry_level' => __('Entry Level'),
                                            'intermediate_level' => __('Intermediate Level'),
                                            'experienced' => __('Experienced'),
                                            'professional' => __('Professional'),
                                            'middle_management' => __('Middle Management'),
                                            'executive_management' => __('Executive Management'),
                                            'senior_management' => __('Senior Management'),
                                            'director' => __('Director'),
                                            'technician' => __('Technician'),
                                            'leader' => __('Leader'),
                                            'manager' => __('Manager'),
                                        ],
                                        old('career_level', $servicedata->career_level ?? 'entry_level'),
                                    )->class('form-control select2js')->required()->id('career_level') }}
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.travel_required'), 'travel_required')->class('form-control-label') }}
                                {{ html()->select(
                                        'travel_required',
                                        [
                                            '0' => __('messages.no'),
                                            '1' => __('messages.yes'),
                                        ],
                                        old('travel_required', $servicedata->travel_required ?? '0'),
                                    )->class('form-control select2js')->required()->id('travel_required') }}
                            </div>





                            <div class="form-group col-md-3">
                                <label class="form-control-label" for="service_attachment">{{ __('messages.image') }} <span class="text-danger">*</span></label>
                                <div class="custom-file">
                                    <input type="file" onchange="handleServiceImageSelection(this)"
                                        name="service_attachment[]" class="custom-file-input"
                                        data-file-error="{{ __('messages.files_not_allowed') }}" multiple
                                        accept="image/*" @if (empty($servicedata->id)) required @endif>
                                    <label
                                        class="custom-file-label upload-label">{{ __('messages.choose_file', ['file' => __('messages.attachments')]) }}</label>
                                </div>
                                <small id="service-image-prepare-status" class="d-none mt-1 text-primary fw-bold"></small>
                            </div>
                        </div>


                        <div class="row service_attachment_div">
                            <div class="col-md-12">

                                @php
                                    $attchments = $servicedata->getMedia('service_attachment');
                                    $file_extention = config('constant.IMAGE_EXTENTIONS');
                                    $validAttachments = collect();

                                    if ($attchments->isNotEmpty()) {
                                        foreach ($attchments as $attchment) {
                                            if (getFileExistsCheck($attchment)) {
                                                $validAttachments->push($attchment);
                                            }
                                        }
                                    }
                                @endphp

                                <div class="border-start">
                                    <p class="ms-2"><b>{{ __('messages.attached_files') }}</b></p>
                                    <div class="ms-2 my-3">
                                        <div class="row" id="new_attachment_previews"></div>
                                        @if ($validAttachments->isNotEmpty())
                                            <div class="row">
                                                @foreach ($validAttachments as $attchment)
                                                    <?php
                                                    $extention = in_array(strtolower(imageExtention($attchment->getFullUrl())), $file_extention);
                                                    ?>
                                                    <div class="col-md-2 pe-10 text-center galary file-gallary-{{ $servicedata->id }} position-relative"
                                                        data-gallery=".file-gallary-{{ $servicedata->id }}"
                                                        id="service_attachment_preview_{{ $attchment->id }}">
                                                        @if ($extention)
                                                            <a id="attachment_files"
                                                                href="{{ $attchment->getFullUrl() }}"
                                                                class="list-group-item-action attachment-list"
                                                                target="_blank">
                                                                <img src="{{ $attchment->getFullUrl() }}"
                                                                    class="attachment-image" alt="">
                                                            </a>
                                                        @else
                                                            <a id="attachment_files"
                                                                class="video list-group-item-action attachment-list"
                                                                href="{{ $attchment->getFullUrl() }}">
                                                                <img src="{{ asset('images/file.png') }}"
                                                                    class="attachment-file">
                                                            </a>
                                                        @endif
                                                        <a class="text-danger remove-file"
                                                            href="{{ route('remove.file', ['id' => $attchment->id, 'type' => 'service_attachment']) }}"
                                                            data--submit="confirm_form" data--confirmation='true'
                                                            data--ajax="true" data-toggle="tooltip"
                                                            title='{{ __('messages.remove_file_title', ['name' => __('messages.attachments')]) }}'
                                                            data-title='{{ __('messages.remove_file_title', ['name' => __('messages.attachments')]) }}'
                                                            data-message='{{ __('messages.remove_file_msg') }}'>
                                                            <i class="ri-close-circle-line"></i>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 quill-group">
                                {{ html()->label(__('messages.description') . ' <span class="text-danger">*</span>', 'description')->class('form-control-label') }}
                                {{ html()->textarea('description', old('description', $servicedata->description))->class('form-control textarea js-richtext')->rows(3)->placeholder(__('messages.description'))->id('description')->attribute('data-required', '1') }}
                            </div>
                            <div class="form-group col-md-6 quill-group">
                                {{ html()->label(__('Cancellation Policy & Fees') . ' <span class="text-danger">*</span>', 'cancellation_policy')->class('form-control-label') }}
                                {{ html()->textarea('cancellation_policy', old('cancellation_policy', $servicedata->cancellation_policy))->class('form-control textarea js-richtext')->rows(3)->placeholder(__('cancellation_policy'))->id('cancellation_policy') }}
                            </div>
                        </div>
                        <div class="row">
                            
                            @if (!empty($slotservice) && $slotservice == 1)
                                <div class="form-group col-md-3">
                                    <div class="custom-control custom-switch">
                                        {{ html()->checkbox('is_slot', old('is_slot', $servicedata->is_slot))->class('custom-control-input')->id('is_slot') }}
                                        <label class="custom-control-label"
                                            for="is_slot">{{ __('messages.slot') }}</label>
                                    </div>
                                </div>
                            @endif
                            @if (auth()->check() && auth()->user()->hasRole(['admin', 'demo_admin']))
                                <div class="form-group col-md-3">
                                    <div class="custom-control custom-switch">
                                        {{ html()->checkbox('is_featured', old('is_featured', $servicedata->is_featured))->class('custom-control-input')->id('is_featured') }}
                                        <label class="custom-control-label"
                                            for="is_featured">{{ __('messages.set_as_featured') }}</label>
                                    </div>
                                </div>
                            @endif
                            <!-- @if (!empty($digitalservicedata) && $digitalservicedata->value == 1)
<div class="form-group col-md-3">
                                <div class="custom-control custom-switch">
                                    {{ Form::checkbox('digital_service', $servicedata->digital_service, null, ['class' => 'custom-control-input', 'id' => 'digital_service']) }}
                                    <label class="custom-control-label"
                                        for="digital_service">{{ __('messages.digital_service') }}</label>
                                </div>
                            </div>
@endif -->
                            {{-- @if (!empty($advancedPaymentSetting) && $advancedPaymentSetting == 1) --}}
                            <div class="form-group col-md-3">
                                <div class="custom-control custom-switch">
                                    {{ html()->checkbox('is_enable_advance_payment', old('is_enable_advance_payment', $servicedata->is_enable_advance_payment))->class('custom-control-input')->id('is_enable_advance_payment') }}
                                    <label class="custom-control-label"
                                        for="is_enable_advance_payment">{{ __('messages.enable_advanced_payment') }}
                                    </label>
                                </div>
                            </div>
                            {{-- @endif --}}
                            <div class="form-group col-md-3" id="amount">
                                {{ html()->label(__('messages.advance_payment_amount') . ' (%) <span class="text-danger">*</span>', 'advance_payment_amount')->class('form-control-label') }}
                                {{ html()->number('advance_payment_amount', $servicedata->advance_payment_amount)->placeholder(__('messages.amount'))->class('form-control')->id('advance_payment_amount')->attributes(['min' => 20, 'max' => 99]) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                        </div>

                        <button type="submit" id="service-submit-button" class="btn btn-md btn-primary float-end" data-default-text="{{ __('messages.publish') }}">
                            {{ __('messages.publish') }}
                        </button>
                        {{ html()->form()->close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @php
        $data = old('provider_address_id')
            ? implode(',', (array) old('provider_address_id'))
            : $servicedata->providerServiceAddress->pluck('provider_address_id')->implode(',');
    @endphp
    @section('bottom_script')
        <script type="text/javascript">
            var discountInput = document.getElementById('discount');
            var discountError = document.getElementById('discount-error');


            document.addEventListener('DOMContentLoaded', function() {
                var initialProviderId = document.getElementById('provider_id').value;
                selectprovider({
                    value: initialProviderId
                });
                document.getElementById('add_provider_address_link').addEventListener('click', function(event) {
                    event.preventDefault();
                    var providerId = document.getElementById('provider_id').value;
                    var providerAddressCreateUrl =
                        "{{ route('provideraddress.create', ['provideraddress' => '']) }}";
                    providerAddressCreateUrl = providerAddressCreateUrl.replace('provideraddress=',
                        'provideraddress=' + providerId);
                    window.location.href = providerAddressCreateUrl;
                });





            });

            function selectprovider(selectElement) {

                var providerId = selectElement.value;
                var addProviderAddressLink = document.getElementById('add_provider_address_link');

                if (providerId) {
                    addProviderAddressLink.classList.remove('d-none');
                } else {
                    addProviderAddressLink.classList.add('d-none');
                }
            }


            discountInput.addEventListener('input', function() {
                var discountValue = parseFloat(discountInput.value);
                if (isNaN(discountValue) || discountValue < 0 || discountValue > 99) {
                    discountError.textContent = "{{ __('messages.discount_value_between_0_99') }}";
                } else {
                    discountError.textContent = "";
                }
            });

            var isEnableAdvancePayment = $("input[name='is_enable_advance_payment']").prop('checked');

            var priceType = $("#price_type").val();

            enableAdvancePayment(priceType);
            checkEnablePayment(isEnableAdvancePayment);

            $("#is_enable_advance_payment").change(function() {
                isEnableAdvancePayment = $(this).prop('checked');
                checkEnablePayment(isEnableAdvancePayment);
                updateAmountVisibility(priceType, isEnableAdvancePayment);
            });

            $("#price_type").change(function() {
                priceType = $(this).val();
                enableAdvancePayment(priceType);
                updateAmountVisibility(priceType, isEnableAdvancePayment);
            });

            function checkEnablePayment(value) {
                $("#amount").toggleClass('d-none', !value);
                $('#advance_payment_amount').prop('required', value);
            }

            function enableAdvancePayment(type) {
                $("#is_enable_advance").toggleClass('d-none', !(type === 'fixed' || type === 'hourly' || type.toLowerCase() ===
                    'daily'));
            }


            function updateAmountVisibility(type, isEnableAdvancePayment) {
                const allowedTypes = ['fixed', 'hourly', 'daily'];
                const typeLower = type.toLowerCase();

                if (allowedTypes.includes(typeLower) && !$("#is_enable_advance").hasClass('d-none') && isEnableAdvancePayment) {
                    $("#amount").removeClass('d-none');
                } else {
                    $("#amount").addClass('d-none');
                }
            }

            (function($) {
                "use strict";
                $(document).ready(function() {
                    var provider_id =
                        "{{ old('provider_id', isset($servicedata->provider_id) ? $servicedata->provider_id : '') }}";
                    var provider_address_id = "{{ isset($data) ? $data : [] }}";

                    var category_id =
                        "{{ old('category_id', isset($servicedata->category_id) ? $servicedata->category_id : '') }}";
                    var subcategory_id =
                        "{{ old('subcategory_id', isset($servicedata->subcategory_id) ? $servicedata->subcategory_id : '') }}";

                    var country_id =
                        "{{ old('country_id', isset($servicedata->country_id) ? $servicedata->country_id : 0) }}";
                    var city_id = "{{ old('city_id', isset($servicedata->city_id) ? $servicedata->city_id : 0) }}";
                    var price_type = "{{ old('type', isset($servicedata->type) ? $servicedata->type : '') }}";

                    providerAddress(provider_id, provider_address_id)
                    getSubCategory(category_id, subcategory_id)
                    priceformat(price_type)

                    $(document).on('change', '#provider_id', function() {
                        var provider_id = $(this).val();
                        $('#provider_address_id').empty();
                        providerAddress(provider_id, provider_address_id);
                    })
                    $(document).on('change', '#category_id', function() {
                        var category_id = $(this).val();
                        $('#subcategory_id').empty();
                        getSubCategory(category_id, subcategory_id);
                    })
                    $(document).on('change', '#price_type', function() {
                        var price_type = $(this).val();
                        priceformat(price_type);
                    })



                    $(document).on('change', '#city_id', function() {
                        var city = $(this).val();
                        console.log('selected city', city);
                    })

                    $('.galary').each(function(index, value) {
                        let galleryClass = $(value).attr('data-gallery');
                        $(galleryClass).magnificPopup({
                            delegate: 'a#attachment_files',
                            type: 'image',
                            gallery: {
                                enabled: true,
                                navigateByImgClick: true,
                                preload: [0,
                                    1
                                ] // Will preload 0 - before current, and 1 after the current image
                            },
                            callbacks: {
                                elementParse: function(item) {
                                    if (item.el[0].className.includes('video')) {
                                        item.type = 'iframe',
                                            item.iframe = {
                                                markup: '<div class="mfp-iframe-scaler">' +
                                                    '<div class="mfp-close"></div>' +
                                                    '<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>' +
                                                    '<div class="mfp-title">Some caption</div>' +
                                                    '</div>'
                                            }
                                    } else {
                                        item.type = 'image',
                                            item.tLoading = 'Loading image #%curr%...',
                                            item.mainClass = 'mfp-img-mobile',
                                            item.image = {
                                                tError: '<a href="%url%">The image #%curr%</a> could not be loaded.'
                                            }
                                    }
                                }
                            }
                        })
                    })
                })

                function providerAddress(provider_id, provider_address_id = "") {
                    var provider_address_route =
                        "{{ route('ajax-list', ['type' => 'provider_address', 'provider_id' => '']) }}" + provider_id;
                    provider_address_route = provider_address_route.replace('amp;', '');

                    $.ajax({
                        url: provider_address_route,
                        success: function(result) {
                            $('#provider_address_id').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name', ['select' => trans('messages.provider_address')]) }}",
                                data: result.results
                            });
                            if (provider_address_id != "") {
                                $('#provider_address_id').val(provider_address_id.split(',')).trigger('change');
                            }
                        }
                    });
                }

                function getSubCategory(category_id, subcategory_id = "") {
                    var get_subcategory_list =
                        "{{ route('ajax-list', ['type' => 'subcategory_list', 'category_id' => '']) }}" + category_id;
                    get_subcategory_list = get_subcategory_list.replace('amp;', '');

                    $.ajax({
                        url: get_subcategory_list,
                        success: function(result) {
                            $('#subcategory_id').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name', ['select' => trans('messages.subcategory')]) }}",
                                data: result.results
                            });
                            if (subcategory_id != "") {
                                $('#subcategory_id').val(subcategory_id).trigger('change');
                            }
                        }
                    });
                }
                var price = "{{ old('price', isset($servicedata->price) ? $servicedata->price : '') }}";
                var discount = "{{ old('discount', isset($servicedata->discount) ? $servicedata->discount : '') }}";

                function priceformat(value) {
                    $('#price').val(price);
                    $('#price').attr("readonly", false)
                    $('#discount').val(discount);
                    $('#discount').attr("readonly", false)
                }

                function cityName(country, city = "") {
                    var city_route = "{{ route('ajax-list', ['type' => 'cityFromCountry', 'country_id' => '']) }}" +
                        country;
                    city_route = city_route.replace('amp;', '');

                    $('#city_id').select2({
                        width: '100%',
                        placeholder: "{{ __('messages.select_name', ['select' => __('messages.city')]) }}",
                    });

                    $.ajax({
                        url: city_route,
                        success: function(result) {
                            // Clear existing options
                            $('#city_id').empty();

                            // Append new options based on the result
                            result.forEach(function(city) {
                                var option = new Option(city.name, city.id, false, false);
                                $('#city_id').append(option);
                            });

                            // If a specific city is selected, set it as the default value
                            if (city !== null && city !== 0) {
                                $("#city_id").val(city).trigger('change');
                            }
                        }
                    });
                }
            })(jQuery);
        </script>

        <script>
            function handleServiceImageSelection(input) {
                previewSelectedImages(input).catch(function(error) {
                    console.error('Service image preparation failed:', error);
                    setServiceImagesPreparing(false);
                    if (typeof Snackbar !== 'undefined') {
                        Snackbar.show({ text: 'Image preparation failed. Please try another image.', pos: 'bottom-center', backgroundColor: '#d32f2f', actionTextColor: '#fff' });
                    } else {
                        alert('Image preparation failed. Please try another image.');
                    }
                });
            }

            async function previewSelectedImages(input) {
                const previewRow = document.getElementById('new_attachment_previews');
                if (!previewRow) return;
                previewRow.innerHTML = '';
                setServiceImagesPreparing(true);

                try {
                    if (input.files && input.files.length) {
                        const dataTransfer = new DataTransfer();
                        const files = Array.from(input.files);
                        const maxOriginalSize = 4 * 1024 * 1024;
                        const rejectedFiles = [];
                        updateServiceImagePrepareStatus('Preparing 0 of ' + files.length + ' images...');

                        for (let index = 0; index < files.length; index++) {
                            const file = files[index];
                            updateServiceImagePrepareStatus('Preparing ' + (index + 1) + ' of ' + files.length + ' images...');

                            if (file.size > maxOriginalSize) {
                                rejectedFiles.push(file.name);
                                continue;
                            }

                            const preparedFile = await prepareServiceImageForUpload(file);
                            dataTransfer.items.add(preparedFile);

                            if (preparedFile.type && preparedFile.type.startsWith('image/')) {
                                const objectUrl = URL.createObjectURL(preparedFile);
                                const col = document.createElement('div');
                                col.className = 'col-md-2 text-center';
                                col.innerHTML = '<img src="' + objectUrl + '" class="attachment-image" alt="">';
                                previewRow.appendChild(col);

                                col.querySelector('img').addEventListener('load', function() {
                                    URL.revokeObjectURL(objectUrl);
                                }, { once: true });
                            }
                        }

                        input.files = dataTransfer.files;
                        updateServiceImagePrepareStatus(input.files.length + ' image' + (input.files.length === 1 ? '' : 's') + ' ready for upload.');

                        if (rejectedFiles.length) {
                            if (typeof Snackbar !== 'undefined') {
                                Snackbar.show({ text: 'Each image must be 4 MB or smaller.', pos: 'bottom-center', backgroundColor: '#d32f2f', actionTextColor: '#fff' });
                            } else {
                                alert('Each image must be 4 MB or smaller.');
                            }
                        }
                    }
                } finally {
                    setServiceImagesPreparing(false);
                }
            }

            function setServiceImagesPreparing(isPreparing) {
                window.serviceImagesPreparing = isPreparing;
                const status = document.getElementById('service-image-prepare-status');

                const submitButton = document.getElementById('service-submit-button');
                if (!submitButton) {
                    if (status && !isPreparing && !status.textContent) status.classList.add('d-none');
                    return;
                }

                if (!submitButton.dataset.defaultText) {
                    submitButton.dataset.defaultText = submitButton.value || submitButton.textContent || 'Publish';
                }

                submitButton.disabled = isPreparing;
                submitButton.classList.toggle('disabled', isPreparing);
                submitButton.classList.toggle('btn-secondary', isPreparing);
                submitButton.classList.toggle('btn-primary', !isPreparing);

                if (submitButton.tagName === 'INPUT') {
                    submitButton.value = isPreparing ? 'Preparing images...' : submitButton.dataset.defaultText;
                } else {
                    submitButton.textContent = isPreparing ? 'Preparing images...' : submitButton.dataset.defaultText;
                }
            }

            function updateServiceImagePrepareStatus(message) {
                const status = document.getElementById('service-image-prepare-status');
                if (!status) return;

                status.textContent = message;
                status.classList.toggle('d-none', !message);
            }

            function prepareServiceImageForUpload(file) {
                const maxSize = 250 * 1024;
                const maxDimension = 1000;

                if (!file.type || !file.type.startsWith('image/')) {
                    return Promise.resolve(file);
                }

                return new Promise(function(resolve) {
                    const image = new Image();
                    const objectUrl = URL.createObjectURL(file);

                    image.onload = function() {
                        URL.revokeObjectURL(objectUrl);

                        if (file.size <= maxSize && Math.max(image.width, image.height) <= maxDimension) {
                            resolve(file);
                            return;
                        }

                        const ratio = Math.min(maxDimension / image.width, maxDimension / image.height, 1);
                        const canvas = document.createElement('canvas');
                        canvas.width = Math.max(1, Math.round(image.width * ratio));
                        canvas.height = Math.max(1, Math.round(image.height * ratio));

                        const context = canvas.getContext('2d');
                        context.drawImage(image, 0, 0, canvas.width, canvas.height);

                        compressServiceImage(canvas, file, [0.72, 0.62, 0.52, 0.44], maxSize, resolve);
                    };

                    image.onerror = function() {
                        URL.revokeObjectURL(objectUrl);
                        resolve(file);
                    };

                    image.src = objectUrl;
                });
            }

            function compressServiceImage(canvas, originalFile, qualities, maxSize, resolve) {
                const quality = qualities.shift() || 0.44;

                canvas.toBlob(function(blob) {
                    if (!blob) {
                        resolve(originalFile);
                        return;
                    }

                    if (blob.size > maxSize && qualities.length) {
                        compressServiceImage(canvas, originalFile, qualities, maxSize, resolve);
                        return;
                    }

                    const safeName = originalFile.name.replace(/\.[^.]+$/, '') + '.jpg';
                    resolve(new File([blob], safeName, {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    }));
                }, 'image/jpeg', quality);
            }
        </script>

        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                var $duration = $('#duration');
                function getPriceType() {
                    var v = $("#price_type").val();
                    return (v && typeof v === 'string') ? v.trim() : (v || '');
                }
                // Initialize duration from current price type (hourly -> 1:00 readonly, daily -> 8:00 readonly, fixed -> editable)
                var initialType = getPriceType();
                handleDurationField(initialType);
                addDurationValidation();

                $('#service').on('submit', function(e) {
                    if (window.serviceImagesPreparing) {
                        if (typeof Snackbar !== 'undefined') {
                            Snackbar.show({ text: 'Please wait, images are preparing for upload.', pos: 'bottom-center', backgroundColor: '#d32f2f', actionTextColor: '#fff' });
                        } else {
                            alert('Please wait, images are preparing for upload.');
                        }
                        e.preventDefault();
                        return false;
                    }

                    // Required field validation (all except Discount %)
                    var $form = $(this);
                    var requiredFields = [
                        { id: 'name', name: '{{ __("messages.name") }}' },
                        { id: 'category_id', name: '{{ __("messages.select_name", ["select" => __("messages.category")]) }}' },
                        { id: 'subcategory_id', name: '{{ __("messages.select_name", ["select" => __("messages.subcategory")]) }}' },
                        { id: 'country_id', name: '{{ __("messages.select_name", ["select" => __("messages.country")]) }}' },
                        { id: 'state_id', name: '{{ __("messages.select_name", ["select" => __("messages.state")]) }}' },
                        { id: 'city_id', name: '{{ __("messages.select_name", ["select" => __("messages.city")]) }}' },
                        { id: 'provider_address_id', name: '{{ __("messages.select_name", ["select" => __("messages.provider_address")]) }}', isMulti: true },
                        { id: 'price_type', name: '{{ __("messages.price_type") }}' },
                        { id: 'price', name: '{{ __("messages.price") }}' },
                        { id: 'minimum_booking', name: '{{ __("messages.minimum_booking") }}' },
                        { id: 'duration', name: '{{ __("messages.duration") }}' },
                        { id: 'status', name: '{{ __("messages.status") }}' },
                        { id: 'visit_type', name: '{{ __("messages.visit_type") }}' },
{ id: 'remote_work_level', name: '{{ __("messages.remote_work_level") }}' },
                                    { id: 'career_level', name: '{{ __("messages.career_level") }}' },
                                    { id: 'travel_required', name: '{{ __("messages.travel_required") }}' }
                    ];
                    if ($('#provider_id').length && $('#provider_id').is(':visible')) {
                        requiredFields.push({ id: 'provider_id', name: '{{ __("messages.select_name", ["select" => __("messages.provider")]) }}' });
                    }
                    for (var i = 0; i < requiredFields.length; i++) {
                        var f = requiredFields[i];
                        var $el = $('#' + f.id);
                        if (!$el.length) continue;
                        var val = $el.val();
                        if (f.isMulti) {
                            val = $el.val();
                            if (!val || (Array.isArray(val) && val.length === 0)) {
                                if (typeof Snackbar !== 'undefined') {
                                    Snackbar.show({ text: f.name + ' {{ __("messages.is_required") }}', pos: 'bottom-center', backgroundColor: '#d32f2f', actionTextColor: '#fff' });
                                } else {
                                    alert(f.name + ' {{ __("messages.is_required") }}');
                                }
                                $el.select2('open');
                                e.preventDefault();
                                return false;
                            }
                        } else if (val === '' || val === null || val === undefined) {
                            if (typeof Snackbar !== 'undefined') {
                                Snackbar.show({ text: f.name + ' {{ __("messages.is_required") }}', pos: 'bottom-center', backgroundColor: '#d32f2f', actionTextColor: '#fff' });
                            } else {
                                alert(f.name + ' {{ __("messages.is_required") }}');
                            }
                            $el.focus();
                            e.preventDefault();
                            return false;
                        }
                    }
                    var descEl = document.querySelector('#description_quill .ql-editor');
                    var descVal = descEl ? (descEl.innerHTML || '') : ($('#description').val() || '');
                    if (typeof descVal === 'string') {
                        var descStrip = descVal.replace(/<[^>]+>/g, '').trim();
                        if (descStrip === '') {
                            if (typeof Snackbar !== 'undefined') {
                                Snackbar.show({ text: '{{ __("messages.description") }} {{ __("messages.is_required") }}', pos: 'bottom-center', backgroundColor: '#d32f2f', actionTextColor: '#fff' });
                            } else {
                                alert('{{ __("messages.description") }} {{ __("messages.is_required") }}');
                            }
                            var q = document.querySelector('#description_quill');
                            if (q) q.scrollIntoView();
                            e.preventDefault();
                            return false;
                        }
                    }
                    var cancelEl = document.querySelector('#cancellation_policy_quill .ql-editor');
                    var cancelVal = cancelEl ? (cancelEl.innerHTML || '') : ($('#cancellation_policy').val() || '');
                    if (typeof cancelVal === 'string') {
                        var cancelStrip = cancelVal.replace(/<[^>]+>/g, '').trim();
                        if (cancelStrip === '') {
                            if (typeof Snackbar !== 'undefined') {
Snackbar.show({ text: '{{ __("messages.cancellation_policy_fees") }} {{ __("messages.is_required") }}', pos: 'bottom-center', backgroundColor: '#d32f2f', actionTextColor: '#fff' });
                                } else {
                                alert('{{ __("messages.cancellation_policy_fees") }} {{ __("messages.is_required") }}');
                            }
                            var q2 = document.querySelector('#cancellation_policy_quill');
                            if (q2) q2.scrollIntoView();
                            e.preventDefault();
                            return false;
                        }
                    }
                    if ($('#amount').length && $('#amount').is(':visible') && $('#advance_payment_amount').prop('required')) {
                        var adv = $('#advance_payment_amount').val();
                        if (adv === '' || isNaN(parseFloat(adv))) {
                            if (typeof Snackbar !== 'undefined') {
                                Snackbar.show({ text: '{{ __("messages.advance_payment_amount") }} {{ __("messages.is_required") }}', pos: 'bottom-center', backgroundColor: '#d32f2f', actionTextColor: '#fff' });
                            } else {
                                alert('{{ __("messages.advance_payment_amount") }} {{ __("messages.is_required") }}');
                            }
                            $('#advance_payment_amount').focus();
                            e.preventDefault();
                            return false;
                        }
                        var advNum = parseInt(adv, 10);
                        if (advNum < 20 || advNum > 99) {
                            if (typeof Snackbar !== 'undefined') {
Snackbar.show({ text: '{{ __("messages.advance_payment_amount") }} {{ __("messages.advance_payment_amount_between_20_99") }}', pos: 'bottom-center', backgroundColor: '#d32f2f', actionTextColor: '#fff' });
                                } else {
                                alert('{{ __("messages.advance_payment_amount") }} {{ __("messages.advance_payment_amount_between_20_99") }}');
                            }
                            $('#advance_payment_amount').focus();
                            e.preventDefault();
                            return false;
                        }
                    }
                    var priceType = $('#price_type').val();
                    if (priceType && priceType.toLowerCase() === 'fixed') {
                        var durationInput = document.getElementById('duration');
                        if (durationInput && !durationInput.readOnly && !durationInput.disabled) {
                            var v = (durationInput.value || '').trim();
                            if (v !== '') {
                                if (/[a-zA-Z]/.test(v)) {
                                    document.getElementById('duration-error').textContent = 'Do not enter text (e.g. "2 days"). Enter only a number (hours) or HH:MM (e.g. 40 or 46:30).';
                                    durationInput.focus();
                                    return false;
                                }
                                if (v.indexOf(':') !== -1) {
                                    if (!/^(\d+):([0-5]?\d)$/.test(v) || parseInt(v.split(':')[1], 10) >= 60) {
                                        document.getElementById('duration-error').textContent = 'Invalid HH:MM. Use e.g. 46:30.';
                                        durationInput.focus();
                                        return false;
                                    }
                                } else if (!/^\d+(\.\d+)?$/.test(v) || isNaN(parseFloat(v)) || parseFloat(v) < 0) {
                                    document.getElementById('duration-error').textContent = 'Enter only a number (hours) or HH:MM.';
                                    durationInput.focus();
                                    return false;
                                }
                            }
                        }
                    }
                });

                $("#price_type").on('change', function() {
                    handleDurationField($(this).val());
                });

                function handleDurationField(type) {
                    var t = (type && typeof type === 'string') ? type.trim().toLowerCase() : '';
                    // Destroy flatpickr instance if it exists
                    if ($duration.data('flatpickr')) {
                        $duration.flatpickr().destroy();
                        $duration.removeData('flatpickr');
                    }

                    if (t === 'hourly') {
                        $duration.removeClass('duration-input').addClass('min-datetimepicker-time');
                        $duration.attr('type', 'text');
                        $duration.val('1:00');
                        $duration.prop('readonly', true).prop('disabled', false).attr('readonly', 'readonly');
                        $duration.css('pointer-events', 'none').css('background-color', '#e9ecef');
                        setTimeout(function() {
                            if (!$duration.data('flatpickr')) {
                                $duration.flatpickr({
                                    enableTime: true,
                                    noCalendar: true,
                                    dateFormat: "H:i",
                                    time_24hr: true,
                                    clickOpens: false
                                });
                            }
                        }, 100);
                    } else if (t === 'daily') {
                        $duration.removeClass('duration-input').addClass('min-datetimepicker-time');
                        $duration.attr('type', 'text');
                        $duration.val('8:00');
                        $duration.prop('readonly', true).prop('disabled', false).attr('readonly', 'readonly');
                        $duration.css('pointer-events', 'none').css('background-color', '#e9ecef');
                        setTimeout(function() {
                            if (!$duration.data('flatpickr')) {
                                $duration.flatpickr({
                                    enableTime: true,
                                    noCalendar: true,
                                    dateFormat: "H:i",
                                    time_24hr: true,
                                    clickOpens: false
                                });
                            }
                        }, 100);
                    } else {
                        // fixed or any other: editable
                        $duration.removeClass('min-datetimepicker-time').addClass('duration-input');
                        $duration.attr('type', 'text');
                        $duration.removeAttr('min').removeAttr('max').removeAttr('step').removeAttr('readonly');
                        $duration.prop('readonly', false).prop('disabled', false).css('pointer-events', 'auto').css('background-color', '');
                    }
                }

                function addDurationValidation() {
                    var durationInput = document.getElementById('duration');
                    var durationError = document.getElementById('duration-error');

                    function isValidDuration(val) {
                        var v = val.trim();
                        if (v === '') return true;
                        if (/[a-zA-Z]/.test(v)) return false;
                        if (v.includes(':')) {
                            return /^(\d+):([0-5]?\d)$/.test(v) && parseInt(v.split(':')[1], 10) < 60;
                        }
                        var n = parseFloat(v);
                        return !isNaN(n) && n >= 0 && /^\d+(\.\d+)?$/.test(v);
                    }

                    function setDurationError(msg) {
                        durationError.textContent = msg || '';
                    }

                    if (durationInput) {
                        durationInput.addEventListener('input', function() {
                            var durationValue = durationInput.value.trim();
                            var priceType = $("#price_type").val();

                            if (!durationInput.readOnly && !durationInput.disabled) {
                                var isValid = false;
                                var errorMessage = "";

                                if (/[a-zA-Z]/.test(durationValue)) {
                                    errorMessage = "Do not enter text (e.g. \"2 days\"). Enter only a number (hours) or HH:MM (e.g. 40 or 46:30).";
                                } else if (durationValue.includes(':')) {
                                    var timePattern = /^(\d+):([0-5]?\d)$/;
                                    if (timePattern.test(durationValue)) {
                                        var parts = durationValue.split(':');
                                        var hours = parseInt(parts[0]);
                                        var minutes = parseInt(parts[1]);
                                        if (hours >= 0 && minutes >= 0 && minutes < 60) {
                                            isValid = true;
                                        } else {
                                            errorMessage = "Invalid time. Minutes must be 0-59. Use HH:MM (e.g., 46:30)";
                                        }
                                    } else {
                                        errorMessage = "Invalid format. Use HH:MM (e.g., 46:30)";
                                    }
                                } else if (durationValue !== '') {
                                    var numericValue = parseFloat(durationValue);
                                    if (!isNaN(numericValue) && numericValue >= 0 && /^\d+(\.\d+)?$/.test(durationValue)) {
                                        isValid = true;
                                    } else {
                                        errorMessage = "Enter only a number (hours) or HH:MM. No text.";
                                    }
                                }

                                if (priceType === 'fixed') {
                                    if (!isValid && durationValue !== '') {
                                        setDurationError(errorMessage || "Enter number (e.g. 40) or HH:MM (e.g. 46:30). Do not use text like \"2 days\".");
                                    } else {
                                        setDurationError("");
                                    }
                                }
                            } else {
                                setDurationError("");
                            }
                        });

                        durationInput.addEventListener('keypress', function(e) {
                            if (durationInput.readOnly || durationInput.disabled) return;
                            var key = e.key;
                            var val = durationInput.value;
                            if (/[a-zA-Z\s]/.test(key)) {
                                e.preventDefault();
                                return;
                            }
                            if (key === ':' && val.indexOf(':') !== -1) e.preventDefault();
                            if (key === '.' && (val.indexOf('.') !== -1 || val.indexOf(':') !== -1)) e.preventDefault();
                        });

                        durationInput.addEventListener('paste', function(e) {
                            if (durationInput.readOnly || durationInput.disabled) return;
                            setTimeout(function() {
                                var raw = durationInput.value;
                                var filtered = raw.replace(/[^0-9.:]/g, '');
                                var firstColon = filtered.indexOf(':');
                                if (firstColon !== -1) {
                                    var after = filtered.slice(firstColon + 1).replace(/:/g, '');
                                    filtered = filtered.slice(0, firstColon + 1) + after;
                                }
                                var parts = filtered.split('.');
                                if (parts.length > 2) filtered = parts[0] + '.' + parts.slice(1).join('');
                                durationInput.value = filtered;
                            }, 0);
                        });
                    }
                }
            });
        </script>

        <script type="text/javascript">
            (function($) {
                "use strict";

                $(document).ready(function() {
                    var country_id =
                        "{{ old('country_id', isset($servicedata->country_id) ? $servicedata->country_id : '') }}";
                    var tax_country_id =
                        "{{ old('tax_country_id', isset($servicedata->tax_country_id) ? $servicedata->tax_country_id : '') }}";
                    var state_id =
                        "{{ old('state_id', isset($servicedata->state_id) ? $servicedata->state_id : '') }}";
                    var city_id =
                        "{{ old('city_id', isset($servicedata->city_id) ? $servicedata->city_id : '') }}";
                    var initialCityId = city_id;
                    var category_id =
                        "{{ old('category_id', isset($servicedata->category_id) ? $servicedata->category_id : '') }}";
                    var subcategory_id =
                        "{{ old('subcategory_id', isset($servicedata->subcategory_id) ? $servicedata->subcategory_id : '') }}";

                    // Initialize select2 on all selects (including tax_country_id_display)
                    $('#country_id, #state_id, #city_id, #tax_country_id_display').select2({
                        width: '100%',
                        dropdownParent: $('body'),
                        placeholder: "{{ __('messages.select_name', ['select' => __('messages.country')]) }}"
                    });

                    // Initialize all other select2js dropdowns that aren't already initialized
                    $('.select2js').each(function() {
                        var $this = $(this);
                        var id = $this.attr('id');
                        
                        // Skip if already initialized or if it's one of the ones initialized above
                        if ($this.hasClass('select2-hidden-accessible') || 
                            ['country_id', 'state_id', 'city_id', 'tax_country_id_display'].includes(id)) {
                            return;
                        }
                        
                        // Initialize select2
                        var $ddParent = $this.closest('.modal');
                        if (!$ddParent.length) { $ddParent = $this.closest('.offcanvas'); }
                        if (!$ddParent.length) { $ddParent = $('body'); }
                        $this.select2({
                            width: '100%',
                            dropdownParent: $ddParent,
                            placeholder: $this.attr('data-placeholder') || $this.data('placeholder') || 'Select an option...'
                        });
                    });

                    // Load dependent dropdown data on page load
                    getStates(country_id, state_id);
                    getSubCategory(category_id, subcategory_id);

                    // Initialize tax_country_id_display and hidden tax_country_id inputs
                    if (tax_country_id) {
                        var taxCountryName = $('#tax_country_id_display option:selected').text();
                        setTaxCountry(tax_country_id, taxCountryName);
                    } else if (country_id) {
                        // If tax_country_id not set, default it to country_id
                        var selectedCountryName = $('#country_id option:selected').text();
                        setTaxCountry(country_id, selectedCountryName);
                    }

                    // When country changes
                    $(document).on('change', '#country_id', function() {
                        var selectedCountryId = $(this).val();
                        var selectedCountryName = $('#country_id option:selected').text();

                        // Only update tax country if not manually changed or empty
                        var currentTaxVal = $('#tax_country_id').val();
                        if (!currentTaxVal || currentTaxVal === country_id) {
                            setTaxCountry(selectedCountryId, selectedCountryName);
                        }

                        getStates(selectedCountryId, '');
                        $('#city_id').empty();

                        // Update the disabled select display for tax country
                        $('#tax_country_id_display').empty()
                            .append(new Option(selectedCountryName, selectedCountryId, true, true))
                            .trigger('change');

                        // Update hidden input value for backend submission
                        $('#tax_country_id').val(selectedCountryId).trigger('change');
                    });

                    // When state changes
                    $(document).on('change', '#state_id', function() {
                        var selectedStateId = $(this).val();
                        getCities(selectedStateId, initialCityId || '');
                        initialCityId = '';
                    });

                    // When category changes
                    $(document).on('change', '#category_id', function() {
                        var selectedCategoryId = $(this).val();
                        getSubCategory(selectedCategoryId, '');
                    });

                    // Function to set tax country (hidden input)
                    function setTaxCountry(id, name) {
                        if (!id) return;
                        // Always set hidden input value so it is submitted
                        $('#tax_country_id').val(id).trigger('change');
                        // Only update the disabled display if we have a name
                        if (name) {
                            $('#tax_country_id_display').empty()
                                .append(new Option(name, id, true, true))
                                .trigger('change');
                        }
                    }

                    // Function to get States by country
                    function getStates(country_id, selectedState = "") {
                        if (country_id !== '') {
                            var getStateListUrl =
                                "{{ route('ajax-list', ['type' => 'state', 'country_id' => '']) }}" + country_id;
                            getStateListUrl = getStateListUrl.replace('amp;', '');

                            $('#state_id').select2({
                                width: '100%',
                                placeholder: "{{ __('messages.select_name', ['select' => __('messages.state')]) }}"
                            });

                            $.ajax({
                                url: getStateListUrl,
                                success: function(result) {
                                    $('#state_id').empty();
                                    result.results.forEach(function(state) {
                                        var option = new Option(state.text, state.id, false,
                                            false);
                                        $('#state_id').append(option);
                                    });

                                    if (selectedState !== null && selectedState !== 0) {
                                        $("#state_id").val(selectedState).trigger('change');
                                    }
                                }
                            });
                        }
                    }

                    // Function to get Cities by state
                    function getCities(state_id, selectedCity = "") {
                        if (state_id !== '') {
                            var getCityListUrl =
                                "{{ route('ajax-list', ['type' => 'city', 'state_id' => '']) }}" + state_id;
                            getCityListUrl = getCityListUrl.replace('amp;', '');

                            $('#city_id').select2({
                                width: '100%',
                                placeholder: "{{ __('messages.select_name', ['select' => __('messages.city')]) }}"
                            });

                            $.ajax({
                                url: getCityListUrl,
                                success: function(result) {
                                    $('#city_id').empty();
                                    result.results.forEach(function(city) {
                                        var option = new Option(city.text, city.id, false,
                                            false);
                                        $('#city_id').append(option);
                                    });

                                    if (selectedCity !== null && selectedCity !== 0) {
                                        $("#city_id").val(selectedCity).trigger('change');
                                    }
                                }
                            });
                        }
                    }

                    // Function to get Subcategories by category
                    function getSubCategory(category_id, selectedSubCategory = "") {
                        if (category_id !== '') {
                            var getSubCategoryListUrl =
                                "{{ route('ajax-list', ['type' => 'subcategory_list', 'category_id' => '']) }}" +
                                category_id;
                            getSubCategoryListUrl = getSubCategoryListUrl.replace('amp;', '');

                            $('#subcategory_id').select2({
                                width: '100%',
                                placeholder: "{{ __('messages.select_name', ['select' => __('messages.subcategory')]) }}"
                            });

                            $.ajax({
                                url: getSubCategoryListUrl,
                                success: function(result) {
                                    $('#subcategory_id').empty();
                                    result.results.forEach(function(subcategory) {
                                        var option = new Option(subcategory.text, subcategory
                                            .id, false, false);
                                        $('#subcategory_id').append(option);
                                    });

                                    if (selectedSubCategory !== null && selectedSubCategory !== 0) {
                                        $("#subcategory_id").val(selectedSubCategory).trigger('change');
                                    }
                                }
                            });
                        }
                    }

                });
            })(jQuery);
        </script>













        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Replace textareas with Quill editors and sync to hidden fields for submit
                function mountQuill(textareaId) {
                    var textarea = document.getElementById(textareaId);
                    if (!textarea) return;

                    // Create container for Quill
                    var container = document.createElement('div');
                    container.id = textareaId + '_quill';
                    container.innerHTML = textarea.value || '';
                    textarea.classList.add('d-none');
                    textarea.parentNode.insertBefore(container, textarea.nextSibling);

                    var quill = new Quill('#' + container.id, {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                ['bold', 'italic', 'underline'],
                                [{ list: 'ordered' }, { list: 'bullet' }],
                                ['link'],
                                ['clean']
                            ]
                        }
                    });

                    // On form submit, copy HTML back into original textarea for backend
                    var form = textarea.closest('form');
                    if (form) {
                        form.addEventListener('submit', function () {
                            textarea.value = quill.root.innerHTML;
                        });
                    }
                }

                mountQuill('description');
                mountQuill('cancellation_policy');
            });
        </script>
    @endsection
</x-master-layout>
