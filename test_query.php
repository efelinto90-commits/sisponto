<?php
try {
    $pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=sis-ponto', 'postgres', 'master10');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET search_path TO ponto");

    $sql = "SELECT f.id, f.nome, f.matricula, f.setor, f.cpf, f.foto_perfil, f.biometria_facial, f.codigo_qr,
                   h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario,
                   (CASE WHEN f.biometria IS NOT NULL THEN 1 ELSE 0 END) as tem_biometria,
                   (CASE WHEN f.foto_perfil IS NOT NULL AND f.foto_perfil <> '' THEN 1 ELSE 0 END) as tem_foto,
                   (CASE WHEN f.biometria_facial IS NOT NULL AND f.biometria_facial <> '' THEN 1 ELSE 0 END) as tem_facial,
                   (CASE WHEN f.codigo_qr IS NOT NULL AND f.codigo_qr <> '' THEN 1 ELSE 0 END) as tem_qr
            FROM funcionarios f
            LEFT JOIN horarios h ON f.id_horario = h.id
            ORDER BY f.nome ASC";

    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "TOTAL ROWS: " . count($data) . "\n";
    if (count($data) > 0) {
        print_r($data[0]);
    } else {
        echo "NO DATA FOUND\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
