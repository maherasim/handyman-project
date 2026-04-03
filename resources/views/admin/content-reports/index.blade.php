<x-master-layout>
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

                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.srno') }}</th>
                                    <th>{{ __('messages.ugc_col_service') }}</th>
                                    <th>{{ __('messages.ugc_col_reporter') }}</th>
                                    <th>{{ __('messages.ugc_col_provider') }}</th>
                                    <th>{{ __('messages.ugc_col_reason') }}</th>
                                    <th>{{ __('messages.ugc_col_details') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.ugc_col_date') }}</th>
                                    <th>{{ __('messages.ugc_col_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
                                    <tr>
                                        <td>{{ $report->id }}</td>
                                        <td>
                                            @if($report->reportable && $report->reportable_type === \App\Models\Service::class)
                                                <strong>{{ \Illuminate\Support\Str::limit($report->reportable->name ?? '—', 40) }}</strong>
                                                <div class="small text-muted">#{{ $report->reportable_id }}</div>
                                            @else
                                                #{{ $report->reportable_id }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($report->reporter)
                                                {{ $report->reporter->email ?? ($report->reporter->first_name.' '.$report->reporter->last_name) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($report->subjectUser)
                                                {{ $report->subjectUser->display_name ?? $report->subjectUser->email }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $report->reason }}</td>
                                        <td class="small">{{ $report->details ? \Illuminate\Support\Str::limit($report->details, 120) : '—' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $report->status }}</span>
                                        </td>
                                        <td>{{ $report->created_at?->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @if($report->status === 'pending')
                                                <div class="d-flex flex-column gap-2" style="min-width: 220px;">
                                                    <form method="POST" action="{{ route('admin.content-reports.update', $report) }}" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="action" value="dismiss">
                                                        <input type="text" name="admin_note" class="form-control form-control-sm mb-1" placeholder="{{ __('messages.ugc_admin_note_placeholder') }}">
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary">{{ __('messages.ugc_action_dismiss') }}</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.content-reports.update', $report) }}" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="action" value="hide_service">
                                                        <input type="text" name="admin_note" class="form-control form-control-sm mb-1" placeholder="{{ __('messages.ugc_admin_note_placeholder') }}">
                                                        <button type="submit" class="btn btn-sm btn-warning">{{ __('messages.ugc_action_hide_service') }}</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.content-reports.update', $report) }}" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="action" value="restore_service">
                                                        <input type="text" name="admin_note" class="form-control form-control-sm mb-1" placeholder="{{ __('messages.ugc_admin_note_placeholder') }}">
                                                        <button type="submit" class="btn btn-sm btn-success">{{ __('messages.ugc_action_restore_service') }}</button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-muted small">{{ __('messages.ugc_no_further_action') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">{{ __('messages.ugc_admin_empty') }}</td>
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
