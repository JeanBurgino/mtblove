<?php
/**
 * MTB Love - Create API User
 * Script to create a new user for API access
 *
 * Usage: php backend/create_user.php <username> <password> [role]
 * Example: php backend/create_user.php api_user SecurePass123 admin
 */

require_once __DIR__ . '/config.php';

// Parse command line arguments
$username = $argv[1] ?? null;
$password = $argv[2] ?? null;
$role = $argv[3] ?? 'editor';  // Default role is 'editor'

if (!$username || !$password) {
    echo "Usage: php backend/create_user.php <username> <password> [role]\n";
    echo "\nArguments:\n";
    echo "  username  - Username for the new user (required)\n";
    echo "  password  - Password for the new user (required)\n";
    echo "  role      - User role: 'admin' or 'editor' (default: editor)\n";
    echo "\nExample:\n";
    echo "  php backend/create_user.php api_user MySecurePassword123 admin\n";
    exit(1);
}

// Validate role
if (!in_array($role, ['admin', 'editor'])) {
    echo "Error: Role must be 'admin' or 'editor'\n";
    exit(1);
}

// Validate password strength
if (strlen($password) < 8) {
    echo "Error: Password must be at least 8 characters long\n";
    exit(1);
}

echo "Creating user...\n";
echo "Username: $username\n";
echo "Role: $role\n";

try {
    $pdo = getDBConnection();

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);

    if ($stmt->fetch()) {
        echo "\nError: User '$username' already exists!\n";
        exit(1);
    }

    // Create password hash
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Insert new user
    $stmt = $pdo->prepare("
        INSERT INTO users (username, password_hash, role, email, created_at)
        VALUES (:username, :password_hash, :role, :email, NOW())
    ");

    $stmt->execute([
        'username' => $username,
        'password_hash' => $passwordHash,
        'role' => $role,
        'email' => $username . '@mtblove.local'
    ]);

    $userId = $pdo->lastInsertId();

    echo "\n✓ User created successfully!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "User ID: $userId\n";
    echo "Username: $username\n";
    echo "Role: $role\n";
    echo "\n📝 API Authentication:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "\n1. Login to get a token:\n";
    echo "   curl -X POST http://your-domain.com/backend/api/index.php \\\n";
    echo "     -d 'action=login' \\\n";
    echo "     -d 'username=$username' \\\n";
    echo "     -d 'password=YOUR_PASSWORD'\n";
    echo "\n2. Use the token for authenticated requests:\n";
    echo "   curl -X POST http://your-domain.com/backend/api/index.php \\\n";
    echo "     -d 'action=update_social_stats' \\\n";
    echo "     -d 'instagram_followers=10000' \\\n";
    echo "     -d 'tiktok_followers=8000' \\\n";
    echo "     -d 'token=YOUR_TOKEN_FROM_LOGIN'\n";
    echo "\n";

} catch (PDOException $e) {
    echo "\nError: " . $e->getMessage() . "\n";
    exit(1);
}
