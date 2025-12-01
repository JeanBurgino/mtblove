<?php
/**
 * MTB Love - Social Media Stats API
 * Handles Instagram and TikTok follower counts
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

/**
 * Get social media stats (public endpoint)
 */
function getSocialStats() {
    $pdo = getDBConnection();

    try {
        // Get social media follower counts from stats table
        $stmt = $pdo->query("
            SELECT stat_key, stat_value
            FROM stats
            WHERE stat_key IN ('instagram_followers', 'tiktok_followers')
        ");
        $statsData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $stats = [
            'instagram_followers' => $statsData['instagram_followers'] ?? 0,
            'tiktok_followers' => $statsData['tiktok_followers'] ?? 0
        ];

        sendJSON($stats);

    } catch (PDOException $e) {
        error_log('Get social stats error: ' . $e->getMessage());
        sendError('Social stats konnten nicht geladen werden', 500);
    }
}

/**
 * Update social media stats (admin only)
 */
function updateSocialStats() {
    requireAuth();

    $instagram = $_POST['instagram_followers'] ?? null;
    $tiktok = $_POST['tiktok_followers'] ?? null;

    if ($instagram === null && $tiktok === null) {
        sendError('Keine Follower-Daten angegeben', 400);
    }

    $pdo = getDBConnection();

    try {
        $pdo->beginTransaction();

        // Update Instagram followers if provided
        if ($instagram !== null) {
            $stmt = $pdo->prepare("
                INSERT INTO stats (stat_key, stat_value)
                VALUES ('instagram_followers', :value)
                ON DUPLICATE KEY UPDATE stat_value = :value
            ");
            $stmt->execute(['value' => intval($instagram)]);
        }

        // Update TikTok followers if provided
        if ($tiktok !== null) {
            $stmt = $pdo->prepare("
                INSERT INTO stats (stat_key, stat_value)
                VALUES ('tiktok_followers', :value)
                ON DUPLICATE KEY UPDATE stat_value = :value
            ");
            $stmt->execute(['value' => intval($tiktok)]);
        }

        $pdo->commit();

        sendJSON([
            'success' => true,
            'message' => 'Social Media Stats aktualisiert',
            'instagram_followers' => $instagram,
            'tiktok_followers' => $tiktok
        ]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Update social stats error: ' . $e->getMessage());
        sendError('Social stats konnten nicht aktualisiert werden', 500);
    }
}
