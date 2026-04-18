@php
    $auth_user = authSession();
@endphp
{{ html()->form('POST', route('customer-rating.destroy', $customerrating->id))->attribute('data--submit', 'customerrating' . $customerrating->id)->open() }}
<div class="d-flex justify-content-end align-items-center">
@if(in_array(auth()->user()->user_type, ['admin', 'demo_admin'], true))
        <a class="me-2" href="{{ route('customer-rating.destroy', $customerrating->id) }}" data--submit="customerrating{{ $customerrating->id }}"
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
