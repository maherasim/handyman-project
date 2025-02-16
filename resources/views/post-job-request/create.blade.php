<x-master-layout>
    <form method="POST" action="{{ route('postJobRequest.save') }}" enctype="multipart/form-data" data-toggle="validator" id="postJob">
        @csrf
        <input type="hidden" name="id">

        <div class="row">
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
            height: 150px;
            margin-right: 10px;
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
                getSubCategory(category_id, subcategory_id);
                getCity(country_id, city_id);

                $(document).on('change', '#category_id', function() {
                    var category_id = $(this).val();
                    $('#subcategory_id').empty();
                    getSubCategory(category_id, subcategory_id);
                });

                $(document).on('change', '#country_id', function() {
                    var country_id = $(this).val();
                    getCity(country_id, city_id);
                });

                // Set minimum selectable dates
                function setMinDates() {
                    var today = new Date().toISOString().split('T')[0];
                    $('#start_date').attr('min', today);
                    $('#end_date').attr('min', today);
                }

                // Calculate days between dates
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
                        $('#total_day_div').val(0).attr('max', 0);
                    }
                }

                setMinDates();
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
                var get_subcategory_list = "{{ route('ajax-list', [ 'type' => 'subcategory_list','category_id' =>'']) }}" + category_id;
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

            function getCity(country, city = "") {
                var city_route = "{{ route('ajax-list', [ 'type' => 'cityFromCountry' ,'country_id' =>'']) }}" + country;
                city_route = city_route.replace('amp;', '');

                $('#city_id').select2({
                    width: '100%',
                    placeholder: "{{ __('messages.select_name',['select' => __('messages.city')]) }}",
                });

                $.ajax({
                    url: city_route,
                    success: function(result) {
                        $('#city_id').empty();
                        result.forEach(function(city) {
                            var option = new Option(city.name, city.id, false, false);
                            $('#city_id').append(option);
                        });
                        if (city !== null && city !== 0) {
                            $("#city_id").val(city).trigger('change');
                        }
                    }
                });
            }

            function displaySelectedImage(input) {
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
                    selectedImages = [];
                    var maxImages = Math.min(files.length, 3);
                    for (var i = 0; i < files.length; i++) {
                        var imageUrl = URL.createObjectURL(files[i]);
                        selectedImages.push(imageUrl);
                        if (i < maxImages) {
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
                var modal = $('#imageModal');
                modal.modal('show');
                modal.find('.modal-body').empty();
                images.forEach(function(imageUrl) {
                    var img = $('<img>').attr({
                        'src': imageUrl,
                        'alt': 'Selected Image',
                        'style': 'width: 27%; height: 90px; margin-right: 10px;'
                    });
                    modal.find('.modal-body').append(img);
                });
            }

            $(".btn-close").click(function(){
                $('#imageModal').modal('hide');
            });

        })(jQuery);
    </script>
    @endsection
</x-master-layout>
