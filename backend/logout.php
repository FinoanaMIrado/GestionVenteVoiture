<?php
require_once __DIR__ . '/config/cors.php';
header("Content-Type: application/json; charset=utf-8");

session_start();
session_destroy();

echo json_encode(['success' => true, 'message' => 'Déconnexion réussie']);
