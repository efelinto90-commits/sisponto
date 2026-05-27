<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT r.id, r.data, f.nome, r.comunicado, r.aviso FROM registros r JOIN funcionarios f ON r.id_funcionario = f.id WHERE (r.comunicado IS NOT NULL AND r.comunicado != '') OR (r.aviso IS NOT NULL AND r.aviso != '') ORDER BY r.updated_at DESC LIMIT 20");
    $regs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Últimos comunicados/avisos registrados:\n";
    print_r($regs);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
