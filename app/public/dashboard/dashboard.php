<?php
/**
 * Admin Center - Instagram Posts Analytics Dashboard
 *
 * Zeigt Analytics und Statistiken für Instagram-Posts
 * Hier können Admins Memes hochladen, bearbeiten und löschen
 * TODO: Instagram API Integration für Post-Analytics
 */

require_once __DIR__ . '/../../config/config.php';

// Login-Pflicht für diese Seite
requireLogin();

// Statistiken für Dashboard abrufen
$stats = [];
try {
    // Gesamtanzahl Memes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM memes");
    $stats['total_memes'] = $stmt->fetch()['total'] ?? 0;

    // Anzahl Memes heute
    $stmt = $pdo->query("SELECT COUNT(*) as today FROM memes WHERE DATE(created_at) = CURDATE()");
    $stats['memes_today'] = $stmt->fetch()['today'] ?? 0;

    // Anzahl aktiver Benutzer (TODO: erweitern)
    $stmt = $pdo->query("SELECT COUNT(*) as users FROM users WHERE active = 1");
    $stats['total_users'] = $stmt->fetch()['users'] ?? 0;

    // TODO: Anzahl Views/Likes für Analytics
    $stats['total_views'] = 0; // Placeholder

    // Neueste Memes abrufen
    $stmt = $pdo->query("SELECT * FROM memes ORDER BY created_at DESC LIMIT 10");
    $recent_memes = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Dashboard stats error: ' . $e->getMessage());
    $stats = ['total_memes' => 0, 'memes_today' => 0, 'total_users' => 0, 'total_views' => 0];
    $recent_memes = [];
}

// Upload-Ergebnis aus Session abrufen (nach Redirect vom upload_handler)
$upload_result = $_SESSION['upload_result'] ?? null;
unset($_SESSION['upload_result']);

$page_title = 'Admin Center - Instagram Analytics';
require_once TEMPLATE_PATH . '/header.php';
?>

<div class="container">
    <!-- Willkommens-Bereich -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5">
                <i class="bi bi-instagram"></i> Admin Center
            </h1>
            <p class="lead text-muted">
                Instagram Posts Analytics Dashboard
            </p>
            <p class="text-muted">
                <small>Eingeloggt als: <?php echo htmlspecialchars($_SESSION['username']); ?></small>
            </p>
        </div>
    </div>

    <!-- Statistik-Karten -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Gesamt Memes</h6>
                            <h2 class="mb-0"><?php echo $stats['total_memes']; ?></h2>
                        </div>
                        <i class="bi bi-image" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Heute hochgeladen</h6>
                            <h2 class="mb-0"><?php echo $stats['memes_today']; ?></h2>
                        </div>
                        <i class="bi bi-calendar-check" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Benutzer</h6>
                            <h2 class="mb-0"><?php echo $stats['total_users']; ?></h2>
                        </div>
                        <i class="bi bi-people" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Views</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['total_views']); ?></h2>
                        </div>
                        <i class="bi bi-eye" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
            <!-- TODO: Analytics-Integration für echte View-Zahlen -->
        </div>
    </div>

    <div class="row">
        <!-- Meme Upload Form -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cloud-upload"></i> Neues Meme hochladen
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($upload_result): ?>
                        <div class="alert alert-<?php echo $upload_result['success'] ? 'success' : 'danger'; ?>" role="alert">
                            <i class="bi bi-<?php echo $upload_result['success'] ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                            <?php echo htmlspecialchars($upload_result['message']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" action="upload_handler.php">
                        <div class="mb-3">
                            <label for="meme_file" class="form-label">Bild auswählen</label>
                            <input type="file"
                                   class="form-control"
                                   id="meme_file"
                                   name="meme_file"
                                   accept="image/*"
                                   required>
                            <small class="text-muted">Max. 5MB (JPG, PNG, GIF, WebP)</small>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Titel</label>
                            <input type="text"
                                   class="form-control"
                                   id="title"
                                   name="title"
                                   placeholder="Lustiger Meme-Titel">
                        </div>

                        <div class="mb-3">
                            <label for="caption" class="form-label">Caption/Beschreibung</label>
                            <textarea class="form-control"
                                      id="caption"
                                      name="caption"
                                      rows="3"
                                      placeholder="Optionale Beschreibung..."></textarea>
                            <!-- TODO: Caption-Text wird später für Suchfunktion verwendet -->
                        </div>

                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text"
                                   class="form-control"
                                   id="tags"
                                   name="tags"
                                   placeholder="lustig, katze, montag">
                            <!-- TODO: Tag-System für bessere Kategorisierung -->
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Hochladen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Neueste Memes Liste -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Neueste Memes
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_memes)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Noch keine Memes vorhanden. Lade dein erstes Meme hoch!
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Vorschau</th>
                                        <th>Titel</th>
                                        <th>Hochgeladen</th>
                                        <th>Aktionen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_memes as $meme): ?>
                                        <tr>
                                            <td>
                                                <img src="<?php echo htmlspecialchars($meme['file_path']); ?>"
                                                     alt="Meme"
                                                     class="img-thumbnail"
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            </td>
                                            <td><?php echo htmlspecialchars($meme['title'] ?? 'Ohne Titel'); ?></td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('d.m.Y H:i', strtotime($meme['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" title="Bearbeiten">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" title="Löschen">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <!-- TODO: Edit/Delete-Funktionen implementieren -->
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TODO: Analytics-Dashboard mit Charts hinzufügen -->
            <!-- TODO: Moderations-Tools für Kommentare/Reports -->
            <!-- TODO: Bulk-Upload-Funktion -->
        </div>
    </div>
</div>

<?php require_once TEMPLATE_PATH . '/footer.php'; ?>
