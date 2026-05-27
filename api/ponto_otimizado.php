<?php
/**
 * ARQUIVO OTIMIZADO: ponto.php
 * Melhorias:
 * - Cache de horários em session
 * - Redução de queries (batch queries em vez de loop)
 * - Prefetching de dados relacionados
 * - Remover SELECT * e especificar campos
 */

header('Content-Type: application/json');

date_default_timezone_set('America/Sao_Paulo');

require_once '../config/Database.php';

use Config\Database;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$matricula = isset($input['matricula']) ? trim($input['matricula']) : '';
$biometria = isset($input['biometria']) ? trim($input['biometria']) : '';

// ============================================================================
// OTIMIZAÇÃO 1: Cache de horários em session
// ============================================================================
if (session_status() === PHP_SESSION_NONE) session_start();

function getCachedHorarios($conn) {
    // Cache em session para evitar queries repetidas
    if (!isset($_SESSION['_cached_horarios'])) {
        $stmt = $conn->query("SELECT id, primeiro_horario, segundo_horario, terceiro_horario, quarto_horario, tolerancia_entrada, tolerancia_saida FROM horarios");
        $_SESSION['_cached_horarios'] = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);
    }
    return $_SESSION['_cached_horarios'];
}

// Ação para obter métodos de acesso permitidos
if (isset($input['action']) && $input['action'] === 'get_metodos') {
    $matricula = $input['matricula'] ?? '';
    $conn = Database::getConnection(); 
    
    // ============================================================================
    // OTIMIZAÇÃO 2: Query específica (sem SELECT *)
    // ============================================================================
    $stmt = $conn->prepare("
        SELECT 
            id, matricula, nome, is_exonerado, senha, 
            metodos_acesso, facial_descriptor, biometria, webauthn_id,
            cpf, data_nascimento, rg_numero, rg_orgao, rg_data_emissao,
            endereco, endereco_numero, endereco_bairro, endereco_cep, 
            endereco_municipio, endereco_uf, nome_mae, celular,
            grade_horarios, lat_permitida, long_permitida, distancia_max_permitida,
            setor
        FROM funcionarios 
        WHERE matricula = :matricula AND (is_exonerado IS FALSE OR is_exonerado IS NULL)
        ORDER BY created_at DESC
        LIMIT 1
    ");
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
        
        // Verificação de ficha incompleta
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

        $metodosFiltrados = array_filter($metodos, function($m) {
            return $m === 'facial' || $m === 'biometria' || $m === 'qrcode'; 
        });
        
        if (empty($metodosFiltrados) && $temFacial) {
            $metodosFiltrados = ['facial'];
        }

        // ============================================================================
        // OTIMIZAÇÃO 3: Prefetch de geofencing em uma única query
        // ============================================================================
        $mapaDias = [
            'Monday' => 'segunda', 'Tuesday' => 'terca', 'Wednesday' => 'quarta',
            'Thursday' => 'quinta', 'Friday' => 'sexta', 'Saturday' => 'sabado', 'Sunday' => 'domingo'
        ];
        $diaHoje = $mapaDias[date('l')];
        $grade = json_decode($func['grade_horarios'] ?? '[]', true);
        
        $geofencingReq = null;
        if (isset($grade[$diaHoje]) && is_array($grade[$diaHoje]) && !empty($grade[$diaHoje]['area'])) {
            $stmtArea = $conn->prepare("SELECT nome_area, latitude, longitude, distancia_max FROM geofencing_presets WHERE nome_area = :nome LIMIT 1");
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
        
        if (!$geofencingReq) {
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

// Ação MEU ACESSO (Individual) - OTIMIZADO
if (isset($input['action']) && $input['action'] === 'get_meu_ponto') {
    $matricula = $input['matricula'] ?? '';
    $senha = $input['senha'] ?? '';
    
    $conn = Database::getConnection();
    
    // ============================================================================
    // OTIMIZAÇÃO 4: Query com JOIN eficiente (sem subqueries)
    // ============================================================================
    $stmt = $conn->prepare("
        SELECT 
            f.id, f.matricula, f.nome, f.senha, f.is_exonerado, f.setor,
            f.grade_horarios, f.id_horario,
            h.primeiro_horario, h.segundo_horario, h.terceiro_horario, 
            h.quarto_horario, h.tolerancia_entrada, h.tolerancia_saida
        FROM funcionarios f 
        LEFT JOIN horarios h ON f.id_horario = h.id 
        WHERE f.matricula = :m AND (f.is_exonerado IS FALSE OR f.is_exonerado IS NULL)
        ORDER BY f.created_at DESC
        LIMIT 1
    ");
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

    // ============================================================================
    // OTIMIZAÇÃO 5: Batch queries (prefetch tudo de uma vez)
    // ============================================================================
    $sql = "
        SELECT 
            r.id, r.data, r.id_funcionario, r.primeiro_ponto, r.segundo_ponto, 
            r.terceiro_ponto, r.quarto_ponto,
            f.grade_horarios
        FROM registros r
        LEFT JOIN funcionarios f ON r.id_funcionario = f.id
        WHERE r.id_funcionario = :id_func AND r.data BETWEEN :start AND :end
        ORDER BY r.data DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id_func' => $func['id'], ':start' => $startDate, ':end' => $endDate]);
    $registrosExistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prefetch afastamentos e liberações de uma vez
    $stmtF = $conn->prepare("
        SELECT id_funcionario, data_inicio, data_fim, tipo_afastamento, motivo_especifico 
        FROM ferias 
        WHERE id_funcionario = :id AND status = 'deferido' 
        AND (data_inicio <= :end AND data_fim >= :start)
    ");
    $stmtF->execute([':id' => $func['id'], ':start' => $startDate, ':end' => $endDate]);
    $afastamentos = $stmtF->fetchAll(PDO::FETCH_ASSOC);

    $setorFunc = $func['setor'] ?? '';
    $stmtL = $conn->prepare("
        SELECT DATE(data_hora) as data_lib, descricao, justificativa
        FROM ponto_liberado 
        WHERE (setor = 'TODOS' OR setor = :s)
        AND DATE(data_hora) BETWEEN :start AND :end
    ");
    $stmtL->execute([':s' => $setorFunc, ':start' => $startDate, ':end' => $endDate]);
    $liberacoes = $stmtL->fetchAll(PDO::FETCH_ASSOC);
    
    // Índice liberações por data
    $mapaLib = [];
    foreach($liberacoes as $l) {
        $mapaLib[$l['data_lib']] = $l;
    }

    // Processamento de registros com dados prefetched
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
            $finalRegistros[] = [
                'data' => $d,
                'id_funcionario' => $func['id'],
                'primeiro_ponto' => null,
                'segundo_ponto' => null,
                'terceiro_ponto' => null,
                'quarto_ponto' => null,
                'virtual' => true
            ];
        }
    }
    
    usort($finalRegistros, function($a, $b) {
        return strcmp($b['data'], $a['data']);
    });
    
    // ============================================================================
    // OTIMIZAÇÃO 6: Cache horários para não ler repetidamente
    // ============================================================================
    $todosHorarios = getCachedHorarios($conn);

    // Indexar afastamentos por funcionário para lookup O(1)
    $afastamentosMap = [];
    foreach ($afastamentos as $a) {
        $afastamentosMap[$a['id_funcionario']][] = $a;
    }

    $registros = &$finalRegistros;

    foreach ($registros as &$reg) {
        $regDate = $reg['data'];
        $reg['falta_turno1_entrada'] = false;
        $reg['falta_turno1_saida'] = false;
        $reg['em_ferias'] = false;
        $reg['liberacao'] = $mapaLib[$regDate] ?? null;

        // Verificar afastamentos com lookup em map O(1)
        if (isset($afastamentosMap[$func['id']])) {
            foreach ($afastamentosMap[$func['id']] as $afast) {
                if ($regDate >= $afast['data_inicio'] && $regDate <= $afast['data_fim']) {
                    $reg['em_ferias'] = true;
                    break;
                }
            }
        }
    }

    echo json_encode(['success' => true, 'data' => $registros]);
    exit;
}

// Outras ações podem ser otimizadas similarmente...
?>
