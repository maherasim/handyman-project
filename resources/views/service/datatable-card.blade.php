@php $showEdit = $showEdit ?? false; @endphp
<div class="service-box-card bg-white rounded-3 mb-0 shadow-sm h-100" data-service-id="{{ $data->id }}" v-pre>
   <div class="iq-image position-relative" style="height: 200px; width: 100%; overflow: hidden; border-radius: 0.5rem;">
      {{-- Action dropdown: View / Edit (Edit only for provider owner when no booking has started) --}}
      <div class="dropdown position-absolute top-0 end-0 m-2" style="z-index: 5;" onclick="event.stopPropagation();">
         <button class="btn btn-sm btn-light rounded-circle shadow-sm dropdown-toggle" type="button" id="serviceAction-{{ $data->id }}" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('messages.action') }}">
            <i class="fa fa-ellipsis-v"></i>
         </button>
         <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="serviceAction-{{ $data->id }}">
            <li><a class="dropdown-item" href="{{ route('service.detail', $data->id) }}"><i class="fa fa-eye me-1"></i>{{ __('messages.view') }}</a></li>
            @if($showEdit)
            <li><a class="dropdown-item" href="{{ route('service.create', ['id' => $data->id]) }}"><i class="fa fa-edit me-1"></i>{{ __('messages.edit') }}</a></li>
            @endif
         </ul>
      </div>
      @if($data->visit_type == 'ONLINE')
         <span class="online-service"></span>
      @endif
      <a href="{{ route('service.detail', $data->id) }}" class="service-img d-block w-100 h-100">
         <img src="{{ isset($serviceImage) ? $serviceImage : getSingleMedia($data, 'service_attachment', null) }}" alt="service"
         class="w-100 h-100" style="object-fit: cover;" onerror="this.src='{{ asset('images/default.png') }}'"> 
      </a>

      @if(auth()->check() && auth()->user()->hasRole('user'))

         @if($favouriteService->isEmpty())
            <form method="POST" id="favoriteForm">
               @csrf

               <input type="hidden" name="service_id" class="service_id" value="{{ $data->id }}" data-service-id="{{ $data->id }}">
               @if(!empty(auth()->user()))
                  <input type="hidden" name="user_id" id="user_id" value="{{ Auth::user()->id }}">
               @endif
               <button type="button" class="btn-link serv-whishlist text-primary save_fav">
                  <svg width="12" height="13" viewBox="0 0 12 13" fill="none" xmlns=" ">
                     <path fill-rule="evenodd" clip-rule="evenodd" d="M1.43593 6.29916C0.899433 4.62416 1.52643 2.70966 3.28493 2.14316C4.20993 1.84466 5.23093 2.02066 5.99993 2.59916C6.72743 2.03666 7.78593 1.84666 8.70993 2.14316C10.4684 2.70966 11.0994 4.62416 10.5634 6.29916C9.72843 8.95416 5.99993 10.9992 5.99993 10.9992C5.99993 10.9992 2.29893 8.98516 1.43593 6.29916Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                     <path d="M8 3.84998C8.535 4.02298 8.913 4.50048 8.9585 5.06098" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
               </button>
            </form>
         @else
            <form method="POST" id="favoriteForm">
               @csrf

               <input type="hidden" name="service_id" class="service_id" value="{{ $data->id }}" data-service-id="{{ $data->id }}">
               @if(!empty(auth()->user()))
                  <input type="hidden" name="user_id" id="user_id" value="{{ Auth::user()->id }}">
               @endif
               <button type="button" class="btn-link serv-whishlist text-primary delete_fav">
                  <svg width="12" height="13" viewBox="0 0 12 13" fill="currentColor" xmlns=" ">
                     <path fill-rule="evenodd" clip-rule="evenodd" d="M1.43593 6.29916C0.899433 4.62416 1.52643 2.70966 3.28493 2.14316C4.20993 1.84466 5.23093 2.02066 5.99993 2.59916C6.72743 2.03666 7.78593 1.84666 8.70993 2.14316C10.4684 2.70966 11.0994 4.62416 10.5634 6.29916C9.72843 8.95416 5.99993 10.9992 5.99993 10.9992C5.99993 10.9992 2.29893 8.98516 1.43593 6.29916Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                     <path d="M8 3.84998C8.535 4.02298 8.913 4.50048 8.9585 5.06098" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
               </button>
            </form>
         @endif
      @else
         <form method="GET" id="favoriteForm" action="{{ route('login') }}">
            @csrf
            <button type="submit" class="btn-link serv-whishlist text-primary">
               <svg width="12" height="13" viewBox="0 0 12 13" fill="none" xmlns=" ">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M1.43593 6.29916C0.899433 4.62416 1.52643 2.70966 3.28493 2.14316C4.20993 1.84466 5.23093 2.02066 5.99993 2.59916C6.72743 2.03666 7.78593 1.84666 8.70993 2.14316C10.4684 2.70966 11.0994 4.62416 10.5634 6.29916C9.72843 8.95416 5.99993 10.9992 5.99993 10.9992C5.99993 10.9992 2.29893 8.98516 1.43593 6.29916Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M8 3.84998C8.535 4.02298 8.913 4.50048 8.9585 5.06098" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
               </svg>
            </button>
         </form>
      @endif
      <div style="
         position: absolute;
         bottom: 10px;
         left: 10px;
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         color: #fff;
         padding: 6px 12px;
         border-radius: 12px;
         font-weight: 700;
         font-size: 13px;
         box-shadow: 0 6px 18px rgba(102, 126, 234, 0.35);
         backdrop-filter: blur(8px);
      ">
         @if($data->price==0)
            {{ __('messages.free') }}
         @else
            {{ getPriceFormat($data->price) }} @if(!empty($data->type)) / {{ ucfirst($data->type) }} @endif
         @endif
      </div>
   </div>
   <a href="{{ route('service.detail', $data->id) }}"
      class="service-heading mt-2 d-block p-0 text-decoration-none"
      data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $data->name }}" aria-label="{{ $data->name }}">
      <h5 class="service-heading text-capitalize text-truncate" style="font-size:15px">
        <b>{{ Str::limit($data->name, 48) }}</b>
      </h5>
  </a>
  
  <h5  class="mt-0 mb-0 text-truncate" style="font-size: 12;">
   <span style="font-size: 12px;"> {{ $data->city ? $data->city->name : __('landingpage.sl_city_fallback') }}-{{ $data->country ? $data->country->name : __('landingpage.sl_country_fallback') }}</span>
  
  </h5>  

  
<div class="d-flex align-items-center justify-content-between w-100">
   <div class="d-flex align-items-center flex-nowrap" style="padding-left: 10px;">
       <img src="{{ getSingleMedia($data->providers, 'profile_image', null) }}"
           alt="service" class="img-fluid rounded-3 object-cover avatar-24">
       <a href="{{ route('provider.detail', $data->providers->id) }}" class="ml-2">
           <span class="font-size-14 service-user-name" style="white-space: nowrap;">
               {{ $data->providers->display_name }}
           </span>
       </a>
   </div>
   <div class="d-flex align-items-center justify-content-end">
       <img src="{{ isset($plan_icon) ? $plan_icon : asset('images/freepng.png') }}" alt="plan"
           style="width: 26px; height: 26px; margin-right: 10px;">
       @php
           $providerId = optional($data->providers)->id;
           $isAllVerified = $providerId && function_exists('verify_provider_document') ? verify_provider_document($providerId) : false;
       @endphp
       @if ($isAllVerified)
           <img src="{{ asset('images/icon/verifiedpng.png') }}" alt="verified"
               style="width: 26px; height: 26px;">
       @else
           <img src="{{ asset('images/icon/notverifiedpng.png') }}" alt="not verified"
               style="width: 26px; height: 26px;">
       @endif
   </div>
</div>

     
      
<!-- <div class="d-flex align-items-center"> -->
   <!-- <div class="d-flex align-items-center gap-2 flex-wrap">
       <div class="star-rating">
           <rating-component :readonly="true" :showrating="false" :ratingvalue="1" />
       </div>
       <h6 class="lh-sm">{{ round($totalRating, 1) }}</h6>
       <a href="#">({{ $totalReviews }} {{ __('messages.reviews') }})</a>
   </div> -->

<!-- </div> -->

      
         
        
      <!-- Statistics Section - Optimized Layout -->
      <div class="stats-section">
         <div class="row g-2 text-center">
            <!-- Reviews Section -->
            <div class="col-4">
               <div class="stats-item">
                  <div class="d-flex align-items-center justify-content-center mb-1">
                     <svg xmlns=" " width="16" height="16" viewBox="0 0 12 12" fill="none" class="stats-icon text-warning">
                        <path d="M6.58578 0.85525L7.92167 3.44562C8.02009 3.63329 8.20793 3.76362 8.42458 3.79259L11.4252 4.21427C11.6005 4.23802 11.7595 4.32723 11.8669 4.46335C11.9731 4.59773 12.0187 4.76803 11.9929 4.93543C11.9719 5.07445 11.9041 5.20304 11.8003 5.30151L9.62603 7.33523C9.467 7.47714 9.39498 7.68741 9.43339 7.89304L9.96871 10.7522C10.0257 11.0974 9.78867 11.4229 9.43339 11.4884C9.28696 11.511 9.13693 11.4872 9.0049 11.4224L6.32833 10.0768C6.12968 9.98005 5.89503 9.98005 5.69639 10.0768L3.01982 11.4224C2.69094 11.5909 2.28346 11.4762 2.10042 11.1634C2.0326 11.0389 2.0086 10.897 2.0308 10.7585L2.56612 7.89883C2.60453 7.69378 2.53191 7.48236 2.37348 7.34044L0.19921 5.30788C-0.0594455 5.06692 -0.0672472 4.67014 0.181806 4.42048C0.187207 4.41527 0.193209 4.40948 0.19921 4.40369C0.302432 4.30232 0.438061 4.23802 0.584493 4.22123L3.58514 3.79896C3.80118 3.76942 3.98902 3.64025 4.08805 3.45141L5.37592 0.85525C5.49055 0.632821 5.7282 0.494383 5.98625 0.500175H6.06667C6.29052 0.526241 6.48556 0.660046 6.58578 0.85525Z" fill="currentColor" />
                     </svg>
                     <span class="stats-value"style="font-size: 11px;">{{ round($totalRating, 1) }}</span>
                  </div>
                  <div class="stats-label">
                     <a href="{{ route('rating.all', ['service_id' => $data->id]) }}" class="text-decoration-none text-muted"style="font-size: 11px;">
                        ({{ $totalReviews }} {{ __('messages.reviews') }})
                     </a>
                  </div>
               </div>
            </div>
            
            <!-- Bookings Section -->
            <div class="col-4">
               <div class="stats-item">
                  <div class="d-flex align-items-center justify-content-center mb-1">
                     <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns=" " class="stats-icon text-success">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                     </svg>
                     <span class="stats-value"style="font-size: 11px;">{{ $completedBookingCount ?? 0 }}</span>
                  </div>
                  <div class="stats-label" style="font-size: 11px;">{{ __('landingpage.bookings') }}</div>
               </div>
            </div>
            
            <!-- Views Section -->
            <div class="col-4">
               <div class="stats-item">
                  <div class="d-flex align-items-center justify-content-center mb-1">
                     <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns=" " class="stats-icon text-info">
                        <path d="M12 5c-7.633 0-10 7-10 7s2.367 7 10 7 10-7 10-7-2.367-7-10-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Zm0-8a3 3 0 1 0 .002 6.002A3 3 0 0 0 12 9Z" fill="currentColor"/>
                     </svg>
                     <span class="stats-value"style="font-size: 11px;">{{ $data->total_views ?? 0 }}</span>
                  </div>
                  <div class="stats-label" style="font-size: 11px;">{{ __('landingpage.sl_views') }}</div>
               </div>
            </div>
         </div>
      </div>  
      <div class="d-flex social-share social-icons" style="gap: 14px; justify-content: center;">
         @php
            $cityName = optional($data->city)->name ?? optional(optional($data->providers)->city)->name ?? 'City';
            $countryName = optional($data->country)->name ?? optional(optional($data->providers)->country)->name ?? 'Country';
            $locationText = $cityName . ', ' . $countryName;
            $serviceShareUrl = route('service.detail', $data->id) . '?v=' . (optional($data->updated_at)->timestamp ?? time());
            $serviceDescSnippet = $data->description ? \Illuminate\Support\Str::limit(strip_tags($data->description), 100) : '';
            $serviceQuote = Str::limit($data->name, 80) . ' • ' . getPriceFormat($data->price) . ' • ' . ucfirst($data->type ?? '') . ' • ' . $locationText . ($serviceDescSnippet ? ' • ' . $serviceDescSnippet : '');
         @endphp
         <span role="button" tabindex="0" class="social-link share-link"
               data-platform="facebook"
               data-share-url="{{ $serviceShareUrl }}"
               data-quote="{{ $serviceQuote }}"
               onclick="return window.__shareClickHandler(event, this);"
               style="cursor: pointer;">
             <img src="https://static.vecteezy.com/system/resources/previews/016/716/481/original/facebook-icon-free-png.png"
                 style="width: 30px; border-radius: 8px;" alt="Share on Facebook">
         </span>
         <span role="button" tabindex="0" class="social-link share-link"
               data-platform="twitter"
               data-share-url="{{ $serviceShareUrl }}"
               data-text="{{ $serviceQuote }}"
               onclick="return window.__shareClickHandler(event, this);"
               style="cursor: pointer;">
             <img src="https://cdn.pixabay.com/photo/2015/03/10/17/30/twitter-667462_640.png"
                 style="width: 30px; border-radius: 8px;" alt="Share on Twitter">
         </span>
         <span role="button" tabindex="0" class="social-link share-link"
               data-platform="instagram"
               data-image-url="{{ isset($serviceImage) ? $serviceImage : asset('images/default.png') }}"
               data-quote="{{ $serviceQuote }} — {{ $serviceShareUrl }}"
               onclick="return window.__shareClickHandler(event, this);"
               style="cursor: pointer;">
             <img src="https://upload.wikimedia.org/wikipedia/commons/9/95/Instagram_logo_2022.svg"
                 style="width: 30px; border-radius: 8px;" alt="Share on Instagram">
         </span>
         <span role="button" tabindex="0" class="social-link share-link"
               data-platform="linkedin"
               data-share-url="{{ $serviceShareUrl }}"
               onclick="return window.__shareClickHandler(event, this);"
               style="cursor: pointer;">
             <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRokEYt0yyh6uNDKL8uksVLlhZ35laKNQgZ9g&s"
                 style="width: 30px; border-radius: 8px;" alt="Share on LinkedIn">
         </span>
     </div>
     
   
   
  
</div>

@if(!isset($skipScripts) || !$skipScripts)
<script>
   $(document).ready(function () {
    const baseUrl = document.querySelector('meta[name="baseUrl"]')?.getAttribute('content') || '{{ env("APP_URL") }}';

    // Only attach handlers if not already attached (for DataTables usage)
    if (typeof window.favoriteHandlersAttached === 'undefined') {
        window.favoriteHandlersAttached = true;

        $('.save_fav').off('click').on('click', function (e) {
           e.preventDefault();
           const form = $(this).closest('form');
           const serviceId = form.find('.service_id').data('service-id');
           const userId = form.find('#user_id').val() || $('input[name="user_id"]').first().val();

           if (!userId) {
               console.error('User ID not found');
               return;
           }

           $.ajax({
                url: '{{ route("save-favourite") }}',
                type: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                data: {
                    _token: '{{ csrf_token() }}',
                    service_id: serviceId,
                    user_id: userId,
                },
                success: function (response) {
                   if (typeof Swal !== 'undefined') {
                       Swal.fire({
                          title: 'Done',
                          text: response.message || 'Favorite saved successfully',
                          icon: 'success',
                          iconColor: '#3333ff'
                       }).then((result) => {
                          if ($('#datatable').length && typeof $('#datatable').DataTable !== 'undefined') {
                             $('#datatable').DataTable().ajax.reload(null, false);
                          } else {
                             window.location.reload();
                          }
                       });
                   } else {
                       alert(response.message || 'Favorite saved successfully');
                       if ($('#datatable').length && typeof $('#datatable').DataTable !== 'undefined') {
                           $('#datatable').DataTable().ajax.reload(null, false);
                       } else {
                           window.location.reload();
                       }
                   }
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error, xhr);
                    if (typeof Swal !== 'undefined') {
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            Swal.fire({
                                title: 'Error',
                                text: xhr.responseJSON.message,
                                icon: 'error'
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'Failed to save favorite. Please try again.',
                                icon: 'error'
                            });
                        }
                    } else {
                        alert('Failed to save favorite. Please try again.');
                    }
                }
            });
        });

        $('.delete_fav').off('click').on('click', function (e) {
           e.preventDefault();
           const form = $(this).closest('form');
           const serviceId = form.find('.service_id').data('service-id');
           const userId = form.find('#user_id').val() || $('input[name="user_id"]').first().val();

           if (!userId) {
               console.error('User ID not found');
               return;
           }

           $.ajax({
                url: '{{ route("delete-favourite") }}',
                type: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                data: {
                    _token: '{{ csrf_token() }}',
                    service_id: serviceId,
                    user_id: userId,
                },
                success: function (response) {
                   if (typeof Swal !== 'undefined') {
                       Swal.fire({
                          title: 'Done',
                          text: response.message || 'Favorite removed successfully',
                          icon: 'success',
                          iconColor: '#3333ff'
                       }).then((result) => {
                          if ($('#datatable').length && typeof $('#datatable').DataTable !== 'undefined') {
                             $('#datatable').DataTable().ajax.reload(null, false);
                          } else {
                             window.location.reload();
                          }
                       });
                   } else {
                       alert(response.message || 'Favorite removed successfully');
                       if ($('#datatable').length && typeof $('#datatable').DataTable !== 'undefined') {
                           $('#datatable').DataTable().ajax.reload(null, false);
                       } else {
                           window.location.reload();
                       }
                   }
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error, xhr);
                    if (typeof Swal !== 'undefined') {
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            Swal.fire({
                                title: 'Error',
                                text: xhr.responseJSON.message,
                                icon: 'error'
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'Failed to delete favorite. Please try again.',
                                icon: 'error'
                            });
                        }
                    } else {
                        alert('Failed to delete favorite. Please try again.');
                    }
                }
            });
        });
    }

    $('.service-heading, .service-img').on('click', function (e) {
        e.preventDefault();
        var serviceId = $(this).closest('.service-box-card').data('service-id');
        const baseUrl = document.querySelector('meta[name="baseUrl"]')?.getAttribute('content') || '{{ env("APP_URL") }}';

        // Local Storage
        var storedServiceIds = JSON.parse(localStorage.getItem('recentlyViewed')) || [];
        if (!storedServiceIds.includes(serviceId)) {
            storedServiceIds.unshift(serviceId);
            storedServiceIds = storedServiceIds.slice(0, 10);
            localStorage.setItem('recentlyViewed', JSON.stringify(storedServiceIds));
        }

        // Laravel Session
        $.ajax({
            url: baseUrl + '/save-recently-viewed/' + serviceId,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
            },
            success: function (response) {
                return response;
            },
            error: function (error) {
                console.error('Error storing recently viewed service:', error);
            }
        });

        window.location.href = $(this).attr('href');
    });

    // Initialize tooltips
    if (window.bootstrap && bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Share handler
    if (!window.__shareClickHandler) {
        window.__shareClickHandler = function(e, el) {
            try { e.preventDefault(); e.stopPropagation(); } catch (_) {}

            function openPopup(url) {
                window.open(url, '_blank', 'noopener,noreferrer,width=600,height=600');
            }

            var platform = el.getAttribute('data-platform');
            var shareUrl = el.getAttribute('data-share-url');

            if (platform === 'facebook') {
                var fbUrl = encodeURIComponent(shareUrl || window.location.href);
                var quote = encodeURIComponent(el.getAttribute('data-quote') || '');
                openPopup('https://www.facebook.com/sharer/sharer.php?u=' + fbUrl + (quote ? '&quote=' + quote : ''));
            } else if (platform === 'twitter') {
                var text = encodeURIComponent(el.getAttribute('data-text') || '');
                var url = encodeURIComponent(shareUrl || window.location.href);
                openPopup('https://twitter.com/intent/tweet?url=' + url + '&text=' + text);
            } else if (platform === 'linkedin') {
                var liUrl = encodeURIComponent(shareUrl || window.location.href);
                openPopup('https://www.linkedin.com/sharing/share-offsite/?url=' + liUrl);
            } else if (platform === 'instagram') {
                var quoteText = el.getAttribute('data-quote') || '';
                if (navigator.share) {
                    try {
                        navigator.share({ text: quoteText, url: shareUrl || window.location.href })
                            .catch(function() {});
                    } catch (_) {
                        openPopup('https://www.instagram.com/');
                    }
                } else {
                    openPopup('https://www.instagram.com/');
                }
            }

            return false;
        };
    }
   });
</script>
@endif