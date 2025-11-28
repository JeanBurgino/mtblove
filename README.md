# MTB Love - Ride With Passion 🚵‍♂️❤️

Eine Full-Stack Webanwendung für MTB-Enthusiasten mit React Frontend, PHP Backend und MySQL Datenbank.

## 📁 Projektstruktur

```
mtblove/
├── frontend/                 # React Frontend
│   ├── src/
│   │   ├── components/      # React Komponenten
│   │   ├── pages/           # Seiten-Komponenten
│   │   ├── context/         # React Context (Auth)
│   │   ├── App.jsx          # Haupt-App Komponente
│   │   ├── main.jsx         # Entry Point
│   │   └── styles.css       # Globale Styles
│   ├── public/              # Statische Dateien
│   ├── package.json         # NPM Dependencies
│   ├── vite.config.js       # Vite Konfiguration
│   └── index.html           # HTML Template
│
├── backend/                 # PHP Backend
│   ├── api/                 # API Endpoints
│   │   ├── index.php        # API Router
│   │   ├── auth.php         # Authentifizierung
│   │   ├── wallpapers.php   # Wallpaper CRUD
│   │   ├── products.php     # Produkt CRUD
│   │   ├── excuses.php      # Ausreden Generator
│   │   └── stats.php        # Statistiken
│   ├── config.php           # Zentrale Konfiguration
│   └── database.sql         # Datenbank Schema
│
└── uploads/                 # Upload-Verzeichnis
    ├── wallpapers/          # Wallpaper Uploads
    └── products/            # Produkt Bilder
```

## 🎯 Features

### Öffentliche Bereiche
- **Home**: Hero Section mit MTB Ausreden-Generator
- **Wallpapers & Downloads**: Kostenlose und Premium Wallpapers
- **Shop**: Merch-Artikel (T-Shirts, Sticker, Poster, etc.)

### Admin-Bereich (Login erforderlich)
- **Dashboard**: Statistiken und Übersicht
- **Wallpaper Verwaltung**: Upload, Bearbeiten, Löschen
- **Produkt Verwaltung**: Artikel pflegen, Lagerbestand verwalten

## 🚀 Installation & Setup

### Voraussetzungen
- PHP 7.4 oder höher
- MySQL 5.7 oder höher
- Node.js 16+ und npm
- Apache oder Nginx Webserver

### 1. Datenbank einrichten

```bash
# MySQL einloggen
mysql -u root -p

# Datenbank und Schema importieren
mysql -u root -p < backend/database.sql
```

Die Datenbank-Zugangsdaten sind in `backend/config.php`:
```php
DB_HOST: localhost
DB_NAME: mtblove
DB_USER: mtblove_admin
DB_PASS: W5vSzoCB1UniJpGZfQU9
```

### 2. Backend konfigurieren

Die Konfiguration befindet sich in `backend/config.php`.

**Wichtig**: Ändere das Admin-Passwort nach der Installation!

Standard Admin-Zugangsdaten:
- Username: `admin`
- Passwort: `admin123`

### 3. Frontend installieren

```bash
cd frontend

# Dependencies installieren
npm install

# Development Server starten
npm run dev

# Für Produktion bauen
npm run build
```

Der Development Server läuft auf `http://localhost:3000`

### 4. Webserver konfigurieren

#### Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # API Requests an backend/ weiterleiten
    RewriteRule ^api/(.*)$ backend/api/index.php [L]

    # Frontend Routing
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ frontend/dist/index.html [L]
</IfModule>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name mtblove.local;
    root /path/to/mtblove;

    # Frontend
    location / {
        try_files $uri $uri/ /frontend/dist/index.html;
    }

    # Backend API
    location /backend {
        try_files $uri $uri/ /backend/api/index.php?$args;

        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php-fpm.sock;
            fastcgi_index index.php;
            include fastcgi_params;
        }
    }

    # Uploads
    location /uploads {
        alias /path/to/mtblove/uploads;
    }
}
```

## 🎨 Technologie-Stack

### Frontend
- **React 18** - UI Library
- **React Router** - Client-side Routing
- **Axios** - HTTP Client
- **Lucide React** - Icons
- **Vite** - Build Tool & Dev Server

### Backend
- **PHP 7.4+** - Server-side Language
- **MySQL** - Datenbank
- **PDO** - Datenbank Abstraction Layer

### Styling
- **Custom CSS** - Mit CSS Variables
- **Responsive Design** - Mobile-first Approach

## 📊 Datenbank Schema

### Tabellen
- `users` - Admin-Benutzer
- `wallpapers` - Wallpaper & Downloads
- `products` - Shop-Produkte
- `excuses` - MTB Ausreden
- `stats` - Dashboard Statistiken
- `sessions` - Session Management

## 🔒 Sicherheit

- Passwort-Hashing mit bcrypt
- Prepared Statements gegen SQL-Injection
- CORS-Header konfiguriert
- Session-basierte Authentifizierung
- File-Upload Validierung

**Wichtige Sicherheitshinweise**:
1. Ändere die Admin-Passwörter nach der Installation
2. Setze `CORS_ALLOWED_ORIGINS` in `config.php` auf deine Domain
3. Aktiviere HTTPS in Produktion
4. Setze `display_errors = 0` in PHP für Produktion

## 🛠️ Development

### Frontend entwickeln
```bash
cd frontend
npm run dev
```

### API testen
```bash
# Beispiel: Wallpapers abrufen
curl -X POST http://localhost/backend/api/index.php \
  -F "action=get_wallpapers"

# Login
curl -X POST http://localhost/backend/api/index.php \
  -F "action=login" \
  -F "user=admin" \
  -F "pass=admin123"
```

## 📝 API Endpoints

### Öffentlich
- `POST /api?action=get_random_excuse` - Zufällige Ausrede
- `POST /api?action=get_wallpapers` - Alle Wallpapers
- `POST /api?action=get_products` - Alle Produkte

### Authentifizierung erforderlich
- `POST /api?action=login` - Login
- `POST /api?action=logout` - Logout
- `POST /api?action=add_wallpaper` - Wallpaper hinzufügen
- `POST /api?action=update_wallpaper` - Wallpaper bearbeiten
- `POST /api?action=delete_wallpaper` - Wallpaper löschen
- `POST /api?action=add_product` - Produkt hinzufügen
- `POST /api?action=update_product` - Produkt bearbeiten
- `POST /api?action=delete_product` - Produkt löschen
- `POST /api?action=get_stats` - Admin Statistiken

## 🐛 Troubleshooting

### Frontend startet nicht
```bash
# Node modules neu installieren
rm -rf node_modules package-lock.json
npm install
```

### Datenbankverbindung schlägt fehl
- Überprüfe Credentials in `backend/config.php`
- Stelle sicher, dass MySQL läuft
- Prüfe ob der Benutzer `mtblove_admin` existiert

### Uploads funktionieren nicht
```bash
# Upload-Verzeichnis erstellen und Berechtigungen setzen
mkdir -p uploads/wallpapers uploads/products
chmod 755 uploads
chmod 755 uploads/wallpapers
chmod 755 uploads/products
```

## 📄 Lizenz

Dieses Projekt ist für persönliche und kommerzielle Nutzung frei verfügbar.

## 👨‍💻 Autor

Erstellt mit ❤️ für die MTB Community

---

**Ride With Passion! 🚵‍♂️**
