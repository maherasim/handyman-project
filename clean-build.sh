#!/bin/bash

# Comprehensive Build Clean Script
# This script cleans all build artifacts, caches, and versioned files
# Run this when builds are broken: bash clean-build.sh

echo "🧹 Starting comprehensive clean..."

# 1. Clean npm build artifacts
echo "📦 Cleaning npm build artifacts..."
rm -rf public/js/* public/css/* 2>/dev/null
rm -f public/mix-manifest.json 2>/dev/null
rm -f build-report.json 2>/dev/null
rm -rf .webpack 2>/dev/null
rm -rf public/hot 2>/dev/null

# 2. Clean webpack/node caches
echo "🗑️  Cleaning webpack and node caches..."
rm -rf node_modules/.cache 2>/dev/null
rm -rf .webpack 2>/dev/null

# 3. Remove all versioned files (files with query strings/hashes)
echo "🔍 Removing versioned files..."
find public/js -type f \( -name "*.js" -o -name "*.map" \) 2>/dev/null | xargs rm -f 2>/dev/null
find public/css -type f \( -name "*.css" -o -name "*.map" \) 2>/dev/null | xargs rm -f 2>/dev/null

# 4. Clean Laravel caches
echo "⚡ Cleaning Laravel caches..."
php artisan cache:clear 2>/dev/null
php artisan config:clear 2>/dev/null
php artisan view:clear 2>/dev/null
php artisan route:clear 2>/dev/null

# 5. Clean compiled views
echo "📄 Cleaning compiled views..."
rm -rf storage/framework/views/*.php 2>/dev/null

# 6. Recreate directories
echo "📁 Recreating directories..."
mkdir -p public/js public/css 2>/dev/null

echo "✅ Clean completed!"
echo ""
echo "Now run: npm run production"
echo "Or for development: npm run dev"
