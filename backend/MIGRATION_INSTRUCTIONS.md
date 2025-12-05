# Datenbank-Migrationen

## 1. Icon-Spalte für Produkttypen

### Zweck
Diese Migration fügt eine `icon` Spalte zur `product_types` Tabelle hinzu, um SVG-Icons für verschiedene Produkttypen zu speichern (ähnlich wie `country_flag` bei Markets).

### Erforderlich für
- Shop-Seite: Anzeige von Produkttyp-Icons neben Länder-Icons

## 2. UUID-Spalte für Designs (WICHTIG für Bulk Import!)

### Zweck
Diese Migration fügt eine `uuid` Spalte zur `designs` Tabelle hinzu, um Amazon Merch Design-UUIDs zu speichern. Dies ermöglicht:
- Identifikation eindeutiger Designs anhand ihrer Amazon Edit URL
- Mehrere Produkttypen für dasselbe Design (als Varianten)
- Vermeidung von Duplikaten beim Bulk-Import

### Erforderlich für
- **Bulk Import Funktion**: Ohne dieses Feld funktioniert die UUID-basierte Duplikaterkennung nicht
- Amazon Merch Integration

## Migration ausführen

### ⚡ Einfachste Methode: PHP-Skript (EMPFOHLEN)
```bash
cd backend
php run_migration.php
```

Dieses Skript führt automatisch die UUID-Migration aus und prüft, ob die Spalte bereits existiert.

### Option 2: Über phpMyAdmin
1. Öffne phpMyAdmin
2. Wähle deine Datenbank aus
3. Klicke auf "SQL"
4. Kopiere den Inhalt der gewünschten SQL-Datei:
   - `migrations/add_uuid_to_designs.sql` (für UUID-Spalte)
   - `add_icon_to_product_types.sql` (für Icon-Spalte)
5. Füge ihn ein und klicke auf "OK"

### Option 3: Über MySQL Command Line
```bash
# UUID-Migration
mysql -u dein_benutzer -p deine_datenbank < backend/migrations/add_uuid_to_designs.sql

# Icon-Migration
mysql -u dein_benutzer -p deine_datenbank < backend/add_icon_to_product_types.sql
```

## Was wird geändert

### UUID-Migration (`add_uuid_to_designs.sql`)
- Neue Spalte `uuid` (VARCHAR 36) in der Tabelle `designs`
- UNIQUE Index auf `uuid` für schnelle Duplikatprüfung
- Speichert Amazon Merch Design-UUID aus Edit URL

**Beispiel Edit URL:**
```
https://merch.amazon.com/designs/f943809c-c98e-45d7-b1b4-757dd14bb8f9/edit
                                 ↑ Diese UUID wird gespeichert
```

### Icon-Migration (`add_icon_to_product_types.sql`)
- Neue Spalte `icon` in der Tabelle `product_types`
- Standard-Icons werden für existierende Produkttypen gesetzt:
  - T-Shirt → `product-tshirt.svg`
  - Hoodie → `product-hoodie.svg`
  - Tank Top → `product-tank-top.svg`
  - Long Sleeve → `product-long-sleeve.svg`
  - iPhone Case → `product-iphone-case.svg`
- View `active_variants` wird aktualisiert, um die neue `icon` Spalte einzuschließen

## Icon-Dateien
Stelle sicher, dass die entsprechenden SVG-Icon-Dateien im Verzeichnis `/uploads/icons/` vorhanden sind.

## Nach der Migration

### UUID-Migration
- Bulk Import kann nun Designs anhand ihrer UUID identifizieren
- Mehrere CSV-Zeilen mit derselben Edit URL werden als Varianten desselben Designs importiert
- Keine Duplikate mehr beim Import

### Icon-Migration
- Shop-Seite zeigt die Produkttyp-Icons zusammen mit den Länder-Icons an
