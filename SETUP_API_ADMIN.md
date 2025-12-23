# API Admin User Setup

Diese Anleitung beschreibt, wie der `api_admin` User für die Shop Item Upload API eingerichtet wird.

## Übersicht

Der `api_admin` User ist ein spezieller Benutzer mit Admin-Rechten, der für automatisierte API-Zugriffe verwendet werden kann. Er hat volle Berechtigung zum Erstellen, Bearbeiten und Löschen von Shop Items (Designs).

## Benutzer-Informationen

- **Username**: `api_admin`
- **Password**: `ApiAdmin123` (bitte in Produktion ändern!)
- **Email**: `api_admin@mtblove.com`
- **Role**: `admin`

## Setup-Methoden

### Methode 1: PHP Setup-Skript (Empfohlen)

Das einfachste Verfahren ist die Verwendung des bereitgestellten PHP-Skripts:

```bash
cd /home/user/mtblove/backend
php setup_api_admin.php
```

**Erfolgreiche Ausgabe:**
```
✓ Successfully created user 'api_admin'
  - Username: api_admin
  - Password: ApiAdmin123
  - Role: admin
  - Email: api_admin@mtblove.com

You can now use these credentials to authenticate API requests.
```

**Falls User bereits existiert:**
```
✓ User 'api_admin' already exists:
  - ID: 2
  - Username: api_admin
  - Role: admin

No changes needed.
```

### Methode 2: SQL-Datei ausführen

Alternativ können Sie die SQL-Datei direkt ausführen:

```bash
mysql -u mtblove_admin -p mtblove < backend/create_user.sql
```

Oder innerhalb der MySQL-Console:

```sql
USE mtblove;
source backend/create_user.sql;
```

## Verwendung

### 1. Login und Token erhalten

Nachdem der User erstellt wurde, können Sie sich wie folgt einloggen:

```bash
curl -X POST 'http://your-domain.com/backend/api/index.php' \
  -d "action=login" \
  -d "username=api_admin" \
  -d "password=ApiAdmin123"
```

**Antwort:**
```json
{
  "success": true,
  "token": "a1b2c3d4e5f6g7h8i9j0...",
  "user": {
    "id": 2,
    "username": "api_admin",
    "role": "admin",
    "email": "api_admin@mtblove.com"
  }
}
```

### 2. Token für API-Aufrufe verwenden

Verwenden Sie das erhaltene Token für alle geschützten API-Endpunkte:

```bash
curl -X POST 'http://your-domain.com/backend/api/index.php' \
  -F "action=add_design" \
  -F "token=YOUR_TOKEN_HERE" \
  -F "title=My Design" \
  -F "description=A cool design"
```

## Berechtigungen

Der `api_admin` User hat folgende Berechtigungen:

### Erlaubte Aktionen

- ✅ Designs erstellen (`add_design`)
- ✅ Designs bearbeiten (`update_design`)
- ✅ Designs löschen (`delete_design`)
- ✅ Designs anzeigen (`get_designs`, `get_design`)
- ✅ Produkte verwalten (`add_product`, `update_product`, `delete_product`)
- ✅ Wallpapers verwalten (`add_wallpaper`, `update_wallpaper`, `delete_wallpaper`)
- ✅ Bulk-Import durchführen (`bulk_import_designs`)
- ✅ Alle öffentlichen Endpunkte nutzen

### Admin vs. Editor

Die Datenbank unterstützt zwei Rollen:

| Rolle | Beschreibung | Berechtigung |
|-------|--------------|--------------|
| `admin` | Vollzugriff | Alle Operationen erlaubt |
| `editor` | Eingeschränkt | Kann angepasst werden in `auth.php` |

Der `api_admin` User hat die Rolle `admin` und damit vollen Zugriff.

## Sicherheitshinweise

### ⚠️ Wichtige Sicherheitsmaßnahmen

1. **Passwort ändern in Produktion**
   ```bash
   php backend/generate_password_hash.php YourSecurePassword123!
   ```
   Dann das generierte Hash in der Datenbank aktualisieren:
   ```sql
   UPDATE users
   SET password_hash = '$2y$12$...'
   WHERE username = 'api_admin';
   ```

2. **HTTPS verwenden**
   - Niemals Passwörter oder Tokens über unverschlüsselte HTTP-Verbindungen senden
   - In Produktion immer HTTPS verwenden

3. **Token-Sicherheit**
   - Tokens nie in Git committen
   - Tokens nie in Logs ausgeben
   - Tokens sicher auf Client-Seite speichern (z.B. in Environment Variables)
   - Tokens haben eine Lebensdauer von 1 Stunde

4. **IP-Whitelisting** (optional)
   - Erwägen Sie, API-Zugriffe auf bestimmte IP-Adressen zu beschränken
   - Kann in `auth.php` implementiert werden

5. **Rate Limiting** (empfohlen für Produktion)
   - Begrenzen Sie die Anzahl der Requests pro Zeiteinheit
   - Verhindert Brute-Force-Angriffe

## Passwort ändern

### Neues Passwort generieren

```bash
php backend/generate_password_hash.php YourNewPassword123
```

**Ausgabe:**
```
Password Hash:
$2y$12$abcdefghijklmnopqrstuvwxyz...

SQL Statement:
UPDATE users SET password_hash = '$2y$12$abcdefghijklmnopqrstuvwxyz...' WHERE username = 'YOUR_USERNAME';
```

### In Datenbank aktualisieren

```sql
UPDATE users
SET password_hash = '$2y$12$abcdefghijklmnopqrstuvwxyz...'
WHERE username = 'api_admin';
```

## Fehlerbehebung

### Problem: User kann nicht erstellt werden

**Fehler**: `Duplicate entry 'api_admin' for key 'username'`

**Lösung**: User existiert bereits. Verwenden Sie stattdessen das Update:

```sql
UPDATE users
SET role = 'admin',
    email = 'api_admin@mtblove.com'
WHERE username = 'api_admin';
```

### Problem: Login schlägt fehl

**Fehler**: `{"success": false, "error": "Ungültige Anmeldedaten"}`

**Mögliche Ursachen**:
1. Falsches Passwort
2. User existiert nicht in der Datenbank
3. User ist deaktiviert

**Prüfung**:
```sql
SELECT id, username, email, role, created_at, last_login
FROM users
WHERE username = 'api_admin';
```

### Problem: Token wird nicht akzeptiert

**Fehler**: `{"success": false, "error": "Nicht autorisiert"}`

**Mögliche Ursachen**:
1. Token ist abgelaufen (Lebensdauer: 1 Stunde)
2. Token ist ungültig
3. Session wurde gelöscht

**Lösung**: Neues Login durchführen und neues Token abrufen

### Problem: Keine Berechtigung für Aktion

**Fehler**: `{"success": false, "error": "Nicht autorisiert"}`

**Prüfung der Rolle**:
```sql
SELECT username, role FROM users WHERE username = 'api_admin';
```

**Lösung**: Sicherstellen, dass role = 'admin'

## Testing

### Test-Skript ausführen

Ein komplettes Test-Skript ist verfügbar:

```bash
php backend/test_shop_item_upload.php
```

**Das Skript testet**:
1. ✅ Login mit api_admin
2. ✅ Token-Generierung
3. ✅ Abruf von Markets und Product Types
4. ✅ Erstellen eines Test-Designs
5. ✅ Abruf des erstellten Designs

**Erwartete Ausgabe**:
```
========================================
STEP 1: Login and Get Authentication Token
========================================

✓ Successfully logged in as: api_admin
ℹ Token: a1b2c3d4e5f6g7h8i9j0...
ℹ User Role: admin

========================================
STEP 2: Get Available Markets and Product Types
========================================

✓ Retrieved 3 markets
ℹ   - [DE] Germany (EUR)
ℹ   - [US] United States (USD)
ℹ   - [UK] United Kingdom (GBP)
✓ Retrieved 5 product types
...
```

### Manueller Login-Test

```bash
curl -X POST 'http://localhost/backend/api/index.php' \
  -d "action=login" \
  -d "username=api_admin" \
  -d "password=ApiAdmin123" \
  -v
```

## Weitere Informationen

- **API Dokumentation**: `SHOP_ITEM_UPLOAD_API.md`
- **Vollständige API Docs**: `API_DOCUMENTATION.md`
- **Quick Start Guide**: `QUICK_START_API.md`
- **Datenbank Schema**: `backend/database.sql`

## Support

Bei Problemen oder Fragen:

1. Prüfen Sie die Datenbank-Konfiguration in `backend/config.php`
2. Prüfen Sie die PHP Error-Logs
3. Validieren Sie die Token-Generierung
4. Testen Sie mit dem bereitgestellten Test-Skript

---

**Erstellt**: 2025-12-23
**Version**: 1.0
