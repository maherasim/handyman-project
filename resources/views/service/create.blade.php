<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="fw-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                            <a href="{{ route('service.index') }}" class=" float-end btn btn-sm btn-primary"><i
                                    class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                            @if($auth_user->can('service list'))
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                         {{ html()->form('POST', route('service.store'))
                            ->attribute('enctype', 'multipart/form-data')
                            ->attribute('data-toggle', 'validator')
                            ->id('service')
                            ->open()
                        }}
                        {{ html()->hidden('id',$servicedata->id ?? null) }}

                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.name') . ' <span class="text-danger">*</span>', 'name')->class('form-control-label') }}
                                {{ html()->text('name', $servicedata->name)->placeholder(__('messages.name'))->class('form-control')->attributes(['title' => 'Please enter alphabetic characters and spaces only'])}}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.select_name',['select' => __('messages.category') ]).' <span class="text-danger">*</span>', 'name')->class('form-control-label') }}
                                <br />
                                {{ html()->select('category_id', [optional($servicedata->category)->id => optional($servicedata->category)->name], optional($servicedata->category)->id)
                                    ->class('select2js form-group category')
                                    ->required()
                                    ->id('category_id')
                                    ->attribute('data-placeholder', __('messages.select_name',[ 'select' => __('messages.category') ]))
                                    ->attribute('data-ajax--url', route('ajax-list', ['type' => 'category']))
                                }}

                            </div>
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.select_name',[ 'select' => __('messages.subcategory')]), 'subcategory_id')->class('form-control-label') }}
                                <br />
                                {{ html()->select('subcategory_id', [])
                                    ->class('select2js form-group subcategory_id')
                                    ->attribute('data-placeholder', __('messages.select_name',[ 'select' => __('messages.subcategory') ]))
                                }}
                            </div>

                            <div class="form-group col-md-6">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.country')]), 'country_id')->class('form-control-label') }}
                                <br />
                                {{ html()->select(
                                        'country_id',
                                       // $countries,  // assuming $countries is a list of countries available in your controller
                                        optional($servicedata->country)->id,  // this ensures the selected country is set
                                    )->class('form-group select2js country')
                                    ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.country')]))
                                    ->attribute('data-ajax--url', route('ajax-list', ['type' => 'country']))
                                }}
                            </div>
                            
        
                            <div class="form-group col-md-6">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.state')]), 'state_id')->class('form-control-label') }}
                                <br />
                                {{ html()->select(
                                        'state_id',
                                        [optional($servicedata->state)->id => optional($servicedata->state)->name],
                                        optional($servicedata->state)->id,
                                    )->class('form-group select2js state_id')->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.state')])) }}
                            </div>
        
                            <div class="form-group col-md-6">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.city')]), 'city_id')->class('form-control-label') }}
                                <br />
                                {{ html()->select(
                                        'city_id',
                                        [optional($servicedata->city)->id => optional($servicedata->city)->name],
                                        optional($servicedata->city)->id,
                                    )->class('form-group select2js city_id')->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.city')])) }}
                            </div>
        










                          

                            @if(auth()->user()->hasAnyRole(['admin','demo_admin']))
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.select_name',[ 'select' => __('messages.provider') ]).' <span class="text-danger">*</span>','name')->class('form-control-label') }}
                                <br />
                                {{ html()->select('provider_id', [ optional($servicedata->providers)->id => optional($servicedata->providers)->display_name], optional($servicedata->providers)->id)
                                    ->class('select2js form-group')
                                    ->id('provider_id')
                                    ->attribute('onchange', 'selectprovider(this)')
                                    ->required()
                                    ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.provider')]))
                                    ->attribute('data-ajax--url', route('ajax-list', ['type' => 'provider']))
                                }}
                            </div>
                            @endif
                            <div class="form-group col-md-4">
                                {{ html()->label( __('messages.select_name',[ 'select' => __('messages.provider_address') ]),'name')->class('form-control-label') }}
                                <br />
                                {{ html()->select('provider_address_id[]', [], old('provider_address_id'))
                                        ->class('select2js form-group provider_address_id')
                                        ->id('provider_address_id')
                                        ->multiple()
                                        ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.provider_address')]))
                                }}
                               
                                 @if(auth()->user()->hasAnyRole(['provider']))
                                    <a href="{{ route('provideraddress.create', ['provideraddress' => auth()->id()]) }}" id="add_provider_address_link" class=""><i class="fa fa-plus-circle mt-2"></i>
                                 {{ trans('messages.add_form_title',['form' => trans('messages.provider_address')  ]) }}</a>
                                 @else
                                    <a href="#" id="add_provider_address_link" class=""><i class="fa fa-plus-circle mt-2"></i>
                                 {{ trans('messages.add_form_title',['form' => trans('messages.provider_address')  ]) }}</a>
                                 @endif
                            </div> 

                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.price_type') . ' <span class="text-danger">*</span>', 'type')->class('form-control-label') }}
                                {{ html()->select('type', [
                                    'fixed' => __('messages.fixed'),
                                    'hourly' => __('messages.hourly'),
                                    'Daily' => __('daily'), // Add 'daily' option here
                                    'free' => __('messages.free')
                                ], $servicedata->type)->class('form-control select2js')->required()->id('price_type') }}
                            </div>
                            
                            <div class="form-group col-md-4" id="price_div">
                                {{ html()->label(__('messages.price') . ' <span class="text-danger">*</span>', 'price')->class('form-control-label') }}
                                {{ html()->text('price',null)->attributes(['min' => 1, 'step' => 'any', 'pattern' => '^\\d+(\\.\\d{1,2})?$'])->placeholder(__('messages.price'))->class('form-control')->required()->id('price')}}
                                <small class="help-block with-errors text-danger"></small>
                            </div>


                            <div class="form-group col-md-4" id="minimum_booking_div">
                                {{ html()->label(__('Minimum Booking'), 'minimum_booking')->class('form-control-label') }}
                                {{ html()->text('minimum_booking', isset($servicedata->minimum_booking) ? $servicedata->minimum_booking : null)->attributes(['step' => 'any'])->placeholder(__('messages.minimum_booking'))->class('form-control')->id('minimum_booking') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>


                            <div class="form-group col-md-4" id="discount_div">
                                {{ html()->label(__('messages.discount') . ' %', 'discount')->class('form-control-label') }}
                                {{ html()->number('discount',null)->attributes(['min' => 0,'max' => 99, 'step' => 'any'])->placeholder(__('messages.discount'))->class('form-control')->id('discount')}}

                                <span id="discount-error" class="text-danger"></span>
                            </div>


                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.duration') . ' (hours) ', 'duration')->class('form-control-label') }}
                                {{ html()->text('duration', $servicedata->duration)->placeholder(__('messages.duration'))->class('form-control min-datetimepicker-time')}}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.status') . ' <span class="text-danger">*</span>', 'status')->class('form-control-label') }}
                                {{ html()->select('status',['1' => __('messages.active'), '0' => __('messages.inactive')], $servicedata->status)->class('form-control select2js')->required()}}
                            </div>
                            
                                <div class="form-group col-md-4">
                                    {{ html()->label(__('messages.visit_type').' ', 'visit_type')->class('form-control-label') }}
                                    <br />
                                    {{ html()->select('visit_type', $visittype, $servicedata->visit_type)->id('visit_type')->class('form-control select2js')->required() }}
                                    </div>
    



                                    
                                <div class="form-group col-md-4">
                                <label class="form-control-label" for="service_attachment">{{ __('messages.image') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="custom-file">
                                    <input type="file" onchange="preview()"  name="service_attachment[]" class="custom-file-input"
                                        data-file-error="{{ __('messages.files_not_allowed') }}" multiple accept="image/*"  required>
                                    <label
                                        class="custom-file-label upload-label">{{ __('messages.choose_file',['file' =>  __('messages.attachments') ]) }}</label>
                                    </div>
                                </div>
                            </div>
    
    
                            <div class="row service_attachment_div">
                                <div class="col-md-12">
    
    
                                    @if(getMediaFileExit($servicedata, 'service_attachment'))
                                    @php
    
                                    $attchments = $servicedata->getMedia('service_attachment');
    
                                    $file_extention = config('constant.IMAGE_EXTENTIONS');
                                    @endphp
                                <div class="border-start">
                                    <p class="ms-2"><b>{{ __('messages.attached_files') }}</b></p>
                                    <div class="ms-2 my-3">
                                            <div class="row">
                                                @foreach($attchments as $attchment )
                                                <?php
                                                $extention = in_array(strtolower(imageExtention($attchment->getFullUrl())), $file_extention);
                                                ?>
    
                                            <div class="col-md-2 pe-10 text-center galary file-gallary-{{$servicedata->id}} position-relative"
                                                    data-gallery=".file-gallary-{{$servicedata->id}}"
                                                    id="service_attachment_preview_{{$attchment->id}}">
                                                    @if($extention)
                                                    <a id="attachment_files" href="{{ $attchment->getFullUrl() }}"
                                                        class="list-group-item-action attachment-list" target="_blank">
                                                        <img src="{{ $attchment->getFullUrl() }}" class="attachment-image"
                                                            alt="">
                                                    </a>
                                                    @else
                                                    <a id="attachment_files"
                                                        class="video list-group-item-action attachment-list"
                                                        href="{{ $attchment->getFullUrl() }}">
                                                        <img src="{{ asset('images/file.png') }}" class="attachment-file">
                                                    </a>
                                                    @endif
                                                    <a class="text-danger remove-file"
                                                        href="{{ route('remove.file', ['id' => $attchment->id, 'type' => 'service_attachment']) }}"
                                                        data--submit="confirm_form" data--confirmation='true'
                                                        data--ajax="true" data-toggle="tooltip"
                                                        title='{{ __("messages.remove_file_title" , ["name" =>  __("messages.attachments") ] ) }}'
                                                        data-title='{{ __("messages.remove_file_title" , ["name" =>  __("messages.attachments") ] ) }}'
                                                        data-message='{{ __("messages.remove_file_msg") }}'>
                                                        <i class="ri-close-circle-line"></i>
                                                    </a>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="form-control-label" for="service_attachment">{{ __('messages.image') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="custom-file">
                                            <input type="file" onchange="preview()" name="service_attachment[]"
                                                class="custom-file-input"
                                                data-file-error="{{ __('messages.files_not_allowed') }}" multiple>
                                            <label
                                                class="custom-file-label upload-label">{{ __('messages.choose_file', ['file' => __('messages.attachments')]) }}</label>
                                        </div>
                                    </div>
                                    <img id="service_attachment_preview" src="" width="150px" />
                                    @endif
                                </div>
                            </div>
    
                            <div class="row">
                                <div class="form-group col-md-12">
                                    {{ html()->label(__('messages.description'), 'description')->class('form-control-label') }}
                                    {{ html()->textarea('description', $servicedata->description)->class('form-control textarea')->rows(3)->placeholder(__('messages.description')) }}
                                </div>
                                <div class="form-group col-md-12">
                                    {{ html()->label(__('Cancellation Policy & Fees'), 'cancellation_policy')->class('form-control-label') }}
                                    {{ html()->textarea('cancellation_policy', $servicedata->cancellation_policy)->class('form-control textarea')->rows(3)->placeholder(__('cancellation_policy')) }}
                                </div>
                                @if(!empty( $slotservice) && $slotservice == 1)
                                <div class="form-group col-md-3">
                                    <div class="custom-control custom-switch">
                                        {{ html()->checkbox('is_slot', $servicedata->is_slot)->class('custom-control-input')->id('is_slot')}}
                                        <label class="custom-control-label"
                                            for="is_slot">{{ __('messages.slot') }}</label>
                                    </div>
                                </div>
                                @endif
                                @if (auth()->check() && auth()->user()->user_type === 'provider')
                                <div class="form-group col-md-3">
                                    <div class="custom-control custom-switch">
                                        {{ html()->checkbox('is_featured', $servicedata->is_featured)->class('custom-control-input')->id('is_featured')}}
                                        <label class="custom-control-label"
                                            for="is_featured">{{ __('messages.set_as_featured') }}</label>
                                    </div>
                            </div> @endif
                            <!-- @if(!empty( $digitalservicedata) && $digitalservicedata->value == 1)
                            <div class="form-group col-md-3">
                                <div class="custom-control custom-switch">
                                    {{ Form::checkbox('digital_service', $servicedata->digital_service, null, ['class' => 'custom-control-input', 'id' => 'digital_service' ]) }}
                                    <label class="custom-control-label"
                                        for="digital_service">{{ __('messages.digital_service') }}</label>
                                </div>
                            </div>
                            @endif -->
                                @if(!empty( $advancedPaymentSetting) && $advancedPaymentSetting == 1)
                                <div class="form-group col-md-3" id="is_enable_advance">
                                    <div class="custom-control custom-switch">
                                        {{ html()->checkbox('is_enable_advance_payment', $servicedata->is_enable_advance_payment)->class('custom-control-input')->id('is_enable_advance_payment')}}
                                        <label class="custom-control-label"
                                            for="is_enable_advance_payment">{{ __('messages.enable_advanced_payment')  }}
                                        </label>
                                    </div>
                                </div>
                                @endif
                                <div class="form-group col-md-4" id="amount">
                                    {{ html()->label(__('messages.advance_payment_amount').' <span class="text-danger"></span> (%)', 'advance_payment_amount')->class('form-control-label')}}
                                    {{ html()->number('advance_payment_amount', $servicedata->advance_payment_amount)->placeholder(__('messages.amount'))->class('form-control')->id('advance_payment_amount')->attributes(['min' => 1, 'max' => 99])}}
                                    <small class="help-block with-errors text-danger"></small>
                                </div>
                            </div>
    
                            {{ html()->submit( __('messages.save'))->class('btn btn-md btn-primary float-end') }}
                            {{ html()->form()->close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @php
    $data = $servicedata->providerServiceAddress->pluck('provider_address_id')->implode(',');
    @endphp
    @section('bottom_script')
    <script type="text/javascript">
     function preview() {
        service_attachment_preview.src = URL.createObjectURL(event.target.files[0]);
    }
    var discountInput = document.getElementById('discount');
    var discountError = document.getElementById('discount-error');

   
      document.addEventListener('DOMContentLoaded', function () {
        var initialProviderId = document.getElementById('provider_id').value;
        selectprovider({ value: initialProviderId }); 
        document.getElementById('add_provider_address_link').addEventListener('click', function (event) {
            event.preventDefault();
            var providerId = document.getElementById('provider_id').value;
            var providerAddressCreateUrl = "{{ route('provideraddress.create', ['provideraddress' => '']) }}";
            providerAddressCreateUrl = providerAddressCreateUrl.replace('provideraddress=', 'provideraddress=' + providerId);
            window.location.href = providerAddressCreateUrl;
        });


      
   

    });

    function selectprovider(selectElement){

        var providerId = selectElement.value;
        var addProviderAddressLink =  document.getElementById('add_provider_address_link');

        if(providerId){
            addProviderAddressLink.classList.remove('d-none');
        } else {
            addProviderAddressLink.classList.add('d-none');
        }
    }

     
    discountInput.addEventListener('input', function() {
        var discountValue = parseFloat(discountInput.value);
        if (isNaN(discountValue) || discountValue < 0 || discountValue > 99) {
            discountError.textContent = "{{ __('Discount value should be between 0 to 99') }}";
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
        $("#is_enable_advance").toggleClass('d-none', type !== 'fixed');
    }

    function updateAmountVisibility(type, isEnableAdvancePayment) {
        if (type === 'fixed' && !$("#is_enable_advance").hasClass('d-none') && isEnableAdvancePayment) {
            $("#amount").removeClass('d-none');
        } else {
            $("#amount").addClass('d-none');
        }
    }

    (function($) {
        "use strict";
        $(document).ready(function() {
            var provider_id = "{{ isset($servicedata->provider_id) ? $servicedata->provider_id : '' }}";
            var provider_address_id = "{{ isset($data) ? $data : [] }}";

            var category_id = "{{ isset($servicedata->category_id) ? $servicedata->category_id : '' }}";
            var subcategory_id =
                "{{ isset($servicedata->subcategory_id) ? $servicedata->subcategory_id : '' }}";

            var price_type = "{{ isset($servicedata->type) ? $servicedata->type : '' }}";

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
                "{{ route('ajax-list', [ 'type' => 'provider_address','provider_id' =>'']) }}" + provider_id;
            provider_address_route = provider_address_route.replace('amp;', '');

            $.ajax({
                url: provider_address_route,
                success: function(result) {
                    $('#provider_address_id').select2({
                        width: '100%',
                        placeholder: "{{ trans('messages.select_name',['select' => trans('messages.provider_address')]) }}",
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
                "{{ route('ajax-list', [ 'type' => 'subcategory_list','category_id' =>'']) }}" + category_id;
            get_subcategory_list = get_subcategory_list.replace('amp;', '');

            $.ajax({
                url: get_subcategory_list,
                success: function(result) {
                    $('#subcategory_id').select2({
                        width: '100%',
                        placeholder: "{{ trans('messages.select_name',['select' => trans('messages.subcategory')]) }}",
                        data: result.results
                    });
                    if (subcategory_id != "") {
                        $('#subcategory_id').val(subcategory_id).trigger('change');
                    }
                }
            });
        }
        var price = "{{ isset($servicedata->price) ? $servicedata->price : '' }}";
        var discount = "{{ isset($servicedata->discount) ? $servicedata->discount : '' }}";
        function priceformat(value) {
            if (value == 'free') {
                $('#price').val(0);
                $('#price').attr("readonly", true)

                $('#discount').val(0);
                $('#discount').attr("readonly", true)

            }
            else{
                $('#price').val(price);
                $('#price').attr("readonly", false)
                $('#discount').val(discount);
                $('#discount').attr("readonly", false)
            }
        }
    })(jQuery);

    document.addEventListener('DOMContentLoaded', function() { 
        checkImage();
    });
    function checkImage() { 
        var id = @json($servicedata->id); 
        var route = "{{ route('check-image', ':id') }}";
        route = route.replace(':id', id);  
        var type = 'service';

        $.ajax({
            url: route,
            type: 'GET',   
            data: {
                type: type,   
            }, 
            success: function(result) {  
                var attachments = result.results;  

                if (attachments.length === 0) { 
                    $('input[name="service_attachment[]"]').attr('required', 'required');
                } else { 
                    $('input[name="service_attachment[]"]').removeAttr('required');
                }         
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);  
            }
        });
    }


    </script>
    <script>
        // (function($) {
        // 	"use strict";
        $(document).ready(function() {
            $('.select2js').select2({
                width: '100%',
                // dropdownParent: $(this).parent()
            });
            var country_id = "{{ isset($servicedata->country_id) ? $servicedata->country_id : 0 }}";
            var state_id = "{{ isset($servicedata->state_id) ? $servicedata->state_id : 0 }}";
            var city_id = "{{ isset($servicedata->city_id) ? $servicedata->city_id : 0 }}";
    
            stateName(country_id, state_id);
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
    
            $(document).ready(function() {
                // Add Section
                $("#add-section").click(function() {
                    var newSection = $(".form-section:first").clone();
                    newSection.find('input').val(''); // Clear input values
                    $(".form-section:last").after(newSection);
                    updateRemoveButtonVisibility();
                });
    
                // Remove Section
                $(document).on('click', '.remove-section', function() {
                    if ($(".form-section").length > 1) {
                        $(this).closest('.form-section').remove();
                        updateRemoveButtonVisibility();
                    }
                });
    
                // Remove Section
                $(document).on('click', '.remove-section1', function() {
    
                    $(this).closest('.form-section1').remove();
    
                });
    
                // Function to update Remove button visibility
                function updateRemoveButtonVisibility() {
                    if ($(".form-section").length > 1) {
                        $('.remove-section').show();
                    } else {
                        $('.remove-section').hide();
                    }
                }
    
                // Initially hide Remove button if there's only one section
                updateRemoveButtonVisibility();
            });
    
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
            $(document).on('change', '#profile_image', function() {
                readURL(this);
            })
    
            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
    
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
    
                    reader.onload = function(e) {
                        $('.profile_image_preview').attr('src', e.target.result);
                        $("#imagelabel").text((input.files[0].name));
                    }
    
                    reader.readAsDataURL(input.files[0]);
                }
            }
    
    
            $(document).ready(function() {
    
                var currentImage = "{{ getSingleMedia($servicedata, 'profile_image', null) }}";
    
    
                if (currentImage !== "") {
    
                    var fileName = currentImage.split('/').pop();
    
                    $('#imagelabel').text(fileName);
                }
            });
    
    
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
        })
        // })(jQuery);
    </script>
     <script>
        tinymce.init({
            selector: '#description', // Target the ID of your textarea
            plugins: 'lists link image preview', // Add any plugins you want to use
            toolbar: 'undo redo | bold italic | bullist numlist | link image preview',
            menubar: false
        });
    </script>
     <script>
        tinymce.init({
            selector: '#cancellation_policy', // Target the ID of your textarea
            plugins: 'lists link image preview', // Add any plugins you want to use
            toolbar: 'undo redo | bold italic | bullist numlist | link image preview',
            menubar: false
        });
    </script>
    @endsection
</x-master-layout>