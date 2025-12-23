<?php
/**
 * MTB Love - Setup API Admin User
 * Creates the api_admin user in the database
 */

require_once __DIR__ . '/config.php';

try {
    $pdo = getDBConnection();

    // Check if api_admin already exists
    $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE username = ?");
    $stmt->execute(['api_admin']);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        echo "✓ User 'api_admin' already exists:\n";
        echo "  - ID: {$existingUser['id']}\n";
        echo "  - Username: {$existingUser['username']}\n";
        echo "  - Role: {$existingUser['role']}\n";
        echo "\nNo changes needed.\n";
        exit(0);
    }

    // Create api_admin user
    // Password: ApiAdmin123
    // Hash generated with: password_hash('ApiAdmin123', PASSWORD_BCRYPT)
    $stmt = $pdo->prepare("
        INSERT INTO users (username, password_hash, email, role, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        'api_admin',
        '$2y$12$ONJfikHadiI.1II6G2TZn.Rd1/mTFY4eofOwPWlyV/8w0cvaUC1Su',
        'api_admin@mtblove.com',
        'admin'
    ]);

    echo "✓ Successfully created user 'api_admin'\n";
    echo "  - Username: api_admin\n";
    echo "  - Password: ApiAdmin123\n";
    echo "  - Role: admin\n";
    echo "  - Email: api_admin@mtblove.com\n";
    echo "\n";
    echo "You can now use these credentials to authenticate API requests.\n";

} catch (PDOException $e) {
    echo "✗ Error creating user: " . $e->getMessage() . "\n";
    exit(1);
}
