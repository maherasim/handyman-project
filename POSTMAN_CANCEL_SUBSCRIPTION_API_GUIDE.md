# Postman Testing Guide: Cancel Subscription API

## API Endpoint Information

**Route:** `Route::post('cancel-subscription', [ API\SubscriptionController::class, 'cancelSubscription' ]);`

**Controller Method:** `SubscriptionController@cancelSubscription` (Line 89-128)

**Authentication:** Required (Sanctum Token)

---

## Overview

This API cancels a user's current subscription plan and automatically moves them to a **Basic (Free) plan** instead of just marking the subscription as cancelled.

### What Happens When You Cancel:
1. ✅ Current subscription status → **"cancelled"**
2. ✅ New subscription created with:
   - **Status:** `active` (not cancelled)
   - **Title:** `Free plan`
   - **Plan Type:** `Free plan`
   - **Type:** `weekly`
   - **Amount:** `0`
   - **No expiration date**
3. ✅ User's `is_subscribe` flag → `0` (free plan user)

---

## Postman Configuration

### 1. **Request Type**
- **Method:** `POST`

### 2. **URL**
```
http://your-domain.com/api/cancel-subscription
```

**Examples:**
- Local: `http://localhost/api/cancel-subscription`
- Local with port: `http://localhost:8000/api/cancel-subscription`
- Production: `https://yourdomain.com/api/cancel-subscription`

---

## Request Parameters

### Required Parameters

#### Body (form-data or x-www-form-urlencoded)

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `id` | integer | **Yes** | The ID of the subscription plan to cancel | `5`, `10`, `15` |

### Optional Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `user_id` | integer | No | User ID (defaults to authenticated user) | `12` |

### Authentication Required

| Header | Value |
|--------|-------|
| `Authorization` | `Bearer YOUR_ACCESS_TOKEN` |

---

## Postman Setup Steps

### Step 1: Create New Request
1. Open Postman
2. Click **"New"** → **"HTTP Request"**
3. Name it: `Cancel Subscription`

### Step 2: Configure Request Method
- Select **POST** from the dropdown

### Step 3: Enter URL
```
http://localhost/api/cancel-subscription
```
*(Replace with your actual domain/port)*

### Step 4: Set Headers
Go to **Headers** tab and add:

| Key | Value |
|-----|-------|
| `Accept` | `application/json` |
| `Content-Type` | `application/json` or `application/x-www-form-urlencoded` |
| `Authorization` | `Bearer YOUR_ACCESS_TOKEN_HERE` |

### Step 5: Add Body Parameters

#### Option A: Using form-data
1. Go to **Body** tab
2. Select **form-data**
3. Add parameters:

| Key | Value |
|-----|-------|
| `id` | `5` |
| `user_id` | `12` *(optional)* |

#### Option B: Using x-www-form-urlencoded
1. Go to **Body** tab
2. Select **x-www-form-urlencoded**
3. Add parameters:

| Key | Value |
|-----|-------|
| `id` | `5` |
| `user_id` | `12` *(optional)* |

#### Option C: Using raw JSON
1. Go to **Body** tab
2. Select **raw**
3. Choose **JSON** from dropdown
4. Enter:
```json
{
    "id": 5,
    "user_id": 12
}
```

---

## Example Requests

### Example 1: Cancel Subscription (Authenticated User)
**URL:** `http://localhost/api/cancel-subscription`

**Method:** `POST`

**Headers:**
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...
Accept: application/json
```

**Body (form-data):**
```
id: 5
```

### Example 2: Cancel Subscription for Specific User (Admin)
**URL:** `http://localhost/api/cancel-subscription`

**Method:** `POST`

**Headers:**
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...
Accept: application/json
```

**Body (JSON):**
```json
{
    "id": 5,
    "user_id": 12
}
```

---

## Expected Response

### Success Response (200 OK)

```json
{
    "status": true,
    "message": "Gold plan has been cancelled successfully. You have been moved to the Basic (Free) plan.",
    "data": null
}
```

### What Happens in Database

#### 1. Old Subscription Record (Updated)
```
id: 5
user_id: 12
title: "Gold plan"
plan_type: "Gold plan"
status: "cancelled"  ← Changed from "active" to "cancelled"
amount: 99.00
start_at: "2024-01-01 10:00:00"
end_at: "2024-02-01 10:00:00"
```

#### 2. New Subscription Record (Created)
```
id: 6  ← New record
user_id: 12
title: "Free plan"  ← Basic plan
plan_type: "Free plan"  ← Free plan type
type: "weekly"  ← Weekly type
status: "active"  ← Active status (NOT cancelled)
amount: 0  ← Free
start_at: "2024-01-15 14:30:00"  ← Current timestamp
end_at: null  ← No expiration
plan_limitation: "{}"  ← Empty limitations
description: "Basic free plan"
identifier: "free_plan_1705327800"
duration: null
```

#### 3. User Record (Updated)
```
id: 12
is_subscribe: 0  ← Changed from 1 to 0 (free plan user)
```

---

## Error Responses

### Error 1: Subscription Not Found (404)

```json
{
    "status": false,
    "message": "Subscription not found",
    "data": null
}
```

**Causes:**
- Invalid subscription ID
- Subscription doesn't belong to the user
- Subscription already cancelled

### Error 2: Unauthorized (401)

```json
{
    "status": false,
    "message": "Unauthenticated",
    "data": null
}
```

**Causes:**
- Missing or invalid authentication token
- Token expired

### Error 3: Missing Required Parameter (422)

```json
{
    "status": false,
    "message": "The id field is required.",
    "data": null
}
```

**Causes:**
- Missing `id` parameter in request

---

## API Behavior Details

### Subscription Status Flow

```
Current Plan (Active)
         ↓
    Cancel API Called
         ↓
    ┌─────────────────────┐
    │ Old Subscription    │
    │ Status: cancelled   │  ← Marked as cancelled
    └─────────────────────┘
         ↓
    ┌─────────────────────┐
    │ New Subscription    │
    │ Status: active      │  ← New Basic plan created
    │ Title: Free plan    │
    │ Type: weekly        │
    │ Amount: 0           │
    └─────────────────────┘
         ↓
    User moved to Basic Plan
```

### Key Features

1. **Automatic Downgrade:** User is automatically moved to Basic/Free plan
2. **No Service Interruption:** New plan is immediately active
3. **Zero Cost:** Free plan has amount = 0
4. **No Expiration:** Free plan has no end date (end_at = null)
5. **Status is Active:** The new Basic plan status is "active", not "cancelled"
6. **Weekly Type:** Free plan type is set to "weekly"
7. **Unique Identifier:** Each free plan gets a unique identifier with timestamp

---

## Testing Scenarios

### Test Case 1: Cancel Active Subscription
**Input:** 
- `id: 5` (Active Gold plan)
- Valid auth token

**Expected:**
- Old plan status → "cancelled"
- New Basic plan created with status "active"
- User `is_subscribe` → 0
- Success message returned

### Test Case 2: Cancel Already Cancelled Subscription
**Input:** 
- `id: 5` (Already cancelled plan)
- Valid auth token

**Expected:**
- May fail or create duplicate Basic plan
- Consider adding validation to prevent this

### Test Case 3: Cancel Non-Existent Subscription
**Input:** 
- `id: 99999` (Doesn't exist)
- Valid auth token

**Expected:**
- No changes made
- Message may be empty or error

### Test Case 4: Cancel Another User's Subscription
**Input:** 
- `id: 5` (Belongs to user 10)
- Auth token for user 12

**Expected:**
- Subscription not found
- No changes made

### Test Case 5: Admin Cancels User Subscription
**Input:** 
- `id: 5`
- `user_id: 12`
- Admin auth token

**Expected:**
- User 12's subscription cancelled
- New Basic plan created for user 12
- Success message returned

---

## Database Changes Summary

### Tables Affected

1. **provider_subscriptions**
   - Old record: `status` updated to "cancelled"
   - New record: Created with Basic/Free plan details

2. **users**
   - `is_subscribe` updated to 0

---

## Important Notes

### 1. **Status is NOT "cancelled"**
The new Basic plan has status = **"active"**, not "cancelled". This ensures:
- User can still access basic features
- System recognizes them as having an active (free) plan
- No confusion with truly cancelled/inactive accounts

### 2. **Plan Type Consistency**
- `title`: "Free plan"
- `plan_type`: "Free plan"
- `type`: "weekly"

All three fields are set consistently for the Basic plan.

### 3. **No Expiration**
Free plans have `end_at = null`, meaning they never expire.

### 4. **User Subscription Flag**
`is_subscribe = 0` indicates the user is on a free plan, not a paid subscription.

### 5. **Multiple Cancellations**
If a user cancels multiple times, multiple Basic plan records will be created. Consider adding logic to:
- Check if user already has an active Basic plan
- Update existing Basic plan instead of creating new one

---

## Recommended Improvements

### 1. Check for Existing Basic Plan
```php
// Before creating new Basic plan
$existing_basic = ProviderSubscription::where('user_id', $user_id)
    ->where('plan_type', 'Free plan')
    ->where('status', 'active')
    ->first();

if (!$existing_basic) {
    // Create new Basic plan
    ProviderSubscription::create($basic_plan_data);
}
```

### 2. Add Validation
```php
if (!$provider_subscription) {
    $message = 'Subscription not found';
    return comman_message_response($message, 404);
}

if ($provider_subscription->status === 'cancelled') {
    $message = 'Subscription is already cancelled';
    return comman_message_response($message, 400);
}
```

### 3. Return More Data
```php
$response = [
    'old_plan' => [
        'id' => $provider_subscription->id,
        'title' => $provider_subscription->title,
        'status' => 'cancelled'
    ],
    'new_plan' => [
        'title' => 'Free plan',
        'status' => 'active',
        'type' => 'weekly'
    ]
];
return comman_custom_response($response);
```

---

## Quick Start Checklist

- [ ] Open Postman
- [ ] Create new POST request
- [ ] Set URL: `http://localhost/api/cancel-subscription`
- [ ] Add header: `Authorization: Bearer YOUR_TOKEN`
- [ ] Add header: `Accept: application/json`
- [ ] Go to Body tab
- [ ] Select **form-data** or **raw JSON**
- [ ] Add parameter: `id` with subscription ID
- [ ] Click **Send**
- [ ] Verify response status is 200
- [ ] Check database: Old plan status = "cancelled"
- [ ] Check database: New plan created with status = "active"
- [ ] Check database: User `is_subscribe` = 0

---

## Sample Postman Collection (JSON)

```json
{
    "info": {
        "name": "Cancel Subscription API",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": [
        {
            "name": "Cancel Subscription",
            "request": {
                "method": "POST",
                "header": [
                    {
                        "key": "Accept",
                        "value": "application/json"
                    },
                    {
                        "key": "Authorization",
                        "value": "Bearer {{access_token}}"
                    }
                ],
                "body": {
                    "mode": "formdata",
                    "formdata": [
                        {
                            "key": "id",
                            "value": "5",
                            "type": "text"
                        },
                        {
                            "key": "user_id",
                            "value": "12",
                            "type": "text",
                            "disabled": true
                        }
                    ]
                },
                "url": {
                    "raw": "http://localhost/api/cancel-subscription",
                    "protocol": "http",
                    "host": ["localhost"],
                    "path": ["api", "cancel-subscription"]
                }
            }
        }
    ]
}
```

Save this as `cancel-subscription-api.postman_collection.json` and import into Postman.

---

## Summary

When the `cancel-subscription` API is called:

✅ **Old Subscription:** Status changed to "cancelled"  
✅ **New Subscription:** Created with status "active" (Basic/Free plan)  
✅ **Title:** "Free plan"  
✅ **Plan Type:** "Free plan"  
✅ **Type:** "weekly"  
✅ **Amount:** 0 (free)  
✅ **Expiration:** None (null)  
✅ **User Flag:** `is_subscribe` = 0  

The user is seamlessly transitioned to a Basic plan instead of being left without any active subscription.
