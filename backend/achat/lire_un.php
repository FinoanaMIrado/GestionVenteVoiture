<?php
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json");

require '../config/database.php';

$numAchat = $_GET['id'] ?? '';

$sql = "SELECT a.numAchat, a.idcli, c.nom as client_nom, c.contact as client_contact,
               a.idvoit, v.Design as voiture_design, v.prix as prix_unitaire,
               a.qte, (a.qte * v.prix) as total, a.date
        FROM achat a
        JOIN voiture v ON a.idvoit = v.idvoit
        JOIN client c ON a.idcli = c.idcli
        WHERE a.numAchat = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $numAchat);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Achat non trouvé']);
}

$stmt->close();
$conn->close();
