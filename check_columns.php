<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $columns = ['deficiencia', 'deficiencia_tipo', 'deficiencia_cid', 'deficiencia_grau'];
    echo "COLUMNS STATUS:\n";
    foreach ($columns as $col) {
        $stmt = $conn->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'funcionarios' AND column_name = :col");
        $stmt->execute([':col' => $col]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo "- $col: EXISTS\n";
        } else {
            echo "- $col: MISSING\n";
        }
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
