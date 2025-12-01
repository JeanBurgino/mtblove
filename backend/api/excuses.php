<?php
/**
 * MTB Love - Excuses API
 * Handles MTB excuse generator
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

/**
 * Get random excuse
 */
function getRandomExcuse() {
    $pdo = getDBConnection();

    try {
        $stmt = $pdo->query("SELECT text FROM excuses ORDER BY RAND() LIMIT 1");
        $excuse = $stmt->fetch();

        if (!$excuse) {
            sendJSON(['text' => 'Keine Ausrede gefunden!']);
        }

        sendJSON($excuse);

    } catch (PDOException $e) {
        error_log('Get random excuse error: ' . $e->getMessage());
        sendError('Ausrede konnte nicht geladen werden', 500);
    }
}

/**
 * Get all excuses (Admin only)
 */
function getAllExcuses() {
    requireAuth();

    $pdo = getDBConnection();

    try {
        $stmt = $pdo->query("SELECT * FROM excuses ORDER BY created_at DESC");
        $excuses = $stmt->fetchAll();

        sendJSON($excuses);

    } catch (PDOException $e) {
        error_log('Get all excuses error: ' . $e->getMessage());
        sendError('Ausreden konnten nicht geladen werden', 500);
    }
}
