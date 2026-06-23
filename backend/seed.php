<?php
require_once __DIR__ . '/config/cors.php';
header("Content-Type: application/json; charset=utf-8");

require __DIR__ . '/config/database.php';

try {
    // --- 1. Insérer des voitures ---
    $voitures = [
        ['idvoit' => 'V001', 'Design' => 'Toyota Corolla 2022', 'prix' => 35000000, 'nombre' => 5],
        ['idvoit' => 'V002', 'Design' => 'Honda Civic 2023', 'prix' => 42000000, 'nombre' => 3],
        ['idvoit' => 'V003', 'Design' => 'BMW Série 3 2022', 'prix' => 85000000, 'nombre' => 2],
        ['idvoit' => 'V004', 'Design' => 'Mercedes Classe C 2023', 'prix' => 95000000, 'nombre' => 1],
        ['idvoit' => 'V005', 'Design' => 'Peugeot 3008 2022', 'prix' => 38000000, 'nombre' => 4],
        ['idvoit' => 'V006', 'Design' => 'Renault Clio 2023', 'prix' => 18000000, 'nombre' => 6],
        ['idvoit' => 'V007', 'Design' => 'Volkswagen Golf 2022', 'prix' => 32000000, 'nombre' => 3],
        ['idvoit' => 'V008', 'Design' => 'Audi A4 2023', 'prix' => 72000000, 'nombre' => 0],
        ['idvoit' => 'V009', 'Design' => 'Nissan X-Trail 2022', 'prix' => 45000000, 'nombre' => 2],
        ['idvoit' => 'V010', 'Design' => 'Hyundai Tucson 2023', 'prix' => 40000000, 'nombre' => 1],
    ];

    foreach ($voitures as $v) {
        $check = $conn->query("SELECT idvoit FROM voiture WHERE idvoit = '{$v['idvoit']}'");
        if ($check->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO voiture (idvoit, Design, prix, nombre) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssii", $v['idvoit'], $v['Design'], $v['prix'], $v['nombre']);
            $stmt->execute();
            $stmt->close();
        }
    }

    // --- 2. Insérer des clients ---
    $clients = [
        ['idcli' => 'CL001', 'nom' => 'Rakoto Jean', 'contact' => '0321234567', 'statut' => 'Actif'],
        ['idcli' => 'CL002', 'nom' => 'Rabe Maria', 'contact' => '0337654321', 'statut' => 'Actif'],
        ['idcli' => 'CL003', 'nom' => 'Randria Paul', 'contact' => '0341122334', 'statut' => 'En attente'],
        ['idcli' => 'CL004', 'nom' => 'Andria Sophie', 'contact' => '0325566778', 'statut' => 'Actif'],
        ['idcli' => 'CL005', 'nom' => 'Rasoa Lala', 'contact' => '0339988776', 'statut' => 'En attente'],
    ];

    foreach ($clients as $c) {
        $check = $conn->query("SELECT idcli FROM client WHERE idcli = '{$c['idcli']}'");
        if ($check->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO client (idcli, nom, contact, statut) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $c['idcli'], $c['nom'], $c['contact'], $c['statut']);
            $stmt->execute();
            $stmt->close();
        }
    }

    // --- 3. Insérer des achats (6 derniers mois) ---
    $achats = [
        ['numAchat' => 'ACH20250101001', 'idcli' => 'CL001', 'idvoit' => 'V001', 'date' => '2026-01-15', 'qte' => 1],
        ['numAchat' => 'ACH20250102002', 'idcli' => 'CL002', 'idvoit' => 'V005', 'date' => '2026-02-10', 'qte' => 1],
        ['numAchat' => 'ACH20250203003', 'idcli' => 'CL003', 'idvoit' => 'V003', 'date' => '2026-02-20', 'qte' => 1],
        ['numAchat' => 'ACH20250304004', 'idcli' => 'CL001', 'idvoit' => 'V002', 'date' => '2026-03-05', 'qte' => 1],
        ['numAchat' => 'ACH20250305005', 'idcli' => 'CL004', 'idvoit' => 'V007', 'date' => '2026-03-18', 'qte' => 1],
        ['numAchat' => 'ACH20250406006', 'idcli' => 'CL002', 'idvoit' => 'V001', 'date' => '2026-04-12', 'qte' => 1],
        ['numAchat' => 'ACH20250407007', 'idcli' => 'CL005', 'idvoit' => 'V009', 'date' => '2026-04-25', 'qte' => 1],
        ['numAchat' => 'ACH20250508008', 'idcli' => 'CL003', 'idvoit' => 'V006', 'date' => '2026-05-08', 'qte' => 2],
        ['numAchat' => 'ACH20250509009', 'idcli' => 'CL004', 'idvoit' => 'V010', 'date' => '2026-05-22', 'qte' => 1],
        ['numAchat' => 'ACH20250610010', 'idcli' => 'CL001', 'idvoit' => 'V005', 'date' => '2026-06-10', 'qte' => 1],
    ];

    foreach ($achats as $a) {
        $check = $conn->query("SELECT numAchat FROM achat WHERE numAchat = '{$a['numAchat']}'");
        if ($check->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO achat (numAchat, idcli, idvoit, date, qte) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $a['numAchat'], $a['idcli'], $a['idvoit'], $a['date'], $a['qte']);
            $stmt->execute();
            $stmt->close();
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Données de test insérées avec succès (voitures, clients, achats)'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
