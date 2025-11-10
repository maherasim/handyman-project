# Service Form Fields Documentation

## Overview
This document describes all fields in the service create/edit form, including field names, types, validation rules, and UI components.

---

## Form Fields

### 1. **Service Name**
- **Field Name:** `name`
- **Type:** Text Input
- **Required:** Yes (*)
- **Validation:** 
  - Alphabetic characters and spaces only
  - Title attribute: "Please enter alphabetic characters and spaces only"
- **Placeholder:** Service Name
- **Database Column:** `name`

### 2. **Category**
- **Field Name:** `category_id`
- **Type:** AJAX Dropdown (Select2)
- **Required:** Yes (*)
- **Validation:** Required field
- **Data Source:** AJAX route `ajax-list` with type `category`
- **Placeholder:** Select Category
- **Database Column:** `category_id`
- **Cascade Effect:** Triggers subcategory dropdown population

### 3. **Subcategory**
- **Field Name:** `subcategory_id`
- **Type:** AJAX Dropdown (Select2)
- **Required:** No
- **Validation:** None
- **Data Source:** Dynamically loaded based on selected category
- **AJAX Route:** `ajax-list` with type `subcategory_list` and `category_id`
- **Placeholder:** Select Subcategory
- **Database Column:** `subcategory_id`

### 4. **Country**
- **Field Name:** `country_id`
- **Type:** AJAX Dropdown (Select2)
- **Required:** No
- **Validation:** None
- **Data Source:** AJAX route `ajax-list` with type `country`
- **Placeholder:** Select Country
- **Database Column:** `country_id`
- **Cascade Effect:** Triggers state dropdown and auto-populates tax country

### 5. **Tax Country (Display)**
- **Field Name:** `tax_country_id_display`
- **Type:** AJAX Dropdown (Select2) - Disabled
- **Required:** No
- **Validation:** None
- **Data Source:** AJAX route `ajax-list` with type `country`
- **Placeholder:** Select Tax Country
- **Note:** Disabled field for display only, auto-populated from country selection
- **Behavior:** Automatically syncs with country_id selection

### 6. **Tax Country (Hidden)**
- **Field Name:** `tax_country_id`
- **Type:** Hidden Input
- **Required:** No
- **Value:** Auto-populated from country selection
- **Database Column:** `tax_country_id`

### 7. **State**
- **Field Name:** `state_id`
- **Type:** AJAX Dropdown (Select2)
- **Required:** Yes (*)
- **Validation:** Required field
- **Data Source:** Dynamically loaded based on selected country
- **AJAX Route:** `ajax-list` with type `state` and `country_id`
- **Placeholder:** Select State
- **Database Column:** `state_id`
- **Cascade Effect:** Triggers city dropdown population

### 8. **City**
- **Field Name:** `city_id`
- **Type:** AJAX Dropdown (Select2)
- **Required:** Yes (*)
- **Validation:** Required field
- **Data Source:** Dynamically loaded based on selected state
- **AJAX Route:** `ajax-list` with type `city` and `state_id`
- **Placeholder:** Select City
- **Database Column:** `city_id`

### 9. **Provider**
- **Field Name:** `provider_id`
- **Type:** AJAX Dropdown (Select2)
- **Required:** Yes (*)
- **Validation:** Required field
- **Visibility:** Only for admin and demo_admin roles
- **Data Source:** AJAX route `ajax-list` with type `provider`
- **Placeholder:** Select Provider
- **Database Column:** `provider_id`
- **Cascade Effect:** Triggers provider address dropdown population
- **JavaScript Event:** `onchange="selectprovider(this)"`

### 10. **Provider Address**
- **Field Name:** `provider_address_id[]`
- **Type:** Multi-Select AJAX Dropdown (Select2)
- **Required:** No
- **Validation:** Multiple selection allowed
- **Data Source:** Dynamically loaded based on selected provider
- **AJAX Route:** `ajax-list` with type `provider_address` and `provider_id`
- **Placeholder:** Select Provider Address
- **Database Column:** `provider_address_id` (stored as array)
- **Additional Feature:** Link to add new provider address

### 11. **Price Type**
- **Field Name:** `type`
- **Type:** Dropdown (Select2)
- **Required:** Yes (*)
- **Options:**
  - `fixed` → Fixed
  - `hourly` → Hourly
  - `Daily` → Daily
  - `free` → Free
- **Validation:** Required field
- **Database Column:** `type`
- **Cascade Effect:** 
  - Controls price field behavior
  - Controls discount field behavior
  - Controls duration field behavior
  - Controls advance payment visibility

### 12. **Price**
- **Field Name:** `price`
- **Type:** Text Input (Number)
- **Required:** Yes (*)
- **Validation:**
  - Min: 1
  - Step: any (decimals allowed)
  - Pattern: `^\d+(\.\d{1,2})?$` (up to 2 decimal places)
  - Auto-set to 0 and readonly when price type is "free"
- **Placeholder:** Price
- **Database Column:** `price`

### 13. **Minimum Booking**
- **Field Name:** `minimum_booking`
- **Type:** Text Input (Number)
- **Required:** No
- **Validation:** 
  - Step: any (decimals allowed)
- **Placeholder:** Minimum Booking
- **Database Column:** `minimum_booking`

### 14. **Discount**
- **Field Name:** `discount`
- **Type:** Number Input
- **Required:** No
- **Validation:**
  - Min: 0
  - Max: 99
  - Step: any (decimals allowed)
  - Custom validation: Must be between 0 and 99
  - Auto-set to 0 and readonly when price type is "free"
- **Placeholder:** Discount
- **Error Message:** "Discount value should be between 0 to 99"
- **Database Column:** `discount`

### 15. **Duration**
- **Field Name:** `duration`
- **Type:** Text Input (Number)
- **Required:** No
- **Validation:**
  - Min: 0.5
  - Step: 0.5
  - Dynamic behavior based on price type:
    - **Hourly:** Auto-set to 1, readonly, disabled
    - **Daily:** Auto-set to 8, readonly, disabled
    - **Fixed:** Editable, min 0.5 hours
    - **Free:** Editable, min 0.5 hours
- **Placeholder:** Duration
- **Help Text:** (hours)
- **Error Messages:**
  - "Duration must be at least 0.5 hours for fixed price type"
  - "Duration must be at least 0.5 hours"
- **Database Column:** `duration`

### 16. **Status**
- **Field Name:** `status`
- **Type:** Dropdown (Select2)
- **Required:** Yes (*)
- **Options:**
  - `1` → Active
  - `0` → Inactive
- **Validation:** Required field
- **Database Column:** `status`

### 17. **Visit Type**
- **Field Name:** `visit_type`
- **Type:** Dropdown (Select2)
- **Required:** Yes
- **Options:** Dynamically loaded from `$visittype` variable
- **Validation:** Required field
- **Database Column:** `visit_type`

### 18. **Remote Work Level**
- **Field Name:** `remote_work_level`
- **Type:** Dropdown (Select2)
- **Required:** Yes (*)
- **Options:**
  - `onsite` → Onsite (100%)
  - `25_remote` → 25% Remote
  - `50_remote` → 50% Remote
  - `75_remote` → 75% Remote
  - `100_remote` → 100% Remote
- **Default Value:** `onsite`
- **Validation:** Required field
- **Database Column:** `remote_work_level`

### 19. **Career Level**
- **Field Name:** `career_level`
- **Type:** Dropdown (Select2)
- **Required:** Yes (*)
- **Options:**
  - `intern` → Intern
  - `entry` → Entry
  - `junior` → Junior
  - `mid` → Mid-Level
  - `senior` → Senior
  - `lead` → Lead
  - `manager` → Manager
- **Default Value:** `entry`
- **Validation:** Required field
- **Database Column:** `career_level`

### 20. **Travel Required**
- **Field Name:** `travel_required`
- **Type:** Dropdown (Select2)
- **Required:** Yes (*)
- **Options:**
  - `0` → No
  - `1` → Yes
- **Default Value:** `0`
- **Validation:** Required field
- **Database Column:** `travel_required`

### 21. **Service Attachments (Images)**
- **Field Name:** `service_attachment[]`
- **Type:** Multiple File Upload
- **Required:** Yes (*) - Only for new services
- **Validation:**
  - Accept: image/* only
  - Multiple files allowed
  - Required when creating new service
  - Optional when editing existing service
- **UI Component:** Custom file input with preview
- **Label Text:** "Choose file Attachments"
- **Preview:** Shows existing images with gallery view (Magnific Popup)
- **Remove Option:** Available for existing images
- **Database Storage:** Media library (service_attachment collection)
- **JavaScript:** `onchange="previewSelectedImages(this)"`

### 22. **Description**
- **Field Name:** `description`
- **Type:** Textarea with Quill Rich Text Editor
- **Required:** No
- **Validation:** None
- **Rows:** 3
- **Placeholder:** Description
- **Editor Features:**
  - Bold, Italic, Underline
  - Ordered/Bullet lists
  - Link insertion
  - Clean formatting
- **Database Column:** `description`

### 23. **Cancellation Policy & Fees**
- **Field Name:** `cancellation_policy`
- **Type:** Textarea with Quill Rich Text Editor
- **Required:** No
- **Validation:** None
- **Rows:** 3
- **Placeholder:** Cancellation Policy
- **Editor Features:**
  - Bold, Italic, Underline
  - Ordered/Bullet lists
  - Link insertion
  - Clean formatting
- **Database Column:** `cancellation_policy`

### 24. **Enable Slot**
- **Field Name:** `is_slot`
- **Type:** Checkbox (Custom Switch)
- **Required:** No
- **Validation:** None
- **Visibility:** Only shown if `$slotservice == 1`
- **Label:** "Slot"
- **Database Column:** `is_slot`

### 25. **Set as Featured**
- **Field Name:** `is_featured`
- **Type:** Checkbox (Custom Switch)
- **Required:** No
- **Validation:** None
- **Visibility:** Only for provider, admin, and demo_admin user types
- **Label:** "Set as Featured"
- **Database Column:** `is_featured`

### 26. **Enable Advanced Payment**
- **Field Name:** `is_enable_advance_payment`
- **Type:** Checkbox (Custom Switch)
- **Required:** No
- **Validation:** None
- **Label:** "Enable Advanced Payment"
- **Database Column:** `is_enable_advance_payment`
- **Cascade Effect:** Controls visibility of advance payment amount field

### 27. **Advance Payment Amount**
- **Field Name:** `advance_payment_amount`
- **Type:** Number Input
- **Required:** Conditional (required if advance payment is enabled)
- **Validation:**
  - Min: 1
  - Max: 99
  - Only visible when advance payment is enabled
  - Only visible for price types: fixed, hourly, daily
- **Placeholder:** Amount
- **Help Text:** (%)
- **Database Column:** `advance_payment_amount`

---

## Hidden Fields

### 28. **ID**
- **Field Name:** `id`
- **Type:** Hidden Input
- **Value:** Service ID (for updates only)
- **Database Column:** `id`

---

## Form Behavior & JavaScript Functionality

### Cascading Dropdowns
1. **Category → Subcategory**
   - Selecting category populates subcategory dropdown

2. **Country → State → City**
   - Selecting country populates state dropdown and auto-sets tax country
   - Selecting state populates city dropdown

3. **Country → Tax Country**
   - Tax country automatically syncs with country selection
   - Uses both display (disabled) and hidden input fields

4. **Provider → Provider Address**
   - Selecting provider populates provider address dropdown
   - Shows/hides "Add Provider Address" link

### Dynamic Field Behavior

#### Price Type Effects
- **Free:**
  - Price field: Set to 0, readonly
  - Discount field: Set to 0, readonly
  - Duration field: Editable, min 0.5
  - Advance payment: Hidden

- **Hourly:**
  - Price field: Editable
  - Discount field: Editable
  - Duration field: Set to 1, readonly, disabled
  - Advance payment: Visible

- **Daily:**
  - Price field: Editable
  - Discount field: Editable
  - Duration field: Set to 8, readonly, disabled
  - Advance payment: Visible

- **Fixed:**
  - Price field: Editable
  - Discount field: Editable
  - Duration field: Editable, min 0.5
  - Advance payment: Visible

#### Advance Payment Visibility
- Only visible when:
  - Price type is fixed, hourly, or daily
  - AND advance payment checkbox is enabled

### Real-time Validation
1. **Discount:**
   - Validates range (0-99)
   - Shows error if out of range

2. **Duration:**
   - Validates minimum 0.5 hours for fixed and free types
   - Auto-set for hourly (1) and daily (8) types

### Image Preview & Gallery
- **Preview:** Selected images shown before upload
- **Gallery View:** Existing images displayed with Magnific Popup
- **Remove:** Individual images can be removed
- **Supported Formats:** All image types (jpg, jpeg, png, gif, etc.)

### Rich Text Editors (Quill)
Applied to:
- `description`
- `cancellation_policy`

**Features:**
- Bold, Italic, Underline formatting
- Ordered and bullet lists
- Link insertion
- Clean formatting tool
- Auto-sync with hidden textarea on form submit

---

## Form Submission

- **Method:** POST
- **Route:** `service.store`
- **Encoding:** `multipart/form-data` (for file upload)
- **Validation:** HTML5 + Server-side
- **Submit Button:** "Publish" (Primary button, float-end)

---

## Field Summary by Type

### Text Inputs (3)
- name, price, minimum_booking

### Number Inputs (3)
- discount, duration, advance_payment_amount

### Single Select Dropdowns (10)
- category_id, country_id, state_id, city_id, provider_id, type (price_type), status, visit_type, remote_work_level, career_level, travel_required

### Multi-Select Dropdown (1)
- provider_address_id[]

### Textareas with Rich Text Editor (2)
- description, cancellation_policy

### File Upload (1)
- service_attachment[] (multiple)

### Checkboxes (3)
- is_slot, is_featured, is_enable_advance_payment

### Hidden Fields (2)
- id, tax_country_id

### Disabled Display Field (1)
- tax_country_id_display

---

## Required Fields (10)
Fields marked with asterisk (*):
1. name
2. category_id
3. state_id
4. city_id
5. provider_id (admin only)
6. type (price_type)
7. price
8. status
9. remote_work_level
10. career_level
11. travel_required
12. service_attachment[] (new services only)

---

## Conditional Required Fields (1)
1. advance_payment_amount (required if advance payment is enabled)

---

## Optional Fields (15)
1. subcategory_id
2. country_id
3. tax_country_id
4. provider_address_id[]
5. minimum_booking
6. discount
7. duration
8. visit_type (required but dynamic)
9. description
10. cancellation_policy
11. is_slot
12. is_featured
13. is_enable_advance_payment
14. advance_payment_amount (conditional)
15. service_attachment[] (edit mode)

---

## Role-Based Field Visibility

### Admin & Demo Admin Only
- provider_id (Provider selection)

### Provider, Admin & Demo Admin
- is_featured (Set as Featured checkbox)

### Conditional Visibility
- is_slot (Only if `$slotservice == 1`)
- advance_payment_amount (Only if advance payment enabled and price type is fixed/hourly/daily)

---

## External Libraries & Dependencies

### CSS Libraries
- **Quill.js:** `https://cdn.quilljs.com/1.3.7/quill.snow.css`

### JavaScript Libraries
- **Quill.js:** `https://cdn.quilljs.com/1.3.7/quill.min.js`
- **Select2:** Enhanced dropdowns
- **Magnific Popup:** Image gallery viewer
- **jQuery:** DOM manipulation and AJAX

---

## AJAX Endpoints Used

1. **Categories:** `ajax-list?type=category`
2. **Subcategories:** `ajax-list?type=subcategory_list&category_id={id}`
3. **Countries:** `ajax-list?type=country`
4. **States:** `ajax-list?type=state&country_id={id}`
5. **Cities:** `ajax-list?type=city&state_id={id}`
6. **Providers:** `ajax-list?type=provider`
7. **Provider Addresses:** `ajax-list?type=provider_address&provider_id={id}`

---

## Custom JavaScript Functions

### Main Functions
1. **selectprovider(selectElement)** - Shows/hides add provider address link
2. **providerAddress(provider_id, provider_address_id)** - Loads provider addresses
3. **getSubCategory(category_id, subcategory_id)** - Loads subcategories
4. **priceformat(value)** - Handles price/discount fields based on price type
5. **handleDurationField(type)** - Manages duration field behavior
6. **addDurationValidation()** - Validates duration input
7. **getStates(country_id, selectedState)** - Loads states by country
8. **getCities(state_id, selectedCity)** - Loads cities by state
9. **setTaxCountry(id, name)** - Sets tax country fields
10. **previewSelectedImages(input)** - Previews selected images before upload
11. **mountQuill(textareaId)** - Initializes Quill editor on textarea

### Validation Functions
- Discount range validation (0-99)
- Duration minimum validation (0.5 hours)
- Price type-based field behavior

---

## Notes
- Form uses Laravel Collective HTML package for form generation
- Select2 library is used for enhanced dropdowns
- Quill.js is used for rich text editing (replacing TinyMCE)
- Form includes CSRF protection (Laravel default)
- Validation uses both client-side (HTML5 + JavaScript) and server-side validation
- AJAX is used for dynamic dropdown population
- Media files are managed through a media library system (Spatie Media Library)
- Tax country automatically syncs with country selection
- Provider address link dynamically updates based on provider selection
- Image gallery uses Magnific Popup for lightbox functionality
- Multiple images can be uploaded and previewed
- Duration field behavior changes based on price type selection
- Advance payment fields are conditionally visible based on price type and checkbox state
