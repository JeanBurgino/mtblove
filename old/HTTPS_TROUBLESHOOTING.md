# HTTPS Troubleshooting Guide für mtblove.com

## Problem: "Site Temporarily Closed" nach Let's Encrypt Installation

Diese Meldung erscheint typischerweise, wenn Apache nach der SSL-Installation nicht richtig konfiguriert ist.

---

## Schritt 1: Teste, ob PHP/HTTPS grundsätzlich funktioniert

Rufe direkt auf:
```
https://mtblove.com/https-test.php
```

**Wenn diese Seite lädt:**
- ✓ PHP funktioniert
- ✓ HTTPS funktioniert
- ✓ Das Problem liegt bei der Anwendung oder .htaccess

**Wenn diese Seite NICHT lädt:**
- ✗ Problem mit Apache VirtualHost-Konfiguration
- Siehe Schritt 2

---

## Schritt 2: Apache VirtualHost für HTTPS prüfen

### SSL VirtualHost anzeigen:
```bash
sudo nano /etc/apache2/sites-available/000-default-le-ssl.conf
# oder
sudo nano /etc/apache2/sites-available/mtblove-le-ssl.conf
```

### Korrekte Konfiguration sollte sein:

```apache
<IfModule mod_ssl.c>
<VirtualHost *:443>
    ServerName mtblove.com
    ServerAlias www.mtblove.com

    # WICHTIG: DocumentRoot muss auf das richtige Verzeichnis zeigen!
    DocumentRoot /var/www/mtblove
    # oder
    # DocumentRoot /home/user/mtblove

    <Directory /var/www/mtblove>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # SSL-Zertifikate (von certbot automatisch eingefügt)
    SSLCertificateFile /etc/letsencrypt/live/mtblove.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/mtblove.com/privkey.pem
    Include /etc/letsencrypt/options-ssl-apache.conf
</VirtualHost>
</IfModule>
```

### Wichtig zu prüfen:

1. **DocumentRoot** zeigt auf das richtige Verzeichnis
2. **AllowOverride All** ist gesetzt (für .htaccess)
3. **Directory**-Pfad stimmt mit DocumentRoot überein

### Nach Änderungen:
```bash
# Konfiguration testen
sudo apache2ctl configtest

# Apache neu starten
sudo systemctl restart apache2
```

---

## Schritt 3: Prüfe .htaccess

Die .htaccess könnte Probleme verursachen. Teste mit minimaler Version:

```bash
cd /var/www/mtblove  # oder /home/user/mtblove

# Backup der aktuellen .htaccess
cp .htaccess .htaccess.backup

# Verwende minimale Version
cp .htaccess.minimal .htaccess

# Teste die Seite
```

**Wenn es jetzt funktioniert:**
- Problem war in der .htaccess
- Füge Regeln schrittweise wieder hinzu

**Wenn es immer noch nicht funktioniert:**
- Problem liegt nicht bei .htaccess
- Siehe Schritt 4

---

## Schritt 4: Prüfe Apache Error-Log

```bash
# Letzten 50 Zeilen anzeigen
sudo tail -50 /var/log/apache2/error.log

# Live-Monitoring
sudo tail -f /var/log/apache2/error.log
```

Häufige Fehler:
- **"File does not exist"** → DocumentRoot falsch
- **"Permission denied"** → Dateiberechtigungen falsch
- **".htaccess: Invalid command"** → Modul fehlt oder .htaccess-Fehler

---

## Schritt 5: Prüfe Dateiberechtigungen

```bash
cd /var/www/mtblove  # oder /home/user/mtblove

# Dateien auf 644
find . -type f -exec chmod 644 {} \;

# Verzeichnisse auf 755
find . -type d -exec chmod 755 {} \;

# Upload-Verzeichnis beschreibbar
chmod 755 app/public/memes/uploads

# Apache muss Dateien lesen können
sudo chown -R www-data:www-data .
# oder auf manchen Systemen:
# sudo chown -R apache:apache .
```

---

## Schritt 6: Teste DirectAccess (ohne .htaccess)

Temporär .htaccess deaktivieren:
```bash
mv .htaccess .htaccess.disabled
```

Dann direkt aufrufen:
```
https://mtblove.com/app/public/index.php
```

**Wenn das funktioniert:**
- Problem ist definitiv in .htaccess
- Verwende .htaccess.minimal und füge Regeln schrittweise hinzu

---

## Schritt 7: Apache Module prüfen

Stelle sicher, dass alle nötigen Module geladen sind:

```bash
# mod_rewrite aktivieren
sudo a2enmod rewrite

# mod_ssl aktivieren (sollte von certbot gemacht worden sein)
sudo a2enmod ssl

# mod_headers aktivieren
sudo a2enmod headers

# Apache neu starten
sudo systemctl restart apache2
```

---

## Schritt 8: HTTPS-Redirect Problem

Falls das HTTPS-Redirect in .htaccess Probleme macht:

```bash
# .htaccess öffnen
nano .htaccess
```

Kommentiere die HTTPS-Redirect-Regeln aus (sollten bereits auskommentiert sein):
```apache
# Force HTTPS (in Produktion)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
```

---

## Schritt 9: PHP Session-Problem

Falls Sessions Probleme machen:

```bash
# Session-Verzeichnis prüfen
ls -la /var/lib/php/sessions

# Falls nicht beschreibbar:
sudo chmod 733 /var/lib/php/sessions
```

---

## Schnell-Diagnose Checkliste

```bash
# 1. Apache läuft?
sudo systemctl status apache2

# 2. SSL-Site aktiviert?
sudo a2ensite 000-default-le-ssl.conf  # oder dein SSL-Config-Name
sudo systemctl reload apache2

# 3. Konfiguration gültig?
sudo apache2ctl configtest

# 4. Ports offen?
sudo netstat -tlnp | grep ':443'

# 5. Firewall?
sudo ufw status
sudo ufw allow 443/tcp

# 6. SELinux (falls aktiv)?
sudo getenforce
# Falls "Enforcing": Temporär deaktivieren zum Testen
sudo setenforce 0
```

---

## Häufige Lösungen

### Problem: DocumentRoot falsch

**Symptom:** "Site Temporarily Closed" oder Standard-Apache-Seite

**Lösung:**
```bash
# Finde den richtigen Pfad
ls -la /var/www/mtblove
# oder
ls -la /home/user/mtblove

# In SSL VirtualHost-Config anpassen
sudo nano /etc/apache2/sites-available/000-default-le-ssl.conf
```

### Problem: AllowOverride nicht gesetzt

**Symptom:** .htaccess wird ignoriert, keine Clean URLs

**Lösung:**
```apache
<Directory /var/www/mtblove>
    AllowOverride All  # ← Das ist wichtig!
</Directory>
```

### Problem: Site-Config nicht aktiviert

**Lösung:**
```bash
sudo a2ensite 000-default-le-ssl.conf
sudo systemctl reload apache2
```

---

## Wenn nichts hilft

1. **Erstelle einen minimalen Test:**
```bash
echo "<?php echo 'PHP works!'; ?>" > /var/www/mtblove/minimal-test.php
```
Rufe auf: `https://mtblove.com/minimal-test.php`

2. **Prüfe alle Apache-Configs:**
```bash
sudo apache2ctl -S
```
Das zeigt alle VirtualHosts und ihre Konfiguration.

3. **Prüfe SSL-Zertifikat:**
```bash
sudo certbot certificates
```

4. **Starte Apache im Debug-Modus:**
```bash
sudo apachectl -X
```

---

## Nach erfolgreicher Behebung

1. Aktiviere HTTPS-Redirect in .htaccess (entkommentieren)
2. Lösche Test-Dateien: `https-test.php`, `test.php`, `setup-check.php`
3. Setze `display_errors = 0` in `index.php`
4. Teste alle Funktionen der Anwendung

---

## Support

Falls das Problem weiterhin besteht, sammle diese Informationen:

```bash
# Apache-Version
apache2 -v

# Aktive Sites
ls -la /etc/apache2/sites-enabled/

# VirtualHost-Dump
sudo apache2ctl -S

# Error-Log (letzte 50 Zeilen)
sudo tail -50 /var/log/apache2/error.log

# Access-Log (letzte 20 Zeilen)
sudo tail -20 /var/log/apache2/access.log
```
