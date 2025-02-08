<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Gestion des pré-requêtes (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Connexion à la base de données
$config = include(__DIR__ . '/config/database.php');

try {
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8", $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur de connexion : " . $e->getMessage()]);
    exit();
}

// Traitement de la requête POST
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['user_id'], $data['game_id'], $data['score'], $data['time_remaining'])) {
    $stmt = $pdo->prepare("INSERT INTO game_sessions (user_id, game_id, score, time_remaining, completed) VALUES (?, ?, ?, ?, 1)");
    $result = $stmt->execute([
        $data['user_id'],
        $data['game_id'],
        $data['score'],
        $data['time_remaining']
    ]);

    if ($result) {
        echo json_encode(["success" => true, "message" => "Partie enregistrée avec succès."]);
    } else {
        echo json_encode(["success" => false, "message" => "Erreur lors de l'enregistrement de la partie."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Données incomplètes."]);
}
?>
