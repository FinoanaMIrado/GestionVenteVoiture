<?php
require_once __DIR__ . '/config/cors.php';
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . '/config/database.php';

session_start();

$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

$sql = "SELECT * FROM utilisateur WHERE nomUtilisateur = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['motDePasse'])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user['nomUtilisateur'];
        $_SESSION['user_id'] = $user['NumeroUtilisateur'];
        $_SESSION['login_time'] = time();
        echo json_encode(['success' => true, 'message' => 'Connexion réussie']);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Identifiants incorrects']);
    }
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Identifiants incorrects']);
}

$stmt->close();
$conn->close();
