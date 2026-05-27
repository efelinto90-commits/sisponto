<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

require_once '../config/Database.php';
use Config\Database;

/**
 * Verifica se um anexo existe na pasta 'uploads' ou na pasta 'upload',
 * corrigindo dinamicamente o caminho do link.
 */
function verificarCaminhoAnexo($dbPath) {
    // Diretório base do projeto (um nível acima de api/)
    $baseDir = dirname(__DIR__) . '/';
    
    // Limpar o caminho básico
    $cleanedPath = preg_replace('/^\.\.\/sisponto\//', '', $dbPath);
    $cleanedPath = preg_replace('/^\.\.\\\\sisponto\\\\/', '', $cleanedPath);
    $cleanedPath = ltrim($cleanedPath, './\\');
    
    // 1. Se o arquivo existe no caminho limpo original, retorna ele
    if (file_exists($baseDir . $cleanedPath) && is_file($baseDir . $cleanedPath)) {
        return $cleanedPath;
    }
    
    // 2. Se não existir, extrai o nome do arquivo e busca em outras pastas conhecidas
    $filename = basename($cleanedPath);
    
    $pastasPossiveis = [
        'uploads/justificativas/',
        'upload/justificativas/',
        'upload/justificativa/',
        'uploads/comunicados/',
        'uploads/afastamentos/',
    ];
    
    foreach ($pastasPossiveis as $pasta) {
        if (file_exists($baseDir . $pasta . $filename) && is_file($baseDir . $pasta . $filename)) {
            return $pasta . $filename;
        }
    }
    
    // 3. Fallback se não for encontrado em nenhuma pasta
    return $cleanedPath;
}

try {
    $conn = Database::getConnection();
    $dataAtual = date('Y-m-d');
    
    // Identificação de permissões (Lógica similar ao api/funcionarios.php)
    $user_name = $_SESSION['user_name'] ?? '';
    $user_setor = $_SESSION['user_setor'] ?? '';
    $user_level = $_SESSION['user_level'] ?? 3;
    $is_super = ($user_level == 1) || in_array(strtolower(trim($user_name)), ['corsin', 'crh']) || in_array(strtolower(trim($user_setor)), ['corsin', 'crh']);

    $sectorFilter = "";
    $params = [];
    if (!$is_super) {
        if (!empty($user_setor)) {
            $sectorFilter = " AND (f.setor = :setor OR f.setor2 = :setor)";
            $params[':setor'] = $user_setor;
        } else {
            $sectorFilter = " AND (f.setor IS NULL OR TRIM(f.setor) = '')";
        }
    }

    // Stats
    $sqlFunc = "SELECT COUNT(*) FROM funcionarios f WHERE (f.is_exonerado IS FALSE OR f.is_exonerado IS NULL) " . $sectorFilter;
    $stmtFunc = $conn->prepare($sqlFunc);
    $stmtFunc->execute($params);
    $totalFuncionarios = $stmtFunc->fetchColumn();

    $sqlRegHoje = "SELECT COUNT(*) FROM registros r JOIN funcionarios f ON r.id_funcionario = f.id WHERE r.data = :data " . $sectorFilter;
    $paramsHoje = array_merge([':data' => $dataAtual], $params);
    $stmtRegHoje = $conn->prepare($sqlRegHoje);
    $stmtRegHoje->execute($paramsHoje);
    $totalRegistrosHoje = $stmtRegHoje->fetchColumn();

    // Atrasos hoje (conta registros que tiveram pelo menos 1 atraso)
    $sqlAtrasos = "
        SELECT COUNT(*) FROM registros r
        JOIN funcionarios f ON r.id_funcionario = f.id
        WHERE r.data = :data 
        AND (r.atrasou_primeiro_ponto = true OR r.atrasou_segundo_ponto = true OR r.atrasou_terceiro_ponto = true OR r.atrasou_quarto_ponto = true)
        " . $sectorFilter . "
    ";
    $stmtAtrasos = $conn->prepare($sqlAtrasos);
    $stmtAtrasos->execute($paramsHoje);
    $totalAtrasosHoje = $stmtAtrasos->fetchColumn();

    // Funcionários afastados vigentes (dentro do período de afastamento e status deferido)
    $sqlAfastados = "
        SELECT f.nome, f.matricula, af.tipo_afastamento, af.motivo_especifico, af.data_inicio, af.data_fim
        FROM ferias af
        JOIN funcionarios f ON af.id_funcionario = f.id
        WHERE :data BETWEEN af.data_inicio AND af.data_fim
        AND af.status = 'deferido'
        " . $sectorFilter . "
        ORDER BY af.data_inicio DESC, f.nome ASC
    ";
    $stmtAfastados = $conn->prepare($sqlAfastados);
    $stmtAfastados->execute($paramsHoje);
    $afastadosHoje = $stmtAfastados->fetchAll();
    $totalAfastadosHoje = count($afastadosHoje);

    // Últimos registros de hoje
    $sqlUltimos = "
        SELECT f.nome, f.matricula, r.primeiro_ponto, r.segundo_ponto, r.terceiro_ponto, r.quarto_ponto,
               r.atrasou_primeiro_ponto, r.atrasou_segundo_ponto, r.atrasou_terceiro_ponto, r.atrasou_quarto_ponto
        FROM registros r
        JOIN funcionarios f ON r.id_funcionario = f.id
        WHERE r.data = :data
        " . $sectorFilter . "
        ORDER BY r.updated_at DESC
        LIMIT 10
    ";
    $stmtUltimos = $conn->prepare($sqlUltimos);
    $stmtUltimos->execute($paramsHoje);
    $ultimosRegistros = $stmtUltimos->fetchAll();

    // Justificativas com anexo pendentes
    $sqlJustificativas = "
        SELECT r.id, r.data, f.nome, f.matricula, r.justificativa, r.tipo_justificativa, r.anexo_justificativa, r.comunicado, r.anexo_comunicado, r.status_crh
        FROM registros r
        JOIN funcionarios f ON r.id_funcionario = f.id
        WHERE (
            (r.anexo_justificativa IS NOT NULL AND r.anexo_justificativa <> '' AND r.anexo_justificativa <> '[]' AND r.anexo_justificativa <> 'null')
            OR 
            (r.anexo_comunicado IS NOT NULL AND r.anexo_comunicado <> '' AND r.anexo_comunicado <> '[]' AND r.anexo_comunicado <> 'null')
        )
        AND (r.status_crh = 'pendente' OR r.status_crh IS NULL)
        AND r.data = :data
        " . $sectorFilter . "
    ";
    
    $stmtJust1 = $conn->prepare($sqlJustificativas);
    $stmtJust1->execute($paramsHoje);
    $regsJust = $stmtJust1->fetchAll(PDO::FETCH_ASSOC);

    $sqlJustificativasTab = "
        SELECT j.id, j.data_registro as data, f.nome, f.matricula, j.texto as justificativa, 'Funcionário' as tipo_justificativa, j.anexos as anexo_justificativa, j.status as status_crh
        FROM justificativas j
        JOIN funcionarios f ON j.id_funcionario = f.id
        WHERE j.anexos IS NOT NULL AND j.anexos <> '' AND j.anexos <> '[]' AND j.anexos <> 'null'
        AND (j.status = 'pendente' OR j.status IS NULL)
        AND j.data_registro = :data
        " . $sectorFilter . "
    ";
    $stmtJust2 = $conn->prepare($sqlJustificativasTab);
    $stmtJust2->execute($paramsHoje);
    $tabJust = $stmtJust2->fetchAll(PDO::FETCH_ASSOC);

    // Mesclar e deduplicar em PHP
    $justificativasMescladas = [];
    foreach ($regsJust as $r) {
        $key = $r['data'] . '_' . $r['matricula'];
        
        $anexos = [];
        if (!empty($r['anexo_justificativa']) && $r['anexo_justificativa'] !== 'null' && $r['anexo_justificativa'] !== '[]') {
            $parsed = json_decode($r['anexo_justificativa'], true);
            if (is_array($parsed)) $anexos = array_merge($anexos, $parsed);
            else $anexos[] = $r['anexo_justificativa'];
        }
        if (!empty($r['anexo_comunicado']) && $r['anexo_comunicado'] !== 'null' && $r['anexo_comunicado'] !== '[]') {
            $parsed = json_decode($r['anexo_comunicado'], true);
            if (is_array($parsed)) $anexos = array_merge($anexos, $parsed);
            else $anexos[] = $r['anexo_comunicado'];
        }
        
        // Limpar e verificar caminhos dos anexos
        foreach ($anexos as &$path) {
            $path = verificarCaminhoAnexo($path);
        }
        unset($path);
        
        $anexos = array_values(array_unique(array_filter($anexos)));
        
        if (empty($anexos)) continue;
        
        $justificativasMescladas[$key] = [
            'id' => $r['id'],
            'data' => $r['data'],
            'nome' => $r['nome'],
            'matricula' => $r['matricula'],
            'justificativa' => $r['justificativa'] ?: $r['comunicado'] ?: 'Sem descrição',
            'tipo_justificativa' => $r['tipo_justificativa'] ?: 'Justificativa',
            'anexos' => $anexos,
            'status_crh' => $r['status_crh'] ?: 'pendente'
        ];
    }
    
    foreach ($tabJust as $j) {
        $key = $j['data'] . '_' . $j['matricula'];
        
        $anexos = [];
        if (!empty($j['anexo_justificativa']) && $j['anexo_justificativa'] !== 'null' && $j['anexo_justificativa'] !== '[]') {
            $parsed = json_decode($j['anexo_justificativa'], true);
            if (is_array($parsed)) $anexos = array_merge($anexos, $parsed);
            else $anexos[] = $j['anexo_justificativa'];
        }
        
        // Limpar e verificar caminhos dos anexos
        foreach ($anexos as &$path) {
            $path = verificarCaminhoAnexo($path);
        }
        unset($path);
        
        $anexos = array_values(array_unique(array_filter($anexos)));
        
        if (empty($anexos)) continue;
        
        if (isset($justificativasMescladas[$key])) {
            $justificativasMescladas[$key]['anexos'] = array_values(array_unique(array_merge($justificativasMescladas[$key]['anexos'], $anexos)));
        } else {
            $justificativasMescladas[$key] = [
                'id' => 'j_' . $j['id'],
                'data' => $j['data'],
                'nome' => $j['nome'],
                'matricula' => $j['matricula'],
                'justificativa' => $j['justificativa'] ?: 'Sem descrição',
                'tipo_justificativa' => $j['tipo_justificativa'] ?: 'Enviado pelo Colaborador',
                'anexos' => $anexos,
                'status_crh' => $j['status_crh'] ?: 'pendente'
            ];
        }
    }
    
    // Ordenar por data DESC
    usort($justificativasMescladas, function($a, $b) {
        return strcmp($b['data'], $a['data']);
    });
    
    $totalJustificativasAnexo = count($justificativasMescladas);

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_funcionarios' => $totalFuncionarios,
            'total_registros_hoje' => $totalRegistrosHoje,
            'total_atrasos_hoje' => $totalAtrasosHoje,
            'total_afastados_hoje' => $totalAfastadosHoje,
            'total_justificativas_anexo' => $totalJustificativasAnexo
        ],
        'ultimos_registros' => $ultimosRegistros,
        'afastados_hoje' => $afastadosHoje,
        'justificativas_anexo' => array_values($justificativasMescladas)
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
