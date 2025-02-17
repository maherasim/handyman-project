<x-master-layout>
    <script src="https://cdn.tiny.cloud/1/m5d82gd2rwdlg96hsxpx0e5wwmfrl2zzkcw35ys8o3glilgq/tinymce/5/tinymce.min.js"
    referrerpolicy="origin"></script>
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
                      <input type="hidden" name="id" value="{{ old('id', $postJob->id ?? '') }}">
 
                      <div class="row">
                         <div class="form-group col-md-2">
                            <label for="title">{{ __('messages.title') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="{{ __('messages.title') }}" required>
                         </div>
 
                         <div class="form-group col-md-2">
                            <label for="country_id">{{ __('messages.country') }} <span class="text-danger">*</span></label>
                            <select name="country_id" id="country_id" class="select2js form-group category" required></select>
                         </div>
 
                         <div class="form-group col-md-2">
                            <label for="city_id">{{ __('messages.city') }} <span class="text-danger">*</span></label>
                            <select name="city_id" id="city_id" class="select2js form-group category" required></select>
                         </div>
 
                         <div class="form-group col-md-2">
                            {{ html()->label(__('messages.select_name', ['select' => __('messages.category')]) . ' <span class="text-danger">*</span>', 'category_id')->class('form-control-label') }}
                            <br />
                            {{ html()->select(
                                    'category_id',
                                    isset($servicedata) && $servicedata->category ? [$servicedata->category->id => $servicedata->category->name] : [],
                                    isset($servicedata) && $servicedata->category ? $servicedata->category->id : null
                                )->class('select2js form-group category')
                                 ->required()
                                 ->id('category_id')
                                 ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.category')]))
                                 ->attribute('data-ajax--url', route('ajax-list', ['type' => 'category'])) 
                            }}
                        </div>
                        
                        <div class="form-group col-md-2">
                            {{ html()->label(__('messages.select_name', ['select' => __('messages.subcategory')]), 'subcategory_id')->class('form-control-label') }}
                            <br />
                            {{ html()->select('subcategory_id', [])->class('select2js form-group subcategory_id')->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.subcategory')])) }}
                        </div>
                        
                      </div>
 
                      <div class="row">
                         <div class="form-group col-md-2">
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
 
                         <!-- NEW TOTAL DAYS FIELD -->
                         <div class="form-group col-md-2">
                            <label for="total_days">{{ __('messages.total_days') }}</label>
                            <input type="number" name="total_days" id="total_days" class="form-control" readonly>
                         </div>
 
                         <div class="form-group col-md-2">
                            <label for="total_hours">{{ __('messages.total_hours') }} <span class="text-danger">*</span></label>
                            <input type="number" name="total_hours" id="total_hours" class="form-control" readonly>
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
 
                  $('#total_days').val(diffDays);  // Update Total Days
                  $('#total_hours').val(diffDays * 24);  // Update Total Hours
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
                      var img = $('<img>').attr({'src': imageUrl, 'class': 'img-fluid mt-2', 'style': 'width: 27%; height: 90px;'});
                      $('#imageContainer').append(img);
                  }
              }
          });
       });
    </script>
     <script>
        function getSubCategory(category_id, selected_subcategory_id = null) {
    if (category_id) {
        $.ajax({
            url: "{{ route('ajax-list', ['type' => 'subcategory']) }}",
            type: "GET",
            data: { category_id: category_id },
            success: function(response) {
                $('#subcategory_id').empty().append('<option value="">{{ __("messages.select_name", ["select" => __("messages.subcategory")]) }}</option>');

                if (response.length > 0) {
                    $.each(response, function(index, subcategory) {
                        let selected = selected_subcategory_id == subcategory.id ? "selected" : "";
                        $('#subcategory_id').append('<option value="' + subcategory.id + '" ' + selected + '>' + subcategory.name + '</option>');
                    });
                }
            },
            error: function() {
                console.error("Failed to fetch subcategories");
            }
        });
    } else {
        $('#subcategory_id').empty().append('<option value="">{{ __("messages.select_name", ["select" => __("messages.subcategory")]) }}</option>');
    }
}

        tinymce.init({
            selector: '#description',
            plugins: 'lists link image preview',
            toolbar: 'undo redo | bold italic | bullist numlist | link image preview',
            menubar: false
        });
    
        $(document).ready(function() {
            var provider_id = "{{ isset($servicedata->provider_id) ? $servicedata->provider_id : '' }}";
            var provider_address_id = @json(isset($data) ? $data : []);
    
            var category_id = "{{ isset($servicedata->category_id) ? $servicedata->category_id : '' }}";
            var subcategory_id = "{{ isset($servicedata->subcategory_id) ? $servicedata->subcategory_id : '' }}";
    
            var country_id = "{{ isset($servicedata->country_id) ? $servicedata->country_id : 0 }}";
            var city_id = "{{ isset($servicedata->city_id) ? $servicedata->city_id : 0 }}";
            var price_type = "{{ isset($servicedata->type) ? $servicedata->type : '' }}";
    
            providerAddress(provider_id, provider_address_id);
            getSubCategory(category_id, subcategory_id);
            priceformat(price_type);
    
            $(document).on('change', '#provider_id', function() {
                var provider_id = $(this).val();
                $('#provider_address_id').empty();
                providerAddress(provider_id, provider_address_id);
            });
    
            $(document).on('change', '#category_id', function() {
                var category_id = $(this).val();
                $('#subcategory_id').empty();
                getSubCategory(category_id, subcategory_id);
            });
    
            $(document).on('change', '#price_type', function() {
                var price_type = $(this).val();
                priceformat(price_type);
            });
    
            $(document).on('change', '#country_id', function() {
                var country = $(this).val();
                $('#city_id').empty();
                cityName(country);
            });
    
            $(document).on('change', '#city_id', function() {
                var city = $(this).val();
                console.log('selected city', city);
            });
    
            $('.galary').each(function(index, value) {
                let galleryClass = $(value).attr('data-gallery');
                $(galleryClass).magnificPopup({
                    delegate: 'a#attachment_files',
                    type: 'image',
                    gallery: {
                        enabled: true,
                        navigateByImgClick: true,
                        preload: [0, 1]
                    },
                    callbacks: {
                        elementParse: function(item) {
                            if (item.el[0].className.includes('video')) {
                                item.type = 'iframe';
                                item.iframe = {
                                    markup: '<div class="mfp-iframe-scaler">' +
                                        '<div class="mfp-close"></div>' +
                                        '<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>' +
                                        '<div class="mfp-title">Some caption</div>' +
                                        '</div>'
                                };
                            } else {
                                item.type = 'image';
                                item.tLoading = 'Loading image #%curr%...';
                                item.mainClass = 'mfp-img-mobile';
                                item.image = {
                                    tError: '<a href="%url%">The image #%curr%</a> could not be loaded.'
                                };
                            }
                        }
                    }
                });
            });
        });
    </script>
    
    @endsection
 </x-master-layout>
 