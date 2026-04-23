<?php include 'layout/header.php'; 

$is_super = ($_SESSION['user_level'] == 1);
?>

<div
    class="max-w-[1450px] mx-auto flex flex-col h-full bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <div
        class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Tratar Ponto</h1>
                <p class="text-sm text-slate-500 mt-1">Trate as inconsistências e envie os registros justificados ao CRH.</p>
            </div>
        </div>

        <!-- Filtros -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-lg border border-slate-200 shadow-sm">
                <span class="text-xs font-semibold text-slate-500">Período:</span>
                <input type="date" id="dailyCardDateStart" onchange="loadPontoData()"
                    class="border-0 p-0 text-sm focus:ring-0 text-slate-700 font-medium bg-transparent">
                <span class="text-slate-300">|</span>
                <input type="date" id="dailyCardDateEnd" onchange="loadPontoData()"
                    class="border-0 p-0 text-sm focus:ring-0 text-slate-700 font-medium bg-transparent">
            </div>

            <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-lg border border-slate-200 shadow-sm">
                <span class="text-xs font-semibold text-slate-500">Filtrar CRH:</span>
                <select id="filtroCrh" onchange="loadPontoData()"
                    class="border-0 p-0 text-sm focus:ring-0 text-slate-700 font-medium bg-transparent min-w-[140px]">
                    <?php if ($is_super): ?>
                        <option value="enviados" selected>Recebidos (Enviados ao CRH)</option>
                        <option value="nao_enviados">Pendentes nos Setores</option>
                        <option value="">Todos os Registros</option>
                    <?php else: ?>
                        <option value="nao_enviados" selected>Meus Pendentes (Não Enviados)</option>
                        <option value="enviados">Já Enviados ao CRH</option>
                        <option value="">Todos (Enviados e Pendentes)</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-lg border border-slate-200 shadow-sm">
                <span class="text-xs font-semibold text-slate-500">Colaborador:</span>
                <div class="w-[200px] lg:w-[250px]">
                    <select id="dailyCardFuncId" onchange="loadPontoData()"
                        class="w-full border-0 p-0 text-sm focus:ring-0 text-slate-700 font-medium bg-transparent">
                        <option value="">Todos os Funcionários</option>
                    </select>
                </div>
            </div>

            <button onclick="loadPontoData()"
                class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-semibold hover:bg-slate-700 transition-colors shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
                Filtrar
            </button>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="px-6 py-3 bg-brand-50 border-b border-brand-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <input type="checkbox" id="selectAll" class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500" onchange="toggleAll(this)">
            <label for="selectAll" class="text-sm font-semibold text-slate-700 cursor-pointer">Selecionar Todos da Página</label>
        </div>
        <button onclick="enviarSelecionados()" id="btnEnviarCrh" disabled
            class="px-5 py-2 bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-sm font-bold shadow-sm hover:bg-brand-500 transition-all flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
            Enviar ao CRH (<span id="contSelected">0</span>)
        </button>
    </div>

    <div class="p-6 flex-1 overflow-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-xs sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-3 rounded-l-lg border-b border-slate-100 w-10 text-center"></th>
                    <th class="px-4 py-3 border-b border-slate-100">Data</th>
                    <th class="px-4 py-3 border-b border-slate-100">Colaborador</th>
                    <th class="px-4 py-3 text-center border-b border-slate-100">Batidas / Falhas</th>
                    <th class="px-4 py-3 border-b border-slate-100">Status Geral</th>
                    <th class="px-4 py-3 text-center border-b border-slate-100">Situação CRH</th>
                    <th class="px-4 py-3 text-right rounded-r-lg border-b border-slate-100">Tratar Acesso</th>
                </tr>
            </thead>
            <tbody id="pontoBody" class="divide-y divide-slate-100">
                <tr>
                    <td colspan="7" class="text-center py-20 text-slate-400">Carregando dados...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Ajustes do Select2 para casar com o design inline Tailwind */
    .select2-container .select2-selection--single {
        height: 24px !important;
        border: none !important;
        background: transparent !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 24px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #334155 !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        line-height: normal !important;
        padding-left: 0 !important;
    }
    .select2-dropdown {
        border-color: #e2e8f0 !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1) !important;
    }
</style>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    const IS_SUPER = <?php echo $is_super ? 'true' : 'false'; ?>;

    function esc(s) {
        if (s == null) return '';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }

    function mostrarMotivo(texto) {
        if (!texto || texto === 'null') return;
        const motivo = texto.includes('[INDEFERIDO PELO CRH]:') ? texto.split('[INDEFERIDO PELO CRH]:')[1] : texto;
        Swal.fire({
            title: 'Justificativa Indeferida',
            text: motivo.trim(),
            icon: 'error',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Entendido'
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        console.log("DOM carregado. Iniciando buscas...");
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
        const currentDay = today.toISOString().split('T')[0];

        const dateStart = document.getElementById('dailyCardDateStart');
        const dateEnd   = document.getElementById('dailyCardDateEnd');

        if (!dateStart.value) dateStart.value = firstDay;
        if (!dateEnd.value)   dateEnd.value   = currentDay;

        carregarListaFuncionariosDaily();
        loadPontoData();
    });

    async function carregarListaFuncionariosDaily() {
        console.log("Iniciando carregarListaFuncionariosDaily...");
        const select = document.getElementById('dailyCardFuncId');
        try {
            const res  = await fetch('../../api/funcionarios.php?strict_sector=1');
            const data = await res.json();
            if (data.success) {
                data.data.forEach(f => {
                    const opt = document.createElement('option');
                    opt.value = f.id;
                    opt.textContent = `${f.matricula} - ${f.nome}`;
                    select.appendChild(opt);
                });
                
                if ($.fn.select2) {
                    $('#dailyCardFuncId').select2({
                        placeholder: "Todos os Funcionários",
                        allowClear: true,
                        width: '100%'
                    }).on('select2:select select2:clear', function() {
                        loadPontoData();
                    });
                }
                console.log("Funcionários carregados com sucesso!");
            }
        } catch (e) { console.error('Erro ao carregar funcionários', e); }
    }

    function toggleAll(chk) {
        document.querySelectorAll('.row-check').forEach(c => {
            if(!c.disabled) c.checked = chk.checked;
        });
        updateCount();
    }

    function updateCount() {
        const count = document.querySelectorAll('.row-check:checked').length;
        const btn = document.getElementById('btnEnviarCrh');
        document.getElementById('contSelected').innerText = count;
        btn.disabled = count === 0;
    }

    async function loadPontoData() {
        console.log("Iniciando loadPontoData...");
        const tbody = document.getElementById('pontoBody');
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-20 text-slate-500"><svg class="animate-spin h-8 w-8 mx-auto mb-3 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Buscando registros...</td></tr>';

        document.getElementById('selectAll').checked = false;
        updateCount();

        try {
            const startStr = document.getElementById('dailyCardDateStart').value;
            const endStr   = document.getElementById('dailyCardDateEnd').value;
            const funcId   = document.getElementById('dailyCardFuncId').value;
            const filtroCrh= document.getElementById('filtroCrh').value;

            console.log(`Chamando API: start=${startStr}, end=${endStr}, func=${funcId}`);
            const res  = await fetch(`../../api/relatorios.php?start_date=${startStr}&end_date=${endStr}&func_id=${funcId}&filtro_crh=${filtroCrh}&strict_sector=1`);
            const json = await res.json();
            console.log("JSON recebido:", json);

            if (!json.success) {
                console.warn("Erro reportado pela API:", json.message);
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-20 text-red-500">${esc(json.message)}</td></tr>`;
                return;
            }

            if (!json.data || json.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-20 text-slate-400">Nenhum ponto encontrado para estes filtros.</td></tr>';
                return;
            }

            window._pontoData = {};
            tbody.innerHTML = '';

            json.data.forEach(r => {
                window._pontoData[r.id] = r;

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50 transition-colors';

                const isEnviado = r.enviado_crh && r.enviado_crh !== 'false' && r.enviado_crh !== '0';
                const isFinalizado = (r.status_crh === 'deferido') && !IS_SUPER;

                // Checkbox
                const tdCb = document.createElement('td');
                tdCb.className = 'px-4 py-4 text-center border-b border-slate-100';
                tdCb.innerHTML = (r.status_crh === 'deferido') ? '-' : `<input type="checkbox" class="row-check w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500" value="${r.id}" onchange="updateCount()">`;
                tr.appendChild(tdCb);

                // Data
                const tdData = document.createElement('td');
                tdData.className = 'px-4 py-4 font-medium text-slate-700 text-xs border-b border-slate-100';
                tdData.textContent = r.data ? String(r.data).split('T')[0].split('-').reverse().join('/') : '-';
                tr.appendChild(tdData);

                // Colaborador
                const tdNome = document.createElement('td');
                tdNome.className = 'px-4 py-4 border-b border-slate-100 min-w-[250px]';

                const hasError = r.falta_turno1_entrada || r.falta_turno1_saida || r.falta_turno2_entrada || r.falta_turno2_saida ||
                                 r.atrasou_primeiro_ponto || r.atrasou_segundo_ponto || r.atrasou_terceiro_ponto || r.atrasou_quarto_ponto;
                
                const hasJust = (r.tipo_justificativa && r.tipo_justificativa !== 'null' && String(r.tipo_justificativa).trim() !== '') || 
                                (r.justificativa && r.justificativa !== 'null' && String(r.justificativa).trim() !== '') || 
                                (r.just_ent1 && r.just_ent1 !== 'null' && String(r.just_ent1).trim() !== '') ||
                                (r.just_sai1 && r.just_sai1 !== 'null' && String(r.just_sai1).trim() !== '') ||
                                (r.just_ent2 && r.just_ent2 !== 'null' && String(r.just_ent2).trim() !== '') ||
                                (r.just_sai2 && r.just_sai2 !== 'null' && String(r.just_sai2).trim() !== '');

                const needsAction = hasError && !hasJust && !isEnviado;

                let alertHtml = '';
                if (needsAction) {
                    alertHtml = `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-[10px] font-black uppercase tracking-wider animate-pulse ml-2 shadow-sm border border-red-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                    Ação Necessária
                                 </span>`;
                }

                tdNome.innerHTML = `
                    <div class="flex items-center">
                        <div class="font-bold text-slate-800">${esc(r.nome)}</div>
                        ${alertHtml}
                    </div>
                    <div class="text-xs text-slate-500 font-mono flex gap-2">
                        <span>Matrícula: ${esc(r.matricula)}</span>
                        <span>Setor: ${esc(r.setor || '-')}</span>
                    </div>`;
                tr.appendChild(tdNome);

                // Batidas/Falhas (Resumo Simples)
                const formatResumo = (hora, hasHorario, label, isAtraso, justIndividual, statusCrh, tipoJustificativa, justificativaGlobal) => {
                    if (r.em_ferias) {
                        return `<span class="text-[10px] font-black bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded border border-amber-200 mr-1 uppercase" title="Afastamento Programado: ${r.motivo_afastamento || 'Afastado'}">📅 ${r.motivo_afastamento || 'AFASTADO'}</span>`;
                    }
                    if (!hasHorario) return '';
                    
                    const isFaltaAuto = !hora || hora === 'FALTA' || hora === 'falta';
                    const isIndeferido = statusCrh === 'indeferido';
                    const isDeferido = statusCrh === 'deferido';
                    const hasJust = justIndividual && justIndividual !== 'null' && String(justIndividual).trim() !== '';
                    const hasGlobalJust = tipoJustificativa && tipoJustificativa !== 'null' && String(tipoJustificativa).trim() !== '';
                    
                    // Se houver qualquer justificativa individual, a global não deve "auto-justificar" as outras batidas sozinhas
                    const hasAnyIndiv = (r.just_ent1 && String(r.just_ent1).trim() !== '' && r.just_ent1 !== 'null') || 
                                        (r.just_sai1 && String(r.just_sai1).trim() !== '' && r.just_sai1 !== 'null') || 
                                        (r.just_ent2 && String(r.just_ent2).trim() !== '' && r.just_ent2 !== 'null') || 
                                        (r.just_sai2 && String(r.just_sai2).trim() !== '' && r.just_sai2 !== 'null');

                    const hasAnyJust = hasJust || (hasGlobalJust && !hasAnyIndiv && (isAtraso && isAtraso !== 'false' || isFaltaAuto));
                    
                    const h = isFaltaAuto ? 'Falta' : String(hora).substring(0, 5);
                    const valJust = justificativaGlobal ? justificativaGlobal.replace(/'/g, "\\'").replace(/"/g, "&quot;").replace(/\n/g, " ") : '';

                    // 1. DEFERIDO (Verde)
                    if (isDeferido && hasAnyJust) {
                        return `<span class="text-[10px] bg-emerald-50 text-emerald-700 font-bold rounded px-1.5 py-0.5 border border-emerald-200 mr-1" title="Justificativa aprovada pelo RH">✔ Justificado</span>`;
                    }

                    // 2. INDEFERIDO (Vermelho com Motivo)
                    if (isIndeferido && hasAnyJust) {
                        const l = isFaltaAuto ? `⛔ Falta` : `⛔ Falta (${h})`;
                        return `<span class="text-[10px] font-bold bg-red-50 text-red-700 px-1.5 py-0.5 rounded border border-red-300 mr-1 cursor-pointer hover:bg-red-100 transition-colors" 
                                      onclick="mostrarMotivo('${valJust}')"
                                      title="Clique para ver o motivo do indeferimento">${l}</span>`;
                    }

                    // 3. JUSTIFICADO PENDENTE (Azul)
                    if (hasAnyJust) {
                        return `<span class="text-[10px] bg-blue-50 text-blue-700 font-bold rounded px-1.5 py-0.5 border border-blue-200 mr-1 cursor-help" title="Pendente de Aprovação">✔ Justificado</span>`;
                    }

                    // 4. ATRASO OU FALTA AUTOMÁTICA (Vermelho Padrão)
                    if (isAtraso && isAtraso !== 'false' && isAtraso !== false && !isFaltaAuto) {
                        return `<span class="text-[10px] bg-red-50 text-red-600 font-bold rounded px-1.5 py-0.5 border border-red-100 mr-1 cursor-help" title="Atraso sem justificativa por enquanto">${h}</span>`;
                    }
                    if (isFaltaAuto) {
                        return `<span class="text-[10px] bg-red-50 text-red-700 font-bold rounded px-1.5 py-0.5 border border-red-300 mr-1 cursor-pointer hover:bg-red-100 transition-colors" onclick="Swal.fire('Aviso', 'Ponto não registrado e sem justificativa até o momento.', 'info')">⛔ Falta</span>`;
                    }

                    // 5. NORMAL OK (Verde)
                    return `<span class="text-[10px] bg-emerald-50 text-emerald-600 font-bold rounded px-1.5 py-0.5 border border-emerald-50 mr-1">${h}</span>`;
                };
                
                let resHtml = '';
                resHtml += formatResumo(r.primeiro_ponto, !!r.primeiro_horario, 'E1', r.atrasou_primeiro_ponto, r.just_ent1, r.status_crh, r.tipo_justificativa, r.justificativa);
                resHtml += formatResumo(r.segundo_ponto, !!r.segundo_horario, 'S1', r.atrasou_segundo_ponto, r.just_sai1, r.status_crh, r.tipo_justificativa, r.justificativa);
                resHtml += formatResumo(r.terceiro_ponto, !!r.terceiro_horario, 'E2', r.atrasou_terceiro_ponto, r.just_ent2, r.status_crh, r.tipo_justificativa, r.justificativa);
                resHtml += formatResumo(r.quarto_ponto, !!r.quarto_horario, 'S2', r.atrasou_quarto_ponto, r.just_sai2, r.status_crh, r.tipo_justificativa, r.justificativa);
                
                if (r.aviso) {
                    resHtml += `<div class="mt-2 text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-200 rounded px-2 py-1 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> Aviso: ${esc(r.aviso)}</div>`;
                }

                const tdBat = document.createElement('td');
                tdBat.className = 'px-4 py-4 text-center border-b border-slate-100';
                tdBat.innerHTML = resHtml || '<span class="text-slate-300">-</span>';
                tr.appendChild(tdBat);

                // Status Geral e Justificativa
                const tdSt = document.createElement('td');
                tdSt.className = 'px-4 py-4 max-w-[200px] border-b border-slate-100 truncate';
                let justifyContent = '<span class="text-[11px] font-bold text-slate-500 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-lg">Sem Justificativa</span>';
                if (hasJust) {
                    const badgeText = r.tipo_justificativa ? esc(r.tipo_justificativa) : 'Justificado';
                    const descText = r.justificativa ? esc(r.justificativa) : '';
                    let attachmentIcons = '';
                    if (r.anexo_justificativa) {
                        try {
                            const paths = JSON.parse(r.anexo_justificativa);
                            if (Array.isArray(paths)) {
                                attachmentIcons = paths.map(p => `
                                    <a href="../../${esc(p)}" target="_blank" class="inline-block p-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition-colors" title="Ver Comprovante">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    </a>
                                `).join(' ');
                            } else {
                                throw new Error();
                            }
                        } catch(e) {
                            attachmentIcons = `
                                <a href="../../${esc(r.anexo_justificativa)}" target="_blank" class="inline-block p-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition-colors" title="Ver Comprovante">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                </a>
                            `;
                        }
                    }
                    justifyContent = `
                        <div class="flex flex-wrap items-center gap-1 mb-1">
                            <span class="text-[11px] font-bold bg-blue-50 text-blue-700 px-2 py-0.5 rounded-lg border border-blue-200" title="${esc(r.justificativa)}">${badgeText}</span>
                            ${attachmentIcons}
                        </div>
                        <span class="text-xs text-slate-500 ml-1 truncate block max-w-full">${descText}</span>
                    `;
                }
                
                // Mostrar Comunicado (Aviso do Funcionário)
                if (r.comunicado) {
                    justifyContent += `
                        <div class="mt-2 p-2 bg-amber-50 border border-amber-100 rounded text-[10px] leading-tight text-amber-800 italic relative group">
                            <span class="font-bold uppercase text-[9px] block mb-1 opacity-70">Aviso do Funcionário:</span>
                            "${esc(r.comunicado)}"
                            ${r.anexo_comunicado ? `
                                <a href="../../${r.anexo_comunicado}" target="_blank" class="flex items-center gap-1 mt-2 text-emerald-700 font-bold hover:underline">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    Ver Documento Anexo
                                </a>
                            ` : ''}
                        </div>
                    `;
                }
                tdSt.innerHTML = justifyContent;
                tr.appendChild(tdSt);

                // Situacao CRH
                const tdCrh = document.createElement('td');
                tdCrh.className = 'px-4 py-4 text-center border-b border-slate-100';
                if (r.status_crh === 'indeferido') {
                    tdCrh.innerHTML = `<span class="text-[11px] font-bold text-red-700 bg-red-100 border border-red-300 px-3 py-1 rounded-full uppercase tracking-wider flex items-center gap-1 inline-flex"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Indeferido</span>`;
                } else if (r.status_crh === 'deferido') {
                    tdCrh.innerHTML = `<span class="text-[11px] font-bold text-emerald-700 bg-emerald-100 border border-emerald-400 px-3 py-1 rounded-full uppercase tracking-wider flex items-center gap-1 inline-flex"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Deferido</span>`;
                } else if (isEnviado) {
                    tdCrh.innerHTML = `<span class="text-[11px] font-bold text-blue-700 bg-blue-100 border border-blue-300 px-3 py-1 rounded-full uppercase tracking-wider flex items-center gap-1 inline-flex"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg> Recebido CRH</span>`;
                } else {
                    tdCrh.innerHTML = `<span class="text-[11px] font-bold text-orange-700 bg-orange-100 border border-orange-300 px-3 py-1 rounded-full uppercase tracking-wider flex items-center gap-1 inline-flex"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Pendente Base</span>`;
                }
                tr.appendChild(tdCrh);

                // Ação Tabela
                const tdAc = document.createElement('td'); tdAc.className = 'px-4 py-4 text-right border-b border-slate-100';
                
                const btnClass = isFinalizado 
                    ? "p-2 text-slate-400 bg-slate-50 cursor-not-allowed opacity-50 font-bold text-xs uppercase tracking-wider rounded-lg border border-slate-200 flex items-center inline-flex gap-1 ml-auto"
                    : "p-2 text-brand-600 hover:bg-brand-100 font-bold text-xs uppercase tracking-wider rounded-lg border border-transparent hover:border-brand-200 transition-all flex items-center inline-flex gap-1 ml-auto";
                
                const btnOnClick = isFinalizado ? "" : `onclick="abrirTratamentoPonto('${r.id}')"`;

                tdAc.innerHTML = `<button ${btnOnClick} class="${btnClass}" ${isFinalizado ? 'disabled title="Registro finalizado pelo CRH - Não pode ser alterado"' : ''}>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Tratar
                </button>`;
                tr.appendChild(tdAc);

                tbody.appendChild(tr);
            });

        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-20 text-red-500 font-semibold">Erro ao carregar dados.</td></tr>`;
            console.error(err);
        }
    }

    async function abrirTratamentoPonto(id) {
        const r = (window._pontoData || {})[id];
        if (!r) return;

        let anexosExistentes = [];
        if (r.anexo_justificativa) {
            try {
                const parse = JSON.parse(r.anexo_justificativa);
                anexosExistentes = Array.isArray(parse) ? parse : [r.anexo_justificativa];
            } catch(e) {
                anexosExistentes = [r.anexo_justificativa];
            }
        }

        const removedPaths = new Set();

        const { value: formValues } = await Swal.fire({
            title: 'Tratamento de Ponto',
            html: `<div class="text-left space-y-3 mt-2">
                <div>
                    <label class="text-xs font-bold text-slate-600">Tipo de Justificativa</label>
                    <select id="sw-tipo" class="swal2-select w-full mt-1 text-sm bg-slate-50 border border-slate-200 rounded p-2 focus:ring focus:ring-brand-200" onchange="document.getElementById('sw-just-container').classList.toggle('hidden', this.value !== 'Outros')">
                        <option value="">Selecione...</option>
                        <option value="Saúde" ${r.tipo_justificativa === 'Saúde' ? 'selected' : ''}>Saúde</option>
                        <option value="Folga eleitoral" ${r.tipo_justificativa === 'Folga eleitoral' ? 'selected' : ''}>Folga eleitoral</option>
                        <option value="Ponto facultativo" ${r.tipo_justificativa === 'Ponto facultativo' ? 'selected' : ''}>Ponto facultativo</option>
                        <option value="Liberação interna (setor)" ${r.tipo_justificativa === 'Liberação interna (setor)' ? 'selected' : ''}>Liberação interna (setor)</option>
                        <option value="Liberação institucional (RH)" ${r.tipo_justificativa === 'Liberação institucional (RH)' ? 'selected' : ''}>Liberação institucional (RH)</option>
                        <option value="Feriado" ${r.tipo_justificativa === 'Feriado' ? 'selected' : ''}>Feriado</option>
                        <option value="Audiência" ${r.tipo_justificativa === 'Audiência' ? 'selected' : ''}>Audiência</option>
                        <option value="Outros" ${r.tipo_justificativa === 'Outros' ? 'selected' : ''}>Outros</option>
                    </select>
                </div>
                <div id="sw-just-container" class="${r.tipo_justificativa === 'Outros' || (!r.tipo_justificativa && r.justificativa) ? '' : 'hidden'}">
                    <label class="text-xs font-bold text-slate-600">Descrição / Motivo</label>
                    <textarea id="sw-just" class="swal2-textarea w-full mt-1 text-sm m-0 p-2" rows="2" placeholder="Motivo geral...">${esc(r.justificativa || '')}</textarea>
                </div>
                <div class="mt-3 p-3 border border-dashed border-slate-300 rounded-lg bg-slate-50 flex flex-col gap-2">
                    <label class="text-xs font-bold text-slate-600 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                        Anexar Documentos Comprobatórios (Máx: 3MB por arquivo)
                    </label>
                    
                    ${r.anexo_comunicado ? `
                        <div class="p-2 bg-amber-50 border border-amber-200 rounded-lg mb-1">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-amber-700 uppercase tracking-tight">📎 Documento do Funcionário</span>
                                <a href="../../${esc(r.anexo_comunicado)}" target="_blank" class="text-[9px] bg-amber-200 hover:bg-amber-300 px-1.5 py-0.5 rounded text-amber-800 font-bold transition-colors">Ver Original</a>
                            </div>
                            <label class="flex items-center gap-2 mt-2 cursor-pointer group">
                                <input type="checkbox" id="sw-usar-comunicado" class="w-3.5 h-3.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500" ${anexosExistentes.includes(r.anexo_comunicado) ? 'checked' : ''}>
                                <span class="text-[11px] text-slate-700 font-medium group-hover:text-brand-700">Manter este documento como justificativa</span>
                            </label>
                        </div>
                    ` : ''}

                    <div id="sw-existentes-list" class="space-y-1">
                        ${anexosExistentes.filter(p => p !== r.anexo_comunicado).map(p => `
                            <div class="flex items-center justify-between p-1.5 bg-white border border-slate-200 rounded text-[10px] group" id="item-${btoa(p).replace(/=/g,'')}">
                                <div class="flex items-center gap-2 truncate pr-4">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <a href="../../${esc(p)}" target="_blank" class="text-blue-600 font-bold truncate hover:underline">${esc(p.split('/').pop())}</a>
                                </div>
                                <button type="button" onclick="window._removerAnexoGlobal('${p}', '${btoa(p).replace(/=/g,'')}')" class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        `).join('')}
                    </div>

                    <input type="file" id="sw-anexo" class="block w-full text-xs text-slate-500 mt-2 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 transition-colors" accept=".pdf,.jpg,.jpeg" multiple>
                    <p class="text-[9px] text-slate-400 mt-1">* Segure Ctrl para selecionar múltiplos novos arquivos</p>
                </div>
                <div class="mt-3">
                    <label class="text-xs font-bold text-slate-600">Justificativas por Marcação</label>
                    <div class="grid grid-cols-2 gap-2 mt-2 text-xs">
                        <div><label class="text-[10px] font-bold text-slate-500 uppercase">Turno 1 - Entrada</label><input id="sw-jent1" class="swal2-input w-full mt-1 m-0 h-8 px-2 text-xs" value="${esc(r.just_ent1 || '')}"></div>
                        <div><label class="text-[10px] font-bold text-slate-500 uppercase">Turno 1 - Saída</label><input id="sw-jsai1" class="swal2-input w-full mt-1 m-0 h-8 px-2 text-xs" value="${esc(r.just_sai1 || '')}"></div>
                        <div><label class="text-[10px] font-bold text-slate-500 uppercase">Turno 2 - Entrada</label><input id="sw-jent2" class="swal2-input w-full mt-1 m-0 h-8 px-2 text-xs" value="${esc(r.just_ent2 || '')}"></div>
                        <div><label class="text-[10px] font-bold text-slate-500 uppercase">Turno 2 - Saída</label><input id="sw-jsai2" class="swal2-input w-full mt-1 m-0 h-8 px-2 text-xs" value="${esc(r.just_sai2 || '')}"></div>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="text-xs font-bold text-red-600 uppercase flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Aviso de Atraso / Falta (Visível para o Funcionário)
                    </label>
                    <textarea id="sw-aviso" class="swal2-textarea w-full mt-1 text-sm m-0 p-2 border-red-200 bg-red-50" rows="2" placeholder="Ex: Atraso excedeu limite permitido...">${esc(r.aviso || '')}</textarea>
                </div></div>`,
            showCancelButton: true,
            confirmButtonText: 'Salvar Justificativa',
            cancelButtonText: 'Cancelar',
            didOpen: () => {
                window._removerAnexoGlobal = (path, idHash) => {
                    removedPaths.add(path);
                    const el = document.getElementById('item-' + idHash);
                    if (el) el.classList.add('hidden');
                };
            },
            preConfirm: () => {
                const tipo = document.getElementById('sw-tipo').value;
                const fileInput = document.getElementById('sw-anexo');
                const usarComunicado = document.getElementById('sw-usar-comunicado')?.checked || false;
                
                if (fileInput.files.length > 0) {
                    for (let i = 0; i < fileInput.files.length; i++) {
                        const file = fileInput.files[i];
                        if (file.size > 3 * 1024 * 1024) {
                            Swal.showValidationMessage(`O arquivo "${file.name}" excede 3MB.`);
                            return false;
                        }
                    }
                }

                if (!usarComunicado && r.anexo_comunicado) {
                    removedPaths.add(r.anexo_comunicado);
                }

                return {
                    tipo_justificativa: tipo,
                    justificativa: document.getElementById('sw-just').value,
                    just_ent1: document.getElementById('sw-jent1').value,
                    just_sai1: document.getElementById('sw-jsai1').value,
                    just_ent2: document.getElementById('sw-jent2').value,
                    just_sai2: document.getElementById('sw-jsai2').value,
                    aviso: document.getElementById('sw-aviso').value,
                    new_files: fileInput.files,
                    removidos: Array.from(removedPaths),
                    usar_anexo_comunicado: usarComunicado
                }
            }
        });

        if (!formValues) return;

        const formData = new FormData();
        formData.append('action', 'justificar');
        formData.append('strict_sector', '1');
        formData.append('id', id);
        formData.append('tipo_justificativa', formValues.tipo_justificativa);
        formData.append('justificativa', formValues.justificativa);
        formData.append('just_ent1', formValues.just_ent1);
        formData.append('just_sai1', formValues.just_sai1);
        formData.append('just_ent2', formValues.just_ent2);
        formData.append('just_sai2', formValues.just_sai2);
        formData.append('aviso', formValues.aviso);
        formData.append('usar_anexo_comunicado', formValues.usar_anexo_comunicado ? '1' : '0');
        formData.append('anexos_removidos', JSON.stringify(formValues.removidos));

        if (formValues.new_files.length > 0) {
            for (let i = 0; i < formValues.new_files.length; i++) {
                formData.append('anexo[]', formValues.new_files[i]);
            }
        }

        const confirmBtn = Swal.getConfirmButton();
        window.setLoading(confirmBtn, true);

        const res  = await fetch('../../api/relatorios.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        window.setLoading(confirmBtn, false);
        Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Salvo!' : 'Erro', text: data.message, timer: 2000, showConfirmButton: false });
        if (data.success) loadPontoData();
    }

    async function enviarSelecionados() {
        const checks = document.querySelectorAll('.row-check:checked');
        if (checks.length === 0) return;

        const ids = Array.from(checks).map(c => c.value);

        const result = await Swal.fire({
            title: `Enviar ${ids.length} registro(s) ao CRH?`,
            text: "Após enviado, o RH passará a ter ciência dessa bateria de justificativas.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sim, Encaminhar',
            cancelButtonText: 'Revisar Mais'
        });

        if (result.isConfirmed) {
            const confirmBtn = Swal.getConfirmButton();
            window.setLoading(confirmBtn, true);
            
            try {
                const res = await fetch('../../api/relatorios.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ action: 'enviar_crh', ids })
                });
                const data = await res.json();
                window.setLoading(confirmBtn, false);
                
                if(data.success) {
                    Swal.fire('Sucesso!', data.message, 'success');
                    loadPontoData();
                } else {
                    Swal.fire('Erro', data.message, 'error');
                }
            } catch(e) {
                window.setLoading(confirmBtn, false);
                Swal.fire('Erro', 'Falha na comunicação com servidor.', 'error');
            }
        }
    }
</script>

<?php include 'layout/footer.php'; ?>
