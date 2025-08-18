
<style>
   .price-box {
      background-color: #007bff; /* Blue background */
      color: white; /* White text for better visibility */
      font-size: 18px; /* Increased text size */
      font-weight: bold;
      color: red; /* Red text for price */
      text-align: center;
      padding: 10px 15px; /* Added consistent padding */
      border-radius: 10px; /* Rounded corners */
      display: inline-block;
      radius: 15%;
      width: 180px; /* Increased width */
      margin: 5px 0; /* Optional: Adds spacing around the box */
   }
   .service-asim {
            height: 10.5rem !important;
            object-fit: cover;
        }
        .provider-info {
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.provider-name {
    display: inline-block;
    line-height: 1.2;
    word-break: break-word;
}

.provider-name span {
    display: block;
}

/* Card polish */
.service-box-card { border: 1px solid #eef0f2; transition: box-shadow .2s ease, transform .2s ease; background: #fff; }
.service-box-card:hover { box-shadow: 0 10px 24px rgba(18,38,63,.08); transform: translateY(-2px); }
.social-share img, .social-share svg { width: 28px; height: 28px; border-radius: 6px; }

</style>
<div class="service-box-card bg-white rounded-3 mb-0 shadow-sm h-100" data-service-id="{{ $data->id }}">
   <div class="iq-image position-relative">
      @if($data->visit_type == 'ONLINE')
         <span class="online-service"></span>
      @endif
      <a href="{{ route('service.detail', $data->id) }}" class="service-img">
         <img src="{{ getSingleMedia($data,'service_attachment', null) }}" alt="service"
         class="service-asim w-100 object-cover img-fluid rounded-3"> 
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
         <form method="GET" id="favoriteForm" action="{{ route('user.login') }}">
            @csrf
            <button type="submit" class="btn-link serv-whishlist text-primary">
               <svg width="12" height="13" viewBox="0 0 12 13" fill="none" xmlns=" ">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M1.43593 6.29916C0.899433 4.62416 1.52643 2.70966 3.28493 2.14316C4.20993 1.84466 5.23093 2.02066 5.99993 2.59916C6.72743 2.03666 7.78593 1.84666 8.70993 2.14316C10.4684 2.70966 11.0994 4.62416 10.5634 6.29916C9.72843 8.95416 5.99993 10.9992 5.99993 10.9992C5.99993 10.9992 2.29893 8.98516 1.43593 6.29916Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M8 3.84998C8.535 4.02298 8.913 4.50048 8.9585 5.06098" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
               </svg>
            </button>
         </form>
      @endif

   </div> 
   <ul>
      <div class="service d-flex justify-content-center "
                                                style="position:relative; z-index:1111; margin:auto; background-image: url('{{ asset('images/icon/banner2.jpg') }}'); background-size: cover; width:85% ; margin-top:-32px;  background-repeat: no-repeat; background-position: center; padding: 10px 20px; color: white; font-weight: 600; font-size: 18px; border-radius: 10px; border: 3px solid #E1DCDD;">

   @if($data->price==0)
   <li class="text-primary fw-500 d-inline-block position-relative font-size-18">Free</li>
   @else
   <li class="text-white fw-500 d-inline-block position-relative font-size-18" 
   @if(isset($col) && $col) style="font-size:16px !important" @endif>
   {{ getPriceFormat($data->price) }} / {{ $data->type }}
</li>



</div>

</ul>
   @endif 
   <a href="{{ route('service.detail', $data->id) }}"
      class="service-heading mt-2 d-block p-0 text-decoration-none"
      data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $data->name }}" aria-label="{{ $data->name }}">
      <h5 class="service-heading text-capitalize text-truncate" style="font-size:15px">
        <b>{{ Str::limit($data->name, 48) }}</b>
      </h5>
  </a>
  
  <h5  class="mt-0 mb-0 text-truncate" style="font-size: 12;">
   <span style="font-size: 12px;"> {{ $data->city ? $data->city->name : 'City' }}-{{ $data->country ? $data->country->name : 'Country' }}</span>
  
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
       <img src="{{ asset('images/icon/freeicon.jpg') }}" alt="icon"
           style="width: 26px; height: 26px; margin-right: 10px;">
       <img src="{{ asset('images/icon/verifiedpng.png') }}" alt="icon"
           style="width: 26px; height: 26px;">
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

      
         
        
      <div class="d-flex align-items-center gap-1 f-none">
         <svg xmlns=" " width="12" height="12" viewBox="0 0 12 12" fill="none"
            class="service-rating">
            <path
               d="M6.58578 0.85525L7.92167 3.44562C8.02009 3.63329 8.20793 3.76362 8.42458 3.79259L11.4252 4.21427C11.6005 4.23802 11.7595 4.32723 11.8669 4.46335C11.9731 4.59773 12.0187 4.76803 11.9929 4.93543C11.9719 5.07445 11.9041 5.20304 11.8003 5.30151L9.62603 7.33523C9.467 7.47714 9.39498 7.68741 9.43339 7.89304L9.96871 10.7522C10.0257 11.0974 9.78867 11.4229 9.43339 11.4884C9.28696 11.511 9.13693 11.4872 9.0049 11.4224L6.32833 10.0768C6.12968 9.98005 5.89503 9.98005 5.69639 10.0768L3.01982 11.4224C2.69094 11.5909 2.28346 11.4762 2.10042 11.1634C2.0326 11.0389 2.0086 10.897 2.0308 10.7585L2.56612 7.89883C2.60453 7.69378 2.53191 7.48236 2.37348 7.34044L0.19921 5.30788C-0.0594455 5.06692 -0.0672472 4.67014 0.181806 4.42048C0.187207 4.41527 0.193209 4.40948 0.19921 4.40369C0.302432 4.30232 0.438061 4.23802 0.584493 4.22123L3.58514 3.79896C3.80118 3.76942 3.98902 3.64025 4.08805 3.45141L5.37592 0.85525C5.49055 0.632821 5.7282 0.494383 5.98625 0.500175H6.06667C6.29052 0.526241 6.48556 0.660046 6.58578 0.85525Z"
               fill="currentColor" />
         </svg>
         <h6 class="font-size-14">{{ round($totalRating, 1) }}
              <a href="{{ route('rating.all', ['service_id' => $data->id]) }}" class="text-body ms-1">({{$totalReviews }} {{__('messages.reviews')}})</a>
         </h6>

         <strong class="px-3">{{ $completedBookingCount }} Bookings</strong>

         <span class="px-2" title="Views">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns=" "><path d="M12 5c-7.633 0-10 7-10 7s2.367 7 10 7 10-7 10-7-2.367-7-10-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Zm0-8a3 3 0 1 0 .002 6.002A3 3 0 0 0 12 9Z" fill="currentColor"/></svg>
            <span class="ms-1">{{ $data->total_views ?? 0 }}</span>
         </span>
      </div>  
      <div class="d-flex social-share" style="gap: 14px; justify-content: center;">
         <a href="#" class="social-share-btn" data-platform="facebook" data-service-id="{{ $data->id }}" style="cursor: pointer;">
             <img src="https://static.vecteezy.com/system/resources/previews/016/716/481/original/facebook-icon-free-png.png"
                 style="width: 30px; border-radius: 8px;" alt="Share on Facebook">
         </a>
         <a href="#" class="social-share-btn" data-platform="twitter" data-service-id="{{ $data->id }}" style="cursor: pointer;">
             <img src="https://cdn.pixabay.com/photo/2015/03/10/17/30/twitter-667462_640.png"
                 style="width: 30px; border-radius: 8px;" alt="Share on Twitter">
         </a>
         <a href="#" class="social-share-btn" data-platform="instagram" data-service-id="{{ $data->id }}" style="cursor: pointer;">
             <img src="https://upload.wikimedia.org/wikipedia/commons/9/95/Instagram_logo_2022.svg"
                 style="width: 30px; border-radius: 8px;" alt="Share on Instagram">
         </a>
         <a href="#" class="social-share-btn" data-platform="linkedin" data-service-id="{{ $data->id }}" style="cursor: pointer;">
             <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRokEYt0yyh6uNDKL8uksVLlhZ35laKNQgZ9g&s"
                 style="width: 30px; border-radius: 8px;" alt="Share on LinkedIn">
         </a>
     </div>
     
   
   
  
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>

<!-- Social Media Sharing Script -->
<script>
$(document).ready(function () {
    // Social Media Sharing Functionality
    $('.social-share-btn').on('click', function(e) {
        e.preventDefault();
        
        const platform = $(this).data('platform');
        const serviceId = $(this).data('service-id');
        const serviceCard = $(this).closest('.service-box-card');
        
        // Get service information from the card
        const serviceName = serviceCard.find('.service-heading').text().trim() || 'Amazing Service';
        const servicePrice = serviceCard.find('.price-box').text().trim() || 'Check Price';
        const serviceImage = serviceCard.find('.service-img img').attr('src');
        const serviceUrl = window.location.origin + '/service-detail/' + serviceId;
        const providerName = serviceCard.find('.provider-name').text().trim() || 'Professional Provider';
        const rating = serviceCard.find('.font-size-14').text().trim() || '5.0';
        const bookings = serviceCard.find('strong').text().trim() || '0 Bookings';
        const views = serviceCard.find('span[title="Views"] span').text().trim() || '0';
        
        // Enhanced shareable content with more details
        const shareContent = {
            title: serviceName,
            description: `🚀 **${serviceName}**\n\n👨‍💼 **Provider:** ${providerName}\n💰 **Price:** ${servicePrice}\n⭐ **Rating:** ${rating}\n📊 **Stats:** ${bookings} • ${views} Views\n\n🔍 **Service Details:**\nThis professional service offers top-quality solutions with excellent customer satisfaction.\n\n📱 **Book Now:** ${serviceUrl}\n\n#services #professional #quality #recommended #handyman #expert`,
            url: serviceUrl,
            image: serviceImage,
            hashtags: 'services,professional,quality,recommended,handyman,expert'
        };
        
        // Share based on platform
        switch(platform) {
            case 'facebook':
                shareToFacebook(shareContent);
                break;
            case 'twitter':
                shareToTwitter(shareContent);
                break;
            case 'instagram':
                shareToInstagram(shareContent);
                break;
            case 'linkedin':
                shareToLinkedIn(shareContent);
                break;
            // removed whatsapp and copy link
        }
    });
    
    // Facebook Sharing
    function shareToFacebook(content) {
        const fbUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(content.url)}&quote=${encodeURIComponent(content.description)}`;
        openShareWindow(fbUrl, 'facebook-share', 600, 400);
        Swal.fire('Sharing to Facebook!', 'Facebook sharing window opened. Complete your post there.', 'info');
    }
    
    // Twitter Sharing
    function shareToTwitter(content) {
        const tweetText = `${content.description}\n\n${content.url}`;
        const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(tweetText)}&hashtags=${content.hashtags}`;
        openShareWindow(twitterUrl, 'twitter-share', 600, 400);
        Swal.fire('Sharing to Twitter!', 'Twitter sharing window opened. Complete your tweet there.', 'info');
    }
    
    // Instagram Sharing (Note: Instagram doesn't support direct sharing via URL)
    function shareToInstagram(content) {
        // Create a modal with Instagram sharing instructions
        Swal.fire({
            title: 'Share to Instagram',
            html: `
                <div class="text-center">
                    <p><strong>Instagram doesn't support direct sharing via links.</strong></p>
                    <p>Here's what you can copy and paste:</p>
                    <div class="bg-light p-3 rounded mb-3">
                        <strong>Caption:</strong><br>
                        <textarea class="form-control mt-2" rows="4" readonly>${content.description}</textarea>
                    </div>
                    <div class="bg-light p-3 rounded">
                        <strong>Service URL:</strong><br>
                        <input type="text" class="form-control mt-2" value="${content.url}" readonly>
                    </div>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'Copy & Close',
            showCancelButton: true,
            cancelButtonText: 'Close'
        }).then((result) => {
            if (result.isConfirmed) {
                // Copy caption to clipboard
                navigator.clipboard.writeText(content.description + '\n\n' + content.url);
                Swal.fire('Copied!', 'Content copied to clipboard. You can now paste it on Instagram.', 'success');
            }
        });
    }
    
    // LinkedIn Sharing
    function shareToLinkedIn(content) {
        const linkedinUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(content.url)}&title=${encodeURIComponent(content.title)}&summary=${encodeURIComponent(content.description)}`;
        openShareWindow(linkedinUrl, 'linkedin-share', 600, 500);
        Swal.fire('Sharing to LinkedIn!', 'LinkedIn sharing window opened. Complete your post there.', 'info');
    }
    
    // removed WhatsApp and copy link functionality
    
    // Helper function to open share windows
    function openShareWindow(url, name, width, height) {
        const left = (screen.width - width) / 2;
        const top = (screen.height - height) / 2;
        
        window.open(url, name, 
            `width=${width},height=${height},left=${left},top=${top},` +
            'toolbar=0,location=0,menubar=0,directories=0,scrollbars=0'
        );
    }
    
    // Enhanced sharing with clipboard fallback
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                Swal.fire('Copied!', 'Content copied to clipboard!', 'success');
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                Swal.fire('Copied!', 'Content copied to clipboard!', 'success');
            } catch (err) {
                Swal.fire('Error', 'Could not copy to clipboard', 'error');
            }
            
            document.body.removeChild(textArea);
        }
    }
});

</script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.bootstrap && bootstrap.Tooltip) {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl)
      })
    }
  });
</script>
<script>
   $(document).ready(function () {
   
    const baseUrl = document.querySelector('meta[name="baseUrl"]').getAttribute('content');

    $('.save_fav').off('click').on('click', function () {

       const form = $(this).closest('form');

       const serviceId = form.find('.service_id').data('service-id');
       const userId = $('#user_id').val();

       $.ajax({
            url: baseUrl + '/api/save-favourite',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                service_id: serviceId,
                user_id: userId,
            },
            success: function (response) {
               Swal.fire({
               title: 'Done',
               text: response.message,
               icon: 'success',
               iconColor: '#5F60B9'
               }).then((result) => {
                  if (result.isConfirmed) {
                     $('#datatable').DataTable().ajax.reload();
                  }
               })
            },
            error: function (error) {
                console.error('Error:', error);
            }
        });
    });

    $('.delete_fav').off('click').on('click', function () {
       const form = $(this).closest('form');

       const serviceId = form.find('.service_id').data('service-id');
       const userId = $('#user_id').val();

       $.ajax({
            url: baseUrl + '/api/delete-favourite',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                service_id: serviceId,
                user_id: userId,
            },
            success: function (response) {
               Swal.fire({
               title: 'Done',
               text: response.message,
               icon: 'success',
               iconColor: '#5F60B9'
               }).then((result) => {
                  if (result.isConfirmed) {
                     $('#datatable').DataTable().ajax.reload();
                  }
               })
            },
            error: function (error) {
                console.error('Error', error);
            }
        });
    });

    $('.service-heading, .service-img').on('click', function (e) {
    e.preventDefault();
    var serviceId = $(this).closest('.service-box-card').data('service-id');

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
});
</script>