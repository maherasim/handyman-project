# UGC safety API — report & block

This document describes endpoints for **reporting listings** and **blocking users** (same behavior as the web flows on `/service-list` and `/job-datatable`).

**Controller:** `App\Http\Controllers\UgcSafetyController`

### Provider (job listing) — two APIs (payload reference)

Use these when the logged-in user is a **provider** (same flows as web `/job-datatable`).

**Common request headers (mobile / API)**

| Header | Value |
|--------|--------|
| `Authorization` | `Bearer <sanctum_personal_access_token>` |
| `Content-Type` | `application/json` |
| `Accept` | `application/json` |

**Base path:** `/api` — e.g. production `https://frobster.com/api/...`

---

#### 1. Report a job request

| | |
|--|--|
| **Method** | `POST` |
| **Path** | `/api/ugc/report-post-job` |
| **Full URL example** | `https://frobster.com/api/ugc/report-post-job` |

**JSON body (required fields)**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `post_job_id` | integer | Yes | ID of the row in `post_job_requests` (the job listing you are reporting). |
| `reason` | string | Yes | One of: `spam`, `harassment`, `inappropriate`, `fraud`, `other`. |
| `details` | string | No | Extra text; max **2000** characters. |

**Example payload**

```json
{
  "post_job_id": 1001,
  "reason": "spam",
  "details": "Optional explanation for moderators."
}
```

**Success (`200`)** — example shape:

```json
{
  "message": "Thank you. Your report was received.",
  "policy": "Our team reviews reports as soon as possible..."
}
```

**Typical errors:** `403` (not allowed to report), `404` (job missing), `422` (validation, cannot report your own job, or duplicate pending report).

---

#### 2. Block the customer (job poster)

| | |
|--|--|
| **Method** | `POST` |
| **Path** | `/api/ugc/block` |
| **Full URL example** | `https://frobster.com/api/ugc/block` |

**JSON body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `blocked_user_id` | integer | Yes | The **customer’s user id** (`customer_id` on the post job). **Not** the `post_job_id`. |

**Example payload**

```json
{
  "blocked_user_id": 128
}
```

**Success (`200`)** — example:

```json
{
  "message": "You will no longer see this person's listings where your account supports blocking."
}
```

**Typical errors:** `403` (not allowed), `422` (cannot block yourself, invalid target user type).

**Idempotent:** if this user was already blocked, the request still succeeds (`firstOrCreate`).

---

**Web (session + CSRF):** same paths **without** the `api` prefix — `POST /ugc/report-post-job` and `POST /ugc/block`, with `X-CSRF-TOKEN` / `_token` as for other web POSTs.

---

### Report reason dropdown (Android / iOS / Flutter)

Build your **Spinner** / **DropdownButton** from this list so `reason` matches validation on `POST .../ugc/report` and `POST .../ugc/report-post-job`.

| | |
|--|--|
| **Method** | `GET` |
| **Path** | `/api/ugc/report-reasons` |
| **Auth** | Not required |
| **Headers** | `Accept: application/json` (optional: `Accept-Language` / app locale for translated `label`) |

**Response (`200`)**

```json
{
  "reasons": [
    { "value": "spam", "label": "Spam or misleading" },
    { "value": "harassment", "label": "Harassment or abuse" },
    { "value": "inappropriate", "label": "Inappropriate content" },
    { "value": "fraud", "label": "Scam or fraud" },
    { "value": "other", "label": "Other" }
  ]
}
```

- Show **`label`** in the UI; send **`value`** as the JSON field `reason` when submitting the report.
- Labels come from `resources/lang/*/messages.php` (`ugc_report_reason_*`).

---

## Base URLs

| Environment | API prefix (Sanctum) | Web (session + CSRF) |
|-------------|----------------------|----------------------|
| Local | `http://127.0.0.1:8000/api` | `http://127.0.0.1:8000` |

**Mobile / Flutter / SPA:** use the **API** paths with a Bearer token.

**In-browser form posts:** use the **web** paths with session cookies and `_token` (CSRF).

---

## Authentication

### API (`/api/...`)

- **Required:** `Authorization: Bearer <sanctum_token>`
- **Headers:** `Accept: application/json`, `Content-Type: application/json`
- Routes are registered inside the `auth:sanctum` group in `routes/api.php`.

### Web (`/ugc/...`)

- **Required:** logged-in session (`auth`, `verified` middleware in `routes/web.php`)
- **CSRF:** send `X-CSRF-TOKEN` header or `_token` field with the request

---

## Overview: four product actions, three HTTP endpoints

| # | User action | HTTP endpoint | Notes |
|---|-------------|---------------|--------|
| 1 | Customer **reports a service** | `POST .../ugc/report` | Body: `service_id` |
| 2 | Customer **blocks the provider** | `POST .../ugc/block` | Body: `blocked_user_id` = provider’s user id |
| 3 | Provider **reports a post job** | `POST .../ugc/report-post-job` | Body: `post_job_id` |
| 4 | Provider **blocks the customer** | `POST .../ugc/block` | Same URL as #2; `blocked_user_id` = customer’s user id |

**Block** is a single endpoint; only `blocked_user_id` changes.  
**Report** uses two different endpoints (service vs post job).

**Optional:** `POST .../ugc/unblock` — remove a block (e.g. settings screen).

---

## 1. Report a service (customer)

Used from the **service listing / service detail** when a **customer** reports another user’s service.

### API

`POST /api/ugc/report`

**Authorization:** customer only (`user_type` / role `user`). Others receive `403`.

### Request body (JSON)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `service_id` | integer | Yes | Must exist in `services` and be active (`status = 1`). |
| `reason` | string | Yes | One of: `spam`, `harassment`, `inappropriate`, `fraud`, `other`. |
| `details` | string | No | Max 2000 characters. |

### Example

```json
{
  "service_id": 42,
  "reason": "spam",
  "details": "Misleading description."
}
```

### Success response (`200`)

```json
{
  "message": "Thank you. Your report was received.",
  "policy": "Our team reviews reports as soon as possible..."
}
```

Keys are translated via `messages.php`; exact strings depend on locale.

### Error responses

| Status | Typical cause |
|--------|----------------|
| `403` | Not logged in as a **customer**. |
| `404` | Service missing or not available. |
| `422` | Validation failed; cannot report **own** service; **pending report** already exists for this service. |

### Web route name

`ugc.report` → `POST /ugc/report`

---

## 2. Report a post job (provider / eligible roles)

Used from the **job listing** when reporting someone else’s **post job request**.

### API

`POST /api/ugc/report-post-job`

**Authorization:** User must pass `UgcListing::canLoadUgcScripts` and `UgcListing::canReportPostJob` (e.g. provider; not the job owner; not admin). See `App\Support\UgcListing`.

### Request body (JSON)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `post_job_id` | integer | Yes | Must exist in `post_job_requests`. |
| `reason` | string | Yes | Same enum as service report. |
| `details` | string | No | Max 2000 characters. |

### Example

```json
{
  "post_job_id": 1001,
  "reason": "inappropriate",
  "details": "Optional context."
}
```

### Success response (`200`)

```json
{
  "message": "Thank you. Your report was received.",
  "policy": "..."
}
```

### Error responses

| Status | Typical cause |
|--------|----------------|
| `403` | Not allowed to report (e.g. wrong role, or job owner). |
| `404` | Job not found. |
| `422` | Validation failed; **own** job; duplicate **pending** report. |

### Web route name

`ugc.report.post_job` → `POST /ugc/report-post-job`

---

## 3. Block user (shared: block provider or block customer)

Creates a row in `user_blocks` (`blocker_id` = current user, `blocked_id` = target).

### API

`POST /api/ugc/block`

**Authorization:** `UgcListing::canLoadUgcScripts` (customer, provider, or handyman — see `UgcSafetyController::blockUser`).

### Request body (JSON)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `blocked_user_id` | integer | Yes | Target user must exist. Allowed target `user_type`: `provider`, `user`, or `handyman`. |

### Examples

**From service list — block the provider**

```json
{ "blocked_user_id": 55 }
```

(`55` = the service’s `provider_id` / provider user id.)

**From job list — block the customer**

```json
{ "blocked_user_id": 12 }
```

(`12` = the post job’s `customer_id`.)

### Success response (`200`)

```json
{
  "message": "You will no longer see this person's listings..."
}
```

### Error responses

| Status | Typical cause |
|--------|----------------|
| `403` | Not allowed to use block feature. |
| `422` | Validation; cannot block **self**; invalid target type. |

**Idempotent:** if the block already exists, it still succeeds (`firstOrCreate`).

### Web route name

`ugc.block` → `POST /ugc/block`

---

## 4. Unblock user (optional)

### API

`POST /api/ugc/unblock`

### Request body (JSON)

| Field | Type | Required |
|-------|------|----------|
| `blocked_user_id` | integer | Yes |

### Success response (`200`)

```json
{
  "message": "Unblocked."
}
```

### Web route name

`ugc.unblock` → `POST /ugc/unblock`

---

## Related listing APIs (moderation & blocks)

After **admin** actions in **Content Reports** (`content_reports`, admin UI), listings are filtered server-side.

| Endpoint | Purpose |
|----------|---------|
| `GET /api/service-list` | Applies `UgcListing::scopePublicServices` — hides moderated/hidden services and respects blocks for customers. |
| `GET /api/get-post-job` (auth) | Applies `UgcListing::scopePublicPostJobs` — aligns with web job datatable. |

**Service detail:** `POST /api/service-detail` — respects hidden services and blocked providers for customers.

**Post job detail:** `POST /api/get-post-job-detail` — visibility uses `UgcListing::canViewPostJobRequest`.

---

## Flutter / client integration checklist

1. **Store Sanctum token** after login; send on all `/api/ugc/*` calls.
2. **Service card:** pass `service_id` for report; `blocked_user_id` = provider user id for block.
3. **Job card:** pass `post_job_id` for report; `blocked_user_id` = customer user id for block.
4. **UI:** reason = single-select (5 values); optional multiline details (max 2000); confirm dialog before block.
5. **After successful block:** remove item from local list or refetch list APIs.
6. **Errors:** show `message` from JSON body for `4xx` responses.

---

## Route reference (Laravel names)

| Name | Method | URI (no domain) |
|------|--------|-----------------|
| `api.ugc.report` | POST | `api/ugc/report` |
| `api.ugc.report_post_job` | POST | `api/ugc/report-post-job` |
| `api.ugc.block` | POST | `api/ugc/block` |
| `api.ugc.unblock` | POST | `api/ugc/unblock` |
| `api.ugc.report_reasons` | GET | `api/ugc/report-reasons` |
| `ugc.report` | POST | `ugc/report` |
| `ugc.report.post_job` | POST | `ugc/report-post-job` |
| `ugc.block` | POST | `ugc/block` |
| `ugc.unblock` | POST | `ugc/unblock` |

---

## Source files

- `app/Http/Controllers/UgcSafetyController.php`
- `app/Support/UgcListing.php`
- `routes/api.php` (Sanctum group)
- `routes/web.php` (authenticated `ugc/*` routes)
