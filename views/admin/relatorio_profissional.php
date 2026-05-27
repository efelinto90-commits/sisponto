<?php include 'layout/header.php'; 

// Proteção da Página: Apenas administrador, corsin e crh
$user_name = $_SESSION['user_name'] ?? '';
$user_setor = $_SESSION['user_setor'] ?? '';
$user_level = $_SESSION['user_level'] ?? 3;
$is_authorized = ($user_level == 1) || in_array(strtolower(trim($user_name)), ['corsin', 'crh']) || in_array(strtolower(trim($user_setor)), ['corsin', 'crh']);

if (!$is_authorized) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Acesso Negado',
            text: 'Você não tem permissão para acessar esta página.',
            confirmButtonColor: '#0ea5e9'
        }).then(() => {
            window.location.href = 'index.php';
        });
    </script>";
    include 'layout/footer.php';
    exit;
}
?>

<!-- SheetJS para Exportação Excel -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Ajustes do Select2 para casar com o design Tailwind */
    .select2-container .select2-selection--single {
        height: 42px !important;
        border-color: #cbd5e1 !important;
        border-radius: 0.75rem !important;
        display: flex !important;
        align-items: center !important;
        background-color: #f8fafc !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #334155 !important;
        font-size: 0.875rem !important;
        line-height: normal !important;
        padding-left: 1rem !important;
        font-weight: 500 !important;
    }
    .select2-dropdown {
        border-color: #cbd5e1 !important;
        border-radius: 0.75rem !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden !important;
    }
    .select2-results__option {
        padding: 8px 12px !important;
        font-size: 0.875rem !important;
    }
</style>

<div class="mx-6 flex flex-col h-full bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <!-- Topbar Interna e Filtros -->
    <div class="px-6 py-5 border-b border-slate-100 flex flex-col gap-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Relatório Profissional
                </h1>
                <p class="text-sm text-slate-500 mt-1">Consulte e exporte os dados cadastrais completos dos profissionais ativos.</p>
            </div>
            
            <button onclick="exportarExcel()" 
                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-sm font-bold shadow-sm transition-all hover:shadow-md inline-flex items-center justify-center gap-2 cursor-pointer duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Exportar Excel
            </button>
        </div>

        <!-- Barra de Filtros -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 p-5 bg-slate-50/70 border border-slate-100 rounded-2xl">
            <!-- Filtro Nome -->
            <div class="flex flex-col gap-1.5">
                <label for="filtro_nome" class="text-xs font-bold text-slate-600 uppercase tracking-wider">Nome</label>
                <div class="relative">
                    <input type="text" id="filtro_nome" 
                        class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 shadow-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all font-medium" 
                        placeholder="Buscar por nome...">
                    <div class="absolute left-3.5 top-2.5 text-slate-400">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Filtro CPF -->
            <div class="flex flex-col gap-1.5">
                <label for="filtro_cpf" class="text-xs font-bold text-slate-600 uppercase tracking-wider">CPF</label>
                <div class="relative">
                    <input type="text" id="filtro_cpf" maxlength="14"
                        class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 shadow-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all font-mono font-medium" 
                        placeholder="000.000.000-00">
                    <div class="absolute left-3.5 top-2.5 text-slate-400">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.333 0 4 .667 4 2v1H5v-1c0-1.333 2.667-2 4-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Filtro Setor -->
            <div class="flex flex-col gap-1.5">
                <label for="filtro_setor" class="text-xs font-bold text-slate-600 uppercase tracking-wider">Setor</label>
                <select id="filtro_setor" class="w-full">
                    <option value="">Todos os Setores</option>
                </select>
            </div>

            <!-- Filtro Status -->
            <div class="flex flex-col gap-1.5">
                <label for="filtro_status" class="text-xs font-bold text-slate-600 uppercase tracking-wider">Status</label>
                <select id="filtro_status" 
                    class="w-full h-[42px] px-3.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 shadow-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all font-semibold cursor-pointer">
                    <option value="ativos" selected>Ativos</option>
                    <option value="exonerados">Exonerados</option>
                    <option value="todos">Todos</option>
                </select>
            </div>

            <!-- Botões de Ação -->
            <div class="flex items-end gap-2.5">
                <button onclick="carregarRelatorio()" 
                    class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-bold shadow-sm transition-all hover:shadow-md inline-flex items-center justify-center gap-2 cursor-pointer duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filtrar
                </button>
                <button onclick="limparFiltros()" 
                    class="py-2.5 px-3.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-500 rounded-xl text-sm font-semibold shadow-sm transition-all cursor-pointer duration-200" title="Limpar Filtros">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Tabela -->
    <div class="p-6 flex-1 overflow-auto relative">
        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="absolute inset-0 z-10 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center transition-opacity duration-500">
            <div class="flex flex-col items-center gap-4">
                <div class="relative">
                    <div class="w-12 h-12 rounded-full border-4 border-slate-200 border-t-brand-500 animate-spin"></div>
                </div>
                <p class="text-sm font-semibold text-slate-500 animate-pulse">Carregando relatório profissional...</p>
            </div>
            <!-- Skeleton Rows -->
            <div class="w-full max-w-4xl mt-8 space-y-3 px-4">
                <div class="h-10 bg-slate-100 rounded-lg animate-pulse"></div>
                <div class="h-10 bg-slate-50 rounded-lg animate-pulse delay-75"></div>
                <div class="h-10 bg-slate-100 rounded-lg animate-pulse delay-150"></div>
                <div class="h-10 bg-slate-50 rounded-lg animate-pulse delay-200"></div>
            </div>
        </div>

        <table id="tabelaRelatorioProfissional" class="w-full text-left border-collapse display" style="width:100%">
            <thead>
                <tr class="bg-slate-50">
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3 rounded-l-lg">Nome</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3 text-center">Função / Cargo</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3 text-center">Contratação</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3 text-center">E-mail</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3 text-center">Telefone</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Endereço</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3 text-center">Admissão</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3 text-center rounded-r-lg">Exoneração</th>
                </tr>
            </thead>
            <tbody>
                <!-- Preenchido dinamicamente -->
            </tbody>
        </table>
    </div>

</div>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let tableRel;

    // Máscara para CPF
    document.getElementById('filtro_cpf').addEventListener('input', function (e) {
        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,3})(\d{0,2})/);
        e.target.value = !x[2] ? x[1] : x[1] + '.' + x[2] + (x[3] ? '.' + x[3] : '') + (x[4] ? '-' + x[4] : '');
    });

    $(document).ready(function () {
        carregarListaSetores();
        initTable();
    });

    async function carregarListaSetores() {
        try {
            const res = await fetch('../../api/funcionarios.php?action=get_setores');
            const data = await res.json();
            if (data.success) {
                const select = document.getElementById('filtro_setor');
                data.data.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s;
                    opt.textContent = s;
                    select.appendChild(opt);
                });
                
                if ($.fn.select2) {
                    $('#filtro_setor').select2({
                        placeholder: "Todos os Setores",
                        allowClear: true,
                        width: '100%'
                    });
                }
            }
        } catch (e) { 
            console.error("Erro ao carregar lista de setores:", e); 
        }
    }

    function initTable() {
        const nome = $('#filtro_nome').val();
        const cpf = $('#filtro_cpf').val();
        const setor = $('#filtro_setor').val();
        const status = $('#filtro_status').val() || 'ativos';

        if ($.fn.DataTable.isDataTable('#tabelaRelatorioProfissional')) {
            $('#tabelaRelatorioProfissional').DataTable().destroy();
        }

        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.remove('hidden');
            overlay.style.opacity = '1';
        }

        tableRel = $('#tabelaRelatorioProfissional').DataTable({
            ajax: {
                url: `../../api/funcionarios.php?action=relatorio_profissional&filtro_nome=${encodeURIComponent(nome)}&filtro_cpf=${encodeURIComponent(cpf)}&filtro_setor=${encodeURIComponent(setor || '')}&filtro_status=${encodeURIComponent(status)}`,
                dataSrc: function (json) {
                    return json.data || [];
                }
            },
            columns: [
                { 
                    data: 'nome', 
                    className: 'font-bold text-slate-800 px-4 py-3.5 whitespace-normal min-w-[180px]',
                    render: function(data, type, row) {
                        let subText = '';
                        if (row.setor) {
                            subText = row.setor;
                            if (row.setor2) subText += ' / ' + row.setor2;
                        }
                        
                        let badge = '';
                        if (row.is_exonerado == 1 || row.is_exonerado === true) {
                            badge = `<span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-rose-50 text-rose-600 border border-rose-100 tracking-wide uppercase">Exonerado</span>`;
                        } else {
                            badge = `<span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 tracking-wide uppercase">Ativo</span>`;
                        }

                        return `<div>
                            <div class="flex items-center flex-wrap gap-1">
                                <span class="text-slate-800 font-bold">${data}</span>
                                ${badge}
                            </div>
                            ${subText ? `<div class="text-[10px] text-slate-400 font-semibold tracking-wide uppercase mt-0.5">${subText}</div>` : ''}
                        </div>`;
                    }
                },
                { 
                    data: 'cargo_funcao', 
                    className: 'text-sm text-slate-600 whitespace-normal min-w-[120px] text-center',
                    render: function(data) {
                        return data ? data : '<span class="text-slate-400 italic">Não informado</span>';
                    }
                },
                { 
                    data: 'tipo_contratacao', 
                    className: 'text-sm text-slate-600 whitespace-normal min-w-[110px] text-center',
                    render: function(data) {
                        return data ? data : '<span class="text-slate-400 italic">Não informado</span>';
                    }
                },
                { 
                    data: 'endereco_email', 
                    className: 'text-xs text-slate-600 lowercase break-all whitespace-normal max-w-[180px] text-center',
                    render: function(data) {
                        return data ? data : '<span class="text-slate-400 italic">Não informado</span>';
                    }
                },
                { 
                    data: null, 
                    className: 'text-sm text-slate-600 whitespace-nowrap text-center',
                    render: function(data, type, row) {
                        let fones = [];
                        if (row.celular) {
                            fones.push(`<span class="font-semibold text-slate-700">Cel:</span> ${row.celular}`);
                        }
                        if (row.telefone_fixo) {
                            fones.push(`<span class="font-semibold text-slate-700">Fixo:</span> ${row.telefone_fixo}`);
                        }
                        return fones.length > 0 ? fones.join('<br>') : '<span class="text-slate-400 italic">Não informado</span>';
                    }
                },
                { 
                    data: null, 
                    className: 'text-xs text-slate-600 max-w-[260px] break-words whitespace-normal',
                    render: function(data, type, row) {
                        let parts = [];
                        if (row.endereco) {
                            let r = row.endereco;
                            if (row.endereco_numero) r += ', ' + row.endereco_numero;
                            if (row.endereco_complemento) r += ' (' + row.endereco_complemento + ')';
                            parts.push(r);
                        }
                        
                        let locationParts = [];
                        if (row.endereco_bairro) locationParts.push(row.endereco_bairro);
                        if (row.endereco_municipio) {
                            let mun = row.endereco_municipio;
                            if (row.endereco_uf) mun += '/' + row.endereco_uf;
                            locationParts.push(mun);
                        }
                        if (row.endereco_cep) locationParts.push('CEP: ' + row.endereco_cep);
                        
                        if (locationParts.length > 0) {
                            parts.push(locationParts.join(' - '));
                        }

                        return parts.length > 0 ? parts.join('<br>') : '<span class="text-slate-400 italic">Não informado</span>';
                    }
                },
                { 
                    data: 'data_admissao', 
                    className: 'text-sm text-slate-600 font-medium whitespace-nowrap text-center',
                    render: function(data) {
                        return data ? data.split('-').reverse().join('/') : '<span class="text-slate-400 italic">-</span>';
                    }
                },
                { 
                    data: 'data_exoneracao', 
                    className: 'text-sm text-slate-600 font-medium whitespace-nowrap text-center',
                    render: function(data, type, row) {
                        if (row.is_exonerado == 1 || row.is_exonerado === true) {
                            return data ? data.split('-').reverse().join('/') : '<span class="text-rose-500 font-semibold italic">Sim (s/ data)</span>';
                        }
                        return '<span class="text-slate-400 italic">-</span>';
                    }
                }
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' },
            dom: '<"flex flex-col md:flex-row justify-between items-center mb-4 gap-4"lf>rt<"flex flex-col md:flex-row justify-between items-center mt-4"ip>',
            pageLength: 10,
            responsive: true,
            initComplete: function () {
                if (overlay) {
                    overlay.style.opacity = '0';
                    setTimeout(() => { 
                        overlay.classList.add('hidden');
                        overlay.style.opacity = '1';
                    }, 500);
                }
            }
        });

        // Estilização dos inputs do DT
        $('.dataTables_filter input').addClass('border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 ml-2 shadow-sm text-sm');
        $('.dataTables_length select').addClass('border border-slate-200 rounded-lg px-2 py-1.5 focus:outline-none shadow-sm mx-1 text-sm');
    }

    function carregarRelatorio() {
        initTable();
    }

    function limparFiltros() {
        $('#filtro_nome').val('');
        $('#filtro_cpf').val('');
        $('#filtro_status').val('ativos');
        if ($.fn.select2) {
            $('#filtro_setor').val('').trigger('change');
        } else {
            $('#filtro_setor').val('');
        }
        initTable();
    }

    function exportarExcel() {
        const rows = tableRel.rows({ search: 'applied' }).data().toArray();
        if (rows.length === 0) {
            Swal.fire('Aviso', 'Não há dados na tabela para exportar.', 'warning');
            return;
        }

        const dataExcel = rows.map(r => {
            let fones = [];
            if (r.celular) fones.push(r.celular);
            if (r.telefone_fixo) fones.push(r.telefone_fixo);
            let fonesStr = fones.join(' / ');

            let endParts = [];
            if (r.endereco) endParts.push(r.endereco);
            if (r.endereco_numero) endParts.push(r.endereco_numero);
            if (r.endereco_complemento) endParts.push(`(${r.endereco_complemento})`);
            let endBase = endParts.join(', ');
            
            let locParts = [];
            if (r.endereco_bairro) locParts.push(r.endereco_bairro);
            if (r.endereco_municipio) {
                let munUf = r.endereco_municipio;
                if (r.endereco_uf) munUf += '/' + r.endereco_uf;
                locParts.push(munUf);
            }
            if (r.endereco_cep) locParts.push(`CEP: ${r.endereco_cep}`);
            let locBase = locParts.join(' - ');
            
            let enderecoCompleto = [endBase, locBase].filter(Boolean).join(' - ');
            let statusStr = (r.is_exonerado == 1 || r.is_exonerado === true) ? 'Exonerado' : 'Ativo';
            let admissaoStr = r.data_admissao ? r.data_admissao.split('-').reverse().join('/') : 'Não informado';

            let cpfStr = '-';
            if (r.cpf) {
                let c = r.cpf.replace(/\D/g, '');
                if (c.length === 11) {
                    cpfStr = c.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
                } else {
                    cpfStr = r.cpf;
                }
            }

            let setorStr = r.setor || '';
            if (r.setor2) {
                setorStr += (setorStr ? ' / ' : '') + r.setor2;
            }

            let exoneracaoStr = '-';
            if (r.is_exonerado == 1 || r.is_exonerado === true) {
                exoneracaoStr = r.data_exoneracao ? r.data_exoneracao.split('-').reverse().join('/') : 'Sim (s/ data)';
            }

            return {
                'Nome': r.nome || '',
                'Status': statusStr,
                'CPF': cpfStr,
                'Setor': setorStr,
                'Função / Cargo': r.cargo_funcao || 'Não informado',
                'Tipo de Contratação': r.tipo_contratacao || 'Não informado',
                'E-mail': r.endereco_email || 'Não informado',
                'Telefone': fonesStr || 'Não informado',
                'Endereço': enderecoCompleto || 'Não informado',
                'Data de Admissão': admissaoStr,
                'Data de Exoneração': exoneracaoStr
            };
        });

        const worksheet = XLSX.utils.json_to_sheet(dataExcel);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Profissionais');

        // Largura automática de colunas
        const maxLens = {};
        dataExcel.forEach(row => {
            Object.keys(row).forEach(key => {
                const val = String(row[key]);
                maxLens[key] = Math.max(maxLens[key] || 10, val.length);
            });
        });
        worksheet['!cols'] = Object.keys(maxLens).map(key => ({
            wch: Math.min(maxLens[key] + 3, 50)
        }));

        XLSX.writeFile(workbook, 'Relatorio_Profissionais_' + new Date().toISOString().split('T')[0] + '.xlsx');
    }
</script>

<?php include 'layout/footer.php'; ?>
