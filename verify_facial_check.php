<?php
require_once 'config/Database.php';
use Config\Database;

$conn = Database::getConnection();

// Mocking facial descriptor for ID 31 (Francisco) - assuming he has one
$stmt = $conn->prepare("SELECT id, matricula, facial_descriptor::text FROM funcionarios WHERE id = 31");
$stmt->execute();
$func = $stmt->fetch();

if (!$func || empty($func['facial_descriptor'])) {
    echo "Funcionario 31 não tem biometria facial para teste.\n";
    exit;
}

$input = [
    'action' => 'ponto_facial',
    'matricula' => $func['matricula'],
    'image_data' => 'data:image/jpeg;base64,DEBUG',
    'facial_descriptor' => json_decode($func['facial_descriptor'], true)
];

// Simulating the logic in api/ponto.php
try {
    // 1. Identification (1:1)
    $stmt2 = $conn->prepare("SELECT f.* FROM funcionarios f WHERE f.matricula = :m");
    $stmt2->execute([':m' => $input['matricula']]);
    $funcionario = $stmt2->fetch();

    if ($funcionario) {
        echo "Identificado: " . $funcionario['nome'] . "\n";
        
        // 2. Check Completeness
        $obrigatorios = [
            'cpf' => 'CPF', 'data_nascimento' => 'Data de Nascimento', 'rg_numero' => 'RG',
            'rg_orgao' => 'Órgão Emissor', 'rg_data_emissao' => 'Data de Emissão do RG',
            'endereco' => 'Logradouro', 'endereco_numero' => 'Número', 'endereco_bairro' => 'Bairro',
            'endereco_cep' => 'CEP', 'endereco_municipio' => 'Cidade', 'endereco_uf' => 'UF', 'nome_mae' => 'Nome da Mãe'
        ];
        
        $camposFaltando = [];
        foreach ($obrigatorios as $campo => $label) {
            if (empty($funcionario[$campo])) {
                $camposFaltando[] = ['campo' => $campo, 'label' => $label];
            }
        }
        
        if (!empty($camposFaltando)) {
            echo "FICHA INCOMPLETA DETECTADA!\n";
            echo "Campos faltando: " . count($camposFaltando) . "\n";
            print_r($camposFaltando);
        } else {
            echo "FICHA COMPLETA.\n";
        }
    }
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage();
}
