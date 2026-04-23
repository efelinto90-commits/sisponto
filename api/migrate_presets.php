<?php
require_once __DIR__ . '/../config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    
    // Criar tabela de presets se não existir
    $sql = "CREATE TABLE IF NOT EXISTS ponto.permissao_presets (
        level INT PRIMARY KEY,
        nome VARCHAR(50) NOT NULL,
        permissoes JSONB DEFAULT '[]'
    )";
    $conn->exec($sql);
    
    // Inserir valores padrão se estiver vazia
    $stmt = $conn->query("SELECT COUNT(*) FROM ponto.permissao_presets");
    if ($stmt->fetchColumn() == 0) {
        $presets = [
            [1, 'Administrador', json_encode(['funcionarios', 'relatorios', 'horarios', 'cargos', 'usuarios'])],
            [2, 'Gestor', json_encode(['funcionarios', 'relatorios'])],
            [3, 'Usuário Comum', json_encode([])]
        ];
        
        $insert = $conn->prepare("INSERT INTO ponto.permissao_presets (level, nome, permissoes) VALUES (?, ?, ?)");
        foreach ($presets as $p) {
            $insert->execute($p);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Tabela e presets criados com sucesso!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
