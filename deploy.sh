#!/bin/bash
# Deploy Script für MTB Love

SOURCE="/home/user/mtblove"
TARGET="/home/httpd/vhosts/mooshagweg.ch/mtblove.com"

echo "🚀 Deploying MTB Love..."

# Backup erstellen
echo "📦 Creating backup..."
cp -r "$TARGET" "$TARGET.backup.$(date +%Y%m%d_%H%M%S)" 2>/dev/null || echo "No backup needed (first deploy)"

# Dateien kopieren
echo "📁 Copying files..."
cp "$SOURCE/index.html" "$TARGET/" && echo "✅ index.html copied"
cp "$SOURCE/wallpapers-standalone.html" "$TARGET/" && echo "✅ wallpapers-standalone.html copied"
cp "$SOURCE/.htaccess" "$TARGET/" && echo "✅ .htaccess copied"
cp "$SOURCE/test-api.html" "$TARGET/" && echo "✅ test-api.html copied"

# Backend kopieren
echo "📁 Copying backend..."
cp -r "$SOURCE/backend" "$TARGET/" && echo "✅ backend copied"

# Berechtigungen setzen
echo "🔐 Setting permissions..."
chmod 644 "$TARGET/index.html"
chmod 644 "$TARGET/wallpapers-standalone.html"
chmod 644 "$TARGET/.htaccess"

echo ""
echo "✅ Deployment complete!"
echo "🌐 Test: https://mtblove.com/"
