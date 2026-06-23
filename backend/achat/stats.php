<?php
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json");

require '../config/database.php';

$sql = "SELECT DATE_FORMAT(a.date, '%Y-%m') as mois,
               SUM(a.qte * v.prix) as revenu,
               COUNT(*) as nombre_ventes
        FROM achat a
        JOIN voiture v ON a.idvoit = v.idvoit
        WHERE a.date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY mois
        ORDER BY mois ASC";
$result = $conn->query($sql);

$monthly = [];
while ($row = $result->fetch_assoc()) {
    $monthly[] = $row;
}

$totalRevenue = 0;
$totalSales = 0;
foreach ($monthly as $row) {
    $totalRevenue += floatval($row['revenu']);
    $totalSales += intval($row['nombre_ventes']);
}

echo json_encode([
    'monthly' => $monthly,
    'total_revenu' => $totalRevenue,
    'total_ventes' => $totalSales
]);
$conn->close();
