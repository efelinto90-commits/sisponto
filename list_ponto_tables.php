<?php
require_once 'c:\xampp\htdocs\sisponto\config\Database.php';
$conn = Config\Database::getConnection();
$stmt = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'ponto' ORDER BY table_name");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);
