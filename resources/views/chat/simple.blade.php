<x-master-layout>
    @php
        $auth = auth()->user();
        $fallbackAvatar = asset('images/user/user.png');
    @endphp
    <div class="container-fluid chat-wrapper">
        <div class="row g-0">
            <!-- Chat Header -->
            <div class="col-12">
                <div class="chat-header">
                    <div class="d-flex align-items-center gap-3">
                        <a href="javascript:history.back()" class="btn btn-light btn-sm rounded-pill px-3 shadow-none">
                            <i class="fas fa-arrow-left me-1"></i> {{ __('messages.back') }}
                        </a>
                        <img src="{{ getSingleMedia($targetUser, 'profile_image', null) ?? $fallbackAvatar }}" 
                             class="rounded-circle chat-avatar">
                        <div>
                            <div class="chat-name">{{ $targetUser->display_name }}</div>
                            <div class="chat-sub">{{ __('messages.' . ($targetUser->user_type ?? 'user')) }}</div>
                        </div>
                        <div class="ms-auto">
                            <small class="text-muted" id="connectionStatus"></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="col-12">
                <div id="messagesContainer" class="chat-body">
                    <div id="messages"></div>
                </div>
            </div>

            <!-- Typing indicator — shown above composer when other person is typing -->
            <div class="col-12" id="typingIndicator" style="display:none;">
                <div class="typing-bubble">
                    <span id="typingName">{{ $targetUser->display_name }}</span>
                    <span class="typing-dots"><span></span><span></span><span></span></span>
                </div>
            </div>

            <!-- Composer -->
            <div class="col-12">
                <div class="chat-composer">
                    <form id="messageForm" class="composer-inner">
                        <input type="file" id="fileInput" class="d-none" accept="image/*,application/pdf,.doc,.docx">
                        <label for="fileInput" class="btn btn-light composer-btn" title="{{ __('messages.chat_attach') }}">
                            <i class="fas fa-paperclip"></i>
                        </label>
                        <input type="text" id="messageInput" class="composer-input" placeholder="{{ __('messages.chat_type_message') }}" required>
                        <button class="btn btn-primary composer-send" type="submit" aria-label="{{ __('messages.send') }}">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                    <div id="filePreview" class="mt-2" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const conversationId = {{ $conversation->id }};
            const currentUserId = {{ (int) auth()->id() }};
            const messagesContainer = document.getElementById('messagesContainer');
            const messagesEl = document.getElementById('messages');
            const messageForm = document.getElementById('messageForm');
            const messageInput = document.getElementById('messageInput');
            const fileInput = document.getElementById('fileInput');
            const filePreview = document.getElementById('filePreview');
            const typingIndicator = document.getElementById('typingIndicator');

            let pollTimer = null;
            let typingTimer = null;
            let typingHideTimer = null;
            let lastRenderedDate = null;
            let isSending = false;
            let pusherChannel = null;
            let pusherConnected = false;

            // Browser Notification Support (WhatsApp-like)
            let notificationPermission = Notification.permission;
            
            // Request notification permission if not granted
            if (notificationPermission === 'default') {
                Notification.requestPermission().then(permission => {
                    notificationPermission = permission;
                    if (permission === 'granted') {
                        console.log('Browser notifications enabled');
                        // Register service worker for background notifications
                        if ('serviceWorker' in navigator) {
                            navigator.serviceWorker.register('/sw.js').catch(err => console.log('SW registration failed:', err));
                        }
                    }
                });
            } else if (notificationPermission === 'granted' && 'serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js').catch(err => console.log('SW registration failed:', err));
            }

            // Function to show browser notification
            function showBrowserNotification(message) {
                console.log('showBrowserNotification called', {
                    permission: notificationPermission,
                    hasFocus: document.hasFocus(),
                    pathname: window.location.pathname,
                    message: message
                });
                
                if (notificationPermission !== 'granted') {
                    console.log('Notification skipped - permission not granted:', notificationPermission);
                    return;
                }
                
                // Only suppress if user is viewing THIS specific chat
                if (document.hasFocus() && window.location.pathname.includes('/chat/') && 
                    window.location.pathname.includes(`/chat/${conversationId}/`)) {
                    console.log('Notification skipped - user is viewing this chat');
                    return;
                }

                const notificationOptions = {
                    body: message.message || @json(__('messages.chat_new_attachment')),
                    icon: message.sender_avatar_url || '{{ $fallbackAvatar }}',
                    badge: '{{ asset("images/logo.png") }}',
                    tag: `chat-${conversationId}-${message.id}`,
                    data: {
                        conversation_id: conversationId,
                        message_id: message.id,
                        sender_id: message.sender_id,
                        url: `/chat/${conversationId}/messages`
                    },
                    requireInteraction: false,
                    vibrate: [200, 100, 200]
                };

                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.ready.then(registration => {
                        console.log('Showing notification via Service Worker');
                        registration.showNotification(
                            @json(__('messages.chat_new_message_from')) + ` ${message.sender_name || @json(__('messages.user'))}`,
                            notificationOptions
                        ).then(() => {
                            console.log('Notification shown successfully');
                        }).catch(err => {
                            console.error('Failed to show notification:', err);
                        });
                    }).catch(err => {
                        console.error('Service Worker not ready:', err);
                        // Fallback to direct notification
                        try {
                            new Notification(
                                @json(__('messages.chat_new_message_from')) + ` ${message.sender_name || @json(__('messages.user'))}`,
                                notificationOptions
                            );
                        } catch (e) {
                            console.error('Direct notification also failed:', e);
                        }
                    });
                } else {
                    console.log('Service Worker not supported, using direct notification');
                    try {
                        new Notification(
                            @json(__('messages.chat_new_message_from')) + ` ${message.sender_name || @json(__('messages.user'))}`,
                            notificationOptions
                        );
                    } catch (e) {
                        console.error('Direct notification failed:', e);
                    }
                }
            }

            // ── Pusher real-time ─────────────────────────────────────────
            if (typeof Pusher !== 'undefined') {
                Pusher.logToConsole = true;
                const pusher = new Pusher('82c4fc5181123cb5e639', {
                    cluster: 'eu',
                    authEndpoint: '/broadcasting/auth',
                    auth: { headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }
                });

                pusher.connection.bind('state_change', (s) => {
                    console.log('[Pusher] ' + s.previous + ' → ' + s.current);
                });
                pusher.connection.bind('error', (err) => {
                    console.error('[Pusher] connection error', err);
                });

                pusherChannel = pusher.subscribe('private-chat.' + conversationId);

                pusherChannel.bind('pusher:subscription_succeeded', () => {
                    console.log('[Pusher] ✅ Subscribed to private-chat.' + conversationId);
                });
                pusherChannel.bind('pusher:subscription_error', (err) => {
                    console.error('[Pusher] ❌ Subscription failed', err);
                });

                pusherChannel.bind('ChatMessageSent', (e) => {
                    console.log('[Pusher] ChatMessageSent received', e);
                    if (!e || e.sender_id === currentUserId) return;
                    typingIndicator.style.display = 'none';
                    maybeRenderDateSeparator(e.created_at);
                    renderMessage(e);
                    scrollToBottom();
                    showBrowserNotification(e);
                });

                pusherChannel.bind('client-typing', () => {
                    typingIndicator.style.display = 'block';
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    clearTimeout(typingHideTimer);
                    typingHideTimer = setTimeout(() => { typingIndicator.style.display = 'none'; }, 3000);
                });

                pusher.connection.bind('connected', () => {
                    pusherConnected = true;
                    console.log('[Pusher] ✅ Connected — slowing poll to 15s');
                    clearInterval(pollTimer);
                    pollTimer = setInterval(loadMessages, 15000);
                });
                pusher.connection.bind('disconnected', () => {
                    pusherConnected = false;
                    console.warn('[Pusher] ⚠️ Disconnected — fast poll 3s');
                    clearInterval(pollTimer);
                    pollTimer = setInterval(loadMessages, 3000);
                });
            } else {
                console.error('[Pusher] library not loaded');
            }

            // Load messages
            function loadMessages() {
                fetch(`/chat/${conversationId}/messages`)
                    .then(r => r.json())
                    .then(data => {
                        messagesEl.innerHTML = '';
                        lastRenderedDate = null;
                        (data.messages || []).forEach(message => {
                            maybeRenderDateSeparator(message.created_at);
                            renderMessage(message);
                        });
                        scrollToBottom();
                    })
                    .catch(err => console.error('Error loading messages:', err));
            }

            // Render a single message
            function renderMessage(message) {
                const isOwn = message.sender_id === currentUserId;
                const row = document.createElement('div');
                row.className = `chat-row ${isOwn ? 'is-own' : 'is-other'}`;
                
                const bubble = document.createElement('div');
                bubble.className = `chat-bubble ${isOwn ? 'own' : 'other'}`;
                
                let content = '';

                // Policy violation: message was hidden by the system
                if (message.policy_violation || message.hidden) {
                    content += `<span class="chat-violation">${@json(__('messages.chat_policy_violation_hidden'))}</span>`;
                } else {
                    // Message text
                    if (message.message) {
                        content += `<div class="chat-text">${escapeHtml(message.message)}</div>`;
                    }
                    
                    // File attachment
                    if (message.attachment) {
                        content += `<div class="mt-2">
                            <a href="${message.attachment.download_url}" target="_blank" class="chat-attach ${isOwn ? 'text-white' : ''}">
                                <i class="fas fa-paperclip me-1"></i> ${escapeHtml(message.attachment.name)}
                            </a>
                        </div>`;
                    }
                }
                
                // Timestamp
                content += `<div class="chat-time">${escapeHtml(message.created_at)}</div>`;
                
                bubble.innerHTML = content;
                
                if (!isOwn) {
                    const avatar = document.createElement('img');
                    avatar.src = message.sender_avatar_url || '{{ $fallbackAvatar }}';
                    avatar.className = 'chat-mini-avatar';
                    row.appendChild(avatar);
                } else {
                    const spacer = document.createElement('div');
                    spacer.style.width = '36px';
                    row.appendChild(spacer);
                }
                
                row.appendChild(bubble);
                messagesEl.appendChild(row);
            }

            // Render date separator when date changes
            function maybeRenderDateSeparator(createdAtStr) {
                try {
                    const d = new Date(createdAtStr.replace(' ', 'T'));
                    const key = isNaN(d.getTime()) ? createdAtStr.split(' ')[0] : d.toDateString();
                    if (lastRenderedDate !== key) {
                        lastRenderedDate = key;
                        const sep = document.createElement('div');
                        sep.className = 'chat-date-sep';
                        sep.innerHTML = `<span>${escapeHtml(formatDateForSep(d, createdAtStr))}</span>`;
                        messagesEl.appendChild(sep);
                    }
                } catch (_) {}
            }
            function formatDateForSep(d, fallback) {
                if (!isNaN(d.getTime())) {
                    const today = new Date();
                    const isToday = d.toDateString() === today.toDateString();
                    if (isToday) return @json(__('messages.today'));
                    const yesterday = new Date();
                    yesterday.setDate(today.getDate() - 1);
                    if (d.toDateString() === yesterday.toDateString()) return @json(__('messages.yesterday'));
                    return d.toLocaleDateString();
                }
                return fallback;
            }

            // Send message
            messageForm.addEventListener('submit', (e) => {
                e.preventDefault();
                
                // Prevent double submission
                if (isSending) {
                    console.log('Message already sending, ignoring duplicate submit');
                    return;
                }
                
                const formData = new FormData();
                const messageText = messageInput.value.trim();
                
                if (messageText) {
                    formData.append('message', messageText);
                }
                
                if (fileInput.files[0]) {
                    formData.append('attachment', fileInput.files[0]);
                }
                
                if (!messageText && !fileInput.files[0]) {
                    return;
                }
                
                // Set sending flag and disable form
                isSending = true;
                const sendButton = messageForm.querySelector('button[type="submit"]');
                if (sendButton) {
                    sendButton.disabled = true;
                    sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                }
                messageInput.disabled = true;
                
                fetch(`/chat/${conversationId}/send`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status) {
                        messageInput.value = '';
                        fileInput.value = '';
                        filePreview.style.display = 'none';
                        filePreview.innerHTML = '';
                        loadMessages();
                    }
                })
                .catch(err => console.error('Error sending message:', err))
                .finally(() => {
                    // Re-enable form after request completes
                    isSending = false;
                    if (sendButton) {
                        sendButton.disabled = false;
                        sendButton.innerHTML = '<i class="fas fa-paper-plane"></i>';
                    }
                    messageInput.disabled = false;
                    messageInput.focus();
                });
            });

            // File input handling
            fileInput.addEventListener('change', () => {
                if (fileInput.files[0]) {
                    const file = fileInput.files[0];
                    filePreview.style.display = 'block';
                    filePreview.innerHTML = `
                        <div class="alert alert-info py-2 px-3 mb-0 rounded-pill d-inline-flex align-items-center">
                            <i class="fas fa-paperclip me-2"></i> 
                            <span>${escapeHtml(file.name)} (${Math.round(file.size/1024)} KB)</span>
                            <button type="button" class="btn btn-sm btn-link text-danger ms-2 p-0" onclick="clearFile()" title="{{ __('messages.remove') }}">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </div>
                    `;
                }
            });

            // Clear file function
            window.clearFile = function() {
                fileInput.value = '';
                filePreview.style.display = 'none';
                filePreview.innerHTML = '';
            };

            // Typing — fire client event so the OTHER person's browser shows the indicator
            messageInput.addEventListener('input', () => {
                clearTimeout(typingTimer);
                if (pusherChannel && pusherConnected) {
                    try { pusherChannel.trigger('client-typing', {}); } catch(e) {
                        console.error('[Pusher] trigger client-typing failed', e);
                    }
                }
                typingTimer = setTimeout(() => {}, 1200);
            });

            // Auto-scroll to bottom
            function scrollToBottom() {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            // Escape HTML
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Initialize — start with 3s polling; Pusher 'connected' event slows it to 15s
            loadMessages();
            pollTimer = setInterval(loadMessages, 3000);
            
            // Cleanup on page unload
            window.addEventListener('beforeunload', () => {
                if (pollTimer) clearInterval(pollTimer);
            });
        });
    </script>

    <style>
        .chat-wrapper { min-height: 100vh; background: linear-gradient(180deg, #f1f5f9 0%, #ffffff 40%); }
        .chat-header { position: sticky; top: 0; z-index: 2; padding: 12px 16px; background: #ffffff; border-bottom: 1px solid #eef1f4; }
        .chat-avatar { width: 40px; height: 40px; object-fit: cover; }
        .chat-name { font-weight: 600; font-size: 16px; line-height: 1.1; }
        .chat-sub { font-size: 12px; color: #6b7280; }

        .chat-body { 
            height: calc(100vh - 150px); 
            overflow-y: auto; 
            padding: 16px; 
            background-color: rgb(59, 21, 102);

            background-image:
                radial-gradient(36px 36px at 20% 12%, rgba(59, 130, 246, 0.08), rgba(59, 130, 246, 0) 70%),
                radial-gradient(28px 28px at 82% 22%, rgba(99, 102, 241, 0.07), rgba(99, 102, 241, 0) 70%),
                radial-gradient(40px 40px at 26% 86%, rgba(16, 185, 129, 0.08), rgba(16, 185, 129, 0) 70%),
                radial-gradient(24px 24px at 90% 78%, rgba(234, 179, 8, 0.07), rgba(234, 179, 8, 0) 70%),
                radial-gradient(1px 1px at 12% 10%, rgba(2, 6, 23, 0.06), transparent 60%),
                radial-gradient(1px 1px at 60% 30%, rgba(2, 6, 23, 0.05), transparent 60%),
                radial-gradient(1px 1px at 30% 70%, rgba(2, 6, 23, 0.05), transparent 60%),
                radial-gradient(1px 1px at 78% 88%, rgba(2, 6, 23, 0.04), transparent 60%);
            background-size: auto, auto, auto, auto, 180px 180px, 220px 220px, 200px 200px, 260px 260px;
            background-repeat: no-repeat, no-repeat, no-repeat, no-repeat, repeat, repeat, repeat, repeat;
            background-attachment: fixed;
        }
        .chat-body { scrollbar-width: thin; }
        .chat-body::-webkit-scrollbar { width: 8px; }
        .chat-body::-webkit-scrollbar-track { background: transparent; }
        .chat-body::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 8px; }

        .chat-row { display: flex; gap: 10px; margin-bottom: 10px; align-items: flex-end; }
        .chat-row.is-own { justify-content: flex-end; }
        .chat-row.is-other { justify-content: flex-start; }
        .chat-mini-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; }

        .chat-bubble { max-width: 70%; padding: 10px 12px; border-radius: 14px; font-size: 14px; box-shadow: 0 1px 1px rgba(0,0,0,0.03); }
        .chat-bubble.other { background: #ffffff; border: 1px solid #eef1f4; color: #111827; }
        .chat-bubble.own { background: var(--bs-primary, #3b82f6); color: #ffffff; }
        .chat-text { white-space: pre-wrap; word-break: break-word; }
        .chat-attach { text-decoration: none; }
        .chat-time { font-size: 11px; opacity: .8; margin-top: 6px; }

        .chat-date-sep { display: flex; align-items: center; justify-content: center; margin: 14px 0; }
        .chat-date-sep span { background: #e5e7eb; color: #374151; font-size: 12px; padding: 4px 10px; border-radius: 999px; }

        .chat-composer { position: sticky; bottom: 0; z-index: 2; padding: 10px 16px; background: #ffffff; border-top: 1px solid #eef1f4; }
        .composer-inner { display: flex; align-items: center; gap: 10px; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 999px; padding: 8px 10px; }
        .composer-btn { border-radius: 999px; box-shadow: none !important; color: #6b7280; }
        .composer-input { flex: 1; border: none; outline: none; background: transparent; padding: 6px 4px; }
        .composer-input:focus { outline: none; }
        .composer-send { border-radius: 999px; padding: 8px 14px; }

        /* Policy violation badge */
        .chat-violation {
            display: inline-block;
            font-size: 11.5px;
            font-weight: 500;
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 6px;
            padding: 3px 8px;
            white-space: normal;
            word-break: break-word;
            line-height: 1.4;
        }
        .chat-bubble.own .chat-violation {
            color: #fca5a5;
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(252, 165, 165, 0.4);
        }

        /* Typing indicator bubble */
        .typing-bubble {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #ffffff;
            border-top: 1px solid #eef1f4;
            font-size: 13px;
            color: #6b7280;
        }
        .typing-dots { display: flex; gap: 3px; align-items: center; }
        .typing-dots span {
            width: 6px; height: 6px; border-radius: 50%;
            background: #9ca3af;
            animation: typingBounce 1.2s infinite ease-in-out;
        }
        .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
        .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typingBounce {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
            30% { transform: translateY(-4px); opacity: 1; }
        }
    </style>
</x-master-layout>
