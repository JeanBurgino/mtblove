<?php
/**
 * Öffentliche Meme-Gallery - Startseite
 *
 * Zeigt alle Memes in einer responsiven Grid-Ansicht
 * Kein Login erforderlich - öffentlich zugänglich
 */

require_once '../config/config.php';

// Pagination-Einstellungen
$items_per_page = 12;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Suchfunktion
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$tag_filter = isset($_GET['tag']) ? trim($_GET['tag']) : '';

// Memes aus Datenbank laden
$memes = [];
$total_memes = 0;

try {
    // WHERE-Bedingungen aufbauen
    $where_clauses = [];
    $params = [];

    if (!empty($search_query)) {
        $where_clauses[] = "(title LIKE ? OR caption LIKE ?)";
        $params[] = "%{$search_query}%";
        $params[] = "%{$search_query}%";
    }

    if (!empty($tag_filter)) {
        $where_clauses[] = "tags LIKE ?";
        $params[] = "%{$tag_filter}%";
    }

    $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

    // Gesamtanzahl für Pagination
    $count_sql = "SELECT COUNT(*) as total FROM memes {$where_sql}";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_memes = $stmt->fetch()['total'];

    // Memes abrufen
    $sql = "SELECT * FROM memes {$where_sql} ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $items_per_page;
    $params[] = $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $memes = $stmt->fetchAll();

    // TODO: View-Counter für Analytics erhöhen
    // TODO: Trending-Algorithmus implementieren

} catch (PDOException $e) {
    error_log('Gallery load error: ' . $e->getMessage());
}

// Pagination berechnen
$total_pages = ceil($total_memes / $items_per_page);

$page_title = 'Meme Gallery';
require_once TEMPLATE_PATH . '/header.php';
?>

<div class="container">
    <!-- Hero-Bereich -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-4 mb-3">
                <i class="bi bi-emoji-laughing-fill text-warning"></i>
                Willkommen in der Meme Gallery!
            </h1>
            <p class="lead text-muted">
                Die lustigsten Memes an einem Ort. Viel Spaß beim Stöbern!
            </p>
        </div>
    </div>

    <!-- Logout-Message anzeigen -->
    <?php if (isset($_SESSION['logout_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['logout_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['logout_message']); ?>
    <?php endif; ?>

    <!-- Such- und Filterleiste -->
    <div class="row mb-4">
        <div class="col-lg-8 mx-auto">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text"
                               class="form-control"
                               name="search"
                               placeholder="Memes durchsuchen..."
                               value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary flex-fill">
                            Suchen
                        </button>
                        <?php if (!empty($search_query) || !empty($tag_filter)): ?>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            <!-- TODO: Erweiterte Filteroptionen (Datum, Beliebtheit, Tags) -->
        </div>
    </div>

    <!-- Meme-Grid -->
    <?php if (empty($memes)): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Noch keine Memes vorhanden</h4>
                    <p class="text-muted">
                        <?php if (isLoggedIn()): ?>
                            <a href="<?php echo BASE_URL; ?>/app/public/dashboard/dashboard.php" class="btn btn-primary mt-2">
                                Erstes Meme hochladen
                            </a>
                        <?php else: ?>
                            Schau später wieder vorbei!
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4 mb-5">
            <?php foreach ($memes as $meme): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card meme-card shadow-sm h-100">
                        <!-- Meme-Bild -->
                        <img src="<?php echo htmlspecialchars($meme['file_path']); ?>"
                             class="card-img-top meme-img"
                             alt="<?php echo htmlspecialchars($meme['title'] ?? 'Meme'); ?>"
                             loading="lazy"
                             data-bs-toggle="modal"
                             data-bs-target="#memeModal<?php echo $meme['id']; ?>">

                        <div class="card-body">
                            <h6 class="card-title">
                                <?php echo htmlspecialchars($meme['title'] ?? 'Ohne Titel'); ?>
                            </h6>

                            <?php if (!empty($meme['caption'])): ?>
                                <p class="card-text text-muted small">
                                    <?php echo htmlspecialchars(substr($meme['caption'], 0, 80)); ?>
                                    <?php if (strlen($meme['caption']) > 80) echo '...'; ?>
                                </p>
                            <?php endif; ?>

                            <!-- Tags -->
                            <?php if (!empty($meme['tags'])): ?>
                                <div class="mb-2">
                                    <?php
                                    $tags = explode(',', $meme['tags']);
                                    foreach (array_slice($tags, 0, 3) as $tag):
                                        $tag = trim($tag);
                                    ?>
                                        <a href="?tag=<?php echo urlencode($tag); ?>"
                                           class="badge bg-secondary text-decoration-none">
                                            #<?php echo htmlspecialchars($tag); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-footer bg-transparent border-0">
                            <div class="d-flex justify-content-between text-muted small">
                                <span>
                                    <i class="bi bi-calendar"></i>
                                    <?php echo date('d.m.Y', strtotime($meme['created_at'])); ?>
                                </span>
                                <span>
                                    <i class="bi bi-eye"></i> 0
                                    <!-- TODO: View-Counter anzeigen -->
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Modal für Vollbild-Ansicht -->
                    <div class="modal fade" id="memeModal<?php echo $meme['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <?php echo htmlspecialchars($meme['title'] ?? 'Meme'); ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <img src="<?php echo htmlspecialchars($meme['file_path']); ?>"
                                         class="img-fluid"
                                         alt="<?php echo htmlspecialchars($meme['title'] ?? 'Meme'); ?>">

                                    <?php if (!empty($meme['caption'])): ?>
                                        <p class="mt-3 text-start">
                                            <?php echo nl2br(htmlspecialchars($meme['caption'])); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <!-- TODO: Share-Buttons hinzufügen -->
                                    <!-- TODO: Like/Favorite-Button -->
                                    <!-- TODO: Download-Button -->
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        Schließen
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="row">
                <div class="col-12">
                    <nav aria-label="Pagination">
                        <ul class="pagination justify-content-center">
                            <!-- Zurück -->
                            <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                   href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>">
                                    <i class="bi bi-chevron-left"></i> Zurück
                                </a>
                            </li>

                            <!-- Seitenzahlen -->
                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);

                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="page-item <?php echo ($i === $current_page) ? 'active' : ''; ?>">
                                    <a class="page-link"
                                       href="?page=<?php echo $i; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Weiter -->
                            <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                   href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>">
                                    Weiter <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- TODO: Infinite Scroll als Alternative zur Pagination -->
    <!-- TODO: Masonry-Layout für bessere Bilddarstellung -->
    <!-- TODO: Share-Funktionen (Social Media) -->
</div>

<?php require_once TEMPLATE_PATH . '/footer.php'; ?>
