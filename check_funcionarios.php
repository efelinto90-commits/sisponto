<?php
require_once 'c:\xampp\htdocs\sisponto\config\Database.php';
$conn = Config\Database::getConnection();
$stmt = $conn->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'funcionarios'");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);
