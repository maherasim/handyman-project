@php
$auth_user = authSession();
// applicants is set by withCount in index_data; fallback for other contexts
$applicantCount = isset($post_job->applicants) ? (int)$post_job->applicants : $post_job->postBidList()->where('status', '!=', 'cancelled')->count();
$hasProposals = $applicantCount > 0;
@endphp
{{ html()->form('DELETE', route('post-job-request.destroy', $post_job->id))->attribute('data--submit', 'post-job-request' . $post_job->id)->open() }}

<div class="d-flex justify-content-end align-items-center">

	{{-- Delete: only for admin, and only when there are no proposals --}}
	@if(auth()->user()->hasAnyRole(['admin']) && !$hasProposals)
		<a class="me-2" href="javascript:void(0)" 
		   data--submit="post-job-request{{ $post_job->id }}" 
		   data--confirmation="true" 
		   data-title="{{ __('messages.delete_form_title',['form'=> __('postJob') ]) }}" 
		   title="{{ __('messages.delete_form_title',['form'=> __('postJob') ]) }}" 
		   data-message="{{ __('messages.delete_msg') }}">
			<i class="far fa-trash-alt text-danger"></i>
		</a>
	@endif

	{{-- Edit: allow owners (user/admin) when status is requested and there are no proposals --}}
	@if(!$hasProposals && (auth()->user()->hasAnyRole(['admin']) || (int)auth()->id() === (int)$post_job->customer_id))
		@if(($post_job->status ?? null) === 'requested')
			<a class="me-2" href="{{ route('post-job-requestjob.edit', $post_job->id) }}" 
			   title="{{ __('messages.update_form_title',['form'=> __('messages.post_job') ]) }}">
				<i class="far fa-edit text-primary"></i>
			</a>
		@endif
	@endif

	{{-- View button: visible only when status is requested --}}
	@if(($post_job->status ?? null) === 'requested')
	<a class="" href="{{ route('post-job-request.show', $post_job->id) }}" 
	   title="{{ __('messages.view_form_title',['form'=> __('messages.postjob') ]) }}">
		<i class="far fa-eye text-secondary me-2"></i>
	</a>
	@endif

	{{-- Provider-specific actions --}}
	@if(auth()->user()->hasAnyRole(['provider']))
		@php
			$providerBid = $post_job->postBidList()
								   ->where('provider_id', auth()->id())
								   ->first();
			$hasProviderBid = $providerBid !== null;
			$providerBidCancelled = $hasProviderBid && strtolower((string)($providerBid->status ?? '')) === 'cancelled';
		@endphp

		{{-- Show BID / UPDATE button only if post is requested/cancelled AND provider's bid is not cancelled --}}
		@if(in_array($post_job->status, ['requested', 'cancelled']) && !$providerBidCancelled)
			<button class="btn btn-success btn-sm bid-button mr-1" 
					style="font-size: 14px; padding: 5px 10px;" 
					type="button" 
					onclick="openBidModal({{ $post_job->id }}, {{ auth()->user()->id }})">
				<i class="fas fa-gavel"></i>
				{{ $hasProviderBid ? 'Update Bid' : 'BID' }}
			</button>

		{{-- Show Start Work button only if status is assigned --}}
		@elseif($post_job->status == 'assigned')
			<button class="btn btn-primary btn-sm start-work-button" 
					style="font-size: 14px; padding: 5px 10px;" 
					type="button" 
					data-post-id="{{ $post_job->id }}">
				<i class="las la-play-circle"></i>
				Start Work
			</button>
		@endif
	@endif
</div>

{{ html()->form()->close() }}

<script>
document.addEventListener('click', function(e) {
	if (e.target.closest('.start-work-button')) {
		const btn = e.target.closest('.start-work-button');
		const postId = btn.getAttribute('data-post-id');

		fetch(`{{ url('post-job-request') }}/${postId}/start-work`, {
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': '{{ csrf_token() }}',
				'Accept': 'application/json'
			}
		}).then(r => r.json()).then(data => {
			if (data.status) {
				window.location.reload();
			} else {
				alert(data.message || 'Unable to start');
			}
		}).catch(() => alert('Unable to start'));
	}
});
</script>