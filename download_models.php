<?php
/**
 * Script para baixar os modelos do face-api.js para o servidor local.
 * Execute este script UMA VEZ pelo navegador ou CLI para hospedar os modelos
 * localmente e eliminar a dependência do CDN externo.
 *
 * Uso: http://localhost/sisponto/download_models.php
 */

if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

$targetDir = __DIR__ . '/models/face-api/';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$base = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/';

$files = [
    // TinyFaceDetector
    'tiny_face_detector_model-shard1',
    'tiny_face_detector_model-weights_manifest.json',
    // FaceLandmark68Net
    'face_landmark_68_model-shard1',
    'face_landmark_68_model-weights_manifest.json',
    // FaceRecognitionNet
    'face_recognition_model-shard1',
    'face_recognition_model-shard2',
    'face_recognition_model-weights_manifest.json',
];

$ok = 0;
$skip = 0;
$fail = 0;

foreach ($files as $file) {
    $dest = $targetDir . $file;

    if (file_exists($dest) && filesize($dest) > 0) {
        echo "SKIP  $file\n";
        $skip++;
        continue;
    }

    $url  = $base . $file;
    $data = @file_get_contents($url);

    if ($data === false) {
        echo "FAIL  $file  (erro ao baixar de $url)\n";
        $fail++;
        continue;
    }

    file_put_contents($dest, $data);
    echo "OK    $file  (" . round(strlen($data) / 1024, 1) . " KB)\n";
    $ok++;
}

echo "\n---\nBaixados: $ok  |  Pulados: $skip  |  Falhas: $fail\n";
if ($fail === 0) {
    echo "Modelos prontos em /models/face-api/\n";
    echo "O sistema usará os modelos locais automaticamente.\n";
} else {
    echo "Alguns arquivos falharam. Verifique a conexão e tente novamente.\n";
}
