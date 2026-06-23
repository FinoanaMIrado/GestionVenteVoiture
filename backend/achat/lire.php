<?php
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json");

require '../config/database.php';

$sql = "SELECT a.numAchat, a.idcli, c.nom as client_nom, a.idvoit, v.Design as voiture_design,
               v.prix as prix_unitaire, a.qte, (a.qte * v.prix) as total, a.date
        FROM achat a
        JOIN voiture v ON a.idvoit = v.idvoit
        JOIN client c ON a.idcli = c.idcli
        ORDER BY a.date DESC";
$result = $conn->query($sql);

$achats = [];
while ($row = $result->fetch_assoc()) {
    $achats[] = $row;
}

echo json_encode($achats);
$conn->close();
