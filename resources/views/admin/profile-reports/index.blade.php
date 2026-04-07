<x-master-layout>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h4 class="card-title mb-0">Profile Reports</h4>
                        <p class="text-muted mb-0 small">Review reports raised against user profiles (user, provider, handyman).</p>
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
                                    <th>ID</th>
                                    <th>Reporter</th>
                                    <th>Reported Profile</th>
                                    <th>Reason</th>
                                    <th>Details</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
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
                                        <td>{{ $report->reason }}</td>
                                        <td class="small">{{ $report->details ? \Illuminate\Support\Str::limit($report->details, 140) : '—' }}</td>
                                        <td>
                                            <span class="badge bg-secondary text-uppercase">{{ str_replace('_', ' ', (string) $report->status) }}</span>
                                        </td>
                                        <td>{{ $report->created_at?->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @if($report->status === 'pending')
                                                <form method="POST" action="{{ route('admin.profile-reports.update', $report) }}" class="d-flex flex-column gap-2" style="min-width: 210px;">
                                                    @csrf
                                                    <select name="action" class="form-select form-select-sm" required>
                                                        <option value="" disabled selected>Select action...</option>
                                                        <option value="dismiss">Dismiss</option>
                                                        <option value="deactivate_user">Deactivate user</option>
                                                        <option value="activate_user">Activate user</option>
                                                    </select>
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" name="admin_note" class="form-control" placeholder="Admin note (optional)">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            @else
                                                <span class="text-muted small">No further action</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No profile reports found.</td>
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
