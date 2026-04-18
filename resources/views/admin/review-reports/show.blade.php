<x-master-layout>
@php
    $r = $reviewReport;
    $reviewItem = $r->resolveReviewItem();
    $reviewText = (string) optional($reviewItem)->review;
    $reviewRating = $reviewItem ? (optional($reviewItem)->rating ?? null) : null;
    $isHidden = $r->isReviewHidden();
    $st = (string) $r->status;
@endphp
<style>
    .review-report-main-card .report-field-label { font-size: 0.75rem; letter-spacing: .04em; text-transform: uppercase; color: #6c757d; font-weight: 600; margin-bottom: .5rem; }
    .review-report-main-card .report-reason-box { min-height: 5rem; font-size: 1.15rem; line-height: 1.5; word-break: break-word; }
    .review-report-main-card .report-details-box { min-height: 12rem; max-height: 50vh; overflow-y: auto; white-space: pre-wrap; word-break: break-word; line-height: 1.6; }
    .review-report-main-card .report-review-text-box { min-height: 10rem; max-height: 45vh; overflow-y: auto; white-space: pre-wrap; word-break: break-word; line-height: 1.6; font-size: 1.05rem; }
    @media (min-width: 992px) {
        .review-report-main-card .report-details-box { min-height: 16rem; max-height: 55vh; }
        .review-report-main-card .report-review-text-box { min-height: 12rem; max-height: 50vh; }
    }
</style>
<div class="container-fluid review-report-detail">
    <div class="row mb-3">
        <div class="col-12 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('admin.review-reports.index') }}">{{ __('messages.review_report_list_title') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">#{{ $r->id }}</li>
                    </ol>
                </nav>
                <h4 class="mb-0">{{ str_replace(':id', (string) $r->id, __('messages.review_report_detail_title')) }}</h4>
                <p class="text-muted small mb-0">{{ __('messages.review_report_field_submitted') }}: {{ $r->created_at?->format('Y-m-d H:i') }}</p>
            </div>
            <a href="{{ route('admin.review-reports.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> {{ __('messages.review_report_back_list') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8 col-xl-9">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-primary text-white py-3">
                            <h6 class="mb-0 text-white"><i class="fas fa-user-edit me-2"></i>{{ __('messages.review_report_section_reporter') }}</h6>
                        </div>
                        <div class="card-body p-4">
                            @if($r->reporter)
                                <div class="d-flex align-items-start gap-3">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center flex-shrink-0" style="width:56px;height:56px;">
                                        <i class="fas fa-user text-primary fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold text-break">{{ $r->reporter->display_name ?? $r->reporter->email }}</div>
                                        <div class="small text-muted text-break">{{ $r->reporter->email }}</div>
                                        <div class="small mt-2"><span class="text-muted">{{ __('messages.review_report_field_user_id') }}:</span> #{{ $r->reporter->id }}</div>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted mb-0">—</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-dark text-white py-3">
                            <h6 class="mb-0 text-white"><i class="fas fa-pen me-2"></i>{{ __('messages.review_report_section_review_owner') }}</h6>
                        </div>
                        <div class="card-body p-4">
                            @if($r->reviewOwner)
                                <div class="d-flex align-items-start gap-3">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center flex-shrink-0" style="width:56px;height:56px;">
                                        <i class="fas fa-id-badge text-dark fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold text-break">{{ $r->reviewOwner->display_name ?? $r->reviewOwner->email }}</div>
                                        <div class="small text-muted text-break">{{ $r->reviewOwner->email }}</div>
                                        <div class="small mt-2"><span class="text-muted">{{ __('messages.review_report_field_user_id') }}:</span> #{{ $r->reviewOwner->id }}</div>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted mb-0">—</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-lg mt-4 review-report-main-card">
                <div class="card-header bg-white border-bottom py-3 px-4 px-lg-5">
                    <h5 class="mb-0"><i class="fas fa-star text-warning me-2"></i>{{ __('messages.review_report_section_review_target') }}</h5>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <div class="mb-4 pb-4 border-bottom">
                        <div class="report-field-label">{{ __('messages.review_report_field_review_type') }}</div>
                        <div>
                            <span class="badge bg-secondary fs-6">{{ \App\Models\ReviewReport::reviewTypeLabel($r->review_type) }}</span>
                        </div>
                    </div>
                    <div class="mb-4 pb-4 border-bottom">
                        <div class="report-field-label">{{ __('messages.review_report_field_review_ref') }}</div>
                        <code class="user-select-all bg-light px-2 py-1 rounded">{{ $r->review_type }}#{{ $r->review_id }}</code>
                    </div>
                    @if($reviewRating !== null && $reviewRating !== '')
                        <div class="mb-4 pb-4 border-bottom">
                            <div class="report-field-label">{{ __('messages.review_report_field_review_rating') }}</div>
                            <div class="fs-5 fw-semibold">{{ $reviewRating }} / 5</div>
                        </div>
                    @endif
                    <div class="mb-4 pb-4 border-bottom">
                        <div class="report-field-label">{{ __('messages.review_report_field_review_visibility') }}</div>
                        <span class="badge fs-6 px-3 py-2 {{ $isHidden ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                            {{ $isHidden ? __('messages.review_report_visibility_hidden') : __('messages.review_report_visibility_visible') }}
                        </span>
                    </div>
                    <div>
                        <div class="report-field-label">{{ __('messages.review_report_field_review_text') }}</div>
                        @if($reviewText !== '')
                            <div class="report-review-text-box p-4 bg-light rounded-3 border">{{ $reviewText }}</div>
                        @else
                            <div class="report-review-text-box p-4 bg-light rounded-3 border d-flex align-items-center">
                                <span class="text-muted fst-italic">{{ __('messages.review_report_no_review_text') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-lg mt-4 review-report-main-card">
                <div class="card-header bg-white border-bottom py-3 px-4 px-lg-5">
                    <h5 class="mb-0"><i class="fas fa-file-alt text-primary me-2"></i>{{ __('messages.review_report_section_report') }}</h5>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <div class="mb-4 pb-4 border-bottom">
                        <div class="report-field-label">{{ __('messages.review_report_field_reason') }}</div>
                        <div class="report-reason-box p-4 bg-light rounded-3 border">
                            {{ \App\Models\ReviewReport::reasonLabel($r->reason) }}
                        </div>
                    </div>
                    <div class="mb-4 pb-4 border-bottom">
                        <div class="report-field-label">{{ __('messages.review_report_field_report_status') }}</div>
                        <span class="badge fs-6 px-3 py-2 {{ $st === 'pending' ? 'bg-warning text-dark' : ($st === 'dismissed' ? 'bg-secondary' : 'bg-info text-dark') }}">
                            {{ \App\Models\ReviewReport::statusLabel($r->status) }}
                        </span>
                    </div>
                    <div>
                        <div class="report-field-label">{{ __('messages.review_report_field_details') }}</div>
                        @if($r->details)
                            <div class="report-details-box p-4 bg-white rounded-3 border border-2">{{ $r->details }}</div>
                        @else
                            <div class="report-details-box p-4 bg-light rounded-3 border d-flex align-items-center">
                                <span class="text-muted fst-italic">{{ __('messages.review_report_no_details') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($r->reviewed_at || $r->admin_note || $r->reviewer)
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h6 class="mb-0"><i class="fas fa-shield-alt text-success me-2"></i>{{ __('messages.review_report_section_admin') }}</h6>
                    </div>
                    <div class="card-body p-4">
                        <dl class="row mb-0">
                            <dt class="col-sm-3 text-muted">{{ __('messages.profile_report_field_reviewed_by') }}</dt>
                            <dd class="col-sm-9">{{ $r->reviewer ? ($r->reviewer->display_name ?? $r->reviewer->email) : '—' }}</dd>
                            <dt class="col-sm-3 text-muted">{{ __('messages.profile_report_field_reviewed_at') }}</dt>
                            <dd class="col-sm-9">{{ $r->reviewed_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                            <dt class="col-sm-3 text-muted align-top">{{ __('messages.profile_report_field_admin_note') }}</dt>
                            <dd class="col-sm-9 text-break">{{ $r->admin_note ? $r->admin_note : '—' }}</dd>
                        </dl>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm sticky-lg-top" style="top: 1rem;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0">{{ __('messages.review_report_actions_heading') }}</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.review-reports.update', $r) }}" class="d-flex flex-column gap-3">
                        @csrf
                        <div>
                            <label class="form-label small text-muted mb-1">{{ __('messages.status') }}</label>
                            <select name="action" class="form-select" required>
                                <option value="" disabled selected>{{ __('messages.review_report_action_select') }}</option>
                                <option value="dismiss">{{ __('messages.review_report_action_dismiss') }}</option>
                                <option value="hide_review" @selected(!$isHidden)>{{ __('messages.review_report_action_hide_review') }}</option>
                                <option value="restore_review" @selected($isHidden)>{{ __('messages.review_report_action_restore_review') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label small text-muted mb-1">{{ __('messages.profile_report_field_admin_note') }}</label>
                            <textarea name="admin_note" class="form-control" rows="5" placeholder="{{ __('messages.review_report_admin_note_placeholder') }}">{{ old('admin_note') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-check me-1"></i> {{ __('messages.review_report_apply') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</x-master-layout>
