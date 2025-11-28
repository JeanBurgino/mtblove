<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$host = 'localhost';
$db   = 'mtb_love_db';
$user = 'root';
$pass = ''; // Dein DB Passwort

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Datenbankfehler']));
}

$action = $_POST['action'] ?? '';

switch($action) {
    case 'get_random_excuse':
        $stmt = $pdo->query("SELECT text FROM excuses ORDER BY RAND() LIMIT 1");
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        break;

    case 'get_artworks':
        $stmt = $pdo->query("SELECT * FROM artworks");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'login':
        $user = $_POST['user'] ?? '';
        $pass = $_POST['pass'] ?? '';
        // Einfacher Check (In Produktion: password_verify mit Hash nutzen!)
        if ($user === 'admin' && $pass === 'admin') {
            echo json_encode(['success' => true, 'token' => bin2hex(random_bytes(16))]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falsche Daten']);
        }
        break;

    default:
        echo json_encode(['error' => 'Ungültige Aktion']);
}
?>