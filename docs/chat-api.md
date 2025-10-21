# Frobster Chat API Documentation

Base URL: `https://frobster.com`

Auth: All endpoints require a Bearer token in the `Authorization` header unless stated otherwise.

Content Types:
- Requests: `application/json` unless sending files (then `multipart/form-data`)
- Responses: `application/json`

---

## 1. Standalone Direct Messages (DM)
Use standalone conversations for app chat (not tied to booking/bid).

### Open or Resume a DM with a User
POST `/api/chat/open-with-user`

Request body
```json
{ "user_id": 123, "title": "Direct Message" }
```

Response
```json
{ "status": true, "conversation_id": 456, "existing": true }
```

Notes
- Prevents self-chat; reuses an existing DM between the same two users.

---

## 2. Conversation List and Unread Summary

### List Conversations (paginated)
GET `/api/chat/conversations?page=1`

Response (example)
```json
{
  "status": true,
  "data": [
    {
      "id": 456,
      "title": "Direct Message",
      "other_user": { "id": 7, "name": "Alex", "avatar_url": "https://..." },
      "last_message": { "id": 999, "preview": "Hi…", "created_at": "2025-10-21 11:22:33", "read": false },
      "unread_count": 3
    }
  ],
  "pagination": { "current_page": 1, "last_page": 5, "per_page": 20, "total": 100 }
}
```

### Unread Summary
GET `/api/chat/unread`

Response
```json
{ "status": true, "total_unread": 3, "by_conversation": [{ "conversation_id": 456, "unread": 3 }] }
```

---

## 3. Messages

### Fetch Messages (paging by id)
GET `/api/chat/{conversationId}/messages?after_id=0&before_id=0&limit=50`

- Use `after_id` to fetch newer messages
- Use `before_id` to load older history (infinite scroll up)

Response (example)
```json
{
  "status": true,
  "messages": [
    {
      "id": 1001,
      "sender_id": 7,
      "sender_name": "Alex",
      "sender_avatar_url": "https://...",
      "message": "Hi there",
      "created_at": "2025-10-21 11:22:33",
      "read": false,
      "attachment": null,
      "policy_violation": false,
      "hidden": false,
      "pii_types": []
    }
  ]
}
```

When `policy_violation` is true, `hidden` will be true and the server will hide the `message`/`attachment` for both users. Display an inline warning in the app.

### Send Message (text and/or file)
POST `/api/chat/{conversationId}/send`

- `multipart/form-data`
- Fields: `message?` (string), `attachment?` (file up to 5 MB)

Response
```json
{ "status": true, "id": 1002, "flagged": true, "pii_types": ["email", "phone"] }
```

App behavior
- If `flagged` is true, show a non-blocking banner: "Message hidden due to policy (email/phone/…)".

### Mark Messages as Read
POST `/api/chat/{conversationId}/read`

Request body
```json
{ "up_to_id": 1002 }
```

Response
```json
{ "status": true }
```

### Download Attachment
GET `/api/chat/download/{messageId}`

- Returns the file for a message the caller is authorized to view.

---

## 4. People Picker (optional)
GET `/api/chat/users?query=alex&page=1`

Response
```json
{
  "status": true,
  "data": [ { "id": 7, "display_name": "Alex", "avatar_url": "https://...", "user_type": "provider" } ],
  "pagination": { "current_page": 1, "last_page": 1, "per_page": 20, "total": 1 }
}
```

---

## 5. Contextual Opens (optional; keep for deep links)
- POST `/api/chat/open-by-bid` { `bid_id`: number } → `{ status, conversation_id }`
- POST `/api/chat/open-by-booking` { `booking_id`: number } → `{ status, conversation_id }`

App should prefer `open-with-user` for core DM UX; use contextual opens for deep links.

---

## 6. Push Notifications (recommended)
Register device tokens per user so the server can push on new messages.

### Register Token
POST `/api/push/register`

Request body
```json
{ "token": "<device-token>", "platform": "ios|android|web", "device_id": "optional-unique-id" }
```
Response
```json
{ "status": true }
```

### Unregister Token
POST `/api/push/unregister`

Request body
```json
{ "token": "<device-token>" }
```
Response
```json
{ "status": true }
```

### Push Payload (FCM example)
```json
{
  "to": "<device-token>",
  "priority": "high",
  "content_available": true,
  "data": {
    "type": "chat",
    "conversation_id": 456,
    "message_id": 1002,
    "sender_id": 7,
    "preview": "Hi there…",
    "sent_at": "2025-10-21T12:34:56Z"
  },
  "notification": {
    "title": "New message from Alex",
    "body": "Hi there…"
  }
}
```

Client handling
- Foreground: if current thread matches `conversation_id`, fetch `after_id` and append; otherwise bump unread badge.
- Background tap: deep link to chat screen with `conversation_id`, then fetch latest messages.

---

## 7. Polling Strategy (fallback to realtime)
If websockets are not used:
- Thread screen: poll `GET /api/chat/{conversationId}/messages?after_id=LAST_ID` every 4–5s
- List screen: poll `GET /api/chat/unread` every 10–15s
- Also react to push notifications to trigger immediate fetches.

---

## 8. PII / Policy Behavior (parity with web)
- Server detects personal contact info (email/phone/social) and flags messages.
- Responses include:
  - On send: `flagged` + `pii_types`
  - On list: `policy_violation`, `hidden`, `pii_types`
- App must display a visible warning when a message is hidden.

---

## 9. Errors & Limits
- 401 Unauthorized (invalid/missing token)
- 403 Forbidden (user is not a conversation participant)
- 422 Validation error (bad inputs)
- 413 Payload too large (attachment > 5 MB)

Recommended limits
- Send rate limit: ~10 requests / 10 seconds per user
- Message length: up to 4000 chars

---

## 10. Quick cURL Examples

Open a DM
```bash
curl -X POST "https://frobster.com/api/chat/open-with-user" \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"user_id":123,"title":"Direct Message"}'
```

Send a message
```bash
curl -X POST "https://frobster.com/api/chat/456/send" \
  -H "Authorization: Bearer <TOKEN>" \
  -F "message=Hello there"
```

Send a file
```bash
curl -X POST "https://frobster.com/api/chat/456/send" \
  -H "Authorization: Bearer <TOKEN>" \
  -F attachment=@/path/to/file.pdf
```

Fetch new messages after last id
```bash
curl -X GET "https://frobster.com/api/chat/456/messages?after_id=1002&limit=50" \
  -H "Authorization: Bearer <TOKEN>"
```

Mark as read
```bash
curl -X POST "https://frobster.com/api/chat/456/read" \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"up_to_id":1002}'
```

Register push token
```bash
curl -X POST "https://frobster.com/api/push/register" \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"token":"<device-token>","platform":"android","device_id":"abc-123"}'
```

---

## 11. Implementation Notes
- App should standardize on `conversation_id` everywhere once a chat is opened (standalone-first).
- Keep bid/booking open endpoints for deep-link context; core UX uses `open-with-user`.
- Attachments are authorized via conversation membership and are hidden for policy violations.
- Ensure device token registration occurs after login and is refreshed on token change.
