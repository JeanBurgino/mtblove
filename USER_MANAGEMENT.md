# User Management Guide

Anleitung zum Erstellen und Verwalten von Benutzern für die MTB Love API.

---

## 🚀 Quick Start - 3 Methoden

### Methode 1: SQL Script (Sofort einsatzbereit)

**Am schnellsten - erstellt 2 fertige Test-Benutzer:**

```bash
mysql -u mtblove_admin -p mtblove < backend/create_user.sql
```

**Credentials:**
- `api_admin` / `ApiAdmin123` (Admin-Rolle)
- `api_editor` / `ApiEditor123` (Editor-Rolle)

**Sofort testen:**
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=login" \
  -d "username=api_admin" \
  -d "password=ApiAdmin123"
```

---

### Methode 2: PHP Script (Empfohlen für individuelle Benutzer)

**Benutzer direkt in der Datenbank anlegen:**

```bash
php backend/create_user.php <username> <password> [role]
```

**Beispiel:**
```bash
php backend/create_user.php john_doe SecurePass123 admin
```

**Output:**
```
✓ User created successfully!
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
User ID: 3
Username: john_doe
Role: admin

📝 API Authentication:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
...
```

---

### Methode 3: Password Hash Generator + SQL

**Wenn du volle Kontrolle über SQL brauchst:**

**Schritt 1 - Hash generieren:**
```bash
php backend/generate_password_hash.php MySecurePassword123
```

**Output:**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Password Hash Generated
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Password: MySecurePassword123
Hash:     $2y$12$abc123...xyz789

SQL INSERT Example:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
INSERT INTO users (username, password_hash, email, role, created_at)
VALUES (
    'your_username',
    '$2y$12$abc123...xyz789',
    'your_email@example.com',
    'admin',
    NOW()
);
```

**Schritt 2 - SQL ausführen:**
```bash
mysql -u mtblove_admin -p mtblove
```

```sql
INSERT INTO users (username, password_hash, email, role, created_at)
VALUES (
    'my_user',
    '$2y$12$abc123...xyz789',
    'my_user@example.com',
    'admin',
    NOW()
);
```

---

## 👥 Standard-Benutzer

### Bereits vorhanden (aus database.sql):

```
Username: admin
Password: admin123
Role: admin
```

**⚠️ WICHTIG:** Ändere dieses Passwort in Produktion!

**Passwort ändern:**
```sql
UPDATE users
SET password_hash = '$2y$12$YOUR_NEW_HASH'
WHERE username = 'admin';
```

Hash generieren:
```bash
php backend/generate_password_hash.php YourNewPassword123
```

---

## 🔐 Rollen und Berechtigungen

### Admin-Rolle
- Voller Zugriff auf alle API-Endpoints
- Kann Wallpapers hinzufügen/bearbeiten/löschen
- Kann Produkte verwalten
- Kann Social Media Stats aktualisieren
- Zugriff auf Admin-Statistiken

### Editor-Rolle
- Eingeschränkter Zugriff (kann in `auth.php` angepasst werden)
- Standardmäßig: Kann lesen, aber nicht ändern
- Keine Admin-Dashboard Zugriffe

---

## 📋 Benutzer verwalten

### Alle Benutzer anzeigen

```sql
SELECT
    id,
    username,
    email,
    role,
    created_at,
    last_login
FROM users
ORDER BY created_at DESC;
```

### Benutzer löschen

```sql
DELETE FROM users WHERE username = 'username_to_delete';
```

### Rolle ändern

```sql
UPDATE users
SET role = 'editor'
WHERE username = 'username';
```

### Passwort zurücksetzen

**Schritt 1 - Neuen Hash generieren:**
```bash
php backend/generate_password_hash.php NewPassword123
```

**Schritt 2 - Hash in DB aktualisieren:**
```sql
UPDATE users
SET password_hash = '$2y$12$NEW_HASH_HERE'
WHERE username = 'username';
```

---

## 🔄 User Workflow

### 1. Benutzer erstellen
```bash
php backend/create_user.php api_user MyPassword123 admin
```

### 2. Login (Token erhalten)
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=login" \
  -d "username=api_user" \
  -d "password=MyPassword123"
```

**Response:**
```json
{
  "success": true,
  "token": "a1b2c3d4e5f6...",
  "user": {
    "id": 2,
    "username": "api_user",
    "role": "admin"
  }
}
```

### 3. Token verwenden
```bash
TOKEN="a1b2c3d4e5f6..."

curl -X POST http://localhost/backend/api/index.php \
  -d "action=update_social_stats" \
  -d "instagram_followers=10000" \
  -d "token=$TOKEN"
```

### 4. Token prüfen
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=check_auth" \
  -d "token=$TOKEN"
```

### 5. Logout
```bash
curl -X POST http://localhost/backend/api/index.php \
  -d "action=logout" \
  -d "token=$TOKEN"
```

---

## 🛠️ Tools Übersicht

| Tool | Verwendung | Output |
|------|------------|--------|
| `create_user.php` | Benutzer direkt in DB anlegen | Erstellt User + zeigt Credentials |
| `generate_password_hash.php` | Password-Hash für SQL generieren | Hash + SQL-Template |
| `create_user.sql` | Fertige Test-User erstellen | 2 vordefinierte Benutzer |

---

## ⚙️ Erweiterte Optionen

### Passwort-Anforderungen anpassen

Bearbeite `backend/create_user.php`:

```php
// Aktuelle Mindestlänge: 8 Zeichen
if (strlen($password) < 8) {
    echo "Error: Password must be at least 8 characters long\n";
    exit(1);
}

// Ändern zu 12 Zeichen:
if (strlen($password) < 12) {
    echo "Error: Password must be at least 12 characters long\n";
    exit(1);
}
```

### Token-Lebensdauer anpassen

Bearbeite `backend/config.php`:

```php
// Aktuelle Lebensdauer: 1 Stunde (3600 Sekunden)
define('SESSION_LIFETIME', 3600);

// Ändern zu 24 Stunden:
define('SESSION_LIFETIME', 86400);
```

### Neue Rolle hinzufügen

**Schritt 1 - Datenbank-Schema erweitern:**
```sql
ALTER TABLE users
MODIFY role ENUM('admin', 'editor', 'viewer') DEFAULT 'viewer';
```

**Schritt 2 - In `create_user.php` validieren:**
```php
if (!in_array($role, ['admin', 'editor', 'viewer'])) {
    echo "Error: Role must be 'admin', 'editor', or 'viewer'\n";
    exit(1);
}
```

---

## 🔒 Sicherheits-Best-Practices

1. **Starke Passwörter verwenden**
   - Mindestens 12 Zeichen
   - Mix aus Groß-/Kleinbuchstaben, Zahlen, Sonderzeichen
   - Keine Wörterbuch-Wörter

2. **Standard-Passwörter ändern**
   - `admin` / `admin123` sofort ändern in Produktion!
   - Test-Benutzer nach Entwicklung löschen

3. **Tokens sicher handhaben**
   - Tokens niemals in Logs speichern
   - Tokens nicht in URLs verwenden
   - HTTPS in Produktion verwenden

4. **Regelmäßige Audits**
   - Ungenutzte Benutzer löschen
   - Abgelaufene Sessions bereinigen:
     ```sql
     DELETE FROM sessions WHERE expires_at < NOW();
     ```

5. **Zugriffsbeschränkung**
   - Nur notwendige Benutzer mit Admin-Rechten
   - Editor-Rolle für eingeschränkte Benutzer nutzen

---

## 🐛 Troubleshooting

### "User already exists"
```bash
# Benutzer löschen und neu erstellen:
mysql -u mtblove_admin -p mtblove -e "DELETE FROM users WHERE username='username';"
php backend/create_user.php username NewPassword123 admin
```

### "Datenbankverbindung fehlgeschlagen"
```bash
# MySQL Status prüfen:
systemctl status mysql

# MySQL starten:
systemctl start mysql

# Credentials in backend/config.php prüfen
```

### "Password verification failed"
```bash
# Neuen Hash generieren und updaten:
php backend/generate_password_hash.php NewPassword123

# Dann SQL UPDATE mit dem neuen Hash
```

---

## 📚 Weiterführende Dokumentation

- **API_DOCUMENTATION.md** - Vollständige API-Referenz
- **QUICK_START_API.md** - Schnellstart-Guide für API
- **SOCIAL_STATS_README.md** - Social Media Counter Feature
