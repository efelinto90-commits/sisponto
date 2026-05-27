<?php
session_start();
header('Content-Type: application/json');
// Libera o bloqueio da sessão imediatamente - as informações já foram lidas
session_write_close();
require_once '../config/Database.php';

use Config\Database;

$method = $_SERVER['REQUEST_METHOD'];

try {
    $conn = Database::getConnection();

    switch ($method) {
        case 'GET':
            $stmt = $conn->query("SELECT * FROM geofencing_presets ORDER BY nome_area ASC");
            $presets = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $presets]);
            break;

        case 'POST':
            if (($_SESSION['user_level'] ?? 3) != 1) {
                echo json_encode(['success' => false, 'message' => 'Permissão negada. Apenas administradores podem gerenciar áreas salvas.']);
                exit;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['nome_area']) || empty($input['latitude']) || empty($input['longitude'])) {
                echo json_encode(['success' => false, 'message' => 'Dados incompletos para salvar a área.']);
                exit;
            }

            // UPSERT logic: If name exists, update; otherwise, insert.
            $sql = "INSERT INTO geofencing_presets (nome_area, latitude, longitude, distancia_max) 
                    VALUES (:nome, :lat, :lng, :dist)
                    ON CONFLICT (nome_area) 
                    DO UPDATE SET latitude = EXCLUDED.latitude, 
                                 longitude = EXCLUDED.longitude, 
                                 distancia_max = EXCLUDED.distancia_max";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':nome' => $input['nome_area'],
                ':lat' => isset($input['latitude']) ? floatval(str_replace(',', '.', $input['latitude'])) : 0,
                ':lng' => isset($input['longitude']) ? floatval(str_replace(',', '.', $input['longitude'])) : 0,
                ':dist' => $input['distancia_max'] ?? 200
            ]);

            echo json_encode(['success' => true, 'message' => 'Área salva com sucesso!']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Método não suportado.']);
            break;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}
