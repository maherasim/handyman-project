<x-master-layout>
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <style>
        .ql-editor { min-height: 220px; }
        /* Space below each Quill editor to avoid rows touching */
        .ql-container { margin-bottom: 1rem; }
        /* Ensure spacing even when wrapped by .form-group overrides */
        .quill-group { margin-bottom: 5.25rem; }
    </style>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ __('messages.pjr_create_job_request') }}</h5>
                            <a href="{{ route('post-job-request.index') }}" class="float-right btn btn-sm btn-primary"><i
                                    class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body pb-5">
                        @php
                            $__currency = Currency::getDefaultCurrency(true);
                            $__currencySymbol = data_get($__currency, 'defaultCurrency.symbol', Currency::defaultSymbol());
                            $__currencyCode = strtoupper((string) data_get($__currency, 'defaultCurrency.currency_code', ''));
                        @endphp
                        <form method="POST" action="{{ route('postJobRequest.save') }}" enctype="multipart/form-data"
                            id="postJob" data-toggle="validator">
                            @csrf
                            <input type="hidden" name="id" value="{{ old('id', $postJob->id) }}">

                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="title">{{ __('messages.title') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="title" class="form-control" placeholder="{{ __('messages.title') }}" title="Please enter alphabetic characters and spaces only" value="{{ old('title', $postJob->title) }}" required>
                                    <small class="help-block with-errors text-danger"></small>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="country_id">{{ __('messages.country') }} <span class="text-danger">*</span></label>
                                    <select name="country_id" id="country_id" class="select2js form-group category" required data-placeholder="{{ __('messages.select_name', ['select' => __('messages.country')]) }}" data-ajax--url="{{ route('ajax-list', ['type' => 'country']) }}">
                                        @php $oldCountryId = old('country_id', optional($postJob->country)->id); @endphp
                                        @if($oldCountryId)
                                        <option value="{{ $oldCountryId }}" selected>
                                            {{ optional($postJob->country)->id == $oldCountryId ? optional($postJob->country)->name : '' }}
                                        </option>
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="state_id">{{ __('messages.select_name', ['select' => __('messages.state')]) }} <span class="text-danger">*</span></label>
                                    <select name="state_id" id="state_id" class="select2js form-group category" required data-placeholder="{{ __('messages.select_name', ['select' => __('messages.state')]) }}">
                                        <!-- State options will be populated dynamically -->
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="city_id">{{ __('messages.select_name', ['select' => __('messages.city')]) }} <span class="text-danger">*</span></label>
                                    <select name="city_id" id="city_id" class="select2js form-group category" required data-placeholder="{{ __('messages.select_name', ['select' => __('messages.city')]) }}">
                                        <!-- City options will be populated dynamically -->
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="category_id">{{ __('messages.category') }} <span class="text-danger">*</span></label>
                                    <select name="category_id" id="category_id" class="select2js form-group category" required data-placeholder="{{ __('messages.select_name', ['select' => __('messages.category')]) }}" data-ajax--url="{{ route('ajax-list', ['type' => 'category']) }}">
                                        @php $oldCategoryId = old('category_id', optional($postJob->category)->id); @endphp
                                        @if($oldCategoryId)
                                        <option value="{{ $oldCategoryId }}" selected>
                                            {{ optional($postJob->category)->id == $oldCategoryId ? optional($postJob->category)->name : '' }}
                                        </option>
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="subcategory_id">{{ __('messages.select_name', ['select' => __('messages.subcategory')]) }} <span class="text-danger">*</span></label>
                                    <select name="subcategory_id" id="subcategory_id" class="select2js form-group subcategory_id" required data-placeholder="{{ __('messages.select_name', ['select' => __('messages.subcategory')]) }}"></select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="price_type">{{ __('messages.pjr_price_type') }} <span class="text-danger">*</span></label>
                                    <select name="price_type" id="price_type" class="form-control" required>
                                        @php $oldPriceType = old('price_type', $postJob->price_type ?? 'fixed'); @endphp
                                        <option value="fixed" {{ $oldPriceType == 'fixed' ? 'selected' : '' }}>{{ __('messages.pjr_fixed') }}</option>
                                        <option value="hourly" {{ $oldPriceType == 'hourly' ? 'selected' : '' }}>{{ __('messages.pjr_hourly') }}</option>
                                        <option value="daily" {{ $oldPriceType == 'daily' ? 'selected' : '' }}>{{ __('messages.pjr_daily') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="job_type">{{ __('messages.pjr_job_type') }} <span class="text-danger">*</span></label>
                                    <select name="type" id="type" class="form-control" required>
                                        <option value="onsite" {{ old('type', $postJob->type) == 'onsite' ? 'selected' : '' }}>{{ __('messages.pjr_onsite') }}</option>
                                        <option value="remote" {{ old('type', $postJob->type) == 'remote' ? 'selected' : '' }}>{{ __('messages.pjr_remote_homeoffice') }}</option>
                                        <option value="hybrid" {{ old('type', $postJob->type) == 'hybrid' ? 'selected' : '' }}>{{ __('messages.pjr_hybrid') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4" id="price_div">
                                    <label for="price">{{ __('messages.price') }} <span class="text-danger">*</span> @if($__currencyCode)<small class="text-muted">({{ $__currencyCode }})</small>@endif</label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ $__currencySymbol }}</span>
                                        <input type="number" name="price" id="price" class="form-control" min="1" step="any" placeholder="{{ __('messages.price') }}" required value="{{ old('price', $postJob->price) }}">
                                    </div>
                                    <small class="help-block with-errors text-danger"></small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="start_date">{{ __('messages.pjr_start_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" required value="{{ old('start_date', optional($postJob->start_date)->format('Y-m-d')) }}">
                                    <small class="help-block with-errors text-danger"></small>
                                    <small id="start_date_error" class="text-danger" style="display:none;">{{ __('messages.pjr_start_date_before_end') }}</small>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="end_date">{{ __('messages.pjr_end_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" required value="{{ old('end_date', optional($postJob->end_date)->format('Y-m-d')) }}">
                                    <small class="help-block with-errors text-danger"></small>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="total_day_div">{{ __('messages.pjr_total_days') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">📅</span>
                                        <input type="number" name="total_day" id="total_day_div" class="form-control" min="1" step="any" placeholder="{{ __('messages.pjr_total_days_placeholder') }}" required>
                                    </div>
                                </div>
                                <input type="hidden" name="total_days" id="hidden_total_days" value="{{ old('total_days', $postJob->total_days) }}">
                                <input type="hidden" name="total_hours" id="hidden_total_hours" value="{{ old('total_hours', $postJob->total_hours) }}">
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="total_hours_div">{{ __('messages.pjr_total_hours') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">⏱</span>
                                        <input type="number" name="total_hours" id="total_hours_div" class="form-control" min="1" step="any" placeholder="{{ __('messages.pjr_total_hours_placeholder') }}" required value="{{ old('total_hours', $postJob->total_hours) }}">
                                    </div>
                                    <small class="help-block with-errors text-danger"></small>
                                </div>
                                <div class="form-group col-md-4" id="total_budget_div">
                                    <label for="total_budget">{{ __('messages.pjr_total_budget') }} <span class="text-danger">*</span> @if($__currencyCode)<small class="text-muted">({{ $__currencyCode }})</small>@endif</label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ $__currencySymbol }}</span>
                                        <input type="number" name="total_budget" id="total_budget" class="form-control" min="0" step="any" placeholder="{{ __('messages.pjr_total_budget') }}" value="{{ old('total_budget', $postJob->total_budget) }}" readonly>
                                    </div>
                                    <small class="help-block with-errors text-danger"></small>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="job_schedule">{{ __('messages.pjr_job_schedule') }} <span class="text-danger">*</span></label>
                                    <select name="job_schedule" id="job_schedule" class="form-control" required>
                                        @php $oldSchedule = old('job_schedule', $postJob->job_schedule); @endphp
                                        <option value="full_time" {{ $oldSchedule == 'full_time' ? 'selected' : '' }}>{{ __('messages.pjr_full_time') }}</option>
                                        <option value="part_time" {{ $oldSchedule == 'part_time' ? 'selected' : '' }}>{{ __('messages.pjr_part_time') }}</option>
                                        <option value="contract" {{ $oldSchedule == 'contract' ? 'selected' : '' }}>{{ __('messages.pjr_contract') }}</option>
                                        <option value="temporary" {{ $oldSchedule == 'temporary' ? 'selected' : '' }}>{{ __('messages.pjr_temporary') }}</option>
                                        <option value="internship" {{ $oldSchedule == 'internship' ? 'selected' : '' }}>{{ __('messages.pjr_internship') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="remote_work_level">{{ __('messages.pjr_remote_work_level') }} <span class="text-danger">*</span></label>
                                    <select name="remote_work_level" id="remote_work_level" class="form-control" required>
                                        @php $oldRemote = old('remote_work_level', $postJob->remote_work_level); @endphp
                                        <option value="onsite" {{ $oldRemote == 'onsite' ? 'selected' : '' }}>{{ __('messages.pjr_onsite_100') }}</option>
                                        <option value="25_remote" {{ $oldRemote == '25_remote' ? 'selected' : '' }}>{{ __('messages.pjr_25_remote') }}</option>
                                        <option value="50_remote" {{ $oldRemote == '50_remote' ? 'selected' : '' }}>{{ __('messages.pjr_50_remote') }}</option>
                                        <option value="75_remote" {{ $oldRemote == '75_remote' ? 'selected' : '' }}>{{ __('messages.pjr_75_remote') }}</option>
                                        <option value="100_remote" {{ $oldRemote == '100_remote' ? 'selected' : '' }}>{{ __('messages.pjr_100_remote') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="career_level">{{ __('messages.career_level') }} <span class="text-danger">*</span></label>
                                    <select name="career_level" id="career_level" class="form-control" required>
                                        @php $oldCareer = old('career_level', $postJob->career_level); @endphp
                                        <option value="not_specified" {{ $oldCareer == 'not_specified' ? 'selected' : '' }}>{{ __('messages.career_level_not_specified') }}</option>
                                        <option value="entry_level" {{ $oldCareer == 'entry_level' ? 'selected' : '' }}>{{ __('messages.career_level_entry_level') }}</option>
                                        <option value="intermediate_level" {{ $oldCareer == 'intermediate_level' ? 'selected' : '' }}>{{ __('messages.career_level_intermediate_level') }}</option>
                                        <option value="experienced" {{ $oldCareer == 'experienced' ? 'selected' : '' }}>{{ __('messages.career_level_experienced') }}</option>
                                        <option value="professional" {{ $oldCareer == 'professional' ? 'selected' : '' }}>{{ __('messages.career_level_professional') }}</option>
                                        <option value="middle_management" {{ $oldCareer == 'middle_management' ? 'selected' : '' }}>{{ __('messages.career_level_middle_management') }}</option>
                                        <option value="executive_management" {{ $oldCareer == 'executive_management' ? 'selected' : '' }}>{{ __('messages.career_level_executive_management') }}</option>
                                        <option value="senior_management" {{ $oldCareer == 'senior_management' ? 'selected' : '' }}>{{ __('messages.career_level_senior_management') }}</option>
                                        <option value="director" {{ $oldCareer == 'director' ? 'selected' : '' }}>{{ __('messages.career_level_director') }}</option>
                                        <option value="technician" {{ $oldCareer == 'technician' ? 'selected' : '' }}>{{ __('messages.career_level_technician') }}</option>
                                        <option value="leader" {{ $oldCareer == 'leader' ? 'selected' : '' }}>{{ __('messages.career_level_leader') }}</option>
                                        <option value="manager" {{ $oldCareer == 'manager' ? 'selected' : '' }}>{{ __('messages.career_level_manager') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="travel_required">{{ __('messages.travel_required') }} <span class="text-danger">*</span></label>
                                    <select name="travel_required" id="travel_required" class="form-control" required>
                                        @php $oldTravel = old('travel_required', $postJob->travel_required); @endphp
                                        <option value="0" {{ (string)$oldTravel === '0' ? 'selected' : '' }}>{{ __('messages.no') }}</option>
                                        <option value="1" {{ (string)$oldTravel === '1' ? 'selected' : '' }}>{{ __('messages.yes') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Education Level -->
                                <div class="form-group col-md-4">
                                    <label for="education_level">{{ __('messages.education_level') }} <span class="text-danger">*</span></label>
                                    <select name="education_level" id="education_level" class="form-control" required>
                                        @php $oldEdu = old('education_level', $postJob->education_level); @endphp
                                        <option value="not_specified" {{ $oldEdu == 'not_specified' ? 'selected' : '' }}>{{ __('messages.education_not_specified') }}</option>
                                        <option value="any_graduate" {{ $oldEdu == 'any_graduate' ? 'selected' : '' }}>{{ __('messages.education_any_graduate') }}</option>
                                        <option value="apprenticeship_degree" {{ $oldEdu == 'apprenticeship_degree' ? 'selected' : '' }}>{{ __('messages.education_apprenticeship_degree') }}</option>
                                        <option value="traineeship_degree" {{ $oldEdu == 'traineeship_degree' ? 'selected' : '' }}>{{ __('messages.education_traineeship_degree') }}</option>
                                        <option value="secondary_degree" {{ $oldEdu == 'secondary_degree' ? 'selected' : '' }}>{{ __('messages.education_secondary_degree') }}</option>
                                        <option value="undergraduate_diploma" {{ $oldEdu == 'undergraduate_diploma' ? 'selected' : '' }}>{{ __('messages.education_undergraduate_diploma') }}</option>
                                        <option value="high_school_graduate" {{ $oldEdu == 'high_school_graduate' ? 'selected' : '' }}>{{ __('messages.education_high_school_graduate') }}</option>
                                        <option value="associate_degree" {{ $oldEdu == 'associate_degree' ? 'selected' : '' }}>{{ __('messages.education_associate_degree') }}</option>
                                        <option value="college_degree" {{ $oldEdu == 'college_degree' ? 'selected' : '' }}>{{ __('messages.education_college_degree') }}</option>
                                        <option value="university_degree" {{ $oldEdu == 'university_degree' ? 'selected' : '' }}>{{ __('messages.education_university_degree') }}</option>
                                        <option value="bachelors_degree" {{ $oldEdu == 'bachelors_degree' ? 'selected' : '' }}>{{ __('messages.education_bachelor_degree') }}</option>
                                        <option value="masters_degree" {{ $oldEdu == 'masters_degree' ? 'selected' : '' }}>{{ __('messages.education_master_degree') }}</option>
                                        <option value="doctorate_degree" {{ $oldEdu == 'doctorate_degree' ? 'selected' : '' }}>{{ __('messages.education_doctorate_degree') }}</option>
                                        <option value="professional_degree" {{ $oldEdu == 'professional_degree' ? 'selected' : '' }}>{{ __('messages.education_professional_degree') }}</option>
                                        <option value="not_specified_2" {{ $oldEdu == 'not_specified_2' ? 'selected' : '' }}>{{ __('messages.education_not_specified_2') }}</option>
                                        <option value="any_graduate_2" {{ $oldEdu == 'any_graduate_2' ? 'selected' : '' }}>{{ __('messages.education_any_graduate_2') }}</option>
                                        <option value="apprenticeship_degree_2" {{ $oldEdu == 'apprenticeship_degree_2' ? 'selected' : '' }}>{{ __('messages.education_apprenticeship_degree_2') }}</option>
                                        <option value="traineeship_degree_2" {{ $oldEdu == 'traineeship_degree_2' ? 'selected' : '' }}>{{ __('messages.education_traineeship_degree_2') }}</option>
                                        <option value="secondary_degree_2" {{ $oldEdu == 'secondary_degree_2' ? 'selected' : '' }}>{{ __('messages.education_secondary_degree_2') }}</option>
                                        <option value="undergraduate_diploma_2" {{ $oldEdu == 'undergraduate_diploma_2' ? 'selected' : '' }}>{{ __('messages.education_undergraduate_diploma_2') }}</option>
                                    </select>
                                </div>
                            
                                <!-- Working Address block -->
                                <div class="col-md-8">
                                    <h6 class="font-weight-bold mb-2">{{ __('messages.pjr_working_address') }}</h6>
                                    <p class="text-muted small mb-3">{{ __('messages.pjr_specify_work_location') }}</p>
                            
                                    <div class="form-row">
                                        <div class="form-group col-md-3">
                                            <label for="street_address">{{ __('messages.pjr_street_house_nr') }} <span class="text-danger">*</span></label>
                                            <input type="text" name="street_address" id="street_address" class="form-control" required
                                                   placeholder="{{ __('messages.pjr_street_name') }}"
                                                   value="{{ old('street_address', $postJob->street_address) }}">
                                        </div>
                            
                                        <div class="form-group col-md-3">
                                            <label for="house_number">{{ __('messages.pjr_pobox_city_country') }} <span class="text-danger">*</span></label>
                                            <input type="text" name="house_number" id="house_number" class="form-control" required
                                                   placeholder="{{ __('messages.pjr_house_unit_no') }}"
                                                   value="{{ old('house_number', $postJob->house_number) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            

                            <div class="row">
                                <div class="form-group col-md-6 quill-group">
                                    <label for="description">{{ __('messages.description') }} <span class="text-danger">*</span></label>
                                    <textarea name="description" id="description" class="form-control textarea editor-fixed" rows="6" placeholder="{{ __('messages.description') }}" required>{{ old('description', $postJob->description) }}</textarea>
                                </div>
                                <div class="form-group col-md-6 quill-group">
                                    <label for="requirement">{{ __('messages.pjr_skills_requirements') }} <span class="text-danger">*</span></label>
                                    <textarea name="requirement" id="requirement" class="form-control textarea editor-fixed" rows="6" placeholder="{{ __('messages.pjr_requirements_placeholder') }}" required>{{ old('requirement', $postJob->requirement) }}</textarea>
                                    <small class="help-block with-errors text-danger"></small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6 quill-group">
                                    <label for="duties">{{ __('messages.pjr_duties_responsibilities') }} <span class="text-danger">*</span></label>
                                    <textarea name="duties" id="duties" class="form-control textarea editor-fixed" rows="6" placeholder="{{ __('messages.pjr_duties_placeholder') }}" required>{{ old('duties', $postJob->duties) }}</textarea>
                                </div>
                                <div class="form-group col-md-6 quill-group">
                                    <label for="benefits">{{ __('messages.pjr_benefits') }} <span class="text-danger">*</span></label>
                                    <textarea name="benefits" id="benefits" class="form-control textarea editor-fixed" rows="6" placeholder="{{ __('messages.pjr_benefits_placeholder') }}" required>{{ old('benefits', $postJob->benefits) }}</textarea>
                                </div></div>
                           

                           
                                <div class="form-group custom-file col-md-6 mt-4">
                                    <label for="image"
                                        class="custom-file-label upload-label">{{ __('messages.image') }}</label>
                                    <input type="file" name="image[]" id="image"
                                        class="custom-file-input custom-file-input-sm detail" accept="image/*"
                                        multiple>
                                    <small id="post-job-image-prepare-status" class="d-none mt-1 text-primary fw-bold"></small>
                                    <div id="imageContainer"></div>
                                    <button type="button" id="showMoreButton" class="btn btn-primary mt-3"
                                        style="display: none;">{{ __('messages.pjr_show_more') }}</button>
                                </div>
                            </div>
                            <div class="row mt-4 mb-4">
                                <div class="col-md-12 d-flex justify-content-end">
                                    <button type="submit" id="post-job-submit-button" class="btn btn-md btn-primary px-4 py-2 me-3" data-default-text="{{ __('messages.publish') }}">{{ __('messages.publish') }}</button>
                                </div>
                            </div>
                        </form>

                        <!-- Image Modal -->
                        <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="imageModalLabel">{{ __('messages.image') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="{{ __('messages.close') }}">X</button>
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
                height: 150px;
                /* Set your desired fixed height for images in the popup */
                margin-right: 10px;
                /* Add margin between images */
                margin-bottom: 10px;
            }
            .editor-fixed {
                min-height: 180px;
            }
                        </style>
 
         <script type="text/javascript">
             (function($) {
                 "use strict";
                 $(document).ready(function() {
                                         var country_id = "{{ old('country_id', $postJob->country_id) }}";
                    var state_id = "{{ old('state_id', $postJob->state_id) }}";
                    var city_id = "{{ old('city_id', $postJob->city_id) }}";
                    var category_id = "{{ old('category_id', $postJob->category_id) }}";
                    var subcategory_id = "{{ old('subcategory_id', $postJob->subcategory_id) }}";

                    getStates(country_id, state_id); // Initial load of states based on country
                    getCities(state_id, city_id); // Initial load of cities based on state
                    getSubCategory(category_id, subcategory_id); // Initial load of subcategory based on category

                    // If dates already present (old input), calculate totals and show them
                    var startVal = $('#start_date').val();
                    var endVal = $('#end_date').val();
                    var oldTotalDays = "{{ old('total_days', $postJob->total_days) }}";
                    var oldTotalHours = "{{ old('total_hours', $postJob->total_hours) }}";
                    if (startVal && endVal) {
                        calculateDays();
                    } else {
                        if (oldTotalDays) { $('#total_day_div').val(oldTotalDays); $('#hidden_total_days').val(oldTotalDays); }
                        if (oldTotalHours) { $('#total_hours_div').val(oldTotalHours); $('#hidden_total_hours').val(oldTotalHours); }
                    }
                    // initialize budget
                    setTimeout(recalcBudget, 0);
 
                     // Preview selected images
                     const input = document.getElementById('image');
                     const container = document.getElementById('imageContainer');
                     const showMoreBtn = document.getElementById('showMoreButton');
                     const MAX_VISIBLE = 4;
                     const pjrCreateLang = {
                         show_more: @json(__('messages.pjr_show_more')),
                         show_less: @json(__('messages.pjr_show_less')),
                     };
                     
                     function clearContainer() {
                         while (container.firstChild) container.removeChild(container.firstChild);
                     }
                     
                     function renderPreviews(files) {
                         clearContainer();
                         Array.from(files).forEach((file, idx) => {
                             const objectUrl = URL.createObjectURL(file);
                             const img = document.createElement('img');
                             img.src = objectUrl;
                             img.alt = 'preview-' + idx;
                             img.className = 'rounded';
                             img.style.maxWidth = '120px';
                             img.style.maxHeight = '120px';
                             img.style.marginRight = '10px';
                             img.style.marginBottom = '10px';
                             img.addEventListener('load', function() {
                                 URL.revokeObjectURL(objectUrl);
                             }, { once: true });
                             const wrapper = document.createElement('div');
                             wrapper.style.display = idx < MAX_VISIBLE ? 'inline-block' : 'none';
                             wrapper.appendChild(img);
                             container.appendChild(wrapper);
                             if (idx === files.length - 1) {
                                 showMoreBtn.style.display = files.length > MAX_VISIBLE ? 'inline-block' : 'none';
                                 showMoreBtn.textContent = pjrCreateLang.show_more;
                                 showMoreBtn.dataset.expanded = 'false';
                             }
                         });
                         showMoreBtn.onclick = () => {
                             const expanded = showMoreBtn.dataset.expanded === 'true';
                             const children = Array.from(container.children);
                             children.forEach((child, idx) => {
                                 if (idx >= MAX_VISIBLE) {
                                     child.style.display = expanded ? 'none' : 'inline-block';
                                 }
                             });
                             showMoreBtn.textContent = expanded ? pjrCreateLang.show_more : pjrCreateLang.show_less;
                             showMoreBtn.dataset.expanded = expanded ? 'false' : 'true';
                         };
                     }

                     function setPostJobImagesPreparing(isPreparing) {
                         window.postJobImagesPreparing = isPreparing;
                         const submitButton = document.getElementById('post-job-submit-button');
                         if (!submitButton) return;
                         if (!submitButton.dataset.defaultText) {
                             submitButton.dataset.defaultText = submitButton.textContent || 'Publish';
                         }
                         submitButton.disabled = isPreparing;
                         submitButton.classList.toggle('disabled', isPreparing);
                         submitButton.classList.toggle('btn-secondary', isPreparing);
                         submitButton.classList.toggle('btn-primary', !isPreparing);
                         submitButton.textContent = isPreparing ? 'Preparing images...' : submitButton.dataset.defaultText;
                     }

                     function updatePostJobImagePrepareStatus(message) {
                         const status = document.getElementById('post-job-image-prepare-status');
                         if (!status) return;
                         status.textContent = message || '';
                         status.classList.toggle('d-none', !message);
                     }

                     function postJobCanvasToBlob(canvas, quality) {
                         return new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));
                     }

                     function loadPostJobImage(file) {
                         return new Promise((resolve, reject) => {
                             const image = new Image();
                             const objectUrl = URL.createObjectURL(file);
                             image.onload = function() {
                                 URL.revokeObjectURL(objectUrl);
                                 resolve(image);
                             };
                             image.onerror = function() {
                                 URL.revokeObjectURL(objectUrl);
                                 reject();
                             };
                             image.src = objectUrl;
                         });
                     }

                     async function preparePostJobImageForUpload(file, batchSize) {
                         const isLargeBatch = batchSize >= 3;
                         const isVeryLargeBatch = batchSize >= 5;
                         const maxOriginalSize = 4 * 1024 * 1024;
                         const maxPreparedSize = isVeryLargeBatch ? 50 * 1024 : (isLargeBatch ? 80 * 1024 : 180 * 1024);
                         const maxDimension = isVeryLargeBatch ? 560 : (isLargeBatch ? 650 : 900);

                         if (file.size > maxOriginalSize) {
                             throw new Error('Each image must be 4 MB or smaller.');
                         }

                         if (!file.type || !file.type.startsWith('image/')) {
                             return file;
                         }

                         const image = await loadPostJobImage(file);
                         if (!isLargeBatch && file.size <= maxPreparedSize && Math.max(image.width, image.height) <= maxDimension) {
                             return file;
                         }

                         const ratio = Math.min(maxDimension / image.width, maxDimension / image.height, 1);
                         const canvas = document.createElement('canvas');
                         canvas.width = Math.max(1, Math.round(image.width * ratio));
                         canvas.height = Math.max(1, Math.round(image.height * ratio));
                         const context = canvas.getContext('2d');
                         context.fillStyle = '#ffffff';
                         context.fillRect(0, 0, canvas.width, canvas.height);
                         context.drawImage(image, 0, 0, canvas.width, canvas.height);

                         const qualities = isVeryLargeBatch ? [0.40, 0.34, 0.28, 0.22] : (isLargeBatch ? [0.50, 0.42, 0.34, 0.28] : [0.64, 0.54, 0.44, 0.36]);
                         let blob = null;
                         for (const quality of qualities) {
                             blob = await postJobCanvasToBlob(canvas, quality);
                             if (blob && blob.size <= maxPreparedSize) break;
                         }

                         if (!blob) return file;

                         return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', {
                             type: 'image/jpeg',
                             lastModified: Date.now()
                         });
                     }
                     
                     // Load existing images on page load (for edit mode)
                     function loadExistingImages() {
                         const existingImages = @json($postJob->images ?? []);
                         if (existingImages && existingImages.length > 0) {
                             clearContainer();
                             existingImages.forEach((imagePath, idx) => {
                                 const img = document.createElement('img');
                                 // Handle both relative and absolute paths
                                 const fullPath = imagePath.startsWith('http') 
                                     ? imagePath 
                                     : "{{ asset('storage') }}/" + imagePath.replace(/^\/+/, '');
                                 img.src = fullPath;
                                 img.alt = 'existing-' + idx;
                                 img.className = 'rounded';
                                 img.style.maxWidth = '120px';
                                 img.style.maxHeight = '120px';
                                 img.style.marginRight = '10px';
                                 img.style.marginBottom = '10px';
                                 const wrapper = document.createElement('div');
                                 wrapper.style.display = idx < MAX_VISIBLE ? 'inline-block' : 'none';
                                 wrapper.appendChild(img);
                                 container.appendChild(wrapper);
                             });
                             
                             if (existingImages.length > MAX_VISIBLE) {
                                 showMoreBtn.style.display = 'inline-block';
                                 showMoreBtn.textContent = pjrCreateLang.show_more;
                                 showMoreBtn.dataset.expanded = 'false';
                                 showMoreBtn.onclick = () => {
                                     const expanded = showMoreBtn.dataset.expanded === 'true';
                                     const children = Array.from(container.children);
                                     children.forEach((child, idx) => {
                                         if (idx >= MAX_VISIBLE) {
                                             child.style.display = expanded ? 'none' : 'inline-block';
                                         }
                                     });
                                     showMoreBtn.textContent = expanded ? pjrCreateLang.show_more : pjrCreateLang.show_less;
                                     showMoreBtn.dataset.expanded = expanded ? 'false' : 'true';
                                 };
                             }
                         }
                     }
                     
                     // Load existing images when page loads
                     loadExistingImages();
                     
                     if (input) {
                         input.addEventListener('change', async function(e) {
                             const files = Array.from(e.target.files || []);
                             if (!files.length) {
                                 updatePostJobImagePrepareStatus('');
                                 renderPreviews([]);
                                 return;
                             }

                             setPostJobImagesPreparing(true);
                             try {
                                 const dataTransfer = new DataTransfer();
                                 updatePostJobImagePrepareStatus('Preparing 0 of ' + files.length + ' images...');

                                 for (let index = 0; index < files.length; index++) {
                                     updatePostJobImagePrepareStatus('Preparing ' + (index + 1) + ' of ' + files.length + ' images...');
                                     const preparedFile = await preparePostJobImageForUpload(files[index], files.length);
                                     dataTransfer.items.add(preparedFile);
                                 }

                                 e.target.files = dataTransfer.files;
                                 renderPreviews(e.target.files);
                                 updatePostJobImagePrepareStatus(e.target.files.length + ' image' + (e.target.files.length === 1 ? '' : 's') + ' ready for upload.');
                             } catch (error) {
                                 e.target.value = '';
                                 clearContainer();
                                 updatePostJobImagePrepareStatus('');
                                 if (typeof Snackbar !== 'undefined') {
                                     Snackbar.show({ text: error.message || 'Please choose valid images.', pos: 'bottom-center', backgroundColor: '#d32f2f', actionTextColor: '#fff' });
                                 } else {
                                     alert(error.message || 'Please choose valid images.');
                                 }
                             } finally {
                                 setPostJobImagesPreparing(false);
                             }
                         });
                     }

                     const postJobForm = document.getElementById('postJob');
                     if (postJobForm) {
                         postJobForm.addEventListener('submit', function(e) {
                             if (window.postJobImagesPreparing) {
                                 e.preventDefault();
                                 if (typeof Snackbar !== 'undefined') {
                                     Snackbar.show({ text: 'Please wait, images are preparing for upload.', pos: 'bottom-center', backgroundColor: '#d32f2f', actionTextColor: '#fff' });
                                 } else {
                                     alert('Please wait, images are preparing for upload.');
                                 }
                                 return false;
                             }
                         });
                     }
 
                     // Fetch states based on selected country
                     $(document).on('change', '#country_id', function() {
                         var selectedCountryId = $(this).val();
                         getStates(selectedCountryId, state_id);
                     });
 
                     // Fetch cities based on selected state
                     $(document).on('change', '#state_id', function() {
                         var selectedStateId = $(this).val();
                         getCities(selectedStateId, city_id);
                     });
 
                     // Fetch subcategories based on selected category
                     $(document).on('change', '#category_id', function() {
                         var selectedCategoryId = $(this).val();
                         getSubCategory(selectedCategoryId, subcategory_id);
                     });
                 });

                // Function to fetch states
                function getStates(country_id, selectedState = "") {
                    if (country_id != '') {
                        var getStateListUrl = "{{ route('ajax-list', ['type' => 'state', 'country_id' => '']) }}" +
                            country_id;
                        getStateListUrl = getStateListUrl.replace('amp;', '');

                        $('#state_id').select2({
                            width: '100%',
                            placeholder: "{{ __('messages.select_name', ['select' => __('messages.state')]) }}",
                        });

                        $.ajax({
                            url: getStateListUrl,
                            success: function(result) {
                                $('#state_id').empty();
                                result.results.forEach(function(state) {
                                    var option = new Option(state.text, state.id, false, false);
                                    $('#state_id').append(option);
                                });

                                if (selectedState !== null && selectedState !== 0) {
                                    $("#state_id").val(selectedState).trigger('change');
                                }
                            }
                        });
                    }
                }

                // Function to fetch cities based on selected state
                function getCities(state_id, selectedCity = "") {
                    if (state_id != '') {
                        var getCityListUrl = "{{ route('ajax-list', ['type' => 'city', 'state_id' => '']) }}" + state_id;
                        getCityListUrl = getCityListUrl.replace('amp;', '');

                        $('#city_id').select2({
                            width: '100%',
                            placeholder: "{{ __('messages.select_name', ['select' => __('messages.city')]) }}",
                        });

                        $.ajax({
                            url: getCityListUrl,
                            success: function(result) {
                                $('#city_id').empty();
                                result.results.forEach(function(city) {
                                    var option = new Option(city.text, city.id, false, false);
                                    $('#city_id').append(option);
                                });

                                if (selectedCity !== null && selectedCity !== 0) {
                                    $("#city_id").val(selectedCity).trigger('change');
                                }
                            }
                        });
                    }
                }
                function setMinDates() {
               var today = new Date().toISOString().split('T')[0];
               $('#start_date').attr('min', today);
               $('#end_date').attr('min', today);
           }
       
           // Function to calculate days between dates
         // Function to calculate days between dates
function calculateDays() {
    var startDate = $('#start_date').val();
    var endDate = $('#end_date').val();
    var priceType = $('#price_type').val();

    if (startDate && endDate) {
        if (startDate > endDate) {
            $('#start_date_error').css('display', 'block');
        } else {
            $('#start_date_error').css('display', 'none');

            var start = new Date(startDate);
            var end = new Date(endDate);
            var diffTime = end - start;
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // Days difference

            if (diffDays > 0) {
                $('#total_day_div').val(diffDays);
                $('#hidden_total_days').val(diffDays);
                if (priceType === 'daily') {
                    // 8 hours per day calculated automatically
                    var hours = diffDays * 8;
                    $('#total_hours_div').val(hours).attr('max', hours).attr('readonly', true);
                    $('#hidden_total_hours').val(hours).attr('max', hours);
                } else {
                    // user can set hours manually for fixed/hourly
                    $('#total_hours_div').attr('readonly', false).attr('max', null);
                }
                // ensure no conflicting max on days
                $('#total_day_div').removeAttr('max');
            } else {
                $('#total_day_div').val(0).removeAttr('max');
                $('#hidden_total_days').val(0);
                if (priceType === 'daily') {
                    $('#total_hours_div').val(0).attr('readonly', true);
                    $('#hidden_total_hours').val(0);
                }
            }
        }
    } else {
        $('#hidden_total_days').val(0);
        $('#total_day_div').val(0).removeAttr('max');
        if (priceType === 'daily') {
            $('#total_hours_div').val(0).attr('readonly', true);
            $('#hidden_total_hours').val(0);
        }
    }
    recalcBudget();
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

                       function recalcBudget() {
                var priceType = $('#price_type').val();
                var price = parseFloat($('#price').val()) || 0;
                var days = parseInt($('#total_day_div').val(), 10) || 0;
                var hours = parseInt($('#total_hours_div').val(), 10) || 0;
                var total = 0;
                if (priceType === 'daily') {
                    total = price * days;
                } else if (priceType === 'hourly') {
                    total = price * hours;
                } else if (priceType === 'fixed') {
                    total = price;
                }
                $('#total_budget').val(total);
                $('#hidden_total_hours').val(hours);
                $('#hidden_total_days').val(days);
            }

            $('#price_type').on('change', function() {
                var priceType = $(this).val();
                if (priceType === 'daily') {
                    calculateDays();
                } else {
                    $('#total_hours_div').attr('readonly', false).attr('max', null);
                }
                recalcBudget();
            });

            $('#price, #total_hours_div, #total_day_div').on('input', function(){
                // guard against invalid max combinations
                var days = parseInt($('#total_day_div').val(), 10) || 0;
                var priceType = $('#price_type').val();
                if (priceType === 'daily') {
                    var hours = days * 8;
                    $('#total_hours_div').val(hours).attr('readonly', true).attr('max', hours);
                    $('#hidden_total_hours').val(hours);
                }
                recalcBudget();
            });
                // Function to fetch subcategories based on selected category
                function getSubCategory(category_id, selectedSubCategory = "") {
                    if (category_id != '') {
                        var getSubCategoryListUrl =
                            "{{ route('ajax-list', ['type' => 'subcategory_list', 'category_id' => '']) }}" + category_id;
                        getSubCategoryListUrl = getSubCategoryListUrl.replace('amp;', '');

                        $('#subcategory_id').select2({
                            width: '100%',
                            placeholder: "{{ __('messages.select_name', ['select' => __('messages.subcategory')]) }}",
                        });

                        $.ajax({
                            url: getSubCategoryListUrl,
                            success: function(result) {
                                $('#subcategory_id').empty();
                                result.results.forEach(function(subcategory) {
                                    var option = new Option(subcategory.text, subcategory.id, false,
                                        false);
                                    $('#subcategory_id').append(option);
                                });

                                if (selectedSubCategory !== null && selectedSubCategory !== 0) {
                                    $("#subcategory_id").val(selectedSubCategory).trigger('change');
                                }
                            }
                        });
                    }
                }

            })(jQuery);
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function mountQuill(textareaId) {
                    var textarea = document.getElementById(textareaId);
                    if (!textarea) return;
                    var container = document.createElement('div');
                    container.id = textareaId + '_quill';
                    container.innerHTML = textarea.value || '';
                    textarea.classList.add('d-none');
                    textarea.parentNode.insertBefore(container, textarea.nextSibling);
                    var quill = new Quill('#' + container.id, {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                ['bold', 'italic', 'underline'],
                                [{ list: 'ordered' }, { list: 'bullet' }],
                                ['link'],
                                ['clean']
                            ]
                        }
                    });
                    var form = textarea.closest('form');
                    if (form) {
                        form.addEventListener('submit', function () {
                            textarea.value = quill.root.innerHTML;
                        });
                    }
                }
                ['description','requirement','duties','benefits'].forEach(mountQuill);
            });
        </script>
    @endsection
</x-master-layout>
