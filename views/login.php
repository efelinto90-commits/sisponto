<?php
session_start();

// Se já estiver logado, redireciona para o admin
if (isset($_SESSION['user_id'])) {
    header('Location: admin/index.php');
    exit;
}

include 'layout/header.php';
?>

<div class="flex-1 flex items-center justify-center relative bg-slate-50 overflow-hidden">

    <!-- Decorações de Fundo -->
    <div
        class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-brand-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob">
    </div>
    <div
        class="absolute top-[-10%] right-[-10%] w-96 h-96 bg-brand-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000">
    </div>

    <div class="w-full max-w-md relative z-10 px-4">
        <div class="bg-white rounded-3xl p-8 sm:p-10 shadow-xl border border-slate-100">

            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-brand-50 text-brand-600 mb-4 shadow-inner">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Acesso Administrativo</h1>
                <p class="text-sm text-slate-500 mt-2">Faça login para gerenciar o sistema</p>
            </div>

            <form id="loginForm" onsubmit="fazerLogin(event)" class="space-y-5">

                <!-- Feedback Error -->
                <div id="loginError"
                    class="hidden bg-red-50 text-red-600 text-sm p-3 rounded-xl border border-red-100 flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span id="errorText">Credenciais inválidas.</span>
                </div>

                <div class="space-y-1">
                    <label for="username" class="block text-sm font-semibold text-slate-700">Usuário
                        Administrativo</label>
                    <input type="text" id="username" required
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition-colors"
                        placeholder="suporte">
                </div>

                <div class="space-y-1 relative">
                    <label for="password" class="block text-sm font-semibold text-slate-700 ml-1">Senha</label>
                    <div class="relative">
                        <input type="password" id="password" required
                            class="w-full pl-4 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all placeholder:text-slate-400 font-medium"
                            placeholder="Sua senha secreta">
                        <button type="button" onclick="togglePassword('password', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-slate-400 hover:text-brand-600 transition-colors focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                id="eye-icon-password">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" id="btnLogin"
                    class="w-full flex justify-center py-3.5 px-4 rounded-xl shadow-md text-base font-bold text-white bg-slate-800 hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all active:scale-[0.98]">
                    Entrar no Painel
                </button>
            </form>

            <div class="mt-8 text-center">
                <a href="relogio.php" class="text-sm font-medium text-slate-400 hover:text-brand-600 transition-colors">
                    &larr; Voltar para o relógio de ponto
                </a>
            </div>

        </div>
    </div>
</div>

<script>
    async function fazerLogin(e) {
        e.preventDefault();
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        const btnLogin = document.getElementById('btnLogin');
        const loginError = document.getElementById('loginError');
        const errorText = document.getElementById('errorText');

        loginError.classList.add('hidden');

        try {
            btnLogin.disabled = true;
            btnLogin.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Validando...`;

            const res = await fetch('../api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'login', username, password })
            });

            const data = await res.json();

            if (data.success) {
                window.location.href = 'admin/index.php';
            } else {
                errorText.textContent = data.message || 'Erro ao efetuar login.';
                loginError.classList.remove('hidden');
            }

        } catch (err) {
            errorText.textContent = 'Erro de comunicação com o servidor.';
            loginError.classList.remove('hidden');
        } finally {
            btnLogin.disabled = false;
            btnLogin.innerHTML = 'Entrar no Painel';
        }
    }
</script>

<?php include 'layout/footer.php'; ?>