<?php include 'layout/header.php'; ?>

<!-- Integrando TomSelect para combobox premium -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<style>
/* Ajustes do Tom Select para harmonizar com Tailwind */
.ts-control {
    border: none !important;
    padding: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    min-width: 280px;
    font-size: 0.875rem !important;
    color: #334155 !important;
    font-weight: 500 !important;
}
.ts-control input::placeholder {
    color: #94a3b8 !important;
}
.ts-wrapper.single .ts-control {
    background-color: transparent !important;
}
.ts-dropdown {
    border-radius: 0.75rem !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
    border: 1px solid #f1f5f9 !important;
    padding: 0.5rem !important;
    z-index: 50 !important;
}
.ts-dropdown .option {
    border-radius: 0.5rem !important;
    padding: 0.5rem 0.75rem !important;
}
.ts-dropdown .active {
    background-color: #f8fafc !important;
    color: #0f172a !important;
}
.ts-wrapper.single .ts-control:after {
    border-color: #94a3b8 transparent transparent transparent !important;
}
</style>

<div
    class="max-w-full mx-4 flex flex-col h-full bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <div
        class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Cartão de Ponto Diário</h1>
                <p class="text-sm text-slate-500 mt-1">Visualize e justifique as batidas de ponto diárias.</p>
            </div>
        </div>

        <!-- Filtros -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-lg border border-slate-200 shadow-sm">
                <span class="text-xs font-semibold text-slate-500">Período:</span>
                <input type="date" id="dailyCardDateStart" onchange="loadDailyCardData()"
                    class="border-0 p-0 text-sm focus:ring-0 text-slate-700 font-medium bg-transparent">
                <span class="text-slate-300">|</span>
                <input type="date" id="dailyCardDateEnd" onchange="loadDailyCardData()"
                    class="border-0 p-0 text-sm focus:ring-0 text-slate-700 font-medium bg-transparent">
            </div>

            <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-lg border border-slate-200 shadow-sm relative z-50">
                <span class="text-xs font-semibold text-slate-500 whitespace-nowrap">Colaborador:</span>
                <select id="dailyCardFuncId" class="min-w-[280px] outline-none" placeholder="Buscar funcionário...">
                    <option value="">Todos os Funcionários</option>
                </select>
            </div>

            <button onclick="loadDailyCardData()"
                class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-semibold hover:bg-slate-700 transition-colors shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
                Atualizar
            </button>
        </div>
    </div>

    <div class="p-6 flex-1 overflow-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-xs sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-3 rounded-l-lg border-b border-slate-100 whitespace-nowrap">Data</th>
                    <th class="px-4 py-3 border-b border-slate-100">Colaborador</th>
                    <th class="px-4 py-3 text-center border-b border-slate-100 whitespace-nowrap">Ent 1</th>
                    <th class="px-4 py-3 text-center border-b border-slate-100 whitespace-nowrap">Sai 1</th>
                    <th class="px-4 py-3 text-center border-b border-slate-100 whitespace-nowrap">Ent 2</th>
                    <th class="px-4 py-3 text-center border-b border-slate-100 whitespace-nowrap">Sai 2</th>
                    <th class="px-4 py-3 border-b border-slate-100 whitespace-nowrap">Status</th>
                    <th class="px-4 py-3 border-b border-slate-100 rounded-r-lg">Comunicado</th>
                </tr>
            </thead>
            <tbody id="dailyCardBody" class="divide-y divide-slate-100">
                <tr>
                    <td colspan="8" class="text-center py-20 text-slate-400">Carregando dados...</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script>
    // Escapa string para uso seguro como texto HTML
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
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
        const currentDay = today.toISOString().split('T')[0];

        const dateStart = document.getElementById('dailyCardDateStart');
        const dateEnd   = document.getElementById('dailyCardDateEnd');

        if (!dateStart.value) dateStart.value = firstDay;
        if (!dateEnd.value)   dateEnd.value   = currentDay;

        carregarListaFuncionariosDaily();
        loadDailyCardData();
    });

    async function carregarListaFuncionariosDaily() {
        const select = document.getElementById('dailyCardFuncId');
        try {
            const res  = await fetch('../../api/funcionarios.php');
            const data = await res.json();
            if (data.success) {
                data.data.forEach(f => {
                    const opt = document.createElement('option');
                    opt.value = f.id;
                    opt.textContent = `${f.matricula} - ${f.nome}`;
                    select.appendChild(opt);
                });
                
                // Inicia o combobox pesquisável usando Tom Select
                new TomSelect(select, {
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    },
                    maxOptions: 100,
                    onChange: function() {
                        loadDailyCardData();
                    }
                });
            }
        } catch (e) { console.error('Erro ao carregar funcionários', e); }
    }

    async function loadDailyCardData() {
        const tbody = document.getElementById('dailyCardBody');
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-20 text-slate-500"><svg class="animate-spin h-8 w-8 mx-auto mb-3 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Buscando registros...</td></tr>';

        try {
            const startStr = document.getElementById('dailyCardDateStart').value;
            const endStr   = document.getElementById('dailyCardDateEnd').value;
            const funcId   = document.getElementById('dailyCardFuncId').value;

            const res  = await fetch(`../../api/relatorios.php?start_date=${startStr}&end_date=${endStr}&func_id=${funcId}`);
            const text = await res.text();

            let json;
            try {
                json = JSON.parse(text);
            } catch(e) {
                console.error('JSON inválido recebido:', text.substring(0, 300));
                tbody.innerHTML = `<tr><td colspan="9" class="text-center py-20 text-red-500 font-semibold">Resposta inválida da API. Verifique o console (F12).</td></tr>`;
                return;
            }

            if (!json.success) {
                tbody.innerHTML = `<tr><td colspan="9" class="text-center py-20 text-red-500">${esc(json.message || 'Erro desconhecido')}</td></tr>`;
                return;
            }

            if (!json.data || json.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center py-20 text-slate-400">Nenhum ponto registrado neste período.</td></tr>';
                return;
            }

            window._pontoData = {};
            tbody.innerHTML = '';

            json.data.forEach(r => {
                window._pontoData[r.id] = r;

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50 transition-colors';

                // — Coluna Data —
                const tdData = document.createElement('td');
                tdData.className = 'px-4 py-4 font-medium text-slate-700 text-xs whitespace-nowrap';
                tdData.textContent = r.data ? String(r.data).split('T')[0].split('-').reverse().join('/') : '-';

                // — Coluna Colaborador —
                const tdNome = document.createElement('td');
                tdNome.className = 'px-4 py-4';
                const hasJustificativa = (r.tipo_justificativa && r.tipo_justificativa !== 'null' && String(r.tipo_justificativa).trim() !== '') ||
                                         (r.justificativa && r.justificativa !== 'null' && String(r.justificativa).trim() !== '') ||
                                         (r.just_ent1 && r.just_ent1 !== 'null' && String(r.just_ent1).trim() !== '') || 
                                         (r.just_sai1 && r.just_sai1 !== 'null' && String(r.just_sai1).trim() !== '') ||
                                         (r.just_ent2 && r.just_ent2 !== 'null' && String(r.just_ent2).trim() !== '') || 
                                         (r.just_sai2 && r.just_sai2 !== 'null' && String(r.just_sai2).trim() !== '');

                const badgeCom = (r.comunicado && r.comunicado !== 'null' && String(r.comunicado).trim() !== '' && !hasJustificativa) ? 
                    `<span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-100 text-amber-700 border border-amber-200 animate-pulse" title="${esc(r.comunicado)}">
                        <svg class="w-2.5 h-2.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        AVISO
                    </span>` : '';
                tdNome.innerHTML = `
                    <div class="flex items-center">
                        <div class="font-bold text-slate-800">${esc(r.nome)}</div>
                        ${badgeCom}
                    </div>
                    <div class="text-xs text-slate-500 font-mono">${esc(r.matricula)}</div>
                `;

                // — Helper de horário —
                function pontoHtml(hora, atrasou, faltaAuto, justIndividual, statusCrh, tipoJustificativa, justificativaGlobal, horarioProgramado, emFerias, motivo, liberacao, pIndex) {
                    if (emFerias) {
                        return `<span class="text-[10px] font-black bg-amber-500 text-white px-2 py-0.5 rounded shadow-sm uppercase border border-amber-600 tracking-tighter" title="Afastamento Programado">${esc(motivo)}</span>`;
                    }

                    const isFaltaAuto = !hora || hora === 'FALTA' || hora === 'falta';

                    // --- Lógica de Ponto Liberado (Feriado, Facultativo, Liberação Antecipada) ---
                    if (liberacao && isFaltaAuto) {
                        const desc = String(liberacao.descricao || '').toUpperCase();
                        const isFeriado = desc.includes('FERIADO');
                        const isFacultativo = desc.includes('FACULTATIVO');
                        
                        if (isFeriado || isFacultativo) {
                            if (horarioProgramado) {
                                const badgeClass = isFeriado ? 'bg-rose-500' : 'bg-violet-500 text-xs';
                                return `<span class="text-[9px] font-black ${badgeClass} text-white px-2 py-1 rounded shadow-sm uppercase tracking-tighter block truncate max-w-[120px]" title="${esc(liberacao.descricao)}">${esc(liberacao.descricao)}</span>`;
                            }
                        } else if (liberacao.data_hora && horarioProgramado) {
                            // Liberação por horário (Ex: Saída Antecipada)
                            const libTime = liberacao.data_hora.split(' ')[1].substring(0, 5);
                            const progTime = horarioProgramado.substring(0, 5);
                            
                            // Se a liberação ocorrer antes ou no horário deste slot, e for um slot de saída (2 ou 4)
                            if (libTime <= progTime && (pIndex === 2 || pIndex === 4)) {
                                return `<div class="flex flex-col items-center">
                                            <span class="text-[8px] font-black text-brand-600 uppercase tracking-tighter mb-0.5">Liberado</span>
                                            <span class="text-[9px] font-bold bg-brand-50 text-brand-700 px-1.5 py-1 rounded border border-brand-200 leading-tight block truncate max-w-[100px]" title="${esc(liberacao.descricao)}">${esc(liberacao.descricao)}</span>
                                        </div>`;
                            }
                        }
                    }

                    const isIndeferido = statusCrh === 'indeferido';
                    const isDeferido = statusCrh === 'deferido';
                    const hasJust = justIndividual && justIndividual !== 'null' && String(justIndividual).trim() !== '';
                    const hasGlobalJust = tipoJustificativa && tipoJustificativa !== 'null' && String(tipoJustificativa).trim() !== '';
                    
                    // Se não há horário programado e não há batida, não é falta.
                    if (!horarioProgramado && isFaltaAuto) {
                        return '<span class="text-slate-300">-</span>';
                    }

                    // Se houver qualquer justificativa individual na linha, a global não deve "auto-justificar" as outras batidas sozinhas
                    const hasAnyIndiv = (r.just_ent1 && String(r.just_ent1).trim() !== '' && r.just_ent1 !== 'null') || 
                                        (r.just_sai1 && String(r.just_sai1).trim() !== '' && r.just_sai1 !== 'null') || 
                                        (r.just_ent2 && String(r.just_ent2).trim() !== '' && r.just_ent2 !== 'null') || 
                                        (r.just_sai2 && String(r.just_sai2).trim() !== '' && r.just_sai2 !== 'null');

                    const hasAnyJust = hasJust || (hasGlobalJust && !hasAnyIndiv && (atrasou || isFaltaAuto || faltaAuto));
                    
                    const time = isFaltaAuto ? 'Falta' : String(hora).substring(0, 5);
                    const valJust = justificativaGlobal ? justificativaGlobal.replace(/'/g, "\\'").replace(/"/g, "&quot;").replace(/\n/g, " ") : '';

                    // 1. DEFERIDO (Verde)
                    if (isDeferido && hasAnyJust) {
                        return `<span class="text-[10px] font-bold bg-emerald-50 text-emerald-700 px-2 py-1 rounded border border-emerald-200" title="Justificativa aprovada pelo RH">✔ Justificado</span>`;
                    }

                    // 2. INDEFERIDO (Vermelho com Motivo)
                    if (isIndeferido && hasAnyJust) {
                        const label = isFaltaAuto ? `⛔ Falta` : `⛔ Falta (${esc(time)})`;
                        return `<span class="text-[10px] font-bold bg-red-50 text-red-700 px-2 py-1 rounded border border-red-300 cursor-pointer hover:bg-red-100 transition-colors" 
                                      onclick="mostrarMotivo('${valJust}')"
                                      title="Clique para ver o motivo do indeferimento">${label}</span>`;
                    }

                    // 3. JUSTIFICADO PENDENTE (Azul)
                    if (hasAnyJust) {
                        const t = hasJust ? `Individual: ${esc(justIndividual)}` : 'Justificativa enviada ao RH';
                        return `<span class="text-[10px] font-bold bg-blue-50 text-blue-700 px-2 py-1 rounded border border-blue-200 cursor-help" title="${t}">✔ Justificado</span>`;
                    }

                    // 4. ATRASO (Amarelo/Amber) - NOVO COMPORTAMENTO
                    if (atrasou && atrasou !== 'false' && atrasou !== false && !isFaltaAuto) {
                        return `<div class="flex flex-col items-center gap-0.5">
                                    <span class="text-amber-700 font-bold bg-amber-50 px-2 py-0.5 rounded border border-amber-200 cursor-help" title="Atraso fora da tolerância">${esc(time)}</span>
                                    <span class="text-[9px] font-black text-amber-600 uppercase tracking-tighter">Atraso</span>
                                </div>`;
                    }

                    // 5. FALTA AUTOMÁTICA (Vermelho Padrão)
                    if (isFaltaAuto) {
                        return `<span class="text-[10px] font-bold bg-red-50 text-red-700 px-2 py-1 rounded border border-red-300 cursor-pointer hover:bg-red-100" onclick="Swal.fire('Aviso', 'Ponto não registrado e sem justificativa até o momento.', 'info')">⛔ Falta</span>`;
                    }
                    if (faltaAuto) {
                        return `<span class="text-[10px] font-bold bg-red-50 text-red-600 px-2 py-1 rounded border border-red-200 cursor-pointer hover:bg-red-100" onclick="Swal.fire('Aviso', 'Este ponto deveria ter sido registrado, mas faturou falta.', 'warning')">Falta</span>`;
                    }

                    // 6. NORMAL OK (Verde)
                    return `<span class="text-brand-600 font-semibold bg-emerald-50 px-2 py-1 rounded border border-emerald-50">${esc(time)}</span>`;
                }

                // — Helper de status —
                function statusHtml(rec) {
                    if (rec.em_ferias) {
                        return `<span class="text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-lg flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Afastado
                                </span>`;
                    }

                    const hasJust = (rec.tipo_justificativa && rec.tipo_justificativa !== 'null' && String(rec.tipo_justificativa).trim() !== '') ||
                                  (rec.justificativa && rec.justificativa !== 'null' && String(rec.justificativa).trim() !== '') ||
                                  (rec.just_ent1 && rec.just_ent1 !== 'null' && String(rec.just_ent1).trim() !== '') || 
                                  (rec.just_sai1 && rec.just_sai1 !== 'null' && String(rec.just_sai1).trim() !== '') ||
                                  (rec.just_ent2 && rec.just_ent2 !== 'null' && String(rec.just_ent2).trim() !== '') || 
                                  (rec.just_sai2 && rec.just_sai2 !== 'null' && String(rec.just_sai2).trim() !== '') ||
                                  (rec.status_turno1 && rec.status_turno1 !== 'null' && String(rec.status_turno1).trim() !== '') || 
                                  (rec.status_turno2 && rec.status_turno2 !== 'null' && String(rec.status_turno2).trim() !== '');

                    if (hasJust) {
                        const titleText = rec.justificativa ? esc(rec.justificativa) : (rec.tipo_justificativa ? esc(rec.tipo_justificativa) : 'Possui justificativa');
                        const labelText = rec.tipo_justificativa ? `✓ OK (${esc(rec.tipo_justificativa)})` : '✓ OK';
                        
                        let icons = '';
                        if (rec.anexo_justificativa) {
                            try {
                                const parse = JSON.parse(rec.anexo_justificativa);
                                const paths = Array.isArray(parse) ? parse : [rec.anexo_justificativa];
                                icons = `<div class="flex gap-1 mt-1">` + paths.map(p => `
                                    <a href="../../${esc(p)}" target="_blank" class="text-blue-600 hover:text-blue-800" title="Ver Anexo">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    </a>
                                `).join('') + `</div>`;
                            } catch(e) {
                                icons = `<div class="flex gap-1 mt-1"><a href="../../${esc(rec.anexo_justificativa)}" target="_blank" class="text-blue-600 hover:text-blue-800"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg></a></div>`;
                            }
                        }

                        return `<div class="flex flex-col">
                            <span class="text-[11px] font-bold bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-lg border border-emerald-200 w-fit" title="${titleText}">${labelText}</span>
                            ${icons}
                        </div>`;
                    }
                    
                    const toMin = v => {
                        if (!v || v === 'FALTA' || v === 'falta') return null;
                        const p = String(v).substring(0, 5).split(':');
                        return parseInt(p[0]) * 60 + parseInt(p[1]);
                    };

                    const has1h = !!rec.primeiro_horario, has2h = !!rec.segundo_horario;
                    const has3h = !!rec.terceiro_horario, has4h = !!rec.quarto_horario;
                    const has1p = !!rec.primeiro_ponto && rec.primeiro_ponto !== 'FALTA' && rec.primeiro_ponto !== 'falta';
                    const has2p = !!rec.segundo_ponto  && rec.segundo_ponto  !== 'FALTA' && rec.segundo_ponto  !== 'falta';
                    const has3p = !!rec.terceiro_ponto && rec.terceiro_ponto !== 'FALTA' && rec.terceiro_ponto !== 'falta';
                    const has4p = !!rec.quarto_ponto   && rec.quarto_ponto   !== 'FALTA' && rec.quarto_ponto   !== 'falta';

                    const incompleto = (has1h && !has1p) || (has2h && !has2p) || (has3h && !has3p) || (has4h && !has4p);

                    let esp = 0, trab = 0;
                    const p1h = toMin(rec.primeiro_horario), p2h = toMin(rec.segundo_horario);
                    if (p1h !== null && p2h !== null) esp += p2h - p1h;
                    const p3h = toMin(rec.terceiro_horario), p4h = toMin(rec.quarto_horario);
                    if (p3h !== null && p4h !== null) esp += p4h - p3h;
                    const p1r = toMin(rec.primeiro_ponto), p2r = toMin(rec.segundo_ponto);
                    if (p1r !== null && p2r !== null) trab += p2r - p1r;
                    const p3r = toMin(rec.terceiro_ponto), p4r = toMin(rec.quarto_ponto);
                    if (p3r !== null && p4r !== null) trab += p4r - p3r;

                    if (incompleto) {
                        return '<span class="text-[11px] font-bold text-orange-600 bg-orange-50 border border-orange-200 px-2 py-0.5 rounded-lg">Incompleto</span>';
                    }

                    if (esp > 0) {
                        return '<span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-lg">✓ OK</span>';
                    }
                    if (!rec.primeiro_ponto && !rec.segundo_ponto && !rec.terceiro_ponto && !rec.quarto_ponto)
                        return '<span class="text-slate-300 text-xs">-</span>';
                    return '<span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-lg">✓ OK</span>';
                }

                const p1 = document.createElement('td'); p1.className = 'px-4 py-4 text-center whitespace-nowrap'; p1.innerHTML = pontoHtml(r.primeiro_ponto, r.atrasou_primeiro_ponto, r.falta_turno1_entrada, r.just_ent1, r.status_crh, r.tipo_justificativa, r.justificativa, r.primeiro_horario, r.em_ferias, r.motivo_afastamento, r.liberacao, 1);
                const p2 = document.createElement('td'); p2.className = 'px-4 py-4 text-center whitespace-nowrap'; p2.innerHTML = pontoHtml(r.segundo_ponto,  r.atrasou_segundo_ponto,  r.falta_turno1_saida,   r.just_sai1, r.status_crh, r.tipo_justificativa, r.justificativa, r.segundo_horario, r.em_ferias, r.motivo_afastamento, r.liberacao, 2);
                const p3 = document.createElement('td'); p3.className = 'px-4 py-4 text-center whitespace-nowrap'; p3.innerHTML = pontoHtml(r.terceiro_ponto, r.atrasou_terceiro_ponto, r.falta_turno2_entrada, r.just_ent2, r.status_crh, r.tipo_justificativa, r.justificativa, r.terceiro_horario, r.em_ferias, r.motivo_afastamento, r.liberacao, 3);
                const p4 = document.createElement('td'); p4.className = 'px-4 py-4 text-center whitespace-nowrap'; p4.innerHTML = pontoHtml(r.quarto_ponto,   r.atrasou_quarto_ponto,   r.falta_turno2_saida,   r.just_sai2, r.status_crh, r.tipo_justificativa, r.justificativa, r.quarto_horario, r.em_ferias, r.motivo_afastamento, r.liberacao, 4);

                const tdSt = document.createElement('td'); tdSt.className = 'px-4 py-4 max-w-[180px] whitespace-nowrap'; tdSt.innerHTML = statusHtml(r);
                
                const tdCom = document.createElement('td'); tdCom.className = 'px-4 py-4 text-[10px] italic text-slate-500 min-w-[150px] whitespace-normal'; tdCom.textContent = r.comunicado || '-';

                tr.append(tdData, tdNome, p1, p2, p3, p4, tdSt, tdCom);
                tbody.appendChild(tr);
            });

        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-20 text-red-500 font-semibold">Erro ao carregar dados: ${esc(String(err))}</td></tr>`;
            console.error(err);
        }
    }
</script>

<?php include 'layout/footer.php'; ?>