<?php
/**
 * Check database tables structure
 */

require_once __DIR__ . '/config.php';

echo "Database Tables Check\n";
echo "====================\n\n";

$pdo = getDBConnection();

// Check if users table exists
echo "1. Checking users table...\n";
try {
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    echo "   ✓ Users table exists\n";
    echo "   Columns:\n";
    foreach ($columns as $col) {
        echo "   - {$col['Field']}: {$col['Type']} {$col['Key']} {$col['Extra']}\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Check table engine
echo "2. Checking users table engine...\n";
try {
    $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'users'");
    $status = $stmt->fetch();
    if ($status) {
        echo "   Engine: {$status['Engine']}\n";
        echo "   Collation: {$status['Collation']}\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Check all tables
echo "3. All tables in database:\n";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    foreach ($tables as $table) {
        echo "   - " . $table[0] . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

echo "====================\n";
