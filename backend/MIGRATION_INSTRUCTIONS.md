# Datenbank-Migration: Icon-Spalte für Produkttypen

## Zweck
Diese Migration fügt eine `icon` Spalte zur `product_types` Tabelle hinzu, um SVG-Icons für verschiedene Produkttypen zu speichern (ähnlich wie `country_flag` bei Markets).

## Erforderlich für
- Shop-Seite: Anzeige von Produkttyp-Icons neben Länder-Icons

## Migration ausführen

### Option 1: Über phpMyAdmin
1. Öffne phpMyAdmin
2. Wähle deine Datenbank aus
3. Klicke auf "SQL"
4. Kopiere den Inhalt der Datei `add_icon_to_product_types.sql`
5. Füge ihn ein und klicke auf "OK"

### Option 2: Über MySQL Command Line
```bash
mysql -u dein_benutzer -p deine_datenbank < backend/add_icon_to_product_types.sql
```

## Was wird geändert
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
Nach erfolgreicher Ausführung zeigt die Shop-Seite die Produkttyp-Icons zusammen mit den Länder-Icons an.
