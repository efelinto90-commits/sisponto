<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    
    $cols_to_add = [
        'nome_social' => 'VARCHAR(255)',
        'unidade_trabalho' => 'VARCHAR(255)',
        'tipo_contratacao' => 'VARCHAR(100)',
        'cargo_funcao' => 'VARCHAR(150)',
        'carga_horaria' => 'VARCHAR(50)',
        'turno_trabalho' => 'VARCHAR(50)',
        'data_admissao' => 'DATE',
        'data_exercicio' => 'DATE',
        'endereco' => 'VARCHAR(255)',
        'endereco_numero' => 'VARCHAR(20)',
        'endereco_complemento' => 'VARCHAR(100)',
        'endereco_cep' => 'VARCHAR(20)',
        'endereco_bairro' => 'VARCHAR(100)',
        'endereco_municipio' => 'VARCHAR(100)',
        'endereco_uf' => 'CHAR(2)',
        'telefone_fixo' => 'VARCHAR(20)',
        'endereco_email' => 'VARCHAR(150)',
        'naturalidade' => 'VARCHAR(100)',
        'naturalidade_uf' => 'CHAR(2)',
        'data_nascimento' => 'DATE',
        'estado_civil' => 'VARCHAR(50)',
        'nacionalidade' => 'VARCHAR(100)',
        'sexo' => 'VARCHAR(50)',
        'raca_cor' => 'VARCHAR(50)',
        'deficiencia' => 'BOOLEAN DEFAULT FALSE',
        'deficiencia_tipo' => 'VARCHAR(100)',
        'deficiencia_cid' => 'VARCHAR(20)',
        'tipo_sanguineo' => 'VARCHAR(20)',
        'escolaridade' => 'VARCHAR(150)',
        'pis_pasep' => 'VARCHAR(50)',
        'carteira_conselheiro' => 'VARCHAR(100)',
        'rg_numero' => 'VARCHAR(50)',
        'rg_orgao' => 'VARCHAR(50)',
        'rg_data_emissao' => 'DATE',
        'cnh_numero' => 'VARCHAR(50)',
        'cnh_categoria' => 'VARCHAR(10)',
        'cnh_validade' => 'DATE',
        'titulo_eleitor_numero' => 'VARCHAR(50)',
        'titulo_eleitor_zona' => 'VARCHAR(10)',
        'titulo_eleitor_secao' => 'VARCHAR(10)',
        'reservista_numero' => 'VARCHAR(50)',
        'reservista_serie' => 'VARCHAR(10)',
        'ctps_numero' => 'VARCHAR(50)',
        'ctps_serie' => 'VARCHAR(20)',
        'ctps_data_emissao' => 'DATE',
        'ctps_uf' => 'CHAR(2)',
        'nome_pai' => 'VARCHAR(255)',
        'nome_mae' => 'VARCHAR(255)',
        'nome_conjuge' => 'VARCHAR(255)',
        'nacionalidade_conjuge' => 'VARCHAR(100)',
        'naturalidade_conjuge' => 'VARCHAR(100)',
        'naturalidade_uf_conjuge' => 'CHAR(2)',
        'data_nascimento_conjuge' => 'DATE',
        'filhos_menores' => 'JSONB',
        'dependentes_ir' => 'JSONB',
        'banco_nome' => 'VARCHAR(100)',
        'banco_agencia' => 'VARCHAR(20)',
        'banco_conta' => 'VARCHAR(50)',
        'observacoes_complementares' => 'TEXT',
        'lat_permitida' => 'DECIMAL(10,8)',
        'long_permitida' => 'DECIMAL(11,8)',
        'distancia_max_permitida' => 'INTEGER DEFAULT 200',
        'area_geofencing' => 'VARCHAR(255)'
    ];

    // Get current columns
    $stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'funcionarios'");
    $existing_cols = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($cols_to_add as $col => $type) {
        if (!in_array($col, $existing_cols)) {
            echo "Adding column $col...\n";
            $conn->exec("ALTER TABLE ponto.funcionarios ADD COLUMN $col $type");
        } else {
            echo "Column $col already exists.\n";
        }
    }

    echo "Migration complete.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
