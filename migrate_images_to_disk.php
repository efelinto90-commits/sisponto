<?php
require_once 'config/Database.php';
use Config\Database;

/**
 * Salva uma imagem base64 em disco
 */
function saveBase64ImageMigrate($base64Data, $directory, $prefix, $id) {
    if (empty($base64Data)) return null;
    if (strpos($base64Data, 'uploads/') === 0) return $base64Data; // Já migrado

    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
    
    try {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
            $data = substr($base64Data, strpos($base64Data, ',') + 1);
            $type = strtolower($type[1]);
            $data = base64_decode($data);
            
            $filename = $prefix . "_" . $id . "_" . time() . "." . $type;
            $relativePath = $directory . $filename;
            
            if (file_put_contents($relativePath, $data)) {
                return $relativePath;
            }
        }
    } catch (Exception $e) {
        echo "Erro ao salvar imagem id {$id}: " . $e->getMessage() . "\n";
    }
    return null;
}

try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT id, foto_perfil, biometria_facial, codigo_qr FROM funcionarios WHERE foto_perfil LIKE 'data:image%' OR biometria_facial LIKE 'data:image%' OR codigo_qr LIKE 'data:image%'");
    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Iniciando migração de " . count($funcionarios) . " registros...\n";

    foreach ($funcionarios as $f) {
        $updates = [];
        $params = [':id' => $f['id']];

        if (!empty($f['foto_perfil']) && strpos($f['foto_perfil'], 'data:image') === 0) {
            $path = saveBase64ImageMigrate($f['foto_perfil'], 'uploads/perfil/', 'perfil', $f['id']);
            if ($path) {
                $updates[] = "foto_perfil = :foto";
                $params[':foto'] = $path;
            }
        }

        if (!empty($f['biometria_facial']) && strpos($f['biometria_facial'], 'data:image') === 0) {
            $path = saveBase64ImageMigrate($f['biometria_facial'], 'uploads/facial/', 'facial', $f['id']);
            if ($path) {
                $updates[] = "biometria_facial = :facial";
                $params[':facial'] = $path;
            }
        }

        if (!empty($f['codigo_qr']) && strpos($f['codigo_qr'], 'data:image') === 0) {
            $path = saveBase64ImageMigrate($f['codigo_qr'], 'uploads/qrcode/', 'qr', $f['id']);
            if ($path) {
                $updates[] = "codigo_qr = :qr";
                $params[':qr'] = $path;
            }
        }

        if (count($updates) > 0) {
            $sql = "UPDATE funcionarios SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmtUpd = $conn->prepare($sql);
            $stmtUpd->execute($params);
            echo "ID {$f['id']}: Migrado com sucesso.\n";
        }
    }

    echo "Migração concluída!\n";

} catch (Exception $e) {
    echo "Erro fatal: " . $e->getMessage() . "\n";
}
