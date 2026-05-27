<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $stmt = $conn->prepare("SELECT id, nome, setor, setor2 FROM funcionarios WHERE nome ILIKE '%Eunice%'");
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($res);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
