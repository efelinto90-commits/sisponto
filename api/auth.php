<?php
ob_start();
session_start();
header('Content-Type: application/json');

date_default_timezone_set('America/Sao_Paulo');

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

} elseif ($action === 'login_facial') {
    // --- Login por Biometria Facial ---
    $inputDescriptor = $input['facial_descriptor'] ?? null;

    if (empty($inputDescriptor) || !is_array($inputDescriptor)) {
        sendJsonResponse(['success' => false, 'message' => 'Descritor facial não recebido.']);
    }

    function euclideanDistanceAuth($a, $b) {
        if (!$a || !$b || count($a) !== count($b)) return 1.0;
        $sum = 0;
        for ($i = 0; $i < count($a); $i++) {
            $sum += pow((float)$a[$i] - (float)$b[$i], 2);
        }
        return sqrt($sum);
    }

    try {
        $conn = Database::getConnection();
        $stmt = $conn->query("SELECT id, name, password, level, permissoes, setor, facial_descriptor FROM users WHERE facial_descriptor IS NOT NULL AND facial_descriptor != ''");
        $users = $stmt->fetchAll();

        $bestMatch = null;
        $bestDistance = 0.55; // threshold de reconhecimento

        foreach ($users as $u) {
            $storedDescriptor = json_decode($u['facial_descriptor'], true);
            if (!is_array($storedDescriptor)) continue;
            $dist = euclideanDistanceAuth($inputDescriptor, $storedDescriptor);
            if ($dist < $bestDistance) {
                $bestDistance = $dist;
                $bestMatch = $u;
            }
        }

        if ($bestMatch) {
            $_SESSION['user_id']          = $bestMatch['id'];
            $_SESSION['user_name']        = $bestMatch['name'];
            $_SESSION['user_level']       = $bestMatch['level'];
            $_SESSION['user_permissions'] = json_decode($bestMatch['permissoes'] ?? '[]', true);
            $_SESSION['user_setor']       = $bestMatch['setor'] ?? '';
            sendJsonResponse(['success' => true, 'user' => $bestMatch['name']]);
        } else {
            sendJsonResponse(['success' => false, 'message' => 'Face não reconhecida. Verifique seu cadastro facial.']);
        }
    } catch (Throwable $e) {
        sendJsonResponse(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
    }

} elseif ($action === 'save_facial') {
    // --- Salvar Descritor Facial (requer sessão) ---
    if (!isset($_SESSION['user_id'])) {
        sendJsonResponse(['success' => false, 'message' => 'Não autenticado.']);
    }
    $descriptor = $input['facial_descriptor'] ?? null;
    if (empty($descriptor) || !is_array($descriptor)) {
        sendJsonResponse(['success' => false, 'message' => 'Descritor inválido.']);
    }
    try {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("UPDATE users SET facial_descriptor = :fd WHERE id = :id");
        $stmt->execute([':fd' => json_encode($descriptor), ':id' => $_SESSION['user_id']]);
        sendJsonResponse(['success' => true, 'message' => 'Biometria facial salva com sucesso!']);
    } catch (Throwable $e) {
        sendJsonResponse(['success' => false, 'message' => 'Erro ao salvar: ' . $e->getMessage()]);
    }

} elseif ($action === 'remove_facial') {
    // --- Remover Descritor Facial (requer sessão) ---
    if (!isset($_SESSION['user_id'])) {
        sendJsonResponse(['success' => false, 'message' => 'Não autenticado.']);
    }
    try {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("UPDATE users SET facial_descriptor = NULL WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        sendJsonResponse(['success' => true, 'message' => 'Biometria facial removida.']);
    } catch (Throwable $e) {
        sendJsonResponse(['success' => false, 'message' => 'Erro ao remover: ' . $e->getMessage()]);
    }

} elseif ($action === 'get_facial_status') {
    // --- Verificar se usuário tem facial cadastrado (requer sessão) ---
    if (!isset($_SESSION['user_id'])) {
        sendJsonResponse(['success' => false, 'message' => 'Não autenticado.']);
    }
    try {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT (facial_descriptor IS NOT NULL AND facial_descriptor != '') AS tem_facial FROM users WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $row = $stmt->fetch();
        sendJsonResponse(['success' => true, 'tem_facial' => (bool)($row['tem_facial'] ?? false)]);
    } catch (Throwable $e) {
        sendJsonResponse(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    }

} elseif ($action === 'alterar_senha') {
    $username = trim($input['username'] ?? '');
    $setor = trim($input['setor'] ?? '');
    $new_password = $input['new_password'] ?? '';

    if (empty($username) || empty($new_password)) {
        sendJsonResponse(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
    }

    if (strlen($new_password) < 6) {
        sendJsonResponse(['success' => false, 'message' => 'A nova senha deve ter no mínimo 6 caracteres.']);
    }

    try {
        $conn = Database::getConnection();
        
        // Verificar se usuário e setor coincidem
        // Se o setor for nulo no banco, permitimos o casamento caso o input seja vazio
        $stmt = $conn->prepare("
            SELECT id, name, level, permissoes 
            FROM users 
            WHERE LOWER(name) = LOWER(:username) 
              AND (
                LOWER(setor) = LOWER(:setor) 
                OR (setor IS NULL AND :setor = '')
              )
        ");
        $stmt->execute([':username' => $username, ':setor' => $setor]);
        $user = $stmt->fetch();

        if (!$user) {
            sendJsonResponse(['success' => false, 'message' => 'Usuário ou setor cadastrado incorretos.']);
        }

        // Atualizar senha
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmtUpdate = $conn->prepare("UPDATE users SET password = :pass, updated_at = NOW() WHERE id = :id");
        $stmtUpdate->execute([':pass' => $hash, ':id' => $user['id']]);

        sendJsonResponse(['success' => true, 'message' => 'Senha alterada com sucesso! Suas permissões foram mantidas.']);
    } catch (Throwable $e) {
        sendJsonResponse(['success' => false, 'message' => 'Erro interno ao alterar senha: ' . $e->getMessage()]);
    }

} else {
    sendJsonResponse(['success' => false, 'message' => 'Ação desconhecida']);
}
