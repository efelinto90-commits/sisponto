<?php
$file = 'c:/xampp/htdocs/sisponto/views/admin/funcionarios.php';
$content = file_get_contents($file);

if ($content === false) {
    echo "Erro ao ler o arquivo.\n";
    exit(1);
}

echo "Tamanho original: " . strlen($content) . "\n";

// Mapeamento de padrões corrompidos para os corretos (UTF-8)
$replacements = [
    'MatrÃ­cula' => 'Matrícula',
    'HorÃ¡rio' => 'Horário',
    'AÃ§Ãµes' => 'Ações',
    'Ãs' => 'às',
    'â??' => ' - ',
    'Sem horÃ¡rio fixo' => 'Sem horário fixo',
    'Vincular HorÃ¡rio' => 'Vincular Horário',
    'HorÃ¡rio Vinculado' => 'Horário Vinculado',
    'funcionÃ¡rios' => 'funcionários',
    'tambÃ©m' => 'também',
    'retornar' => 'retornar', // Garantir que não quebra nada
    'Ã§Ãµes' => 'ações',
    'â€“' => ' - ',
];

$fixedContent = str_replace(array_keys($replacements), array_values($replacements), $content);

// Tenta converter para UTF-8 limpo se houver lixo
$finalContent = mb_convert_encoding($fixedContent, 'UTF-8', 'UTF-8');

if (file_put_contents($file, $finalContent) !== false) {
    echo "Arquivo corrigido com sucesso.\n";
    echo "Tamanho final: " . strlen($finalContent) . "\n";
} else {
    echo "Erro ao salvar o arquivo.\n";
}
