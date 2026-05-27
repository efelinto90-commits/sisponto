<?php
require_once 'config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT matricula FROM ponto.funcionarios WHERE matricula LIKE '%-0' OR matricula LIKE '%-X' OR matricula LIKE '%-10'");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
