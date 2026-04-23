<?php
$h2 = file_get_contents('timbre/extracted/word/header2.xml');
// print specific tags surrounding rId1, rId2, rId3
preg_match_all('/<wp:docPr id=\"([0-9]*)\" name=\"([^\"]+)\"([^>]+)\/>/', $h2, $names);
print_r($names);

preg_match_all('/<wp:extent cx=\"([0-9]+)\" cy=\"([0-9]+)\"\/>/', $h2, $extents);
print_r($extents);
