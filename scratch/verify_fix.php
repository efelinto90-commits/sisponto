<?php
chdir(__DIR__ . '/../api');
require_once '../config/Database.php';
use Config\Database;

// Simular uma requisição à API de relatórios para o Edilson
$_GET['start_date'] = '2026-05-01';
$_GET['end_date'] = '2026-05-31';
$_GET['func_id'] = '4'; // ID do Edilson

ob_start();
require 'relatorios.php';
$output = ob_get_clean();

$json = json_decode($output, true);

if (!$json || !$json['success']) {
    echo "ERRO: Falha ao chamar a API\n";
    echo $output;
    exit;
}

echo "Registros encontrados: " . count($json['data']) . "\n";

// Verificar um dia em que ele deveria estar afastado (ex: hoje 2026-05-08)
$encontrado = false;
foreach ($json['data'] as $reg) {
    if ($reg['data'] === '2026-05-08') {
        echo "Data: " . $reg['data'] . "\n";
        echo "Nome: " . $reg['nome'] . "\n";
        echo "Em Férias: " . ($reg['em_ferias'] ? 'Sim' : 'Não') . "\n";
        echo "Status Afastamento: " . $reg['status_afastamento'] . "\n";
        echo "Motivo: " . $reg['motivo_afastamento'] . "\n";
        $encontrado = true;
        break;
    }
}

if (!$encontrado) {
    echo "ERRO: Registro do dia 2026-05-08 não encontrado.\n";
}
