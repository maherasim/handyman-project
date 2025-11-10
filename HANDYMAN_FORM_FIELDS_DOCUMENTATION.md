# Handyman Form Fields Documentation

## Overview
This document describes all fields in the handyman create/edit form, including field names, types, validation rules, and UI components.

---

## Form Fields

### 1. **First Name**
- **Field Name:** `first_name`
- **Type:** Text Input
- **Required:** Yes (*)
- **Validation:** Required field
- **Placeholder:** First Name
- **Database Column:** `first_name`

### 2. **Last Name**
- **Field Name:** `last_name`
- **Type:** Text Input
- **Required:** Yes (*)
- **Validation:** Required field
- **Placeholder:** Last Name
- **Database Column:** `last_name`

### 3. **Username**
- **Field Name:** `username`
- **Type:** Text Input
- **Required:** Yes (*)
- **Validation:** Required field
- **Placeholder:** Username
- **Database Column:** `username`

### 4. **Email**
- **Field Name:** `email`
- **Type:** Email Input
- **Required:** Yes (*)
- **Validation:** 
  - Required field
  - Pattern: `[^@]+@[^@]+\.[a-zA-Z]{2,}`
  - Must be valid email format
- **Placeholder:** Email
- **Database Column:** `email`

### 5. **Company Name**
- **Field Name:** `company_name`
- **Type:** Text Input
- **Required:** Yes (*)
- **Validation:** Required field
- **Placeholder:** Company Name
- **Database Column:** `company_name`

### 6. **VAT Number**
- **Field Name:** `vat_number`
- **Type:** Text Input
- **Required:** Yes (*)
- **Validation:** Required field
- **Placeholder:** Vat Number
- **Database Column:** `vat_number`

### 7. **Skills**
- **Field Name:** `skills`
- **Type:** Text Input
- **Required:** Yes (*)
- **Validation:** Required field
- **Placeholder:** Skills
- **Database Column:** `skills`

### 8. **Languages**
- **Field Name:** `languages[]`
- **Type:** Multi-Select Dropdown (Select2)
- **Required:** No
- **Options:**
  - English
  - French
  - Chinese
  - Urdu
  - Spanish
  - German
- **Validation:** Multiple selection allowed
- **Placeholder:** Select Language
- **Database Column:** `languages` (stored as array/JSON)

### 9. **Experience**
- **Field Name:** `experience`
- **Type:** Textarea with TinyMCE Editor
- **Required:** No
- **Validation:** None
- **Rows:** 2
- **Placeholder:** Experience
- **Editor Features:** Bold, Italic, Lists, Links, Images, Preview
- **Database Column:** `experience`

### 10. **Education**
- **Field Name:** `education`
- **Type:** Text Input
- **Required:** Yes (*)
- **Validation:** Required field
- **Placeholder:** Education
- **Database Column:** `education`

### 11. **About Me**
- **Field Name:** `about_me`
- **Type:** Textarea with TinyMCE Editor
- **Required:** No
- **Validation:** None
- **Rows:** 2
- **Placeholder:** About Me
- **Editor Features:** Bold, Italic, Lists, Links, Images, Preview
- **Database Column:** `about_me`

### 12. **Certification**
- **Field Name:** `certification`
- **Type:** Text Input
- **Required:** Yes (*)
- **Validation:** Required field
- **Placeholder:** Certification
- **Database Column:** `certification`

### 13. **Availability**
- **Field Name:** `availability`
- **Type:** Dropdown (Select)
- **Required:** Yes (*)
- **Options:**
  - `full_time` → Full-time
  - `part_time` → Part-time
- **Validation:** Required field
- **Placeholder:** Select Availability
- **Database Column:** `availability`

### 14. **Mobility**
- **Field Name:** `mobility`
- **Type:** Text Input
- **Required:** Yes (*)
- **Validation:** Required field
- **Placeholder:** Mobility
- **Database Column:** `mobility`

### 15. **Password**
- **Field Name:** `password`
- **Type:** Password Input
- **Required:** Yes (*) - Only for new handyman creation
- **Validation:** Required for new records only
- **Placeholder:** Password
- **Autocomplete:** new-password
- **Note:** Not shown when editing existing handyman
- **Database Column:** `password` (hashed)

### 16. **Provider**
- **Field Name:** `provider_id`
- **Type:** AJAX Dropdown (Select2)
- **Required:** Yes (*)
- **Validation:** Required field
- **Visibility:** Only for admin and demo_admin roles
- **Data Source:** AJAX route `ajax-list` with type `provider`
- **Placeholder:** Select Provider
- **Database Column:** `provider_id`

### 17. **Handyman Commission**
- **Field Name:** `handyman_commission`
- **Type:** Number Input
- **Required:** No
- **Validation:**
  - Min: 1
  - Max: 85
  - Step: any (decimals allowed)
  - Custom validation: Must be between 1 and 85
- **Placeholder:** e.g. 34.5
- **Help Text:** Enter 1 to 85. Decimals allowed (e.g., 34.5).
- **Database Column:** `handyman_commission`

### 18. **Service Address**
- **Field Name:** `service_address_id`
- **Type:** AJAX Dropdown (Select2)
- **Required:** Yes (*)
- **Validation:** Required field
- **Data Source:** Dynamically loaded based on selected provider
- **AJAX Route:** `ajax-list` with type `provider_address` and `provider_id`
- **Placeholder:** Select Provider Address
- **Database Column:** `service_address_id`

### 19. **Country**
- **Field Name:** `country_id`
- **Type:** AJAX Dropdown (Select2)
- **Required:** Yes (*)
- **Validation:** Required field
- **Data Source:** AJAX route `ajax-list` with type `country`
- **Placeholder:** Select Country
- **Database Column:** `country_id`
- **Cascade Effect:** Triggers state dropdown population

### 20. **State**
- **Field Name:** `state_id`
- **Type:** AJAX Dropdown (Select2)
- **Required:** Yes (*)
- **Validation:** Required field
- **Data Source:** Dynamically loaded based on selected country
- **AJAX Route:** `ajax-list` with type `state` and `country_id`
- **Placeholder:** Select State
- **Database Column:** `state_id`
- **Cascade Effect:** Triggers city dropdown population

### 21. **City**
- **Field Name:** `city_id`
- **Type:** AJAX Dropdown (Select2)
- **Required:** Yes (*)
- **Validation:** Required field
- **Data Source:** Dynamically loaded based on selected state
- **AJAX Route:** `ajax-list` with type `city` and `state_id`
- **Placeholder:** Select City
- **Database Column:** `city_id`

### 22. **Contact Number**
- **Field Name:** `contact_number`
- **Type:** Text Input
- **Required:** Yes (*)
- **Validation:**
  - Required field
  - Max length: 15 characters
  - Pattern: Only numbers, +, -, and spaces allowed
  - Custom JavaScript validation
- **Placeholder:** Contact Number
- **Error Messages:**
  - "Contact number should not exceed 15 characters"
  - "Please enter a valid mobile number"
- **Database Column:** `contact_number`

### 23. **Status**
- **Field Name:** `status`
- **Type:** Dropdown (Select2)
- **Required:** Yes (*)
- **Options:**
  - `1` → Active
  - `0` → Inactive
- **Validation:** Required field
- **Database Column:** `status`

### 24. **Profile Image**
- **Field Name:** `profile_image`
- **Type:** File Upload
- **Required:** No
- **Validation:** 
  - Accept: image/* (jpg, jpeg, png, gif, bmp, webp, svg, tiff, ico, heic, heif)
- **UI Component:** Custom file input with label
- **Label Text:** "Choose file Profile Image"
- **Preview:** Shows existing image if available
- **Remove Option:** Available for existing images
- **Database Storage:** Media library (attachment)

### 25. **Address**
- **Field Name:** `address`
- **Type:** Textarea with TinyMCE Editor
- **Required:** No
- **Validation:** None
- **Rows:** 2
- **Placeholder:** Address
- **Editor Features:** Bold, Italic, Lists, Links, Images, Preview
- **Database Column:** `address`

---

## Hidden Fields

### 26. **ID**
- **Field Name:** `id`
- **Type:** Hidden Input
- **Value:** Handyman ID (for updates only)
- **Database Column:** `id`

### 27. **User Type**
- **Field Name:** `user_type`
- **Type:** Hidden Input
- **Value:** `handyman` (fixed)
- **Database Column:** `user_type`

---

## Form Behavior & JavaScript Functionality

### Cascading Dropdowns
1. **Country → State → City**
   - Selecting country populates state dropdown
   - Selecting state populates city dropdown

2. **Provider → Service Address**
   - Selecting provider populates service address dropdown

### Real-time Validation
1. **Contact Number:**
   - Strips non-numeric characters (except +, -, space)
   - Limits to 15 characters
   - Shows error messages dynamically

2. **Handyman Commission:**
   - Validates range (1-85)
   - Shows error if out of range

### TinyMCE Editors
Applied to:
- `address`
- `about_me`
- `experience`

**Features:**
- Undo/Redo
- Bold/Italic formatting
- Bullet/Numbered lists
- Link insertion
- Image insertion
- Preview mode

---

## Form Submission

- **Method:** POST
- **Route:** `handyman.store`
- **Encoding:** `multipart/form-data` (for file upload)
- **Validation:** HTML5 + Server-side
- **Submit Button:** "Save" (Primary button, float-end)

---

## Field Summary by Type

### Text Inputs (11)
- first_name, last_name, username, company_name, vat_number, skills, education, certification, mobility, contact_number

### Email Input (1)
- email

### Password Input (1)
- password (conditional)

### Number Input (1)
- handyman_commission

### Textareas with Rich Text Editor (3)
- experience, about_me, address

### Single Select Dropdowns (3)
- availability, status, country_id

### Multi-Select Dropdown (1)
- languages[]

### AJAX Dropdowns (4)
- provider_id, service_address_id, state_id, city_id

### File Upload (1)
- profile_image

### Hidden Fields (2)
- id, user_type

---

## Required Fields (17)
Fields marked with asterisk (*):
1. first_name
2. last_name
3. username
4. email
5. company_name
6. vat_number
7. skills
8. education
9. certification
10. availability
11. mobility
12. password (new records only)
13. provider_id (admin only)
14. service_address_id
15. country_id
16. state_id
17. city_id
18. contact_number
19. status

---

## Optional Fields (8)
1. languages
2. experience
3. about_me
4. handyman_commission
5. profile_image
6. address

---

## Notes
- Form uses Laravel Collective HTML package for form generation
- Select2 library is used for enhanced dropdowns
- TinyMCE 5 is used for rich text editing
- Form includes CSRF protection (Laravel default)
- Validation uses both client-side (HTML5) and server-side validation
- AJAX is used for dynamic dropdown population
- Media files are managed through a media library system
