<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $stmt = $conn->query("
        SELECT f.id, f.nome, f.matricula, f.setor, f.cpf, f.foto_perfil, f.biometria_facial, f.codigo_qr,
               f.lat_permitida, f.long_permitida, f.distancia_max_permitida, f.area_geofencing,
               h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario,
               (CASE WHEN f.biometria IS NOT NULL THEN 1 ELSE 0 END) as tem_biometria,
               (CASE WHEN f.foto_perfil IS NOT NULL AND f.foto_perfil <> '' THEN 1 ELSE 0 END) as tem_foto,
               (CASE WHEN f.biometria_facial IS NOT NULL AND f.biometria_facial <> '' THEN 1 ELSE 0 END) as tem_facial,
               (CASE WHEN f.codigo_qr IS NOT NULL AND f.codigo_qr <> '' THEN 1 ELSE 0 END) as tem_qr
        FROM funcionarios f
        LEFT JOIN horarios h ON f.id_horario = h.id
        ORDER BY f.nome ASC
    ");
    $funcionarios = $stmt->fetchAll();
    echo "Count: " . count($funcionarios) . "\n";
    if (count($funcionarios) > 0) {
        print_r($funcionarios[0]);
    }
} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}
