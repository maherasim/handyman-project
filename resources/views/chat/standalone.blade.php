<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <!-- Users List Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2"></i>Start New Chat
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Search Users -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" id="userSearch" class="form-control" placeholder="Search users...">
                            </div>
                        </div>
                        
                        <!-- Users List -->
                        <div id="usersList" class="users-list">
                            <!-- Users will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Chat Area -->
            <div class="col-md-9">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0" id="chatTitle">
                                <i class="fas fa-comments me-2"></i>Select a user to start chatting
                            </h5>
                        </div>
                        <div id="chatActions" style="display: none;">
                            <button class="btn btn-sm btn-outline-secondary" id="clearChat">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <!-- Chat Messages Area -->
                        <div id="chatMessages" class="chat-messages">
                            <div class="text-center text-muted p-5">
                                <i class="fas fa-comment-dots fa-3x mb-3"></i>
                                <p>Select a user from the sidebar to start a conversation</p>
                            </div>
                        </div>
                        
                        <!-- Message Input -->
                        <div id="messageInput" class="message-input p-3 border-top" style="display: none;">
                            <form id="sendMessageForm">
                                <div class="input-group">
                                    <input type="text" id="messageText" class="form-control" placeholder="Type your message..." maxlength="4000">
                                    <button type="button" class="btn btn-outline-secondary" id="attachFile">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                                <input type="file" id="fileInput" style="display: none;" accept="image/*,video/*,audio/*,.pdf,.doc,.docx">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .users-list {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .user-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .user-item:hover {
            background-color: #f8f9fa;
        }
        
        .user-item.active {
            background-color: #e3f2fd;
            border-left: 3px solid #2196f3;
        }
        
        .chat-messages {
            height: 500px;
            overflow-y: auto;
            padding: 15px;
        }
        
        .message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }
        
        .message.own {
            justify-content: flex-end;
        }
        
        .message-content {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
            position: relative;
        }
        
        .message.own .message-content {
            background-color: #007bff;
            color: white;
        }
        
        .message.other .message-content {
            background-color: #f1f3f4;
            color: #333;
        }
        
        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin: 0 10px;
            object-fit: cover;
        }
        
        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 5px;
        }
        
        .attachment {
            margin-top: 10px;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }
        
        .attachment a {
            color: inherit;
            text-decoration: none;
        }
        
        .attachment a:hover {
            text-decoration: underline;
        }
    </style>

    <script>
        let currentConversationId = null;
        let currentUserId = {{ auth()->id() }};
        let messagePolling = null;

        $(document).ready(function() {
            loadUsers();
            
            // Search users
            $('#userSearch').on('input', function() {
                loadUsers($(this).val());
            });
            
            // Send message
            $('#sendMessageForm').on('submit', function(e) {
                e.preventDefault();
                sendMessage();
            });
            
            // File attachment
            $('#attachFile').on('click', function() {
                $('#fileInput').click();
            });
            
            $('#fileInput').on('change', function() {
                if (this.files.length > 0) {
                    sendMessage();
                }
            });
            
            // Clear chat
            $('#clearChat').on('click', function() {
                clearChat();
            });
        });

        function loadUsers(search = '') {
            $.ajax({
                url: '/api/chat/users',
                method: 'GET',
                data: { search: search },
                headers: {
                    'Authorization': 'Bearer ' + $('meta[name="api-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status) {
                        displayUsers(response.users);
                    }
                },
                error: function(xhr) {
                    console.error('Error loading users:', xhr);
                }
            });
        }

        function displayUsers(users) {
            let html = '';
            users.forEach(function(user) {
                html += `
                    <div class="user-item" onclick="startChat(${user.id}, '${user.name}', '${user.avatar_url || ''}')">
                        <div class="d-flex align-items-center">
                            <img src="${user.avatar_url || '/images/default.png'}" 
                                 alt="${user.name}" class="message-avatar">
                            <div>
                                <div class="fw-bold">${user.name}</div>
                                <small class="text-muted">${user.user_type}</small>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#usersList').html(html);
        }

        function startChat(userId, userName, userAvatar) {
            // Update UI
            $('.user-item').removeClass('active');
            $(event.target).closest('.user-item').addClass('active');
            
            $('#chatTitle').html(`
                <i class="fas fa-comments me-2"></i>Chat with ${userName}
            `);
            $('#chatActions').show();
            $('#messageInput').show();
            
            // Stop previous polling
            if (messagePolling) {
                clearInterval(messagePolling);
            }
            
            // Open or create conversation
            $.ajax({
                url: '/api/chat/open-with-user',
                method: 'POST',
                data: {
                    user_id: userId,
                    title: `Chat with ${userName}`
                },
                headers: {
                    'Authorization': 'Bearer ' + $('meta[name="api-token"]').attr('content'),
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status) {
                        currentConversationId = response.conversation_id;
                        loadMessages();
                        startMessagePolling();
                    }
                },
                error: function(xhr) {
                    console.error('Error starting chat:', xhr);
                    alert('Error starting chat. Please try again.');
                }
            });
        }

        function loadMessages() {
            if (!currentConversationId) return;
            
            $.ajax({
                url: `/api/chat/${currentConversationId}/messages`,
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + $('meta[name="api-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status) {
                        displayMessages(response.messages);
                    }
                },
                error: function(xhr) {
                    console.error('Error loading messages:', xhr);
                }
            });
        }

        function displayMessages(messages) {
            let html = '';
            messages.forEach(function(message) {
                const isOwn = message.sender_id == currentUserId;
                const messageClass = isOwn ? 'own' : 'other';
                const avatar = isOwn ? 
                    '{{ getSingleMedia(auth()->user(), "profile_image", null) }}' : 
                    message.sender_avatar_url || '/images/default.png';
                
                html += `
                    <div class="message ${messageClass}">
                        ${!isOwn ? `<img src="${avatar}" alt="${message.sender_name}" class="message-avatar">` : ''}
                        <div class="message-content">
                            <div>${message.message || ''}</div>
                            ${message.attachment ? `
                                <div class="attachment">
                                    <a href="${message.attachment.download_url}" target="_blank">
                                        <i class="fas fa-paperclip"></i> ${message.attachment.name}
                                    </a>
                                </div>
                            ` : ''}
                            <div class="message-time">
                                ${new Date(message.created_at).toLocaleTimeString()}
                            </div>
                        </div>
                        ${isOwn ? `<img src="${avatar}" alt="${message.sender_name}" class="message-avatar">` : ''}
                    </div>
                `;
            });
            
            $('#chatMessages').html(html);
            scrollToBottom();
        }

        function sendMessage() {
            if (!currentConversationId) return;
            
            const messageText = $('#messageText').val().trim();
            const fileInput = $('#fileInput')[0];
            
            if (!messageText && !fileInput.files.length) return;
            
            const formData = new FormData();
            if (messageText) {
                formData.append('message', messageText);
            }
            if (fileInput.files.length) {
                formData.append('attachment', fileInput.files[0]);
            }
            
            $.ajax({
                url: `/api/chat/${currentConversationId}/send`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'Authorization': 'Bearer ' + $('meta[name="api-token"]').attr('content'),
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status) {
                        $('#messageText').val('');
                        $('#fileInput').val('');
                        loadMessages();
                    }
                },
                error: function(xhr) {
                    console.error('Error sending message:', xhr);
                    alert('Error sending message. Please try again.');
                }
            });
        }

        function startMessagePolling() {
            messagePolling = setInterval(function() {
                loadMessages();
            }, 3000); // Poll every 3 seconds
        }

        function clearChat() {
            currentConversationId = null;
            $('#chatTitle').html('<i class="fas fa-comments me-2"></i>Select a user to start chatting');
            $('#chatActions').hide();
            $('#messageInput').hide();
            $('#chatMessages').html(`
                <div class="text-center text-muted p-5">
                    <i class="fas fa-comment-dots fa-3x mb-3"></i>
                    <p>Select a user from the sidebar to start a conversation</p>
                </div>
            `);
            $('.user-item').removeClass('active');
            
            if (messagePolling) {
                clearInterval(messagePolling);
                messagePolling = null;
            }
        }

        function scrollToBottom() {
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    </script>
</x-master-layout>
