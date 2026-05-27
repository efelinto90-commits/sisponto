<?php
require_once 'config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'funcionarios' AND column_name = 'data_exoneracao'");
    $exists = $stmt->fetch();
    echo $exists ? "EXISTS" : "NOT_EXISTS";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
