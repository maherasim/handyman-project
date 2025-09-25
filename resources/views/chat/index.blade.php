<x-master-layout>
    <div class="container py-3">
        <div class="d-flex align-items-center mb-3">
            <h5 class="mb-0">Messages</h5>
            <a href="{{ route('chat.unread.ping') }}" class="ms-auto btn btn-sm btn-outline-secondary">Refresh</a>
        </div>
        <div class="card">
            <div class="list-group list-group-flush">
                @forelse($items as $c)
                    <a href="{{ $c['url'] ?? (isset($c['bid_id']) && $c['bid_id'] ? route('chat.view.bid', $c['bid_id']) : '#') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                        <img src="{{ $c['other_avatar'] ?? asset('images/user/user.png') }}" class="rounded-circle" style="width:40px;height:40px;object-fit:cover;">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center">
                                <div class="fw-bold">{{ $c['other_name'] ?? 'User' }}</div>
                                @if($c['unread'] > 0)
                                    <span class="badge bg-danger ms-2">{{ $c['unread'] }}</span>
                                @endif
                                <div class="ms-auto small text-muted">{{ $c['last_at'] }}</div>
                            </div>
                            <div class="small text-muted">{{ $c['title'] ?? $c['bid_title'] }}</div>
                            <div class="small">{{ $c['last_snippet'] }}</div>
                        </div>
                    </a>
                @empty
                    <div class="list-group-item text-center text-muted">No conversations yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const last = sessionStorage.getItem('chat_toast_last_id') || '0';
            fetch('{{ route('chat.unread.ping') }}').then(r=>r.json()).then(j=>{
                if (j && j.latest && j.latest.id && j.latest.id.toString() !== last.toString()){
                    sessionStorage.setItem('chat_toast_last_id', j.latest.id);
                    if (j.latest.sender_name){
                        const text = `${j.latest.sender_name}: ${j.latest.snippet || ''}`;
                        if (window.Swal){ Swal.fire({ toast:true, position:'bottom-end', timer:3500, showConfirmButton:false, icon:'info', title: text }); }
                        if (window.__playChatNotify) window.__playChatNotify();
                    }
                }
            }).catch(()=>{});
        });
    </script>
</x-master-layout>

