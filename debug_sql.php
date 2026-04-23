<?php
$content = file_get_contents('c:/xampp/htdocs/sisponto/api/funcionarios.php');

// Analisar o caso POST (INSERT)
preg_match('/\$sql = "INSERT INTO ponto\.funcionarios \((.*?)\) VALUES \((.*?)\)";/s', $content, $matches);
if ($matches) {
    $cols = array_map('trim', explode(',', $matches[1]));
    $placeholders = array_map('trim', explode(',', $matches[2]));
    echo "POST Columns: " . count($cols) . "\n";
    echo "POST Placeholders: " . count($placeholders) . "\n";
}

// Analisar o caso PUT (UPDATE)
preg_match('/\$sql = "UPDATE ponto\.funcionarios SET (.*?) WHERE id = :id";/s', $content, $matches);
if ($matches) {
    preg_match_all('/:([a-z0-9_]+)/', $matches[1], $placeholder_matches);
    echo "PUT Base Placeholders: " . count($placeholder_matches[0]) . "\n";
}
