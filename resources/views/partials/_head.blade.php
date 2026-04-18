{{-- Google Analytics (gtag.js) --}}
<script async src="https://www.googletagmanager.com/gtag/js?id=G-RQ69M877X0"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-RQ69M877X0');
</script>

<link rel="shortcut icon" class="favicon_preview" href="{{ getSingleMedia(imageSession('get'),'favicon',null) }}" />
<link rel='stylesheet' href="{{ asset('vendor/fullcalendar/core/main.css')}}" />
<link rel='stylesheet' href="{{ asset('vendor/fullcalendar/daygrid/main.css')}}" />
<link rel='stylesheet' href="{{ asset('vendor/fullcalendar/timegrid/main.css')}}" />
<link rel='stylesheet' href="{{ asset('vendor/fullcalendar/list/main.css')}}" />
<link rel="stylesheet" href="{{ asset('css/backend-plugin.min.css')}}">
<link rel="stylesheet" href="{{ asset('css/backend.css?v=1.0.0')}}">
<link rel="stylesheet" href="{{ asset('css/swiper-bundle.min.css')}}">
<link rel="stylesheet" href="{{ asset('vendor/@fortawesome/fontawesome-free/css/all.min.css')}}">
<link rel="stylesheet" href="{{ asset('vendor/line-awesome/dist/line-awesome/css/line-awesome.min.css')}}">
<link rel="stylesheet" href="{{ asset('vendor/remixicon/fonts/remixicon.css')}}">
<link rel="stylesheet" href="{{ asset('vendor/confirmJs/jquery-confirm.css')}}">
{{-- Select2 base styles: must NOT live under public/css alone — npm clean wipes public/css and breaks all selects. Use vendor copy (or bundle). --}}
<link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{ asset('vendor/magnific-popup/magnific-popup.css') }}">
<!-- @if(session()->get('dir') == 'rtl')
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
@endif -->