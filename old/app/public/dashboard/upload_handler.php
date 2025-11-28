<?php
/**
 * Upload-Handler für Meme-Upload
 *
 * Verarbeitet AJAX- und normale Form-Uploads
 * Gibt JSON-Response zurück für AJAX-Requests
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// Login-Pflicht
requireLogin();

$response = ['success' => false, 'message' => '', 'meme_id' => null];

// POST-Request verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Formular-Daten abrufen
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $is_public = isset($_POST['is_public']) ? (bool)$_POST['is_public'] : true;
    $user_id = $_SESSION['user_id'];

    // Datei-Upload prüfen
    if (!isset($_FILES['meme_file']) || $_FILES['meme_file']['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'Bitte wähle eine Datei aus.';

        // Error-Codes behandeln
        if (isset($_FILES['meme_file']['error'])) {
            switch ($_FILES['meme_file']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $response['message'] = 'Datei ist zu groß.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $response['message'] = 'Keine Datei hochgeladen.';
                    break;
                default:
                    $response['message'] = 'Upload-Fehler aufgetreten.';
            }
        }
    } else {
        // Meme speichern mit Hilfsfunktion
        $result = saveMeme($_FILES['meme_file'], $title, $description, $category, $user_id, $is_public);
        $response = $result;

        // Analytics-Event tracken
        if ($result['success']) {
            trackEvent('meme_uploaded', [
                'meme_id' => $result['meme_id'],
                'user_id' => $user_id,
                'has_title' => !empty($title),
                'has_description' => !empty($description),
                'has_category' => !empty($category),
                'is_public' => $is_public
            ]);
        }
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
$_SESSION['upload_result'] = $response;
redirect(BASE_URL . '/admin-center');
?>
