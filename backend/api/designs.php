<?php
// ============================================================================
// MTB Love - Design Shop API
// ============================================================================
// Handles CRUD operations for designs, markets, product_types, and variants
// ============================================================================

header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

// Check if user is authenticated and has admin role
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Nicht authentifiziert']);
    exit;
}

if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'editor') {
    http_response_code(403);
    echo json_encode(['error' => 'Keine Berechtigung']);
    exit;
}

// Get action from request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        // ========== DESIGNS ==========
        case 'get_designs':
            getDesigns();
            break;

        case 'get_design':
            getDesign();
            break;

        case 'add_design':
            addDesign();
            break;

        case 'update_design':
            updateDesign();
            break;

        case 'delete_design':
            deleteDesign();
            break;

        // ========== MARKETS ==========
        case 'get_markets':
            getMarkets();
            break;

        // ========== PRODUCT TYPES ==========
        case 'get_product_types':
            getProductTypes();
            break;

        // ========== VARIANTS ==========
        case 'get_variants':
            getVariants();
            break;

        case 'get_design_variants':
            getDesignVariants();
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ungültige Aktion']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server-Fehler: ' . $e->getMessage()]);
}

// ============================================================================
// DESIGN FUNCTIONS
// ============================================================================

/**
 * Get all designs
 */
function getDesigns() {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT d.*,
               COUNT(v.id) as variant_count
        FROM designs d
        LEFT JOIN variants v ON d.id = v.design_id AND v.is_active = 1
        WHERE d.is_active = 1
        GROUP BY d.id
        ORDER BY d.created_at DESC
    ");
    $stmt->execute();
    $designs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($designs);
}

/**
 * Get single design with variants
 */
function getDesign() {
    global $pdo;

    $id = $_GET['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Design ID fehlt']);
        return;
    }

    // Get design
    $stmt = $pdo->prepare("SELECT * FROM designs WHERE id = ? AND is_active = 1");
    $stmt->execute([$id]);
    $design = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$design) {
        http_response_code(404);
        echo json_encode(['error' => 'Design nicht gefunden']);
        return;
    }

    // Get variants
    $stmt = $pdo->prepare("
        SELECT v.*,
               m.country_code, m.country_name,
               pt.name as product_type_name, pt.slug as product_type_slug
        FROM variants v
        INNER JOIN markets m ON v.market_id = m.id
        INNER JOIN product_types pt ON v.product_type_id = pt.id
        WHERE v.design_id = ? AND v.is_active = 1
        ORDER BY m.display_order, pt.display_order
    ");
    $stmt->execute([$id]);
    $design['variants'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($design);
}

/**
 * Add new design with variants
 */
function addDesign() {
    global $pdo;

    // Validate required fields
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $mockup_image_url = trim($_POST['mockup_image_url'] ?? '');
    $tags = trim($_POST['tags'] ?? '');

    if (empty($title)) {
        http_response_code(400);
        echo json_encode(['error' => 'Titel ist erforderlich']);
        return;
    }

    // Generate slug from title
    $slug = generateSlug($title);

    // Check if slug already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM designs WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetchColumn() > 0) {
        // Add timestamp to make it unique
        $slug .= '-' . time();
    }

    try {
        $pdo->beginTransaction();

        // Insert design
        $stmt = $pdo->prepare("
            INSERT INTO designs (title, slug, mockup_image_url, description, tags, is_active)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$title, $slug, $mockup_image_url, $description, $tags]);
        $designId = $pdo->lastInsertId();

        // Process variants
        $variants = json_decode($_POST['variants'] ?? '[]', true);

        if (!empty($variants)) {
            $stmt = $pdo->prepare("
                INSERT INTO variants (design_id, market_id, product_type_id, asin, price, is_active)
                VALUES (?, ?, ?, ?, ?, 1)
            ");

            foreach ($variants as $variant) {
                if (!empty($variant['asin'])) {
                    $stmt->execute([
                        $designId,
                        $variant['market_id'],
                        $variant['product_type_id'],
                        trim($variant['asin']),
                        $variant['price'] ?? null
                    ]);
                }
            }
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Design erfolgreich erstellt',
            'design_id' => $designId,
            'slug' => $slug
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Fehler beim Erstellen: ' . $e->getMessage()]);
    }
}

/**
 * Update design
 */
function updateDesign() {
    global $pdo;

    $id = $_POST['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Design ID fehlt']);
        return;
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $mockup_image_url = trim($_POST['mockup_image_url'] ?? '');
    $tags = trim($_POST['tags'] ?? '');

    if (empty($title)) {
        http_response_code(400);
        echo json_encode(['error' => 'Titel ist erforderlich']);
        return;
    }

    try {
        $pdo->beginTransaction();

        // Update design
        $stmt = $pdo->prepare("
            UPDATE designs
            SET title = ?, mockup_image_url = ?, description = ?, tags = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$title, $mockup_image_url, $description, $tags, $id]);

        // Delete existing variants and re-create them
        $stmt = $pdo->prepare("DELETE FROM variants WHERE design_id = ?");
        $stmt->execute([$id]);

        // Process variants
        $variants = json_decode($_POST['variants'] ?? '[]', true);

        if (!empty($variants)) {
            $stmt = $pdo->prepare("
                INSERT INTO variants (design_id, market_id, product_type_id, asin, price, is_active)
                VALUES (?, ?, ?, ?, ?, 1)
            ");

            foreach ($variants as $variant) {
                if (!empty($variant['asin'])) {
                    $stmt->execute([
                        $id,
                        $variant['market_id'],
                        $variant['product_type_id'],
                        trim($variant['asin']),
                        $variant['price'] ?? null
                    ]);
                }
            }
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Design erfolgreich aktualisiert'
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Fehler beim Aktualisieren: ' . $e->getMessage()]);
    }
}

/**
 * Delete design (soft delete)
 */
function deleteDesign() {
    global $pdo;

    $id = $_POST['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Design ID fehlt']);
        return;
    }

    try {
        // Soft delete design
        $stmt = $pdo->prepare("UPDATE designs SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);

        // Soft delete associated variants
        $stmt = $pdo->prepare("UPDATE variants SET is_active = 0 WHERE design_id = ?");
        $stmt->execute([$id]);

        echo json_encode([
            'success' => true,
            'message' => 'Design erfolgreich gelöscht'
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Fehler beim Löschen: ' . $e->getMessage()]);
    }
}

// ============================================================================
// MARKET FUNCTIONS
// ============================================================================

/**
 * Get all active markets
 */
function getMarkets() {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT * FROM markets
        WHERE is_active = 1
        ORDER BY display_order
    ");
    $stmt->execute();
    $markets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($markets);
}

// ============================================================================
// PRODUCT TYPE FUNCTIONS
// ============================================================================

/**
 * Get all active product types
 */
function getProductTypes() {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT * FROM product_types
        WHERE is_active = 1
        ORDER BY display_order
    ");
    $stmt->execute();
    $productTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($productTypes);
}

// ============================================================================
// VARIANT FUNCTIONS
// ============================================================================

/**
 * Get all active variants with full details
 */
function getVariants() {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM active_variants ORDER BY design_title, country_code, display_order");
    $stmt->execute();
    $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($variants);
}

/**
 * Get variants for a specific design
 */
function getDesignVariants() {
    global $pdo;

    $designId = $_GET['design_id'] ?? null;

    if (!$designId) {
        http_response_code(400);
        echo json_encode(['error' => 'Design ID fehlt']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT * FROM active_variants
        WHERE design_id = ?
        ORDER BY country_code, display_order
    ");
    $stmt->execute([$designId]);
    $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($variants);
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Generate URL-friendly slug from title
 */
function generateSlug($text) {
    // Convert to lowercase
    $text = strtolower($text);

    // Replace German umlauts
    $text = str_replace(['ä', 'ö', 'ü', 'ß'], ['ae', 'oe', 'ue', 'ss'], $text);

    // Remove special characters and replace spaces with hyphens
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    $text = trim($text, '-');

    return $text;
}
