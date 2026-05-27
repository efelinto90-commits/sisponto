<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: admin/index.php'); exit; }
include 'layout/header.php';
?>

<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<div class="flex-1 flex items-center justify-center relative overflow-hidden bg-gradient-to-br from-brand-900 via-brand-600 to-sky-500">

    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCI+PHBhdGggZD0iTTAgMGgyeTR2MjRIMGoiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wMSkiLz48L3N2Zz4=')] opacity-20"></div>

    <div class="w-full max-w-sm relative z-10 px-4">
        <div class="glass rounded-3xl p-8 shadow-2xl relative overflow-hidden border border-white/20 flex flex-col">

            <div class="text-center mb-8 mt-2">
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight gap-2 flex justify-center items-center">
                    <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Acesso Restrito
                </h1>
                <p class="text-slate-500 text-sm mt-1 font-medium">FUNAD &bull; Painel Administrativo</p>
            </div>

            <!-- Login por senha -->
            <div id="senhaCollapse" class="space-y-3 mb-2">
                <div id="loginError" class="hidden bg-red-50 text-red-600 text-sm p-3 rounded-xl border border-red-200 flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span id="errorText">Credenciais inválidas.</span>
                </div>
                <form id="loginForm" onsubmit="fazerLogin(event)" class="space-y-4">
                    <input type="text" id="username" required
                        class="block w-full px-4 py-3.5 border border-slate-200 rounded-xl bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 font-medium transition-colors shadow-sm"
                        placeholder="Usuário administrativo" autocomplete="off">
                    <div class="relative">
                        <input type="password" id="password" required
                            class="block w-full pl-4 pr-12 py-3.5 border border-slate-200 rounded-xl bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 font-medium transition-colors shadow-sm"
                            placeholder="Senha">
                        <button type="button" onclick="togglePassword('password',this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-slate-400 hover:text-brand-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <button type="submit" id="btnLogin"
                        class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-md text-base font-bold text-white bg-brand-600 hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all hover:-translate-y-0.5 transform active:translate-y-0 mt-2">
                        Entrar no Sistema
                    </button>
                </form>
                
                <a href="#" onclick="abrirModalAlterarSenha(event)" 
                    class="text-xs text-slate-400 hover:text-brand-600 transition-colors block text-center mt-3.5 font-semibold">
                    Esqueceu sua senha? Alterar Senha
                </a>
            </div>

        </div>


    </div>



</div>

<script>
function togglePassword(id, btn) {
    const inp = document.getElementById(id);
    inp.type = inp.type === 'password' ? 'text' : 'password';
}

async function fazerLogin(e) {
    e.preventDefault();
    const btn = document.getElementById('btnLogin');
    const err = document.getElementById('loginError');
    err.classList.add('hidden');
    btn.disabled = true;
    btn.textContent = 'Validando...';
    try {
        const res  = await fetch('../api/auth.php', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action:'login', username: document.getElementById('username').value.trim(), password: document.getElementById('password').value })
        });
        const data = await res.json();
        if (data.success) { window.location.href = 'admin/index.php'; }
        else { document.getElementById('errorText').textContent = data.message || 'Credenciais inválidas.'; err.classList.remove('hidden'); }
    } catch(e) { document.getElementById('errorText').textContent = 'Erro de comunicação.'; err.classList.remove('hidden'); }
    finally { btn.disabled = false; btn.textContent = 'Entrar no Sistema'; }
}

async function abrirModalAlterarSenha(e) {
    e.preventDefault();
    
    const result = await Swal.fire({
        title: 'Alterar Senha de Acesso',
        html: `
            <p class="text-xs text-slate-500 mb-4 text-center leading-relaxed">Confirme seu usuário e setor cadastrado para validar sua identidade e atualizar sua senha mantendo as permissões atuais.</p>
            <div class="space-y-3.5 text-left">
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Nome de Usuário</label>
                    <input type="text" id="swal-username" class="swal2-input w-full m-0 text-sm px-3.5 py-2.5 font-semibold text-slate-800" placeholder="Ex: RAFAEL.COELHO" autocomplete="off">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Setor Cadastrado</label>
                    <input type="text" id="swal-setor" class="swal2-input w-full m-0 text-sm px-3.5 py-2.5 font-semibold text-slate-800" placeholder="Ex: CRH (Deixe em branco se for suporte)">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Nova Senha</label>
                    <input type="password" id="swal-newpass" class="swal2-input w-full m-0 text-sm px-3.5 py-2.5 font-semibold text-slate-800" placeholder="Mínimo de 6 caracteres">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Confirmar Nova Senha</label>
                    <input type="password" id="swal-confpass" class="swal2-input w-full m-0 text-sm px-3.5 py-2.5 font-semibold text-slate-800" placeholder="Repita a nova senha">
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0ea5e9',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Alterar Senha',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'premium-swal rounded-3xl',
            title: 'premium-title text-slate-800',
            confirmButton: 'premium-confirm px-5 py-2.5 text-sm font-semibold rounded-xl text-white bg-sky-500 hover:bg-sky-600',
            cancelButton: 'premium-cancel px-5 py-2.5 text-sm font-semibold rounded-xl text-slate-700 bg-slate-100 hover:bg-slate-200'
        },
        preConfirm: () => {
            const username = document.getElementById('swal-username').value.trim();
            const setor = document.getElementById('swal-setor').value.trim();
            const newpass = document.getElementById('swal-newpass').value;
            const confpass = document.getElementById('swal-confpass').value;

            if (!username) {
                Swal.showValidationMessage("O nome de usuário é obrigatório!");
                return false;
            }
            if (!newpass || newpass.length < 6) {
                Swal.showValidationMessage("A nova senha deve ter no mínimo 6 caracteres!");
                return false;
            }
            if (newpass !== confpass) {
                Swal.showValidationMessage("As senhas informadas não coincidem!");
                return false;
            }

            return { username, setor, new_password: newpass };
        }
    });

    if (result.isConfirmed && result.value) {
        Swal.fire({ title: 'Salvando nova senha...', didOpen: () => { Swal.showLoading(); }, allowOutsideClick: false });
        try {
            const res = await fetch('../api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'alterar_senha', ...result.value })
            });
            const data = await res.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: data.message,
                    confirmButtonColor: '#0ea5e9',
                    confirmButtonText: 'Entendido'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao alterar',
                    text: data.message,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Corrigir'
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Erro estrutural',
                text: 'Não foi possível se comunicar com o servidor.',
                confirmButtonColor: '#ef4444'
            });
        }
    }
}
</script>

<?php include 'layout/footer.php'; ?>