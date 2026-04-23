<?php
$f = 'c:/xampp/htdocs/sis-ponto/views/admin/funcionarios.php';
$c = file_get_contents($f);

$replacements = [
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
    'Ã‘' => 'Ñ',
    'Ã±' => 'ñ',
    'Ã‡' => 'Ç',
    'Ã‰' => 'É',
    'ÃŠ' => 'Ê',
    'Ã' => 'Á',
    'Ã‚' => 'Â',
    'Â' => '',  // Sometimes Â appears before characters like Â©
    'Ã“' => 'Ó',
    'Ã”' => 'Ô',
    'Ã•' => 'Õ',
    'Ãš' => 'Ú',
    'Ãœ' => 'Ü',
    'Ã¼' => 'ü',
];

$c = strtr($c, $replacements);
file_put_contents($f, $c);
echo "Fixed characters using strtr.\n";
