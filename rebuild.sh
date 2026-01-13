#!/bin/bash

# Complete Rebuild Script
# This script does a full clean and rebuild
# Run this when you need a fresh build: bash rebuild.sh

echo "🔄 Starting complete rebuild..."

# Run the clean script
bash clean-build.sh

# Rebuild assets
echo ""
echo "🔨 Building assets..."
npm run production

echo ""
echo "✅ Rebuild completed!"
