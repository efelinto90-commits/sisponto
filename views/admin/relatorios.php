<?php include 'layout/header.php'; ?>

<div
    class="max-w-7xl mx-auto flex flex-col h-full bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Relatórios de Ponto</h1>
            <p class="text-sm text-slate-500 mt-1">Filtre e exporte os registros de ponto.</p>
        </div>

        <!-- Filtros -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="w-[280px]">
                <select id="func_id" class="w-full">
                    <option value="">Todos os Funcionários</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="date" id="start_date"
                    class="border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 shadow-sm focus:ring-1 focus:ring-brand-500">
                <span class="text-slate-400">até</span>
                <input type="date" id="end_date"
                    class="border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 shadow-sm focus:ring-1 focus:ring-brand-500">
            </div>
            <button onclick="carregarRelatorio()"
                class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-semibold hover:bg-slate-700 transition-colors shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                    </path>
                </svg>
                Filtrar
            </button>
        </div>
    </div>

    <div class="p-6 flex-1 overflow-auto relative">
        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="absolute inset-0 z-10 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center transition-opacity duration-500 hidden">
            <div class="flex flex-col items-center gap-4">
                <div class="relative">
                    <div class="w-12 h-12 rounded-full border-4 border-slate-200 border-t-brand-500 animate-spin"></div>
                </div>
                <p class="text-sm font-semibold text-slate-500 animate-pulse">Carregando relatórios...</p>
            </div>
            <!-- Skeleton Rows -->
            <div class="w-full max-w-4xl mt-8 space-y-3 px-4 text-center">
                <div class="h-10 bg-slate-100 rounded-lg animate-pulse mx-auto"></div>
                <div class="h-10 bg-slate-50 rounded-lg animate-pulse delay-75 mx-auto"></div>
                <div class="h-10 bg-slate-100 rounded-lg animate-pulse delay-150 mx-auto"></div>
                <div class="h-10 bg-slate-50 rounded-lg animate-pulse delay-200 mx-auto"></div>
            </div>
        </div>
        <table id="tabelaRelatorios" class="w-full text-left border-collapse display nowrap" style="width:100%">
            <thead>
                <tr class="bg-slate-50">
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3 rounded-l-lg">
                        Data</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">
                        Matrícula</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Nome do
                        Funcionário</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3 text-center">
                        Entrada 1</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3 text-center">
                        Saída 1</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3 text-center">
                        Entrada 2</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3 text-center">
                        Saída 2</th>
                    <th
                        class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3 text-center rounded-r-lg">
                        Justificativa</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

</div>

</div>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Ajustes do Select2 para casar com o design Tailwind */
    .select2-container .select2-selection--single {
        height: 38px !important;
        border-color: #e2e8f0 !important;
        border-radius: 0.5rem !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #334155 !important;
        font-size: 0.875rem !important;
        line-height: normal !important;
        padding-left: 0.75rem !important;
    }
    .select2-dropdown {
        border-color: #e2e8f0 !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1) !important;
    }
</style>

<!-- DataTables Buttons for Export -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let tableRel;
    window._relData = {};

    function esc(s) {
        if (s == null) return '';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }

    function mostrarMotivo(texto) {
        if (!texto) return;
        const motivo = texto.includes('[INDEFERIDO PELO CRH]:') ? texto.split('[INDEFERIDO PELO CRH]:')[1] : texto;
        Swal.fire({
            title: 'Justificativa Indeferida',
            text: motivo.trim(),
            icon: 'error',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Entendido'
        });
    }

    $(document).ready(function () {
        // Set default dates (first day and last day of current month)
        const date = new Date();
        const firstDay = new Date(date.getFullYear(), date.getMonth(), 1).toISOString().split('T')[0];
        const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0).toISOString().split('T')[0];

        $('#start_date').val(firstDay);
        $('#end_date').val(lastDay);

        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.remove('hidden');
            overlay.style.opacity = '1';
        }

        carregarListaFuncionarios();
        initTable();
    });

    async function carregarListaFuncionarios() {
        try {
            const res = await fetch('../../api/funcionarios.php');
            const data = await res.json();
            if (data.success) {
                const select = document.getElementById('func_id');
                data.data.forEach(f => {
                    const opt = document.createElement('option');
                    opt.value = f.id;
                    opt.textContent = `${f.matricula} - ${f.nome}`;
                    select.appendChild(opt);
                });
                
                // Inicializa o Select2
                if ($.fn.select2) {
                    $('#func_id').select2({
                        placeholder: "Todos os Funcionários",
                        allowClear: true,
                        width: '100%'
                    });
                }
            }
        } catch (e) { console.error("Erro ao carregar lista de funcionários"); }
    }

    function initTable() {
        const start = $('#start_date').val();
        const end = $('#end_date').val();
        const funcId = $('#func_id').val();

        if ($.fn.DataTable.isDataTable('#tabelaRelatorios')) {
            $('#tabelaRelatorios').DataTable().destroy();
        }

        tableRel = $('#tabelaRelatorios').DataTable({
            ajax: {
                url: `../../api/relatorios.php?start_date=${start}&end_date=${end}&func_id=${funcId}`,
                dataSrc: function (json) {
                    window._relData = {};
                    if(json && json.data) {
                        json.data.forEach(r => window._relData[r.id] = r);
                    }
                    return json.data || [];
                }
            },
            columns: [
                { 
                    data: 'data', 
                    className: 'text-sm text-slate-600',
                    render: (data) => data ? data.split('-').reverse().join('/') : '-'
                },
                { data: 'matricula', className: 'font-mono text-sm text-slate-500' },
                { data: 'nome', className: 'font-bold text-slate-800' },
                {
                    data: 'primeiro_ponto', className: 'text-center',
                    render: (data, type, row) => formatPonto(data, row.atrasou_primeiro_ponto, row.falta_turno1_entrada, row.just_ent1, row.status_crh, row.tipo_justificativa, row.justificativa, row, row.primeiro_horario, 1)
                },
                {
                    data: 'segundo_ponto', className: 'text-center',
                    render: (data, type, row) => formatPonto(data, row.atrasou_segundo_ponto, row.falta_turno1_saida, row.just_sai1, row.status_crh, row.tipo_justificativa, row.justificativa, row, row.segundo_horario, 2)
                },
                {
                    data: 'terceiro_ponto', className: 'text-center',
                    render: (data, type, row) => formatPonto(data, row.atrasou_terceiro_ponto, row.falta_turno2_entrada, row.just_ent2, row.status_crh, row.tipo_justificativa, row.justificativa, row, row.terceiro_horario, 3)
                },
                {
                    data: 'quarto_ponto', className: 'text-center',
                    render: (data, type, row) => formatPonto(data, row.atrasou_quarto_ponto, row.falta_turno2_saida, row.just_sai2, row.status_crh, row.tipo_justificativa, row.justificativa, row, row.quarto_horario, 4)
                },
                {
                    data: 'justificativa', className: 'text-center max-w-[200px] truncate',
                    render: (data, type, row) => {
                        let rawJusts = [];
                        if (row.tipo_justificativa && row.tipo_justificativa !== 'null') rawJusts.push(row.tipo_justificativa.trim());
                        if (row.just_ent1 && row.just_ent1 !== 'null') rawJusts.push(row.just_ent1.trim());
                        if (row.just_sai1 && row.just_sai1 !== 'null') rawJusts.push(row.just_sai1.trim());
                        if (row.just_ent2 && row.just_ent2 !== 'null') rawJusts.push(row.just_ent2.trim());
                        if (row.just_sai2 && row.just_sai2 !== 'null') rawJusts.push(row.just_sai2.trim());
                        if (data && data !== 'null') rawJusts.push(data.trim());

                        // Remove duplicados e vazios
                        let uniqueJusts = [...new Set(rawJusts.filter(j => j !== ''))];

                        if (uniqueJusts.length === 0) return '<span class="text-slate-300">-</span>';

                        let combined = uniqueJusts.join(' | ');
                        return `<div class="flex items-center justify-between gap-2">
                                    <span title="${esc(combined)}" class="text-xs text-slate-800 font-medium px-2 py-1 bg-blue-50 text-blue-700 rounded border border-blue-200 inline-block truncate flex-1">${esc(combined)}</span>
                                    <button onclick="verDetalhesJustificativa(${row.id})" class="flex-shrink-0 p-1.5 text-brand-600 hover:bg-brand-100 rounded-lg transition-colors cursor-pointer" title="Ver Detalhes do Tratamento">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                </div>`;
                    }
                }
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' },
            dom: '<"flex flex-col md:flex-row justify-between items-center mb-4 gap-4"lBf>rt<"flex flex-col md:flex-row justify-between items-center mt-4"ip>',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<svg class="w-4 h-4 inline-block -mt-1 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg> Gerar PDF',
                    className: 'px-3 py-1.5 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors border-0',
                    title: '',
                    filename: 'Relatorio_Ponto_' + start + '_a_' + end,
                    orientation: 'landscape',
                    pageSize: 'A4',
                    customize: function (doc) {
                        doc.defaultStyle.fontSize = 8;
                        doc.styles.tableHeader.fontSize = 9;
                        
                        // Ajustando Margens: [Esquerda, Topo, Direita, Baixo]
                        doc.pageMargins = [40, 80, 40, 70];

                        var bgFullBase64 = "data:image/png;base64,<?php echo base64_encode(file_get_contents('../../public/img/timbre_bg.png')); ?>";
                        var logoBgBase64 = "data:image/png;base64,<?php echo base64_encode(file_get_contents('../../public/img/timbre_brasao.png')); ?>";
                        var logoGovpbBase64 = "data:image/png;base64,<?php echo base64_encode(file_get_contents('../../public/img/timbre_header.png')); ?>";
                        var logoFunadBase64 = "data:image/jpeg;base64,<?php echo base64_encode(file_get_contents('../../public/img/timbre_funad.jpeg')); ?>";

                        // Marca D'Água + Background das bordas originais geométricas sem distorção
                        doc.background = function() {
                            return [
                                {
                                    image: bgFullBase64,
                                    width: 842,
                                    height: 595,
                                    absolutePosition: { x: 0, y: 0 }
                                },
                                {
                                    image: logoBgBase64,
                                    width: 450,
                                    absolutePosition: { x: (842 / 2) - 225, y: (595 / 2) - 225 },
                                    opacity: 0.15
                                }
                            ];
                        };

                        // Cabeçalho Timbrado com os logos unidos na esquerda
                        doc.header = {
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
                                    text: 'RELATÓRIO DE PONTO ELETRÔNICO\nPeríodo: ' + start.split('-').reverse().join('/') + ' a ' + end.split('-').reverse().join('/') + '\nEmitido em: ' + new Date().toLocaleString('pt-BR'),
                                    alignment: 'right',
                                    fontSize: 9,
                                    bold: true,
                                    color: '#000000',
                                    margin: [0, 15, 0, 0]
                                }
                            ]
                        };

                        // Criação do Rodapé completo fiel ao documento oficial
                        doc.footer = function(currentPage, pageCount) {
                            return {
                                margin: [40, 0, 40, 20],
                                columns: [
                                    { text: 'Pág ' + currentPage.toString() + ' / ' + pageCount, alignment: 'left', fontSize: 7, color: '#000000', width: 60, margin: [0, 25, 0, 0] },
                                    {
                                        text: 'SECRETARIA DE ESTADO DA EDUCAÇÃO\nFUNAD – FUNDAÇÃO CENTRO INTEGRADO DE APOIO À PESSOA COM DEFICIÊNCIA\nCER IV – CENTRO ESPECIALIZADO EM REABILITAÇÃO\nRua Dr. Orestes Lisboa, S/N - Pedro Gondim - CEP 58031-090 - João Pessoa/PB\nCNPJ: 24.507.065/0001-07 Email: funad@funad.pb.gov.br\nTel.: (83) 3214-7879 / (83) 3244-1542 / (83) 3243-8446 / (83) 3243-3765',
                                        alignment: 'center',
                                        fontSize: 6,
                                        color: '#000000',
                                        width: '*',
                                        bold: true
                                    },
                                    { text: '', width: 60 }
                                ]
                            };
                        };

                        // Remover título automático invisível do DataTables se existir
                        if (doc.content[0] && doc.content[0].text === '') {
                             doc.content.splice(0, 1);
                        }

                        // Forçar a tabela a ocupar 100% da largura da folha, dividindo o espaço de forma proporcional
                        doc.content.forEach(function(item) {
                            if (item.table) {
                                // 8 colunas: Data(8%), Matr(9%), Nome(*), Ent1(9%), Sai1(9%), Ent2(9%), Sai2(9%), Justificativa(22%)
                                item.table.widths = ['8%', '9%', '*', '9%', '9%', '9%', '9%', '22%'];
                                
                                item.layout = {
                                    hLineWidth: function(i, node) { return 0.5; },
                                    vLineWidth: function(i, node) { return 0.5; },
                                    hLineColor: function(i, node) { return '#cbd5e1'; },
                                    vLineColor: function(i, node) { return '#cbd5e1'; },
                                    paddingLeft: function(i, node) { return 4; },
                                    paddingRight: function(i, node) { return 4; },
                                    paddingTop: function(i, node) { return 3; },
                                    paddingBottom: function(i, node) { return 3; }
                                };
                            }
                        });
                    }
                }
            ],
            pageLength: 25,
            responsive: true,
            order: [[0, 'desc'], [3, 'asc']],
            initComplete: function () {
                const overlay = document.getElementById('loadingOverlay');
                if (overlay) {
                    overlay.style.opacity = '0';
                    setTimeout(() => { 
                        overlay.classList.add('hidden');
                        overlay.style.opacity = '1'; // Reset for next time
                    }, 500);
                }
            }
        });

        // Tailwind styling for filter/length
        $('.dataTables_filter input').addClass('border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-brand-500 ml-2 shadow-sm text-sm');
        $('.dt-buttons button').removeClass('dt-button');
    }

    function carregarRelatorio() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.remove('hidden');
            overlay.style.opacity = '1';
        }
        initTable();
    }

    // Helper: Formata a célula do ponto (destaca em vermelho se for atraso ou falta)
    function formatPonto(hora, isAtraso, isFalta, justIndividual, statusCrh, tipoJustificativa, justificativaGlobal, row, horarioProgramado, pIndex) {
        const isFaltaAuto = !hora || hora === 'FALTA' || hora === 'falta';

        // --- Lógica de Ponto Liberado (Feriado, Facultativo, Liberação Antecipada) ---
        if (row.liberacao && isFaltaAuto) {
            const desc = String(row.liberacao.descricao || '').toUpperCase();
            const isFeriado = desc.includes('FERIADO');
            const isFacultativo = desc.includes('FACULTATIVO');
            
            if (isFeriado || isFacultativo) {
                if (horarioProgramado) {
                    const badgeClass = isFeriado ? 'bg-rose-500' : 'bg-violet-500';
                    return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black ${badgeClass} text-white uppercase tracking-tighter" title="${esc(row.liberacao.descricao)}">${esc(row.liberacao.descricao)}</span>`;
                }
            } else if (row.liberacao.data_hora && horarioProgramado) {
                const libTime = row.liberacao.data_hora.split(' ')[1].substring(0, 5);
                const progTime = horarioProgramado.substring(0, 5);
                if (libTime <= progTime && (pIndex === 2 || pIndex === 4)) {
                    return `<div class="flex flex-col items-center leading-none">
                                <span class="text-[8px] font-black text-brand-600 uppercase tracking-tighter mb-0.5">Liberado</span>
                                <span class="text-[9px] font-bold bg-brand-50 text-brand-700 px-1.5 py-0.5 rounded border border-brand-200" title="${esc(row.liberacao.descricao)}">${esc(row.liberacao.descricao)}</span>
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
        const hasAnyIndiv = (row.just_ent1 && String(row.just_ent1).trim() !== '' && row.just_ent1 !== 'null') || 
                            (row.just_sai1 && String(row.just_sai1).trim() !== '' && row.just_sai1 !== 'null') || 
                            (row.just_ent2 && String(row.just_ent2).trim() !== '' && row.just_ent2 !== 'null') || 
                            (row.just_sai2 && String(row.just_sai2).trim() !== '' && row.just_sai2 !== 'null');

        const hasAnyJust = hasJust || (hasGlobalJust && !hasAnyIndiv && (isAtraso && isAtraso !== 'false' || isFaltaAuto || isFalta));
        
        const time = isFaltaAuto ? 'Falta' : String(hora).substring(0, 5);
        const valJust = justificativaGlobal ? justificativaGlobal.replace(/'/g, "\\'").replace(/"/g, "&quot;").replace(/\n/g, " ") : '';

        // 1. DEFERIDO (Verde)
        if (isDeferido && hasAnyJust) {
            return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Justificativa aprovada pelo RH">✔ Justificado</span>`;
        }

        // 2. INDEFERIDO (Vermelho com Motivo)
        if (isIndeferido && hasAnyJust) {
            const label = isFaltaAuto ? `⛔ Falta` : `⛔ Falta (${time})`;
            return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-red-50 text-red-700 border border-red-300 cursor-pointer hover:bg-red-100 transition-colors" 
                          onclick="mostrarMotivo('${valJust}')"
                          title="Clique para ver o motivo do indeferimento">${label}</span>`;
        }

        // 3. JUSTIFICADO PENDENTE (Azul)
        if (hasAnyJust) {
            return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 cursor-help" title="Justificativa enviada ao RH">✔ Justificado</span>`;
        }

        // 4. ATRASO (Amarelo/Amber) - NOVO COMPORTAMENTO
        if (isAtraso && isAtraso !== 'false' && isAtraso !== false && !isFaltaAuto) {
            return `<div class="flex flex-col items-center gap-0.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 cursor-help" title="Atraso fora da tolerância">${time}</span>
                        <span class="text-[8px] font-black text-amber-600 uppercase tracking-tighter">Atraso</span>
                    </div>`;
        }

        // 5. FALTA AUTOMÁTICA (Vermelho Padrão)
        if (isFaltaAuto) {
            return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-red-50 text-red-700 border border-red-300 cursor-pointer hover:bg-red-100 transition-colors" onclick="Swal.fire('Aviso', 'Ponto não registrado e sem justificativa até o momento.', 'info')">⛔ Falta</span>`;
        }
        if (isFalta) {
            return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-red-50 text-red-600 border border-red-200 cursor-pointer hover:bg-red-100" onclick="Swal.fire('Aviso', 'Este ponto deveria ter sido registrado, mas faturou falta.', 'warning')">Falta</span>`;
        }

        // 6. NORMAL OK
        return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700">${time}</span>`;
    }

    function verDetalhesJustificativa(id) {
        const row = window._relData[id];
        if (!row) return;

        const val = v => (v && v !== 'null') ? esc(v.trim()) : '<span class="text-slate-400 italic font-mono">Não informado</span>';

        let html = `
            <div class="text-left space-y-4 mt-2">
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 flex flex-col gap-1 shadow-sm">
                    <span class="text-[10px] font-bold text-brand-500 uppercase tracking-wider">Tipo de Justificativa</span>
                    <span class="text-sm font-semibold text-slate-800">${val(row.tipo_justificativa)}</span>
                </div>
                
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="p-3 bg-white border border-slate-200 rounded-lg shadow-sm">
                        <span class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Turno 1 - Entrada</span>
                        <div class="font-medium text-slate-700 text-xs">${val(row.just_ent1)}</div>
                    </div>
                    <div class="p-3 bg-white border border-slate-200 rounded-lg shadow-sm">
                        <span class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Turno 1 - Saída</span>
                        <div class="font-medium text-slate-700 text-xs">${val(row.just_sai1)}</div>
                    </div>
                    <div class="p-3 bg-white border border-slate-200 rounded-lg shadow-sm">
                        <span class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Turno 2 - Entrada</span>
                        <div class="font-medium text-slate-700 text-xs">${val(row.just_ent2)}</div>
                    </div>
                    <div class="p-3 bg-white border border-slate-200 rounded-lg shadow-sm">
                        <span class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Turno 2 - Saída</span>
                        <div class="font-medium text-slate-700 text-xs">${val(row.just_sai2)}</div>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg shadow-sm">
                    <span class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Descrição Global / Acordo</span>
                    <div class="text-xs font-semibold text-slate-700 whitespace-pre-wrap">${val(row.justificativa)}</div>
                </div>
        `;

        if (row.anexo_justificativa && row.anexo_justificativa !== 'null') {
            html += `
                <div class="mt-4 p-4 border border-emerald-200 bg-emerald-50/50 rounded-xl flex flex-col gap-3 shadow-sm">
                    <div class="flex items-center gap-3 text-emerald-700">
                        <div class="p-2 bg-emerald-100 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold">Documentos Comprobatórios</span>
                            <span class="text-xs text-emerald-600 font-medium">Anexos enviados com a justificativa</span>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        ${(() => {
                            let paths = [];
                            try {
                                const parsed = JSON.parse(row.anexo_justificativa);
                                paths = Array.isArray(parsed) ? parsed : [row.anexo_justificativa];
                            } catch(e) {
                                paths = [row.anexo_justificativa];
                            }
                            return paths.map(p => `
                                <a href="../../${esc(p)}" target="_blank" class="px-3 py-1.5 bg-emerald-600 text-white text-[10px] font-bold rounded-lg hover:bg-emerald-500 transition-colors shadow-sm uppercase tracking-wider flex items-center gap-2">
                                    ${p.split('/').pop().length > 15 ? 'Ver Documento' : p.split('/').pop()}
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            `).join('');
                        })()}
                    </div>
                </div>
            `;
        }

        html += `</div>`;

        Swal.fire({
            title: '<div class="text-lg font-bold text-slate-800 pt-2">Detalhes da Justificativa</div>',
            html: html,
            showCloseButton: true,
            showConfirmButton: false,
            width: '600px',
            customClass: {
                popup: 'rounded-2xl shadow-2xl border border-slate-100',
                closeButton: 'text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors'
            }
        });
    }
</script>

<?php include 'layout/footer.php'; ?>