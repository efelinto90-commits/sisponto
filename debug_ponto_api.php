<?php
// Mocking the environment for api/ponto.php
$_SERVER['REQUEST_METHOD'] = 'POST';
$input = [
    'action' => 'get_metodos',
    'matricula' => '0022888' // Known incomplete matricula from verify_profile.php
];

// Mocking php://input
function file_get_contents_mock($path) {
    global $input;
    if ($path === 'php://input') return json_encode($input);
    return file_get_contents($path);
}

// Since we cannot easily mock php://input globally without an extension, 
// we will temporarily modify api/ponto.php to allow a mock injection or just use a test script that includes it.

require_once 'config/Database.php';
use Config\Database;

$conn = Database::getConnection(); 
$matricula = $input['matricula'];

$stmt = $conn->prepare("SELECT id, nome, matricula, cpf, data_nascimento, rg_numero, rg_orgao, rg_data_emissao, endereco, endereco_numero, endereco_bairro, endereco_cep, endereco_municipio, endereco_uf, nome_mae, metodos_acesso, webauthn_id FROM funcionarios WHERE matricula = :matricula");
$stmt->execute([':matricula' => $matricula]);
$func = $stmt->fetch();

if ($func) {
    $temWebAuthn = !empty($func['webauthn_id']);
    
    // Verificação de ficha incompleta
    $obrigatorios = [
        'cpf' => 'CPF',
        'data_nascimento' => 'Data de Nascimento',
        'rg_numero' => 'RG',
        'rg_orgao' => 'Órgão Emissor',
        'rg_data_emissao' => 'Data de Emissão do RG',
        'endereco' => 'Logradouro',
        'endereco_numero' => 'Número',
        'endereco_bairro' => 'Bairro',
        'endereco_cep' => 'CEP',
        'endereco_municipio' => 'Cidade',
        'endereco_uf' => 'UF',
        'nome_mae' => 'Nome da Mãe'
    ];
    
    $camposFaltando = [];
    foreach ($obrigatorios as $campo => $label) {
        if (empty($func[$campo])) {
            $camposFaltando[] = ['campo' => $campo, 'label' => $label];
        }
    }

    echo json_encode([
        'success' => true, 
        'metodos' => json_decode($func['metodos_acesso'] ?? '["senha"]', true),
        'has_webauthn' => $temWebAuthn,
        'id' => $func['id'],
        'ficha_incompleta' => !empty($camposFaltando),
        'campos_faltando' => $camposFaltando
    ], JSON_PRETTY_PRINT);
} else {
    echo "Funcionario não encontrado.";
}
