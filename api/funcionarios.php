<?php
session_start();
header('Content-Type: application/json');
require_once '../config/Database.php';

use Config\Database;

/**
 * Salva uma imagem base64 em disco
 */
function saveBase64Image($base64Data, $directory, $prefix, $id) {
    if (empty($base64Data)) return null;
    
    // Garantir que o diretório existe
    if (!is_dir("../" . $directory)) {
        mkdir("../" . $directory, 0777, true);
    }
    
    try {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
            $data = substr($base64Data, strpos($base64Data, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, etc
            $data = base64_decode($data);
            
            $filename = $prefix . "_" . $id . "_" . time() . "." . $type;
            $relativePath = $directory . $filename;
            $absolutePath = "../" . $relativePath;
            
            if (file_put_contents($absolutePath, $data)) {
                return $relativePath;
            }
        }
    } catch (Exception $e) {
        error_log("Erro ao salvar imagem em disco: " . $e->getMessage());
    }
    return null;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $conn = Database::getConnection();

    // Identificação de Super Usuário (Admin ou CRH/CORSIN)
    $user_name = $_SESSION['user_name'] ?? '';
    $user_setor = $_SESSION['user_setor'] ?? '';
    $user_level = $_SESSION['user_level'] ?? 3;
    $is_super = ($user_level == 1) || in_array(strtolower(trim($user_name)), ['corsin', 'crh']) || in_array(strtolower(trim($user_setor)), ['corsin', 'crh']);

    switch ($method) {
        case 'GET':
            // Verificação de duplicidade de CPF ou Matrícula
            if (isset($_GET['check_duplicado'])) {
                $campo  = $_GET['campo']  ?? '';
                $valor  = trim($_GET['valor'] ?? '');
                $excl   = $_GET['exclude_id'] ?? null;

                $camposPermitidos = ['cpf', 'matricula'];
                if (!in_array($campo, $camposPermitidos) || $valor === '') {
                    echo json_encode(['exists' => false]);
                    exit;
                }

                $sql = "SELECT id, nome FROM funcionarios WHERE {$campo} = :valor";
                $params = [':valor' => $valor];
                if ($excl) {
                    $sql .= " AND id != :excl";
                    $params[':excl'] = $excl;
                }
                $sql .= " LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                $found = $stmt->fetch();

                if ($found) {
                    echo json_encode(['exists' => true, 'nome' => $found['nome'], 'id' => $found['id']]);
                } else {
                    echo json_encode(['exists' => false]);
                }
                exit;
            }

            if (isset($_GET['id'])) {
                // Get One
                $stmt = $conn->prepare("SELECT * FROM funcionarios WHERE id = :id");
                $stmt->execute([':id' => $_GET['id']]);
                $func = $stmt->fetch();
                echo json_encode(['success' => true, 'data' => $func]);
            } else {
                // Get All com Nome do Cargo e Dia da Semana
                // Nível 1 (Admin) vê tudo. Outros níveis sofrem filtro estrito se solicitado.
                $strict = isset($_GET['strict_sector']) && $_GET['strict_sector'] == '1';
                if ($strict) {
                    $is_super_list = ($user_level == 1);
                } else {
                    $is_super_list = $is_super;
                }
                
                // Use WHERE 1=1 as base so AND clauses always work
                $baseWhereClause = "WHERE 1=1";
                $params = [];
                // Se estiver logado, aplica filtro de setor. Se for público (relógio), mostra todos.
                if (!empty($user_name) && !$is_super_list) {
                    if (!empty($user_setor)) {
                        $baseWhereClause .= " AND (f.setor = :setor OR f.setor2 = :setor)";
                        $params[':setor'] = $user_setor;
                    } else {
                        // Se logado mas sem setor, visualiza apenas funcionários sem setor.
                        $baseWhereClause .= " AND (f.setor IS NULL OR TRIM(f.setor) = '')";
                    }
                }

                // Status Tab filter - aplicado apenas na listagem, NÃO na query de contagem
                $status = $_GET['status'] ?? 'ativos';
                $whereClause = $baseWhereClause;
                if ($status === 'ativos') {
                    $whereClause .= " AND (f.is_exonerado IS FALSE OR f.is_exonerado IS NULL)
                              AND NOT EXISTS (SELECT 1 FROM ferias WHERE id_funcionario = f.id AND CURRENT_DATE BETWEEN data_inicio AND data_fim LIMIT 1)";
                } elseif ($status === 'afastados') {
                    $whereClause .= " AND (f.is_exonerado IS FALSE OR f.is_exonerado IS NULL)
                              AND EXISTS (SELECT 1 FROM ferias WHERE id_funcionario = f.id AND CURRENT_DATE BETWEEN data_inicio AND data_fim LIMIT 1)";
                } elseif ($status === 'exonerados') {
                    $whereClause .= " AND f.is_exonerado IS TRUE";
                }

                $stmt = $conn->prepare("
                    SELECT f.id, f.nome, f.matricula, f.setor, f.setor2, f.cpf,
                           f.lat_permitida, f.long_permitida, f.distancia_max_permitida, f.area_geofencing,
                           f.turno1_inicio, f.turno1_fim, f.turno2_inicio, f.turno2_fim,
                           f.lat_permitida_2, f.long_permitida_2, f.distancia_max_permitida_2, f.area_geofencing_2,
                           h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario,
                           (CASE WHEN f.biometria IS NOT NULL THEN 1 ELSE 0 END) as tem_biometria,
                           (CASE WHEN f.foto_perfil IS NOT NULL AND f.foto_perfil <> '' THEN 1 ELSE 0 END) as tem_foto,
                           (CASE WHEN f.biometria_facial IS NOT NULL AND f.biometria_facial <> '' THEN 1 ELSE 0 END) as tem_facial,
                           (CASE WHEN f.codigo_qr IS NOT NULL AND f.codigo_qr <> '' THEN 1 ELSE 0 END) as tem_qr,
                           f.grade_horarios,
                           f.is_exonerado,
                           f.motivo_exoneracao,
                           (SELECT data_fim FROM ferias WHERE id_funcionario = f.id AND CURRENT_DATE BETWEEN data_inicio AND data_fim LIMIT 1) as data_fim_afastamento
                    FROM funcionarios f
                    LEFT JOIN horarios h ON f.id_horario = h.id
                    $whereClause
                    ORDER BY f.nome ASC
                ");
                $stmt->execute($params);
                $funcionarios = $stmt->fetchAll();

                // Count for all statuses (using baseWhereClause WITHOUT tab filter)
                $stmtCount = $conn->prepare("
                    SELECT 
                        COUNT(*) FILTER (WHERE (f.is_exonerado IS FALSE OR f.is_exonerado IS NULL)
                            AND NOT EXISTS (SELECT 1 FROM ferias fe WHERE fe.id_funcionario = f.id AND CURRENT_DATE BETWEEN fe.data_inicio AND fe.data_fim LIMIT 1)) as total_ativos,
                        COUNT(*) FILTER (WHERE (f.is_exonerado IS FALSE OR f.is_exonerado IS NULL)
                            AND EXISTS (SELECT 1 FROM ferias fe WHERE fe.id_funcionario = f.id AND CURRENT_DATE BETWEEN fe.data_inicio AND fe.data_fim LIMIT 1)) as total_afastados,
                        COUNT(*) FILTER (WHERE f.is_exonerado IS TRUE) as total_exonerados
                    FROM funcionarios f
                    $baseWhereClause
                ");
                $stmtCount->execute($params);
                $counts = $stmtCount->fetch();

                $stmt2 = $conn->query("SELECT * FROM horarios ORDER BY id ASC");
                $horarios = $stmt2->fetchAll();

                echo json_encode(['success' => true, 'data' => $funcionarios, 'horarios' => $horarios, 'counts' => $counts]);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? '';

            // Ação de atualização de ficha é permitida para o próprio funcionário (via Totem/Relógio)
            // Se NÃO for update_ficha, exige permissão de super usuário
            if ($action !== 'update_ficha' && !$is_super) {
                echo json_encode(['success' => false, 'message' => 'Permissão negada. Apenas administradores e gestores do CRH podem criar ou editar funcionários.']);
                exit;
            }

            // Ação Especial para Biometria
            if (isset($input['action']) && $input['action'] === 'save_biometria') {
                if (empty($input['id']) || empty($input['biometria'])) {
                    echo json_encode(['success' => false, 'message' => 'ID ou Biometria ausentes.']);
                    exit;
                }
                $stmt = $conn->prepare("UPDATE funcionarios SET biometria = :biometria WHERE id = :id");
                $stmt->execute([
                    ':biometria' => $input['biometria'],
                    ':id' => $input['id']
                ]);
                echo json_encode(['success' => true, 'message' => 'Biometria cadastrada com sucesso!']);
                exit;
            }

            // Ação Especial para Limpar Biometrias
            if (isset($input['action']) && $input['action'] === 'clear_biometrics') {
                if (empty($input['id'])) {
                    echo json_encode(['success' => false, 'message' => 'ID ausente.']);
                    exit;
                }
                $stmt = $conn->prepare("UPDATE funcionarios SET biometria = NULL, biometria_facial = NULL, webauthn_id = NULL, webauthn_pk = NULL, webauthn_counter = 0, codigo_qr = NULL WHERE id = :id");
                $stmt->execute([':id' => $input['id']]);
                echo json_encode(['success' => true, 'message' => 'Todas as biometrias e registros de acesso foram removidos!']);
                exit;
            }

            // Ações Especiais Câmera Mobile
            if (isset($input['action']) && in_array($input['action'], ['save_foto_perfil', 'save_codigo_qr', 'save_biometria_facial'])) {
                if (empty($input['id']) || empty($input['image_data'])) {
                    echo json_encode(['success' => false, 'message' => 'Dados de imagem inválidos.']);
                    exit;
                }

                $coluna = '';
                $msg = '';
                $dir = '';
                $prefix = '';

                if ($input['action'] === 'save_foto_perfil') {
                    $coluna = 'foto_perfil';
                    $msg = 'Foto de Perfil salva com sucesso!';
                    $dir = 'uploads/perfil/';
                    $prefix = 'perfil';
                } else if ($input['action'] === 'save_codigo_qr') {
                    $coluna = 'codigo_qr';
                    $msg = 'Leitura QR Code registrada com sucesso!';
                    $dir = 'uploads/qrcode/';
                    $prefix = 'qr';
                } else if ($input['action'] === 'save_biometria_facial') {
                    $coluna = 'biometria_facial';
                    $msg = 'Biometria Facial mapeada com sucesso!';
                    $dir = 'uploads/facial/';
                    $prefix = 'facial';
                }

                // Salvar em disco em vez de base64 no banco
                $path = saveBase64Image($input['image_data'], $dir, $prefix, $input['id']);
                
                if (!$path) {
                    echo json_encode(['success' => false, 'message' => 'Erro ao processar e salvar imagem em disco.']);
                    exit;
                }

                $sql = "UPDATE funcionarios SET {$coluna} = :imagem";
                $params = [':imagem' => $path, ':id' => $input['id']];

                if (isset($input['facial_descriptor'])) {
                    $sql .= ", facial_descriptor = :descr";
                    $params[':descr'] = json_encode($input['facial_descriptor']);
                }

                $sql .= " WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);

                echo json_encode(['success' => true, 'message' => $msg, 'path' => $path]);
                exit;
            }

            // Ação Especial para Exoneração
            if (isset($input['action']) && $input['action'] === 'exonerate') {
                if (!$is_super) {
                    echo json_encode(['success' => false, 'message' => 'Permissão negada. Apenas administradores e gestores do CRH podem exonerar funcionários.']);
                    exit;
                }
                if (empty($input['id'])) {
                    echo json_encode(['success' => false, 'message' => 'ID ausente.']);
                    exit;
                }
                // Marca como exonerado e limpa métodos de acesso para garantir bloqueio
                $stmt = $conn->prepare("UPDATE funcionarios SET is_exonerado = TRUE, motivo_exoneracao = :motivo, updated_at = NOW() WHERE id = :id");
                $stmt->execute([
                    ':id' => $input['id'],
                    ':motivo' => $input['motivo'] ?? 'Não informado'
                ]);
                echo json_encode(['success' => true, 'message' => 'Funcionário exonerado com sucesso!']);
                exit;
            }

            // Ação Especial para Reintegração
            if (isset($input['action']) && $input['action'] === 'reintegrate') {
                if (!$is_super) {
                    echo json_encode(['success' => false, 'message' => 'Permissão negada. Apenas administradores e gestores do CRH podem reintegrar funcionários.']);
                    exit;
                }
                if (empty($input['id'])) {
                    echo json_encode(['success' => false, 'message' => 'ID ausente.']);
                    exit;
                }
                // Remove status de exonerado e limpa o motivo
                $stmt = $conn->prepare("UPDATE funcionarios SET is_exonerado = FALSE, motivo_exoneracao = NULL, updated_at = NOW() WHERE id = :id");
                $stmt->execute([':id' => $input['id']]);
                echo json_encode(['success' => true, 'message' => 'Funcionário reintegrado com sucesso!']);
                exit;
            }

            // Ação Especial para Geolocalização em Massa
            if (isset($input['action']) && $input['action'] === 'bulk_geofencing') {
                if (($_SESSION['user_level'] ?? 3) != 1) {
                    echo json_encode(['success' => false, 'message' => 'Permissão negada. Apenas administradores podem alterar geolocalização em massa.']);
                    exit;
                }
                $ids = $input['ids'] ?? []; // Array de IDs ou "all"
                $lat = !empty($input['lat']) ? floatval(str_replace(',', '.', $input['lat'])) : null;
                $lng = !empty($input['lng']) ? floatval(str_replace(',', '.', $input['lng'])) : null;
                $dist = !empty($input['dist']) ? (int)$input['dist'] : 200;
                $area = !empty($input['area']) ? $input['area'] : null;

                if ($ids === 'all') {
                    $stmt = $conn->prepare("UPDATE funcionarios SET lat_permitida = :lat, long_permitida = :lng, distancia_max_permitida = :dist, area_geofencing = :area, updated_at = NOW()");
                    $stmt->execute([':lat' => $lat, ':lng' => $lng, ':dist' => $dist, ':area' => $area]);
                } else if (is_array($ids) && count($ids) > 0) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $stmt = $conn->prepare("UPDATE funcionarios SET lat_permitida = ?, long_permitida = ?, distancia_max_permitida = ?, area_geofencing = ?, updated_at = NOW() WHERE id IN ($placeholders)");
                    $stmt->execute(array_merge([$lat, $lng, $dist, $area], $ids));
                } else {
                    echo json_encode(['success' => false, 'message' => 'Nenhum funcionário selecionado.']);
                    exit;
                }

                echo json_encode(['success' => true, 'message' => 'Geolocalização aplicada com sucesso!']);
                exit;
            }

            // Ação Especial para Atualização de Ficha (Mandatória no Ponto)
            if (isset($input['action']) && $input['action'] === 'update_ficha') {
                if (empty($input['id'])) {
                    echo json_encode(['success' => false, 'message' => 'ID do funcionário ausente.']);
                    exit;
                }

                // Se não for super usuário, verificar se a ficha já está completa
                if (!$is_super) {
                    $stmtCheck = $conn->prepare("SELECT nome_mae, cpf FROM funcionarios WHERE id = :id");
                    $stmtCheck->execute([':id' => $input['id']]);
                    $current = $stmtCheck->fetch();
                    // Se já tiver os dados essenciais, bloqueia edição por não-gestores
                    if ($current && !empty($current['nome_mae']) && !empty($current['cpf'])) {
                        echo json_encode(['success' => false, 'message' => 'Sua ficha já foi preenchida anteriormente. Alterações agora só podem ser feitas pelo CRH ou Administrador.']);
                        exit;
                    }
                }

                $fields_to_update = [
                    'nome', 'nome_social', 'cpf', 'rg_numero', 'rg_orgao', 'rg_data_emissao',
                    'data_nascimento', 'naturalidade', 'naturalidade_uf', 'nacionalidade',
                    'estado_civil', 'sexo', 'raca_cor', 'tipo_sanguineo', 'deficiencia',
                    'deficiencia_grau', 'deficiencia_tipo', 'deficiencia_cid',
                    'pis_pasep', 'cnh_numero', 'cnh_categoria', 'cnh_validade', 
                    'titulo_eleitor_numero', 'titulo_eleitor_zona', 'titulo_eleitor_secao', 
                    'reservista_numero', 'reservista_serie', 'ctps_numero', 'ctps_serie', 'ctps_data_emissao', 'ctps_uf',
                    'escolaridade', 'carteira_conselheiro',
                    'endereco', 'endereco_numero', 'endereco_complemento', 'endereco_bairro', 'endereco_cep',
                    'endereco_municipio', 'endereco_uf', 'celular', 'telefone_fixo', 'endereco_email',
                    'nome_pai', 'nome_mae', 'nome_conjuge', 'data_nascimento_conjuge', 'nacionalidade_conjuge',
                    'naturalidade_conjuge', 'naturalidade_uf_conjuge',
                    'filhos_menores', 'dependentes_ir', 'banco_nome', 'banco_agencia', 'banco_conta',
                    'grade_horarios', 'setor', 'setor2', 'is_exonerado', 'observacoes_complementares',
                    'termo_dados', 'metodos_acesso'
                ];

                $sql_parts = [];
                $params = [':id' => $input['id']];

                foreach ($fields_to_update as $field) {
                    if (isset($input[$field])) {
                        $sql_parts[] = "{$field} = :{$field}";
                        $val = $input[$field];
                        
                        // Special handling for JSON fields
                        if (in_array($field, ['filhos_menores', 'dependentes_ir', 'metodos_acesso', 'grade_horarios', 'termo_dados'])) {
                            $params[":{$field}"] = !empty($val) ? json_encode($val) : null;
                        } else if ($field === 'deficiencia' || $field === 'is_exonerado') {
                            $params[":{$field}"] = ($val === 'true' || $val === true || $val === '1' || $val === 1) ? 1 : 0;
                        } else {
                            $params[":{$field}"] = ($val !== '' && $val !== null) ? $val : null;
                        }
                    }
                }

                if (empty($sql_parts)) {
                    echo json_encode(['success' => false, 'message' => 'Nenhum dado para atualizar.']);
                    exit;
                }

                $sql = "UPDATE funcionarios SET " . implode(', ', $sql_parts) . ", updated_at = NOW() WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);

                echo json_encode(['success' => true, 'message' => 'Ficha cadastral atualizada com sucesso!']);
                exit;
            }

            // Helpers para campos opcionais e tipos específicos
            $helper_null = function($val) { return !empty($val) ? $val : null; };
            $helper_json = function($val) { return !empty($val) ? json_encode($val) : null; };
            $helper_bool = function($val) { return ($val === 'true' || $val === true || $val === 1 || $val === '1') ? 1 : 0; };

            $sql = "INSERT INTO ponto.funcionarios (
                        nome, matricula, setor, setor2, cpf, celular, id_horario, metodos_acesso, senha, 
                        lat_permitida, long_permitida, distancia_max_permitida, area_geofencing,
                        turno1_inicio, turno1_fim, turno2_inicio, turno2_fim,
                        lat_permitida_2, long_permitida_2, distancia_max_permitida_2, area_geofencing_2,
                        foto_perfil, biometria_facial, facial_descriptor,
                        nome_social, unidade_trabalho, tipo_contratacao, cargo_funcao, carga_horaria, 
                        turno_trabalho, data_admissao, data_exercicio, endereco, endereco_numero, 
                        endereco_complemento, endereco_cep, endereco_bairro, endereco_municipio, 
                        endereco_uf, telefone_fixo, endereco_email, naturalidade, naturalidade_uf, 
                        data_nascimento, estado_civil, nacionalidade, sexo, raca_cor, deficiencia, 
                        deficiencia_tipo, deficiencia_cid, deficiencia_grau, tipo_sanguineo, escolaridade, pis_pasep, 
                        carteira_conselheiro, rg_numero, rg_orgao, rg_data_emissao, cnh_numero, 
                        cnh_categoria, cnh_validade, titulo_eleitor_numero, titulo_eleitor_zona, 
                        titulo_eleitor_secao, reservista_numero, reservista_serie, ctps_numero, 
                        ctps_serie, ctps_data_emissao, ctps_uf, nome_pai, nome_mae, nome_conjuge, 
                        nacionalidade_conjuge, naturalidade_conjuge, naturalidade_uf_conjuge, 
                        data_nascimento_conjuge, filhos_menores, dependentes_ir, banco_nome, 
                        banco_agencia, banco_conta, observacoes_complementares, termo_dados,
                        grade_horarios, is_exonerado, motivo_exoneracao,
                        created_at, updated_at
                    ) VALUES (
                        :nome, :matricula, :setor, :setor2, :cpf, :celular, :id_horario, :metodos_acesso, :senha, 
                        :lat, :lng, :dist, :area,
                        :t1_ini, :t1_fim, :t2_ini, :t2_fim,
                        :lat2, :lng2, :dist2, :area2,
                        :foto_perfil, :biometria_facial, :facial_descriptor,
                        :nome_social, :unidade_trabalho, :tipo_contratacao, :cargo_funcao, :carga_horaria, 
                        :turno_trabalho, :data_admissao, :data_exercicio, :endereco, :endereco_numero, 
                        :endereco_complemento, :endereco_cep, :endereco_bairro, :endereco_municipio, 
                        :endereco_uf, :telefone_fixo, :endereco_email, :naturalidade, :naturalidade_uf, 
                        :data_nascimento, :estado_civil, :nacionalidade, :sexo, :raca_cor, :deficiencia, 
                        :deficiencia_tipo, :deficiencia_cid, :deficiencia_grau, :tipo_sanguineo, :escolaridade, :pis_pasep, 
                        :carteira_conselheiro, :rg_numero, :rg_orgao, :rg_data_emissao, :cnh_numero, 
                        :cnh_categoria, :cnh_validade, :titulo_eleitor_numero, :titulo_eleitor_zona, 
                        :titulo_eleitor_secao, :reservista_numero, :reservista_serie, :ctps_numero, 
                        :ctps_serie, :ctps_data_emissao, :ctps_uf, :nome_pai, :nome_mae, :nome_conjuge, 
                        :nacionalidade_conjuge, :naturalidade_conjuge, :naturalidade_uf_conjuge, 
                        :data_nascimento_conjuge, :filhos_menores, :dependentes_ir, :banco_nome, 
                        :banco_agencia, :banco_conta, :observacoes_complementares, :termo_dados,
                        :grade_horarios, :is_exonerado, :motivo_exoneracao,
                        NOW(), NOW()
                    )";
            
            $stmt = $conn->prepare($sql);
            $params = [
                ':nome' => $input['nome'] ?? '',
                ':matricula' => $input['matricula'] ?? '',
                ':setor' => $input['setor'] ?? '',
                ':setor2' => $input['setor2'] ?? '',
                ':cpf' => $input['cpf'] ?? '',
                ':celular' => $input['celular'] ?? '',
                ':id_horario' => $helper_null($input['id_horario'] ?? null),
                ':metodos_acesso' => isset($input['metodos_acesso']) ? json_encode($input['metodos_acesso']) : '["senha"]',
                ':senha' => !empty($input['senha']) ? password_hash($input['senha'], PASSWORD_DEFAULT) : null,
                ':lat' => ($_SESSION['user_level'] ?? 3) == 1 ? $helper_null($input['lat_permitida'] ?? null) : null,
                ':lng' => ($_SESSION['user_level'] ?? 3) == 1 ? $helper_null($input['long_permitida'] ?? null) : null,
                ':dist' => ($_SESSION['user_level'] ?? 3) == 1 ? ((!empty($input['distancia_max_permitida']) || ($input['distancia_max_permitida'] ?? null) === 0 || ($input['distancia_max_permitida'] ?? null) === '0') ? (int)$input['distancia_max_permitida'] : 200) : 200,
                ':area' => ($_SESSION['user_level'] ?? 3) == 1 ? $helper_null($input['area_geofencing'] ?? null) : null,
                ':t1_ini' => ($_SESSION['user_level'] ?? 3) == 1 ? $helper_null($input['turno1_inicio'] ?? null) : null,
                ':t1_fim' => ($_SESSION['user_level'] ?? 3) == 1 ? $helper_null($input['turno1_fim'] ?? null) : null,
                ':t2_ini' => ($_SESSION['user_level'] ?? 3) == 1 ? $helper_null($input['turno2_inicio'] ?? null) : null,
                ':t2_fim' => ($_SESSION['user_level'] ?? 3) == 1 ? $helper_null($input['turno2_fim'] ?? null) : null,
                ':lat2' => ($_SESSION['user_level'] ?? 3) == 1 ? $helper_null($input['lat_permitida_2'] ?? null) : null,
                ':lng2' => ($_SESSION['user_level'] ?? 3) == 1 ? $helper_null($input['long_permitida_2'] ?? null) : null,
                ':dist2' => ($_SESSION['user_level'] ?? 3) == 1 ? ((!empty($input['distancia_max_permitida_2']) || ($input['distancia_max_permitida_2'] ?? null) === 0 || ($input['distancia_max_permitida_2'] ?? null) === '0') ? (int)$input['distancia_max_permitida_2'] : 200) : 200,
                ':area2' => ($_SESSION['user_level'] ?? 3) == 1 ? $helper_null($input['area_geofencing_2'] ?? null) : null,
                ':foto_perfil' => saveBase64Image($input['foto_base64'] ?? null, 'uploads/perfil/', 'perfil', 'new'),
                ':biometria_facial' => saveBase64Image($input['foto_base64'] ?? null, 'uploads/facial/', 'facial', 'new'),
                ':facial_descriptor' => $helper_null($input['facial_descriptor'] ?? null),
                ':nome_social' => $helper_null($input['nome_social'] ?? null),
                ':unidade_trabalho' => $helper_null($input['unidade_trabalho'] ?? null),
                ':tipo_contratacao' => $helper_null($input['tipo_contratacao'] ?? null),
                ':cargo_funcao' => $helper_null($input['cargo_funcao'] ?? null),
                ':carga_horaria' => $helper_null($input['carga_horaria'] ?? null),
                ':turno_trabalho' => $helper_null($input['turno_trabalho'] ?? null),
                ':data_admissao' => $helper_null($input['data_admissao'] ?? null),
                ':data_exercicio' => $helper_null($input['data_exercicio'] ?? null),
                ':endereco' => $helper_null($input['endereco'] ?? null),
                ':endereco_numero' => $helper_null($input['endereco_numero'] ?? null),
                ':endereco_complemento' => $helper_null($input['endereco_complemento'] ?? null),
                ':endereco_cep' => $helper_null($input['endereco_cep'] ?? null),
                ':endereco_bairro' => $helper_null($input['endereco_bairro'] ?? null),
                ':endereco_municipio' => $helper_null($input['endereco_municipio'] ?? null),
                ':endereco_uf' => $helper_null($input['endereco_uf'] ?? null),
                ':telefone_fixo' => $helper_null($input['telefone_fixo'] ?? null),
                ':endereco_email' => $helper_null($input['endereco_email'] ?? null),
                ':naturalidade' => $helper_null($input['naturalidade'] ?? null),
                ':naturalidade_uf' => $helper_null($input['naturalidade_uf'] ?? null),
                ':data_nascimento' => $helper_null($input['data_nascimento'] ?? null),
                ':estado_civil' => $helper_null($input['estado_civil'] ?? null),
                ':nacionalidade' => $helper_null($input['nacionalidade'] ?? null),
                ':sexo' => $helper_null($input['sexo'] ?? null),
                ':raca_cor' => $helper_null($input['raca_cor'] ?? null),
                ':deficiencia' => $helper_bool($input['deficiencia'] ?? false),
                ':deficiencia_tipo' => $helper_null($input['deficiencia_tipo'] ?? null),
                ':deficiencia_cid' => $helper_null($input['deficiencia_cid'] ?? null),
                ':deficiencia_grau' => $helper_null($input['deficiencia_grau'] ?? null),
                ':tipo_sanguineo' => $helper_null($input['tipo_sanguineo'] ?? null),
                ':escolaridade' => $helper_null($input['escolaridade'] ?? null),
                ':pis_pasep' => $helper_null($input['pis_pasep'] ?? null),
                ':carteira_conselheiro' => $helper_null($input['carteira_conselheiro'] ?? null),
                ':rg_numero' => $helper_null($input['rg_numero'] ?? null),
                ':rg_orgao' => $helper_null($input['rg_orgao'] ?? null),
                ':rg_data_emissao' => $helper_null($input['rg_data_emissao'] ?? null),
                ':cnh_numero' => $helper_null($input['cnh_numero'] ?? null),
                ':cnh_categoria' => $helper_null($input['cnh_categoria'] ?? null),
                ':cnh_validade' => $helper_null($input['cnh_validade'] ?? null),
                ':titulo_eleitor_numero' => $helper_null($input['titulo_eleitor_numero'] ?? null),
                ':titulo_eleitor_zona' => $helper_null($input['titulo_eleitor_zona'] ?? null),
                ':titulo_eleitor_secao' => $helper_null($input['titulo_eleitor_secao'] ?? null),
                ':reservista_numero' => $helper_null($input['reservista_numero'] ?? null),
                ':reservista_serie' => $helper_null($input['reservista_serie'] ?? null),
                ':ctps_numero' => $helper_null($input['ctps_numero'] ?? null),
                ':ctps_serie' => $helper_null($input['ctps_serie'] ?? null),
                ':ctps_data_emissao' => $helper_null($input['ctps_data_emissao'] ?? null),
                ':ctps_uf' => $helper_null($input['ctps_uf'] ?? null),
                ':nome_pai' => $helper_null($input['nome_pai'] ?? null),
                ':nome_mae' => $helper_null($input['nome_mae'] ?? null),
                ':nome_conjuge' => $helper_null($input['nome_conjuge'] ?? null),
                ':nacionalidade_conjuge' => $helper_null($input['nacionalidade_conjuge'] ?? null),
                ':naturalidade_conjuge' => $helper_null($input['naturalidade_conjuge'] ?? null),
                ':naturalidade_uf_conjuge' => $helper_null($input['naturalidade_uf_conjuge'] ?? null),
                ':data_nascimento_conjuge' => $helper_null($input['data_nascimento_conjuge'] ?? null),
                ':filhos_menores' => $helper_json($input['filhos_menores'] ?? null),
                ':dependentes_ir' => $helper_json($input['dependentes_ir'] ?? null),
                ':banco_nome' => $helper_null($input['banco_nome'] ?? null),
                ':banco_agencia' => $helper_null($input['banco_agencia'] ?? null),
                ':banco_conta' => $helper_null($input['banco_conta'] ?? null),
                ':observacoes_complementares' => $helper_null($input['observacoes_complementares'] ?? null),
                ':termo_dados' => $helper_json($input['termo_dados'] ?? null),
                ':grade_horarios' => $helper_json($input['grade_horarios'] ?? null),
                ':is_exonerado' => $helper_bool($input['is_exonerado'] ?? false),
                ':motivo_exoneracao' => $input['motivo_exoneracao'] ?? null
            ];

            $stmt->execute($params);

            echo json_encode(['success' => true, 'message' => 'Funcionário cadastrado com sucesso!', 'id' => $conn->lastInsertId()]);
            break;

        case 'PUT':
            if (!$is_super) {
                echo json_encode(['success' => false, 'message' => 'Permissão negada. Apenas administradores e gestores do CRH podem editar funcionários.']);
                exit;
            }
            // Update
            $input = json_decode(file_get_contents('php://input'), true);
            if (!isset($input['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID não informado.']);
                exit;
            }

            if (empty(trim($input['nome'] ?? '')) || empty(trim($input['matricula'] ?? '')) || (empty(trim($input['setor'] ?? '')) && empty(trim($input['setor2'] ?? '')))) {
                echo json_encode(['success' => false, 'message' => 'Campos básicos (Nome, Matrícula, Setor) são obrigatórios.']);
                exit;
            }

            $helper_null = function($val) { return !empty($val) ? $val : null; };
            $helper_json = function($val) { return !empty($val) ? json_encode($val) : null; };
            $helper_bool = function($val) { return ($val === 'true' || $val === true || $val === 1 || $val === '1') ? 1 : 0; };

            // --- TRAVA DE SEGURANÇA: GEOLOCALIZAÇÃO ---
            if (!$is_super) {
                // Se não é admin, buscamos os valores atuais no banco para sobrescrever o input e impedir alteração
                $stmtCheck = $conn->prepare("SELECT lat_permitida, long_permitida, distancia_max_permitida, area_geofencing, 
                                                   turno1_inicio, turno1_fim, turno2_inicio, turno2_fim,
                                                   lat_permitida_2, long_permitida_2, distancia_max_permitida_2, area_geofencing_2
                                            FROM ponto.funcionarios WHERE id = :id");
                $stmtCheck->execute([':id' => $input['id']]);
                $current = $stmtCheck->fetch();

                if ($current) {
                    $input['lat_permitida'] = $current['lat_permitida'];
                    $input['long_permitida'] = $current['long_permitida'];
                    $input['distancia_max_permitida'] = $current['distancia_max_permitida'];
                    $input['area_geofencing'] = $current['area_geofencing'];
                    $input['turno1_inicio'] = $current['turno1_inicio'];
                    $input['turno1_fim'] = $current['turno1_fim'];
                    $input['turno2_inicio'] = $current['turno2_inicio'];
                    $input['turno2_fim'] = $current['turno2_fim'];
                    $input['lat_permitida_2'] = $current['lat_permitida_2'];
                    $input['long_permitida_2'] = $current['long_permitida_2'];
                    $input['distancia_max_permitida_2'] = $current['distancia_max_permitida_2'];
                    $input['area_geofencing_2'] = $current['area_geofencing_2'];
                }
            }
            // ------------------------------------------

            $extraSql = "";
            $params = [
                ':nome' => $input['nome'],
                ':matricula' => $input['matricula'],
                ':setor' => $input['setor'] ?? '',
                ':setor2' => $input['setor2'] ?? '',
                ':cpf' => $input['cpf'] ?? null,
                ':celular' => $input['celular'] ?? null,
                ':id_horario' => $helper_null($input['id_horario'] ?? null),
                ':metodos_acesso' => isset($input['metodos_acesso']) ? json_encode($input['metodos_acesso']) : '["senha"]',
                ':lat' => $helper_null($input['lat_permitida'] ?? null),
                ':lng' => $helper_null($input['long_permitida'] ?? null),
                ':dist' => (!empty($input['distancia_max_permitida']) || ($input['distancia_max_permitida'] ?? null) === 0 || ($input['distancia_max_permitida'] ?? null) === '0') ? (int)$input['distancia_max_permitida'] : 200,
                ':area' => $helper_null($input['area_geofencing'] ?? null),
                ':t1_ini' => $helper_null($input['turno1_inicio'] ?? null),
                ':t1_fim' => $helper_null($input['turno1_fim'] ?? null),
                ':t2_ini' => $helper_null($input['turno2_inicio'] ?? null),
                ':t2_fim' => $helper_null($input['turno2_fim'] ?? null),
                ':lat2' => $helper_null($input['lat_permitida_2'] ?? null),
                ':lng2' => $helper_null($input['long_permitida_2'] ?? null),
                ':dist2' => (!empty($input['distancia_max_permitida_2']) || ($input['distancia_max_permitida_2'] ?? null) === 0 || ($input['distancia_max_permitida_2'] ?? null) === '0') ? (int)$input['distancia_max_permitida_2'] : 200,
                ':area2' => $helper_null($input['area_geofencing_2'] ?? null),
                ':nome_social' => $helper_null($input['nome_social'] ?? null),
                ':unidade_trabalho' => $helper_null($input['unidade_trabalho'] ?? null),
                ':tipo_contratacao' => $helper_null($input['tipo_contratacao'] ?? null),
                ':cargo_funcao' => $helper_null($input['cargo_funcao'] ?? null),
                ':carga_horaria' => $helper_null($input['carga_horaria'] ?? null),
                ':turno_trabalho' => $helper_null($input['turno_trabalho'] ?? null),
                ':data_admissao' => $helper_null($input['data_admissao'] ?? null),
                ':data_exercicio' => $helper_null($input['data_exercicio'] ?? null),
                ':endereco' => $helper_null($input['endereco'] ?? null),
                ':endereco_numero' => $helper_null($input['endereco_numero'] ?? null),
                ':endereco_complemento' => $helper_null($input['endereco_complemento'] ?? null),
                ':endereco_cep' => $helper_null($input['endereco_cep'] ?? null),
                ':endereco_bairro' => $helper_null($input['endereco_bairro'] ?? null),
                ':endereco_municipio' => $helper_null($input['endereco_municipio'] ?? null),
                ':endereco_uf' => $helper_null($input['endereco_uf'] ?? null),
                ':telefone_fixo' => $helper_null($input['telefone_fixo'] ?? null),
                ':endereco_email' => $helper_null($input['endereco_email'] ?? null),
                ':naturalidade' => $helper_null($input['naturalidade'] ?? null),
                ':naturalidade_uf' => $helper_null($input['naturalidade_uf'] ?? null),
                ':data_nascimento' => $helper_null($input['data_nascimento'] ?? null),
                ':estado_civil' => $helper_null($input['estado_civil'] ?? null),
                ':nacionalidade' => $helper_null($input['nacionalidade'] ?? null),
                ':sexo' => $helper_null($input['sexo'] ?? null),
                ':raca_cor' => $helper_null($input['raca_cor'] ?? null),
                ':deficiencia' => $helper_bool($input['deficiencia'] ?? false),
                ':deficiencia_tipo' => $helper_null($input['deficiencia_tipo'] ?? null),
                ':deficiencia_cid' => $helper_null($input['deficiencia_cid'] ?? null),
                ':deficiencia_grau' => $helper_null($input['deficiencia_grau'] ?? null),
                ':tipo_sanguineo' => $helper_null($input['tipo_sanguineo'] ?? null),
                ':escolaridade' => $helper_null($input['escolaridade'] ?? null),
                ':pis_pasep' => $helper_null($input['pis_pasep'] ?? null),
                ':carteira_conselheiro' => $helper_null($input['carteira_conselheiro'] ?? null),
                ':rg_numero' => $helper_null($input['rg_numero'] ?? null),
                ':rg_orgao' => $helper_null($input['rg_orgao'] ?? null),
                ':rg_data_emissao' => $helper_null($input['rg_data_emissao'] ?? null),
                ':cnh_numero' => $helper_null($input['cnh_numero'] ?? null),
                ':cnh_categoria' => $helper_null($input['cnh_categoria'] ?? null),
                ':cnh_validade' => $helper_null($input['cnh_validade'] ?? null),
                ':titulo_eleitor_numero' => $helper_null($input['titulo_eleitor_numero'] ?? null),
                ':titulo_eleitor_zona' => $helper_null($input['titulo_eleitor_zona'] ?? null),
                ':titulo_eleitor_secao' => $helper_null($input['titulo_eleitor_secao'] ?? null),
                ':reservista_numero' => $helper_null($input['reservista_numero'] ?? null),
                ':reservista_serie' => $helper_null($input['reservista_serie'] ?? null),
                ':ctps_numero' => $helper_null($input['ctps_numero'] ?? null),
                ':ctps_serie' => $helper_null($input['ctps_serie'] ?? null),
                ':ctps_data_emissao' => $helper_null($input['ctps_data_emissao'] ?? null),
                ':ctps_uf' => $helper_null($input['ctps_uf'] ?? null),
                ':nome_pai' => $helper_null($input['nome_pai'] ?? null),
                ':nome_mae' => $helper_null($input['nome_mae'] ?? null),
                ':nome_conjuge' => $helper_null($input['nome_conjuge'] ?? null),
                ':nacionalidade_conjuge' => $helper_null($input['nacionalidade_conjuge'] ?? null),
                ':naturalidade_conjuge' => $helper_null($input['naturalidade_conjuge'] ?? null),
                ':naturalidade_uf_conjuge' => $helper_null($input['naturalidade_uf_conjuge'] ?? null),
                ':data_nascimento_conjuge' => $helper_null($input['data_nascimento_conjuge'] ?? null),
                ':filhos_menores' => $helper_json($input['filhos_menores'] ?? null),
                ':dependentes_ir' => $helper_json($input['dependentes_ir'] ?? null),
                ':banco_nome' => $helper_null($input['banco_nome'] ?? null),
                ':banco_agencia' => $helper_null($input['banco_agencia'] ?? null),
                ':banco_conta' => $helper_null($input['banco_conta'] ?? null),
                ':observacoes_complementares' => $helper_null($input['observacoes_complementares'] ?? null),
                ':termo_dados' => $helper_json($input['termo_dados'] ?? null),
                ':grade_horarios' => $helper_json($input['grade_horarios'] ?? null),
                ':is_exonerado' => $helper_bool($input['is_exonerado'] ?? false),
                ':motivo_exoneracao' => $input['motivo_exoneracao'] ?? null,
                ':id' => $input['id']
            ];

            if (!empty($input['foto_base64']) && strpos($input['foto_base64'], 'data:image') === 0) {
                $pathPerfil = saveBase64Image($input['foto_base64'], 'uploads/perfil/', 'perfil', $input['id']);
                $pathFacial = saveBase64Image($input['foto_base64'], 'uploads/facial/', 'facial', $input['id']);
                
                if ($pathPerfil && $pathFacial) {
                    $extraSql .= ", foto_perfil = :foto_perfil, biometria_facial = :biometria_facial";
                    $params[':foto_perfil'] = $pathPerfil;
                    $params[':biometria_facial'] = $pathFacial;
                }
            }
            if (!empty($input['facial_descriptor'])) {
                $extraSql .= ", facial_descriptor = :facial_descriptor";
                // Já é enviado como JSON string pelo frontend
                $params[':facial_descriptor'] = $input['facial_descriptor'];
            }

            if (!empty($input['senha'])) {
                $extraSql .= ", senha = :senha";
                $params[':senha'] = password_hash($input['senha'], PASSWORD_DEFAULT);
            }

            $sql = "UPDATE ponto.funcionarios SET 
                    nome = :nome, matricula = :matricula, setor = :setor, setor2 = :setor2,
                    cpf = :cpf, celular = :celular, id_horario = :id_horario, 
                    metodos_acesso = :metodos_acesso, lat_permitida = :lat,
                    long_permitida = :lng, distancia_max_permitida = :dist,
                    area_geofencing = :area,
                    turno1_inicio = :t1_ini, turno1_fim = :t1_fim,
                    turno2_inicio = :t2_ini, turno2_fim = :t2_fim,
                    lat_permitida_2 = :lat2, long_permitida_2 = :lng2,
                    distancia_max_permitida_2 = :dist2, area_geofencing_2 = :area2,
                    nome_social = :nome_social, unidade_trabalho = :unidade_trabalho, 
                    tipo_contratacao = :tipo_contratacao, cargo_funcao = :cargo_funcao, 
                    carga_horaria = :carga_horaria, turno_trabalho = :turno_trabalho, 
                    data_admissao = :data_admissao, data_exercicio = :data_exercicio, 
                    endereco = :endereco, endereco_numero = :endereco_numero, 
                    endereco_complemento = :endereco_complemento, endereco_cep = :endereco_cep, 
                    endereco_bairro = :endereco_bairro, endereco_municipio = :endereco_municipio, 
                    endereco_uf = :endereco_uf, telefone_fixo = :telefone_fixo, 
                    endereco_email = :endereco_email, naturalidade = :naturalidade, 
                    naturalidade_uf = :naturalidade_uf, data_nascimento = :data_nascimento, 
                    estado_civil = :estado_civil, nacionalidade = :nacionalidade, 
                    sexo = :sexo, raca_cor = :raca_cor, deficiencia = :deficiencia, 
                    deficiencia_tipo = :deficiencia_tipo, deficiencia_cid = :deficiencia_cid, 
                    deficiencia_grau = :deficiencia_grau,
                    tipo_sanguineo = :tipo_sanguineo, escolaridade = :escolaridade, 
                    pis_pasep = :pis_pasep, carteira_conselheiro = :carteira_conselheiro, 
                    rg_numero = :rg_numero, rg_orgao = :rg_orgao, rg_data_emissao = :rg_data_emissao, 
                    cnh_numero = :cnh_numero, cnh_categoria = :cnh_categoria, 
                    cnh_validade = :cnh_validade, titulo_eleitor_numero = :titulo_eleitor_numero, 
                    titulo_eleitor_zona = :titulo_eleitor_zona, titulo_eleitor_secao = :titulo_eleitor_secao, 
                    reservista_numero = :reservista_numero, reservista_serie = :reservista_serie, 
                    ctps_numero = :ctps_numero, ctps_serie = :ctps_serie, 
                    ctps_data_emissao = :ctps_data_emissao, ctps_uf = :ctps_uf, 
                    nome_pai = :nome_pai, nome_mae = :nome_mae, nome_conjuge = :nome_conjuge, 
                    nacionalidade_conjuge = :nacionalidade_conjuge, naturalidade_conjuge = :naturalidade_conjuge, 
                    naturalidade_uf_conjuge = :naturalidade_uf_conjuge, data_nascimento_conjuge = :data_nascimento_conjuge, 
                    filhos_menores = :filhos_menores, dependentes_ir = :dependentes_ir, 
                    banco_nome = :banco_nome, banco_agencia = :banco_agencia, 
                    banco_conta = :banco_conta, observacoes_complementares = :observacoes_complementares,
                    termo_dados = :termo_dados, grade_horarios = :grade_horarios,
                    is_exonerado = :is_exonerado, motivo_exoneracao = :motivo_exoneracao
                    {$extraSql}, updated_at = NOW() 
                    WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            echo json_encode(['success' => true, 'message' => 'Funcionário atualizado com sucesso!']);
            break;

        case 'DELETE':
            // Verificação de permissão para exclusão
            if (!$is_super) {
                echo json_encode(['success' => false, 'message' => 'Permissão negada. Apenas administradores e gestores do CRH podem excluir funcionários.']);
                exit;
            }

            // Delete
            $input = json_decode(file_get_contents('php://input'), true);
            if (isset($input['ids']) && is_array($input['ids'])) {
                // Bulk Delete
                $ids = $input['ids'];
                if (empty($ids)) {
                    echo json_encode(['success' => false, 'message' => 'Nenhum funcionário selecionado.']);
                    exit;
                }
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $conn->prepare("DELETE FROM funcionarios WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                echo json_encode(['success' => true, 'message' => count($ids) . ' funcionários removidos com sucesso!']);
            } else if (isset($input['id'])) {
                // Single Delete
                $stmt = $conn->prepare("DELETE FROM funcionarios WHERE id = :id");
                $stmt->execute([':id' => $input['id']]);
                echo json_encode(['success' => true, 'message' => 'Funcionário removido com sucesso!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'ID não informado.']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            break;
    }

} catch (PDOException $e) {
    if ($e->getCode() == '23503') { // Foreign Key Violation in Postgres
        echo json_encode(['success' => false, 'message' => 'Não é possível remover pois existem registros associados.']);
    } else {
        // Log the error and return a detailed message for debugging
        echo json_encode(['success' => false, 'message' => 'Erro de banco de dados: ' . $e->getMessage()]);
    }
}
