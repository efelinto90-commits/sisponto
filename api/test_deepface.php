<?php
require_once '../config/Database.php';
use Config\Database;

function callDeepFace($action, $data) {
    $url = "http://127.0.0.1:5000/" . $action;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) return ['success' => false, 'message' => "CURL Error: " . $error];
    return json_decode($response, true);
}

header('Content-Type: text/plain');

echo "--- Testing DeepFace Service Connectivity ---\n";
$res = callDeepFace('analyze', ['image_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==']); // Empty tiny image

if (!$res) {
    echo "ERROR: Service not responding or returned empty response.\n";
    echo "Make sure to run: python api/deepface_service.py\n";
} else {
    echo "Response received:\n";
    print_r($res);
}

echo "\n--- Checking uploads/facial/ directory ---\n";
$dir = "../uploads/facial/";
if (!is_dir($dir)) {
    echo "ERROR: Directory $dir does not exist.\n";
} else {
    $files = glob($dir . "*.{jpg,jpeg,png}", GLOB_BRACE);
    echo "Found " . count($files) . " images in $dir\n";
    if (count($files) > 0) {
        echo "Example image: " . basename($files[0]) . "\n";
    }
}
