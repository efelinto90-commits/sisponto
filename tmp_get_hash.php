<?php
require_once 'c:\xampp\htdocs\sisponto\config\Database.php';
$pdo = Config\Database::getConnection();
$stmt = $pdo->prepare("SELECT password FROM users WHERE name = 'admin_temp'");
$stmt->execute();
echo $stmt->fetchColumn();
