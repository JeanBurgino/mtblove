<?php
/**
 * Lokale Entwicklungs-Konfiguration
 *
 * Kopiere diese Datei zu config_local.php und passe die Werte an
 * config_local.php wird in .gitignore ignoriert
 *
 * Diese Datei überschreibt die Werte aus config.php für lokale Entwicklung
 */

// Für lokale Entwicklung
define('BASE_URL_LOCAL', 'http://localhost/mtblove');

// Session-Cookie-Secure für HTTP deaktivieren
ini_set('session.cookie_secure', 0);

// Error-Reporting für Entwicklung
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Lokale Datenbank (falls abweichend)
// define('DB_HOST_LOCAL', 'localhost');
// define('DB_NAME_LOCAL', 'meme_gallery_dev');
// define('DB_USER_LOCAL', 'root');
// define('DB_PASS_LOCAL', '');

?>
