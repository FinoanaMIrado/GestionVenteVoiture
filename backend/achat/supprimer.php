<?php
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json");

require '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);
$numAchat = $data['numAchat'];

$conn->begin_transaction();

try {
    $query = "SELECT idvoit, qte FROM achat WHERE numAchat = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $numAchat);
    $stmt->execute();
    $result = $stmt->get_result();
    $achat = $result->fetch_assoc();

    if (!$achat) throw new Exception('Achat non trouvé');

    $updateStock = "UPDATE voiture SET nombre = nombre + ? WHERE idvoit = ?";
    $stmt = $conn->prepare($updateStock);
    $stmt->bind_param("is", $achat['qte'], $achat['idvoit']);
    $stmt->execute();

    $delete = "DELETE FROM achat WHERE numAchat = ?";
    $stmt = $conn->prepare($delete);
    $stmt->bind_param("s", $numAchat);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Achat annulé, stock restauré']);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
