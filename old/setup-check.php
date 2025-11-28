<?php
/**
 * Setup-Überprüfung für mtblove.com
 *
 * Diese Datei überprüft die Systemvoraussetzungen und Konfiguration
 * Rufe sie auf unter: http://your-domain.com/setup-check.php
 *
 * WICHTIG: Lösche diese Datei nach erfolgreichem Setup!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Check - mtblove.com</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #ccc;
        }
        .check-item.success {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .check-item.warning {
            background: #fff3cd;
            border-left-color: #ffc107;
        }
        .check-item.error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .icon {
            font-weight: bold;
            margin-right: 10px;
        }
        .success .icon { color: #28a745; }
        .warning .icon { color: #ffc107; }
        .error .icon { color: #dc3545; }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Setup-Überprüfung für mtblove.com</h1>

        <?php
        $checks = [];
        $errors = 0;
        $warnings = 0;

        // PHP Version
        $php_version = phpversion();
        if (version_compare($php_version, '7.4.0', '>=')) {
            $checks[] = ['success', 'PHP Version', "PHP $php_version ist installiert ✓"];
        } else {
            $checks[] = ['error', 'PHP Version', "PHP $php_version ist zu alt. Mindestens PHP 7.4 erforderlich!"];
            $errors++;
        }

        // PDO Extension
        if (extension_loaded('pdo') && extension_loaded('pdo_mysql')) {
            $checks[] = ['success', 'PDO MySQL', 'PDO MySQL Extension ist verfügbar ✓'];
        } else {
            $checks[] = ['error', 'PDO MySQL', 'PDO MySQL Extension fehlt!'];
            $errors++;
        }

        // mod_rewrite (Apache)
        if (function_exists('apache_get_modules')) {
            if (in_array('mod_rewrite', apache_get_modules())) {
                $checks[] = ['success', 'mod_rewrite', 'Apache mod_rewrite ist aktiviert ✓'];
            } else {
                $checks[] = ['error', 'mod_rewrite', 'Apache mod_rewrite ist NICHT aktiviert!'];
                $errors++;
            }
        } else {
            $checks[] = ['warning', 'mod_rewrite', 'Kann nicht überprüft werden (nicht auf Apache oder CLI)'];
            $warnings++;
        }

        // Konfigurationsdatei
        $config_file = __DIR__ . '/app/config/config.php';
        if (file_exists($config_file)) {
            $checks[] = ['success', 'Konfiguration', 'config.php existiert ✓'];

            // Config laden und DB-Verbindung testen
            try {
                require_once $config_file;

                // DB-Verbindung testen
                if (isset($pdo) && $pdo instanceof PDO) {
                    $checks[] = ['success', 'Datenbankverbindung', 'Verbindung zur Datenbank erfolgreich ✓'];

                    // Tabellen prüfen
                    $tables = ['users', 'memes', 'meme_views', 'meme_likes'];
                    $existing_tables = [];
                    foreach ($tables as $table) {
                        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                        if ($stmt->rowCount() > 0) {
                            $existing_tables[] = $table;
                        }
                    }

                    if (count($existing_tables) === count($tables)) {
                        $checks[] = ['success', 'Datenbank-Schema', 'Alle Tabellen existieren ✓'];
                    } else {
                        $missing = array_diff($tables, $existing_tables);
                        $checks[] = ['error', 'Datenbank-Schema', 'Fehlende Tabellen: ' . implode(', ', $missing) . '<br>Importiere <code>app/config/schema.sql</code>'];
                        $errors++;
                    }

                    // Demo-User prüfen
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                    $user_count = $stmt->fetch()['count'];
                    if ($user_count > 0) {
                        $checks[] = ['success', 'Benutzer', "$user_count Benutzer in der Datenbank ✓"];
                    } else {
                        $checks[] = ['warning', 'Benutzer', 'Keine Benutzer vorhanden. Schema importieren!'];
                        $warnings++;
                    }
                }
            } catch (Exception $e) {
                $checks[] = ['error', 'Datenbankverbindung', 'Fehler: ' . htmlspecialchars($e->getMessage())];
                $errors++;
            }
        } else {
            $checks[] = ['error', 'Konfiguration', 'config.php fehlt!'];
            $errors++;
        }

        // Upload-Verzeichnis
        $upload_dir = __DIR__ . '/app/public/memes/uploads';
        if (is_dir($upload_dir)) {
            if (is_writable($upload_dir)) {
                $checks[] = ['success', 'Upload-Verzeichnis', 'Upload-Verzeichnis ist beschreibbar ✓'];
            } else {
                $checks[] = ['error', 'Upload-Verzeichnis', 'Upload-Verzeichnis ist NICHT beschreibbar! <code>chmod 755 app/public/memes/uploads</code>'];
                $errors++;
            }
        } else {
            $checks[] = ['error', 'Upload-Verzeichnis', 'Upload-Verzeichnis existiert nicht!'];
            $errors++;
        }

        // .htaccess
        $htaccess_file = __DIR__ . '/.htaccess';
        if (file_exists($htaccess_file)) {
            $checks[] = ['success', '.htaccess', '.htaccess Datei existiert ✓'];
        } else {
            $checks[] = ['warning', '.htaccess', '.htaccess fehlt - Clean URLs funktionieren möglicherweise nicht'];
            $warnings++;
        }

        // HTTPS
        $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        if ($is_https) {
            $checks[] = ['success', 'HTTPS', 'Verbindung ist verschlüsselt (HTTPS) ✓'];
        } else {
            $checks[] = ['warning', 'HTTPS', 'Verbindung ist NICHT verschlüsselt (HTTP). HTTPS empfohlen!'];
            $warnings++;
        }

        // Ausgabe
        foreach ($checks as $check) {
            echo "<div class='check-item {$check[0]}'>";
            echo "<span class='icon'>";
            echo $check[0] === 'success' ? '✓' : ($check[0] === 'warning' ? '⚠' : '✗');
            echo "</span>";
            echo "<strong>{$check[1]}:</strong> {$check[2]}";
            echo "</div>";
        }
        ?>

        <div class="info-box">
            <h3>📊 Zusammenfassung</h3>
            <p>
                <strong>Fehler:</strong> <?php echo $errors; ?><br>
                <strong>Warnungen:</strong> <?php echo $warnings; ?>
            </p>

            <?php if ($errors === 0 && $warnings === 0): ?>
                <p style="color: #28a745; font-weight: bold;">
                    ✓ Alle Prüfungen erfolgreich! Die Anwendung ist einsatzbereit.
                </p>
                <p style="color: #dc3545; font-weight: bold;">
                    ⚠️ WICHTIG: Lösche diese Datei (setup-check.php) aus Sicherheitsgründen!
                </p>
            <?php elseif ($errors === 0): ?>
                <p style="color: #ffc107;">
                    Die Anwendung sollte funktionieren, aber es gibt einige Warnungen.
                </p>
            <?php else: ?>
                <p style="color: #dc3545;">
                    Es gibt kritische Fehler, die behoben werden müssen!
                </p>
            <?php endif; ?>
        </div>

        <div class="info-box">
            <h3>🚀 Nächste Schritte</h3>
            <ol>
                <li>Behebe alle Fehler (rot markiert)</li>
                <li>Importiere das Datenbank-Schema: <code>mysql -u root -p &lt; app/config/schema.sql</code></li>
                <li>Passe die Datenbank-Credentials in <code>app/config/config.php</code> an</li>
                <li>Setze die Upload-Berechtigungen: <code>chmod 755 app/public/memes/uploads</code></li>
                <li>Aktiviere mod_rewrite: <code>sudo a2enmod rewrite && sudo systemctl restart apache2</code></li>
                <li>Teste die Anwendung unter <a href="/">mtblove.com</a></li>
                <li><strong>Lösche diese Datei nach erfolgreichem Setup!</strong></li>
            </ol>
        </div>

        <div class="info-box">
            <h3>📖 Demo-Login</h3>
            <p>Nach dem Import des Schemas kannst du dich mit folgenden Daten einloggen:</p>
            <ul>
                <li><strong>E-Mail:</strong> admin@example.com</li>
                <li><strong>Passwort:</strong> admin123</li>
            </ul>
            <p style="color: #dc3545;"><strong>Ändere das Passwort in Produktion!</strong></p>
        </div>
    </div>
</body>
</html>
