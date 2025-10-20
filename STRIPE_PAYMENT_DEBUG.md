# Stripe Payment Debug Guide

## Issues Fixed

### 1. URL Encoding Issue
**Problem**: The plan_type parameter in the success URL was not properly URL encoded, which could cause issues with special characters in plan names.

**Fix**: Added `urlencode()` around `$request->plan_type` in the success URL:
```php
'success_url' => $baseURL . '/subscription/stripe/save/' . $user_id .
    '?plan_type=' . urlencode($request->plan_type) . '&session_id={CHECKOUT_SESSION_ID}',
```

### 2. APP_URL Validation
**Problem**: No validation for APP_URL environment variable.

**Fix**: Added validation to ensure APP_URL is configured:
```php
if (empty($baseURL)) {
    return response()->json(['status' => false, 'message' => 'APP_URL not configured'], 500);
}
```

### 3. URL Format Consistency
**Problem**: Potential trailing slash issues in base URL.

**Fix**: Added URL normalization:
```php
$baseURL = rtrim($baseURL, '/');
```

### 4. Enhanced Logging
**Problem**: Limited debugging information when Stripe payment fails.

**Fix**: Added comprehensive logging:
- Stripe secret key validation
- Currency code determination
- URL generation
- Unit amount calculation
- Session creation details

### 5. Currency Code Default
**Problem**: Default currency was set to 'EURO' which might not be valid.

**Fix**: Changed default currency to 'USD':
```php
$currencyCode = $country ? $country->currency_code : 'USD';
```

## How to Test

1. **Check Environment Variables**:
   - Ensure `APP_URL` is properly set in your `.env` file
   - Verify Stripe keys are configured in payment gateway settings

2. **Test the Payment Flow**:
   - Go to provider detail page
   - Click "Upgrade to [Plan Name]"
   - Select "Stripe" as payment method
   - Check browser console for any JavaScript errors
   - Check Laravel logs for detailed debugging information

3. **Check Logs**:
   - Look for log entries starting with "Creating Stripe session for user"
   - Check for "Stripe URLs - Success" and "Cancel" entries
   - Look for "Unit amount calculated" entries

## Common Issues to Check

1. **APP_URL Configuration**:
   ```bash
   # In .env file
   APP_URL=http://your-domain.com
   ```

2. **Stripe Configuration**:
   - Check if Stripe keys are properly configured in admin panel
   - Verify Stripe secret key is not empty

3. **Route Access**:
   - Ensure user is authenticated
   - Check if routes are accessible

4. **Currency Settings**:
   - Verify default currency is set in site settings
   - Check if currency code is valid for Stripe

## Debugging Steps

If payment still fails:

1. Check browser developer tools console for JavaScript errors
2. Check Laravel logs (`storage/logs/laravel.log`) for detailed error messages
3. Verify all environment variables are properly set
4. Test with a simple plan name (no special characters)
5. Check if Stripe webhook endpoints are properly configured

## Files Modified

- `app/Http/Controllers/ProviderController.php` - Enhanced `createSubscriptionStripePayment` method
