<?php
header('Content-Type: application/json');
require_once '../config/Database.php';

use Config\Database;

$method = $_SERVER['REQUEST_METHOD'];

try {
    $conn = Database::getConnection();

    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $stmt = $conn->prepare("SELECT * FROM tipos_contratacao WHERE id = :id");
                $stmt->execute([':id' => $_GET['id']]);
                echo json_encode(['success' => true, 'data' => $stmt->fetch()]);
            } else {
                $stmt = $conn->query("SELECT * FROM tipos_contratacao ORDER BY nome ASC");
                echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['nome'])) {
                echo json_encode(['success' => false, 'message' => 'Nome é obrigatório']);
                exit;
            }
            $sql = "INSERT INTO tipos_contratacao (nome) VALUES (:nome)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':nome' => $input['nome']]);
            echo json_encode(['success' => true, 'message' => 'Tipo de contratação cadastrado!', 'id' => $conn->lastInsertId()]);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['id']) || empty($input['nome'])) {
                echo json_encode(['success' => false, 'message' => 'ID e Nome são obrigatórios']);
                exit;
            }
            $sql = "UPDATE tipos_contratacao SET nome = :nome WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':nome' => $input['nome'], ':id' => $input['id']]);
            echo json_encode(['success' => true, 'message' => 'Tipo de contratação atualizado!']);
            break;

        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID é obrigatório']);
                exit;
            }
            $stmt = $conn->prepare("DELETE FROM tipos_contratacao WHERE id = :id");
            $stmt->execute([':id' => $input['id']]);
            echo json_encode(['success' => true, 'message' => 'Tipo de contratação removido!']);
            break;
    }

} catch (PDOException $e) {
    if ($e->getCode() == '23505') {
        echo json_encode(['success' => false, 'message' => 'Este tipo de contratação já existe.']);
    } else if ($e->getCode() == '23503') {
        echo json_encode(['success' => false, 'message' => 'O tipo de contratação está em uso.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro interno de banco: ' . $e->getMessage()]);
    }
}
