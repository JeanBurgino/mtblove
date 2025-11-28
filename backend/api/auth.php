<?php
/**
 * MTB Love - Authentication API
 * Handles login, logout, and session management
 */

require_once __DIR__ . '/../config.php';

/**
 * Login Handler
 */
function handleLogin() {
    $username = $_POST['user'] ?? $_POST['username'] ?? '';
    $password = $_POST['pass'] ?? $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        sendError('Benutzername und Passwort erforderlich', 400);
    }

    $pdo = getDBConnection();

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            sendError('Ungültige Anmeldedaten', 401);
        }

        // Session erstellen
        startSession();
        $_SESSION['authenticated'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Token generieren
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);

        // Token in DB speichern
        $stmt = $pdo->prepare("
            INSERT INTO sessions (user_id, token, ip_address, user_agent, expires_at)
            VALUES (:user_id, :token, :ip, :ua, :expires)
        ");

        $stmt->execute([
            'user_id' => $user['id'],
            'token' => $token,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'expires' => $expiresAt
        ]);

        // Last Login aktualisieren
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
        $stmt->execute(['id' => $user['id']]);

        sendJSON([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ]
        ]);

    } catch (PDOException $e) {
        error_log('Login error: ' . $e->getMessage());
        sendError('Login fehlgeschlagen', 500);
    }
}

/**
 * Logout Handler
 */
function handleLogout() {
    startSession();

    $token = $_POST['token'] ?? '';

    if ($token) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE token = :token");
        $stmt->execute(['token' => $token]);
    }

    session_destroy();

    sendJSON(['success' => true, 'message' => 'Erfolgreich abgemeldet']);
}

/**
 * Check Authentication
 */
function handleCheckAuth() {
    $token = $_POST['token'] ?? $_GET['token'] ?? '';

    if (empty($token)) {
        sendJSON(['authenticated' => false]);
    }

    $pdo = getDBConnection();

    try {
        $stmt = $pdo->prepare("
            SELECT s.*, u.username, u.role
            FROM sessions s
            JOIN users u ON s.user_id = u.id
            WHERE s.token = :token AND s.expires_at > NOW()
            LIMIT 1
        ");

        $stmt->execute(['token' => $token]);
        $session = $stmt->fetch();

        if ($session) {
            sendJSON([
                'authenticated' => true,
                'user' => [
                    'id' => $session['user_id'],
                    'username' => $session['username'],
                    'role' => $session['role']
                ]
            ]);
        } else {
            sendJSON(['authenticated' => false]);
        }

    } catch (PDOException $e) {
        error_log('Auth check error: ' . $e->getMessage());
        sendJSON(['authenticated' => false]);
    }
}

/**
 * Check if user is authenticated (for protected routes)
 */
function requireAuth() {
    if (!isAuthenticated()) {
        sendError('Nicht autorisiert', 401);
    }
}
