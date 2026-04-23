<?php
session_start();
header('Content-Type: application/json');
require_once '../config/Database.php';

use Config\Database;

// Check base authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$user_id_session = $_SESSION['user_id'];
$is_crh = (in_array(strtolower(trim($_SESSION['user_name'] ?? '')), ['corsin', 'crh']) || in_array(strtolower(trim($_SESSION['user_setor'] ?? '')), ['corsin', 'crh']));
$can_manage = in_array($_SESSION['user_level'], ['1', '2']) || $is_crh;
$has_usuarios_perm = in_array('usuarios', $_SESSION['user_permissions'] ?? []) || $is_crh || $_SESSION['user_level'] == '1';

try {
    $conn = Database::getConnection();

    switch ($method) {
        case 'GET':
            // Apenas quem tem permissão de usuários pode listar
            if (!$has_usuarios_perm) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
                exit;
            }

            if (isset($_GET['action'])) {
                if ($_GET['action'] === 'get_presets') {
                    $stmt = $conn->query("SELECT level, nome, permissoes FROM ponto.permissao_presets ORDER BY level ASC");
                    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
                }
                exit;
            }

            if (isset($_GET['id'])) {
                $stmt = $conn->prepare("SELECT id, name, level, permissoes, setor FROM users WHERE id = :id");
                $stmt->execute([':id' => $_GET['id']]);
                echo json_encode(['success' => true, 'data' => $stmt->fetch()]);
            } else {
                $stmt = $conn->query("SELECT id, name, level, permissoes, setor FROM users ORDER BY id ASC");
                echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Gerenciar presets: apenas Admin
            if (isset($input['action']) && $input['action'] === 'save_presets') {
                if (!$can_manage) {
                    echo json_encode(['success' => false, 'message' => 'Acesso negado para gerenciar presets.']);
                    exit;
                }
                foreach ($input['presets'] as $preset) {
                    $stmt = $conn->prepare("UPDATE ponto.permissao_presets SET permissoes = :perms WHERE level = :lvl");
                    $stmt->execute([
                        ':perms' => json_encode($preset['permissoes']),
                        ':lvl' => $preset['level']
                    ]);
                }
                echo json_encode(['success' => true, 'message' => 'Configurações de categoria salvas!']);
                exit;
            }

            // Criar usuário: Admin ou Gestor
            if (!$can_manage) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado para criar novos usuários.']);
                exit;
            }

            // Restrição: Apenas Nível 1 pode criar Nível 1
            $target_level = $input['level'] ?? '3';
            if ($target_level == '1' && $_SESSION['user_level'] != '1') {
                echo json_encode(['success' => false, 'message' => 'Apenas administradores podem criar outros administradores.']);
                exit;
            }

            $hash = password_hash($input['password'] ?? '123456', PASSWORD_DEFAULT);
            $perms = json_encode($input['permissoes'] ?? []);
            $setor = !empty($input['setor']) ? $input['setor'] : null;

            $sql = "INSERT INTO users (name, password, level, permissoes, setor, created_at, updated_at) 
                    VALUES (:name, :pass, :level, :perms, :setor, NOW(), NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':name' => $input['name'],
                ':pass' => $hash,
                ':level' => $target_level,
                ':perms' => $perms,
                ':setor' => $setor
            ]);
            echo json_encode(['success' => true, 'message' => 'Usuário cadastrado com sucesso!']);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $target_id = $input['id'] ?? null;

            // Caso especial: Troca de senha própria
            if (isset($input['action']) && $input['action'] === 'change_password') {
                // Forçar o ID do usuário da sessão para garantir segurança e evitar erros de ID ausente
                $target_id = $user_id_session;
                
                if (empty($input['password'])) {
                    echo json_encode(['success' => false, 'message' => 'A nova senha não pode estar vazia.']);
                    exit;
                }
                
                $hash = password_hash($input['password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = :pass, updated_at = NOW() WHERE id = :id");
                $stmt->execute([':pass' => $hash, ':id' => $target_id]);
                echo json_encode(['success' => true, 'message' => 'Senha alterada com sucesso!']);
                exit;
            }

            // Edição geral: Admin ou Gestor
            if (!$can_manage) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado para editar usuários.']);
                exit;
            }

            // Restrição: Apenas Nível 1 pode editar Nível 1 ou promover para Nível 1
            $new_level = $input['level'] ?? '3';
            
            // Buscar nível atual
            $stmtLevel = $conn->prepare("SELECT level FROM users WHERE id = :id");
            $stmtLevel->execute([':id' => $target_id]);
            $current_user_data = $stmtLevel->fetch();
            $current_level = $current_user_data['level'] ?? '3';

            if (($current_level == '1' || $new_level == '1') && $_SESSION['user_level'] != '1') {
                echo json_encode(['success' => false, 'message' => 'Apenas administradores podem gerenciar contas de nível administrador.']);
                exit;
            }

            $perms = json_encode($input['permissoes'] ?? []);
            $level = $new_level;
            $setor = !empty($input['setor']) ? $input['setor'] : null;

            if (!empty($input['password'])) {
                $hash = password_hash($input['password'], PASSWORD_DEFAULT);
                $sql = "UPDATE users SET name = :name, password = :pass, level = :level, permissoes = :perms, setor = :setor, updated_at = NOW() WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':name' => $input['name'],
                    ':pass' => $hash,
                    ':level' => $level,
                    ':perms' => $perms,
                    ':setor' => $setor,
                    ':id' => $target_id
                ]);
            } else {
                $sql = "UPDATE users SET name = :name, level = :level, permissoes = :perms, setor = :setor, updated_at = NOW() WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':name' => $input['name'],
                    ':level' => $level,
                    ':perms' => $perms,
                    ':setor' => $setor,
                    ':id' => $target_id
                ]);
            }
            echo json_encode(['success' => true, 'message' => 'Usuário atualizado!']);
            break;

        case 'DELETE':
            if (!$can_manage) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado para remover usuários.']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if ($input['id'] == $user_id_session) {
                echo json_encode(['success' => false, 'message' => 'Você não pode excluir a si mesmo!']);
                exit;
            }

            // Restrição: Apenas Nível 1 pode excluir Nível 1
            $stmtLevel = $conn->prepare("SELECT level FROM users WHERE id = :id");
            $stmtLevel->execute([':id' => $input['id']]);
            $current_user_data = $stmtLevel->fetch();
            $current_level = $current_user_data['level'] ?? '3';

            if ($current_level == '1' && $_SESSION['user_level'] != '1') {
                echo json_encode(['success' => false, 'message' => 'Apenas administradores podem remover outros administradores.']);
                exit;
            }

            $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $input['id']]);
            echo json_encode(['success' => true, 'message' => 'Usuário removido!']);
            break;
    }

} catch (PDOException $e) {
    if ($e->getCode() == '23505') { // Unique violation
        echo json_encode(['success' => false, 'message' => 'Esse nome de usuário já está em uso.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro interno de banco: ' . $e->getMessage()]);
    }
}
