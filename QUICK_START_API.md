# Quick Start - API Zugriff

## Standard-Benutzer (bereits vorhanden)

Die Datenbank enthält bereits einen Standard-Admin-Benutzer:

```
Username: admin
Password: admin123
Role: admin
```

**⚠️ WICHTIG:** Ändere das Passwort in Produktion!

---

## Schnellstart: API-Zugriff in 3 Schritten

### 1. Login und Token erhalten

```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=login" \
  -d "username=admin" \
  -d "password=admin123"
```

**Response:**
```json
{
  "success": true,
  "token": "a1b2c3d4e5f6g7h8...",
  "user": {
    "id": 1,
    "username": "admin",
    "role": "admin"
  }
}
```

### 2. Token in Variable speichern

**Linux/Mac:**
```bash
TOKEN="a1b2c3d4e5f6g7h8..."
```

**Windows (PowerShell):**
```powershell
$TOKEN = "a1b2c3d4e5f6g7h8..."
```

### 3. API-Requests mit Token

**Social Stats aktualisieren:**
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=update_social_stats" \
  -d "instagram_followers=10000" \
  -d "tiktok_followers=8000" \
  -d "token=$TOKEN"
```

**Social Stats abrufen (ohne Token - public):**
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=get_social_stats"
```

---

## Neuen API-Benutzer erstellen

### Option 1: Mit Script (empfohlen)

```bash
php backend/create_user.php api_user SecurePassword123 admin
```

### Option 2: Via SQL

```sql
INSERT INTO users (username, password_hash, role, email, created_at)
VALUES (
    'api_user',
    -- Password: SecurePassword123
    '$2y$10$...',  -- Use PHP password_hash() for correct hash
    'admin',
    'api_user@mtblove.local',
    NOW()
);
```

### Option 3: Via Login-Page

1. Öffne `http://localhost/index.html`
2. Klicke auf "Login"
3. Login mit `admin` / `admin123`
4. Gehe zum Admin-Bereich
5. (User Management Feature muss noch implementiert werden)

---

## Häufige API-Requests

### Social Media Stats

**Abrufen (Public):**
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=get_social_stats"
```

**Aktualisieren (Admin):**
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=update_social_stats" \
  -d "instagram_followers=15000" \
  -d "tiktok_followers=9500" \
  -d "token=$TOKEN"
```

### Wallpapers

**Alle abrufen (Public):**
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=get_wallpapers"
```

**Like togglen:**
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=toggle_like" \
  -d "id=1" \
  -d "liked=true"
```

### Produkte

**Alle abrufen (Public):**
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=get_products"
```

---

## Testing mit Postman/Insomnia

### 1. Login Request

**Method:** POST
**URL:** `http://localhost/backend/api/index.php`
**Body (form-data):**
```
action: login
username: admin
password: admin123
```

### 2. Copy Token aus Response

```json
{
  "success": true,
  "token": "COPY_THIS_TOKEN",
  ...
}
```

### 3. Authenticated Request

**Method:** POST
**URL:** `http://localhost/backend/api/index.php`
**Body (form-data):**
```
action: update_social_stats
instagram_followers: 10000
tiktok_followers: 8000
token: PASTE_TOKEN_HERE
```

---

## Troubleshooting

### "Datenbankverbindung fehlgeschlagen"

Die MySQL-Datenbank läuft möglicherweise nicht. Prüfe:
```bash
# MySQL Status prüfen
systemctl status mysql

# MySQL starten
systemctl start mysql
```

### "Ungültige Anmeldedaten"

- Username/Passwort falsch → Nutze `admin` / `admin123`
- User existiert nicht in DB → Führe `backend/database.sql` aus

### "Nicht autorisiert"

- Token fehlt oder ungültig
- Token abgelaufen (1 Stunde Gültigkeit)
- Lösung: Neu einloggen und neuen Token verwenden

### CORS-Fehler im Browser

Wenn du von einer anderen Domain/Port zugreifst:
1. Öffne `backend/config.php`
2. Ändere `CORS_ALLOWED_ORIGINS` von `*` zu deiner Domain
3. Oder füge deine Domain zur Allowed List hinzu

---

## Vollständige API-Dokumentation

Siehe `API_DOCUMENTATION.md` für:
- Alle verfügbaren Endpoints
- Detaillierte Request/Response Beispiele
- Fehlerbehandlung
- Sicherheitshinweise
- Erweiterte Authentifizierung

## Social Stats Dokumentation

Siehe `SOCIAL_STATS_README.md` für:
- Social Media Follower Counter Feature
- Installation und Setup
- Datenbank Schema
- Frontend-Integration
