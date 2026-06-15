<x-master-layout>
    <div class="container-fluid">
        <div class="d-flex align-items-center mb-3">
            <h4 class="mb-0">{{ __('messages.chat_flagged_messages') }}</h4>
            <span class="badge bg-danger ml-2">{{ number_format($messages->total()) }}</span>
            <div class="ms-auto">
                <a href="{{ route('chat.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('messages.chat_back_to_messages') }}</a>
            </div>
        </div>
        <div class="card">
            <div class="table-responsive">
                @if(session('status'))
                    <div class="alert alert-success m-3">{{ session('status') }}</div>
                @endif
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.id') }}</th>
                            <th>{{ __('messages.sender') }}</th>
                            <th>{{ __('messages.type') }}</th>
                            <th>{{ __('messages.chat_excerpt') }}</th>
                            <th>{{ __('messages.chat_conversation') }}</th>
                            <th>{{ __('messages.created_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $m)
                            <tr>
                                <td>{{ $m->id }}</td>
                                <td>{{ optional($m->sender)->display_name ?? (__('messages.user').'#'.$m->sender_id) }}</td>
                                <td>
                                    @php $types = $m->pii_types ? explode(',', $m->pii_types) : []; @endphp
                                    @foreach($types as $t)
                                        @php $typeKey = 'messages.chat_pii_type_' . $t; @endphp
                                        <span class="badge bg-warning text-dark mr-1">{{ \Illuminate\Support\Facades\Lang::has($typeKey) ? __($typeKey) : ucfirst(str_replace('_', ' ', $t)) }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $m->message ? 
                                    (mb_strlen($m->message) > 80 ? mb_substr($m->message,0,80).'…' : $m->message)
                                 : __('messages.chat_attachment_empty') }}</td>
                                <td>#{{ $m->conversation_id }}</td>
                                <td>
                                    <div>{{ $m->created_at?->toDateTimeString() }}</div>
                                    <form method="POST" action="{{ route('chat.flagged.warn', $m->id) }}" class="mt-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('messages.chat_send_warning_email') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">{{ __('messages.chat_no_flagged_messages') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $messages->links() }}</div>
        </div>
    </div>
</x-master-layout>

