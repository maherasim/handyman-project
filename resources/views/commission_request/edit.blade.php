<x-master-layout>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <h5 class="fw-bold mb-0">{{ $pageTitle }}</h5>
                            <a href="{{ route('handyman.detail', $handyman->id) }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">

                        {{-- Approval banner --}}
                        <div class="alert alert-success d-flex align-items-center gap-2">
                            <i class="fas fa-check-circle fa-lg"></i>
                            <div>
                                <strong>{{ __('messages.commission_request_approved') }}</strong><br>
                                <span class="small">{{ __('messages.commission_approved_info') }}</span>
                            </div>
                        </div>

                        {{-- Summary --}}
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="p-3 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;">
                                    <p class="text-muted small mb-1">{{ __('messages.current_commission') }}</p>
                                    <h4 class="mb-0 text-danger">{{ $commissionRequest->current_commission }}%</h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                    <p class="text-muted small mb-1">{{ __('messages.approved_commission') }}</p>
                                    <h4 class="mb-0 text-success">{{ $commissionRequest->requested_commission }}%</h4>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('commission-request.update', $commissionRequest->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="form-group mb-4">
                                <label class="form-label fw-semibold">
                                    {{ __('messages.new_commission') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="handyman_commission" class="form-control form-control-lg"
                                    value="{{ $commissionRequest->requested_commission }}"
                                    min="1" max="99" step="0.01" required>
                                <small class="text-muted">
                                    {{ __('messages.commission_range_hint') }} —
                                    {{ __('messages.approved_max_note', ['max' => $commissionRequest->requested_commission]) }}
                                </small>
                            </div>

                            @if($commissionRequest->helpdesk_id)
                                <div class="alert alert-info small">
                                    💬 {{ __('messages.view_discussion') }}:
                                    <a href="{{ route('helpdesk.show', $commissionRequest->helpdesk_id) }}" target="_blank">
                                        {{ __('messages.helpdesk') }} #{{ $commissionRequest->helpdesk_id }}
                                    </a>
                                </div>
                            @endif

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-save me-2"></i>{{ __('messages.save_commission') }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-master-layout>
