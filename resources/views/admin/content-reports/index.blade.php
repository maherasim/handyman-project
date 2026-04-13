<x-master-layout>
<style>
    /* Align with post-job-request / dashboard tables */
    .content-reports-page .table thead th {
        background: #3333ff !important;
        color: #fff !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        font-weight: 600;
        font-size: 0.8125rem;
        white-space: nowrap;
        vertical-align: middle;
        padding: 0.65rem 0.75rem;
    }
    .content-reports-page .table tbody td {
        vertical-align: middle;
        font-size: 0.8125rem;
        padding: 0.65rem 0.75rem;
    }
    .content-reports-page .table-striped > tbody > tr:nth-of-type(odd) > * {
        --bs-table-accent-bg: rgba(51, 51, 255, 0.04);
    }
    .content-reports-page .table {
        --bs-table-border-color: #dee2e6;
    }
    {{-- Listing / advertisement column (hidden)
    .content-reports-page .listing-cell {
        min-width: 10rem;
        max-width: 14rem;
    }
    .content-reports-page .listing-cell .listing-title-preview {
        display: block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    --}}
    .content-reports-page .text-cell {
        min-width: 9rem;
        max-width: 14rem;
    }
    .content-reports-page .details-cell {
        min-width: 10rem;
        max-width: 16rem;
    }
    .content-reports-page .view-cell {
        width: 1%;
        min-width: 6.5rem;
        white-space: nowrap;
        text-align: center;
        vertical-align: middle;
    }
    .content-reports-page .actions-cell {
        min-width: 16rem;
    }
    .content-reports-page .actions-cell form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }
    .content-reports-page .actions-cell .form-select-sm {
        min-width: 11rem;
        max-width: 15rem;
    }
    .content-reports-page .actions-cell .admin-note-input {
        min-width: 8rem;
        max-width: 14rem;
        flex: 1 1 8rem;
    }
</style>
<div class="container-fluid content-reports-page">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 p-3">
                        <div>
                            <h5 class="font-weight-bold mb-0">{{ __('messages.ugc_admin_page_title') }}</h5>
                            <p class="text-muted small mb-0 mt-1">{{ __('messages.ugc_admin_page_subtitle') }}</p>
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
                            <th>{{ __('messages.srno') }}</th>
                            {{-- <th class="listing-cell">{{ __('messages.ugc_col_listing') }}</th> --}}
                            <th class="text-cell">{{ __('messages.ugc_col_reporter') }}</th>
                            <th class="text-cell">{{ __('messages.ugc_col_reported_party') }}</th>
                            <th class="text-cell">{{ __('messages.ugc_col_reason') }}</th>
                            <th class="details-cell">{{ __('messages.ugc_col_details') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.ugc_col_date') }}</th>
                            <th class="view-cell">{{ __('messages.profile_report_col_view') }}</th>
                            <th class="actions-cell">{{ __('messages.ugc_col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
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
                                $ugcStatusLabels = [
                                    'pending' => __('messages.ugc_report_status_pending'),
                                    'dismissed' => __('messages.ugc_report_status_dismissed'),
                                    'action_taken' => __('messages.ugc_report_status_action_taken'),
                                    'reviewed' => __('messages.ugc_report_status_reviewed'),
                                ];
                                $statusLabel = $ugcStatusLabels[$report->status] ?? ucfirst(str_replace('_', ' ', (string) $report->status));
                                $isHiddenFromPublic = (bool) (optional($report->reportable)->is_hidden_from_public ?? false);
                                $reviewItem = null;
                                $isReviewHidden = false;
                                if ($report->reportable_type === \App\Models\BookingRating::class) {
                                    $reviewItem = \App\Models\BookingRating::withTrashed()->find($report->reportable_id);
                                    $isReviewHidden = (bool) ((int) (optional($reviewItem)->status ?? 0) === 1 || optional($reviewItem)->trashed());
                                }
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $report->id }}</td>
                                {{--
                                <td class="listing-cell">
                                    @if($report->reportable && $report->reportable_type === \App\Models\Service::class)
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="badge bg-primary text-white align-self-start">{{ __('messages.service') }}</span>
                                            <div class="min-w-0">
                                                @php $svcTitle = (string) ($report->reportable->name ?? '—'); @endphp
                                                <div class="fw-semibold listing-title-preview" title="{{ $svcTitle }}">{{ \Illuminate\Support\Str::words($svcTitle, 2, '…') }}</div>
                                                <small class="text-muted">#{{ $report->reportable_id }}</small>
                                            </div>
                                        </div>
                                    @elseif($report->reportable && $report->reportable_type === \App\Models\PostJobRequest::class)
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="badge bg-primary text-white align-self-start">{{ __('messages.post_job_request') }}</span>
                                            <div class="min-w-0">
                                                @php $jobTitle = (string) ($report->reportable->title ?? '—'); @endphp
                                                <div class="fw-semibold listing-title-preview" title="{{ $jobTitle }}">{{ \Illuminate\Support\Str::words($jobTitle, 2, '…') }}</div>
                                                <small class="text-muted">#{{ $report->reportable_id }}</small>
                                            </div>
                                        </div>
                                    @elseif($report->reportable_type === \App\Models\BookingRating::class)
                                        @php
                                            $reviewItemRow = \App\Models\BookingRating::withTrashed()->find($report->reportable_id);
                                        @endphp
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="badge bg-secondary align-self-start">Review</span>
                                            <div class="min-w-0">
                                                @php $revText = trim((string) optional($reviewItemRow)->review) ?: '—'; @endphp
                                                <div class="small listing-title-preview" title="{{ $revText }}">{{ \Illuminate\Support\Str::words($revText, 2, '…') }}</div>
                                                <small class="text-muted">#{{ $report->reportable_id }}</small>
                                            </div>
                                        </div>
                                    @elseif($report->reportable && $report->reportable_type === \App\Models\User::class)
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="badge bg-info text-dark align-self-start">{{ __('messages.provider') }}</span>
                                            <div class="min-w-0">
                                                @php $partyLabel = (string) ($report->reportable->display_name ?? $report->reportable->email ?? '—'); @endphp
                                                <div class="fw-semibold listing-title-preview" title="{{ $partyLabel }}">{{ \Illuminate\Support\Str::words($partyLabel, 2, '…') }}</div>
                                                <small class="text-muted">#{{ $report->reportable_id }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">{{ class_basename($report->reportable_type) }} #{{ $report->reportable_id }}</span>
                                    @endif
                                </td>
                                --}}
                                <td class="text-cell small">
                                    @if($report->reporter)
                                        <div class="fw-semibold text-truncate" title="{{ $report->reporter->display_name ?? $report->reporter->email }}">{{ \Illuminate\Support\Str::limit($report->reporter->display_name ?? $report->reporter->email, 28) }}</div>
                                        <div class="text-muted text-truncate" title="{{ $report->reporter->email }}">{{ \Illuminate\Support\Str::limit($report->reporter->email ?? '—', 32) }}</div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-cell small">
                                    @if($report->subjectUser)
                                        <div class="fw-semibold text-truncate" title="{{ $report->subjectUser->display_name ?? $report->subjectUser->email }}">{{ \Illuminate\Support\Str::limit($report->subjectUser->display_name ?? $report->subjectUser->email, 28) }}</div>
                                        <div class="text-muted text-truncate" title="{{ $report->subjectUser->email }}">{{ \Illuminate\Support\Str::limit($report->subjectUser->email ?? '—', 32) }}</div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-cell small" title="{{ $reasonText }}">
                                    {{ \Illuminate\Support\Str::limit($reasonText, 42) }}
                                </td>
                                <td class="details-cell small text-muted" title="{{ $report->details }}">
                                    {{ $report->details ? \Illuminate\Support\Str::limit($report->details, 80) : '—' }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $statusLabel }}</span>
                                </td>
                                <td class="small text-muted text-nowrap">
                                    {{ $report->created_at?->format('Y-m-d') }}<br>{{ $report->created_at?->format('H:i') }}
                                </td>
                                <td class="view-cell">
                                    <a href="{{ route('admin.content-reports.show', $report) }}" class="btn btn-sm btn-outline-primary text-nowrap" target="_blank" rel="noopener" title="{{ __('messages.profile_report_view') }}">
                                        <i class="fas fa-eye me-1"></i>{{ __('messages.profile_report_view') }}
                                    </a>
                                </td>
                                <td class="actions-cell">
                                        <form method="POST" action="{{ route('admin.content-reports.update', $report) }}">
                                        @csrf
                                        <select name="action" class="form-select form-select-sm" required>
                                            <option value="" disabled selected>{{ __('messages.action') }}…</option>
                                            <option value="dismiss">{{ __('messages.ugc_action_dismiss') }}</option>
                                            @if($report->reportable_type === \App\Models\Service::class)
                                                <option value="hide_service" @selected(!$isHiddenFromPublic)>{{ __('messages.ugc_action_hide_service') }}</option>
                                                <option value="restore_service" @selected($isHiddenFromPublic)>{{ __('messages.ugc_action_restore_service') }}</option>
                                            @elseif($report->reportable_type === \App\Models\PostJobRequest::class)
                                                <option value="hide_post_job" @selected(!$isHiddenFromPublic)>{{ __('messages.ugc_action_hide_post_job') }}</option>
                                                <option value="restore_post_job" @selected($isHiddenFromPublic)>{{ __('messages.ugc_action_restore_post_job') }}</option>
                                            @elseif($report->reportable_type === \App\Models\BookingRating::class)
                                                <option value="hide_review" @selected(!$isReviewHidden)>Hide review</option>
                                                <option value="restore_review" @selected($isReviewHidden)>Restore review</option>
                                            @endif
                                        </select>
                                        <input type="text" name="admin_note" class="form-control form-control-sm admin-note-input" placeholder="{{ __('messages.ugc_admin_note_placeholder') }}">
                                        <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                                            <i class="fas fa-check me-1"></i>{{ __('messages.apply') }}
                                        </button>
                                        </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">{{ __('messages.ugc_admin_empty') }}</td>
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
