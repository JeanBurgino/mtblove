# Plesk Setup-Anleitung für mtblove.com

## Entdeckte Server-Struktur

Dein Server verwendet **Plesk** mit folgender Struktur:
```
/home/httpd/vhosts/mooshagweg.ch/mtblove.com/
```

---

## 1. Überprüfe die Dateistruktur

Stelle sicher, dass **alle** Dateien hochgeladen wurden:

```bash
# Per SSH verbinden
ssh dein-benutzer@server

# Zum Verzeichnis navigieren
cd /home/httpd/vhosts/mooshagweg.ch/mtblove.com/

# Struktur prüfen
ls -la

# Sollte zeigen:
# - index.php
# - .htaccess
# - app/
#   - config/
#     - config.php
#     - schema.sql
#   - includes/
#     - functions.php
#   - public/
#   - templates/
```

### Wichtig: Vollständige Verzeichnisstruktur hochladen!

Wenn Dateien fehlen, lade das **komplette** Repository hoch, nicht nur einzelne Dateien.

---

## 2. Datenbank einrichten (über Plesk)

### Datenbank erstellen:

1. Öffne **Plesk Panel**
2. Gehe zu **Datenbanken**
3. Klicke **Datenbank hinzufügen**
4. Erstelle:
   - **Datenbankname:** `meme_gallery` (oder wie gewünscht)
   - **Benutzername:** Wähle einen Benutzer
   - **Passwort:** Sicheres Passwort generieren

### Schema importieren:

**Variante A: Über phpMyAdmin (einfacher)**
1. Öffne **phpMyAdmin** in Plesk
2. Wähle die Datenbank `meme_gallery`
3. Gehe zu **Importieren**
4. Wähle Datei: `app/config/schema.sql`
5. Klicke **OK**

**Variante B: Per SSH**
```bash
mysql -u dein_db_benutzer -p dein_db_name < app/config/schema.sql
```

---

## 3. Konfiguration anpassen

Bearbeite: `/home/httpd/vhosts/mooshagweg.ch/mtblove.com/app/config/config.php`

```php
// Datenbank-Konfiguration
define('DB_HOST', 'localhost');
define('DB_NAME', 'meme_gallery');      // Dein DB-Name aus Plesk
define('DB_USER', 'dein_db_benutzer');  // Dein DB-User aus Plesk
define('DB_PASS', 'dein_db_passwort');  // Dein DB-Passwort

// Base URL (sollte schon korrekt sein)
define('BASE_URL', 'https://mtblove.com');
```

**In Plesk-Panel bearbeiten:**
1. Gehe zu **Dateien**
2. Navigiere zu `app/config/config.php`
3. Klicke **Bearbeiten**
4. Trage die DB-Credentials ein
5. **Speichern**

---

## 4. Berechtigungen setzen

```bash
cd /home/httpd/vhosts/mooshagweg.ch/mtblove.com/

# Upload-Verzeichnis beschreibbar machen
chmod 755 app/public/memes/uploads

# Dateien lesbar für Webserver
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Besitzer setzen (je nach Plesk-Konfiguration)
# Normalerweise ist das der FTP-Benutzer oder www-data
chown -R dein-benutzer:psacln .
# oder
chown -R dein-benutzer:psaserv .
```

**Wenn du keinen SSH-Zugriff hast:**
- In Plesk über **Dateien** → Rechtsklick auf Verzeichnis → **Berechtigungen ändern**
- `app/public/memes/uploads` auf **755** setzen

---

## 5. SSL-Zertifikat (Let's Encrypt) in Plesk

### Aktiviere HTTPS:

1. Öffne **Plesk Panel**
2. Gehe zu **SSL/TLS-Zertifikate**
3. Wähle **Let's Encrypt**
4. Aktiviere:
   - ✓ `mtblove.com`
   - ✓ `www.mtblove.com` (optional)
5. Klicke **Installieren**

### Nach SSL-Installation:

1. Gehe zu **Apache & nginx Einstellungen**
2. Aktiviere **"Permanente SEO-sichere 301-Umleitung von HTTP zu HTTPS"**

**Alternativ:** Entkommentiere in `.htaccess` Zeilen 10-11:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
```

---

## 6. PHP-Einstellungen (Plesk)

### Upload-Limits erhöhen:

1. Gehe zu **PHP-Einstellungen** in Plesk
2. Setze:
   - `upload_max_filesize` = **5M**
   - `post_max_size` = **5M**
   - `max_execution_time` = **300**
   - `memory_limit` = **128M** (oder höher)

3. **Speichern**

---

## 7. Teste die Installation

### Test 1: PHP funktioniert?
```
https://mtblove.com/test.php
```
Sollte PHP-Info zeigen.

### Test 2: Setup-Check
```
https://mtblove.com/setup-check.php
```
Zeigt alle Systemprüfungen.

### Test 3: HTTPS-Test
```
https://mtblove.com/https-test.php
```
Zeigt SSL- und Server-Konfiguration.

### Test 4: Startseite
```
https://mtblove.com/
```
Sollte die Meme-Gallery zeigen.

---

## 8. Häufige Probleme & Lösungen

### Problem: "Failed to open stream: No such file or directory"

**Ursache:** Nicht alle Dateien wurden hochgeladen.

**Lösung:**
```bash
# Prüfe ob config.php existiert
ls -la /home/httpd/vhosts/mooshagweg.ch/mtblove.com/app/config/config.php

# Falls nicht vorhanden: Komplett hochladen!
```

### Problem: "Site Temporarily Closed"

**Ursache:** Apache nutzt falsches DocumentRoot oder .htaccess-Problem.

**Lösung:**
1. In Plesk: **Hosting-Einstellungen**
2. **Document Root** sollte sein: `/`
3. Nicht: `/httpdocs` oder `/public_html`

### Problem: Datenbankverbindung fehlgeschlagen

**Lösung:**
1. Prüfe DB-Credentials in `app/config/config.php`
2. Prüfe ob DB existiert in Plesk → **Datenbanken**
3. Importiere Schema erneut

### Problem: 500 Internal Server Error

**Ursache:** .htaccess-Fehler oder PHP-Fehler

**Lösung:**
1. Prüfe Plesk-Logs: **Logs** → **Error Log**
2. Temporär `.htaccess` umbenennen: `.htaccess.backup`
3. Teste direkt: `https://mtblove.com/app/public/index.php`

### Problem: Upload funktioniert nicht

**Lösung:**
```bash
# Verzeichnis beschreibbar machen
chmod 755 app/public/memes/uploads

# In Plesk: PHP-Einstellungen → upload_max_filesize erhöhen
```

---

## 9. Plesk-spezifische Hinweise

### PHP-Version wählen:

1. Gehe zu **PHP-Einstellungen**
2. Wähle **PHP 7.4** oder höher (empfohlen: PHP 8.0+)
3. **Übernehmen**

### Apache Restart:

Nach `.htaccess`-Änderungen nicht nötig, Apache lädt automatisch neu.

Falls doch:
1. **Tools & Einstellungen** → **Dienste**
2. **Apache** → **Neu starten**

### Logs anzeigen:

1. **Logs** → **Error Log** für PHP/Apache-Fehler
2. **Access Log** für Zugriffe

---

## 10. Nach erfolgreicher Einrichtung

### Sicherheit:

1. **Lösche Test-Dateien:**
   - `test.php`
   - `setup-check.php`
   - `https-test.php`

2. **Deaktiviere Error-Display:**
   In `index.php` Zeilen 9-10 auskommentieren:
   ```php
   // ini_set('display_errors', 1);
   // error_reporting(E_ALL);
   ```

3. **Ändere Demo-Passwort:**
   - Login: admin@example.com / admin123
   - Ändere in Datenbank oder erstelle neuen User

4. **Backups einrichten:**
   - Plesk: **Backup Manager** → **Backup planen**

---

## Support

Falls Probleme bestehen, sammle diese Informationen:

1. **Plesk-Version:** Tools & Einstellungen → Updates
2. **PHP-Version:** PHP-Einstellungen
3. **Error-Log:** Logs → Error Log (letzte 50 Zeilen)
4. **Dateistruktur:**
   ```bash
   cd /home/httpd/vhosts/mooshagweg.ch/mtblove.com/
   ls -laR
   ```

---

## Schnellstart

```bash
# 1. Alle Dateien hochladen
# Via FTP oder Plesk File Manager

# 2. Datenbank erstellen und Schema importieren
# Via Plesk → Datenbanken → phpMyAdmin

# 3. config.php anpassen
# Via Plesk → Dateien → app/config/config.php

# 4. Berechtigungen setzen
chmod 755 app/public/memes/uploads

# 5. Testen
# https://mtblove.com/setup-check.php
```

Fertig! 🎉
