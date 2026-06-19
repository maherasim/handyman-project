<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-2">
                            <h5 class="fw-bold mb-0">{{ $pageTitle }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body p-0">

                        @if(session('success'))
                            <div class="alert alert-success m-3">{{ session('success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger m-3">{{ $errors->first() }}</div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('messages.handyman') }}</th>
                                        <th>{{ __('messages.provider') }}</th>
                                        <th>{{ __('messages.current_commission') }}</th>
                                        <th>{{ __('messages.requested_commission') }}</th>
                                        <th>{{ __('messages.status') }}</th>
                                        <th>{{ __('messages.helpdesk') }}</th>
                                        <th>{{ __('messages.created_at') }}</th>
                                        <th>{{ __('messages.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $req)
                                        <tr>
                                            <td>{{ $req->id }}</td>
                                            <td>
                                                <a href="{{ route('handyman.detail', $req->handyman_id) }}" class="fw-semibold">
                                                    {{ $req->handyman->display_name ?? '—' }}
                                                </a>
                                            </td>
                                            <td>{{ $req->provider->display_name ?? '—' }}</td>
                                            <td>{{ $req->current_commission }}%</td>
                                            <td class="fw-bold text-primary">{{ $req->requested_commission }}%</td>
                                            <td>
                                                @if($req->status === 'pending')
                                                    <span class="badge bg-warning text-dark">⏳ {{ __('messages.pending') }}</span>
                                                @elseif($req->status === 'approved')
                                                    <span class="badge bg-success">✅ {{ __('messages.approved') }}</span>
                                                @else
                                                    <span class="badge bg-danger">❌ {{ __('messages.rejected') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($req->helpdesk_id)
                                                    <a href="{{ route('helpdesk.show', $req->helpdesk_id) }}" class="btn btn-xs btn-outline-secondary">
                                                        <i class="fas fa-comments"></i> #{{ $req->helpdesk_id }}
                                                    </a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>{{ $req->created_at->format('d M Y') }}</td>
                                            <td>
                                                @if($req->isPending())
                                                    {{-- Approve --}}
                                                    <form method="POST" action="{{ route('commission-request.approve', $req->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                            onclick="return confirm('{{ __('messages.commission_approve_confirm') }}')">
                                                            ✅ {{ __('messages.approve') }}
                                                        </button>
                                                    </form>
                                                    {{-- Reject with notes --}}
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        data-bs-toggle="modal" data-toggle="modal"
                                                        data-bs-target="#rejectModal{{ $req->id }}"
                                                        data-target="#rejectModal{{ $req->id }}">
                                                        ❌ {{ __('messages.reject') }}
                                                    </button>

                                                    {{-- Reject Modal --}}
                                                    <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <form method="POST" action="{{ route('commission-request.reject', $req->id) }}">
                                                                    @csrf
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">{{ __('messages.reject_commission_request') }}</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="mb-3">
                                                                            <p class="text-muted">
                                                                                {{ __('messages.handyman') }}: <strong>{{ $req->handyman->display_name }}</strong><br>
                                                                                {{ __('messages.requested_commission') }}: <strong>{{ $req->requested_commission }}%</strong>
                                                                            </p>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="form-label fw-semibold">{{ __('messages.reason_for_rejection') }}</label>
                                                                            <textarea name="admin_notes" class="form-control" rows="3" required
                                                                                placeholder="{{ __('messages.rejection_reason_placeholder') }}"></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">
                                                                            {{ __('messages.cancel') }}
                                                                        </button>
                                                                        <button type="submit" class="btn btn-danger">
                                                                            {{ __('messages.reject') }}
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($req->isApproved())
                                                    <span class="text-muted small">{{ __('messages.waiting_provider_to_update') }}</span>
                                                @else
                                                    @if($req->admin_notes)
                                                        <span class="text-muted small" title="{{ $req->admin_notes }}">
                                                            💬 {{ \Str::limit($req->admin_notes, 30) }}
                                                        </span>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">
                                                {{ __('messages.no_commission_requests') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="p-3">
                            {{ $requests->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-master-layout>
