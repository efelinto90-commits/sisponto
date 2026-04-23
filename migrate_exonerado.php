<?php
require_once 'c:\xampp\htdocs\sisponto\config\Database.php';
$conn = Config\Database::getConnection();

try {
    $conn->exec("ALTER TABLE funcionarios ADD COLUMN IF NOT EXISTS is_exonerado BOOLEAN DEFAULT FALSE;");
    echo "Column is_exonerado added successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
