<?php
/**
 * Delete-Handler für Meme-Löschung
 *
 * Verarbeitet AJAX- und normale Delete-Requests
 * Gibt JSON-Response zurück für AJAX-Requests
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// Login-Pflicht
requireLogin();

$response = ['success' => false, 'message' => ''];

// POST-Request verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Meme-ID abrufen
    $meme_id = intval($_POST['meme_id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    // Validierung
    if ($meme_id <= 0) {
        $response['message'] = 'Ungültige Meme-ID.';
    } else {
        // Meme löschen mit Hilfsfunktion
        $result = deleteMeme($meme_id, $user_id);
        $response = $result;
    }

} else {
    $response['message'] = 'Ungültige Anfrage.';
}

// AJAX-Request? JSON zurückgeben
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Normale Form-Submission: Redirect mit Session-Message
$_SESSION['delete_result'] = $response;
redirect(BASE_URL . '/admin-center');
?>
