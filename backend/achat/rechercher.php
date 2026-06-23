<?php
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json");

require '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);
$date_debut = $data['date_debut'] ?? '';
$date_fin = $data['date_fin'] ?? '';

$sql = "SELECT a.numAchat, a.idcli, c.nom as client_nom, a.idvoit, v.Design as voiture_design,
               v.prix as prix_unitaire, a.qte, (a.qte * v.prix) as total, a.date
        FROM achat a
        JOIN voiture v ON a.idvoit = v.idvoit
        JOIN client c ON a.idcli = c.idcli
        WHERE DATE(a.date) BETWEEN ? AND ?
        ORDER BY a.date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $date_debut, $date_fin);
$stmt->execute();
$result = $stmt->get_result();

$achats = [];
while ($row = $result->fetch_assoc()) {
    $achats[] = $row;
}

echo json_encode($achats);
$stmt->close();
$conn->close();
