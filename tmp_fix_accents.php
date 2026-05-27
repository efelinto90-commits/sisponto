<?php
$file = 'c:/xampp/htdocs/sisponto/views/relogio.php';
$content = file_get_contents($file);

// Mapeamento de sequências comuns de "double encoding" (UTF-8 bytes interpretados como ISO-8859-1 e salvos como UTF-8)
$replacements = [
    'Ã¡' => 'á', 'Ã©' => 'é', 'Ã­' => 'í', 'Ã³' => 'ó', 'Ãº' => 'ú',
    'Ã£' => 'ã', 'Ãµ' => 'õ', 'Ã§' => 'ç', 'Ãª' => 'ê', 'Ã´' => 'ô',
    'Ã¢' => 'â', 'Ã€' => 'À', 'Ã ' => 'Á', 'Ã‰' => 'É', 'Ã“' => 'Ó',
    'Ãš' => 'Ú', 'Ã‡' => 'Ç', 'Ãƒ' => 'Ã', 'Ã•' => 'Õ', 'ÃŠ' => 'Ê',
    'Ãs' => 'às' // Caso específico encontrado
];

$content = str_replace(array_keys($replacements), array_values($replacements), $content);

file_put_contents($file, $content);
echo "Correção concluída via PHP.";
