<?php
$f = 'c:/xampp/htdocs/sis-ponto/views/admin/cartao_ponto.php';
$c = file_get_contents($f);

$replacements = [
    urldecode('%C3%83%C2%A1') => 'á',
    urldecode('%C3%83%C2%A2') => 'â',
    urldecode('%C3%83%C2%A3') => 'ã',
    urldecode('%C3%83%C2%A7') => 'ç',
    urldecode('%C3%83%C2%A9') => 'é',
    urldecode('%C3%83%C2%AA') => 'ê',
    urldecode('%C3%83%C2%AD') => 'í',
    urldecode('%C3%83%C2%B3') => 'ó',
    urldecode('%C3%83%C2%B4') => 'ô',
    urldecode('%C3%83%C2%B5') => 'õ',
    urldecode('%C3%83%C2%BA') => 'ú',
    urldecode('%C3%83%C2%A0') => 'à',
    urldecode('%C3%83%C2%87') => 'Ç',
    urldecode('%C3%83%C2%89') => 'É',
    urldecode('%C3%83%C2%8A') => 'Ê',
    urldecode('%C3%83%C2%81') => 'Á',
    urldecode('%C3%83%C2%82') => 'Â',
    urldecode('%C3%83%C2%93') => 'Ó',
    urldecode('%C3%83%C2%94') => 'Ô',
    urldecode('%C3%83%C2%95') => 'Õ',
    urldecode('%C3%83%C2%9A') => 'Ú',

    // Sometimes it's encoded as ISO-8859-1 inside UTF-8
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
    'Ãš' => 'Ú',

    // Specific weird unicode artifacts 
    'âœ“' => '✓',
    'âœ”' => '✔',
    'â€”' => '—',
    'â›”' => '⛔',
    'â†’' => '→'
];

$c = strtr($c, $replacements);
file_put_contents($f, $c);
echo "Cleaned up double encoding!\n";
