# UGC reporting & block — API reference (Postman)

Base URL example: `https://frobster.com/api`  
 

All paths below are relative to that base (e.g. `POST {{baseUrl}}/ugc/report-profile`).

---

## Headers (protected endpoints)

| Header | Value |
|--------|--------|
| `Authorization` | `Bearer <your_sanctum_token>` |
| `Accept` | `application/json` |
| `Content-Type` | `application/json` |

---

## 1. Get report reasons (dropdown)

| | |
|--|--|
| **Method** | `GET` |
| **URL** | `/ugc/report-reasons` |
| **Auth** | No |

**Success — `200`**

```json
{
  "reasons": [
    { "value": "off_platform_requests", "label": "Off-platform requests" }
  ]
}
```

Use each object’s **`value`** as the `reason` field in the POST endpoints below. Show **`label`** in the UI.

---

## 2. Report a user (profile report)

Report another member (customer, provider, or handyman account).

| | |
|--|--|
| **Method** | `POST` |
| **URL** | `/ugc/report-profile` |
| **Auth** | Yes (Sanctum) |

**Body (JSON)**

| Field | Type | Required | Notes |
|-------|------|----------|--------|
| `reported_user_id` | number | Yes | User id to report |
| `reason` | string | Yes | From `GET /ugc/report-reasons` → `value` |
| `details` | string | No | Max length 2000 |

**Example**

```json
{
  "reported_user_id": 42,
  "reason": "unprofessional_behavior",
  "details": "Optional notes for moderators."
}
```

**Success — `200`**

```json
{
  "message": "…",
  "policy": "…"
}
```

| Status | Typical case |
|--------|----------------|
| `403` | Not logged in or account type not allowed to report |
| `404` | User not found or not an allowed target type |
| `422` | Invalid input, cannot report yourself, or duplicate pending report |

---

## 3. Report a provider (alternate — content report on user)

Only accepts targets with `user_type` = `provider`. Prefer **`/ugc/report-profile`** unless your product specifically needs this flow.

| | |
|--|--|
| **Method** | `POST` |
| **URL** | `/ugc/report-provider` |
| **Auth** | Yes |

**Body (JSON)**

| Field | Type | Required | Notes |
|-------|------|----------|--------|
| `provider_id` | number | Yes | Must be a provider user id |
| `reason` | string | Yes | From report-reasons |
| `details` | string | No | Max 2000 |

**Example**

```json
{
  "provider_id": 42,
  "reason": "spam_or_scams",
  "details": "Optional."
}
```

**Success — `200`:** `{ "message": "…", "policy": "…" }`  
**Errors:** `403`, `404` (not a provider), `422`

---

## 4. Report a service

| | |
|--|--|
| **Method** | `POST` |
| **URL** | `/ugc/report` |
| **Auth** | Yes |

**Body (JSON)**

| Field | Type | Required | Notes |
|-------|------|----------|--------|
| `service_id` | number | Yes | Active service only |
| `reason` | string | Yes | From report-reasons |
| `details` | string | No | Max 2000 |

**Example**

```json
{
  "service_id": 3,
  "reason": "misleading_profile_or_skills",
  "details": "Optional."
}
```

**Success — `200`:** `{ "message": "…", "policy": "…" }`  
**Errors:** `403`, `404`, `422` (e.g. own service, duplicate pending)

---

## 5. Report a posted job

| | |
|--|--|
| **Method** | `POST` |
| **URL** | `/ugc/report-post-job` |
| **Auth** | Yes |

**Body (JSON)**

| Field | Type | Required | Notes |
|-------|------|----------|--------|
| `post_job_id` | number | Yes | Job request id |
| `reason` | string | Yes | From report-reasons |
| `details` | string | No | Max 2000 |

**Example**

```json
{
  "post_job_id": 3,
  "reason": "violation_of_platform_rules",
  "details": "Optional."
}
```

**Success — `200`:** `{ "message": "…", "policy": "…" }`  
**Errors:** `403`, `404`, `422`

---

## 6. Report a review

| | |
|--|--|
| **Method** | `POST` |
| **URL** | `/ugc/report-review` |
| **Auth** | Yes |

**Body (JSON)**

| Field | Type | Required | Notes |
|-------|------|----------|--------|
| `review_id` | number | Yes | Id of the review row |
| `review_type` | string | No | Default: `booking_rating` |
| `reason` | string | Yes | From report-reasons |
| `details` | string | No | Max 2000 |

**Allowed `review_type` values**

| `review_type` |
|---------------|
| `booking_rating` |
| `customer_rating` |
| `post_job_bid_rating` |
| `post_job_bid_customer_rating` |

**Example**

```json
{
  "review_type": "booking_rating",
  "review_id": 99,
  "reason": "harassment_or_bullying",
  "details": "Optional."
}
```

**Success — `200`:** `{ "message": "…", "policy": "…" }`  
**Errors:** `403`, `404`, `422`

---

## 7. Block a user

| | |
|--|--|
| **Method** | `POST` |
| **URL** | `/ugc/block` |
| **Auth** | Yes |

**Body (JSON)**

```json
{
  "blocked_user_id": 55
}
```

**Success — `200`**

```json
{
  "message": "…"
}
```

**Errors:** `403`, `422`  
Repeat calls with the same id are treated as success (idempotent).

---

## 8. Unblock a user

| | |
|--|--|
| **Method** | `POST` |
| **URL** | `/ugc/unblock` |
| **Auth** | Yes |

**Body (JSON)**

```json
{
  "blocked_user_id": 55
}
```

**Success — `200`:** `{ "message": "…" }`

---

## Postman quick setup

1. Create an environment variable `baseUrl` = `https://frobster.com/api` (or your host).
2. After login via your app’s auth API, copy the Sanctum token into `token` (or paste into the `Authorization` header).
3. **Collection auth (optional):** Type Bearer Token, token `{{token}}`.
4. Run **`GET {{baseUrl}}/ugc/report-reasons`** first; pick a `value` for `reason` in POSTs.

---

## Endpoint summary

| Method | Path |
|--------|------|
| `GET` | `/ugc/report-reasons` |
| `POST` | `/ugc/report-profile` |
| `POST` | `/ugc/report-provider` |
| `POST` | `/ugc/report` |
| `POST` | `/ugc/report-post-job` |
| `POST` | `/ugc/report-review` |
| `POST` | `/ugc/block` |
| `POST` | `/ugc/unblock` |

---

## Which “report user” call to use

| Scenario | Endpoint | Id field |
|----------|----------|----------|
| Report employer on service page | `POST /ugc/report-profile` | `reported_user_id` = provider’s user id |
| Report customer on job page | `POST /ugc/report-profile` | `reported_user_id` = customer’s user id |
| Report service listing | `POST /ugc/report` | `service_id` |
| Report job listing | `POST /ugc/report-post-job` | `post_job_id` |
