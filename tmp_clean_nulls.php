<?php
$f = 'c:/xampp/htdocs/sisponto/views/relogio.php';
$content = file_get_contents($f);
$clean = str_replace("\0", "", $content);
file_put_contents($f, $clean);
echo "Null bytes removed. New size: " . strlen($clean) . "\n";
?>
