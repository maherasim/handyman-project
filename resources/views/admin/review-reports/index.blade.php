<x-master-layout>
<style>
    .review-reports-page .table thead th {
        background: #3333ff !important;
        color: #fff !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        font-weight: 600;
        font-size: 0.8125rem;
        white-space: nowrap;
        vertical-align: middle;
        padding: 0.65rem 0.75rem;
    }
    .review-reports-page .table tbody td {
        vertical-align: middle;
        font-size: 0.8125rem;
        padding: 0.65rem 0.75rem;
    }
    .review-reports-page .table-striped > tbody > tr:nth-of-type(odd) > * {
        --bs-table-accent-bg: rgba(51, 51, 255, 0.04);
    }
    .review-reports-page .table {
        --bs-table-border-color: #dee2e6;
    }
    .review-reports-page .text-cell {
        min-width: 9rem;
        max-width: 14rem;
    }
    .review-reports-page .details-cell {
        min-width: 10rem;
        max-width: 16rem;
    }
    .review-reports-page .view-cell {
        width: 1%;
        min-width: 6.5rem;
        white-space: nowrap;
        text-align: center;
        vertical-align: middle;
    }
    .review-reports-page .actions-cell {
        min-width: 16rem;
    }
    .review-reports-page .actions-cell form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }
    .review-reports-page .actions-cell .form-select-sm {
        min-width: 11rem;
        max-width: 15rem;
    }
    .review-reports-page .actions-cell .admin-note-input {
        min-width: 8rem;
        max-width: 14rem;
        flex: 1 1 8rem;
    }
</style>
<div class="container-fluid review-reports-page">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 p-3">
                        <div>
                            <h5 class="font-weight-bold mb-0">{{ __('messages.review_report_list_title') }}</h5>
                            <p class="text-muted small mb-0 mt-1">{{ __('messages.review_report_index_subtitle') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped border mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('messages.review_report_col_id') }}</th>
                            <th class="text-cell">{{ __('messages.review_report_col_reporter') }}</th>
                            <th class="text-cell">{{ __('messages.review_report_col_review_owner') }}</th>
                            <th class="text-cell">{{ __('messages.review_report_col_reason') }}</th>
                            <th class="details-cell">{{ __('messages.review_report_col_details') }}</th>
                            <th>{{ __('messages.review_report_col_status') }}</th>
                            <th>{{ __('messages.review_report_col_date') }}</th>
                            <th class="view-cell">{{ __('messages.profile_report_col_view') }}</th>
                            <th class="actions-cell">{{ __('messages.review_report_col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            @php
                                $isHidden = $report->isReviewHidden();
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $report->id }}</td>
                                <td class="text-cell small">
                                    @if($report->reporter)
                                        <div class="fw-semibold text-truncate" title="{{ $report->reporter->display_name ?? $report->reporter->email }}">{{ \Illuminate\Support\Str::limit($report->reporter->display_name ?? $report->reporter->email, 28) }}</div>
                                        <div class="text-muted text-truncate" title="{{ $report->reporter->email }}">{{ \Illuminate\Support\Str::limit($report->reporter->email, 32) }}</div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-cell small">
                                    @if($report->reviewOwner)
                                        <div class="fw-semibold text-truncate" title="{{ $report->reviewOwner->display_name ?? $report->reviewOwner->email }}">{{ \Illuminate\Support\Str::limit($report->reviewOwner->display_name ?? $report->reviewOwner->email, 28) }}</div>
                                        <div class="text-muted text-truncate" title="{{ $report->reviewOwner->email }}">{{ \Illuminate\Support\Str::limit($report->reviewOwner->email, 32) }}</div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-cell small" title="{{ \App\Models\ReviewReport::reasonLabel($report->reason) }}">
                                    {{ \Illuminate\Support\Str::limit(\App\Models\ReviewReport::reasonLabel($report->reason), 42) }}
                                </td>
                                <td class="details-cell small text-muted" title="{{ $report->details }}">
                                    {{ $report->details ? \Illuminate\Support\Str::limit($report->details, 80) : '—' }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ \App\Models\ReviewReport::statusLabel($report->status) }}</span>
                                </td>
                                <td class="small text-muted text-nowrap">
                                    {{ $report->created_at?->format('Y-m-d') }}<br>{{ $report->created_at?->format('H:i') }}
                                </td>
                                <td class="view-cell">
                                    <a href="{{ route('admin.review-reports.show', $report) }}" class="btn btn-sm btn-outline-primary text-nowrap" target="_blank" rel="noopener" title="{{ __('messages.profile_report_view') }}">
                                        <i class="fas fa-eye me-1"></i>{{ __('messages.profile_report_view') }}
                                    </a>
                                </td>
                                <td class="actions-cell">
                                    <form method="POST" action="{{ route('admin.review-reports.update', $report) }}">
                                        @csrf
                                        <select name="action" class="form-select form-select-sm" required>
                                            <option value="" disabled selected>{{ __('messages.review_report_action_select') }}</option>
                                            <option value="dismiss">{{ __('messages.review_report_action_dismiss') }}</option>
                                            <option value="hide_review" @selected(!$isHidden)>{{ __('messages.review_report_action_hide_review') }}</option>
                                            <option value="restore_review" @selected($isHidden)>{{ __('messages.review_report_action_restore_review') }}</option>
                                        </select>
                                        <input type="text" name="admin_note" class="form-control form-control-sm admin-note-input" placeholder="{{ __('messages.review_report_admin_note_short_placeholder') }}">
                                        <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                                            <i class="fas fa-check me-1"></i>{{ __('messages.apply') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">{{ __('messages.review_report_empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                {{ $reports->links() }}
            </div>
        </div>
    </div>
</div>
</x-master-layout>
