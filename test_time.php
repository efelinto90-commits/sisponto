<?php
$horaAtual = '09:00';
$horaEsperada = '08:00';
$tolerancia_entrada = 15;
$quarto_horario = '18:00';

$is_entrada = true;
$tolerancia_minutos = 15;

$agora_ts = strtotime($horaAtual);
$esperado_ts = strtotime($horaEsperada);
$limite_ts = $esperado_ts + ($tolerancia_minutos * 60);

echo "Agora: " . date('Y-m-d H:i:s', $agora_ts) . "\n";
echo "Esperado: " . date('Y-m-d H:i:s', $esperado_ts) . "\n";
echo "Limite: " . date('Y-m-d H:i:s', $limite_ts) . "\n";

$is_atraso = false;
if ($agora_ts > $limite_ts) {
    $is_atraso = true;
    echo "Atrasado!\n";
} else {
    echo "No horario\n";
}

$saida_ts = strtotime($quarto_horario);
if ($is_entrada && $is_atraso) {
    if ($agora_ts < $saida_ts) {
        echo "BLOQUEADO PARA RH!\n";
    } else {
        echo "Após saida, nao bloqueia RH\n";
    }
}
