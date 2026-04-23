<?php
require 'C:/xampp/htdocs/sis-ponto/config/Database.php';
try {
    $conn = Config\Database::getConnection();
    echo "Adding facial_descriptor...\n";
    $conn->exec("ALTER TABLE ponto.funcionarios ADD COLUMN facial_descriptor TEXT");
    echo "Done!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
