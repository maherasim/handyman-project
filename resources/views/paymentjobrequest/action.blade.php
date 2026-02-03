@php
    $auth_user = auth()->user();
@endphp
<div class="d-flex justify-content-end align-items-center gap-1">
    @if($auth_user->hasAnyRole(['admin', 'demo_admin']))
        @if(strtolower((string)($payment->payment_status ?? '')) === 'pending')
            <a class="btn btn-success btn-sm postjob-verify-btn"
                href="#"
                data-payment-id="{{ $payment->id }}"
                data-approve-url="{{ route('paymentjobrequest.cash.approve') }}"
                data-csrf="{{ csrf_token() }}"
                title="{{ __('messages.approvecash') }}">
                <i class="fa fa-check me-1"></i> {{ __('Verify') }}
            </a>
        @else
            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $payment->payment_status ?? '—')) }}</span>
        @endif
    @endif
</div>
