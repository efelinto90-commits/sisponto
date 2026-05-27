<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    // Check if column exists first
    $q = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'ferias' AND column_name = 'periodo_aquisitivo'");
    if (!$q->fetch()) {
        $conn->exec("ALTER TABLE ponto.ferias ADD COLUMN periodo_aquisitivo VARCHAR(50)");
        echo "SUCCESS: Column periodo_aquisitivo added to ponto.ferias.\n";
    } else {
        echo "SUCCESS: Column already exists.\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
