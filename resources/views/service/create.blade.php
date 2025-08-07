<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="fw-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                            <a href="{{ route('service.index') }}" class="float-end btn btn-sm btn-primary">
                                <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                            </a>
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
                            <!-- Name Field -->
                            <div class="form-group col-md-2">
                                {{ html()->label(__('messages.name') . ' <span class="text-danger">*</span>', 'name')->class('form-control-label') }}
                                {{ html()->text('name', $servicedata->name)->placeholder(__('messages.name'))->class('form-control')->required()->attributes(['title' => 'Please enter alphabetic characters and spaces only']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <!-- Category Field -->
                            <div class="form-group col-md-2">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.category')]) . ' <span class="text-danger">*</span>', 'category_id')->class('form-control-label') }}
                                {{ html()->select(
                                    'category_id',
                                    [optional($servicedata->category)->id => optional($servicedata->category)->name],
                                    optional($servicedata->category)->id,
                                )->class('select2js form-group category')->required()->id('category_id')->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.category')]))->attribute('data-ajax--url', route('ajax-list', ['type' => 'category'])) }}
                            </div>

                            <!-- Subcategory Field -->
                            <div class="form-group col-md-2">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.subcategory')]), 'subcategory_id')->class('form-control-label') }}
                                {{ html()->select('subcategory_id', [])->class('select2js form-group subcategory_id')->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.subcategory')])) }}
                            </div>

                            <!-- Country Field -->
                            <div class="col-md-2">
                                <label for="country_id">{{ __('messages.select_name', ['select' => __('messages.country')]) }}</label>
                                <select name="country_id" id="country_id" class="select2js country"
                                    data-placeholder="{{ __('messages.select_name', ['select' => __('messages.country')]) }}"
                                    data-ajax--url="{{ route('ajax-list', ['type' => 'country']) }}">
                                    <option value="{{ optional($servicedata->country)->id }}" selected>
                                        {{ optional($servicedata->country)->name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Tax Country Field -->
                            <div class="col-md-2">
                                <label for="tax_country_id_display">{{ __('messages.select_name', ['select' => __('Tax Country')]) }}</label>
                                {{ html()->select('tax_country_id_display', 
                                    optional($servicedata->tax_country)
                                        ? [optional($servicedata->tax_country)->id => optional($servicedata->tax_country)->name] 
                                        : []
                                )
                                ->class('form-group select2js tax_country')
                                ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.tax_country')]))
                                ->attribute('data-ajax--url', route('ajax-list', ['type' => 'country']))
                                ->attribute('disabled', true)
                                ->id('tax_country_id_display') }}
                            </div>

                            <!-- State Field -->
                            <div class="form-group col-md-2">
                                <label for="state_id">{{ __('messages.select_name', ['select' => __('messages.state')]) }} <span class="text-danger">*</span></label>
                                <select name="state_id" id="state_id" class="select2js form-group category" required
                                    data-placeholder="{{ __('messages.select_name', ['select' => __('messages.state')]) }}">
                                    @if($servicedata->state_id)
                                        <option value="{{ $servicedata->state_id }}" selected>
                                            {{ $servicedata->state->name ?? '' }}
                                        </option>
                                    @endif
                                </select>
                            </div>

                            <!-- City Field -->
                            <div class="form-group col-md-2">
                                <label for="city_id">{{ __('messages.select_name', ['select' => __('messages.city')]) }} <span class="text-danger">*</span></label>
                                <select name="city_id" id="city_id" class="select2js form-group category" required
                                    data-placeholder="{{ __('messages.select_name', ['select' => __('messages.city')]) }}">
                                    @if($servicedata->city_id)
                                        <option value="{{ $servicedata->city_id }}" selected>
                                            {{ $servicedata->city->name ?? '' }}
                                        </option>
                                    @endif
                                </select>
                            </div>

                            <input type="hidden" name="tax_country_id" id="tax_country_id" value="{{ old('tax_country_id', optional($servicedata->tax_country)->id) }}">

                            @if (auth()->user()->hasAnyRole(['admin', 'demo_admin']))
                                <div class="form-group col-md-4">
                                    {{ html()->label(__('messages.select_name', ['select' => __('messages.provider')]) . ' <span class="text-danger">*</span>', 'provider_id')->class('form-control-label') }}
                                    {{ html()->select(
                                        'provider_id',
                                        [optional($servicedata->providers)->id => optional($servicedata->providers)->display_name],
                                        optional($servicedata->providers)->id,
                                    )->class('select2js form-group')->id('provider_id')->attribute('onchange', 'selectprovider(this)')->required()->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.provider')]))->attribute('data-ajax--url', route('ajax-list', ['type' => 'provider'])) }}
                                </div>
                            @endif

                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.provider_address')]), 'provider_address_id')->class('form-control-label') }}
                                {{ html()->select('provider_address_id[]', [], old('provider_address_id'))->class('select2js form-group provider_address_id')->id('provider_address_id')->multiple()->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.provider_address')])) }}

                                <a href="{{ route('provideraddress.create', ['provideraddress' => auth()->id()]) }}" 
                                   id="add_provider_address_link" class="d-block mt-2">
                                    <i class="fa fa-plus-circle"></i>
                                    {{ trans('messages.add_form_title', ['form' => trans('messages.provider_address')]) }}
                                </a>
                            </div>

                            <!-- Price Type -->
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.price_type') . ' <span class="text-danger">*</span>', 'type')->class('form-control-label') }}
                                {{ html()->select(
                                    'type',
                                    [
                                        'fixed' => __('messages.fixed'),
                                        'hourly' => __('messages.hourly'),
                                        'Daily' => __('Daily'),
                                        'free' => __('messages.free'),
                                    ],
                                    $servicedata->type,
                                )->class('form-control select2js')->required()->id('price_type') }}
                            </div>

                            <!-- Price -->
                            <div class="form-group col-md-4" id="price_div">
                                {{ html()->label(__('messages.price') . ' <span class="text-danger">*</span>', 'price')->class('form-control-label') }}
                                {{ html()->text('price', $servicedata->price ?? null)->attributes(['min' => 1, 'step' => 'any', 'pattern' => '^\\d+(\\.\\d{1,2})?$'])->placeholder(__('messages.price'))->class('form-control')->required()->id('price') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <!-- Minimum Booking -->
                            <div class="form-group col-md-4" id="minimum_booking_div">
                                {{ html()->label(__('Minimum Booking'), 'minimum_booking')->class('form-control-label') }}
                                {{ html()->text('minimum_booking', $servicedata->minimum_booking ?? null)->attributes(['step' => 'any'])->placeholder(__('minimum booking'))->class('form-control')->id('minimum_booking') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <!-- Discount -->
                            <div class="form-group col-md-4" id="discount_div">
                                {{ html()->label(__('messages.discount') . ' %', 'discount')->class('form-control-label') }}
                                {{ html()->number('discount', $servicedata->discount ?? null)->attributes(['min' => 0, 'max' => 99, 'step' => 'any'])->placeholder(__('messages.discount'))->class('form-control')->id('discount') }}
                                <span id="discount-error" class="text-danger"></span>
                            </div>

                            <!-- Duration -->
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.duration') . ' (hours) ', 'duration')->class('form-control-label') }}
                                {{ html()->text('duration', $servicedata->duration)->placeholder(__('messages.duration'))->class('form-control min-datetimepicker-time') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <!-- Status -->
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.status') . ' <span class="text-danger">*</span>', 'status')->class('form-control-label') }}
                                {{ html()->select('status', ['1' => __('messages.active'), '0' => __('messages.inactive')], $servicedata->status)->class('form-control select2js')->required() }}
                            </div>

                            <!-- Visit Type -->
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.visit_type') . ' ', 'visit_type')->class('form-control-label') }}
                                {{ html()->select('visit_type', $visittype, $servicedata->visit_type)->id('visit_type')->class('form-control select2js')->required() }}
                            </div>

                            <!-- Image Upload -->
                            <div class="form-group col-md-4">
                                <label class="form-control-label" for="service_attachment">{{ __('messages.image') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="custom-file">
                                    <input type="file" name="service_attachment[]" id="service_attachment"
                                        class="custom-file-input" multiple accept="image/*"
                                        data-file-error="{{ __('messages.files_not_allowed') }}"
                                        {{ !getMediaFileExit($servicedata, 'service_attachment') ? 'required' : '' }}>
                                    <label class="custom-file-label" for="service_attachment">
                                        {{ __('messages.choose_file', ['file' => __('messages.attachments')]) }}
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    {{ __('messages.max_file_size', ['size' => '5MB']) }}
                                </small>
                            </div>
                        </div>

                        <!-- Image Preview Section -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="image-preview-container" id="image-preview-container">
                                    @if(getMediaFileExit($servicedata, 'service_attachment'))
                                        @foreach($servicedata->getMedia('service_attachment') as $attachment)
                                            <div class="image-preview-item" data-id="{{ $attachment->id }}">
                                                <img src="{{ $attachment->getFullUrl() }}" class="img-thumbnail" width="150">
                                                <div class="image-actions">
                                                    <a href="{{ $attachment->getFullUrl() }}" target="_blank" class="btn btn-sm btn-info">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('remove.file', ['id' => $attachment->id, 'type' => 'service_attachment']) }}" 
                                                       class="btn btn-sm btn-danger remove-image" data-ajax="true">
                                                        <i class="ri-close-circle-line"></i>
                                                    </a>
                                                </div>
                                                <input type="hidden" name="existing_attachments[]" value="{{ $attachment->id }}">
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Description and Other Fields -->
                        <div class="row">
                            <div class="form-group col-md-12">
                                {{ html()->label(__('messages.description'), 'description')->class('form-control-label') }}
                                {{ html()->textarea('description', $servicedata->description)->class('form-control textarea')->rows(3)->placeholder(__('messages.description')) }}
                            </div>
                            
                            <div class="form-group col-md-12">
                                {{ html()->label(__('Cancellation Policy & Fees'), 'cancellation_policy')->class('form-control-label') }}
                                {{ html()->textarea('cancellation_policy', $servicedata->cancellation_policy)->class('form-control textarea')->rows(3)->placeholder(__('cancellation_policy')) }}
                            </div>

                            @if (!empty($slotservice) && $slotservice == 1)
                                <div class="form-group col-md-3">
                                    <div class="custom-control custom-switch">
                                        {{ html()->checkbox('is_slot', $servicedata->is_slot)->class('custom-control-input')->id('is_slot') }}
                                        <label class="custom-control-label" for="is_slot">{{ __('messages.slot') }}</label>
                                    </div>
                                </div>
                            @endif

                            @if (auth()->check() && auth()->user()->user_type === 'provider')
                                <div class="form-group col-md-3">
                                    <div class="custom-control custom-switch">
                                        {{ html()->checkbox('is_featured', $servicedata->is_featured)->class('custom-control-input')->id('is_featured') }}
                                        <label class="custom-control-label" for="is_featured">{{ __('messages.set_as_featured') }}</label>
                                    </div>
                                </div>
                            @endif

                            <div class="form-group col-md-3">
                                <div class="custom-control custom-switch">
                                    {{ html()->checkbox('is_enable_advance_payment', $servicedata->is_enable_advance_payment)->class('custom-control-input')->id('is_enable_advance_payment') }}
                                    <label class="custom-control-label" for="is_enable_advance_payment">
                                        {{ __('messages.enable_advanced_payment') }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4" id="amount">
                                {{ html()->label(__('messages.advance_payment_amount') . ' <span class="text-danger"></span> (%)', 'advance_payment_amount')->class('form-control-label') }}
                                {{ html()->number('advance_payment_amount', $servicedata->advance_payment_amount)->placeholder(__('messages.amount'))->class('form-control')->id('advance_payment_amount')->attributes(['min' => 1, 'max' => 99]) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                        </div>

                        {{ html()->submit(__('messages.save'))->class('btn btn-md btn-primary float-end') }}
                        {{ html()->form()->close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('bottom_script')
        <script src="https://cdn.tiny.cloud/1/m5d82gd2rwdlg96hsxpx0e5wwmfrl2zzkcw35ys8o3glilgq/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
        
        <script type="text/javascript">
            // Initialize TinyMCE editors
            tinymce.init({
                selector: '#description',
                plugins: 'lists link image preview',
                toolbar: 'undo redo | bold italic | bullist numlist | link image preview',
                menubar: false,
                height: 300
            });

            tinymce.init({
                selector: '#cancellation_policy',
                plugins: 'lists link image preview',
                toolbar: 'undo redo | bold italic | bullist numlist | link image preview',
                menubar: false,
                height: 300
            });

            // Image preview functionality
            document.getElementById('service_attachment').addEventListener('change', function(e) {
                const container = document.getElementById('image-preview-container');
                const files = e.target.files;
                
                // Clear any temporary previews (keep existing attachments)
                document.querySelectorAll('.temp-preview').forEach(el => el.remove());
                
                if (files && files.length > 0) {
                    Array.from(files).forEach(file => {
                        if (!file.type.match('image.*')) return;
                        
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const previewItem = document.createElement('div');
                            previewItem.className = 'image-preview-item temp-preview';
                            previewItem.innerHTML = `
                                <img src="${e.target.result}" class="img-thumbnail" width="150">
                                <div class="image-actions">
                                    <button type="button" class="btn btn-sm btn-danger remove-preview">
                                        <i class="ri-close-circle-line"></i>
                                    </button>
                                </div>
                            `;
                            container.appendChild(previewItem);
                            
                            // Handle preview removal
                            previewItem.querySelector('.remove-preview').addEventListener('click', function() {
                                previewItem.remove();
                                // You may need to remove the file from the input here
                            });
                        };
                        reader.readAsDataURL(file);
                    });
                }
            });

            // Handle removal of existing images
            $(document).on('click', '.remove-image', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const item = $(this).closest('.image-preview-item');
                
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {_method: 'DELETE', _token: '{{ csrf_token() }}'},
                    success: function() {
                        item.remove();
                        // Make file input required if no images left
                        if ($('.image-preview-item').length === 0) {
                            $('#service_attachment').attr('required', 'required');
                        }
                    },
                    error: function(xhr) {
                        alert('Error deleting image: ' + xhr.responseText);
                    }
                });
            });

            // Provider selection handler
            function selectprovider(selectElement) {
                const providerId = selectElement.value;
                const addProviderAddressLink = document.getElementById('add_provider_address_link');
                
                if (providerId) {
                    addProviderAddressLink.href = addProviderAddressLink.href.split('?')[0] + '?provider_id=' + providerId;
                    addProviderAddressLink.classList.remove('d-none');
                } else {
                    addProviderAddressLink.classList.add('d-none');
                }
            }

            // Discount validation
            const discountInput = document.getElementById('discount');
            const discountError = document.getElementById('discount-error');
            
            discountInput.addEventListener('input', function() {
                const discountValue = parseFloat(discountInput.value);
                if (isNaN(discountValue) {
                    discountError.textContent = "{{ __('Please enter a valid number') }}";
                } else if (discountValue < 0 || discountValue > 99) {
                    discountError.textContent = "{{ __('Discount must be between 0 and 99') }}";
                } else {
                    discountError.textContent = "";
                }
            });

            // Price type and advance payment handling
            const isEnableAdvancePayment = $("input[name='is_enable_advance_payment']").prop('checked');
            let priceType = $("#price_type").val();

            function enableAdvancePayment(type) {
                const allowedTypes = ['fixed', 'hourly', 'daily'];
                $("#is_enable_advance_payment").closest('.form-group').toggleClass('d-none', !allowedTypes.includes(type.toLowerCase()));
            }

            function checkEnablePayment(value) {
                $("#amount").toggleClass('d-none', !value);
                $('#advance_payment_amount').prop('required', value);
            }

            $("#is_enable_advance_payment").change(function() {
                checkEnablePayment($(this).prop('checked'));
            });

            $("#price_type").change(function() {
                priceType = $(this).val();
                enableAdvancePayment(priceType);
                
                // Handle duration field based on price type
                const $duration = $('#duration');
                if (priceType === 'hourly') {
                    $duration.val(1).prop('readonly', true);
                } else if (priceType.toLowerCase() === 'daily') {
                    $duration.val(8).prop('readonly', true);
                } else {
                    $duration.prop('readonly', false);
                }
            });

            // Initialize on page load
            $(document).ready(function() {
                // Set initial states
                enableAdvancePayment(priceType);
                checkEnablePayment(isEnableAdvancePayment);
                
                // Handle duration field
                if (priceType === 'hourly') {
                    $('#duration').val(1).prop('readonly', true);
                } else if (priceType.toLowerCase() === 'daily') {
                    $('#duration').val(8).prop('readonly', true);
                }

                // Initialize select2 for all select elements
                $('.select2js').select2({
                    width: '100%',
                    placeholder: function() {
                        return $(this).data('placeholder') || 'Select an option';
                    }
                });

                // Load provider addresses if provider is selected
                const providerId = "{{ $servicedata->provider_id ?? '' }}";
                const providerAddressIds = "{{ isset($data) ? $data : '' }}";
                
                if (providerId) {
                    loadProviderAddresses(providerId, providerAddressIds);
                }

                // Load subcategories if category is selected
                const categoryId = "{{ $servicedata->category_id ?? '' }}";
                const subcategoryId = "{{ $servicedata->subcategory_id ?? '' }}";
                
                if (categoryId) {
                    loadSubCategories(categoryId, subcategoryId);
                }

                // Load states and cities if country is selected
                const countryId = "{{ $servicedata->country_id ?? '' }}";
                const stateId = "{{ $servicedata->state_id ?? '' }}";
                const cityId = "{{ $servicedata->city_id ?? '' }}";
                
                if (countryId) {
                    loadStates(countryId, stateId);
                    if (stateId) {
                        loadCities(stateId, cityId);
                    }
                }
            });

            // AJAX functions for dynamic dropdowns
            function loadProviderAddresses(providerId, selectedIds = '') {
                const url = "{{ route('ajax-list', ['type' => 'provider_address', 'provider_id' => '']) }}" + providerId;
                
                $.ajax({
                    url: url,
                    success: function(result) {
                        $('#provider_address_id').empty().select2({
                            data: result.results,
                            width: '100%',
                            placeholder: "{{ __('messages.select_name', ['select' => __('messages.provider_address')]) }}"
                        });
                        
                        if (selectedIds) {
                            $('#provider_address_id').val(selectedIds.split(',')).trigger('change');
                        }
                    }
                });
            }

            function loadSubCategories(categoryId, selectedId = '') {
                const url = "{{ route('ajax-list', ['type' => 'subcategory_list', 'category_id' => '']) }}" + categoryId;
                
                $.ajax({
                    url: url,
                    success: function(result) {
                        $('#subcategory_id').empty().select2({
                            data: result.results,
                            width: '100%',
                            placeholder: "{{ __('messages.select_name', ['select' => __('messages.subcategory')]) }}"
                        });
                        
                        if (selectedId) {
                            $('#subcategory_id').val(selectedId).trigger('change');
                        }
                    }
                });
            }

            function loadStates(countryId, selectedId = '') {
                const url = "{{ route('ajax-list', ['type' => 'state', 'country_id' => '']) }}" + countryId;
                
                $.ajax({
                    url: url,
                    success: function(result) {
                        $('#state_id').empty().select2({
                            data: result.results,
                            width: '100%',
                            placeholder: "{{ __('messages.select_name', ['select' => __('messages.state')]) }}"
                        });
                        
                        if (selectedId) {
                            $('#state_id').val(selectedId).trigger('change');
                        }
                    }
                });
            }

            function loadCities(stateId, selectedId = '') {
                const url = "{{ route('ajax-list', ['type' => 'city', 'state_id' => '']) }}" + stateId;
                
                $.ajax({
                    url: url,
                    success: function(result) {
                        $('#city_id').empty().select2({
                            data: result.results,
                            width: '100%',
                            placeholder: "{{ __('messages.select_name', ['select' => __('messages.city')]) }}"
                        });
                        
                        if (selectedId) {
                            $('#city_id').val(selectedId).trigger('change');
                        }
                    }
                });
            }

            // Event handlers for dynamic dropdowns
            $(document).on('change', '#provider_id', function() {
                loadProviderAddresses($(this).val());
            });

            $(document).on('change', '#category_id', function() {
                loadSubCategories($(this).val());
            });

            $(document).on('change', '#country_id', function() {
                loadStates($(this).val());
                $('#city_id').empty();
            });

            $(document).on('change', '#state_id', function() {
                loadCities($(this).val());
            });

            // Handle tax country sync with main country
            $(document).on('change', '#country_id', function() {
                const countryId = $(this).val();
                const countryName = $(this).find('option:selected').text();
                
                // Update tax country display
                $('#tax_country_id_display').empty().append(
                    new Option(countryName, countryId, true, true)
                ).trigger('change');
                
                // Update hidden tax country field
                $('#tax_country_id').val(countryId);
            });
        </script>
        
        <style>
            .image-preview-container {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                margin-top: 15px;
            }
            
            .image-preview-item {
                position: relative;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 5px;
                background: #f9f9f9;
            }
            
            .image-preview-item img {
                max-width: 150px;
                max-height: 150px;
                object-fit: contain;
            }
            
            .image-actions {
                position: absolute;
                bottom: 5px;
                right: 5px;
                display: flex;
                gap: 5px;
            }
            
            .image-actions .btn {
                padding: 0.25rem 0.5rem;
                font-size: 12px;
            }
            
            .temp-preview {
                opacity: 0.8;
            }
            
            .custom-file-label::after {
                content: "{{ __('messages.browse') }}";
            }
        </style>
    @endsection
</x-master-layout>