<?php
chdir(__DIR__ . '/../api');
$_GET['status'] = 'todos';
$_SERVER['REQUEST_METHOD'] = 'GET';

ob_start();
require 'funcionarios.php';
$output = ob_get_clean();

$json = json_decode($output, true);

if (!$json || !$json['success']) {
    echo "ERRO: Falha ao chamar a API\n";
    echo $output;
    exit;
}

$encontrado = false;
foreach ($json['data'] as $f) {
    if (stripos($f['nome'], 'Edilson Felinto') !== false) {
        echo "Funcionario encontrado: " . $f['nome'] . " (ID: " . $f['id'] . ")\n";
        $encontrado = true;
        break;
    }
}

if (!$encontrado) {
    echo "ERRO: Edilson não encontrado na listagem 'todos'.\n";
} else {
    echo "SUCESSO: Edilson encontrado.\n";
}
