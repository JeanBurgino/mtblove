<?php
/**
 * Logout-Script
 *
 * Beendet die Benutzersitzung sicher und leitet zur Startseite weiter
 */

require_once '../config/config.php';

// TODO: Logout-Event für Analytics tracken
// if (isLoggedIn()) {
//     trackEvent('user_logout', ['user_id' => $_SESSION['user_id']]);
// }

// Session-Daten löschen
$_SESSION = array();

// Session-Cookie löschen
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Session zerstören
session_destroy();

// Neue Session starten für Flash-Message
session_start();
$_SESSION['logout_message'] = 'Sie wurden erfolgreich abgemeldet.';

// Zur Startseite weiterleiten
redirect(BASE_URL . '/app/public/index.php');
?>
