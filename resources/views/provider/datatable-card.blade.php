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
          <img src="{{ asset('images/icon/verifiedpng.png') }}" alt="verified icon"
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
      @php 
          $rating = round($providerRating ?? 0, 1);
          $reviewsCount = $totalReviews ?? 0;
          $displayRating = $rating; // Use separate variable for display
      @endphp

      <a href="javascript:void(0);" class="provider-rating-link text-decoration-none" 
         data-provider-id="{{ $data->id }}" 
         data-rating="{{ $rating }}" 
         data-reviews-count="{{ $reviewsCount }}"
         style="color: inherit; cursor: pointer;">
          @php $starRating = $displayRating; @endphp
          @foreach (range(1, 5) as $i)
              <span class="fa-stack" style="width:1em">
                  <i class="far fa-star fa-stack-1x"></i>
                  @if ($starRating > 0)
                      @if ($starRating >= 1)
                          <i class="fas fa-star fa-stack-1x"></i>
                      @elseif ($starRating > 0.5)
                          <i class="fas fa-star-half fa-stack-1x"></i>
                      @endif
                  @endif
                  @php $starRating--; @endphp
              </span>
          @endforeach
          <span class="rating-value">({{ round($providerRating ?? 0, 1) }})</span>
          @if ($reviewsCount > 0)
              <span class="reviews-count text-muted" style="font-size: 0.85em;">{{ $reviewsCount }} {{ $reviewsCount == 1 ? 'review' : 'reviews' }}</span>
          @endif
      </a>
  </div>
  <h6 class="text-primary text-capitalize mt-2 line-count-1">{{ $data->designation }}</h6>
  <p class="mt-0 mb-0" style="font-size: 12;  ">
      <div>
        {{ $data->city->name ?? '' }} - {{ $data->country->name ?? '' }}


      </p>
  
</div>
@php
    $providerShareUrl = route('provider.detail', $data->id);
    $providerQuote = $data->display_name . ' • ' . ($data->designation ?? '') . ' • ' . ($data->city->name ?? '') . ', ' . ($data->country->name ?? '');
    $providerImageUrl = getSingleMedia($data, 'profile_image', null);
    $providerImageUrl = $providerImageUrl ? (str_starts_with($providerImageUrl, 'http') ? $providerImageUrl : asset($providerImageUrl)) : asset('images/post-job/ac_refresh_and_revive.png');
@endphp
<div class="d-flex align-items-center justify-content-center gap-3 mt-1 social-icons">
    <span role="button" tabindex="0" class="social-link share-link" data-platform="facebook" data-share-url="{{ $providerShareUrl }}" data-quote="{{ $providerQuote }}" onclick="return typeof window.__shareClickHandler === 'function' && window.__shareClickHandler(event, this);" style="cursor: pointer;">
        <img src="https://static.vecteezy.com/system/resources/previews/016/716/481/original/facebook-icon-free-png.png" style="width: 30px; border-radius: 8px;" alt="Facebook">
    </span>
    <span role="button" tabindex="0" class="social-link share-link" data-platform="instagram" data-share-url="{{ $providerShareUrl }}" data-quote="{{ $providerQuote }} — {{ $providerShareUrl }}" data-image-url="{{ $providerImageUrl }}" onclick="return typeof window.__shareClickHandler === 'function' && window.__shareClickHandler(event, this);" style="cursor: pointer;">
        <img src="https://upload.wikimedia.org/wikipedia/commons/9/95/Instagram_logo_2022.svg" style="width: 30px; border-radius: 8px;" alt="Instagram">
    </span>
    <span role="button" tabindex="0" class="social-link share-link" data-platform="twitter" data-share-url="{{ $providerShareUrl }}" data-text="{{ $providerQuote }}" onclick="return typeof window.__shareClickHandler === 'function' && window.__shareClickHandler(event, this);" style="cursor: pointer;">
        <img src="https://cdn.pixabay.com/photo/2015/03/10/17/30/twitter-667462_640.png" style="width: 30px; border-radius: 8px;" alt="Twitter">
    </span>
    <span role="button" tabindex="0" class="social-link share-link" data-platform="linkedin" data-share-url="{{ $providerShareUrl }}" onclick="return typeof window.__shareClickHandler === 'function' && window.__shareClickHandler(event, this);" style="cursor: pointer;">
        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRokEYt0yyh6uNDKL8uksVLlhZ35laKNQgZ9g&s" style="width: 30px; border-radius: 8px;" alt="LinkedIn">
    </span>
</div>


</div>
