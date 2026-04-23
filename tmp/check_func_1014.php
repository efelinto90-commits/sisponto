<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $id_func = 1014;
    $today = date('Y-m-d');
    
    echo "--- Funcionário ID: $id_func ---\n";
    $stmt = $conn->prepare("SELECT id, nome, matricula, id_horario, grade_horarios, facial_descriptor FROM funcionarios WHERE id = :id");
    $stmt->execute([':id' => $id_func]);
    $func = $stmt->fetch();
    print_r($func);
    
    if ($func && $func['id_horario']) {
        echo "\n--- Horário Vinculado ---\n";
        $stmtH = $conn->prepare("SELECT * FROM horarios WHERE id = :id");
        $stmtH->execute([':id' => $func['id_horario']]);
        $horario = $stmtH->fetch();
        print_r($horario);
    }
    
    echo "\n--- Registros de Hoje ($today) ---\n";
    $stmtR = $conn->prepare("SELECT * FROM registros WHERE id_funcionario = :id AND data = :data");
    $stmtR->execute([':id' => $id_func, ':data' => $today]);
    $registro = $stmtR->fetch();
    print_r($registro);

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
