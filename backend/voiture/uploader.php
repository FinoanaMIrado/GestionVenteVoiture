<?php
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'error' => 'Aucun fichier reçu']);
    exit;
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la limite autorisée par le serveur (upload_max_filesize)',
        UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la limite du formulaire',
        UPLOAD_ERR_PARTIAL => 'Upload interrompu',
        UPLOAD_ERR_NO_TMP_DIR => 'Répertoire temporaire manquant sur le serveur',
        UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire le fichier sur le disque',
    ];
    $msg = $errors[$file['error']] ?? 'Erreur inconnue';
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'error' => 'Format non autorisé: .' . $ext . ' (autorisés: jpg, jpeg, png, gif, webp)']);
    exit;
}

$filename = time() . '_' . uniqid() . '.' . $ext;
$upload_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$target = $upload_dir . $filename;

if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        echo json_encode(['success' => false, 'error' => 'Erreur serveur: impossible de créer le dossier d\'upload']);
        exit;
    }
}

if (!is_writable($upload_dir)) {
    echo json_encode(['success' => false, 'error' => 'Erreur serveur: le dossier d\'upload n\'est pas accessible en écriture']);
    exit;
}

if (move_uploaded_file($file['tmp_name'], $target)) {
    echo json_encode(['success' => true, 'filename' => $filename]);
} else {
    echo json_encode(['success' => false, 'error' => 'Erreur serveur lors de l\'enregistrement du fichier']);
}
?>
