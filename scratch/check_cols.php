<?php
require_once 'config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'registros'");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($cols);
} catch (Exception $e) {
    echo $e->getMessage();
}
