<?php
/**
 * Hauptkonfigurationsdatei für die Meme-Anwendung
 *
 * Diese Datei enthält alle wichtigen Konfigurationseinstellungen
 * einschließlich Datenbankverbindung, Pfade und Sicherheitseinstellungen
 */

// Session-Sicherheitseinstellungen
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// Cookie-Secure nur bei HTTPS aktivieren
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || $_SERVER['SERVER_PORT'] == 443
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

ini_set('session.cookie_secure', $is_https ? 1 : 0);
session_start();

// Zeitzone setzen
date_default_timezone_set('Europe/Berlin');

// Fehlerbehandlung (in Produktion auf 0 setzen)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Datenbank-Konfiguration
define('DB_HOST', 'localhost');
define('DB_NAME', 'mtblove');
define('DB_USER', 'mtblove_admin');
define('DB_PASS', 'W5vSzoCB1UniJpGZfQU9');
define('DB_CHARSET', 'utf8mb4');

// Anwendungspfade
define('BASE_PATH', dirname(dirname(__DIR__)));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', APP_PATH . '/public');
define('TEMPLATE_PATH', APP_PATH . '/templates');
define('UPLOAD_PATH', PUBLIC_PATH . '/memes/uploads');

// URLs
// Für Entwicklung auf localhost: 'http://localhost' oder 'http://localhost/mtblove'
// Für Produktion: 'https://mtblove.com'
define('BASE_URL', 'https://mtblove.com');
define('MEMES_URL', BASE_URL . '/memes');

// Upload-Einstellungen
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Datenbankverbindung herstellen
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Error-Logging
    error_log('Database connection failed: ' . $e->getMessage());

    // In Produktion: Generische Fehlermeldung
    if (ini_get('display_errors')) {
        die('<h1>Datenbankverbindung fehlgeschlagen</h1><p>' . htmlspecialchars($e->getMessage()) . '</p><p><strong>Prüfe:</strong></p><ul><li>MySQL-Server läuft</li><li>Datenbank existiert</li><li>Credentials in config.php korrekt</li><li>Schema wurde importiert (app/config/schema.sql)</li></ul>');
    } else {
        die('<h1>Service temporarily unavailable</h1><p>Please try again later.</p>');
    }

    // TODO: Analytics-Event für DB-Fehler senden
}

// Hilfsfunktion für sichere Redirects
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Hilfsfunktion zur Überprüfung des Login-Status
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Hilfsfunktion für geschützte Seiten
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/login');
    }
}

// TODO: Später hier Analytics-Tracking-ID konfigurieren
// define('ANALYTICS_ID', 'UA-XXXXX-Y');

// Instagram Graph API Konfiguration
// WICHTIG: Diese Werte müssen mit echten Credentials gefüllt werden
// Anleitung: https://developers.facebook.com/docs/instagram-basic-display-api/getting-started
define('INSTAGRAM_ACCESS_TOKEN', ''); // Instagram Access Token hier eintragen
define('INSTAGRAM_USER_ID', ''); // Instagram Business Account User ID hier eintragen
define('INSTAGRAM_API_VERSION', 'v18.0'); // Graph API Version

?>
