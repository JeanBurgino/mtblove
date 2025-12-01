<?php
/**
 * MTB Love - Login Test Script
 * Testet die Login-Funktionalität und Passwort-Hashes
 */

require_once __DIR__ . '/config.php';

echo "Login Test Script\n";
echo "=================\n\n";

// Test 1: Datenbankverbindung
echo "1. Testing database connection...\n";
try {
    $pdo = getDBConnection();
    echo "   ✓ Database connection successful\n\n";
} catch (Exception $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: User aus Datenbank abrufen
echo "2. Checking users in database...\n";
try {
    $stmt = $pdo->query("SELECT id, username, email, role, LEFT(password_hash, 60) as hash FROM users ORDER BY id");
    $users = $stmt->fetchAll();

    if (count($users) > 0) {
        echo "   Found " . count($users) . " user(s):\n";
        foreach ($users as $user) {
            echo "   - ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Role: {$user['role']}\n";
            echo "     Hash: {$user['hash']}\n";
        }
    } else {
        echo "   ✗ No users found in database\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 3: Passwort-Verifikation für 'admin' Benutzer
echo "3. Testing password verification for 'admin' user...\n";
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => 'admin']);
    $user = $stmt->fetch();

    if ($user) {
        echo "   User found: {$user['username']}\n";

        $testPassword = 'admin123';
        echo "   Testing password: '$testPassword'\n";

        if (password_verify($testPassword, $user['password_hash'])) {
            echo "   ✓ Password verification SUCCESSFUL\n";
            echo "   Login should work with username: 'admin' and password: 'admin123'\n";
        } else {
            echo "   ✗ Password verification FAILED\n";
            echo "   The password hash in the database does NOT match 'admin123'\n";

            // Generate correct hash
            echo "\n   Generating correct hash for 'admin123':\n";
            $correctHash = password_hash($testPassword, PASSWORD_BCRYPT);
            echo "   New hash: $correctHash\n";
            echo "\n   Run this SQL to fix:\n";
            echo "   UPDATE users SET password_hash = '$correctHash' WHERE username = 'admin';\n";
        }
    } else {
        echo "   ✗ User 'admin' not found in database\n";
        echo "\n   Run this SQL to create admin user:\n";
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        echo "   INSERT INTO users (username, password_hash, email, role) VALUES ('admin', '$hash', 'admin@mtblove.com', 'admin');\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 4: Simulierter Login-Request
echo "4. Simulating login request...\n";
try {
    $_POST['user'] = 'admin';
    $_POST['pass'] = 'admin123';

    $username = $_POST['user'] ?? $_POST['username'] ?? '';
    $password = $_POST['pass'] ?? $_POST['password'] ?? '';

    echo "   Username parameter: '$username'\n";
    echo "   Password parameter: '$password'\n";

    if (empty($username) || empty($password)) {
        echo "   ✗ Username or password empty\n\n";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if (!$user) {
            echo "   ✗ User not found\n\n";
        } elseif (!password_verify($password, $user['password_hash'])) {
            echo "   ✗ Password verification failed (401 Unauthorized)\n";
            echo "   This is the error you're seeing!\n\n";
        } else {
            echo "   ✓ Login would be SUCCESSFUL\n";
            echo "   User ID: {$user['id']}\n";
            echo "   Username: {$user['username']}\n";
            echo "   Role: {$user['role']}\n\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

echo "=================\n";
echo "Test completed\n";
