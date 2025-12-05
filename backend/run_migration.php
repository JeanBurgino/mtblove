<?php
/**
 * Migration Runner - Add UUID field to designs table
 */

require_once __DIR__ . '/config.php';

try {
    $pdo = getDBConnection();

    // Check if uuid column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM designs LIKE 'uuid'");
    if ($stmt->rowCount() > 0) {
        echo "UUID field already exists in designs table.\n";
        exit(0);
    }

    // Add uuid column
    echo "Adding uuid field to designs table...\n";
    $pdo->exec("
        ALTER TABLE `designs`
        ADD COLUMN `uuid` VARCHAR(36) NULL COMMENT 'Amazon Merch Design UUID from Edit URL' AFTER `slug`,
        ADD UNIQUE INDEX `idx_uuid` (`uuid`)
    ");

    echo "✅ Migration completed successfully!\n";
    echo "UUID field added to designs table.\n";

} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
