<?php
require_once 'config/Database.php';
use Config\Database;

// Mocking some environment variables if needed or just testing the logic directly
// For simplicity, let's create a standalone function that replicates the logic for testing

function testAlignment($agora_time, $funcionario, $registroHoje = null) {
    $agora_ts = strtotime($agora_time);
    $todosPontos = [
        ['campo' => 'primeiro_ponto', 'previsto' => $funcionario['primeiro_horario'], 'is_entrada' => true],
        ['campo' => 'segundo_ponto', 'previsto' => $funcionario['segundo_horario'], 'is_entrada' => false],
        ['campo' => 'terceiro_ponto', 'previsto' => $funcionario['terceiro_horario'], 'is_entrada' => true],
        ['campo' => 'quarto_ponto', 'previsto' => $funcionario['quarto_horario'], 'is_entrada' => false],
    ];

    $horariosPrevistos = [];
    foreach ($todosPontos as $p) {
        if (!empty($p['previsto'])) {
            $horariosPrevistos[] = [
                'campo' => $p['campo'],
                'ts' => strtotime($p['previsto']),
                'previsto' => $p['previsto']
            ];
        }
    }

    $numHorarios = count($horariosPrevistos);
    $campoPonto = '';
    
    for ($i = 0; $i < $numHorarios; $i++) {
        $atual = $horariosPrevistos[$i];
        if ($registroHoje && !empty($registroHoje[$atual['campo']])) continue;

        $limiteInferior = ($i === 0) ? 0 : ($horariosPrevistos[$i]['ts'] + $horariosPrevistos[$i-1]['ts']) / 2;
        $limiteSuperior = ($i === $numHorarios - 1) ? 9999999999 : ($horariosPrevistos[$i]['ts'] + $horariosPrevistos[$i+1]['ts']) / 2;

        if ($agora_ts >= $limiteInferior && $agora_ts < $limiteSuperior) {
            $campoPonto = $atual['campo'];
            break;
        }
    }

    if (empty($campoPonto)) {
        for ($i = $numHorarios - 1; $i >= 0; $i--) {
            if (!$registroHoje || empty($registroHoje[$horariosPrevistos[$i]['campo']])) {
                $campoPonto = $horariosPrevistos[$i]['campo'];
                break;
            }
        }
    }
    
    return $campoPonto;
}

$funcionario = [
    'primeiro_horario' => '08:00',
    'segundo_horario' => '12:00',
    'terceiro_horario' => '13:00',
    'quarto_horario' => '17:00'
];

echo "Teste 1 (08:05): " . testAlignment('08:05', $funcionario) . " (Esperado: primeiro_ponto)\n";
echo "Teste 2 (11:30): " . testAlignment('11:30', $funcionario) . " (Esperado: segundo_ponto)\n";
echo "Teste 3 (12:45): " . testAlignment('12:45', $funcionario) . " (Esperado: terceiro_ponto)\n";
echo "Teste 4 (09:50): " . testAlignment('09:50', $funcionario) . " (Esperado: primeiro_ponto)\n";
echo "Teste 5 (10:10): " . testAlignment('10:10', $funcionario) . " (Esperado: segundo_ponto)\n";

$registroHoje = ['primeiro_ponto' => '08:00'];
echo "Teste 6 (08:30 com P1 ja feito): " . testAlignment('08:30', $funcionario, $registroHoje) . " (Esperado: segundo_ponto)\n";

$registroHoje = ['primeiro_ponto' => '08:00', 'segundo_ponto' => '12:00'];
echo "Teste 7 (12:40 com P1,P2 ja feitos): " . testAlignment('12:40', $funcionario, $registroHoje) . " (Esperado: terceiro_ponto)\n";
