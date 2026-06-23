<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'gestionventevoiture';

if (!class_exists('PDO')) {
    throw new Exception('Extension PDO manquante');
}

$pdo = new PDO(
    "mysql:host=$host;dbname=$database;charset=utf8",
    $user,
    $password,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);