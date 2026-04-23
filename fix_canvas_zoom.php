<?php
$file = 'views/relogio.php';
$content = file_get_contents($file);

$target = '<canvas id="videoCanvas" class="hidden" style="transform: scaleX(-1);"></canvas>';
$replacement = '<canvas id="videoCanvas" class="absolute h-full w-full object-cover z-10 hidden" style="transform: scaleX(-1);"></canvas>';

if (strpos($content, $target) !== false) {
    $newContent = str_replace($target, $replacement, $content);
    file_put_contents($file, $newContent);
    echo "SUCCESS: Canvas classes updated.\n";
} else {
    echo "FAILED: Target literal not found. Trying regex...\n";
    $pattern = '/<canvas id="videoCanvas" class="hidden" style="transform: scaleX\(-1\);"><\/canvas>/';
    $newContent = preg_replace($pattern, $replacement, $content);
    if ($newContent !== $content) {
        file_put_contents($file, $newContent);
        echo "SUCCESS: Canvas classes updated via REGEX.\n";
    } else {
        echo "CRITICAL: Could not find videoCanvas element.\n";
    }
}
