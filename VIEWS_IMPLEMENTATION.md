# View Tracking Implementation Guide

## Overview
View tracking has been implemented for both **Services** and **Job Requests**.

## Database Setup

### Required Migrations
Run these migrations if not already done:
```bash
php artisan migrate
```

The following migrations add `total_views` column:
- `2025_08_15_000001_add_total_views_to_services_table.php`
- `2025_08_15_000002_add_total_views_to_post_job_requests_table.php`

### Check if Column Exists
```sql
-- Check services table
DESCRIBE services;

-- Check post_job_requests table  
DESCRIBE post_job_requests;

-- Update existing NULL values to 0
UPDATE services SET total_views = 0 WHERE total_views IS NULL;
UPDATE post_job_requests SET total_views = 0 WHERE total_views IS NULL;
```

## How It Works

### For Services

#### Web Route
- **URL**: `/service-detail/{id}`
- **Controller**: `FrontendController::serviceDetail()`
- **Behavior**: Calls API `ServiceController::getServiceDetail()` which increments views

#### API Route
- **Endpoint**: `POST /api/service-detail`
- **Body**: `{"service_id": 123}`
- **Controller**: `API\ServiceController::getServiceDetail()` (line 208)
- **Behavior**: Automatically increments `total_views` when fetching service details

### For Job Requests

#### Web Routes
1. **Listing Page** (NO increment)
   - **URL**: `/job-datatable`
   - **Purpose**: Shows list of jobs with their current view counts
   - **Behavior**: Does NOT increment views (this is correct)

2. **Detail Page** (INCREMENTS views)
   - **URL**: `/job-details/{id}`
   - **Controller**: `FrontendController::showdetails()` (line 764)
   - **Behavior**: Increments `total_views` when viewing job details

#### API Route
- **Endpoint**: `POST /api/get-post-job-detail`
- **Body**: `{"post_request_id": 123}`
- **Controller**: `API\PostJobRequestController::getPostRequestDetail()` (line 311)
- **Behavior**: Automatically increments `total_views` when fetching job details

## For Flutter App

### Fetch Service Details (Auto-increments views)
```dart
POST /api/service-detail
Body: {"service_id": 123}

Response:
{
  "service_detail": {
    "id": 123,
    "name": "Service Name",
    "total_views": 42,
    ...
  }
}
```

### Fetch Job Request Details (Auto-increments views)
```dart
POST /api/get-post-job-detail
Body: {"post_request_id": 123}

Response:
{
  "post_request_detail": {
    "id": 123,
    "title": "Job Title",
    "total_views": 42,
    ...
  }
}
```

### Fetch Job Request List (Does NOT increment views)
```dart
GET /api/get-post-job

Response:
{
  "data": [
    {
      "id": 123,
      "title": "Job Title",
      "total_views": 42,  // Shows current count, doesn't increment
      ...
    }
  ]
}
```

## Troubleshooting

### Issue: Views showing NULL in database

**Solution 1: Run migrations**
```bash
php artisan migrate
```

**Solution 2: Update existing NULL values**
```sql
UPDATE services SET total_views = 0 WHERE total_views IS NULL;
UPDATE post_job_requests SET total_views = 0 WHERE total_views IS NULL;
```

### Issue: Views not incrementing

**Check 1: Verify column exists**
```sql
SHOW COLUMNS FROM services LIKE 'total_views';
SHOW COLUMNS FROM post_job_requests LIKE 'total_views';
```

**Check 2: Test increment manually**
```sql
-- Test service increment
UPDATE services SET total_views = total_views + 1 WHERE id = 1;

-- Test job request increment
UPDATE post_job_requests SET total_views = total_views + 1 WHERE id = 1;
```

**Check 3: Clear cache**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Issue: Service detail page not incrementing

The service detail page calls the API internally, so check:
1. API route is accessible: `POST /api/service-detail`
2. Authentication is working (if required)
3. Check Laravel logs: `storage/logs/laravel.log`

## Testing

### Test Service Views
1. Visit: `https://frobster.com/service-detail/1`
2. Check database: `SELECT total_views FROM services WHERE id = 1;`
3. Refresh page and check again - should increment by 1

### Test Job Request Views
1. Visit: `https://frobster.com/job-details/1`
2. Check database: `SELECT total_views FROM post_job_requests WHERE id = 1;`
3. Refresh page and check again - should increment by 1

### Test API (Flutter)
```bash
# Test service detail API
curl -X POST https://frobster.com/api/service-detail \
  -H "Content-Type: application/json" \
  -d '{"service_id": 1}'

# Test job request detail API
curl -X POST https://frobster.com/api/get-post-job-detail \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"post_request_id": 1}'
```

## Important Notes

1. **Listing pages do NOT increment views** - This is by design
   - `/job-datatable` - Shows view counts but doesn't increment
   - `/service-list` - Shows view counts but doesn't increment

2. **Detail pages DO increment views** - Every time they're loaded
   - `/service-detail/{id}` - Increments on each visit
   - `/job-details/{id}` - Increments on each visit

3. **API endpoints increment views** - When fetching details
   - `POST /api/service-detail` - Increments
   - `POST /api/get-post-job-detail` - Increments
   - `GET /api/get-post-job` - Does NOT increment (listing)

4. **View tracking is automatic** - No separate API call needed
   - Just fetch the detail, and views increment automatically
   - No need to call a separate "increment view" endpoint

## Files Modified

1. `app/Http/Controllers/FrontendController.php` (line 762-767)
   - Added view increment to `showdetails()` method

2. `app/Http/Controllers/API/PostJobRequestController.php` (line 311)
   - Already had view increment in `getPostRequestDetail()` method

3. `app/Http/Controllers/API/ServiceController.php` (line 208)
   - Already had view increment in `getServiceDetail()` method

4. `app/Http/Resources/API/PostJobRequestDetailResource.php` (line 96)
   - Removed duplicate `total_views` field

5. `app/Http/Resources/API/PostJobRequestResource.php` (line 64)
   - Already includes `total_views` in response
