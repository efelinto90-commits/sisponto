<?php
header('Content-Type: application/json');

date_default_timezone_set('America/Sao_Paulo');

require_once '../config/Database.php';

use Config\Database;

$method = $_SERVER['REQUEST_METHOD'];

try {
    $conn = Database::getConnection();

    // Verificação de Autorização para Escrita (POST, PUT, DELETE)
    if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $user_level = $_SESSION['user_level'] ?? '3';
        $user_setor = strtolower(trim($_SESSION['user_setor'] ?? ''));
        $user_name  = strtolower(trim($_SESSION['user_name'] ?? ''));

        $is_authorized = ($user_level == '1' || $user_setor == 'crh' || $user_setor == 'corsin' || $user_name == 'crh' || $user_name == 'corsin');

        if (!$is_authorized) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado. Você não tem permissão para realizar esta ação.']);
            exit;
        }
    }

    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $stmt = $conn->prepare("SELECT * FROM horarios WHERE id = :id");
                $stmt->execute([':id' => $_GET['id']]);
                echo json_encode(['success' => true, 'data' => $stmt->fetch()]);
            } else {
                $stmt = $conn->query("SELECT * FROM horarios ORDER BY id ASC");
                echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $sql = "INSERT INTO horarios (nome, primeiro_horario, segundo_horario, terceiro_horario, quarto_horario, tolerancia_entrada, tolerancia_saida, created_at, updated_at) 
                    VALUES (:nome, :p1, :p2, :p3, :p4, :te, :ts, NOW(), NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':nome' => !empty($input['nome']) ? trim($input['nome']) : null,
                ':p1' => !empty($input['p1']) ? $input['p1'] : null,
                ':p2' => !empty($input['p2']) ? $input['p2'] : null,
                ':p3' => !empty($input['p3']) ? $input['p3'] : null,
                ':p4' => !empty($input['p4']) ? $input['p4'] : null,
                ':te' => isset($input['te']) ? (int) $input['te'] : 0,
                ':ts' => isset($input['ts']) ? (int) $input['ts'] : 0
            ]);
            echo json_encode(['success' => true, 'message' => 'Horário cadastrado!']);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $sql = "UPDATE horarios SET 
                    nome = :nome,
                    primeiro_horario = :p1, segundo_horario = :p2, terceiro_horario = :p3, quarto_horario = :p4, 
                    tolerancia_entrada = :te, tolerancia_saida = :ts, updated_at = NOW() 
                    WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':nome' => !empty($input['nome']) ? trim($input['nome']) : null,
                ':p1' => !empty($input['p1']) ? $input['p1'] : null,
                ':p2' => !empty($input['p2']) ? $input['p2'] : null,
                ':p3' => !empty($input['p3']) ? $input['p3'] : null,
                ':p4' => !empty($input['p4']) ? $input['p4'] : null,
                ':te' => isset($input['te']) ? (int) $input['te'] : 0,
                ':ts' => isset($input['ts']) ? (int) $input['ts'] : 0,
                ':id' => $input['id']
            ]);
            echo json_encode(['success' => true, 'message' => 'Horário atualizado!']);
            break;

        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            $stmt = $conn->prepare("DELETE FROM horarios WHERE id = :id");
            $stmt->execute([':id' => $input['id']]);
            echo json_encode(['success' => true, 'message' => 'Horário removido!']);
            break;
    }

} catch (PDOException $e) {
    if ($e->getCode() == '23503') {
        echo json_encode(['success' => false, 'message' => 'Não é possível excluir este horário, pois existem funcionários vinculados a ele. Altere o horário destes funcionários primeiro.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro interno de banco: ' . $e->getMessage()]);
    }
}
