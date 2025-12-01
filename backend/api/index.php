<?php
/**
 * MTB Love - API Router
 * Haupteinstiegspunkt für alle API-Requests
 */

require_once __DIR__ . '/../config.php';

// Start session before any output
startSession();

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

        // Design Shop Endpoints
        case 'get_designs':
            require_once __DIR__ . '/designs.php';
            getDesigns();
            break;

        case 'get_design':
            require_once __DIR__ . '/designs.php';
            getDesign();
            break;

        case 'add_design':
            require_once __DIR__ . '/designs.php';
            addDesign();
            break;

        case 'update_design':
            require_once __DIR__ . '/designs.php';
            updateDesign();
            break;

        case 'delete_design':
            require_once __DIR__ . '/designs.php';
            deleteDesign();
            break;

        case 'get_markets':
            require_once __DIR__ . '/designs.php';
            getMarkets();
            break;

        case 'get_product_types':
            require_once __DIR__ . '/designs.php';
            getProductTypes();
            break;

        case 'get_variants':
            require_once __DIR__ . '/designs.php';
            getVariants();
            break;

        case 'get_design_variants':
            require_once __DIR__ . '/designs.php';
            getDesignVariants();
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

        // User Management Endpoints
        case 'get_users':
            require_once __DIR__ . '/users.php';
            getUsers();
            break;

        case 'get_user':
            require_once __DIR__ . '/users.php';
            getUser();
            break;

        case 'create_user':
            require_once __DIR__ . '/users.php';
            createUser();
            break;

        case 'update_user':
            require_once __DIR__ . '/users.php';
            updateUser();
            break;

        case 'change_password':
            require_once __DIR__ . '/users.php';
            changePassword();
            break;

        case 'delete_user':
            require_once __DIR__ . '/users.php';
            deleteUser();
            break;

        default:
            sendError('Ungültige Aktion', 400);
    }
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    sendError('Ein Fehler ist aufgetreten', 500);
}
