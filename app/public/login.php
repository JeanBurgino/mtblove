<?php
/**
 * Login-Seite für die Meme-Gallery
 *
 * Authentifiziert Benutzer mit E-Mail und Passwort
 * Verwendet sichere Session-basierte Authentifizierung
 */

require_once '../config/config.php';

// Wenn bereits eingeloggt, zum Admin Center weiterleiten
if (isLoggedIn()) {
    redirect(BASE_URL . '/admin-center');
}

$error = '';
$success = '';

// Login-Formular verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Bitte alle Felder ausfüllen.';
    } else {
        try {
            // Benutzer aus Datenbank abrufen
            $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE email = ? AND active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Passwort überprüfen
            if ($user && password_verify($password, $user['password_hash'])) {
                // Session-Daten setzen
                session_regenerate_id(true); // Verhindert Session Fixation
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['login_time'] = time();

                // TODO: Login-Event für Analytics tracken
                // trackEvent('user_login', ['user_id' => $user['id']]);

                // TODO: Last-Login-Zeit in DB aktualisieren
                // $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                // $stmt->execute([$user['id']]);

                // Weiterleitung zum Admin Center
                redirect(BASE_URL . '/admin-center');
            } else {
                $error = 'Ungültige Anmeldedaten.';

                // TODO: Failed-Login-Attempts tracken für Sicherheit
                // TODO: Nach X Fehlversuchen Account temporär sperren
            }
        } catch (PDOException $e) {
            $error = 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.';
            // TODO: Error-Logging implementieren
            error_log('Login error: ' . $e->getMessage());
        }
    }
}

$page_title = 'Login';
require_once TEMPLATE_PATH . '/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">E-Mail</label>
                            <input type="email"
                                   class="form-control"
                                   id="email"
                                   name="email"
                                   required
                                   autofocus
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Passwort</label>
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="password"
                                   required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Angemeldet bleiben
                            </label>
                            <!-- TODO: "Remember Me" Funktionalität implementieren -->
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Einloggen
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="text-muted mb-2">
                            <a href="#" class="text-decoration-none">Passwort vergessen?</a>
                            <!-- TODO: Password-Reset-Funktion implementieren -->
                        </p>
                        <p class="text-muted">
                            Noch kein Konto?
                            <a href="#" class="text-decoration-none">Registrieren</a>
                            <!-- TODO: Registrierungs-Seite erstellen -->
                        </p>
                    </div>

                    <!-- Demo-Hinweis (in Produktion entfernen) -->
                    <div class="alert alert-info mt-3 mb-0">
                        <small>
                            <strong>Demo-Login:</strong><br>
                            E-Mail: admin@example.com<br>
                            Passwort: admin123
                            <!-- TODO: Demo-Benutzer per SQL-Script erstellen -->
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once TEMPLATE_PATH . '/footer.php'; ?>
