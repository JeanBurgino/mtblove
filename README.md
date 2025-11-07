# mtblove.com - Meme Gallery & Instagram Analytics

Eine moderne, responsive Web-Anwendung zur Verwaltung und Anzeige von Memes mit Instagram Posts Analytics Dashboard, entwickelt mit PHP und MySQL.

**Live URL:** https://mtblove.com

## Features

### Öffentlicher Bereich (ohne Login)
- Responsive Meme-Gallery mit Grid-Layout
- Suchfunktion für Memes (Titel, Caption, Tags)
- Tag-basierte Filterung
- Vollbild-Ansicht mit Modal
- Pagination für große Gallerien
- Mobile-optimiert mit Bootstrap 5

### Interner Bereich - Admin Center (Login erforderlich)
- Instagram Posts Analytics Dashboard
- Meme-Upload mit Validierung
- Titel, Caption und Tags hinzufügen
- Meme-Verwaltung (Bearbeiten/Löschen)
- Benutzer-Verwaltung
- Statistiken und Analytics (in Planung)

### Technische Features
- **Clean URLs** mit mod_rewrite
- Sichere Session-basierte Authentifizierung
- Password-Hashing mit PHP `password_hash()`
- SQL-Injection-Schutz mit Prepared Statements
- XSS-Schutz durch `htmlspecialchars()`
- File-Upload-Validierung
- MIME-Type-Überprüfung
- Responsive Design (Mobile & Desktop)
- HTTPS-Ready

## URL-Struktur

Die Anwendung verwendet Clean URLs für eine bessere Benutzererfahrung:

- `https://mtblove.com/` - Startseite (Meme Gallery)
- `https://mtblove.com/login` - Login-Seite
- `https://mtblove.com/admin-center` - Admin Center (Instagram Analytics Dashboard)
- `https://mtblove.com/logout` - Logout
- `https://mtblove.com/memes/uploads/` - Meme-Uploads (statische Dateien)

## Systemvoraussetzungen

- PHP >= 7.4 (empfohlen: PHP 8.0+)
- MySQL >= 5.7 oder MariaDB >= 10.2
- Apache oder Nginx Webserver
- PDO MySQL Extension
- GD oder ImageMagick Extension (für zukünftige Thumbnail-Generierung)

## Installation

### 1. Repository klonen

```bash
git clone <repository-url>
cd mtblove
```

### 2. Datenbank einrichten

```bash
# MySQL/MariaDB einloggen
mysql -u root -p

# Schema importieren
mysql -u root -p < app/config/schema.sql
```

Alternativ per phpMyAdmin:
1. Datenbank `meme_gallery` erstellen
2. SQL-Datei `app/config/schema.sql` importieren

### 3. Konfiguration anpassen

Öffne `app/config/config.php` und passe folgende Werte an:

```php
// Datenbank-Konfiguration
define('DB_HOST', 'localhost');
define('DB_NAME', 'meme_gallery');
define('DB_USER', 'root');         // Anpassen!
define('DB_PASS', '');             // Anpassen!

// Base URL anpassen
// Für Produktion:
define('BASE_URL', 'https://mtblove.com');

// Für lokale Entwicklung:
// define('BASE_URL', 'http://localhost/mtblove');
```

**Für lokale Entwicklung:**
- Kopiere `app/config/config_local.example.php` zu `app/config/config_local.php`
- Passe die Werte für lokale Entwicklung an
- Ändere in `.htaccess` die `RewriteBase` von `/` zu `/mtblove` für localhost

### 4. Berechtigungen setzen

```bash
# Upload-Verzeichnis beschreibbar machen
chmod 755 app/public/memes/uploads
```

### 5. Apache-Konfiguration

Stelle sicher, dass `mod_rewrite` aktiviert ist:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Virtual Host Beispiel (`/etc/apache2/sites-available/meme-gallery.conf`):

```apache
<VirtualHost *:80>
    ServerName meme-gallery.local
    DocumentRoot /path/to/mtblove

    <Directory /path/to/mtblove>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/meme-gallery-error.log
    CustomLog ${APACHE_LOG_DIR}/meme-gallery-access.log combined
</VirtualHost>
```

### 6. Nginx-Konfiguration (Alternative)

```nginx
server {
    listen 80;
    server_name meme-gallery.local;
    root /path/to/mtblove;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Projektstruktur

```
mtblove/
├── app/
│   ├── config/
│   │   ├── config.php          # Hauptkonfiguration
│   │   └── schema.sql          # Datenbankschema
│   ├── includes/
│   │   └── functions.php       # Hilfsfunktionen
│   ├── public/
│   │   ├── memes/
│   │   │   └── uploads/        # Upload-Verzeichnis
│   │   ├── dashboard/
│   │   │   └── dashboard.php   # Admin-Dashboard
│   │   ├── index.php           # Öffentliche Gallery
│   │   ├── login.php           # Login-Seite
│   │   └── logout.php          # Logout-Script
│   └── templates/
│       ├── header.php          # Header-Template
│       └── footer.php          # Footer-Template
├── .htaccess                   # Apache-Konfiguration
└── README.md                   # Diese Datei
```

## Verwendung

### Erste Schritte

1. **Öffne die Anwendung im Browser:**
   ```
   http://localhost/mtblove/app/public/index.php
   ```

2. **Login mit Demo-Account:**
   - E-Mail: `admin@example.com`
   - Passwort: `admin123`

   **WICHTIG:** Ändere das Demo-Passwort in Produktion!

3. **Erstes Meme hochladen:**
   - Nach dem Login zum Dashboard navigieren
   - Bild auswählen (max. 5MB)
   - Titel und Caption hinzufügen
   - Tags vergeben (komma-getrennt)
   - Upload starten

4. **Memes anschauen:**
   - Zurück zur Startseite
   - Memes im Grid-Layout durchstöbern
   - Auf Meme klicken für Vollbild-Ansicht

### Benutzer erstellen

Neuen Admin-Benutzer per SQL erstellen:

```sql
-- Passwort wird automatisch gehasht (Beispiel: "meinpasswort123")
INSERT INTO users (username, email, password_hash, role) VALUES
('neueradmin', 'admin@example.com', '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXX', 'admin');
```

Passwort-Hash generieren mit PHP:

```php
echo password_hash('meinpasswort123', PASSWORD_DEFAULT);
```

## Troubleshooting

### 500 Internal Server Error

Wenn du einen 500 Error beim Aufruf der Seite erhältst, führe folgende Schritte durch:

#### 1. Setup-Check ausführen

Rufe im Browser auf:
```
http://mtblove.com/setup-check.php
```

Diese Seite zeigt alle Probleme und fehlende Voraussetzungen an.

#### 2. Häufige Ursachen

**Problem: Datenbankverbindung fehlgeschlagen**
```bash
# Prüfe ob MySQL läuft
sudo systemctl status mysql

# Importiere das Schema
mysql -u root -p < app/config/schema.sql

# Prüfe DB-Credentials in app/config/config.php
```

**Problem: mod_rewrite nicht aktiviert**
```bash
# Apache mod_rewrite aktivieren
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**Problem: .htaccess wird ignoriert**
```apache
# In deiner Apache VirtualHost-Config:
<Directory /var/www/mtblove>
    AllowOverride All
</Directory>
```

**Problem: Session-Cookie-Fehler**
- Die Anwendung erkennt automatisch HTTP/HTTPS
- Bei Reverse-Proxy (nginx) setze: `HTTP_X_FORWARDED_PROTO` Header

**Problem: Upload-Verzeichnis nicht beschreibbar**
```bash
chmod 755 app/public/memes/uploads
```

#### 3. Debug-Modus aktivieren

Falls setup-check.php auch einen 500 Error liefert:

1. Rufe `test.php` auf - wenn das funktioniert, ist PHP OK
2. Prüfe Apache Error-Log:
```bash
tail -f /var/log/apache2/error.log
```

3. Temporär in `.htaccess` auskommentieren:
```apache
# Kommentiere alle RewriteRules aus, um .htaccess-Probleme auszuschließen
```

#### 4. PHP-Fehler anzeigen

In `index.php` ist bereits aktiviert:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

Falls du weitere Details brauchst, prüfe:
- Apache Error-Log: `/var/log/apache2/error.log`
- PHP Error-Log: `/var/log/php/error.log`

#### 5. Nach erfolgreicher Einrichtung

Lösche diese Dateien aus Sicherheitsgründen:
- `setup-check.php`
- `test.php`

## Sicherheitshinweise

### Vor dem Produktiv-Einsatz

1. **Demo-Account entfernen oder Passwort ändern**
2. **`display_errors` in config.php auf `0` setzen**
3. **Starke Passwörter verwenden**
4. **HTTPS aktivieren** (Let's Encrypt) - Session-Cookies werden dann automatisch auf "secure" gesetzt
5. **HTTPS-Redirect aktivieren** (in .htaccess auskommentiert)
6. **Datenbank-Zugangsdaten sichern**
7. **Test- und Setup-Dateien löschen** (setup-check.php, test.php)
8. **Regelmäßige Backups erstellen**

### Dateirechte

```bash
# Sichere Berechtigungen setzen
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 755 app/public/memes/uploads
```

## Zukünftige Erweiterungen (TODO)

### Analytics
- [ ] Google Analytics Integration
- [ ] Eigenes Analytics-Dashboard
- [ ] View-Counter für Memes
- [ ] Trending-Memes-Algorithmus
- [ ] User-Tracking für Insights

### Features
- [ ] Registrierungs-Seite
- [ ] Passwort-Vergessen-Funktion
- [ ] Kommentar-System
- [ ] Like/Favorite-System
- [ ] Share-Buttons (Social Media)
- [ ] Meme-Upload via URL
- [ ] Bulk-Upload
- [ ] Thumbnail-Generierung
- [ ] Lazy Loading für Bilder
- [ ] Infinite Scroll
- [ ] Masonry-Layout-Option
- [ ] Dark Mode

### Caption-System
- [ ] Caption-Editor mit WYSIWYG
- [ ] Caption-Templates
- [ ] Text-Overlays auf Bildern
- [ ] Meme-Generator (Text auf Bild)
- [ ] Caption-Formatierung (Schriftart, Größe, Farbe)

### Admin-Features
- [ ] Erweiterte Benutzer-Verwaltung
- [ ] Rollen & Berechtigungen
- [ ] Moderation-Tools
- [ ] Report-System
- [ ] Batch-Operationen
- [ ] Export-Funktion

### Performance
- [ ] Image-Optimization
- [ ] CDN-Integration
- [ ] Caching (Redis/Memcached)
- [ ] Database-Query-Optimization
- [ ] Lazy-Loading

## Troubleshooting

### Fehler: "Datenbankverbindung fehlgeschlagen"
- Datenbank-Zugangsdaten in `config.php` überprüfen
- MySQL-Server läuft? (`sudo systemctl status mysql`)
- Datenbank existiert? (`SHOW DATABASES;`)

### Fehler: "Fehler beim Speichern der Datei"
- Upload-Verzeichnis existiert?
- Berechtigungen korrekt? (`chmod 755 uploads`)
- Speicherplatz verfügbar?

### Fehler: "Page not found" / 404
- `.htaccess` vorhanden?
- `mod_rewrite` aktiviert?
- `BASE_URL` in config.php korrekt?

### Bilder werden nicht angezeigt
- Pfad in Datenbank korrekt?
- Dateien im Upload-Verzeichnis vorhanden?
- Browser-Console auf Fehler prüfen (F12)

## Mitwirken

Contributions sind willkommen! Bitte:
1. Fork das Repository
2. Erstelle einen Feature-Branch (`git checkout -b feature/AmazingFeature`)
3. Committe deine Änderungen (`git commit -m 'Add some AmazingFeature'`)
4. Push zum Branch (`git push origin feature/AmazingFeature`)
5. Öffne einen Pull Request

## Lizenz

Dieses Projekt ist Open Source. Siehe LICENSE-Datei für Details.

## Support

Bei Fragen oder Problemen:
- Issue erstellen auf GitHub
- Dokumentation lesen
- Code-Kommentare beachten (TODO-Marker zeigen geplante Features)

## Technologie-Stack

- **Backend:** PHP 7.4+ mit PDO
- **Datenbank:** MySQL 5.7+ / MariaDB 10.2+
- **Frontend:** Bootstrap 5.3.2
- **Icons:** Bootstrap Icons 1.11.1
- **JavaScript:** Vanilla JS (kein Framework)
- **Webserver:** Apache 2.4+ oder Nginx

## Changelog

### Version 1.0.0 (Initial Release)
- Basis-Struktur implementiert
- Login/Logout-System
- Öffentliche Meme-Gallery
- Admin-Dashboard
- Upload-Funktion vorbereitet
- Responsive Design mit Bootstrap 5

---

**Entwickelt mit ❤️ und PHP**
