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
                                    <th>{{ __('messages.ugc_col_listing') }}</th>
                                    <th>{{ __('messages.ugc_col_reporter') }}</th>
                                    <th>{{ __('messages.ugc_col_reported_party') }}</th>
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
                                                <span class="badge bg-light text-dark border mb-1">{{ __('messages.service') }}</span>
                                                <strong>{{ \Illuminate\Support\Str::limit($report->reportable->name ?? '—', 40) }}</strong>
                                                <div class="small text-muted">#{{ $report->reportable_id }}</div>
                                            @elseif($report->reportable && $report->reportable_type === \App\Models\PostJobRequest::class)
                                                <span class="badge bg-light text-dark border mb-1">{{ __('messages.post_job_request') }}</span>
                                                <strong>{{ \Illuminate\Support\Str::limit($report->reportable->title ?? '—', 40) }}</strong>
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
                                        <td>
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
                                            @endphp
                                            {{ $reasonText }}
                                        </td>
                                        <td class="small">{{ $report->details ? \Illuminate\Support\Str::limit($report->details, 120) : '—' }}</td>
                                        <td>
                                            @php
                                                $ugcStatusLabels = [
                                                    'pending' => __('messages.ugc_report_status_pending'),
                                                    'dismissed' => __('messages.ugc_report_status_dismissed'),
                                                    'action_taken' => __('messages.ugc_report_status_action_taken'),
                                                    'reviewed' => __('messages.ugc_report_status_reviewed'),
                                                ];
                                                $statusLabel = $ugcStatusLabels[$report->status] ?? ucfirst(str_replace('_', ' ', (string) $report->status));
                                            @endphp
                                            <span class="badge bg-secondary">{{ $statusLabel }}</span>
                                        </td>
                                        <td>{{ $report->created_at?->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.content-reports.update', $report) }}" class="d-flex flex-column gap-2" style="min-width: 200px;">
                                                @csrf
                                                @php
                                                    $isHiddenFromPublic = (bool) ($report->reportable->is_hidden_from_public ?? false);
                                                @endphp
                                                <select name="action" class="form-select form-select-sm border-0 shadow-sm" required style="border-radius: 8px; font-weight: 500;">
                                                    <option value="" disabled selected>{{ __('messages.action') }}...</option>
                                                    <option value="dismiss" class="text-secondary">{{ __('messages.ugc_action_dismiss') }}</option>
                                                    @if($report->reportable_type === \App\Models\Service::class)
                                                        <option value="hide_service" class="text-warning fw-bold" @selected(!$isHiddenFromPublic)>{{ __('messages.ugc_action_hide_service') }}</option>
                                                        <option value="restore_service" class="text-success" @selected($isHiddenFromPublic)>{{ __('messages.ugc_action_restore_service') }}</option>
                                                    @elseif($report->reportable_type === \App\Models\PostJobRequest::class)
                                                        <option value="hide_post_job" class="text-warning fw-bold" @selected(!$isHiddenFromPublic)>{{ __('messages.ugc_action_hide_post_job') }}</option>
                                                        <option value="restore_post_job" class="text-success" @selected($isHiddenFromPublic)>{{ __('messages.ugc_action_restore_post_job') }}</option>
                                                    @endif
                                                </select>
                                                
                                                <div class="input-group input-group-sm shadow-sm" style="border-radius: 8px; overflow: hidden;">
                                                    <input type="text" name="admin_note" class="form-control border-0" placeholder="{{ __('messages.ugc_admin_note_placeholder') }}" style="background: #f8f9fa;">
                                                    <button type="submit" class="btn btn-primary border-0" style="background: #667eea; transition: all 0.2s;">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </div>
                                            </form>
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
