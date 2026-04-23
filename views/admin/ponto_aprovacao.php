<?php include 'layout/header.php'; 

// Somente Admin/RH pode acessar
$is_crh = (($_SESSION['user_level'] ?? '') == '1' || in_array(strtolower(trim($_SESSION['user_name'] ?? '')), ['corsin', 'crh']) || in_array(strtolower(trim($_SESSION['user_setor'] ?? '')), ['corsin', 'crh']));
if (!$is_crh) {
    echo "<div class='p-8 text-center text-red-500 font-bold'>Acesso negado. Apenas o setor de Recursos Humanos pode acessar esta página.</div>";
    include 'layout/footer.php';
    exit;
}
?>

<div class="max-w-full mx-4 flex flex-col h-full bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Aprovação CRH</h1>
                <p class="text-sm text-slate-500 mt-1">Valide as justificativas submetidas pelos gestores.</p>
            </div>
        </div>

        <!-- Filtros -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-lg border border-slate-200 shadow-sm">
                <span class="text-xs font-semibold text-slate-500">Período:</span>
                <input type="date" id="dailyCardDateStart" onchange="loadPontoAprovacao()"
                    class="border-0 p-0 text-sm focus:ring-0 text-slate-700 font-medium bg-transparent">
                <span class="text-slate-300">|</span>
                <input type="date" id="dailyCardDateEnd" onchange="loadPontoAprovacao()"
                    class="border-0 p-0 text-sm focus:ring-0 text-slate-700 font-medium bg-transparent">
            </div>

            <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-lg border border-slate-200 shadow-sm">
                <span class="text-xs font-semibold text-slate-500">Status:</span>
                <select id="filtroStatusCrh" onchange="loadPontoAprovacao()"
                    class="border-0 p-0 text-sm focus:ring-0 text-slate-700 font-medium bg-transparent min-w-[140px]">
                    <option value="pendente" selected>Pendentes (Aguardando Análise)</option>
                    <option value="deferido">Deferidos (Aprovados)</option>
                    <option value="indeferido">Indeferidos (Recusados)</option>
                    <option value="todos">Todos os Registros Recebidos</option>
                </select>
            </div>
            
            <input type="hidden" id="filtFuncId" value="">
            <button onclick="abrirModalSelecaoFunc()" id="btnFiltFunc"
                class="px-3 py-2 bg-white text-slate-700 border border-slate-200 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-colors shadow-sm inline-flex items-center gap-2 max-w-[200px] truncate">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span>Colaborador: Todos</span>
            </button>

            <button onclick="abrirRelatorioGeral()"
                class="px-4 py-2 bg-white text-indigo-600 border border-indigo-200 rounded-lg text-sm font-semibold hover:bg-indigo-50 transition-colors shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Gerar Relatório
            </button>

            <button onclick="loadPontoAprovacao()"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition-colors shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Atualizar
            </button>
        </div>
    </div>

    <div class="p-6 flex-1 overflow-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-indigo-50/50 text-slate-500 font-semibold uppercase text-xs sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-3 rounded-l-lg border-b border-slate-100 whitespace-nowrap">Data</th>
                    <th class="px-4 py-3 border-b border-slate-100">Colaborador</th>
                    <th class="px-4 py-3 text-center border-b border-slate-100 whitespace-nowrap">Batidas Originais</th>
                    <th class="px-4 py-3 border-b border-slate-100">Justificativa do Gestor</th>
                    <th class="px-4 py-3 text-right rounded-r-lg border-b border-slate-100 whitespace-nowrap">Ação CRH</th>
                </tr>
            </thead>
            <tbody id="pontoBody" class="divide-y divide-slate-100">
                <tr>
                    <td colspan="5" class="text-center py-20 text-slate-400">Carregando dados...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    const CURRENT_USER_LEVEL = <?php echo $_SESSION['user_level'] ?? 3; ?>;
    const CURRENT_USER_NAME  = "<?php echo strtolower(trim($_SESSION['user_name'] ?? '')); ?>";
    const CURRENT_USER_SETOR = "<?php echo strtolower(trim($_SESSION['user_setor'] ?? '')); ?>";
    
    // Gestores e Administradores podem alterar decisões
    const IS_SUPER_USER = (CURRENT_USER_LEVEL == 1) || 
                          ['corsin', 'crh'].includes(CURRENT_USER_NAME) || 
                          ['corsin', 'crh'].includes(CURRENT_USER_SETOR);

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

        loadPontoAprovacao();
    });

    async function loadPontoAprovacao() {
        const tbody = document.getElementById('pontoBody');
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-20 text-slate-500"><svg class="animate-spin h-8 w-8 mx-auto mb-3 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Buscando pendências...</td></tr>';

        try {
            const startStr = document.getElementById('dailyCardDateStart').value;
            const endStr   = document.getElementById('dailyCardDateEnd').value;
            const statusCrh= document.getElementById('filtroStatusCrh').value;
            const funcId   = document.getElementById('filtFuncId').value;

            let url = `../../api/relatorios.php?start_date=${startStr}&end_date=${endStr}&filtro_crh=enviados`;
            if (funcId) url += `&func_id=${funcId}`;

            const res  = await fetch(url);
            const json = await res.json();

            if (!json.success) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-20 text-red-500">${esc(json.message)}</td></tr>`;
                return;
            }

            let filtered = (json.data || []).filter(r => {
                const s = r.status_crh || 'pendente';
                if (statusCrh === 'todos') return true;
                return s === statusCrh;
            });

            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-20 text-slate-400">Nenhum registro encontrado para a aprovação nesta categoria.</td></tr>';
                return;
            }

            tbody.innerHTML = '';

            filtered.forEach(r => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50 transition-colors';

                // Data
                const tdData = document.createElement('td');
                tdData.className = 'px-4 py-4 font-medium text-slate-700 text-xs border-b border-slate-100 whitespace-nowrap';
                tdData.innerHTML = r.data ? `<div>${String(r.data).split('T')[0].split('-').reverse().join('/')}</div><div class="text-[10px] text-slate-400 font-normal mt-0.5">ID: #${r.id}</div>` : '-';
                tr.appendChild(tdData);

                // Colaborador
                const tdNome = document.createElement('td');
                tdNome.className = 'px-4 py-4 border-b border-slate-100';
                tdNome.innerHTML = `<div class="font-bold text-slate-800">${esc(r.nome)}</div><div class="text-xs text-slate-500 flex gap-2"><span>Matrícula: ${esc(r.matricula)}</span>&bull;<span>Setor: ${esc(r.setor || '-')}</span></div>`;
                tr.appendChild(tdNome);

                let resHtml = '';
                const hasGlobalJust = r.tipo_justificativa && r.tipo_justificativa !== 'null' && String(r.tipo_justificativa).trim() !== '';

                // Batidas Originais (Resumo Simples)
                const formatResumo = (hora, hasHorario, label, statusCrh, hasIndivJust, atrasou, faltaAuto) => {
                    if (r.em_ferias) {
                        return `<span class="text-[10px] font-black bg-amber-50 text-amber-700 px-1.5 py-1 rounded border border-amber-200 mr-1 uppercase" title="Afastamento Programado: ${r.motivo_afastamento || 'Afastado'}">📅 ${r.motivo_afastamento || 'AFASTADO'}</span>`;
                    }
                    if (!hasHorario) return '';
                    const isFaltaAuto = !hora || hora === 'FALTA' || hora === 'falta';
                    const isIndeferido = statusCrh === 'indeferido';
                    const isDeferido = statusCrh === 'deferido';
                    const hasJust = hasIndivJust;
                    
                    // Se houver qualquer justificativa individual na linha, a global não deve "auto-justificar" as outras batidas sozinhas
                    const hasAnyIndiv = (r.just_ent1 && String(r.just_ent1).trim() !== '' && r.just_ent1 !== 'null') || 
                                        (r.just_sai1 && String(r.just_sai1).trim() !== '' && r.just_sai1 !== 'null') || 
                                        (r.just_ent2 && String(r.just_ent2).trim() !== '' && r.just_ent2 !== 'null') || 
                                        (r.just_sai2 && String(r.just_sai2).trim() !== '' && r.just_sai2 !== 'null');

                    const hasAnyJust = hasJust || (hasGlobalJust && !hasAnyIndiv && (atrasou || isFaltaAuto || faltaAuto));
                    
                    const time = isFaltaAuto ? 'Falta' : String(hora).substring(0, 5);
                    const valJust = r.justificativa ? r.justificativa.replace(/'/g, "\\'").replace(/"/g, "&quot;").replace(/\n/g, " ") : '';

                    // 1. DEFERIDO (Verde)
                    if (isDeferido && hasAnyJust) {
                        return `<span class="text-[10px] font-bold bg-emerald-50 text-emerald-700 px-1.5 py-1 rounded border border-emerald-200 mr-1" title="Justificativa aprovada pelo RH">✔ Justificado</span>`;
                    }

                    // 2. INDEFERIDO (Vermelho com Motivo)
                    if (isIndeferido && hasAnyJust) {
                        const l = isFaltaAuto ? `⛔ Falta` : `⛔ Falta (${time})`;
                        return `<span class="text-[10px] font-bold bg-red-50 text-red-700 px-1.5 py-1 rounded border border-red-300 mr-1 cursor-pointer hover:bg-red-100 transition-colors" 
                                      onclick="mostrarMotivo('${valJust}')"
                                      title="Clique para ver o motivo do indeferimento">${l}</span>`;
                    }

                    // 3. JUSTIFICADO PENDENTE (Azul)
                    if (hasAnyJust) {
                        return `<span class="text-[10px] bg-blue-50 text-blue-700 font-bold rounded px-1.5 py-1 border border-blue-200 mr-1 cursor-help" title="Justificativa enviada ao RH">✔ Justificado</span>`;
                    }

                    // 4. ATRASO OU FALTA AUTOMÁTICA (Vermelho Padrão)
                    if (atrasou && atrasou !== 'false' && atrasou !== false && !isFaltaAuto) {
                        return `<span class="text-[10px] font-bold bg-red-50 text-red-800 px-1.5 py-1 rounded border border-red-200 mr-1 cursor-help" title="Atraso sem justificativa por enquanto">${time}</span>`;
                    }
                    if (isFaltaAuto) {
                        return `<span class="text-[10px] font-bold bg-red-50 text-red-700 px-1.5 py-1 rounded border border-red-300 mr-1 cursor-pointer hover:bg-red-100 transition-colors" onclick="Swal.fire('Aviso', 'Ponto não registrado e sem justificativa até o momento.', 'info')">⛔ Falta</span>`;
                    }
                    if (faltaAuto) {
                        return `<span class="text-[10px] font-bold bg-red-50 text-red-600 px-1.5 py-1 rounded border border-red-200 cursor-pointer hover:bg-red-100 mr-1" onclick="Swal.fire('Aviso', 'Este ponto deveria ter sido registrado, mas faturou falta.', 'warning')">Falta</span>`;
                    }

                    // 5. NORMAL OK
                    return `<span class="text-[10px] font-bold bg-slate-100 text-slate-700 px-1.5 py-1 rounded border border-slate-100 mr-1">${time}</span>`;
                };
                resHtml += formatResumo(r.primeiro_ponto, !!r.primeiro_horario, 'E1', r.status_crh, !!r.just_ent1, r.atrasou_primeiro_ponto, r.falta_turno1_entrada);
                resHtml += formatResumo(r.segundo_ponto, !!r.segundo_horario, 'S1', r.status_crh, !!r.just_sai1, r.atrasou_segundo_ponto, r.falta_turno1_saida);
                resHtml += formatResumo(r.terceiro_ponto, !!r.terceiro_horario, 'E2', r.status_crh, !!r.just_ent2, r.atrasou_terceiro_ponto, r.falta_turno2_entrada);
                resHtml += formatResumo(r.quarto_ponto, !!r.quarto_horario, 'S2', r.status_crh, !!r.just_sai2, r.atrasou_quarto_ponto, r.falta_turno2_saida);
                
                const tdBat = document.createElement('td');
                tdBat.className = 'px-4 py-4 text-center border-b border-slate-100 whitespace-nowrap';
                tdBat.innerHTML = resHtml || '<span class="text-slate-300">-</span>';
                tr.appendChild(tdBat);

                // Justificativa do Gestor
                const tdSt = document.createElement('td');
                tdSt.className = 'px-4 py-4 max-w-[400px] border-b border-slate-100';
                
                let indivJusts = [];
                if (r.just_ent1) indivJusts.push(`<b class="text-indigo-600">E1:</b> ${esc(r.just_ent1)}`);
                if (r.just_sai1) indivJusts.push(`<b class="text-indigo-600">S1:</b> ${esc(r.just_sai1)}`);
                if (r.just_ent2) indivJusts.push(`<b class="text-indigo-600">E2:</b> ${esc(r.just_ent2)}`);
                if (r.just_sai2) indivJusts.push(`<b class="text-indigo-600">S2:</b> ${esc(r.just_sai2)}`);

                let justifyContent = '';
                const hasJust = (r.tipo_justificativa && r.tipo_justificativa !== 'null') || (r.justificativa && r.justificativa !== 'null') || indivJusts.length > 0;
                
                if (hasJust) {
                    const badgeText = r.tipo_justificativa && r.tipo_justificativa !== 'null' ? esc(r.tipo_justificativa) : 'Justificado';
                    const descText = r.justificativa && r.justificativa !== 'null' ? esc(r.justificativa) : '';
                    let attachment = '';
                    if (r.anexo_justificativa) {
                        let paths = [];
                        try {
                            const parsed = JSON.parse(r.anexo_justificativa);
                            paths = Array.isArray(parsed) ? parsed : [r.anexo_justificativa];
                        } catch(e) {
                            paths = [r.anexo_justificativa];
                        }

                        attachment = `<div class="flex flex-wrap gap-2 mt-2">` + paths.map(p => `
                            <a href="../../${esc(p)}" target="_blank" class="inline-flex items-center gap-1.5 text-[10px] text-white bg-blue-600 hover:bg-blue-700 px-2 py-1.5 rounded-lg border border-blue-500 font-bold shadow-sm transition-all uppercase tracking-tight" title="Ver Arquivo">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg> 
                                ${p.split('/').pop().length > 15 ? 'Ver Documento' : p.split('/').pop()}
                            </a>`).join('') + `</div>`;
                    }
                    
                    let indivHtml = indivJusts.length > 0 ? `<div class="text-[10px] text-slate-600 mt-2 p-2 bg-indigo-50/50 rounded-lg border border-indigo-100/50 space-y-1">${indivJusts.join('<br>')}</div>` : '';

                    justifyContent = `<div class="flex flex-col gap-1 items-start whitespace-normal">
                        <span class="text-[11px] font-bold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded border border-indigo-200">${badgeText}</span>
                        ${descText ? `<div class="text-[11px] text-slate-500 font-medium italic mt-1 leading-relaxed">${descText}</div>` : ''}
                        ${indivHtml}
                        ${attachment}
                    </div>`;
                } else {
                    justifyContent = '<span class="text-[11px] text-slate-400 italic">Nenhum texto informado</span>';
                }
                tdSt.innerHTML = justifyContent;
                tr.appendChild(tdSt);

                // Ação Tabela
                const tdAc = document.createElement('td'); tdAc.className = 'px-4 py-4 text-right border-b border-slate-100 whitespace-nowrap';
                
                const buttonsHtml = `
                    <div class="flex items-center justify-end gap-2">
                        <button onclick="aprovarCrh(${r.id}, true)" class="px-2 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:text-emerald-800 border border-emerald-200 font-bold text-xs uppercase tracking-wider rounded-lg transition-all flex items-center gap-1" title="Aprovar e manter justificado">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Deferir
                        </button>
                        <button onclick="aprovarCrh(${r.id}, false)" class="px-2 py-1.5 bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 border border-red-200 font-bold text-xs uppercase tracking-wider rounded-lg transition-all flex items-center gap-1" title="Recusar e reverter para Falta">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Indeferir
                        </button>
                    </div>`;

                if (!r.status_crh || r.status_crh === 'pendente') {
                    tdAc.innerHTML = buttonsHtml;
                } else {
                    const statusLabel = r.status_crh === 'deferido' 
                        ? `<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg> Deferido</span>`
                        : `<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-red-100 text-red-800 border border-red-200"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Indeferido</span>`;
                    
                    let auditHtml = '';
                    if (r.crh_history) {
                        try {
                            const history = typeof r.crh_history === 'string' ? JSON.parse(r.crh_history) : r.crh_history;
                            if (Array.isArray(history) && history.length > 0) {
                                auditHtml = '<div class="mt-2 text-[9px] text-slate-400 text-right space-y-1 border-t border-slate-50 pt-1">';
                                history.slice().reverse().forEach((h, idx) => {
                                    const dateH = h.timestamp ? h.timestamp.split(' ')[0].split('-').reverse().join('/') + ' ' + h.timestamp.split(' ')[1].substring(0, 5) : '-';
                                    const badge = h.action === 'deferido' ? '✔' : '✘';
                                    const color = h.action === 'deferido' ? 'text-emerald-500' : 'text-red-500';
                                    auditHtml += `<div class="${idx === 0 ? 'font-bold text-slate-500' : ''}" title="${h.motivo ? 'Motivo: ' + h.motivo : ''}">
                                        <span class="${color}">${badge}</span> ${esc(h.user)} - ${dateH}
                                    </div>`;
                                });
                                auditHtml += '</div>';
                            }
                        } catch (e) {
                            console.error("Erro ao processar histórico", e);
                        }
                    }

                    if (!auditHtml && r.crh_user) {
                        const dateAudit = r.crh_updated_at ? r.crh_updated_at.split(' ')[0].split('-').reverse().join('/') : '-';
                        auditHtml = `<div class="text-[9px] text-slate-400 mt-1 italic">Por: ${esc(r.crh_user)} em ${dateAudit}</div>`;
                    }

                    const btnAlterar = (IS_SUPER_USER) 
                        ? `<button onclick="this.parentElement.innerHTML = \`${buttonsHtml.replace(/"/g, '&quot;').replace(/'/g, "\\'")}\`" class="text-[10px] font-bold text-slate-400 hover:text-indigo-600 uppercase tracking-widest transition-colors mt-2">Alterar Decisão</button>`
                        : '';
                    
                    tdAc.innerHTML = `<div class="flex flex-col items-end gap-0">
                        ${statusLabel}
                        ${auditHtml}
                        ${btnAlterar}
                    </div>`;
                }

                tr.appendChild(tdAc);

                tbody.appendChild(tr);
            });

        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-20 text-red-500 font-semibold">Erro ao carregar dados do servidor.</td></tr>`;
        }
    }

    async function abrirRelatorioGeral() {
        // Pega filtros atuais
        const start = document.getElementById('dailyCardDateStart').value;
        const end   = document.getElementById('dailyCardDateEnd').value;
        const statusCrh = document.getElementById('filtroStatusCrh').value;
        const funcId = document.getElementById('filtFuncId').value;
        
        Swal.fire({
            title: `<div class="text-left"><div class="text-indigo-600">Relatório Geral de Aprovações</div><div class="text-sm font-normal text-slate-500">Período: ${start.split('-').reverse().join('/')} a ${end.split('-').reverse().join('/')}</div></div>`,
            html: `<div id="modalRelWrap" class="overflow-hidden min-h-[400px]">
                        <div class="flex items-center justify-center py-20">
                            <svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>
                   </div>`,
            width: '1200px',
            showConfirmButton: false,
            showCloseButton: true,
            customClass: { popup: 'rounded-3xl shadow-2xl' }
        });

        try {
            let url = `../../api/relatorios.php?start_date=${start}&end_date=${end}&filtro_crh=enviados`;
            if (funcId) url += `&func_id=${funcId}`;

            const res = await fetch(url);
            const json = await res.json();
            
            if(!json.success) {
                document.getElementById('modalRelWrap').innerHTML = `<div class="py-10 text-red-500">${esc(json.message)}</div>`;
                return;
            }

            let filtered = (json.data || []).filter(r => {
                const s = r.status_crh || 'pendente';
                if (statusCrh === 'todos') return true;
                return s === statusCrh;
            });

            if (filtered.length === 0) {
                document.getElementById('modalRelWrap').innerHTML = `<div class="py-20 text-slate-400">Nenhum registro encontrado para os filtros selecionados.</div>`;
                return;
            }

            let tableHtml = `<div class="text-right mb-2"><button onclick="imprimirRelatorioPDF()" class="text-[10px] font-bold text-slate-500 hover:text-indigo-600 uppercase tracking-widest flex items-center gap-1 ml-auto"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2H7a2 2 0 00-2 2v4m14 0h-2"></path></svg>Gerar PDF</button></div>
            <div class="max-h-[60vh] overflow-y-auto mt-4 border border-slate-100 rounded-xl" id="tablePrintArea">
                <table class="w-full text-left text-[11px] border-collapse bg-white">
                    <thead class="bg-slate-50 sticky top-0 text-slate-500 uppercase font-black text-[9px] tracking-widest border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3">Data</th>
                            <th class="px-4 py-3">Colaborador</th>
                            <th class="px-4 py-3">Ponto Original</th>
                            <th class="px-4 py-3">Justificativa</th>
                            <th class="px-4 py-3 text-center">Situação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">`;

            filtered.forEach(r => {
                const day = String(r.data).split('T')[0].split('-').reverse().join('/');
                
                const formatBat = (h, hp, pIdx) => {
                    const isFaltaAuto = (!h || h.toLowerCase() === 'falta');
                    if (r.liberacao && isFaltaAuto) {
                        const d = String(r.liberacao.descricao || '').toUpperCase();
                        if (d.includes('FERIADO') || d.includes('FACULTATIVO')) {
                            return `<span class="text-indigo-600 font-bold">${d}</span>`;
                        }
                        if (r.liberacao.data_hora && (pIdx === 2 || pIdx === 4)) {
                             const libTime = r.liberacao.data_hora.split(' ')[1].substring(0, 5);
                             const progTime = hp ? hp.substring(0, 5) : '23:59';
                             if (libTime <= progTime) return `<span class="text-indigo-600 font-bold">LIBERADO</span>`;
                        }
                    }
                    if(!hp) return '';
                    if(isFaltaAuto) return '<span class="text-red-500 font-bold">FALTA</span>';
                    return `<span class="font-mono">${String(h).substring(0,5)}</span>`;
                };

                let batidas = [
                    formatBat(r.primeiro_ponto, r.primeiro_horario, 1),
                    formatBat(r.segundo_ponto, r.segundo_horario, 2),
                    formatBat(r.terceiro_ponto, r.terceiro_horario, 3),
                    formatBat(r.quarto_ponto, r.quarto_horario, 4)
                ].filter(x => x !== '').join(' | ');

                let stLabel = '';
                if (r.status_crh === 'deferido') stLabel = '<span class="text-[9px] font-black text-emerald-600">DEFERIDO</span>';
                else if (r.status_crh === 'indeferido') stLabel = '<span class="text-[9px] font-black text-red-600">INDEFERIDO</span>';
                else stLabel = '<span class="text-[9px] font-black text-orange-600">PENDENTE</span>';

                const just = (r.justificativa || r.tipo_justificativa || (r.liberacao ? r.liberacao.descricao : '-'));

                tableHtml += `<tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 text-slate-600">${day}</td>
                    <td class="px-4 py-3">
                        <div class="font-bold text-slate-800">${esc(r.nome)}</div>
                        <div class="text-[10px] text-slate-500 uppercase">${esc(r.setor || '-')}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-700 font-medium">${batidas}</td>
                    <td class="px-4 py-3 max-w-[250px] truncate text-slate-500" title="${esc(r.justificativa || '')}">${esc(just)}</td>
                    <td class="px-4 py-3 text-center">${stLabel}</td>
                </tr>`;
            });

            tableHtml += `</tbody></table></div>`;
            document.getElementById('modalRelWrap').innerHTML = tableHtml;

        } catch (e) {
            document.getElementById('modalRelWrap').innerHTML = `<div class="py-10 text-red-500">Erro na conexão com o servidor.</div>`;
        }
    }

    window.imprimirRelatorioPDF = function() {
        const start = document.getElementById('dailyCardDateStart').value.split('-').reverse().join('/');
        const end   = document.getElementById('dailyCardDateEnd').value.split('-').reverse().join('/');
        
        var bgFullBase64 = "data:image/png;base64,<?php echo base64_encode(file_get_contents('../../public/img/timbre_bg.png')); ?>";
        var logoBgBase64 = "data:image/png;base64,<?php echo base64_encode(file_get_contents('../../public/img/timbre_brasao.png')); ?>";
        var logoGovpbBase64 = "data:image/png;base64,<?php echo base64_encode(file_get_contents('../../public/img/timbre_header.png')); ?>";
        var logoFunadBase64 = "data:image/jpeg;base64,<?php echo base64_encode(file_get_contents('../../public/img/timbre_funad.jpeg')); ?>";

        let pdfBody = [];
        
        // Cabeçalho da Tabela
        pdfBody.push([
            { text: 'Data', style: 'th', alignment: 'left' },
            { text: 'Colaborador', style: 'th', alignment: 'left' },
            { text: 'Ponto Original', style: 'th', alignment: 'center' },
            { text: 'Justificativa', style: 'th', alignment: 'left' },
            { text: 'Situação', style: 'th', alignment: 'center' }
        ]);

        const rows = document.querySelectorAll('#tablePrintArea tbody tr');
        rows.forEach(tr => {
            const cols = tr.querySelectorAll('td');
            if(cols.length >= 5) {
                pdfBody.push([
                    { text: cols[0].innerText.trim(), fontSize: 8 },
                    { text: cols[1].innerText.trim(), fontSize: 8 },
                    { text: cols[2].innerText.replace(/\|/g, '').trim(), fontSize: 8, alignment: 'center' },
                    { text: cols[3].innerText.trim(), fontSize: 8 },
                    { text: cols[4].innerText.trim(), fontSize: 8, alignment: 'center' }
                ]);
            }
        });

        if (pdfBody.length === 1) {
            pdfBody.push([{ text: 'Nenhum registro encontrado para exportar.', colSpan: 5, alignment: 'center', margin: [0, 20, 0, 20], color: '#64748b', italics: true }, {}, {}, {}, {}]);
        }

        var docDefinition = {
            pageSize: 'A4',
            pageOrientation: 'landscape',
            pageMargins: [40, 80, 40, 70],
            background: function() {
                return [
                    { image: bgFullBase64, width: 842, height: 595, absolutePosition: { x: 0, y: 0 } },
                    { image: logoBgBase64, width: 450, absolutePosition: { x: (842 / 2) - 225, y: (595 / 2) - 225 }, opacity: 0.15 }
                ];
            },
            header: {
                margin: [40, 20, 40, 0],
                columns: [
                    {
                        width: 250,
                        columns: [
                            { image: logoFunadBase64, width: 60, margin: [0, 8, 15, 0] },
                            { image: logoGovpbBase64, width: 140 }
                        ]
                    },
                    {
                        text: 'RELATÓRIO GERAL DE APROVAÇÕES DO CRH\nPeríodo: ' + start + ' a ' + end + '\nEmitido em: ' + new Date().toLocaleString('pt-BR'),
                        alignment: 'right', fontSize: 9, bold: true, color: '#000000', margin: [0, 10, 0, 0]
                    }
                ]
            },
            footer: function(currentPage, pageCount) {
                return {
                    margin: [40, 0, 40, 20],
                    columns: [
                        { text: 'Pág ' + currentPage.toString() + ' / ' + pageCount, alignment: 'left', fontSize: 7, color: '#000000', width: 60, margin: [0, 25, 0, 0] },
                        {
                            text: 'SECRETARIA DE ESTADO DA EDUCAÇÃO\nFUNAD – FUNDAÇÃO CENTRO INTEGRADO DE APOIO À PESSOA COM DEFICIÊNCIA\nCER IV – CENTRO ESPECIALIZADO EM REABILITAÇÃO\nRua Dr. Orestes Lisboa, S/N - Pedro Gondim - CEP 58031-090 - João Pessoa/PB\nCNPJ: 24.507.065/0001-07 Email: funad@funad.pb.gov.br\nTel.: (83) 3214-7879 / (83) 3244-1542 / (83) 3243-8446 / (83) 3243-3765',
                            alignment: 'center', fontSize: 6, color: '#000000', width: '*', bold: true
                        },
                        { text: '', width: 60 }
                    ]
                };
            },
            styles: {
                th: { fontSize: 9, bold: true, color: '#000000', margin: [0, 3, 0, 3] }
            },
            content: [
                {
                    table: {
                        headerRows: 1,
                        // Data(10%) Colaborador(*) PontoOrig(18%) Just(30%) Sit(12%)
                        widths: ['10%', '*', '18%', '30%', '12%'],
                        body: pdfBody
                    },
                    layout: {
                        hLineWidth: function(i, node) { return 0.5; },
                        vLineWidth: function(i, node) { return 0.5; },
                        hLineColor: function(i, node) { return '#cbd5e1'; },
                        vLineColor: function(i, node) { return '#cbd5e1'; },
                        paddingLeft: function(i, node) { return 6; },
                        paddingRight: function(i, node) { return 6; },
                        paddingTop: function(i, node) { return 4; },
                        paddingBottom: function(i, node) { return 4; }
                    }
                }
            ]
        };

        if (typeof pdfMake !== 'undefined') {
            pdfMake.vfs = window.pdfMake && window.pdfMake.vfs ? window.pdfMake.vfs : (pdfMake.vfs || {});
            if (!pdfMake.fonts) {
                pdfMake.fonts = {
                    Roboto: {
                        normal: 'Roboto-Regular.ttf',
                        bold: 'Roboto-Medium.ttf',
                        italics: 'Roboto-Italic.ttf',
                        bolditalics: 'Roboto-MediumItalic.ttf'
                    }
                };
            }
        }

        try {
            pdfMake.createPdf(docDefinition).download(`Relatorio_Aprovacoes_${start.replace(/\//g,'-')}.pdf`);
        } catch(e) {
            console.error(e);
            Swal.fire('Erro Técnico', 'Detalhes: ' + (e.message || String(e)), 'error');
        }
    }

    async function aprovarCrh(id, isDeferimento) {
        if (isDeferimento) {
            const result = await Swal.fire({
                title: 'Confirmar Deferimento',
                text: "O ponto será mantido como justificado no cartão.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Sim, Deferir',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                processarStatus(id, 'deferir_crh', '');
            }
        } else {
            const result = await Swal.fire({
                title: 'Indeferir Justificativa',
                html: `<p class="text-sm text-slate-500 mb-3 block">Forneça o motivo do indeferimento. A justificativa será anulada e o turno voltará a ser descontado como <b class="text-red-600">Falta</b>.</p>
                       <textarea id="swal-motivo" class="swal2-textarea w-full mt-0 text-sm p-3" rows="3" placeholder="Motivo da recusa pelo CRH..."></textarea>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Indeferir e Reverter para Falta',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const motivo = document.getElementById('swal-motivo').value;
                    if (!motivo) Swal.showValidationMessage("Você precisa informar o motivo do indeferimento!");
                    return motivo;
                }
            });

            if (result.isConfirmed) {
                processarStatus(id, 'indeferir_crh', result.value);
            }
        }
    }

    async function processarStatus(id, actionCode, motivo) {
        Swal.fire({ title: 'Processando...', didOpen: () => { Swal.showLoading(); }, allowOutsideClick: false });
        
        try {
            const formData = new FormData();
            formData.append('action', actionCode);
            formData.append('id', id);
            if (motivo) formData.append('motivo', motivo);

            const res = await fetch('../../api/relatorios.php', { method: 'POST', body: formData });
            const data = await res.json();
            
            if(data.success) {
                Swal.fire('Sucesso!', data.message, 'success');
                loadPontoAprovacao();
            } else {
                Swal.fire('Erro', data.message, 'error');
            }
        } catch(e) {
            Swal.fire('Erro', 'Falha estrutural na requisição ao servidor.', 'error');
        }
    }
    async function abrirModalSelecaoFunc() {
        Swal.fire({
            title: 'Filtrar por Colaborador',
            html: `
                <div class="p-2">
                    <input type="text" id="swFuncSearch" class="swal2-input w-full m-0 mb-4" placeholder="Buscar por nome ou matrícula..." onkeyup="filtrarListaFunc(this.value)">
                    <div id="swFuncList" class="max-h-[300px] overflow-y-auto border border-slate-100 rounded-lg divide-y divide-slate-50 text-left">
                        <div class="p-8 text-center text-slate-400">Carregando colaboradores...</div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            cancelButtonText: 'Limpar Filtro',
            showConfirmButton: false,
            width: '500px',
            didOpen: async () => {
                try {
                    const res = await fetch('../../api/funcionarios.php?action=list');
                    const data = await res.json();
                    window._swFiltroFuncs = data.data || [];
                    renderizarListaFunc(window._swFiltroFuncs);
                } catch (e) {
                    document.getElementById('swFuncList').innerHTML = '<div class="p-4 text-red-500">Erro ao carregar.</div>';
                }
            }
        }).then((result) => {
            if (result.dismiss === Swal.DismissReason.cancel) {
                document.getElementById('filtFuncId').value = '';
                document.getElementById('btnFiltFunc').querySelector('span').textContent = 'Colaborador: Todos';
                loadPontoAprovacao();
            }
        });
    }

    function renderizarListaFunc(lista) {
        const container = document.getElementById('swFuncList');
        if (!lista.length) {
            container.innerHTML = '<div class="p-4 text-center text-slate-400">Nenhum encontrado.</div>';
            return;
        }
        container.innerHTML = lista.map(f => `
            <div onclick="selecionarFuncFiltro(${f.id}, '${esc(f.nome)}')" class="p-3 hover:bg-indigo-50 cursor-pointer transition-colors flex items-center justify-between group">
                <div>
                    <div class="font-bold text-slate-700 group-hover:text-indigo-600">${esc(f.nome)}</div>
                    <div class="text-[10px] text-slate-500">${esc(f.matricula)}</div>
                </div>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>
        `).join('');
    }

    function filtrarListaFunc(query) {
        const filtered = (window._swFiltroFuncs || []).filter(f => 
            f.nome.toLowerCase().includes(query.toLowerCase()) || 
            f.matricula.toLowerCase().includes(query.toLowerCase())
        );
        renderizarListaFunc(filtered);
    }

    function selecionarFuncFiltro(id, nome) {
        document.getElementById('filtFuncId').value = id;
        document.getElementById('btnFiltFunc').querySelector('span').textContent = 'Colaborador: ' + nome;
        Swal.close();
        loadPontoAprovacao();
    }
</script>

<?php include 'layout/footer.php'; ?>
