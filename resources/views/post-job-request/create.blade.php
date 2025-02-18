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
                          <!-- Fields replaced with HTML -->
                          <div class="form-group col-md-2">
                             <label for="title">{{ __('messages.title') }} <span class="text-danger">*</span></label>
                             <input type="text" name="title" id="title" class="form-control" placeholder="{{ __('messages.title') }}" title="Please enter alphabetic characters and spaces only" value="{{ old('title', $postJob->title) }}" required>
                             <small class="help-block with-errors text-danger"></small>
                          </div>
                          
                          <div class="form-group col-md-2">
                             <label for="country_id">{{ __('messages.country') }} <span class="text-danger">*</span></label>
                             <select name="country_id" id="country_id" class="select2js form-group category" required data-placeholder="{{ __('messages.select_name', ['select' => __('messages.country')]) }}" data-ajax--url="{{ route('ajax-list', ['type' => 'country']) }}">
                                 <option value="{{ optional($postJob->country)->id }}" selected>{{ optional($postJob->country)->name }}</option>
                             </select>
                          </div>
                          
                          <div class="form-group col-md-2">
                             <label for="city_id">{{ __('messages.select_name', ['select' => __('messages.city')]) }} <span class="text-danger">*</span></label>
                             <select name="city_id" id="city_id" class="select2js form-group category" required data-placeholder="{{ __('messages.select_name', ['select' => __('messages.city')]) }}"></select>
                          </div>
                          
                          <div class="form-group col-md-2">
                             <label for="category_id">{{ __('messages.category') }} <span class="text-danger">*</span></label>
                             <select name="category_id" id="category_id" class="select2js form-group category" required data-placeholder="{{ __('messages.select_name', ['select' => __('messages.category')]) }}" data-ajax--url="{{ route('ajax-list', ['type' => 'category']) }}">
                                 <option value="{{ optional($postJob->category_id)->id }}" selected>{{ optional($postJob->category)->name }}</option>
                             </select>
                          </div>
                          
                          <div class="form-group col-md-2">
                             <label for="subcategory_id">{{ __('messages.select_name', ['select' => __('messages.subcategory')]) }} <span class="text-danger">*</span></label>
                             <select name="subcategory_id" id="subcategory_id" class="select2js form-group subcategory_id" required data-placeholder="{{ __('messages.select_name', ['select' => __('messages.subcategory')]) }}"></select>
                          </div>
                       </div>
                       
                       <div class="row">
                        <div class="form-group col-md-2">
                            <label for="price_type">{{ __('Pice Type') }} <span
                                    class="text-danger">*</span></label>
                            <select name="job_price" id="job_price" class="form-control" required>
                                <option value="fixed">{{ __('Fixed') }}</option>
                                <option value="hourly">{{ __('Hourly') }}</option>
                                <option value="daily">{{ __('Daily') }}</option>
                            </select>
                        </div>

                        <div class="form-group col-md-2">
                            <label for="job_type">{{ __('Job Type') }} <span
                                    class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-control" required>
                                <option value="onsite">{{ __('Onsite') }}</option>
                                <option value="remote">{{ __('Remote/Homeoffice') }}</option>
                                <option value="hybrid">{{ __('Hybrid') }}</option>
                            </select>
                        </div>
                          <div class="form-group col-md-2" id="price_div">
                             <label for="price">{{ __('messages.price') }} <span class="text-danger">*</span></label>
                             <input type="number" name="price" id="price" class="form-control" min="1" step="any" placeholder="{{ __('messages.price') }}" required value="{{ old('price', $postJob->price) }}">
                             <small class="help-block with-errors text-danger"></small>
                          </div>
                          
                          <div class="form-group col-md-2">
                             <label for="start_date">{{ __('Start Date') }} <span class="text-danger">*</span></label>
                             <input type="date" name="start_date" id="start_date" class="form-control" required value="{{ old('start_date', $postJob->start_date) }}">
                             <small class="help-block with-errors text-danger"></small>
                          </div>
                          
                          <div class="form-group col-md-2">
                             <label for="end_date">{{ __('End Date') }} <span class="text-danger">*</span></label>
                             <input type="date" name="end_date" id="end_date" class="form-control" required value="{{ old('end_date', $postJob->end_date) }}">
                             <small class="help-block with-errors text-danger"></small>
                          </div>
                        

                          <div class="form-group col-md-2">
                             <label for="total_day_div">{{ __('Total Days') }} <span class="text-danger">*</span></label>
                             <input type="number" name="total_day" id="total_day_div" class="form-control" min="1" step="any" placeholder="{{ __('messages.total_days') }}" required disabled>
                             <small class="help-block with-errors text-danger"></small>
                          </div>
                          
                          <input type="hidden" name="total_days" id="hidden_total_days" value="{{ old('total_days', $postJob->total_days) }}">
                          <input type="hidden" name="total_hours" id="hidden_total_hours" value="{{ old('total_hours', $postJob->total_hours) }}">
                          
                          <div class="form-group col-md-2">
                             <label for="total_hours_div">{{ __('messages.total_hours') }} <span class="text-danger">*</span></label>
                             <input type="number" name="total_hours" id="total_hours_div" class="form-control" min="1" step="any" placeholder="{{ __('messages.total_hours') }}" required value="{{ old('total_hours', $postJob->total_hours) }}">
                             <small class="help-block with-errors text-danger"></small>
                          </div>
                       </div>
                       
                       <div class="row">
                          <div class="form-group col-md-6">
                             <label for="description">{{ __('messages.description') }}</label>
                             <textarea name="description" id="description" class="form-control textarea" rows="3" placeholder="{{ __('messages.description') }}">{{ old('description', $postJob->description) }}</textarea>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="title">{{ __('Requirment') }} <span class="text-danger">*</span></label>
                            <input type="text" name="requirnment" id="title" class="form-control" placeholder="{{ __('messages.title') }}" title="requirnment" value="{{ old('title', $postJob->requirnment) }}" required>
                            <small class="help-block with-errors text-danger"></small>
                         </div>
                          <div class="form-group custom-file col-md-6 mt-30">
                             <label for="image" class="custom-file-label upload-label">{{ __('messages.image') }}</label>
                             <input type="file" name="image[]" id="image" class="custom-file-input custom-file-input-sm detail" accept="image/*" multiple>
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
       
                       }
                   });
               }
       
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
           });
       })(jQuery);
    </script>
    <script>
         <script src="https://cdn.tiny.cloud/1/m5d82gd2rwdlg96hsxpx0e5wwmfrl2zzkcw35ys8o3glilgq/tinymce/5/tinymce.min.js"
        referrerpolicy="origin"></script>

        tinymce.init({
            selector: '#description', // Target the ID of your textarea
            plugins: 'lists link image preview', // Add any plugins you want to use
            toolbar: 'undo redo | bold italic | bullist numlist | link image preview',
            menubar: false
        });
    </script>

    @endsection
 </x-master-layout>
