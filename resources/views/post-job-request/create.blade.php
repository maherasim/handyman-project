<x-master-layout>
    <div class="container-fluid">
       <div class="row">
          <div class="col-lg-12">
             <div class="card card-block card-stretch">
                <div class="card-body p-0">
                   <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                      <h5 class="font-weight-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
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
                      <input type="hidden" name="id" value="{{ $postJob->id ?? '' }}">
 
                      <div class="row">
                         <div class="form-group col-md-2">
                            <label for="title" class="form-control-label">{{ __('messages.title') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="{{ __('messages.title') }}" required>
                         </div>
 
                         <div class="form-group col-md-2">
                            <label for="country_id" class="form-control-label">{{ __('messages.country') }} <span class="text-danger">*</span></label>
                            <select id="country_id" name="country_id" class="select2js form-group category" required>
                               <option value="">{{ __('messages.select_name', ['select' => __('messages.country')]) }}</option>
                            </select>
                         </div>
 
                         <div class="form-group col-md-2">
                            <label for="city_id" class="form-control-label">{{ __('messages.city') }} <span class="text-danger">*</span></label>
                            <select id="city_id" name="city_id" class="select2js form-group category" required>
                               <option value="">{{ __('messages.select_name', ['select' => __('messages.city')]) }}</option>
                            </select>
                         </div>
 
                         <div class="form-group col-md-2">
                            <label for="category_id" class="form-control-label">{{ __('messages.category') }} <span class="text-danger">*</span></label>
                            <select id="category_id" name="category_id" class="select2js form-group category" required>
                               <option value="">{{ __('messages.select_name', ['select' => __('messages.category')]) }}</option>
                            </select>
                         </div>
 
                         <div class="form-group col-md-2">
                            <label for="subcategory_id" class="form-control-label">{{ __('messages.subcategory') }} <span class="text-danger">*</span></label>
                            <select id="subcategory_id" name="subcategory_id" class="select2js form-group subcategory_id" required>
                               <option value="">{{ __('messages.select_name', ['select' => __('messages.subcategory')]) }}</option>
                            </select>
                         </div>
                      </div>
 
                      <div class="row">
                         <div class="form-group col-md-2">
                            <label for="price" class="form-control-label">{{ __('messages.price') }} <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" min="1" required>
                         </div>
 
                         <div class="form-group col-md-2">
                            <label for="start_date" class="form-control-label">{{ __('messages.start_date') }} <span class="text-danger">*</span></label>
                            <input type="date" id="start_date" name="start_date" class="form-control" required>
                         </div>
 
                         <div class="form-group col-md-2">
                            <label for="end_date" class="form-control-label">{{ __('messages.end_date') }} <span class="text-danger">*</span></label>
                            <input type="date" id="end_date" name="end_date" class="form-control" required>
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
    <script type="text/javascript">
       $(document).ready(function() {
          var category_id = "{{ $postJob->category_id ?? '' }}";
          var country_id = "{{ $postJob->country_id ?? '' }}";
          var city_id = "{{ $postJob->city_id ?? '' }}";
 
          loadDropdown("country", "#country_id", country_id);
          loadDropdown("category", "#category_id", category_id);
 
          $("#country_id").change(function() {
             var selectedCountry = $(this).val();
             loadDropdown("cityFromCountry", "#city_id", city_id, selectedCountry);
          });
 
          $("#category_id").change(function() {
             var selectedCategory = $(this).val();
             loadDropdown("subcategory_list", "#subcategory_id", "", selectedCategory);
          });
 
          function loadDropdown(type, selector, selectedValue = "", parentID = "") {
             var url = "{{ route('ajax-list', ['type' => '']) }}/" + type;
             if (parentID) {
                url += "?parent_id=" + parentID;
             }
 
             $.ajax({
                url: url,
                success: function(result) {
                   $(selector).empty().append(`<option value="">Select</option>`);
                   $.each(result.results, function(index, item) {
                      var selected = item.id == selectedValue ? "selected" : "";
                      $(selector).append(`<option value="${item.id}" ${selected}>${item.name}</option>`);
                   });
                }
             });
          }
       });
 
       function calculateDays() {
          var startDate = $('#start_date').val();
          var endDate = $('#end_date').val();
 
          if (startDate && endDate) {
             var start = new Date(startDate);
             var end = new Date(endDate);
             var diffTime = end - start;
             var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
 
             if (diffDays > 0) {
                $('#total_day_div').val(diffDays);
             }
          }
       }
 
       $('#start_date, #end_date').on('change', calculateDays);
    </script>
    @endsection
 </x-master-layout>
 