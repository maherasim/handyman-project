<?php
$auth_user= authSession();
?>
{{-- {{ $earningData->id}} --}}
{{ html()->form('DELETE', route('paymentjobrequest.destroy', $payment->id))->attribute('data--submit', 'paymentjobrequest'.$payment->id)->open() }}
<div class="d-flex justify-content-end align-items-center">
@if(auth()->user()->hasAnyRole(['admin']))
    <a class="me-3" href="{{ route('paymentjobrequest.destroy', $payment->id) }}" data--submit="paymentjobrequest{{$payment->id}}" 
        data--confirmation='true' 
        data--ajax="true"
        data-datatable="reload"
        data-title="{{ __('messages.delete_form_title',['form'=>  __('messages.payment job request') ]) }}"
        title="{{ __('messages.delete_form_title',['form'=>  __('messages.payment job request') ]) }}"
        data-message='{{ __("messages.delete_msg") }}'>
        <i class="far fa-trash-alt text-danger"></i>
    </a>
@endif
</div>
{{ html()->form()->close() }}