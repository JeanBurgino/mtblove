# Instagram Graph API Integration - Setup Anleitung

Diese Anleitung erklärt, wie Sie die Instagram Graph API Integration einrichten, um Posts automatisch zu importieren.

## Voraussetzungen

1. **Facebook Business Account** - Sie benötigen einen Facebook Business Account
2. **Instagram Business Account** - Ihr Instagram Account muss ein Business- oder Creator-Account sein
3. **Facebook App** - Eine registrierte Facebook App mit Instagram Graph API Zugriff

## Schritt-für-Schritt Anleitung

### 1. Facebook App erstellen

1. Gehen Sie zu [Facebook Developers](https://developers.facebook.com/)
2. Klicken Sie auf "My Apps" → "Create App"
3. Wählen Sie "Business" als App-Typ
4. Geben Sie einen App-Namen ein und wählen Sie Ihren Business Account
5. Erstellen Sie die App

### 2. Instagram Graph API aktivieren

1. In Ihrer Facebook App, gehen Sie zu "Add Product"
2. Fügen Sie "Instagram Graph API" hinzu
3. Konfigurieren Sie die Berechtigungen:
   - `instagram_basic`
   - `instagram_manage_insights`
   - `pages_read_engagement`

### 3. Access Token generieren

#### Option A: Graph API Explorer (Für Testing)

1. Gehen Sie zu [Graph API Explorer](https://developers.facebook.com/tools/explorer/)
2. Wählen Sie Ihre App aus
3. Fügen Sie folgende Berechtigungen hinzu:
   - `instagram_basic`
   - `instagram_manage_insights`
   - `pages_read_engagement`
4. Klicken Sie auf "Generate Access Token"
5. Kopieren Sie den Token (⚠️ Dieser Token ist nur 1 Stunde gültig!)

#### Option B: Long-Lived Access Token (Empfohlen für Produktion)

```bash
# 1. Holen Sie sich einen Short-Lived Token über Graph API Explorer

# 2. Konvertieren Sie ihn zu einem Long-Lived Token (60 Tage):
curl -i -X GET "https://graph.facebook.com/v18.0/oauth/access_token?grant_type=fb_exchange_token&client_id=YOUR_APP_ID&client_secret=YOUR_APP_SECRET&fb_exchange_token=YOUR_SHORT_LIVED_TOKEN"

# 3. Holen Sie sich einen Never-Expiring Page Access Token:
curl -i -X GET "https://graph.facebook.com/v18.0/me/accounts?access_token=YOUR_LONG_LIVED_TOKEN"

# 4. Mit dem Page Access Token, holen Sie den Instagram Business Account:
curl -i -X GET "https://graph.facebook.com/v18.0/PAGE_ID?fields=instagram_business_account&access_token=PAGE_ACCESS_TOKEN"
```

### 4. Instagram User ID finden

```bash
# Mit Ihrem Access Token:
curl -i -X GET "https://graph.facebook.com/v18.0/me?fields=id,username&access_token=YOUR_ACCESS_TOKEN"
```

Die Response enthält Ihre Instagram User ID.

### 5. Credentials in die Anwendung eintragen

Öffnen Sie die Datei `/app/config/config.php` und tragen Sie Ihre Credentials ein:

```php
// Instagram Graph API Konfiguration
define('INSTAGRAM_ACCESS_TOKEN', 'IHR_ACCESS_TOKEN_HIER');
define('INSTAGRAM_USER_ID', 'IHRE_USER_ID_HIER');
define('INSTAGRAM_API_VERSION', 'v18.0');
```

## Verwendung

1. Melden Sie sich im Admin Center an
2. Wechseln Sie zum Tab "Instagram Posts"
3. Klicken Sie auf den Button "Posts importieren"
4. Die Posts werden automatisch importiert

## Was wird importiert?

Für jeden Instagram Post werden folgende Daten importiert:

- **Post ID** - Eindeutige Instagram Post ID
- **Caption** - Post-Text
- **Hashtags** - Automatisch aus dem Caption extrahiert
- **Media URL** - URL zum Bild/Video
- **Post Date** - Veröffentlichungsdatum
- **Views** (Impressions) - Anzahl der Aufrufe
- **Likes** - Anzahl der Likes
- **Comments** - Anzahl der Kommentare
- **Saves** - Anzahl der Speicherungen
- **Engagement Rate** - Wird automatisch berechnet

## API Limits

Beachten Sie die Rate Limits der Instagram Graph API:

- **Rate Limit**: 200 Calls pro Stunde pro User
- **Daten-Verzögerung**: Analytics-Daten können bis zu 48 Stunden verzögert sein

## Troubleshooting

### "Instagram API nicht konfiguriert"
→ Prüfen Sie, ob Access Token und User ID in `config.php` eingetragen sind

### "Fehler beim Abrufen der Instagram Posts"
→ Prüfen Sie:
- Access Token ist gültig und nicht abgelaufen
- Instagram Account ist ein Business/Creator Account
- API-Berechtigungen sind korrekt gesetzt
- User ID ist korrekt

### "Keine Instagram Posts gefunden"
→ Ihr Instagram Account hat möglicherweise noch keine Posts

## Sicherheitshinweise

⚠️ **WICHTIG**:
- Committen Sie niemals Ihre Access Tokens in Git!
- Verwenden Sie `.gitignore` für `config.php` oder verwenden Sie Umgebungsvariablen
- Erneuern Sie regelmäßig Ihre Access Tokens
- Verwenden Sie Long-Lived Tokens für Produktion

## Weiterführende Links

- [Instagram Graph API Dokumentation](https://developers.facebook.com/docs/instagram-api)
- [Instagram Insights](https://developers.facebook.com/docs/instagram-api/reference/ig-media/insights)
- [Access Token Verwaltung](https://developers.facebook.com/docs/facebook-login/guides/access-tokens/)

## Support

Bei Fragen oder Problemen:
1. Prüfen Sie die [Facebook Developer Documentation](https://developers.facebook.com/docs/)
2. Prüfen Sie die Error Logs in `/var/log/` oder in den Browser-Console
3. Kontaktieren Sie den Administrator
