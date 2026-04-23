<!-- Modal Afastamentos -->
<div id="modalFerias" class="fixed inset-0 z-[100] hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="fecharFerias()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-slate-100">
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
                            <label class="block text-sm font-semibold text-slate-700 mb-1 flex items-center justify-between">
                                Anexar Documento
                                <span id="label_anexo" class="text-[10px] text-indigo-600 font-bold hidden">Arquivo Selecionado</span>
                            </label>
                            <div class="relative">
                                <input type="file" id="ferias_anexo" class="hidden" onchange="document.getElementById('label_anexo').classList.toggle('hidden', !this.files.length)">
                                <button type="button" onclick="document.getElementById('ferias_anexo').click()"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-2 border-2 border-dashed border-slate-300 rounded-xl text-slate-500 hover:text-indigo-600 hover:border-indigo-400 hover:bg-indigo-50 transition-all font-medium">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                    Cliquar para Anexar
                                </button>
                                <input type="hidden" id="ferias_anexo_atual">
                            </div>
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
    function abrirFerias(id, nome) {
        document.getElementById('ferias_func_id').value = id;
        document.getElementById('feriasNomeFunc').textContent = nome;
        document.getElementById('modalFerias').classList.remove('hidden');
        resetFormFerias();
        carregarFerias(id);
    }

    function fecharFerias() {
        document.getElementById('modalFerias').classList.add('hidden');
    }

    function toggleMotivoEspecifico() {
        const tipo = document.getElementById('ferias_tipo').value;
        const div = document.getElementById('div_motivo_especifico');
        div.classList.toggle('hidden', tipo !== 'outros');
        if (tipo !== 'outros') document.getElementById('ferias_motivo_especifico').value = '';
    }

    function resetFormFerias() {
        document.getElementById('ferias_id').value = '';
        document.getElementById('ferias_tipo').value = '';
        document.getElementById('ferias_motivo_especifico').value = '';
        document.getElementById('ferias_inicio').value = '';
        document.getElementById('ferias_fim').value = '';
        document.getElementById('ferias_anexo_atual').value = '';
        document.getElementById('ferias_anexo').value = '';
        document.getElementById('label_anexo').classList.add('hidden');
        toggleMotivoEspecifico();
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
                    const anexoBtn = f.anexo ? `<a href="../../${f.anexo}" target="_blank" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors border border-transparent hover:border-emerald-100" title="Ver Anexo"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg></a>` : '';

                    return `
                    <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-sm hover:border-amber-300 hover:shadow-md transition-all relative group">
                        <div class="flex justify-between items-start mb-1.5">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-amber-600 block mb-0.5">${motivoTxt}</span>
                                <span class="text-sm font-bold text-slate-800">${dtIniStr} - ${dtFimStr}</span>
                            </div>
                            <div class="flex gap-1 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                ${anexoBtn}
                                <button type="button" onclick="editarFerias(${f.id}, '${f.data_inicio}', '${f.data_fim}', '${f.tipo_afastamento || ''}', '${(f.motivo_especifico || '').replace(/'/g, "\\'")}', '${f.anexo || ''}')" class="p-1.5 text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors border border-transparent hover:border-indigo-100" title="Editar"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                                <button type="button" onclick="excluirFerias(${f.id})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-100" title="Excluir"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
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

    function editarFerias(id, ini, fim, tipo, espec, anexo) {
        document.getElementById('ferias_id').value = id;
        document.getElementById('ferias_inicio').value = ini;
        document.getElementById('ferias_fim').value = fim;
        document.getElementById('ferias_tipo').value = tipo;
        document.getElementById('ferias_motivo_especifico').value = espec;
        document.getElementById('ferias_anexo_atual').value = anexo;
        document.getElementById('label_anexo').classList.toggle('hidden', !anexo);
        if (anexo) document.getElementById('label_anexo').textContent = "Anexo Existente";
        toggleMotivoEspecifico();
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

        const formData = new FormData();
        formData.append('action', feriasId ? 'update' : 'create');
        formData.append('id_funcionario', funcId);
        formData.append('id', feriasId);
        formData.append('data_inicio', ini);
        formData.append('data_fim', fim);
        formData.append('tipo_afastamento', document.getElementById('ferias_tipo').value);
        formData.append('motivo_especifico', document.getElementById('ferias_motivo_especifico').value);
        formData.append('anexo_atual', document.getElementById('ferias_anexo_atual').value);

        const fileInput = document.getElementById('ferias_anexo');
        if (fileInput.files.length > 0) {
            formData.append('anexo', fileInput.files[0]);
        }

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
                    } else {
                        Swal.fire('Erro', data.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('Erro', 'Falha ao processar.', 'error');
                }
            }
        });
    }
</script>