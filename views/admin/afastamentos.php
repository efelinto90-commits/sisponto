<?php include 'layout/header.php'; ?>

<div class="max-w-7xl mx-auto flex flex-col h-full bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Histórico de Afastamentos</h1>
            <p class="text-sm text-slate-500 mt-1">Consulte férias, licenças e outros períodos de ausência.</p>
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
                    class="border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 shadow-sm focus:ring-1 focus:ring-amber-500 outline-none">
                <span class="text-slate-400">até</span>
                <input type="date" id="end_date"
                    class="border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 shadow-sm focus:ring-1 focus:ring-amber-500 outline-none">
            </div>
            <button onclick="carregarAfastamentos()"
                class="px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-semibold hover:bg-amber-500 transition-colors shadow-sm inline-flex items-center gap-2">
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
                <div class="w-12 h-12 rounded-full border-4 border-slate-200 border-t-amber-500 animate-spin"></div>
                <p class="text-sm font-semibold text-slate-500">Buscando afastamentos...</p>
            </div>
        </div>

        <table id="tabelaAfastamentos" class="w-full text-left border-collapse display nowrap" style="width:100%">
            <thead>
                <tr class="bg-slate-50">
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3 rounded-l-lg">Funcionário</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Matrícula</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Tipo</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Data Início</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Data Fim</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Total Dias</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3 rounded-r-lg">Anexo</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

</div>

<!-- Select2 & DataTables Depedencies -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single { height: 38px !important; border-color: #e2e8f0 !important; border-radius: 0.5rem !important; display: flex !important; align-items: center !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { color: #334155 !important; font-size: 0.875rem !important; padding-left: 0.75rem !important; }
</style>

<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let tableAfant;

    $(document).ready(function () {
        // Datas padrão: mês atual
        const date = new Date();
        const firstDay = new Date(date.getFullYear(), date.getMonth(), 1).toISOString().split('T')[0];
        const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0).toISOString().split('T')[0];
        $('#start_date').val(firstDay);
        $('#end_date').val(lastDay);

        carregarListaFuncionarios();
        initTable();
    });

    async function carregarListaFuncionarios() {
        try {
            const res = await fetch('../../api/funcionarios.php');
            const data = await res.json();
            if (data.success) {
                const select = $('#func_id');
                data.data.forEach(f => {
                    select.append(new Option(`${f.matricula} - ${f.nome}`, f.id));
                });
                select.select2({ placeholder: "Todos os Funcionários", allowClear: true, width: '100%' });
            }
        } catch (e) { console.error("Erro ao carregar funcionários"); }
    }

    function initTable() {
        const start = $('#start_date').val();
        const end = $('#end_date').val();
        const funcId = $('#func_id').val();

        // Mostrar Loading
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.remove('hidden');
            overlay.style.opacity = '1';
        }

        if ($.fn.DataTable.isDataTable('#tabelaAfastamentos')) {
            $('#tabelaAfastamentos').DataTable().destroy();
        }

        tableAfant = $('#tabelaAfastamentos').DataTable({
            ajax: {
                url: `../../api/ferias.php?start_date=${start}&end_date=${end}&func_id=${funcId}`,
                dataSrc: 'data'
            },
            columns: [
                { data: 'nome_funcionario', className: 'font-bold text-slate-800' },
                { data: 'matricula', className: 'font-mono text-sm text-slate-500' },
                { 
                    data: 'tipo_afastamento', 
                    render: (data, type, row) => {
                        let txt = data === 'outros' ? (row.motivo_especifico || 'Outros') : data;
                        return `<span class="px-2 py-1 rounded-md bg-amber-50 text-amber-700 text-xs font-bold border border-amber-100 uppercase tracking-tighter">${txt}</span>`;
                    }
                },
                { 
                    data: 'data_inicio', 
                    render: data => data ? data.split('-').reverse().join('/') : '-'
                },
                { 
                    data: 'data_fim', 
                    render: data => data ? data.split('-').reverse().join('/') : '-'
                },
                {
                    data: null,
                    render: (data, type, row) => {
                        if (row.data_inicio && row.data_fim) {
                            const d1 = new Date(row.data_inicio);
                            const d2 = new Date(row.data_fim);
                            const diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
                            return `${diff} dias`;
                        }
                        return '-';
                    }
                },
                {
                    data: 'anexo',
                    className: 'text-center',
                    render: data => data ? 
                        `<a href="../../${data}" target="_blank" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg inline-block transition-colors border border-transparent hover:border-emerald-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                        </a>` : '<span class="text-slate-300">-</span>'
                }
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' },
            dom: '<"flex justify-between items-center mb-4"lBf>rtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: 'Gerar PDF',
                    className: 'px-3 py-1.5 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors border-0',
                    title: '',
                    filename: 'Relatorio_Afastamentos',
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
                                    text: 'RELATÓRIO DE AFASTAMENTOS E FÉRIAS\nPeríodo: ' + $('#start_date').val().split('-').reverse().join('/') + ' a ' + $('#end_date').val().split('-').reverse().join('/') + '\nEmitido em: ' + new Date().toLocaleString('pt-BR'),
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

                        if (doc.content[0] && doc.content[0].text === '') {
                             doc.content.splice(0, 1);
                        }

                        // Forçar a tabela a ocupar 100% do espaço (alinhando com a margem direita)
                        doc.content.forEach(function(item) {
                            if (item.table) {
                                // Distribuição Proporcional (Nome fica com o restante '*')
                                // Func(*) Matr(12%) Tipo(18%) DataIni(11%) DataFim(11%) Dias(10%) Anexo(8%)
                                item.table.widths = ['*', '12%', '18%', '11%', '11%', '10%', '8%'];
                                
                                // Otimizando o padding das células para ficar mais espaçado
                                item.layout = {
                                    hLineWidth: function(i, node) { return 0.5; },
                                    vLineWidth: function(i, node) { return 0.5; },
                                    hLineColor: function(i, node) { return '#cbd5e1'; },
                                    vLineColor: function(i, node) { return '#cbd5e1'; },
                                    paddingLeft: function(i, node) { return 6; },
                                    paddingRight: function(i, node) { return 6; },
                                    paddingTop: function(i, node) { return 4; },
                                    paddingBottom: function(i, node) { return 4; }
                                };
                            }
                        });
                    }
                }
            ],
            pageLength: 25,
            responsive: true,
            order: [[3, 'desc']],
            initComplete: function () {
                // Esconder Loading
                if (overlay) {
                    overlay.style.opacity = '0';
                    setTimeout(() => { 
                        overlay.classList.add('hidden');
                        overlay.style.opacity = '1';
                    }, 500);
                }
            }
        });

        // Styling search input
        $('.dataTables_filter input').addClass('border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-amber-500 ml-2 shadow-sm text-sm');
    }

    function carregarAfastamentos() {
        initTable();
    }
</script>

<?php include 'layout/footer.php'; ?>
