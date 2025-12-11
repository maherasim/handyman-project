// Service Worker for Browser Push Notifications
// This handles background notifications even when the browser tab is not active

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

// Handle push notifications
self.addEventListener('push', (event) => {
    let data = {};
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data = { title: 'New Message', body: event.data.text() || 'You have a new message' };
        }
    }

    const options = {
        title: data.title || 'New Message',
        body: data.body || data.message || 'You have a new message',
        icon: data.icon || '/images/logo.png',
        badge: '/images/logo.png',
        tag: data.tag || 'chat-message',
        data: data.data || {},
        requireInteraction: false,
        vibrate: [200, 100, 200],
        timestamp: Date.now()
    };

    event.waitUntil(
        self.registration.showNotification(options.title, options)
    );
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const data = event.notification.data || {};
    let url = data.url || '/messages';

    // If we have conversation_id, open that specific chat
    if (data.conversation_id && !url.includes('/chat/')) {
        url = `/chat/${data.conversation_id}/messages`;
    } else if (data.sender_id && !url.includes('/messages/user/')) {
        url = `/messages/user/${data.sender_id}`;
    }

    // Ensure URL is absolute
    if (!url.startsWith('http')) {
        url = self.location.origin + url;
    }

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // Check if there's already a window/tab open
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if ('focus' in client) {
                    // Focus existing window
                    client.focus();
                    // Post message to navigate (window will handle it)
                    client.postMessage({ type: 'navigate', url: url });
                    return;
                }
            }
            // If no window is open, open a new one
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});

