<x-master-layout>
    @php
        $auth = auth()->user();
        $fallbackAvatar = asset('images/user/user.png');
    @endphp
    <div class="container-fluid" style="min-height: 100vh;">
        <div class="row">
            <div class="col-12 col-md-4 col-lg-3 border-end d-flex flex-column p-0 mb-3 mb-md-0">
                <div class="p-3 border-bottom">
                    <h5 class="mb-0">Messages</h5>
                    <div class="text-muted small">Bid #{{ $bid->id }} — {{ $bid->postrequest->title ?? 'Post' }}</div>
                </div>
                <div class="flex-grow-1 overflow-auto p-2" id="threadList">
                    <div class="d-flex align-items-center gap-2 p-2 rounded hover-bg cursor-pointer active">
                        <div>
                            <img src="{{ getSingleMedia(optional($bid->provider), 'profile_image', null) ?? $fallbackAvatar }}" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold mb-0 small">{{ optional($bid->provider)->display_name }}</div>
                            <div class="text-muted small">Provider</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 p-2 rounded hover-bg cursor-pointer active mt-1">
                        <div>
                            <img src="{{ getSingleMedia(optional($bid->customer), 'profile_image', null) ?? $fallbackAvatar }}" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold mb-0 small">{{ optional($bid->customer)->display_name }}</div>
                            <div class="text-muted small">Customer</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-8 col-lg-9 d-flex flex-column p-0">
                <div class="p-3 border-bottom d-flex align-items-center gap-2">
                    <img src="{{ getSingleMedia(optional($auth), 'profile_image', null) ?? $fallbackAvatar }}" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                    <div class="fw-bold">{{ $auth->display_name }}</div>
                    <div class="ms-auto small text-muted" id="typingDot" style="display:none;">typing...</div>
                </div>
                <div id="msgScroll" class="flex-grow-1 overflow-auto p-3 bg-light" style="position:relative; min-height: 300px;">
                    <div id="loadMoreTop" class="text-center mb-2">
                        <button class="btn btn-sm btn-outline-secondary" id="loadOlderBtn">Load older</button>
                    </div>
                    <div id="messages"></div>
                </div>
                <div class="border-top p-2" style="position: sticky; bottom: 0; background: #fff; z-index: 2;">
                    <form id="composer" class="d-flex align-items-center gap-2">
                        <input type="file" id="fileInput" class="form-control" style="max-width:260px;">
                        <input type="text" id="textInput" class="form-control" placeholder="Type a message...">
                        <button class="btn btn-primary" id="sendBtn" type="submit"><i class="fas fa-paper-plane"></i></button>
                    </form>
                    <div id="attachmentPreview" class="mt-2" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-bg:hover { background: #f8f9fa; }
        .cursor-pointer { cursor: pointer; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const conversationId = {{ $conversation->id }};
            const currentUserId = {{ (int) auth()->id() }};
            const messagesEl = document.getElementById('messages');
            const msgScroll = document.getElementById('msgScroll');
            const textInput = document.getElementById('textInput');
            const fileInput = document.getElementById('fileInput');
            const sendBtn = document.getElementById('sendBtn');
            const typingEl = document.getElementById('typingDot');
            const previewEl = document.getElementById('attachmentPreview');

            const messagesUrl = `{{ route('chat.messages', ':cid') }}`.replace(':cid', conversationId);
            const sendUrl = `{{ route('chat.send', ':cid') }}`.replace(':cid', conversationId);

            let oldestId = 0;
            let newestId = 0;
            let pollTimer = null;
            let typingTimer = null;

            // Lightweight audio ringtone via Web Audio API (no external asset)
            let audioCtx = null;
            function initAudio() {
                if (audioCtx) return;
                const Ctx = window.AudioContext || window.webkitAudioContext;
                if (Ctx) audioCtx = new Ctx();
            }
            function playNotify() {
                try {
                    if (!audioCtx) return;
                    if (audioCtx.state === 'suspended') { audioCtx.resume(); }
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'sine';
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    const now = audioCtx.currentTime;
                    // Two short beeps for a unique tone
                    osc.frequency.setValueAtTime(880, now);
                    gain.gain.setValueAtTime(0.0001, now);
                    gain.gain.exponentialRampToValueAtTime(0.12, now + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.12);
                    osc.frequency.setValueAtTime(1320, now + 0.14);
                    gain.gain.exponentialRampToValueAtTime(0.12, now + 0.16);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.26);
                    osc.start(now);
                    osc.stop(now + 0.28);
                } catch (e) { /* ignore */ }
            }
            const unlockAudio = () => { initAudio(); };
            window.addEventListener('click', unlockAudio);
            window.addEventListener('keydown', unlockAudio);

            const safe = (t) => (t || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

            function renderMessage(m) {
                const wrap = document.createElement('div');
                const mine = m.sender_id === currentUserId;
                wrap.className = 'd-flex mb-2 ' + (mine ? 'justify-content-end' : 'justify-content-start');
                const bubble = document.createElement('div');
                bubble.className = 'p-2 rounded ' + (mine ? 'bg-primary text-white' : 'bg-white border');
                let html = '';
                const name = safe(m.sender_name || 'User');
                const avatar = safe(m.sender_avatar_url || '{{ $fallbackAvatar }}');
                html += `<div class="d-flex align-items-center mb-1">`+
                    `<img src="${avatar}" class="rounded-circle me-2" style="width:22px;height:22px;object-fit:cover;">`+
                    `<span class="small fw-bold">${name}</span>`+
                    `</div>`;
                if (m.message) {
                    html += `<div class="small">${safe(m.message)}</div>`;
                }
                if (m.attachment) {
                    const name = safe(m.attachment.name || 'attachment');
                    html += `<div class="mt-1"><a href="${m.attachment.download_url}" target="_blank" class="text-decoration-underline ${mine ? 'text-white' : ''}"><i class="fas fa-paperclip"></i> ${name}</a></div>`;
                }
                html += `<div class="text-end small opacity-75 mt-1">${safe(m.created_at || '')}${m.read ? ' · <i class="fas fa-check-double"></i>' : ''}</div>`;
                bubble.innerHTML = html;
                wrap.appendChild(bubble);
                messagesEl.appendChild(wrap);
            }

            function fetchInitial() {
                fetch(messagesUrl)
                    .then(r => r.json()).then(j => {
                        messagesEl.innerHTML = '';
                        (j.messages || []).forEach(m => {
                            oldestId = oldestId === 0 ? m.id : Math.min(oldestId, m.id);
                            newestId = Math.max(newestId, m.id);
                            renderMessage(m);
                        });
                        msgScroll.scrollTop = msgScroll.scrollHeight;
                    });
            }

            function fetchOlder() {
                if (!oldestId) return;
                const url = messagesUrl + '?before_id=' + oldestId + '&limit=30';
                fetch(url).then(r => r.json()).then(j => {
                    const atBottom = msgScroll.scrollHeight - msgScroll.scrollTop === msgScroll.clientHeight;
                    const prevHeight = msgScroll.scrollHeight;
                    const fragments = document.createDocumentFragment();
                    (j.messages || []).forEach(m => {
                        oldestId = Math.min(oldestId, m.id);
                        const wrap = document.createElement('div');
                        const mine = m.sender_id === currentUserId;
                        wrap.className = 'd-flex mb-2 ' + (mine ? 'justify-content-end' : 'justify-content-start');
                        const bubble = document.createElement('div');
                        bubble.className = 'p-2 rounded ' + (mine ? 'bg-primary text-white' : 'bg-white border');
                        let html = '';
                        const name = safe(m.sender_name || 'User');
                        const avatar = safe(m.sender_avatar_url || '{{ $fallbackAvatar }}');
                        html += `<div class="d-flex align-items-center mb-1">`+
                            `<img src="${avatar}" class="rounded-circle me-2" style="width:22px;height:22px;object-fit:cover;">`+
                            `<span class="small fw-bold">${name}</span>`+
                            `</div>`;
                        if (m.message) { html += `<div class="small">${safe(m.message)}</div>`; }
                        if (m.attachment) { const name = safe(m.attachment.name || 'attachment'); html += `<div class="mt-1"><a href="${m.attachment.download_url}" target="_blank" class="text-decoration-underline ${mine ? 'text-white' : ''}"><i class="fas fa-paperclip"></i> ${name}</a></div>`; }
                        html += `<div class="text-end small opacity-75 mt-1">${safe(m.created_at || '')}${m.read ? ' · <i class=\"fas fa-check-double\"></i>' : ''}</div>`;
                        bubble.innerHTML = html;
                        wrap.appendChild(bubble);
                        fragments.appendChild(wrap);
                    });
                    messagesEl.prepend(fragments);
                    if (!atBottom) {
                        msgScroll.scrollTop = msgScroll.scrollHeight - prevHeight;
                    }
                });
            }

            function pollNewer() {
                const url = newestId ? (messagesUrl + '?after_id=' + newestId) : messagesUrl;
                fetch(url).then(r => r.json()).then(j => {
                    let inbound = false;
                    (j.messages || []).forEach(m => {
                        oldestId = oldestId === 0 ? m.id : Math.min(oldestId, m.id);
                        newestId = Math.max(newestId, m.id);
                        if (m.sender_id !== currentUserId) inbound = true;
                        renderMessage(m);
                    });
                    if (j.messages && j.messages.length) {
                        msgScroll.scrollTop = msgScroll.scrollHeight;
                        if (inbound) playNotify();
                    }
                });
            }

            document.getElementById('loadOlderBtn').addEventListener('click', fetchOlder);

            document.getElementById('composer').addEventListener('submit', (e) => {
                e.preventDefault();
                const fd = new FormData();
                const text = (textInput.value || '').trim();
                if (text) fd.append('message', text);
                if (fileInput.files && fileInput.files[0]) fd.append('attachment', fileInput.files[0]);
                fetch(sendUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: fd
                }).then(r => r.json()).then(j => {
                    if (j && j.status) {
                        textInput.value = '';
                        if (fileInput) fileInput.value = '';
                        previewEl.style.display = 'none';
                        previewEl.innerHTML = '';
                        pollNewer();
                    }
                });
            });

            fileInput.addEventListener('change', () => {
                previewEl.innerHTML = '';
                if (fileInput.files && fileInput.files[0]) {
                    const f = fileInput.files[0];
                    previewEl.style.display = '';
                    previewEl.innerHTML = `<div class=\"small\"><i class=\"fas fa-paperclip\"></i> ${safe(f.name)} (${Math.round(f.size/1024)} KB)</div>`;
                } else {
                    previewEl.style.display = 'none';
                }
            });

            textInput.addEventListener('input', () => {
                clearTimeout(typingTimer);
                typingEl.style.display = '';
                typingTimer = setTimeout(() => typingEl.style.display = 'none', 1200);
            });

            // init
            fetchInitial();
            pollTimer = setInterval(pollNewer, 4000);
        });
    </script>
</x-master-layout>

