<?php
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json");

require '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

$idcli = $data['idcli'];

if (isset($data['statut'])) {
    $sql = "UPDATE client SET statut = ? WHERE idcli = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $data['statut'], $idcli);
} else {
    $nom = $data['nom'];
    $contact = $data['contact'];
    $sql = "UPDATE client SET nom = ?, contact = ? WHERE idcli = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nom, $contact, $idcli);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
