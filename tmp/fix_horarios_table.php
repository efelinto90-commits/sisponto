<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    echo "Conectado. Tentando ALTER TABLE...\n";
    $conn->exec("ALTER TABLE horarios ADD COLUMN IF NOT EXISTS grade_horarios TEXT");
    echo "Comando executado.\n";
    
    $stmt = $conn->query("
        SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_schema = 'ponto' 
          AND table_name = 'horarios'
    ");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}
?>
