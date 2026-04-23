<?php
require_once 'c:\xampp\htdocs\sisponto\config\Database.php';
$conn = Config\Database::getConnection();

// 1. Marcar um funcionário como exonerado para teste (Matrícula 1234 se existir, ou pegar o primeiro)
$stmt = $conn->query("SELECT id, matricula, nome FROM funcionarios LIMIT 1");
$func = $stmt->fetch();

if (!$func) {
    die("Nenhum funcionário encontrado para teste.\n");
}

echo "Testando com funcionário: {$func['nome']} (ID: {$func['id']})\n";

// Marcar como exonerado
$conn->prepare("UPDATE funcionarios SET is_exonerado = true WHERE id = :id")->execute([':id' => $func['id']]);
echo "Status alterado para EXONERADO.\n";

// Simular chamada de API get_metodos
$ch = curl_init('http://localhost:81/sisponto/api/ponto.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['action' => 'get_metodos', 'matricula' => $func['matricula']]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
curl_close($ch);

echo "Resposta da API (Relógio): " . $res . "\n";

// Voltar ao normal
$conn->prepare("UPDATE funcionarios SET is_exonerado = false WHERE id = :id")->execute([':id' => $func['id']]);
echo "Status restaurado para ATIVO.\n";
