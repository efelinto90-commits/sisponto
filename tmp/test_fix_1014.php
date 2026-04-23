<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $id_func = 1014;
    $dataTeste = '2026-04-07'; // Data de teste (amanhã)
    $horaAtual = '15:15';
    $campoPonto = 'terceiro_ponto'; // Simula que o funcionário está batendo o 3º ponto
    $is_atraso = false;

    echo "--- Testando Fix para ID 1014 em $dataTeste ---\n";
    
    // Limpar registro de teste se existir
    $conn->prepare("DELETE FROM registros WHERE id_funcionario = :id AND data = :data")->execute([':id' => $id_func, ':data' => $dataTeste]);

    // Lógica do Fix (Simulada do api/ponto.php)
    $registroHoje = false; // Simula que não existe registro ainda

    if (!$registroHoje) {
        $colunaAtraso = 'atrasou_' . $campoPonto;
        $sql = "INSERT INTO registros (id_funcionario, data, {$campoPonto}, {$colunaAtraso}, created_at, updated_at)
                VALUES (:id, :data, :hora, :atraso, NOW(), NOW())";
        
        echo "Executando SQL: $sql\n";
        
        $conn->prepare($sql)->execute([
            ':id' => $id_func,
            ':data' => $dataTeste,
            ':hora' => $horaAtual,
            ':atraso' => $is_atraso ? 'true' : 'false'
        ]);
        
        echo "Sucesso! Registro criado.\n";
    }

    // Verificar se o registro foi criado corretamente
    $stmt = $conn->prepare("SELECT * FROM registros WHERE id_funcionario = :id AND data = :data");
    $stmt->execute([':id' => $id_func, ':data' => $dataTeste]);
    $res = $stmt->fetch();
    print_r($res);

    // Limpar após o teste
    $conn->prepare("DELETE FROM registros WHERE id_funcionario = :id AND data = :data")->execute([':id' => $id_func, ':data' => $dataTeste]);
    echo "\nTeste concluído e limpo.\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
