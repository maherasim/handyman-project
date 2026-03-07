

@extends('landing-page.layouts.default')

@section('content')
<style>
    /* Ensure SweetAlert2 and Bootstrap modals sit above any card ribbons/badges */
    .swal2-container { z-index: 200000 !important; }
    .swal2-container .swal2-popup { z-index: 200001 !important; }
    .modal-backdrop { z-index: 200010 !important; }
    .modal { z-index: 200020 !important; }
</style>
<div class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-12">

                <!-- Tab panes -->
                <service-page link="{{ route('service.data', ['id' => $id, 'type' => $type, 'latitude' => $latitude, 'longitude' => $longitude]) }}"></service-page>


            </div>
        </div>
    </div>
</div>

@endsection

@section('after_script')
<script>
(function() {
    window.__shareClickHandler = function(e, el) {
        try { e.preventDefault(); e.stopPropagation(); } catch (_) {}
        function openPopup(url) { window.open(url, '_blank', 'noopener,noreferrer,width=600,height=600'); }
        var platform = el.getAttribute('data-platform');
        var shareUrl = el.getAttribute('data-share-url') || window.location.href;
        if (platform === 'facebook') {
            var fbUrl = encodeURIComponent(shareUrl);
            var quote = encodeURIComponent(el.getAttribute('data-quote') || '');
            openPopup('https://www.facebook.com/sharer/sharer.php?u=' + fbUrl + (quote ? '&quote=' + quote : ''));
        } else if (platform === 'twitter') {
            var text = encodeURIComponent(el.getAttribute('data-text') || el.getAttribute('data-quote') || '');
            var url = encodeURIComponent(shareUrl);
            openPopup('https://twitter.com/intent/tweet?url=' + url + '&text=' + text);
        } else if (platform === 'linkedin') {
            openPopup('https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(shareUrl));
        } else if (platform === 'instagram') {
            var quote = el.getAttribute('data-quote') || '';
            var textToCopy = quote ? quote + ' ' + shareUrl : shareUrl;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(textToCopy).then(function() {
                    if (typeof window.__shareCopyToast === 'function') window.__shareCopyToast();
                }).catch(function() { fallbackCopy(textToCopy); });
            } else { fallbackCopy(textToCopy); }
            function fallbackCopy(str) {
                var ta = document.createElement('textarea');
                ta.value = str; ta.setAttribute('readonly', ''); ta.style.position = 'fixed'; ta.style.left = '-9999px';
                document.body.appendChild(ta); ta.select();
                try { document.execCommand('copy'); if (typeof window.__shareCopyToast === 'function') window.__shareCopyToast(); } catch (err) {}
                document.body.removeChild(ta);
            }
        }
        return false;
    };
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            var el = e.target.closest && e.target.closest('.share-link');
            if (el && typeof window.__shareClickHandler === 'function') {
                window.__shareClickHandler(e, el);
            }
        });
    });
})();
</script>
@endsection
