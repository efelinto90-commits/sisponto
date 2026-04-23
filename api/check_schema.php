<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    // Test if ponto.funcionarios exists and has the column
    $q = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'funcionarios' AND column_name = 'motivo_exoneracao'");
    if ($q->fetch()) {
        echo "COLUNA EXISTE EM ponto.funcionarios";
    } else {
        echo "COLUNA NAO EXISTE EM ponto.funcionarios";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
