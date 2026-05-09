# Profile form (`/setting/profile_form`) — required vs optional

Source: `resources/views/setting/profile_form.blade.php` + `UserRequest` when `profile=profile` on **web** (not API).

Legend: **R** = required (UI `*` and/or server). **O** = optional. **R*** = required in UI but server rule looser (legacy / mismatch). **—** = not on that role’s form.

Profile image: **Spatie** collection `profile_image` (not a `users` column).

---

## Customer (`user_type` = `user`)

| UI label | DB column | R/O | Notes |
|----------|-----------|-----|--------|
| First name | `first_name` | R | UI `*` |
| Last name | `last_name` | R | UI `*` |
| Languages (multi) | `languages` | R | At least one; UI + server (JSON/array) |
| Username | `username` | R | Server unique |
| Country | `country_id` | R | Dropdown; UI + server |
| State | `state_id` | R | Dropdown; UI + server |
| City | `city_id` | R | Dropdown; UI + server |
| Tax country | `tax_country_id` | O | Disabled; synced from country + hidden input |
| Email | `email` | R | UI `*`; server unique |
| Contact number | `contact_number` | R* | UI `*`; server `nullable` (legacy) |
| Status (active/inactive) | `status` | R | Dropdown; UI `*` |
| Profile image | *(media)* | O | Collection `profile_image` |
| Company name | `company_name` | O | |
| VAT number | `vat_number` | O | No `*` for customers |
| Skills | `skills` | O | |
| Education | `education` | O | Dropdown |
| Career level | `career_level` | R | Dropdown; UI + server |
| Years of experience | `years_of_experience` | O | Dropdown |
| Certification | `certification` | O | |
| Availability | `availability` | R* | UI `required`; server `nullable` |
| Mobility | `mobility` | O | |
| Designation | `designation` | — | Not shown for customers |
| Experience (textarea) | `experience` | O | |
| About me | `about_me` | O | |
| Address | `address` | R | UI `*` |
| Service address | `service_address_id` | — | Not shown for customers |
| Why choose me (title / description / reasons) | `why_choose_me` | — | Not shown for customers |

---

## Provider (`user_type` = `provider`)

Full list for fields that appear on this profile page (plus hidden post fields).

| UI label | DB column | R/O | Notes |
|----------|-----------|-----|--------|
| First name | `first_name` | R | UI `*` |
| Last name | `last_name` | R | UI `*` |
| Languages (multi) | `languages` | R | At least one; UI + server |
| Username | `username` | R | Hidden input; server unique |
| Country | `country_id` | R | Dropdown; UI + server |
| State | `state_id` | R | Dropdown; UI + server |
| City | `city_id` | R | Dropdown; UI + server |
| Tax country | `tax_country_id` | O | Disabled select + hidden; synced to country |
| Email | `email` | R | Hidden input; server unique |
| Contact number | `contact_number` | R* | UI `*`; server `nullable` |
| Status (active/inactive) | `status` | R | Dropdown; UI `*` |
| Profile image | *(media)* | O | `profile_image` |
| Company name | `company_name` | O | |
| VAT number | `vat_number` | R | UI `*`; server required |
| Skills | `skills` | O | |
| Education | `education` | O | Dropdown |
| Career level | `career_level` | R | Dropdown; UI + server |
| Years of experience | `years_of_experience` | O | Dropdown |
| Certification | `certification` | O | |
| Availability | `availability` | R* | UI `required`; server `nullable` |
| Mobility | `mobility` | O | |
| Designation | `designation` | O | Provider-only block |
| Experience (textarea) | `experience` | O | |
| About me | `about_me` | O | |
| Address | `address` | R | UI `*` |
| Service address | `service_address_id` | — | Not on provider profile form |
| Why choose me — Title | `why_choose_me` → `title` | O | Stored in JSON column |
| Why choose me — Description | `why_choose_me` → `about_description` | O | Quill HTML inside JSON |
| Why choose me — Reason(s) | `why_choose_me` → `reason` | O | Array of strings in JSON |

---

## Handyman / worker (`user_type` = `handyman`)

Full list for fields on this profile page.

| UI label | DB column | R/O | Notes |
|----------|-----------|-----|--------|
| First name | `first_name` | R | UI `*` |
| Last name | `last_name` | R | UI `*` |
| Languages (multi) | `languages` | R | At least one; UI + server |
| Username | `username` | R | Hidden input; server unique |
| Country | `country_id` | R | Dropdown; UI + server |
| State | `state_id` | R | Dropdown; UI + server |
| City | `city_id` | R | Dropdown; UI + server |
| Tax country | `tax_country_id` | O | Disabled + hidden; synced to country |
| Email | `email` | R | Hidden input; server unique |
| Contact number | `contact_number` | R* | UI `*`; server `nullable` |
| **Service address** | `service_address_id` | R | Dropdown; `provider_address_mappings.id`; UI `*` + server |
| Status (active/inactive) | `status` | R | Dropdown; UI `*` |
| Profile image | *(media)* | O | `profile_image` |
| Company name | `company_name` | O | |
| VAT number | `vat_number` | R | UI `*`; server required |
| Skills | `skills` | O | |
| Education | `education` | O | Dropdown |
| Career level | `career_level` | R | Dropdown; UI + server |
| Years of experience | `years_of_experience` | O | Dropdown |
| Certification | `certification` | O | |
| Availability | `availability` | R* | UI `required`; server `nullable` |
| Mobility | `mobility` | O | |
| Designation | `designation` | — | Not shown (provider-only in Blade) |
| Experience (textarea) | `experience` | O | |
| About me | `about_me` | O | |
| Address | `address` | R | UI `*` |
| Why choose me (title / description / reasons) | `why_choose_me` | — | Not shown for handyman (`user_type == provider` block only) |

---

## Quick compare

| DB column / topic | Customer | Provider | Handyman |
|-------------------|----------|----------|----------|
| `vat_number` | O | R | R |
| `designation` | — | O | — |
| `service_address_id` | — | — | R |
| `why_choose_me` | — | O (all subfields) | — |
| All other shared columns | see customer table | same R/O as customer except rows above | same as customer except rows above |

---

## Reference

- **Model:** `App\Models\User` `$fillable`
- **UI:** `resources/views/setting/profile_form.blade.php`
- **Validation:** `App\Http\Requests\UserRequest` → `profile === 'profile'`, web
- **Save:** `SettingController::updateProfile`

API profile uses different (looser) rules; this doc is **web** only.
