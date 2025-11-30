<?php
/**
 * MTB Love - API Router
 * Haupteinstiegspunkt für alle API-Requests
 */

require_once __DIR__ . '/../config.php';

// CORS Headers
header('Access-Control-Allow-Origin: ' . CORS_ALLOWED_ORIGINS);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Request Method und Action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Route Request basierend auf Action
try {
    switch ($action) {
        // Auth Endpoints
        case 'login':
            require_once __DIR__ . '/auth.php';
            handleLogin();
            break;

        case 'logout':
            require_once __DIR__ . '/auth.php';
            handleLogout();
            break;

        case 'check_auth':
            require_once __DIR__ . '/auth.php';
            handleCheckAuth();
            break;

        // Wallpaper Endpoints
        case 'get_wallpapers':
            require_once __DIR__ . '/wallpapers.php';
            getWallpapers();
            break;

        case 'get_wallpaper':
            require_once __DIR__ . '/wallpapers.php';
            getWallpaper();
            break;

        case 'add_wallpaper':
            require_once __DIR__ . '/wallpapers.php';
            addWallpaper();
            break;

        case 'update_wallpaper':
            require_once __DIR__ . '/wallpapers.php';
            updateWallpaper();
            break;

        case 'delete_wallpaper':
            require_once __DIR__ . '/wallpapers.php';
            deleteWallpaper();
            break;

        case 'increment_download':
            require_once __DIR__ . '/wallpapers.php';
            incrementDownload();
            break;

        case 'toggle_like':
            require_once __DIR__ . '/wallpapers.php';
            toggleLike();
            break;

        // Product Endpoints
        case 'get_products':
            require_once __DIR__ . '/products.php';
            getProducts();
            break;

        case 'get_product':
            require_once __DIR__ . '/products.php';
            getProduct();
            break;

        case 'add_product':
            require_once __DIR__ . '/products.php';
            addProduct();
            break;

        case 'update_product':
            require_once __DIR__ . '/products.php';
            updateProduct();
            break;

        case 'delete_product':
            require_once __DIR__ . '/products.php';
            deleteProduct();
            break;

        // Excuse Endpoints
        case 'get_random_excuse':
            require_once __DIR__ . '/excuses.php';
            getRandomExcuse();
            break;

        case 'get_all_excuses':
            require_once __DIR__ . '/excuses.php';
            getAllExcuses();
            break;

        // Stats Endpoints
        case 'get_stats':
        case 'get_admin_stats':
            require_once __DIR__ . '/stats.php';
            getStats();
            break;

        // Social Media Stats Endpoints
        case 'get_social_stats':
            require_once __DIR__ . '/social.php';
            getSocialStats();
            break;

        case 'update_social_stats':
            require_once __DIR__ . '/social.php';
            updateSocialStats();
            break;

        default:
            sendError('Ungültige Aktion', 400);
    }
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    sendError('Ein Fehler ist aufgetreten', 500);
}
