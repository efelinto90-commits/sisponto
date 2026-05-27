<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNOSTICO DE AMBIENTE PYTHON (VENV) ===\n\n";

$venv_path = "C:\\venv_ponto";
$scripts_path = "$venv_path\\Scripts";
$python_exe = "$scripts_path\\python.exe";

echo "1. Verificando pasta do VENV ($venv_path): ";
if (is_dir($venv_path)) {
    echo "OK\n";
} else {
    echo "FALHA (Pasta nao existe)\n";
}

echo "2. Verificando executavel Python ($python_exe): ";
if (file_exists($python_exe)) {
    echo "OK\n";
} else {
    echo "FALHA (Python nao encontrado no VENV)\n";
}

echo "3. Testando execucao do Python: ";
$output = [];
$return_var = 0;
exec("\"$python_exe\" --version 2>&1", $output, $return_var);

if ($return_var === 0) {
    echo "OK (" . implode("", $output) . ")\n";
} else {
    echo "FALHA (Erro ao executar Python)\n";
    print_r($output);
}

echo "\n4. Verificando modulo DeepFace: ";
$output = [];
exec("\"$python_exe\" -c \"import deepface; print(deepface.__version__)\" 2>&1", $output, $return_var);
if ($return_var === 0) {
    echo "OK (Versao: " . implode("", $output) . ")\n";
} else {
    echo "FALHA (Modulo DeepFace nao instalado ou com erro)\n";
    echo "Log de erro: " . implode("\n", $output) . "\n";
}

echo "\n--- ACOES RECOMENDADAS ---\n";
if ($return_var !== 0) {
    echo "- Execute o arquivo 'api/setup_venv.bat' como ADMINISTRADOR.\n";
    echo "- Se o erro persistir, apague a pasta 'C:\\venv_ponto' e rode o setup novamente.\n";
} else {
    echo "- O ambiente parece OK! Rode 'api/run_deepface.bat' para iniciar o serviço.\n";
}
