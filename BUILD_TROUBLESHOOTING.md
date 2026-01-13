# Build Troubleshooting Guide

## Problem: Design/Build Broken After Changes

Sometimes after making changes, the build appears broken even after cleaning and rebuilding. This happens because of **cached files and versioning issues**.

## Root Causes

1. **Laravel Mix Versioning**: Creates versioned files (e.g., `backend-bundle.min.js?id=abc123`) and `mix-manifest.json`
2. **Old Versioned Files**: Previous versioned files may still exist even after cleaning
3. **Laravel Cache**: Cached views/configs referencing old asset paths
4. **Webpack Cache**: `.webpack` directory caching old builds
5. **OPcache**: PHP OPcache may cache compiled views
6. **Browser Cache**: Client-side caching (less common on server)

## Solutions

### Quick Fix (Recommended)

Use the comprehensive clean script:

```bash
bash clean-build.sh
npm run production
```

Or use the rebuild script (does both):

```bash
bash rebuild.sh
```

### Manual Steps

If scripts don't work, run these commands manually:

```bash
# 1. Clean npm build artifacts
rm -rf public/js/* public/css/*
rm -f public/mix-manifest.json
rm -rf .webpack public/hot

# 2. Clean webpack/node caches
rm -rf node_modules/.cache

# 3. Clean Laravel caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 4. Clean compiled views
rm -rf storage/framework/views/*.php

# 5. Rebuild
npm run production
```

### Using NPM Scripts

```bash
# Basic clean
npm run clean

# Clean everything (including Laravel caches)
npm run clean:all

# Complete rebuild (clean + build)
npm run rebuild

# Production build with clean
npm run prod
```

### If Still Broken

If the issue persists after cleaning:

1. **Clear OPcache** (if you have access):
   ```bash
   # Via PHP CLI
   php -r "opcache_reset();"
   
   # Or restart PHP-FPM
   sudo service php-fpm restart
   # or
   sudo systemctl restart php8.1-fpm
   ```

2. **Check file permissions**:
   ```bash
   chmod -R 755 public/js public/css
   chown -R www-data:www-data public/js public/css
   ```

3. **Verify mix-manifest.json**:
   ```bash
   cat public/mix-manifest.json
   ```
   Should contain current file mappings.

4. **Nuclear option** (last resort):
   ```bash
   # Remove node_modules and reinstall
   rm -rf node_modules package-lock.json
   npm install
   npm run production
   ```

## Prevention

1. **Always use `npm run prod`** instead of just `npm run production` (it cleans first)
2. **Use `npm run rebuild`** when builds are broken
3. **Clear Laravel cache** after major changes: `php artisan cache:clear`
4. **Version control**: Don't commit `mix-manifest.json` or build files (already in .gitignore)

## Files to Check

- `public/mix-manifest.json` - Should match current build
- `public/js/*.js` - Should be current files
- `public/css/*.css` - Should be current files
- `storage/framework/views/*.php` - Compiled views (clear if issues)

## Why Git Clone Works

When you clone fresh from git:
- No cached files exist
- No old versioned files
- Clean state = fresh build works

The scripts above replicate this clean state without needing to delete the project.
