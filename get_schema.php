<?php
require_once 'c:\xampp\htdocs\sisponto\config\Database.php';
$conn = Config\Database::getConnection();
$stmt = $conn->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'registros'");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($cols, JSON_PRETTY_PRINT);
