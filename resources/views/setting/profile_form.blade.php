<div class="col-md-12">
    <div class="row ">
        <div class="col-md-3">
            <div class="user-sidebar">
                <div class="user-body user-profile text-center mx-0 px-0">
                    <div class="user-img">
                        <img class="rounded-circle avatar-90 image-fluid profile_image_preview"
                            src="{{ getSingleMedia($user_data, 'profile_image', null) }}" alt="{{ __('messages.profile_image') }}">
                    </div>
                    <div class="sideuser-info">
                        <span class="mb-2">{{ $user_data->first_name . ' ' . $user_data->last_name }}</span>
                        <!-- <a>{{ $user_data->email }}</a> -->
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="user-content">
                {{ html()->form('POST', route('updateProfile'))->attribute('data-toggle', 'validator')->attribute('enctype', 'multipart/form-data')->id('user-form')->open() }}

                <input type="hidden" name="profile" value="profile">
                {{ html()->hidden('username') }}
                {{ html()->hidden('email') }}
                {{ html()->hidden('id', $user_data->id ?? null)->placeholder('id')->class('form-control') }}
                <div class="row ">

                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.first_name') . ' <span class="text-danger">*</span>')->class('form-control-label text-danger')->for('first_name') }}
                        {{ html()->text('first_name', $user_data->first_name)->placeholder(__('messages.first_name'))->class('form-control')->required() }}
                        <small class="help-block with-errors text-danger"></small>
                    </div>

                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.last_name') . ' <span class="text-danger">*</span>')->class('form-control-label text-danger')->for('last_name') }}
                        {{ html()->text('last_name', $user_data->last_name)->placeholder(__('messages.last_name'))->class('form-control')->required() }}
                        <small class="help-block with-errors text-danger"></small>
                    </div>
                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.select_name', ['select' => __('messages.language')]) . ' <span class="text-danger">*</span>', 'languages')->class('form-control-label text-danger')->for('languages') }}
                        <br />
                        {{ html()->select(
                                'languages[]',
                                config('spoken_language_options'),
                                old('languages', $user_data->languages ?? []),
                            )->class('form-group select2js')->multiple()->required()->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.language')])) }}
                    </div>


                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.username') . ' <span class="text-danger">*</span>')->class('form-control-label text-danger')->for('username') }}
                        {{ html()->text('username', $user_data->username)->placeholder(__('messages.username'))->class('form-control')->required() }}
                        <small class="help-block with-errors text-danger"></small>
                    </div>
                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.select_name', ['select' => __('messages.country')]) . ' <span class="text-danger">*</span>', 'country_id')->class('form-control-label text-danger') }}
                        <br />
                        {{ html()->select(
                                'country_id',
                                [optional($user_data->country)->id => optional($user_data->country)->name],
                                optional($user_data->country)->id,
                            )->class('form-group select2js country')->required()->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.country')]))->attribute('data-ajax--url', route('ajax-list', ['type' => 'country'])) }}
                    </div>

                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.select_name', ['select' => __('messages.state')]) . ' <span class="text-danger">*</span>', 'state_id')->class('form-control-label text-danger') }}
                        <br />
                        {{ html()->select(
                                'state_id',
                                [optional($user_data->state)->id => optional($user_data->state)->name],
                                optional($user_data->state)->id,
                            )->class('form-group select2js state_id')->required()->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.state')])) }}
                    </div>

                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.select_name', ['select' => __('messages.city')]) . ' <span class="text-danger">*</span>', 'city_id')->class('form-control-label text-danger') }}
                        <br />
                        {{ html()->select(
                                'city_id',
                                [optional($user_data->city)->id => optional($user_data->city)->name],
                                optional($user_data->city)->id,
                            )->class('form-group select2js city_id')->required()->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.city')])) }}
                    </div>
                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.country_tax'), 'tax_country_id')->class('form-control-label') }}
                        <br />
                        {{ html()->select(
                                'tax_country_id',
                                [optional($user_data->country)->id => optional($user_data->country)->name],
                                optional($user_data->country)->id,
                            )->class('form-group select2js tax_country')->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.tax_country')]))->attribute('data-ajax--url', route('ajax-list', ['type' => 'country']))->attribute('disabled', true) }}
                    </div>
                    <input type="hidden" name="tax_country_id" value="{{ optional($user_data->country)->id }}">


                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.email') . ' <span class="text-danger">*</span>', 'email')->class('form-control-label text-danger') }}
                        {{ html()->email('email', $user_data->email)->placeholder(__('messages.email'))->class('form-control')->required()->attribute('pattern', '[^@]+@[^@]+\.[a-zA-Z]{2,}')->attribute('title', __('messages.email_valid_title')) }}
                        <small class="help-block with-errors text-danger"></small>
                    </div>

                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.contact_number') . ' <span class="text-danger">*</span>', 'contact_number')->class('form-control-label text-danger') }}
                        {{ html()->text('contact_number', $user_data->contact_number)->placeholder(__('messages.contact_number'))->class('form-control contact_number')->required() }}
                        <small class="help-block with-errors text-danger " id="contact_number_err"></small>
                    </div>

                    @if (auth()->user()->hasRole('handyman'))
                        <div class="form-group col-md-6">
                            {{ html()->label(__('messages.select_name', ['select' => __('messages.provider_address')]) . ' <span class="text-danger">*</span>', 'name')->class('form-control-label text-danger') }}
                            <br />
                            {{ html()->select(
                                    'service_address_id',
                                    [optional($user_data->handymanAddressMapping)->id => optional($user_data->handymanAddressMapping)->address],
                                    $user_data->service_address_id,
                                )->class('select2js form-group service_address_id')->id('service_address_id')->attribute(
                                    'data-ajax--url',
                                    route('ajax-list', ['type' => 'provider_address', 'provider_id' => $user_data->provider_id]),
                                )->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.provider_address')]))->required() }}
                        </div>
                    @endif

                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.status') . ' <span class="text-danger">*</span>', 'status')->class('form-control-label text-danger') }}
                        {{ html()->select('status', ['1' => __('messages.active'), '0' => __('messages.inactive')], $user_data->status)->class('form-control select2js')->required() }}
                    </div>

                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.choose_profile_image'), 'profile_image')->class('form-control-label') }}
                        <div class="custom-file">
                            {{ html()->file('profile_image')->class('custom-file-input custom-file-input-sm detail')->id('profile_image')->attribute('accept', 'image/*') }}
                            <label class="custom-file-label upload-label" id="imagelabel"
                                for="profile_image">{{ __('messages.profile_image') }}</label>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.company_name'), 'company_name')->class('form-control-label')->for('company_name') }}
                        {{ html()->text('company_name', $user_data->company_name)->placeholder(__('messages.company_name'))->class('form-control') }}
                        <small class="help-block with-errors text-danger"></small>
                    </div>

                    <div class="form-group col-md-6">
                        @php $vatRequired = in_array($user_data->user_type ?? '', ['provider', 'handyman'], true); @endphp
                        {{ html()->label(__('messages.vat_number') . ($vatRequired ? ' <span class="text-danger">*</span>' : ''))->class('form-control-label' . ($vatRequired ? ' text-danger' : ''))->for('vat_number') }}
                        {{ html()->text('vat_number', $user_data->vat_number)->placeholder(__('messages.vat_number'))->class('form-control')->required($vatRequired) }}
                        <small class="help-block with-errors text-danger"></small>
                    </div>


                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.skills'))->class('form-control-label')->for('skills') }}
                        {{ html()->text('skills', $user_data->skills)->placeholder(__('messages.skills'))->class('form-control') }}
                        <small class="help-block with-errors text-danger"></small>
                    </div>

                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.education'), 'education')->class('form-control-label')->for('education') }}
                        {{ html()->select(
                                'education',
                                [
                                    '' => __('messages.select_name', ['select' => __('messages.education')]),
                                    'any_graduate' => __('messages.education_any_graduate'),
                                    'apprenticeship_degree' => __('messages.education_apprenticeship_degree'),
                                    'traineeship_degree' => __('messages.education_traineeship_degree'),
                                    'secondary_degree' => __('messages.education_secondary_degree'),
                                    'undergraduate_diploma' => __('messages.education_undergraduate_diploma'),
                                    'high_school_graduate' => __('messages.education_high_school_graduate'),
                                    'associate_degree' => __('messages.education_associate_degree'),
                                    'college_degree' => __('messages.education_college_degree'),
                                    'university_degree' => __('messages.education_university_degree'),
                                    'bachelors_degree' => __('messages.education_bachelor_degree'),
                                    'masters_degree' => __('messages.education_master_degree'),
                                    'doctorate_degree' => __('messages.education_doctorate_degree'),
                                    'professional_degree' => __('messages.education_professional_degree'),
                                    'not_specified_2' => __('messages.education_not_specified_2'),
                                    'any_graduate_2' => __('messages.education_any_graduate_2'),
                                    'apprenticeship_degree_2' => __('messages.education_apprenticeship_degree_2'),
                                    'traineeship_degree_2' => __('messages.education_traineeship_degree_2'),
                                    'secondary_degree_2' => __('messages.education_secondary_degree_2'),
                                    'undergraduate_diploma_2' => __('messages.education_undergraduate_diploma_2'),
                                ],
                                $user_data->education ?? '',
                            )->class('form-control')->attribute('data-placeholder', __('messages.education')) }}
                        <small class="help-block with-errors text-danger"></small>
                    </div>

                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.career_level'))->class('form-control-label')->for('career_level') }}
                        {{ html()->select(
                                'career_level',
                                [
                                    'not_specified' => __('messages.career_level_not_specified'),
                                    'entry_level' => __('messages.career_level_entry_level'),
                                    'intermediate_level' => __('messages.career_level_intermediate_level'),
                                    'experienced' => __('messages.career_level_experienced'),
                                    'professional' => __('messages.career_level_professional'),
                                    'middle_management' => __('messages.career_level_middle_management'),
                                    'executive_management' => __('messages.career_level_executive_management'),
                                    'senior_management' => __('messages.career_level_senior_management'),
                                    'director' => __('messages.career_level_director'),
                                    'technician' => __('messages.career_level_technician'),
                                    'leader' => __('messages.career_level_leader'),
                                    'manager' => __('messages.career_level_manager'),
                                ],
                                old('career_level', $user_data->career_level ?: 'not_specified'),
                            )->class('form-control')->required()->attribute('data-placeholder', __('messages.career_level')) }}
                        <small class="help-block with-errors text-danger"></small>
                    </div>

                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.years_of_experience'))->class('form-control-label')->for('years_of_experience') }}
                        {{ html()->select(
                                'years_of_experience',
                                [
                                    '' => __('messages.select_name', ['select' => __('messages.years_of_experience')]),
                                    'less_than_1' => __('messages.years_of_experience_less_than_1'),
                                    '1_to_3' => __('messages.years_of_experience_1_to_3'),
                                    '3_to_5' => __('messages.years_of_experience_3_to_5'),
                                    '5_to_8' => __('messages.years_of_experience_5_to_8'),
                                    '8_to_10' => __('messages.years_of_experience_8_to_10'),
                                    'more_than_10' => __('messages.years_of_experience_more_than_10'),
                                ],
                                $user_data->years_of_experience ?? '',
                            )->class('form-control')->attribute('data-placeholder', __('messages.years_of_experience')) }}
                        <small class="help-block with-errors text-danger"></small>
                    </div>

                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.certification'))->class('form-control-label')->for('certification') }}
                        {{ html()->text('certification', $user_data->certification)->placeholder(__('messages.certification'))->class('form-control') }}
                        <small class="help-block with-errors text-danger"></small>
                    </div>

                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.availability'), 'availability')->class('form-control-label') }}
                        @php
                            // Convert old values (1/0 or 'Full-time'/'Part-time') to new format for display
                            $availabilityValue = $user_data->availability;
                            if ($availabilityValue == '1' || $availabilityValue == 1 || $availabilityValue == 'Full-time') {
                                $availabilityValue = 'full_time';
                            } elseif ($availabilityValue == '0' || $availabilityValue == 0 || $availabilityValue == 'Part-time') {
                                $availabilityValue = 'part_time';
                            }
                        @endphp
                        {{ html()->select(
                                'availability',
                                [
                                    '' => __('messages.select_name', ['select' => __('messages.availability')]),
                                    'full_time' => __('messages.full_time'),
                                    'part_time' => __('messages.part_time'),
                                ],
                                $availabilityValue,
                            )->class('form-control')->required() }}
                        <small class="help-block with-errors text-danger"></small>
                    </div>

                    <div class="form-group col-md-6">
                        {{ html()->label(__('messages.mobility'))->class('form-control-label')->for('mobility') }}
                        {{ html()->text('mobility', $user_data->mobility)->placeholder(__('messages.mobility'))->class('form-control') }}
                        <small class="help-block with-errors text-danger"></small>
                    </div>







                    @if (auth()->user()->hasRole('provider'))
                        <div class="form-group col-md-6">
                            {{ html()->label(__('messages.designation'))->class('form-control-label')->for('designation') }}
                            {{ html()->text('designation', $user_data->designation)->placeholder(__('messages.designation'))->class('form-control') }}
                            <small class="help-block with-errors text-danger"></small>
                        </div>
                    @endif
                    <div class="form-group col-md-12">
                        {{ html()->label(__('messages.experience'), 'experience')->class('form-control-label') }}
                        {{ html()->textarea('experience', strip_tags((string) $user_data->experience))->class('form-control textarea')->rows(2)->placeholder(__('messages.experience'))->id('experience') }}
                    </div>
                    <div class="form-group col-md-12">
                        {{ html()->label(__('messages.about_me'))->class('form-control-label')->for('about_me') }}
                        {{ html()->textarea('about_me', strip_tags((string) $user_data->about_me))->class('form-control textarea')->rows(2)->placeholder(__('messages.about_me'))->id('about_me') }}
                    </div>


                    <div class="form-group col-md-12">
                        {{ html()->label(__('messages.address') . ' <span class="text-danger">*</span>', 'address')->class('form-control-label text-danger') }}
                        {{ html()->textarea('address', strip_tags((string) $user_data->address))->class('form-control textarea')->rows(2)->placeholder(__('messages.address'))->id('address')->required() }}
                    </div>

                    @if ($user_data->user_type == 'provider')

                        <div class="form-group col-md-12 mt-4">
                            <h4>{{ __('messages.why_choose_me') }}</h4>
                        </div>

                        <div class="form-group col-md-12">
                            {{ html()->label(__('messages.title'))->class('form-control-label')->for('title') }}
                            {{ html()->text('title', $user_data->title)->class('form-control')->placeholder(__('messages.title')) }}
                            <small class="help-block with-errors text-danger"></small>
                        </div>

                        <div class="form-group col-md-12">
                            {{ html()->label(__('messages.description'))->class('form-control-label')->for('about_description') }}
                            {{ html()->textarea('about_description', $user_data->about_description)->class('form-control textarea')->rows(2)->placeholder(__('messages.description'))->id('about_description') }}
                        </div>



                        @if ($user_data->reason != null)

                            @foreach ($user_data->reason as $reason)
                                <div class="form-section1 form-group col-md-12 ">
                                    <div class="row">
                                        <div class="form-group col-md-12 d-flex">
                                            {{ html()->text('reason[]', $reason)->placeholder(__('messages.reason'))->class('form-control') }}
                                            <small class="help-block with-errors text-danger"></small>
                                            <div class="form-group col-3 mb-0 align-self-center">
                                                <button class="remove-section1 button-custom button-remove"
                                                    data-title="remove" title="{{ __('landingpage.remove') }}">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        @endif

                        <div class="form-section form-group col-md-12 ">
                            {{ html()->label(__('messages.reason'))->class('form-control-label')->for('reason') }}
                            <div class="row">
                                <div class="form-group col-md-12 d-flex">
                                    {{ html()->text('reason[]')->placeholder(__('messages.reason'))->class('form-control') }}
                                    <small class="help-block with-errors text-danger"></small>

                                    <div class="form-group mb-0 col-3 align-self-center">

                                        <button class="remove-section  button-custom button-remove"> <i
                                                class="far fa-trash-alt"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="form-group row">
                                <div class="col-md-9 text-md-right pe-1">
                                    <button type="button" id="add-section" class="button-custom button-added">
                                        <i class="fas fa-plus me-2"></i>{{ __('messages.profile_add_more_reason') }}
                                    </button>
                                </div>
                                <div class="col-md-3"></div>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-12">
                        {{ html()->submit(__('messages.update'))->class('btn btn-md btn-primary float-md-end')->id('profile-submit-button')->attribute('data-default-text', __('messages.update')) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<style>.ql-editor{min-height:200px}</style>
<script>
    $(document).ready(function () {
        $('.select2js').select2({
            width: '100%',
        });

        var country_id = "{{ isset($user_data->country_id) ? $user_data->country_id : 0 }}";
        var state_id = "{{ isset($user_data->state_id) ? $user_data->state_id : 0 }}";
        var city_id = "{{ isset($user_data->city_id) ? $user_data->city_id : 0 }}";

        stateName(country_id, state_id);

        // When country changes
        $(document).on('change', '#country_id', function () {
            var country = $(this).val();
            var selectedText = $("#country_id option:selected").text();

            $('#state_id').empty();
            $('#city_id').empty();
            stateName(country);

            // === Sync tax_country_id (disabled select)
            $('#tax_country_id').empty(); // Clear to avoid duplicates
            var newOption = new Option(selectedText, country, true, true);
            $('#tax_country_id').append(newOption).trigger('change');

            // === Update hidden input for submission
            $('input[name="tax_country_id"]').val(country);
        });

        // When state changes
        $(document).on('change', '#state_id', function () {
            var state = $(this).val();
            $('#city_id').empty();
            cityName(state, city_id);
        });

        // Add/Remove Section
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

        // Contact number validation
        $(document).on('keyup', '.contact_number', function () {
            var contactNumberInput = document.getElementById('contact_number');
            var inputValue = contactNumberInput.value;
            inputValue = inputValue.replace(/[^0-9+\- ]/g, '');

            if (inputValue.length > 15) {
                inputValue = inputValue.substring(0, 15);
                $('#contact_number_err').text({!! json_encode(__('messages.contact_number_max_15')) !!});
            } else {
                $('#contact_number_err').text('');
            }

            contactNumberInput.value = inputValue;

            if (inputValue.match(/^[0-9+\- ]+$/)) {
                $('#contact_number_err').text('');
            } else {
                $('#contact_number_err').text({!! json_encode(__('messages.valid_mobile_number')) !!});
            }
        });

        // State AJAX function
        function stateName(country, state = "") {
            var state_route = "{{ route('ajax-list', ['type' => 'state', 'country_id' => '']) }}" + country;
            state_route = state_route.replace('amp;', '');

            $.ajax({
                url: state_route,
                success: function (result) {
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

        // City AJAX function
        function cityName(state, city = "") {
            var city_route = "{{ route('ajax-list', ['type' => 'city', 'state_id' => '']) }}" + state;
            city_route = city_route.replace('amp;', '');

            $.ajax({
                url: city_route,
                success: function (result) {
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

        // Profile image preview
        $(document).on('change', '#profile_image', function () {
            readURL(this);
        });

        $(document).on('submit', '#user-form', function () {
            if (window.profileImagePreparing) {
                Snackbar.show({
                    text: 'Please wait, image is preparing for upload.',
                    pos: 'bottom-center',
                    backgroundColor: '#d32f2f',
                    actionTextColor: '#fff'
                });
                return false;
            }
        });

        async function readURL(input) {
            if (input.files && input.files[0]) {
                var res = isImage(input.files[0].name);

                if (res == false) {
                    var msg = "{{ __('messages.image_png_gif') }}";
                    Snackbar.show({
                        text: msg,
                        pos: 'bottom-center',
                        backgroundColor: '#d32f2f',
                        actionTextColor: '#fff'
                    });
                    return false;
                }

                setProfileImagePreparing(true);

                try {
                    const preparedFile = await prepareProfileImageForUpload(input.files[0]);
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(preparedFile);
                    input.files = dataTransfer.files;

                    const objectUrl = URL.createObjectURL(preparedFile);
                    $('.profile_image_preview').attr('src', objectUrl);
                    $('.profile_image_preview').one('load', function () {
                        URL.revokeObjectURL(objectUrl);
                    });
                    $("#imagelabel").text(preparedFile.name);
                } finally {
                    setProfileImagePreparing(false);
                }
            }
        }

        function setProfileImagePreparing(isPreparing) {
            window.profileImagePreparing = isPreparing;

            const submitButton = document.getElementById('profile-submit-button');
            if (!submitButton) return;

            if (!submitButton.dataset.defaultText) {
                submitButton.dataset.defaultText = submitButton.value || submitButton.textContent || 'Update';
            }

            submitButton.disabled = isPreparing;
            if (submitButton.tagName === 'INPUT') {
                submitButton.value = isPreparing ? 'Preparing image...' : submitButton.dataset.defaultText;
            } else {
                submitButton.textContent = isPreparing ? 'Preparing image...' : submitButton.dataset.defaultText;
            }
        }

        function prepareProfileImageForUpload(file) {
            const maxSize = 450 * 1024;
            const maxDimension = 1200;

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

                    compressProfileImage(canvas, file, [0.78, 0.68, 0.58], maxSize, resolve);
                };

                image.onerror = function() {
                    URL.revokeObjectURL(objectUrl);
                    resolve(file);
                };

                image.src = objectUrl;
            });
        }

        function compressProfileImage(canvas, originalFile, qualities, maxSize, resolve) {
            const quality = qualities.shift() || 0.58;

            canvas.toBlob(function(blob) {
                if (!blob) {
                    resolve(originalFile);
                    return;
                }

                if (blob.size > maxSize && qualities.length) {
                    compressProfileImage(canvas, originalFile, qualities, maxSize, resolve);
                    return;
                }

                const safeName = originalFile.name.replace(/\.[^.]+$/, '') + '.jpg';
                resolve(new File([blob], safeName, {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                }));
            }, 'image/jpeg', quality);
        }

        // Show image file name if already uploaded
        var currentImage = "{{ getSingleMedia($user_data, 'profile_image', null) }}";
        if (currentImage !== "") {
            var fileName = currentImage.split('/').pop();
            $('#imagelabel').text(fileName);
        }

        function getExtension(filename) {
            var parts = filename.split('.');
            return parts[parts.length - 1];
        }

        function isImage(filename) {
            var ext = getExtension(filename);
            switch (ext.toLowerCase()) {
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'gif':
                    return true;
            }
            return false;
        }

        // === Initialize tax_country_id with user's current value ===
        let initialTaxCountryId = "{{ optional($user_data->country)->id }}";
        let initialTaxCountryName = "{{ optional($user_data->country)->name }}";
        if (initialTaxCountryId && initialTaxCountryName) {
            let initialOption = new Option(initialTaxCountryName, initialTaxCountryId, true, true);
            $('#tax_country_id').append(initialOption).trigger('change');
            $('input[name="tax_country_id"]').val(initialTaxCountryId);
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function mountQuill(textareaId) {
            var textarea = document.getElementById(textareaId);
            if (!textarea) return;
            var container = document.createElement('div');
            container.id = textareaId + '_quill';
            container.innerHTML = textarea.value || '';
            textarea.classList.add('d-none');
            textarea.parentNode.insertBefore(container, textarea.nextSibling);
            var quill = new Quill('#' + container.id, {
                theme: 'snow',
                modules: { toolbar: [['bold','italic','underline'],[{list:'ordered'},{list:'bullet'}],['link'],['clean']] }
            });
            var form = textarea.closest('form');
            if (form) {
                form.addEventListener('submit', function(){ textarea.value = quill.root.innerHTML; });
            }
        }
        ['about_description'].forEach(mountQuill);
    });
</script>
