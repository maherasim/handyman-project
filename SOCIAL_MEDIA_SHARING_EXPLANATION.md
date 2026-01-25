# Social Media Sharing Functionality - Data Posted

## Overview
The application has social media sharing functionality for **Featured Services** and **Top Rated Services** sections on the homepage. When users click on social media icons, specific data is automatically posted/shared.

---

## 📍 **Location on Homepage**
- **Featured Services Section** - Has functional social media sharing
- **Top Rated Services Section** - Currently has placeholder links (not functional)

---

## 🔗 **Supported Platforms**

### 1. **Facebook** 
**How it works:**
- Opens Facebook share dialog in a popup window
- Uses Facebook's native share API: `https://www.facebook.com/sharer/sharer.php`

**Data Posted:**
```
- URL: Service detail page URL (e.g., /service-detail/123)
- Quote/Description: 
  • Service Name (limited to 80 characters)
  • Price (formatted, e.g., "$100.00")
  • Service Type (Fixed/Hourly)
  • Location (City, Country)
```

**Example Share Text:**
```
"Plumbing Service • $100.00 • Fixed • New York, United States"
```

**Code Location:**
- `resources/views/landing-page/index.blade.php` (lines 883-888)
- JavaScript handler: `resources/views/landing-page/index.blade.php` (lines 1800-1803)

---

### 2. **Twitter/X**
**How it works:**
- Opens Twitter compose tweet dialog in a popup window
- Uses Twitter's intent API: `https://twitter.com/intent/tweet`

**Data Posted:**
```
- URL: Service detail page URL
- Text/Tweet Content:
  • Service Name (limited to 80 characters)
  • Price (formatted)
  • Service Type
  • Location (City, Country)
```

**Example Tweet:**
```
"Plumbing Service • $100.00 • Fixed • New York, United States"
[Link: /service-detail/123]
```

**Code Location:**
- `resources/views/landing-page/index.blade.php` (lines 897-902)
- JavaScript handler: `resources/views/landing-page/index.blade.php` (lines 1804-1807)

---

### 3. **LinkedIn**
**How it works:**
- Opens LinkedIn share dialog in a popup window
- Uses LinkedIn's share API: `https://www.linkedin.com/sharing/share-offsite/`

**Data Posted:**
```
- URL: Service detail page URL only
- LinkedIn automatically fetches:
  • Page title (from meta tags)
  • Page description (from meta tags)
  • Preview image (from Open Graph tags)
```

**Note:** LinkedIn uses Open Graph meta tags from the service detail page for rich preview.

**Code Location:**
- `resources/views/landing-page/index.blade.php` (lines 904-909)
- JavaScript handler: `resources/views/landing-page/index.blade.php` (lines 1808-1810)

---

### 4. **Instagram**
**How it works:**
- Uses native Web Share API if available (mobile devices)
- Falls back to opening Instagram homepage if Web Share API not available
- **Note:** Instagram doesn't support direct URL sharing via web API

**Data Posted:**
```
- Text/Quote:
  • Service Name (limited to 80 characters)
  • Price (formatted)
  • Service Type
  • Location (City, Country)
  • Service detail URL
- Image URL: Service image attachment
```

**Example Share Text:**
```
"Plumbing Service • $100.00 • Fixed • New York, United States — https://example.com/service-detail/123"
```

**Code Location:**
- `resources/views/landing-page/index.blade.php` (lines 890-895)
- JavaScript handler: `resources/views/landing-page/index.blade.php` (lines 1811-1822)

---

## 📊 **Data Structure for Services**

### **Featured Services Section:**
```php
data-share-url: "{{ route('service.detail', $data->id) }}?v={{ timestamp }}"
data-quote: "{{ Service Name }} • {{ Price }} • {{ Type }} • {{ City }}, {{ Country }}"
data-text: "{{ Service Name }} • {{ Price }} • {{ Type }} • {{ City }}, {{ Country }}"
data-image-url: "{{ Service Image URL }}"
```

### **Fields Included:**
1. **Service Name** - Limited to 80 characters using `Str::limit()`
2. **Price** - Formatted using `getPriceFormat()` function (e.g., "$100.00")
3. **Service Type** - Capitalized (Fixed/Hourly)
4. **Location** - City name and Country name
5. **Service URL** - Direct link to service detail page with version timestamp

---

## 🔧 **Technical Implementation**

### **JavaScript Handler:**
Located in: `resources/views/landing-page/index.blade.php` (lines 1789-1827)

```javascript
window.__shareClickHandler = function(e, el) {
    // Prevents default link behavior
    // Opens platform-specific share dialog
    // Handles: Facebook, Twitter, LinkedIn, Instagram
}
```

### **Share URL Format:**
```
/service-detail/{service_id}?v={timestamp}
```
- The `?v={timestamp}` parameter ensures fresh content (cache busting)

---

## 📝 **Example Share Content**

### **For a Service:**
```
Service Name: "Professional Plumbing Service"
Price: "$150.00"
Type: "Fixed"
Location: "New York, United States"
URL: "https://frobster.com/service-detail/123?v=1234567890"
```

### **Facebook Share:**
```
Quote: "Professional Plumbing Service • $150.00 • Fixed • New York, United States"
URL: https://frobster.com/service-detail/123?v=1234567890
```

### **Twitter Share:**
```
Tweet: "Professional Plumbing Service • $150.00 • Fixed • New York, United States"
URL: https://frobster.com/service-detail/123?v=1234567890
```

### **LinkedIn Share:**
```
URL: https://frobster.com/service-detail/123?v=1234567890
(LinkedIn fetches meta tags automatically)
```

### **Instagram Share:**
```
Text: "Professional Plumbing Service • $150.00 • Fixed • New York, United States — https://frobster.com/service-detail/123"
Image: Service attachment image
```

---

## ⚠️ **Important Notes**

1. **Instagram Limitations:**
   - Instagram doesn't support direct web-based sharing
   - Uses native Web Share API on mobile devices
   - Desktop users are redirected to Instagram homepage

2. **Top Rated Services:**
   - Currently has placeholder social media icons (href="#")
   - Not functional - needs to be updated with same implementation as Featured Services

3. **Automatic Posting:**
   - The sharing opens a popup where users can edit before posting
   - It's NOT fully automatic - users must confirm/share manually
   - For automatic posting (admin only), see `SocialShareController.php` (Facebook API integration)

4. **Data Source:**
   - All data comes from the Service model
   - Service name, price, type, location are fetched from database
   - Service image is fetched from media attachments

---

## 🎯 **Files Involved**

1. **View Files:**
   - `resources/views/landing-page/index.blade.php` - Main homepage with sharing buttons
   - `resources/views/service/datatable-card.blade.php` - Service card with sharing

2. **Controller:**
   - `app/Http/Controllers/SocialShareController.php` - Facebook automatic posting (admin only)

3. **JavaScript:**
   - Inline script in `index.blade.php` (lines 1788-1827)

---

## 🔄 **How to Make Top Rated Services Functional**

Currently, Top Rated Services section (around line 1155-1166) has placeholder links. To make them functional, replace:

```html
<a href="#"><img src="...facebook..."></a>
```

With:

```html
<span role="button" tabindex="0" class="social-link share-link"
      data-platform="facebook"
      data-share-url="{{ route('service.detail', $data->id) }}?v={{ optional($data->updated_at)->timestamp ?? time() }}"
      data-quote="{{ Str::limit($data->name, 80) }} • {{ getPriceFormat($data->price) }} • {{ ucfirst($data->type) }} • {{ $data->city->name ?? 'City' }}, {{ $data->country->name ?? 'Country' }}"
      onclick="return window.__shareClickHandler(event, this);">
    <img src="..." style="width: 30px; border-radius: 8px;" alt="Facebook">
</span>
```

Apply same pattern for Twitter, LinkedIn, and Instagram.
