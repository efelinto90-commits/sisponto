<?php
$lines = file('c:/xampp/htdocs/sisponto/api/funcionarios.php');

$start_post = -1;
$end_post = -1;
foreach($lines as $i => $l) {
    if (strpos($l, '$params = [') !== false && $i < 312 && $i > 200) $start_post = $i;
    if (strpos($l, '];') !== false && $start_post != -1 && $end_post == -1) $end_post = $i;
}

if ($start_post != -1) {
    echo "POST params lines: " . ($start_post+1) . " to " . ($end_post+1) . "\n";
    $count = 0;
    for($i = $start_post + 1; $i < $end_post; $i++) {
        if (preg_match("/':(\w+)'/", $lines[$i])) $count++;
    }
    echo "POST Params Count: $count\n";
}

$start_put = -1;
$end_put = -1;
foreach($lines as $i => $l) {
    if (strpos($l, '$params = [') !== false && $i > 312) $start_put = $i;
    if (strpos($l, '];') !== false && $start_put != -1 && $end_put == -1) $end_put = $i;
}

if ($start_put != -1) {
    echo "PUT params lines: " . ($start_put+1) . " to " . ($end_put+1) . "\n";
    $count = 0;
    for($i = $start_put + 1; $i < $end_put; $i++) {
        if (preg_match("/':(\w+)'/", $lines[$i])) $count++;
    }
    echo "PUT Params Count: $count\n";
}
