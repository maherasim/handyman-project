@php
    $themeSetup = \App\Models\Setting::where('type', 'theme-setup')->where('key', 'theme-setup')->first();
@endphp

<a href="{{route('frontend.index')}}" class="navbar-brand m-0">
    <span class="logo-normal">
      <img src="{{ getSingleMedia($themeSetup,'footer_logo',null) }}" class="img-fluid" alt="logo" loading="lazy">
    </span>
 </a>
