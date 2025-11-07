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

    // Instagram Posts Statistiken
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM instagram_posts");
    $stats['total_instagram_posts'] = $stmt->fetch()['total'] ?? 0;

    // Instagram Analytics Summary
    $stmt = $pdo->query("SELECT * FROM instagram_analytics_summary");
    $ig_summary = $stmt->fetch();
    $stats['total_views'] = $ig_summary['total_views'] ?? 0;
    $stats['total_likes'] = $ig_summary['total_likes'] ?? 0;
    $stats['avg_engagement'] = $ig_summary['avg_engagement_rate'] ?? 0;

    // Anzahl Benutzer
    $stmt = $pdo->query("SELECT COUNT(*) as users FROM users");
    $stats['total_users'] = $stmt->fetch()['users'] ?? 0;

    // Neueste Memes abrufen
    $stmt = $pdo->query("SELECT * FROM memes ORDER BY created_at DESC LIMIT 10");
    $recent_memes = $stmt->fetchAll();

    // Top Instagram Posts abrufen
    $stmt = $pdo->query("SELECT * FROM top_instagram_posts LIMIT 5");
    $top_ig_posts = $stmt->fetchAll();

    // Alle Instagram Posts für Tab 2 abrufen
    $stmt = $pdo->query("SELECT * FROM instagram_posts ORDER BY post_date DESC");
    $all_instagram_posts = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Dashboard stats error: ' . $e->getMessage());
    $stats = [
        'total_memes' => 0,
        'memes_today' => 0,
        'total_users' => 0,
        'total_views' => 0,
        'total_likes' => 0,
        'total_instagram_posts' => 0,
        'avg_engagement' => 0
    ];
    $recent_memes = [];
    $top_ig_posts = [];
    $all_instagram_posts = [];
}

// Upload-Ergebnis aus Session abrufen (nach Redirect vom upload_handler)
$upload_result = $_SESSION['upload_result'] ?? null;
unset($_SESSION['upload_result']);

// Edit-Ergebnis aus Session abrufen
$edit_result = $_SESSION['edit_result'] ?? null;
unset($_SESSION['edit_result']);

// Delete-Ergebnis aus Session abrufen
$delete_result = $_SESSION['delete_result'] ?? null;
unset($_SESSION['delete_result']);

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
                            <h6 class="card-title mb-0">Instagram Views</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['total_views']); ?></h2>
                        </div>
                        <i class="bi bi-eye" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Instagram Analytics Karten -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-instagram">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0 text-muted">Instagram Posts</h6>
                            <h2 class="mb-0 text-instagram"><?php echo number_format($stats['total_instagram_posts']); ?></h2>
                        </div>
                        <i class="bi bi-instagram text-instagram" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0 text-muted">Total Likes</h6>
                            <h2 class="mb-0 text-danger"><?php echo number_format($stats['total_likes']); ?></h2>
                        </div>
                        <i class="bi bi-heart-fill text-danger" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0 text-muted">Ø Engagement</h6>
                            <h2 class="mb-0 text-success"><?php echo number_format($stats['avg_engagement'], 1); ?>%</h2>
                        </div>
                        <i class="bi bi-graph-up text-success" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0 text-muted">Benutzer</h6>
                            <h2 class="mb-0 text-primary"><?php echo $stats['total_users']; ?></h2>
                        </div>
                        <i class="bi bi-people text-primary" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .text-instagram { color: #E4405F; }
        .border-instagram { border: 2px solid #E4405F; }
        .nav-tabs .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
        .filter-input {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
        }
        .sortable-header {
            cursor: pointer;
            user-select: none;
        }
        .sortable-header:hover {
            background-color: #f8f9fa;
        }
        .sort-icon {
            opacity: 0.3;
            font-size: 0.75rem;
            margin-left: 0.25rem;
        }
        .sortable-header.sorted .sort-icon {
            opacity: 1;
        }
    </style>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab" aria-controls="upload" aria-selected="true">
                <i class="bi bi-cloud-upload"></i> Image Upload
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="posts-tab" data-bs-toggle="tab" data-bs-target="#posts" type="button" role="tab" aria-controls="posts" aria-selected="false">
                <i class="bi bi-instagram"></i> Instagram Posts
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="adminTabsContent">
        <!-- Tab 1: Image Upload -->
        <div class="tab-pane fade show active" id="upload" role="tabpanel" aria-labelledby="upload-tab">
            <!-- Benachrichtigungen für Edit/Delete -->
            <?php if ($edit_result): ?>
                <div class="alert alert-<?php echo $edit_result['success'] ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?php echo $edit_result['success'] ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo htmlspecialchars($edit_result['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($delete_result): ?>
                <div class="alert alert-<?php echo $delete_result['success'] ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?php echo $delete_result['success'] ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo htmlspecialchars($delete_result['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Image Upload Form -->
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-cloud-upload"></i> Neues Image hochladen
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if ($upload_result): ?>
                                <div class="alert alert-<?php echo $upload_result['success'] ? 'success' : 'danger'; ?>" role="alert">
                                    <i class="bi bi-<?php echo $upload_result['success'] ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                                    <?php echo htmlspecialchars($upload_result['message']); ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" enctype="multipart/form-data" action="dashboard/upload_handler.php">
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
                                           placeholder="Image-Titel">
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Beschreibung</label>
                                    <textarea class="form-control"
                                              id="description"
                                              name="description"
                                              rows="3"
                                              placeholder="Optionale Beschreibung..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="category" class="form-label">Kategorie</label>
                                    <select class="form-select"
                                            id="category"
                                            name="category">
                                        <option value="">Keine Kategorie</option>
                                        <option value="Wallpaper">Wallpaper</option>
                                        <option value="Art">Art</option>
                                        <option value="Meme">Meme</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="is_public"
                                               name="is_public"
                                               value="1"
                                               checked>
                                        <label class="form-check-label" for="is_public">
                                            Öffentlich sichtbar
                                        </label>
                                    </div>
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

                <!-- Neueste Images Liste -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-clock-history"></i> Neueste Posts
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_memes)): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Noch keine Images vorhanden. Lade dein erstes Image hoch!
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
                                                        <img src="<?php echo htmlspecialchars($meme['image_url']); ?>"
                                                             alt="Image"
                                                             class="img-thumbnail"
                                                             style="width: 50px; height: 50px; object-fit: cover;">
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars($meme['title'] ?? 'Ohne Titel'); ?>
                                                        <?php if (!$meme['is_public']): ?>
                                                            <span class="badge bg-warning text-dark ms-1">Privat</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?php echo date('d.m.Y H:i', strtotime($meme['created_at'])); ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary edit-meme-btn"
                                                                title="Bearbeiten"
                                                                data-meme-id="<?php echo $meme['id']; ?>"
                                                                data-title="<?php echo htmlspecialchars($meme['title'] ?? ''); ?>"
                                                                data-description="<?php echo htmlspecialchars($meme['description'] ?? ''); ?>"
                                                                data-category="<?php echo htmlspecialchars($meme['category'] ?? ''); ?>"
                                                                data-is-public="<?php echo $meme['is_public'] ? '1' : '0'; ?>">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger delete-meme-btn"
                                                                title="Löschen"
                                                                data-meme-id="<?php echo $meme['id']; ?>"
                                                                data-title="<?php echo htmlspecialchars($meme['title'] ?? 'Ohne Titel'); ?>">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Instagram Posts -->
        <div class="tab-pane fade" id="posts" role="tabpanel" aria-labelledby="posts-tab">
            <div class="card shadow-sm">
                <div class="card-header bg-instagram text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-instagram"></i> Neueste Posts
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($all_instagram_posts)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Noch keine Instagram Posts vorhanden.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="instagramPostsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="sortable-header" data-column="id">
                                            ID<span class="sort-icon">▲</span>
                                            <input type="text" class="form-control filter-input mt-1" placeholder="Filter..." data-column="id">
                                        </th>
                                        <th class="sortable-header" data-column="ig_post_id">
                                            Post ID<span class="sort-icon">▲</span>
                                            <input type="text" class="form-control filter-input mt-1" placeholder="Filter..." data-column="ig_post_id">
                                        </th>
                                        <th class="sortable-header" data-column="caption">
                                            Caption<span class="sort-icon">▲</span>
                                            <input type="text" class="form-control filter-input mt-1" placeholder="Filter..." data-column="caption">
                                        </th>
                                        <th class="sortable-header" data-column="hashtags">
                                            Hashtags<span class="sort-icon">▲</span>
                                            <input type="text" class="form-control filter-input mt-1" placeholder="Filter..." data-column="hashtags">
                                        </th>
                                        <th class="sortable-header" data-column="post_date">
                                            Post Datum<span class="sort-icon">▲</span>
                                            <input type="text" class="form-control filter-input mt-1" placeholder="Filter..." data-column="post_date">
                                        </th>
                                        <th class="sortable-header" data-column="views">
                                            Views<span class="sort-icon">▲</span>
                                            <input type="text" class="form-control filter-input mt-1" placeholder="Filter..." data-column="views">
                                        </th>
                                        <th class="sortable-header" data-column="likes">
                                            Likes<span class="sort-icon">▲</span>
                                            <input type="text" class="form-control filter-input mt-1" placeholder="Filter..." data-column="likes">
                                        </th>
                                        <th class="sortable-header" data-column="comments">
                                            Comments<span class="sort-icon">▲</span>
                                            <input type="text" class="form-control filter-input mt-1" placeholder="Filter..." data-column="comments">
                                        </th>
                                        <th class="sortable-header" data-column="shares">
                                            Shares<span class="sort-icon">▲</span>
                                            <input type="text" class="form-control filter-input mt-1" placeholder="Filter..." data-column="shares">
                                        </th>
                                        <th class="sortable-header" data-column="saves">
                                            Saves<span class="sort-icon">▲</span>
                                            <input type="text" class="form-control filter-input mt-1" placeholder="Filter..." data-column="saves">
                                        </th>
                                        <th class="sortable-header" data-column="engagement_rate">
                                            Engagement %<span class="sort-icon">▲</span>
                                            <input type="text" class="form-control filter-input mt-1" placeholder="Filter..." data-column="engagement_rate">
                                        </th>
                                        <th class="sortable-header" data-column="imported_at">
                                            Importiert<span class="sort-icon">▲</span>
                                            <input type="text" class="form-control filter-input mt-1" placeholder="Filter..." data-column="imported_at">
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_instagram_posts as $post): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($post['id']); ?></td>
                                            <td><?php echo htmlspecialchars($post['ig_post_id']); ?></td>
                                            <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($post['caption']); ?>">
                                                <?php echo htmlspecialchars($post['caption']); ?>
                                            </td>
                                            <td style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($post['hashtags']); ?>">
                                                <?php echo htmlspecialchars($post['hashtags']); ?>
                                            </td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($post['post_date'])); ?></td>
                                            <td><?php echo number_format($post['views']); ?></td>
                                            <td><?php echo number_format($post['likes']); ?></td>
                                            <td><?php echo number_format($post['comments']); ?></td>
                                            <td><?php echo number_format($post['shares']); ?></td>
                                            <td><?php echo number_format($post['saves']); ?></td>
                                            <td><?php echo number_format($post['engagement_rate'], 2); ?>%</td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($post['imported_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Meme Modal -->
    <div class="modal fade" id="editMemeModal" tabindex="-1" aria-labelledby="editMemeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMemeModalLabel">
                        <i class="bi bi-pencil"></i> Meme bearbeiten
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="dashboard/edit_handler.php" id="editMemeForm">
                    <div class="modal-body">
                        <input type="hidden" name="meme_id" id="edit_meme_id">

                        <div class="mb-3">
                            <label for="edit_title" class="form-label">Titel</label>
                            <input type="text"
                                   class="form-control"
                                   id="edit_title"
                                   name="title"
                                   placeholder="Image-Titel">
                        </div>

                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Beschreibung</label>
                            <textarea class="form-control"
                                      id="edit_description"
                                      name="description"
                                      rows="3"
                                      placeholder="Optionale Beschreibung..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="edit_category" class="form-label">Kategorie</label>
                            <select class="form-select"
                                    id="edit_category"
                                    name="category">
                                <option value="">Keine Kategorie</option>
                                <option value="Wallpaper">Wallpaper</option>
                                <option value="Art">Art</option>
                                <option value="Meme">Meme</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="edit_is_public"
                                       name="is_public"
                                       value="1">
                                <label class="form-check-label" for="edit_is_public">
                                    Öffentlich sichtbar
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Abbrechen
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Speichern
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Edit und Delete Funktionalität für Neueste Posts
    document.addEventListener('DOMContentLoaded', function() {
        // Edit Button Event Handler
        const editButtons = document.querySelectorAll('.edit-meme-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const memeId = this.dataset.memeId;
                const title = this.dataset.title;
                const description = this.dataset.description;
                const category = this.dataset.category;
                const isPublic = this.dataset.isPublic === '1';

                // Modal-Felder befüllen
                document.getElementById('edit_meme_id').value = memeId;
                document.getElementById('edit_title').value = title;
                document.getElementById('edit_description').value = description;
                document.getElementById('edit_category').value = category;
                document.getElementById('edit_is_public').checked = isPublic;

                // Modal öffnen
                const modal = new bootstrap.Modal(document.getElementById('editMemeModal'));
                modal.show();
            });
        });

        // Delete Button Event Handler
        const deleteButtons = document.querySelectorAll('.delete-meme-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const memeId = this.dataset.memeId;
                const title = this.dataset.title;

                if (confirm(`Möchten Sie "${title}" wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.`)) {
                    // Form erstellen und absenden
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'dashboard/delete_handler.php';

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'meme_id';
                    input.value = memeId;

                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });

    // Sortierung und Filter für Instagram Posts Tabelle
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.getElementById('instagramPostsTable');
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const headers = table.querySelectorAll('.sortable-header');
        const filterInputs = table.querySelectorAll('.filter-input');

        let sortColumn = null;
        let sortDirection = 'asc';
        let originalRows = Array.from(tbody.querySelectorAll('tr'));

        // Sortierung
        headers.forEach(header => {
            header.addEventListener('click', function(e) {
                if (e.target.classList.contains('filter-input')) return;

                const column = this.dataset.column;
                const columnIndex = Array.from(this.parentElement.children).indexOf(this);

                // Toggle sort direction
                if (sortColumn === column) {
                    sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    sortColumn = column;
                    sortDirection = 'asc';
                }

                // Update visual indicators
                headers.forEach(h => {
                    h.classList.remove('sorted');
                    h.querySelector('.sort-icon').textContent = '▲';
                });
                this.classList.add('sorted');
                this.querySelector('.sort-icon').textContent = sortDirection === 'asc' ? '▲' : '▼';

                // Sort rows
                const rows = Array.from(tbody.querySelectorAll('tr'));
                rows.sort((a, b) => {
                    let aVal = a.children[columnIndex].textContent.trim();
                    let bVal = b.children[columnIndex].textContent.trim();

                    // Try to parse as number
                    const aNum = parseFloat(aVal.replace(/[^0-9.-]/g, ''));
                    const bNum = parseFloat(bVal.replace(/[^0-9.-]/g, ''));

                    if (!isNaN(aNum) && !isNaN(bNum)) {
                        return sortDirection === 'asc' ? aNum - bNum : bNum - aNum;
                    }

                    // String comparison
                    return sortDirection === 'asc' ?
                        aVal.localeCompare(bVal) :
                        bVal.localeCompare(aVal);
                });

                // Rebuild tbody
                tbody.innerHTML = '';
                rows.forEach(row => tbody.appendChild(row));
            });
        });

        // Filter
        filterInputs.forEach(input => {
            input.addEventListener('input', function() {
                const filters = {};
                filterInputs.forEach(inp => {
                    const col = inp.dataset.column;
                    const val = inp.value.toLowerCase().trim();
                    if (val) filters[col] = val;
                });

                originalRows.forEach(row => {
                    let show = true;
                    const cells = row.querySelectorAll('td');

                    Object.keys(filters).forEach(col => {
                        const columnIndex = Array.from(headers).findIndex(h => h.dataset.column === col);
                        if (columnIndex !== -1) {
                            const cellText = cells[columnIndex].textContent.toLowerCase();
                            if (!cellText.includes(filters[col])) {
                                show = false;
                            }
                        }
                    });

                    row.style.display = show ? '' : 'none';
                });
            });

            // Prevent sorting when clicking on filter input
            input.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    });
    </script>

    <style>
        .bg-instagram {
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
        }
    </style>
</div>

<?php require_once TEMPLATE_PATH . '/footer.php'; ?>
