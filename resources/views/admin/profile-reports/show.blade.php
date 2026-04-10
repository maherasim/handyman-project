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
@endphp
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
        <div class="col-lg-8">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-primary text-white py-3">
                            <h6 class="mb-0 text-white"><i class="fas fa-user-edit me-2"></i>{{ __('messages.profile_report_section_reporter') }}</h6>
                        </div>
                        <div class="card-body">
                            @if($r->reporter)
                                <div class="d-flex align-items-start gap-3">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center flex-shrink-0" style="width:56px;height:56px;">
                                        <i class="fas fa-user text-primary fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold text-truncate">{{ $r->reporter->display_name ?? $r->reporter->email }}</div>
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
                        <div class="card-body">
                            @if($r->reportedUser)
                                <div class="d-flex align-items-start gap-3">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center flex-shrink-0" style="width:56px;height:56px;">
                                        <i class="fas fa-id-badge text-dark fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold text-truncate">{{ $r->reportedUser->display_name ?? $r->reportedUser->email }}</div>
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

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0"><i class="fas fa-file-alt text-primary me-2"></i>{{ __('messages.profile_report_section_report') }}</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3 text-muted">{{ __('messages.profile_report_field_reason') }}</dt>
                        <dd class="col-sm-9">
                            <span class="fs-6 fw-semibold">{{ \App\Models\ProfileReport::reasonLabel($r->reason) }}</span>
                        </dd>
                        <dt class="col-sm-3 text-muted">{{ __('messages.profile_report_field_reason_code') }}</dt>
                        <dd class="col-sm-9"><code class="user-select-all bg-light px-2 py-1 rounded">{{ $r->reason }}</code></dd>
                        <dt class="col-sm-3 text-muted">{{ __('messages.profile_report_field_report_status') }}</dt>
                        <dd class="col-sm-9">
                            @php $st = (string) $r->status; @endphp
                            <span class="badge {{ $st === 'pending' ? 'bg-warning text-dark' : ($st === 'dismissed' ? 'bg-secondary' : 'bg-info text-dark') }}">
                                {{ \App\Models\ProfileReport::statusLabel($r->status) }}
                            </span>
                        </dd>
                        <dt class="col-sm-3 text-muted align-top">{{ __('messages.profile_report_field_details') }}</dt>
                        <dd class="col-sm-9">
                            @if($r->details)
                                <div class="p-3 bg-light rounded border-start border-4 border-primary">{{ $r->details }}</div>
                            @else
                                <span class="text-muted fst-italic">{{ __('messages.profile_report_no_details') }}</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            @if($r->reviewed_at || $r->admin_note || $r->reviewer)
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0"><i class="fas fa-shield-alt text-success me-2"></i>{{ __('messages.profile_report_section_admin') }}</h6>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3 text-muted">{{ __('messages.profile_report_field_reviewed_by') }}</dt>
                            <dd class="col-sm-9">{{ $r->reviewer ? ($r->reviewer->display_name ?? $r->reviewer->email) : '—' }}</dd>
                            <dt class="col-sm-3 text-muted">{{ __('messages.profile_report_field_reviewed_at') }}</dt>
                            <dd class="col-sm-9">{{ $r->reviewed_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                            <dt class="col-sm-3 text-muted align-top">{{ __('messages.profile_report_field_admin_note') }}</dt>
                            <dd class="col-sm-9">{{ $r->admin_note ? $r->admin_note : '—' }}</dd>
                        </dl>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-lg-top" style="top: 1rem;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0">{{ __('messages.profile_report_actions_heading') }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.profile-reports.update', $r) }}" class="d-flex flex-column gap-3">
                        @csrf
                        <div>
                            <label class="form-label small text-muted mb-1">{{ __('messages.status') }}</label>
                            <select name="action" class="form-select" required>
                                <option value="" disabled selected>Select action…</option>
                                <option value="dismiss">Dismiss</option>
                                @if($r->reportedUser)
                                    <option value="deactivate_user" @selected($reportedUserStatus === 1)>Deactivate user</option>
                                    <option value="activate_user" @selected($reportedUserStatus === 0)>Activate user</option>
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="form-label small text-muted mb-1">{{ __('messages.profile_report_field_admin_note') }}</label>
                            <textarea name="admin_note" class="form-control" rows="4" placeholder="Optional note for the record">{{ old('admin_note') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-check me-1"></i> Apply
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</x-master-layout>
