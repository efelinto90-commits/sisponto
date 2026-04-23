<?php
// Script para forçar logout de todas as sessões
session_start();
session_destroy();

// Limpa cookies de sessão
setcookie(session_name(), '', time() - 3600, '/');

// Redireciona para login
echo '<h2>Sessão limpa com sucesso!</h2>';
echo '<p>Em 3 segundos você será redirecionado para o login...</p>';
echo '<meta http-equiv="refresh" content="3;url=/sisponto/views/login.php">';
