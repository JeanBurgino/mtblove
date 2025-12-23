# Shop Item Upload API Dokumentation

Diese Dokumentation beschreibt die REST API für das Hochladen von Shop Items (Designs) in die MTB Love Plattform.

## Inhaltsverzeichnis

1. [Übersicht](#übersicht)
2. [Authentifizierung](#authentifizierung)
3. [API Endpunkt](#api-endpunkt)
4. [Parameter](#parameter)
5. [Beispiele](#beispiele)
6. [Fehlerbehandlung](#fehlerbehandlung)
7. [Markets und Product Types](#markets-und-product-types)

---

## Übersicht

Die Shop Item Upload API ermöglicht das Erstellen neuer Designs mit zugehörigen Produktvarianten für verschiedene Märkte (Amazon DE, US, UK) und Produkttypen (T-Shirt, Hoodie, etc.).

**API Endpunkt**: `POST /backend/api/index.php?action=add_design`

**Authentifizierung**: Erforderlich (Token-basiert)

**Content-Type**: `multipart/form-data` (wegen Bild-Uploads)

---

## Authentifizierung

### Schritt 1: Login und Token erhalten

Zunächst müssen Sie sich mit dem `api_admin` User einloggen, um ein Authentifizierungs-Token zu erhalten.

#### API Endpunkt
```
POST /backend/api/index.php
```

#### Parameter
- `action=login`
- `username=api_admin`
- `password=ApiAdmin123`

#### cURL Beispiel
```bash
curl -X POST 'http://your-domain.com/backend/api/index.php' \
  -d "action=login" \
  -d "username=api_admin" \
  -d "password=ApiAdmin123"
```

#### Erfolgreiche Antwort
```json
{
  "success": true,
  "token": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6a7b8c9d0e1f2",
  "user": {
    "id": 2,
    "username": "api_admin",
    "role": "admin",
    "email": "api_admin@mtblove.com"
  }
}
```

⚠️ **Wichtig**: Speichern Sie das `token` für weitere API-Aufrufe.

### Schritt 2: Token bei API-Aufrufen verwenden

Bei allen geschützten Endpunkten (wie `add_design`) muss das Token mitgesendet werden:

- Als POST Parameter: `token=YOUR_TOKEN_HERE`
- Oder als GET Parameter: `?token=YOUR_TOKEN_HERE`

---

## API Endpunkt

### Shop Item Upload

**URL**: `/backend/api/index.php?action=add_design`

**Methode**: `POST`

**Content-Type**: `multipart/form-data`

**Authentifizierung**: Erforderlich

---

## Parameter

### Pflichtfelder

| Parameter | Typ | Beschreibung |
|-----------|-----|--------------|
| `action` | string | Muss `add_design` sein |
| `token` | string | Authentifizierungs-Token vom Login |
| `title` | string | Titel des Designs (z.B. "Vintage Mountain Bike") |

### Optionale Felder

| Parameter | Typ | Beschreibung |
|-----------|-----|--------------|
| `description` | string | SEO-Beschreibung des Designs |
| `tags` | string | Komma-getrennte Suchbegriffe (z.B. "vintage,bike,mtb") |
| `design_image` | file | Hauptbild des Designs (JPEG, PNG, GIF, WebP, max 10MB) |
| `mockup_image_1` | file | Mockup für Standard T-Shirt (Product Type ID 1) |
| `mockup_image_2` | file | Mockup für Hoodie (Product Type ID 2) |
| `mockup_image_3` | file | Mockup für Tank Top (Product Type ID 3) |
| `mockup_image_4` | file | Mockup für Long Sleeve (Product Type ID 4) |
| `mockup_image_5` | file | Mockup für iPhone Case (Product Type ID 5) |
| `variants` | JSON | Array von Produktvarianten (siehe unten) |

### Variants JSON Format

Das `variants` Parameter ist ein JSON Array mit folgender Struktur:

```json
[
  {
    "asin": "B08XYZ123ABC",
    "market_id": 1,
    "product_type_id": 1,
    "price": 19.99
  },
  {
    "asin": "B08XYZ456DEF",
    "market_id": 2,
    "product_type_id": 1,
    "price": 24.99
  }
]
```

#### Variant Felder

| Feld | Typ | Pflicht | Beschreibung |
|------|-----|---------|--------------|
| `asin` | string | Ja | Amazon Standard Identification Number |
| `market_id` | integer | Ja | Markt-ID (1=DE, 2=US, 3=UK) |
| `product_type_id` | integer | Ja | Produkttyp-ID (1-5) |
| `price` | decimal | Nein | Preis des Produkts |

---

## Beispiele

### Beispiel 1: Minimales Design (nur Titel)

```bash
curl -X POST 'http://your-domain.com/backend/api/index.php' \
  -F "action=add_design" \
  -F "token=YOUR_TOKEN_HERE" \
  -F "title=Simple MTB Design"
```

### Beispiel 2: Design mit Beschreibung und Tags

```bash
curl -X POST 'http://your-domain.com/backend/api/index.php' \
  -F "action=add_design" \
  -F "token=YOUR_TOKEN_HERE" \
  -F "title=Vintage Mountain Bike Art" \
  -F "description=Cooler Retro-Style Mountain Bike Design für echte Biker" \
  -F "tags=vintage,retro,mountain bike,cycling,mtb"
```

### Beispiel 3: Vollständiges Design mit Bildern und Varianten

```bash
curl -X POST 'http://your-domain.com/backend/api/index.php' \
  -F "action=add_design" \
  -F "token=YOUR_TOKEN_HERE" \
  -F "title=Epic Trail Rider" \
  -F "description=Episches Mountain Bike Design für Trailliebhaber" \
  -F "tags=trail,mountain bike,adventure,outdoor" \
  -F "design_image=@/path/to/design-preview.jpg" \
  -F "mockup_image_1=@/path/to/tshirt-mockup.jpg" \
  -F "mockup_image_2=@/path/to/hoodie-mockup.jpg" \
  -F 'variants=[
    {
      "asin": "B08TRAIL123",
      "market_id": 1,
      "product_type_id": 1,
      "price": 19.99
    },
    {
      "asin": "B08TRAIL456",
      "market_id": 2,
      "product_type_id": 1,
      "price": 24.99
    },
    {
      "asin": "B08TRAIL789",
      "market_id": 1,
      "product_type_id": 2,
      "price": 39.99
    }
  ]'
```

### Beispiel 4: Mit PHP

```php
<?php

// Login und Token erhalten
$loginData = [
    'action' => 'login',
    'username' => 'api_admin',
    'password' => 'ApiAdmin123'
];

$ch = curl_init('http://your-domain.com/backend/api/index.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($loginData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$result = json_decode($response, true);
$token = $result['token'];

curl_close($ch);

// Design hochladen
$variants = [
    [
        'asin' => 'B08XYZ123',
        'market_id' => 1,
        'product_type_id' => 1,
        'price' => 19.99
    ],
    [
        'asin' => 'B08XYZ456',
        'market_id' => 2,
        'product_type_id' => 1,
        'price' => 24.99
    ]
];

$postData = [
    'action' => 'add_design',
    'token' => $token,
    'title' => 'Awesome MTB Design',
    'description' => 'Ein cooles Mountain Bike Design',
    'tags' => 'mtb,bike,mountain',
    'variants' => json_encode($variants)
];

// Bild hinzufügen
$postData['design_image'] = new CURLFile('/path/to/image.jpg', 'image/jpeg', 'design.jpg');
$postData['mockup_image_1'] = new CURLFile('/path/to/tshirt.jpg', 'image/jpeg', 'tshirt.jpg');

$ch = curl_init('http://your-domain.com/backend/api/index.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$result = json_decode($response, true);

curl_close($ch);

print_r($result);
```

### Beispiel 5: Mit Python

```python
import requests

# Login und Token erhalten
login_url = 'http://your-domain.com/backend/api/index.php'
login_data = {
    'action': 'login',
    'username': 'api_admin',
    'password': 'ApiAdmin123'
}

response = requests.post(login_url, data=login_data)
token = response.json()['token']

# Design hochladen
upload_url = 'http://your-domain.com/backend/api/index.php'

variants = [
    {
        'asin': 'B08XYZ123',
        'market_id': 1,
        'product_type_id': 1,
        'price': 19.99
    },
    {
        'asin': 'B08XYZ456',
        'market_id': 2,
        'product_type_id': 1,
        'price': 24.99
    }
]

data = {
    'action': 'add_design',
    'token': token,
    'title': 'Awesome MTB Design',
    'description': 'Ein cooles Mountain Bike Design',
    'tags': 'mtb,bike,mountain',
    'variants': json.dumps(variants)
}

files = {
    'design_image': open('/path/to/design.jpg', 'rb'),
    'mockup_image_1': open('/path/to/tshirt.jpg', 'rb')
}

response = requests.post(upload_url, data=data, files=files)
print(response.json())
```

---

## Erfolgreiche Antwort

### Status Code: 200

```json
{
  "success": true,
  "message": "Design erfolgreich erstellt",
  "design_id": 42,
  "slug": "awesome-mtb-design"
}
```

| Feld | Typ | Beschreibung |
|------|-----|--------------|
| `success` | boolean | Immer `true` bei Erfolg |
| `message` | string | Erfolgsmeldung |
| `design_id` | integer | ID des neu erstellten Designs |
| `slug` | string | URL-freundlicher Slug des Designs |

---

## Fehlerbehandlung

### Authentifizierungsfehler (401)

```json
{
  "success": false,
  "error": "Nicht autorisiert"
}
```

**Ursachen**:
- Kein Token mitgesendet
- Token ungültig oder abgelaufen
- Token gehört zu einem gelöschten User

**Lösung**: Erneut einloggen und neues Token abrufen

### Validierungsfehler (400)

```json
{
  "success": false,
  "error": "Titel ist erforderlich"
}
```

**Häufige Ursachen**:
- Titel fehlt oder ist leer
- Ungültiger Dateityp (nur JPEG, PNG, GIF, WebP erlaubt)
- Datei zu groß (max 10MB)
- Ungültiges JSON im `variants` Parameter

### Serverfehler (500)

```json
{
  "success": false,
  "error": "Fehler beim Erstellen: Duplicate entry 'design-slug' for key 'slug'"
}
```

**Ursachen**:
- Datenbankfehler
- Fehler beim Hochladen der Datei
- Slug bereits vorhanden (wird normalerweise automatisch gelöst)

---

## Markets und Product Types

### Verfügbare Markets

| ID | Code | Land | Base URL | Währung |
|----|------|------|----------|---------|
| 1 | DE | Germany | https://www.amazon.de | EUR (€) |
| 2 | US | United States | https://www.amazon.com | USD ($) |
| 3 | UK | United Kingdom | https://www.amazon.co.uk | GBP (£) |

### Verfügbare Product Types

| ID | Name | Slug | Icon |
|----|------|------|------|
| 1 | Standard T-Shirt | standard-t-shirt | 👕 |
| 2 | Hoodie | hoodie | 🧥 |
| 3 | Tank Top | tank-top | 🎽 |
| 4 | Long Sleeve | long-sleeve | 👔 |
| 5 | iPhone Case | iphone-case | 📱 |

### Markets und Product Types abrufen

#### Markets Liste

```bash
curl 'http://your-domain.com/backend/api/index.php?action=get_markets'
```

**Antwort**:
```json
[
  {
    "id": 1,
    "country_code": "DE",
    "country_name": "Germany",
    "country_flag": "flag-de.svg",
    "base_url": "https://www.amazon.de",
    "affiliate_tag": "mtblove-21",
    "currency_symbol": "€",
    "currency_code": "EUR"
  },
  ...
]
```

#### Product Types Liste

```bash
curl 'http://your-domain.com/backend/api/index.php?action=get_product_types'
```

**Antwort**:
```json
[
  {
    "id": 1,
    "name": "Standard T-Shirt",
    "slug": "standard-t-shirt",
    "icon_class": "tshirt-icon"
  },
  ...
]
```

---

## Technische Details

### Datei-Upload

- **Erlaubte Dateitypen**: JPEG, PNG, GIF, WebP
- **Maximale Dateigröße**: 10 MB
- **Upload-Verzeichnisse**:
  - Design-Bilder: `/backend/uploads/designs/`
  - Mockup-Bilder: `/backend/uploads/mockups/`

### Slug-Generierung

Der Slug wird automatisch aus dem Titel generiert:

- Deutsche Umlaute werden konvertiert: ä→ae, ö→oe, ü→ue, ß→ss
- Sonderzeichen werden entfernt
- Leerzeichen werden durch Bindestriche ersetzt
- Alles wird in Kleinbuchstaben umgewandelt

**Beispiele**:
- "Schöner MTB Titel" → "schoener-mtb-titel"
- "Über 100 Wege" → "ueber-100-wege"
- "T-Shirt Design #1" → "t-shirt-design-1"

### Token-Lebensdauer

- **Gültigkeit**: 1 Stunde (3600 Sekunden)
- **Ablauf**: Nach Ablauf muss ein neues Login durchgeführt werden
- **Speicherung**: Token wird in der `sessions` Tabelle gespeichert

### Transaktionssicherheit

Das Erstellen eines Designs erfolgt in einer Datenbank-Transaktion:

1. Design-Datensatz wird erstellt
2. Alle Varianten werden erstellt
3. Bei Fehler wird alles zurückgerollt

Dies gewährleistet Datenkonsistenz.

---

## Setup und Installation

### 1. api_admin User erstellen

Führen Sie das Setup-Skript aus, um den `api_admin` User anzulegen:

```bash
cd /home/user/mtblove/backend
php setup_api_admin.php
```

**Ausgabe**:
```
✓ Successfully created user 'api_admin'
  - Username: api_admin
  - Password: ApiAdmin123
  - Role: admin
  - Email: api_admin@mtblove.com

You can now use these credentials to authenticate API requests.
```

Falls der User bereits existiert, wird folgendes angezeigt:

```
✓ User 'api_admin' already exists:
  - ID: 2
  - Username: api_admin
  - Role: admin

No changes needed.
```

### 2. Upload-Verzeichnisse prüfen

Stellen Sie sicher, dass folgende Verzeichnisse existieren und beschreibbar sind:

```bash
mkdir -p /home/user/mtblove/backend/uploads/designs
mkdir -p /home/user/mtblove/backend/uploads/mockups
chmod -R 755 /home/user/mtblove/backend/uploads
```

### 3. Datenbank-Tabellen prüfen

Die benötigten Tabellen sollten bereits existieren:

- `users` - Benutzer und Authentifizierung
- `sessions` - Session-Tokens
- `designs` - Design-Hauptdaten
- `variants` - Produktvarianten
- `markets` - Amazon-Märkte
- `product_types` - Produkttypen

---

## Sicherheitshinweise

1. **Passwort ändern**: Ändern Sie das Standard-Passwort `ApiAdmin123` in Produktion!
2. **HTTPS verwenden**: Verwenden Sie immer HTTPS für API-Aufrufe in Produktion
3. **Token sicher speichern**: Token nie in Git committen oder öffentlich teilen
4. **Rate Limiting**: Implementieren Sie Rate Limiting für Produktionsumgebungen
5. **Input Validation**: Die API validiert bereits Eingaben, aber prüfen Sie zusätzlich auf Client-Seite

---

## Weiterführende Dokumentation

- [Vollständige API Dokumentation](API_DOCUMENTATION.md)
- [Quick Start Guide](QUICK_START_API.md)
- [Datenbank Schema](backend/database.sql)
- [Design Tables Schema](backend/create_designs_tables.sql)

---

## Support und Fragen

Bei Fragen oder Problemen:

1. Prüfen Sie die Error-Logs: `/backend/api/` (PHP Error Logs)
2. Testen Sie die Authentifizierung separat
3. Validieren Sie JSON-Strukturen mit einem JSON Validator
4. Prüfen Sie Datei-Berechtigungen für Upload-Verzeichnisse

---

**Version**: 1.0
**Erstellt**: 2025-12-23
**Autor**: MTB Love Development Team
