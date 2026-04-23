<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    $name = 'admin_temp';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $level = '1';
    $permissoes = json_encode(['funcionarios', 'horarios', 'relatorios', 'usuarios', 'cargos']);
    
    $stmt = $conn->prepare("INSERT INTO users (name, password, level, permissoes) VALUES (:name, :password, :level, :permissoes)");
    $stmt->execute([':name' => $name, ':password' => $password, ':level' => $level, ':permissoes' => $permissoes]);
    echo "Usuário admin_temp criado com sucesso.";
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
