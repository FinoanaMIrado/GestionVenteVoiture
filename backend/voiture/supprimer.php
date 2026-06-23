<?php
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json");

require '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

$idvoit = $data['idvoit'];

$sql = "DELETE FROM voiture WHERE idvoit = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $idvoit);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
