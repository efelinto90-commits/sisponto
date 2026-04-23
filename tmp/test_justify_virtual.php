<?php
require_once 'config/Database.php';
$conn = Config\Database::getConnection();

// Mocking POST request for justification
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SESSION['user_level'] = 1;
$_SESSION['user_name'] = 'suporte';
$_SESSION['user_setor'] = 'TI';

// We'll use a virtual ID for an employee
$f = $conn->query("SELECT id FROM funcionarios LIMIT 1")->fetch();
$funcId = $f['id'];
$date = '2026-04-09';
$virtualId = "v_{$funcId}_{$date}";

// Check if record already exists (to clean up if necessary)
$conn->prepare("DELETE FROM registros WHERE id_funcionario = ? AND data = ?")->execute([$funcId, $date]);

// Mocking input
$input = [
    'action' => 'justificar',
    'id' => $virtualId,
    'tipo_justificativa' => 'Saúde',
    'justificativa' => 'Atestado médico de teste',
    'status_turno1' => 'justificado',
    'status_turno2' => ''
];

$_POST = $input; // The API also looks at $_POST

ob_start();
chdir('api');
include 'relatorios.php';
chdir('..');
$output = ob_get_clean();

echo "API Response: " . $output . "\n";

// Verify if record was created
$stmt = $conn->prepare("SELECT * FROM registros WHERE id_funcionario = ? AND data = ?");
$stmt->execute([$funcId, $date]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo "Success: Record created in database!\n";
    echo "ID: {$row['id']} | Data: {$row['data']} | Just: {$row['justificativa']}\n";
} else {
    echo "Error: Record not found in database.\n";
}
