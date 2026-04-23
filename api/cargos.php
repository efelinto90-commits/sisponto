<?php
header('Content-Type: application/json');
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
                $stmt = $conn->prepare("SELECT * FROM cargos WHERE id = :id");
                $stmt->execute([':id' => $_GET['id']]);
                echo json_encode(['success' => true, 'data' => $stmt->fetch()]);
            } else {
                $stmt = $conn->query("SELECT * FROM cargos ORDER BY cargo ASC");
                echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $sql = "INSERT INTO cargos (cargo, created_at, updated_at) VALUES (:cargo, NOW(), NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':cargo' => $input['cargo']]);
            echo json_encode(['success' => true, 'message' => 'Cargo cadastrado!']);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $sql = "UPDATE cargos SET cargo = :cargo, updated_at = NOW() WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':cargo' => $input['cargo'], ':id' => $input['id']]);
            echo json_encode(['success' => true, 'message' => 'Cargo atualizado!']);
            break;

        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            $stmt = $conn->prepare("DELETE FROM cargos WHERE id = :id");
            $stmt->execute([':id' => $input['id']]);
            echo json_encode(['success' => true, 'message' => 'Cargo removido!']);
            break;
    }

} catch (PDOException $e) {
    if ($e->getCode() == '23503') {
        echo json_encode(['success' => false, 'message' => 'O cargo está em uso.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro interno de banco: ' . $e->getMessage()]);
    }
}
