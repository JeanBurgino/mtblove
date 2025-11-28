<?php
/**
 * Hilfsfunktionen für die Meme-Gallery Anwendung
 *
 * Sammlung nützlicher Funktionen für:
 * - File Upload/Validation
 * - Bild-Verarbeitung
 * - Sicherheit
 * - Analytics
 */

/**
 * Validiert hochgeladene Dateien
 *
 * @param array $file Das $_FILES Array Element
 * @return array ['success' => bool, 'message' => string, 'file_info' => array]
 */
function validateUpload($file) {
    $result = ['success' => false, 'message' => '', 'file_info' => []];

    // Prüfen ob Datei hochgeladen wurde
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $result['message'] = 'Keine Datei hochgeladen.';
        return $result;
    }

    // Dateigröße prüfen
    if ($file['size'] > MAX_FILE_SIZE) {
        $result['message'] = 'Datei ist zu groß (max. ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB).';
        return $result;
    }

    // Dateiendung prüfen
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        $result['message'] = 'Dateityp nicht erlaubt. Erlaubt: ' . implode(', ', ALLOWED_EXTENSIONS);
        return $result;
    }

    // MIME-Type prüfen
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime_type, $allowed_mimes)) {
        $result['message'] = 'Ungültiger Dateityp.';
        return $result;
    }

    // Bildabmessungen auslesen
    $image_info = getimagesize($file['tmp_name']);
    if (!$image_info) {
        $result['message'] = 'Ungültige Bilddatei.';
        return $result;
    }

    $result['success'] = true;
    $result['message'] = 'Datei ist gültig.';
    $result['file_info'] = [
        'extension' => $extension,
        'mime_type' => $mime_type,
        'width' => $image_info[0],
        'height' => $image_info[1],
        'size' => $file['size']
    ];

    return $result;
}

/**
 * Speichert ein hochgeladenes Meme
 *
 * @param array $file Das $_FILES Array Element
 * @param string $title Titel des Memes
 * @param string $description Beschreibung
 * @param string $category Kategorie
 * @param int $user_id User-ID des Uploaders
 * @param bool $is_public Öffentlich sichtbar (default: true)
 * @return array ['success' => bool, 'message' => string, 'meme_id' => int]
 */
function saveMeme($file, $title, $description, $category, $user_id, $is_public = true) {
    global $pdo;

    $result = ['success' => false, 'message' => '', 'meme_id' => null];

    // Datei validieren
    $validation = validateUpload($file);
    if (!$validation['success']) {
        return $validation;
    }

    $file_info = $validation['file_info'];

    // Einzigartigen Dateinamen generieren
    $unique_name = uniqid('meme_', true) . '.' . $file_info['extension'];
    $upload_dir = UPLOAD_PATH;

    // Upload-Verzeichnis erstellen falls nicht vorhanden
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $target_path = $upload_dir . '/' . $unique_name;
    $relative_path = '/memes/uploads/' . $unique_name; // Pfad relativ zu BASE_URL

    // Datei verschieben
    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        $result['message'] = 'Fehler beim Speichern der Datei.';
        return $result;
    }

    // TODO: Thumbnail erstellen für schnellere Ladezeiten
    // createThumbnail($target_path, $upload_dir . '/thumb_' . $unique_name);

    // In Datenbank speichern
    try {
        $stmt = $pdo->prepare("
            INSERT INTO memes (title, image_url, description, category, created_by, is_public)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $title,
            $relative_path,
            $description,
            $category,
            $user_id,
            $is_public ? 1 : 0
        ]);

        $meme_id = $pdo->lastInsertId();

        $result['success'] = true;
        $result['message'] = 'Meme erfolgreich hochgeladen!';
        $result['meme_id'] = $meme_id;

        // TODO: Analytics-Event senden
        // trackEvent('meme_uploaded', ['meme_id' => $meme_id, 'user_id' => $user_id]);

    } catch (PDOException $e) {
        // Bei Fehler Datei wieder löschen
        @unlink($target_path);
        $result['message'] = 'Datenbankfehler: ' . $e->getMessage();
        error_log('Meme save error: ' . $e->getMessage());
    }

    return $result;
}

/**
 * Aktualisiert ein Meme
 *
 * @param int $meme_id ID des Memes
 * @param string $title Titel des Memes
 * @param string $description Beschreibung
 * @param string $category Kategorie
 * @param bool $is_public Öffentlich sichtbar
 * @param int $user_id User-ID (nur eigene Memes oder Admin)
 * @return array ['success' => bool, 'message' => string]
 */
function updateMeme($meme_id, $title, $description, $category, $is_public, $user_id) {
    global $pdo;

    $result = ['success' => false, 'message' => ''];

    try {
        // Meme-Daten abrufen
        $stmt = $pdo->prepare("SELECT * FROM memes WHERE id = ?");
        $stmt->execute([$meme_id]);
        $meme = $stmt->fetch();

        if (!$meme) {
            $result['message'] = 'Meme nicht gefunden.';
            return $result;
        }

        // Berechtigung prüfen (nur eigene Memes oder Admin)
        if ($meme['created_by'] != $user_id && $_SESSION['role'] !== 'admin') {
            $result['message'] = 'Keine Berechtigung.';
            return $result;
        }

        // Meme aktualisieren
        $stmt = $pdo->prepare("
            UPDATE memes
            SET title = ?, description = ?, category = ?, is_public = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $title,
            $description,
            $category,
            $is_public ? 1 : 0,
            $meme_id
        ]);

        $result['success'] = true;
        $result['message'] = 'Meme erfolgreich aktualisiert.';

        // Analytics-Event tracken
        trackEvent('meme_updated', [
            'meme_id' => $meme_id,
            'user_id' => $user_id
        ]);

    } catch (PDOException $e) {
        $result['message'] = 'Fehler beim Aktualisieren: ' . $e->getMessage();
        error_log('Meme update error: ' . $e->getMessage());
    }

    return $result;
}

/**
 * Löscht ein Meme
 *
 * @param int $meme_id ID des Memes
 * @param int $user_id User-ID (nur eigene Memes oder Admin)
 * @return array ['success' => bool, 'message' => string]
 */
function deleteMeme($meme_id, $user_id) {
    global $pdo;

    $result = ['success' => false, 'message' => ''];

    try {
        // Meme-Daten abrufen
        $stmt = $pdo->prepare("SELECT * FROM memes WHERE id = ?");
        $stmt->execute([$meme_id]);
        $meme = $stmt->fetch();

        if (!$meme) {
            $result['message'] = 'Meme nicht gefunden.';
            return $result;
        }

        // Berechtigung prüfen (nur eigene Memes oder Admin)
        if ($meme['created_by'] != $user_id && $_SESSION['role'] !== 'admin') {
            $result['message'] = 'Keine Berechtigung.';
            return $result;
        }

        // Datei löschen
        $file_path = UPLOAD_PATH . '/' . basename($meme['image_url']);
        if (file_exists($file_path)) {
            @unlink($file_path);
        }

        // TODO: Thumbnail löschen
        // @unlink(str_replace('/uploads/', '/uploads/thumb_', $file_path));

        // DB-Eintrag löschen
        $stmt = $pdo->prepare("DELETE FROM memes WHERE id = ?");
        $stmt->execute([$meme_id]);

        $result['success'] = true;
        $result['message'] = 'Meme erfolgreich gelöscht.';

        // Analytics-Event tracken
        trackEvent('meme_deleted', [
            'meme_id' => $meme_id,
            'user_id' => $user_id
        ]);

    } catch (PDOException $e) {
        $result['message'] = 'Fehler beim Löschen: ' . $e->getMessage();
        error_log('Meme delete error: ' . $e->getMessage());
    }

    return $result;
}

/**
 * Sanitize User Input
 *
 * @param string $input
 * @return string
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generiert einen sicheren Slug aus einem String
 *
 * @param string $text
 * @return string
 */
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Formatiert eine Dateigröße für die Anzeige
 *
 * @param int $bytes
 * @return string
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Sendet ein Analytics-Event
 * TODO: Implementierung mit echtem Analytics-Service
 *
 * @param string $event_type
 * @param array $data
 */
function trackEvent($event_type, $data = []) {
    global $pdo;

    try {
        // Event in DB speichern
        $stmt = $pdo->prepare("
            INSERT INTO analytics_events (event_type, event_data, user_id, ip_address, user_agent, referer)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $event_type,
            json_encode($data),
            $_SESSION['user_id'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $_SERVER['HTTP_REFERER'] ?? null
        ]);

        // TODO: An externe Analytics-API senden (Google Analytics, Matomo, etc.)

    } catch (PDOException $e) {
        error_log('Analytics tracking error: ' . $e->getMessage());
    }
}

/**
 * Generiert Breadcrumbs für Navigation
 *
 * @param array $items [['title' => 'Home', 'url' => '/'], ...]
 * @return string HTML
 */
function generateBreadcrumbs($items) {
    $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';

    foreach ($items as $index => $item) {
        $is_last = ($index === count($items) - 1);

        if ($is_last) {
            $html .= '<li class="breadcrumb-item active" aria-current="page">' .
                     htmlspecialchars($item['title']) . '</li>';
        } else {
            $html .= '<li class="breadcrumb-item">' .
                     '<a href="' . htmlspecialchars($item['url']) . '">' .
                     htmlspecialchars($item['title']) . '</a></li>';
        }
    }

    $html .= '</ol></nav>';
    return $html;
}

/**
 * Erstellt ein Thumbnail
 * TODO: Implementieren für bessere Performance
 *
 * @param string $source_path
 * @param string $target_path
 * @param int $max_width
 * @param int $max_height
 */
function createThumbnail($source_path, $target_path, $max_width = 300, $max_height = 300) {
    // TODO: GD oder ImageMagick verwenden
    // Thumbnail-Generierung für schnellere Ladezeiten
}

/**
 * Rate Limiting prüfen
 * TODO: Implementieren für Sicherheit
 *
 * @param string $action
 * @param int $max_attempts
 * @param int $time_window Sekunden
 * @return bool
 */
function checkRateLimit($action, $max_attempts = 5, $time_window = 3600) {
    // TODO: Rate-Limiting implementieren (z.B. mit Redis oder DB)
    // Verhindert Spam und Brute-Force-Attacken
    return true;
}

?>
