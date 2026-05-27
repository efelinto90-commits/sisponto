<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    $q = $conn->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'ferias'");
    $cols = $q->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($cols, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
