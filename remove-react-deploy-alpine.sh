#!/bin/bash
# MTB Love - Complete React Removal & Alpine.js Migration

SOURCE="/home/user/mtblove"
TARGET="/home/httpd/vhosts/mooshagweg.ch/mtblove.com"

echo "🔥 REMOVING ALL REACT FILES AND DEPLOYING ALPINE.JS"
echo "=================================================="
echo ""

# Check if target exists
if [ ! -d "$TARGET" ]; then
    echo "❌ Target directory does not exist: $TARGET"
    exit 1
fi

# Backup alles
echo "📦 Creating full backup..."
BACKUP_DIR="$TARGET.react-backup.$(date +%Y%m%d_%H%M%S)"
cp -r "$TARGET" "$BACKUP_DIR" && echo "✅ Full backup: $BACKUP_DIR"
echo ""

# React-Dateien löschen
echo "🗑️  REMOVING REACT FILES..."
rm -rf "$TARGET/frontend/dist/" && echo "✅ Removed frontend/dist/ (React build)"
rm -rf "$TARGET/frontend/src/" && echo "✅ Removed frontend/src/ (React source)"
rm -rf "$TARGET/frontend/node_modules/" && echo "✅ Removed node_modules"
rm -f "$TARGET/frontend/package.json" && echo "✅ Removed package.json"
rm -f "$TARGET/frontend/package-lock.json" && echo "✅ Removed package-lock.json"
rm -f "$TARGET/frontend/vite.config.js" && echo "✅ Removed vite.config.js"
rm -f "$TARGET/frontend/tailwind.config.js" && echo "✅ Removed tailwind.config.js"
rm -f "$TARGET/frontend/postcss.config.js" && echo "✅ Removed postcss.config.js"
rm -f "$TARGET/frontend/index.html" && echo "✅ Removed old frontend/index.html"

echo ""
echo "📁 DEPLOYING ALPINE.JS FILES..."

# Alpine.js Dateien kopieren
cp "$SOURCE/index.html" "$TARGET/index.html" && echo "✅ index.html (Alpine.js) → ROOT"
cp "$SOURCE/wallpapers-standalone.html" "$TARGET/wallpapers-standalone.html" && echo "✅ wallpapers-standalone.html → ROOT"
cp "$SOURCE/.htaccess" "$TARGET/.htaccess" && echo "✅ .htaccess → ROOT"

# Backend kopieren
if [ -d "$SOURCE/backend" ]; then
    echo ""
    echo "📁 Deploying backend..."
    cp -r "$SOURCE/backend" "$TARGET/" && echo "✅ backend/ copied"
fi

# Berechtigungen
echo ""
echo "🔐 Setting permissions..."
chmod 755 "$TARGET" 2>/dev/null
chmod 644 "$TARGET/index.html" 2>/dev/null
chmod 644 "$TARGET/wallpapers-standalone.html" 2>/dev/null
chmod 644 "$TARGET/.htaccess" 2>/dev/null

echo ""
echo "✅ REACT COMPLETELY REMOVED!"
echo "✅ ALPINE.JS DEPLOYED!"
echo ""
echo "📋 Structure:"
echo "   $TARGET/"
echo "   ├── index.html (Alpine.js) ✅"
echo "   ├── wallpapers-standalone.html ✅"
echo "   ├── .htaccess ✅"
echo "   └── backend/ ✅"
echo ""
echo "   ❌ NO MORE:"
echo "   ├── frontend/dist/ (REMOVED)"
echo "   ├── frontend/src/ (REMOVED)"
echo "   └── node_modules/ (REMOVED)"
echo ""
echo "🔧 RESTART APACHE:"
echo "   sudo service apache2 restart"
echo ""
echo "🌐 TEST:"
echo "   https://mtblove.com/ → Alpine.js"
echo "   Console: cdn.tailwindcss.com ✅"
echo "   Console: cdn.jsdelivr.net/npm/alpinejs ✅"
echo "   Console: NO react-dom ✅"
