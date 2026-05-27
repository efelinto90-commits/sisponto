<?php
session_start();
header('Content-Type: application/json');
session_write_close();

require_once '../config/Database.php';
use Config\Database;

$method = $_SERVER['REQUEST_METHOD'];

try {
    $conn = Database::getConnection();

    switch ($method) {
        case 'GET':
            $stmt = $conn->query("SELECT * FROM pleitos_presets ORDER BY nome ASC");
            $presets = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $presets]);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['nome'])) {
                echo json_encode(['success' => false, 'message' => 'O nome do pleito é obrigatório.']);
                exit;
            }
            
            $sql = "INSERT INTO pleitos_presets (nome) VALUES (:nome)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':nome' => trim($input['nome'])]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Pleito cadastrado com sucesso!', 
                'id' => $conn->lastInsertId()
            ]);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['id']) || empty($input['nome'])) {
                echo json_encode(['success' => false, 'message' => 'ID e Nome são obrigatórios.']);
                exit;
            }
            
            $sql = "UPDATE pleitos_presets SET nome = :nome WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':nome' => trim($input['nome']), 
                ':id' => $input['id']
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Pleito atualizado com sucesso!']);
            break;

        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID é obrigatório.']);
                exit;
            }
            
            $stmt = $conn->prepare("DELETE FROM pleitos_presets WHERE id = :id");
            $stmt->execute([':id' => $input['id']]);
            
            echo json_encode(['success' => true, 'message' => 'Pleito removido com sucesso!']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Método não suportado.']);
            break;
    }

} catch (PDOException $e) {
    if ($e->getCode() == '23505') {
        echo json_encode(['success' => false, 'message' => 'Este pleito já existe.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro de banco de dados: ' . $e->getMessage()]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}
?>
