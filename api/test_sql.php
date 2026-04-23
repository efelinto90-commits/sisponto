<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    $stmt = $conn->prepare("
        SELECT f.id, f.nome, f.matricula, f.setor, f.setor2, f.cpf,
               f.lat_permitida, f.long_permitida, f.distancia_max_permitida, f.area_geofencing,
               h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario,
               (CASE WHEN f.biometria IS NOT NULL THEN 1 ELSE 0 END) as tem_biometria,
               (CASE WHEN f.foto_perfil IS NOT NULL AND f.foto_perfil <> '' THEN 1 ELSE 0 END) as tem_foto,
               (CASE WHEN f.biometria_facial IS NOT NULL AND f.biometria_facial <> '' THEN 1 ELSE 0 END) as tem_facial,
               (CASE WHEN f.codigo_qr IS NOT NULL AND f.codigo_qr <> '' THEN 1 ELSE 0 END) as tem_qr,
               f.grade_horarios,
               f.is_exonerado,
               f.motivo_exoneracao,
               (SELECT data_fim FROM ferias WHERE id_funcionario = f.id AND CURRENT_DATE BETWEEN data_inicio AND data_fim LIMIT 1) as data_fim_afastamento
        FROM funcionarios f
        LEFT JOIN horarios h ON f.id_horario = h.id
        ORDER BY f.nome ASC
    ");
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "SUCCESS: " . count($res) . " rows";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
