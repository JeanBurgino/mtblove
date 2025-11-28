<?php
/**
 * MTB Love - Statistics API
 * Handles admin dashboard statistics
 */

require_once __DIR__ . '/../config.php';

/**
 * Get statistics
 */
function getStats() {
    requireAuth();

    $pdo = getDBConnection();

    try {
        // Statistiken aus der Datenbank holen
        $stmt = $pdo->query("SELECT stat_key, stat_value FROM stats");
        $statsData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Zusätzliche berechnete Statistiken
        $stmt = $pdo->query("SELECT SUM(downloads) as total_downloads FROM wallpapers");
        $downloadsData = $stmt->fetch();

        $stmt = $pdo->query("SELECT SUM(sales_count) as total_sales FROM products");
        $salesData = $stmt->fetch();

        $stats = [
            'followers' => $statsData['total_followers'] ?? '0',
            'downloads' => $downloadsData['total_downloads'] ?? '0',
            'revenue' => number_format($statsData['total_revenue'] ?? 0, 2, ',', '.') . ' €',
            'visitors' => $statsData['total_visitors'] ?? '0',
            'total_wallpapers' => $pdo->query("SELECT COUNT(*) FROM wallpapers WHERE is_active = 1")->fetchColumn(),
            'total_products' => $pdo->query("SELECT COUNT(*) FROM products WHERE is_available = 1")->fetchColumn(),
            'total_sales' => $salesData['total_sales'] ?? '0'
        ];

        sendJSON($stats);

    } catch (PDOException $e) {
        error_log('Get stats error: ' . $e->getMessage());
        sendError('Statistiken konnten nicht geladen werden', 500);
    }
}
