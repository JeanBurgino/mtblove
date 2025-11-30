<?php
/**
 * MTB Love - Initialize Social Media Stats
 * Run this script once to initialize Instagram and TikTok follower counts
 *
 * Usage: php backend/init_social_stats.php
 */

require_once __DIR__ . '/config.php';

echo "Initializing social media stats...\n";

try {
    $pdo = getDBConnection();

    // Initialize Instagram followers (example: 5420)
    $stmt = $pdo->prepare("
        INSERT INTO stats (stat_key, stat_value)
        VALUES ('instagram_followers', '5420')
        ON DUPLICATE KEY UPDATE stat_value = '5420'
    ");
    $stmt->execute();
    echo "✓ Instagram followers initialized to 5420\n";

    // Initialize TikTok followers (example: 3280)
    $stmt = $pdo->prepare("
        INSERT INTO stats (stat_key, stat_value)
        VALUES ('tiktok_followers', '3280')
        ON DUPLICATE KEY UPDATE stat_value = '3280'
    ");
    $stmt->execute();
    echo "✓ TikTok followers initialized to 3280\n";

    echo "\nSuccess! Social media stats initialized.\n";
    echo "You can update these values through the API endpoint 'update_social_stats' (requires authentication).\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
