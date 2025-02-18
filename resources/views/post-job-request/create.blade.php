<x-master-layout>
    <script src="https://cdn.tiny.cloud/1/m5d82gd2rwdlg96hsxpx0e5wwmfrl2zzkcw35ys8o3glilgq/tinymce/5/tinymce.min.js"
        referrerpolicy="origin"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">Create Job Request</h5>
                            <a href="{{ route('post-job-request.index') }}" class="float-right btn btn-sm btn-primary">
                                <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('postJobRequest.save') }}" enctype="multipart/form-data"
                            id="postJob">
                            @csrf
                            <input type="hidden" name="id" value="{{ old('id', $postJob->id ?? '') }}">

                            <div class="row">
                                <div class="form-group col-md-2">
                                    <label for="title">{{ __('messages.title') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="title" id="title" class="form-control"
                                        placeholder="{{ __('messages.title') }}" required>
                                </div>

                                <div class="form-group col-md-2">
                                    <label for="country_id">{{ __('messages.country') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="country_id" id="country_id" class="select2js form-group category"
                                        required></select>
                                </div>

                                <div class="form-group col-md-2">
                                    <label for="city_id">{{ __('messages.city') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="city_id" id="city_id" class="select2js form-group category"
                                        required></select>
                                </div>

                                <div class="form-group col-md-4">
                                    {{ html()->label(__('messages.select_name', ['select' => __('messages.category')]) . ' <span class="text-danger">*</span>', 'name')->class('form-control-label') }}
                                    <br />
                                    {{ html()->select(
                                            'category_id',
                                            [optional($servicedata->category)->id => optional($servicedata->category)->name],
                                            optional($servicedata->category)->id,
                                        )->class('select2js form-group category')->required()->id('category_id')->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.category')]))->attribute('data-ajax--url', route('ajax-list', ['type' => 'category'])) }}

                                </div>
                                <div class="form-group col-md-4">
                                    {{ html()->label(__('messages.select_name', ['select' => __('messages.subcategory')]), 'subcategory_id')->class('form-control-label') }}
                                    <br />
                                    {{ html()->select('subcategory_id', $subcategories->pluck('name', 'id'), null)
                                        ->class('select2js form-group subcategory_id')
                                        ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.subcategory')])) }}
                                </div>
                                

                            </div>

                            <div class="row">
                                <div class="form-group col-md-2">
                                    <label for="price">{{ __('messages.price') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="price" id="price" class="form-control"
                                        min="1" required>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="price_type">{{ __('messages.price_type') }} <span class="text-danger">*</span></label>
                                    <select name="price_type" id="price_type" class="form-control" required>
                                        <option value="fixed">{{ __('Fixed') }}</option>
                                        <option value="hourly">{{ __('Hourly') }}</option>
                                        <option value="daily">{{ __('Daily') }}</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-2">
                                    <label for="job_type">{{ __('messages.job_type') }} <span class="text-danger">*</span></label>
                                    <select name="job_type" id="job_type" class="form-control" required>
                                        <option value="onsite">{{ __('Onsite') }}</option>
                                        <option value="remote">{{ __('Remote/Homeoffice') }}</option>
                                        <option value="hybrid">{{ __('Hybrid') }}</option>
                                    </select>
                                </div>


                                <div class="form-group col-md-2">
                                    <label for="start_date">{{ __('Start Date') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" class="form-control"
                                        required>
                                </div>

                                <div class="form-group col-md-2">
                                    <label for="end_date">{{ __('End Date') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                                </div>

                                <!-- NEW TOTAL DAYS FIELD -->
                                <div class="form-group col-md-2">
                                    <label for="total_days">{{ __('Total days') }}</label>
                                    <input type="number" name="total_days" id="total_days" class="form-control"
                                        readonly>
                                </div>

                                <div class="form-group col-md-2">
                                    <label for="total_hours">{{ __('Total Hours') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="total_hours" id="total_hours" class="form-control"
                                        readonly>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="description">{{ __('messages.description') }}</label>
                                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                                </div>

                                <div class="form-group custom-file col-md-6 mt-30">
                                    <input type="file" name="image[]" id="image" class="custom-file-input"
                                        accept="image/*" multiple>
                                    <label class="custom-file-label" for="image">{{ __('messages.image') }}</label>
                                    <div id="imageContainer"></div>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="requirements">{{ __('messages.requirements') }} <span class="text-danger">*</span></label>
                                    <select name="requirements[]" id="requirements" class="form-control select2" multiple="multiple" required>
                                        <option value="requirement_1">{{ __('Requirement 1') }}</option>
                                        <option value="requirement_2">{{ __('Requirement 2') }}</option>
                                        <option value="requirement_3">{{ __('Requirement 3') }}</option>
                                        <option value="requirement_4">{{ __('Requirement 4') }}</option>
                                        <!-- Add more options as needed -->
                                    </select>
                                </div>
                            </div>

                            <button type="submit"
                                class="btn btn-md btn-primary float-right">{{ __('messages.save') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('bottom_script')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script>
            $(document).ready(function() {
                // Initialize select2 for subcategory dropdown
                $('#subcategory_id').select2();
            
                // Listen for category change event
                $('#category_id').on('change', function() {
                    var category_id = $(this).val();
                    if (category_id) {
                        $.ajax({
                            url: "{{ route('get-subcategories') }}", // Ensure this route exists
                            type: "GET",
                            data: { category_id: category_id },
                            dataType: "json",
                            success: function(data) {
                                console.log("Subcategories Loaded:", data); // Debugging log
                                
                                // Clear the subcategory dropdown before adding new options
                                $('#subcategory_id').empty().append('<option value="">' + 
                                    "{{ __('messages.select_name', ['select' => __('messages.subcategory')]) }}" + 
                                    '</option>');
            
                                // Check if the response data is correct
                                if (data && typeof data === 'object') {
                                    $.each(data, function(key, value) {
                                        // Append new subcategory options
                                        $('#subcategory_id').append('<option value="' + key + '">' + value + '</option>');
                                    });
                                } else {
                                    console.error("Invalid data format:", data); // Log an error if data is not an object
                                }
            
                                // Refresh select2 after updating options
                                $('#subcategory_id').select2(); // Re-initialize select2 for new options
                            },
                            error: function(xhr, status, error) {
                                console.error("Error fetching subcategories:", xhr.responseText);
                            }
                        });
                    } else {
                        // If no category is selected, clear the subcategory dropdown
                        $('#subcategory_id').empty().trigger('change');
                    }
                });
            });
            </script>
            

        <script>
            $(document).ready(function() {
                function setMinDates() {
                    var today = new Date().toISOString().split('T')[0];
                    $('#start_date, #end_date').attr('min', today);
                }

                function calculateDays() {
                    var startDate = $('#start_date').val();
                    var endDate = $('#end_date').val();

                    if (startDate && endDate && startDate <= endDate) {
                        var start = new Date(startDate);
                        var end = new Date(endDate);
                        var diffDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

                        $('#total_days').val(diffDays); // Update Total Days
                        $('#total_hours').val(diffDays * 24); // Update Total Hours
                    } else {
                        $('#total_days').val('');
                        $('#total_hours').val('');
                    }
                }

                setMinDates();

                $('#start_date, #end_date').on('change', function() {
                    calculateDays();

                    var startDate = $('#start_date').val();
                    if (startDate) {
                        $('#end_date').attr('min', startDate);
                    }
                });

                $("#image").change(function(event) {
                    var files = event.target.files;
                    $('#imageContainer').empty();

                    if (files.length > 0) {
                        for (var i = 0; i < Math.min(files.length, 3); i++) {
                            var imageUrl = URL.createObjectURL(files[i]);
                            var img = $('<img>').attr({
                                'src': imageUrl,
                                'class': 'img-fluid mt-2',
                                'style': 'width: 27%; height: 90px;'
                            });
                            $('#imageContainer').append(img);
                        }
                    }
                });
            });
        </script>
        <script>
            tinymce.init({
                selector: '#description', // Target the ID of your textarea
                plugins: 'lists link image preview', // Add any plugins you want to use
                toolbar: 'undo redo | bold italic | bullist numlist | link image preview',
                menubar: false
            });
        </script>
    @section('bottom_script')
        <script>
            tinymce.init({
                selector: '#description', // Target the ID of your textarea
                plugins: 'lists link image preview', // Add any plugins you want to use
                toolbar: 'undo redo | bold italic | bullist numlist | link image preview',
                menubar: false
            });
        </script>
    @endsection
@endsection
</x-master-layout>
