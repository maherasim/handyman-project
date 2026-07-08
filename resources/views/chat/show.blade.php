<x-master-layout>
    @php
        $auth = auth()->user();
        $fallbackAvatar = asset('images/user/user.png');
    @endphp
    <div class="container-fluid" style="min-height: 100vh;">
        <div class="row">
            <div class="col-12 col-md-4 col-lg-3 border-end d-flex flex-column p-0 mb-3 mb-md-0">
                <div class="p-3 border-bottom">
                    <h5 class="mb-0">{{ __('messages.messages') }}</h5>
                    @php
                        $headerLine = '';
                        if (isset($bid)) {
                            $headerLine = __('messages.bid') . ' #' . ($bid->id ?? '-') . ' — ' . (($bid->postrequest->title ?? null) ?: __('messages.post'));
                        } elseif (isset($booking)) {
                            $serviceName = optional(optional($booking)->service)->name ?? __('messages.booking');
                            $headerLine = __('messages.booking') . ' #' . ($booking->id ?? '-') . ' — ' . $serviceName;
                        } elseif (isset($isStandaloneChat) && $isStandaloneChat) {
                            $headerLine = __('messages.chat_direct_message');
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
                                <div class="text-muted small">{{ __('messages.' . ($targetUser->user_type ?? 'user')) }}</div>
                            </div>
                        </div>
                    @elseif(isset($isHandymanChat) && $isHandymanChat && isset($handyman))
                        <div class="d-flex align-items-center gap-2 p-2 rounded hover-bg cursor-pointer active">
                            <div>
                                <img src="{{ getSingleMedia($handyman, 'profile_image', null) ?? $fallbackAvatar }}" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold mb-0 small">{{ $handyman->display_name }}</div>
                                <div class="text-muted small">{{ __('messages.handyman') }}</div>
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
                                    <div class="text-muted small">{{ __('messages.provider') }}</div>
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
                                    <div class="text-muted small">{{ __('messages.customer') }}</div>
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
                    <div class="ms-auto small text-muted" id="typingDot" style="display:none;">{{ __('messages.chat_typing') }}</div>
                </div>
                <div id="msgScroll" class="flex-grow-1 overflow-auto p-3 bg-light" style="position:relative; min-height: 300px;">
                    <div id="loadMoreTop" class="text-center mb-2">
                        <button class="btn btn-sm btn-outline-secondary" id="loadOlderBtn">{{ __('messages.chat_load_older') }}</button>
                    </div>
                    <div id="messages"></div>
                </div>
                <div class="border-top p-2" style="position: sticky; bottom: 0; background: #fff; z-index: 2;">
                    <form id="composer" class="d-flex align-items-center gap-2">
                        <input type="file" id="fileInput" class="form-control" style="max-width:260px;">
                        <input type="text" id="textInput" class="form-control" placeholder="{{ __('messages.chat_type_message') }}">
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

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // ── Client-side PII pre-check (mirrors PiiDetector.php) ──────────────
        function clientPiiCheck(text) {
            if (!text) return false;
            const t = text.toLowerCase();

            // Normalise obfuscation
            let norm = t
                .replace(/\b(at the rate|at)\b/g, '@')
                .replace(/[\[{(]\s*at\s*[\]})]|@/g, '@')
                .replace(/\b(dot|punkt)\b/g, '.')
                .replace(/[\[{(]\s*dot\s*[\]})]|\./g, '.')
                .replace(/\s*(@|\.)\s*/g, '$1')
                .replace(/g\s*mail/g,'gmail').replace(/y\s*ahoo/g,'yahoo')
                .replace(/hot\s*mail/g,'hotmail').replace(/out\s*look/g,'outlook')
                .replace(/proton\s*mail/g,'protonmail').replace(/i\s*cloud/g,'icloud')
                .replace(/y\s*andex/g,'yandex').replace(/z\s*o\s*h\s*o/g,'zoho')
                .replace(/web\s*\.?\s*de/g,'web.de').replace(/t\s*-?\s*online/g,'t-online');

            const emailRe = /[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}/i;
            if (emailRe.test(t) || emailRe.test(norm)) return true;

            // Email providers
            if (['gmail','yahoo','hotmail','outlook','icloud','protonmail','ymail','gmx','aol',
                 'mail.com','yandex','zoho','web.de','gmx.de','gmx.net','t-online',
                 'freenet','posteo','mailbox.org'].some(p => t.includes(p))) return true;

            // Phone — numeric (7+ digits)
            const digits = (t.match(/\d/g) || []).length;
            if (digits >= 7 && /(?:(?:\+|00)?\d{1,3}[\s.\-]?)?(?:\(?\d{2,4}\)?[\s.\-]?)?\d{3,4}[\s.\-]?\d{3,4}/.test(t)) return true;
            // Space-separated single digits: "0 3 0 1 2 3 4 5 6"
            if (/(?<!\d)\d(\s\d){6,}(?!\d)/.test(t)) return true;

            // English spelled-out digits
            const enMap = {zero:'0',oh:'0',one:'1',two:'2',three:'3',four:'4',five:'5',six:'6',seven:'7',eight:'8',nine:'9'};
            let cnt = 0, rep = 1;
            for (const w of t.split(/[^a-z0-9+]+/).filter(Boolean)) {
                if (w==='double'){rep=2;continue;} if(w==='triple'){rep=3;continue;}
                const d = enMap[w] ? enMap[w].repeat(rep) : (/^\+?\d+$/.test(w) ? w.replace(/\D/g,'').repeat(rep) : '');
                if (d) { cnt += d.length; if (cnt>=7) return true; } else cnt=0;
                rep=1;
            }

            // German spelled-out digits
            const deMap = {null:'0',nul:'0',eins:'1',ein:'1',zwei:'2',drei:'3',vier:'4',
                           fuenf:'5',fünf:'5',sechs:'6',sieben:'7',acht:'8',neun:'9',
                           zehn:'00',zwanzig:'00',dreissig:'000',dreißig:'000'};
            cnt = 0;
            for (const w of t.split(/[\s,\-\/]+/).filter(Boolean)) {
                if (deMap[w]) { cnt += deMap[w].length; if (cnt>=7) return true; }
                else if (/^\d+$/.test(w)) { cnt += w.length; if (cnt>=7) return true; }
                else cnt = 0;
            }

            // Messaging platforms
            if (['whatsapp','wa.me','telegram','t.me/','skype','viber','wechat',
                 'discord','snapchat','instagram','linkedin','twitter','x.com/',
                 'signal.org','zoom.us','facebook','messenger','fb.com','m.me/'].some(p=>t.includes(p))) return true;
            if (/\bsignal\s*(app|me|id|\.org)\b|\bget\s+signal\b/i.test(t)) return true;
            if (/\bline\s*(app|id|me)\b|\bmy\s+line\s+(is|:)/i.test(t)) return true;
            if (/\b(ms\s+teams|microsoft\s+teams|teams\s+id)\b/i.test(t)) return true;
            if (/\bzoom\s*(id|link|meeting|call)\b|\bjoin\s+(my\s+)?zoom\b/i.test(t)) return true;
            if (/\b\w{2,32}#\d{4}\b/.test(t)) return true; // Discord tag
            if (/\bsnapchat\b|\bmy\s+snap\b/i.test(t)) return true;
            if (/\binstagram\b|\bmy\s+insta\b|\big\s*:/i.test(t)) return true;

            // Any URL
            if (/https?:\/\//i.test(t) || t.includes('calendly.com') || t.includes('cal.com')) return true;

            // @handle (not already caught as email)
            if (!emailRe.test(t) && /@[a-z0-9_\.]{3,}/i.test(t)) return true;

            // Contact intent phrases
            if (['call me','reach me','contact me','find me on','message me on','dm me',
                 'text me','add me on','ping me','hit me up','connect on',
                 'ruf mich an','schreib mir','kontaktiere mich','füge mich hinzu'].some(p=>t.includes(p))) return true;

            return false;
        }

        document.addEventListener('DOMContentLoaded', () => {
            const conversationId = {{ $conversation->id }};
            const currentUserId = {{ (int) auth()->id() }};
            const chatPiiWarning = {!! json_encode(__('messages.chat_pii_warning')) !!};
            const chatPiiWarningBubble = {!! json_encode(__('messages.chat_pii_warning_bubble')) !!};
            const chatPolicyViolationTitle = {!! json_encode(__('messages.chat_policy_violation_title')) !!};
            const chatOkText = {!! json_encode(__('messages.ok')) !!};
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
                const isViolation = !!(m.hidden || m.policy_violation || m.violation_message || (m.pii_types && m.pii_types.length && !m.message && !m.attachment));
                const bubbleCls = isViolation ? 'bg-white border border-danger policy-warning-bubble text-danger' : (mine ? 'bg-primary text-white' : 'bg-white border');
                bubble.className = 'p-2 rounded ' + bubbleCls;
                let html = '';
                const name = safe(m.sender_name || @json(__('messages.user')));
                const avatar = safe(m.sender_avatar_url || '{{ $fallbackAvatar }}');
                const violationText = safe(m.violation_message || chatPiiWarningBubble || @json(__('messages.chat_policy_violation_hidden')));
                html += `<div class="d-flex align-items-center mb-1 ${isViolation ? 'text-danger' : ''}">`+
                    `<img src="${avatar}" class="rounded-circle me-2" style="width:22px;height:22px;object-fit:cover;">`+
                    `<span class="small fw-bold">${name}</span>`+
                    `</div>`;
                if (isViolation) {
                    html += `<div class="small"><i class="fas fa-exclamation-triangle me-1"></i> ${violationText}</div>`;
                    const warn = document.getElementById('policyWarning');
                    if (warn) {
                        warn.textContent = chatPiiWarning;
                        warn.classList.remove('d-none');
                    }
                } else if (m.message) {
                    html += `<div class="small">${safe(m.message)}</div>`;
                }
                if (m.attachment) {
                    const name = safe(m.attachment.name || @json(__('messages.attachment')));
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
                        const isViolation = !!(m.hidden || m.policy_violation || m.violation_message || (m.pii_types && m.pii_types.length && !m.message && !m.attachment));
                        const wrap = document.createElement('div');
                        const mine = m.sender_id === currentUserId;
                        wrap.className = 'd-flex mb-2 ' + (mine ? 'justify-content-end' : 'justify-content-start');
                        const bubble = document.createElement('div');
                        bubble.className = 'p-2 rounded ' + (isViolation ? 'bg-white border border-danger policy-warning-bubble text-danger' : (mine ? 'bg-primary text-white' : 'bg-white border'));
                        const violationText = safe(m.violation_message || chatPiiWarningBubble || @json(__('messages.chat_policy_violation_hidden')));
                        let html = '';
                        const name = safe(m.sender_name || @json(__('messages.user')));
                        const avatar = safe(m.sender_avatar_url || '{{ $fallbackAvatar }}');
                        html += `<div class="d-flex align-items-center mb-1">`+
                            `<img src="${avatar}" class="rounded-circle me-2" style="width:22px;height:22px;object-fit:cover;">`+
                            `<span class="small fw-bold">${name}</span>`+
                            `</div>`;
                        if (isViolation) {
                            html += `<div class=\"small text-danger\"><i class=\"fas fa-exclamation-triangle me-1\"></i> ${violationText}</div>`;
                        } else {
                            if (m.message) { html += `<div class=\"small\">${safe(m.message)}</div>`; }
                            if (m.attachment) { const attName = safe(m.attachment.name || @json(__('messages.attachment'))); html += `<div class=\"mt-1\"><a href=\"${m.attachment.download_url}\" target=\"_blank\" class=\"text-decoration-underline ${mine ? 'text-white' : ''}\"><i class=\"fas fa-paperclip\"></i> ${attName}</a></div>`; }
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

            document.getElementById('composer').addEventListener('submit', async (e) => {
                e.preventDefault();

                // Prevent double submission
                if (isSending) {
                    console.log('Message already sending, ignoring duplicate submit');
                    return;
                }

                const fd = new FormData();
                const text = (textInput.value || '').trim();
                if (text) fd.append('message', text);
                if (fileInput.files && fileInput.files[0]) fd.append('attachment', fileInput.files[0]);
                
                // Set sending flag and disable form
                isSending = true;
                sendBtn.disabled = true;
                sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                textInput.disabled = true;

                // Optimistic rendering: show text message immediately before server responds
                const hasFile = fileInput.files && fileInput.files[0];
                let optimisticEl = null;
                if (text && !hasFile) {
                    textInput.value = '';
                    optimisticEl = document.createElement('div');
                    optimisticEl.className = 'd-flex mb-2 justify-content-end';
                    const optBubble = document.createElement('div');
                    optBubble.className = 'p-2 rounded bg-primary text-white opacity-75';
                    const ts = new Date().toISOString().slice(0, 19).replace('T', ' ');
                    optBubble.innerHTML = `<div class="d-flex align-items-center mb-1"><span class="small fw-bold">{{ $auth->display_name }}</span></div><div class="small">${safe(text)}</div><div class="text-end small opacity-75 mt-1">${ts} <i class="fas fa-clock"></i></div>`;
                    optimisticEl.appendChild(optBubble);
                    messagesEl.appendChild(optimisticEl);
                    msgScroll.scrollTop = msgScroll.scrollHeight;
                }

                fetch(sendUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: fd
                }).then(r => r.json()).then(j => {
                    if (j && j.status) {
                        if (!optimisticEl) textInput.value = '';
                        if (fileInput) fileInput.value = '';
                        previewEl.style.display = 'none';
                        previewEl.innerHTML = '';
                        // Remove optimistic bubble before pollNewer renders the real message
                        if (optimisticEl && optimisticEl.parentNode) {
                            optimisticEl.parentNode.removeChild(optimisticEl);
                            optimisticEl = null;
                        }
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
                                Swal.fire({ icon:'warning', title: chatPolicyViolationTitle, text: chatPiiWarning });
                            }
                        }
                        pollNewer();
                    }
                }).catch(err => {
                    console.error('Error sending message:', err);
                    // Remove optimistic bubble and restore input on failure
                    if (optimisticEl && optimisticEl.parentNode) {
                        optimisticEl.parentNode.removeChild(optimisticEl);
                    }
                    if (text && !hasFile) textInput.value = text;
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

            // ── Pusher real-time ──────────────────────────────────────────
            let pusherChannel = null;
            let typingHideTimer = null;
            let pusherConnected = false;

            console.log('[Pusher] Starting init. conversationId=' + conversationId + ' userId=' + currentUserId);

            if (typeof Pusher !== 'undefined') {
                Pusher.logToConsole = true; // enable Pusher's own verbose logging

                const pusher = new Pusher('82c4fc5181123cb5e639', {
                    cluster: 'eu',
                    authEndpoint: '/broadcasting/auth',
                    auth: {
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    }
                });

                console.log('[Pusher] Instance created. Key=82c4fc5181123cb5e639 cluster=eu');

                pusher.connection.bind('state_change', (states) => {
                    console.log('[Pusher] Connection state: ' + states.previous + ' → ' + states.current);
                });

                pusher.connection.bind('error', (err) => {
                    console.error('[Pusher] Connection error:', err);
                });

                pusherChannel = pusher.subscribe('private-chat.{{ $conversation->id }}');
                console.log('[Pusher] Subscribed to channel: private-chat.{{ $conversation->id }}');

                pusherChannel.bind('pusher:subscription_succeeded', () => {
                    console.log('[Pusher] ✅ Subscription succeeded on private-chat.{{ $conversation->id }}');
                });

                pusherChannel.bind('pusher:subscription_error', (err) => {
                    console.error('[Pusher] ❌ Subscription FAILED on private-chat.{{ $conversation->id }}:', err);
                });

                // Incoming message — render instantly, skip polling
                pusherChannel.bind('ChatMessageSent', (data) => {
                    console.log('[Pusher] ChatMessageSent received:', data);
                    if (data.sender_id === currentUserId) return; // we already did optimistic render
                    if (data.id && data.id <= newestId) return;  // dedup
                    newestId = Math.max(newestId, data.id || 0);
                    renderMessage(data);
                    msgScroll.scrollTop = msgScroll.scrollHeight;
                    playNotify();
                    typingEl.style.display = 'none';
                });

                // Other person is typing
                pusherChannel.bind('client-typing', (data) => {
                    console.log('[Pusher] ✅ client-typing callback fired!', data);
                    console.log('[Pusher] typingEl:', typingEl);
                    if (!typingEl) { console.error('[Pusher] typingEl is NULL'); return; }
                    typingEl.removeAttribute('style');
                    typingEl.style.setProperty('display', 'inline-block', 'important');
                    console.log('[Pusher] typingEl computed display:', window.getComputedStyle(typingEl).display);
                    clearTimeout(typingHideTimer);
                    typingHideTimer = setTimeout(() => { typingEl.style.display = 'none'; }, 3000);
                });

                pusher.connection.bind('connected', () => {
                    pusherConnected = true;
                    console.log('[Pusher] ✅ Connected to Pusher');
                    clearInterval(pollTimer);
                    pollTimer = setInterval(pollNewer, 15000);
                });

                pusher.connection.bind('disconnected', () => {
                    pusherConnected = false;
                    console.warn('[Pusher] ⚠️ Disconnected — falling back to 3s polling');
                    clearInterval(pollTimer);
                    pollTimer = setInterval(pollNewer, 3000);
                });
            } else {
                console.error('[Pusher] ❌ Pusher library not loaded — check CDN script tag');
            }

            // Typing — send client event to Pusher so the OTHER person sees it
            textInput.addEventListener('input', () => {
                clearTimeout(typingTimer);
                if (pusherChannel && pusherConnected) {
                    console.log('[Pusher] Sending client-typing event');
                    try { pusherChannel.trigger('client-typing', {}); } catch(e) {
                        console.error('[Pusher] Failed to trigger client-typing:', e);
                    }
                } else {
                    console.log('[Pusher] Typing not sent — channel=' + !!pusherChannel + ' connected=' + pusherConnected);
                }
                typingTimer = setTimeout(() => {}, 1200);
            });

            // init
            fetchInitial();
            pollTimer = setInterval(pollNewer, 3000);
        });
    </script>
</x-master-layout>

