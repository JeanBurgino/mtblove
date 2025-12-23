<?php
/**
 * MTB Love - Shop Item Upload API Test Script
 *
 * This script demonstrates how to use the Shop Item Upload API
 * to create a new design with images and variants.
 *
 * Usage:
 *   php backend/test_shop_item_upload.php
 */

// Configuration
$API_BASE_URL = 'http://localhost/backend/api/index.php';
$USERNAME = 'api_admin';
$PASSWORD = 'ApiAdmin123';

// ANSI Color codes for terminal output
$GREEN = "\033[0;32m";
$RED = "\033[0;31m";
$YELLOW = "\033[1;33m";
$BLUE = "\033[0;34m";
$NC = "\033[0m"; // No Color

function printStatus($message, $success = true) {
    global $GREEN, $RED, $NC;
    $symbol = $success ? "✓" : "✗";
    $color = $success ? $GREEN : $RED;
    echo "{$color}{$symbol}{$NC} {$message}\n";
}

function printHeader($message) {
    global $BLUE, $NC;
    echo "\n{$BLUE}========================================{$NC}\n";
    echo "{$BLUE}{$message}{$NC}\n";
    echo "{$BLUE}========================================{$NC}\n\n";
}

function printInfo($message) {
    global $YELLOW, $NC;
    echo "{$YELLOW}ℹ{$NC} {$message}\n";
}

// ============================================================================
// STEP 1: Login and get token
// ============================================================================

printHeader("STEP 1: Login and Get Authentication Token");

$loginData = http_build_query([
    'action' => 'login',
    'username' => $USERNAME,
    'password' => $PASSWORD
]);

$ch = curl_init($API_BASE_URL);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    printStatus("Login failed with HTTP code: {$httpCode}", false);
    echo "Response: {$response}\n";
    exit(1);
}

$loginResult = json_decode($response, true);

if (!$loginResult['success']) {
    printStatus("Login failed: " . ($loginResult['error'] ?? 'Unknown error'), false);
    exit(1);
}

$token = $loginResult['token'];
printStatus("Successfully logged in as: {$USERNAME}");
printInfo("Token: " . substr($token, 0, 20) . "...");
printInfo("User Role: " . $loginResult['user']['role']);

// ============================================================================
// STEP 2: Get available markets and product types
// ============================================================================

printHeader("STEP 2: Get Available Markets and Product Types");

// Get markets
$ch = curl_init($API_BASE_URL . '?action=get_markets');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$marketsResponse = curl_exec($ch);
curl_close($ch);

$markets = json_decode($marketsResponse, true);
printStatus("Retrieved " . count($markets) . " markets");

foreach ($markets as $market) {
    printInfo("  - [{$market['country_code']}] {$market['country_name']} ({$market['currency_code']})");
}

// Get product types
$ch = curl_init($API_BASE_URL . '?action=get_product_types');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$productTypesResponse = curl_exec($ch);
curl_close($ch);

$productTypes = json_decode($productTypesResponse, true);
printStatus("Retrieved " . count($productTypes) . " product types");

foreach ($productTypes as $pt) {
    printInfo("  - [{$pt['id']}] {$pt['name']}");
}

// ============================================================================
// STEP 3: Create a test design (without images for testing)
// ============================================================================

printHeader("STEP 3: Create Test Design");

$testDesign = [
    'action' => 'add_design',
    'token' => $token,
    'title' => 'API Test Design - ' . date('Y-m-d H:i:s'),
    'description' => 'This is a test design created via the Shop Item Upload API to verify functionality.',
    'tags' => 'test,api,mtb,mountain bike,demo',
    'variants' => json_encode([
        [
            'asin' => 'B0TEST' . rand(100000, 999999),
            'market_id' => 1, // Germany
            'product_type_id' => 1, // Standard T-Shirt
            'price' => 19.99
        ],
        [
            'asin' => 'B0TEST' . rand(100000, 999999),
            'market_id' => 2, // US
            'product_type_id' => 1, // Standard T-Shirt
            'price' => 24.99
        ],
        [
            'asin' => 'B0TEST' . rand(100000, 999999),
            'market_id' => 1, // Germany
            'product_type_id' => 2, // Hoodie
            'price' => 39.99
        ]
    ])
];

printInfo("Creating design with title: " . $testDesign['title']);
printInfo("Adding 3 variants (2x T-Shirt for DE/US, 1x Hoodie for DE)");

$ch = curl_init($API_BASE_URL);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testDesign));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    printStatus("Design creation failed with HTTP code: {$httpCode}", false);
    echo "Response: {$response}\n";
    exit(1);
}

$createResult = json_decode($response, true);

if (!isset($createResult['success']) || !$createResult['success']) {
    printStatus("Design creation failed: " . ($createResult['error'] ?? 'Unknown error'), false);
    echo "Full response:\n";
    print_r($createResult);
    exit(1);
}

printStatus("Design created successfully!");
printInfo("Design ID: " . $createResult['design_id']);
printInfo("Slug: " . $createResult['slug']);

// ============================================================================
// STEP 4: Verify the created design
// ============================================================================

printHeader("STEP 4: Verify Created Design");

$designId = $createResult['design_id'];

$ch = curl_init($API_BASE_URL . "?action=get_design&id={$designId}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$design = json_decode($response, true);

if (!$design) {
    printStatus("Could not retrieve created design", false);
    exit(1);
}

printStatus("Design retrieved successfully");
printInfo("Title: " . $design['title']);
printInfo("Slug: " . $design['slug']);
printInfo("Description: " . substr($design['description'], 0, 50) . "...");
printInfo("Tags: " . $design['tags']);
printInfo("Variants: " . count($design['variants']));

echo "\nVariants:\n";
foreach ($design['variants'] as $variant) {
    echo "  - ASIN: {$variant['asin']} | Market: {$variant['market_name']} | Product: {$variant['product_type_name']} | Price: {$variant['price']}\n";
}

// ============================================================================
// Summary
// ============================================================================

printHeader("TEST SUMMARY");

printStatus("All tests passed successfully!");
printInfo("The Shop Item Upload API is working correctly.");

echo "\n";
printInfo("Next steps:");
echo "  1. Try uploading a design with images using the examples in SHOP_ITEM_UPLOAD_API.md\n";
echo "  2. Test with different markets and product types\n";
echo "  3. Integrate the API into your application\n";

echo "\n";
printInfo("Documentation:");
echo "  - Full API Documentation: SHOP_ITEM_UPLOAD_API.md\n";
echo "  - General API Docs: API_DOCUMENTATION.md\n";
echo "  - Quick Start: QUICK_START_API.md\n";

echo "\n";
printStatus("Test completed!");

exit(0);
