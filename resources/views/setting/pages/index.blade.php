<x-master-layout>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3">
                        <h5 class="fw-bold">{{ $pageTitle ?? __('messages.content_pages') }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.title') }}</th>
                                    <th>{{ __('messages.slug') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.sort_order') }}</th>
                                    <th width="120">{{ __('messages.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pages as $page)
                                <tr>
                                    <td>{{ $page->title }}</td>
                                    <td><code>{{ $page->slug }}</code></td>
                                    <td>
                                        @if($page->is_active)
                                            <span class="badge bg-success">{{ __('messages.active') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('messages.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $page->sort_order }}</td>
                                    <td>
                                        <a href="{{ route('page.edit', $page->id) }}" class="btn btn-sm btn-primary">{{ __('messages.edit') }}</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">{{ __('messages.record_not_found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-master-layout>
