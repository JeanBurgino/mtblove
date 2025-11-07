<?php
/**
 * Edit-Handler für Meme-Bearbeitung
 *
 * Verarbeitet AJAX- und normale Form-Updates
 * Gibt JSON-Response zurück für AJAX-Requests
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// Login-Pflicht
requireLogin();

$response = ['success' => false, 'message' => ''];

// POST-Request verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Formular-Daten abrufen
    $meme_id = intval($_POST['meme_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $is_public = isset($_POST['is_public']) ? (bool)$_POST['is_public'] : true;
    $user_id = $_SESSION['user_id'];

    // Validierung
    if ($meme_id <= 0) {
        $response['message'] = 'Ungültige Meme-ID.';
    } else {
        // Meme aktualisieren mit Hilfsfunktion
        $result = updateMeme($meme_id, $title, $description, $category, $is_public, $user_id);
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
$_SESSION['edit_result'] = $response;
redirect(BASE_URL . '/admin-center');
?>
