<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: text/html; charset=utf-8');

// Proteção de Rota
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Helper de permissão
$permissions = $_SESSION['user_permissions'] ?? [];
function hasPerm($mod)
{
    global $permissions;
    return in_array($mod, $permissions);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - FUNAD</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#f0f9ff', 100: '#e0f2fe', 500: '#0ea5e9', 600: '#0284c7', 900: '#0c4a6e' }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- DataTables com Tailwind UI -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <link rel="stylesheet" href="../../public/css/style.css">

    <!-- PWA -->
    <meta name="theme-color" content="#0284c7">
    <link rel="manifest" href="../../manifest.json">
    <link rel="apple-touch-icon" href="../../public/img/icon-192.png">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('../../service-worker.js')
                    .then(reg => console.log('Service Worker registrado com sucesso:', reg.scope))
                    .catch(err => console.log('Falha ao registrar o Service Worker:', err));
            });
        }

        // Global Helper for Button Loading States
        window.setLoading = function(btn, isLoading) {
            if (!btn) return;
            // If it's a jQuery object, get the DOM element
            if (btn.jquery) btn = btn[0];
            
            if (isLoading) {
                btn.classList.add('btn-loading');
                btn.disabled = true;
            } else {
                btn.classList.remove('btn-loading');
                btn.disabled = false;
            }
        };
    </script>
</head>

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex font-sans overflow-hidden">

    <!-- Sidebar -->
    <aside
        class="w-64 bg-slate-900 text-white flex flex-col h-screen transition-all duration-300 shadow-2xl z-20 hidden md:flex">

        <div class="p-8 flex items-center gap-4 border-b border-white/5">
            <div class="w-10 h-10 rounded-xl bg-brand-500 flex items-center justify-center shadow-lg shadow-brand-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <span class="text-2xl font-black tracking-tight">SisPonto</span>
        </div>

        <nav class="flex-1 p-4 space-y-1 overflow-y-auto sidebar-scroll">

            <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-4 mt-8">Menu Principal</p>

            <a href="index.php"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-white/10 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-brand-600 text-white' : 'text-slate-300'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                    </path>
                </svg>
                Dashboard
            </a>

            <?php if (hasPerm('funcionarios')): ?>
                <a href="funcionarios.php"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-white/10 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'funcionarios.php' ? 'bg-brand-600 text-white' : 'text-slate-300'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    Funcionários
                </a>
            <?php endif; ?>

            <a href="cartao_ponto.php"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-white/10 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'cartao_ponto.php' ? 'bg-brand-600 text-white' : 'text-slate-300'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
                Cartão de Ponto<br>Diário
            </a>

            <a href="ponto_justificado.php"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-white/10 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'ponto_justificado.php' ? 'bg-brand-600 text-white' : 'text-slate-300'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Tratar Ponto
            </a>

            <?php 
            $is_crh_viewer = (($_SESSION['user_level'] ?? '') == '1' || in_array(strtolower(trim($_SESSION['user_name'] ?? '')), ['corsin', 'crh']) || in_array(strtolower(trim($_SESSION['user_setor'] ?? '')), ['corsin', 'crh']));
            if ($is_crh_viewer): 
            ?>
            <a href="ponto_aprovacao.php"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-white/10 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'ponto_aprovacao.php' ? 'bg-brand-600 text-white' : 'text-slate-300'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Aprovação CRH
            </a>

            <a href="ponto_liberado.php"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-white/10 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'ponto_liberado.php' ? 'bg-brand-600 text-white' : 'text-slate-300'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                </svg>
                Liberar Ponto
            </a>

            <a href="afastamentos.php"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-white/10 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'afastamentos.php' ? 'bg-brand-600 text-white' : 'text-slate-300'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zM10 13l4 4m0-4l-4 4"></path>
                </svg>
                Afastamentos
            </a>
            <?php endif; ?>

            <?php if (hasPerm('relatorios')): ?>
                <a href="relatorios.php"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-white/10 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'relatorios.php' ? 'bg-brand-600 text-white' : 'text-slate-300'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Relatórios Ponto
                </a>
                <?php if ($is_crh_viewer): ?>
                <a href="relatorio_profissional.php"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-white/10 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'relatorio_profissional.php' ? 'bg-brand-600 text-white' : 'text-slate-300'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    Relatório Profissional
                </a>
                <?php endif; ?>
            <?php endif; ?>

            <a href="geolocalizacao.php"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-white/10 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'geolocalizacao.php' ? 'bg-brand-600 text-white' : 'text-slate-300'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Geolocalização
            </a>

            <p class="px-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6">Configurações</p>

            <?php 
            $is_admin_or_crh_config = (($_SESSION['user_level'] ?? '') == '1' || in_array(strtolower(trim($_SESSION['user_name'] ?? '')), ['corsin', 'crh']) || in_array(strtolower(trim($_SESSION['user_setor'] ?? '')), ['corsin', 'crh']));
            if ($is_admin_or_crh_config && hasPerm('horarios')): 
            ?>
                <a href="horarios.php"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-white/10 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'horarios.php' ? 'bg-brand-600 text-white' : 'text-slate-300'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Horários
                </a>
            <?php endif; ?>

            <?php if ($is_admin_or_crh_config && hasPerm('cargos')): ?>
                <a href="cargos.php"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-white/10 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'cargos.php' ? 'bg-brand-600 text-white' : 'text-slate-300'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                    Cargos
                </a>
            <?php endif; ?>

            <?php 
            $is_crh_users = (($_SESSION['user_level'] ?? '') == '1' || in_array(strtolower(trim($_SESSION['user_name'] ?? '')), ['corsin', 'crh']) || in_array(strtolower(trim($_SESSION['user_setor'] ?? '')), ['corsin', 'crh']));
            if (hasPerm('usuarios') || $is_crh_users): 
            ?>
                <a href="usuarios.php"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-white/10 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'usuarios.php' ? 'bg-brand-600 text-white' : 'text-slate-300'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                    Usuários do Sistema
                </a>
            <?php endif; ?>

            <?php if (($_SESSION['user_level'] ?? '') == '1'): ?>
                <p class="px-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6">Sistema</p>

                <a href="db_manager.php"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-white/10 hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'db_manager.php' ? 'bg-brand-600 text-white' : 'text-slate-300'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582 4-8 4m16 0c0 2.21-3.582 4-8 4m0-4v4m0 4v4">
                        </path>
                    </svg>
                    Banco de Dados
                </a>
            <?php endif; ?>

        </nav>

        <div class="p-4 border-t border-white/5 bg-slate-900/50">
            <div class="flex items-center gap-3 mb-6 px-2">
                <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-white text-xl font-bold border border-white/10 shadow-inner">
                    <?php echo strtolower(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-white truncate">
                        <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuário'); ?>
                    </p>
                    <p class="text-xs text-slate-400 truncate font-medium">
                        <?php
                        $lvlNames = ['1' => 'Administrador', '2' => 'Gestor', '3' => 'Usuário'];
                        echo $lvlNames[$_SESSION['user_level'] ?? '3'] ?? 'Usuário';
                        ?>
                    </p>
                </div>
            </div>
            <div class="flex flex-col gap-2">
                <button onclick="openChangePasswordModal()"
                    class="w-full flex items-center justify-center gap-3 px-4 py-2.5 rounded-xl bg-slate-800/50 hover:bg-slate-800 text-slate-300 hover:text-white transition-all text-sm font-bold border border-white/5 shadow-sm group">
                    <svg class="w-4 h-4 transition-transform group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                        </path>
                    </svg>
                    Trocar Senha
                </button>
                <button onclick="logout()"
                    class="w-full flex items-center justify-center gap-3 px-4 py-2.5 rounded-xl bg-slate-800/50 hover:bg-slate-800 text-slate-300 hover:text-white transition-all text-sm font-bold border border-white/5 shadow-sm group">
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                    Sair do Sistema
                </button>
            </div>
        </div>
    </aside>

    <!-- Modal Troca de Senha Global -->
    <div id="modalChangePassword" class="fixed inset-0 z-[60] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeChangePasswordModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <h3 class="text-lg font-bold text-slate-800">Alterar Minha Senha</h3>
                        <button onclick="closeChangePasswordModal()" class="text-slate-400 hover:bg-slate-100 p-2 rounded-lg transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="p-6">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nova Senha</label>
                        <input type="password" id="new_password_global" class="w-full px-4 py-2 border rounded-xl focus:ring-1 focus:ring-brand-500 bg-slate-50 outline-none" placeholder="Digite sua nova senha">
                        <p class="text-xs text-slate-500 mt-2 italic">Dica: Use uma senha forte que você não use em outros lugares.</p>
                    </div>
                    <div class="px-6 py-4 bg-slate-50 flex justify-end gap-3 rounded-b-2xl">
                        <button onclick="closeChangePasswordModal()" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200 rounded-xl transition-all border border-slate-200">Cancelar</button>
                        <button onclick="confirmChangePassword()" class="px-4 py-2 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-500 rounded-xl transition-all shadow-sm">Salvar Nova Senha</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50/50">
        <!-- Topbar Mobile (visível apenas em telas pequenas) -->
        <header
            class="md:hidden bg-white shadow-sm border-b border-slate-200 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <?php if (basename($_SERVER['PHP_SELF']) != 'index.php'): ?>
                    <a href="index.php"
                        class="w-10 h-10 -ml-2 rounded-xl flex items-center justify-center text-slate-400 hover:bg-slate-50 hover:text-brand-600 transition-all active:scale-90">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                <?php endif; ?>
                <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-lg font-bold text-slate-800 tracking-tight">SisPonto</span>
            </div>
            <!-- Botão de Sair no Mobile (Opcional - mas útil rápido) -->
            <button onclick="logout()"
                class="w-10 h-10 -mr-2 rounded-xl flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all active:scale-90">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                    </path>
                </svg>
            </button>
        </header>

        <!-- Dynamic Page Content -->
        <div class="flex-1 overflow-y-auto p-4 md:p-8 pb-24 md:pb-8">
<script>
    function openChangePasswordModal() {
        const modal = document.getElementById('modalChangePassword');
        if (modal) modal.classList.remove('hidden');
    }

    function closeChangePasswordModal() {
        const modal = document.getElementById('modalChangePassword');
        if (modal) modal.classList.add('hidden');
        const input = document.getElementById('new_password_global');
        if (input) input.value = '';
    }

    async function confirmChangePassword() {
        const newPassword = document.getElementById('new_password_global').value;
        if (!newPassword) {
            Swal.fire('Atenção', 'Digite a nova senha.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Alterando senha...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            // Caminho para ../../api/usuarios.php (estando em views/admin/)
            const response = await fetch('../../api/usuarios.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'change_password', password: newPassword })
            });
            const data = await response.json();

            if (data.success) {
                Swal.fire('Sucesso!', 'Senha alterada com sucesso.', 'success');
                closeChangePasswordModal();
            } else {
                Swal.fire('Erro', data.message || 'Erro ao alterar senha.', 'error');
            }
        } catch (error) {
            Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
        }
    }
    
    function logout() {
        Swal.fire({
            title: 'Deseja sair?',
            text: "Sua sessão será encerrada.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sim, sair!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // logout.php está no mesmo diretório views/admin/
                window.location.href = 'logout.php';
            }
        });
    }
</script>

            <?php if (basename($_SERVER['PHP_SELF']) != 'index.php'): ?>
                <div class="flex items-center gap-2 mb-6 hidden md:flex">
                    <a href="index.php"
                        class="group inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-white border border-slate-200 text-slate-600 hover:text-brand-600 hover:border-brand-200 hover:bg-brand-50/50 transition-all duration-200 text-xs font-bold shadow-sm ring-1 ring-transparent hover:ring-brand-100">
                        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-300" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Voltar ao Painel Principal
                    </a>
                </div>
            <?php endif; ?>