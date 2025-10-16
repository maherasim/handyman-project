<x-master-layout>
    @php
        $auth = auth()->user();
        $fallbackAvatar = asset('images/user/user.png');
    @endphp
    <div class="container-fluid" style="min-height: 100vh;">
        <div class="row">
            <!-- Simple Chat Header -->
            <div class="col-12">
                <div class="p-3 border-bottom bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <img src="{{ getSingleMedia($targetUser, 'profile_image', null) ?? $fallbackAvatar }}" 
                             class="rounded-circle" style="width:40px;height:40px;object-fit:cover;">
                        <div>
                            <h5 class="mb-0">{{ $targetUser->display_name }}</h5>
                            <small class="text-muted">{{ ucfirst($targetUser->user_type ?? 'User') }}</small>
                        </div>
                        <div class="ms-auto">
                            <small class="text-muted" id="typingIndicator" style="display:none;">typing...</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Chat Messages Area -->
            <div class="col-12">
                <div id="messagesContainer" class="bg-light p-3" style="min-height: 400px; max-height: 500px; overflow-y: auto;">
                    <div id="messages"></div>
                </div>
            </div>
            
            <!-- Message Input -->
            <div class="col-12">
                <div class="border-top p-3 bg-white">
                    <form id="messageForm" class="d-flex align-items-center gap-2">
                        <input type="file" id="fileInput" class="form-control" style="max-width:200px;" accept="image/*,application/pdf,.doc,.docx">
                        <input type="text" id="messageInput" class="form-control" placeholder="Type your message..." required>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                    <div id="filePreview" class="mt-2" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

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

            // Load messages
            function loadMessages() {
                fetch(`/chat/${conversationId}/messages`)
                    .then(r => r.json())
                    .then(data => {
                        messagesEl.innerHTML = '';
                        (data.messages || []).forEach(message => {
                            renderMessage(message);
                        });
                        scrollToBottom();
                    })
                    .catch(err => console.error('Error loading messages:', err));
            }

            // Render a single message
            function renderMessage(message) {
                const isOwn = message.sender_id === currentUserId;
                const messageDiv = document.createElement('div');
                messageDiv.className = `d-flex mb-3 ${isOwn ? 'justify-content-end' : 'justify-content-start'}`;
                
                const bubble = document.createElement('div');
                bubble.className = `p-3 rounded ${isOwn ? 'bg-primary text-white' : 'bg-white border'}`;
                bubble.style.maxWidth = '70%';
                
                let content = '';
                
                // Message text
                if (message.message) {
                    content += `<div class="mb-1">${escapeHtml(message.message)}</div>`;
                }
                
                // File attachment
                if (message.attachment) {
                    content += `<div class="mt-2">
                        <a href="${message.attachment.download_url}" target="_blank" class="text-decoration-none ${isOwn ? 'text-white' : 'text-primary'}">
                            <i class="fas fa-paperclip"></i> ${escapeHtml(message.attachment.name)}
                        </a>
                    </div>`;
                }
                
                // Timestamp
                content += `<div class="small opacity-75 mt-1">${escapeHtml(message.created_at)}</div>`;
                
                bubble.innerHTML = content;
                messageDiv.appendChild(bubble);
                messagesEl.appendChild(messageDiv);
            }

            // Send message
            messageForm.addEventListener('submit', (e) => {
                e.preventDefault();
                
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
                .catch(err => console.error('Error sending message:', err));
            });

            // File input handling
            fileInput.addEventListener('change', () => {
                if (fileInput.files[0]) {
                    const file = fileInput.files[0];
                    filePreview.style.display = 'block';
                    filePreview.innerHTML = `
                        <div class="alert alert-info py-2">
                            <i class="fas fa-paperclip"></i> 
                            ${escapeHtml(file.name)} (${Math.round(file.size/1024)} KB)
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="clearFile()">Remove</button>
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

            // Typing indicator
            messageInput.addEventListener('input', () => {
                clearTimeout(typingTimer);
                typingIndicator.style.display = 'block';
                typingTimer = setTimeout(() => {
                    typingIndicator.style.display = 'none';
                }, 1000);
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

            // Poll for new messages every 3 seconds
            function startPolling() {
                pollTimer = setInterval(loadMessages, 3000);
            }

            // Initialize
            loadMessages();
            startPolling();
            
            // Cleanup on page unload
            window.addEventListener('beforeunload', () => {
                if (pollTimer) clearInterval(pollTimer);
            });
        });
    </script>

    <style>
        #messagesContainer {
            scrollbar-width: thin;
        }
        
        #messagesContainer::-webkit-scrollbar {
            width: 6px;
        }
        
        #messagesContainer::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        #messagesContainer::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        #messagesContainer::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</x-master-layout>
