
<?php
    $auth_user = authSession();
?>
<div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
    @if ((int) auth()->id() === (int) ($handymanrating->handyman_id ?? 0))
        <button type="button" class="btn btn-outline-danger btn-sm"
            title="{{ __('messages.ugc_report') }}"
            onclick="if (window.triggerUgcReportReview) { window.triggerUgcReportReview({{ (int) $handymanrating->id }}, this, 'handyman_rating'); } else { alert('Report unavailable'); }">
            <i class="fas fa-flag me-1"></i>{{ __('messages.ugc_report') }}
        </button>
    @endif
    @if (auth()->user()->hasAnyRole(['admin']))
        {{ html()->form('DELETE', route('handyman-rating.destroy', $handymanrating->id))->attribute('data--submit', 'handymanrating' . $handymanrating->id)->open() }}
        <div class="d-flex justify-content-end align-items-center">
            <a class="me-2" href="{{ route('handyman-rating.destroy', $handymanrating->id) }}" data--submit="handymanrating{{ $handymanrating->id }}"
                data--confirmation='true'
                data--ajax="true"
                data-datatable="reload"
                data-title="{{ __('messages.delete_form_title', ['form' => __('messages.rating')]) }}"
                title="{{ __('messages.delete_form_title', ['form' => __('messages.rating')]) }}"
                data-message='{{ __('messages.delete_msg') }}'>
                <i class="far fa-trash-alt text-danger"></i>
            </a>
        </div>
        {{ html()->form()->close() }}
    @endif
</div>
