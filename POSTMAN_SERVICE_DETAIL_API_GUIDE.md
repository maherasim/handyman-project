# Postman Testing Guide: Service Detail API

## API Endpoint Information

**Route:** `Route::post('service-detail', [ API\ServiceController::class, 'getServiceDetail' ]);`

**Controller Method:** `ServiceController@getServiceDetail` (Line 186-278)

---

## Postman Configuration

### 1. **Request Type**
- **Method:** `POST`

### 2. **URL**
```
http://your-domain.com/api/service-detail
```

**Examples:**
- Local: `http://localhost/api/service-detail`
- Local with port: `http://localhost:8000/api/service-detail`
- Production: `https://yourdomain.com/api/service-detail`

---

## Request Parameters

### Required Parameters

#### Body (form-data or x-www-form-urlencoded)

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `service_id` | integer | **Yes** | The ID of the service to fetch details | `1`, `5`, `10` |

### Optional Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `customer_id` | integer | No | Customer ID to fetch customer-specific reviews | `12` |

---

## Postman Setup Steps

### Step 1: Create New Request
1. Open Postman
2. Click **"New"** → **"HTTP Request"**
3. Name it: `Get Service Detail`

### Step 2: Configure Request Method
- Select **POST** from the dropdown

### Step 3: Enter URL
```
http://localhost/api/service-detail
```
*(Replace with your actual domain/port)*

### Step 4: Set Headers (Optional)
Go to **Headers** tab and add:

| Key | Value |
|-----|-------|
| `Accept` | `application/json` |
| `Content-Type` | `application/json` or `application/x-www-form-urlencoded` |

### Step 5: Add Body Parameters

#### Option A: Using form-data
1. Go to **Body** tab
2. Select **form-data**
3. Add parameters:

| Key | Value |
|-----|-------|
| `service_id` | `1` |
| `customer_id` | `12` *(optional)* |

#### Option B: Using x-www-form-urlencoded
1. Go to **Body** tab
2. Select **x-www-form-urlencoded**
3. Add parameters:

| Key | Value |
|-----|-------|
| `service_id` | `1` |
| `customer_id` | `12` *(optional)* |

#### Option C: Using raw JSON
1. Go to **Body** tab
2. Select **raw**
3. Choose **JSON** from dropdown
4. Enter:
```json
{
    "service_id": 1,
    "customer_id": 12
}
```

### Step 6: Authentication (If Required)

If the API requires authentication (Sanctum token):

1. Go to **Headers** tab
2. Add:

| Key | Value |
|-----|-------|
| `Authorization` | `Bearer YOUR_ACCESS_TOKEN_HERE` |

**Note:** Based on the route file (line 66), this endpoint does NOT require authentication, but authenticated users get additional features (admin can see deleted services).

---

## Example Requests

### Example 1: Basic Request (Minimum Required)
**URL:** `http://localhost/api/service-detail`

**Method:** `POST`

**Body (form-data):**
```
service_id: 1
```

### Example 2: Request with Customer ID
**URL:** `http://localhost/api/service-detail`

**Method:** `POST`

**Body (form-data):**
```
service_id: 5
customer_id: 12
```

### Example 3: JSON Request
**URL:** `http://localhost/api/service-detail`

**Method:** `POST`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "service_id": 1,
    "customer_id": 12
}
```

### Example 4: Authenticated Request (Admin/User)
**URL:** `http://localhost/api/service-detail`

**Method:** `POST`

**Headers:**
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...
Accept: application/json
```

**Body (form-data):**
```
service_id: 1
```

---

## Expected Response

### Success Response (200 OK)

```json
{
    "status": true,
    "message": "Success",
    "data": {
        "service_detail": {
            "id": 1,
            "name": "Service Name",
            "description": "Service description...",
            "price": 100.00,
            "discount": 10,
            "type": "fixed",
            "duration": "2.5",
            "status": 1,
            "category_id": 5,
            "provider_id": 3,
            "total_views": 150,
            "is_featured": 1,
            "service_type": "service",
            "created_at": "2024-01-01T10:00:00.000000Z",
            "updated_at": "2024-01-10T15:30:00.000000Z",
            "category": {
                "id": 5,
                "name": "Category Name"
            },
            "providers": {
                "id": 3,
                "display_name": "Provider Name",
                "email": "provider@example.com"
            },
            "serviceRating": [],
            "serviceAddon": [],
            "serviceBooking": []
        },
        "provider": {
            "id": 3,
            "first_name": "John",
            "last_name": "Doe",
            "display_name": "John Doe",
            "email": "provider@example.com",
            "contact_number": "+1234567890"
        },
        "rating_data": [
            {
                "id": 1,
                "rating": 5,
                "review": "Excellent service!",
                "customer_id": 10,
                "service_id": 1
            }
        ],
        "customer_review": [],
        "coupon_data": [
            {
                "id": 1,
                "code": "DISCOUNT10",
                "discount": 10,
                "discount_type": "percentage"
            }
        ],
        "taxes": [
            {
                "id": 1,
                "title": "VAT",
                "type": "percentage",
                "value": 15
            }
        ],
        "related_service": [
            {
                "id": 2,
                "name": "Related Service",
                "price": 80.00
            }
        ],
        "service_faq": [
            {
                "id": 1,
                "title": "FAQ Question",
                "description": "FAQ Answer"
            }
        ],
        "serviceaddon": [
            {
                "id": 1,
                "name": "Addon Name",
                "price": 20.00
            }
        ]
    }
}
```

### Error Response: Service Not Found (406)

```json
{
    "status": false,
    "message": "Record not found",
    "data": null
}
```

### Error Response: Missing service_id (500)

```json
{
    "status": false,
    "message": "The service_id field is required.",
    "data": null
}
```

---

## Response Data Breakdown

The API returns comprehensive service information:

### 1. **service_detail**
- Complete service information
- Includes: name, description, price, discount, type, duration, status
- Related data: category, provider, ratings, addons, bookings

### 2. **provider**
- Provider/vendor information
- Includes: name, email, contact details, city, country

### 3. **rating_data**
- Top 5 service ratings
- Includes: rating score, review text, customer info

### 4. **customer_review**
- Customer-specific reviews (if `customer_id` provided)
- Shows reviews left by the specific customer

### 5. **coupon_data**
- Active coupons applicable to this service
- Includes: code, discount amount/percentage, expiry date

### 6. **taxes**
- Tax information based on service's tax country
- Includes: tax title, type (percentage/fixed), value

### 7. **related_service**
- Services in the same category
- Helps with cross-selling

### 8. **service_faq**
- Frequently asked questions about the service

### 9. **serviceaddon**
- Additional services/addons available
- Includes: addon name, price, status

---

## Special Features

### 1. **View Counter**
- Each API call increments the `total_views` counter for the service
- Tracked for analytics purposes

### 2. **Role-Based Access**
- **Admin:** Can view deleted/trashed services
- **Authenticated Users:** Can view all active services
- **Guest Users:** Can only view active services (status = 1)

### 3. **Subscription Check**
- If earning type is "subscription", only services from subscribed providers are shown in related services

---

## Testing Scenarios

### Test Case 1: Valid Service ID
**Input:** `service_id: 1`
**Expected:** Success response with full service details

### Test Case 2: Invalid Service ID
**Input:** `service_id: 99999`
**Expected:** 406 error with "Record not found" message

### Test Case 3: Missing Service ID
**Input:** *(no service_id)*
**Expected:** Error response

### Test Case 4: With Customer ID
**Input:** `service_id: 1, customer_id: 12`
**Expected:** Success response with customer-specific reviews

### Test Case 5: Inactive Service (Guest User)
**Input:** `service_id: 5` *(where status = 0)*
**Expected:** 406 error (service not found for guests)

### Test Case 6: Inactive Service (Admin User)
**Input:** `service_id: 5` *(where status = 0)* + Admin token
**Expected:** Success response (admins can view inactive services)

---

## Troubleshooting

### Issue 1: "Record not found" Error
**Causes:**
- Invalid service_id
- Service is inactive (status = 0) and you're not authenticated
- Service doesn't exist in database

**Solution:**
- Verify service_id exists in database
- Check service status
- Try with admin authentication

### Issue 2: Empty Response Data
**Causes:**
- Service exists but has no related data
- Database relationships not properly loaded

**Solution:**
- Check database for related records
- Verify foreign key relationships

### Issue 3: 500 Internal Server Error
**Causes:**
- Missing required parameter
- Database connection issue
- Server configuration problem

**Solution:**
- Check Laravel logs: `storage/logs/laravel.log`
- Verify database connection
- Ensure all migrations are run

### Issue 4: Authentication Issues
**Causes:**
- Invalid or expired token
- Token not properly formatted

**Solution:**
- Generate new token via login API
- Ensure token format: `Bearer YOUR_TOKEN`
- Check token in `Authorization` header

---

## Quick Start Checklist

- [ ] Open Postman
- [ ] Create new POST request
- [ ] Set URL: `http://localhost/api/service-detail`
- [ ] Add header: `Accept: application/json`
- [ ] Go to Body tab
- [ ] Select **form-data** or **raw JSON**
- [ ] Add parameter: `service_id` with value `1`
- [ ] Click **Send**
- [ ] Verify response status is 200
- [ ] Check response contains service details

---

## Additional Notes

1. **No Authentication Required:** This is a public endpoint (not behind `auth:sanctum` middleware)
2. **Service Type Filter:** Only returns services where `service_type = 'service'`
3. **View Tracking:** Automatically increments view counter on each request
4. **Related Services:** Limited to same category and active providers
5. **Coupon Validation:** Only returns active coupons (not expired)
6. **Tax Calculation:** Uses service's `tax_country_id` for tax lookup

---

## Sample Postman Collection (JSON)

You can import this into Postman:

```json
{
    "info": {
        "name": "Service Detail API",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": [
        {
            "name": "Get Service Detail",
            "request": {
                "method": "POST",
                "header": [
                    {
                        "key": "Accept",
                        "value": "application/json"
                    }
                ],
                "body": {
                    "mode": "formdata",
                    "formdata": [
                        {
                            "key": "service_id",
                            "value": "1",
                            "type": "text"
                        },
                        {
                            "key": "customer_id",
                            "value": "12",
                            "type": "text",
                            "disabled": true
                        }
                    ]
                },
                "url": {
                    "raw": "http://localhost/api/service-detail",
                    "protocol": "http",
                    "host": ["localhost"],
                    "path": ["api", "service-detail"]
                }
            }
        }
    ]
}
```

Save this as `service-detail-api.postman_collection.json` and import into Postman.
