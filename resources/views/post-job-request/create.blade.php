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
                   <form method="POST" action="{{ route('postJobRequest.save') }}" enctype="multipart/form-data" id="postJob">
                      @csrf
                      <input type="hidden" name="id" value="{{ old('id', $postJob->id ?? '') }}">
                      <div class="row">
                         <div class="form-group col-md-2">
                            <label for="title">{{ __('messages.title') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="{{ __('messages.title') }}" required>
                            <small class="help-block with-errors text-danger"></small>
                         </div>
                         <div class="form-group col-md-2">
                            <label for="country_id">{{ __('messages.country') }} <span class="text-danger">*</span></label>
                            <select name="country_id" id="country_id" class="select2js form-group category" required>
                            </select>
                         </div>
                         <div class="form-group col-md-2">
                            <label for="city_id">{{ __('messages.city') }} <span class="text-danger">*</span></label>
                            <select name="city_id" id="city_id" class="select2js form-group category" required>
                            </select>
                         </div>
                         <div class="form-group col-md-2">
                            <label for="category_id">{{ __('messages.category') }} <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="select2js form-group category" required>
                            </select>
                         </div>
                         <div class="form-group col-md-2">
                            <label for="subcategory_id">{{ __('messages.subcategory') }} <span class="text-danger">*</span></label>
                            <select name="subcategory_id" id="subcategory_id" class="select2js form-group subcategory_id" required>
                            </select>
                         </div>
                      </div>
                      <div class="row">
                         <div class="form-group col-md-2" id="price_div">
                            <label for="price">{{ __('messages.price') }} <span class="text-danger">*</span></label>
                            <input type="number" name="price" id="price" class="form-control" min="1" required>
                         </div>
                         <div class="form-group col-md-2">
                            <label for="start_date">{{ __('messages.start_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                         </div>
                         <div class="form-group col-md-2">
                            <label for="end_date">{{ __('messages.end_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                         </div>
                         <div class="form-group col-md-2">
                            <label for="total_hours">{{ __('messages.total_hours') }} <span class="text-danger">*</span></label>
                            <input type="number" name="total_hours" id="total_hours_div" class="form-control" min="1" required>
                         </div>
                      </div>
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
                  $('#total_hours_div').val(diffDays * 24);
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
                      var img = $('<img>').attr({'src': imageUrl, 'class': 'img-fluid mt-2', 'style': 'width: 27%; height: 90px;'});
                      $('#imageContainer').append(img);
                  }
              }
          });
       });
    </script>
    @endsection
 </x-master-layout>
 