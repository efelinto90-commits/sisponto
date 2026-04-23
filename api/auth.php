<?php
ob_start();
session_start();
header('Content-Type: application/json');

require_once '../config/Database.php';

use Config\Database;

// Função utilitária para enviar JSON limpo
function sendJsonResponse($data) {
    while (ob_get_level() > 0) {
        if (!ob_end_clean()) break;
    }
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Método inválido']);
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'login') {
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($username) || empty($password)) {
        sendJsonResponse(['success' => false, 'message' => 'Preencha todos os campos']);
    }

    try {
        $conn = Database::getConnection();
        // Usando LOWER para busca case-insensitive
        $stmt = $conn->prepare("SELECT id, name, password, level, permissoes, setor FROM users WHERE LOWER(name) = LOWER(:username)");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Sucesso!
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_level'] = $user['level'];
            $_SESSION['user_permissions'] = json_decode($user['permissoes'] ?? '[]', true);
            $_SESSION['user_setor'] = $user['setor'] ?? '';

            sendJsonResponse(['success' => true]);
        } else {
            sendJsonResponse(['success' => false, 'message' => 'Usuário ou senha incorretos.']);
        }
    } catch (Throwable $e) {
        sendJsonResponse(['success' => false, 'message' => 'Erro interno de autenticação: ' . $e->getMessage()]);
    }

} elseif ($action === 'verify') {
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($username) || empty($password)) {
        sendJsonResponse(['success' => false, 'message' => 'Preencha todos os campos']);
    }

    try {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT password FROM users WHERE LOWER(name) = LOWER(:username)");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            sendJsonResponse(['success' => true]);
        } else {
            sendJsonResponse(['success' => false, 'message' => 'Usuário ou senha incorretos.']);
        }
    } catch (Throwable $e) {
        sendJsonResponse(['success' => false, 'message' => 'Erro interno de verificação: ' . $e->getMessage()]);
    }

} elseif ($action === 'logout') {
    session_destroy();
    sendJsonResponse(['success' => true]);
} else {
    sendJsonResponse(['success' => false, 'message' => 'Ação desconhecida']);
}
