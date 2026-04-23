<?php
require 'C:/xampp/htdocs/sis-ponto/config/Database.php';
try {
    $conn = Config\Database::getConnection();
    echo "Adding metodos_acesso...\n";
    $conn->exec("ALTER TABLE ponto.funcionarios ADD COLUMN metodos_acesso TEXT");
    echo "Done!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
