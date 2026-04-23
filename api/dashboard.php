<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

require_once '../config/Database.php';
use Config\Database;

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
        " . $sectorFilter;
    $stmtAtrasos = $conn->prepare($sqlAtrasos);
    $stmtAtrasos->execute($paramsHoje);
    $totalAtrasosHoje = $stmtAtrasos->fetchColumn();

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

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_funcionarios' => $totalFuncionarios,
            'total_registros_hoje' => $totalRegistrosHoje,
            'total_atrasos_hoje' => $totalAtrasosHoje
        ],
        'ultimos_registros' => $ultimosRegistros
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
