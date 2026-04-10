<x-master-layout>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h4 class="card-title mb-0">{{ __('messages.review_report_list_title') }}</h4>
                        <p class="text-muted mb-0 small">{{ __('messages.review_report_index_subtitle') }}</p>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.review_report_col_id') }}</th>
                                    <th>{{ __('messages.review_report_col_reporter') }}</th>
                                    <th>{{ __('messages.review_report_col_review_owner') }}</th>
                                    <th>{{ __('messages.review_report_col_review') }}</th>
                                    <th>{{ __('messages.review_report_col_reason') }}</th>
                                    <th>{{ __('messages.review_report_col_details') }}</th>
                                    <th>{{ __('messages.review_report_col_status') }}</th>
                                    <th>{{ __('messages.review_report_col_date') }}</th>
                                    <th>{{ __('messages.profile_report_col_view') }}</th>
                                    <th>{{ __('messages.review_report_col_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
                                    @php
                                        $reviewItem = $report->resolveReviewItem();
                                        $reviewText = (string) optional($reviewItem)->review;
                                        $isHidden = $report->isReviewHidden();
                                    @endphp
                                    <tr>
                                        <td>{{ $report->id }}</td>
                                        <td>
                                            @if($report->reporter)
                                                <div class="fw-semibold">{{ $report->reporter->display_name ?? $report->reporter->email }}</div>
                                                <div class="small text-muted">{{ $report->reporter->email }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($report->reviewOwner)
                                                <div class="fw-semibold">{{ $report->reviewOwner->display_name ?? $report->reviewOwner->email }}</div>
                                                <div class="small text-muted">{{ $report->reviewOwner->email }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="small">
                                            <span class="badge bg-light text-dark border mb-1">{{ \App\Models\ReviewReport::reviewTypeLabel($report->review_type) }}</span>
                                            <div class="text-muted small mb-1">{{ __('messages.review_report_field_review_ref') }}: <code class="small">{{ $report->review_type }}#{{ $report->review_id }}</code></div>
                                            <div>{{ \Illuminate\Support\Str::limit($reviewText, 90) ?: '—' }}</div>
                                            <div class="mt-1">
                                                <span class="badge {{ $isHidden ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                                                    {{ $isHidden ? __('messages.review_report_visibility_hidden') : __('messages.review_report_visibility_visible') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ \App\Models\ReviewReport::reasonLabel($report->reason) }}</span>
                                        </td>
                                        <td class="small">{{ $report->details ? \Illuminate\Support\Str::limit($report->details, 140) : '—' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ \App\Models\ReviewReport::statusLabel($report->status) }}</span>
                                        </td>
                                        <td>{{ $report->created_at?->format('Y-m-d H:i') }}</td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('admin.review-reports.show', $report) }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                                                <i class="fas fa-eye me-1"></i> {{ __('messages.profile_report_view') }}
                                            </a>
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.review-reports.update', $report) }}" class="d-flex flex-column gap-2" style="min-width: 210px;">
                                                @csrf
                                                <select name="action" class="form-select form-select-sm" required>
                                                    <option value="" disabled selected>{{ __('messages.review_report_action_select') }}</option>
                                                    <option value="dismiss">{{ __('messages.review_report_action_dismiss') }}</option>
                                                    <option value="hide_review" @selected(!$isHidden)>{{ __('messages.review_report_action_hide_review') }}</option>
                                                    <option value="restore_review" @selected($isHidden)>{{ __('messages.review_report_action_restore_review') }}</option>
                                                </select>
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="admin_note" class="form-control" placeholder="{{ __('messages.review_report_admin_note_short_placeholder') }}">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">{{ __('messages.review_report_empty') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $reports->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-master-layout>
