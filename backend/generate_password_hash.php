<?php
/**
 * MTB Love - Password Hash Generator
 * Generates a bcrypt hash for use in SQL user creation
 *
 * Usage: php backend/generate_password_hash.php <password>
 * Example: php backend/generate_password_hash.php MySecurePassword123
 */

if ($argc < 2) {
    echo "Password Hash Generator\n";
    echo "======================\n\n";
    echo "Usage: php backend/generate_password_hash.php <password>\n";
    echo "Example: php backend/generate_password_hash.php MySecurePassword123\n\n";
    exit(1);
}

$password = $argv[1];

// Validate password strength
if (strlen($password) < 8) {
    echo "Error: Password must be at least 8 characters long\n";
    exit(1);
}

// Generate bcrypt hash
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Password Hash Generated\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "Password: $password\n";
echo "Hash:     $hash\n\n";

echo "SQL INSERT Example:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "INSERT INTO users (username, password_hash, email, role, created_at)\n";
echo "VALUES (\n";
echo "    'your_username',\n";
echo "    '$hash',\n";
echo "    'your_email@example.com',\n";
echo "    'admin',\n";
echo "    NOW()\n";
echo ");\n\n";

echo "cURL Login Test:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "curl -X POST http://localhost/backend/api/index.php \\\n";
echo "  -d \"action=login\" \\\n";
echo "  -d \"username=your_username\" \\\n";
echo "  -d \"password=$password\"\n\n";

// Verify the hash works
if (password_verify($password, $hash)) {
    echo "✓ Hash verified successfully\n";
} else {
    echo "✗ Hash verification failed\n";
}

echo "\n";
