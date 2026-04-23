<?php
$dir = 'timbre/extracted/word/media/';
foreach(scandir($dir) as $f) {
    if(is_file($dir.$f)) {
        $s = getimagesize($dir.$f);
        echo $f . ': ' . $s[0] . 'x' . $s[1] . "\n";
    }
}
