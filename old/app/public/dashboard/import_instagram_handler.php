<?php
/**
 * Instagram Import Handler
 *
 * Importiert Instagram Posts über die Meta Graph API
 * und speichert sie in der Datenbank
 */

require_once __DIR__ . '/../../config/config.php';

// Login-Pflicht für diese Seite
requireLogin();

// Nur POST-Anfragen erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Nur POST-Anfragen erlaubt.']);
    exit;
}

// JSON Response Header setzen
header('Content-Type: application/json');

// Ergebnis-Array initialisieren
$result = [
    'success' => false,
    'message' => '',
    'imported_count' => 0,
    'errors' => []
];

/**
 * Ruft Instagram Posts von der Graph API ab
 *
 * @return array|false Array mit Posts oder false bei Fehler
 */
function fetchInstagramPosts() {
    $access_token = INSTAGRAM_ACCESS_TOKEN;
    $user_id = INSTAGRAM_USER_ID;
    $api_version = INSTAGRAM_API_VERSION;

    // Validierung der Konfiguration
    if (empty($access_token) || empty($user_id)) {
        return false;
    }

    // Graph API Endpoint für Media-Liste
    $media_url = "https://graph.facebook.com/{$api_version}/{$user_id}/media";
    $media_url .= "?fields=id,caption,media_type,media_url,permalink,timestamp";
    $media_url .= "&access_token={$access_token}";
    $media_url .= "&limit=25"; // Maximale Anzahl Posts pro Request

    // API Request durchführen
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $media_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // HTTP-Statuscode prüfen
    if ($http_code !== 200) {
        error_log("Instagram API Error: HTTP {$http_code} - {$response}");
        return false;
    }

    $data = json_decode($response, true);

    if (!isset($data['data']) || !is_array($data['data'])) {
        error_log("Instagram API Error: Invalid response format");
        return false;
    }

    return $data['data'];
}

/**
 * Ruft detaillierte Daten für einen einzelnen Post ab (Likes, Comments)
 *
 * @param string $media_id Instagram Media ID
 * @return array Post-Details
 */
function fetchPostDetails($media_id) {
    $access_token = INSTAGRAM_ACCESS_TOKEN;
    $api_version = INSTAGRAM_API_VERSION;

    // Post-Details mit Likes und Comments abrufen
    $details_url = "https://graph.facebook.com/{$api_version}/{$media_id}";
    $details_url .= "?fields=like_count,comments_count";
    $details_url .= "&access_token={$access_token}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $details_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $details = [
        'like_count' => 0,
        'comments_count' => 0
    ];

    if ($http_code === 200) {
        $data = json_decode($response, true);
        $details['like_count'] = $data['like_count'] ?? 0;
        $details['comments_count'] = $data['comments_count'] ?? 0;
    }

    return $details;
}

/**
 * Ruft Insights für einen einzelnen Post ab
 *
 * @param string $media_id Instagram Media ID
 * @return array Insights-Daten
 */
function fetchPostInsights($media_id) {
    $access_token = INSTAGRAM_ACCESS_TOKEN;
    $api_version = INSTAGRAM_API_VERSION;

    // Insights für den Post abrufen
    // Verfügbare Metriken: impressions, reach, engagement, saved, video_views
    $insights_url = "https://graph.facebook.com/{$api_version}/{$media_id}/insights";
    $insights_url .= "?metric=impressions,reach,engagement,saved";
    $insights_url .= "&access_token={$access_token}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $insights_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $insights = [
        'impressions' => 0,
        'reach' => 0,
        'engagement' => 0,
        'saved' => 0
    ];

    if ($http_code === 200) {
        $data = json_decode($response, true);
        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $metric) {
                $name = $metric['name'];
                $value = $metric['values'][0]['value'] ?? 0;
                if (isset($insights[$name])) {
                    $insights[$name] = $value;
                }
            }
        }
    }

    return $insights;
}

/**
 * Extrahiert Hashtags aus einem Caption-Text
 *
 * @param string $caption Post Caption
 * @return string Komma-getrennte Hashtags
 */
function extractHashtags($caption) {
    if (empty($caption)) {
        return '';
    }

    preg_match_all('/#(\w+)/', $caption, $matches);

    if (empty($matches[0])) {
        return '';
    }

    return implode(', ', $matches[0]);
}

/**
 * Importiert einen Instagram Post in die Datenbank
 *
 * @param array $post Post-Daten von der API
 * @param PDO $pdo Datenbankverbindung
 * @return bool Erfolg
 */
function importPost($post, $pdo) {
    try {
        // Nur Foto- und Video-Posts importieren
        if (!isset($post['media_type']) || !in_array($post['media_type'], ['IMAGE', 'VIDEO', 'CAROUSEL_ALBUM'])) {
            return false;
        }

        // Post ID
        $ig_post_id = $post['id'];

        // Prüfen ob Post bereits existiert
        $stmt = $pdo->prepare("SELECT id FROM instagram_posts WHERE ig_post_id = ?");
        $stmt->execute([$ig_post_id]);

        if ($stmt->fetch()) {
            // Post existiert bereits - Update durchführen
            return updateExistingPost($post, $pdo);
        }

        // Post-Details abrufen (Likes, Comments)
        $details = fetchPostDetails($ig_post_id);

        // Insights abrufen (Views, Engagement, Saves)
        $insights = fetchPostInsights($ig_post_id);

        // Post-Daten vorbereiten
        $caption = $post['caption'] ?? '';
        $hashtags = extractHashtags($caption);
        $image_url = $post['media_url'] ?? $post['permalink'] ?? '';
        $post_date = isset($post['timestamp']) ? date('Y-m-d H:i:s', strtotime($post['timestamp'])) : date('Y-m-d H:i:s');

        // Metriken
        $views = $insights['impressions'];
        $likes = $details['like_count'];
        $comments = $details['comments_count'];
        $shares = 0; // Shares sind nicht über die API verfügbar
        $saves = $insights['saved'];

        // In Datenbank einfügen
        $stmt = $pdo->prepare("
            INSERT INTO instagram_posts
            (ig_post_id, image_url, caption, hashtags, post_date, views, likes, comments, shares, saves)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $ig_post_id,
            $image_url,
            $caption,
            $hashtags,
            $post_date,
            $views,
            $likes,
            $comments,
            $shares,
            $saves
        ]);

        return true;

    } catch (PDOException $e) {
        error_log('Instagram import error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Aktualisiert einen existierenden Post
 *
 * @param array $post Post-Daten von der API
 * @param PDO $pdo Datenbankverbindung
 * @return bool Erfolg
 */
function updateExistingPost($post, $pdo) {
    try {
        $ig_post_id = $post['id'];

        // Post-Details abrufen (Likes, Comments)
        $details = fetchPostDetails($ig_post_id);

        // Insights abrufen (Views, Engagement, Saves)
        $insights = fetchPostInsights($ig_post_id);

        // Alle Metriken aktualisieren
        $stmt = $pdo->prepare("
            UPDATE instagram_posts
            SET views = ?, likes = ?, comments = ?, saves = ?
            WHERE ig_post_id = ?
        ");

        $stmt->execute([
            $insights['impressions'],
            $details['like_count'],
            $details['comments_count'],
            $insights['saved'],
            $ig_post_id
        ]);

        return true;

    } catch (PDOException $e) {
        error_log('Instagram update error: ' . $e->getMessage());
        return false;
    }
}

// Hauptlogik: Instagram Posts importieren
try {
    // Konfiguration prüfen
    if (empty(INSTAGRAM_ACCESS_TOKEN) || empty(INSTAGRAM_USER_ID)) {
        $result['message'] = 'Instagram API nicht konfiguriert. Bitte Access Token und User ID in der config.php eintragen.';
        echo json_encode($result);
        exit;
    }

    // Posts von Instagram abrufen
    $posts = fetchInstagramPosts();

    if ($posts === false) {
        $result['message'] = 'Fehler beim Abrufen der Instagram Posts. Prüfen Sie den Access Token und die User ID.';
        echo json_encode($result);
        exit;
    }

    if (empty($posts)) {
        $result['message'] = 'Keine Instagram Posts gefunden.';
        echo json_encode($result);
        exit;
    }

    // Posts importieren
    $imported_count = 0;
    foreach ($posts as $post) {
        if (importPost($post, $pdo)) {
            $imported_count++;
        }
    }

    // Erfolg
    $result['success'] = true;
    $result['message'] = "Instagram Import erfolgreich abgeschlossen!";
    $result['imported_count'] = $imported_count;

    // Analytics-Event tracken
    trackEvent('instagram_import', [
        'imported_count' => $imported_count,
        'total_posts' => count($posts)
    ]);

} catch (Exception $e) {
    error_log('Instagram import error: ' . $e->getMessage());
    $result['message'] = 'Fehler beim Import: ' . $e->getMessage();
}

echo json_encode($result);
?>
