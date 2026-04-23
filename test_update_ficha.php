<?php
require_once 'config/Database.php';
use Config\Database;

$input = [
    'action' => 'update_ficha',
    'id' => 30, // Elisangela from verify_profile.php
    'nome_mae' => 'MAE DE TESTE',
    'cpf' => '05649661421',
    'data_nascimento' => '1980-01-01',
    'rg_numero' => '123456',
    'rg_orgao' => 'SSP/PB',
    'rg_data_emissao' => '2000-01-01',
    'endereco_cep' => '58000-000',
    'endereco' => 'RUA DE TESTE',
    'endereco_numero' => '100',
    'endereco_bairro' => 'CENTRO',
    'endereco_municipio' => 'JOAO PESSOA',
    'endereco_uf' => 'PB'
];

try {
    $conn = Database::getConnection();
    
    $fields_to_update = [
        'cpf', 'data_nascimento', 'rg_numero', 'rg_orgao', 'rg_data_emissao',
        'endereco', 'endereco_numero', 'endereco_bairro', 'endereco_cep',
        'endereco_municipio', 'endereco_uf', 'nome_mae'
    ];

    $sql_parts = [];
    $params = [':id' => $input['id']];

    foreach ($fields_to_update as $field) {
        if (isset($input[$field])) {
            $sql_parts[] = "{$field} = :{$field}";
            $params[":{$field}"] = !empty($input[$field]) ? $input[$field] : null;
        }
    }

    $sql = "UPDATE funcionarios SET " . implode(', ', $sql_parts) . ", updated_at = NOW() WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    echo "Update successful for ID 30.\n";
    
    // Verify
    $stmt2 = $conn->prepare("SELECT nome_mae, updated_at FROM funcionarios WHERE id = 30");
    $stmt2->execute();
    $res = $stmt2->fetch();
    echo "Nome da Mãe: " . $res['nome_mae'] . "\n";
    echo "Updated At: " . $res['updated_at'] . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
