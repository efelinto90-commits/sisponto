<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    
    echo "--- DADOS DETALHADOS FUNCIONARIO 2236-5 ---\n";
    $stmt = $conn->prepare("SELECT f.id, f.nome, f.matricula, f.id_horario, h.nome as nome_horario, h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario 
                            FROM funcionarios f 
                            LEFT JOIN horarios h ON f.id_horario = h.id 
                            WHERE f.matricula = '2236-5'");
    $stmt->execute();
    $func = $stmt->fetch();
    print_r($func);

    echo "\n--- REGISTRO DO DIA 2026-03-12 ---\n";
    $stmt = $conn->prepare("SELECT * FROM registros WHERE id_funcionario = :id AND data = '2026-03-12'");
    $stmt->execute([':id' => $func['id']]);
    $reg = $stmt->fetch();
    print_r($reg);

    echo "\n--- VERIFICANDO TOLERANCIA NO BANCO ---\n";
    $stmt = $conn->prepare("SELECT id, tolerancia_entrada, tolerancia_saida FROM horarios WHERE id = :hid");
    $stmt->execute([':hid' => $func['id_horario']]);
    $hor = $stmt->fetch();
    print_r($hor);

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
