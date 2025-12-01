<?php
/**
 * MTB Love - Complete Login Flow Debug
 * Tests the entire login process step by step
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';

echo "Complete Login Flow Debug\n";
echo "==========================\n\n";

$username = 'admin';
$password = 'admin123';

// Step 1: Database Connection
echo "Step 1: Database Connection\n";
try {
    $pdo = getDBConnection();
    echo "   ✓ Connected\n\n";
} catch (Exception $e) {
    echo "   ✗ Failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Step 2: User Lookup
echo "Step 2: User Lookup\n";
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user) {
        echo "   ✓ User found: {$user['username']} (ID: {$user['id']})\n\n";
    } else {
        echo "   ✗ User not found\n\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Step 3: Password Verification
echo "Step 3: Password Verification\n";
if (password_verify($password, $user['password_hash'])) {
    echo "   ✓ Password correct\n\n";
} else {
    echo "   ✗ Password incorrect\n\n";
    exit(1);
}

// Step 4: Session Start
echo "Step 4: Session Start\n";
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
        echo "   ✓ Session started: " . session_id() . "\n\n";
    } else {
        echo "   ✓ Session already active: " . session_id() . "\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Step 5: Set Session Variables
echo "Step 5: Set Session Variables\n";
try {
    $_SESSION['authenticated'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    echo "   ✓ Session variables set\n";
    echo "   - authenticated: " . ($_SESSION['authenticated'] ? 'true' : 'false') . "\n";
    echo "   - user_id: {$_SESSION['user_id']}\n";
    echo "   - username: {$_SESSION['username']}\n";
    echo "   - role: {$_SESSION['role']}\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Step 6: Generate Token
echo "Step 6: Generate Token\n";
try {
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
    echo "   ✓ Token generated: " . substr($token, 0, 16) . "...\n";
    echo "   - Expires at: $expiresAt\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Step 7: Check sessions table
echo "Step 7: Check sessions table structure\n";
try {
    $stmt = $pdo->query("DESCRIBE sessions");
    $columns = $stmt->fetchAll();
    echo "   ✓ Sessions table exists\n";
    echo "   Columns:\n";
    foreach ($columns as $col) {
        echo "   - {$col['Field']} ({$col['Type']})\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    echo "   Sessions table might not exist!\n\n";
}

// Step 8: Insert Session Token
echo "Step 8: Insert Session Token\n";
try {
    $stmt = $pdo->prepare("
        INSERT INTO sessions (user_id, token, ip_address, user_agent, expires_at)
        VALUES (:user_id, :token, :ip, :ua, :expires)
    ");

    $result = $stmt->execute([
        'user_id' => $user['id'],
        'token' => $token,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'expires' => $expiresAt
    ]);

    if ($result) {
        echo "   ✓ Session token inserted (ID: " . $pdo->lastInsertId() . ")\n\n";
    } else {
        echo "   ✗ Insert failed\n\n";
    }
} catch (PDOException $e) {
    echo "   ✗ Database Error: " . $e->getMessage() . "\n";
    echo "   Error Code: " . $e->getCode() . "\n\n";
    echo "   This is likely causing your 500 error!\n\n";
}

// Step 9: Update Last Login
echo "Step 9: Update Last Login\n";
try {
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
    $stmt->execute(['id' => $user['id']]);
    echo "   ✓ Last login updated\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Step 10: Build Response
echo "Step 10: Build Response\n";
try {
    $response = [
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ]
    ];

    echo "   ✓ Response built:\n";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

echo "==========================\n";
echo "Debug completed\n";
echo "\nIf all steps passed, the login should work!\n";
echo "If any step failed, that's where the 500 error is coming from.\n";
