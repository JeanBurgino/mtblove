<?php
/**
 * MTB Love - Wallpapers API
 * Handles wallpaper CRUD operations
 */

require_once __DIR__ . '/../config.php';

/**
 * Get all wallpapers
 */
function getWallpapers() {
    $pdo = getDBConnection();

    $type = $_GET['type'] ?? 'all';

    try {
        if ($type === 'all') {
            $stmt = $pdo->query("SELECT * FROM wallpapers WHERE is_active = 1 ORDER BY created_at DESC");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM wallpapers WHERE is_active = 1 AND type = :type ORDER BY created_at DESC");
            $stmt->execute(['type' => $type]);
        }

        $wallpapers = $stmt->fetchAll();
        sendJSON($wallpapers);

    } catch (PDOException $e) {
        error_log('Get wallpapers error: ' . $e->getMessage());
        sendError('Wallpapers konnten nicht geladen werden', 500);
    }
}

/**
 * Get single wallpaper
 */
function getWallpaper() {
    $id = $_GET['id'] ?? 0;

    if (!$id) {
        sendError('ID erforderlich', 400);
    }

    $pdo = getDBConnection();

    try {
        $stmt = $pdo->prepare("SELECT * FROM wallpapers WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $wallpaper = $stmt->fetch();

        if (!$wallpaper) {
            sendError('Wallpaper nicht gefunden', 404);
        }

        sendJSON($wallpaper);

    } catch (PDOException $e) {
        error_log('Get wallpaper error: ' . $e->getMessage());
        sendError('Wallpaper konnte nicht geladen werden', 500);
    }
}

/**
 * Add new wallpaper (Admin only)
 */
function addWallpaper() {
    requireAuth();

    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $style = $_POST['style'] ?? '';
    $type = $_POST['type'] ?? 'free';

    if (empty($title)) {
        sendError('Titel erforderlich', 400);
    }

    // File Upload handling
    $filename = '';
    $filepath = '';
    $filesize = 0;
    $width = 0;
    $height = 0;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];

        // Validierung
        if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
            sendError('Ungültiger Dateityp', 400);
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            sendError('Datei zu groß (max 5MB)', 400);
        }

        // Bildgröße ermitteln
        list($width, $height) = getimagesize($file['tmp_name']);

        // Dateinamen generieren
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('wallpaper_') . '.' . $extension;
        $filepath = UPLOAD_DIR . 'wallpapers/' . $filename;

        // Datei verschieben
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            sendError('Upload fehlgeschlagen', 500);
        }

        $filesize = $file['size'];
        $filepath = '/uploads/wallpapers/' . $filename;
    }

    $pdo = getDBConnection();

    try {
        $stmt = $pdo->prepare("
            INSERT INTO wallpapers (title, description, style, filename, file_path, file_size, width, height, type)
            VALUES (:title, :description, :style, :filename, :filepath, :filesize, :width, :height, :type)
        ");

        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'style' => $style,
            'filename' => $filename,
            'filepath' => $filepath,
            'filesize' => $filesize,
            'width' => $width,
            'height' => $height,
            'type' => $type
        ]);

        $id = $pdo->lastInsertId();

        sendJSON([
            'success' => true,
            'id' => $id,
            'message' => 'Wallpaper erfolgreich hinzugefügt'
        ], 201);

    } catch (PDOException $e) {
        error_log('Add wallpaper error: ' . $e->getMessage());
        sendError('Wallpaper konnte nicht hinzugefügt werden', 500);
    }
}

/**
 * Update wallpaper (Admin only)
 */
function updateWallpaper() {
    requireAuth();

    $id = $_POST['id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $style = $_POST['style'] ?? '';
    $type = $_POST['type'] ?? 'free';

    if (!$id || empty($title)) {
        sendError('ID und Titel erforderlich', 400);
    }

    $pdo = getDBConnection();

    try {
        $stmt = $pdo->prepare("
            UPDATE wallpapers
            SET title = :title, description = :description, style = :style, type = :type
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'style' => $style,
            'type' => $type
        ]);

        sendJSON([
            'success' => true,
            'message' => 'Wallpaper erfolgreich aktualisiert'
        ]);

    } catch (PDOException $e) {
        error_log('Update wallpaper error: ' . $e->getMessage());
        sendError('Wallpaper konnte nicht aktualisiert werden', 500);
    }
}

/**
 * Delete wallpaper (Admin only)
 */
function deleteWallpaper() {
    requireAuth();

    $id = $_POST['id'] ?? $_GET['id'] ?? 0;

    if (!$id) {
        sendError('ID erforderlich', 400);
    }

    $pdo = getDBConnection();

    try {
        // Soft delete
        $stmt = $pdo->prepare("UPDATE wallpapers SET is_active = 0 WHERE id = :id");
        $stmt->execute(['id' => $id]);

        sendJSON([
            'success' => true,
            'message' => 'Wallpaper erfolgreich gelöscht'
        ]);

    } catch (PDOException $e) {
        error_log('Delete wallpaper error: ' . $e->getMessage());
        sendError('Wallpaper konnte nicht gelöscht werden', 500);
    }
}

/**
 * Increment download counter
 */
function incrementDownload() {
    $id = $_POST['id'] ?? 0;

    if (!$id) {
        sendError('ID erforderlich', 400);
    }

    $pdo = getDBConnection();

    try {
        $stmt = $pdo->prepare("UPDATE wallpapers SET downloads = downloads + 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);

        sendJSON(['success' => true]);

    } catch (PDOException $e) {
        error_log('Increment download error: ' . $e->getMessage());
        sendError('Download konnte nicht gezählt werden', 500);
    }
}
