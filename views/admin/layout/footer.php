</div>
</main>
<!-- Modal Justificativa (Global) -->
<div id="modalJustificativa" class="fixed inset-0 z-[110] hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeModalJust()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-800" id="modalJustTitle">Justificar Falta/Atraso</h3>
                    <button onclick="closeModalJust()"
                        class="text-slate-400 hover:bg-slate-100 p-2 rounded-lg border shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form id="formJustificativa" onsubmit="salvarJustificativa(event)" class="px-6 py-6 space-y-5">
                    <input type="hidden" id="just_id">

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Entrada 1</label>
                                <textarea id="just_ent1" rows="2"
                                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors shadow-sm outline-none bg-slate-50 focus:bg-white resize-none"
                                    placeholder="Justificativa (Se houver)..."></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Saída 1</label>
                                <textarea id="just_sai1" rows="2"
                                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors shadow-sm outline-none bg-slate-50 focus:bg-white resize-none"
                                    placeholder="Justificativa (Se houver)..."></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Entrada 2</label>
                                <textarea id="just_ent2" rows="2"
                                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors shadow-sm outline-none bg-slate-50 focus:bg-white resize-none"
                                    placeholder="Justificativa (Se houver)..."></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Saída 2</label>
                                <textarea id="just_sai2" rows="2"
                                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors shadow-sm outline-none bg-slate-50 focus:bg-white resize-none"
                                    placeholder="Justificativa (Se houver)..."></textarea>
                            </div>
                        </div>

                        <!-- Manter justificativa geral opcional por compatibilidade retroativa -->
                        <div class="hidden">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Anotação Geral</label>
                            <textarea id="just_texto" rows="1"></textarea>
                            <input type="hidden" id="just_turno1">
                            <input type="hidden" id="just_turno2">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeModalJust()"
                            class="px-5 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-700 hover:bg-slate-50 font-semibold transition-colors">Cancelar</button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl font-semibold shadow-sm transition-colors">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    async function logout() {
        try {
            const res = await fetch('../../api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'logout' })
            });
            const data = await res.json();
            if (data.success) {
                window.location.href = '../login.php';
            }
        } catch (err) {
            console.error(err);
        }
    }


    // Modal Justificativa Logic
    const modalJust = document.getElementById('modalJustificativa');
    function abrirJustificativa(id, justificativaAtual, justEnt1, justSai1, justEnt2, justSai2, status1, status2) {
        document.getElementById('just_id').value = id;
        document.getElementById('just_texto').value = (justificativaAtual === 'null' || !justificativaAtual) ? '' : justificativaAtual;

        document.getElementById('just_ent1').value = (justEnt1 === 'null' || !justEnt1) ? '' : justEnt1;
        document.getElementById('just_sai1').value = (justSai1 === 'null' || !justSai1) ? '' : justSai1;
        document.getElementById('just_ent2').value = (justEnt2 === 'null' || !justEnt2) ? '' : justEnt2;
        document.getElementById('just_sai2').value = (justSai2 === 'null' || !justSai2) ? '' : justSai2;

        document.getElementById('just_turno1').value = (status1 === 'null' || !status1) ? '' : status1;
        document.getElementById('just_turno2').value = (status2 === 'null' || !status2) ? '' : status2;

        modalJust.classList.remove('hidden');
    }

    function closeModalJust() {
        modalJust.classList.add('hidden');
    }

    async function salvarJustificativa(e) {
        e.preventDefault();
        const id = document.getElementById('just_id').value;
        const texto = document.getElementById('just_texto').value;
        const st1 = document.getElementById('just_turno1').value;
        const st2 = document.getElementById('just_turno2').value;

        const je1 = document.getElementById('just_ent1').value;
        const js1 = document.getElementById('just_sai1').value;
        const je2 = document.getElementById('just_ent2').value;
        const js2 = document.getElementById('just_sai2').value;

        try {
            const res = await fetch('../../api/relatorios.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'justificar',
                    id: id,
                    justificativa: texto,
                    just_ent1: je1,
                    just_sai1: js1,
                    just_ent2: je2,
                    just_sai2: js2,
                    status_turno1: st1,
                    status_turno2: st2
                })
            });
            const data = await res.json();

            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
                }
                closeModalJust();

                // Refresh Daily Card if open
                if (window.location.pathname.includes('cartao_ponto.php')) {
                    loadDailyCardData();
                }

                // Refresh Relatorios table if it exists
                if (typeof tableRel !== 'undefined' && tableRel) {
                    tableRel.ajax.reload(null, false);
                }
            } else {
                if (typeof Swal !== 'undefined') Swal.fire('Erro', data.message, 'error');
                else console.error('Erro: ' + data.message);
            }
        } catch (err) {
            console.error(err);
        }
    }
</script>

<!-- Mobile Bottom Navigation Bar (Hidden on md+) -->
<nav
    class="md:hidden fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-xl border-t border-slate-200 z-[90] pb-safe shadow-[0_-4px_10px_rgba(0,0,0,0.02)]">
    <div class="flex items-center justify-around h-16 px-2">
        <!-- Início -->
        <a href="index.php"
            class="flex flex-col items-center justify-center w-full h-full space-y-1 <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-brand-600' : 'text-slate-400 hover:text-slate-800'; ?>">
            <svg class="w-6 h-6 <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'fill-brand-50 stroke-brand-600' : 'fill-none stroke-current'; ?> transition-all"
                stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                </path>
            </svg>
            <span class="text-[10px] font-semibold">Início</span>
        </a>

        <!-- Ponto Diário -->
        <a href="cartao_ponto.php"
            class="flex flex-col items-center justify-center w-full h-full space-y-1 <?php echo basename($_SERVER['PHP_SELF']) == 'cartao_ponto.php' ? 'text-brand-600' : 'text-slate-400 hover:text-slate-800'; ?> transition-all">
            <svg class="w-6 h-6 <?php echo basename($_SERVER['PHP_SELF']) == 'cartao_ponto.php' ? 'fill-brand-50 stroke-brand-600' : 'fill-none stroke-current'; ?>" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span class="text-[10px] font-semibold">Frequência</span>
        </a>

        <!-- Funcionários -->
        <?php if (hasPerm('funcionarios')): ?>
            <a href="funcionarios.php"
                class="flex flex-col items-center justify-center w-full h-full space-y-1 <?php echo basename($_SERVER['PHP_SELF']) == 'funcionarios.php' ? 'text-brand-600' : 'text-slate-400 hover:text-slate-800'; ?>">
                <svg class="w-6 h-6 <?php echo basename($_SERVER['PHP_SELF']) == 'funcionarios.php' ? 'fill-brand-50 stroke-brand-600' : 'fill-none stroke-current'; ?> transition-all"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                </svg>
                <span class="text-[10px] font-semibold">Equipe</span>
            </a>
        <?php endif; ?>

        <!-- Menu / Mais -->
        <button onclick="toggleMobileMenu()"
            class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-400 hover:text-slate-800 transition-all">
            <div class="w-6 h-6 bg-slate-100/50 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                    </path>
                </svg>
            </div>
            <span class="text-[10px] font-semibold">Menu</span>
        </button>
    </div>
</nav>

<!-- Mobile Bottom Sheet Menu -->
<div id="mobileBottomSheet" class="md:hidden fixed inset-0 z-[100] hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0" id="mobileBottomSheetBg"
        onclick="toggleMobileMenu()"></div>

    <div class="fixed inset-x-0 bottom-0 z-10 transform translate-y-full transition-transform duration-300 ease-out"
        id="mobileBottomSheetContent">
        <div class="bg-white rounded-t-3xl shadow-2xl p-6 pb-safe">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-6"></div>

            <div class="flex items-center gap-4 mb-8">
                <div
                    class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-lg font-bold text-brand-600">
                    <?php echo substr($_SESSION['user_name'], 0, 1); ?>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </h3>
                    <p class="text-xs text-slate-500">Administrador</p>
                </div>
            </div>

            <div class="space-y-2">
                <?php if (hasPerm('relatorios')): ?>
                    <a href="relatorios.php"
                        class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-slate-50 text-slate-700 font-semibold transition-colors">
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        Relatórios de Frequência
                    </a>
                <?php endif; ?>

                <?php if (hasPerm('horarios')): ?>
                    <a href="horarios.php"
                        class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-slate-50 text-slate-700 font-semibold transition-colors">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        Registros de Horários
                    </a>
                <?php endif; ?>

                <?php if (hasPerm('cargos')): ?>
                    <a href="cargos.php"
                        class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-slate-50 text-slate-700 font-semibold transition-colors">
                        <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        Cargos
                    </a>
                <?php endif; ?>

                <?php if (hasPerm('usuarios')): ?>
                    <a href="usuarios.php"
                        class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-slate-50 text-slate-700 font-semibold transition-colors">
                        <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                        </div>
                        Usuários do Sistema
                    </a>
                <?php endif; ?>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 mb-4">
                <button onclick="logout()"
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-red-50 hover:bg-red-100 text-red-600 font-bold transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                    Sair da Conta
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleMobileMenu() {
        const sheet = document.getElementById('mobileBottomSheet');
        const bg = document.getElementById('mobileBottomSheetBg');
        const content = document.getElementById('mobileBottomSheetContent');

        if (sheet.classList.contains('hidden')) {
            sheet.classList.remove('hidden');
            setTimeout(() => {
                bg.classList.remove('opacity-0');
                content.classList.remove('translate-y-full');
            }, 10);
        } else {
            bg.classList.add('opacity-0');
            content.classList.add('translate-y-full');
            setTimeout(() => {
                sheet.classList.add('hidden');
            }, 300);
        }
    }
</script>

</body>

</html>