# Chat Push Notification Setup Guide

## Overview
The chat system uses Firebase Cloud Messaging (FCM) to send push notifications when users receive messages. This guide explains how to enable and configure push notifications for chat messages.

## ⚠️ Important: Two Different Configurations Needed

**For Backend (Laravel/PHP):**
- ✅ Service Account JSON file (from Firebase Console → Service Accounts)
- ✅ Upload to admin panel settings
- ✅ Used by server to **send** notifications

**For Flutter App:**
- ✅ `google-services.json` (Android) or `GoogleService-Info.plist` (iOS)
- ✅ Download from Firebase Console → Project Settings → Your apps
- ✅ Used by app to **receive** notifications

**These are completely different files!** The service account JSON is for the backend, while the Flutter app needs its own configuration files.

## How Chat Notifications Work

When a chat message is sent:
1. The message is saved to the database
2. A real-time broadcast is sent via WebSockets (if enabled)
3. A push notification is sent via FCM to the recipient's device
4. The notification uses FCM **topic-based messaging** with topic: `user_{recipient_id}`

## Requirements

To enable chat push notifications, you need:

1. ✅ **Firebase Project** - A Firebase project with FCM enabled
2. ✅ **Firebase Service Account JSON** - Service account credentials file
3. ✅ **Admin Settings Configuration** - Enable notifications in admin panel
4. ✅ **Mobile App Setup** - App must subscribe users to FCM topics

---

## Step-by-Step Setup

### Step 1: Get Firebase Service Account JSON

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project (or create a new one)
3. Go to **Project Settings** (gear icon) → **Service Accounts** tab
4. Click **Generate New Private Key**
5. **Important:** The language selection (Node.js, Python, Go, Java, etc.) is **ONLY for example code snippets**. The JSON file itself is **identical regardless of which language you select**. You can choose any language - it doesn't matter!
6. Download the JSON file (e.g., `your-project-firebase-adminsdk-xxxxx.json`)

**Note:** This service account JSON is for your **backend (Laravel/PHP)** to send push notifications. Your **Flutter app** needs separate configuration files (see "Flutter App Configuration" section below).

### Step 2: Configure Admin Settings

1. **Login to Admin Panel**
   - Navigate to: `Settings` → `Other Settings` (or `/setting/other-setting`)

2. **Enable Firebase Notifications**
   - Toggle **"Firebase Notification"** switch to **ON**

3. **Enter Firebase Project ID**
   - Find your Project ID in Firebase Console (Project Settings → General)
   - Enter it in the **"Firebase Project ID"** field

4. **Upload Service Account JSON**
   - Click **"Upload Firebase JSON files"**
   - Select the JSON file downloaded in Step 1
   - The file will be saved to `storage/app/data/` directory

5. **Save Settings**
   - Click **Save** to apply changes

### Step 3: Verify Configuration

Check the following:

#### A. Check Settings in Database
```sql
SELECT * FROM settings WHERE type = 'OTHER_SETTING' AND key = 'OTHER_SETTING';
```

The `value` JSON should contain:
```json
{
  "firebase_notification": 1,
  "project_id": "your-project-id"
}
```

**Example of Correct Configuration:**
```json
{
  "firebase_notification": 1,
  "project_id": "laravel-firebase-28deb",
  "auto_assign_provider": 0,
  "dashboard_type": "dashboard"
}
```

✅ **Your Configuration Status:**
- ✅ `firebase_notification: 1` - **Enabled** ✓
- ✅ `project_id: "laravel-firebase-28deb"` - **Configured** ✓

**Your Firebase notification settings are correctly configured!** The system should be able to send push notifications.

**Next Step:** Verify that the Firebase service account JSON file is uploaded (see section B below).

#### B. Verify JSON File Location
The Firebase service account JSON file should be in:
```
storage/app/data/*.json
```

**Check if file exists:**

**Via Command Line:**
```bash
# Windows (PowerShell)
Get-ChildItem storage\app\data\*.json

# Linux/Mac
ls storage/app/data/*.json
```

**Via PHP (in Laravel Tinker):**
```php
php artisan tinker
>>> File::glob(storage_path('app/data/*.json'));
```

**Expected Result:**
- Should return at least one `.json` file
- File should contain `"project_id": "laravel-firebase-28deb"` (must match your Project ID)
- File should have `"type": "service_account"`

**If file is missing:**
1. Go to Admin Panel → Settings → Other Settings
2. Upload the Firebase service account JSON file
3. The file will be saved to `storage/app/data/` automatically

#### C. Check Logs
When a chat message is sent, check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

Look for these log messages:
- ✅ `FCM notification sent successfully to user {id}` - Success
- ⚠️ `FCM notification skipped - Firebase notifications disabled` - Not enabled
- ⚠️ `FCM notification skipped - No project ID configured` - Missing project ID
- ⚠️ `FCM notification skipped - No access token available` - JSON file issue
- ⚠️ `FCM notification failed for user {id} - HTTP {code}` - API error

---

## Mobile App Requirements (Flutter)

**Important:** The mobile app must subscribe users to FCM topics for notifications to work.

### Flutter App Configuration

Your Flutter app needs **different configuration files** than the service account JSON:

#### For Android:
1. Go to Firebase Console → Project Settings → **Your apps** → Android app
2. Download `google-services.json`
3. Place it in: `android/app/google-services.json`
4. Add to `android/build.gradle`:
```gradle
dependencies {
    classpath 'com.google.gms:google-services:4.3.15'
}
```
5. Add to `android/app/build.gradle`:
```gradle
apply plugin: 'com.google.gms.google-services'
```

#### For iOS:
1. Go to Firebase Console → Project Settings → **Your apps** → iOS app
2. Download `GoogleService-Info.plist`
3. Place it in: `ios/Runner/GoogleService-Info.plist`
4. Add to Xcode project

#### Flutter Dependencies:
Add to `pubspec.yaml`:
```yaml
dependencies:
  firebase_core: ^2.24.0
  firebase_messaging: ^14.7.0
```

### Topic Subscription (Flutter)

When a user logs in, subscribe them to their notification topic:

```dart
import 'package:firebase_messaging/firebase_messaging.dart';

// Subscribe user to their notification topic
Future<void> subscribeToUserTopic(int userId) async {
  String topic = 'user_$userId';
  await FirebaseMessaging.instance.subscribeToTopic(topic);
  print('Subscribed to topic: $topic');
}

// Unsubscribe when user logs out
Future<void> unsubscribeFromUserTopic(int userId) async {
  String topic = 'user_$userId';
  await FirebaseMessaging.instance.unsubscribeFromTopic(topic);
  print('Unsubscribed from topic: $topic');
}
```

### Complete Flutter Setup Example

```dart
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

// Initialize Firebase
await Firebase.initializeApp();

// Request notification permissions
NotificationSettings settings = await FirebaseMessaging.instance.requestPermission(
  alert: true,
  badge: true,
  sound: true,
);

if (settings.authorizationStatus == AuthorizationStatus.authorized) {
  print('User granted permission');
  
  // Subscribe to user topic after login
  int userId = getCurrentUserId(); // Your function to get user ID
  await subscribeToUserTopic(userId);
  
  // Handle foreground messages
  FirebaseMessaging.onMessage.listen((RemoteMessage message) {
    print('Got a message whilst in the foreground!');
    print('Message data: ${message.data}');
    if (message.notification != null) {
      print('Message also contained a notification: ${message.notification}');
    }
  });
  
  // Handle background messages (requires top-level function)
  FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);
}
```

### Background Message Handler

Add this to your main.dart or a separate file:

```dart
@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  print('Handling a background message: ${message.messageId}');
  print('Message data: ${message.data}');
}
```

### Implementation Example (React Native)
```javascript
import messaging from '@react-native-firebase/messaging';

// Subscribe user to their notification topic
const topic = `user_${userId}`;
await messaging().subscribeToTopic(topic);
```

---

## Troubleshooting

### Issue 1: Notifications Not Received

**Check:**
1. ✅ Firebase notification is enabled in admin settings
2. ✅ Project ID is correctly configured
3. ✅ JSON file is uploaded and accessible
4. ✅ Mobile app is subscribed to correct topic (`user_{user_id}`)
5. ✅ User has granted notification permissions on device
6. ✅ Check Laravel logs for error messages

**Debug Steps:**
```php
// Check if notification is being sent
// Look in storage/logs/laravel.log for:
// - "FCM notification sent successfully"
// - "FCM notification skipped"
// - "FCM notification failed"
```

### Issue 2: "No access token available"

**Cause:** Firebase JSON file is missing or invalid

**Solution:**
1. Re-upload the Firebase service account JSON file
2. Ensure file is in `storage/app/data/` directory
3. Check file permissions (should be readable)
4. Verify JSON file is valid (not corrupted)

### Issue 3: "No project ID configured"

**Cause:** Project ID is not set in admin settings

**Solution:**
1. Go to admin settings → Other Settings
2. Enter your Firebase Project ID
3. Save settings

### Issue 4: HTTP 401/403 Errors

**Cause:** Invalid service account credentials or permissions

**Solution:**
1. Generate a new service account JSON file
2. Ensure the service account has "Firebase Cloud Messaging API Admin" role
3. Re-upload the JSON file

### Issue 5: HTTP 404 Errors

**Cause:** Incorrect Project ID

**Solution:**
1. Verify Project ID in Firebase Console
2. Update Project ID in admin settings
3. Ensure Project ID matches the JSON file

---

## Testing Notifications

### Test via Admin Panel
1. Go to **Settings** → **Push Notification**
2. Send a test notification to a specific user
3. Check if notification is received

### Test via Chat
1. Send a chat message from User A to User B
2. Check Laravel logs for notification attempt
3. Verify User B receives push notification on their device

### Manual Test (via API)
You can test the FCM API directly:

```bash
curl -X POST \
  "https://fcm.googleapis.com/v1/projects/{PROJECT_ID}/messages:send" \
  -H "Authorization: Bearer {ACCESS_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "message": {
      "topic": "user_123",
      "notification": {
        "title": "Test Notification",
        "body": "This is a test message"
      }
    }
  }'
```

---

## Code Reference

### Notification Sending Logic
Location: `app/Http/Controllers/ChatController.php`

Key methods:
- `sendMessageNotification()` - Main notification handler
- `sendDirectFCMNotification()` - FCM API call

### Settings Location
- View: `resources/views/setting/other-setting.blade.php`
- Controller: `app/Http/Controllers/SettingController.php`
- Route: `/setting/other-setting` (POST to `/other-setting`)

### Access Token Function
Location: `app/Helper/helper.php`
- `getAccessToken()` - Retrieves OAuth token from Firebase JSON

---

## Important Notes

1. **Topic-Based Messaging**: Notifications use topics (`user_{id}`), not device tokens
2. **Mobile App Required**: Users must be subscribed to their topic in the mobile app
3. **Real-time vs Push**: WebSocket broadcasts work independently of push notifications
4. **Logging**: All notification attempts are logged in Laravel logs
5. **Error Handling**: Notification failures don't prevent messages from being sent

---

## Additional Resources

- [Firebase Console](https://console.firebase.google.com/)
- [FCM Documentation](https://firebase.google.com/docs/cloud-messaging)
- [FCM Topic Messaging](https://firebase.google.com/docs/cloud-messaging/send-message#send-messages-to-topics)

---

## Support

If notifications still don't work after following this guide:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify Firebase project settings
3. Test FCM API directly with curl
4. Ensure mobile app is properly configured

