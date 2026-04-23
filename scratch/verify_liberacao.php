<?php
require_once '../config/Database.php';
use Config\Database;

$conn = Database::getConnection();

// 1. Criar uma liberação fictícia para hoje (Dia Todo)
$hoje = date('Y-m-d');
$dh = $hoje . ' 00:00:00';
$desc = "Teste de Liberação Dia Todo " . uniqid();

$conn->prepare("INSERT INTO ponto_liberado (data_hora, descricao, setor) VALUES (?, ?, 'TODOS')")->execute([$dh, $desc]);
$libId = $conn->lastInsertId();

echo "Liberação criada ID: $libId\n";

// 2. Chamar a API de relatórios
$url = "http://localhost:81/sisponto/api/relatorios.php?start_date=$hoje&end_date=$hoje";
$res = file_get_contents($url);
$data = json_decode($res, true);

if ($data && $data['success']) {
    $found = false;
    foreach ($data['data'] as $reg) {
        if ($reg['liberado_primeiro_ponto'] === true && $reg['primeiro_ponto'] === 'LIBERADO') {
            echo "Sucesso: Registro liberado encontrado para " . $reg['nome'] . "\n";
            echo "Comunicado: " . $reg['comunicado'] . "\n";
            $found = true;
            break;
        }
    }
    if (!$found) echo "Aviso: Nenhum registro LIBERADO encontrado. Verifique se o funcionário tem horário previsto para hoje.\n";
} else {
    echo "Erro ao chamar API: " . ($data['message'] ?? 'Desconhecido') . "\n";
}

// 3. Limpar
$conn->prepare("DELETE FROM ponto_liberado WHERE id = ?")->execute([$libId]);
echo "Liberação removida.\n";
