<x-master-layout>
    <script src="https://cdn.tiny.cloud/1/m5d82gd2rwdlg96hsxpx0e5wwmfrl2zzkcw35ys8o3glilgq/tinymce/5/tinymce.min.js"
    referrerpolicy="origin"></script>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="fw-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                            <a href="{{ route('handyman.index') }}" class=" float-end btn btn-sm btn-primary"><i
                                    class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                            @if ($auth_user->can('handyman list'))
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ html()->form('POST', route('handyman.store'))->attribute('enctype', 'multipart/form-data')->attribute('data-toggle', 'validator')->id('handyman')->open() }}
                        {{ html()->hidden('id', $handymandata->id ?? null) }}
                        {{ html()->hidden('user_type', 'handyman') }}
                        <div class="row">
                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.first_name') . ' <span class="text-danger">*</span>', 'first_name')->class('form-control-label') }}
                                {{ html()->text('first_name', $handymandata->first_name)->placeholder(__('messages.first_name'))->class('form-control')->required() }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.last_name') . ' <span class="text-danger">*</span>', 'last_name')->class('form-control-label') }}
                                {{ html()->text('last_name', $handymandata->last_name)->placeholder(__('messages.last_name'))->class('form-control')->required() }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.username') . ' <span class="text-danger">*</span>', 'username')->class('form-control-label') }}
                                {{ html()->text('username', $handymandata->username)->placeholder(__('messages.username'))->class('form-control')->required() }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.email') . ' <span class="text-danger">*</span>', 'email')->class('form-control-label') }}
                                {{ html()->email('email', $handymandata->email)->placeholder(__('messages.email'))->class('form-control')->required()->attribute('pattern', '[^@]+@[^@]+\.[a-zA-Z]{2,}')->attribute('title', __('messages.email_valid_title')) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.company_name'))->class('form-control-label')->for('company_name') }}
                                {{ html()->text('company_name', $handymandata->company_name)->placeholder(__('messages.company_name'))->class('form-control') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.vat_number') . ' <span class="text-danger">*</span>')->class('form-control-label')->for('company_name') }}
                                {{ html()->text('vat_number', $handymandata->vat_number)->placeholder(__('messages.vat_number'))->class('form-control')->required() }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>


                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.skills'))->class('form-control-label')->for('skills') }}
                                {{ html()->text('skills', $handymandata->skills)->placeholder(__('messages.skills'))->class('form-control') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.language')]) . ' <span class="text-danger">*</span>', 'languages')->class('form-control-label text-danger') }}
                                <br />
                                {{ html()->select(
                                        'languages[]',
                                        config('spoken_language_options'),
                                        old('languages', $handymandata->languages ?? []),
                                    )->class('form-group select2js')->multiple()->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.language')]))->attribute('required', true)->id('languages_select') }}
                                <small class="help-block with-errors text-danger" id="languages_error"></small>
                            </div>
                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.education'))->class('form-control-label')->for('education') }}
                                {{ html()->text('education', $handymandata->education)->placeholder(__('messages.education'))->class('form-control') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.certification'))->class('form-control-label')->for('certification') }}
                                {{ html()->text('certification', $handymandata->certification)->placeholder(__('messages.certification'))->class('form-control') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.availability'))->class('form-control-label')->for('availability') }}
                                @php
                                    // Convert old values (1/0 or 'Full-time'/'Part-time') to new format for display
                                    $availabilityValue = $handymandata->availability;
                                    if ($availabilityValue == '1' || $availabilityValue == 1 || $availabilityValue == 'Full-time') {
                                        $availabilityValue = 'full_time';
                                    } elseif ($availabilityValue == '0' || $availabilityValue == 0 || $availabilityValue == 'Part-time') {
                                        $availabilityValue = 'part_time';
                                    }
                                @endphp
                                {{ html()->select(
                                        'availability',
                                        [
                                            'full_time' => __('messages.full_time'),
                                            'part_time' => __('messages.part_time'),
                                        ],
                                        $availabilityValue,
                                    )->class('form-control')->placeholder(__('messages.select_availability')) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.mobility'))->class('form-control-label')->for('mobility') }}
                                {{ html()->text('mobility', $handymandata->mobility)->placeholder(__('messages.mobility'))->class('form-control') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            @if (!isset($handymandata->id) || $handymandata->id == null)
                                <div class="form-group col-md-3">
                                    {{ html()->label(__('messages.password') . ' <span class="text-danger">*</span>', 'password')->class('form-control-label') }}
                                    <input class="form-control" type="password" id="handyman_password" name="password" required autocomplete="new-password" placeholder="{{ __('messages.password') }}">
                                    <p class="mb-1 mt-2 text-secondary small">{{ __('auth.password_requirements_intro') }}</p>
                                    <ul class="hm-password-requirements list-unstyled mb-1" aria-live="polite">
                                        <li id="hm-req-length"><span class="hm-req-icon" aria-hidden="true"></span>{{ __('auth.password_rule_min') }}</li>
                                        <li id="hm-req-letter"><span class="hm-req-icon" aria-hidden="true"></span>{{ __('auth.password_rule_letter') }}</li>
                                        <li id="hm-req-number"><span class="hm-req-icon" aria-hidden="true"></span>{{ __('auth.password_rule_number') }}</li>
                                    </ul>
                                    <small class="help-block with-errors text-danger"></small>
                                </div>
                            @endif
                            @if (auth()->user()->hasAnyRole(['admin', 'demo_admin']))
                                <div class="form-group col-md-3">
                                    {{ html()->label(__('messages.select_name', ['select' => __('messages.providers')]), 'provider_id')->class('form-control-label') }}
                                    <br />
                                    {{ html()->select(
                                            'provider_id',
                                            [optional($handymandata->providers)->id => optional($handymandata->providers)->display_name],
                                            optional($handymandata->providers)->id,
                                        )->class('select2js form-group providers')->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.providers')]))->attribute('data-ajax--url', route('ajax-list', ['type' => 'provider'])) }}
                                </div>
                            @endif
                            {{-- <div class="form-group col-md-3">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.handymantype')]) . ' <span class="text-danger">*</span>', 'handymantype_id')->class('form-control-label') }}
                                <br />
                                {{ html()->select('handymantype_id', [], old('handymantype_id'))->class('select2js form-group handymantype_id')->id('handymantype_id')->required()->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.handymantype')])) }}
                            </div> --}}

                           <div class="form-group col-md-3">
                                {{ html()->label(__('messages.handyman_commission_pct') . ' <span class="text-danger">*</span>', 'handyman_commission')->class('form-control-label') }}
                                {{ html()->number('handyman_commission', $handymandata->handyman_commission ?? null)
                                    ->attributes(['min' => 1, 'max' => 99, 'step' => 'any', 'placeholder' => 'e.g. 34.5'])
                                    ->class('form-control')
                                    ->id('handyman_commission')
                                    ->required() }}
                                <small class="text-muted">Enter 1 to 99. Decimals allowed (e.g., 34.5).</small>
                                <small class="help-block text-danger" id="commission_error"></small>
                            </div>


                            <div class="form-group col-md-3">
                                <label class="form-control-label" for="service_address_id">
                                    {{ __('messages.select_name', ['select' => __('messages.provider_address')]) }}
                                    <span class="text-danger addr-required-star">*</span>
                                </label>
                                <a id="add-address-link" href="{{ route('provideraddress.create') }}" target="_blank" class="btn btn-sm btn-outline-primary float-end mb-1">
                                    <i class="fa fa-plus-circle"></i> {{ __('messages.add_address') }}
                                </a>
                                <br />
                                {{ html()->select('service_address_id', [], old('service_address_id'))->class('select2js form-group service_address_id')->id('service_address_id')->required()->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.provider_address')])) }}
                                <small id="no-address-hint" class="d-none d-block mt-1 text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ __('messages.no_addresses_found') }}
                                </small>
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.country')]) . ' <span class="text-danger">*</span>', 'country_id')->class('form-control-label') }}
                                <br />
                                {{ html()->select(
                                        'country_id',
                                        [optional($handymandata->country)->id => optional($handymandata->country)->name],
                                        optional($handymandata->country)->id,
                                    )->class('select2js form-group country')->required()->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.country')]))->attribute('data-ajax--url', route('ajax-list', ['type' => 'country'])) }}
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.state')]) . ' <span class="text-danger">*</span>', 'state_id')->class('form-control-label') }}
                                <br />
                                {{ html()->select('state_id', [], [])->class('select2js form-group state_id')->required()->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.state')])) }}
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.city')]) . ' <span class="text-danger">*</span>', 'city_id')->class('form-control-label') }}
                                <br />
                                {{ html()->select('city_id', [], old('city_id'))->class('select2js form-group city_id')->required()->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.city')])) }}
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.contact_number') . ' <span class="text-danger">*</span>', 'contact_number')->class('form-control-label') }}
                                {{ html()->text('contact_number', $handymandata->contact_number)->placeholder(__('messages.contact_number'))->class('form-control contact_number')->required() }}
                                {{-- //'maxlength' => 20, // Maximum 20 characters allowed
                                      //'pattern' => '^(\+|-)?\d+$', // Accepts '+' and numeric characters only --}}
                                <small class="help-block with-errors text-danger" id="contact_number_err"></small>
                            </div>

                            <div class="form-group col-md-3">
                                {{ html()->label(__('messages.status') . ' <span class="text-danger">*</span>', 'status')->class('form-control-label') }}
                                {{ html()->select('status', ['1' => __('messages.active'), '0' => __('messages.inactive')], old('status'))->class('form-control select2js')->required() }}
                            </div>

                            <div class="form-group col-md-3">
                                <label class="form-control-label"
                                    for="profile_image">{{ __('messages.profile_image') }}
                                </label>
                                <div class="custom-file">
                                    <input type="file" name="profile_image" id="handyman_profile_image" class="custom-file-input"
                                        accept="image/*">
                                    <label
                                        class="custom-file-label upload-label">{{ __('messages.choose_file', ['file' => __('messages.profile_image')]) }}</label>
                                </div>
                                <small id="handyman-profile-image-status" class="d-none mt-1 text-primary fw-bold"></small>
                                <!-- <span class="selected_file"></span> -->
                            </div>

                            @if (getMediaFileExit($handymandata, 'profile_image'))
                                <div class="col-md-3 mb-2 position-relative">
                                    <img id="profile_image_preview"
                                        src="{{ getSingleMedia($handymandata, 'profile_image') }}" alt="#"
                                        class="attachment-image mt-1">
                                    <a class="text-danger remove-file"
                                        href="{{ route('remove.file', ['id' => $handymandata->id, 'type' => 'profile_image']) }}"
                                        data--submit="confirm_form" data--confirmation='true' data--ajax="true"
                                        data-toggle="tooltip"
                                        title='{{ __('messages.remove_file_title', ['name' => __('messages.image')]) }}'
                                        data-title='{{ __('messages.remove_file_title', ['name' => __('messages.image')]) }}'
                                        data-message='{{ __('messages.remove_file_msg') }}'>
                                        <i class="ri-close-circle-line"></i>
                                    </a>
                                </div>
                            @endif

                            <!-- Text Editors Row - All three together at the end -->
                            <div class="w-100"></div>
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.experience'), 'experience')->class('form-control-label') }}
                                {{ html()->textarea('experience', $handymandata->experience)->class('form-control textarea')->rows(2)->placeholder(__('messages.experience'))->id('experience') }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.about_me'))->class('form-control-label')->for('about_me') }}
                                {{ html()->textarea('about_me', $handymandata->about_me)->class('form-control textarea')->rows(2)->placeholder(__('messages.about_me'))->id('about_me') }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.address'), 'address')->class('form-control-label') }}
                                {{ html()->textarea('address', $handymandata->address)->class('form-control textarea')->rows(2)->placeholder(__('messages.address'))->id('address') }}
                            </div>

                        </div>
                        <button type="submit" id="handyman-submit-button" class="btn btn-md btn-primary float-end" data-default-text="{{ __('messages.save') }}">{{ __('messages.save') }}</button>
                        {{ html()->form()->close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('bottom_script')
        <script type="text/javascript">
            (function($) {
                "use strict";
                $(document).ready(function() {
                    var country_id = "{{ isset($handymandata->country_id) ? $handymandata->country_id : 0 }}";
                    var state_id = "{{ isset($handymandata->state_id) ? $handymandata->state_id : 0 }}";
                    var city_id = "{{ isset($handymandata->city_id) ? $handymandata->city_id : 0 }}";

                    var provider_id = "{{ isset($handymandata->provider_id) ? $handymandata->provider_id : '' }}";
                    var service_address_id =
                        "{{ isset($handymandata->service_address_id) ? $handymandata->service_address_id : 0 }}";
                    var handymantype_id =
                        "{{ isset($handymandata->handymantype_id) ? $handymandata->handymantype_id : 0 }}";

                    stateName(country_id, state_id);
                    providerAddress(provider_id, service_address_id)
                    handymanType(provider_id, handymantype_id)
                    $(document).on('change', '#country_id', function() {
                        var country = $(this).val();
                        $('#state_id').empty();
                        $('#city_id').empty();
                        stateName(country);
                    })
                    $(document).on('change', '#state_id', function() {
                        var state = $(this).val();
                        $('#city_id').empty();
                        cityName(state, city_id);
                    })
                    $(document).on('change', '#provider_id', function() {
                        var provider_id = $(this).val();
                        $('#service_address_id').empty();
                        $('#handymantype_id').empty();
                        providerAddress(provider_id, service_address_id);
                        handymanType(provider_id, handymantype_id)
                    })

                })
                $(document).on('keyup', '.contact_number', function() {
                    var contactNumberInput = document.getElementById('contact_number');
                    var inputValue = contactNumberInput.value;
                    inputValue = inputValue.replace(/[^0-9+\- ]/g, '');
                    if (inputValue.length > 15) {
                        inputValue = inputValue.substring(0, 15);
                        $('#contact_number_err').text('{{ __("messages.contact_number_max_15") }}');
                    } else {
                        $('#contact_number_err').text('');
                    }
                    contactNumberInput.value = inputValue;
                    if (inputValue.match(/^[0-9+\- ]+$/)) {
                        $('#contact_number_err').text('');
                    } else {
                        $('#contact_number_err').text("{{ __('messages.valid_mobile_number') }}");
                    }
                });


                function stateName(country, state = "") {
                    var state_route = "{{ route('ajax-list', ['type' => 'state', 'country_id' => '']) }}" + country;
                    state_route = state_route.replace('amp;', '');

                    $.ajax({
                        url: state_route,
                        success: function(result) {
                            $('#state_id').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name', ['select' => trans('messages.state')]) }}",
                                data: result.results
                            });
                            if (state != null) {
                                $("#state_id").val(state).trigger('change');
                            }
                        }
                    });
                }

                function cityName(state, city = "") {
                    var city_route = "{{ route('ajax-list', ['type' => 'city', 'state_id' => '']) }}" + state;
                    city_route = city_route.replace('amp;', '');

                    $.ajax({
                        url: city_route,
                        success: function(result) {
                            $('#city_id').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name', ['select' => trans('messages.city')]) }}",
                                data: result.results
                            });
                            if (city != null || city != 0) {
                                $("#city_id").val(city).trigger('change');
                            }
                        }
                    });
                }

                function providerAddress(provider_id, service_address_id = "") {
                    var provider_address_route =
                        "{{ route('ajax-list', ['type' => 'provider_address', 'provider_id' => '']) }}" + provider_id;
                    provider_address_route = provider_address_route.replace('amp;', '');

                    $.ajax({
                        url: provider_address_route,
                        success: function(result) {
                            $('#service_address_id').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name', ['select' => trans('messages.provider_address')]) }}",
                                data: result.results
                            });
                            if (service_address_id != "") {
                                $('#service_address_id').val(service_address_id).trigger('change');
                            }
                            var hasAddresses = result.results && result.results.length > 0;
                            var $select = $('#service_address_id');
                            var $hint = $('#no-address-hint');

                            if (hasAddresses) {
                                $select.attr('required', true);
                                $hint.addClass('d-none');
                                $('.addr-required-star').show();
                            } else {
                                $select.removeAttr('required');
                                $hint.removeClass('d-none');
                                $('.addr-required-star').hide();
                            }

                            var addLink = "{{ route('provideraddress.create') }}" + (provider_id ? "?provider_id=" + provider_id : "");
                            $('#add-address-link').attr('href', addLink);
                        }
                    });
                }

                function handymanType(provider_id, handymantype_id = "") {
                    var handymantype_route =
                        "{{ route('ajax-list', ['type' => 'handymantype', 'provider_id' => '']) }}" + provider_id;
                    handymantype_route = handymantype_route.replace('amp;', '');

                    $.ajax({
                        url: handymantype_route,
                        success: function(result) {
                            $('#handymantype_id').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name', ['select' => trans('messages.handymantype')]) }}",
                                data: result.results
                            });
                            if (handymantype_id != "") {
                                $('#handymantype_id').val(handymantype_id).trigger('change');
                            }
                        }
                    });
                }
            })(jQuery);
        </script>
         <script>
            tinymce.init({
                selector: '#address', // Target the ID of your textarea
                plugins: 'lists link image preview', // Add any plugins you want to use
                toolbar: 'undo redo | bold italic | bullist numlist | link image preview',
                menubar: false
            });
        </script>
        <script>
            tinymce.init({
                selector: '#about_me', // Target the ID of your textarea
                plugins: 'lists link image preview', // Add any plugins you want to use
                toolbar: 'undo redo | bold italic | bullist numlist | link image preview',
                menubar: false
            });
        </script>
        <script>
            tinymce.init({
                selector: '#experience', // Target the ID of your textarea
                plugins: 'lists link image preview', // Add any plugins you want to use
                toolbar: 'undo redo | bold italic | bullist numlist | link image preview',
                menubar: false
            });
            $(document).on('input', '#handyman_commission', function () {
    let value = parseFloat($(this).val());
    let errorField = $('#commission_error');

    if (isNaN(value)) {
        errorField.text('');
        return;
    }

    if (value < 1 || value > 99) {
        errorField.text('{{ __("messages.commission_between_1_85") }}');
    } else {
        errorField.text('');
    }
});

            $('#handyman').on('submit', function () {
                if (window.handymanProfileImagePreparing) {
                    if (typeof Snackbar !== 'undefined') {
                        Snackbar.show({ text: '{{ __("messages.please_wait_image_preparing") }}', pos: 'bottom-center', backgroundColor: '#d32f2f', actionTextColor: '#fff' });
                    } else {
                        alert('{{ __("messages.please_wait_image_preparing") }}');
                    }
                    return false;
                }
                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                }
                var lang = $('#languages_select').val();
                var err = $('#languages_error');
                if (!lang || (Array.isArray(lang) && lang.length === 0)) {
                    err.text("{{ __('messages.select_at_least_one_language') }}");
                    return false;
                }
                err.text('');
            });

            function setHandymanProfileImagePreparing(isPreparing) {
                window.handymanProfileImagePreparing = isPreparing;
                var button = document.getElementById('handyman-submit-button');
                if (!button) return;
                button.disabled = isPreparing;
                button.classList.toggle('btn-secondary', isPreparing);
                button.classList.toggle('btn-primary', !isPreparing);
                button.textContent = isPreparing ? '{{ __("messages.preparing_image") }}' : button.dataset.defaultText;
            }

            function updateHandymanProfileImageStatus(message) {
                var status = document.getElementById('handyman-profile-image-status');
                if (!status) return;
                status.textContent = message || '';
                status.classList.toggle('d-none', !message);
            }

            function handymanCanvasToBlob(canvas, quality) {
                return new Promise(function(resolve) {
                    canvas.toBlob(resolve, 'image/jpeg', quality);
                });
            }

            function loadHandymanProfileImage(file) {
                return new Promise(function(resolve, reject) {
                    var image = new Image();
                    var objectUrl = URL.createObjectURL(file);
                    image.onload = function() {
                        URL.revokeObjectURL(objectUrl);
                        resolve(image);
                    };
                    image.onerror = function() {
                        URL.revokeObjectURL(objectUrl);
                        reject();
                    };
                    image.src = objectUrl;
                });
            }

            async function prepareHandymanProfileImage(file) {
                var maxOriginalSize = 4 * 1024 * 1024;
                var maxPreparedSize = 120 * 1024;
                var maxDimension = 800;

                if (file.size > maxOriginalSize) {
                    throw new Error('{{ __("messages.image_max_4mb") }}');
                }

                if (!file.type || !file.type.startsWith('image/')) {
                    return file;
                }

                var image = await loadHandymanProfileImage(file);
                if (file.type === 'image/jpeg' && file.size <= maxPreparedSize && Math.max(image.width, image.height) <= maxDimension) {
                    return file;
                }

                var ratio = Math.min(maxDimension / image.width, maxDimension / image.height, 1);
                var canvas = document.createElement('canvas');
                canvas.width = Math.max(1, Math.round(image.width * ratio));
                canvas.height = Math.max(1, Math.round(image.height * ratio));
                var context = canvas.getContext('2d');
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                context.drawImage(image, 0, 0, canvas.width, canvas.height);

                var qualities = [0.62, 0.52, 0.44, 0.36, 0.28];
                var blob = null;
                for (var i = 0; i < qualities.length; i++) {
                    blob = await handymanCanvasToBlob(canvas, qualities[i]);
                    if (blob && blob.size <= maxPreparedSize) break;
                }

                if (!blob) return file;

                return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                });
            }

            $(document).on('change', '#handyman_profile_image', async function() {
                var input = this;
                var file = input.files && input.files[0];
                updateHandymanProfileImageStatus('');

                if (!file) return;

                setHandymanProfileImagePreparing(true);
                updateHandymanProfileImageStatus('{{ __("messages.preparing_image") }}');

                try {
                    var preparedFile = await prepareHandymanProfileImage(file);
                    var dataTransfer = new DataTransfer();
                    dataTransfer.items.add(preparedFile);
                    input.files = dataTransfer.files;
                    $('.upload-label').text(preparedFile.name);
                    updateHandymanProfileImageStatus('{{ __("messages.image_ready_for_upload") }}');

                    var objectUrl = URL.createObjectURL(preparedFile);
                    $('#profile_image_preview').attr('src', objectUrl).one('load', function() {
                        URL.revokeObjectURL(objectUrl);
                    });
                } catch (error) {
                    input.value = '';
                    $('.upload-label').text("{{ __('messages.choose_file', ['file' => __('messages.profile_image')]) }}");
                    updateHandymanProfileImageStatus('');
                    if (typeof Snackbar !== 'undefined') {
                        Snackbar.show({ text: error.message || '{{ __("messages.please_choose_valid_image") }}', pos: 'bottom-center', backgroundColor: '#d32f2f', actionTextColor: '#fff' });
                    } else {
                        alert(error.message || '{{ __("messages.please_choose_valid_image") }}');
                    }
                } finally {
                    setHandymanProfileImagePreparing(false);
                }
            });

        </script>

        <style>
            .hm-password-requirements { font-size: 0.875rem; }
            .hm-password-requirements li { margin-bottom: 0.2rem; transition: color 0.15s ease; color: #6c757d; }
            .hm-password-requirements li.valid { color: #198754; }
            .hm-password-requirements li.valid .hm-req-icon::before { content: '✓ '; font-weight: bold; }
            .hm-password-requirements li:not(.valid) .hm-req-icon::before { content: '○ '; }
        </style>
        <script>
            (function () {
                function hmPasswordPolicyCheck(pwd) {
                    return {
                        lengthOk: pwd.length >= 12 && pwd.length <= 20,
                        letterOk: /[a-zA-Z]/.test(pwd),
                        numberOk: /[0-9]/.test(pwd)
                    };
                }
                function hmUpdatePasswordUi(pwd) {
                    var c = hmPasswordPolicyCheck(pwd);
                    var toggle = function(id, ok) { var el = document.getElementById(id); if (el) el.classList.toggle('valid', ok); };
                    toggle('hm-req-length', c.lengthOk);
                    toggle('hm-req-letter', c.letterOk);
                    toggle('hm-req-number', c.numberOk);
                }
                var input = document.getElementById('handyman_password');
                if (input) {
                    input.addEventListener('input', function () { hmUpdatePasswordUi(this.value); });
                }
            })();
        </script>
    @endsection
</x-master-layout>
