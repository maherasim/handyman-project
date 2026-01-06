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
                                {{ html()->password('password')->class('form-control')->placeholder(__('messages.password'))->required()->autocomplete('new-password') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            @endif
                    
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.designation'), 'designation')->class('form-control-label') }}
                                {{ html()->text('designation',  $providerdata->designation)->placeholder(__('messages.designation'))->class('form-control') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.providertype')]) . ' <span class="text-danger">*</span>', 'providertype_id')->class('form-control-label') }}
                                <br />
                                {{ html()->select('providertype_id', [optional($providerdata->providertype)->id => optional($providerdata->providertype)->name], optional($providerdata->providertype)->id)
                                    ->class('select2js form-group providertype')
                                    ->required()
                                    ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.providertype')]))
                                    ->attribute('data-ajax--url', route('ajax-list', ['type' => 'providertype'])) }}
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
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.tax')]), 'tax_id')->class('form-control-label') }}
                                <br />
                                {{ html()->select('tax_id[]', [], old('tax_id'))
                                    ->class('select2js form-group tax_id')
                                    ->id('tax_id')
                                    ->multiple()
                                    ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.tax')])) }}
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
                                    <input type="file" name="profile_image" class="custom-file-input" accept="image/*">
                                    <label
                                        class="custom-file-label upload-label">{{  __('messages.choose_file',['file' =>  __('messages.profile_image') ]) }}</label>
                                </div>
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
                                        [
                                            'afrikaans' => 'Afrikaans',
                                            'albanian' => 'Albanian',
                                            'amharic' => 'Amharic',
                                            'arabic' => 'Arabic',
                                            'armenian' => 'Armenian',
                                            'assamese' => 'Assamese',
                                            'azerbaijani' => 'Azerbaijani (Azeri)',
                                            'bassa' => 'Bassa',
                                            'belarusian' => 'Belarusian',
                                            'bengali' => 'Bengali',
                                            'bosnian' => 'Bosnian',
                                            'braille' => 'Braille',
                                            'bulgarian' => 'Bulgarian',
                                            'burmese' => 'Burmese',
                                            'cambodian' => 'Cambodian',
                                            'cape_verde_creole' => 'Cape Verde Creole',
                                            'cebuano' => 'Cebuano',
                                            'central_kurdish' => 'Central Kurdish',
                                            'cherokee' => 'Cherokee',
                                            'chinese' => 'Chinese',
                                            'chuukese' => 'Chuukese',
                                            'croatian' => 'Croatian',
                                            'czech' => 'Czech',
                                            'danish' => 'Danish',
                                            'dari' => 'Dari',
                                            'dutch' => 'Dutch',
                                            'english' => 'English',
                                            'estonian' => 'Estonian',
                                            'farsi' => 'Farsi (Persian)',
                                            'finnish' => 'Finnish',
                                            'flemmish' => 'Flemmish',
                                            'french' => 'French',
                                            'fulani' => 'Fulani',
                                            'galician' => 'Galician',
                                            'georgian' => 'Georgian',
                                            'german' => 'German',
                                            'greek' => 'Greek',
                                            'gujarati' => 'Gujarati',
                                            'haitian_creole' => 'Haitian Creole',
                                            'hakha_chin' => 'Hakha Chin',
                                            'hakka' => 'Hakka (Chinese)',
                                            'hausa' => 'Hausa',
                                            'hebrew' => 'Hebrew',
                                            'hindi' => 'Hindi',
                                            'hmong' => 'Hmong',
                                            'hungarian' => 'Hungarian',
                                            'icelandic' => 'Icelandic',
                                            'igbo' => 'Igbo/Ibo',
                                            'ilocano' => 'Ilocano',
                                            'ilonggo' => 'Ilonggo (Hiligaynon)',
                                            'indonesian' => 'Indonesian',
                                            'irish' => 'Irish',
                                            'isixhosa' => 'isiXhosa',
                                            'isizulu' => 'isiZulu',
                                            'italian' => 'Italian',
                                            'japanese' => 'Japanese',
                                            'javanese' => 'Javanese',
                                            'kannada' => 'Kannada',
                                            'karen' => 'Karen',
                                            'kazakh' => 'Kazakh',
                                            'khmer' => 'Khmer',
                                            'kiche' => "K'iche'",
                                            'kinyarwanda' => 'Kinyarwanda',
                                            'kirundi' => 'Kirundi',
                                            'kiswahili' => 'KiSwahili',
                                            'konkani' => 'Konkani',
                                            'korean' => 'Korean',
                                            'kurdish' => 'Kurdish',
                                            'kyrgyz' => 'Kyrgyz/Kirgiz',
                                            'lao' => 'Lao (Laotian)',
                                            'latvian' => 'Latvian',
                                            'lithuanian' => 'Lithuanian',
                                            'luxembourgish' => 'Luxembourgish',
                                            'macedonian' => 'Macedonian',
                                            'malay' => 'Malay',
                                            'malayalam' => 'Malayalam',
                                            'maltese' => 'Maltese',
                                            'mandinka' => 'Mandinka',
                                            'maori' => 'Maori',
                                            'marathi' => 'Marathi',
                                            'marshallese' => 'Marshallese',
                                            'mien' => 'Mien',
                                            'mongolian' => 'Mongolian',
                                            'montenegrin' => 'Montenegrin',
                                            'navajo' => 'Navajo',
                                            'nepali' => 'Nepali',
                                            'norwegian' => 'Norwegian',
                                            'odia' => 'Odia',
                                            'oromo' => 'Oromo',
                                            'pashto' => 'Pashto',
                                            'persian' => 'Persian',
                                            'polish' => 'Polish',
                                            'portuguese' => 'Portuguese',
                                            'punjabi' => 'Punjabi',
                                            'quechua' => 'Quechua',
                                            'rohingya' => 'Rohingya',
                                            'romanian' => 'Romanian',
                                            'russian' => 'Russian',
                                            'scottish_gaelic' => 'Scottish Gaelic',
                                            'serbian' => 'Serbian',
                                            'sesotho_sa_leboa' => 'Sesotho sa Leboa',
                                            'setswana' => 'Setswana',
                                            'sindhi' => 'Sindhi',
                                            'sinhala' => 'Sinhala',
                                            'slovak' => 'Slovak',
                                            'slovenian' => 'Slovenian',
                                            'somali' => 'Somali',
                                            'spanish' => 'Spanish',
                                            'swahili' => 'Swahili',
                                            'swedish' => 'Swedish',
                                            'tagalog' => 'Tagalog',
                                            'tajik' => 'Tajik',
                                            'tamil' => 'Tamil',
                                            'tatar' => 'Tatar',
                                            'telugu' => 'Telugu',
                                            'thai' => 'Thai',
                                            'tibetan' => 'Tibetan',
                                            'tigrinya' => 'Tigrinya',
                                            'turkish' => 'Turkish',
                                            'turkmen' => 'Turkmen',
                                            'ukrainian' => 'Ukrainian',
                                            'urdu' => 'Urdu',
                                            'uzbek' => 'Uzbek',
                                            'valencian' => 'Valencian',
                                            'vietnamese' => 'Vietnamese',
                                            'welsh' => 'Welsh',
                                            'wolof' => 'Wolof',
                                            'yoruba' => 'Yoruba',
                                        ],
                                        old('languages', $providerdata->languages ?? []),
                                    )->class('form-group select2js')->multiple()->attribute('data-placeholder', __('select_name', ['select' => __('messages.language')])) }}
                            </div>

                            <div class="form-group col-md-6">
                                {{ html()->label(__('messages.select_name', ['select' => __('Country tax')]), 'tax_country_id')->class('form-control-label') }}
                                <br />
                                {{ html()->select(
                                        'tax_country_id',
                                        [optional($providerdata->country)->id => optional($providerdata->country)->name],
                                        optional($providerdata->country)->id,
                                    )->class('form-group select2js tax_country')->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.tax_country')]))->attribute('data-ajax--url', route('ajax-list', ['type' => 'country']))->attribute('disabled', true) }}
                            </div>
                            <input type="hidden" name="tax_country_id" value="{{ optional($providerdata->country)->id }}">

                            <div class="form-group col-md-6">
                                {{ html()->label(__('Company Name') . ' <span class="text-danger">*</span>')->class('form-control-label')->for('company_name') }}
                                {{ html()->text('company_name', $providerdata->company_name)->placeholder(__('Company Name'))->class('form-control')->required() }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-6">
                                {{ html()->label(__('Vat Number') . ' <span class="text-danger">*</span>')->class('form-control-label')->for('vat_number') }}
                                {{ html()->text('vat_number', $providerdata->vat_number)->placeholder(__('Vat Number'))->class('form-control')->required() }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-6">
                                {{ html()->label(__('skills') . ' <span class="text-danger">*</span>')->class('form-control-label')->for('skills') }}
                                {{ html()->text('skills', $providerdata->skills)->placeholder(__('skills'))->class('form-control')->required() }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-6">
                                {{ html()->label(__('Education') . ' <span class="text-danger">*</span>')->class('form-control-label')->for('education') }}
                                {{ html()->text('education', $providerdata->education)->placeholder(__('education'))->class('form-control')->required() }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-6">
                                {{ html()->label(__('Certification') . ' <span class="text-danger">*</span>')->class('form-control-label')->for('certification') }}
                                {{ html()->text('certification', $providerdata->certification)->placeholder(__('Certification'))->class('form-control')->required() }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-6">
                                {{ html()->label(__('Availability') . ' <span class="text-danger">*</span>')->class('form-control-label')->for('availability') }}
                                {{ html()->select(
                                        'availability',
                                        [
                                            'Full-time' => 'Full-time',
                                            'Part-time' => 'Part-time',
                                        ],
                                        $providerdata->availability,
                                    )->class('form-control')->required()->placeholder(__('Select Availability')) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-6">
                                {{ html()->label(__('Mobility') . ' <span class="text-danger">*</span>')->class('form-control-label')->for('mobility') }}
                                {{ html()->text('mobility', $providerdata->mobility)->placeholder(__('Mobility'))->class('form-control')->required() }}
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
                        {{ html()->submit(__('messages.save'))->class('btn btn-md btn-primary float-end') }}
                        {{ html()->form()->close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @php
    $data = $providerdata->providerTaxMapping->pluck('tax_id')->implode(',');
    @endphp
    @section('bottom_script')
    <script type="text/javascript">
    (function($) {
        "use strict";
        $(document).ready(function() {
            var country_id = "{{ isset($providerdata->country_id) ? $providerdata->country_id : 0 }}";
            var state_id = "{{ isset($providerdata->state_id) ? $providerdata->state_id : 0 }}";
            var city_id = "{{ isset($providerdata->city_id) ? $providerdata->city_id : 0 }}";

            var provider_id = "{{ isset($providerdata->id) ? $providerdata->id : '' }}";
            var provider_tax_id = "{{ isset($data) ? $data : [] }}";

            getTax(provider_id, provider_tax_id)
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

        function getTax(provider_id, provider_tax_id = "") {
            var provider_tax_route = "{{ route('ajax-list', [ 'type' => 'provider_tax','provider_id' =>'']) }}" +
                provider_id;
            provider_tax_route = provider_tax_route.replace('amp;', '');

            $.ajax({
                url: provider_tax_route,
                success: function(result) {
                    $('#tax_id').select2({
                        width: '100%',
                        placeholder: "{{ trans('messages.select_name',['select' => trans('messages.tax')]) }}",
                        data: result.results
                    });
                    if (provider_tax_id != "") {
                        $('#tax_id').val(provider_tax_id.split(',')).trigger('change');
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
    </script>
    @endsection
</x-master-layout>