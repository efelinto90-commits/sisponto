<?php
$f = 'c:/xampp/htdocs/sis-ponto/views/admin/cartao_ponto.php';
$c = file_get_contents($f);

// Direct replacements for all corrupted words based on grep output
$rep = [
    'CartÃ£o' => 'Cartão',
    'DiÃ¡rio' => 'Diário',
    'diÃ¡rias' => 'diárias',
    'PerÃ­odo' => 'Período',
    'FuncionÃ¡rios' => 'Funcionários',
    'AÃ§Ã£o' => 'Ação',
    'funcionÃ¡rios' => 'funcionários',
    'automÃ¡tica' => 'automática',
    'hÃ¡' => 'há',
    'RelatÃ³rio' => 'Relatório',
    'tolerÃ¢ncia' => 'tolerância',
    'ausÃªncia' => 'ausência',
    'horÃ¡rio' => 'horário',
    'SaÃ­da' => 'Saída',
    'Ã¢ÂœÂ“' => '✓',
    'Ã¢ÂœÂ”' => '✔',
    'â†’' => '→',
    'âœ”' => '✔',
    'âœ“' => '✓',
    'â€”' => '—',
    'â›”' => '⛔',
    'perÃ­odo' => 'período',
    // also replacing any remaining ISO artifacts
    'Ã¡' => 'á',
    'Ã¢' => 'â',
    'Ã£' => 'ã',
    'Ã§' => 'ç',
    'Ã©' => 'é',
    'Ãª' => 'ê',
    'Ã­' => 'í',
    'Ã³' => 'ó',
    'Ã´' => 'ô',
    'Ãµ' => 'õ',
    'Ãº' => 'ú',
    'Ã ' => 'à',
    'Ã‡' => 'Ç',
    'Ã‰' => 'É',
    'ÃŠ' => 'Ê',
    'Ã' => 'Á',
    'Ã‚' => 'Â',
    'Ã“' => 'Ó',
    'Ã”' => 'Ô',
    'Ã•' => 'Õ',
    'Ãš' => 'Ú'
];

$c = strtr($c, $rep);
file_put_contents($f, $c);
echo "Direct replacements done!\n";
