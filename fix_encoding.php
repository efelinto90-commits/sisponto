<?php
$f = 'c:/xampp/htdocs/sis-ponto/views/admin/funcionarios.php';
$c = file_get_contents($f);
$c = mb_convert_encoding($c, 'ISO-8859-1', 'UTF-8');
file_put_contents($f, $c);
echo "Fixed";
