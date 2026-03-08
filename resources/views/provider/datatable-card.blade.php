<div class="iq-team text-center mb-5">
  <div class="iq-provider-img position-relative">
      <a href="{{ route('provider.detail', $data->id) }}"
          class="position-absolute w-100 h-100 start-0 top-0 d-block"></a>
      <img src="{{ getSingleMedia($data, 'profile_image', null) }}" alt="{{ __('landingpage.pd_alt_provider') }}"
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
          <img src="{{ asset('images/icon/notverifiedpng.png') }}" alt="{{ __('landingpage.pd_alt_not_verified_icon') }}"
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
      <img src="{{ $plan_icon }}" alt="{{ __('landingpage.pl_plan_icon') }}" style="width: 14%; height: 23%;">
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
              <span class="reviews-count text-muted" style="font-size: 0.85em;">{{ $reviewsCount }} {{ $reviewsCount == 1 ? __('messages.review') : __('messages.reviews') }}</span>
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
    $providerShareUrl = route('provider.detail', $data->id) . '?v=' . time();
    $location = trim(implode(', ', array_filter([$data->city->name ?? null, $data->country->name ?? null])));
    $skillsRaw = $data->skills ?? null;
    $skillsStr = '';
    if ($skillsRaw !== null && $skillsRaw !== '') {
        if (is_string($skillsRaw)) {
            $decoded = json_decode($skillsRaw, true);
            $skillsStr = is_array($decoded) ? implode(', ', array_slice($decoded, 0, 5)) : \Illuminate\Support\Str::limit($skillsRaw, 80);
        } elseif (is_array($skillsRaw)) {
            $skillsStr = implode(', ', array_slice($skillsRaw, 0, 5));
        }
    }
    $whyChoose = $data->why_choose_me ?? null;
    $benefitStr = '';
    if (is_string($whyChoose)) {
        $whyChoose = json_decode($whyChoose, true);
    }
    if (is_array($whyChoose)) {
        $title = $whyChoose['why_choose_me_title'] ?? '';
        $reasons = $whyChoose['why_choose_me_reason'] ?? [];
        $firstReason = is_array($reasons) ? (reset($reasons) ?: '') : (string) $reasons;
        $benefitStr = trim($title . ($firstReason ? ' — ' . \Illuminate\Support\Str::limit($firstReason, 60) : ''));
    }
    $careerLevel = $data->career_level ?? null;
    $careerStr = $careerLevel ? (is_string($careerLevel) ? ucwords(str_replace('_', ' ', $careerLevel)) : '') : '';
    $experienceRaw = $data->experience ?? null;
    $experienceStr = '';
    if ($experienceRaw !== null && $experienceRaw !== '') {
        if (is_string($experienceRaw)) {
            $decoded = json_decode($experienceRaw, true);
            $experienceStr = is_array($decoded) ? implode(', ', array_slice($decoded, 0, 3)) : \Illuminate\Support\Str::limit($experienceRaw, 60);
        } elseif (is_array($experienceRaw)) {
            $experienceStr = implode(', ', array_slice($experienceRaw, 0, 3));
        }
    }
    $aboutMe = isset($data->about_me) && $data->about_me !== '' ? \Illuminate\Support\Str::limit(strip_tags($data->about_me), 250) : '';
    $parts = array_filter([
        $data->display_name,
        $data->designation ? $data->designation : null,
        $location ? 'Location: ' . $location : null,
        $skillsStr ? 'Skills: ' . $skillsStr : null,
        $aboutMe ? 'About: ' . $aboutMe : null,
        'View full profile on ' . config('app.name')
    ]);
    $providerQuote = implode(' | ', $parts);
    $providerImageUrl = getSingleMedia($data, 'profile_image', null);
    $providerImageUrl = $providerImageUrl ? (str_starts_with($providerImageUrl, 'http') ? $providerImageUrl : url($providerImageUrl)) : url('images/post-job/ac_refresh_and_revive.png');
@endphp
<div class="d-flex align-items-center justify-content-center gap-3 mt-1 social-icons">
    <span role="button" tabindex="0" class="social-link share-link" data-platform="facebook" data-share-url="{{ $providerShareUrl }}" data-quote="{{ $providerQuote }}" style="cursor: pointer;" title="Facebook">
        <img src="{{ asset('assets/fb.png') }}" style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt="{{ __('landingpage.pd_alt_facebook') }}">
    </span>
    <span role="button" tabindex="0" class="social-link share-link" data-platform="telegram" data-share-url="{{ $providerShareUrl }}" data-quote="{{ $providerQuote }}" style="cursor: pointer;" title="Telegram">
        <img src="{{ asset('assets/telegram.png') }}" style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt="{{ __('landingpage.pd_alt_telegram') }}">
    </span>
    <span role="button" tabindex="0" class="social-link share-link" data-platform="twitter" data-share-url="{{ $providerShareUrl }}" data-text="{{ $providerQuote }}" style="cursor: pointer;" title="Twitter">
        <img src="{{ asset('assets/twiter.png') }}" style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt="{{ __('landingpage.pd_alt_twitter') }}">
    </span>
    <span role="button" tabindex="0" class="social-link share-link" data-platform="linkedin" data-share-url="{{ $providerShareUrl }}" style="cursor: pointer;" title="LinkedIn">
        <img src="{{ asset('assets/linkedIn.jpg') }}" style="width: 28px; height: 28px; object-fit: contain; border-radius: 8px;" alt="{{ __('landingpage.pd_alt_linkedin') }}">
    </span>
</div>


</div>
