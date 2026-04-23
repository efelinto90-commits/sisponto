<?php
header('Content-Type: application/json');

require_once '../config/Database.php';
use Config\Database;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_crh_viewer = (($_SESSION['user_level'] ?? '') == '1' || in_array(strtolower(trim($_SESSION['user_name'] ?? '')), ['corsin', 'crh']) || in_array(strtolower(trim($_SESSION['user_setor'] ?? '')), ['corsin', 'crh']));
if (!$is_crh_viewer) {
    echo json_encode(['success' => false, 'message' => 'Permissão negada.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    $conn = Database::getConnection();

    if ($method === 'GET') {
        if (isset($_GET['action']) && $_GET['action'] === 'get_setores') {
            $stmt = $conn->query("SELECT DISTINCT setor FROM funcionarios WHERE setor IS NOT NULL AND setor != '' ORDER BY setor ASC");
            $setores = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo json_encode(['success' => true, 'data' => $setores]);
            exit;
        }

        $stmt = $conn->query("SELECT * FROM ponto_liberado ORDER BY data_hora DESC");
        $liberacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $liberacoes]);
        exit;
    }

    if ($method === 'POST') {
        $data_hora = $input['data_hora'] ?? '';
        $descricao = $input['descricao'] ?? '';
        $setor = $input['setor'] ?? 'TODOS';

        if (empty($data_hora)) {
            echo json_encode(['success' => false, 'message' => 'Data e hora são obrigatórias.']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO ponto_liberado (data_hora, descricao, setor) VALUES (:dh, :descr, :setor)");
        $stmt->execute([
            ':dh' => $data_hora,
            ':descr' => $descricao,
            ':setor' => $setor
        ]);

        echo json_encode(['success' => true, 'message' => 'Liberação de ponto registrada com sucesso!']);
        exit;
    }

    if ($method === 'DELETE') {
        $id = $input['id'] ?? '';
        $stmt = $conn->prepare("DELETE FROM ponto_liberado WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['success' => true, 'message' => 'Registro removido.']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
