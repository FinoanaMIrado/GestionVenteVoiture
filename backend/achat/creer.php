<?php
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json; charset=utf-8");

require '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

$idvoit = $data['idvoit'];
$idcli = $data['idcli'];
$qte = (int)$data['qte'];

$numAchat = 'ACH' . date('Ymd') . strtoupper(substr(uniqid(), -6));
$today = date('Y-m-d');

$conn->begin_transaction();

try {
    $stockQuery = "SELECT nombre, prix FROM voiture WHERE idvoit = ? FOR UPDATE";
    $stmt = $conn->prepare($stockQuery);
    $stmt->bind_param("s", $idvoit);
    $stmt->execute();
    $result = $stmt->get_result();
    $voiture = $result->fetch_assoc();

    if (!$voiture) throw new Exception('Voiture non trouvée');
    if ($voiture['nombre'] < $qte) throw new Exception('Stock insuffisant. Stock actuel: ' . $voiture['nombre']);

    $updateStock = "UPDATE voiture SET nombre = nombre - ? WHERE idvoit = ?";
    $stmt = $conn->prepare($updateStock);
    $stmt->bind_param("is", $qte, $idvoit);
    $stmt->execute();

    $sql = "INSERT INTO achat (numAchat, idcli, idvoit, date, qte) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $numAchat, $idcli, $idvoit, $today, $qte);
    $stmt->execute();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Achat effectué avec succès',
        'numAchat' => $numAchat
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
