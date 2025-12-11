<x-master-layout>
    <div class="container py-3">
        <div class="d-flex align-items-center mb-3">
            <h5 class="mb-0">Messages</h5>
            <div class="ms-auto d-flex gap-2">
                <button type="button" id="enableNotificationsBtn" class="btn btn-sm btn-outline-primary" aria-label="Enable Notifications" title="Enable Browser Notifications" style="display: none;">
                    <i class="fas fa-bell"></i> Enable Notifications
                </button>
                <button type="button" id="refreshUnreadBtn" class="btn btn-sm btn-outline-secondary" aria-label="Refresh" title="Refresh"><i class="fas fa-sync-alt"></i></button>
            </div>
        </div>
        <div class="card">
            @isset($countries)
            <form method="GET" class="p-3 border-bottom">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-2">
                        <label class="form-label">User type</label>
                        <select name="user_type" class="form-select" id="filterUserType">
                            <option value="">All</option>
                            <option value="user" {{ (($filters['user_type'] ?? '')==='user') ? 'selected' : '' }}>User</option>
                            <option value="provider" {{ (($filters['user_type'] ?? '')==='provider') ? 'selected' : '' }}>Provider</option>
                            <option value="handyman" {{ (($filters['user_type'] ?? '')==='handyman') ? 'selected' : '' }}>Handyman</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Country</label>
                        <select name="country_id" class="form-select" id="filterCountry">
                            <option value="">All</option>
                            @foreach($countries as $ct)
                                <option value="{{ $ct->id }}" {{ ((string)($filters['country_id'] ?? '') === (string)$ct->id) ? 'selected' : '' }}>{{ $ct->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">State</label>
                        <select name="state_id" class="form-select" id="filterState">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">City</label>
                        <select name="city_id" class="form-select" id="filterCity">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-1 d-grid">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>
            @endisset
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
            const endpoint = '{{ route('chat.unread.ping') }}';
            const lastKey = 'chat_toast_last_id';
            const apiStates = (countryId) => `/api/countries/${countryId}/states`;
            const apiCities = (stateId) => `/api/states/${stateId}/cities`;
            const countryEl = document.getElementById('filterCountry');
            const stateEl = document.getElementById('filterState');
            const cityEl = document.getElementById('filterCity');

            async function loadStates(countryId, preselect) {
                if (!stateEl) return;
                stateEl.innerHTML = '<option value="">All</option>';
                cityEl && (cityEl.innerHTML = '<option value="">All</option>');
                if (!countryId) return;
                try {
                    const r = await fetch(apiStates(countryId));
                    const j = await r.json();
                    (j.states || []).forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.id; opt.textContent = s.name;
                        if (preselect && String(preselect) === String(s.id)) opt.selected = true;
                        stateEl.appendChild(opt);
                    });
                } catch (_) {}
            }
            async function loadCities(stateId, preselect) {
                if (!cityEl) return;
                cityEl.innerHTML = '<option value="">All</option>';
                if (!stateId) return;
                try {
                    const r = await fetch(apiCities(stateId));
                    const j = await r.json();
                    (j.cities || []).forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id; opt.textContent = c.name;
                        if (preselect && String(preselect) === String(c.id)) opt.selected = true;
                        cityEl.appendChild(opt);
                    });
                } catch (_) {}
            }

            if (countryEl && stateEl) {
                countryEl.addEventListener('change', () => loadStates(countryEl.value));
            }
            if (stateEl && cityEl) {
                stateEl.addEventListener('change', () => loadCities(stateEl.value));
            }

            // hydrate states/cities from existing filters
            try {
                const preCountry = '{{ (string)($filters['country_id'] ?? '') }}';
                const preState = '{{ (string)($filters['state_id'] ?? '') }}';
                const preCity = '{{ (string)($filters['city_id'] ?? '') }}';
                if (countryEl && preCountry) {
                    loadStates(preCountry, preState).then(() => {
                        if (preState) loadCities(preState, preCity);
                    });
                }
            } catch (_) {}
            // Browser Notification Support (WhatsApp-like)
            let notificationPermission = Notification.permission;
            const enableNotificationsBtn = document.getElementById('enableNotificationsBtn');
            
            // Show/hide enable notifications button
            if (enableNotificationsBtn) {
                if (notificationPermission === 'denied' || notificationPermission === 'default') {
                    enableNotificationsBtn.style.display = 'block';
                } else {
                    enableNotificationsBtn.style.display = 'none';
                }
                
                enableNotificationsBtn.addEventListener('click', () => {
                    if (notificationPermission === 'denied') {
                        alert('Notifications are blocked. Please enable them in your browser settings.');
                        return;
                    }
                    Notification.requestPermission().then(permission => {
                        notificationPermission = permission;
                        if (permission === 'granted') {
                            console.log('Browser notifications enabled');
                            enableNotificationsBtn.style.display = 'none';
                            // Register service worker for background notifications
                            if ('serviceWorker' in navigator) {
                                navigator.serviceWorker.register('/sw.js').catch(err => console.log('SW registration failed:', err));
                            }
                        } else {
                            enableNotificationsBtn.style.display = 'block';
                        }
                    });
                });
            }
            
            // Request notification permission if not granted (auto-request on first visit)
            if (notificationPermission === 'default') {
                // Don't auto-request, let user click button instead
                // Notification.requestPermission().then(permission => {
                //     notificationPermission = permission;
                //     if (permission === 'granted') {
                //         console.log('Browser notifications enabled');
                //         if ('serviceWorker' in navigator) {
                //             navigator.serviceWorker.register('/sw.js').catch(err => console.log('SW registration failed:', err));
                //         }
                //     }
                // });
            } else if (notificationPermission === 'granted' && 'serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js').catch(err => console.log('SW registration failed:', err));
            }

            // Function to show browser notification
            function showBrowserNotification(message) {
                if (notificationPermission !== 'granted') return;
                if (document.hasFocus() && window.location.pathname === '/messages') return; // Don't notify if user is on messages page

                const notificationOptions = {
                    body: message.snippet || 'New message',
                    icon: message.sender_avatar || '{{ asset("images/user/user.png") }}',
                    badge: '{{ asset("images/logo.png") }}',
                    tag: `chat-${message.conversation_id || message.id}`,
                    data: {
                        conversation_id: message.conversation_id,
                        message_id: message.id,
                        sender_id: message.sender_id,
                        url: message.url || '/messages'
                    },
                    requireInteraction: false,
                    vibrate: [200, 100, 200]
                };

                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.ready.then(registration => {
                        registration.showNotification(
                            `New message from ${message.sender_name || 'User'}`,
                            notificationOptions
                        );
                    });
                } else {
                    new Notification(
                        `New message from ${message.sender_name || 'User'}`,
                        notificationOptions
                    );
                }
            }

            async function handlePingResponse(j, forceToast){
                const last = sessionStorage.getItem(lastKey) || '0';
                if (j && j.latest && j.latest.id) {
                    const isNew = j.latest.id.toString() !== last.toString();
                    if (isNew) sessionStorage.setItem(lastKey, j.latest.id);
                    if ((isNew || forceToast) && j.latest.sender_name){
                        const text = `${j.latest.sender_name}: ${j.latest.snippet || ''}`;
                        if (window.Swal){ Swal.fire({ toast:true, position:'bottom-end', timer:3500, showConfirmButton:false, icon:'info', title: text }); }
                        if (window.__playChatNotify) window.__playChatNotify();
                        
                        // Show browser notification for new messages
                        if (isNew && notificationPermission === 'granted') {
                            showBrowserNotification(j.latest);
                        }
                        
                        try {
                            await fetch('{{ route('chat.unread.ack') }}', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                                body: JSON.stringify({ last_id: j.latest.id })
                            });
                        } catch(_) {}
                    }
                }
            }
            function fetchUnread(forceToast){
                const url = forceToast ? (endpoint + '?first=1') : endpoint;
                fetch(url).then(r=>r.json()).then(j=>handlePingResponse(j, !!forceToast)).catch(()=>{});
            }
            // Initial ping
            fetchUnread(true);
            // Manual refresh button
            const btn = document.getElementById('refreshUnreadBtn');
            if (btn) btn.addEventListener('click', function(e){ e.preventDefault(); fetchUnread(true); });
        });
    </script>
</x-master-layout>