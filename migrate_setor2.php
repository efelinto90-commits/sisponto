<?php
require_once 'c:\xampp\htdocs\sisponto\config\Database.php';
try {
    $conn = Config\Database::getConnection();
    $conn->exec("ALTER TABLE ponto.funcionarios ADD COLUMN setor2 VARCHAR(255)");
    echo "Column 'setor2' added successfully to 'ponto.funcionarios' table.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
