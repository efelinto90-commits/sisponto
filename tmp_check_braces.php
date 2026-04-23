<?php
$content = file_get_contents('c:/xampp/htdocs/sisequipe/dashboard.php');
if (preg_match('/<script>(.*?)<\/script>/s', $content, $matches)) {
    $js = $matches[1];
    $open = substr_count($js, '{');
    $close = substr_count($js, '}');
    echo "Open: $open, Close: $close\n";
    if ($open != $close) {
        echo "MISMATCH DETECTED!\n";
    } else {
        echo "Braces are balanced.\n";
    }
} else {
    echo "Script block not found or empty.\n";
}
