<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                            <a href="{{ route('post-job-request.index') }}" class="float-right btn btn-sm btn-primary"><i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::model($postJob,['method' => 'POST','route'=>'postJobRequest.save', 'enctype'=>'multipart/form-data', 'data-toggle'=>"validator" ,'id'=>'postJob'] ) }}

                        {{ Form::hidden('id') }}
                        <div class="row">
                        </div>
                        <div class="row">
                            <div class="form-group col-md-2">
                                {{ Form::label('title', __('messages.title').' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                                {{ Form::text('title', old('title'), ['placeholder' => __('messages.title'), 'class' => 'form-control', 'title' => 'Please enter alphabetic characters and spaces only','required']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-2">
                                {{ Form::label('country', __('messages.country').' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                                {{ Form::select('country_id', [optional($postJob->country)->id => optional($postJob->country)->name], optional($postJob->category)->id, [
                                    'class' => 'select2js form-group category',
                                    'required',
                                    'id' => 'country_id',
                                    'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.country') ]),
                                    'data-ajax--url' => route('ajax-list', ['type' => 'country']),
                                ]) }}
                            </div>

                            <div class="form-group col-md-2">
                                {{ Form::label('city_id', __('messages.select_name',[ 'select' => __('messages.city') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                <br />
                                {{ Form::select('city_id', [], [
                                                'class' => 'select2js form-group category',
                                                'required',
                                                'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.city') ]),
                                ]) }}
                            </div>

                            <div class="form-group col-md-2">
                                {{ Form::label('category', __('messages.category').' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                                {{ Form::select('category_id', [optional($postJob->category_id)->id => optional($postJob->category)->name], optional($postJob->category)->id, [
                                    'class' => 'select2js form-group category',
                                    'required',
                                    'id' => 'category_id',
                                    'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.category') ]),
                                    'data-ajax--url' => route('ajax-list', ['type' => 'category']),
                                ]) }}
                            </div>

                            <div class="form-group col-md-2">
                                {{ Form::label('subcategory_id', __('messages.select_name',[ 'select' => __('messages.subcategory') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                            <br />
                            {{ Form::select('subcategory_id', [], [
                                        'class' => 'select2js form-group subcategory_id',
                                        'required',
                                        'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.subcategory') ]),
                                    ]) }}
                            </div>
                                    {{--
                                <div class="form-group col-md-4">
                                    {{ Form::label('name', __('messages.select_name',[ 'select' => __('messages.provider') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                    <br />
                                    {{ Form::select('provider_id', [optional($postJob->provider)->id => optional($postJob->provider)->name], optional($postJob->provider)->id, [
                                                'class' => 'select2js form-group provider',
                                                'required',
                                                'id' => 'provider_id',
                                                'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.provider') ]),
                                                'data-ajax--url' => route('ajax-list', ['type' => 'provider']),
                                            ]) }}

                                </div> --}}
                        </div>


                        <div class="row">
                            <div class="form-group col-md-2" id="price_div">
                                {{ Form::label('price',__('messages.price').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                {{ Form::number('price',null, [ 'min' => 1, 'step' => 'any' , 'placeholder' => __('messages.price'),'class' =>'form-control', 'required','id' => 'price' ]) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-2">
                                {{ Form::label('start_date', __('messages.start_date').' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                                {{ Form::date('start_date', isset($postJob->start_date) ? $postJob->start_date : null, ['class' => 'form-control', 'id' => 'start_date','required']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-2">
                                {{ Form::label('end_date', __('messages.end_date').' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                                {{ Form::date('end_date', isset($postJob->end_date) ? $postJob->end_date : null, ['class' => 'form-control','id' => 'end_date', 'required']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-2">
                                {{ Form::label('total_day_div', __('messages.total_days').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                {{ Form::number('total_day',null, [ 'min' => 1, 'step' => 'any' , 'placeholder' => __('messages.total_days'),'class' =>'form-control', 'id' => 'total_day_div', 'required','disabled' ]) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            {{ Form::hidden('total_days', null, ['id' => 'hidden_total_days']) }}
                            {{ Form::hidden('total_hours', null, ['id' => 'hidden_total_hours']) }}

                            <div class="form-group col-md-2">
                                {{ Form::label('total_hours_div', __('messages.total_hours').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                {{ Form::number('total_hours',null, [ 'min' => 1, 'step' => 'any' , 'placeholder' => __('messages.total_hours'),'class' =>'form-control', 'id' => 'total_hours_div', 'required' ]) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                {{ Form::label('description',__('messages.description'), ['class' => 'form-control-label'])}}
                                {{ Form::textarea('description', null, ['class'=>"form-control textarea" , 'rows'=> 3 , 'placeholder'=> __('messages.description') ]) }}
                            </div>
                            {{-- <div class="form-group custom-file col-md-6 mt-30">
                                {{ Form::file('image[]', ['class'=>"custom-file-input custom-file-input-sm detail", 'id'=>"image", 'lang'=>"en", 'accept'=>"image/*", 'multiple']) }}
                                <label class="custom-file-label upload-label" for="image">{{ __('messages.image') }}</label>
                            </div> --}}
                            <div class="form-group custom-file col-md-6 mt-30">
                                {{ Form::file('image[]', ['class'=>"custom-file-input custom-file-input-sm detail", 'id'=>"image", 'lang'=>"en", 'accept'=>"image/*", 'multiple']) }}
                                <label class="custom-file-label upload-label" for="image">{{ __('messages.image') }}</label>
                                <div id="imageContainer"></div>
                                <button id="showMoreButton" class="btn btn-primary mt-3" style="display: none;">Show More</button>

                            </div>
                        </div>
                {{--
                        @if(auth()->user()->hasAnyRole(['admin','demo_admin']))
                            <div class="form-group col-md-4">
                                {{ Form::label('name', __('messages.select_name',[ 'select' => __('messages.provider') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                <br />
                {{ Form::select('provider_id', [ optional($servicedata->providers)->id => optional($servicedata->providers)->display_name ], optional($servicedata->providers)->id, [
                                            'class' => 'select2js form-group',
                                            'id' => 'provider_id',
                                            'required',
                                            'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.provider') ]),
                                            'data-ajax--url' => route('ajax-list', ['type' => 'provider']),
                                        ]) }}
            </div>
            @endif --}}


            {{-- <div class="form-group col-md-4" id="job_price_div">
                            {{ Form::label('job_price',__('messages.job_price').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
            {{ Form::number('job_price',null, [ 'min' => 1, 'step' => 'any' , 'placeholder' => __('messages.job_price'),'class' =>'form-control', 'required','id' => 'job_price' ]) }}
            <small class="help-block with-errors text-danger"></small>
        </div> --}}

    {{-- </div> --}}

    {{ Form::submit(__('messages.save'), ['class'=>'btn btn-md btn-primary float-right']) }}
    {{ Form::close() }}
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

    </div>
    </div>
    </div>
    </div>
    </div>


    @section('bottom_script')
    <style>
        #imageContainer img {
            width: 100%;
            height: auto;
            max-height: 150px;
            margin-bottom: 10px;
            margin-right: 10px;
        }
        #imageModal .modal-body img {
            width: 27%;
            height: 150px; /* Set your desired fixed height for images in the popup */
            margin-right: 10px; /* Add margin between images */
            margin-bottom: 10px;
        }
    </style>
    <script type="text/javascript">
        (function($) {
            "use strict";

            $(document).ready(function() {
                var provider_id = "{{ isset($postJob->provider_id) ? $postJob->provider_id : '' }}";
                var category_id = "{{ isset($postJob->category_id) ? $postJob->category_id : '' }}";
                var subcategory_id = "{{ isset($postJob->subcategory_id) ? $postJob->subcategory_id : '' }}";
                var country_id = "{{ isset($postJob->country_id) ? $postJob->country_id : '' }}";
                var state_id = "{{ isset($postJob->state_id) ? $postJob->state_id : '' }}";
                var city_id = "{{ isset($postJob->city_id) ? $postJob->city_id : '' }}";
                getSubCategory(category_id, subcategory_id)
                getCity(country_id, city_id);

                $(document).on('change', '#category_id', function() {
                        var category_id = $(this).val();
                        $('#subcategory_id').empty();
                        // console.log(category_id + ' : ' + subcategory_id );
                        getSubCategory(category_id, subcategory_id);
                    })

                $(document).on('change', '#country_id', function() {
                    // console.log("country_id");
                    var country_id = $(this).val();
                    // $('#city_id').empty();
                    getCity(country_id, city_id);
                });

                  // Function to set minimum selectable dates
            function setMinDates() {
                var today = new Date().toISOString().split('T')[0];
                $('#start_date').attr('min', today);
                $('#end_date').attr('min', today);
            }

            // Function to calculate days between dates
            function calculateDays() {
                var startDate = $('#start_date').val();
                var endDate = $('#end_date').val();

                if (startDate && endDate) {
                    if (startDate > endDate) {
                        $('#start_date_error').css('display', 'block');
                    } else {
                        $('#start_date_error').css('display', 'none');

                        var start = new Date(startDate);
                        var end = new Date(endDate);
                        var diffTime = end - start;
                        var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                        console.log(diffDays * 24);
                        if (diffDays > 0) {
                            $('#total_day_div').val(diffDays);
                            $('#hidden_total_days').val(diffDays);
                            $('#total_hours_div').val(diffDays * 24).attr('max', diffDays * 24);
                            $('#hidden_total_hours').val(diffDays * 24).attr('max', diffDays * 24);
                        } else {
                            $('#total_day_div').val(0);
                            $('#hidden_total_days').val(0);
                            $('#total_hours_div').val(0 * 24).attr('max', 0 * 24);
                            $('#hidden_total_hours').val(0 * 24).attr('max', 0 * 24);
                        }
                    }
                } else {
                    $('#hidden_total_days').val(0);
                    $('#total_day_div').val(0).attr('max', 0); // Added missing period here
                }
            }

            // Set initial min dates
            setMinDates();

            // Attach event listeners
            $('#start_date, #end_date').on('change', function() {
                calculateDays();
                var startDate = $('#start_date').val();
                if (startDate) {
                    $('#end_date').attr('min', startDate);
                } else {
                    setMinDates();
                }
            });


        });



            function getSubCategory(category_id, subcategory_id = "") {
                // console.log('s');
                var get_subcategory_list =
                    "{{ route('ajax-list', [ 'type' => 'subcategory_list','category_id' =>'']) }}" + category_id;
                get_subcategory_list = get_subcategory_list.replace('amp;', '');

                $.ajax({
                    url: get_subcategory_list,
                    success: function(result) {
                        // console.log(result);
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



            function getCity(country, city = "") {
                if (country_id != '') {
                    var get_city_list = "{{ route('ajax-list', [ 'type' => 'cityFromCountry','country_id' =>'']) }}" + country;
                    get_city_list = get_city_list.replace('amp;', '');

                    $('#city_id').select2({
                        width: '100%',
                        placeholder: "{{ __('messages.select_name',['select' => __('messages.city')]) }}",
                    });


                    $.ajax({
                        url: get_city_list,
                        success: function(result) {

                            $('#city_id').empty();

                            result.forEach(function(city) {
                                var option = new Option(city.name, city.id, false, false);
                                $('#city_id').append(option);
                            });

                            if (city !== null && city !== 0) {
                                $("#city_id").val(city).trigger('change');
                            }

                            // console.log('result',result)
                            // $('#city_id').select2({
                            //     width: '100%',
                            //     placeholder: "{{ trans('messages.select_name',['select' => trans('messages.city')]) }}",
                            //     data: result
                            // });
                            // console.log(city);
                            // if (city != "") {
                            //     $('#city_id').val(city).trigger('change');
                            // }
                        }
                    });
                }




                    // console.log('country',country);
                    // var city_route = "{{ route('ajax-list', [ 'type' => 'cityFromCountry' ,'country_id' =>'']) }}" + country;
                    // city_route = city_route.replace('amp;', '');
                    // $('#city_id').select2({
                    //     width: '100%',
                    //     placeholder: "{{ __('messages.select_name',['select' => __('messages.city')]) }}",
                    // });

                    // $.ajax({
                    //     url: city_route,
                    //     success: function(result) {
                    //         // Clear existing options
                    //         $('#city_id').empty();

                    //         // Append new options based on the result
                    //         result.forEach(function(city) {
                    //             var option = new Option(city.name, city.id, false, false);
                    //             $('#city_id').append(option);
                    //         });

                    //         // If a specific city is selected, set it as the default value
                    //         // console.log('getCity',city);
                    //         if (city !== null && city !== 0) {
                    //             $("#city_id").val(city).trigger('change');
                    //         }
                    //     }
                    // });
                }

                function displaySelectedImage(input) {
                    // console.log('input', input);
                    var files = input.files;

                    if (files && files[0]) {
                        var reader = new FileReader();

                        reader.onload = function (e) {
                            $('#selectedImage').attr('src', e.target.result).show();
                        };

                        reader.readAsDataURL(files[0]);
                    }
                }

                var selectedImages = [];


                $("#image").change(function(event) {
                var files = event.target.files;

                if (files && files.length > 0) {
                    $('#imageContainer').empty();
                    selectedImages = []; // Clear the array

                    var maxImages = Math.min(files.length, 3);

                    for (var i = 0; i < files.length; i++) {
                        var imageUrl = URL.createObjectURL(files[i]);

                        selectedImages.push(imageUrl);

                        if (i < maxImages) {
                            // Create an image element
                            var img = $('<img>').attr({
                                'src': imageUrl,
                                'alt': 'Selected Image',
                                'style': 'width: 27%; height: auto; max-height: 90px;',
                                'class': 'img-fluid mt-2',
                            });

                            $('#imageContainer').append(img);
                        }
                    }

                    if (files.length > 3) {
                        $('#showMoreButton').show();
                    } else {
                        $('#showMoreButton').hide();
                    }
                }
            });

            $('#showMoreButton').click(function() {
                openImagePopup(selectedImages);
            });

            function openImagePopup(images) {
                // console.log('images', images);
                var modal = $('#imageModal');
                modal.modal('show');

                modal.find('.modal-body').empty();

                images.forEach(function(imageUrl) {
                    var img = $('<img>').attr({
                        'src': imageUrl,
                        'alt': 'Selected Image',
                        'style': 'width: 27%; height: 90px; margin-right: 10px;' // Add margin between images
                    });

                    modal.find('.modal-body').append(img);
                });
            }

            $(".btn-close").click(function(){
                $('#imageModal').modal('hide');
            })


        })(jQuery);
    </script>
    @endsection
</x-master-layout>
