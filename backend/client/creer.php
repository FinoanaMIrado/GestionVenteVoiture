<?php
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json");

require '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

$idcli = $data['idcli'] ?? '';
$nom = $data['nom'];
$contact = $data['contact'];
$statut = $data['statut'] ?? 'En attente';

if ($idcli) {
    $sql = "INSERT INTO client (idcli, nom, contact, statut) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $idcli, $nom, $contact, $statut);
} else {
    $sql = "INSERT INTO client (nom, contact, statut) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nom, $contact, $statut);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
