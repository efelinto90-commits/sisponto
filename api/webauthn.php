<?php
header('Content-Type: application/json');
require_once '../config/Database.php';
use Config\Database;

$input = json_decode(file_get_contents('php://input'), true);
$action = isset($input['action']) ? $input['action'] : '';

try {
    $conn = Database::getConnection();

    if ($action === 'get_registration_options') {
        $id = $input['id'];
        // Generates a random challenge
        $challenge = bin2hex(random_bytes(32));
        // Simple mock challenge storage (in session or temporary table)
        // For simplicity, we just return it. In a real scenario, verify it later.
        echo json_encode([
            'success' => true,
            'challenge' => $challenge,
            'user' => [
                'id' => base64_encode($id),
                'name' => 'Funcionario_' . $id,
                'displayName' => 'Funcionário ' . $id
            ]
        ]);
        exit;
    }

    if ($action === 'register_credential') {
        $id = $input['id'];
        $credentialId = $input['credentialId']; // Base64
        $publicKey = $input['publicKey']; // Raw public key from client

        $stmt = $conn->prepare("UPDATE ponto.funcionarios SET webauthn_id = :cid, webauthn_pk = :pk WHERE id = :id");
        $stmt->execute([
            ':cid' => $credentialId,
            ':pk' => $publicKey,
            ':id' => $id
        ]);

        echo json_encode(['success' => true, 'message' => 'Biometria mobile vinculada!']);
        exit;
    }

    if ($action === 'get_assertion_options') {
        // Find user by credential if provided or just send a challenge
        $challenge = bin2hex(random_bytes(32));
        echo json_encode([
            'success' => true,
            'challenge' => $challenge
        ]);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
