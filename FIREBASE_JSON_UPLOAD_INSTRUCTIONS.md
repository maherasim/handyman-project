# Firebase JSON File Upload Instructions

## ✅ Your Firebase JSON File

You have the Firebase service account JSON file:
- **File Name**: `laravel-firebase-28deb-firebase-adminsdk-guxsf-513a8979c9.json`
- **Project ID**: `laravel-firebase-28deb` ✅ (matches your settings)

## 📤 How to Upload the JSON File

### Option 1: Via Admin Panel (Recommended)

1. **Login to Admin Panel**
   - Go to: `https://frobster.com/setting/other-setting`

2. **Scroll to Firebase Notification Section**
   - Find the "Firebase Notification" toggle (should be ON)
   - Below it, you'll see "Firebase Project ID" and "JSON File" upload field

3. **Upload the JSON File**
   - Click "Upload Firebase JSON files" button
   - Select your file: `laravel-firebase-28deb-firebase-adminsdk-guxsf-513a8979c9.json`
   - Click "Save" to upload

4. **Verify Upload**
   - The file will be automatically saved to `storage/app/data/` directory
   - You should see a success message

### Option 2: Manual Upload (Via FTP/SSH)

If you have server access:

1. **Upload via FTP/SSH**
   ```bash
   # Copy file to storage/app/data/ directory
   cp laravel-firebase-28deb-firebase-adminsdk-guxsf-513a8979c9.json storage/app/data/
   ```

2. **Set Permissions** (if needed)
   ```bash
   chmod 644 storage/app/data/laravel-firebase-28deb-firebase-adminsdk-guxsf-513a8979c9.json
   ```

3. **Verify File Location**
   ```bash
   ls -la storage/app/data/*.json
   ```

## ✅ Verification Steps

### 1. Check File Exists
```bash
# Via SSH/Terminal
ls storage/app/data/*.json

# Should show your file
```

### 2. Check File Content
The file should contain:
- `"project_id": "laravel-firebase-28deb"` ✅
- `"type": "service_account"` ✅
- `"private_key"` (should be present) ✅

### 3. Test Access Token
```php
// In Laravel Tinker
php artisan tinker
>>> getAccessToken();
// Should return an access token string (not null)
```

### 4. Test Notification
1. Send a chat message from one user to another
2. Check Laravel logs: `storage/logs/laravel.log`
3. Look for: `FCM notification sent successfully to user {id}` ✅

## 🔧 Fixed Issues

### Issue 1: FCM Payload Format ✅ FIXED
- **Problem**: FCM v1 API requires payload wrapped in `message` object
- **Fixed**: Updated `ChatController.php` and `helper.php` to use correct format
- **Status**: ✅ Fixed

### Issue 2: JSON File Upload
- **Status**: ⚠️ Needs to be uploaded via admin panel or manually

## 📝 Next Steps

1. **Upload JSON File** (via admin panel or manually)
2. **Test Notification** (send a chat message)
3. **Check Logs** (verify success message)

## 🐛 Troubleshooting

### If you still get 401 errors:
- ✅ Verify JSON file is in `storage/app/data/` directory
- ✅ Check file permissions (should be readable)
- ✅ Verify JSON file is valid (not corrupted)
- ✅ Check `project_id` in JSON matches settings (`laravel-firebase-28deb`)

### If you get 400 errors:
- ✅ Already fixed - payload format updated
- ✅ Clear Laravel cache: `php artisan cache:clear`
- ✅ Restart queue workers if using queues

### If access token is null:
- ✅ Check JSON file exists in `storage/app/data/`
- ✅ Verify JSON file is valid JSON format
- ✅ Check file permissions
- ✅ Verify `getAccessToken()` function can read the file

## ✅ Expected Log Messages

**Success:**
```
FCM notification sent successfully to user {id}
```

**If JSON file missing:**
```
FCM notification skipped - No access token available
```

**If payload wrong (now fixed):**
```
FCM notification failed for user {id} - HTTP 400: Invalid JSON payload
```

---

**Status**: 
- ✅ FCM payload format fixed
- ⚠️ JSON file needs to be uploaded to `storage/app/data/`

