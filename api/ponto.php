<?php
header('Content-Type: application/json');

require_once '../config/Database.php';

use Config\Database;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$matricula = isset($input['matricula']) ? trim($input['matricula']) : '';
$biometria = isset($input['biometria']) ? trim($input['biometria']) : '';
// Ação para obter métodos de acesso permitidos antes de registrar
if (isset($input['action']) && $input['action'] === 'get_metodos') {
    $matricula = $input['matricula'] ?? '';
    $conn = Database::getConnection(); 
    $stmt = $conn->prepare("SELECT * FROM funcionarios WHERE matricula = :matricula");
    $stmt->execute([':matricula' => $matricula]);
    $func = $stmt->fetch();

    if ($func) {
        if (!empty($func['is_exonerado'])) {
            echo json_encode(['success' => false, 'message' => 'Acesso revogado. Funcionário exonerado.']);
            exit;
        }
        $temSenha = !empty($func['senha']);
        $temFacial = !empty($func['facial_descriptor']) && $func['facial_descriptor'] !== 'null' && $func['facial_descriptor'] !== '[]' && $func['facial_descriptor'] !== '""';
        $temDigital = !empty($func['biometria']);
        
        if (!$temSenha && !$temFacial && !$temDigital) {
            echo json_encode(['success' => false, 'message' => 'Matrícula ou senha incorretos.']);
            exit;
        }

        $temWebAuthn = !empty($func['webauthn_id']);
        
        // Verificação de ficha incompleta (incluindo novos campos)
        $obrigatorios = [
            'cpf' => 'CPF', 'data_nascimento' => 'Data de Nascimento', 'rg_numero' => 'RG',
            'rg_orgao' => 'Órgão Emissor', 'rg_data_emissao' => 'Data de Emissão do RG',
            'endereco' => 'Logradouro', 'endereco_numero' => 'Número', 'endereco_bairro' => 'Bairro',
            'endereco_cep' => 'CEP', 'endereco_municipio' => 'Cidade', 'endereco_uf' => 'UF', 
            'nome_mae' => 'Nome da Mãe', 'celular' => 'Celular'
        ];
        
        $camposFaltando = [];
        foreach ($obrigatorios as $campo => $label) {
            if (empty($func[$campo])) {
                $camposFaltando[] = ['campo' => $campo, 'label' => $label];
            }
        }

        $metodosRaw = $func['metodos_acesso'] ?? '["senha"]';
        $metodos = json_decode($metodosRaw, true);
        if (!is_array($metodos)) {
            $metodos = ['senha'];
        }

        // --- RESTRIÇÃO SOLICITADA ---
        // Removendo 'senha' e 'digital' dos métodos permitidos para o RELÓGIO (Batida)
        // A biometria deve ser EXCLUSIVAMENTE facial para registrar o ponto.
        // A senha será usada apenas no "Meu Acesso".
        $metodosFiltrados = array_filter($metodos, function($m) {
            return $m === 'facial' || $m === 'biometria' || $m === 'qrcode'; 
        });
        
        // Se após filtrar não sobrar nada mas o usuário tem facial cadastrado, forçamos facial
        if (empty($metodosFiltrados) && $temFacial) {
            $metodosFiltrados = ['facial'];
        }

        // ---- Geolocalização Diária (Novo) ----
        $mapaDias = [
            'Monday' => 'segunda', 'Tuesday' => 'terca', 'Wednesday' => 'quarta',
            'Thursday' => 'quinta', 'Friday' => 'sexta', 'Saturday' => 'sabado', 'Sunday' => 'domingo'
        ];
        $diaHoje = $mapaDias[date('l')];
        $grade = json_decode($func['grade_horarios'] ?? '[]', true);
        
        $geofencingReq = null;
        if (isset($grade[$diaHoje]) && is_array($grade[$diaHoje]) && !empty($grade[$diaHoje]['area'])) {
            $stmtArea = $conn->prepare("SELECT * FROM geofencing_presets WHERE nome_area = :nome");
            $stmtArea->execute([':nome' => $grade[$diaHoje]['area']]);
            $preset = $stmtArea->fetch(PDO::FETCH_ASSOC);
            if ($preset) {
                $geofencingReq = [
                    'nome' => $preset['nome_area'],
                    'lat' => (float)$preset['latitude'],
                    'lng' => (float)$preset['longitude'],
                    'dist' => (int)$preset['distancia_max']
                ];
            }
        }
        
        // Se não houver na grade, tenta Turno 2 (se no horário) ou Turno 1 (Principal)
        if (!$geofencingReq) {
            // Lógica simplificada de fallback para o retorno de métodos
            if (!empty($func['lat_permitida']) && !empty($func['long_permitida'])) {
                $geofencingReq = [
                    'nome' => 'Principal',
                    'lat' => (float)$func['lat_permitida'],
                    'lng' => (float)$func['long_permitida'],
                    'dist' => (int)($func['distancia_max_permitida'] ?? 200)
                ];
            }
        }

        echo json_encode(array_merge($func, [
            'success' => true, 
            'metodos' => array_values($metodosFiltrados),
            'has_webauthn' => $temWebAuthn,
            'ficha_incompleta' => !empty($camposFaltando),
            'campos_faltando' => $camposFaltando,
            'geofencing' => $geofencingReq
        ]));
    } else {
        echo json_encode(['success' => false, 'message' => 'Matrícula ou senha incorretos.']);
    }
    exit;
}

// Ação MEU ACESSO (Individual)
if (isset($input['action']) && $input['action'] === 'get_meu_ponto') {
    $matricula = $input['matricula'] ?? '';
    $senha = $input['senha'] ?? '';
    
    $conn = Database::getConnection();
    $stmt = $conn->prepare("SELECT f.*, h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario, h.tolerancia_entrada, h.tolerancia_saida 
                            FROM funcionarios f 
                            LEFT JOIN horarios h ON f.id_horario = h.id 
                            WHERE f.matricula = :m");
    $stmt->execute([':m' => $matricula]);
    $func = $stmt->fetch();
    if ($func && !empty($func['is_exonerado'])) {
        echo json_encode(['success' => false, 'message' => 'Acesso ao portal revogado. Funcionário exonerado.']);
        exit;
    }

    if (!$func || empty($func['senha']) || !password_verify($senha, $func['senha'])) {
        echo json_encode(['success' => false, 'message' => 'Credenciais inválidas para visualização.']);
        exit;
    }

    $startDate = !empty($input['start_date']) ? $input['start_date'] : date('Y-m-01');
    $endDate = !empty($input['end_date']) ? $input['end_date'] : date('Y-m-t');

    $sql = "SELECT r.*, h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario, h.tolerancia_entrada, h.tolerancia_saida, f.grade_horarios
            FROM registros r
            LEFT JOIN funcionarios f ON r.id_funcionario = f.id
            LEFT JOIN horarios h ON f.id_horario = h.id
            WHERE r.id_funcionario = :id_func AND r.data BETWEEN :start AND :end
            ORDER BY r.data DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id_func' => $func['id'], ':start' => $startDate, ':end' => $endDate]);
    $registrosExistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- NOVO: Preenchimento de dias vazios (Virtualização) ---
    $regPorData = [];
    foreach ($registrosExistentes as $r) {
        $regPorData[$r['data']] = $r;
    }

    $finalRegistros = [];
    $startTs = strtotime($startDate);
    $endTs = strtotime($endDate);
    
    for ($ts = $startTs; $ts <= $endTs; $ts = strtotime("+1 day", $ts)) {
        $d = date('Y-m-d', $ts);
        if (isset($regPorData[$d])) {
            $finalRegistros[] = $regPorData[$d];
        } else {
            // Registro Virtual
            $finalRegistros[] = [
                'data' => $d,
                'id_funcionario' => $func['id'],
                'nome' => $func['nome'],
                'matricula' => $func['matricula'],
                'primeiro_ponto' => null,
                'segundo_ponto' => null,
                'terceiro_ponto' => null,
                'quarto_ponto' => null,
                'justificativa' => null,
                'tipo_justificativa' => null,
                'primeiro_horario' => $func['primeiro_horario'],
                'segundo_horario' => $func['segundo_horario'],
                'terceiro_horario' => $func['terceiro_horario'],
                'quarto_horario' => $func['quarto_horario'],
                'tolerancia_entrada' => $func['tolerancia_entrada'],
                'tolerancia_saida' => $func['tolerancia_saida'],
                'grade_horarios' => $func['grade_horarios'],
                'virtual' => true
            ];
        }
    }
    
    // Ordenar por data DESC
    usort($finalRegistros, function($a, $b) {
        return strcmp($b['data'], $a['data']);
    });
    
    $registros = &$finalRegistros;

    // Buscar todos os horários para lookup rápido no processamento da grade
    $stmtH = $conn->query("SELECT * FROM horarios");
    $todosHorarios = [];
    while($h = $stmtH->fetch(PDO::FETCH_ASSOC)) {
        $todosHorarios[$h['id']] = $h;
    }

    // Buscar afastamentos do funcionário para o período
    $stmtF = $conn->prepare("SELECT * FROM ferias WHERE id_funcionario = :id AND (:start BETWEEN data_inicio AND data_fim OR :end BETWEEN data_inicio AND data_fim OR data_inicio BETWEEN :start AND :end)");
    $stmtF->execute([':id' => $func['id'], ':start' => $startDate, ':end' => $endDate]);
    $afastamentos = $stmtF->fetchAll(PDO::FETCH_ASSOC);

    // Buscar liberações de ponto para o período
    $setorFunc = $func['setor'] ?? '';
    $stmtL = $conn->prepare("SELECT * FROM ponto_liberado 
                             WHERE (setor = 'TODOS' OR setor = :s1 OR setor = :s2)
                             AND (DATE(data_hora) BETWEEN :start AND :end)");
    $stmtL->execute([':s1' => $setorFunc, ':s2' => $setorFunc, ':start' => $startDate, ':end' => $endDate]);
    $liberacoes = $stmtL->fetchAll(PDO::FETCH_ASSOC);
    
    $mapaLib = [];
    foreach($liberacoes as $l) {
        $d = explode(' ', $l['data_hora'])[0];
        $mapaLib[$d] = $l;
    }

    // Processamento de Faltas (Igual ao api/relatorios.php)
    $hojeStr = date('Y-m-d');
    $agoraStr = date('H:i');

    foreach ($registros as &$reg) {
        $regDate = $reg['data'];
        $reg['falta_turno1_entrada'] = false;
        $reg['falta_turno1_saida'] = false;
        $reg['falta_turno2_entrada'] = false;
        $reg['falta_turno2_saida'] = false;
        $reg['em_ferias'] = false;
        $reg['motivo_afastamento'] = null;
        $reg['liberacao'] = $mapaLib[$regDate] ?? null;

        // Verificar afastamentos
        foreach ($afastamentos as $afast) {
            if ($regDate >= $afast['data_inicio'] && $regDate <= $afast['data_fim']) {
                $reg['em_ferias'] = true;
                $reg['motivo_afastamento'] = ($afast['tipo_afastamento'] === 'outros') ? ($afast['motivo_especifico'] ?: 'Outros') : ($afast['tipo_afastamento'] ?: 'Afastamento');
                break;
            }
        }

        if ($reg['em_ferias'] || $regDate > $hojeStr) continue;

        // ---- Aplicar Grade de Horários Específica para o Dia ----
        $mapaDias = [
            'Monday' => 'segunda', 'Tuesday' => 'terca', 'Wednesday' => 'quarta',
            'Thursday' => 'quinta', 'Friday' => 'sexta', 'Saturday' => 'sabado', 'Sunday' => 'domingo'
        ];
        $diaSemana = $mapaDias[date('l', strtotime($regDate))];
        $grade = json_decode($reg['grade_horarios'] ?? '[]', true);

        if (isset($grade[$diaSemana]) && !empty($grade[$diaSemana])) {
            $diaData = $grade[$diaSemana];
            
            if (is_array($diaData) && isset($diaData['horario_id']) && !empty($diaData['horario_id'])) {
                $hId = $diaData['horario_id'];
                if (isset($todosHorarios[$hId])) {
                    $hEsp = $todosHorarios[$hId];
                    $reg['primeiro_horario'] = $hEsp['primeiro_horario'];
                    $reg['segundo_horario'] = $hEsp['segundo_horario'];
                    $reg['terceiro_horario'] = $hEsp['terceiro_horario'];
                    $reg['quarto_horario'] = $hEsp['quarto_horario'];
                    $reg['tolerancia_entrada'] = $hEsp['tolerancia_entrada'];
                    $reg['tolerancia_saida'] = $hEsp['tolerancia_saida'];
                }
            } else if (is_array($diaData)) {
                $p1 = $diaData[0] ?? ($diaData['p1'] ?? null);
                $p2 = $diaData[1] ?? ($diaData['p2'] ?? null);
                $p3 = $diaData[2] ?? ($diaData['p3'] ?? null);
                $p4 = $diaData[3] ?? ($diaData['p4'] ?? null);
                
                if ($p1 || $p2 || $p3 || $p4) {
                   $reg['primeiro_horario'] = $p1;
                   $reg['segundo_horario'] = $p2;
                   $reg['terceiro_horario'] = $p3;
                   $reg['quarto_horario'] = $p4;
                }
            }
        }

        $tolEntr = (int)($reg['tolerancia_entrada'] ?? 15);
        $tolSai = (int)($reg['tolerancia_saida'] ?? 15);
        
        // Turno 1 - Entrada
        if ($reg['primeiro_horario'] && empty($reg['primeiro_ponto'])) {
            $limite = date('H:i', strtotime($reg['primeiro_horario']) + ($tolEntr * 60));
            if ($regDate < $hojeStr || ($regDate == $hojeStr && $agoraStr > $limite)) $reg['falta_turno1_entrada'] = true;
        } else if ($reg['primeiro_horario'] && !empty($reg['primeiro_ponto'])) {
            $pSeg = strtotime($reg['primeiro_ponto']);
            $hSeg = strtotime($reg['primeiro_horario']);
            $reg['atrasou_primeiro_ponto'] = ($pSeg > ($hSeg + ($tolEntr * 60)));
        }

        // Turno 1 - Saída
        if ($reg['segundo_horario'] && empty($reg['segundo_ponto'])) {
            if ($regDate < $hojeStr) $reg['falta_turno1_saida'] = true;
        } else if ($reg['segundo_horario'] && !empty($reg['segundo_ponto'])) {
            $pSeg = strtotime($reg['segundo_ponto']);
            $hSeg = strtotime($reg['segundo_horario']);
            $reg['atrasou_segundo_ponto'] = ($pSeg < ($hSeg - ($tolSai * 60)));
        }

        // Turno 2 - Entrada
        if ($reg['terceiro_horario'] && empty($reg['terceiro_ponto'])) {
            $limite = date('H:i', strtotime($reg['terceiro_horario']) + ($tolEntr * 60));
            if ($regDate < $hojeStr || ($regDate == $hojeStr && $agoraStr > $limite)) $reg['falta_turno2_entrada'] = true;
        } else if ($reg['terceiro_horario'] && !empty($reg['terceiro_ponto'])) {
            $pSeg = strtotime($reg['terceiro_ponto']);
            $hSeg = strtotime($reg['terceiro_horario']);
            $reg['atrasou_terceiro_ponto'] = ($pSeg > ($hSeg + ($tolEntr * 60)));
        }

        // Turno 2 - Saída
        if ($reg['quarto_horario'] && empty($reg['quarto_ponto'])) {
            if ($regDate < $hojeStr) $reg['falta_turno2_saida'] = true;
        } else if ($reg['quarto_horario'] && !empty($reg['quarto_ponto'])) {
            $pSeg = strtotime($reg['quarto_ponto']);
            $hSeg = strtotime($reg['quarto_horario']);
            $reg['atrasou_quarto_ponto'] = ($pSeg < ($hSeg - ($tolSai * 60)));
        }
    }

    echo json_encode(['success' => true, 'data' => $registros, 'funcionario' => $func['nome']]);
    exit;
}

// Ação de TROCA DE SENHA (Indivual)
if (isset($input['action']) && $input['action'] === 'change_password') {
    $matricula = $input['matricula'] ?? '';
    $senhaAntiga = $input['senha_antiga'] ?? '';
    $novaSenha = $input['nova_senha'] ?? '';
    
    if (strlen($novaSenha) < 6) {
        echo json_encode(['success' => false, 'message' => 'A nova senha deve ter pelo menos 6 caracteres.']);
        exit;
    }

    $conn = Database::getConnection();
    $stmt = $conn->prepare("SELECT id, senha FROM funcionarios WHERE matricula = :m");
    $stmt->execute([':m' => $matricula]);
    $func = $stmt->fetch();

    if (!$func || empty($func['senha']) || !password_verify($senhaAntiga, $func['senha'])) {
        echo json_encode(['success' => false, 'message' => 'Senha atual incorreta.']);
        exit;
    }

    $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
    $stmtUpd = $conn->prepare("UPDATE funcionarios SET senha = :s, updated_at = NOW() WHERE id = :id");
    $stmtUpd->execute([':s' => $hash, ':id' => $func['id']]);

    echo json_encode(['success' => true, 'message' => 'Senha alterada com sucesso!']);
    exit;
}

// Ação de COMUNICADO (Funcionário informando atraso/falta)
if (isset($input['action']) && $input['action'] === 'salvar_comunicado') {
    $matricula = $input['matricula'] ?? '';
    $senha = $input['senha'] ?? '';
    $mensagem = $input['mensagem'] ?? '';
    $dataComunicado = $input['data'] ?? date('Y-m-d');
    
    $conn = Database::getConnection();
    $stmt = $conn->prepare("SELECT id, senha FROM funcionarios WHERE matricula = :m");
    $stmt->execute([':m' => $matricula]);
    $func = $stmt->fetch();

    if (!$func || empty($func['senha']) || !password_verify($senha, $func['senha'])) {
        echo json_encode(['success' => false, 'message' => 'Credenciais inválidas.']);
        exit;
    }

    // Processamento de Anexo no Comunicado
    $anexoPath = null;
    $anexoData = $input['anexo'] ?? null; // Esperamos base64 ou nome do arquivo se vier via $_FILES

    if ($anexoData) {
        $uploadDir = '../uploads/comunicados/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (strpos($anexoData, 'data:image') === 0 || strpos($anexoData, 'data:application/pdf') === 0) {
            // É base64
            $parts = explode(',', $anexoData);
            $data = base64_decode($parts[1]);
            $mime = explode(':', explode(';', $parts[0])[0])[1];
            $ext = ($mime === 'application/pdf') ? 'pdf' : 'jpg';
            $filename = uniqid('comunicado_') . '.' . $ext;
            if (file_put_contents($uploadDir . $filename, $data)) {
                $anexoPath = 'uploads/comunicados/' . $filename;
            }
        }
    }

    // Verifica se já existe registro para o dia, senão cria
    $stmtReg = $conn->prepare("SELECT id FROM registros WHERE id_funcionario = :id_func AND data = :dt");
    $stmtReg->execute([':id_func' => $func['id'], ':dt' => $dataComunicado]);
    $reg = $stmtReg->fetch();

    if ($reg) {
        $sql = "UPDATE registros SET comunicado = :msg, updated_at = NOW()";
        $params = [':msg' => $mensagem, ':id' => $reg['id']];
        if ($anexoPath) {
            $sql .= ", anexo_comunicado = :anexo";
            $params[':anexo'] = $anexoPath;
        }
        $sql .= " WHERE id = :id";
        $stmtUpd = $conn->prepare($sql);
        $stmtUpd->execute($params);
    } else {
        $stmtIns = $conn->prepare("INSERT INTO registros (id_funcionario, data, comunicado, anexo_comunicado, created_at, updated_at) VALUES (:id_func, :dt, :msg, :anexo, NOW(), NOW())");
        $stmtIns->execute([
            ':id_func' => $func['id'], 
            ':dt' => $dataComunicado, 
            ':msg' => $mensagem,
            ':anexo' => $anexoPath
        ]);
    }

    echo json_encode(['success' => true, 'message' => 'Comunicado enviado com sucesso!']);
    exit;
}

$action = isset($input['action']) ? $input['action'] : '';
$imageData = isset($input['image_data']) ? $input['image_data'] : '';
$credentialId = isset($input['credentialId']) ? $input['credentialId'] : '';

if (empty($matricula) && empty($biometria) && $action !== 'ponto_facial' && $action !== 'validar_facial' && $action !== 'ponto_webauthn') {
    echo json_encode(['success' => false, 'message' => 'Identificação não informada.']);
    exit;
}

try {
    $conn = Database::getConnection();

    // 1. Buscar o funcionário e seu horário
    if ($action === 'ponto_facial' || $action === 'validar_facial') {
        if (empty($imageData)) {
            echo json_encode(['success' => false, 'message' => 'Imagem não capturada.']);
            exit;
        }

        $inputDescriptor = isset($input['facial_descriptor']) ? $input['facial_descriptor'] : null;
        $funcionario = null;
        $bestMatch = 1.0; 
        $threshold = 0.5; // Stricter threshold for overall accuracy

        function euclideanDistance($a, $b)
        {
            if (!$a || !$b || count($a) !== count($b))
                return 1.0;
            $sum = 0;
            for ($i = 0; $i < count($a); $i++) {
                $sum += pow($a[$i] - $b[$i], 2);
            }
            return sqrt($sum);
        }

        if (!empty($matricula)) {
            // PASS 1: 1:1 Matching (Identify by Matricula and only verify Face)
            $stmt = $conn->prepare("
                SELECT f.*, 
                       h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario, h.tolerancia_entrada, h.tolerancia_saida
                FROM funcionarios f
                LEFT JOIN horarios h ON f.id_horario = h.id
                WHERE f.matricula = :matricula
            ");
            $stmt->execute([':matricula' => $matricula]);
            $f = $stmt->fetch();

            if ($f && !empty($f['facial_descriptor']) && $inputDescriptor) {
                $storedDescriptor = json_decode($f['facial_descriptor'], true);
                $distance = euclideanDistance($inputDescriptor, $storedDescriptor);
                
                // Use a slightly more relaxed threshold for 1:1 match if desired, 
                // but 0.6 is standard face-api. 0.5 is safer.
                if ($distance < 0.6) { 
                    $f['facial_descriptor'] = (string)$f['facial_descriptor']; // Ensure string type for consistency
                    $funcionario = $f;
                    $bestMatch = $distance;
                } else {
                    echo json_encode(['success' => false, 'message' => 'A face capturada não corresponde à matrícula informada.']);
                    exit;
                }
            } else if (!$f) {
                echo json_encode(['success' => false, 'message' => 'Matrícula não encontrada para verificação facial.']);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Biometria facial não cadastrada para esta matrícula.']);
                exit;
            }
        } else {
            // PASS 2: 1:N Global Search (No matricula provided - fallback)
            $stmt = $conn->prepare("
                SELECT f.*, 
                       h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario, h.tolerancia_entrada, h.tolerancia_saida
                FROM funcionarios f
                LEFT JOIN horarios h ON f.id_horario = h.id
                WHERE f.facial_descriptor::text IS NOT NULL AND f.facial_descriptor::text != '' AND f.facial_descriptor::text != 'null'
            ");
            $stmt->execute();
            $funcionariosComFacial = $stmt->fetchAll();

            if ($inputDescriptor) {
                foreach ($funcionariosComFacial as $f) {
                    if (empty($f['facial_descriptor']))
                        continue;
                    $storedDescriptor = json_decode($f['facial_descriptor'], true);
                    $distance = euclideanDistance($inputDescriptor, $storedDescriptor);

                    if ($distance < $threshold && $distance < $bestMatch) {
                        $bestMatch = $distance;
                        $funcionario = $f;
                    }
                }
            }
        }

        if (!$funcionario) {
            echo json_encode(['success' => false, 'message' => 'Face não reconhecida ou não cadastrada.']);
            exit;
        }

        // Se for apenas validação para o termo de imagem, paramos aqui
        if ($action === 'validar_facial') {
            echo json_encode([
                'success' => true,
                'id' => $funcionario['id'],
                'nome' => $funcionario['nome'],
                'matricula' => $funcionario['matricula']
            ]);
            exit;
        }

    } else if ($action === 'ponto_webauthn') {
        echo json_encode(['success' => false, 'message' => 'O registro de ponto deve ser exclusivamente via Biometria Facial.']);
        exit;

    } else if (!empty($biometria)) {
        $stmt = $conn->prepare("
            SELECT f.*, 
                   h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario, h.tolerancia_entrada, h.tolerancia_saida
            FROM funcionarios f
            LEFT JOIN horarios h ON f.id_horario = h.id
            WHERE f.biometria = :biometria
        ");
        $stmt->execute([':biometria' => $biometria]);
        $funcionario = $stmt->fetch();
        
        // Bloqueio de Digital (Se o usuário quer exclusivamente facial)
        echo json_encode(['success' => false, 'message' => 'O registro de ponto deve ser exclusivamente via Biometria Facial.']);
        exit;
    } else {
        // Bloqueio de Senha para registro de ponto
        echo json_encode(['success' => false, 'message' => 'O registro de ponto deve ser exclusivamente via Biometria Facial. A senha é permitida apenas para consulta do cartão.']);
        exit;
    }

    if (!$funcionario) {
        if (!empty($biometria)) {
            echo json_encode(['success' => false, 'message' => 'Digital não reconhecida.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Matrícula ou senha incorretos.']);
        }
        exit;
    }

    // --- BLOQUEIO PARA EXONERADOS ---
    if (!empty($funcionario['is_exonerado'])) {
        echo json_encode(['success' => false, 'message' => 'Acesso revogado. Funcionário exonerado.']);
        exit;
    }

    // --- VERIFICAÇÃO DE FICHA INCOMPLETA (MANDATÓRIA) ---
    $obrigatorios = [
        'cpf' => 'CPF', 'data_nascimento' => 'Data de Nascimento', 'rg_numero' => 'RG',
        'rg_orgao' => 'Órgão Emissor', 'rg_data_emissao' => 'Data de Emissão do RG',
        'endereco' => 'Logradouro', 'endereco_numero' => 'Número', 'endereco_bairro' => 'Bairro',
        'endereco_cep' => 'CEP', 'endereco_municipio' => 'Cidade', 'endereco_uf' => 'UF', 
        'nome_mae' => 'Nome da Mãe', 'celular' => 'Celular'
    ];
    $camposFaltando = [];
    foreach ($obrigatorios as $campo => $label) {
        if (empty($funcionario[$campo])) {
            $camposFaltando[] = ['campo' => $campo, 'label' => $label];
        }
    }
    if (!empty($camposFaltando)) {
        echo json_encode(array_merge($funcionario, [
            'success' => false,
            'ficha_incompleta' => true,
            'campos_faltando' => $camposFaltando,
            'message' => 'Ficha incompleta. Por favor, atualize seus dados para liberar o ponto.'
        ]));
        exit;
    }

    // 1.1 Verificar se o método utilizado é permitido
    $metodosPermitidos = isset($funcionario['metodos_acesso']) ? json_decode($funcionario['metodos_acesso'], true) : ['senha'];
    $metodoUsado = '';
    if ($action === 'ponto_facial')
        $metodoUsado = 'facial';
    else if ($action === 'ponto_webauthn' || !empty($biometria))
        $metodoUsado = 'biometria';
    else if (!empty($matricula))
        $metodoUsado = 'senha';
    // QR Code ainda não implementado no fluxo de captura, mas adicionaremos suporte
    if ($action === 'ponto_qrcode')
        $metodoUsado = 'qrcode';

    if (!in_array($metodoUsado, $metodosPermitidos)) {
        $labels = [
            'senha' => 'Senha (Matrícula)',
            'facial' => 'Biometria Facial',
            'biometria' => 'Digital/Mobile',
            'qrcode' => 'QR Code'
        ];
        $metodoNome = isset($labels[$metodoUsado]) ? $labels[$metodoUsado] : $metodoUsado;
        echo json_encode(['success' => false, 'message' => "O método [{$metodoNome}] não está habilitado para o seu perfil. Procure o RH."]);
        exit;
    }

    // ---- Distância Haversine (Geofencing) ----
    function haversineDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1-$a));
    }

    $lat_cliente = isset($input['lat']) ? (float)$input['lat'] : null;
    $lng_cliente = isset($input['lng']) ? (float)$input['lng'] : null;

    $id_funcionario = $funcionario['id'];
    $dataAtual = date('Y-m-d');
    $horaAtual = date('H:i');
    $agoraCompleto = date('Y-m-d H:i:s');

    // --- VERIFICAÇÃO DE PONTO LIBERADO (CRH) ---
    $is_liberado = false;
    $sqlLib = "SELECT id FROM ponto_liberado 
               WHERE :agora >= data_hora AND DATE(:agora) = DATE(data_hora)
               AND (setor = 'TODOS' OR setor = :setor OR setor = :setor2)
               LIMIT 1";
    $stmtLib = $conn->prepare($sqlLib);
    $stmtLib->execute([
        ':agora' => $agoraCompleto,
        ':setor' => $funcionario['setor'] ?? '',
        ':setor2' => $funcionario['setor2'] ?? ''
    ]);
    if ($stmtLib->fetch()) {
        $is_liberado = true;
    }

    $lat_esperada = null;
    $lng_esperada = null;
    $dist_max_esperada = 200;

    // --- PRIORIDADE 1: ÁREA GRAVADA NA GRADE DO DIA ---
    $mapaDias = [
        'Monday' => 'segunda', 'Tuesday' => 'terca', 'Wednesday' => 'quarta',
        'Thursday' => 'quinta', 'Friday' => 'sexta', 'Saturday' => 'sabado', 'Sunday' => 'domingo'
    ];
    $diaHoje = $mapaDias[date('l')];
    $grade = json_decode($funcionario['grade_horarios'] ?? '[]', true);

    $nome_area_exibir = "Área Principal";
    if (isset($grade[$diaHoje]) && is_array($grade[$diaHoje]) && !empty($grade[$diaHoje]['area'])) {
        $stmtArea = $conn->prepare("SELECT * FROM geofencing_presets WHERE nome_area = :nome");
        $stmtArea->execute([':nome' => $grade[$diaHoje]['area']]);
        $preset = $stmtArea->fetch(PDO::FETCH_ASSOC);
        if ($preset) {
            $lat_esperada = $preset['latitude'];
            $lng_esperada = $preset['longitude'];
            $dist_max_esperada = (int)$preset['distancia_max'];
            $nome_area_exibir = $preset['nome_area'];
        }
    }

    // --- PRIORIDADE 2: TURNO 2 (Se estiver no horário) ---
    if ($lat_esperada === null) {
        if (!empty($funcionario['lat_permitida_2']) && !empty($funcionario['long_permitida_2']) && 
            !empty($funcionario['turno2_inicio']) && !empty($funcionario['turno2_fim'])) {
            
            $t2ini = strtotime("-1 hour", strtotime($funcionario['turno2_inicio']));
            $t2fim = strtotime("+1 hour", strtotime($funcionario['turno2_fim']));
            $agora = strtotime($horaAtual);
            
            if ($t2ini <= $t2fim) {
                $inT2 = ($agora >= $t2ini && $agora <= $t2fim);
            } else {
                $inT2 = ($agora >= $t2ini || $agora <= $t2fim);
            }
            
            if ($inT2) {
                $lat_esperada = $funcionario['lat_permitida_2'];
                $lng_esperada = $funcionario['long_permitida_2'];
                $dist_max_esperada = !empty($funcionario['distancia_max_permitida_2']) ? (int)$funcionario['distancia_max_permitida_2'] : 200;
                $nome_area_exibir = "Área 2º Turno";
            }
        }
    }

    // --- PRIORIDADE 3: TURNO 1 (PRINCIPAL) ---
    if ($lat_esperada === null) {
        if (!empty($funcionario['lat_permitida']) && !empty($funcionario['long_permitida'])) {
            $lat_esperada = $funcionario['lat_permitida'];
            $lng_esperada = $funcionario['long_permitida'];
            $dist_max_esperada = !empty($funcionario['distancia_max_permitida']) ? (int)$funcionario['distancia_max_permitida'] : 200;
            $nome_area_exibir = "Área Principal";
        }
    }

    if ($lat_esperada !== null && $lng_esperada !== null) {
        if ($lat_cliente === null || $lng_cliente === null) {
            echo json_encode(['success' => false, 'message' => 'Geolocalização obrigatória para seu turno de trabalho. Ative o GPS.']);
            exit;
        }

        $distancia_real = haversineDistance(
            (float)$lat_esperada,
            (float)$lng_esperada,
            $lat_cliente,
            $lng_cliente
        );

        if ($distancia_real > $dist_max_esperada) {
            echo json_encode(['success' => false, 'message' => "Fora do raio permitido para a área: [{$nome_area_exibir}]! Distância: " . round($distancia_real) . "m (Limite: {$dist_max_esperada}m)"]);
            exit;
        }
    }

    // ---- Aplicar Grade de Horários Específica para o Dia (Horários) ----
    // (A variável $grade e $diaHoje já foram definidas acima para o geofencing)

    if (isset($grade[$diaHoje]) && !empty($grade[$diaHoje])) {
        $hojeData = $grade[$diaHoje];
        
        // Se for um objeto com horario_id, buscamos os dados desse horário para obter as tolerâncias
        if (is_array($hojeData) && isset($hojeData['horario_id']) && !empty($hojeData['horario_id'])) {
            $stmtH = $conn->prepare("SELECT * FROM horarios WHERE id = :hid");
            $stmtH->execute([':hid' => $hojeData['horario_id']]);
            $hEsp = $stmtH->fetch();
            if ($hEsp) {
                $funcionario['primeiro_horario'] = $hEsp['primeiro_horario'];
                $funcionario['segundo_horario'] = $hEsp['segundo_horario'];
                $funcionario['terceiro_horario'] = $hEsp['terceiro_horario'];
                $funcionario['quarto_horario'] = $hEsp['quarto_horario'];
                $funcionario['tolerancia_entrada'] = $hEsp['tolerancia_entrada'];
                $funcionario['tolerancia_saida'] = $hEsp['tolerancia_saida'];
            }
        } else if (is_array($hojeData)) {
            // Se for o formato de array (legado) ou objeto sem ID (manual)
            $p1 = $hojeData[0] ?? ($hojeData['p1'] ?? null);
            $p2 = $hojeData[1] ?? ($hojeData['p2'] ?? null);
            $p3 = $hojeData[2] ?? ($hojeData['p3'] ?? null);
            $p4 = $hojeData[3] ?? ($hojeData['p4'] ?? null);
            
            if ($p1 || $p2 || $p3 || $p4) {
               $funcionario['primeiro_horario'] = $p1;
               $funcionario['segundo_horario'] = $p2;
               $funcionario['terceiro_horario'] = $p3;
               $funcionario['quarto_horario'] = $p4;
            }
        }
    }

    // ---- Verificar se o funcionário está em período de afastamento hoje ----
    $sqlFerias = "SELECT data_fim FROM ferias WHERE id_funcionario = :id AND :hoje BETWEEN data_inicio AND data_fim LIMIT 1";
    $stmtFerias = $conn->prepare($sqlFerias);
    $stmtFerias->execute([':id' => $id_funcionario, ':hoje' => $dataAtual]);
    $feriasAtiva = $stmtFerias->fetch();

    if ($feriasAtiva) {
        $retorno = date('d/m/Y', strtotime($feriasAtiva['data_fim'] . ' +1 day'));
        echo json_encode(['success' => false, 'message' => "📅 Período em afastamento, batida não necessária. Seu retorno está previsto para: {$retorno}."]);
        exit;
    }

    // 2. Buscar o registro de hoje
    $stmt = $conn->prepare("SELECT * FROM registros WHERE id_funcionario = :id AND data = :data");
    $stmt->execute([':id' => $id_funcionario, ':data' => $dataAtual]);
    $registroHoje = $stmt->fetch();

    $campoPonto = '';
    $horaEsperada = '';

    $tol_entrada = isset($funcionario['tolerancia_entrada']) ? (int) $funcionario['tolerancia_entrada'] : 15;
    $tol_saida = isset($funcionario['tolerancia_saida']) ? (int) $funcionario['tolerancia_saida'] : 15;

    // Todos os 4 pontos com seus horários previstos e tipo (entrada/saída)
    $todosPontos = [
        ['campo' => 'primeiro_ponto', 'previsto' => $funcionario['primeiro_horario'], 'is_entrada' => true],
        ['campo' => 'segundo_ponto', 'previsto' => $funcionario['segundo_horario'], 'is_entrada' => false],
        ['campo' => 'terceiro_ponto', 'previsto' => $funcionario['terceiro_horario'], 'is_entrada' => true],
        ['campo' => 'quarto_ponto', 'previsto' => $funcionario['quarto_horario'], 'is_entrada' => false],
    ];

    $agora_ts = strtotime($horaAtual);
    $temHorarioVinculado = !empty($funcionario['primeiro_horario']) || !empty($funcionario['segundo_horario']) || !empty($funcionario['terceiro_horario']) || !empty($funcionario['quarto_horario']);

    // Se o ponto estiver liberado e o funcionário não tiver horário, criamos slots "fantasma" para permitir o registro
    if ($is_liberado && !$temHorarioVinculado) {
        $funcionario['primeiro_horario'] = '08:00';
        $funcionario['segundo_horario']  = '12:00';
        $funcionario['terceiro_horario'] = '13:00';
        $funcionario['quarto_horario']   = '17:00';
        $temHorarioVinculado = true;
    }

    // --- Lógica de Alinhamento Inteligente (Best Fit) ---
    $campoPonto = '';
    $horaEsperada = '';
    
    // 1. Identificar slots disponíveis (não preenchidos hoje) e seus horários
    $slotsDisponiveis = [];
    foreach ($todosPontos as $p) {
        if (!empty($p['previsto'])) {
            // Se já tem registro hoje, verificar se este campo está vazio
            if ($registroHoje && !empty($registroHoje[$p['campo']])) {
                continue;
            }
            $slotsDisponiveis[] = $p;
        }
    }

    if (empty($slotsDisponiveis)) {
        if ($temHorarioVinculado) {
            echo json_encode(['success' => false, 'message' => 'Todos os pontos de hoje já foram registrados.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Nenhum horário de expediente vinculado ao seu perfil. Procure o RH.']);
        }
        exit;
    }

    // 2. Encontrar o slot que melhor se ajusta ao horário atual
    // Calculamos os pontos médios entre os horários previstos para definir as fronteiras zonais
    $horariosPrevistos = [];
    foreach ($todosPontos as $p) {
        if (!empty($p['previsto'])) {
            $horariosPrevistos[] = [
                'campo' => $p['campo'],
                'ts' => strtotime($p['previsto']),
                'previsto' => $p['previsto'],
                'is_entrada' => $p['is_entrada']
            ];
        }
    }

    $numHorarios = count($horariosPrevistos);
    $alvoIndex = -1;

    for ($i = 0; $i < $numHorarios; $i++) {
        // Definir limite inferior e superior para esta zona usando as tolerâncias com limite contínuo
        if ($i === 0) {
            $limiteInferior = 0;
        } else {
            $curr = $horariosPrevistos[$i];
            $curr_tol = $curr['is_entrada'] ? $tol_entrada : $tol_saida;
            $limiteInferior = $curr['ts'] - ($curr_tol * 60);
        }

        if ($i === $numHorarios - 1) {
            $limiteSuperior = 9999999999;
        } else {
            $next = $horariosPrevistos[$i+1];
            $next_tol = $next['is_entrada'] ? $tol_entrada : $tol_saida;
            $limiteSuperior = $next['ts'] - ($next_tol * 60);
        }

        if ($agora_ts >= $limiteInferior && $agora_ts < $limiteSuperior) {
            $alvoIndex = $i;
            break;
        }
    }

    if ($alvoIndex !== -1) {
        // --- NOVO BLOQUEIO DE DUPLICIDADE (Baseado na Zona / Best Fit) ---
        $campoAlvo = $horariosPrevistos[$alvoIndex]['campo'];
        if ($registroHoje && !empty($registroHoje[$campoAlvo]) && $registroHoje[$campoAlvo] !== 'FALTA') {
            echo json_encode(['success' => false, 'message' => 'seu ponto já foi registrado aguarde a tolerancia para o proximo registro']);
            exit;
        }

        // Se a zona alvo já estiver preenchida, tentamos o próximo slot disponível
        for ($i = $alvoIndex; $i < $numHorarios; $i++) {
            $campo = $horariosPrevistos[$i]['campo'];
            if (!$registroHoje || empty($registroHoje[$campo])) {
                $campoPonto = $campo;
                $horaEsperada = $horariosPrevistos[$i]['previsto'];
                break;
            }
        }
    }

    // Se ainda não encontrou (ex: zona alvo preenchida e sem próximos disponíveis), 
    // ou se caiu fora do loop, pega o primeiro vazio de trás pra frente
    if (empty($campoPonto)) {
        for ($i = $numHorarios - 1; $i >= 0; $i--) {
            $campo = $horariosPrevistos[$i]['campo'];
            if (!$registroHoje || empty($registroHoje[$campo])) {
                $campoPonto = $campo;
                $horaEsperada = $horariosPrevistos[$i]['previsto'];
                break;
            }
        }
    }

    // 3. Marcar como FALTA os slots anteriores ao alvo que ainda estão vazios
    foreach ($horariosPrevistos as $idx => $h) {
        $idxAlvo = -1;
        foreach($horariosPrevistos as $k => $tmp) if($tmp['campo'] === $campoPonto) $idxAlvo = $k;
        
        if ($idx < $idxAlvo) {
            $campoAnterior = $h['campo'];
            if (!$registroHoje || empty($registroHoje[$campoAnterior])) {
                $colunaAtraso = 'atrasou_' . $campoAnterior;
                if (!$registroHoje) {
                    $conn->prepare("INSERT INTO registros (id_funcionario, data, $campoAnterior, $colunaAtraso, created_at, updated_at)
                                    VALUES (:id, :data, 'FALTA', 'true', NOW(), NOW())")
                        ->execute([':id' => $id_funcionario, ':data' => $dataAtual]);
                    $stmt2 = $conn->prepare("SELECT * FROM registros WHERE id_funcionario = :id AND data = :data");
                    $stmt2->execute([':id' => $id_funcionario, ':data' => $dataAtual]);
                    $registroHoje = $stmt2->fetch();
                } else {
                    $conn->prepare("UPDATE registros SET $campoAnterior = 'FALTA', $colunaAtraso = 'true', updated_at = NOW()
                                    WHERE id = :id_reg")
                        ->execute([':id_reg' => $registroHoje['id']]);
                    $registroHoje[$campoAnterior] = 'FALTA';
                }
            }
        }
    }

    if (empty($campoPonto)) {
        if (!$temHorarioVinculado) {
            echo json_encode(['success' => false, 'message' => 'Nenhum horário de expediente vinculado ao seu perfil. Procure o RH.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Todos os pontos de hoje já foram registrados.']);
        }
        exit;
    }

    // 4. Validar janelas e calcular atraso
    $is_atraso = false;
    if ($horaEsperada) {
        $esperado_ts = strtotime($horaEsperada);
        $ehEntrada = in_array($campoPonto, ['primeiro_ponto', 'terceiro_ponto']);
        $tol_min = $ehEntrada ? $tol_entrada : $tol_saida;
        
        // Se a tolerância for 0, o ponto pode ser batido SEM RESTRIÇÕES de horário
        if ($tol_min > 0) {
            // Limites de tolerância
            $limite_inferior = $ehEntrada ? ($esperado_ts - ($tol_min * 60)) : $esperado_ts;
            $limite_superior = $esperado_ts + ($tol_min * 60);

            // Bloqueio de batida antecipada
            if ($agora_ts < $limite_inferior && !$is_liberado) {
                $msg = $ehEntrada ? "Muito cedo para registrar este ponto. Aguarde até " . date('H:i', $limite_inferior) . "." : 
                                   "Registro de saída antecipado não é permitido. O horário de saída é " . $horaEsperada . ".";
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }

            // is_atraso: se estiver batendo depois do horário previsto + tolerância
            if ($agora_ts > $limite_superior && $ehEntrada && !$is_liberado) {
                $is_atraso = true;
            }
        }
    }

    if ($is_liberado) $is_atraso = false;

    if (!$registroHoje) {
        // Primeira batida do dia: cria o registro no slot identificado
        $colunaAtraso = 'atrasou_' . $campoPonto;
        $sql = "INSERT INTO registros (id_funcionario, data, {$campoPonto}, {$colunaAtraso}, created_at, updated_at)
                VALUES (:id, :data, :hora, :atraso, NOW(), NOW())";
        $conn->prepare($sql)->execute([
            ':id' => $id_funcionario,
            ':data' => $dataAtual,
            ':hora' => $horaAtual,
            ':atraso' => $is_atraso ? 'true' : 'false'
        ]);
    } else {
        // Registro já existe, apenas atualiza o campo do slot correspondente
        $colunaAtraso = 'atrasou_' . $campoPonto;
        $sql = "UPDATE registros SET {$campoPonto} = :hora, {$colunaAtraso} = :atraso, updated_at = NOW()
                WHERE id = :id_registro";
        $conn->prepare($sql)->execute([
            ':hora' => $horaAtual,
            ':atraso' => $is_atraso ? 'true' : 'false',
            ':id_registro' => $registroHoje['id']
        ]);
    }

    echo json_encode([
        'success' => true,
        'funcionario' => $funcionario['nome'],
        'hora' => $horaAtual,
        'ponto_marcado' => $campoPonto,
        'is_atraso' => $is_atraso
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
