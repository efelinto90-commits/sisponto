<?php
/**
 * Sincronizador de Fotos - SisPonto
 * Copia fotos faltantes do servidor remoto para o servidor local
 * Acesse: http://localhost:81/sisponto/api/sync_fotos.php
 */
session_start();
header('Content-Type: application/json');
session_write_close();

// Só admin pode usar
if (($_SESSION['user_level'] ?? 3) != 1) {
    echo json_encode(['success' => false, 'message' => 'Acesso restrito a administradores.']);
    exit;
}

require_once '../config/Database.php';
use Config\Database;

// URL base do servidor remoto (onde o sisponto está em produção)
// Ajuste se necessário
$REMOTE_BASE = 'http://funad.ddns.net:81/sisponto/';

$action = $_GET['action'] ?? 'check';

try {
    $conn = Database::getConnection();
    session_write_close();

    // Listar todos com fotos
    $stmt = $conn->query("SELECT id, nome, foto_perfil, biometria_facial FROM ponto.funcionarios WHERE foto_perfil IS NOT NULL OR biometria_facial IS NOT NULL ORDER BY id");
    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $basePath = realpath(__DIR__ . '/../');
    $missing = [];
    $synced  = [];
    $errors  = [];

    foreach ($funcionarios as $f) {
        $paths = [];
        if ($f['foto_perfil'])      $paths[] = ['field' => 'foto_perfil',      'path' => $f['foto_perfil']];
        if ($f['biometria_facial']) $paths[] = ['field' => 'biometria_facial',  'path' => $f['biometria_facial']];

        foreach ($paths as $item) {
            $localAbs = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $item['path']);
            if (!file_exists($localAbs)) {
                $missing[] = [
                    'id'    => $f['id'],
                    'nome'  => $f['nome'],
                    'field' => $item['field'],
                    'path'  => $item['path'],
                    'local' => $localAbs,
                ];
                if ($action === 'sync') {
                    // Tentar baixar do servidor remoto
                    $remoteUrl = $REMOTE_BASE . $item['path'];
                    $dir = dirname($localAbs);
                    if (!is_dir($dir)) mkdir($dir, 0777, true);

                    $ch = curl_init($remoteUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    $imgData = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if ($httpCode === 200 && $imgData && strlen($imgData) > 1000) {
                        file_put_contents($localAbs, $imgData);
                        $synced[] = ['id' => $f['id'], 'nome' => $f['nome'], 'path' => $item['path']];
                    } else {
                        $errors[] = ['id' => $f['id'], 'nome' => $f['nome'], 'path' => $item['path'], 'http' => $httpCode];
                    }
                }
            }
        }
    }

    echo json_encode([
        'success'       => true,
        'action'        => $action,
        'remote_base'   => $REMOTE_BASE,
        'total_checked' => count($funcionarios),
        'missing_count' => count($missing),
        'missing'       => $action === 'check' ? $missing : null,
        'synced_count'  => count($synced),
        'synced'        => $synced,
        'error_count'   => count($errors),
        'errors'        => $errors,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
