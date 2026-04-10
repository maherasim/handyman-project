<x-master-layout>
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

                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.profile_report_col_id') }}</th>
                                    <th>{{ __('messages.profile_report_col_reporter') }}</th>
                                    <th>{{ __('messages.profile_report_col_reported_profile') }}</th>
                                    <th>{{ __('messages.profile_report_col_user_status') }}</th>
                                    <th>{{ __('messages.profile_report_col_reason') }}</th>
                                    <th>{{ __('messages.profile_report_col_details') }}</th>
                                    <th>{{ __('messages.profile_report_col_status') }}</th>
                                    <th>{{ __('messages.profile_report_col_date') }}</th>
                                    <th>{{ __('messages.profile_report_col_view') }}</th>
                                    <th>{{ __('messages.profile_report_col_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
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
                                                <div class="fw-semibold">{{ $report->reportedUser->display_name ?? $report->reportedUser->email }}</div>
                                                <div class="small text-muted">{{ $report->reportedUser->email }}</div>
                                                <span class="badge bg-light text-dark border mt-1 text-uppercase">{{ $roleLabel }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($report->reportedUser)
                                                @php
                                                    $reportedUserStatus = (int) $report->reportedUser->status;
                                                    $statusLabel = $reportedUserStatus === 1 ? __('messages.active') : __('messages.inactive');
                                                @endphp
                                                <span class="badge {{ $reportedUserStatus === 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                    {{ $statusLabel }}
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ \App\Models\ProfileReport::reasonLabel($report->reason) }}</span>
                                        </td>
                                        <td class="small">{{ $report->details ? \Illuminate\Support\Str::limit($report->details, 140) : '—' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ \App\Models\ProfileReport::statusLabel($report->status) }}</span>
                                        </td>
                                        <td>{{ $report->created_at?->format('Y-m-d H:i') }}</td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('admin.profile-reports.show', $report) }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                                                <i class="fas fa-eye me-1"></i> {{ __('messages.profile_report_view') }}
                                            </a>
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.profile-reports.update', $report) }}" class="d-flex flex-column gap-2" style="min-width: 210px;">
                                                @csrf
                                                @php
                                                    $reportedUserStatus = (int) ($report->reportedUser->status ?? 0);
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
