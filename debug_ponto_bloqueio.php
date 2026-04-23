<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    
    // 1. Buscar funcionário e horário
    echo "--- FUNCIONARIO ---\n";
    $stmt = $conn->prepare("SELECT f.id, f.nome, f.matricula, h.* FROM funcionarios f LEFT JOIN horarios h ON f.id_horario = h.id WHERE f.matricula = '2236-5'");
    $stmt->execute();
    $func = $stmt->fetch();
    print_r($func);

    // 2. Buscar registros de hoje
    echo "\n--- REGISTROS HOJE (2026-03-12) ---\n";
    $stmt = $conn->prepare("SELECT * FROM registros WHERE id_funcionario = :id AND data = '2026-03-12'");
    $stmt->execute([':id' => $func['id']]);
    $reg = $stmt->fetch();
    print_r($reg);

    // 3. Simular lógica do ponto.php
    echo "\n--- SIMULACAO LOGICA ponto.php ---\n";
    $horaAtual = '11:16'; // Hora relatada pelo usuário
    $agora_ts = strtotime($horaAtual);
    
    $todosPontos = [
        ['campo' => 'primeiro_ponto', 'previsto' => $func['primeiro_horario'], 'is_entrada' => true],
        ['campo' => 'segundo_ponto', 'previsto' => $func['segundo_horario'], 'is_entrada' => false],
        ['campo' => 'terceiro_ponto', 'previsto' => $func['terceiro_horario'], 'is_entrada' => true],
        ['campo' => 'quarto_ponto', 'previsto' => $func['quarto_horario'], 'is_entrada' => false],
    ];

    $campoPonto = '';
    foreach ($todosPontos as $ponto) {
        $campo = $ponto['campo'];
        $previsto = $ponto['previsto'];
        $ehEntrada = $ponto['is_entrada'];
        
        if (empty($previsto)) continue;
        
        $jaRegistrado = $reg && !empty($reg[$campo]);
        if ($jaRegistrado) {
            echo "Campo $campo já registrado: " . $reg[$campo] . "\n";
            continue;
        }

        $esperado_ts = strtotime($previsto);
        $tol_min = $ehEntrada ? (int)$func['tolerancia_entrada'] : (int)$func['tolerancia_saida'];
        $janela_fim = $esperado_ts + ($tol_min * 60);

        if ($agora_ts > $janela_fim) {
            echo "Campo $campo EXPIRADO (Janela fim: " . date('H:i', $janela_fim) . "). Seria marcado como FALTA.\n";
            continue;
        }

        $campoPonto = $campo;
        echo "Próximo campo disponível: $campoPonto (Previsto: $previsto)\n";
        break;
    }

    if (empty($campoPonto)) {
        echo "RESULTADO: Todos os pontos já foram registrados ou expiraram.\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
