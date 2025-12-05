<?php
/**
 * Migration script to fix duplicate /dp/ in base_url
 */

require_once __DIR__ . '/../config.php';

echo "Starting migration to fix duplicate /dp/ in base_url...\n\n";

try {
    $pdo = getDBConnection();

    // Show current values
    echo "Current base_url values:\n";
    echo "========================\n";
    $stmt = $pdo->query("SELECT country_code, base_url FROM markets ORDER BY display_order");
    $markets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($markets as $market) {
        echo "{$market['country_code']}: {$market['base_url']}\n";
    }
    echo "\n";

    // Fix any base_url that contains /dp/
    echo "Fixing base_url values...\n";
    $stmt = $pdo->prepare("
        UPDATE markets
        SET base_url = TRIM(TRAILING '/' FROM REPLACE(REPLACE(base_url, '/dp/', '/'), '/dp', ''))
        WHERE base_url LIKE '%/dp/%' OR base_url LIKE '%/dp'
    ");
    $stmt->execute();
    $rowsAffected = $stmt->rowCount();

    if ($rowsAffected > 0) {
        echo "✅ Fixed {$rowsAffected} base_url value(s)\n\n";
    } else {
        echo "ℹ️  No base_url values needed fixing\n\n";
    }

    // Show updated values
    echo "Updated base_url values:\n";
    echo "========================\n";
    $stmt = $pdo->query("SELECT country_code, base_url FROM markets ORDER BY display_order");
    $markets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($markets as $market) {
        echo "{$market['country_code']}: {$market['base_url']}\n";
    }

    echo "\n✅ Migration completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
