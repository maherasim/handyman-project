<x-master-layout>
    <form method="POST" action="{{ route('postJobRequest.save') }}" enctype="multipart/form-data" data-toggle="validator" id="postJob">
        @csrf
        <input type="hidden" name="id">

       
            <div class="form-group col-md-2">
                <label for="title" class="form-control-label">{{ __('messages.title') }} <span class="text-danger">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" class="form-control" placeholder="{{ __('messages.title') }}" required>
            </div>

            <div class="form-group col-md-2">
                <label for="country_id" class="form-control-label">{{ __('messages.country') }} <span class="text-danger">*</span></label>
                <select name="country_id" id="country_id" class="select2js form-group category" required data-placeholder="{{ __('messages.select_name', ['select' => __('messages.country')]) }}" data-ajax--url="{{ route('ajax-list', ['type' => 'country']) }}">
                    <option value="{{ optional($postJob->country)->id }}">{{ optional($postJob->country)->name }}</option>
                </select>
            </div>

            <div class="form-group col-md-2">
                <label for="city_id" class="form-control-label">{{ __('messages.select_name', ['select' => __('messages.city')]) }} <span class="text-danger">*</span></label>
                <select name="city_id" class="select2js form-group category" required data-placeholder="{{ __('messages.select_name', ['select' => __('messages.city')]) }}">
                </select>
            </div>
        </div>

        <div class="row">

        <div class="form-group col-md-4">
            {{ html()->label(__('messages.select_name', ['select' => __('messages.country')]).' <span class="text-danger">*</span>', 'country_id')->class('form-control-label') }}
            <br />
            {{ html()->select('country_id', [optional($handymandata->country)->id => optional($handymandata->country)->name], optional($handymandata->country)->id)
                ->class('select2js form-group country')
                ->required()
                ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.country')]))
                ->attribute('data-ajax--url', route('ajax-list', ['type' => 'country'])) 
                }}
        </div>

        <div class="form-group col-md-4">
            {{ html()->label(__('messages.select_name', ['select' => __('messages.state')]).' <span class="text-danger">*</span>', 'state_id')->class('form-control-label') }}
            <br />
            {{ html()->select('state_id', [], [])
                ->class('select2js form-group state_id')
                ->required()
                ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.state')]))       
                }}
        </div>

        <div class="form-group col-md-4">
            {{ html()->label(__('messages.select_name', ['select' => __('messages.city')]).' <span class="text-danger">*</span>', 'city_id')->class('form-control-label') }}
            <br />
            {{ html()->select('city_id', [], old('city_id'))
                ->class('select2js form-group city_id')->required()->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.city')])) }}
        </div>
    </div>











        
        <div class="row">
            <div class="form-group col-md-2">
                <label for="price" class="form-control-label">{{ __('messages.price') }} <span class="text-danger">*</span></label>
                <input type="number" name="price" class="form-control" min="1" step="any" placeholder="{{ __('messages.price') }}" required>
            </div>

            <div class="form-group col-md-2">
                <label for="start_date" class="form-control-label">{{ __('messages.start_date') }} <span class="text-danger">*</span></label>
                <input type="date" name="start_date" class="form-control" required>
            </div>

            <div class="form-group col-md-2">
                <label for="end_date" class="form-control-label">{{ __('messages.end_date') }} <span class="text-danger">*</span></label>
                <input type="date" name="end_date" class="form-control" required>
            </div>

            <!-- Add Total Days Field -->
            <div class="form-group col-md-2">
                <label for="total_day_div" class="form-control-label">{{ __('messages.total_days') }}</label>
                <input type="text" id="total_day_div" class="form-control" readonly>
                <input type="hidden" id="hidden_total_days" name="total_days">
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-6">
                <label for="description" class="form-control-label">{{ __('messages.description') }}</label>
                <textarea name="description" class="form-control textarea" rows="3" placeholder="{{ __('messages.description') }}"></textarea>
            </div>
        </div>

        <div class="form-group custom-file col-md-6 mt-30">
            <input type="file" name="post_Job_image[]" class="custom-file-input custom-file-input-sm detail" id="image" lang="en" accept="image/*" multiple>
            <label class="custom-file-label upload-label" for="image">{{ __('messages.image') }}</label>
            <div id="imageContainer"></div>
            <button id="showMoreButton" class="btn btn-primary mt-3" style="display: none;">Show More</button>
        </div>

        <button type="submit" class="btn btn-primary float-right">{{ __('messages.save') }}</button>
    </form>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">{{ __('messages.image') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}">X</button>
                </div>
                <div class="modal-body">
                    <!-- Images will be displayed here -->
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
    @endsection
@endsection
</x-master-layout>
