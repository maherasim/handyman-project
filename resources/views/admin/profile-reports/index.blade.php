<x-master-layout>
<style>
    .report-list-table-wrap { font-size: 0.8125rem; }
    .report-list-table-wrap .table { margin-bottom: 0; }
    .report-list-table-wrap .cell-tight { width: 1%; white-space: nowrap; vertical-align: middle; }
    .report-list-table-wrap .cell-clip { max-width: 10rem; overflow: hidden; }
    .report-list-table-wrap .cell-clip-md { max-width: 13rem; overflow: hidden; }
    .report-list-table-wrap .cell-actions { width: 1%; min-width: 9rem; max-width: 11rem; vertical-align: top; }
    .report-list-table-wrap .cell-details { max-width: 9rem; overflow: hidden; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; line-height: 1.35; word-break: break-word; }
    .report-list-table-wrap .cell-view { width: 1%; min-width: 6.5rem; white-space: nowrap; vertical-align: middle; text-align: center; }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h4 class="card-title mb-0">{{ __('messages.profile_report_list_title') }}</h4>
                        <p class="text-muted mb-0 small">{{ __('messages.profile_report_index_subtitle') }}</p>
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
                                    <th class="cell-tight">{{ __('messages.profile_report_col_id') }}</th>
                                    <th class="cell-clip-md">{{ __('messages.profile_report_col_reporter') }}</th>
                                    <th class="cell-clip-md">{{ __('messages.profile_report_col_reported_profile') }}</th>
                                    <th class="cell-tight">{{ __('messages.profile_report_col_user_status') }}</th>
                                    <th class="cell-clip">{{ __('messages.profile_report_col_reason') }}</th>
                                    <th class="cell-details">{{ __('messages.profile_report_col_details') }}</th>
                                    <th class="cell-tight">{{ __('messages.profile_report_col_status') }}</th>
                                    <th class="cell-tight">{{ __('messages.profile_report_col_date') }}</th>
                                    <th class="cell-view">{{ __('messages.profile_report_col_view') }}</th>
                                    <th class="cell-actions">{{ __('messages.profile_report_col_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
                                    <tr>
                                        <td class="cell-tight">{{ $report->id }}</td>
                                        <td class="cell-clip-md small">
                                            @if($report->reporter)
                                                <div class="fw-semibold text-truncate" title="{{ $report->reporter->display_name ?? $report->reporter->email }}">{{ \Illuminate\Support\Str::limit($report->reporter->display_name ?? $report->reporter->email, 22) }}</div>
                                                <div class="text-muted text-truncate" title="{{ $report->reporter->email }}">{{ \Illuminate\Support\Str::limit($report->reporter->email, 24) }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="cell-clip-md small">
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
                                                <div class="fw-semibold text-truncate" title="{{ $report->reportedUser->display_name ?? $report->reportedUser->email }}">{{ \Illuminate\Support\Str::limit($report->reportedUser->display_name ?? $report->reportedUser->email, 22) }}</div>
                                                <div class="text-muted text-truncate" title="{{ $report->reportedUser->email }}">{{ \Illuminate\Support\Str::limit($report->reportedUser->email, 24) }}</div>
                                                <span class="badge bg-light text-dark border mt-1" style="font-size: 0.65rem;">{{ $roleLabel }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="cell-tight">
                                            @if($report->reportedUser)
                                                @php
                                                    $reportedUserStatus = (int) $report->reportedUser->status;
                                                    $statusLabel = $reportedUserStatus === 1 ? __('messages.active') : __('messages.inactive');
                                                @endphp
                                                <span class="badge {{ $reportedUserStatus === 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}" style="font-size: 0.7rem;">
                                                    {{ $statusLabel }}
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="cell-clip small" title="{{ \App\Models\ProfileReport::reasonLabel($report->reason) }}">
                                            <span class="fw-medium">{{ \Illuminate\Support\Str::limit(\App\Models\ProfileReport::reasonLabel($report->reason), 42) }}</span>
                                        </td>
                                        <td class="cell-details small" title="{{ $report->details }}">{{ $report->details ? \Illuminate\Support\Str::limit($report->details, 72) : '—' }}</td>
                                        <td class="cell-tight">
                                            <span class="badge bg-secondary" style="font-size: 0.7rem;">{{ \App\Models\ProfileReport::statusLabel($report->status) }}</span>
                                        </td>
                                        <td class="cell-tight small text-muted">{{ $report->created_at?->format('Y-m-d') }}<br>{{ $report->created_at?->format('H:i') }}</td>
                                        <td class="cell-view">
                                            <a href="{{ route('admin.profile-reports.show', $report) }}" class="btn btn-sm btn-outline-primary px-2 py-1 text-nowrap" target="_blank" rel="noopener" title="{{ __('messages.profile_report_view') }}">
                                                <i class="fas fa-eye me-1"></i>{{ __('messages.profile_report_view') }}
                                            </a>
                                        </td>
                                        <td class="cell-actions">
                                            <form method="POST" action="{{ route('admin.profile-reports.update', $report) }}" class="d-flex flex-column gap-1">
                                                @csrf
                                                @php
                                                    $reportedUserStatus = (int) ($report->reportedUser?->status ?? 0);
                                                @endphp
                                                <select name="action" class="form-select form-select-sm" required>
                                                    <option value="" disabled selected>{{ __('messages.profile_report_action_select') }}</option>
                                                    <option value="dismiss">{{ __('messages.profile_report_action_dismiss') }}</option>
                                                    @if($report->reportedUser)
                                                        <option value="deactivate_user" @selected($reportedUserStatus === 1)>{{ __('messages.profile_report_action_deactivate') }}</option>
                                                        <option value="activate_user" @selected($reportedUserStatus === 0)>{{ __('messages.profile_report_action_activate') }}</option>
                                                    @endif
                                                </select>
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="admin_note" class="form-control" placeholder="{{ __('messages.profile_report_admin_note_short_placeholder') }}">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">{{ __('messages.profile_report_empty') }}</td>
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
