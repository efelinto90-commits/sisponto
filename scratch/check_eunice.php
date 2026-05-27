<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) FROM registros WHERE id_funcionario = 750");
    $stmt->execute();
    echo "Registros de Eunice (750): " . $stmt->fetchColumn() . "\n";
    
    $stmt = $conn->prepare("SELECT id, data, comunicado, aviso FROM registros WHERE id_funcionario = 750 ORDER BY data DESC LIMIT 10");
    $stmt->execute();
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
