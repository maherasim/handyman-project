<x-master-layout>
    <script src="https://cdn.tiny.cloud/1/m5d82gd2rwdlg96hsxpx0e5wwmfrl2zzkcw35ys8o3glilgq/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
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
                        <form method="POST" action="{{ route('postJobRequest.save') }}" enctype="multipart/form-data" id="postJob">
                            @csrf
                            <input type="hidden" name="id" value="{{ old('id', $postJob->id ?? '') }}">

                            <!-- First row with 4 fields -->
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="title">{{ __('messages.title') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="title" class="form-control" placeholder="{{ __('messages.title') }}" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="country_id">{{ __('messages.country') }} <span class="text-danger">*</span></label>
                                    <select name="country_id" id="country_id" class="select2js form-group category" required></select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="city_id">{{ __('messages.city') }} <span class="text-danger">*</span></label>
                                    <select name="city_id" id="city_id" class="select2js form-group category" required></select>
                                </div>

                                <div class="form-group col-md-3">
                                    {{ html()->label(__('messages.select_name', ['select' => __('messages.category')]) . ' <span class="text-danger">*</span>', 'name')->class('form-control-label') }}
                                    <br />
                                    {{ html()->select('category_id', [optional($servicedata->category)->id => optional($servicedata->category)->name], optional($servicedata->category)->id)
                                        ->class('select2js form-group category')
                                        ->required()
                                        ->id('category_id')
                                        ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.category')]))
                                        ->attribute('data-ajax--url', route('ajax-list', ['type' => 'category'])) }}
                                </div>
                            </div>

                            <!-- Second row with 4 fields -->
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="price">{{ __('messages.price') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="price" id="price" class="form-control" min="1" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="price_type">{{ __('messages.price_type') }} <span class="text-danger">*</span></label>
                                    <select name="price_type" id="price_type" class="form-control" required>
                                        <option value="fixed">{{ __('Fixed') }}</option>
                                        <option value="hourly">{{ __('Hourly') }}</option>
                                        <option value="daily">{{ __('Daily') }}</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="job_type">{{ __('messages.job_type') }} <span class="text-danger">*</span></label>
                                    <select name="job_type" id="job_type" class="form-control" required>
                                        <option value="onsite">{{ __('Onsite') }}</option>
                                        <option value="remote">{{ __('Remote/Homeoffice') }}</option>
                                        <option value="hybrid">{{ __('Hybrid') }}</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="start_date">{{ __('Start Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                                </div>
                            </div>

                            <!-- Third row with 4 fields -->
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="end_date">{{ __('End Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="total_days">{{ __('Total days') }}</label>
                                    <input type="number" name="total_days" id="total_days" class="form-control" readonly>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="total_hours">{{ __('Total Hours') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="total_hours" id="total_hours" class="form-control" readonly>
                                </div>

                                <!-- Multi-select Requirements Field -->
                                <div class="form-group col-md-3">
                                    <label for="requirements">{{ __('messages.requirements') }} <span class="text-danger">*</span></label>
                                    <select name="requirements[]" id="requirements" class="form-control select2" multiple="multiple" required>
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
                                    <input type="file" name="image[]" id="image" class="custom-file-input" accept="image/*" multiple>
                                    <label class="custom-file-label" for="image">{{ __('messages.image') }}</label>
                                    <div id="imageContainer"></div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-md btn-primary float-right">{{ __('messages.save') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('bottom_script')
        <script>
            $(document).ready(function() {
                // Initialize select2 for subcategory dropdown and requirements dropdown
                $('#subcategory_id').select2();
                $('#requirements').select2();
            });
        </script>

        <script>
            tinymce.init({
                selector: '#description', // Target the ID of your textarea
                plugins: 'lists link image preview',
                toolbar: 'undo redo | bold italic | bullist numlist | link image preview',
                menubar: false
            });
        </script>
    @endsection
</x-master-layout>
