<?php
session_start();
$host = 'localhost';
$db = 'escape_game_db';
$user = 'root';
$password = '';

// Connexion à la base de données
$config = include(__DIR__ . '/config/database.php');

// Vérification de la session utilisateur
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['username' => $user['username']]);
} else {
    echo json_encode(['username' => 'Invité']);
}
?>
