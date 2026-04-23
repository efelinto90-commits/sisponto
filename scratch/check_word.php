<?php
$h1 = file_get_contents('timbre/extracted/word/header1.xml');
$h2 = file_get_contents('timbre/extracted/word/header2.xml');
$h3 = file_get_contents('timbre/extracted/word/header3.xml');
echo "H1 relations:\n";
preg_match_all('/r:embed="([^"]+)"/', $h1, $m); print_r($m[1]);
echo "H2 relations:\n";
preg_match_all('/r:embed="([^"]+)"/', $h2, $m); print_r($m[1]);
echo "H3 relations:\n";
preg_match_all('/r:embed="([^"]+)"/', $h3, $m); print_r($m[1]);
