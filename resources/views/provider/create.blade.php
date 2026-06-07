<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="fw-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                            <a href="{{ route('provider.index') }}" class=" float-end btn btn-sm btn-primary"><i
                                    class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                            @if($auth_user->can('provider list'))
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ html()->form('POST', route('provider.store'))->id('provider')->attribute('enctype', 'multipart/form-data')->attribute('data-toggle', 'validator')->open() }}
                        {{ html()->hidden('id',$providerdata->id ?? null) }}
                        {{ html()->hidden('user_type','provider') }}
                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.first_name') . ' <span class="text-danger">*</span>', 'first_name')->class('form-control-label') }}
                                {{ html()->text('first_name',$providerdata->first_name)->placeholder(__('messages.first_name'))->class('form-control')->required() }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                    
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.last_name') . ' <span class="text-danger">*</span>', 'last_name')->class('form-control-label') }}
                                {{ html()->text('last_name', $providerdata->last_name)->placeholder(__('messages.last_name'))->class('form-control')->required() }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                    
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.username') . ' <span class="text-danger">*</span>', 'username')->class('form-control-label') }}
                                {{ html()->text('username', $providerdata->username)->placeholder(__('messages.username'))->class('form-control')->required() }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                    
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.email') . ' <span class="text-danger">*</span>', 'email')->class('form-control-label') }}
                                {{ html()->email('email', $providerdata->email)->placeholder(__('messages.email'))->class('form-control')->required()->attribute('pattern' ,'[^@]+@[^@]+\.[a-zA-Z]{2,}')->attribute('title', 'Please enter a valid email address')}}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                    
                            @if (!isset($providerdata->id) || $providerdata->id == null)
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.password') . ' <span class="text-danger">*</span>', 'password')->class('form-control-label') }}
                                {{ html()->password('password')->id('password')->class('form-control')->placeholder(__('messages.password'))->required()->autocomplete('new-password')->attribute('minlength', 12)->attribute('pattern', '^(?=.*[A-Za-z])(?=.*\d).{12,}$')->attribute('title', __('auth.password_requirements_intro') . ' ' . __('auth.password_rule_min') . ', ' . __('auth.password_rule_letter') . ', ' . __('auth.password_rule_number')) }}
                                <small class="text-muted d-block mt-1 password-policy">
                                    {{ __('auth.password_requirements_intro') }}
                                    <br><span class="text-muted" data-password-rule="length">{{ __('auth.password_rule_min') }}</span>
                                    <br><span class="text-muted" data-password-rule="letter">{{ __('auth.password_rule_letter') }}</span>
                                    <br><span class="text-muted" data-password-rule="number">{{ __('auth.password_rule_number') }}</span>
                                </small>
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            @endif
                    
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.designation'), 'designation')->class('form-control-label') }}
                                {{ html()->text('designation',  $providerdata->designation)->placeholder(__('messages.designation'))->class('form-control') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                    
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.country')]), 'country_id')->class('form-control-label') }}
                                <br />
                                {{ html()->select('country_id', [optional($providerdata->country)->id => optional($providerdata->country)->name], optional($providerdata->country)->id)
                                    ->class('select2js form-group country')
                                    ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.country')]))
                                    ->attribute('data-ajax--url', route('ajax-list', ['type' => 'country'])) }}
                            </div>
                    
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.state')]), 'state_id')->class('form-control-label') }}
                                <br />
                                {{ html()->select('state_id', [])
                                    ->class('select2js form-group state_id')
                                    ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.state')])) }}
                            </div>
                    
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.city')]), 'city_id')->class('form-control-label') }}
                                <br />
                                {{ html()->select('city_id', [], old('city_id'))
                                    ->class('select2js form-group city_id')
                                    ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.city')])) }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.contact_number') . ' <span class="text-danger">*</span>', 'contact_number')->class('form-control-label') }}
                                {{ html()->text('contact_number',$providerdata->contact_number)->placeholder(__('messages.contact_number'))->class('form-control contact_number')->required() }}
                                <small class="help-block with-errors text-danger" id="contact_number_err"></small>
                            </div>
                    
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.status') . ' <span class="text-danger">*</span>', 'status')->class('form-control-label') }}
                                {{ html()->select('status', ['1' => __('messages.active'), '0' => __('messages.inactive')], $providerdata->status)->class('form-control select2js')->required() }}
                            </div>
                    
                            <div class="form-group col-md-4">
                                <label class="form-control-label" for="profile_image">{{ __('messages.profile_image') }}
                                </label>
                                <div class="custom-file">
                                    <input type="file" name="profile_image" id="provider_profile_image" class="custom-file-input" accept="image/*">
                                    <label
                                        class="custom-file-label upload-label">{{  __('messages.choose_file',['file' =>  __('messages.profile_image') ]) }}</label>
                                </div>
                                <small id="provider-profile-image-status" class="d-none mt-1 text-primary fw-bold"></small>
                                <!-- <span class="selected_file"></span> -->
                            </div>
                    
                            @if(getMediaFileExit($providerdata, 'profile_image'))
                            <div class="col-md-2 mb-2 position-relative">
                                <img id="profile_image_preview" src="{{getSingleMedia($providerdata,'profile_image')}}"
                                    alt="#" class="attachment-image mt-1">
                                <a class="text-danger remove-file"
                                    href="{{ route('remove.file', ['id' => $providerdata->id, 'type' => 'profile_image']) }}"
                                    data--submit="confirm_form" data--confirmation='true' data--ajax="true"
                                    data-toggle="tooltip"
                                    title='{{ __("messages.remove_file_title" , ["name" =>  __("messages.image") ]) }}'
                                    data-title='{{ __("messages.remove_file_title" , ["name" =>  __("messages.image") ]) }}'
                                    data-message='{{ __("messages.remove_file_msg") }}'>
                                    <i class="ri-close-circle-line"></i>
                                </a>
                            </div>
                            @endif
                    
                            <div class="form-group col-md-6">
                                {{ html()->label(__('messages.select_name', ['select' => __('Language')]), 'languages')->class('form-control-label') }}
                                <br />
                                {{ html()->select(
                                        'languages[]',
                                        config('spoken_language_options'),
                                        old('languages', $providerdata->languages ?? []),
                                    )->class('form-group select2js')->multiple()->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.language')])) }}
                            </div>

                            <div class="form-group col-md-6">
                                {{ html()->label(__('messages.select_name', ['select' => __('Country tax')]), 'tax_country_id')->class('form-control-label') }}
                                <br />
                                {{ html()->select(
                                        'tax_country_id',
                                        [optional($providerdata->country)->id => optional($providerdata->country)->name],
                                        optional($providerdata->country)->id,
                                    )->class('form-group select2js tax_country')->attribute('data-placeholder', __('messages.select_name', ['select' => __('Tax Country')]))->attribute('data-ajax--url', route('ajax-list', ['type' => 'country']))->attribute('disabled', true) }}
                            </div>
                            <input type="hidden" name="tax_country_id" value="{{ optional($providerdata->country)->id }}">

                            <div class="form-group col-md-6">
                                {{ html()->label(__('Company Name'))->class('form-control-label')->for('company_name') }}
                                {{ html()->text('company_name', $providerdata->company_name)->placeholder(__('Company Name'))->class('form-control') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-6">
                                {{ html()->label(__('Vat Number') . ' <span class="text-danger">*</span>')->class('form-control-label text-danger')->for('vat_number') }}
                                {{ html()->text('vat_number', $providerdata->vat_number)->placeholder(__('Vat Number'))->class('form-control')->required() }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-6">
                                {{ html()->label(__('skills'))->class('form-control-label')->for('skills') }}
                                {{ html()->text('skills', $providerdata->skills)->placeholder(__('skills'))->class('form-control') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-6">
                                {{ html()->label(__('Education'))->class('form-control-label')->for('education') }}
                                {{ html()->text('education', $providerdata->education)->placeholder(__('education'))->class('form-control') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-6">
                                {{ html()->label(__('Certification'))->class('form-control-label')->for('certification') }}
                                {{ html()->text('certification', $providerdata->certification)->placeholder(__('Certification'))->class('form-control') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-6">
                                {{ html()->label(__('Availability'))->class('form-control-label')->for('availability') }}
                                @php
                                    // Convert old values (1/0 or 'Full-time'/'Part-time') to new format for display
                                    $availabilityValue = $providerdata->availability;
                                    if ($availabilityValue == '1' || $availabilityValue == 1 || $availabilityValue == 'Full-time') {
                                        $availabilityValue = 'full_time';
                                    } elseif ($availabilityValue == '0' || $availabilityValue == 0 || $availabilityValue == 'Part-time') {
                                        $availabilityValue = 'part_time';
                                    }
                                @endphp
                                {{ html()->select(
                                        'availability',
                                        [
                                            'full_time' => 'Full-time',
                                            'part_time' => 'Part-time',
                                        ],
                                        $availabilityValue,
                                    )->class('form-control')->placeholder(__('Select Availability')) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-6">
                                {{ html()->label(__('Mobility'))->class('form-control-label')->for('mobility') }}
                                {{ html()->text('mobility', $providerdata->mobility)->placeholder(__('Mobility'))->class('form-control') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-12">
                                {{ html()->label(__('experience'), 'experience')->class('form-control-label') }}
                                {{ html()->textarea('experience', $providerdata->experience)->class('form-control textarea')->rows(2)->placeholder(__('experience'))->id('experience') }}
                            </div>

                            <div class="form-group col-md-12">
                                {{ html()->label(__('About Me'))->class('form-control-label')->for('about_me') }}
                                {{ html()->textarea('about_me', $providerdata->about_me)->class('form-control textarea')->rows(2)->placeholder(__('about_me'))->id('about_me') }}
                            </div>

                            <div class="form-group col-md-12">
                                {{ html()->label(__('messages.address'), 'address')->class('form-control-label') }}
                                {{ html()->textarea('address', $providerdata->address)->class("form-control textarea")->rows(3)->placeholder(__('messages.address'))->id('address') }}
                            </div>

                            <div class="form-group col-md-12 mt-4">
                                <h4>{{ __('messages.why_choose_me') }}</h4>
                            </div>

                            <div class="form-group col-md-12">
                                {{ html()->label(__('messages.title'))->class('form-control-label')->for('title') }}
                                {{ html()->text('title', $providerdata->title)->class('form-control')->placeholder(__('messages.title')) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-12">
                                {{ html()->label(__('messages.description'))->class('form-control-label')->for('about_description') }}
                                {{ html()->textarea('about_description', $providerdata->about_description)->class('form-control textarea')->rows(2)->placeholder(__('messages.description'))->id('about_description') }}
                            </div>

                            @php
                                $reasons = $providerdata->reason ?? [];
                                if (is_string($reasons)) {
                                    $reasons = json_decode($reasons, true) ?? [];
                                }
                                if (isset($reasons['reason']) && is_array($reasons['reason'])) {
                                    $reasons = $reasons['reason'];
                                }
                            @endphp

                            @if (!empty($reasons))
                                @foreach ($reasons as $reason)
                                    <div class="form-section1 form-group col-md-12">
                                        <div class="row">
                                            <div class="form-group col-md-12 d-flex">
                                                {{ html()->text('reason[]', $reason)->placeholder(__('messages.reason'))->class('form-control') }}
                                                <small class="help-block with-errors text-danger"></small>
                                                <div class="form-group col-3 mb-0 align-self-center">
                                                    <button class="remove-section1 button-custom button-remove"
                                                        data-title="remove" title="Remove">
                                                        <i class="far fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            <div class="form-section form-group col-md-12">
                                {{ html()->label(__('messages.reason'))->class('form-control-label')->for('reason') }}
                                <div class="row">
                                    <div class="form-group col-md-12 d-flex">
                                        {{ html()->text('reason[]')->placeholder(__('messages.reason'))->class('form-control') }}
                                        <small class="help-block with-errors text-danger"></small>
                                        <div class="form-group mb-0 col-3 align-self-center">
                                            <button class="remove-section button-custom button-remove">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <div class="form-group row">
                                    <div class="col-md-9 text-md-right pe-1">
                                        <button type="button" id="add-section" class="button-custom button-added">
                                            <i class="fas fa-plus me-2"></i>Add More Reason
                                        </button>
                                    </div>
                                    <div class="col-md-3"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <div class="custom-control custom-switch custom-control-inline">
                                    {{ html()->checkbox('is_featured', $providerdata->is_featured)->class('custom-control-input')->id('is_featured') }}
                                    <label class="custom-control-label"
                                        for="is_featured">{{ __('messages.set_as_featured')  }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" id="provider-submit-button" class="btn btn-md btn-primary float-end" data-default-text="{{ __('messages.save') }}">{{ __('messages.save') }}</button>
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
            var country_id = "{{ isset($providerdata->country_id) ? $providerdata->country_id : 0 }}";
            var state_id = "{{ isset($providerdata->state_id) ? $providerdata->state_id : 0 }}";
            var city_id = "{{ isset($providerdata->city_id) ? $providerdata->city_id : 0 }}";

            stateName(country_id, state_id);
            $(document).on('change', '#country_id', function() {
                var country = $(this).val();
                var selectedText = $("#country_id option:selected").text();
                $('#state_id').empty();
                $('#city_id').empty();
                stateName(country);

                // Sync tax_country_id (disabled select)
                $('#tax_country_id').empty();
                var newOption = new Option(selectedText, country, true, true);
                $('#tax_country_id').append(newOption).trigger('change');

                // Update hidden input for submission
                $('input[name="tax_country_id"]').val(country);
            })
            $(document).on('change', '#state_id', function() {
                var state = $(this).val();
                $('#city_id').empty();
                cityName(state, city_id);
            })
        })

        $(document).on('keyup', '.contact_number', function() {
        var contactNumberInput = document.getElementById('contact_number');
        var inputValue = contactNumberInput.value;
        inputValue = inputValue.replace(/[^0-9+\- ]/g, '');
        if (inputValue.length > 15) {
            inputValue = inputValue.substring(0, 15);
            $('#contact_number_err').text('Contact number should not exceed 15 characters');
        } else {
            $('#contact_number_err').text('');
        }
        contactNumberInput.value = inputValue;
        if (inputValue.match(/^[0-9+\- ]+$/)) {
            $('#contact_number_err').text('');
        } else {
            $('#contact_number_err').text('Please enter a valid mobile number');
        }
    });

        function stateName(country, state = "") {
            var state_route = "{{ route('ajax-list', [ 'type' => 'state','country_id' =>'']) }}" + country;
            state_route = state_route.replace('amp;', '');

            $.ajax({
                url: state_route,
                success: function(result) {
                    $('#state_id').select2({
                        width: '100%',
                        placeholder: "{{ trans('messages.select_name',['select' => trans('messages.state')]) }}",
                        data: result.results
                    });
                    if (state != null) {
                        $("#state_id").val(state).trigger('change');
                    }
                }
            });
        }

        function cityName(state, city = "") {
            var city_route = "{{ route('ajax-list', [ 'type' => 'city' ,'state_id' =>'']) }}" + state;
            city_route = city_route.replace('amp;', '');

            $.ajax({
                url: city_route,
                success: function(result) {
                    $('#city_id').select2({
                        width: '100%',
                        placeholder: "{{ trans('messages.select_name',['select' => trans('messages.city')]) }}",
                        data: result.results
                    });
                    if (city != null || city != 0) {
                        $("#city_id").val(city).trigger('change');
                    }
                }
            });
        }

    })(jQuery);

    // Add/Remove Reason Section
    $("#add-section").click(function () {
        var newSection = $(".form-section:first").clone();
        newSection.find('input').val('');
        $(".form-section:last").after(newSection);
        updateRemoveButtonVisibility();
    });

    $(document).on('click', '.remove-section', function () {
        if ($(".form-section").length > 1) {
            $(this).closest('.form-section').remove();
            updateRemoveButtonVisibility();
        }
    });

    $(document).on('click', '.remove-section1', function () {
        $(this).closest('.form-section1').remove();
    });

    function updateRemoveButtonVisibility() {
        if ($(".form-section").length > 1) {
            $('.remove-section').show();
        } else {
            $('.remove-section').hide();
        }
    }

    updateRemoveButtonVisibility();

    // Initialize tax_country_id with provider's current value
    let initialTaxCountryId = "{{ optional($providerdata->country)->id }}";
    let initialTaxCountryName = "{{ optional($providerdata->country)->name }}";
    if (initialTaxCountryId && initialTaxCountryName) {
        let initialOption = new Option(initialTaxCountryName, initialTaxCountryId, true, true);
        $('#tax_country_id').append(initialOption).trigger('change');
        $('input[name="tax_country_id"]').val(initialTaxCountryId);
    }

document.addEventListener('DOMContentLoaded', function() { 
    checkImage();
    initPasswordPolicyChecklist();
});

function setProviderProfileImagePreparing(isPreparing) {
    window.providerProfileImagePreparing = isPreparing;
    var button = document.getElementById('provider-submit-button');
    if (!button) return;
    button.disabled = isPreparing;
    button.classList.toggle('btn-secondary', isPreparing);
    button.classList.toggle('btn-primary', !isPreparing);
    button.textContent = isPreparing ? 'Preparing image...' : button.dataset.defaultText;
}

function updateProviderProfileImageStatus(message) {
    var status = document.getElementById('provider-profile-image-status');
    if (!status) return;
    status.textContent = message || '';
    status.classList.toggle('d-none', !message);
}

function providerCanvasToBlob(canvas, quality) {
    return new Promise(function(resolve) {
        canvas.toBlob(resolve, 'image/jpeg', quality);
    });
}

function loadProviderProfileImage(file) {
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

async function prepareProviderProfileImage(file) {
    var maxOriginalSize = 4 * 1024 * 1024;
    var maxPreparedSize = 120 * 1024;
    var maxDimension = 800;

    if (file.size > maxOriginalSize) {
        throw new Error('Image must be 4 MB or smaller.');
    }

    if (!file.type || !file.type.startsWith('image/')) {
        return file;
    }

    var image = await loadProviderProfileImage(file);
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
        blob = await providerCanvasToBlob(canvas, qualities[i]);
        if (blob && blob.size <= maxPreparedSize) break;
    }

    if (!blob) return file;

    return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', {
        type: 'image/jpeg',
        lastModified: Date.now()
    });
}

$(document).on('change', '#provider_profile_image', async function() {
    var input = this;
    var file = input.files && input.files[0];
    updateProviderProfileImageStatus('');

    if (!file) return;

    setProviderProfileImagePreparing(true);
    updateProviderProfileImageStatus('Preparing image...');

    try {
        var preparedFile = await prepareProviderProfileImage(file);
        var dataTransfer = new DataTransfer();
        dataTransfer.items.add(preparedFile);
        input.files = dataTransfer.files;
        $('.upload-label').text(preparedFile.name);
        updateProviderProfileImageStatus('Image ready for upload.');

        var objectUrl = URL.createObjectURL(preparedFile);
        $('#profile_image_preview').attr('src', objectUrl).one('load', function() {
            URL.revokeObjectURL(objectUrl);
        });
    } catch (error) {
        input.value = '';
        $('.upload-label').text("{{ __('messages.choose_file',['file' =>  __('messages.profile_image') ]) }}");
        updateProviderProfileImageStatus('');
        if (typeof Snackbar !== 'undefined') {
            Snackbar.show({ text: error.message || 'Please choose a valid image.', pos: 'bottom-center', backgroundColor: '#d32f2f', actionTextColor: '#fff' });
        } else {
            alert(error.message || 'Please choose a valid image.');
        }
    } finally {
        setProviderProfileImagePreparing(false);
    }
});

$('#provider').on('submit', function() {
    if (window.providerProfileImagePreparing) {
        if (typeof Snackbar !== 'undefined') {
            Snackbar.show({ text: 'Please wait, image is preparing for upload.', pos: 'bottom-center', backgroundColor: '#d32f2f', actionTextColor: '#fff' });
        } else {
            alert('Please wait, image is preparing for upload.');
        }
        return false;
    }
});

function checkImage() { 
        var id = @json($providerdata->id ?? null); 
        if (!id) return;
    var route = "{{ route('check-image', ':id') }}";
    route = route.replace(':id', id);  
    var type = 'profile_image';

    $.ajax({
        url: route,
        type: 'GET',   
        data: {
            type: type,   
        }, 
        success: function(result) {  
            var attachments = result.results;  

            if (attachments.length === 0) { 
                $('input[name="profile_image"]').attr('required', 'required');
            } else { 
                $('input[name="profile_image"]').removeAttr('required');
            }         
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);  
        }
    });
}

function initPasswordPolicyChecklist() {
    var passwordInput = document.getElementById('password');
    if (!passwordInput) return;

    var rules = {
        length: document.querySelector('[data-password-rule="length"]'),
        letter: document.querySelector('[data-password-rule="letter"]'),
        number: document.querySelector('[data-password-rule="number"]')
    };

    function setRuleState(rule, isValid) {
        if (!rule) return;
        rule.classList.toggle('text-success', isValid);
        rule.classList.toggle('text-muted', !isValid);
    }

    function updatePasswordPolicy() {
        var value = passwordInput.value || '';
        setRuleState(rules.length, value.length >= 12);
        setRuleState(rules.letter, /[A-Za-z]/.test(value));
        setRuleState(rules.number, /[0-9]/.test(value));
    }

    passwordInput.addEventListener('input', updatePasswordPolicy);
    updatePasswordPolicy();
}
    </script>
    @endsection
</x-master-layout>
