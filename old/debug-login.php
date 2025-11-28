<?php
/**
 * Debug-Skript für Login-Probleme
 * Überprüft alle Aspekte des Login-Prozesses
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/app/config/config.php';

echo "=== Login Debug-Tool ===\n\n";

// Test-Daten
$test_email = 'admin@example.com';
$test_password = 'admin123';

echo "1. DATENBANKVERBINDUNG\n";
echo "   ✓ Verbindung hergestellt\n\n";

echo "2. BENUTZER IN DATENBANK\n";
try {
    $stmt = $pdo->query("SELECT id, username, email, role, active, password_hash, created_at FROM users");
    $users = $stmt->fetchAll();

    echo "   Gefundene Benutzer: " . count($users) . "\n\n";

    foreach ($users as $user) {
        echo "   User ID: {$user['id']}\n";
        echo "   - Username: {$user['username']}\n";
        echo "   - Email: {$user['email']}\n";
        echo "   - Role: {$user['role']}\n";
        echo "   - Active: " . (isset($user['active']) ? ($user['active'] ? 'Ja (1)' : 'Nein (0)') : 'FELD FEHLT!') . "\n";
        echo "   - Created: {$user['created_at']}\n";
        echo "   - Hash: " . substr($user['password_hash'], 0, 20) . "...\n";
        echo "   - Hash-Länge: " . strlen($user['password_hash']) . " Zeichen\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ Fehler: " . $e->getMessage() . "\n\n";
}

echo "3. SPALTEN IN users-TABELLE\n";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM users");
    $columns = $stmt->fetchAll();
    echo "   Vorhandene Spalten:\n";
    foreach ($columns as $col) {
        echo "   - {$col['Field']} ({$col['Type']})\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Fehler: " . $e->getMessage() . "\n\n";
}

echo "4. LOGIN-SIMULATION FÜR admin@example.com\n";
try {
    // Genau wie in login.php
    $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE email = ? AND active = 1");
    $stmt->execute([$test_email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "   ✗ FEHLER: Benutzer nicht gefunden mit dieser Query!\n";
        echo "   SQL: SELECT id, username, email, password_hash FROM users WHERE email = ? AND active = 1\n";
        echo "   Parameter: $test_email\n\n";

        // Versuche ohne active-Filter
        echo "   Versuche ohne active-Filter...\n";
        $stmt = $pdo->prepare("SELECT id, username, email, password_hash, active FROM users WHERE email = ?");
        $stmt->execute([$test_email]);
        $user = $stmt->fetch();

        if ($user) {
            echo "   ✓ Benutzer gefunden (ohne active-Filter)\n";
            echo "   - Active-Wert: " . (isset($user['active']) ? $user['active'] : 'NULL/nicht gesetzt') . "\n";
            echo "   Problem: active-Feld ist nicht 1!\n\n";
        } else {
            echo "   ✗ Benutzer existiert nicht in der Datenbank\n\n";
        }
    } else {
        echo "   ✓ Benutzer gefunden\n";
        echo "   - ID: {$user['id']}\n";
        echo "   - Username: {$user['username']}\n";
        echo "   - Email: {$user['email']}\n\n";

        echo "5. PASSWORT-VERIFIKATION\n";
        echo "   Test-Passwort: '$test_password'\n";
        echo "   Gespeicherter Hash: {$user['password_hash']}\n";
        echo "   Hash-Typ: " . substr($user['password_hash'], 0, 4) . "\n\n";

        // Test verschiedener Passwörter
        $test_passwords = ['admin123', 'Admin123', 'ADMIN123', 'password'];

        foreach ($test_passwords as $pwd) {
            $result = password_verify($pwd, $user['password_hash']);
            $status = $result ? '✓ MATCH' : '✗ kein Match';
            echo "   password_verify('$pwd', hash): $status\n";
        }

        echo "\n6. HASH-GENERIERUNG TEST\n";
        $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
        echo "   Neu generierter Hash: $new_hash\n";
        echo "   Verifikation mit neuem Hash: " . (password_verify($test_password, $new_hash) ? '✓ OK' : '✗ FEHLER') . "\n\n";

        echo "7. EMPFEHLUNG\n";
        if (!password_verify($test_password, $user['password_hash'])) {
            echo "   ⚠️  Der gespeicherte Hash ist NICHT korrekt für '$test_password'\n";
            echo "   Führe aus: php fix-login.php\n";
            echo "   Oder UPDATE manuell:\n";
            echo "   UPDATE users SET password_hash = '$new_hash' WHERE email = '$test_email';\n";
        } else {
            echo "   ✓ Passwort-Hash ist korrekt!\n";
            echo "   Das Login sollte funktionieren.\n";
        }
    }

} catch (Exception $e) {
    echo "   ✗ Fehler: " . $e->getMessage() . "\n\n";
}

echo "\n=== Debug-Analyse abgeschlossen ===\n";
?>
