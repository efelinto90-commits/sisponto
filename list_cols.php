<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'funcionarios' ORDER BY column_name");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo implode(", ", $cols) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
