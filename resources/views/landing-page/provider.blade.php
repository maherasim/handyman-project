@extends('landing-page.layouts.default')


@section('content')
<div class="blog-list section-padding ">
    <div class="container">
        {{-- How it works & Explore how we post --}}
        <div class="row mb-4 align-items-center">
            <div class="col-12">
                <div class="d-flex flex-wrap align-items-center gap-3 gap-md-4">
                    <a href="{{ url('pages/how-it-works-for-customer') }}" class="btn btn-outline-primary btn-sm text-nowrap">
                        <i class="ri-information-line me-1"></i>{{ __('landingpage.how_it_works') }}
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm text-nowrap">
                        <i class="ri-file-add-line me-1"></i>{{ __('landingpage.post_job_request') }}
                    </a>
                    <span class="text-muted small">{{ __('landingpage.how_it_works_lead') }}</span>
                </div>
            </div>
        </div>
        <provider-page link="{{ route('provider.data') }}" search-placeholder="{{ __('landingpage.pl_search_placeholder') }}"></provider-page>
    </div>
</div>

<!-- Provider Reviews Modal -->
<div class="modal fade" id="providerReviewsModal" tabindex="-1" aria-labelledby="providerReviewsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="providerReviewsModalLabel">{{ __('landingpage.pl_provider_reviews') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('landingpage.pl_close') }}"></button>
            </div>
            <div class="modal-body" id="providerReviewsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">{{ __('landingpage.pl_loading') }}</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('landingpage.pl_close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after_script')
<script>
(function() {
    var shareCopyMsg = {!! json_encode(__('landingpage.share_link_copied_instagram')) !!};
    window.__shareCopyToast = function() {
        var msg = document.createElement('div');
        msg.setAttribute('role', 'status');
        msg.className = 'share-copy-toast';
        msg.textContent = shareCopyMsg;
        msg.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#333;color:#fff;padding:10px 20px;border-radius:8px;font-size:14px;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,0.2);';
        document.body.appendChild(msg);
        setTimeout(function() { if (msg.parentNode) msg.parentNode.removeChild(msg); }, 2500);
    };
})();
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
        // Instagram has no share URL like Facebook/LinkedIn. Use native Share so user can pick Instagram (share sheet → tap Instagram).
        var quote = (el.getAttribute('data-quote') || '').trim();
        var shareTitle = quote ? quote.split('|')[0].trim() : (document.title || 'Provider');
        var textToCopy = quote ? quote + ' ' + shareUrl : shareUrl;
        if (navigator.share) {
            navigator.share({
                title: shareTitle,
                text: quote || shareTitle,
                url: shareUrl
            }).then(function() {
                if (typeof window.__shareCopyToast === 'function') {
                    var msg = document.createElement('div');
                    msg.setAttribute('role', 'status');
                    msg.className = 'share-copy-toast';
                    msg.textContent = 'Shared! Open Instagram to post if needed.';
                    msg.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#333;color:#fff;padding:10px 20px;border-radius:8px;font-size:14px;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,0.2);';
                    document.body.appendChild(msg);
                    setTimeout(function() { if (msg.parentNode) msg.parentNode.removeChild(msg); }, 2000);
                }
            }).catch(function(err) {
                if (err && err.name !== 'AbortError') copyAndNotify();
            });
        } else {
            copyAndNotify();
        }
        function copyAndNotify() {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(textToCopy).then(function() {
                    if (typeof window.__shareCopyToast === 'function') window.__shareCopyToast();
                }).catch(function() { fallbackCopy(textToCopy); });
            } else {
                fallbackCopy(textToCopy);
            }
        }
        function fallbackCopy(str) {
            var ta = document.createElement('textarea');
            ta.value = str;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed'; ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            try {
                document.execCommand('copy');
                if (typeof window.__shareCopyToast === 'function') window.__shareCopyToast();
            } catch (err) {}
            if (ta.parentNode) ta.parentNode.removeChild(ta);
        }
    }
    return false;
};
document.addEventListener('DOMContentLoaded', function() {
    // Event delegation: social share (Facebook, Twitter, LinkedIn) for provider cards loaded via AJAX
    document.addEventListener('click', function(e) {
        var el = e.target.closest && e.target.closest('.share-link');
        if (el && typeof window.__shareClickHandler === 'function') {
            window.__shareClickHandler(e, el);
        }
    });
    // Handle provider rating link clicks
    $(document).on('click', '.provider-rating-link', function(e) {
        e.preventDefault();
        var providerId = $(this).data('provider-id');
        var rating = $(this).data('rating');
        var reviewsCount = $(this).data('reviews-count');
        
        // Show modal
        $('#providerReviewsModal').modal('show');
        
        // Load reviews
        loadProviderReviews(providerId);
    });
    
    function loadProviderReviews(providerId) {
        var baseUrl = "{{ url('/') }}";
        var csrfToken = "{{ csrf_token() }}";
        
        $('#providerReviewsContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="sr-only">' + @json(__('landingpage.pl_loading')) + '</span></div></div>');
        
        $.ajax({
            url: baseUrl + '/api/provider-reviews',
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                provider_id: providerId
            },
            success: function(response) {
                var html = '';
                
                if (response.data && response.data.length > 0) {
                    // Calculate average rating
                    var totalRating = 0;
                    response.data.forEach(function(review) {
                        totalRating += parseFloat(review.rating || 0);
                    });
                    var avgRating = (totalRating / response.data.length).toFixed(1);
                    
                    html += '<div class="mb-4">';
                    html += '<h6>' + @json(__('landingpage.pl_average_rating')) + ': ' + avgRating + ' / 5.0</h6>';
                    html += '<div class="mb-2">';
                    for (var i = 1; i <= 5; i++) {
                        if (i <= Math.floor(avgRating)) {
                            html += '<i class="fas fa-star text-warning"></i>';
                        } else if (i === Math.ceil(avgRating) && avgRating % 1 !== 0) {
                            html += '<i class="fas fa-star-half-alt text-warning"></i>';
                        } else {
                            html += '<i class="far fa-star text-muted"></i>';
                        }
                    }
                    html += '</div>';
                    html += '<p class="text-muted">' + @json(__('landingpage.pl_based_on')) + ' ' + response.data.length + ' ' + (response.data.length === 1 ? @json(__('messages.review')) : @json(__('messages.reviews'))) + '</p>';
                    html += '</div>';
                    
                    html += '<hr>';
                    html += '<div class="reviews-list">';
                    response.data.forEach(function(review) {
                        html += '<div class="review-item mb-4 p-3 border rounded">';
                        html += '<div class="d-flex justify-content-between align-items-start mb-2">';
                        html += '<div>';
                        html += '<strong>' + (review.customer_name || @json(__('landingpage.pl_anonymous'))) + '</strong>';
                        if (review.service_name) {
                            html += '<div class="text-muted small">' + @json(__('landingpage.pl_service')) + ': ' + review.service_name + '</div>';
                        }
                        html += '<div class="mt-1">';
                        for (var j = 1; j <= 5; j++) {
                            if (j <= review.rating) {
                                html += '<i class="fas fa-star text-warning"></i>';
                            } else {
                                html += '<i class="far fa-star text-muted"></i>';
                            }
                        }
                        html += ' <span class="ms-2">' + review.rating + ' / 5.0</span>';
                        html += '</div>';
                        html += '</div>';
                        if (review.created_at) {
                            html += '<small class="text-muted">' + new Date(review.created_at).toLocaleDateString() + '</small>';
                        }
                        html += '</div>';
                        if (review.review) {
                            html += '<p class="mb-0 mt-2">' + review.review + '</p>';
                        }
                        html += '</div>';
                    });
                    html += '</div>';
                } else {
                    html += '<div class="alert alert-info text-center">' + @json(__('landingpage.pl_no_reviews_available')) + '</div>';
                }
                
                $('#providerReviewsContent').html(html);
            },
            error: function(xhr) {
                console.error('Error loading reviews:', xhr);
                $('#providerReviewsContent').html('<div class="alert alert-danger">' + @json(__('landingpage.pl_failed_load_reviews')) + '</div>');
            }
        });
    }
});
</script>
@endsection
