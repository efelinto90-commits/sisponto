<?php
require_once 'c:\xampp\htdocs\sisponto\config\Database.php';
$conn = Config\Database::getConnection();
$stmt = $conn->query("SELECT name, level FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'users'");
$cols = $stmt2->fetchAll(PDO::FETCH_COLUMN);

file_put_contents('c:\xampp\htdocs\sisponto\db_info.txt', "USERS:\n" . print_r($users, true) . "\nCOLS:\n" . print_r($cols, true));
echo "Done";
