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
                    @php
                        $headerLine = '';
                        if (isset($bid)) {
                            $headerLine = 'Bid #' . ($bid->id ?? '-') . ' — ' . (($bid->postrequest->title ?? null) ?: 'Post');
                        } elseif (isset($booking)) {
                            $serviceName = optional(optional($booking)->service)->name ?? 'Booking';
                            $headerLine = 'Booking #' . ($booking->id ?? '-') . ' — ' . $serviceName;
                        } elseif (isset($isStandaloneChat) && $isStandaloneChat) {
                            $headerLine = 'Direct Message';
                        }
                        $providerObj = isset($bid) ? optional($bid->provider) : (isset($booking) ? optional($booking->provider) : null);
                        $customerObj = isset($bid) ? optional($bid->customer) : (isset($booking) ? optional($booking->customer) : null);
                    @endphp
                    @if($headerLine !== '')
                        <div class="text-muted small">{{ $headerLine }}</div>
                    @endif
                </div>
                <div class="flex-grow-1 overflow-auto p-2" id="threadList">
                    @if(isset($isStandaloneChat) && $isStandaloneChat && isset($targetUser))
                        <div class="d-flex align-items-center gap-2 p-2 rounded hover-bg cursor-pointer active">
                            <div>
                                <img src="{{ getSingleMedia($targetUser, 'profile_image', null) ?? $fallbackAvatar }}" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold mb-0 small">{{ $targetUser->display_name }}</div>
                                <div class="text-muted small">{{ ucfirst($targetUser->user_type ?? 'User') }}</div>
                            </div>
                        </div>
                    @elseif(isset($isHandymanChat) && $isHandymanChat && isset($handyman))
                        <div class="d-flex align-items-center gap-2 p-2 rounded hover-bg cursor-pointer active">
                            <div>
                                <img src="{{ getSingleMedia($handyman, 'profile_image', null) ?? $fallbackAvatar }}" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold mb-0 small">{{ $handyman->display_name }}</div>
                                <div class="text-muted small">Handyman</div>
                            </div>
                        </div>
                    @else
                        @if($providerObj)
                            <div class="d-flex align-items-center gap-2 p-2 rounded hover-bg cursor-pointer active">
                                <div>
                                    <img src="{{ getSingleMedia($providerObj, 'profile_image', null) ?? $fallbackAvatar }}" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold mb-0 small">{{ $providerObj->display_name }}</div>
                                    <div class="text-muted small">Provider</div>
                                </div>
                            </div>
                        @endif
                        @if($customerObj)
                            <div class="d-flex align-items-center gap-2 p-2 rounded hover-bg cursor-pointer active mt-1">
                                <div>
                                    <img src="{{ getSingleMedia($customerObj, 'profile_image', null) ?? $fallbackAvatar }}" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold mb-0 small">{{ $customerObj->display_name }}</div>
                                    <div class="text-muted small">Customer</div>
                                </div>
                            </div>
                        @endif
                    @endif
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
                    <div id="policyWarning" class="alert alert-warning py-2 px-3 d-none mt-2" role="alert"></div>
                    <div id="attachmentPreview" class="mt-2" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-bg:hover { background: #f8f9fa; }
        .cursor-pointer { cursor: pointer; }
        .policy-warning-bubble { box-shadow: 0 0 0 2px rgba(220,53,69,.1) inset; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const conversationId = {{ $conversation->id }};
            const currentUserId = {{ (int) auth()->id() }};
            const chatPiiWarning = {!! json_encode(__('messages.chat_pii_warning')) !!};
            const chatPiiWarningBubble = {!! json_encode(__('messages.chat_pii_warning_bubble')) !!};
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
            let isSending = false; // Flag to prevent double submissions

            // Lightweight audio ringtone via Web Audio API with external fallback
            let audioCtx = null;
            const externalAudio = document.getElementById('chatRingtone');
            function initAudio() {
                if (audioCtx) return;
                const Ctx = window.AudioContext || window.webkitAudioContext;
                if (Ctx) audioCtx = new Ctx();
            }
            function playNotify() {
                if (externalAudio) {
                    try { externalAudio.currentTime = 0; externalAudio.play(); return; } catch(e) {}
                }
                try {
                    if (!audioCtx) return;
                    if (audioCtx.state === 'suspended') { audioCtx.resume(); }
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'sine';
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    const now = audioCtx.currentTime;
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
                const isViolation = !!(m.hidden && m.policy_violation);
                const bubbleCls = isViolation ? 'bg-white border border-danger policy-warning-bubble text-danger' : (mine ? 'bg-primary text-white' : 'bg-white border');
                bubble.className = 'p-2 rounded ' + bubbleCls;
                let html = '';
                const name = safe(m.sender_name || 'User');
                const avatar = safe(m.sender_avatar_url || '{{ $fallbackAvatar }}');
                html += `<div class="d-flex align-items-center mb-1 ${isViolation ? 'text-danger' : ''}">`+
                    `<img src="${avatar}" class="rounded-circle me-2" style="width:22px;height:22px;object-fit:cover;">`+
                    `<span class="small fw-bold">${name}</span>`+
                    `</div>`;
                if (isViolation) {
                    html += `<div class="small"><i class="fas fa-exclamation-triangle me-1"></i> ${safe(chatPiiWarningBubble)}</div>`;
                    const warn = document.getElementById('policyWarning');
                    if (warn) {
                        warn.textContent = chatPiiWarning;
                        warn.classList.remove('d-none');
                    }
                } else if (m.message) {
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
                        if (m.hidden && m.policy_violation) {
                            const reason = (m.pii_types && m.pii_types.length) ? m.pii_types.join(', ') : 'policy violation';
                            html += `<div class=\"small text-danger\"><i class=\"fas fa-shield-alt\"></i> Message hidden due to ${safe(reason)}.</div>`;
                        } else {
                            if (m.message) { html += `<div class=\"small\">${safe(m.message)}</div>`; }
                            if (m.attachment) { const name = safe(m.attachment.name || 'attachment'); html += `<div class=\"mt-1\"><a href=\"${m.attachment.download_url}\" target=\"_blank\" class=\"text-decoration-underline ${mine ? 'text-white' : ''}\"><i class=\"fas fa-paperclip\"></i> ${name}</a></div>`; }
                        }
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
                
                // Prevent double submission
                if (isSending) {
                    console.log('Message already sending, ignoring duplicate submit');
                    return;
                }
                
                const fd = new FormData();
                const text = (textInput.value || '').trim();
                // Client-side quick PII check
                const lower = text.toLowerCase();
                const emailRe = /[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i;
                const phoneRe = /(?:(?:\+|00)?\d{1,3}[\s.-]?)?(?:\(?\d{2,4}\)?[\s.-]?)?\d{3,4}[\s.-]?\d{3,4}/;
                const hasMinDigits = (text.match(/\d/g) || []).length >= 7;
                const isWhats = lower.includes('whatsapp') || lower.includes('wa.me/') || lower.includes('api.whatsapp.com');
                const mentionsEmailProviders = ['gmail','yahoo','hotmail','outlook','icloud','protonmail','ymail','gmx','aol','mail.com','yandex','zoho'].some(p => lower.includes(p));
                const mentionsInstagram = lower.includes('instagram.com') || /\binsta(?:gram)?\b/i.test(text);
                const mentionsFacebook = lower.includes('facebook.com') || lower.includes('fb.com') || lower.includes('m.me/') || lower.includes('messenger.com') || lower.includes('facebook');
                // Basic obfuscation normalization for email
                const norm = lower
                    .replace(/\b(at the rate|at)\b/g, '@')
                    .replace(/[\[{(]\s*at\s*[\]})]/g, '@')
                    .replace(/\b(dot)\b/g, '.')
                    .replace(/[\[{(]\s*dot\s*[\]})]/g, '.')
                    .replace(/\s*(@|\.)\s*/g, '$1')
                    .replace(/g\s*mail/g, 'gmail')
                    .replace(/y\s*ahoo/g, 'yahoo')
                    .replace(/hot\s*mail/g, 'hotmail')
                    .replace(/out\s*look/g, 'outlook')
                    .replace(/proton\s*mail/g, 'protonmail')
                    .replace(/i\s*cloud/g, 'icloud')
                    .replace(/y\s*andex/g, 'yandex')
                    .replace(/z\s*o\s*h\s*o/g, 'zoho');
                const emailObfuscated = emailRe.test(norm);

                // Spelled-out phone detection: count digits reconstructed
                const words = lower.split(/[^a-z0-9+]+/).filter(Boolean);
                const map = {zero:'0', oh:'0', o:'0', one:'1', two:'2', three:'3', four:'4', five:'5', six:'6', seven:'7', eight:'8', nine:'9'};
                let count = 0, rep = 1;
                for (const w of words) {
                  if (w === 'double') { rep = 2; continue; }
                  if (w === 'triple') { rep = 3; continue; }
                  let add = '';
                  if (map[w]) add = map[w].repeat(rep);
                  else if (/^\+?\d+$/.test(w)) add = (w.replace(/\D/g,'')).repeat(rep);
                  if (add) { count += add.length; if (count >= 7) break; }
                  rep = 1;
                }
                const phoneSpelled = count >= 7;

                const violate = text && (emailRe.test(text) || emailObfuscated || mentionsEmailProviders || (phoneRe.test(text) && hasMinDigits) || phoneSpelled || isWhats || lower.includes('telegram') || lower.includes('t.me/') || mentionsInstagram || mentionsFacebook);
                if (violate) {
                    const warn = document.getElementById('policyWarning');
                    if (warn) {
                        warn.textContent = chatPiiWarning;
                        warn.classList.remove('d-none');
                    }
                    if (window.Swal) {
                        Swal.fire({ icon:'warning', title:'Policy violation', text: chatPiiWarning, confirmButtonText:'OK' });
                    }
                }
                if (text) fd.append('message', text);
                if (fileInput.files && fileInput.files[0]) fd.append('attachment', fileInput.files[0]);
                
                // Set sending flag and disable form
                isSending = true;
                sendBtn.disabled = true;
                sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                textInput.disabled = true;
                
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
                        if (j.flagged) {
                            const warn = document.getElementById('policyWarning');
                            if (warn) {
                                warn.textContent = chatPiiWarning;
                                warn.classList.remove('d-none');
                            }
                            // Immediately render a local warning bubble so user sees it without waiting for poll
                            renderMessage({
                                id: (new Date().getTime()),
                                sender_id: currentUserId,
                                sender_name: @json($auth->display_name),
                                sender_avatar_url: @json(getSingleMedia(optional($auth), 'profile_image', null) ?? $fallbackAvatar),
                                message: null,
                                created_at: new Date().toISOString().slice(0,19).replace('T',' '),
                                read: true,
                                attachment: null,
                                policy_violation: true,
                                hidden: true,
                                pii_types: j.pii_types || []
                            });
                            if (window.Swal) {
                                Swal.fire({ icon:'warning', title:'Policy violation', text: chatPiiWarning });
                            }
                        }
                        pollNewer();
                    }
                }).catch(err => {
                    console.error('Error sending message:', err);
                }).finally(() => {
                    // Re-enable form after request completes
                    isSending = false;
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
                    textInput.disabled = false;
                    textInput.focus();
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

