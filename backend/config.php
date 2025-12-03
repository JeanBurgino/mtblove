<?php
/**
 * MTB Love - Konfigurationsdatei
 * Zentrale Konfiguration für Datenbank und Anwendung
 */

// Fehlerbehandlung
error_reporting(E_ALL);
ini_set('display_errors', 0); // In Produktion auf 0 setzen
ini_set('log_errors', 1);

// Datenbank-Konfiguration
define('DB_HOST', 'localhost');
define('DB_NAME', 'mtblove');
define('DB_USER', 'mtblove_admin');
define('DB_PASS', 'W5vSzoCB1UniJpGZfQU9');
define('DB_CHARSET', 'utf8mb4');

// Anwendungs-Konfiguration
define('APP_NAME', 'MTB Love');
define('APP_URL', 'http://localhost');
define('API_VERSION', '1.0');

// Session-Konfiguration
define('SESSION_LIFETIME', 3600); // 1 Stunde
define('SESSION_NAME', 'mtblove_session');

// Upload-Konfiguration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MOCKUP_PATH', '/uploads/mockups/'); // Pfad für Varianten-Mockup-Bilder (relativ zur Webroot)
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Admin-Konfiguration
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', password_hash('admin123', PASSWORD_BCRYPT)); // Später ändern!

// CORS-Konfiguration
define('CORS_ALLOWED_ORIGINS', '*'); // In Produktion spezifische Domains angeben

// Timezone
date_default_timezone_set('Europe/Berlin');

/**
 * Datenbankverbindung erstellen
 * @return PDO
 */
function getDBConnection() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Datenbankverbindungsfehler: " . $e->getMessage());
            http_response_code(500);
            die(json_encode(['error' => 'Datenbankverbindung fehlgeschlagen']));
        }
    }

    return $pdo;
}

/**
 * JSON Response senden
 * @param mixed $data
 * @param int $statusCode
 */
function sendJSON($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Origin: ' . CORS_ALLOWED_ORIGINS);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Fehler-Response senden
 * @param string $message
 * @param int $statusCode
 */
function sendError($message, $statusCode = 400) {
    sendJSON(['error' => $message], $statusCode);
}

/**
 * Session starten
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
    }
}

/**
 * Prüfen ob Benutzer eingeloggt ist
 * @return bool
 */
function isAuthenticated() {
    startSession();
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

/**
 * Upload-Verzeichnis erstellen falls nicht vorhanden
 */
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
if (!is_dir(UPLOAD_DIR . 'wallpapers/')) {
    mkdir(UPLOAD_DIR . 'wallpapers/', 0755, true);
}
if (!is_dir(UPLOAD_DIR . 'products/')) {
    mkdir(UPLOAD_DIR . 'products/', 0755, true);
}
if (!is_dir(UPLOAD_DIR . 'mockups/')) {
    mkdir(UPLOAD_DIR . 'mockups/', 0755, true);
}
