<?php
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json");

ini_set('display_errors', 0);

try {
    require '../config/database.php';
    
    if (!$conn) {
        throw new Exception('Connexion à la base de données échouée');
    }
    
    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);
    
    if (!$data) {
        throw new Exception('Données JSON invalides');
    }
    
    $idvoit = trim($data['idvoit'] ?? '');
    $design = trim($data['Design'] ?? '');
    $prix = (int)($data['prix'] ?? 0);
    $nombre = (int)($data['nombre'] ?? 0);
    $image = trim($data['image'] ?? '');
    
    if (!$idvoit) {
        throw new Exception('ID obligatoire');
    }
    
    // Si une nouvelle image est fournie
    if ($image && $image !== '') {
        $sql = "UPDATE voiture SET Design = ?, prix = ?, nombre = ?, image = ? WHERE idvoit = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Erreur prepare: ' . $conn->error);
        }
        
        $stmt->bind_param("siiss", $design, $prix, $nombre, $image, $idvoit);
    } else {
        // Sinon, on garde l'ancienne image
        $sql = "UPDATE voiture SET Design = ?, prix = ?, nombre = ? WHERE idvoit = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Erreur prepare: ' . $conn->error);
        }
        
        $stmt->bind_param("siis", $design, $prix, $nombre, $idvoit);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Erreur execute: ' . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode(['success' => true, 'message' => 'Voiture modifiée avec succès']);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
