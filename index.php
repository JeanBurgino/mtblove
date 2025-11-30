<?php
/**
 * MTB Love - Main Router
 * Fallback für Shared Hosting ohne .htaccess Support
 *
 * Dieser Router leitet Requests weiter:
 * - API Requests → backend/api/index.php
 * - Frontend Requests → index.html (Alpine.js App)
 * - Statische Assets → direkt ausliefern
 */

// Aktuellen Request-URI ermitteln
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Query String für API-Requests
$queryString = $_SERVER['QUERY_STRING'] ?? '';

// 1. API Requests an backend/api/index.php weiterleiten
if (strpos($requestPath, '/api/') === 0 || strpos($requestPath, '/backend/api/') === 0) {
    // API Request - an backend/api/index.php weiterleiten
    require_once __DIR__ . '/backend/api/index.php';
    exit;
}

// Auch wenn "action=" im Query String ist, ist es wahrscheinlich eine API-Request
if (!empty($queryString) && strpos($queryString, 'action=') !== false) {
    require_once __DIR__ . '/backend/api/index.php';
    exit;
}

// 2. Upload-Verzeichnis: Dateien direkt ausliefern
if (strpos($requestPath, '/uploads/') === 0) {
    $filePath = __DIR__ . $requestPath;
    if (file_exists($filePath) && is_file($filePath)) {
        // MIME-Type ermitteln
        $mimeType = mime_content_type($filePath);
        header('Content-Type: ' . $mimeType);
        readfile($filePath);
        exit;
    }
}

// 3. Assets-Verzeichnis direkt ausliefern
if (strpos($requestPath, '/assets/') === 0) {
    $filePath = __DIR__ . $requestPath;
    if (file_exists($filePath) && is_file($filePath)) {
        // MIME-Type ermitteln
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'map' => 'application/json'
        ];

        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        header('Content-Type: ' . $mimeType);
        readfile($filePath);
        exit;
    }
}

// 4. Root-Level statische Dateien (logo.svg, etc.)
if ($requestPath !== '/' && $requestPath !== '/index.php') {
    $filePath = __DIR__ . $requestPath;
    if (file_exists($filePath) && is_file($filePath)) {
        // Sicherheit: Verhindere Zugriff auf sensible Dateien
        $basename = basename($filePath);
        $deniedFiles = ['config.php', '.env', '.htaccess', 'composer.json', 'package.json'];

        if (!in_array($basename, $deniedFiles) && strpos($basename, '.') !== 0) {
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $mimeTypes = [
                'svg' => 'image/svg+xml',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'ico' => 'image/x-icon'
            ];

            $mimeType = $mimeTypes[$extension] ?? mime_content_type($filePath);
            header('Content-Type: ' . $mimeType);
            readfile($filePath);
            exit;
        }
    }
}

// 5. Alle anderen Requests: Alpine.js Frontend ausliefern
$frontendIndexPath = __DIR__ . '/index.html';

if (file_exists($frontendIndexPath)) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($frontendIndexPath);
    exit;
} else {
    // Fehler: Frontend nicht gefunden
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MTB Love - Setup erforderlich</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
                max-width: 800px;
                margin: 50px auto;
                padding: 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            .container {
                background: rgba(255, 255, 255, 0.1);
                padding: 30px;
                border-radius: 10px;
                backdrop-filter: blur(10px);
            }
            h1 { margin-top: 0; }
            code {
                background: rgba(0, 0, 0, 0.3);
                padding: 2px 6px;
                border-radius: 4px;
                font-family: 'Courier New', monospace;
            }
            pre {
                background: rgba(0, 0, 0, 0.3);
                padding: 15px;
                border-radius: 5px;
                overflow-x: auto;
            }
            .status {
                margin: 20px 0;
                padding: 15px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 5px;
            }
            .error { color: #ff6b6b; }
            .success { color: #51cf66; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🚵‍♂️ MTB Love Setup</h1>

            <div class="status error">
                <strong>❌ Frontend nicht gefunden</strong><br>
                Die Alpine.js App wurde nicht gefunden.
            </div>

            <h2>Nächste Schritte:</h2>

            <p>Die index.html wurde nicht gefunden unter:</p>
            <code><?php echo $frontendIndexPath; ?></code>

            <h3>Lösung:</h3>
            <p>Stellen Sie sicher, dass die index.html Datei im Root-Verzeichnis vorhanden ist.</p>

            <h3>Diagnose:</h3>
            <ul>
                <li><strong>Request URI:</strong> <?php echo htmlspecialchars($requestUri); ?></li>
                <li><strong>Request Path:</strong> <?php echo htmlspecialchars($requestPath); ?></li>
                <li><strong>Document Root:</strong> <?php echo htmlspecialchars($_SERVER['DOCUMENT_ROOT']); ?></li>
                <li><strong>Script Filename:</strong> <?php echo htmlspecialchars($_SERVER['SCRIPT_FILENAME']); ?></li>
            </ul>
        </div>
    </body>
    </html>
    <?php
    exit;
}
