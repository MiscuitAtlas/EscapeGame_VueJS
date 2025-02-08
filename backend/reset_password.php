<?php
header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Gérer les requêtes préflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

$config = include(__DIR__ . '/config/database.php');

try {
    $conn = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8",
        $config['username'],
        $config['password']
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erreur de connexion à la base de données", "details" => $e->getMessage()]);
    exit();
}

// Lire les données JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["error" => "Données JSON invalides."]);
    exit();
}

$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$newPassword = $data['newPassword'];

// Journalisation des données reçues pour le débogage
file_put_contents('debug.log', json_encode($data) . PHP_EOL, FILE_APPEND);

if (empty($email) || empty($newPassword)) {
    echo json_encode(["error" => "Tous les champs sont obligatoires."]);
    exit();
}

// Vérifie si l'utilisateur existe
try {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE users SET password = :password WHERE email = :email");
        $updateStmt->bindParam(':password', $hashedPassword);
        $updateStmt->bindParam(':email', $email);

        if ($updateStmt->execute()) {
            echo json_encode(["success" => true, "message" => "Mot de passe réinitialisé avec succès."]);
        } else {
            $errorInfo = $updateStmt->errorInfo();
            echo json_encode(["error" => "Erreur lors de la mise à jour du mot de passe.", "details" => $errorInfo]);
        }
    } else {
        echo json_encode(["error" => "Adresse email non trouvée."]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Erreur SQL", "details" => $e->getMessage()]);
}
?>
