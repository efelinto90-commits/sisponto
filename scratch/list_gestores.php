<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT id, nome, setor, setor2, user_level FROM funcionarios WHERE user_level = 1 OR user_level = 2");
    $gestores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Gestores/Admins encontrados:\n";
    print_r($gestores);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
