<?php
/**
 * Check and initialize social media stats
 */

require_once __DIR__ . '/config.php';

try {
    $pdo = getDBConnection();

    // Check if social media stats exist
    $stmt = $pdo->query("
        SELECT stat_key, stat_value
        FROM stats
        WHERE stat_key IN ('instagram_followers', 'tiktok_followers')
    ");
    $existing = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    echo "Existing stats:\n";
    print_r($existing);
    echo "\n";

    // Initialize if missing
    if (!isset($existing['instagram_followers'])) {
        echo "Adding instagram_followers...\n";
        $pdo->exec("INSERT INTO stats (stat_key, stat_value) VALUES ('instagram_followers', '5420')");
    }

    if (!isset($existing['tiktok_followers'])) {
        echo "Adding tiktok_followers...\n";
        $pdo->exec("INSERT INTO stats (stat_key, stat_value) VALUES ('tiktok_followers', '3280')");
    }

    // Verify
    $stmt = $pdo->query("
        SELECT stat_key, stat_value
        FROM stats
        WHERE stat_key IN ('instagram_followers', 'tiktok_followers')
    ");
    $final = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    echo "\nFinal stats:\n";
    print_r($final);

    echo "\nDone!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
