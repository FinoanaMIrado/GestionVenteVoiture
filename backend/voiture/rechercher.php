<?php
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json");

require '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);
$mot = $data['mot'] ?? '';

$mot = '%' . $conn->real_escape_string($mot) . '%';

$sql = "SELECT * FROM voiture WHERE idvoit LIKE ? OR Design LIKE ? ORDER BY idvoit";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $mot, $mot);
$stmt->execute();

$result = $stmt->get_result();
$voitures = [];

while ($row = $result->fetch_assoc()) {
    $voitures[] = $row;
}

echo json_encode($voitures);

$stmt->close();
$conn->close();
?>
