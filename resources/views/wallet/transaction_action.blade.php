@if($query->status == 'paid')
<span class="badge badge-active text-success bg-success-subtle">{{ __('messages.paid') }}</span>
@elseif($query->status == 'rejected')
<span class="badge badge-inactive text-danger bg-danger-subtle">{{ __('messages.rejected') }}</span>
@else
<div class="d-flex gap-2 flex-wrap">
    <a href="#" onclick="confirmApprove({{ $query->id }})" class="btn btn-sm btn-success">
        <i class="fas fa-check me-1"></i>{{ __('messages.approve') }}
    </a>
    <a href="#" onclick="confirmReject({{ $query->id }})" class="btn btn-sm btn-danger">
        <i class="fas fa-times me-1"></i>{{ __('messages.reject_refund') }}
    </a>
</div>
@endif

<script>
function confirmApprove(id) {
    Swal.fire({
        title: '{{ __("messages.are_you_sure") }}',
        text: "{{ __('messages.confirm_approve_withdrawal') }}",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '{{ __("messages.yes") }}'
    }).then((result) => {
        if (result.isConfirmed) {
            var url = '{{ route("wallet.wallet_transaction_payout", ":id") }}'.replace(':id', id);
            window.location.href = url;
        }
    });
}

function confirmReject(id) {
    Swal.fire({
        title: '{{ __("messages.are_you_sure") }}',
        text: "{{ __('messages.confirm_reject_withdrawal') }}",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '{{ __("messages.yes_reject_refund") }}'
    }).then((result) => {
        if (result.isConfirmed) {
            var url = '{{ route("wallet.wallet_transaction_reject", ":id") }}'.replace(':id', id);
            window.location.href = url;
        }
    });
}
</script>
