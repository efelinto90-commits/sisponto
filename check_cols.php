<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'funcionarios'");
    $cols = $stmt->fetchAll();
    foreach($cols as $c) {
        echo $c['column_name'] . ": " . $c['data_type'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
