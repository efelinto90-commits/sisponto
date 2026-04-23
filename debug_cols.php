<?php
require 'C:/xampp/htdocs/sis-ponto/config/Database.php';
try {
    $conn = Config\Database::getConnection();
    $stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'funcionarios'");
    print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
