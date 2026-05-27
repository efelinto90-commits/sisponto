<?php
// Script de teste para verificar a correção dinâmica de caminhos de anexo em relatorios.php

require_once __DIR__ . '/../config/Database.php';
use Config\Database;

/**
 * Copiado de api/relatorios.php para testes
 */
function verificarCaminhoAnexoLocal($dbPath) {
    // Diretório base do projeto (um nível acima de api/ ou scratch/)
    $baseDir = dirname(__DIR__) . '/';
    
    // Limpar o caminho básico
    $cleanedPath = preg_replace('/^\.\.\/sisponto\//', '', $dbPath);
    $cleanedPath = preg_replace('/^\.\.\\\\sisponto\\\\/', '', $cleanedPath);
    $cleanedPath = ltrim($cleanedPath, './\\');
    
    // 1. Se o arquivo existe no caminho limpo original, retorna ele
    if (file_exists($baseDir . $cleanedPath) && is_file($baseDir . $cleanedPath)) {
        return ['status' => 'ORIGINAL_FOUND', 'path' => $cleanedPath];
    }
    
    // 2. Se não existir, extrai o nome do arquivo e busca em outras pastas conhecidas
    $filename = basename($cleanedPath);
    
    $pastasPossiveis = [
        'uploads/justificativas/',
        'upload/justificativas/',
        'upload/justificativa/',
        'uploads/comunicados/',
        'uploads/afastamentos/',
    ];
    
    foreach ($pastasPossiveis as $pasta) {
        if (file_exists($baseDir . $pasta . $filename) && is_file($baseDir . $pasta . $filename)) {
            return ['status' => 'ALTERNATIVE_FOUND (' . $pasta . ')', 'path' => $pasta . $filename];
        }
    }
    
    // 3. Fallback se não for encontrado em nenhuma pasta
    return ['status' => 'NOT_FOUND_FALLBACK', 'path' => $cleanedPath];
}

try {
    $conn = Database::getConnection();
    echo "Conexão com o banco de dados realizada com sucesso!\n\n";

    // 1. Testar alguns registros com anexo_justificativa na tabela registros
    $stmt = $conn->query("SELECT id, data, anexo_justificativa, anexo_comunicado FROM registros WHERE (anexo_justificativa IS NOT NULL AND anexo_justificativa <> '' AND anexo_justificativa <> '[]' AND anexo_justificativa <> 'null') OR (anexo_comunicado IS NOT NULL AND anexo_comunicado <> '' AND anexo_comunicado <> '[]' AND anexo_comunicado <> 'null') LIMIT 5");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "=== Testando Anexos da Tabela de Registros ===\n";
    if (empty($registros)) {
        echo "Nenhum registro com anexo encontrado.\n";
    } else {
        foreach ($registros as $r) {
            echo "Registro ID: {$r['id']} | Data: {$r['data']}\n";
            if (!empty($r['anexo_justificativa'])) {
                echo "  - original anexo_justificativa: {$r['anexo_justificativa']}\n";
                $parsed = json_decode($r['anexo_justificativa'], true);
                if (is_array($parsed)) {
                    foreach ($parsed as $p) {
                        $res = verificarCaminhoAnexoLocal($p);
                        echo "    * parsed item original: $p => status: {$res['status']} => resolved: {$res['path']}\n";
                    }
                } else {
                    $res = verificarCaminhoAnexoLocal($r['anexo_justificativa']);
                    echo "    * raw item: {$r['anexo_justificativa']} => status: {$res['status']} => resolved: {$res['path']}\n";
                }
            }
            if (!empty($r['anexo_comunicado'])) {
                echo "  - original anexo_comunicado: {$r['anexo_comunicado']}\n";
                $parsed = json_decode($r['anexo_comunicado'], true);
                if (is_array($parsed)) {
                    foreach ($parsed as $p) {
                        $res = verificarCaminhoAnexoLocal($p);
                        echo "    * parsed item original: $p => status: {$res['status']} => resolved: {$res['path']}\n";
                    }
                } else {
                    $res = verificarCaminhoAnexoLocal($r['anexo_comunicado']);
                    echo "    * raw item: {$r['anexo_comunicado']} => status: {$res['status']} => resolved: {$res['path']}\n";
                }
            }
            echo "\n";
        }
    }

    // 2. Testar alguns registros na tabela justificativas
    $stmtJust = $conn->query("SELECT id, data_registro, campo_ponto, anexos FROM justificativas WHERE anexos IS NOT NULL AND anexos <> '' AND anexos <> '[]' AND anexos <> 'null' LIMIT 5");
    $justs = $stmtJust->fetchAll(PDO::FETCH_ASSOC);

    echo "=== Testando Anexos da Tabela de Justificativas (PWA) ===\n";
    if (empty($justs)) {
        echo "Nenhuma justificativa PWA com anexo encontrada.\n";
    } else {
        foreach ($justs as $j) {
            echo "Justificativa ID: {$j['id']} | Data: {$j['data_registro']} | Campo: {$j['campo_ponto']}\n";
            echo "  - original anexos: {$j['anexos']}\n";
            $parsed = json_decode($j['anexos'], true);
            if (is_array($parsed)) {
                foreach ($parsed as $p) {
                    $res = verificarCaminhoAnexoLocal($p);
                    echo "    * parsed item original: $p => status: {$res['status']} => resolved: {$res['path']}\n";
                }
            } else {
                $res = verificarCaminhoAnexoLocal($j['anexos']);
                echo "    * raw item: {$j['anexos']} => status: {$res['status']} => resolved: {$res['path']}\n";
            }
            echo "\n";
        }
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
