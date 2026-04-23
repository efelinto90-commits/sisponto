<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

if (ob_get_level()) { ob_end_clean(); }
ob_start();
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$projectRoot = realpath(__DIR__ . '/..');
$deployConfigFile = $projectRoot . '/config/deploy.json';

function sendJsonResponse($data) {
    while (ob_get_level() > 0) {
        if (!ob_end_clean()) break;
    }
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function getDeployConfig() {
    global $deployConfigFile;
    if (file_exists($deployConfigFile)) {
        return json_decode(file_get_contents($deployConfigFile), true) ?: [];
    }
    return ['prod_path' => ''];
}

function saveDeployConfig($config) {
    global $deployConfigFile;
    return file_put_contents($deployConfigFile, json_encode($config, JSON_PRETTY_PRINT));
}

function listFilesRecursive($dir, $baseDir = '') {
    $results = [];
    $ignoreFolders = ['vendor', '.git', 'node_modules', '.gemini', 'tmp'];
    $ignoreFiles = ['.DS_Store', 'deploy.json', 'local.json', 'prod.json'];

    $files = scandir($dir);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $path = $dir . DIRECTORY_SEPARATOR . $file;
        $relativePath = $baseDir ? $baseDir . '/' . $file : $file;

        if (is_dir($path)) {
            if (in_array($file, $ignoreFolders)) continue;
            $results = array_merge($results, listFilesRecursive($path, $relativePath));
        } else {
            if (in_array($file, $ignoreFiles)) continue;
            
            $results[$relativePath] = [
                'md5' => md5_file($path),
                'size' => filesize($path),
                'mtime' => filemtime($path)
            ];
        }
    }

    return $results;
}

if ($action === 'get_config') {
    sendJsonResponse(['success' => true, 'config' => getDeployConfig()]);
}

if ($action === 'save_config') {
    $input = json_decode(file_get_contents('php://input'), true);
    $prodPath = $input['prod_path'] ?? '';
    
    if (empty($prodPath)) {
        sendJsonResponse(['success' => false, 'message' => 'O caminho de produção não pode estar vazio.']);
    }

    if (!is_dir($prodPath)) {
        // Tenta criar se não existir
        if (!@mkdir($prodPath, 0777, true)) {
            sendJsonResponse(['success' => false, 'message' => 'O caminho de produção não é um diretório válido e não pôde ser criado.']);
        }
    }

    if (saveDeployConfig(['prod_path' => realpath($prodPath)])) {
        sendJsonResponse(['success' => true, 'message' => 'Configuração salva!']);
    } else {
        sendJsonResponse(['success' => false, 'message' => 'Falha ao salvar configuração.']);
    }
}

if ($action === 'compare') {
    $config = getDeployConfig();
    $prodPath = $config['prod_path'] ?? '';

    if (empty($prodPath) || !is_dir($prodPath)) {
        sendJsonResponse(['success' => false, 'message' => 'Caminho de produção não configurado ou inválido.']);
    }

    $localFiles = listFilesRecursive($projectRoot);
    $prodFiles = listFilesRecursive($prodPath);

    $comparison = [];
    $protectedFiles = ['config/local.json', 'config/prod.json', 'config/deploy.json'];

    foreach ($localFiles as $path => $info) {
        $status = 'novo';
        $pPath = $prodPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
        
        if (isset($prodFiles[$path])) {
            if ($info['md5'] === $prodFiles[$path]['md5']) {
                $status = 'igual';
            } else {
                $status = 'alterado';
            }
        }

        $protected = false;
        if (in_array($path, $protectedFiles) || strpos($path, 'config/') === 0) {
            $protected = true;
        }

        $comparison[] = [
            'path' => $path,
            'status' => $status,
            'size' => $info['size'],
            'mtime' => date('Y-m-d H:i:s', $info['mtime']),
            'protected' => $protected
        ];
    }

    // Identificar arquivos que existem na produção mas não no local (opcional, para informação)
    foreach ($prodFiles as $path => $info) {
        if (!isset($localFiles[$path])) {
            $comparison[] = [
                'path' => $path,
                'status' => 'apenas_prod',
                'size' => $info['size'],
                'mtime' => date('Y-m-d H:i:s', $info['mtime']),
                'protected' => false
            ];
        }
    }

    sendJsonResponse(['success' => true, 'files' => $comparison]);
}

if ($action === 'deploy') {
    $input = json_decode(file_get_contents('php://input'), true);
    $filesToDeploy = $input['files'] ?? [];
    $config = getDeployConfig();
    $prodPath = $config['prod_path'] ?? '';

    if (empty($prodPath) || !is_dir($prodPath)) {
        sendJsonResponse(['success' => false, 'message' => 'Caminho de produção não configurado ou inválido.']);
    }

    if (empty($filesToDeploy)) {
        sendJsonResponse(['success' => false, 'message' => 'Nenhum arquivo selecionado para deploy.']);
    }

    $log = [];
    $protectedFiles = ['config/local.json', 'config/prod.json', 'config/deploy.json'];

    foreach ($filesToDeploy as $path) {
        // Proteção extra no backend
        if (in_array($path, $protectedFiles) || strpos($path, 'config/') === 0) {
            $log[] = "PULADO: $path (Arquivo protegido)";
            continue;
        }

        $sourceFile = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
        $destFile = $prodPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);

        if (!file_exists($sourceFile)) {
            $log[] = "ERRO: $path (Arquivo de origem não encontrado)";
            continue;
        }

        $destDir = dirname($destFile);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        }

        if (copy($sourceFile, $destFile)) {
            $log[] = "SUCESSO: $path";
        } else {
            $log[] = "ERRO: $path (Falha ao copiar)";
        }
    }

    sendJsonResponse(['success' => true, 'log' => $log]);
}

sendJsonResponse(['success' => false, 'message' => 'Ação inválida.']);
