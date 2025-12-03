<?php
/**
 * Migration Script: Fix variant mockup image paths
 * Removes '/uploads/mockups/' prefix from variants.mockup_image_url
 * Run this script once to fix existing data
 */

require_once __DIR__ . '/../config.php';

echo "Starting migration: Fix variant mockup image paths...\n\n";

try {
    $pdo = getDBConnection();

    // Check how many records need fixing
    $stmt = $pdo->query("
        SELECT COUNT(*) as count
        FROM variants
        WHERE mockup_image_url LIKE '/uploads/mockups/%'
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $affectedCount = $result['count'];

    echo "Found {$affectedCount} variants with full path in mockup_image_url\n";

    if ($affectedCount > 0) {
        // Show some examples before
        echo "\nExamples BEFORE migration:\n";
        $stmt = $pdo->query("
            SELECT id, product_type_id, mockup_image_url
            FROM variants
            WHERE mockup_image_url LIKE '/uploads/mockups/%'
            LIMIT 3
        ");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  ID {$row['id']}: {$row['mockup_image_url']}\n";
        }

        // Perform the migration
        echo "\nExecuting migration...\n";
        $stmt = $pdo->exec("
            UPDATE variants
            SET mockup_image_url = REPLACE(mockup_image_url, '/uploads/mockups/', '')
            WHERE mockup_image_url LIKE '/uploads/mockups/%'
        ");

        echo "✓ Updated {$stmt} records\n";

        // Show examples after
        echo "\nExamples AFTER migration:\n";
        $stmt = $pdo->query("
            SELECT id, product_type_id, mockup_image_url
            FROM variants
            WHERE mockup_image_url IS NOT NULL
            LIMIT 3
        ");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  ID {$row['id']}: {$row['mockup_image_url']}\n";
        }

        echo "\n✓ Migration completed successfully!\n";
    } else {
        echo "No records need to be updated.\n";
    }

} catch (PDOException $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
