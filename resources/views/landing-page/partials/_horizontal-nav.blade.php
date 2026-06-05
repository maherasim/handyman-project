<!-- Horizontal Menu Start -->
<nav id="navbar_main" class="mobile-offcanvas nav navbar navbar-expand-xl hover-nav horizontal-nav py-xl-0">
    <div class="container-fluid p-lg-0">
        <div class="offcanvas-header px-0">
            <div class="navbar-brand ms-3">
                @include('landing-page.components.widgets.logo')
            </div>
            <button class="btn-close float-end px-3"></button>
        </div>
        @php
                $headerSection = App\Models\FrontendSetting::where('key', 'heder-menu-setting')->first();
                $sectionData = $headerSection ? json_decode($headerSection->value, true) : null;
                $settings = App\Models\Setting::whereIn('type', ['service-configurations','OTHER_SETTING'])
                ->whereIn('key', ['service-configurations', 'OTHER_SETTING'])
                ->get()
                ->keyBy('type');

                $serviceconfig = $settings->has('service-configurations') ? json_decode($settings['service-configurations']->value) : null;
                $othersetting = $settings->has('OTHER_SETTING') ? json_decode($settings['OTHER_SETTING']->value) : null;
                $navPages = \App\Models\Page::active()->ordered()->get();
        @endphp
         @if ($sectionData && isset($sectionData['header_setting']) && $sectionData['header_setting'] == 1)
        <ul class="navbar-nav iq-nav-menu list-unstyled" id="header-menu">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('frontend.index') ? 'active' : '' }}" href="{{ route('frontend.index') }}">{{__('landingpage.home')}}</a>
            </li>
            @if(config('app.show_language_switcher', true) && !empty($sectionData['enable_language']))
                @php
                    $language_option = sitesetupSession('get')->language_option ?? ['en', 'de', 'fr'];
                    if (is_array($language_option)) {
                        $language_option = array_values(array_unique(array_merge($language_option, ['en', 'de', 'fr'])));
                    }
                    $language_option = array_values(array_filter($language_option, function ($locale) {
                        return is_dir(resource_path('lang/' . $locale));
                    }));
                    $language_array = !empty($language_option) ? languagesArray($language_option) : [];
                @endphp
                @if(count($language_array) > 0)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="landingLanguageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ strtoupper(app()->getLocale()) }}
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="landingLanguageDropdown">
                            @foreach($language_array as $lang)
                                <li>
                                    <a class="dropdown-item {{ app()->getLocale() == $lang['id'] ? 'active' : '' }}" href="{{ route('switch-language', ['locale' => $lang['id']]) }}">
                                        {{ strtoupper($lang['id']) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif
            @endif
            @if( isset($sectionData['categories']) && $sectionData['categories'] == 1)
            {{-- @if(isset($sectionData['categories']) && $sectionData['categories'] == 1) --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('category.*') ? 'active' : '' }}" href="{{ route('category.list') }}">{{__('landingpage.categories')}}</a>
            </li>
            @endif
            @if(  isset($sectionData['service']) && $sectionData['service'] == 1)
            {{-- @if(isset($sectionData['service']) && $sectionData['service'] == 1)p --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('service.*') ? 'active' : '' }}" href="{{ route('service.list') }}">{{__('landingpage.services')}}</a>
            </li>
            @endif
            @if(optional($othersetting)->blog  == 1)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}" href="{{ route('blog.list') }}">{{__('landingpage.blogs')}}</a>
            </li>
            @endif
            @if($sectionData['provider'] == 1)
            {{-- @if(isset($sectionData['provider']) && $sectionData['provider'] == 1) --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('frontend.provider') || request()->routeIs('frontend.provider.*') ? 'active' : '' }}" href="{{ route('frontend.provider') }}">{{__('landingpage.providers')}}</a>
            </li>
            @endif
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('job.data') || request()->routeIs('job.data.*') ? 'active' : '' }}" href="{{ route('job.data') }}">{{__('landingpage.jobs')}}</a>
            </li>
            @if(auth()->check() && auth()->user()->user_type == 'user' && $sectionData['bookings'] == 1)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('booking.*') ? 'active' : '' }}" href="{{ route('booking.list') }}">{{__('landingpage.bookings')}}</a>
                </li>
            @endif
            {{-- Pages dropdown – commented out per request
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle {{ request()->routeIs('user.page') ? 'active' : '' }}" href="#" id="navbarPagesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Pages</a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarPagesDropdown">
                    @foreach($navPages as $p)
                        <li><a class="dropdown-item {{ request()->routeIs('user.page') && request()->route('slug') === $p->slug ? 'active' : '' }}" href="{{ route('user.page', $p->slug) }}">{{ $p->title }}</a></li>
                    @endforeach
                    @if($navPages->isNotEmpty())<li><hr class="dropdown-divider"></li>@endif
                    <li><a class="dropdown-item" href="{{ route('user.term_conditions') }}">{{__('landingpage.terms_conditions')}}</a></li>
                    <li><a class="dropdown-item" href="{{ route('user.privacy_policy') }}">{{__('landingpage.privacy_policy')}}</a></li>
                    <li><a class="dropdown-item" href="{{ route('user.help_support') }}">{{__('landingpage.help_support')}}</a></li>
                    <li><a class="dropdown-item" href="{{ route('user.refund_policy') }}">{{__('landingpage.refund_policy')}}</a></li>
                </ul>
            </li>
            --}}
            {{-- @if(auth()->check() && auth()->user()->user_type == 'user' && optional($serviceconfig)->post_services == 1)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('post.job.*') ? 'active' : '' }}" href="{{ route('post.job.list') }}">{{__('landingpage.job_request')}}</a>
            </li>
            @endif --}}
        </ul>
    @endif
    </div>
    <!-- container-fluid.// -->
</nav>
<!-- Sidebar Menu End -->
