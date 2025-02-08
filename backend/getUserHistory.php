<?php
header("Access-Control-Allow-Origin: *"); // Autorise toutes les origines
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Connexion à la base de données
$config = include(__DIR__ . '/config/database.php');

try {
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8", $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur de connexion : " . $e->getMessage()]);
    exit();
}

// Vérification de l'ID utilisateur
if (!isset($_GET['user_id'])) {
    echo json_encode(["success" => false, "message" => "ID utilisateur manquant."]);
    exit();
}

$user_id = intval($_GET['user_id']);

// Requête pour récupérer l'historique de l'utilisateur
$query = $pdo->prepare("
    SELECT gs.score, gs.time_remaining AS duration, gs.played_at, g.name AS game_name
    FROM game_sessions gs
    JOIN games g ON gs.game_id = g.id
    WHERE gs.user_id = ?
    ORDER BY gs.played_at DESC
");
$query->execute([$user_id]);
$history = $query->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($history);
?>
