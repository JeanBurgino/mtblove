<?php
/**
 * MTB Love - User Management API
 * Handles CRUD operations for users
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

/**
 * Get all users
 */
function getUsers() {
    requireAuth();

    $pdo = getDBConnection();

    try {
        $stmt = $pdo->query("
            SELECT id, username, email, role, created_at, last_login
            FROM users
            ORDER BY created_at DESC
        ");

        $users = $stmt->fetchAll();
        sendJSON($users);
    } catch (PDOException $e) {
        error_log('Get users error: ' . $e->getMessage());
        sendError('Fehler beim Laden der Benutzer', 500);
    }
}

/**
 * Get single user
 */
function getUser() {
    requireAuth();

    $userId = $_GET['id'] ?? $_POST['id'] ?? null;

    if (!$userId) {
        sendError('Benutzer-ID erforderlich', 400);
    }

    $pdo = getDBConnection();

    try {
        $stmt = $pdo->prepare("
            SELECT id, username, email, role, created_at, last_login
            FROM users
            WHERE id = :id
        ");

        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        if (!$user) {
            sendError('Benutzer nicht gefunden', 404);
        }

        sendJSON($user);
    } catch (PDOException $e) {
        error_log('Get user error: ' . $e->getMessage());
        sendError('Fehler beim Laden des Benutzers', 500);
    }
}

/**
 * Create new user
 */
function createUser() {
    requireAuth();

    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'editor';

    if (empty($username) || empty($email) || empty($password)) {
        sendError('Benutzername, E-Mail und Passwort erforderlich', 400);
    }

    if (!in_array($role, ['admin', 'editor'])) {
        sendError('Ungültige Rolle', 400);
    }

    $pdo = getDBConnection();

    try {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);

        if ($stmt->fetch()) {
            sendError('Benutzername bereits vergeben', 400);
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ($stmt->fetch()) {
            sendError('E-Mail bereits vergeben', 400);
        }

        // Create user
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("
            INSERT INTO users (username, password_hash, email, role)
            VALUES (:username, :password_hash, :email, :role)
        ");

        $stmt->execute([
            'username' => $username,
            'password_hash' => $passwordHash,
            'email' => $email,
            'role' => $role
        ]);

        $userId = $pdo->lastInsertId();

        sendJSON([
            'success' => true,
            'message' => 'Benutzer erfolgreich erstellt',
            'user_id' => $userId
        ]);
    } catch (PDOException $e) {
        error_log('Create user error: ' . $e->getMessage());
        sendError('Fehler beim Erstellen des Benutzers', 500);
    }
}

/**
 * Update user
 */
function updateUser() {
    requireAuth();

    $userId = $_POST['id'] ?? null;
    $username = $_POST['username'] ?? null;
    $email = $_POST['email'] ?? null;
    $role = $_POST['role'] ?? null;

    if (!$userId) {
        sendError('Benutzer-ID erforderlich', 400);
    }

    if ($role && !in_array($role, ['admin', 'editor'])) {
        sendError('Ungültige Rolle', 400);
    }

    $pdo = getDBConnection();

    try {
        // Build update query dynamically
        $updates = [];
        $params = ['id' => $userId];

        if ($username !== null) {
            // Check if username is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
            $stmt->execute(['username' => $username, 'id' => $userId]);

            if ($stmt->fetch()) {
                sendError('Benutzername bereits vergeben', 400);
            }

            $updates[] = 'username = :username';
            $params['username'] = $username;
        }

        if ($email !== null) {
            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
            $stmt->execute(['email' => $email, 'id' => $userId]);

            if ($stmt->fetch()) {
                sendError('E-Mail bereits vergeben', 400);
            }

            $updates[] = 'email = :email';
            $params['email'] = $email;
        }

        if ($role !== null) {
            $updates[] = 'role = :role';
            $params['role'] = $role;
        }

        if (empty($updates)) {
            sendError('Keine Änderungen angegeben', 400);
        }

        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        sendJSON([
            'success' => true,
            'message' => 'Benutzer erfolgreich aktualisiert'
        ]);
    } catch (PDOException $e) {
        error_log('Update user error: ' . $e->getMessage());
        sendError('Fehler beim Aktualisieren des Benutzers', 500);
    }
}

/**
 * Change user password
 */
function changePassword() {
    requireAuth();

    $userId = $_POST['id'] ?? null;
    $newPassword = $_POST['password'] ?? '';

    if (!$userId) {
        sendError('Benutzer-ID erforderlich', 400);
    }

    if (empty($newPassword)) {
        sendError('Neues Passwort erforderlich', 400);
    }

    if (strlen($newPassword) < 6) {
        sendError('Passwort muss mindestens 6 Zeichen lang sein', 400);
    }

    $pdo = getDBConnection();

    try {
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("UPDATE users SET password_hash = :password_hash WHERE id = :id");
        $stmt->execute([
            'password_hash' => $passwordHash,
            'id' => $userId
        ]);

        sendJSON([
            'success' => true,
            'message' => 'Passwort erfolgreich geändert'
        ]);
    } catch (PDOException $e) {
        error_log('Change password error: ' . $e->getMessage());
        sendError('Fehler beim Ändern des Passworts', 500);
    }
}

/**
 * Delete user
 */
function deleteUser() {
    requireAuth();

    $userId = $_POST['id'] ?? null;

    if (!$userId) {
        sendError('Benutzer-ID erforderlich', 400);
    }

    $pdo = getDBConnection();

    try {
        // Delete user's sessions first
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE user_id = :id");
        $stmt->execute(['id' => $userId]);

        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);

        if ($stmt->rowCount() === 0) {
            sendError('Benutzer nicht gefunden', 404);
        }

        sendJSON([
            'success' => true,
            'message' => 'Benutzer erfolgreich gelöscht'
        ]);
    } catch (PDOException $e) {
        error_log('Delete user error: ' . $e->getMessage());
        sendError('Fehler beim Löschen des Benutzers', 500);
    }
}
