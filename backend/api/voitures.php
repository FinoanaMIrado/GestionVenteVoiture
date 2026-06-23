<?php
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

try {
    require_once __DIR__ . '/../config/connexion.php';

    $idvoit = trim($_POST['idvoit'] ?? '');
    $design = trim($_POST['Design'] ?? '');
    $prix = (int)($_POST['prix'] ?? 0);
    $nombre = (int)($_POST['nombre'] ?? 0);

    if (!$idvoit || !$design) {
        throw new Exception('ID et Désignation obligatoires');
    }

    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            throw new Exception('Format non autorisé: ' . $ext);
        }
        $image = time() . '_' . uniqid() . '.' . $ext;
        $upload_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        if (!move_uploaded_file($file['tmp_name'], $upload_dir . $image)) {
            throw new Exception("Erreur lors de l'enregistrement de l'image");
        }
    } else {
        $stmt = $pdo->prepare("SELECT image FROM voiture WHERE idvoit = ?");
        $stmt->execute([$idvoit]);
        $existing = $stmt->fetchColumn();
        $image = $existing ?: '';
    }

    $sql = "UPDATE voiture SET Design = ?, prix = ?, nombre = ?, image = ? WHERE idvoit = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$design, $prix, $nombre, $image, $idvoit]);

    echo json_encode(['success' => true, 'message' => 'Voiture modifiée avec succès', 'image' => $image]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
