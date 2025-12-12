# Browser Notification Troubleshooting Guide

## 🔍 Quick Diagnostic Steps

### Step 1: Check Notification Permission

**Open Browser Console** (F12) and run:
```javascript
Notification.permission
```

**Expected Results:**
- `"granted"` ✅ - Permission is granted
- `"default"` ⚠️ - Permission not requested yet
- `"denied"` ❌ - Permission was denied

**If not granted:**
1. Go to `/messages` page
2. Click "Enable Notifications" button
3. Click "Allow" in browser prompt

### Step 2: Check Service Worker Registration

**Open Browser Console** (F12) and run:
```javascript
navigator.serviceWorker.getRegistrations().then(regs => console.log(regs))
```

**Expected Result:**
- Should show an array with at least one service worker registered
- Should show `sw.js` is active

**If not registered:**
1. Check if `https://frobster.com/sw.js` is accessible
2. Check browser console for errors
3. Service Workers require HTTPS (or localhost)

### Step 3: Test Notification Manually

**Open Browser Console** (F12) and run:
```javascript
// Test if notifications work
if (Notification.permission === 'granted') {
    new Notification('Test Notification', {
        body: 'If you see this, notifications are working!',
        icon: '/images/logo.png'
    });
} else {
    console.log('Permission not granted. Current status:', Notification.permission);
}
```

**Expected Result:**
- Should show a browser notification ✅
- If not, permission is not granted

### Step 4: Check WebSocket Connection

**Open Browser Console** (F12) and check:
1. Look for WebSocket connection errors
2. Check if Pusher is connected
3. Look for "Echo" or "Pusher" connection messages

**If WebSocket not working:**
- Notifications won't trigger in real-time
- Will only work via polling (every 5 seconds)

### Step 5: Check Notification Trigger Logic

The notification is triggered when:
1. ✅ WebSocket receives message (real-time)
2. ✅ Polling detects new message (every 5 seconds)
3. ❌ User is viewing that specific chat (suppressed)
4. ❌ User is on `/messages` page (suppressed for list view)

## 🐛 Common Issues & Solutions

### Issue 1: "Enable Notifications" Button Not Showing

**Problem:** Button should appear if permission is not granted

**Solution:**
1. Check if button exists in HTML (should be in messages page header)
2. Check browser console for JavaScript errors
3. Manually request permission:
   ```javascript
   Notification.requestPermission()
   ```

### Issue 2: Permission Denied

**Problem:** User previously denied permission

**Solution:**
1. **Chrome:**
   - Click lock icon in address bar
   - Click "Site settings"
   - Find "Notifications"
   - Change to "Allow"

2. **Firefox:**
   - Click lock icon in address bar
   - Click "More Information"
   - Go to "Permissions" tab
   - Find "Notifications"
   - Change to "Allow"

3. **Edge:**
   - Click lock icon in address bar
   - Click "Site permissions"
   - Find "Notifications"
   - Change to "Allow"

### Issue 3: Service Worker Not Registering

**Problem:** `sw.js` file not accessible or has errors

**Check:**
1. Visit: `https://frobster.com/sw.js`
2. Should see JavaScript code (not 404 error)
3. Check browser console for registration errors

**Solution:**
1. Ensure file exists: `public/sw.js`
2. Check file permissions (should be readable)
3. Clear browser cache
4. Check HTTPS is enabled (required for service workers)

### Issue 4: Notifications Not Showing When Message Arrives

**Problem:** Notification function not being called

**Debug Steps:**
1. Open browser console
2. Send a test message
3. Look for console logs:
   - `"Browser notifications enabled"` ✅
   - `"SW registration failed"` ❌
   - No errors ✅

**Check Notification Function:**
Add this to browser console to test:
```javascript
// Test notification function
function testNotification() {
    if (Notification.permission === 'granted') {
        new Notification('Test from Console', {
            body: 'This is a test notification',
            icon: '/images/logo.png',
            tag: 'test-notification'
        });
    } else {
        console.log('Permission:', Notification.permission);
    }
}
testNotification();
```

### Issue 5: Notifications Only Work When Tab is Active

**Problem:** Service Worker not handling background notifications

**Solution:**
1. Ensure service worker is registered
2. Check `sw.js` file is accessible
3. Test by:
   - Minimizing browser window
   - Sending a message
   - Should see notification even with tab inactive

### Issue 6: Notifications Suppressed When Viewing Chat

**This is by design!** Notifications are suppressed when:
- You're viewing the specific chat conversation
- You're on the messages list page

**To test notifications:**
1. Open messages page in one tab
2. Open a different website in another tab
3. Send a message from another account
4. Should see notification in the inactive tab

## 🔧 Manual Testing Steps

### Test 1: Permission Request
1. Go to `https://frobster.com/messages`
2. Click "Enable Notifications" button
3. Click "Allow" in browser prompt
4. Check console: Should see "Browser notifications enabled"

### Test 2: Service Worker Registration
1. Open DevTools → Application → Service Workers
2. Should see `sw.js` registered and active
3. If not, check console for errors

### Test 3: Manual Notification
1. Open browser console
2. Run: `new Notification('Test', {body: 'Test message'})`
3. Should show notification

### Test 4: Real Message Notification
1. Open messages page in Tab 1
2. Open another website in Tab 2
3. From another account, send a message
4. Switch to Tab 2
5. Should see notification in Tab 2

## 📋 Complete Checklist

- [ ] Notification permission is `"granted"`
- [ ] Service Worker is registered (`sw.js` active)
- [ ] `sw.js` file is accessible at `/sw.js`
- [ ] HTTPS is enabled (required for service workers)
- [ ] Browser console shows no errors
- [ ] "Enable Notifications" button works (if shown)
- [ ] Manual notification test works
- [ ] WebSocket/Pusher is connected (for real-time)
- [ ] Polling is working (fallback every 5 seconds)

## 🚨 Still Not Working?

### Check These:

1. **Browser Compatibility:**
   - Chrome ✅ (Best support)
   - Firefox ✅ (Good support)
   - Edge ✅ (Good support)
   - Safari ⚠️ (Limited support)

2. **HTTPS Required:**
   - Service Workers only work on HTTPS
   - `localhost` works for development
   - `http://` sites won't work

3. **Browser Settings:**
   - Check if notifications are globally disabled
   - Check "Do Not Disturb" mode
   - Check browser notification settings

4. **Console Errors:**
   - Open browser console (F12)
   - Look for red error messages
   - Check Network tab for failed requests

## 💡 Quick Fix Commands

**In Browser Console:**

```javascript
// 1. Check permission
Notification.permission

// 2. Request permission
Notification.requestPermission().then(p => console.log('Permission:', p))

// 3. Register service worker manually
navigator.serviceWorker.register('/sw.js').then(r => console.log('Registered:', r))

// 4. Test notification
new Notification('Test', {body: 'Test message', icon: '/images/logo.png'})

// 5. Check service worker
navigator.serviceWorker.getRegistrations().then(regs => console.log('SWs:', regs))
```

## 📞 Need More Help?

If notifications still don't work after following this guide:
1. Share browser console errors
2. Share service worker registration status
3. Share notification permission status
4. Test in different browser
5. Check if HTTPS is properly configured


