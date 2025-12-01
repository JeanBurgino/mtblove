<?php
/**
 * MTB Love - Products API
 * Handles shop product CRUD operations
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

/**
 * Get all products
 */
function getProducts() {
    $pdo = getDBConnection();

    $category = $_GET['category'] ?? 'all';

    try {
        if ($category === 'all') {
            $stmt = $pdo->query("SELECT * FROM products WHERE is_available = 1 ORDER BY created_at DESC");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE is_available = 1 AND category = :category ORDER BY created_at DESC");
            $stmt->execute(['category' => $category]);
        }

        $products = $stmt->fetchAll();

        // Preis formatieren
        foreach ($products as &$product) {
            $product['price_formatted'] = number_format($product['price'], 2, ',', '.') . ' ' . $product['currency'];
        }

        sendJSON($products);

    } catch (PDOException $e) {
        error_log('Get products error: ' . $e->getMessage());
        sendError('Produkte konnten nicht geladen werden', 500);
    }
}

/**
 * Get single product
 */
function getProduct() {
    $id = $_GET['id'] ?? 0;

    if (!$id) {
        sendError('ID erforderlich', 400);
    }

    $pdo = getDBConnection();

    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();

        if (!$product) {
            sendError('Produkt nicht gefunden', 404);
        }

        $product['price_formatted'] = number_format($product['price'], 2, ',', '.') . ' ' . $product['currency'];

        sendJSON($product);

    } catch (PDOException $e) {
        error_log('Get product error: ' . $e->getMessage());
        sendError('Produkt konnte nicht geladen werden', 500);
    }
}

/**
 * Add new product (Admin only)
 */
function addProduct() {
    requireAuth();

    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $category = $_POST['category'] ?? '';
    $tag = $_POST['tag'] ?? null;
    $stock = $_POST['stock_quantity'] ?? 0;

    if (empty($name) || $price <= 0) {
        sendError('Name und gültiger Preis erforderlich', 400);
    }

    // File Upload handling
    $imagePath = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];

        if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
            sendError('Ungültiger Dateityp', 400);
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            sendError('Datei zu groß (max 5MB)', 400);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('product_') . '.' . $extension;
        $filepath = UPLOAD_DIR . 'products/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            sendError('Upload fehlgeschlagen', 500);
        }

        $imagePath = '/uploads/products/' . $filename;
    }

    $pdo = getDBConnection();

    try {
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, price, image_path, category, tag, stock_quantity)
            VALUES (:name, :description, :price, :image_path, :category, :tag, :stock)
        ");

        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'image_path' => $imagePath,
            'category' => $category,
            'tag' => $tag,
            'stock' => $stock
        ]);

        $id = $pdo->lastInsertId();

        sendJSON([
            'success' => true,
            'id' => $id,
            'message' => 'Produkt erfolgreich hinzugefügt'
        ], 201);

    } catch (PDOException $e) {
        error_log('Add product error: ' . $e->getMessage());
        sendError('Produkt konnte nicht hinzugefügt werden', 500);
    }
}

/**
 * Update product (Admin only)
 */
function updateProduct() {
    requireAuth();

    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $category = $_POST['category'] ?? '';
    $tag = $_POST['tag'] ?? null;
    $stock = $_POST['stock_quantity'] ?? 0;

    if (!$id || empty($name) || $price <= 0) {
        sendError('ID, Name und gültiger Preis erforderlich', 400);
    }

    $pdo = getDBConnection();

    try {
        $stmt = $pdo->prepare("
            UPDATE products
            SET name = :name, description = :description, price = :price,
                category = :category, tag = :tag, stock_quantity = :stock
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'category' => $category,
            'tag' => $tag,
            'stock' => $stock
        ]);

        sendJSON([
            'success' => true,
            'message' => 'Produkt erfolgreich aktualisiert'
        ]);

    } catch (PDOException $e) {
        error_log('Update product error: ' . $e->getMessage());
        sendError('Produkt konnte nicht aktualisiert werden', 500);
    }
}

/**
 * Delete product (Admin only)
 */
function deleteProduct() {
    requireAuth();

    $id = $_POST['id'] ?? $_GET['id'] ?? 0;

    if (!$id) {
        sendError('ID erforderlich', 400);
    }

    $pdo = getDBConnection();

    try {
        // Soft delete
        $stmt = $pdo->prepare("UPDATE products SET is_available = 0 WHERE id = :id");
        $stmt->execute(['id' => $id]);

        sendJSON([
            'success' => true,
            'message' => 'Produkt erfolgreich gelöscht'
        ]);

    } catch (PDOException $e) {
        error_log('Delete product error: ' . $e->getMessage());
        sendError('Produkt konnte nicht gelöscht werden', 500);
    }
}
