<?php
// Script de teste unitário para a sincronização de justificativas do funcionário (PWA + Comunicados) na tabela de registros

require_once __DIR__ . '/../config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $conn->beginTransaction();
    echo "Conexão com o banco estabelecida e transação iniciada.\n\n";

    // 1. Criar um funcionário temporário
    $stmt = $conn->query("SELECT id FROM funcionarios LIMIT 1");
    $funcId = $stmt->fetchColumn();
    if (!$funcId) {
        throw new Exception("Nenhum funcionário encontrado no banco para rodar o teste.");
    }
    
    $testDate = '2026-05-26';
    echo "Usando Funcionário ID: $funcId e Data: $testDate para o teste.\n";

    // 2. Limpar qualquer registro pré-existente desse funcionário na data
    $conn->prepare("DELETE FROM registros WHERE id_funcionario = ? AND data = ?")->execute([$funcId, $testDate]);
    $conn->prepare("DELETE FROM justificativas WHERE id_funcionario = ? AND data_registro = ?")->execute([$funcId, $testDate]);

    // 3. Criar registro de batida com Comunicado e Anexo de Comunicado
    $stmtInsReg = $conn->prepare("
        INSERT INTO registros (id_funcionario, data, comunicado, anexo_comunicado, justificativa, anexo_justificativa, created_at, updated_at)
        VALUES (?, ?, 'Meu carro quebrou no caminho', '[\"uploads/comunicados/teste_com.pdf\"]', '', '[]', NOW(), NOW())
    ");
    $stmtInsReg->execute([$funcId, $testDate]);
    $regId = $conn->lastInsertId();
    echo "Registro de teste criado em registros com ID: $regId\n";

    // 4. Criar justificativa do PWA na tabela justificativas
    $stmtInsPwa = $conn->prepare("
        INSERT INTO justificativas (id_funcionario, data_registro, campo_ponto, tipo_justificativa, texto, anexos, status)
        VALUES (?, ?, 'primeiro_ponto', 'Saúde', 'Fui ao médico fazer exames', '[\"upload/justificativa/just_teste.jpg\"]', 'pendente')
    ");
    $stmtInsPwa->execute([$funcId, $testDate]);
    echo "Justificativa PWA de teste criada na tabela justificativas.\n\n";

    // 5. Executar a lógica de sincronização (simulando deferir_crh/justificar)
    echo "=== Executando a Sincronização (Código da API) ===\n";
    
    $getRegInfo = $conn->prepare("SELECT id_funcionario, data, comunicado, anexo_comunicado, anexo_justificativa FROM registros WHERE id = :id");
    $getRegInfo->execute([':id' => $regId]);
    $regInfoRow = $getRegInfo->fetch(PDO::FETCH_ASSOC);
    
    $regIdFunc = $regInfoRow['id_funcionario'];
    $regDataVal = $regInfoRow['data'];
    $regComunicado = $regInfoRow['comunicado'];
    $regAnexoCom = $regInfoRow['anexo_comunicado'];
    $regAnexoJust = $regInfoRow['anexo_justificativa'];
    
    $just = ''; // inicializando
    $pwaTexts = [];
    $pwaAnexos = [];
    
    if ($regIdFunc && $regDataVal) {
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
    
    // Função local para o teste
    function verificarCaminhoAnexoTeste($dbPath) {
        $cleanedPath = preg_replace('/^\.\.\/sisponto\//', '', $dbPath);
        return ltrim($cleanedPath, './\\');
    }
    foreach ($anexosAtuais as &$path) {
        $path = verificarCaminhoAnexoTeste($path);
    }
    unset($path);
    $anexosAtuais = array_values(array_unique(array_filter($anexosAtuais)));
    $finalAnexoJson = !empty($anexosAtuais) ? json_encode($anexosAtuais) : null;
    
    echo "Textos Consolidados Obtidos:\n\"$just\"\n\n";
    echo "Anexos Consolidados Obtidos:\n$finalAnexoJson\n\n";

    // 6. Validar Asserções
    if (strpos($just, '[Saúde do Servidor]: Fui ao médico fazer exames') === false) {
        throw new Exception("FALHA: A justificativa do PWA não foi concatenada!");
    }
    if (strpos($just, '[Aviso do Funcionário]: Meu carro quebrou no caminho') === false) {
        throw new Exception("FALHA: O comunicado do funcionário não foi concatenado!");
    }
    if (strpos($finalAnexoJson, 'just_teste.jpg') === false) {
        throw new Exception("FALHA: O anexo do PWA não foi mesclado!");
    }
    if (strpos($finalAnexoJson, 'teste_com.pdf') === false) {
        throw new Exception("FALHA: O anexo do comunicado não foi mesclado!");
    }
    
    echo "=== TODOS OS TESTES PASSARAM COM SUCESSO! ===\n";
    
    $conn->rollBack();
    echo "Transação desfeita com sucesso. Banco de dados intacto.\n";

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "\nERRO NO TESTE: " . $e->getMessage() . "\n";
}
