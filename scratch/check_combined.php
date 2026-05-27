<?php
require_once __DIR__ . '/../config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    
    $sectorFilter = ""; // For testing, retrieve all
    
    // Query registrations
    $sql1 = "
        SELECT r.id, r.data, f.nome, f.matricula, r.justificativa, r.tipo_justificativa, r.anexo_justificativa, r.comunicado, r.anexo_comunicado, r.status_crh
        FROM registros r
        JOIN funcionarios f ON r.id_funcionario = f.id
        WHERE (
            (r.anexo_justificativa IS NOT NULL AND r.anexo_justificativa <> '' AND r.anexo_justificativa <> '[]' AND r.anexo_justificativa <> 'null')
            OR 
            (r.anexo_comunicado IS NOT NULL AND r.anexo_comunicado <> '' AND r.anexo_comunicado <> '[]' AND r.anexo_comunicado <> 'null')
        )
        " . $sectorFilter . "
        ORDER BY r.data DESC
    ";
    
    $stmt1 = $conn->query($sql1);
    $regs = $stmt1->fetchAll(PDO::FETCH_ASSOC);
    
    // Query employee justifications table directly to catch any not yet in registros
    $sql2 = "
        SELECT j.id, j.data_registro as data, f.nome, f.matricula, j.texto as justificativa, 'Enviado pelo Colaborador' as tipo_justificativa, j.anexos as anexo_justificativa, j.status as status_crh
        FROM justificativas j
        JOIN funcionarios f ON j.id_funcionario = f.id
        WHERE j.anexos IS NOT NULL AND j.anexos <> '' AND j.anexos <> '[]' AND j.anexos <> 'null'
        " . $sectorFilter . "
        ORDER BY j.data_registro DESC
    ";
    $stmt2 = $conn->query($sql2);
    $justs = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    // Merge and deduplicate by date + matricula
    $merged = [];
    
    foreach ($regs as $r) {
        $key = $r['data'] . '_' . $r['matricula'];
        
        $anexos = [];
        if (!empty($r['anexo_justificativa']) && $r['anexo_justificativa'] !== 'null' && $r['anexo_justificativa'] !== '[]') {
            $parsed = json_decode($r['anexo_justificativa'], true);
            if (is_array($parsed)) $anexos = array_merge($anexos, $parsed);
            else $anexos[] = $r['anexo_justificativa'];
        }
        if (!empty($r['anexo_comunicado']) && $r['anexo_comunicado'] !== 'null' && $r['anexo_comunicado'] !== '[]') {
            $parsed = json_decode($r['anexo_comunicado'], true);
            if (is_array($parsed)) $anexos = array_merge($anexos, $parsed);
            else $anexos[] = $r['anexo_comunicado'];
        }
        
        $anexos = array_values(array_unique(array_filter($anexos)));
        
        $merged[$key] = [
            'id' => $r['id'],
            'data' => $r['data'],
            'nome' => $r['nome'],
            'matricula' => $r['matricula'],
            'justificativa' => $r['justificativa'] ?: $r['comunicado'] ?: 'Sem texto',
            'tipo_justificativa' => $r['tipo_justificativa'] ?: 'Justificativa',
            'anexos' => $anexos,
            'status_crh' => $r['status_crh'] ?: 'pendente'
        ];
    }
    
    foreach ($justs as $j) {
        $key = $j['data'] . '_' . $j['matricula'];
        
        $anexos = [];
        if (!empty($j['anexo_justificativa']) && $j['anexo_justificativa'] !== 'null' && $j['anexo_justificativa'] !== '[]') {
            $parsed = json_decode($j['anexo_justificativa'], true);
            if (is_array($parsed)) $anexos = array_merge($anexos, $parsed);
            else $anexos[] = $j['anexo_justificativa'];
        }
        
        $anexos = array_values(array_unique(array_filter($anexos)));
        
        if (isset($merged[$key])) {
            // Merge attachments if we have more
            $merged[$key]['anexos'] = array_values(array_unique(array_merge($merged[$key]['anexos'], $anexos)));
        } else {
            $merged[$key] = [
                'id' => 'j_' . $j['id'],
                'data' => $j['data'],
                'nome' => $j['nome'],
                'matricula' => $j['matricula'],
                'justificativa' => $j['justificativa'] ?: 'Sem texto',
                'tipo_justificativa' => $j['tipo_justificativa'],
                'anexos' => $anexos,
                'status_crh' => $j['status_crh'] ?: 'pendente'
            ];
        }
    }
    
    // Sort merged by date DESC
    usort($merged, function($a, $b) {
        return strcmp($b['data'], $a['data']);
    });
    
    echo "=== MERGED JUSTIFICATIONS WITH ATTACHMENTS (" . count($merged) . ") ===\n";
    foreach (array_slice($merged, 0, 10) as $item) {
        echo "Data: {$item['data']} | Func: {$item['nome']} | Status: {$item['status_crh']} | Anexos: " . json_encode($item['anexos']) . " | Just: {$item['justificativa']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
