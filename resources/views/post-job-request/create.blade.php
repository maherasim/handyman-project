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
                            <h5 class="font-weight-bold">Create Job request</h5>
                            <a href="{{ route('post-job-request.index') }}" class="float-right btn btn-sm btn-primary"><i
                                    class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
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
                                    <label for="price_type">{{ __('Price Type') }} <span class="text-danger">*</span></label>
                                    <select name="price_type" id="price_type" class="form-control" required>
                                        @php $oldPriceType = old('price_type', $postJob->price_type ?? 'fixed'); @endphp
                                        <option value="fixed" {{ $oldPriceType == 'fixed' ? 'selected' : '' }}>{{ __('Fixed') }}</option>
                                        <option value="hourly" {{ $oldPriceType == 'hourly' ? 'selected' : '' }}>{{ __('Hourly') }}</option>
                                        <option value="daily" {{ $oldPriceType == 'daily' ? 'selected' : '' }}>{{ __('Daily') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="job_type">{{ __('Job Type') }} <span class="text-danger">*</span></label>
                                    <select name="type" id="type" class="form-control" required>
                                        <option value="onsite" {{ old('type', $postJob->type) == 'onsite' ? 'selected' : '' }}>{{ __('Onsite') }}</option>
                                        <option value="remote" {{ old('type', $postJob->type) == 'remote' ? 'selected' : '' }}>{{ __('Remote/Homeoffice') }}</option>
                                        <option value="hybrid" {{ old('type', $postJob->type) == 'hybrid' ? 'selected' : '' }}>{{ __('Hybrid') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4" id="price_div">
                                    <label for="price">{{ __('messages.price') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="price" id="price" class="form-control" min="1" step="any" placeholder="{{ __('messages.price') }}" required value="{{ old('price', $postJob->price) }}">
                                    <small class="help-block with-errors text-danger"></small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="start_date">{{ __('Start Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" required value="{{ old('start_date', optional($postJob->start_date)->format('Y-m-d')) }}">
                                    <small class="help-block with-errors text-danger"></small>
                                    <small id="start_date_error" class="text-danger" style="display:none;">{{ __('Start date must be before end date') }}</small>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="end_date">{{ __('End Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" required value="{{ old('end_date', optional($postJob->end_date)->format('Y-m-d')) }}">
                                    <small class="help-block with-errors text-danger"></small>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="total_day_div">{{ __('Total Days') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">📅</span>
                                        <input type="number" name="total_day" id="total_day_div" class="form-control" min="1" step="any" placeholder="{{ __('total days') }}" required>
                                    </div>
                                </div>
                                <input type="hidden" name="total_days" id="hidden_total_days" value="{{ old('total_days', $postJob->total_days) }}">
                                <input type="hidden" name="total_hours" id="hidden_total_hours" value="{{ old('total_hours', $postJob->total_hours) }}">
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="total_hours_div">{{ __('Total Hours') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">⏱</span>
                                        <input type="number" name="total_hours" id="total_hours_div" class="form-control" min="1" step="any" placeholder="{{ __('total_hours') }}" required value="{{ old('total_hours', $postJob->total_hours) }}">
                                    </div>
                                    <small class="help-block with-errors text-danger"></small>
                                </div>
                                <div class="form-group col-md-4" id="total_budget_div">
                                    <label for="total_budget">{{ __('Total Budget') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="total_budget" id="total_budget" class="form-control" min="0" step="any" placeholder="{{ __('Total Budget') }}" value="{{ old('total_budget', $postJob->total_budget) }}" readonly>
                                    <small class="help-block with-errors text-danger"></small>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="job_schedule">{{ __('Job Schedule') }} <span class="text-danger">*</span></label>
                                    <select name="job_schedule" id="job_schedule" class="form-control" required>
                                        @php $oldSchedule = old('job_schedule', $postJob->job_schedule); @endphp
                                        <option value="full_time" {{ $oldSchedule == 'full_time' ? 'selected' : '' }}>{{ __('Full-Time') }}</option>
                                        <option value="part_time" {{ $oldSchedule == 'part_time' ? 'selected' : '' }}>{{ __('Part-Time') }}</option>
                                        <option value="contract" {{ $oldSchedule == 'contract' ? 'selected' : '' }}>{{ __('Contract') }}</option>
                                        <option value="temporary" {{ $oldSchedule == 'temporary' ? 'selected' : '' }}>{{ __('Temporary') }}</option>
                                        <option value="internship" {{ $oldSchedule == 'internship' ? 'selected' : '' }}>{{ __('Internship') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="remote_work_level">{{ __('Remote Work Level') }} <span class="text-danger">*</span></label>
                                    <select name="remote_work_level" id="remote_work_level" class="form-control" required>
                                        @php $oldRemote = old('remote_work_level', $postJob->remote_work_level); @endphp
                                        <option value="onsite" {{ $oldRemote == 'onsite' ? 'selected' : '' }}>{{ __('Onsite (100%)') }}</option>
                                        <option value="25_remote" {{ $oldRemote == '25_remote' ? 'selected' : '' }}>{{ __('25% Remote') }}</option>
                                        <option value="50_remote" {{ $oldRemote == '50_remote' ? 'selected' : '' }}>{{ __('50% Remote') }}</option>
                                        <option value="75_remote" {{ $oldRemote == '75_remote' ? 'selected' : '' }}>{{ __('75% Remote') }}</option>
                                        <option value="100_remote" {{ $oldRemote == '100_remote' ? 'selected' : '' }}>{{ __('100% Remote') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="career_level">{{ __('Career Level') }} <span class="text-danger">*</span></label>
                                    <select name="career_level" id="career_level" class="form-control" required>
                                        @php $oldCareer = old('career_level', $postJob->career_level); @endphp
                                        <option value="intern" {{ $oldCareer == 'intern' ? 'selected' : '' }}>{{ __('Intern') }}</option>
                                        <option value="entry" {{ $oldCareer == 'entry' ? 'selected' : '' }}>{{ __('Entry') }}</option>
                                        <option value="junior" {{ $oldCareer == 'junior' ? 'selected' : '' }}>{{ __('Junior') }}</option>
                                        <option value="mid" {{ $oldCareer == 'mid' ? 'selected' : '' }}>{{ __('Mid-Level') }}</option>
                                        <option value="senior" {{ $oldCareer == 'senior' ? 'selected' : '' }}>{{ __('Senior') }}</option>
                                        <option value="lead" {{ $oldCareer == 'lead' ? 'selected' : '' }}>{{ __('Lead') }}</option>
                                        <option value="manager" {{ $oldCareer == 'manager' ? 'selected' : '' }}>{{ __('Manager') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="travel_required">{{ __('Travel Required') }} <span class="text-danger">*</span></label>
                                    <select name="travel_required" id="travel_required" class="form-control" required>
                                        @php $oldTravel = old('travel_required', $postJob->travel_required); @endphp
                                        <option value="0" {{ (string)$oldTravel === '0' ? 'selected' : '' }}>{{ __('No') }}</option>
                                        <option value="1" {{ (string)$oldTravel === '1' ? 'selected' : '' }}>{{ __('Yes') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Education Level -->
                                <div class="form-group col-md-4">
                                    <label for="education_level">{{ __('Education Level') }} <span class="text-danger">*</span></label>
                                    <select name="education_level" id="education_level" class="form-control" required>
                                        @php $oldEdu = old('education_level', $postJob->education_level); @endphp
                                        <option value="high_school" {{ $oldEdu == 'high_school' ? 'selected' : '' }}>{{ __('High School') }}</option>
                                        <option value="associate" {{ $oldEdu == 'associate' ? 'selected' : '' }}>{{ __('Associate Degree') }}</option>
                                        <option value="undergraduate" {{ $oldEdu == 'undergraduate' ? 'selected' : '' }}>{{ __('Undergraduate Degree') }}</option>
                                        <option value="graduate" {{ $oldEdu == 'graduate' ? 'selected' : '' }}>{{ __('Graduate/Master\'s') }}</option>
                                        <option value="doctorate" {{ $oldEdu == 'doctorate' ? 'selected' : '' }}>{{ __('Doctorate') }}</option>
                                    </select>
                                </div>
                            
                                <!-- Working Address block -->
                                <div class="col-md-8">
                                    <h6 class="font-weight-bold mb-2">{{ __('Working Address') }}</h6>
                                    <p class="text-muted small mb-3">{{ __('Specify where the work will be performed.') }}</p>
                            
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="street_address">{{ __('Street & House Nr.') }}</label>
                                            <input type="text" name="street_address" id="street_address" class="form-control"
                                                   placeholder="{{ __('Street name') }}"
                                                   value="{{ old('street_address', $postJob->street_address) }}">
                                        </div>
                            
                                        <div class="form-group col-md-6">
                                            <label for="house_number">{{ __('Pobox & City-Country.') }}</label>
                                            <input type="text" name="house_number" id="house_number" class="form-control"
                                                   placeholder="{{ __('House/Unit No.') }}"
                                                   value="{{ old('house_number', $postJob->house_number) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            

                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="description">{{ __('messages.description') }}</label>
                                    <textarea name="description" id="description" class="form-control textarea editor-fixed" rows="6" placeholder="{{ __('messages.description') }}">{{ old('description', $postJob->description) }}</textarea>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="requirement">{{ __('Skills & Requirements') }} <span class="text-danger">*</span></label>
                                    <textarea name="requirement" id="requirement" class="form-control textarea editor-fixed" rows="6" placeholder="{{ __('requirements') }}" required>{{ old('requirement', $postJob->requirement) }}</textarea>
                                    <small class="help-block with-errors text-danger"></small>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="duties">{{ __('Duties & Responsibilities') }}</label>
                                    <textarea name="duties" id="duties" class="form-control textarea editor-fixed" rows="6" placeholder="{{ __('duties & responsibilities') }}">{{ old('duties', $postJob->duties) }}</textarea>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="benefits">{{ __('Benefits') }}</label>
                                    <textarea name="benefits" id="benefits" class="form-control textarea editor-fixed" rows="6" placeholder="{{ __('benefits') }}">{{ old('benefits', $postJob->benefits) }}</textarea>
                                </div>
                            </div>

                           
                                <div class="form-group custom-file col-md-6 mt-4">
                                    <label for="image"
                                        class="custom-file-label upload-label">{{ __('messages.image') }}</label>
                                    <input type="file" name="image[]" id="image"
                                        class="custom-file-input custom-file-input-sm detail" accept="image/*"
                                        multiple>
                                    <div id="imageContainer"></div>
                                    <button type="button" id="showMoreButton" class="btn btn-primary mt-3"
                                        style="display: none;">Show More</button>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-md btn-primary">{{ __('messages.publish') }}</button>
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
                     function clearContainer() {
                         while (container.firstChild) container.removeChild(container.firstChild);
                     }
                     function renderPreviews(files) {
                         clearContainer();
                         const urls = [];
                         Array.from(files).forEach((file, idx) => {
                             const reader = new FileReader();
                             reader.onload = (e) => {
                                 const img = document.createElement('img');
                                 img.src = e.target.result;
                                 img.alt = 'preview-' + idx;
                                 img.className = 'rounded';
                                 img.style.maxWidth = '120px';
                                 img.style.maxHeight = '120px';
                                 img.style.marginRight = '10px';
                                 img.style.marginBottom = '10px';
                                 const wrapper = document.createElement('div');
                                 wrapper.style.display = idx < MAX_VISIBLE ? 'inline-block' : 'none';
                                 wrapper.appendChild(img);
                                 container.appendChild(wrapper);
                                 urls.push({ wrapper });
                                 if (idx === files.length - 1) {
                                     showMoreBtn.style.display = files.length > MAX_VISIBLE ? 'inline-block' : 'none';
                                     showMoreBtn.textContent = 'Show More';
                                     showMoreBtn.dataset.expanded = 'false';
                                 }
                             };
                             reader.readAsDataURL(file);
                         });
                         showMoreBtn.onclick = () => {
                             const expanded = showMoreBtn.dataset.expanded === 'true';
                             const children = Array.from(container.children);
                             children.forEach((child, idx) => {
                                 if (idx >= MAX_VISIBLE) {
                                     child.style.display = expanded ? 'none' : 'inline-block';
                                 }
                             });
                             showMoreBtn.textContent = expanded ? 'Show More' : 'Show Less';
                             showMoreBtn.dataset.expanded = expanded ? 'false' : 'true';
                         };
                     }
                     if (input) {
                         input.addEventListener('change', (e) => renderPreviews(e.target.files));
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
            if (window.tinymce) {
                tinymce.init({
                    selector: '#description',
                    plugins: 'lists link image preview',
                    toolbar: 'undo redo | bold italic | bullist numlist | link image preview',
                    menubar: false
                });
            }
        </script>
        <script>
            if (window.tinymce) {
                tinymce.init({
                    selector: '#requirement, #duties, #benefits',
                    plugins: 'lists link image preview',
                    toolbar: 'undo redo | bold italic | bullist numlist | link image preview',
                    menubar: false
                });
            }
        </script>
         <script>
            if (window.tinymce) {
                tinymce.init({
                    selector: '#working_address',
                    plugins: 'lists link image preview',
                    toolbar: 'undo redo | bold italic | bullist numlist | link image preview',
                    menubar: false
                });
            }
        </script>
    @endsection
</x-master-layout>
