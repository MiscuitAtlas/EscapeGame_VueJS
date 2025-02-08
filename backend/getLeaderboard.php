<?php
header("Access-Control-Allow-Origin: *"); // Autorise toutes les origines
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=escape_game', 'root', '');

$game_id = isset($_GET['game_id']) ? intval($_GET['game_id']) : 1;

$sql = "SELECT u.username, MAX(gs.score) AS score, MAX(gs.time_remaining) AS duration
        FROM game_sessions gs
        JOIN users u ON gs.user_id = u.id
        WHERE gs.game_id = :game_id AND gs.completed = 1
        GROUP BY gs.user_id
        ORDER BY score DESC, time_remaining DESC
        LIMIT 10";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':game_id', $game_id, PDO::PARAM_INT);
$stmt->execute();

$leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($leaderboard);
?>
