<x-master-layout>
@php
    $r = $contentReport;
    $st = (string) $r->status;
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
    $reasonText = $reasonLabels[$r->reason] ?? ucwords(str_replace('_', ' ', (string) $r->reason));
    $ugcStatusLabels = [
        'pending' => __('messages.ugc_report_status_pending'),
        'dismissed' => __('messages.ugc_report_status_dismissed'),
        'action_taken' => __('messages.ugc_report_status_action_taken'),
        'reviewed' => __('messages.ugc_report_status_reviewed'),
    ];
    $statusLabel = $ugcStatusLabels[$st] ?? ucfirst(str_replace('_', ' ', $st));

    $publicUrl = null;
    $listingTitle = '—';
    if ($r->reportable_type === \App\Models\Service::class && $r->reportable) {
        $publicUrl = route('service.detail', $r->reportable_id);
        $listingTitle = ($r->reportable->name ?? '') !== '' ? (string) $r->reportable->name : ('#'.$r->reportable_id);
    } elseif ($r->reportable_type === \App\Models\PostJobRequest::class && $r->reportable) {
        $publicUrl = route('job.details', $r->reportable_id);
        $listingTitle = ($r->reportable->title ?? '') !== '' ? (string) $r->reportable->title : ('#'.$r->reportable_id);
    } elseif ($r->reportable_type === \App\Models\BookingRating::class) {
        $br = \App\Models\BookingRating::withTrashed()->find($r->reportable_id);
        if ($br && $br->service_id) {
            $publicUrl = route('service.detail', $br->service_id);
        }
        $listingTitle = \Illuminate\Support\Str::limit((string) optional($br)->review, 120) ?: ('#'.$r->reportable_id);
    } elseif ($r->reportable_type === \App\Models\User::class) {
        $publicUrl = route('provider.detail', $r->reportable_id);
        $listingTitle = optional($r->reportable)->display_name
            ?? optional($r->reportable)->email
            ?? ('#'.$r->reportable_id);
    }
@endphp
<style>
    .content-report-detail .report-field-label { font-size: 0.75rem; letter-spacing: .04em; text-transform: uppercase; color: #6c757d; font-weight: 600; margin-bottom: .5rem; }
    .content-report-detail .report-details-box { min-height: 12rem; max-height: 50vh; overflow-y: auto; white-space: pre-wrap; word-break: break-word; line-height: 1.6; }
</style>
<div class="container-fluid content-report-detail">
    <div class="row mb-3">
        <div class="col-12 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('admin.content-reports.index') }}">{{ __('messages.ugc_admin_page_title') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">#{{ $r->id }}</li>
                    </ol>
                </nav>
                <h4 class="mb-0">{{ str_replace(':id', (string) $r->id, __('messages.content_report_detail_title')) }}</h4>
                <p class="text-muted small mb-0">{{ __('messages.content_report_field_submitted') }}: {{ $r->created_at?->format('Y-m-d H:i') }}</p>
            </div>
            <a href="{{ route('admin.content-reports.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> {{ __('messages.content_report_back_list') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="mb-0 text-white"><i class="fas fa-bullhorn me-2"></i>{{ __('messages.content_report_section_listing') }}</h6>
                </div>
                <div class="card-body p-4">
                    <div class="report-field-label">{{ __('messages.ugc_col_listing') }}</div>
                    <p class="fw-semibold mb-2">{{ $listingTitle }}</p>
                    <div class="small text-muted mb-3"><code>{{ class_basename($r->reportable_type) }}</code> #{{ $r->reportable_id }}</div>
                    @if($publicUrl)
                        <a href="{{ $publicUrl }}" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">
                            <i class="fas fa-external-link-alt me-1"></i>{{ __('messages.content_report_open_public') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-secondary text-white py-3">
                    <h6 class="mb-0 text-white"><i class="fas fa-flag me-2"></i>{{ __('messages.ugc_col_reason') }}</h6>
                </div>
                <div class="card-body p-4">
                    <div class="fs-5">{{ $reasonText }}</div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light py-3 border-bottom">
                    <h6 class="mb-0"><i class="fas fa-align-left me-2 text-muted"></i>{{ __('messages.ugc_col_details') }}</h6>
                </div>
                <div class="card-body p-4">
                    <div class="report-details-box text-muted">{{ $r->details ?: '—' }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="report-field-label">{{ __('messages.status') }}</div>
                    <span class="badge bg-secondary">{{ $statusLabel }}</span>
                    @if($r->admin_note)
                        <div class="report-field-label mt-3">{{ __('messages.ugc_admin_note_placeholder') }}</div>
                        <div class="small text-break">{{ $r->admin_note }}</div>
                    @endif
                    @if($r->reviewed_at)
                        <div class="small text-muted mt-2">{{ __('messages.content_report_reviewed_at') }} {{ $r->reviewed_at->format('Y-m-d H:i') }}</div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header py-2 bg-light border-bottom small fw-semibold">{{ __('messages.ugc_col_reporter') }}</div>
                <div class="card-body small">
                    @if($r->reporter)
                        <div class="fw-semibold text-break">{{ $r->reporter->display_name ?? $r->reporter->email }}</div>
                        <div class="text-muted text-break">{{ $r->reporter->email }}</div>
                        <div class="text-muted mt-1">#{{ $r->reporter->id }}</div>
                    @else
                        —
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header py-2 bg-light border-bottom small fw-semibold">{{ __('messages.ugc_col_reported_party') }}</div>
                <div class="card-body small">
                    @if($r->subjectUser)
                        <div class="fw-semibold text-break">{{ $r->subjectUser->display_name ?? $r->subjectUser->email }}</div>
                        <div class="text-muted text-break">{{ $r->subjectUser->email }}</div>
                        <div class="text-muted mt-1">#{{ $r->subjectUser->id }}</div>
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</x-master-layout>
