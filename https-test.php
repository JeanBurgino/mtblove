<?php
/**
 * HTTPS-Test nach SSL-Installation
 *
 * Diese Datei testet, ob HTTPS korrekt funktioniert
 * Aufruf: https://mtblove.com/https-test.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTTPS Test - mtblove.com</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #28a745;
            padding-bottom: 10px;
        }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #007bff; margin: 15px 0; }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        table td:first-child {
            font-weight: bold;
            width: 40%;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>🔒 HTTPS-Test für mtblove.com</h1>

        <?php
        // HTTPS-Status prüfen
        $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        ?>

        <div class="info">
            <?php if ($is_https): ?>
                <span class="success">✓ Diese Seite wird über HTTPS aufgerufen</span>
            <?php else: ?>
                <span class="error">✗ Diese Seite wird über HTTP aufgerufen</span>
            <?php endif; ?>
        </div>

        <h2>Server-Informationen</h2>
        <table>
            <tr>
                <td>HTTPS Status:</td>
                <td><?php echo isset($_SERVER['HTTPS']) ? htmlspecialchars($_SERVER['HTTPS']) : 'nicht gesetzt'; ?></td>
            </tr>
            <tr>
                <td>Server Port:</td>
                <td><?php echo htmlspecialchars($_SERVER['SERVER_PORT']); ?></td>
            </tr>
            <tr>
                <td>Server Name:</td>
                <td><?php echo htmlspecialchars($_SERVER['SERVER_NAME']); ?></td>
            </tr>
            <tr>
                <td>Document Root:</td>
                <td><?php echo htmlspecialchars($_SERVER['DOCUMENT_ROOT']); ?></td>
            </tr>
            <tr>
                <td>Request URI:</td>
                <td><?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?></td>
            </tr>
            <tr>
                <td>PHP Version:</td>
                <td><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <td>X-Forwarded-Proto:</td>
                <td><?php echo isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? htmlspecialchars($_SERVER['HTTP_X_FORWARDED_PROTO']) : 'nicht gesetzt'; ?></td>
            </tr>
        </table>

        <h2>Datei-Struktur</h2>
        <?php
        $files_to_check = [
            'index.php',
            'app/config/config.php',
            'app/public/index.php',
            '.htaccess'
        ];

        echo "<table>";
        foreach ($files_to_check as $file) {
            $full_path = __DIR__ . '/' . $file;
            $exists = file_exists($full_path);
            echo "<tr>";
            echo "<td>" . htmlspecialchars($file) . "</td>";
            echo "<td>";
            if ($exists) {
                echo "<span class='success'>✓ Existiert</span>";
                if (is_readable($full_path)) {
                    echo " (lesbar)";
                } else {
                    echo " <span class='error'>(nicht lesbar!)</span>";
                }
            } else {
                echo "<span class='error'>✗ Fehlt</span>";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        ?>

        <h2>Apache-Module</h2>
        <?php if (function_exists('apache_get_modules')): ?>
            <table>
                <?php
                $required_modules = ['mod_rewrite', 'mod_ssl', 'mod_headers'];
                $loaded_modules = apache_get_modules();

                foreach ($required_modules as $module) {
                    echo "<tr>";
                    echo "<td>$module</td>";
                    echo "<td>";
                    if (in_array($module, $loaded_modules)) {
                        echo "<span class='success'>✓ Geladen</span>";
                    } else {
                        echo "<span class='error'>✗ Nicht geladen</span>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        <?php else: ?>
            <p>Apache-Module-Info nicht verfügbar (CGI/FPM-Modus)</p>
        <?php endif; ?>

        <div class="info">
            <h3>📋 Nächste Schritte:</h3>
            <ol>
                <li>Wenn diese Seite lädt, funktioniert PHP und HTTPS grundsätzlich</li>
                <li>Prüfe die Apache VirtualHost-Konfiguration für Port 443</li>
                <li>Stelle sicher, dass DocumentRoot auf <code><?php echo __DIR__; ?></code> zeigt</li>
                <li>Überprüfe <code>.htaccess</code> auf problematische Regeln</li>
                <li>Prüfe Apache Error-Log: <code>sudo tail -f /var/log/apache2/error.log</code></li>
            </ol>
        </div>

        <div class="info">
            <h3>🔗 Test-Links:</h3>
            <ul>
                <li><a href="/">Startseite (mit Redirects)</a></li>
                <li><a href="/test.php">PHP-Info Test</a></li>
                <li><a href="/setup-check.php">Setup-Check</a></li>
                <li><a href="/app/public/index.php">Direkt zur Gallery (ohne Redirect)</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
