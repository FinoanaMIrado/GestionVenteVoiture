<?php
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json");

require '../config/database.php';

$sql = "SELECT * FROM voiture ORDER BY idvoit";
$result = $conn->query($sql);

$voitures = [];
while ($row = $result->fetch_assoc()) {
    $voitures[] = $row;
}

echo json_encode($voitures);
$conn->close();
?>
