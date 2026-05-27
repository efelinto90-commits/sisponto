<!-- Modal Afastamentos -->
<?php 
$_feriasOperatorName = $_SESSION['user_name'] ?? $_SESSION['nome'] ?? 'Operador';
$_feriasOperatorSetor = $_SESSION['user_setor'] ?? $_SESSION['setor'] ?? '';
?>
<script>
    const feriasGestorNome  = <?php echo json_encode($_feriasOperatorName); ?>;
    const feriasGestorSetor = <?php echo json_encode($_feriasOperatorSetor); ?>;
</script>
<div id="modalFerias" class="fixed inset-0 z-[100] hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="fecharFerias()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-100">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-amber-50">
                    <h3 class="text-lg font-bold text-amber-800 flex items-center gap-2">
                        <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zM10 13l4 4m0-4l-4 4" />
                        </svg>
                        Motivo Afastamento: <span id="feriasNomeFunc" class="text-amber-900 ml-1"></span>
                    </h3>
                    <button onclick="fecharFerias()"
                        class="text-slate-400 hover:text-slate-600 transition-colors bg-white hover:bg-slate-100 p-2 rounded-lg border border-slate-200 shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Form Cadastro -->
                    <form id="formFerias" onsubmit="salvarFerias(event)"
                        class="space-y-4 md:border-r border-slate-100 md:pr-6">
                        <input type="hidden" id="ferias_func_id">
                        <input type="hidden" id="ferias_id">

                        <!-- Aviso do Funcionário (Destaque Amarelo) -->
                        <div id="boxAvisoFunc" class="hidden mb-4 p-4 bg-amber-50 border border-amber-200 rounded-xl shadow-inner">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="p-1 bg-amber-100 rounded-lg">
                                    <svg class="w-3 h-3 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <label class="block text-[10px] font-black text-amber-700 uppercase tracking-widest">Aviso do Funcionário</label>
                            </div>
                            <p id="txtAvisoFunc" class="text-sm italic text-amber-900 leading-relaxed mb-3"></p>
                            <div id="listaAnexosFunc">
                                <label class="block text-[10px] font-black text-amber-700 uppercase tracking-widest mb-2">Anexos do Funcionário (Mantidos p/ Tratamento)</label>
                                <div id="containerAnexosFunc" class="space-y-1"></div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Motivo do Afastamento</label>
                            <select id="ferias_tipo" required onchange="toggleMotivoEspecifico()"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors bg-slate-50 focus:bg-white text-slate-800 outline-none">
                                <option value="">Selecione um motivo...</option>
                                <option value="saude">Saúde</option>
                                <option value="folga eleitoral">Folga Eleitoral</option>
                                <option value="ponto facultativo">Ponto Facultativo</option>
                                <option value="liberacao interna">Liberação Interna</option>
                                <option value="liberacao institucional">Liberação Institucional</option>
                                <option value="feriado">Feriado</option>
                                <option value="audiencia">Audiência</option>
                                <option value="ferias">Férias</option>
                                <option value="outros">Outros</option>
                            </select>
                        </div>
                        <div id="div_motivo_especifico" class="hidden">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Especifique o Motivo</label>
                            <input type="text" id="ferias_motivo_especifico"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors bg-slate-50 focus:bg-white text-slate-800 outline-none"
                                placeholder="Descreva o motivo...">
                        </div>
                        <div id="div_periodo_aquisitivo" class="hidden">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Período Aquisitivo <span class="text-red-500">*</span></label>
                            <select id="ferias_periodo_aquisitivo"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors bg-slate-50 focus:bg-white text-slate-800 outline-none">
                                <option value="">Selecione o período aquisitivo...</option>
                            </select>
                            <p id="ferias_saldo_info" class="text-[11px] text-amber-700 font-semibold mt-1 hidden"></p>
                        </div>
                        <div id="div_pleito_eleitoral" class="hidden">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Pleito Eleitoral <span class="text-red-500">*</span></label>
                            <select id="ferias_pleito_eleitoral"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors bg-slate-50 focus:bg-white text-slate-800 outline-none">
                                <option value="">Selecione o pleito...</option>
                            </select>
                            <p id="folga_saldo_info" class="text-[11px] text-amber-700 font-semibold mt-1 hidden"></p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Data de Início</label>
                                <input type="date" id="ferias_inicio" required
                                    class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors bg-slate-50 focus:bg-white text-slate-800 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Data de Fim</label>
                                <input type="date" id="ferias_fim" required
                                    class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors bg-slate-50 focus:bg-white text-slate-800 outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Observações / Instruções</label>
                            <textarea id="ferias_observacao" rows="2"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors bg-slate-50 focus:bg-white text-slate-800 outline-none text-sm"
                                placeholder="Notas adicionais..."></textarea>
                            <p id="avisoJuntaMedica" class="text-[10px] text-red-500 font-bold mt-1 hidden">⚠️ Afastamentos de 5 dias ou mais exigem a observação: "Funcionário precisa buscar a junta médica"</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Comprovantes / Anexos (Múltiplos)</label>
                            <div id="containerAnexosExistentes" class="flex flex-wrap gap-2 mb-2"></div>
                            <div class="relative">
                                <input type="file" id="ferias_anexo" class="hidden" multiple onchange="atualizarListaArquivosSeleccionados()">
                                <button type="button" onclick="document.getElementById('ferias_anexo').click()"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-2 border-2 border-dashed border-slate-300 rounded-xl text-slate-500 hover:text-indigo-600 hover:border-indigo-400 hover:bg-indigo-50 transition-all font-medium">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                    Clique para Anexar Arquivos
                                </button>
                                <input type="hidden" id="ferias_anexo_atual">
                                <input type="hidden" id="ferias_anexos_removidos" value="[]">
                            </div>
                            <div id="listaNovosArquivos" class="mt-2 space-y-1"></div>
                        </div>
                        <div class="pt-2 flex gap-2">
                            <button type="button" onclick="resetFormFerias()"
                                class="flex-1 py-2 bg-white border border-slate-300 rounded-xl text-slate-700 hover:bg-slate-50 font-semibold transition-colors text-sm">Limpar</button>
                            <button type="submit"
                                class="flex-1 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-semibold shadow-sm transition-colors text-sm flex items-center justify-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Salvar Afastamento
                            </button>
                        </div>
                    </form>

                    <!-- Lista de Férias -->
                    <div class="flex flex-col h-full border-t md:border-t-0 pt-6 md:pt-0 border-slate-100">
                        <h4 class="text-sm font-bold text-slate-800 mb-3 flex items-center justify-between">
                            Histórico de Afastamentos
                            <span class="text-xs font-normal text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full"
                                id="contadorFerias">0 reg</span>
                        </h4>
                        <div class="flex-1 overflow-y-auto pr-2 space-y-3 max-h-[400px] scroolbar-hide"
                            id="listaFerias">
                            <!-- Carregado via JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function esc(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }

    function abrirFerias(id, nome, anexoPreenchido = null, msgPreenchida = null) {
        document.getElementById('ferias_func_id').value = id;
        document.getElementById('feriasNomeFunc').textContent = nome;
        document.getElementById('modalFerias').classList.remove('hidden');
        resetFormFerias();

        if (anexoPreenchido || msgPreenchida) {
            const box = document.getElementById('boxAvisoFunc');
            const txt = document.getElementById('txtAvisoFunc');
            const container = document.getElementById('containerAnexosFunc');
            
            box.classList.remove('hidden');
            txt.textContent = msgPreenchida ? `"${msgPreenchida}"` : 'Sem mensagem adicional.';
            
            container.innerHTML = '';
            if (anexoPreenchido) {
                document.getElementById('ferias_anexo_atual').value = anexoPreenchido;
                // Não chamamos renderAnexosExistentes aqui para evitar duplicidade com o box amarelo

                let anexos = [];
                try {
                    anexos = JSON.parse(anexoPreenchido);
                    if (!Array.isArray(anexos)) anexos = [anexoPreenchido];
                } catch(e) { anexos = [anexoPreenchido]; }

                anexos.forEach((path, idx) => {
                    const filename = path.split('/').pop();
                    container.innerHTML += `
                        <div class="flex items-center justify-between bg-white/60 p-2 rounded-lg border border-amber-200/50 text-[11px]">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" checked data-path="${path}" onchange="toggleAnexoFunc('${path}', this)" class="rounded text-amber-600 focus:ring-amber-500 w-3.5 h-3.5 cursor-pointer">
                                <span class="truncate max-w-[150px] font-medium text-amber-900">${filename}</span>
                            </div>
                            <a href="../../${path}" target="_blank" class="text-amber-700 hover:text-amber-900 font-bold underline decoration-amber-300">Ver Original</a>
                        </div>
                    `;
                });
            } else {
                container.innerHTML = '<p class="text-[10px] text-amber-600 italic">Sem anexos.</p>';
            }
        }

        carregarFerias(id);
        carregarPeriodosSelect(id);
    }

    function toggleAnexoFunc(path, checkbox) {
        if (!checkbox.checked) {
            // Se desmarcar, remove da lista oficial (adiciona aos removidos)
            let removidos = JSON.parse(document.getElementById('ferias_anexos_removidos').value);
            if (!removidos.includes(path)) {
                removidos.push(path);
                document.getElementById('ferias_anexos_removidos').value = JSON.stringify(removidos);
            }
        } else {
            // Se marcar, remove da lista de removidos para restaurar
            let removidos = JSON.parse(document.getElementById('ferias_anexos_removidos').value);
            removidos = removidos.filter(a => a !== path);
            document.getElementById('ferias_anexos_removidos').value = JSON.stringify(removidos);
        }
        // Não re-renderizamos a lista padrão se estivermos no modo de aviso do funcionário (evita duplicidade)
    }

    function fecharFerias() {
        document.getElementById('modalFerias').classList.add('hidden');
    }

    function toggleMotivoEspecifico() {
        const tipo = document.getElementById('ferias_tipo').value;
        const div = document.getElementById('div_motivo_especifico');
        const divPeriodo = document.getElementById('div_periodo_aquisitivo');
        const divPleito = document.getElementById('div_pleito_eleitoral');
        
        div.classList.toggle('hidden', tipo !== 'outros');
        if (tipo !== 'outros') document.getElementById('ferias_motivo_especifico').value = '';
        
        divPeriodo.classList.toggle('hidden', tipo !== 'ferias');
        if (tipo !== 'ferias') {
            document.getElementById('ferias_periodo_aquisitivo').value = '';
            document.getElementById('ferias_saldo_info').classList.add('hidden');
        } else {
            // Quando seleciona férias, mostrar saldo ao trocar de período
            const selectPeriodo = document.getElementById('ferias_periodo_aquisitivo');
            selectPeriodo.onchange = function() {
                const opt = this.options[this.selectedIndex];
                const info = document.getElementById('ferias_saldo_info');
                if (opt && opt.dataset.dias) {
                    info.textContent = `Saldo disponível: ${opt.dataset.dias} dia(s)`;
                    info.classList.remove('hidden');
                } else {
                    info.classList.add('hidden');
                }
            };
            
            // Se for um novo cadastro e não houver período selecionado ainda,
            // vamos auto-selecionar o primeiro período válido disponível no select.
            if (!document.getElementById('ferias_id').value && !selectPeriodo.value) {
                if (selectPeriodo.options.length > 1) {
                    selectPeriodo.selectedIndex = 1;
                    if (typeof selectPeriodo.onchange === 'function') {
                        selectPeriodo.onchange();
                    }
                    selectPeriodo.dispatchEvent(new Event('change'));
                }
            }
        }
        
        divPleito.classList.toggle('hidden', tipo !== 'folga eleitoral');
        if (tipo !== 'folga eleitoral') {
            document.getElementById('ferias_pleito_eleitoral').value = '';
            document.getElementById('folga_saldo_info').classList.add('hidden');
        } else {
            document.getElementById('ferias_pleito_eleitoral').onchange = function() {
                const opt = this.options[this.selectedIndex];
                const info = document.getElementById('folga_saldo_info');
                if (opt.dataset.saldo) {
                    info.textContent = `Saldo disponível: ${opt.dataset.saldo} dia(s)`;
                    info.classList.remove('hidden');
                } else {
                    info.classList.add('hidden');
                }
            };
        }
    }

    function resetFormFerias() {
        document.getElementById('ferias_id').value = '';
        document.getElementById('ferias_tipo').value = '';
        document.getElementById('ferias_motivo_especifico').value = '';
        document.getElementById('ferias_observacao').value = '';
        document.getElementById('ferias_inicio').value = '';
        document.getElementById('ferias_fim').value = '';
        document.getElementById('ferias_anexo_atual').value = '';
        document.getElementById('ferias_anexo').value = '';
        document.getElementById('ferias_anexos_removidos').value = '[]';
        document.getElementById('containerAnexosExistentes').innerHTML = '';
        document.getElementById('listaNovosArquivos').innerHTML = '';
        document.getElementById('avisoJuntaMedica').classList.add('hidden');
        document.getElementById('boxAvisoFunc').classList.add('hidden');
        document.getElementById('txtAvisoFunc').textContent = '';
        document.getElementById('containerAnexosFunc').innerHTML = '';
        // Resetar campo período aquisitivo
        document.getElementById('div_periodo_aquisitivo').classList.add('hidden');
        document.getElementById('ferias_periodo_aquisitivo').value = '';
        document.getElementById('ferias_saldo_info').classList.add('hidden');
        // Resetar campo pleito eleitoral
        document.getElementById('div_pleito_eleitoral').classList.add('hidden');
        document.getElementById('ferias_pleito_eleitoral').value = '';
        document.getElementById('folga_saldo_info').classList.add('hidden');
        toggleMotivoEspecifico();
    }

    function atualizarListaArquivosSeleccionados() {
        const input = document.getElementById('ferias_anexo');
        const container = document.getElementById('listaNovosArquivos');
        container.innerHTML = '';
        
        if (input.files.length > 0) {
            Array.from(input.files).forEach((file, index) => {
                container.innerHTML += `
                    <div class="flex items-center justify-between bg-slate-50 p-2 rounded-lg border border-slate-100 text-[11px]">
                        <span class="truncate max-w-[200px] font-medium text-slate-700">${file.name}</span>
                        <span class="text-slate-400">(${(file.size/1024).toFixed(1)} KB)</span>
                    </div>
                `;
            });
        }
    }

    function removerAnexoExistente(path, btn) {
        let removidos = JSON.parse(document.getElementById('ferias_anexos_removidos').value);
        if (!removidos.includes(path)) {
            removidos.push(path);
            document.getElementById('ferias_anexos_removidos').value = JSON.stringify(removidos);
            btn.closest('.anexo-item').classList.add('opacity-40', 'grayscale');
            btn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>';
            btn.onclick = function() { restaurarAnexoExistente(path, btn); };
            
            // Desmarca no box amarelo se existir
            document.querySelectorAll(`#containerAnexosFunc input[type="checkbox"]`).forEach(cb => {
                if (cb.getAttribute('data-path') === path) cb.checked = false;
            });
        }
    }

    function restaurarAnexoExistente(path, btn) {
        let removidos = JSON.parse(document.getElementById('ferias_anexos_removidos').value);
        removidos = removidos.filter(a => a !== path);
        document.getElementById('ferias_anexos_removidos').value = JSON.stringify(removidos);
        btn.closest('.anexo-item').classList.remove('opacity-40', 'grayscale');
        btn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
        btn.onclick = function() { removerAnexoExistente(path, btn); };
        
        // Marca no box amarelo se existir
        document.querySelectorAll(`#containerAnexosFunc input[type="checkbox"]`).forEach(cb => {
            if (cb.getAttribute('data-path') === path) cb.checked = true;
        });
    }

    async function carregarFerias(funcId) {
        const lista = document.getElementById('listaFerias');
        const contador = document.getElementById('contadorFerias');
        lista.innerHTML = '<div class="text-sm text-slate-400 text-center py-8">Carregando...</div>';

        try {
            const res = await fetch(`../../api/ferias.php?func_id=${funcId}`);
            const data = await res.json();

            if (data.success) {
                contador.textContent = `${data.data.length} reg`;
                if (data.data.length === 0) {
                    lista.innerHTML = '<div class="text-sm text-slate-400 text-center py-8 bg-slate-50 rounded-xl border border-dashed border-slate-200">Nenhum registro de afastamento encontrado.</div>';
                    return;
                }

                lista.innerHTML = data.data.map(f => {
                    const dtIniParts = f.data_inicio.split('-');
                    const dtFimParts = f.data_fim.split('-');
                    const dtIniStr = dtIniParts[2] + '/' + dtIniParts[1] + '/' + dtIniParts[0];
                    const dtFimStr = dtFimParts[2] + '/' + dtFimParts[1] + '/' + dtFimParts[0];

                    const motivoTxt = f.tipo_afastamento === 'outros' ? (f.motivo_especifico || 'Outros') : (f.tipo_afastamento || 'Não especificado');
                    
                    let anexosHtml = '';
                    if (f.anexo) {
                        try {
                            const anexos = JSON.parse(f.anexo);
                            if (Array.isArray(anexos)) {
                                anexosHtml = anexos.map((path, idx) => `
                                    <a href="../../${path}" target="_blank" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors border border-transparent hover:border-emerald-100" title="Ver Anexo ${idx+1}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    </a>
                                `).join('');
                            } else {
                                anexosHtml = `<a href="../../${f.anexo}" target="_blank" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors border border-transparent hover:border-emerald-100" title="Ver Anexo"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg></a>`;
                            }
                        } catch(e) {
                            anexosHtml = `<a href="../../${f.anexo}" target="_blank" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors border border-transparent hover:border-emerald-100" title="Ver Anexo"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg></a>`;
                        }
                    }

                    let statusBadge = '';
                    if (f.status === 'deferido') {
                        statusBadge = '<span class="px-1.5 py-0.5 text-[9px] font-black uppercase bg-emerald-100 text-emerald-700 rounded border border-emerald-200">Deferido</span>';
                    } else if (f.status === 'indeferido') {
                        statusBadge = `<span class="px-1.5 py-0.5 text-[9px] font-black uppercase bg-red-100 text-red-700 rounded border border-red-200 cursor-help" title="MOTIVO DO INDEFERIMENTO: ${esc(f.motivo_indeferimento || 'Não informado')}">Indeferido</span>`;
                    } else {
                        statusBadge = '<span class="px-1.5 py-0.5 text-[9px] font-black uppercase bg-amber-100 text-amber-700 rounded border border-amber-200 animate-pulse">Pendente</span>';
                    }

                    let actions = '';
                    if (isAdminOrCRH || f.status === 'indeferido') {
                        const anexoEscaped = (f.anexo || '').replace(/'/g, "\\'").replace(/"/g, "&quot;");
                        const obsEscaped = (f.observacao || '').replace(/'/g, "\\'").replace(/\n/g, " ");
                        const periodo = (f.periodo_aquisitivo || '').replace(/'/g, "\\'");
                        actions += `<button type="button" onclick="editarFerias(${f.id}, '${f.data_inicio}', '${f.data_fim}', '${f.tipo_afastamento || ''}', '${(f.motivo_especifico || '').replace(/'/g, "\\'")}', '${anexoEscaped}', '${obsEscaped}', '${periodo}', '${f.status}')" class="p-1.5 text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors border border-transparent hover:border-indigo-100" title="Editar / Corrigir"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>`;
                    }
                    if (isAdminOrCRH) {
                        actions += `
                            <button type="button" onclick="excluirFerias(${f.id})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-100" title="Excluir"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                            ${f.status === 'pendente' ? `
                                <button type="button" onclick="atualizarStatusAfastamento(${f.id}, 'approve')" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors border border-transparent hover:border-emerald-100" title="Deferir"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></button>
                                <button type="button" onclick="atualizarStatusAfastamento(${f.id}, 'deny')" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors border border-transparent hover:border-rose-100" title="Indeferir"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                            ` : ''}
                        `;
                    }

                    return `
                    <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-sm hover:border-amber-300 hover:shadow-md transition-all relative group">
                        <div class="flex justify-between items-start mb-1.5">
                            <div>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-xs font-bold uppercase tracking-wider text-amber-600">${motivoTxt}</span>
                                    ${statusBadge}
                                </div>
                                <span class="text-sm font-bold text-slate-800">${dtIniStr} - ${dtFimStr}</span>
                            </div>
                            <div class="flex gap-1 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                ${anexosHtml}
                                ${actions}
                            </div>
                        </div>
                    </div>
                    `;
                }).join('');
            } else {
                lista.innerHTML = `<div class="text-sm text-red-500 text-center py-4">${data.message}</div>`;
            }
        } catch (e) {
            lista.innerHTML = `<div class="text-sm text-red-500 text-center py-4">Erro de conexão ao carregar.</div>`;
        }
    }

    function renderAnexosExistentes(anexo) {
        const container = document.getElementById('containerAnexosExistentes');
        container.innerHTML = '';
        
        // Se o box de aviso do funcionário estiver visível, não duplicamos os anexos na lista padrão
        if (!document.getElementById('boxAvisoFunc').classList.contains('hidden')) return;

        if (!anexo || anexo === 'null' || anexo === '[]' || anexo === '') return;
        
        let anexos = [];
        try {
            anexos = JSON.parse(anexo);
            if (!Array.isArray(anexos)) anexos = [anexo];
        } catch(e) { anexos = [anexo]; }
        
        const removidos = JSON.parse(document.getElementById('ferias_anexos_removidos').value || '[]');
        anexos.forEach(path => {
            if (removidos.includes(path)) return; // Pula os removidos para manter consistência
            const filename = path.split('/').pop();
            container.innerHTML += `
                <div class="anexo-item flex items-center gap-2 bg-indigo-50 text-indigo-700 px-2 py-1 rounded-lg border border-indigo-100 text-[10px] font-bold transition-all">
                    <a href="../../${path}" target="_blank" class="hover:underline truncate max-w-[100px]">${filename}</a>
                    <button type="button" onclick="removerAnexoExistente('${path}', this)" class="text-rose-500 hover:text-rose-700 p-0.5 bg-white rounded shadow-sm border border-rose-100">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            `;
        });
    }

    function editarFerias(id, ini, fim, tipo, espec, anexo, obs, periodo, status) {
        document.getElementById('ferias_id').value = id;
        document.getElementById('ferias_inicio').value = ini;
        document.getElementById('ferias_fim').value = fim;
        document.getElementById('ferias_tipo').value = tipo;
        document.getElementById('ferias_motivo_especifico').value = espec;
        document.getElementById('ferias_observacao').value = obs || '';
        document.getElementById('ferias_anexo_atual').value = anexo;
        document.getElementById('ferias_anexos_removidos').value = '[]';
        document.getElementById('listaNovosArquivos').innerHTML = '';
        
        renderAnexosExistentes(anexo);
        
        // Guardar dados originais para validação virtual
        const d1 = new Date(ini);
        const d2 = new Date(fim);
        const diffTime = Math.abs(d2 - d1);
        window._edicaoFeriasDiasOriginais = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        window._edicaoFeriasStatusOriginal = status;
        window._edicaoFeriasPeriodoOriginal = periodo;

        toggleMotivoEspecifico();
        
        if (tipo === 'ferias' && periodo) {
            const select = document.getElementById('ferias_periodo_aquisitivo');
            let optionExists = false;
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].value === periodo) {
                    optionExists = true;
                    break;
                }
            }
            if (!optionExists && select.dataset.fullPeriodos) {
                try {
                    const fullPeriodos = JSON.parse(select.dataset.fullPeriodos);
                    const matched = fullPeriodos.find(p => p.periodo === periodo);
                    if (matched) {
                        const opt = document.createElement('option');
                        opt.value = periodo;
                        opt.textContent = `${periodo} — ${matched.dias} dia(s) disponíveis`;
                        opt.dataset.dias = matched.dias;
                        select.appendChild(opt);
                    }
                } catch(e) { console.error("Erro ao reconstruir periodo quitado:", e); }
            }
            select.value = periodo;
            if (typeof select.onchange === 'function') {
                select.onchange();
            }
            select.dispatchEvent(new Event('change'));
        } else if (tipo === 'folga eleitoral' && periodo) {
            document.getElementById('ferias_pleito_eleitoral').value = periodo;
        }
        
        document.getElementById('avisoJuntaMedica').classList.add('hidden');
    }

    async function carregarPeriodosSelect(funcId) {
        const select = document.getElementById('ferias_periodo_aquisitivo');
        const selectPleito = document.getElementById('ferias_pleito_eleitoral');
        select.innerHTML = '<option value="">Carregando períodos...</option>';
        selectPleito.innerHTML = '<option value="">Carregando pleitos...</option>';
        try {
            const res = await fetch(`../../api/funcionarios.php?id=${funcId}`);
            const data = await res.json();
            
            select.innerHTML = '<option value="">Selecione o período aquisitivo...</option>';
            selectPleito.innerHTML = '<option value="">Selecione o pleito...</option>';
            
            if (data.success && data.data) {
                // Férias
                if (data.data.ferias_periodos) {
                    let periodos = data.data.ferias_periodos;
                    if (typeof periodos === 'string') {
                        try { periodos = JSON.parse(periodos); } catch(e) { periodos = []; }
                    }
                    select.dataset.fullPeriodos = JSON.stringify(periodos || []);
                    const abertos = (periodos || []).filter(p => (p.status === 'aberto' || !p.status) && parseInt(p.dias || 0) > 0);
                    if (abertos.length === 0) {
                        select.innerHTML = '<option value="">Nenhum período com saldo disponível</option>';
                    } else {
                        abertos.forEach(p => {
                            const opt = document.createElement('option');
                            opt.value = p.periodo;
                            opt.textContent = `${p.periodo} — ${p.dias} dia(s) disponíveis`;
                            opt.dataset.dias = p.dias;
                            select.appendChild(opt);
                        });

                        // Se o tipo atual do form for 'ferias' e não tivermos ferias_id (novo cadastro) ou se estiver vazio:
                        const tipoAtual = document.getElementById('ferias_tipo').value;
                        if (tipoAtual === 'ferias' && !select.value) {
                            if (select.options.length > 1) {
                                select.selectedIndex = 1;
                                if (typeof select.onchange === 'function') {
                                    select.onchange();
                                }
                                select.dispatchEvent(new Event('change'));
                            }
                        }
                    }
                } else {
                    select.innerHTML = '<option value="">Sem períodos cadastrados</option>';
                }
                
                // Pleitos Eleitorais
                if (data.data.folgas_eleitorais) {
                    let pleitos = data.data.folgas_eleitorais;
                    if (typeof pleitos === 'string') {
                        try { pleitos = JSON.parse(pleitos); } catch(e) { pleitos = []; }
                    }
                    const cSaldo = (pleitos || []).filter(p => parseInt(p.saldo || 0) > 0);
                    if (cSaldo.length === 0) {
                        selectPleito.innerHTML = '<option value="">Nenhum pleito com saldo disponível</option>';
                    } else {
                        cSaldo.forEach(p => {
                            const opt = document.createElement('option');
                            opt.value = p.nome;
                            opt.textContent = `${p.nome} — Saldo: ${p.saldo} dia(s)`;
                            opt.dataset.saldo = p.saldo;
                            selectPleito.appendChild(opt);
                        });
                    }
                } else {
                    selectPleito.innerHTML = '<option value="">Sem pleitos cadastrados</option>';
                }
            } else {
                select.innerHTML = '<option value="">Sem períodos cadastrados</option>';
                selectPleito.innerHTML = '<option value="">Sem pleitos cadastrados</option>';
            }
        } catch (e) {
            select.innerHTML = '<option value="">Erro ao carregar</option>';
            selectPleito.innerHTML = '<option value="">Erro ao carregar</option>';
        }
    }

    async function salvarFerias(e) {
        e.preventDefault();
        const funcId = document.getElementById('ferias_func_id').value;
        const feriasId = document.getElementById('ferias_id').value;
        const ini = document.getElementById('ferias_inicio').value;
        const fim = document.getElementById('ferias_fim').value;

        if (ini > fim) {
            Swal.fire('Atenção', 'A data de fim não pode ser anterior à data de início.', 'warning');
            return;
        }

        // Validação de 5 dias para Junta Médica
        const d1 = new Date(ini);
        const d2 = new Date(fim);
        const diffTime = Math.abs(d2 - d1);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        const tipo = document.getElementById('ferias_tipo').value;
        const obs = document.getElementById('ferias_observacao').value;

        if (tipo === 'saude' && diffDays >= 5) {
            const regexJunta = /junta\s+m[eé]dica/i;
            if (!regexJunta.test(obs)) {
                document.getElementById('avisoJuntaMedica').classList.remove('hidden');
                Swal.fire({
                    title: 'Atenção: Junta Médica',
                    text: 'Para afastamentos de 5 dias ou mais, é obrigatório informar na observação que o funcionário precisa buscar a junta médica.',
                    icon: 'warning',
                    confirmButtonColor: '#f59e0b'
                });
                return;
            }
        }
        // Validação do período aquisitivo se motivo for férias
        if (tipo === 'ferias') {
            const select = document.getElementById('ferias_periodo_aquisitivo');
            const periodoSel = select.value;
            if (!periodoSel) {
                Swal.fire('Atenção', 'Selecione o período aquisitivo correspondente às férias.', 'warning');
                return;
            }
            let saldo = parseInt(select.options[select.selectedIndex].dataset.dias || 0);
            
            // Validação virtual: se for uma edição do mesmo período e o status original era 'deferido',
            // somamos virtualmente os dias originais ao saldo disponível para validação.
            if (feriasId && window._edicaoFeriasStatusOriginal === 'deferido' && window._edicaoFeriasPeriodoOriginal === periodoSel) {
                saldo += parseInt(window._edicaoFeriasDiasOriginais || 0);
            }

            if (diffDays > saldo) {
                Swal.fire('Atenção', `Você solicitou ${diffDays} dia(s), mas o período selecionado tem apenas ${saldo} dia(s) de saldo.`, 'warning');
                return;
            }
        }
        
        // Validação do pleito se motivo for folga eleitoral
        if (tipo === 'folga eleitoral') {
            const select = document.getElementById('ferias_pleito_eleitoral');
            const pleitoSel = select.value;
            if (!pleitoSel) {
                Swal.fire('Atenção', 'Selecione o pleito eleitoral correspondente.', 'warning');
                return;
            }
            const saldo = parseInt(select.options[select.selectedIndex].dataset.saldo || 0);
            if (diffDays > saldo) {
                Swal.fire('Atenção', `Você solicitou ${diffDays} dia(s), mas o pleito selecionado tem apenas ${saldo} dia(s) de saldo.`, 'warning');
                return;
            }
        }
        
        document.getElementById('avisoJuntaMedica').classList.add('hidden');

        const formData = new FormData();
        formData.append('action', feriasId ? 'update' : 'create');
        formData.append('id_funcionario', funcId);
        formData.append('id', feriasId);
        formData.append('data_inicio', ini);
        formData.append('data_fim', fim);
        formData.append('observacao', obs);
        formData.append('tipo_afastamento', document.getElementById('ferias_tipo').value);
        formData.append('motivo_especifico', document.getElementById('ferias_motivo_especifico').value);
        
        let periodoAquisitivo = '';
        if (tipo === 'ferias') periodoAquisitivo = document.getElementById('ferias_periodo_aquisitivo').value;
        if (tipo === 'folga eleitoral') periodoAquisitivo = document.getElementById('ferias_pleito_eleitoral').value;
        formData.append('periodo_aquisitivo', periodoAquisitivo);
        // Gestor que solicitou o afastamento (apenas na criação)
        if (!feriasId) {
            formData.append('gestor_solicitante', feriasGestorNome);
            formData.append('gestor_setor', feriasGestorSetor);
        }
        formData.append('anexo_atual', document.getElementById('ferias_anexo_atual').value);

        const fileInput = document.getElementById('ferias_anexo');
        if (fileInput.files.length > 0) {
            Array.from(fileInput.files).forEach(file => {
                formData.append('anexo[]', file);
            });
        }
        formData.append('anexos_removidos', document.getElementById('ferias_anexos_removidos').value);

        try {
            const res = await fetch('../../api/ferias.php', {
                method: 'POST',
                // Sem Content-Type header para o navegador definir multipart/form-data com boundary
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
                resetFormFerias();
                carregarFerias(funcId);
                if (typeof tableFunc !== 'undefined') tableFunc.ajax.reload(null, false);
                
                // Atualizar abas se estiver no formulário de funcionário
                if (typeof carregarHistoricoTab === 'function') {
                    carregarHistoricoTab('ferias', 'lista-ferias-tab');
                    carregarHistoricoTab('folga eleitoral', 'lista-folga-tab');
                }
                // Recarregar ficha de períodos aquisitivos na tela do funcionário
                if (typeof carregarFuncionario === 'function') {
                    carregarFuncionario(funcId);
                }
                // Recarregar tabela de tratamento se existir
                if (typeof loadPontoData === 'function') {
                    loadPontoData();
                }
            } else {
                Swal.fire('Falha', data.message, 'error');
            }
        } catch (err) {
            Swal.fire('Erro', 'Falha ao se comunicar com o servidor.', 'error');
        }
    }

    async function excluirFerias(id) {
        Swal.fire({
            title: 'Remover Afastamento?',
            text: "Este registro de afastamento será apagado.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Sim, Apagar'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await fetch('../../api/ferias.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete', id: id })
                    });
                    const data = await res.json();

                    if (data.success) {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
                        const funcId = document.getElementById('ferias_func_id').value;
                        carregarFerias(funcId);
                        if (typeof tableFunc !== 'undefined') tableFunc.ajax.reload(null, false);
                        
                        // Atualizar abas se estiver no formulário de funcionário
                        if (typeof carregarHistoricoTab === 'function') {
                            carregarHistoricoTab('ferias', 'lista-ferias-tab');
                            carregarHistoricoTab('folga eleitoral', 'lista-folga-tab');
                        }
                        // Recarregar ficha de períodos aquisitivos na tela do funcionário
                        if (typeof carregarFuncionario === 'function') {
                            carregarFuncionario(funcId);
                        }
                        // Recarregar tabela de tratamento se existir
                        if (typeof loadPontoData === 'function') {
                            loadPontoData();
                        }
                    } else {
                        Swal.fire('Erro', data.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('Erro', 'Falha ao processar.', 'error');
                }
            }
        });
    }

    async function atualizarStatusAfastamento(id, action) {
        let motivo = null;
        if (action === 'deny') {
            const { value: text } = await Swal.fire({
                title: 'Motivo do Indeferimento',
                input: 'textarea',
                inputLabel: 'Informe por que o afastamento foi recusado',
                inputPlaceholder: 'Ex: Documentação incompleta...',
                showCancelButton: true,
                confirmButtonText: 'Confirmar Recusa',
                cancelButtonText: 'Voltar',
                confirmButtonColor: '#dc2626',
                inputValidator: (value) => {
                    if (!value) return 'Você precisa informar um motivo!';
                }
            });
            if (text === undefined) return;
            motivo = text;
        } else {
            const result = await Swal.fire({
                title: 'Confirmar Deferimento',
                text: "O afastamento será aprovado e passará a valer no cartão de ponto.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, Deferir',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#10b981'
            });
            if (!result.isConfirmed) return;
        }

        try {
            const res = await fetch('../../api/ferias.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action, id, motivo })
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
                const funcId = document.getElementById('ferias_func_id').value;
                carregarFerias(funcId);
                if (typeof tableFunc !== 'undefined') tableFunc.ajax.reload(null, false);
                
                // Recarregar ficha de períodos aquisitivos na tela do funcionário
                if (typeof carregarFuncionario === 'function') {
                    carregarFuncionario(funcId);
                }
            } else {
                Swal.fire('Erro', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Erro', 'Falha ao processar.', 'error');
        }
    }
</script>