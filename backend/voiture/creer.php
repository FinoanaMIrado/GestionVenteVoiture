<?php
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json; charset=utf-8");

// Désactiver l'affichage des erreurs en HTML
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Tout passer par des logs
error_log("=== CREER.PHP START ===");

try {
    // Inclure la config
    $config_path = dirname(__DIR__) . '/config/database.php';
    error_log("Loading config from: " . $config_path);
    
    if (!file_exists($config_path)) {
        throw new Exception('Config file not found: ' . $config_path);
    }
    
    require $config_path;
    
    if (!isset($conn) || !$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Lire les données JSON
    $input = file_get_contents("php://input");
    error_log("Raw input: " . substr($input, 0, 200));
    
    $data = json_decode($input, true);
    
    if ($data === null) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }
    
    // Extraire les données
    $idvoit = isset($data['idvoit']) ? trim($data['idvoit']) : '';
    $design = isset($data['Design']) ? trim($data['Design']) : '';
    $prix = isset($data['prix']) ? (int)$data['prix'] : 0;
    $nombre = isset($data['nombre']) ? (int)$data['nombre'] : 0;
    $image = isset($data['image']) ? trim($data['image']) : '';
    
    error_log("Data extracted: idvoit=$idvoit, design=$design, prix=$prix, nombre=$nombre, image=$image");
    
    // Validation
    if (empty($idvoit) || empty($design)) {
        throw new Exception('ID et Désignation obligatoires');
    }
    
    // Préparer et exécuter la requête
    $sql = "INSERT INTO voiture (idvoit, Design, prix, nombre, image) VALUES (?, ?, ?, ?, ?)";
    error_log("SQL: " . $sql);
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Prepare error: ' . $conn->error);
    }
    
    error_log("Binding params: s=$idvoit, s=$design, i=$prix, i=$nombre, s=$image");
    
    $stmt->bind_param("ssiis", $idvoit, $design, $prix, $nombre, $image);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute error: ' . $stmt->error);
    }
    
    error_log("Insert successful");
    
    $stmt->close();
    $conn->close();
    
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Voiture ajoutée']);
    
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (Throwable $t) {
    error_log("Throwable: " . $t->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $t->getMessage()]);
}

error_log("=== CREER.PHP END ===");
?>
