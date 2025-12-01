<?php
/**
 * Test social media stats update
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/api/auth.php';

// Simulate authenticated session
startSession();
$_SESSION['authenticated'] = true;
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['role'] = 'admin';

// Simulate POST data
$_POST['instagram_followers'] = 10000;
$_POST['tiktok_followers'] = 8000;

echo "Testing social stats update...\n\n";

try {
    $instagram = $_POST['instagram_followers'] ?? null;
    $tiktok = $_POST['tiktok_followers'] ?? null;

    echo "Instagram: $instagram\n";
    echo "TikTok: $tiktok\n\n";

    $pdo = getDBConnection();
    $pdo->beginTransaction();

    // Update Instagram followers if provided
    if ($instagram !== null) {
        echo "Updating Instagram...\n";
        $stmt = $pdo->prepare("
            INSERT INTO stats (stat_key, stat_value)
            VALUES ('instagram_followers', :value)
            ON DUPLICATE KEY UPDATE stat_value = VALUES(stat_value)
        ");
        $stmt->execute(['value' => intval($instagram)]);
        echo "Instagram updated successfully!\n";
    }

    // Update TikTok followers if provided
    if ($tiktok !== null) {
        echo "Updating TikTok...\n";
        $stmt = $pdo->prepare("
            INSERT INTO stats (stat_key, stat_value)
            VALUES ('tiktok_followers', :value)
            ON DUPLICATE KEY UPDATE stat_value = VALUES(stat_value)
        ");
        $stmt->execute(['value' => intval($tiktok)]);
        echo "TikTok updated successfully!\n";
    }

    $pdo->commit();

    echo "\nTransaction committed!\n\n";

    // Verify
    $stmt = $pdo->query("
        SELECT stat_key, stat_value
        FROM stats
        WHERE stat_key IN ('instagram_followers', 'tiktok_followers')
    ");
    $result = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    echo "Current values in database:\n";
    print_r($result);

    echo "\nTest successful! ✓\n";

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
