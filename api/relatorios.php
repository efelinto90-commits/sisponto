<?php
header('Content-Type: application/json');

date_default_timezone_set('America/Sao_Paulo');

require_once '../config/Database.php';

use Config\Database;

/**
 * Verifica se um anexo existe na pasta 'uploads' ou na pasta 'upload',
 * corrigindo dinamicamente o caminho do link.
 */
function verificarCaminhoAnexo($dbPath) {
    // Diretório base do projeto (um nível acima de api/)
    $baseDir = dirname(__DIR__) . '/';
    
    // Limpar o caminho básico
    $cleanedPath = preg_replace('/^\.\.\/sisponto\//', '', $dbPath);
    $cleanedPath = preg_replace('/^\.\.\\\\sisponto\\\\/', '', $cleanedPath);
    $cleanedPath = ltrim($cleanedPath, './\\');
    
    // 1. Se o arquivo existe no caminho limpo original, retorna ele
    if (file_exists($baseDir . $cleanedPath) && is_file($baseDir . $cleanedPath)) {
        return $cleanedPath;
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
            return $pasta . $filename;
        }
    }
    
    // 3. Fallback se não for encontrado em nenhuma pasta
    return $cleanedPath;
}

/**
 * Verifica se todas as faltas e atrasos do registro estão devidamente justificados.
 */
function verificarRegistroTotalmenteJustificado($conn, $regId, $newJustInput = null) {
    $is_virtual = (strpos($regId, 'v_') === 0);
    
    if ($is_virtual) {
        $parts = explode('_', $regId);
        $funcId = $parts[1] ?? null;
        $regDate = $parts[2] ?? null;
        if (!$funcId || !$regDate) return false;
        
        // Carregar funcionário e horários
        $stmtF = $conn->prepare("
            SELECT f.id, h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario, h.tolerancia_entrada, h.tolerancia_saida, f.grade_horarios
            FROM funcionarios f 
            LEFT JOIN horarios h ON f.id_horario = h.id 
            WHERE f.id = ?
        ");
        $stmtF->execute([$funcId]);
        $f = $stmtF->fetch(PDO::FETCH_ASSOC);
        if (!$f) return false;
        
        $regInfo = [
            'id_funcionario' => $funcId,
            'data' => $regDate,
            'primeiro_ponto' => null,
            'segundo_ponto' => null,
            'terceiro_ponto' => null,
            'quarto_ponto' => null,
            'atrasou_primeiro_ponto' => false,
            'atrasou_segundo_ponto' => false,
            'atrasou_terceiro_ponto' => false,
            'atrasou_quarto_ponto' => false,
            'just_ent1' => null,
            'just_sai1' => null,
            'just_ent2' => null,
            'just_sai2' => null,
            'tipo_justificativa' => null,
            'justificativa' => null,
            'primeiro_horario' => $f['primeiro_horario'],
            'segundo_horario' => $f['segundo_horario'],
            'terceiro_horario' => $f['terceiro_horario'],
            'quarto_horario' => $f['quarto_horario'],
            'tolerancia_entrada' => $f['tolerancia_entrada'],
            'tolerancia_saida' => $f['tolerancia_saida'],
            'grade_horarios' => $f['grade_horarios'],
        ];
    } else {
        $getReg = $conn->prepare("SELECT * FROM registros WHERE id = ?");
        $getReg->execute([$regId]);
        $regInfo = $getReg->fetch(PDO::FETCH_ASSOC);
        if (!$regInfo) return false;
        
        // Buscar horários se vazios
        if (empty($regInfo['primeiro_horario']) && empty($regInfo['segundo_horario']) && empty($regInfo['terceiro_horario']) && empty($regInfo['quarto_horario'])) {
            $stmtF = $conn->prepare("
                SELECT h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario, h.tolerancia_entrada, h.tolerancia_saida, f.grade_horarios
                FROM funcionarios f 
                LEFT JOIN horarios h ON f.id_horario = h.id 
                WHERE f.id = ?
            ");
            $stmtF->execute([$regInfo['id_funcionario']]);
            $f = $stmtF->fetch(PDO::FETCH_ASSOC);
            if ($f) {
                $regInfo['primeiro_horario'] = $f['primeiro_horario'];
                $regInfo['segundo_horario'] = $f['segundo_horario'];
                $regInfo['terceiro_horario'] = $f['terceiro_horario'];
                $regInfo['quarto_horario'] = $f['quarto_horario'];
                $regInfo['tolerancia_entrada'] = $f['tolerancia_entrada'];
                $regInfo['tolerancia_saida'] = $f['tolerancia_saida'];
                $regInfo['grade_horarios'] = $f['grade_horarios'];
            }
        }
    }
    
    // Se o colaborador estiver em férias/afastamento, está justificado
    $stmtFerias = $conn->prepare("SELECT COUNT(*) FROM ferias WHERE status IN ('deferido', 'pendente') AND id_funcionario = ? AND ? BETWEEN data_inicio AND data_fim");
    $stmtFerias->execute([$regInfo['id_funcionario'], $regInfo['data']]);
    if ($stmtFerias->fetchColumn() > 0) {
        return true;
    }
    
    // Aplicar grade de horários
    $mapaDias = [
        'Monday' => 'segunda', 'Tuesday' => 'terca', 'Wednesday' => 'quarta',
        'Thursday' => 'quinta', 'Friday' => 'sexta', 'Saturday' => 'sabado', 'Sunday' => 'domingo'
    ];
    $diaSemana = $mapaDias[date('l', strtotime($regInfo['data']))];
    $grade = json_decode($regInfo['grade_horarios'] ?? '[]', true);
    
    if (isset($grade[$diaSemana]) && !empty($grade[$diaSemana])) {
        $diaData = $grade[$diaSemana];
        if (is_array($diaData) && isset($diaData['horario_id']) && !empty($diaData['horario_id'])) {
            // Buscar horário específico
            $stmtH = $conn->prepare("SELECT * FROM horarios WHERE id = ?");
            $stmtH->execute([$diaData['horario_id']]);
            $hEsp = $stmtH->fetch(PDO::FETCH_ASSOC);
            if ($hEsp) {
                $regInfo['primeiro_horario'] = $hEsp['primeiro_horario'];
                $regInfo['segundo_horario'] = $hEsp['segundo_horario'];
                $regInfo['terceiro_horario'] = $hEsp['terceiro_horario'];
                $regInfo['quarto_horario'] = $hEsp['quarto_horario'];
                $regInfo['tolerancia_entrada'] = $hEsp['tolerancia_entrada'];
                $regInfo['tolerancia_saida'] = $hEsp['tolerancia_saida'];
            }
        } else if (is_array($diaData)) {
            $regInfo['primeiro_horario'] = $diaData[0] ?? ($diaData['p1'] ?? null);
            $regInfo['segundo_horario'] = $diaData[1] ?? ($diaData['p2'] ?? null);
            $regInfo['terceiro_horario'] = $diaData[2] ?? ($diaData['p3'] ?? null);
            $regInfo['quarto_horario'] = $diaData[3] ?? ($diaData['p4'] ?? null);
        }
    }
    
    $toleranciaEntrada = isset($regInfo['tolerancia_entrada']) ? (int)$regInfo['tolerancia_entrada'] : 15;
    $toleranciaSaida = isset($regInfo['tolerancia_saida']) ? (int)$regInfo['tolerancia_saida'] : 15;
    $hojeStr = date('Y-m-d');
    $agoraStr = date('H:i:s');
    $regDate = $regInfo['data'];
    
    // Inconsistências
    $e1Inconsistente = false;
    if ($regInfo['primeiro_horario'] && (empty($regInfo['primeiro_ponto']) || strtoupper(trim($regInfo['primeiro_ponto'])) === 'FALTA')) {
        $limite = date('H:i:s', strtotime($regInfo['primeiro_horario']) + ($toleranciaEntrada * 60));
        if ($regDate < $hojeStr || ($regDate == $hojeStr && $agoraStr > $limite)) {
            $e1Inconsistente = true;
        }
    } elseif ($regInfo['primeiro_horario'] && !empty($regInfo['primeiro_ponto']) && strtoupper(trim($regInfo['primeiro_ponto'])) !== 'FALTA') {
        $pontoSeg = strtotime($regInfo['primeiro_ponto']);
        $horaSeg = strtotime($regInfo['primeiro_horario']);
        if ($pontoSeg > ($horaSeg + ($toleranciaEntrada * 60))) {
            $e1Inconsistente = true;
        }
    }
    
    $s1Inconsistente = false;
    if ($regInfo['segundo_horario'] && (empty($regInfo['segundo_ponto']) || strtoupper(trim($regInfo['segundo_ponto'])) === 'FALTA')) {
        if ($regDate < $hojeStr) {
            $s1Inconsistente = true;
        }
    } elseif ($regInfo['segundo_horario'] && !empty($regInfo['segundo_ponto']) && strtoupper(trim($regInfo['segundo_ponto'])) !== 'FALTA') {
        $pontoSeg = strtotime($regInfo['segundo_ponto']);
        $horaSeg = strtotime($regInfo['segundo_horario']);
        if ($pontoSeg < ($horaSeg - ($toleranciaSaida * 60))) {
            $s1Inconsistente = true;
        }
    }
    
    $e2Inconsistente = false;
    if ($regInfo['terceiro_horario'] && (empty($regInfo['terceiro_ponto']) || strtoupper(trim($regInfo['terceiro_ponto'])) === 'FALTA')) {
        $limite = date('H:i:s', strtotime($regInfo['terceiro_horario']) + ($toleranciaEntrada * 60));
        if ($regDate < $hojeStr || ($regDate == $hojeStr && $agoraStr > $limite)) {
            $e2Inconsistente = true;
        }
    } elseif ($regInfo['terceiro_horario'] && !empty($regInfo['terceiro_ponto']) && strtoupper(trim($regInfo['terceiro_ponto'])) !== 'FALTA') {
        $pontoSeg = strtotime($regInfo['terceiro_ponto']);
        $horaSeg = strtotime($regInfo['terceiro_horario']);
        if ($pontoSeg > ($horaSeg + ($toleranciaEntrada * 60))) {
            $e2Inconsistente = true;
        }
    }
    
    $s2Inconsistente = false;
    if ($regInfo['quarto_horario'] && (empty($regInfo['quarto_ponto']) || strtoupper(trim($regInfo['quarto_ponto'])) === 'FALTA')) {
        if ($regDate < $hojeStr) {
            $s2Inconsistente = true;
        }
    } elseif ($regInfo['quarto_horario'] && !empty($regInfo['quarto_ponto']) && strtoupper(trim($regInfo['quarto_ponto'])) !== 'FALTA') {
        $pontoSeg = strtotime($regInfo['quarto_ponto']);
        $horaSeg = strtotime($regInfo['quarto_horario']);
        if ($pontoSeg < ($horaSeg - ($toleranciaSaida * 60))) {
            $s2Inconsistente = true;
        }
    }
    
    // Obter justificativas
    $jEnt1 = ($newJustInput && isset($newJustInput['just_ent1'])) ? $newJustInput['just_ent1'] : ($regInfo['just_ent1'] ?? '');
    $jSai1 = ($newJustInput && isset($newJustInput['just_sai1'])) ? $newJustInput['just_sai1'] : ($regInfo['just_sai1'] ?? '');
    $jEnt2 = ($newJustInput && isset($newJustInput['just_ent2'])) ? $newJustInput['just_ent2'] : ($regInfo['just_ent2'] ?? '');
    $jSai2 = ($newJustInput && isset($newJustInput['just_sai2'])) ? $newJustInput['just_sai2'] : ($regInfo['just_sai2'] ?? '');
    $tJust = ($newJustInput && isset($newJustInput['tipo_justificativa'])) ? $newJustInput['tipo_justificativa'] : ($regInfo['tipo_justificativa'] ?? '');
    $justObs = ($newJustInput && isset($newJustInput['justificativa'])) ? $newJustInput['justificativa'] : ($regInfo['justificativa'] ?? '');
    
    // Validar se cada inconsistência ativa está devidamente justificada (não pode ser vazia, nula ou Indeferida)
    if ($e1Inconsistente && (empty($jEnt1) || $jEnt1 === 'null' || trim($jEnt1) === '' || strtolower(trim($jEnt1)) === 'indeferido')) return false;
    if ($s1Inconsistente && (empty($jSai1) || $jSai1 === 'null' || trim($jSai1) === '' || strtolower(trim($jSai1)) === 'indeferido')) return false;
    if ($e2Inconsistente && (empty($jEnt2) || $jEnt2 === 'null' || trim($jEnt2) === '' || strtolower(trim($jEnt2)) === 'indeferido')) return false;
    if ($s2Inconsistente && (empty($jSai2) || $jSai2 === 'null' || trim($jSai2) === '' || strtolower(trim($jSai2)) === 'indeferido')) return false;
    
    return true;
}

try {
    $conn = Database::getConnection();

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'POST') {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $user_name = $_SESSION['user_name'] ?? '';
        $user_setor = $_SESSION['user_setor'] ?? '';
        $user_level = $_SESSION['user_level'] ?? 3;

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input && !empty($_POST)) {
            $input = $_POST;
        }
        
        if (isset($input['action']) && $input['action'] == 'justificar') {
            $id = $input['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID inválido.']);
                exit;
            }

            $is_virtual = (strpos($id, 'v_') === 0);
            $virtual_data = null;
            $virtual_func = null;

            if ($is_virtual) {
                // Formato: v_{id_funcionario}_{data}
                $parts = explode('_', $id);
                if (count($parts) >= 3) {
                    $virtual_func = $parts[1];
                    $virtual_data = $parts[2];
                } else {
                    echo json_encode(['success' => false, 'message' => 'ID virtual inválido.']);
                    exit;
                }
            }

            // Verificar se o registro está finalizado e se o usuário tem permissão para alterar
            $strict = isset($_POST['strict_sector']) && $_POST['strict_sector'] == '1';
            if ($strict) {
                $is_super_api = ($user_level == 1);
            } else {
                $is_super_api = ($user_level == 1) || in_array(strtolower(trim($user_name)), ['corsin', 'crh']) || in_array(strtolower(trim($user_setor)), ['corsin', 'crh']);
            }

            if (!$is_super_api && !$is_virtual) {
                $checkStmt = $conn->prepare("SELECT status_crh FROM registros WHERE id = :id");
                $checkStmt->execute([':id' => $id]);
                $current = $checkStmt->fetch(PDO::FETCH_ASSOC);
                if ($current && $current['status_crh'] === 'deferido') {
                    echo json_encode(['success' => false, 'message' => 'Este registro já foi DEFERIDO pelo CRH e não pode ser alterado por gestores de setor.']);
                    exit;
                }
            }

            // --- NOVA LÓGICA DE MÚLTIPLOS ANEXOS ---
            $anexosAtuais = [];
            if (!$is_virtual) {
                $getOld = $conn->prepare("SELECT anexo_justificativa, status_turno1, status_turno2 FROM registros WHERE id = :id");
                $getOld->execute([':id' => $id]);
                $oldRow = $getOld->fetch(PDO::FETCH_ASSOC);
                if ($oldRow && !empty($oldRow['anexo_justificativa'])) {
                    $decoded = json_decode($oldRow['anexo_justificativa'], true);
                    if (is_array($decoded)) {
                        $anexosAtuais = $decoded;
                    } else {
                        $anexosAtuais = [$oldRow['anexo_justificativa']]; // Formato antigo (string simples)
                    }
                }
            }

            // Pull any employee-submitted justification files from PWA (funadponto)
            $func_id_for_pull = null;
            $data_for_pull = null;
            if ($is_virtual) {
                $func_id_for_pull = $virtual_func;
                $data_for_pull = $virtual_data;
            } else {
                $getFuncData = $conn->prepare("SELECT id_funcionario, data FROM registros WHERE id = :id");
                $getFuncData->execute([':id' => $id]);
                $fdRow = $getFuncData->fetch(PDO::FETCH_ASSOC);
                if ($fdRow) {
                    $func_id_for_pull = $fdRow['id_funcionario'];
                    $data_for_pull = $fdRow['data'];
                }
            }

            if ($func_id_for_pull && $data_for_pull) {
                try {
                    $pullStmt = $conn->prepare("SELECT anexos FROM justificativas WHERE id_funcionario = :f_id AND data_registro = :dt");
                    $pullStmt->execute([':f_id' => $func_id_for_pull, ':dt' => $data_for_pull]);
                    while ($jRow = $pullStmt->fetch(PDO::FETCH_ASSOC)) {
                        if (!empty($jRow['anexos'])) {
                            $jAnexos = json_decode($jRow['anexos'], true);
                            if (is_array($jAnexos)) {
                                foreach ($jAnexos as $p) {
                                    $cleaned_p = preg_replace('/^\.\.\/sisponto\//', '', $p);
                                    if (!in_array($cleaned_p, $anexosAtuais)) {
                                        $anexosAtuais[] = $cleaned_p;
                                    }
                                }
                            }
                        }
                    }
                } catch (Exception $e) {}
            }

            // Remover anexos marcados (se enviado pelo frontend)
            if (isset($input['anexos_removidos']) && !empty($input['anexos_removidos'])) {
                $removidosInput = $input['anexos_removidos'];
                $removidos = [];
                
                if (is_array($removidosInput)) {
                    $removidos = $removidosInput;
                } else {
                    $json = json_decode($removidosInput, true);
                    if (is_array($json)) {
                        $removidos = $json;
                    } else {
                        $removidos = explode(',', $removidosInput);
                    }
                }

                $anexosAtuais = array_values(array_filter($anexosAtuais, function($val) use ($removidos) {
                    return !in_array($val, $removidos);
                }));
            }

            // Adicionar Novos Arquivos
            if (isset($_FILES['anexo'])) {
                $files = $_FILES['anexo'];
                // Normalizar estrutura $_FILES para múltiplos ou único
                if (!is_array($files['name'])) {
                    $fileList = [
                        ['name' => $files['name'], 'type' => $files['type'], 'tmp_name' => $files['tmp_name'], 'error' => $files['error'], 'size' => $files['size']]
                    ];
                } else {
                    $fileList = [];
                    foreach ($files['name'] as $k => $v) {
                        $fileList[] = ['name' => $files['name'][$k], 'type' => $files['type'][$k], 'tmp_name' => $files['tmp_name'][$k], 'error' => $files['error'][$k], 'size' => $files['size'][$k]];
                    }
                }

                $uploadDir = '../uploads/justificativas/';
                if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

                foreach ($fileList as $f) {
                    if ($f['error'] === UPLOAD_ERR_OK) {
                        if ($f['size'] > 3 * 1024 * 1024) continue; // Pula se > 3MB
                        
                        $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                        $filename = uniqid('just_') . ($ext ? '.' . strtolower($ext) : '');
                        $destination = $uploadDir . $filename;
                        if (move_uploaded_file($f['tmp_name'], $destination)) {
                            $anexosAtuais[] = 'uploads/justificativas/' . $filename;
                        }
                    }
                }
            }

            // Reaproveitar Anexo do Comunicado (Se solicitado e não repetido)
            if (isset($input['usar_anexo_comunicado']) && $input['usar_anexo_comunicado'] == '1') {
                if (!$is_virtual) {
                    $getAnexo = $conn->prepare("SELECT anexo_comunicado FROM registros WHERE id = :id");
                    $getAnexo->execute([':id' => $id]);
                    $comRow = $getAnexo->fetch(PDO::FETCH_ASSOC);
                    if ($comRow && !empty($comRow['anexo_comunicado'])) {
                        $comPaths = json_decode($comRow['anexo_comunicado'], true);
                        if (!is_array($comPaths)) $comPaths = [$comRow['anexo_comunicado']];
                        
                        foreach ($comPaths as $cp) {
                            // Só adiciona se não estiver na lista de removidos e não estiver já na lista de atuais
                            if (!in_array($cp, $removidos) && !in_array($cp, $anexosAtuais)) {
                                $anexosAtuais[] = $cp;
                            }
                        }
                    }
                }
            }

            // --- CONSOLIDAR E UNIFICAR TEXTOS DE JUSTIFICATIVA DO FUNCIONÁRIO ---
            $justificativaOriginal = $input['justificativa'] ?? '';
            $pwaTexts = [];
            
            // 1. Buscar do PWA (tabela justificativas)
            if ($func_id_for_pull && $data_for_pull) {
                try {
                    $pullTxtStmt = $conn->prepare("SELECT texto, tipo_justificativa FROM justificativas WHERE id_funcionario = :f_id AND data_registro = :dt");
                    $pullTxtStmt->execute([':f_id' => $func_id_for_pull, ':dt' => $data_for_pull]);
                    while ($jRow = $pullTxtStmt->fetch(PDO::FETCH_ASSOC)) {
                        if (!empty($jRow['texto'])) {
                            $tipoStr = !empty($jRow['tipo_justificativa']) ? $jRow['tipo_justificativa'] : 'Justificativa';
                            $pwaTexts[] = "[" . $tipoStr . " do Servidor]: " . $jRow['texto'];
                        }
                    }
                } catch (Exception $e) {}
            }
            
            // 2. Buscar Comunicado do registro (se não for virtual)
            if (!$is_virtual) {
                try {
                    $comStmt = $conn->prepare("SELECT comunicado FROM registros WHERE id = :id");
                    $comStmt->execute([':id' => $id]);
                    $comTxt = $comStmt->fetchColumn();
                    if (!empty($comTxt)) {
                        $pwaTexts[] = "[Aviso do Funcionário]: " . $comTxt;
                    }
                } catch (Exception $e) {}
            }
            
            // 3. Concatenar de forma amigável ao texto do gestor
            if (!empty($pwaTexts)) {
                $pwaTextsCombined = implode(" | ", $pwaTexts);
                if (empty($justificativaOriginal)) {
                    $justificativaOriginal = $pwaTextsCombined;
                } else {
                    if (strpos($justificativaOriginal, $pwaTextsCombined) === false) {
                        $justificativaOriginal = $justificativaOriginal . "\n" . $pwaTextsCombined;
                    }
                }
            }
            
            $input['justificativa'] = !empty($justificativaOriginal) ? $justificativaOriginal : null;

            $finalAnexoJson = !empty($anexosAtuais) ? json_encode(array_values(array_unique($anexosAtuais))) : null;

            // Calcular se o registro está totalmente justificado
            $totalmenteJustificado = verificarRegistroTotalmenteJustificado($conn, $id, $input);
            $enviadoCrhVal = $totalmenteJustificado ? 1 : 0;

            if ($is_virtual) {
                // Criar NOVO registro para dia sem batida
                $sql = "INSERT INTO registros (
                    id_funcionario, data, justificativa, tipo_justificativa, 
                    status_turno1, status_turno2, just_ent1, just_sai1, just_ent2, just_sai2,
                    aviso, anexo_justificativa, created_at, updated_at, gestor_nome, gestor_setor,
                    enviado_crh, status_crh
                ) VALUES (
                    :func_id, :data, :justificativa, :tipo_justificativa,
                    :st1, :st2, :j_e1, :j_s1, :j_e2, :j_s2,
                    :aviso, :anexo, NOW(), NOW(), :gn, :gs,
                    :enviado_crh, 'pendente'
                )";
                $params = [
                    ':func_id' => $virtual_func,
                    ':data' => $virtual_data,
                    ':justificativa' => $input['justificativa'] ?? null,
                    ':tipo_justificativa' => $input['tipo_justificativa'] ?? null,
                    ':st1' => $input['status_turno1'] ?? '',
                    ':st2' => $input['status_turno2'] ?? '',
                    ':j_e1' => $input['just_ent1'] ?? null,
                    ':j_s1' => $input['just_sai1'] ?? null,
                    ':j_e2' => $input['just_ent2'] ?? null,
                    ':j_s2' => $input['just_sai2'] ?? null,
                    ':aviso' => $input['aviso'] ?? null,
                    ':anexo' => $finalAnexoJson,
                    ':gn' => $user_name,
                    ':gs' => $user_setor,
                    ':enviado_crh' => $enviadoCrhVal
                ];
            } else {
                $sql = "UPDATE registros SET 
                    justificativa = :justificativa, tipo_justificativa = :tipo_justificativa, 
                    status_turno1 = :st1, status_turno2 = :st2, 
                    just_ent1 = :j_e1, just_sai1 = :j_s1, just_ent2 = :j_e2, just_sai2 = :j_s2,
                    aviso = :aviso, anexo_justificativa = :anexo,
                    enviado_crh = :enviado_crh, status_crh = CASE WHEN status_crh = 'indeferido' THEN 'pendente' ELSE status_crh END,
                    updated_at = NOW(), gestor_nome = :gn, gestor_setor = :gs WHERE id = :id";
                
                $params = [
                    ':justificativa' => $input['justificativa'] ?? null,
                    ':tipo_justificativa' => $input['tipo_justificativa'] ?? null,
                    ':st1' => $input['status_turno1'] ?? ($oldRow['status_turno1'] ?? ''),
                    ':st2' => $input['status_turno2'] ?? ($oldRow['status_turno2'] ?? ''),
                    ':j_e1' => $input['just_ent1'] ?? null,
                    ':j_s1' => $input['just_sai1'] ?? null,
                    ':j_e2' => $input['just_ent2'] ?? null,
                    ':j_s2' => $input['just_sai2'] ?? null,
                    ':aviso' => $input['aviso'] ?? null,
                    ':anexo' => $finalAnexoJson,
                    ':gn' => $user_name,
                    ':gs' => $user_setor,
                    ':enviado_crh' => $enviadoCrhVal,
                    ':id' => $id
                ];
            }
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode(['success' => true, 'message' => 'Justificativa salva com sucesso!']);
            exit;
        }

        if (isset($input['action']) && $input['action'] == 'enviar_crh') {
            $ids = $input['ids'] ?? [];
            if (!is_array($ids) || empty($ids)) {
                echo json_encode(['success' => false, 'message' => 'Nenhum registro selecionado.']);
                exit;
            }

            // Validar se todos os registros selecionados estão totalmente justificados
            foreach ($ids as $id) {
                if (!verificarRegistroTotalmenteJustificado($conn, $id)) {
                    $colaboradorNome = "Colaborador";
                    $dataFormatada = "";
                    if (strpos($id, 'v_') === 0) {
                        $parts = explode('_', $id);
                        $fId = $parts[1] ?? '';
                        $regDate = $parts[2] ?? '';
                        $dataFormatada = $regDate ? date('d/m/Y', strtotime($regDate)) : '';
                        $stmtN = $conn->prepare("SELECT nome FROM funcionarios WHERE id = ?");
                        $stmtN->execute([$fId]);
                        $colaboradorNome = $stmtN->fetchColumn() ?: "Colaborador";
                    } else {
                        $stmtN = $conn->prepare("SELECT f.nome, r.data FROM registros r JOIN funcionarios f ON r.id_funcionario = f.id WHERE r.id = ?");
                        $stmtN->execute([$id]);
                        $rowN = $stmtN->fetch(PDO::FETCH_ASSOC);
                        if ($rowN) {
                            $colaboradorNome = $rowN['nome'];
                            $dataFormatada = date('d/m/Y', strtotime($rowN['data']));
                        }
                    }
                    echo json_encode(['success' => false, 'message' => "Não é possível concluir o envio ao CRH. O registro de {$colaboradorNome} em {$dataFormatada} possui marcações com falta ou atraso pendentes de justificativa específica."]);
                    exit;
                }
            }
            
            $realIds = [];
            foreach ($ids as $id) {
                if (strpos($id, 'v_') === 0) {
                    $parts = explode('_', $id);
                    if (count($parts) >= 3) {
                        $fId = $parts[1];
                        $data = $parts[2];
                        
                        // Verificar se já não existe (prevenção contra duplicidade se o usuário clicar rápido ou algo assim)
                        $check = $conn->prepare("SELECT id FROM registros WHERE id_funcionario = ? AND data = ?");
                        $check->execute([$fId, $data]);
                        $dbId = $check->fetchColumn();
                        
                        if (!$dbId) {
                            $ins = $conn->prepare("INSERT INTO registros (id_funcionario, data, enviado_crh, status_crh, created_at, updated_at, gestor_nome, gestor_setor) VALUES (?, ?, TRUE, 'pendente', NOW(), NOW(), ?, ?)");
                            $ins->execute([$fId, $data, $user_name, $user_setor]);
                            $dbId = $conn->lastInsertId();
                        }
                        
                        if ($dbId) $realIds[] = (int)$dbId;
                    }
                } else {
                    $realIds[] = (int)$id;
                }
            }
            
            if (!empty($realIds)) {
                $placeholders = implode(',', array_fill(0, count($realIds), '?'));
                $stmt = $conn->prepare("UPDATE registros SET enviado_crh = TRUE, status_crh = CASE WHEN status_crh = 'indeferido' THEN 'pendente' ELSE status_crh END, updated_at = NOW(), gestor_nome = ?, gestor_setor = ? WHERE id IN ($placeholders)");
                $stmt->execute(array_merge([$user_name, $user_setor], $realIds));
            }
            
            echo json_encode(['success' => true, 'message' => 'Envio ao CRH realizado com sucesso!']);
            exit;
        }

        if (isset($input['action']) && $input['action'] == 'deferir_crh') {
            $id = $input['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID inválido.']);
                exit;
            }

            if (session_status() === PHP_SESSION_NONE) session_start();
            $user_name = $_SESSION['user_name'] ?? 'Sistema';
            $user_setor = $_SESSION['user_setor'] ?? '';
            $user_level = $_SESSION['user_level'] ?? 3;

            // Converter ID virtual em registro real se necessário
            if (strpos($id, 'v_') === 0) {
                $parts = explode('_', $id);
                if (count($parts) >= 3) {
                    $virtual_func = $parts[1];
                    $virtual_data = $parts[2];
                    
                    // Verificar se já não existe
                    $check = $conn->prepare("SELECT id FROM registros WHERE id_funcionario = ? AND data = ?");
                    $check->execute([$virtual_func, $virtual_data]);
                    $dbId = $check->fetchColumn();
                    
                    if (!$dbId) {
                        $ins = $conn->prepare("INSERT INTO registros (id_funcionario, data, enviado_crh, status_crh, created_at, updated_at, gestor_nome, gestor_setor) VALUES (?, ?, TRUE, 'pendente', NOW(), NOW(), ?, ?)");
                        $ins->execute([$virtual_func, $virtual_data, $user_name, $user_setor]);
                        $dbId = $conn->lastInsertId();
                    }
                    $id = $dbId;
                } else {
                    echo json_encode(['success' => false, 'message' => 'ID virtual inválido.']);
                    exit;
                }
            }
            
            $is_super_api = ($user_level == 1) || in_array(strtolower(trim($user_name)), ['corsin', 'crh']) || in_array(strtolower(trim($user_setor)), ['corsin', 'crh']);

            // Verificar se já existe uma decisão e se o usuário é admin/gestor para alterar
            $stmt = $conn->prepare("SELECT status_crh, justificativa FROM registros WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row && !empty($row['status_crh']) && $row['status_crh'] !== 'pendente' && !$is_super_api) {
                echo json_encode(['success' => false, 'message' => 'Apenas administradores ou gestores do CRH podem alterar uma decisão já tomada.']);
                exit;
            }

            $just = $row ? $row['justificativa'] : '';
            
            if (!empty($just) && strpos($just, '[INDEFERIDO PELO CRH]:') !== false) {
                $lines = explode("\n", $just);
                $filteredLines = array_filter($lines, function($line) {
                    return strpos($line, '[INDEFERIDO PELO CRH]:') === false;
                });
                $just = trim(implode("\n", $filteredLines));
            }

            // --- SINCRONIZAR E UNIFICAR TEXTOS E ANEXOS DO FUNCIONÁRIO NO DEFERIMENTO ---
            $regIdFunc = null;
            $regDataVal = null;
            $regComunicado = null;
            $regAnexoCom = null;
            $regAnexoJust = null;
            
            $getRegInfo = $conn->prepare("SELECT id_funcionario, data, comunicado, anexo_comunicado, anexo_justificativa FROM registros WHERE id = :id");
            $getRegInfo->execute([':id' => $id]);
            $regInfoRow = $getRegInfo->fetch(PDO::FETCH_ASSOC);
            if ($regInfoRow) {
                $regIdFunc = $regInfoRow['id_funcionario'];
                $regDataVal = $regInfoRow['data'];
                $regComunicado = $regInfoRow['comunicado'];
                $regAnexoCom = $regInfoRow['anexo_comunicado'];
                $regAnexoJust = $regInfoRow['anexo_justificativa'];
            }
            
            $pwaTexts = [];
            $pwaAnexos = [];
            
            if ($regIdFunc && $regDataVal) {
                try {
                    $pullTxtStmt = $conn->prepare("SELECT texto, tipo_justificativa, anexos FROM justificativas WHERE id_funcionario = :f_id AND data_registro = :dt");
                    $pullTxtStmt->execute([':f_id' => $regIdFunc, ':dt' => $regDataVal]);
                    while ($jRow = $pullTxtStmt->fetch(PDO::FETCH_ASSOC)) {
                        if (!empty($jRow['texto'])) {
                            $tipoStr = !empty($jRow['tipo_justificativa']) ? $jRow['tipo_justificativa'] : 'Justificativa';
                            $pwaTexts[] = "[" . $tipoStr . " do Servidor]: " . $jRow['texto'];
                        }
                        if (!empty($jRow['anexos'])) {
                            $decoded = json_decode($jRow['anexos'], true);
                            if (is_array($decoded)) {
                                $pwaAnexos = array_merge($pwaAnexos, $decoded);
                            } else {
                                $pwaAnexos[] = $jRow['anexos'];
                            }
                        }
                    }
                } catch (Exception $e) {}
            }
            
            if (!empty($regComunicado)) {
                $pwaTexts[] = "[Aviso do Funcionário]: " . $regComunicado;
            }
            
            // Concatenar textos se houver
            if (!empty($pwaTexts)) {
                $pwaTextsCombined = implode(" | ", $pwaTexts);
                if (empty($just)) {
                    $just = $pwaTextsCombined;
                } else {
                    if (strpos($just, $pwaTextsCombined) === false) {
                        $just = $just . "\n" . $pwaTextsCombined;
                    }
                }
            }
            
            // Unificar anexos
            $anexosAtuais = [];
            if (!empty($regAnexoJust)) {
                $decoded = json_decode($regAnexoJust, true);
                if (is_array($decoded)) $anexosAtuais = $decoded;
                else $anexosAtuais[] = $regAnexoJust;
            }
            if (!empty($regAnexoCom)) {
                $decoded = json_decode($regAnexoCom, true);
                if (is_array($decoded)) $pwaAnexos = array_merge($pwaAnexos, $decoded);
                else $pwaAnexos[] = $regAnexoCom;
            }
            if (!empty($pwaAnexos)) {
                foreach ($pwaAnexos as $p) {
                    $cleaned_p = preg_replace('/^\.\.\/sisponto\//', '', $p);
                    if (!in_array($cleaned_p, $anexosAtuais)) {
                        $anexosAtuais[] = $cleaned_p;
                    }
                }
            }
            foreach ($anexosAtuais as &$path) {
                $path = verificarCaminhoAnexo($path);
            }
            unset($path);
            $anexosAtuais = array_values(array_unique(array_filter($anexosAtuais)));
            $finalAnexoJson = !empty($anexosAtuais) ? json_encode($anexosAtuais) : null;

            // Obter histórico atual
            $stmtHist = $conn->prepare("SELECT crh_history FROM registros WHERE id = :id");
            $stmtHist->execute([':id' => $id]);
            $histJson = $stmtHist->fetchColumn();
            $history = json_decode($histJson ?: '[]', true);
            
            // Adicionar nova entrada ao histórico
            $history[] = [
                'action' => 'deferido',
                'user' => $user_name,
                'setor' => $user_setor,
                'horario' => $input['horario'] ?? null,
                'timestamp' => date('Y-m-d H:i:s'),
                'motivo' => ''
            ];

            $horario = $input['horario'] ?? null;
            $columnUpdate = "";
            $simulatedInput = [];
            if ($horario) {
                $columnMap = ['E1' => 'just_ent1', 'S1' => 'just_sai1', 'E2' => 'just_ent2', 'S2' => 'just_sai2'];
                if (isset($columnMap[$horario])) {
                    $col = $columnMap[$horario];
                    $columnUpdate = ", $col = 'Deferido'";
                    $simulatedInput[$col] = 'Deferido';
                }
            }

            // Calcular se o registro ficará totalmente justificado com este deferimento
            // IMPORTANTE: enviado_crh só é TRUE quando TODOS os turnos faltosos estiverem justificados.
            // Além disso, o status_crh só sobe para 'deferido' quando tudo estiver OK, pois o filtro
            // SQL de "enviados" usa (enviado_crh=TRUE OR status_crh IN ('deferido','indeferido')).
            $totalmenteJustificado = verificarRegistroTotalmenteJustificado($conn, $id, $simulatedInput);
            $enviadoCrhVal = $totalmenteJustificado ? 1 : 0;

            // Só promover status_crh para 'deferido' quando todos os turnos estiverem devidamente tratados.
            // Enquanto houver turnos vermelhos pendentes, manter 'pendente' para não vazar ao filtro de enviados.
            $newStatus = ($is_super_api && $totalmenteJustificado) ? 'deferido' : 'pendente';
            $stmt = $conn->prepare("UPDATE registros SET status_crh = :newStatus, enviado_crh = :enviadoCrh, justificativa = :just, anexo_justificativa = :anexo, crh_user = :user, crh_updated_at = NOW(), crh_history = :history, updated_at = NOW() $columnUpdate WHERE id = :id");
            $stmt->execute([':newStatus' => $newStatus, ':enviadoCrh' => $enviadoCrhVal, ':id' => $id, ':just' => $just, ':anexo' => $finalAnexoJson, ':user' => $user_name, ':history' => json_encode($history)]);

            // Obter data e id_funcionario do registro para sincronizar com justificativas
            $stmtGetReg = $conn->prepare("SELECT id_funcionario, data FROM registros WHERE id = :id");
            $stmtGetReg->execute([':id' => $id]);
            $regData = $stmtGetReg->fetch(PDO::FETCH_ASSOC);
            if ($regData) {
                try {
                    $stmtUpdJust = $conn->prepare("UPDATE justificativas SET status = 'deferido', observacao_admin = :obs, updated_at = NOW() WHERE id_funcionario = :fId AND data_registro = :dt");
                    $stmtUpdJust->execute([':obs' => 'Aprovado via sisponto', ':fId' => $regData['id_funcionario'], ':dt' => $regData['data']]);
                } catch (Exception $e) {
                    // Ignora se der erro
                }
            }

            echo json_encode(['success' => true, 'message' => 'Justificativa deferida com sucesso.']);
            exit;
        }

        if (isset($input['action']) && $input['action'] == 'indeferir_crh') {
            $id = $input['id'] ?? null;
            $motivo = $input['motivo'] ?? '';
            if (!$id || empty(trim($motivo))) {
                echo json_encode(['success' => false, 'message' => 'Motivo da recusa é obrigatório.']);
                exit;
            }

            if (session_status() === PHP_SESSION_NONE) session_start();
            $user_name = $_SESSION['user_name'] ?? 'Sistema';
            $user_setor = $_SESSION['user_setor'] ?? '';
            $user_level = $_SESSION['user_level'] ?? 3;

            // Converter ID virtual em registro real se necessário
            if (strpos($id, 'v_') === 0) {
                $parts = explode('_', $id);
                if (count($parts) >= 3) {
                    $virtual_func = $parts[1];
                    $virtual_data = $parts[2];
                    
                    // Verificar se já não existe
                    $check = $conn->prepare("SELECT id FROM registros WHERE id_funcionario = ? AND data = ?");
                    $check->execute([$virtual_func, $virtual_data]);
                    $dbId = $check->fetchColumn();
                    
                    if (!$dbId) {
                        $ins = $conn->prepare("INSERT INTO registros (id_funcionario, data, enviado_crh, status_crh, created_at, updated_at, gestor_nome, gestor_setor) VALUES (?, ?, TRUE, 'pendente', NOW(), NOW(), ?, ?)");
                        $ins->execute([$virtual_func, $virtual_data, $user_name, $user_setor]);
                        $dbId = $conn->lastInsertId();
                    }
                    $id = $dbId;
                } else {
                    echo json_encode(['success' => false, 'message' => 'ID virtual inválido.']);
                    exit;
                }
            }
            
            $is_super_api = ($user_level == 1) || in_array(strtolower(trim($user_name)), ['corsin', 'crh']) || in_array(strtolower(trim($user_setor)), ['corsin', 'crh']);

            $stmt = $conn->prepare("SELECT status_crh, justificativa FROM registros WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row && !empty($row['status_crh']) && $row['status_crh'] !== 'pendente' && !$is_super_api) {
                echo json_encode(['success' => false, 'message' => 'Apenas administradores ou gestores do CRH podem alterar uma decisão já tomada.']);
                exit;
            }

            $just = $row ? $row['justificativa'] : '';
            
            if (!empty($just) && strpos($just, '[INDEFERIDO PELO CRH]:') !== false) {
                $lines = explode("\n", $just);
                $filteredLines = array_filter($lines, function($line) {
                    return strpos($line, '[INDEFERIDO PELO CRH]:') === false;
                });
                $just = trim(implode("\n", $filteredLines));
            }

            // --- SINCRONIZAR E UNIFICAR TEXTOS E ANEXOS DO FUNCIONÁRIO NO INDEFERIMENTO ---
            $regIdFunc = null;
            $regDataVal = null;
            $regComunicado = null;
            $regAnexoCom = null;
            $regAnexoJust = null;
            
            $getRegInfo = $conn->prepare("SELECT id_funcionario, data, comunicado, anexo_comunicado, anexo_justificativa FROM registros WHERE id = :id");
            $getRegInfo->execute([':id' => $id]);
            $regInfoRow = $getRegInfo->fetch(PDO::FETCH_ASSOC);
            if ($regInfoRow) {
                $regIdFunc = $regInfoRow['id_funcionario'];
                $regDataVal = $regInfoRow['data'];
                $regComunicado = $regInfoRow['comunicado'];
                $regAnexoCom = $regInfoRow['anexo_comunicado'];
                $regAnexoJust = $regInfoRow['anexo_justificativa'];
            }
            
            $pwaTexts = [];
            $pwaAnexos = [];
            
            if ($regIdFunc && $regDataVal) {
                try {
                    $pullTxtStmt = $conn->prepare("SELECT texto, tipo_justificativa, anexos FROM justificativas WHERE id_funcionario = :f_id AND data_registro = :dt");
                    $pullTxtStmt->execute([':f_id' => $regIdFunc, ':dt' => $regDataVal]);
                    while ($jRow = $pullTxtStmt->fetch(PDO::FETCH_ASSOC)) {
                        if (!empty($jRow['texto'])) {
                            $tipoStr = !empty($jRow['tipo_justificativa']) ? $jRow['tipo_justificativa'] : 'Justificativa';
                            $pwaTexts[] = "[" . $tipoStr . " do Servidor]: " . $jRow['texto'];
                        }
                        if (!empty($jRow['anexos'])) {
                            $decoded = json_decode($jRow['anexos'], true);
                            if (is_array($decoded)) {
                                $pwaAnexos = array_merge($pwaAnexos, $decoded);
                            } else {
                                $pwaAnexos[] = $jRow['anexos'];
                            }
                        }
                    }
                } catch (Exception $e) {}
            }
            
            if (!empty($regComunicado)) {
                $pwaTexts[] = "[Aviso do Funcionário]: " . $regComunicado;
            }
            
            // Concatenar textos se houver
            if (!empty($pwaTexts)) {
                $pwaTextsCombined = implode(" | ", $pwaTexts);
                if (empty($just)) {
                    $just = $pwaTextsCombined;
                } else {
                    if (strpos($just, $pwaTextsCombined) === false) {
                        $just = $just . "\n" . $pwaTextsCombined;
                    }
                }
            }
            
            // Unificar anexos
            $anexosAtuais = [];
            if (!empty($regAnexoJust)) {
                $decoded = json_decode($regAnexoJust, true);
                if (is_array($decoded)) $anexosAtuais = $decoded;
                else $anexosAtuais[] = $regAnexoJust;
            }
            if (!empty($regAnexoCom)) {
                $decoded = json_decode($regAnexoCom, true);
                if (is_array($decoded)) $pwaAnexos = array_merge($pwaAnexos, $decoded);
                else $pwaAnexos[] = $regAnexoCom;
            }
            if (!empty($pwaAnexos)) {
                foreach ($pwaAnexos as $p) {
                    $cleaned_p = preg_replace('/^\.\.\/sisponto\//', '', $p);
                    if (!in_array($cleaned_p, $anexosAtuais)) {
                        $anexosAtuais[] = $cleaned_p;
                    }
                }
            }
            foreach ($anexosAtuais as &$path) {
                $path = verificarCaminhoAnexo($path);
            }
            unset($path);
            $anexosAtuais = array_values(array_unique(array_filter($anexosAtuais)));
            $finalAnexoJson = !empty($anexosAtuais) ? json_encode($anexosAtuais) : null;

            $reasonTag = "[INDEFERIDO PELO CRH]: " . trim($motivo);
            $newJust = !empty($just) ? $just . "\n" . $reasonTag : $reasonTag;

            // Obter histórico atual
            $stmtHist = $conn->prepare("SELECT crh_history FROM registros WHERE id = :id");
            $stmtHist->execute([':id' => $id]);
            $histJson = $stmtHist->fetchColumn();
            $history = json_decode($histJson ?: '[]', true);
            
            // Adicionar nova entrada ao histórico
            $history[] = [
                'action' => 'indeferido',
                'user' => $user_name,
                'setor' => $user_setor,
                'horario' => $input['horario'] ?? null,
                'timestamp' => date('Y-m-d H:i:s'),
                'motivo' => trim($motivo)
            ];

            $horario = $input['horario'] ?? null;
            $columnUpdate = "";
            $simulatedInput = [];
            if ($horario) {
                $columnMap = ['E1' => 'just_ent1', 'S1' => 'just_sai1', 'E2' => 'just_ent2', 'S2' => 'just_sai2'];
                if (isset($columnMap[$horario])) {
                    $col = $columnMap[$horario];
                    $columnUpdate = ", $col = 'Indeferido'";
                    $simulatedInput[$col] = 'Indeferido';
                }
            }

            // Calcular se o registro ficará totalmente justificado com este indeferimento
            // IMPORTANTE: enviado_crh só é TRUE quando TODOS os turnos faltosos estiverem justificados.
            // O status_crh só sobe para 'indeferido' quando tudo estiver tratado, para não vazar ao
            // filtro de enviados via (status_crh IN ('deferido','indeferido')) enquanto há turnos pendentes.
            $totalmenteJustificado = verificarRegistroTotalmenteJustificado($conn, $id, $simulatedInput);
            
            // Só promover status_crh para 'indeferido' quando todos os turnos estiverem devidamente tratados.
            $newStatus = ($is_super_api && $totalmenteJustificado) ? 'indeferido' : 'pendente';
            $enviadoCrhVal = $totalmenteJustificado ? 1 : 0;

            $stmt = $conn->prepare("
                UPDATE registros 
                SET status_crh = :newStatus,
                    enviado_crh = :enviadoCrh,
                    justificativa = :just,
                    anexo_justificativa = :anexo,
                    crh_user = :user,
                    crh_updated_at = NOW(),
                    crh_history = :history,
                    updated_at = NOW()
                    $columnUpdate
                WHERE id = :id
            ");
            $stmt->execute([
                ':newStatus' => $newStatus,
                ':enviadoCrh' => $enviadoCrhVal,
                ':id' => $id,
                ':just' => $newJust,
                ':anexo' => $finalAnexoJson,
                ':user' => $user_name,
                ':history' => json_encode($history)
            ]);

            // Obter data e id_funcionario do registro para sincronizar com justificativas
            $stmtGetReg = $conn->prepare("SELECT id_funcionario, data FROM registros WHERE id = :id");
            $stmtGetReg->execute([':id' => $id]);
            $regData = $stmtGetReg->fetch(PDO::FETCH_ASSOC);
            if ($regData) {
                try {
                    $stmtUpdJust = $conn->prepare("UPDATE justificativas SET status = 'indeferido', observacao_admin = :obs, updated_at = NOW() WHERE id_funcionario = :fId AND data_registro = :dt");
                    $stmtUpdJust->execute([':obs' => 'Recusado via sisponto: ' . $motivo, ':fId' => $regData['id_funcionario'], ':dt' => $regData['data']]);
                } catch (Exception $e) {
                    // Ignora se der erro
                }
            }

            echo json_encode(['success' => true, 'message' => 'Justificativa recusada. O turno voltará a constar como falta.']);
            exit;
        }
    }

    // Filtros
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    $startDate = $_GET['start_date'] ?? date('Y-m-01'); // Inicio do mês atual
    $endDate = $_GET['end_date'] ?? date('Y-m-t'); // Fim do mês atual
    $funcId = trim($_GET['func_id'] ?? '');
    $setor = trim($_GET['setor'] ?? '');

    $params = [':start' => $startDate, ':end' => $endDate];
    $sqlFilter = "";

    // Filtro de Segurança por Setor
    $user_name = $_SESSION['user_name'] ?? '';
    $user_setor = $_SESSION['user_setor'] ?? '';
    $user_level = $_SESSION['user_level'] ?? 3;
    $strict = isset($_GET['strict_sector']) && $_GET['strict_sector'] == '1';
    if ($strict) {
        $is_super = ($user_level == 1);
    } else {
        $is_super = ($user_level == 1) || in_array(strtolower(trim($user_name)), ['corsin', 'crh']) || in_array(strtolower(trim($user_setor)), ['corsin', 'crh']);
    }

    if (!$is_super) {
        if (!empty($user_setor)) {
            $sqlFilter .= " AND (TRIM(f.setor) ILIKE TRIM(:user_setor) OR TRIM(f.setor2) ILIKE TRIM(:user_setor))";
            $params[':user_setor'] = trim($user_setor);
        } else {
            $sqlFilter .= " AND (f.setor IS NULL OR TRIM(f.setor) = '')";
        }
    }

    if (!empty($funcId)) {
        $sqlFilter .= " AND r.id_funcionario = :funcId";
        $params[':funcId'] = $funcId;
    }

    $setor = $_GET['setor'] ?? '';
    if (!empty($setor)) {
        $sqlFilter .= " AND (TRIM(f.setor) ILIKE TRIM(:setor) OR TRIM(f.setor2) ILIKE TRIM(:setor))";
        $params[':setor'] = $setor;
    }

    // Filtro de Status CRH
    $filtroCrh = $_GET['filtro_crh'] ?? '';
    if ($filtroCrh === 'enviados') {
        $sqlFilter .= " AND (r.enviado_crh = TRUE OR r.status_crh IN ('deferido', 'indeferido'))";
    } elseif ($filtroCrh === 'nao_enviados') {
        $sqlFilter .= " AND (r.enviado_crh = FALSE OR r.enviado_crh IS NULL) AND (r.status_crh NOT IN ('deferido', 'indeferido') OR r.status_crh IS NULL)";
    }

    $sql = "
        SELECT 
            r.id, r.data, f.id AS id_funcionario, f.nome, f.matricula, f.setor, f.setor2, 
            r.primeiro_ponto, r.segundo_ponto, r.terceiro_ponto, r.quarto_ponto,
            r.atrasou_primeiro_ponto, r.atrasou_segundo_ponto, r.atrasou_terceiro_ponto, r.atrasou_quarto_ponto,
            r.justificativa, r.tipo_justificativa, r.anexo_justificativa, r.status_turno1, r.status_turno2,
            r.just_ent1, r.just_sai1, r.just_ent2, r.just_sai2, r.enviado_crh, r.status_crh, r.aviso, r.comunicado, r.anexo_comunicado,
            r.crh_user, r.crh_updated_at, r.crh_history, r.gestor_nome, r.gestor_setor,
            h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario, h.tolerancia_entrada, h.tolerancia_saida,
            f.grade_horarios
        FROM registros r
        JOIN funcionarios f ON r.id_funcionario = f.id
        LEFT JOIN horarios h ON f.id_horario = h.id
        WHERE r.data BETWEEN :start AND :end
        $sqlFilter
        ORDER BY r.data DESC, f.nome ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $registrosExistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Criar um mapa de dias que possuem qualquer registro físico no banco de dados para evitar duplicar com registros virtuais
    $mapaDiasComRegistro = [];
    try {
        $stmtDias = $conn->prepare("SELECT data, id_funcionario FROM registros WHERE data BETWEEN :start AND :end");
        $stmtDias->execute([':start' => $startDate, ':end' => $endDate]);
        while ($rowDia = $stmtDias->fetch(PDO::FETCH_ASSOC)) {
            $dateOnly = !empty($rowDia['data']) ? substr($rowDia['data'], 0, 10) : '';
            if ($dateOnly) {
                $mapaDiasComRegistro[$dateOnly][$rowDia['id_funcionario']] = true;
            }
        }
    } catch (Exception $e) {
        // Fallback em caso de qualquer erro
    }

    // Organizar registros existentes por data e funcionário para consulta rápida
    $mapaRegistros = [];
    foreach ($registrosExistentes as $r) {
        $dateOnly = !empty($r['data']) ? substr($r['data'], 0, 10) : '';
        if ($dateOnly) {
            $mapaRegistros[$dateOnly][$r['id_funcionario']] = $r;
        }
    }

    // Buscar liberações de ponto no período para processamento de feriados/facultativos
    $stmtLib = $conn->prepare("SELECT * FROM ponto_liberado WHERE DATE(data_hora) BETWEEN :start AND :end");
    $stmtLib->execute([':start' => $startDate, ':end' => $endDate]);
    $liberacoes = $stmtLib->fetchAll(PDO::FETCH_ASSOC);

    // Se um funcionário específico for filtrado, vamos preencher as lacunas do período
    // Caso contrário, se o setor estiver filtrado, podemos também preencher as lacunas para todos do setor
    // IMPORTANTE: Não preencher lacunas se estivermos buscando apenas registros ENVIADOS ao CRH
    $funcionariosParaPreencher = [];
    if (empty($filtroCrh) || $filtroCrh !== 'enviados') {
        if (!empty($funcId)) {
            $stmtF = $conn->prepare("SELECT f.*, h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario, h.tolerancia_entrada, h.tolerancia_saida 
                                     FROM funcionarios f LEFT JOIN horarios h ON f.id_horario = h.id WHERE f.id = :id");
            $stmtF->execute([':id' => $funcId]);
            $f = $stmtF->fetch(PDO::FETCH_ASSOC);
            if ($f) $funcionariosParaPreencher[] = $f;
        } else {
            // Preenche para todos os funcionários não exonerados
            $sqlF = "SELECT f.*, h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario, h.tolerancia_entrada, h.tolerancia_saida 
                     FROM funcionarios f LEFT JOIN horarios h ON f.id_horario = h.id 
                     WHERE (f.is_exonerado IS FALSE OR f.is_exonerado IS NULL)";
            
            $paramsF = [];
            if (!empty($setor)) {
                $sqlF .= " AND (TRIM(f.setor) ILIKE TRIM(:s) OR TRIM(f.setor2) ILIKE TRIM(:s))";
                $paramsF[':s'] = $setor;
            } else if (!$is_super) {
                $sqlF .= " AND (TRIM(f.setor) ILIKE TRIM(:s) OR TRIM(f.setor2) ILIKE TRIM(:s))";
                $paramsF[':s'] = trim($user_setor);
            }

            $stmtF = $conn->prepare($sqlF);
            $stmtF->execute($paramsF);
            $funcionariosParaPreencher = $stmtF->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    $registrosFinal = [];
    $dataInicio = new DateTime($startDate);
    $dataFim = new DateTime($endDate);
    
    // Gerar registros para cada dia e cada funcionário relevante
    $periodo = new DatePeriod($dataInicio, new DateInterval('P1D'), (clone $dataFim)->modify('+1 day'));

    foreach ($periodo as $d) {
        $dataStr = $d->format('Y-m-d');
        
        foreach ($funcionariosParaPreencher as $f) {
            if (isset($mapaDiasComRegistro[$dataStr][$f['id']])) {
                // Já existe qualquer registro no banco (seja filtrado ou não)
                if (isset($mapaRegistros[$dataStr][$f['id']])) {
                    $registrosFinal[] = $mapaRegistros[$dataStr][$f['id']];
                }
            } else {
                // Criar registro VIRTUAL
                // Mas apenas se o funcionário tiver horário nesse dia (evitar finais de semana se não for escala)
                
                $mapaDias = [
                    'Monday' => 'segunda', 'Tuesday' => 'terca', 'Wednesday' => 'quarta',
                    'Thursday' => 'quinta', 'Friday' => 'sexta', 'Saturday' => 'sabado', 'Sunday' => 'domingo'
                ];
                $diaSemana = $mapaDias[$d->format('l')];
                $grade = json_decode($f['grade_horarios'] ?? '[]', true);
                $temHorario = false;

                // Verificar se há horário na grade ou se o horário padrão tem entradas
                if (isset($grade[$diaSemana]) && !empty($grade[$diaSemana])) {
                    $temHorario = true;
                } else if (!empty($f['primeiro_horario']) || !empty($f['terceiro_horario'])) {
                    // Se não tem grade, mas tem horário fixo, consideramos que trabalha dias úteis (seg-sex)
                    $nDia = (int)$d->format('N'); // 1 (Mon) to 7 (Sun)
                    if ($nDia <= 5) $temHorario = true;
                }

                if ($temHorario) {
                    $registrosFinal[] = [
                        'id' => "v_{$f['id']}_{$dataStr}",
                        'data' => $dataStr,
                        'id_funcionario' => $f['id'],
                        'nome' => $f['nome'],
                        'matricula' => $f['matricula'],
                        'setor' => $f['setor'],
                        'setor2' => $f['setor2'],
                        'primeiro_ponto' => null,
                        'segundo_ponto' => null,
                        'terceiro_ponto' => null,
                        'quarto_ponto' => null,
                        'justificativa' => null,
                        'tipo_justificativa' => null,
                        'anexo_justificativa' => null,
                        'status_turno1' => null,
                        'status_turno2' => null,
                        'enviado_crh' => false,
                        'status_crh' => 'pendente',
                        'atrasou_primeiro_ponto' => false,
                        'atrasou_segundo_ponto' => false,
                        'atrasou_terceiro_ponto' => false,
                        'atrasou_quarto_ponto' => false,
                        'just_ent1' => null,
                        'just_sai1' => null,
                        'just_ent2' => null,
                        'just_sai2' => null,
                        'aviso' => null,
                        'comunicado' => null,
                        'crh_user' => null,
                        'crh_updated_at' => null,
                        'crh_history' => null,
                        'primeiro_horario' => $f['primeiro_horario'],
                        'segundo_horario' => $f['segundo_horario'],
                        'terceiro_horario' => $f['terceiro_horario'],
                        'quarto_horario' => $f['quarto_horario'],
                        'tolerancia_entrada' => $f['tolerancia_entrada'],
                        'tolerancia_saida' => $f['tolerancia_saida'],
                        'grade_horarios' => $f['grade_horarios'],
                        'virtual' => true
                    ];
                }
            }
        }
    }

    // Se NÃO houver filtro de funcionário nem de setor (muito raro nesse report), manter apenas os existentes
    if (empty($registrosFinal)) {
        $registrosFinal = $registrosExistentes;
    } else {
        // Ordenar por data DESC
        usort($registrosFinal, function($a, $b) {
            if ($a['data'] === $b['data']) return strcmp($a['nome'] ?? '', $b['nome'] ?? '');
            return strcmp($b['data'], $a['data']);
        });
    }

    $registros = &$registrosFinal;

    // Buscar todos os horários para lookup rápido no processamento da grade
    $stmtH = $conn->query("SELECT * FROM horarios");
    $todosHorarios = [];
    while($h = $stmtH->fetch(PDO::FETCH_ASSOC)) {
        $todosHorarios[$h['id']] = $h;
    }

    // Buscar períodos de afastamentos ativos no período consultado (Deferidos ou Pendentes)
    $stmtFerias = $conn->prepare("SELECT id_funcionario, data_inicio, data_fim, tipo_afastamento, motivo_especifico, status FROM ferias WHERE status IN ('deferido', 'pendente') AND data_inicio <= :end AND data_fim >= :start");
    $stmtFerias->execute([':start' => $startDate, ':end' => $endDate]);
    $feriasRows = $stmtFerias->fetchAll();

    // Índice por funcionário para lookup rápido
    $feriasPorFunc = [];
    foreach ($feriasRows as $f) {
        $feriasPorFunc[$f['id_funcionario']][] = [
            'ini' => $f['data_inicio'], 
            'fim' => $f['data_fim'],
            'tipo' => $f['tipo_afastamento'],
            'espec' => $f['motivo_especifico'],
            'status' => $f['status']
        ];
    }

    // Processar faltas automáticas
    $hojeStr = date('Y-m-d');
    $agoraStr = date('H:i:s');

    foreach ($registros as &$reg) {
        $regDate = $reg['data'];
        $funcId = $reg['id_funcionario'] ?? null;

        // Associar liberação de ponto se houver
        $reg['liberacao'] = null;
        foreach ($liberacoes as $lib) {
            $libDate = date('Y-m-d', strtotime($lib['data_hora']));
            if ($libDate === $regDate) {
                if ($lib['setor'] === 'TODOS' || $lib['setor'] === ($reg['setor'] ?? '') || $lib['setor'] === ($reg['setor2'] ?? '')) {
                    $reg['liberacao'] = [
                        'id' => $lib['id'],
                        'data_hora' => $lib['data_hora'],
                        'descricao' => $lib['descricao'],
                        'justificativa' => $lib['justificativa'] ?? ''
                    ];
                    break;
                }
            }
        }

        $reg['falta_turno1_entrada'] = false;
        $reg['falta_turno1_saida'] = false;
        $reg['falta_turno2_entrada'] = false;
        $reg['falta_turno2_saida'] = false;
        $reg['em_ferias'] = false;
        $reg['status_afastamento'] = null;
        $reg['motivo_afastamento'] = null;

        // Verificar se está em afastamento neste dia → ignorar faltas
        if ($funcId && isset($feriasPorFunc[$funcId])) {
            foreach ($feriasPorFunc[$funcId] as $periodo) {
                if ($regDate >= $periodo['ini'] && $regDate <= $periodo['fim']) {
                    $reg['em_ferias'] = true;
                    $reg['status_afastamento'] = $periodo['status'];
                    $reg['motivo_afastamento'] = ($periodo['tipo'] === 'outros') ? ($periodo['espec'] ?: 'Outros') : ($periodo['tipo'] ?: 'Afastamento');
                    break;
                }
            }
        }
        // if ($reg['em_ferias'])
        //     continue;

        // ---- Aplicar Grade de Horários Específica para o Dia ----
        $mapaDias = [
            'Monday' => 'segunda', 'Tuesday' => 'terca', 'Wednesday' => 'quarta',
            'Thursday' => 'quinta', 'Friday' => 'sexta', 'Saturday' => 'sabado', 'Sunday' => 'domingo'
        ];
        $diaSemana = $mapaDias[date('l', strtotime($regDate))];
        $grade = json_decode($reg['grade_horarios'] ?? '[]', true);

        if (isset($grade[$diaSemana]) && !empty($grade[$diaSemana])) {
            $diaData = $grade[$diaSemana];
            
            if (is_array($diaData) && isset($diaData['horario_id']) && !empty($diaData['horario_id'])) {
                $hId = $diaData['horario_id'];
                if (isset($todosHorarios[$hId])) {
                    $hEsp = $todosHorarios[$hId];
                    $reg['primeiro_horario'] = $hEsp['primeiro_horario'];
                    $reg['segundo_horario'] = $hEsp['segundo_horario'];
                    $reg['terceiro_horario'] = $hEsp['terceiro_horario'];
                    $reg['quarto_horario'] = $hEsp['quarto_horario'];
                    $reg['tolerancia_entrada'] = $hEsp['tolerancia_entrada'];
                    $reg['tolerancia_saida'] = $hEsp['tolerancia_saida'];
                }
            } else if (is_array($diaData)) {
                $p1 = $diaData[0] ?? ($diaData['p1'] ?? null);
                $p2 = $diaData[1] ?? ($diaData['p2'] ?? null);
                $p3 = $diaData[2] ?? ($diaData['p3'] ?? null);
                $p4 = $diaData[3] ?? ($diaData['p4'] ?? null);
                
                if ($p1 || $p2 || $p3 || $p4) {
                   $reg['primeiro_horario'] = $p1;
                   $reg['segundo_horario'] = $p2;
                   $reg['terceiro_horario'] = $p3;
                   $reg['quarto_horario'] = $p4;
                }
            }
        }

        // Se o registro é de uma data futura, ignora
        if ($regDate > $hojeStr)
            continue;

        $toleranciaEntrada = isset($reg['tolerancia_entrada']) ? (int) $reg['tolerancia_entrada'] : 15;
        $toleranciaSaida = isset($reg['tolerancia_saida']) ? (int) $reg['tolerancia_saida'] : 15;

        // Turno 1 - Entrada (primeiro_ponto)
        if ($reg['primeiro_horario'] && (empty($reg['primeiro_ponto']) || strtoupper(trim($reg['primeiro_ponto'])) === 'FALTA')) {
            $limite = date('H:i:s', strtotime($reg['primeiro_horario']) + ($toleranciaEntrada * 60));
            if ($regDate < $hojeStr || ($regDate == $hojeStr && $agoraStr > $limite)) {
                $reg['falta_turno1_entrada'] = true;
            }
        } elseif ($reg['primeiro_horario'] && !empty($reg['primeiro_ponto']) && strtoupper(trim($reg['primeiro_ponto'])) !== 'FALTA') {
            // Recalcular atraso se houver batida
            $pontoSeg = strtotime($reg['primeiro_ponto']);
            $horaSeg = strtotime($reg['primeiro_horario']);
            if ($pontoSeg > ($horaSeg + ($toleranciaEntrada * 60))) {
                $reg['atrasou_primeiro_ponto'] = true;
            } else {
                $reg['atrasou_primeiro_ponto'] = false;
            }
        }

        // Turno 1 - Saída (segundo_ponto)
        if ($reg['segundo_horario'] && (empty($reg['segundo_ponto']) || strtoupper(trim($reg['segundo_ponto'])) === 'FALTA')) {
            if ($regDate < $hojeStr) {
                $reg['falta_turno1_saida'] = true;
            }
        } elseif ($reg['segundo_horario'] && !empty($reg['segundo_ponto']) && strtoupper(trim($reg['segundo_ponto'])) !== 'FALTA') {
            $pontoSeg = strtotime($reg['segundo_ponto']);
            $horaSeg = strtotime($reg['segundo_horario']);
            // Atraso na saída é sair ANTES do horário planejado (exceção à regra de tolerância que geralmente é pra entrada)
            // Mas seguindo o padrão do sistema para 'atrasou_...', verificamos a tolerância de saída se for o caso
            if ($pontoSeg < ($horaSeg - ($toleranciaSaida * 60))) {
                $reg['atrasou_segundo_ponto'] = true;
            } else {
                $reg['atrasou_segundo_ponto'] = false;
            }
        }

        // Turno 2 - Entrada (terceiro_ponto)
        if ($reg['terceiro_horario'] && (empty($reg['terceiro_ponto']) || strtoupper(trim($reg['terceiro_ponto'])) === 'FALTA')) {
            $limite = date('H:i:s', strtotime($reg['terceiro_horario']) + ($toleranciaEntrada * 60));
            if ($regDate < $hojeStr || ($regDate == $hojeStr && $agoraStr > $limite)) {
                $reg['falta_turno2_entrada'] = true;
            }
        } elseif ($reg['terceiro_horario'] && !empty($reg['terceiro_ponto']) && strtoupper(trim($reg['terceiro_ponto'])) !== 'FALTA') {
            $pontoSeg = strtotime($reg['terceiro_ponto']);
            $horaSeg = strtotime($reg['terceiro_horario']);
            if ($pontoSeg > ($horaSeg + ($toleranciaEntrada * 60))) {
                $reg['atrasou_terceiro_ponto'] = true;
            } else {
                $reg['atrasou_terceiro_ponto'] = false;
            }
        }

        // Turno 2 - Saída (quarto_ponto)
        if ($reg['quarto_horario'] && (empty($reg['quarto_ponto']) || strtoupper(trim($reg['quarto_ponto'])) === 'FALTA')) {
            if ($regDate < $hojeStr) {
                $reg['falta_turno2_saida'] = true;
            }
        } elseif ($reg['quarto_horario'] && !empty($reg['quarto_ponto']) && strtoupper(trim($reg['quarto_ponto'])) !== 'FALTA') {
            $pontoSeg = strtotime($reg['quarto_ponto']);
            $horaSeg = strtotime($reg['quarto_horario']);
            if ($pontoSeg < ($horaSeg - ($toleranciaSaida * 60))) {
                $reg['atrasou_quarto_ponto'] = true;
            } else {
                $reg['atrasou_quarto_ponto'] = false;
            }
        }
    }

    if (isset($_GET['action']) && $_GET['action'] == 'get_setores') {
        // Se for gestor, ele só pode ver o próprio setor dele na lista
        if (!$is_super && !empty($user_setor)) {
            echo json_encode(['success' => true, 'data' => [trim($user_setor)]]);
            exit;
        }

        $stmt = $conn->query("
            SELECT DISTINCT TRIM(setor) as s FROM funcionarios WHERE setor IS NOT NULL AND setor <> ''
            UNION
            SELECT DISTINCT TRIM(setor2) as s FROM funcionarios WHERE setor2 IS NOT NULL AND setor2 <> ''
            ORDER BY s ASC
        ");
        $setores = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['success' => true, 'data' => array_values(array_unique(array_filter($setores)))]);
        exit;
    }

    // --- INTEGRAR JUSTIFICATIVAS DO FUNCIONÁRIO (funadponto) ---
    $employeeIds = array_unique(array_filter(array_column($registros, 'id_funcionario')));
    $justificativasMap = [];

    if (!empty($employeeIds) && !empty($startDate) && !empty($endDate)) {
        try {
            $placeholders = implode(',', array_fill(0, count($employeeIds), '?'));
            $sqlJust = "SELECT * FROM justificativas WHERE id_funcionario IN ($placeholders) AND data_registro BETWEEN ? AND ?";
            $stmtJust = $conn->prepare($sqlJust);
            
            $paramsJust = array_merge($employeeIds, [$startDate, $endDate]);
            $stmtJust->execute($paramsJust);
            
            while ($j = $stmtJust->fetch(PDO::FETCH_ASSOC)) {
                $justificativasMap[$j['data_registro']][$j['id_funcionario']][$j['campo_ponto']] = $j;
            }
        } catch (Exception $e) {
            // Ignorar se a tabela/colunas não existirem
        }
    }

    // Anexar ao registro correspondente e verificar caminhos de anexos
    foreach ($registros as &$reg) {
        $regDate = $reg['data'];
        $fId = $reg['id_funcionario'] ?? null;
        $reg['justificativas_funcionario'] = $justificativasMap[$regDate][$fId] ?? null;

        // 1. Verificar/corrigir caminho de anexo_justificativa (Gestor)
        if (!empty($reg['anexo_justificativa']) && $reg['anexo_justificativa'] !== 'null' && $reg['anexo_justificativa'] !== '[]') {
            $parsed = json_decode($reg['anexo_justificativa'], true);
            if (is_array($parsed)) {
                foreach ($parsed as &$path) {
                    $path = verificarCaminhoAnexo($path);
                }
                unset($path);
                $reg['anexo_justificativa'] = json_encode(array_values(array_unique(array_filter($parsed))));
            } else {
                $reg['anexo_justificativa'] = verificarCaminhoAnexo($reg['anexo_justificativa']);
            }
        }

        // 2. Verificar/corrigir caminho de anexo_comunicado (Funcionário)
        if (!empty($reg['anexo_comunicado']) && $reg['anexo_comunicado'] !== 'null' && $reg['anexo_comunicado'] !== '[]') {
            $parsed = json_decode($reg['anexo_comunicado'], true);
            if (is_array($parsed)) {
                foreach ($parsed as &$path) {
                    $path = verificarCaminhoAnexo($path);
                }
                unset($path);
                $reg['anexo_comunicado'] = json_encode(array_values(array_unique(array_filter($parsed))));
            } else {
                $reg['anexo_comunicado'] = verificarCaminhoAnexo($reg['anexo_comunicado']);
            }
        }

        // 3. Verificar/corrigir caminhos dos anexos em justificativas_funcionario (PWA)
        if (!empty($reg['justificativas_funcionario']) && is_array($reg['justificativas_funcionario'])) {
            foreach ($reg['justificativas_funcionario'] as $campo => &$jRow) {
                if (!empty($jRow['anexos']) && $jRow['anexos'] !== 'null' && $jRow['anexos'] !== '[]') {
                    $parsed = json_decode($jRow['anexos'], true);
                    if (is_array($parsed)) {
                        foreach ($parsed as &$path) {
                            $path = verificarCaminhoAnexo($path);
                        }
                        unset($path);
                        $jRow['anexos'] = json_encode(array_values(array_unique(array_filter($parsed))));
                    } else {
                        $jRow['anexos'] = verificarCaminhoAnexo($jRow['anexos']);
                    }
                }
            }
            unset($jRow);
        }
    }

    echo json_encode(['success' => true, 'data' => $registros]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno de banco de dados: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}
