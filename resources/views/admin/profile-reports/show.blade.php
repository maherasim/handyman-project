<x-master-layout>
@php
    $r = $profileReport;
    $reportedRole = (string) ($r->reportedUser->user_type ?? '');
    $roleLabel = match ($reportedRole) {
        'provider' => __('messages.profile_report_role_employer'),
        'user' => __('messages.profile_report_role_customer'),
        'handyman' => __('messages.profile_report_role_worker'),
        default => __('messages.profile_report_role_unknown'),
    };
    $reportedUserStatus = (int) ($r->reportedUser->status ?? 0);
    $accountStatusLabel = $r->reportedUser ? ($reportedUserStatus === 1 ? __('messages.active') : __('messages.inactive')) : '—';
    $st = (string) $r->status;
@endphp
<style>
    .profile-report-main-card .report-field-label { font-size: 0.75rem; letter-spacing: .04em; text-transform: uppercase; color: #6c757d; font-weight: 600; margin-bottom: .5rem; }
    .profile-report-main-card .report-reason-box { min-height: 5rem; font-size: 1.15rem; line-height: 1.5; word-break: break-word; }
    .profile-report-main-card .report-details-box { min-height: 16rem; max-height: 50vh; overflow-y: auto; white-space: pre-wrap; word-break: break-word; line-height: 1.6; }
    @media (min-width: 992px) {
        .profile-report-main-card .report-details-box { min-height: 20rem; max-height: 55vh; }
    }
</style>
<div class="container-fluid profile-report-detail">
    <div class="row mb-3">
        <div class="col-12 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('admin.profile-reports.index') }}">{{ __('messages.profile_report_list_title') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">#{{ $r->id }}</li>
                    </ol>
                </nav>
                <h4 class="mb-0">{{ str_replace(':id', (string) $r->id, __('messages.profile_report_detail_title')) }}</h4>
                <p class="text-muted small mb-0">{{ __('messages.profile_report_field_submitted') }}: {{ $r->created_at?->format('Y-m-d H:i') }}</p>
            </div>
            <a href="{{ route('admin.profile-reports.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> {{ __('messages.profile_report_back_list') }}
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
                            <h6 class="mb-0 text-white"><i class="fas fa-user-edit me-2"></i>{{ __('messages.profile_report_section_reporter') }}</h6>
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
                                        <div class="small mt-2"><span class="text-muted">{{ __('messages.profile_report_field_user_id') }}:</span> #{{ $r->reporter->id }}</div>
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
                            <h6 class="mb-0 text-white"><i class="fas fa-flag me-2"></i>{{ __('messages.profile_report_section_reported') }}</h6>
                        </div>
                        <div class="card-body p-4">
                            @if($r->reportedUser)
                                <div class="d-flex align-items-start gap-3">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center flex-shrink-0" style="width:56px;height:56px;">
                                        <i class="fas fa-id-badge text-dark fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold text-break">{{ $r->reportedUser->display_name ?? $r->reportedUser->email }}</div>
                                        <div class="small text-muted text-break">{{ $r->reportedUser->email }}</div>
                                        <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                                            <span class="badge bg-secondary">{{ $roleLabel }}</span>
                                            <span class="badge {{ $reportedUserStatus === 1 ? 'bg-success' : 'bg-danger' }}">{{ $accountStatusLabel }}</span>
                                        </div>
                                        <div class="small mt-2"><span class="text-muted">{{ __('messages.profile_report_field_user_id') }}:</span> #{{ $r->reportedUser->id }}</div>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted mb-0">—</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-lg mt-4 profile-report-main-card">
                <div class="card-header bg-white border-bottom py-3 px-4 px-lg-5">
                    <h5 class="mb-0"><i class="fas fa-file-alt text-primary me-2"></i>{{ __('messages.profile_report_section_report') }}</h5>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <div class="mb-4 pb-4 border-bottom">
                        <div class="report-field-label">{{ __('messages.profile_report_field_reason') }}</div>
                        <div class="report-reason-box p-4 bg-light rounded-3 border">
                            {{ \App\Models\ProfileReport::reasonLabel($r->reason) }}
                        </div>
                    </div>
                    <div class="mb-4 pb-4 border-bottom">
                        <div class="report-field-label">{{ __('messages.profile_report_field_report_status') }}</div>
                        <div>
                            <span class="badge fs-6 px-3 py-2 {{ $st === 'pending' ? 'bg-warning text-dark' : ($st === 'dismissed' ? 'bg-secondary' : 'bg-info text-dark') }}">
                                {{ \App\Models\ProfileReport::statusLabel($r->status) }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="report-field-label">{{ __('messages.profile_report_field_details') }}</div>
                        @if($r->details)
                            <div class="report-details-box p-4 bg-white rounded-3 border border-2">{{ $r->details }}</div>
                        @else
                            <div class="report-details-box p-4 bg-light rounded-3 border d-flex align-items-center">
                                <span class="text-muted fst-italic">{{ __('messages.profile_report_no_details') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($r->reviewed_at || $r->admin_note || $r->reviewer)
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h6 class="mb-0"><i class="fas fa-shield-alt text-success me-2"></i>{{ __('messages.profile_report_section_admin') }}</h6>
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
                    <h6 class="mb-0">{{ __('messages.profile_report_actions_heading') }}</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.profile-reports.update', $r) }}" class="d-flex flex-column gap-3">
                        @csrf
                        <div>
                            <label class="form-label small text-muted mb-1">{{ __('messages.status') }}</label>
                            <select name="action" class="form-select" required>
                                <option value="" disabled selected>{{ __('messages.profile_report_action_select') }}</option>
                                <option value="dismiss">{{ __('messages.profile_report_action_dismiss') }}</option>
                                @if($r->reportedUser)
                                    <option value="deactivate_user" @selected($reportedUserStatus === 1)>{{ __('messages.profile_report_action_deactivate') }}</option>
                                    <option value="activate_user" @selected($reportedUserStatus === 0)>{{ __('messages.profile_report_action_activate') }}</option>
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="form-label small text-muted mb-1">{{ __('messages.profile_report_field_admin_note') }}</label>
                            <textarea name="admin_note" class="form-control" rows="5" placeholder="{{ __('messages.profile_report_admin_note_placeholder') }}">{{ old('admin_note') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-check me-1"></i> {{ __('messages.profile_report_apply') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</x-master-layout>
