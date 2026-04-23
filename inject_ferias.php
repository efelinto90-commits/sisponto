<?php
$f = 'c:/xampp/htdocs/sis-ponto/views/admin/funcionarios.php';
$c = file_get_contents($f);

// If not already injected
if (strpos($c, 'ferias_modal.php') === false) {
    $search = "<?php include 'layout/footer.php'; ?>";
    $replace = "<?php include 'ferias_modal.php'; ?>\n" . $search;

    $c = str_replace($search, $replace, $c);
    file_put_contents($f, $c);
    echo "Injected successfully.";
} else {
    echo "Already injected.";
}
