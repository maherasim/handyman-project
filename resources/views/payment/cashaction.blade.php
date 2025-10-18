<?php
$auth_user= authSession();
?>
{{ html()->form('DELETE', route('payment.destroy', $payment->id))->attribute('data--submit', 'payment'.$payment->id)->open() }}
    <div class="d-flex justify-content-end align-items-center">
        @php
            $payment_status_check = App\Models\PaymentHistory::where('payment_id',$payment->id)->orderBy('datetime','desc')->first();
        @endphp

        {{-- Verify Button - Only show for pending payments --}}
        @if($payment_status_check !== null && $payment_status_check->status == 'pending_by_admin') 
            <a class="btn btn-success btn-sm me-2" href="{{ route('cash.approve', $payment->id) }}" onclick="return confirm('Are you sure you want to verify this payment?')">
                <i class="fa fa-check"></i> Verify
            </a>
        @endif

        
    </div>
{{ html()->form()->close() }}