<x-master-layout>
    <div class="container-fluid" style="min-height: 100vh;">
        <div class="row">
            <div class="col-12 col-md-4 col-lg-3 border-end d-flex flex-column p-0 mb-3 mb-md-0">
                <div class="p-3 border-bottom">
                    <h5 class="mb-0">All Conversations</h5>
                    <div class="text-muted small">Direct Messages</div>
                </div>
                <div class="flex-grow-1 overflow-auto p-2" id="conversationsList">
                    @if($conversations->count() > 0)
                        @foreach($conversations as $conversation)
                            @php
                                $otherUser = ($conversation->user_one_id === $currentUser->id) ? $conversation->userTwo : $conversation->userOne;
                                $lastMessage = $conversation->messages->first();
                            @endphp
                            <div class="d-flex align-items-center gap-2 p-2 rounded hover-bg cursor-pointer conversation-item" 
                                 data-user-id="{{ $otherUser->id }}" 
                                 data-conversation-id="{{ $conversation->id }}">
                                <div>
                                    <img src="{{ getSingleMedia($otherUser, 'profile_image', null) ?? asset('images/user/user.png') }}" 
                                         class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold mb-0 small">{{ $otherUser->display_name }}</div>
                                    <div class="text-muted small">{{ ucfirst($otherUser->user_type ?? 'User') }}</div>
                                    @if($lastMessage)
                                        <div class="text-muted small text-truncate" style="max-width: 200px;">
                                            {{ Str::limit($lastMessage->message, 30) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-end">
                                    @if($lastMessage)
                                        <div class="text-muted small">
                                            {{ $lastMessage->created_at->diffForHumans() }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-comments fa-2x mb-2"></i>
                            <p>No conversations yet</p>
                            <small>Start chatting with users from bookings or job requests</small>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-md-8 col-lg-9 d-flex flex-column p-0">
                <div class="p-3 border-bottom d-flex align-items-center gap-2">
                    <img src="{{ getSingleMedia($currentUser, 'profile_image', null) ?? asset('images/user/user.png') }}" 
                         class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                    <div class="fw-bold">{{ $currentUser->display_name }}</div>
                    <div class="ms-auto small text-muted" id="typingDot" style="display:none;">typing...</div>
                </div>
                <div id="msgScroll" class="flex-grow-1 overflow-auto p-3 bg-light d-flex align-items-center justify-content-center" style="position:relative; min-height: 300px;">
                    <div class="text-center text-muted">
                        <i class="fas fa-comments fa-3x mb-3"></i>
                        <h5>Select a conversation</h5>
                        <p>Choose a conversation from the sidebar to start messaging</p>
                    </div>
                </div>
                <div class="border-top p-2" style="position: sticky; bottom: 0; background: #fff; z-index: 2; display: none;" id="messageComposer">
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
        .conversation-item.active { background: #e3f2fd; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const currentUserId = {{ (int) auth()->id() }};
            const messagesEl = document.getElementById('msgScroll');
            const textInput = document.getElementById('textInput');
            const fileInput = document.getElementById('fileInput');
            const sendBtn = document.getElementById('sendBtn');
            const typingEl = document.getElementById('typingDot');
            const previewEl = document.getElementById('attachmentPreview');
            const messageComposer = document.getElementById('messageComposer');

            let currentConversationId = null;
            let oldestId = 0;
            let newestId = 0;
            let pollTimer = null;
            let typingTimer = null;

            // Handle conversation selection
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.addEventListener('click', () => {
                    // Remove active class from all items
                    document.querySelectorAll('.conversation-item').forEach(i => i.classList.remove('active'));
                    // Add active class to clicked item
                    item.classList.add('active');
                    
                    // Get conversation data
                    const userId = item.dataset.userId;
                    const conversationId = item.dataset.conversationId;
                    
                    // Update current conversation
                    currentConversationId = conversationId;
                    
                    // Show message composer
                    messageComposer.style.display = 'block';
                    
                    // Load messages for this conversation
                    loadMessages();
                    
                    // Start polling for new messages
                    if (pollTimer) clearInterval(pollTimer);
                    pollTimer = setInterval(pollNewer, 4000);
                });
            });

            // Load messages for current conversation
            function loadMessages() {
                if (!currentConversationId) return;
                
                const messagesUrl = `/chat/${currentConversationId}/messages`;
                fetch(messagesUrl)
                    .then(r => r.json())
                    .then(j => {
                        messagesEl.innerHTML = '';
                        (j.messages || []).forEach(m => {
                            oldestId = oldestId === 0 ? m.id : Math.min(oldestId, m.id);
                            newestId = Math.max(newestId, m.id);
                            renderMessage(m);
                        });
                        messagesEl.scrollTop = messagesEl.scrollHeight;
                    });
            }

            // Render a single message
            function renderMessage(m) {
                const wrap = document.createElement('div');
                const mine = m.sender_id === currentUserId;
                wrap.className = 'd-flex mb-2 ' + (mine ? 'justify-content-end' : 'justify-content-start');
                
                const bubble = document.createElement('div');
                bubble.className = 'p-2 rounded ' + (mine ? 'bg-primary text-white' : 'bg-white border');
                
                let html = '';
                const name = (m.sender_name || 'User').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                const avatar = (m.sender_avatar_url || '/images/user/user.png').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                
                html += `<div class="d-flex align-items-center mb-1">`+
                    `<img src="${avatar}" class="rounded-circle me-2" style="width:22px;height:22px;object-fit:cover;">`+
                    `<span class="small fw-bold">${name}</span>`+
                    `</div>`;
                
                if (m.message) {
                    html += `<div class="small">${(m.message || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>`;
                }
                
                if (m.attachment) {
                    const name = (m.attachment.name || 'attachment').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                    html += `<div class="mt-1"><a href="${m.attachment.download_url}" target="_blank" class="text-decoration-underline ${mine ? 'text-white' : ''}"><i class="fas fa-paperclip"></i> ${name}</a></div>`;
                }
                
                html += `<div class="text-end small opacity-75 mt-1">${(m.created_at || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')}${m.read ? ' · <i class="fas fa-check-double"></i>' : ''}</div>`;
                bubble.innerHTML = html;
                wrap.appendChild(bubble);
                messagesEl.appendChild(wrap);
            }

            // Poll for newer messages
            function pollNewer() {
                if (!currentConversationId) return;
                
                const url = newestId ? (`/chat/${currentConversationId}/messages?after_id=${newestId}`) : `/chat/${currentConversationId}/messages`;
                fetch(url).then(r => r.json()).then(j => {
                    let inbound = false;
                    (j.messages || []).forEach(m => {
                        oldestId = oldestId === 0 ? m.id : Math.min(oldestId, m.id);
                        newestId = Math.max(newestId, m.id);
                        if (m.sender_id !== currentUserId) inbound = true;
                        renderMessage(m);
                    });
                    if (j.messages && j.messages.length) {
                        messagesEl.scrollTop = messagesEl.scrollHeight;
                    }
                });
            }

            // Handle message sending
            document.getElementById('composer').addEventListener('submit', (e) => {
                e.preventDefault();
                if (!currentConversationId) return;
                
                const fd = new FormData();
                const text = (textInput.value || '').trim();
                if (text) fd.append('message', text);
                if (fileInput.files && fileInput.files[0]) fd.append('attachment', fileInput.files[0]);
                
                fetch(`/chat/${currentConversationId}/send`, {
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

            // Handle file input
            fileInput.addEventListener('change', () => {
                previewEl.innerHTML = '';
                if (fileInput.files && fileInput.files[0]) {
                    const f = fileInput.files[0];
                    previewEl.style.display = '';
                    previewEl.innerHTML = `<div class="small"><i class="fas fa-paperclip"></i> ${f.name} (${Math.round(f.size/1024)} KB)</div>`;
                } else {
                    previewEl.style.display = 'none';
                }
            });

            // Handle typing indicator
            textInput.addEventListener('input', () => {
                clearTimeout(typingTimer);
                typingEl.style.display = '';
                typingTimer = setTimeout(() => typingEl.style.display = 'none', 1200);
            });
        });
    </script>
</x-master-layout>