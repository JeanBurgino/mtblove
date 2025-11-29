# MTB Love - Deployment Anleitung für Admin

## Problem
Die Website zeigt "500 Internal Server Error" weil sie noch React-Dateien sucht, die nicht mehr existieren.

## Lösung
React komplett entfernen und durch Alpine.js ersetzen.

---

## SCHRITT 1: Backup erstellen

```bash
cd /home/httpd/vhosts/mooshagweg.ch/mtblove.com
cp -r . ../mtblove.com.backup.$(date +%Y%m%d_%H%M%S)
```

---

## SCHRITT 2: React-Dateien löschen

```bash
cd /home/httpd/vhosts/mooshagweg.ch/mtblove.com

# React Build löschen
rm -rf frontend/dist/
rm -rf frontend/src/
rm -rf frontend/node_modules/

# React Config löschen
rm -f frontend/package.json
rm -f frontend/package-lock.json
rm -f frontend/vite.config.js
rm -f frontend/tailwind.config.js
rm -f frontend/postcss.config.js
rm -f frontend/index.html
```

---

## SCHRITT 3: Alpine.js Dateien deployen

```bash
# Von Development nach Live kopieren
cp /home/user/mtblove/index.html /home/httpd/vhosts/mooshagweg.ch/mtblove.com/index.html
cp /home/user/mtblove/wallpapers-standalone.html /home/httpd/vhosts/mooshagweg.ch/mtblove.com/wallpapers-standalone.html
cp /home/user/mtblove/.htaccess /home/httpd/vhosts/mooshagweg.ch/mtblove.com/.htaccess

# Backend kopieren (falls geändert)
cp -r /home/user/mtblove/backend /home/httpd/vhosts/mooshagweg.ch/mtblove.com/
```

---

## SCHRITT 4: Berechtigungen setzen

```bash
cd /home/httpd/vhosts/mooshagweg.ch/mtblove.com

chmod 644 index.html
chmod 644 wallpapers-standalone.html
chmod 644 .htaccess
chmod -R 755 backend/
```

---

## SCHRITT 5: Apache neu starten

```bash
service apache2 restart
# oder
systemctl restart apache2
# oder
apachectl restart
```

---

## SCHRITT 6: Testen

**Im Browser (Inkognito):**
```
https://mtblove.com/
```

**Erwartetes Ergebnis:**
- ✅ Alpine.js Home-Seite lädt
- ✅ Keine 500 Errors
- ✅ Console (F12) zeigt: `cdn.tailwindcss.com`, `cdn.jsdelivr.net/npm/alpinejs`
- ❌ KEINE `react-dom.production.min.js` Fehler

**Test 2 - Gallery:**
```
https://mtblove.com/
→ Klick auf "Gallery"
→ Sollte zu wallpapers-standalone.html weiterleiten
```

---

## Finale Struktur

Nach dem Deployment sollte es so aussehen:

```
/home/httpd/vhosts/mooshagweg.ch/mtblove.com/
├── index.html                    ← Alpine.js (NEU)
├── wallpapers-standalone.html    ← Gallery (NEU)
├── .htaccess                     ← Routing (AKTUALISIERT)
├── backend/                      ← PHP API
│   ├── api/
│   │   ├── index.php
│   │   ├── wallpapers.php
│   │   └── ...
│   └── config.php
└── uploads/                      ← User uploads

GELÖSCHT:
├── frontend/dist/                ❌
├── frontend/src/                 ❌
└── frontend/node_modules/        ❌
```

---

## Wichtig

- **Kein npm mehr nötig!**
- **Kein Build-Prozess!**
- **Alles läuft über CDN** (Tailwind + Alpine.js)
- **Einfache Änderungen**: index.html editieren → neu laden → fertig!

---

## Bei Problemen

**500 Error nach Deployment:**
```bash
# Apache Error Log prüfen
tail -n 50 /var/log/apache2/error.log

# .htaccess Syntax prüfen
apachectl configtest
```

**Redirect funktioniert nicht:**
```bash
# mod_rewrite aktivieren
a2enmod rewrite
service apache2 restart
```

**Permissions Error:**
```bash
# Owner check
ls -la /home/httpd/vhosts/mooshagweg.ch/mtblove.com/

# Falls nötig, Owner anpassen
chown -R www-data:www-data /home/httpd/vhosts/mooshagweg.ch/mtblove.com/
```

---

## Kontakt

Bei Fragen oder Problemen wenden Sie sich an den Entwickler.

**Wichtig**: Nach dem Deployment sollte die Seite OHNE npm funktionieren!
