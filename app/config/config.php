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
ini_set('session.cookie_secure', 1); // HTTPS aktiviert für mtblove.com
session_start();

// Zeitzone setzen
date_default_timezone_set('Europe/Berlin');

// Fehlerbehandlung (in Produktion auf 0 setzen)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Datenbank-Konfiguration
define('DB_HOST', 'localhost');
define('DB_NAME', 'meme_gallery');
define('DB_USER', 'root');
define('DB_PASS', '');
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
    // TODO: Später hier besseres Error-Logging implementieren
    // TODO: Analytics-Event für DB-Fehler senden
    die('Datenbankverbindung fehlgeschlagen: ' . $e->getMessage());
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

// TODO: Später hier API-Keys für externe Services hinzufügen
// define('API_KEY', 'your-api-key');

?>
