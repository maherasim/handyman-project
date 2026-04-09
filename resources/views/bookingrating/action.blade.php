
<?php
    $auth_user= authSession();
?>
{{ html()->form('DELETE', route('booking-rating.destroy', $bookingrating->id))->attribute('data--submit', 'bookingrating' . $bookingrating->id)->open() }}
<div class="d-flex justify-content-end align-items-center">
        @if(auth()->check() && auth()->user()->hasRole('handyman'))
            <a class="me-2" href="javascript:void(0)"
               onclick="window.triggerUgcReportReview({{ (int) $bookingrating->id }}, this, 'booking_rating')"
               title="{{ __('messages.ugc_report_title') }}">
                <i class="fas fa-flag text-warning"></i>
            </a>
        @endif
@if(auth()->user()->hasAnyRole(['admin']))
        <a class="me-2" href="{{ route('booking-rating.destroy', $bookingrating->id) }}" data--submit="bookingrating{{$bookingrating->id}}" 
            data--confirmation='true' 
            data--ajax="true"
            data-datatable="reload"
            data-title="{{ __('messages.delete_form_title',['form'=>  __('messages.rating') ]) }}"
            title="{{ __('messages.delete_form_title',['form'=>  __('messages.rating') ]) }}"
            data-message='{{ __("messages.delete_msg") }}'>
            <i class="far fa-trash-alt text-danger"></i>
        </a>
@endif
</div>
{{ html()->form()->close() }}