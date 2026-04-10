<x-master-layout>
<style>
    .report-list-table-wrap { font-size: 0.8125rem; }
    .report-list-table-wrap .table { margin-bottom: 0; }
    .report-list-table-wrap .cell-tight { width: 1%; white-space: nowrap; vertical-align: middle; }
    .report-list-table-wrap .cell-clip { max-width: 10rem; overflow: hidden; }
    .report-list-table-wrap .cell-listing { max-width: 11rem; overflow: hidden; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; line-height: 1.3; word-break: break-word; }
    .report-list-table-wrap .cell-details { max-width: 8rem; overflow: hidden; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; line-height: 1.35; word-break: break-word; }
    .report-list-table-wrap .cell-actions { width: 1%; min-width: 8.5rem; max-width: 10.5rem; vertical-align: top; }
    .report-list-table-wrap .cell-view { width: 1%; min-width: 6.5rem; white-space: nowrap; vertical-align: middle; text-align: center; }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h4 class="card-title mb-0">{{ __('messages.ugc_admin_page_title') }}</h4>
                        <p class="text-muted mb-0 small">{{ __('messages.ugc_admin_page_subtitle') }}</p>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive report-list-table-wrap border rounded">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="cell-tight">{{ __('messages.srno') }}</th>
                                    <th class="cell-listing">{{ __('messages.ugc_col_listing') }}</th>
                                    <th class="cell-clip">{{ __('messages.ugc_col_reporter') }}</th>
                                    <th class="cell-clip">{{ __('messages.ugc_col_reported_party') }}</th>
                                    <th class="cell-clip">{{ __('messages.ugc_col_reason') }}</th>
                                    <th class="cell-details">{{ __('messages.ugc_col_details') }}</th>
                                    <th class="cell-tight">{{ __('messages.status') }}</th>
                                    <th class="cell-tight">{{ __('messages.ugc_col_date') }}</th>
                                    <th class="cell-view">{{ __('messages.profile_report_col_view') }}</th>
                                    <th class="cell-actions">{{ __('messages.ugc_col_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
                                    <tr>
                                        <td class="cell-tight">{{ $report->id }}</td>
                                        <td class="cell-listing small">
                                            @if($report->reportable && $report->reportable_type === \App\Models\Service::class)
                                                <span class="badge bg-light text-dark border mb-1" style="font-size: 0.65rem;">{{ __('messages.service') }}</span>
                                                <strong class="d-block text-truncate" title="{{ $report->reportable->name ?? '—' }}">{{ \Illuminate\Support\Str::limit($report->reportable->name ?? '—', 32) }}</strong>
                                                <div class="text-muted">#{{ $report->reportable_id }}</div>
                                            @elseif($report->reportable && $report->reportable_type === \App\Models\PostJobRequest::class)
                                                <span class="badge bg-light text-dark border mb-1" style="font-size: 0.65rem;">{{ __('messages.post_job_request') }}</span>
                                                <strong class="d-block text-truncate" title="{{ $report->reportable->title ?? '—' }}">{{ \Illuminate\Support\Str::limit($report->reportable->title ?? '—', 32) }}</strong>
                                                <div class="text-muted">#{{ $report->reportable_id }}</div>
                                            @elseif($report->reportable_type === \App\Models\BookingRating::class)
                                                @php
                                                    $reviewItem = \App\Models\BookingRating::withTrashed()->find($report->reportable_id);
                                                @endphp
                                                <span class="badge bg-light text-dark border mb-1" style="font-size: 0.65rem;">Review</span>
                                                <strong class="d-block">{{ \Illuminate\Support\Str::limit((string) optional($reviewItem)->review, 32) ?: '—' }}</strong>
                                                <div class="text-muted">#{{ $report->reportable_id }}</div>
                                            @else
                                                #{{ $report->reportable_id }}
                                            @endif
                                        </td>
                                        <td class="cell-clip small text-truncate" title="{{ $report->reporter ? ($report->reporter->email ?? ($report->reporter->first_name.' '.$report->reporter->last_name)) : '' }}">
                                            @if($report->reporter)
                                                {{ \Illuminate\Support\Str::limit($report->reporter->email ?? ($report->reporter->first_name.' '.$report->reporter->last_name), 26) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="cell-clip small text-truncate" title="{{ $report->subjectUser ? ($report->subjectUser->display_name ?? $report->subjectUser->email) : '' }}">
                                            @if($report->subjectUser)
                                                {{ \Illuminate\Support\Str::limit($report->subjectUser->display_name ?? $report->subjectUser->email, 24) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="cell-clip small">
                                            @php
                                                $reasonLabels = [
                                                    'spam' => 'Spam or misleading',
                                                    'harassment' => 'Harassment or abuse',
                                                    'inappropriate' => 'Inappropriate content',
                                                    'fraud' => 'Scam or fraud',
                                                    'other' => 'Other',
                                                    'off_platform_requests' => 'Off-platform requests',
                                                    'misleading_profile_or_skills' => 'Misleading profile or skills',
                                                    'no_show_or_unreliability' => 'No-show or unreliability',
                                                    'poor_work_quality' => 'Poor work quality',
                                                    'unprofessional_behavior' => 'Unprofessional behavior',
                                                    'fraud_or_misleading_information' => 'Fraud or misleading information',
                                                    'overcharging_or_hidden_fees' => 'Overcharging or hidden fees',
                                                    'incomplete_or_abandoned_work' => 'Incomplete or abandoned work',
                                                    'harassment_or_bullying' => 'Harassment or bullying',
                                                    'spam_or_scams' => 'Spam or scams',
                                                    'hate_speech' => 'Hate speech',
                                                    'violence_or_threats' => 'Violence or threats',
                                                    'payment_issues' => 'Payment issues',
                                                    'overcharging_or_extra_fees' => 'Overcharging or extra fees',
                                                    'violation_of_platform_rules' => 'Violation of platform rules',
                                                    'plagiarism_or_copied_work' => 'Plagiarism or copied work',
                                                    'nudity_or_sexual_content' => 'Nudity or sexual content',
                                                ];
                                                $reasonText = $reasonLabels[$report->reason] ?? ucwords(str_replace('_', ' ', (string) $report->reason));
                                            @endphp
                                            <span title="{{ $reasonText }}">{{ \Illuminate\Support\Str::limit($reasonText, 38) }}</span>
                                        </td>
                                        <td class="cell-details small" title="{{ $report->details }}">{{ $report->details ? \Illuminate\Support\Str::limit($report->details, 70) : '—' }}</td>
                                        <td class="cell-tight">
                                            @php
                                                $ugcStatusLabels = [
                                                    'pending' => __('messages.ugc_report_status_pending'),
                                                    'dismissed' => __('messages.ugc_report_status_dismissed'),
                                                    'action_taken' => __('messages.ugc_report_status_action_taken'),
                                                    'reviewed' => __('messages.ugc_report_status_reviewed'),
                                                ];
                                                $statusLabel = $ugcStatusLabels[$report->status] ?? ucfirst(str_replace('_', ' ', (string) $report->status));
                                            @endphp
                                            <span class="badge bg-secondary" style="font-size: 0.7rem;">{{ $statusLabel }}</span>
                                        </td>
                                        <td class="cell-tight small text-muted">{{ $report->created_at?->format('Y-m-d') }}<br>{{ $report->created_at?->format('H:i') }}</td>
                                        <td class="cell-view">
                                            <a href="{{ route('admin.content-reports.show', $report) }}" class="btn btn-sm btn-outline-primary px-2 py-1 text-nowrap" target="_blank" rel="noopener" title="{{ __('messages.profile_report_view') }}">
                                                <i class="fas fa-eye me-1"></i>{{ __('messages.profile_report_view') }}
                                            </a>
                                        </td>
                                        <td class="cell-actions">
                                            <form method="POST" action="{{ route('admin.content-reports.update', $report) }}" class="d-flex flex-column gap-1">
                                                @csrf
                                                @php
                                                    $isHiddenFromPublic = (bool) ($report->reportable->is_hidden_from_public ?? false);
                                                    $reviewItem = null;
                                                    $isReviewHidden = false;
                                                    if ($report->reportable_type === \App\Models\BookingRating::class) {
                                                        $reviewItem = \App\Models\BookingRating::withTrashed()->find($report->reportable_id);
                                                        $isReviewHidden = (bool) ((int) (optional($reviewItem)->status ?? 0) === 1 || optional($reviewItem)->trashed());
                                                    }
                                                @endphp
                                                <select name="action" class="form-select form-select-sm" required>
                                                    <option value="" disabled selected>{{ __('messages.action') }}...</option>
                                                    <option value="dismiss" class="text-secondary">{{ __('messages.ugc_action_dismiss') }}</option>
                                                    @if($report->reportable_type === \App\Models\Service::class)
                                                        <option value="hide_service" class="text-warning fw-bold" @selected(!$isHiddenFromPublic)>{{ __('messages.ugc_action_hide_service') }}</option>
                                                        <option value="restore_service" class="text-success" @selected($isHiddenFromPublic)>{{ __('messages.ugc_action_restore_service') }}</option>
                                                    @elseif($report->reportable_type === \App\Models\PostJobRequest::class)
                                                        <option value="hide_post_job" class="text-warning fw-bold" @selected(!$isHiddenFromPublic)>{{ __('messages.ugc_action_hide_post_job') }}</option>
                                                        <option value="restore_post_job" class="text-success" @selected($isHiddenFromPublic)>{{ __('messages.ugc_action_restore_post_job') }}</option>
                                                    @elseif($report->reportable_type === \App\Models\BookingRating::class)
                                                        <option value="hide_review" class="text-warning fw-bold" @selected(!$isReviewHidden)>Hide review</option>
                                                        <option value="restore_review" class="text-success" @selected($isReviewHidden)>Restore review</option>
                                                    @endif
                                                </select>
                                                
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="admin_note" class="form-control" placeholder="{{ __('messages.ugc_admin_note_placeholder') }}">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">{{ __('messages.ugc_admin_empty') }}</td>
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
