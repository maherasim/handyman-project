# Profile form — field reference (UI only)

Source: `resources/views/setting/profile_form.blade.php`.  
**Required** / **Optional** = HTML5 `required()` in the blade + `UserRequest` when `profile=profile` (where they differ, both are noted).

**Legend**

- **text** = single-line input  
- **textarea** = multi-line  
- **select** = native dropdown (fixed options in blade)  
- **select2** = Select2 dropdown (country/state/city use AJAX options)  
- **multiselect** = Select2, multiple  
- **file** = file input  

---

## Shared fields (shown to both **provider** and **handyman**)

| Field | Type | Required | Optional |
|-------|------|----------|----------|
| `first_name` | text | Yes * | |
| `last_name` | text | Yes * | |
| `languages[]` | multiselect (spoken language **codes** from `config('spoken_language_options')`) | Yes * | |
| `username` | text | Yes * | |
| `country_id` | select2 (country list) | Yes * | |
| `state_id` | select2 (state list) | Yes * | |
| `city_id` | select2 (city list) | Yes * | |
| `tax_country_id` | select2 (disabled; value mirrored by hidden field, follows country) | — | treated as optional in UI (no *) |
| `email` | email | Yes * | |
| `contact_number` | text | Yes * | |
| `status` | select (`1` active, `0` inactive) | Yes * | |
| `profile_image` | file (`image/*`) | | Yes |
| `company_name` | text | | Yes |
| `vat_number` | text | Yes * (for provider & handyman in blade) | |
| `skills` | text | | Yes |
| `education` | select | | Yes (server: nullable) |
| `career_level` | select | Yes * (HTML + server) | |
| `years_of_experience` | select | | Yes |
| `certification` | text | | Yes |
| `availability` | select (`full_time`, `part_time`) | Yes * (HTML) | server may allow empty (nullable) |
| `mobility` | text | | Yes |
| `experience` | textarea | | Yes |
| `about_me` | textarea | | Yes |
| `address` | textarea | Yes * | |

Hidden / system (not “profile” inputs in the same sense): `profile`, `id`, duplicate hidden `username` / `email`, hidden `tax_country_id`.

---

## Provider-only fields (extra on top of shared)

Shown when `auth()->user()->hasRole('provider')` **or** when `user_type == 'provider'` for the block below.

| Field | Type | Required | Optional |
|-------|------|----------|----------|
| `designation` | text | | Yes (`@if` provider **role**) |

**“Why choose me”** — shown when `$user_data->user_type == 'provider'`:

| Field | Type | Required | Optional |
|-------|------|----------|----------|
| `title` | text | | Yes |
| `about_description` | textarea (Quill attaches in page script) | | Yes |
| `reason[]` | one or more text inputs (repeatable) | | Yes |

---

## Handyman-only fields (extra on top of shared)

Shown when `auth()->user()->hasRole('handyman')`:

| Field | Type | Required | Optional |
|-------|------|----------|----------|
| `service_address_id` | select2 (provider address list for `provider_id`) | Yes * | |

Handyman does **not** see: `designation`, or the **“Why choose me”** block (that block is only for `user_type == 'provider'`).

---

## Native `<select>` option values (same file)

**`education`:** `''`, `any_graduate`, `apprenticeship_degree`, `traineeship_degree`, `secondary_degree`, `undergraduate_diploma`, `high_school_graduate`, `associate_degree`, `college_degree`, `university_degree`, `bachelors_degree`, `masters_degree`, `doctorate_degree`, `professional_degree`

**`career_level`:** `not_specified`, `entry_level`, `intermediate_level`, `experienced`, `professional`, `middle_management`, `executive_management`, `senior_management`, `director`, `technician`, `leader`, `manager`

**`years_of_experience`:** `''`, `less_than_1`, `1_to_3`, `3_to_5`, `5_to_8`, `8_to_10`, `more_than_10`

**`availability`:** `''`, `full_time`, `part_time`

**`status`:** `1`, `0`

**`languages[]`:** all keys from `config/spoken_language_options.php` (e.g. `english`, `german`).

---

## Server validation snapshot (`profile=profile`)

- **Required:** `country_id`, `state_id`, `city_id`, `languages` (array, min 1), `career_level`, `vat_number` if user is provider or handyman, `service_address_id` if user type is handyman.  
- **Nullable (examples):** `company_name`, `skills`, `education`, `experience`, `availability` (must be `full_time` / `part_time` if present).

See `App\Http\Requests\UserRequest` for the exact rules if you need them.
