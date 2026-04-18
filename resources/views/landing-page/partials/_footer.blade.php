<footer class="footer-ultra" id="main-footer">

    @php
    $settings = App\Models\Setting::whereIn('type', ['general-setting', 'social-media', 'site-setup'])
        ->whereIn('key', ['general-setting', 'social-media', 'site-setup'])
        ->get()
        ->keyBy('type');
    $generalsetting = $settings->has('general-setting') ? json_decode($settings['general-setting']->value) : null;
    $socialmedia = $settings->has('social-media') ? json_decode($settings['social-media']->value) : null;
    $appsetting = $settings->has('site-setup') ? json_decode($settings['site-setup']->value) : null;
        $copyright_text = $appsetting ? $appsetting->site_copyright : null;
        $position = strpos($copyright_text, 'by');
        if ($position !== false) {
            $first_part = substr($copyright_text, 0, $position + 2);
            $second_part = substr($copyright_text, $position + 2);
        } else {
            $first_part = $copyright_text;
            $second_part = '';
        }
    @endphp

    {{-- ===== ANIMATED BACKGROUND MESH ===== --}}
    <div class="footer-mesh-bg" aria-hidden="true">
        <div class="footer-orb footer-orb-1"></div>
        <div class="footer-orb footer-orb-2"></div>
        <div class="footer-orb footer-orb-3"></div>
        <svg class="footer-grid-svg" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
            <defs>
                <pattern id="footer-grid" x="0" y="0" width="60" height="60" patternUnits="userSpaceOnUse">
                    <path d="M 60 0 L 0 0 0 60" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="1"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#footer-grid)" />
        </svg>
    </div>

    {{-- ===== NEWSLETTER BANNER ===== --}}
    <div class="footer-newsletter-wrap">
        <div class="container">
            <div class="footer-newsletter-card">
                <div class="footer-nl-glow" aria-hidden="true"></div>
                <div class="row align-items-center g-4">
                    <div class="col-lg-6">
                        <div class="footer-nl-icon-row">
                            <div class="footer-nl-icon-badge">
                                <i class="ri-mail-send-line"></i>
                            </div>
                            <span class="footer-nl-tag">{{ __('landingpage.ft_newsletter') }}</span>
                        </div>
                        <h3 class="footer-nl-title">{{ __('landingpage.ft_get_best_deals') }} <span class="footer-nl-accent">{{ __('landingpage.ft_in_your_inbox') }}</span></h3>
                        <p class="footer-nl-desc">{{ __('landingpage.ft_newsletter_desc') }}</p>
                    </div>
                    <div class="col-lg-6">
                        <div class="footer-nl-form-wrap">
                            <div class="footer-nl-input-group">
                                <i class="ri-mail-line footer-nl-input-icon"></i>
                                <input type="email" id="email" class="footer-nl-input" placeholder="{{ __('landingpage.ft_enter_email') }}" autocomplete="email">
                                <button class="footer-nl-btn" id="submit_btn" type="button">
                                    <span>{{ __('landingpage.ft_subscribe') }}</span>
                                    <i class="ri-arrow-right-line"></i>
                                </button>
                            </div>
                            <p class="footer-nl-note"><i class="ri-shield-check-line"></i> {{ __('landingpage.ft_no_spam') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MAIN FOOTER BODY ===== --}}
    <div class="footer-body">
        <div class="container">
            <div class="row g-5">

                {{-- Brand Column --}}
                <div class="col-lg-4 col-md-12">
                    <div class="footer-brand-col">
                        <div class="footer-logo-wrap">
                            @include('landing-page.components.widgets.logo')
                        </div>

                        <p class="footer-brand-desc readmore-text">
                            {{ optional($generalsetting)->site_description }}
                        </p>
                        <a href="javascript:void(0);" class="footer-readmore readmore-btn">
                            {{__('landingpage.read_more')}} <i class="ri-arrow-right-line"></i>
                        </a>

                        {{-- Contact Cards --}}
                        @if(optional($generalsetting)->inquriy_email || optional($generalsetting)->helpline_number)
                        <div class="footer-contact-cards">
                            @if(optional($generalsetting)->inquriy_email)
                            <a href="mailto:{{ optional($generalsetting)->inquriy_email }}" class="footer-contact-card">
                                <div class="footer-contact-icon-wrap">
                                    <i class="ri-mail-line"></i>
                                </div>
                                <div>
                                    <span class="footer-contact-label">{{__('landingpage.business_inquries')}}</span>
                                    <span class="footer-contact-value">{{ optional($generalsetting)->inquriy_email }}</span>
                                </div>
                            </a>
                            @endif
                            @if(optional($generalsetting)->helpline_number)
                            <a href="tel:{{optional($generalsetting)->helpline_number}}" class="footer-contact-card">
                                <div class="footer-contact-icon-wrap">
                                    <i class="ri-phone-line"></i>
                                </div>
                                <div>
                                    <span class="footer-contact-label">{{__('landingpage.helpline_number')}}</span>
                                    <span class="footer-contact-value">{{optional($generalsetting)->helpline_number}}</span>
                                </div>
                            </a>
                            @endif
                        </div>
                        @endif

                        {{-- Social Links --}}
                        @if($socialmedia !== null)
                        <div class="footer-social-wrap">
                            <span class="footer-social-label">{{__('landingpage.follow_us')}}</span>
                            <div class="footer-social-icons">
                                {{-- Same PNG icons + sizes as service list (datatable-card / service cards) --}}
                                @if(optional($socialmedia)->facebook_url)
                                <a href="{{ optional($socialmedia)->facebook_url }}" target="_blank" rel="noopener noreferrer" class="footer-social-btn footer-social-fb" title="Facebook" aria-label="Facebook">
                                    <img src="{{ asset('assets/fb.png') }}?v=20260303" alt="" width="30" height="30" class="footer-social-img footer-social-img-fb">
                                </a>
                                @endif
                                @if(optional($socialmedia)->twitter_url)
                                <a href="{{ optional($socialmedia)->twitter_url }}" target="_blank" rel="noopener noreferrer" class="footer-social-btn footer-social-tw" title="Twitter / X" aria-label="Twitter">
                                    <img src="{{ asset('assets/twiter.png') }}?v=20260303" alt="" width="28" height="28" class="footer-social-img footer-social-img-tw">
                                </a>
                                @endif
                                @if(optional($socialmedia)->telegram_url)
                                <a href="{{ optional($socialmedia)->telegram_url }}" target="_blank" rel="noopener noreferrer" class="footer-social-btn footer-social-tg" title="Telegram" aria-label="Telegram">
                                    <img src="{{ asset('assets/telegram.png') }}?v=20260303" alt="" width="28" height="28" class="footer-social-img footer-social-img-tg">
                                </a>
                                @endif
                                @if(optional($socialmedia)->linkedin_url)
                                <a href="{{ optional($socialmedia)->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="footer-social-btn footer-social-li" title="LinkedIn" aria-label="LinkedIn">
                                    <img src="{{ asset('assets/linkedIn.png') }}?v=20260303" alt="" width="28" height="28" class="footer-social-img footer-social-img-li">
                                </a>
                                @endif
                                @if(optional($socialmedia)->youtube_url)
                                <a href="{{ optional($socialmedia)->youtube_url }}" target="_blank" rel="noopener noreferrer" class="footer-social-btn footer-social-yt" title="YouTube" aria-label="YouTube">
                                    <img src="{{ asset('assets/youtube.png') }}?v=20260418" alt="" width="28" height="28" class="footer-social-img footer-social-img-yt" loading="lazy">
                                </a>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Categories & Services Columns --}}
                @php
                $footerSection = App\Models\FrontendSetting::where('key', 'footer-setting')->first();
                $sectionData = $footerSection ? json_decode($footerSection->value, true) : null;
                @endphp

                @if ($sectionData && isset($sectionData['footer_setting']) && $sectionData['footer_setting'] == 1)

                    @if ($sectionData['footer_setting'] == 1 && isset($sectionData['enable_popular_category']) && $sectionData['enable_popular_category'] == 1)
                    <div class="col-lg-3 col-sm-6">
                        <div class="footer-links-col">
                            <h5 class="footer-col-title">
                                <span class="footer-col-title-dot"></span>
                                {{__('landingpage.handyman_category')}}
                            </h5>
                            <ul class="footer-link-list">
                                @foreach ($sectionData['category_id'] as $categoryId)
                                @php $category = App\Models\Category::find($categoryId); @endphp
                                @if($category && $category->status==1)
                                <li>
                                    <a href="{{ route('category.detail', $category->id) }}" class="footer-link-item">
                                        <span class="footer-link-arrow"><i class="ri-arrow-right-s-line"></i></span>
                                        <span>{{ $category->name }}</span>
                                    </a>
                                </li>
                                @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    {{-- For Customers — always shown --}}
                    <div class="col-lg-3 col-sm-6">
                        <div class="footer-links-col">
                            <h5 class="footer-col-title">
                                <span class="footer-col-title-dot"></span>
                                {{ __('landingpage.for_customers') }}
                            </h5>
                            <ul class="footer-link-list">
                                <li>
                                    <a href="{{ route('register') }}" class="footer-link-item">
                                        <span class="footer-link-arrow"><i class="ri-arrow-right-s-line"></i></span>
                                        <span>{{ __('landingpage.post_job_request') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('frontend.provider') }}" class="footer-link-item">
                                        <span class="footer-link-arrow"><i class="ri-arrow-right-s-line"></i></span>
                                        <span>{{ __('landingpage.find_workers') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ url('pages/how-it-works-for-customer') }}" class="footer-link-item">
                                        <span class="footer-link-arrow"><i class="ri-arrow-right-s-line"></i></span>
                                        <span>{{ __('landingpage.how_it_works') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- For Employers — always shown (col-lg-2 so row totals 12 with category) --}}
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="footer-links-col">
                            <h5 class="footer-col-title">
                                <span class="footer-col-title-dot"></span>
                                {{ __('landingpage.for_employers') }}
                            </h5>
                            <ul class="footer-link-list">
                                <li>
                                    <a href="{{ route('register') }}" class="footer-link-item">
                                        <span class="footer-link-arrow"><i class="ri-arrow-right-s-line"></i></span>
                                        <span>{{ __('landingpage.post_services') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('job.data') }}" class="footer-link-item">
                                        <span class="footer-link-arrow"><i class="ri-arrow-right-s-line"></i></span>
                                        <span>{{ __('landingpage.find_job') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ url('pages/how-it-works') }}" class="footer-link-item">
                                        <span class="footer-link-arrow"><i class="ri-arrow-right-s-line"></i></span>
                                        <span>{{ __('landingpage.how_it_works') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                @else
                    {{-- Fallback Quick Links if footer setting disabled --}}
                    <div class="col-lg-4 col-sm-6">
                        <div class="footer-links-col">
                            <h5 class="footer-col-title">
                                <span class="footer-col-title-dot"></span>
                                {{ __('landingpage.ft_quick_links') }}
                            </h5>
                            <ul class="footer-link-list">
                                <li><a href="{{ route('service.list') }}" class="footer-link-item"><span class="footer-link-arrow"><i class="ri-arrow-right-s-line"></i></span><span>{{ __('landingpage.ft_all_services') }}</span></a></li>
                                <li><a href="{{ route('post.job.list') }}" class="footer-link-item"><span class="footer-link-arrow"><i class="ri-arrow-right-s-line"></i></span><span>{{ __('landingpage.ft_post_a_job') }}</span></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6">
                        <div class="footer-links-col">
                            <h5 class="footer-col-title">
                                <span class="footer-col-title-dot"></span>
                                {{ __('landingpage.ft_support') }}
                            </h5>
                            <ul class="footer-link-list">
                                <li><a href="{{ route('user.help_support') }}" class="footer-link-item"><span class="footer-link-arrow"><i class="ri-arrow-right-s-line"></i></span><span>{{ __('landingpage.help_support') }}</span></a></li>
                                <li><a href="{{ route('user.privacy_policy') }}" class="footer-link-item"><span class="footer-link-arrow"><i class="ri-arrow-right-s-line"></i></span><span>{{ __('landingpage.privacy_policy') }}</span></a></li>
                                <li><a href="{{ route('user.term_conditions') }}" class="footer-link-item"><span class="footer-link-arrow"><i class="ri-arrow-right-s-line"></i></span><span>{{ __('landingpage.terms_conditions') }}</span></a></li>
                            </ul>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- ===== DIVIDER WITH TAGLINE ===== --}}
    <div class="footer-divider-wrap">
        <div class="container">
            <div class="footer-divider-inner">
                <span class="footer-divider-line"></span>
                <span class="footer-divider-icon"><i class="ri-tools-fill"></i></span>
                <span class="footer-divider-line"></span>
            </div>
        </div>
    </div>

    {{-- ===== BOTTOM BAR ===== --}}
    <div class="footer-bottom-bar">
        <div class="container">
            <div class="row align-items-center g-3">
                <div class="col-md-5 text-md-start text-center">
                    <p class="footer-copyright">
                        {{ $first_part }}
                        <a target="_blank" href="{{ optional($generalsetting)->website }}" class="footer-copyright-link">{{ $second_part }}</a>
                    </p>
                </div>
                <div class="col-md-7">
                    @php $footerPages = \App\Models\Page::active()->ordered()->get(); @endphp
                    <div class="footer-bottom-links">
                        @foreach($footerPages as $fp)
                            <a href="{{ route('user.page', $fp->slug) }}" class="footer-bottom-link">{{ $fp->title }}</a>
                        @endforeach
                        <a href="{{ route('user.term_conditions') }}" target="_blank" class="footer-bottom-link">{{__('landingpage.terms_conditions')}}</a>
                        <a href="{{ route('user.privacy_policy') }}" target="_blank" class="footer-bottom-link">{{__('landingpage.privacy_policy')}}</a>
                        <a href="{{ route('user.help_support') }}" target="_blank" class="footer-bottom-link">{{__('landingpage.help_support')}}</a>
                        <a href="{{ route('user.refund_policy') }}" target="_blank" class="footer-bottom-link">{{__('landingpage.refund_policy')}}</a>
                        <a href="{{ route('user.imprint') }}" target="_blank" class="footer-bottom-link">{{__('landingpage.imprint')}}</a>
                        <a href="{{ route('user.data_deletion_request') }}" target="_blank" class="footer-bottom-link">{{__('landingpage.data_deletion_request')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</footer>

<style>
/* ===================================================================
   ULTRA FOOTER — Complete redesign
   Primary: #3333ff | Deep dark background with neon glow accents
=================================================================== */

/* ------ ROOT ------ */
.footer-ultra {
    position: relative;
    overflow: hidden;
    background: linear-gradient(160deg, #0b0b1e 0%, #0e0e2a 40%, #12103a 70%, #0c0c22 100%);
    color: #e2e4f0;
    font-family: "Montserrat", sans-serif;
}

/* ------ ANIMATED MESH BACKGROUND ------ */
.footer-mesh-bg {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 0;
}

.footer-grid-svg {
    position: absolute;
    inset: 0;
    opacity: 1;
}

.footer-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(90px);
    animation: orbFloat 12s ease-in-out infinite;
    pointer-events: none;
}
.footer-orb-1 {
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(51, 51, 255, 0.18) 0%, transparent 70%);
    top: -180px; right: -100px;
    animation-duration: 14s;
}
.footer-orb-2 {
    width: 380px; height: 380px;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, transparent 70%);
    bottom: -100px; left: -60px;
    animation-duration: 18s;
    animation-direction: reverse;
}
.footer-orb-3 {
    width: 260px; height: 260px;
    background: radial-gradient(circle, rgba(168, 85, 247, 0.10) 0%, transparent 70%);
    top: 40%; left: 40%;
    animation-duration: 22s;
}
@keyframes orbFloat {
    0%, 100% { transform: translateY(0) scale(1); }
    33% { transform: translateY(-40px) scale(1.05); }
    66% { transform: translateY(20px) scale(0.97); }
}

/* ------ NEWSLETTER STRIP ------ */
.footer-newsletter-wrap {
    position: relative;
    z-index: 2;
    padding: 60px 0 0;
}

.footer-newsletter-card {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(51, 51, 255, 0.22) 0%, rgba(99, 102, 241, 0.18) 50%, rgba(168, 85, 247, 0.15) 100%);
    border: 1px solid rgba(99, 102, 241, 0.28);
    border-radius: 24px;
    padding: 44px 48px;
    backdrop-filter: blur(20px);
    box-shadow: 0 0 60px rgba(51, 51, 255, 0.12), inset 0 1px 0 rgba(255,255,255,0.08);
}

.footer-nl-glow {
    position: absolute;
    top: -60px; right: -60px;
    width: 260px; height: 260px;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.3) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
    animation: nlGlowPulse 4s ease-in-out infinite;
}
@keyframes nlGlowPulse {
    0%, 100% { opacity: 0.6; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.15); }
}

.footer-nl-icon-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
}
.footer-nl-icon-badge {
    width: 46px; height: 46px;
    background: linear-gradient(135deg, #3333ff 0%, #6366f1 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 24px rgba(51, 51, 255, 0.45);
    font-size: 1.4rem;
    color: #fff;
    flex-shrink: 0;
}
.footer-nl-tag {
    background: rgba(99, 102, 241, 0.2);
    border: 1px solid rgba(99, 102, 241, 0.4);
    color: #a5b4fc;
    border-radius: 50px;
    padding: 4px 14px;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
.footer-nl-title {
    font-size: clamp(1.35rem, 2.5vw, 1.75rem);
    font-weight: 800;
    color: #fff;
    line-height: 1.25;
    margin-bottom: 8px;
    letter-spacing: -0.02em;
}
.footer-nl-accent {
    background: linear-gradient(90deg, #818cf8 0%, #a78bfa 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.footer-nl-desc {
    font-size: 0.97rem;
    color: rgba(200, 204, 230, 0.8);
    line-height: 1.55;
    margin: 0;
}

.footer-nl-form-wrap { }

.footer-nl-input-group {
    position: relative;
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.07);
    border: 1px solid rgba(99, 102, 241, 0.35);
    border-radius: 16px;
    padding: 6px 6px 6px 18px;
    gap: 10px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    backdrop-filter: blur(10px);
}
.footer-nl-input-group:focus-within {
    border-color: rgba(99, 102, 241, 0.7);
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15), 0 0 30px rgba(51, 51, 255, 0.12);
}
.footer-nl-input-icon {
    color: rgba(200, 204, 230, 0.5);
    font-size: 1.1rem;
    flex-shrink: 0;
}
.footer-nl-input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: #fff;
    font-size: 0.95rem;
    font-family: "Montserrat", sans-serif;
    padding: 10px 0;
}
.footer-nl-input::placeholder { color: rgba(200, 204, 230, 0.4); }

.footer-nl-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #3333ff 0%, #6366f1 100%);
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 13px 22px;
    font-size: 0.9rem;
    font-weight: 700;
    font-family: "Montserrat", sans-serif;
    cursor: pointer;
    flex-shrink: 0;
    transition: all 0.3s ease;
    box-shadow: 0 6px 20px rgba(51, 51, 255, 0.4);
}
.footer-nl-btn:hover {
    background: linear-gradient(135deg, #2929e6 0%, #4f52d4 100%);
    box-shadow: 0 10px 30px rgba(51, 51, 255, 0.55);
    transform: translateY(-2px);
}
.footer-nl-btn:hover i { transform: translateX(4px); }
.footer-nl-btn i { transition: transform 0.3s ease; }

.footer-nl-note {
    margin: 12px 0 0;
    font-size: 0.8rem;
    color: rgba(200, 204, 230, 0.5);
    display: flex;
    align-items: center;
    gap: 6px;
}
.footer-nl-note i { color: #6366f1; font-size: 0.9rem; }

/* ------ MAIN FOOTER BODY ------ */
.footer-body {
    position: relative;
    z-index: 2;
    padding: 64px 0 48px;
}

/* ------ BRAND COLUMN ------ */
.footer-brand-col { }

.footer-logo-wrap {
    margin-bottom: 20px;
}
.footer-logo-wrap img,
.footer-logo-wrap a {
    display: inline-block;
}

.footer-brand-desc {
    font-size: 0.93rem;
    line-height: 1.7;
    color: rgba(200, 204, 230, 0.65);
    margin-bottom: 10px;
}

.footer-readmore {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #818cf8 !important;
    text-decoration: none !important;
    font-size: 0.88rem;
    font-weight: 600;
    transition: all 0.3s ease;
    -webkit-text-fill-color: #818cf8 !important;
    background: none !important;
    -webkit-background-clip: unset !important;
    background-clip: unset !important;
}
.footer-readmore:hover {
    color: #a5b4fc !important;
    -webkit-text-fill-color: #a5b4fc !important;
    gap: 10px;
}

/* ------ CONTACT CARDS ------ */
.footer-contact-cards {
    margin-top: 30px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.footer-contact-card {
    display: flex;
    align-items: center;
    gap: 14px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(99, 102, 241, 0.2);
    border-radius: 16px;
    padding: 14px 18px;
    text-decoration: none !important;
    transition: all 0.35s ease;
    backdrop-filter: blur(8px);
}
.footer-contact-card:hover {
    background: rgba(99, 102, 241, 0.12);
    border-color: rgba(99, 102, 241, 0.45);
    transform: translateX(6px);
    box-shadow: 0 8px 24px rgba(51, 51, 255, 0.15);
}

.footer-contact-icon-wrap {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, rgba(51, 51, 255, 0.35) 0%, rgba(99, 102, 241, 0.25) 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.3rem;
    color: #818cf8;
    transition: all 0.3s ease;
}
.footer-contact-card:hover .footer-contact-icon-wrap {
    background: linear-gradient(135deg, #3333ff, #6366f1);
    color: #fff;
    box-shadow: 0 6px 16px rgba(51, 51, 255, 0.4);
}

.footer-contact-label {
    display: block;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    font-weight: 700;
    color: rgba(200, 204, 230, 0.5);
    margin-bottom: 2px;
}
.footer-contact-value {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    color: #e2e4f0;
}

/* ------ SOCIAL ICONS ------ */
.footer-social-wrap {
    margin-top: 30px;
}
.footer-social-label {
    display: block;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-weight: 700;
    color: rgba(200, 204, 230, 0.45);
    margin-bottom: 14px;
}
.footer-social-icons {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.footer-social-btn {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none !important;
    font-size: 1.2rem;
    color: #fff !important;
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.07);
    position: relative;
    overflow: hidden;
}
.footer-social-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    opacity: 0;
    border-radius: inherit;
    transition: opacity 0.35s ease;
}
.footer-social-btn:hover { transform: translateY(-5px) rotate(5deg); border-color: transparent; }
.footer-social-btn:hover::before { opacity: 1; }

.footer-social-fb::before  { background: #1877f2; }
.footer-social-tw::before  { background: #000; }
.footer-social-tg::before  { background: #0088cc; }
.footer-social-ig::before  { background: linear-gradient(135deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); }
.footer-social-yt::before  { background: #ff0000; }
.footer-social-li::before  { background: #0077b5; }

.footer-social-btn i { position: relative; z-index: 1; }
/* PNG icons — same assets/sizes as service list cards (datatable-card) */
.footer-social-img {
    position: relative;
    z-index: 1;
    display: block;
    object-fit: contain;
    border-radius: 8px;
    flex-shrink: 0;
}
.footer-social-img-fb { width: 30px !important; height: auto !important; max-height: 30px; }
.footer-social-img-tw,
.footer-social-img-tg,
.footer-social-img-li,
.footer-social-img-yt { width: 28px !important; height: 28px !important; }

/* ------ LINKS COLUMN ------ */
.footer-links-col,
.footer-services-col { }

.footer-col-title {
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
    letter-spacing: 0.01em;
}

.footer-col-title-dot {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3333ff 0%, #818cf8 100%);
    box-shadow: 0 0 10px rgba(51, 51, 255, 0.6);
    flex-shrink: 0;
    animation: dotPulse 2.5s ease-in-out infinite;
}
@keyframes dotPulse {
    0%, 100% { box-shadow: 0 0 10px rgba(51, 51, 255, 0.6); }
    50% { box-shadow: 0 0 20px rgba(51, 51, 255, 1); }
}

.footer-link-list {
    list-style: none;
    padding: 0; margin: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.footer-link-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: rgba(200, 204, 230, 0.65) !important;
    text-decoration: none !important;
    font-size: 0.9rem;
    font-weight: 500;
    padding: 7px 10px;
    border-radius: 10px;
    transition: all 0.3s ease;
    -webkit-text-fill-color: rgba(200, 204, 230, 0.65) !important;
    background: none !important;
    -webkit-background-clip: unset !important;
    background-clip: unset !important;
}
.footer-link-item:hover {
    background: rgba(99, 102, 241, 0.1) !important;
    color: #818cf8 !important;
    -webkit-text-fill-color: #818cf8 !important;
    padding-left: 16px;
}
.footer-link-arrow {
    font-size: 1rem;
    color: #4f6eff;
    transition: transform 0.3s ease;
    flex-shrink: 0;
}
.footer-link-item:hover .footer-link-arrow { transform: translateX(3px); }

/* ------ SERVICE TILES ------ */
.footer-service-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
}
.footer-service-tile {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-decoration: none !important;
    transition: all 0.35s ease;
    width: 90px;
}
.footer-service-tile:hover { transform: translateY(-8px); }

.footer-service-img-wrap {
    width: 86px; height: 86px;
    border-radius: 18px;
    overflow: hidden;
    position: relative;
    border: 2px solid rgba(99, 102, 241, 0.25);
    background: rgba(99, 102, 241, 0.08);
    transition: all 0.35s ease;
}
.footer-service-tile:hover .footer-service-img-wrap {
    border-color: rgba(99, 102, 241, 0.65);
    box-shadow: 0 10px 30px rgba(51, 51, 255, 0.3), 0 0 0 3px rgba(99, 102, 241, 0.15);
}
.footer-service-img-wrap img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.footer-service-tile:hover .footer-service-img-wrap img { transform: scale(1.1); }

.footer-service-overlay {
    position: absolute;
    inset: 0;
    background: rgba(51, 51, 255, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.35s ease;
    font-size: 1.3rem;
    color: #fff;
    border-radius: inherit;
}
.footer-service-tile:hover .footer-service-overlay { opacity: 1; }

.footer-service-name {
    font-size: 0.78rem;
    font-weight: 600;
    color: rgba(200, 204, 230, 0.7);
    text-align: center;
    line-height: 1.3;
    max-width: 90px;
    transition: color 0.3s ease;
}
.footer-service-tile:hover .footer-service-name { color: #a5b4fc; }

/* ------ DIVIDER ------ */
.footer-divider-wrap {
    position: relative;
    z-index: 2;
    padding: 0 0 10px;
}
.footer-divider-inner {
    display: flex;
    align-items: center;
    gap: 16px;
}
.footer-divider-line {
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, rgba(99, 102, 241, 0.35) 50%, transparent 100%);
}
.footer-divider-icon {
    width: 36px; height: 36px;
    background: rgba(99, 102, 241, 0.15);
    border: 1px solid rgba(99, 102, 241, 0.3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    color: #818cf8;
    flex-shrink: 0;
    animation: spinIcon 20s linear infinite;
}
@keyframes spinIcon {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* ------ BOTTOM BAR ------ */
.footer-bottom-bar {
    position: relative;
    z-index: 2;
    padding: 24px 0 28px;
    border-top: 1px solid rgba(99, 102, 241, 0.15);
}

.footer-copyright {
    margin: 0;
    font-size: 0.88rem;
    color: rgba(200, 204, 230, 0.5);
    font-weight: 500;
}
.footer-copyright-link {
    color: #818cf8 !important;
    text-decoration: none !important;
    font-weight: 700;
    transition: color 0.3s ease;
    -webkit-text-fill-color: #818cf8 !important;
}
.footer-copyright-link:hover {
    color: #a5b4fc !important;
    -webkit-text-fill-color: #a5b4fc !important;
    text-decoration: underline !important;
}

.footer-bottom-links {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 4px 6px;
    justify-content: flex-end;
}
@media (max-width: 767px) {
    .footer-bottom-links { justify-content: center; }
}
.footer-bottom-link {
    font-size: 0.82rem;
    color: rgba(200, 204, 230, 0.5) !important;
    text-decoration: none !important;
    font-weight: 500;
    padding: 4px 8px;
    border-radius: 6px;
    transition: all 0.25s ease;
    white-space: nowrap;
    -webkit-text-fill-color: rgba(200, 204, 230, 0.5) !important;
}
.footer-bottom-link:hover {
    color: #818cf8 !important;
    -webkit-text-fill-color: #818cf8 !important;
    background: rgba(99, 102, 241, 0.1);
}

/* ------ RESPONSIVE ------ */
@media (max-width: 991px) {
    .footer-newsletter-card { padding: 36px 32px; }
    .footer-body { padding: 48px 0 36px; }
}
@media (max-width: 767px) {
    .footer-nl-input-group { flex-wrap: wrap; gap: 8px; padding: 10px 14px; }
    .footer-nl-btn { width: 100%; justify-content: center; border-radius: 10px; }
    .footer-nl-input { width: 100%; }
    .footer-service-grid { justify-content: flex-start; }
    .footer-body { padding: 40px 0 32px; }
    .footer-newsletter-card { padding: 28px 22px; }
}
@media (max-width: 480px) {
    .footer-service-tile { width: 80px; }
    .footer-service-img-wrap { width: 75px; height: 75px; }
}
</style>

@include('partials._scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>
<script>
    $('#submit_btn').on('click', function () {
       const email = $('#email').val();
       const errTitle = @json(__('landingpage.ft_error'));
       const pleaseEmail = @json(__('landingpage.ft_please_enter_email'));
       const invalidEmail = @json(__('landingpage.ft_invalid_email'));
       const doneTitle = @json(__('landingpage.ft_done'));
       const somethingWrong = @json(__('landingpage.ft_something_wrong'));

       if (!email.trim()) {
        Swal.fire({
            title: errTitle,
            text: pleaseEmail,
            icon: 'error',
            iconColor: '#3333ff'
        });
        return;
    }
        if (!validateEmail(email)) {
            Swal.fire({
                title: errTitle,
                text: invalidEmail,
                icon: 'error',
                iconColor: '#3333ff'
            });
            return;
        }

       $.ajax({
            url: '/user-subscribe',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                email: email,
            },
            success: function (response) {
               Swal.fire({
               title: doneTitle,
               text: response.message,
               icon: 'success',
               iconColor: '#3333ff'
               }).then((result) => {
                  if (result.isConfirmed) {
                     document.getElementById('email').value = '';
                     window.location.reload();
                  }
               });
            },
            error: function (error) {
                Swal.fire({
                title: errTitle,
                text: somethingWrong,
                icon: 'error',
                iconColor: '#3333ff'
                }).then((result) => {

                });
                console.error('Error:', error);
            }
        });
    });

    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    document.addEventListener("DOMContentLoaded", function() {
        var description = document.querySelector('.readmore-text');
        var readmoreBtn = document.querySelector('.readmore-btn');

        if (description && description.offsetHeight < description.scrollHeight) {
            readmoreBtn.style.display = 'inline-flex';
        } else if (readmoreBtn) {
            readmoreBtn.style.display = 'none';
        }
    });
</script>
