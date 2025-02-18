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

                            <!-- First row with 4 fields -->
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

                                <div class="form-group col-md-3">
                                    {{ html()->label(__('messages.select_name', ['select' => __('messages.category')]) . ' <span class="text-danger">*</span>', 'name')->class('form-control-label') }}
                                    <br />
                                    {{ html()->select(
                                            'category_id',
                                            [optional($servicedata->category)->id => optional($servicedata->category)->name],
                                            optional($servicedata->category)->id,
                                        )->class('select2js form-group category')->required()->id('category_id')->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.category')]))->attribute('data-ajax--url', route('ajax-list', ['type' => 'category'])) }}
                                </div>
                                <div class="form-group col-md-3">
                                    {{ html()->label(__('messages.select_name', ['select' => __('messages.subcategory')]), 'subcategory_id')->class('form-control-label') }}
                                    <br />
                                    {{ html()->select('subcategory_id', $subcategories->pluck('name', 'id'), null)->class('select2js form-group subcategory_id')->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.subcategory')])) }}
                                </div>

                            </div>

                            <!-- Second row with 4 fields -->
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="price">{{ __('messages.price') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="price" id="price" class="form-control"
                                        min="1" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="price_type">{{ __('messages.price_type') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="price_type" id="price_type" class="form-control" required>
                                        <option value="fixed">{{ __('Fixed') }}</option>
                                        <option value="hourly">{{ __('Hourly') }}</option>
                                        <option value="daily">{{ __('Daily') }}</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="job_type">{{ __('messages.job_type') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="job_type" id="job_type" class="form-control" required>
                                        <option value="onsite">{{ __('Onsite') }}</option>
                                        <option value="remote">{{ __('Remote/Homeoffice') }}</option>
                                        <option value="hybrid">{{ __('Hybrid') }}</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="start_date">{{ __('Start Date') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" class="form-control"
                                        required>
                                </div>
                            </div>

                            <!-- Third row with 4 fields -->
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="end_date">{{ __('End Date') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="total_days">{{ __('Total days') }}</label>
                                    <input type="number" name="total_days" id="total_days" class="form-control"
                                        readonly>
                                </div>


                                <div class="form-group col-md-3">
                                    <label for="total_hours">{{ __('Total Hours') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="total_hours" id="total_hours" class="form-control"
                                        readonly>
                                </div>

                                <!-- Multi-select Requirements Field -->
                                <div class="form-group col-md-3">
                                    <label for="requirements">{{ __('messages.requirements') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="requirements[]" id="requirements" class="form-control select2"
                                        multiple="multiple" required>
                                        <option value="requirement_1">{{ __('Requirement 1') }}</option>
                                        <option value="requirement_2">{{ __('Requirement 2') }}</option>
                                        <option value="requirement_3">{{ __('Requirement 3') }}</option>
                                        <option value="requirement_4">{{ __('Requirement 4') }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Fourth row with 2 fields -->
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="description">{{ __('messages.description') }}</label>
                                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                                </div>

                                <div class="form-group custom-file col-md-6 mt-30">
                                    <input type="file" name="image[]" id="image" class="custom-file-input"
                                        accept="image/*" multiple>
                                    <label class="custom-file-label"
                                        for="image">{{ __('messages.image') }}</label>
                                    <div id="imageContainer"></div>
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
  <script>$(document).ready(function() {
    // First, set min dates
    function setMinDates() {
        var today = new Date().toISOString().split('T')[0];
        $('#start_date, #end_date').attr('min', today); // Ensure dates are not in the past
    }

    // Calculate Total Days and Hours
    function calculateDays() {
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        // Proceed only if both dates are selected and startDate is less than or equal to endDate
        if (startDate && endDate && startDate <= endDate) {
            var start = new Date(startDate);
            var end = new Date(endDate);
            var diffTime = end - start; // Time difference in milliseconds
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // Convert to days and add 1 to include start date

            // Update Total Days and Total Hours
            $('#total_days').val(diffDays);
            $('#total_hours').val(diffDays * 24); // Assuming 24 hours in a day for full days
        } else {
            $('#total_days').val(''); // Clear total days if the dates are not valid
            $('#total_hours').val(''); // Clear total hours if the dates are not valid
        }
    }

    // Initialize Select2 for the requirements field
    $('#requirements').select2({
        placeholder: "{{ __('Select requirements') }}",
        allowClear: true // Allows the user to clear selections
    });

    // Initialize TinyMCE for the description field
    tinymce.init({
        selector: '#description', // Target the ID of your textarea
        plugins: 'lists link image preview', // Add any plugins you want to use
        toolbar: 'undo redo | bold italic | bullist numlist | link image preview',
        menubar: false
    });

    // Set minimum dates on page load
    setMinDates(); 

    // Trigger calculateDays when either start_date or end_date is changed
    $('#start_date, #end_date').on('change', function() {
        calculateDays();
        
        // Update end_date min attribute if start_date is set
        var startDate = $('#start_date').val();
        if (startDate) {
            $('#end_date').attr('min', startDate); // Ensure end date can't be before start date
        }
    });

    // For the image preview functionality
    $("#image").change(function(event) {
        var files = event.target.files;
        $('#imageContainer').empty(); // Clear previous images

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
</x-master-layout>
