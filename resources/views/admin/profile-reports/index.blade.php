<x-master-layout>
<style>
    .profile-reports-page .table thead th {
        background: #3333ff !important;
        color: #fff !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        font-weight: 600;
        font-size: 0.8125rem;
        white-space: nowrap;
        vertical-align: middle;
        padding: 0.65rem 0.75rem;
    }
    .profile-reports-page .table tbody td {
        vertical-align: middle;
        font-size: 0.8125rem;
        padding: 0.65rem 0.75rem;
    }
    .profile-reports-page .table-striped > tbody > tr:nth-of-type(odd) > * {
        --bs-table-accent-bg: rgba(51, 51, 255, 0.04);
    }
    .profile-reports-page .table {
        --bs-table-border-color: #dee2e6;
    }
    .profile-reports-page .text-cell {
        min-width: 9rem;
        max-width: 14rem;
    }
    .profile-reports-page .profile-cell {
        min-width: 11rem;
        max-width: 16rem;
    }
    .profile-reports-page .details-cell {
        min-width: 10rem;
        max-width: 16rem;
    }
    .profile-reports-page .view-cell {
        width: 1%;
        min-width: 6.5rem;
        white-space: nowrap;
        text-align: center;
        vertical-align: middle;
    }
    .profile-reports-page .actions-cell {
        min-width: 16rem;
    }
    .profile-reports-page .actions-cell form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }
    .profile-reports-page .actions-cell .form-select-sm {
        min-width: 11rem;
        max-width: 15rem;
    }
    .profile-reports-page .actions-cell .admin-note-input {
        min-width: 8rem;
        max-width: 14rem;
        flex: 1 1 8rem;
    }
</style>
<div class="container-fluid profile-reports-page">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 p-3">
                        <div>
                            <h5 class="font-weight-bold mb-0">{{ __('messages.profile_report_list_title') }}</h5>
                            <p class="text-muted small mb-0 mt-1">{{ __('messages.profile_report_index_subtitle') }}</p>
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
                            <th>{{ __('messages.profile_report_col_id') }}</th>
                            <th class="text-cell">{{ __('messages.profile_report_col_reporter') }}</th>
                            <th class="profile-cell">{{ __('messages.profile_report_col_reported_profile') }}</th>
                            <th>{{ __('messages.profile_report_col_user_status') }}</th>
                            <th class="text-cell">{{ __('messages.profile_report_col_reason') }}</th>
                            <th class="details-cell">{{ __('messages.profile_report_col_details') }}</th>
                            <th>{{ __('messages.profile_report_col_status') }}</th>
                            <th>{{ __('messages.profile_report_col_date') }}</th>
                            <th class="view-cell">{{ __('messages.profile_report_col_view') }}</th>
                            <th class="actions-cell">{{ __('messages.profile_report_col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            @php
                                $reportedUserStatus = (int) ($report->reportedUser?->status ?? 0);
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
                                <td class="profile-cell small">
                                    @if($report->reportedUser)
                                        @php
                                            $reportedRole = (string) ($report->reportedUser->user_type ?? '');
                                            $roleLabel = match ($reportedRole) {
                                                'provider' => __('messages.profile_report_role_employer'),
                                                'user' => __('messages.profile_report_role_customer'),
                                                'handyman' => __('messages.profile_report_role_worker'),
                                                default => __('messages.profile_report_role_unknown'),
                                            };
                                        @endphp
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="badge bg-primary text-white align-self-start">{{ $roleLabel }}</span>
                                            <div class="min-w-0">
                                                <div class="fw-semibold text-truncate" title="{{ $report->reportedUser->display_name ?? $report->reportedUser->email }}">{{ \Illuminate\Support\Str::limit($report->reportedUser->display_name ?? $report->reportedUser->email, 28) }}</div>
                                                <div class="text-muted text-truncate small" title="{{ $report->reportedUser->email }}">{{ \Illuminate\Support\Str::limit($report->reportedUser->email, 32) }}</div>
                                            </div>
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if($report->reportedUser)
                                        @php
                                            $acctStatusLabel = $reportedUserStatus === 1 ? __('messages.active') : __('messages.inactive');
                                        @endphp
                                        <span class="badge {{ $reportedUserStatus === 1 ? 'bg-success' : 'bg-danger' }}">{{ $acctStatusLabel }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-cell small" title="{{ \App\Models\ProfileReport::reasonLabel($report->reason) }}">
                                    {{ \Illuminate\Support\Str::limit(\App\Models\ProfileReport::reasonLabel($report->reason), 42) }}
                                </td>
                                <td class="details-cell small text-muted" title="{{ $report->details }}">
                                    {{ $report->details ? \Illuminate\Support\Str::limit($report->details, 80) : '—' }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ \App\Models\ProfileReport::statusLabel($report->status) }}</span>
                                </td>
                                <td class="small text-muted text-nowrap">
                                    {{ $report->created_at?->format('Y-m-d') }}<br>{{ $report->created_at?->format('H:i') }}
                                </td>
                                <td class="view-cell">
                                    <a href="{{ route('admin.profile-reports.show', $report) }}" class="btn btn-sm btn-outline-primary text-nowrap" target="_blank" rel="noopener" title="{{ __('messages.profile_report_view') }}">
                                        <i class="fas fa-eye me-1"></i>{{ __('messages.profile_report_view') }}
                                    </a>
                                </td>
                                <td class="actions-cell">
                                        <form method="POST" action="{{ route('admin.profile-reports.update', $report) }}">
                                            @csrf
                                            <select name="action" class="form-select form-select-sm" required>
                                                <option value="" disabled selected>{{ __('messages.profile_report_action_select') }}</option>
                                                <option value="dismiss">{{ __('messages.profile_report_action_dismiss') }}</option>
                                                @if($report->reportedUser)
                                                    <option value="deactivate_user" @selected($reportedUserStatus === 1)>{{ __('messages.profile_report_action_deactivate') }}</option>
                                                    <option value="activate_user" @selected($reportedUserStatus === 0)>{{ __('messages.profile_report_action_activate') }}</option>
                                                @endif
                                            </select>
                                            <input type="text" name="admin_note" class="form-control form-control-sm admin-note-input" placeholder="{{ __('messages.profile_report_admin_note_short_placeholder') }}">
                                            <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                                                <i class="fas fa-check me-1"></i>{{ __('messages.apply') }}
                                            </button>
                                        </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">{{ __('messages.profile_report_empty') }}</td>
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
