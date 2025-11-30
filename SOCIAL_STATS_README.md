# Social Media Follower Counters - Dokumentation

## Übersicht

Dieses Feature fügt Instagram und TikTok Follower-Zähler zur Homepage hinzu. Die Follower-Zahlen werden in der Datenbank gespeichert und können über die Admin-API aktualisiert werden.

## Neue Dateien

- `backend/api/social.php` - API-Endpunkte für Social Media Stats
- `backend/init_social_stats.php` - Initialisierungsskript für die Follower-Zahlen

## API Endpoints

### GET Social Stats (Öffentlich)
```
POST /backend/api/index.php
action=get_social_stats

Response:
{
  "instagram_followers": 5420,
  "tiktok_followers": 3280
}
```

### UPDATE Social Stats (Admin)
```
POST /backend/api/index.php
action=update_social_stats
instagram_followers=10000
tiktok_followers=8000

Response:
{
  "success": true,
  "message": "Social Media Stats aktualisiert",
  "instagram_followers": 10000,
  "tiktok_followers": 8000
}
```

## Installation

### 1. Datenbank initialisieren

Führe das Initialisierungsskript aus:

```bash
php backend/init_social_stats.php
```

Dies erstellt die folgenden Einträge in der `stats` Tabelle:
- `instagram_followers` = 5420
- `tiktok_followers` = 3280

### 2. Frontend

Die Follower-Counter werden automatisch auf der Homepage angezeigt. Die Zahlen werden beim Laden der Seite von der API abgerufen.

## Follower-Zahlen aktualisieren

### Option 1: Via API (empfohlen)

Mit einem authentifizierten POST-Request:

```bash
curl -X POST http://your-domain.com/backend/api/index.php \
  -d "action=update_social_stats" \
  -d "instagram_followers=10000" \
  -d "tiktok_followers=8000" \
  -H "Cookie: your-session-cookie"
```

### Option 2: Direkt in der Datenbank

```sql
UPDATE stats SET stat_value = '10000' WHERE stat_key = 'instagram_followers';
UPDATE stats SET stat_value = '8000' WHERE stat_key = 'tiktok_followers';
```

## Frontend-Funktionen

### Formatierung der Zahlen

Die Follower-Zahlen werden automatisch formatiert:
- Unter 1.000: Exakte Zahl (z.B. "543")
- 1.000 - 999.999: Mit "K" (z.B. "5.4K")
- Ab 1.000.000: Mit "M" (z.B. "1.2M")

### Design

Die Counter haben ein ansprechendes Design mit:
- Instagram: Pink/Lila Gradient mit Instagram-Icon
- TikTok: Cyan/Pink Gradient mit TikTok-Icon
- Hover-Effekt: Leichte Vergrößerung beim Darüberfahren
- Responsive Design für Mobile und Desktop

## Technische Details

### Datenbank Schema

Die Follower-Zahlen werden in der bestehenden `stats` Tabelle gespeichert:

```sql
CREATE TABLE IF NOT EXISTS `stats` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `stat_key` VARCHAR(50) NOT NULL UNIQUE,
    `stat_value` VARCHAR(100),
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Sicherheit

- Der `get_social_stats` Endpoint ist öffentlich zugänglich
- Der `update_social_stats` Endpoint erfordert Admin-Authentifizierung
- Alle Eingaben werden validiert und als Integer gespeichert

## Erweiterungsmöglichkeiten

Das System kann leicht erweitert werden für:
- Facebook Follower
- YouTube Abonnenten
- Twitter Follower
- Weitere Social Media Plattformen

Einfach neue Einträge in der `stats` Tabelle hinzufügen und das Frontend entsprechend anpassen.
