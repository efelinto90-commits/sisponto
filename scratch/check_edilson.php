<?php
require_once 'config/Database.php';
use Config\Database;

$conn = Database::getConnection();
$stmt = $conn->prepare("SELECT f.*, h.primeiro_horario as h_p1, h.segundo_horario as h_p2, h.terceiro_horario as h_p3, h.quarto_horario as h_p4 FROM funcionarios f LEFT JOIN horarios h ON f.id_horario = h.id WHERE f.nome ILIKE '%Edilson Felinto%'");
$stmt->execute();
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Funcionarios encontrados:\n";
foreach ($res as $f) {
    echo "ID: " . $f['id'] . "\n";
    echo "Nome: " . $f['nome'] . "\n";
    echo "Setor: " . $f['setor'] . "\n";
    echo "Exonerado: " . ($f['is_exonerado'] ? 'Sim' : 'Não') . "\n";
    echo "Horário P1: " . $f['h_p1'] . "\n";
    echo "Grade: " . $f['grade_horarios'] . "\n";
}

foreach ($res as $f) {
    $stmt2 = $conn->prepare("SELECT * FROM ferias WHERE id_funcionario = :id");
    $stmt2->execute([':id' => $f['id']]);
    $ferias = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    echo "Afastamentos para " . $f['nome'] . ":\n";
    print_r($ferias);
}
