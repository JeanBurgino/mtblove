<?php
/**
 * Behebt das Login-Problem
 * - Fügt das 'active' Feld zur users-Tabelle hinzu
 * - Aktualisiert Passwort-Hashes auf den korrekten Wert
 */

require_once __DIR__ . '/app/config/config.php';

echo "=== Login-Fix für admin@example.com ===\n\n";

try {
    // 1. Active-Feld hinzufügen (falls noch nicht vorhanden)
    echo "1. Prüfe 'active' Feld...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'active'");
    if ($stmt->rowCount() === 0) {
        echo "   → Füge 'active' Feld hinzu...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN active TINYINT(1) DEFAULT 1 AFTER role");
        $pdo->exec("ALTER TABLE users ADD INDEX idx_active (active)");
        echo "   ✓ Feld hinzugefügt\n";
    } else {
        echo "   ✓ Feld existiert bereits\n";
    }

    // 2. Korrekten Passwort-Hash setzen
    echo "\n2. Aktualisiere Passwort-Hashes...\n";
    $correctHash = '$2y$12$2NXMAG9LpnCMWxr7s/moxe7HOJjzil3r1OPywZL3vTZNZ0UCIiHTC';

    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, active = 1 WHERE email IN (?, ?, ?)");
    $stmt->execute([$correctHash, 'admin@example.com', 'editor@example.com', 'viewer@example.com']);
    $affected = $stmt->rowCount();
    echo "   ✓ $affected Benutzer aktualisiert\n";

    // 3. Verifizierung
    echo "\n3. Verifiziere Admin-Benutzer...\n";
    $stmt = $pdo->prepare("SELECT id, username, email, role, active, password_hash FROM users WHERE email = ?");
    $stmt->execute(['admin@example.com']);
    $user = $stmt->fetch();

    if ($user) {
        echo "   ✓ Benutzer gefunden:\n";
        echo "     - ID: {$user['id']}\n";
        echo "     - Username: {$user['username']}\n";
        echo "     - Email: {$user['email']}\n";
        echo "     - Role: {$user['role']}\n";
        echo "     - Active: " . ($user['active'] ? 'Ja' : 'Nein') . "\n";

        // Test des Passworts
        $testPassword = 'admin123';
        if (password_verify($testPassword, $user['password_hash'])) {
            echo "     - Passwort: ✓ Korrekt (admin123 funktioniert)\n";
        } else {
            echo "     - Passwort: ✗ FEHLER (admin123 funktioniert NICHT)\n";
        }
    } else {
        echo "   ✗ Admin-Benutzer nicht gefunden!\n";
        echo "   → Erstelle Admin-Benutzer...\n";

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@example.com', $correctHash, 'admin', 1]);
        echo "   ✓ Admin-Benutzer erstellt\n";
    }

    echo "\n=== Fix erfolgreich abgeschlossen ===\n";
    echo "\nDu kannst dich jetzt einloggen mit:\n";
    echo "E-Mail: admin@example.com\n";
    echo "Passwort: admin123\n";

} catch (Exception $e) {
    echo "\n✗ FEHLER: " . $e->getMessage() . "\n";
    exit(1);
}
?>
