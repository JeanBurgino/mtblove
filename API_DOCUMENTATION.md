# MTB Love - REST API Documentation

## Authentication

Die MTB Love API verwendet Session-basierte Authentifizierung mit Tokens.

### 1. Benutzer erstellen

#### Option A: Mit dem Create User Script (empfohlen)

```bash
php backend/create_user.php <username> <password> [role]
```

Beispiel:
```bash
php backend/create_user.php api_user MySecurePassword123 admin
```

**Parameter:**
- `username` - Benutzername (erforderlich)
- `password` - Passwort, mindestens 8 Zeichen (erforderlich)
- `role` - Rolle: `admin` oder `editor` (optional, Standard: `editor`)

#### Option B: Direkt in der Datenbank

```sql
INSERT INTO users (username, password_hash, role, email, created_at)
VALUES (
    'api_user',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    'api_user@mtblove.local',
    NOW()
);
```

**Hinweis:** Der obige Hash ist für das Passwort `admin123`.

### 2. Login (Token erhalten)

**Endpoint:** `POST /backend/api/index.php`

**Parameter:**
```
action=login
username=<dein_username>
password=<dein_password>
```

**cURL Beispiel:**
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=login" \
  -d "username=api_user" \
  -d "password=MySecurePassword123"
```

**Erfolgreiche Response:**
```json
{
  "success": true,
  "token": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6",
  "user": {
    "id": 1,
    "username": "api_user",
    "role": "admin"
  }
}
```

**Fehler Response:**
```json
{
  "error": "Ungültige Anmeldedaten"
}
```

### 3. Token verwenden

Verwende den erhaltenen Token bei allen geschützten API-Requests:

```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=update_social_stats" \
  -d "instagram_followers=10000" \
  -d "token=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6"
```

---

## API Endpoints

### Public Endpoints (Keine Authentifizierung erforderlich)

#### Get Social Stats
Liefert Instagram und TikTok Follower-Zahlen.

**Request:**
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=get_social_stats"
```

**Response:**
```json
{
  "instagram_followers": 5420,
  "tiktok_followers": 3280
}
```

---

#### Get Wallpapers
Liefert alle aktiven Wallpapers.

**Request:**
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=get_wallpapers"
```

**Response:**
```json
[
  {
    "id": 1,
    "title": "Skull Trail",
    "description": "Dunkle MTB Kunst mit Totenkopf-Motiv",
    "style": "Dark Art",
    "file_path": "/uploads/wallpapers/skull-trail.jpg",
    "type": "free",
    "downloads": 0,
    "likes": 0
  }
]
```

---

#### Get Products
Liefert alle verfügbaren Shop-Produkte.

**Request:**
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=get_products"
```

**Response:**
```json
[
  {
    "id": 1,
    "name": "T-Shirt \"MTB Love\"",
    "description": "Hochwertiges Baumwoll-Shirt",
    "price": "29.99",
    "image_path": "/uploads/products/tshirt-mtb-love.jpg"
  }
]
```

---

### Protected Endpoints (Authentifizierung erforderlich)

Alle folgenden Endpoints benötigen einen gültigen Token-Parameter.

#### Update Social Stats
Aktualisiert Instagram und TikTok Follower-Zahlen.

**Request:**
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=update_social_stats" \
  -d "instagram_followers=10000" \
  -d "tiktok_followers=8000" \
  -d "token=YOUR_TOKEN"
```

**Response:**
```json
{
  "success": true,
  "message": "Social Media Stats aktualisiert",
  "instagram_followers": 10000,
  "tiktok_followers": 8000
}
```

---

#### Add Wallpaper
Fügt ein neues Wallpaper hinzu.

**Request:**
```bash
curl -X POST http://localhost/backend/api/index.php \
  -F "action=add_wallpaper" \
  -F "title=My New Wallpaper" \
  -F "description=Cool MTB artwork" \
  -F "style=Abstract" \
  -F "type=free" \
  -F "file=@/path/to/image.jpg" \
  -F "token=YOUR_TOKEN"
```

**Response:**
```json
{
  "success": true,
  "message": "Wallpaper erfolgreich erstellt",
  "wallpaper_id": 5
}
```

---

#### Delete Wallpaper
Löscht ein Wallpaper.

**Request:**
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=delete_wallpaper" \
  -d "id=5" \
  -d "token=YOUR_TOKEN"
```

**Response:**
```json
{
  "success": true,
  "message": "Wallpaper erfolgreich gelöscht"
}
```

---

#### Get Stats (Admin Dashboard)
Liefert vollständige Statistiken für das Admin-Dashboard.

**Request:**
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=get_stats" \
  -d "token=YOUR_TOKEN"
```

**Response:**
```json
{
  "followers": "111200",
  "downloads": "4800",
  "revenue": "842,50 €",
  "visitors": "25340",
  "total_wallpapers": 4,
  "total_products": 4,
  "total_sales": 0
}
```

---

## Fehlerbehandlung

### HTTP Status Codes

- `200` - Erfolg
- `400` - Bad Request (ungültige Parameter)
- `401` - Unauthorized (nicht authentifiziert)
- `500` - Server Error

### Fehler Response Format

```json
{
  "error": "Fehlermeldung hier"
}
```

### Häufige Fehler

**401 Unauthorized:**
```json
{
  "error": "Nicht autorisiert"
}
```
→ Token fehlt oder ist ungültig. Neu einloggen.

**400 Bad Request:**
```json
{
  "error": "Ungültige Aktion"
}
```
→ Action-Parameter fehlt oder ist ungültig.

---

## Komplettes Beispiel: Social Stats aktualisieren

### 1. Benutzer erstellen
```bash
php backend/create_user.php social_admin MyPassword123 admin
```

### 2. Login
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=login" \
  -d "username=social_admin" \
  -d "password=MyPassword123"
```

**Response:**
```json
{
  "success": true,
  "token": "abc123...",
  "user": {
    "id": 2,
    "username": "social_admin",
    "role": "admin"
  }
}
```

### 3. Token speichern
```bash
TOKEN="abc123..."
```

### 4. Social Stats aktualisieren
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=update_social_stats" \
  -d "instagram_followers=15420" \
  -d "tiktok_followers=9280" \
  -d "token=$TOKEN"
```

**Response:**
```json
{
  "success": true,
  "message": "Social Media Stats aktualisiert",
  "instagram_followers": 15420,
  "tiktok_followers": 9280
}
```

### 5. Verifizieren (Public Endpoint)
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=get_social_stats"
```

**Response:**
```json
{
  "instagram_followers": 15420,
  "tiktok_followers": 9280
}
```

---

## Session Management

### Token Lebensdauer
- Tokens sind standardmäßig 1 Stunde gültig (definiert in `SESSION_LIFETIME`)
- Nach Ablauf muss ein neuer Login durchgeführt werden

### Logout
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=logout" \
  -d "token=YOUR_TOKEN"
```

**Response:**
```json
{
  "success": true,
  "message": "Erfolgreich abgemeldet"
}
```

### Check Authentication
Prüfe, ob ein Token noch gültig ist:

```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=check_auth" \
  -d "token=YOUR_TOKEN"
```

**Response (gültig):**
```json
{
  "authenticated": true,
  "user": {
    "id": 1,
    "username": "api_user",
    "role": "admin"
  }
}
```

**Response (ungültig):**
```json
{
  "authenticated": false
}
```

---

## Sicherheitshinweise

1. **HTTPS verwenden**: In Produktion sollte die API nur über HTTPS erreichbar sein
2. **Sichere Passwörter**: Mindestens 8 Zeichen, Groß-/Kleinbuchstaben, Zahlen, Sonderzeichen
3. **Token-Sicherheit**: Tokens niemals in Logs oder URLs speichern
4. **Rate Limiting**: Bei hohem Traffic Rate Limiting implementieren
5. **CORS-Konfiguration**: In Produktion spezifische Domains in `CORS_ALLOWED_ORIGINS` definieren

---

## Troubleshooting

### "Datenbankverbindung fehlgeschlagen"
- Prüfe MySQL Server Status: `systemctl status mysql`
- Prüfe Credentials in `backend/config.php`
- Teste DB-Verbindung: `mysql -u mtblove_admin -p`

### "Nicht autorisiert"
- Token abgelaufen → Neu einloggen
- Token ungültig → Prüfe Token-String
- Falscher Benutzer → Prüfe Benutzername/Passwort

### "Ungültige Aktion"
- Prüfe `action` Parameter
- Siehe Liste der verfügbaren Endpoints oben
- Case-sensitive: `get_social_stats`, nicht `Get_Social_Stats`
