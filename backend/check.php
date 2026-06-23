<?php
require_once __DIR__ . '/config/cors.php';
header("Content-Type: application/json; charset=utf-8");

session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo json_encode([
        'success' => true,
        'username' => $_SESSION['username'],
        'login_time' => $_SESSION['login_time']
    ]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
}
