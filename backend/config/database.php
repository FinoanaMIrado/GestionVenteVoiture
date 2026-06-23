<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'gestionventevoiture';

if (!class_exists('mysqli')) {
    die(json_encode(['error' => 'Extension mysqli manquante']));
}

$conn = @new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connexion échouée: ' . $conn->connect_error]));
}

$conn->set_charset("utf8");
