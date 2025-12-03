<?php
/**
 * Run the variant mockup image migration
 */

require_once __DIR__ . '/config.php';

try {
    $pdo = getDBConnection();

    // Read the migration file
    $migrationSQL = file_get_contents(__DIR__ . '/migrations/add_variant_mockup_image.sql');

    // Split by semicolon to execute each statement separately
    $statements = array_filter(
        array_map('trim', explode(';', $migrationSQL)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );

    echo "Running migration: add_variant_mockup_image.sql\n\n";

    foreach ($statements as $statement) {
        if (empty(trim($statement))) continue;

        echo "Executing: " . substr($statement, 0, 100) . "...\n";
        $pdo->exec($statement);
        echo "✓ Success\n\n";
    }

    echo "Migration completed successfully!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
