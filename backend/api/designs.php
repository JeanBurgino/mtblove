<?php
/**
 * MTB Love - Design Shop API
 * Handles CRUD operations for designs, markets, product_types, and variants
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

// ============================================================================
// DESIGN FUNCTIONS
// ============================================================================

/**
 * Get all designs
 */
function getDesigns() {
    $pdo = getDBConnection();

    try {
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

        // Fetch variants with icons for each design
        // Check if icon column exists, otherwise use icon_class as fallback
        $iconColumn = 'icon_class';
        try {
            $checkColumn = $pdo->query("SHOW COLUMNS FROM product_types LIKE 'icon'");
            if ($checkColumn->rowCount() > 0) {
                $iconColumn = 'icon';
            }
        } catch (PDOException $e) {
            // If check fails, use icon_class as fallback
        }

        $variantStmt = $pdo->prepare("
            SELECT v.design_id,
                   m.country_flag,
                   pt.{$iconColumn} as product_type_icon
            FROM variants v
            INNER JOIN markets m ON v.market_id = m.id
            INNER JOIN product_types pt ON v.product_type_id = pt.id
            WHERE v.design_id = ? AND v.is_active = 1
        ");

        foreach ($designs as &$design) {
            $variantStmt->execute([$design['id']]);
            $design['variants'] = $variantStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        sendJSON($designs);
    } catch (PDOException $e) {
        error_log('Get designs error: ' . $e->getMessage());
        sendError('Designs konnten nicht geladen werden', 500);
    }
}

/**
 * Get single design with variants
 */
function getDesign() {
    $pdo = getDBConnection();
    $id = $_GET['id'] ?? null;

    if (!$id) {
        sendError('Design ID fehlt', 400);
    }

    try {
        // Get design
        $stmt = $pdo->prepare("SELECT * FROM designs WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        $design = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$design) {
            sendError('Design nicht gefunden', 404);
        }

        // Determine which icon column to use
        $iconColumn = 'icon_class';
        try {
            $checkColumn = $pdo->query("SHOW COLUMNS FROM product_types LIKE 'icon'");
            if ($checkColumn->rowCount() > 0) {
                $iconColumn = 'icon';
            }
        } catch (PDOException $e) {
            // If check fails, use icon_class as fallback
        }

        // Get variants
        $stmt = $pdo->prepare("
            SELECT v.*,
                   m.country_code, m.country_name,
                   pt.name as product_type_name, pt.slug as product_type_slug,
                   pt.{$iconColumn} as product_type_icon
            FROM variants v
            INNER JOIN markets m ON v.market_id = m.id
            INNER JOIN product_types pt ON v.product_type_id = pt.id
            WHERE v.design_id = ? AND v.is_active = 1
            ORDER BY m.display_order, pt.display_order
        ");
        $stmt->execute([$id]);
        $design['variants'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendJSON($design);
    } catch (PDOException $e) {
        error_log('Get design error: ' . $e->getMessage());
        sendError('Design konnte nicht geladen werden', 500);
    }
}

/**
 * Add new design with variants
 */
function addDesign() {
    requireAuth(['admin', 'editor']);

    $pdo = getDBConnection();

    // Validate required fields
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $tags = trim($_POST['tags'] ?? '');

    if (empty($title)) {
        sendError('Titel ist erforderlich', 400);
    }

    // Handle design image upload (saved to uploads/designs)
    $mockup_image_url = '';
    if (isset($_FILES['design_image']) && $_FILES['design_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['design_image'];

        // Validate file type
        if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
            sendError('Ungültiger Dateityp. Nur JPEG, PNG, GIF und WebP sind erlaubt.', 400);
        }

        // Validate file size
        if ($file['size'] > MAX_FILE_SIZE) {
            sendError('Datei zu groß (max 10MB)', 400);
        }

        // Create unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'design_' . time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = UPLOAD_DIR . 'designs/' . $filename;

        // Create directory if it doesn't exist
        if (!file_exists(UPLOAD_DIR . 'designs/')) {
            mkdir(UPLOAD_DIR . 'designs/', 0755, true);
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $mockup_image_url = '/uploads/designs/' . $filename;
        } else {
            sendError('Fehler beim Hochladen der Datei', 500);
        }
    }

    // Handle product type mockup images (saved to uploads/mockups)
    $productTypeMockups = [];
    foreach ($_FILES as $key => $file) {
        if (strpos($key, 'mockup_image_') === 0 && $file['error'] === UPLOAD_ERR_OK) {
            $productTypeId = str_replace('mockup_image_', '', $key);

            // Validate file type
            if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
                sendError('Ungültiger Dateityp für ' . $key . '. Nur JPEG, PNG, GIF und WebP sind erlaubt.', 400);
            }

            // Validate file size
            if ($file['size'] > MAX_FILE_SIZE) {
                sendError('Datei zu groß (max 10MB) für ' . $key, 400);
            }

            // Create unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'mockup_pt' . $productTypeId . '_' . time() . '_' . uniqid() . '.' . $extension;
            $uploadPath = UPLOAD_DIR . 'mockups/' . $filename;

            // Create directory if it doesn't exist
            if (!file_exists(UPLOAD_DIR . 'mockups/')) {
                mkdir(UPLOAD_DIR . 'mockups/', 0755, true);
            }

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $productTypeMockups[$productTypeId] = $filename; // Store only filename, not full path
            } else {
                sendError('Fehler beim Hochladen der Datei für ' . $key, 500);
            }
        }
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
                INSERT INTO variants (design_id, market_id, product_type_id, asin, price, mockup_image_url, is_active)
                VALUES (?, ?, ?, ?, ?, ?, 1)
            ");

            foreach ($variants as $variant) {
                if (!empty($variant['asin'])) {
                    // Get the mockup image URL for this product type
                    $mockupImageUrl = $productTypeMockups[$variant['product_type_id']] ?? null;

                    $stmt->execute([
                        $designId,
                        $variant['market_id'],
                        $variant['product_type_id'],
                        trim($variant['asin']),
                        $variant['price'] ?? null,
                        $mockupImageUrl
                    ]);
                }
            }
        }

        $pdo->commit();

        sendJSON([
            'success' => true,
            'message' => 'Design erfolgreich erstellt',
            'design_id' => $designId,
            'slug' => $slug
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Add design error: ' . $e->getMessage());
        sendError('Fehler beim Erstellen: ' . $e->getMessage(), 500);
    }
}

/**
 * Update design
 */
function updateDesign() {
    requireAuth(['admin', 'editor']);

    $pdo = getDBConnection();
    $id = $_POST['id'] ?? null;

    if (!$id) {
        sendError('Design ID fehlt', 400);
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $tags = trim($_POST['tags'] ?? '');

    if (empty($title)) {
        sendError('Titel ist erforderlich', 400);
    }

    // Get current design and variants
    $stmt = $pdo->prepare("SELECT mockup_image_url FROM designs WHERE id = ?");
    $stmt->execute([$id]);
    $currentDesign = $stmt->fetch(PDO::FETCH_ASSOC);
    $mockup_image_url = $currentDesign['mockup_image_url'];

    // Get existing variant mockup images (to preserve if not replaced)
    $stmt = $pdo->prepare("SELECT product_type_id, mockup_image_url FROM variants WHERE design_id = ? AND mockup_image_url IS NOT NULL");
    $stmt->execute([$id]);
    $existingMockups = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existingMockups[$row['product_type_id']] = $row['mockup_image_url'];
    }

    // Handle design image upload (if new file is provided)
    if (isset($_FILES['design_image']) && $_FILES['design_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['design_image'];

        // Validate file type
        if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
            sendError('Ungültiger Dateityp. Nur JPEG, PNG, GIF und WebP sind erlaubt.', 400);
        }

        // Validate file size
        if ($file['size'] > MAX_FILE_SIZE) {
            sendError('Datei zu groß (max 10MB)', 400);
        }

        // Delete old image if it exists
        if (!empty($mockup_image_url) && file_exists($_SERVER['DOCUMENT_ROOT'] . $mockup_image_url)) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $mockup_image_url);
        }

        // Create unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'design_' . time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = UPLOAD_DIR . 'designs/' . $filename;

        // Create directory if it doesn't exist
        if (!file_exists(UPLOAD_DIR . 'designs/')) {
            mkdir(UPLOAD_DIR . 'designs/', 0755, true);
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $mockup_image_url = '/uploads/designs/' . $filename;
        } else {
            sendError('Fehler beim Hochladen der Datei', 500);
        }
    }

    // Handle product type mockup images (saved to uploads/mockups)
    $productTypeMockups = $existingMockups; // Start with existing mockups
    foreach ($_FILES as $key => $file) {
        if (strpos($key, 'mockup_image_') === 0 && $file['error'] === UPLOAD_ERR_OK) {
            $productTypeId = str_replace('mockup_image_', '', $key);

            // Validate file type
            if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
                sendError('Ungültiger Dateityp für ' . $key . '. Nur JPEG, PNG, GIF und WebP sind erlaubt.', 400);
            }

            // Validate file size
            if ($file['size'] > MAX_FILE_SIZE) {
                sendError('Datei zu groß (max 10MB) für ' . $key, 400);
            }

            // Delete old mockup if it exists
            if (isset($existingMockups[$productTypeId]) && file_exists($_SERVER['DOCUMENT_ROOT'] . $existingMockups[$productTypeId])) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $existingMockups[$productTypeId]);
            }

            // Create unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'mockup_pt' . $productTypeId . '_' . time() . '_' . uniqid() . '.' . $extension;
            $uploadPath = UPLOAD_DIR . 'mockups/' . $filename;

            // Create directory if it doesn't exist
            if (!file_exists(UPLOAD_DIR . 'mockups/')) {
                mkdir(UPLOAD_DIR . 'mockups/', 0755, true);
            }

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $productTypeMockups[$productTypeId] = $filename; // Store only filename, not full path
            } else {
                sendError('Fehler beim Hochladen der Datei für ' . $key, 500);
            }
        }
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
                INSERT INTO variants (design_id, market_id, product_type_id, asin, price, mockup_image_url, is_active)
                VALUES (?, ?, ?, ?, ?, ?, 1)
            ");

            foreach ($variants as $variant) {
                if (!empty($variant['asin'])) {
                    // Get the mockup image URL for this product type
                    $mockupImageUrl = $productTypeMockups[$variant['product_type_id']] ?? null;

                    $stmt->execute([
                        $id,
                        $variant['market_id'],
                        $variant['product_type_id'],
                        trim($variant['asin']),
                        $variant['price'] ?? null,
                        $mockupImageUrl
                    ]);
                }
            }
        }

        $pdo->commit();

        sendJSON([
            'success' => true,
            'message' => 'Design erfolgreich aktualisiert'
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Update design error: ' . $e->getMessage());
        sendError('Fehler beim Aktualisieren: ' . $e->getMessage(), 500);
    }
}

/**
 * Delete design (soft delete)
 */
function deleteDesign() {
    requireAuth(['admin', 'editor']);

    $pdo = getDBConnection();
    $id = $_POST['id'] ?? null;

    if (!$id) {
        sendError('Design ID fehlt', 400);
    }

    try {
        // Soft delete design
        $stmt = $pdo->prepare("UPDATE designs SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);

        // Soft delete associated variants
        $stmt = $pdo->prepare("UPDATE variants SET is_active = 0 WHERE design_id = ?");
        $stmt->execute([$id]);

        sendJSON([
            'success' => true,
            'message' => 'Design erfolgreich gelöscht'
        ]);

    } catch (PDOException $e) {
        error_log('Delete design error: ' . $e->getMessage());
        sendError('Fehler beim Löschen: ' . $e->getMessage(), 500);
    }
}

// ============================================================================
// MARKET FUNCTIONS
// ============================================================================

/**
 * Get all active markets
 */
function getMarkets() {
    $pdo = getDBConnection();

    try {
        $stmt = $pdo->prepare("
            SELECT * FROM markets
            WHERE is_active = 1
            ORDER BY display_order
        ");
        $stmt->execute();
        $markets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendJSON($markets);
    } catch (PDOException $e) {
        error_log('Get markets error: ' . $e->getMessage());
        sendError('Märkte konnten nicht geladen werden', 500);
    }
}

// ============================================================================
// PRODUCT TYPE FUNCTIONS
// ============================================================================

/**
 * Get all active product types
 */
function getProductTypes() {
    $pdo = getDBConnection();

    try {
        $stmt = $pdo->prepare("
            SELECT * FROM product_types
            WHERE is_active = 1
            ORDER BY display_order
        ");
        $stmt->execute();
        $productTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendJSON($productTypes);
    } catch (PDOException $e) {
        error_log('Get product types error: ' . $e->getMessage());
        sendError('Produkttypen konnten nicht geladen werden', 500);
    }
}

// ============================================================================
// VARIANT FUNCTIONS
// ============================================================================

/**
 * Get all active variants with full details
 */
function getVariants() {
    $pdo = getDBConnection();

    try {
        $stmt = $pdo->prepare("SELECT * FROM active_variants ORDER BY design_title, country_code, display_order");
        $stmt->execute();
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendJSON($variants);
    } catch (PDOException $e) {
        error_log('Get variants error: ' . $e->getMessage());
        sendError('Varianten konnten nicht geladen werden', 500);
    }
}

/**
 * Get variants for a specific design
 */
function getDesignVariants() {
    $pdo = getDBConnection();
    $designId = $_GET['design_id'] ?? null;

    if (!$designId) {
        sendError('Design ID fehlt', 400);
    }

    try {
        $stmt = $pdo->prepare("
            SELECT * FROM active_variants
            WHERE design_id = ?
            ORDER BY country_code, display_order
        ");
        $stmt->execute([$designId]);
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendJSON($variants);
    } catch (PDOException $e) {
        error_log('Get design variants error: ' . $e->getMessage());
        sendError('Design-Varianten konnten nicht geladen werden', 500);
    }
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
