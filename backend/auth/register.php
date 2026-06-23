<?php
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json; charset=utf-8");

$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Nom d\'utilisateur et mot de passe requis']);
    exit;
}

if (strlen($password) < 4) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Le mot de passe doit contenir au moins 4 caractères']);
    exit;
}

// Use PDO for secure password_hash() storage
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=gestionventevoiture;charset=utf8",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $check = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE nomUtilisateur = ?");
    $check->execute([$username]);
    if ($check->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Ce nom d\'utilisateur existe déjà']);
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO utilisateur (nomUtilisateur, motDePasse) VALUES (?, ?)");
    $stmt->execute([$username, $hashed]);

    echo json_encode(['success' => true, 'message' => 'Compte créé avec succès']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
}
