<?php
session_start();
header('Content-Type: application/json');
echo json_encode([
    'user_id' => $_SESSION['user_id'] ?? 'N/A',
    'user_name' => $_SESSION['user_name'] ?? 'N/A',
    'user_level' => $_SESSION['user_level'] ?? 'N/A',
    'user_level_type' => gettype($_SESSION['user_level'] ?? null),
    'user_permissions' => $_SESSION['user_permissions'] ?? [],
    'user_setor' => $_SESSION['user_setor'] ?? 'N/A'
], JSON_PRETTY_PRINT);
?>
