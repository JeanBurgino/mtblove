#!/bin/bash
# Deploy Script für MTB Love

SOURCE="/home/user/mtblove"
TARGET="/home/httpd/vhosts/mooshagweg.ch/mtblove.com"

echo "🚀 Deploying MTB Love (Alpine.js)..."
echo ""

# Check if target exists
if [ ! -d "$TARGET" ]; then
    echo "❌ Target directory does not exist: $TARGET"
    echo "Please check the path or create it first."
    exit 1
fi

# Backup erstellen
echo "📦 Creating backup..."
BACKUP_DIR="$TARGET.backup.$(date +%Y%m%d_%H%M%S)"
cp -r "$TARGET" "$BACKUP_DIR" 2>/dev/null && echo "✅ Backup created: $BACKUP_DIR" || echo "⚠️  No backup created"

echo ""
echo "📁 Copying files to live server..."

# Hauptdateien kopieren
cp "$SOURCE/index.html" "$TARGET/index.html" && echo "✅ index.html (Alpine.js) → $TARGET/"
cp "$SOURCE/wallpapers-standalone.html" "$TARGET/wallpapers-standalone.html" && echo "✅ wallpapers-standalone.html → $TARGET/"
cp "$SOURCE/.htaccess" "$TARGET/.htaccess" && echo "✅ .htaccess → $TARGET/"
cp "$SOURCE/test-api.html" "$TARGET/test-api.html" && echo "✅ test-api.html → $TARGET/"

# Backend kopieren (falls geändert)
if [ -d "$SOURCE/backend" ]; then
    echo ""
    echo "📁 Copying backend..."
    cp -r "$SOURCE/backend" "$TARGET/" && echo "✅ backend/ → $TARGET/"
fi

# Berechtigungen setzen
echo ""
echo "🔐 Setting permissions..."
chmod 644 "$TARGET/index.html" 2>/dev/null
chmod 644 "$TARGET/wallpapers-standalone.html" 2>/dev/null
chmod 644 "$TARGET/.htaccess" 2>/dev/null

echo ""
echo "✅ Deployment complete!"
echo ""
echo "📋 Next steps:"
echo "1. Restart Apache: sudo service apache2 restart"
echo "2. Test in Incognito: https://mtblove.com/"
echo "3. Click 'Gallery' - should redirect to wallpapers-standalone.html"
echo ""
echo "🔍 Verify Alpine.js is running (not React):"
echo "   Open Console (F12) and check for:"
echo "   ✅ cdn.tailwindcss.com"
echo "   ✅ cdn.jsdelivr.net/npm/alpinejs"
echo "   ❌ NO react-dom.production.min.js"

