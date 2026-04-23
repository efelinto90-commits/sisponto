<?php
require_once __DIR__ . '/../config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    
    // Atualizar preset do Gestor (Level 2) para acesso total (exceto usuários talvez?)
    $perms = json_encode(['funcionarios', 'relatorios', 'horarios', 'cargos']);
    $stmt = $conn->prepare("UPDATE ponto.permissao_presets SET permissoes = ? WHERE level = 2");
    $stmt->execute([$perms]);
    
    echo json_encode(['success' => true, 'message' => 'Preset do Gestor atualizado!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
