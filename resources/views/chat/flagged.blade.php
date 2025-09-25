<x-master-layout>
    <div class="container-fluid">
        <div class="d-flex align-items-center mb-3">
            <h4 class="mb-0">Flagged Messages</h4>
            <span class="badge bg-danger ml-2">{{ number_format($messages->total()) }}</span>
            <div class="ms-auto">
                <a href="{{ route('chat.index') }}" class="btn btn-sm btn-outline-secondary">Back to Messages</a>
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
                            <th>ID</th>
                            <th>Sender</th>
                            <th>Types</th>
                            <th>Excerpt</th>
                            <th>Conversation</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $m)
                            <tr>
                                <td>{{ $m->id }}</td>
                                <td>{{ optional($m->sender)->display_name ?? ('User#'.$m->sender_id) }}</td>
                                <td>
                                    @php $types = $m->pii_types ? explode(',', $m->pii_types) : []; @endphp
                                    @foreach($types as $t)
                                        <span class="badge bg-warning text-dark mr-1">{{ $t }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $m->message ? 
                                    (mb_strlen($m->message) > 80 ? mb_substr($m->message,0,80).'…' : $m->message)
                                 : 'Attachment / empty' }}</td>
                                <td>#{{ $m->conversation_id }}</td>
                                <td>
                                    <div>{{ $m->created_at?->toDateTimeString() }}</div>
                                    <form method="POST" action="{{ route('chat.flagged.warn', $m->id) }}" class="mt-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Send Warning Email</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No flagged messages.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $messages->links() }}</div>
        </div>
    </div>
</x-master-layout>

