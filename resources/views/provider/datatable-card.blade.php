<div class="iq-team text-center mb-5">
  <div class="iq-provider-img position-relative">
      <a href="{{ route('provider.detail', $data->id) }}"
          class="position-absolute w-100 h-100 start-0 top-0 d-block"></a>
      <img src="{{ getSingleMedia($data, 'profile_image', null) }}" alt="provider"
          class="provider-img img-fluid object-cover rounded-3 w-100" loading="lazy" />

  </div>
  <div class="d-flex align-items-center justify-content-around mt-3 gap-2">
     @php
        // Use centralized helper to determine verification based on required & verified provider documents
        $allVerified = function_exists('verify_provider_document') ? verify_provider_document($data->id) : false;
     @endphp
      <!-- First icon -->
        @if ($allVerified)
          <img src="{{ asset('images/icon/verified.jpg') }}" alt="verified icon"
               style="width: 14%; height: 23%; margin-right: 10px;">
      @else
          <img src="{{ asset('images/icon/notverifiedpng.png') }}" alt="not verified icon"
               style="width: 14%; height: 23%; margin-right: 10px;">
      @endif

      <!-- Display name with ellipsis for overflow -->
      <a href="{{ route('provider.detail', $data->id) }}"
        style="flex-grow: 1; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
         <h5 class="provider-heading line-count-1" style="margin: 0; font-size: 16px; line-height: 1.2;">
             {{ $data->display_name }}
         </h5>
     </a>
     

      <!-- Verified icon (if applicable) -->
      {{-- Removed duplicate per-document checkmark; top-left badge handles verification status --}}

      <!-- Second icon -->
      <img src="{{ $plan_icon }}" alt="icon" style="width: 14%; height: 23%;">
  </div>


  <div>

      @php $rating = round($providers_service_rating, 1); @endphp

      @foreach (range(1, 5) as $i)
          <span class="fa-stack" style="width:1em">
              <i class="far fa-star fa-stack-1x"></i>
              @if ($rating > 0)
                  @if ($rating > 0.5)
                      <i class="fas fa-star fa-stack-1x"></i>
                  @else
                      <i class="fas fa-star-half fa-stack-1x"></i>
                  @endif
              @endif
              @php $rating--; @endphp
          </span>
      @endforeach
      ({{ round($providers_service_rating, 1) }})
  </div>
  <h6 class="text-primary text-capitalize mt-2 line-count-1">{{ $data->designation }}</h6>
  <p class="mt-0 mb-0" style="font-size: 12;  ">
      <div>
        {{ $data->city->name ?? '' }} - {{ $data->country->name ?? '' }}


      </p>
  
</div>
<div class="d-flex align-items-center justify-content-center gap-3 mt-1">
    <!-- Facebook Link -->
    <a href="https://www.facebook.com" target="_blank">
        <img src="https://static.vecteezy.com/system/resources/previews/016/716/481/original/facebook-icon-free-png.png" 
            style="width: 30px; border-radius: 8px;" alt="Facebook">
    </a>
    <!-- Instagram Link -->
    <a href="https://www.instagram.com" target="_blank">
        <img src="https://upload.wikimedia.org/wikipedia/commons/9/95/Instagram_logo_2022.svg" 
            style="width: 30px; border-radius: 8px;" alt="Instagram">
    </a>
    <!-- Twitter Link -->
    <a href="https://www.twitter.com" target="_blank">
        <img src="https://cdn.pixabay.com/photo/2015/03/10/17/30/twitter-667462_640.png" 
            style="width: 30px; border-radius: 8px;" alt="Twitter">
    </a>
    <!-- LinkedIn Link -->
    <a href="https://www.linkedin.com" target="_blank">
        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRokEYt0yyh6uNDKL8uksVLlhZ35laKNQgZ9g&s"
            style="width: 30px; border-radius: 8px;" alt="LinkedIn">
    </a>
</div>


</div>
