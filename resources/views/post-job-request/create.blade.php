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
                    <form method="POST" action="{{ route('postJobRequest.save') }}" enctype="multipart/form-data" id="postJob" data-toggle="validator">
                        @csrf
                        <input type="hidden" name="id" value="{{ old('id', $postJob->id) }}">

                        <div class="row">
                            <div class="form-group col-md-2">
                                <label for="title" class="form-control-label">{{ __('messages.title').' <span class="text-danger">*</span>' }}</label>
                                <input type="text" name="title" id="title" value="{{ old('title', $postJob->title) }}" placeholder="{{ __('messages.title') }}" class="form-control" title="Please enter alphabetic characters and spaces only" required>
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-2">
                                <label for="country_id" class="form-control-label">{{ __('messages.country').' <span class="text-danger">*</span>' }}</label>
                                <select name="country_id" id="country_id" class="select2js form-group category" required data-placeholder="{{ __('messages.select_name', ['select' => __('messages.country')]) }}" data-ajax--url="{{ route('ajax-list', ['type' => 'country']) }}">
                                    <option value="{{ optional($postJob->country)->id }}" selected>{{ optional($postJob->country)->name }}</option>
                                </select>
                            </div>

                            <div class="form-group col-md-2">
                                <label for="city_id" class="form-control-label">{{ __('messages.select_name', ['select' => __('messages.city')]) .' <span class="text-danger">*</span>' }}</label>
                                <select name="city_id" id="city_id" class="select2js form-group category" required data-placeholder="{{ __('messages.select_name', ['select' => __('messages.city')]) }}">
                                </select>
                            </div>

                            <div class="form-group col-md-2">
                                <label for="category_id" class="form-control-label">{{ __('messages.category').' <span class="text-danger">*</span>' }}</label>
                                <select name="category_id" id="category_id" class="select2js form-group category" required data-placeholder="{{ __('messages.select_name', ['select' => __('messages.category')]) }}" data-ajax--url="{{ route('ajax-list', ['type' => 'category']) }}">
                                    <option value="{{ optional($postJob->category)->id }}" selected>{{ optional($postJob->category)->name }}</option>
                                </select>
                            </div>

                            <div class="form-group col-md-2">
                                <label for="subcategory_id" class="form-control-label">{{ __('messages.select_name', ['select' => __('messages.subcategory')]) .' <span class="text-danger">*</span>' }}</label>
                                <select name="subcategory_id" id="subcategory_id" class="select2js form-group subcategory_id" required data-placeholder="{{ __('messages.select_name', ['select' => __('messages.subcategory')]) }}">
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-2" id="price_div">
                                <label for="price" class="form-control-label">{{ __('messages.price').' <span class="text-danger">*</span>' }}</label>
                                <input type="number" name="price" id="price" value="{{ old('price', $postJob->price) }}" min="1" step="any" placeholder="{{ __('messages.price') }}" class="form-control" required>
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-2">
                                <label for="start_date" class="form-control-label">{{ __('messages.start_date').' <span class="text-danger">*</span>' }}</label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $postJob->start_date) }}" class="form-control" required>
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-2">
                                <label for="end_date" class="form-control-label">{{ __('messages.end_date').' <span class="text-danger">*</span>' }}</label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $postJob->end_date) }}" class="form-control" required>
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-2">
                                <label for="total_day_div" class="form-control-label">{{ __('messages.total_days').' <span class="text-danger">*</span>' }}</label>
                                <input type="number" name="total_day" id="total_day_div" value="{{ old('total_day', $postJob->total_day) }}" min="1" step="any" placeholder="{{ __('messages.total_days') }}" class="form-control" required disabled>
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <input type="hidden" name="total_days" id="hidden_total_days" value="{{ old('total_days', $postJob->total_days) }}">
                            <input type="hidden" name="total_hours" id="hidden_total_hours" value="{{ old('total_hours', $postJob->total_hours) }}">

                            <div class="form-group col-md-2">
                                <label for="total_hours_div" class="form-control-label">{{ __('messages.total_hours').' <span class="text-danger">*</span>' }}</label>
                                <input type="number" name="total_hours" id="total_hours_div" value="{{ old('total_hours', $postJob->total_hours) }}" min="1" step="any" placeholder="{{ __('messages.total_hours') }}" class="form-control" required>
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="description" class="form-control-label">{{ __('messages.description') }}</label>
                                <textarea name="description" id="description" class="form-control textarea" rows="3" placeholder="{{ __('messages.description') }}">{{ old('description', $postJob->description) }}</textarea>
                            </div>

                            <div class="form-group custom-file col-md-6 mt-30">
                                <input type="file" name="post_Job_image[]" id="image" class="custom-file-input custom-file-input-sm detail" lang="en" accept="image/*" multiple>
                                <label for="image" class="custom-file-label upload-label">{{ __('messages.image') }}</label>
                                <div id="imageContainer"></div>
                                <button type="button" id="showMoreButton" class="btn btn-primary mt-3" style="display: none;">Show More</button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-md btn-primary float-right">{{ __('messages.save') }}</button>
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
                </div>
            </div>
        </div>
    </div>
</div>
