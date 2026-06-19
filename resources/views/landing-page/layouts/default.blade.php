<!DOCTYPE html>
 <!-- <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session()->has('dir') ? session()->get('dir') : 'ltr' }}">  -->
<html lang="en" onload="pageLoad()">
<head>
    @yield('before_head')
    @include('landing-page.partials._head')


    @yield('after_head')


</head>
<script>
    var frontendLocale = "{{ session()->get('locale') ?? 'en' }}";
    sessionStorage.setItem("local", frontendLocale);
    (function() {
        const savedTheme = localStorage.getItem('data-bs-theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
        if (savedTheme === 'dark') {
            document.body.classList.add('dark');
        }
    })();
</script>
<body class="body-bg">


    <span class="screen-darken"></span>

    <div id="loading">
        @include('landing-page.partials.loading')
    </div>


    <main class="main-content" id="landing-app">
        <div class="position-relative">

            @include('landing-page.partials._header')
        </div>
        @yield('content')
    </main>

    @include('landing-page.partials._footer')

    @include('landing-page.partials.cookie')

    @include('landing-page.partials.back-to-top')



  @yield('before_script')
    @include('landing-page.partials._scripts')
    @include('landing-page.partials._currencyscripts')
    @yield('after_script')

    @include('partials.ugc-service-cards-script')

    <script>
        function pageLoad() {
            var html = localStorage.getItem('data-bs-theme');
            if (html == null) {
                html = 'light';
            }
            if (html == 'light') {
                jQuery('body').addClass('dark');
                $('.darkmode-logo').removeClass('d-none')
                $('.light-logo').addClass('d-none')
            } else {
                jQuery('body').removeClass('dark');
                $('.darkmode-logo').addClass('d-none')
                $('.light-logo').removeClass('d-none')
            }
        }
        pageLoad();

        const savedTheme = localStorage.getItem('data-bs-theme');
        if (savedTheme === 'dark') {
            $('html').attr('data-bs-theme', 'dark');
        } else {
            $('html').attr('data-bs-theme', 'light');
        }

        $('.change-mode').on('click', function() {
            const body = jQuery('body')
            var html = $('html').attr('data-bs-theme');
            console.log('mode ' +html);

            if (html == 'light') {
                body.removeClass('dark');
                $('html').attr('data-bs-theme', 'dark');
                $('.darkmode-logo').addClass('d-none')
                $('.light-logo').removeClass('d-none')
                localStorage.setItem('dark', true)
                localStorage.setItem('data-bs-theme', 'dark')
            } else {

                $('.body-bg').addClass('dark');
                $('html').attr('data-bs-theme', 'light');
                $('.darkmode-logo').removeClass('d-none')
                $('.light-logo').addClass('d-none')
                localStorage.setItem('dark', false)
                localStorage.setItem('data-bs-theme', 'light')
            }

        })

    </script>

    <script>
        $(document).ready(function() {

            // Modal fix – Bootstrap's data-api may not fire when two BS5 instances
            // are loaded (landing-app.min.js + bootstrap.bundle.js). Handle manually.
            $(document).on('click', '[data-bs-toggle="modal"]', function(e) {
                e.preventDefault();
                var target = $(this).data('bs-target') || $(this).attr('href');
                var $modal = $(target);
                if (!$modal.length) return;
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance($modal[0]).show();
                } else {
                    // Fallback: toggle CSS classes directly
                    $modal.addClass('show').css('display', 'block').removeAttr('aria-hidden');
                    $('body').addClass('modal-open');
                    if (!$('.modal-backdrop').length) {
                        $('<div class="modal-backdrop fade show"></div>').appendTo('body');
                    }
                }
            });
            $(document).on('click', '[data-bs-dismiss="modal"]', function() {
                var $modal = $(this).closest('.modal');
                if (!$modal.length) return;
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var inst = bootstrap.Modal.getInstance($modal[0]);
                    if (inst) inst.hide();
                } else {
                    $modal.removeClass('show').css('display', 'none').attr('aria-hidden', 'true');
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                }
            });

            $('.textbuttoni').click(function() {
                $(this).prev('.custome-seatei').toggleClass('active');
                if ($(this).text() === '{{ __('landingpage.read_more') }}') {
                    $(this).text('{{ __('landingpage.read_less') }}');
                } else {
                    $(this).text('{{ __('landingpage.read_more') }}');
                }
            });

            // Top header language dropdown – standalone handler that works
            // regardless of which Bootstrap instance is active after the build.
            var $topToggle = $('.top-header [data-bs-toggle="dropdown"]');
            var $topMenu   = $('.top-header .dropdown-menu');
            if ($topToggle.length && $topMenu.length) {
                $topToggle.on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var open = $topMenu.hasClass('show');
                    $topMenu.toggleClass('show', !open);
                    $topToggle.attr('aria-expanded', String(!open));
                });
                $(document).on('click', function(e) {
                    if (!$topToggle.is(e.target) && !$topToggle.has(e.target).length) {
                        $topMenu.removeClass('show');
                        $topToggle.attr('aria-expanded', 'false');
                    }
                });
            }
        });
    </script>

</body>
</html>
