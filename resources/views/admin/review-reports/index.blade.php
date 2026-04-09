<x-master-layout>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h4 class="card-title mb-0">Review Reports</h4>
                        <p class="text-muted mb-0 small">Review reports raised by logged-in users.</p>
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
                                    <th>Review Owner</th>
                                    <th>Review</th>
                                    <th>Reason</th>
                                    <th>Details</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
                                    @php
                                        $reviewItem = match ($report->review_type) {
                                            'customer_rating' => \App\Models\CustomerRating::withTrashed()->find($report->review_id),
                                            'post_job_bid_rating' => \App\Models\PostJobBidRating::query()->find($report->review_id),
                                            'post_job_bid_customer_rating' => \App\Models\PostJobBidCustomerRating::query()->find($report->review_id),
                                            default => \App\Models\BookingRating::withTrashed()->find($report->review_id),
                                        };
                                        $reviewText = (string) optional($reviewItem)->review;
                                        $isHiddenByStatus = $reviewItem && (int) ($reviewItem->status ?? 0) === 1;
                                        $isLegacyTrashed = $reviewItem && method_exists($reviewItem, 'trashed') && $reviewItem->trashed();
                                        $isHidden = $isHiddenByStatus || $isLegacyTrashed;
                                        $reviewTypeLabel = match ($report->review_type) {
                                            'customer_rating' => 'Provider review (booking)',
                                            'post_job_bid_rating' => 'Customer review (post job)',
                                            'post_job_bid_customer_rating' => 'Employer review (post job)',
                                            default => 'Customer review (booking)',
                                        };
                                    @endphp
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
                                            @if($report->reviewOwner)
                                                <div class="fw-semibold">{{ $report->reviewOwner->display_name ?? $report->reviewOwner->email }}</div>
                                                <div class="small text-muted">{{ $report->reviewOwner->email }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="small">
                                            <span class="badge bg-light text-dark border mb-1">{{ $reviewTypeLabel }}</span>
                                            <div class="text-muted small mb-1">Ref: <code class="small">{{ $report->review_type }}#{{ $report->review_id }}</code></div>
                                            <br>
                                            {{ \Illuminate\Support\Str::limit($reviewText, 90) ?: '—' }}
                                            <div class="mt-1">
                                                <span class="badge {{ $isHidden ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                                                    {{ $isHidden ? 'Hidden' : 'Visible' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>{{ ucwords(str_replace('_', ' ', (string) $report->reason)) }}</td>
                                        <td class="small">{{ $report->details ? \Illuminate\Support\Str::limit($report->details, 140) : '—' }}</td>
                                        <td>
                                            <span class="badge bg-secondary text-uppercase">{{ str_replace('_', ' ', (string) $report->status) }}</span>
                                        </td>
                                        <td>{{ $report->created_at?->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.review-reports.update', $report) }}" class="d-flex flex-column gap-2" style="min-width: 210px;">
                                                @csrf
                                                <select name="action" class="form-select form-select-sm" required>
                                                    <option value="" disabled selected>Select action...</option>
                                                    <option value="dismiss">Dismiss</option>
                                                    <option value="hide_review" @selected(!$isHidden)>Hide review</option>
                                                    <option value="restore_review" @selected($isHidden)>Restore review</option>
                                                </select>
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="admin_note" class="form-control" placeholder="Admin note (optional)">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">No review reports found.</td>
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
