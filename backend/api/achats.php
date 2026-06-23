<?php
error_reporting(0);
ini_set('display_errors', 0);

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $headers = headers_list();
        $hasContentType = false;
        foreach ($headers as $h) {
            if (stripos($h, 'Content-Type:') === 0) $hasContentType = true;
        }
        if (!$hasContentType) header("Content-Type: application/json; charset=utf-8");
        while (ob_get_level()) ob_end_clean();
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Erreur interne du serveur"]);
    }
});

require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json; charset=utf-8");

try {
    require_once __DIR__ . '/../config/connexion.php';

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (empty($data['nom']) || empty($data['contact']) || empty($data['idvoit']) || empty($data['qte'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Données incomplètes."]);
        exit;
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT Design, prix, nombre FROM voiture WHERE idvoit = ?");
    $stmt->execute([$data['idvoit']]);
    $voiture = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$voiture) throw new Exception("Voiture introuvable.");
    if ($voiture['nombre'] < $data['qte']) throw new Exception("Stock insuffisant ! Il reste uniquement " . $voiture['nombre'] . " unité(s).");

    $idcli = "CLI-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

    $stmt = $pdo->prepare("INSERT INTO client (idcli, nom, contact) VALUES (?, ?, ?)");
    $stmt->execute([$idcli, $data['nom'], $data['contact']]);

    $numAchat = !empty($data['numAchat']) ? $data['numAchat'] : 'ACH' . date('Ymd') . strtoupper(substr(uniqid(), -6));
    $dateAchat = !empty($data['date']) ? $data['date'] : date('Y-m-d');

    $stmt = $pdo->prepare("INSERT INTO achat (numAchat, idcli, idvoit, date, qte) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$numAchat, $idcli, $data['idvoit'], $dateAchat, $data['qte']]);

    $stmt = $pdo->prepare("UPDATE voiture SET nombre = nombre - ? WHERE idvoit = ?");
    $stmt->execute([$data['qte'], $data['idvoit']]);

    $pdo->commit();

    echo json_encode(["success" => true, "invoiceDetails" => [
        "numAchat" => $numAchat,
        "date" => $dateAchat,
        "clientNom" => $data['nom'],
        "clientContact" => $data['contact'],
        "idcli" => $idcli,
        "designation" => $voiture['Design'],
        "qte" => $data['qte'],
        "prixUnitaire" => $voiture['prix'],
        "totalGeneral" => $voiture['prix'] * $data['qte']
    ]]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
} catch (Throwable $t) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Erreur serveur: " . $t->getMessage()]);
}
