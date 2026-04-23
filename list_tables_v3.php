<?php
require_once 'c:\xampp\htdocs\sisponto\config\Database.php';
$conn = Config\Database::getConnection();
$stmt = $conn->query("SELECT current_schema()");
$schema = $stmt->fetchColumn();
echo "Current Schema: " . $schema . "\n";
$stmt = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = '$schema'");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);
