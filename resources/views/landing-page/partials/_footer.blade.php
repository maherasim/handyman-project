<footer class="footer-modern" style="position: relative !important; overflow: hidden !important; background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;">
    <!-- Decorative Background Elements -->
    <div class="footer-bg-decoration-1"></div>
    <div class="footer-bg-decoration-2"></div>
    
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
    <div class="footer-top py-5" style="position: relative !important; z-index: 1 !important; padding: 80px 0 60px 0 !important;">
        <div class="container">
            <div class="row g-4">
                <!-- Brand Section -->
                <div class="col-lg-5 col-md-6">
                    <div class="footer-brand-section">
                        @include('landing-page.components.widgets.logo')
                        <p class="mt-4 mb-3 readmore-text" style="color: rgba(255, 255, 255, 0.8) !important; font-size: 0.95rem !important; line-height: 1.7 !important;">
                            {{ optional($generalsetting)->site_description }}
                        </p>
                        <a href="javascript:void(0);" class="readmore-btn" style="color: #5F60B9 !important; text-decoration: none !important; font-weight: 600 !important; font-size: 0.9rem !important; transition: all 0.3s ease !important; display: inline-flex !important; align-items: center !important; gap: 5px !important;">
                            {{__('landingpage.read_more')}}
                            <i class="ri-arrow-right-line" style="transition: transform 0.3s ease !important;"></i>
                        </a>
                        
                        <!-- Contact Information -->
                        @if(optional($generalsetting)->inquriy_email  || optional($generalsetting)->helpline_number)
                        <div class="mt-5">
                            <div class="d-flex flex-column gap-4">
                                @if(optional($generalsetting)->inquriy_email)
                                <div class="d-flex align-items-center gap-3">
                                    <div class="contact-icon-wrapper footer-contact-icon">
                                        <i class="ri-mail-line"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1" style="color: rgba(255, 255, 255, 0.9) !important; font-size: 0.85rem !important; font-weight: 600 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important;">{{__('landingpage.business_inquries')}}</h6>
                                        <a href="mailto:{{ optional($generalsetting)->inquriy_email }}" style="color: #5F60B9 !important; text-decoration: none !important; font-size: 0.95rem !important; font-weight: 500 !important; transition: all 0.3s ease !important;">{{ optional($generalsetting)->inquriy_email }}</a>
                                    </div>
                                </div>
                                @endif
                                @if(optional($generalsetting)->helpline_number)
                                <div class="d-flex align-items-center gap-3">
                                    <div class="contact-icon-wrapper footer-contact-icon">
                                        <i class="ri-phone-line"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1" style="color: rgba(255, 255, 255, 0.9) !important; font-size: 0.85rem !important; font-weight: 600 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important;">{{__('landingpage.helpline_number')}}</h6>
                                        <a href="tel:{{optional($generalsetting)->helpline_number}}" style="color: #5F60B9 !important; text-decoration: none !important; font-size: 0.95rem !important; font-weight: 500 !important; transition: all 0.3s ease !important;">{{optional($generalsetting)->helpline_number}}</a>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        <!-- Social Media Links -->
                        @if($socialmedia !== null)
                        <div class="mt-5">
                            <h6 class="mb-3" style="color: rgba(255, 255, 255, 0.9) !important; font-size: 0.9rem !important; font-weight: 600 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important;">{{__('landingpage.follow_us')}}</h6>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                @if(optional($socialmedia)->facebook_url)
                                    <a href="{{ optional($socialmedia)->facebook_url }}" target="_blank" class="social-icon-link" style="width: 42px !important; height: 42px !important; background: rgba(255, 255, 255, 0.25) !important; border-radius: 10px !important; display: flex !important; align-items: center !important; justify-content: center !important; color: #fff !important; text-decoration: none !important; transition: all 0.3s ease !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; backdrop-filter: blur(10px) !important;">
                                        <i class="ri-facebook-fill" style="font-size: 1.3rem !important; color: #ffffff !important; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5) !important; font-family: 'remixicon' !important; font-style: normal !important; display: inline-block !important; line-height: 1 !important;"></i>
                                    </a>
                                @endif
                                @if(optional($socialmedia)->twitter_url)
                                    <a href="{{ optional($socialmedia)->twitter_url }}" target="_blank" class="social-icon-link" style="width: 42px !important; height: 42px !important; background: rgba(255, 255, 255, 0.25) !important; border-radius: 10px !important; display: flex !important; align-items: center !important; justify-content: center !important; color: #fff !important; text-decoration: none !important; transition: all 0.3s ease !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; backdrop-filter: blur(10px) !important;">
                                        <i class="ri-twitter-fill" style="font-size: 1.3rem !important; color: #ffffff !important; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5) !important; font-family: 'remixicon' !important; font-style: normal !important; display: inline-block !important; line-height: 1 !important;"></i>
                                    </a>
                                @endif
                                @if(optional($socialmedia)->instagram_url)
                                    <a href="{{ optional($socialmedia)->instagram_url }}" target="_blank" class="social-icon-link" style="width: 42px !important; height: 42px !important; background: rgba(255, 255, 255, 0.25) !important; border-radius: 10px !important; display: flex !important; align-items: center !important; justify-content: center !important; color: #fff !important; text-decoration: none !important; transition: all 0.3s ease !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; backdrop-filter: blur(10px) !important;">
                                        <i class="ri-instagram-fill" style="font-size: 1.3rem !important; color: #ffffff !important; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5) !important; font-family: 'remixicon' !important; font-style: normal !important; display: inline-block !important; line-height: 1 !important;"></i>
                                    </a>
                                @endif
                                @if(optional($socialmedia)->youtube_url)
                                    <a href="{{ optional($socialmedia)->youtube_url }}" target="_blank" class="social-icon-link" style="width: 42px !important; height: 42px !important; background: rgba(255, 255, 255, 0.25) !important; border-radius: 10px !important; display: flex !important; align-items: center !important; justify-content: center !important; color: #fff !important; text-decoration: none !important; transition: all 0.3s ease !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; backdrop-filter: blur(10px) !important;">
                                        <i class="ri-youtube-fill" style="font-size: 1.3rem !important; color: #ffffff !important; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5) !important; font-family: 'remixicon' !important; font-style: normal !important; display: inline-block !important; line-height: 1 !important;"></i>
                                    </a>
                                @endif
                                @if(optional($socialmedia)->linkedin_url)
                                    <a href="{{ optional($socialmedia)->linkedin_url }}" target="_blank" class="social-icon-link" style="width: 42px !important; height: 42px !important; background: rgba(255, 255, 255, 0.25) !important; border-radius: 10px !important; display: flex !important; align-items: center !important; justify-content: center !important; color: #fff !important; text-decoration: none !important; transition: all 0.3s ease !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; backdrop-filter: blur(10px) !important;">
                                        <i class="ri-linkedin-fill" style="font-size: 1.3rem !important; color: #ffffff !important; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5) !important; font-family: 'remixicon' !important; font-style: normal !important; display: inline-block !important; line-height: 1 !important;"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Categories & Services Section -->
                @php
                $footerSection = App\Models\FrontendSetting::where('key', 'footer-setting')->first();
                $sectionData = $footerSection ? json_decode($footerSection->value, true) : null;
                @endphp
                @if ($sectionData && isset($sectionData['footer_setting']) && $sectionData['footer_setting'] == 1)
                <div class="col-lg-7 col-md-6">
                    <div class="row g-4">
                        @if ($sectionData['footer_setting'] == 1 && isset($sectionData['enable_popular_category']) && $sectionData['enable_popular_category'] == 1)
                        <div class="col-md-6">
                            <div class="footer-links-section">
                                <h5 class="mb-4 footer-section-title">
                                    {{__('landingpage.handyman_category')}}
                                    <span class="footer-title-underline"></span>
                                </h5>
                                <ul class="footer-links-list" style="list-style: none !important; padding: 0 !important; margin: 0 !important;">
                                    @foreach ($sectionData['category_id'] as $categoryId)
                                    @php
                                        $category = App\Models\Category::find($categoryId);
                                    @endphp
                                        @if($category && $category->status==1)
                                        <li class="mb-2">
                                            <a href="{{ route('category.detail', $category->id) }}" style="color: rgba(255, 255, 255, 0.75) !important; text-decoration: none !important; font-size: 0.9rem !important; transition: all 0.3s ease !important; display: inline-flex !important; align-items: center !important; padding: 4px 0 !important;">
                                                <i class="ri-arrow-right-s-line me-2" style="font-size: 0.85rem !important; color: #5F60B9 !important; transition: all 0.3s ease !important;"></i>{{ $category->name }}
                                            </a>
                                        </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif
                        
                        @php
                        $footerServiceSection = App\Models\FrontendSetting::where('key', 'footer-setting')->first();
                        $sectionData = $footerServiceSection ? json_decode($footerServiceSection->value, true) : null;
                        @endphp
                        @if ($sectionData && isset($sectionData['footer_setting']) && $sectionData['footer_setting'] == 1 && isset($sectionData['enable_popular_service']) && $sectionData['enable_popular_service'] == 1)
                        <div class="col-md-6">
                            <div class="footer-services-section">
                                <h5 class="mb-4 footer-section-title">
                                    {{__('landingpage.popular_services')}}
                                    <span class="footer-title-underline"></span>
                                </h5>
                                <ul class="footer-services-list d-flex flex-wrap gap-3" style="list-style: none !important; padding: 0 !important; margin: 0 !important;">
                                    @foreach ($sectionData['service_id'] as $serviceId)
                                    @php
                                        $service = App\Models\Service::find($serviceId);
                                        $mediaServiceImages = $service ? $service->getMedia('service_attachment') : null;
                                    @endphp
                                    @if ($service && $mediaServiceImages->isNotEmpty())
                                        @php
                                            $firstMedia = $mediaServiceImages->first();
                                        @endphp
                                        @if ($firstMedia && getFileExistsCheck($firstMedia))
                                        <li>
                                            <a href="{{ route('service.detail', $service->id) }}" class="service-link-item" style="display: flex !important; flex-direction: column !important; align-items: center !important; text-decoration: none !important; transition: all 0.3s ease !important;">
                                                <div style="width: 85px !important; height: 85px !important; border-radius: 14px !important; overflow: hidden !important; background: rgba(95, 96, 185, 0.1) !important; border: 2px solid rgba(95, 96, 185, 0.3) !important; margin-bottom: 10px !important; transition: all 0.3s ease !important; position: relative !important;">
                                                    <img src="{{ url($firstMedia->getUrl()) }}" alt="service-image" style="width: 100% !important; height: 100% !important; object-fit: cover !important; position: relative !important; z-index: 0 !important;">
                                                </div>
                                                <span style="color: rgba(255, 255, 255, 0.75) !important; font-size: 0.8rem !important; text-align: center !important; max-width: 85px !important; line-height: 1.3 !important; font-weight: 500 !important; transition: all 0.3s ease !important;">{{$service->name}}</span>
                                            </a>
                                        </li>
                                        @endif
                                    @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-md-start text-center mb-md-0 mb-3">
                    <p class="mb-0" style="color: rgba(255, 255, 255, 0.7) !important; font-size: 0.9rem !important;">
                        {{ $first_part }}
                        <a target="_blank" href="{{ optional($generalsetting)->website }}" style="color: #5F60B9 !important; text-decoration: none !important; font-weight: 600 !important; transition: all 0.3s ease !important;">{{ $second_part }}</a>
                    </p>
                </div>
                <div class="col-md-6 text-md-end text-center">
                    <div class="d-inline-flex align-items-center gap-3 flex-wrap justify-content-md-end justify-content-center">
                        <a target="_blank" href="{{ route('user.term_conditions') }}" style="color: rgba(255, 255, 255, 0.7) !important; text-decoration: none !important; font-size: 0.85rem !important; transition: all 0.3s ease !important;">{{__('landingpage.terms_conditions')}}</a>
                        <span style="color: rgba(255, 255, 255, 0.3) !important;">|</span>
                        <a target="_blank" href="{{ route('user.privacy_policy') }}" style="color: rgba(255, 255, 255, 0.7) !important; text-decoration: none !important; font-size: 0.85rem !important; transition: all 0.3s ease !important;">{{__('landingpage.privacy_policy')}}</a>
                        <span style="color: rgba(255, 255, 255, 0.3) !important;">|</span>
                        <a target="_blank" href="{{ route('user.help_support') }}" style="color: rgba(255, 255, 255, 0.7) !important; text-decoration: none !important; font-size: 0.85rem !important; transition: all 0.3s ease !important;">{{__('landingpage.help_support')}}</a>
                        <span style="color: rgba(255, 255, 255, 0.3) !important;">|</span>
                        <a target="_blank" href="{{ route('user.refund_policy') }}" style="color: rgba(255, 255, 255, 0.7) !important; text-decoration: none !important; font-size: 0.85rem !important; transition: all 0.3s ease !important;">{{__('landingpage.refund_policy')}}</a>
                        <span style="color: rgba(255, 255, 255, 0.3) !important;">|</span>
                        <a target="_blank" href="{{ route('user.imprint') }}" style="color: rgba(255, 255, 255, 0.7) !important; text-decoration: none !important; font-size: 0.85rem !important; transition: all 0.3s ease !important;">{{__('landingpage.imprint')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
    /* Professional Footer with Clear Red-Blue Gradient Theme */
    body .footer-modern,
    body footer.footer-modern,
    footer.footer-modern,
    .footer-modern {
        background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
        background-image: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
        background-color: transparent !important;
        color: #ffffff !important;
        position: relative !important;
        overflow: hidden !important;
    }
    
    /* Remove any overlays that might dim the gradient */
    body .footer-modern::before,
    body .footer-modern::after,
    footer.footer-modern::before,
    footer.footer-modern::after {
        display: none !important;
        content: none !important;
    }
    
    /* Decorative Background Elements - Very subtle to keep gradient clear */
    body .footer-bg-decoration-1 {
        position: absolute !important;
        top: -150px !important;
        right: -150px !important;
        width: 500px !important;
        height: 500px !important;
        background: radial-gradient(circle, rgba(255, 0, 0, 0.05) 0%, rgba(95, 96, 185, 0.05) 100%) !important;
        border-radius: 50% !important;
        filter: blur(150px) !important;
        z-index: 0 !important;
        opacity: 0.3 !important;
        animation: floatFooter 8s ease-in-out infinite !important;
    }
    
    body .footer-bg-decoration-2 {
        position: absolute !important;
        bottom: -150px !important;
        left: -150px !important;
        width: 450px !important;
        height: 450px !important;
        background: radial-gradient(circle, rgba(95, 96, 185, 0.05) 0%, rgba(255, 0, 0, 0.05) 100%) !important;
        border-radius: 50% !important;
        filter: blur(150px) !important;
        z-index: 0 !important;
        opacity: 0.3 !important;
        animation: floatFooter 10s ease-in-out infinite reverse !important;
    }
    
    @keyframes floatFooter {
        0%, 100% {
            transform: translateY(0px) scale(1);
        }
        50% {
            transform: translateY(-30px) scale(1.1);
        }
    }
    
    body .footer-top {
        position: relative !important;
        z-index: 1 !important;
        padding: 90px 0 70px 0 !important;
    }
    
    /* Contact Icons with Gradient */
    body .footer-contact-icon {
        width: 60px !important;
        height: 60px !important;
        background: linear-gradient(135deg, rgba(255, 0, 0, 0.2) 0%, rgba(95, 96, 185, 0.2) 100%) !important;
        border-radius: 16px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        border: 2px solid rgba(255, 0, 0, 0.3) !important;
        box-shadow: 0 6px 20px rgba(255, 0, 0, 0.2) !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    body .footer-contact-icon i {
        font-size: 1.8rem !important;
        background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
    }
    
    body .footer-contact-icon:hover {
        background: linear-gradient(135deg, rgba(255, 0, 0, 0.35) 0%, rgba(95, 96, 185, 0.35) 100%) !important;
        border-color: rgba(255, 0, 0, 0.5) !important;
        transform: translateY(-5px) scale(1.1) rotate(5deg) !important;
        box-shadow: 0 10px 30px rgba(255, 0, 0, 0.35) !important;
    }
    
    body .footer-contact-icon:hover i {
        transform: scale(1.15) !important;
    }
    
    /* Section Titles with Gradient */
    body .footer-section-title {
        color: #fff !important;
        font-size: 1.2rem !important;
        font-weight: 700 !important;
        position: relative !important;
        padding-bottom: 15px !important;
        margin-bottom: 20px !important;
    }
    
    body .footer-title-underline {
        position: absolute !important;
        bottom: 0 !important;
        left: 0 !important;
        width: 60px !important;
        height: 4px !important;
        background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
        border-radius: 3px !important;
        box-shadow: 0 2px 8px rgba(255, 0, 0, 0.4) !important;
    }
    
    /* Read More Button */
    body .readmore-btn {
        color: #5F60B9 !important;
        text-decoration: none !important;
        font-weight: 600 !important;
        font-size: 0.95rem !important;
        transition: all 0.3s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
    }
    
    body .readmore-btn:hover {
        background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
        transform: translateX(5px) !important;
    }
    
    body .readmore-btn:hover i {
        transform: translateX(4px) !important;
    }
    
    /* Social Media Icons with Gradient - Enhanced Visibility */
    body .social-icon-link {
        position: relative !important;
        overflow: visible !important;
        width: 45px !important;
        height: 45px !important;
        background: rgba(255, 255, 255, 0.25) !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #fff !important;
        text-decoration: none !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        backdrop-filter: blur(10px) !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2) !important;
    }
    
    body .social-icon-link::before {
        content: '' !important;
        position: absolute !important;
        inset: 0 !important;
        background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
        opacity: 0 !important;
        transition: opacity 0.4s ease !important;
        z-index: 0 !important;
    }
    
    body .social-icon-link i {
        position: relative !important;
        z-index: 1 !important;
        font-size: 1.3rem !important;
        color: #ffffff !important;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5) !important;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.5)) !important;
        font-family: 'remixicon' !important;
        font-style: normal !important;
        -webkit-font-smoothing: antialiased !important;
        -moz-osx-font-smoothing: grayscale !important;
        display: inline-block !important;
        line-height: 1 !important;
    }
    
    body .social-icon-link:hover::before {
        opacity: 1 !important;
    }
    
    body .social-icon-link:hover {
        background: rgba(255, 255, 255, 0.35) !important;
        transform: translateY(-5px) scale(1.15) rotate(5deg) !important;
        box-shadow: 0 10px 25px rgba(255, 0, 0, 0.4) !important;
        border-color: rgba(255, 255, 255, 0.5) !important;
    }
    
    body .social-icon-link:hover i {
        color: #fff !important;
        transform: scale(1.1) !important;
        text-shadow: 0 3px 6px rgba(0, 0, 0, 0.6) !important;
    }
    
    /* Footer Links with Gradient Hover */
    body .footer-links-list a {
        color: rgba(255, 255, 255, 0.75) !important;
        text-decoration: none !important;
        font-size: 0.9rem !important;
        transition: all 0.3s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        padding: 6px 0 !important;
    }
    
    body .footer-links-list a:hover {
        background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
        transform: translateX(10px) !important;
        padding-left: 8px !important;
    }
    
    body .footer-links-list a:hover i {
        background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
        transform: translateX(5px) !important;
    }
    
    /* Service Cards with Gradient */
    body .service-link-item {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        text-decoration: none !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    body .service-link-item > div {
        width: 90px !important;
        height: 90px !important;
        border-radius: 16px !important;
        overflow: hidden !important;
        background: linear-gradient(135deg, rgba(255, 0, 0, 0.1) 0%, rgba(95, 96, 185, 0.1) 100%) !important;
        border: 2px solid rgba(255, 0, 0, 0.3) !important;
        margin-bottom: 12px !important;
        transition: all 0.4s ease !important;
        position: relative !important;
    }
    
    body .service-link-item > div::before {
        content: '' !important;
        position: absolute !important;
        inset: 0 !important;
        background: linear-gradient(135deg, rgba(255, 0, 0, 0.2) 0%, rgba(95, 96, 185, 0.2) 100%) !important;
        opacity: 0 !important;
        transition: opacity 0.4s ease !important;
        z-index: 1 !important;
    }
    
    body .service-link-item > div img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        position: relative !important;
        z-index: 0 !important;
    }
    
    body .service-link-item:hover {
        transform: translateY(-10px) !important;
    }
    
    body .service-link-item:hover > div {
        border-color: rgba(255, 0, 0, 0.6) !important;
        box-shadow: 0 12px 35px rgba(255, 0, 0, 0.35) !important;
        transform: scale(1.08) rotate(2deg) !important;
    }
    
    body .service-link-item:hover > div::before {
        opacity: 1 !important;
    }
    
    body .service-link-item span {
        color: rgba(255, 255, 255, 0.75) !important;
        font-size: 0.8rem !important;
        text-align: center !important;
        max-width: 90px !important;
        line-height: 1.4 !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
    }
    
    body .service-link-item:hover span {
        background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
        font-weight: 600 !important;
    }
    
    /* Footer Bottom with Gradient Border */
    body .footer-bottom {
        position: relative !important;
        z-index: 1 !important;
        padding: 35px 0 !important;
        border-top: 2px solid transparent !important;
        border-image: linear-gradient(135deg, rgba(255, 0, 0, 0.4) 0%, rgba(95, 96, 185, 0.4) 100%) 1 !important;
        background: linear-gradient(135deg, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.3) 100%) !important;
    }
    
    body .footer-bottom a {
        color: rgba(255, 255, 255, 0.7) !important;
        text-decoration: none !important;
        font-size: 0.85rem !important;
        transition: all 0.3s ease !important;
    }
    
    body .footer-bottom a:hover,
    body .footer-bottom a:active,
    body .footer-bottom a:focus,
    body .footer-bottom a:visited {
        color: rgba(255, 255, 255, 0.9) !important;
        -webkit-text-fill-color: rgba(255, 255, 255, 0.9) !important;
        background: transparent !important;
        transform: translateY(-2px) !important;
        text-decoration: underline !important;
    }
    
    body .footer-bottom span {
        background: linear-gradient(135deg, rgba(255, 0, 0, 0.5) 0%, rgba(95, 96, 185, 0.5) 100%) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
    }
    
    /* Contact Links */
    body .footer-brand-section a[href^="mailto:"],
    body .footer-brand-section a[href^="tel:"] {
        color: #5F60B9 !important;
        text-decoration: none !important;
        font-size: 0.95rem !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
    }
    
    body .footer-brand-section a[href^="mailto:"]:hover,
    body .footer-brand-section a[href^="tel:"]:hover {
        background: linear-gradient(135deg, #FF0000 0%, #5F60B9 100%) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        body .footer-top {
            padding: 70px 0 50px 0 !important;
        }
        
        body .footer-brand-section,
        body .footer-links-section,
        body .footer-services-section {
            margin-bottom: 2.5rem !important;
        }
        
        body .footer-contact-icon {
            width: 50px !important;
            height: 50px !important;
        }
        
        body .footer-contact-icon i {
            font-size: 1.5rem !important;
        }
        
        body .service-link-item > div {
            width: 75px !important;
            height: 75px !important;
        }
        
        body .social-icon-link {
            width: 40px !important;
            height: 40px !important;
        }
    }
</style>

@include('partials._scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>
<script>
    $('#submit_btn').on('click', function () {
       const email = $('#email').val();

       if (!email.trim()) {
        Swal.fire({
            title: 'Error',
            text: 'Please enter an email address',
            icon: 'error',
            iconColor: '#5F60B9'
        });
        return;
    }
        if (!validateEmail(email)) {
            Swal.fire({
                title: 'Error',
                text: 'Invalid email address',
                icon: 'error',
                iconColor: '#5F60B9'
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
               title: 'Done',
               text: response.message,
               icon: 'success',
               iconColor: '#5F60B9'
               }).then((result) => {
                  if (result.isConfirmed) {
                     document.getElementById('email').value = '';
                     window.location.reload();
                  }
               });
            },
            error: function (error) {
                Swal.fire({
                title: 'Error',
                text: 'Something Went Wrong!',
                icon: 'error',
                iconColor: '#5F60B9'
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
            readmoreBtn.style.display = 'block';
        } else if (readmoreBtn) {
            readmoreBtn.style.display = 'none';
        }
    });
</script>
