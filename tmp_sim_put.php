<?php
// Simular um PUT para api/funcionarios.php
$url = 'http://localhost/sisponto/api/funcionarios.php'; // Ajuste se necessário, mas aqui vamos tentar via local file if possible or just include it

// Como estamos no mesmo servidor, podemos simular o ambiente
$_SERVER['REQUEST_METHOD'] = 'PUT';
$input = [
    'id' => 1,
    'nome' => 'Teste Update',
    'matricula' => 'TESTE-001',
    'setor' => 'TI',
    'cpf' => '12345678901',
    'distancia_max_permitida' => '' // Provável causa do erro se for INTEGER
];

// Mock do php://input
function mock_input($data) {
    $temp = tmpfile();
    fwrite($temp, json_encode($data));
    fseek($temp, 0);
    return $temp;
}

// Inclusão do script original com captura de output
ob_start();
include 'api/funcionarios.php';
$output = ob_get_clean();

echo "API Output:\n$output\n";
