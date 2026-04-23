<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once '../config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $method = $_SERVER['REQUEST_METHOD'];

    // ---- LIST: retorna afastamentos/férias ----
    if ($method === 'GET') {
        $funcId = $_GET['func_id'] ?? null;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $sql = "SELECT f.*, func.nome as nome_funcionario, func.matricula 
                FROM ferias f 
                JOIN funcionarios func ON f.id_funcionario = func.id 
                WHERE 1=1";
        $params = [];

        if ($funcId) {
            $sql .= " AND f.id_funcionario = :funcId";
            $params[':funcId'] = $funcId;
        }

        if ($startDate) {
            $sql .= " AND f.data_fim >= :start";
            $params[':start'] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND f.data_inicio <= :end";
            $params[':end'] = $endDate;
        }

        $sql .= " ORDER BY f.data_inicio DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input && !empty($_POST)) {
        $input = $_POST;
    }
    $action = $input['action'] ?? '';

    // ---- CREATE / UPDATE ----
    if ($action === 'create' || $action === 'update') {
        $anexoPath = $input['anexo_atual'] ?? null;

        // Processamento de Upload
        if (isset($_FILES['anexo']) && $_FILES['anexo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/afastamentos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($_FILES['anexo']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('afast_') . ($ext ? '.' . strtolower($ext) : '');
            $destination = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['anexo']['tmp_name'], $destination)) {
                $anexoPath = 'uploads/afastamentos/' . $filename;
            }
        }

        if ($action === 'create') {
            $stmt = $conn->prepare("INSERT INTO ponto.ferias (id_funcionario, data_inicio, data_fim, observacao, tipo_afastamento, motivo_especifico, anexo, created_at, updated_at)
                                    VALUES (:func, :ini, :fim, :obs, :tipo, :espec, :anexo, NOW(), NOW())");
            $stmt->execute([
                ':func' => $input['id_funcionario'],
                ':ini' => $input['data_inicio'],
                ':fim' => $input['data_fim'],
                ':obs' => $input['observacao'] ?? null,
                ':tipo' => $input['tipo_afastamento'] ?? null,
                ':espec' => $input['motivo_especifico'] ?? null,
                ':anexo' => $anexoPath
            ]);
            echo json_encode(['success' => true, 'message' => 'Afastamento cadastrado com sucesso!']);
        } else {
            $stmt = $conn->prepare("UPDATE ponto.ferias SET data_inicio=:ini, data_fim=:fim, observacao=:obs, tipo_afastamento=:tipo, motivo_especifico=:espec, anexo=:anexo, updated_at=NOW() WHERE id=:id");
            $stmt->execute([
                ':ini' => $input['data_inicio'], 
                ':fim' => $input['data_fim'], 
                ':obs' => $input['observacao'] ?? null, 
                ':tipo' => $input['tipo_afastamento'] ?? null,
                ':espec' => $input['motivo_especifico'] ?? null,
                ':anexo' => $anexoPath,
                ':id' => $input['id']
            ]);
            echo json_encode(['success' => true, 'message' => 'Afastamento atualizado com sucesso!']);
        }
        exit;
    }

    // ---- DELETE ----
    if ($action === 'delete') {
        $conn->prepare("DELETE FROM ponto.ferias WHERE id = :id")->execute([':id' => $input['id']]);
        echo json_encode(['success' => true, 'message' => 'Registro de afastamento removido.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Ação inválida.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
