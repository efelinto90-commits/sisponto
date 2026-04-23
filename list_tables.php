<?php
require_once 'c:\xampp\htdocs\sisponto\config\Database.php';
$conn = Config\Database::getConnection();
$stmt = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);
