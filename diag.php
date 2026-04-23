<?php
function countPlaceholders($sql) {
    preg_match_all('/:([a-zA-Z0-9_]+)/', $sql, $matches);
    return count($matches[0]);
}

$content = file_get_contents('c:/xampp/htdocs/sisponto/api/funcionarios.php');

// INSERT
if (preg_match('/\$sql = "INSERT INTO ponto\.funcionarios \((.*?)\) VALUES \((.*?)\)";/s', $content, $m)) {
    echo "INSERT Placeholders: " . countPlaceholders($m[2]) . "\n";
}

// UPDATE (Assume base)
if (preg_match('/\$sql = "UPDATE ponto\.funcionarios SET (.*?) WHERE id = :id";/s', $content, $m)) {
    echo "UPDATE Placeholders (Base): " . (countPlaceholders($m[1]) + 1) . "\n";
}

// Count parameters provided in arrays
if (preg_match_all('/\$params = \[(.*?)\];/s', $content, $m)) {
    foreach ($m[1] as $idx => $arr) {
        preg_match_all("/':([a-zA-Z0-9_]+)'/", $arr, $pmatches);
        echo "Array $idx Params: " . count($pmatches[0]) . "\n";
    }
}
