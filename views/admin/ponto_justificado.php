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

    <!-- Card Informativo: Fluxo de Deferimento -->
    <div id="avisoCardPonto" class="border-b border-amber-200/70 transition-all duration-300">
        <!-- Cabeçalho clicável -->
        <button onclick="toggleAvisoPonto()" class="w-full flex items-center justify-between px-6 py-3 bg-amber-50 hover:bg-amber-100/70 transition-colors duration-200 group cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-full bg-amber-400 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-left">
                    <p class="text-[10px] font-black text-amber-800 uppercase tracking-wider leading-none">Aviso Importante</p>
                    <p class="text-[9px] text-amber-600 font-semibold mt-0.5">Em vigor a partir de 26/05/2026</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-[10px] text-amber-600 font-semibold hidden sm:block" id="avisoPontoLabel">Clique para recolher</span>
                <svg id="avisoPontoChevron" class="w-4 h-4 text-amber-600 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                </svg>
                <span onclick="fecharAvisoPonto(event)" title="Fechar aviso"
                    class="w-5 h-5 rounded-full bg-amber-200 hover:bg-red-200 hover:text-red-600 text-amber-700 flex items-center justify-center transition-all duration-200 cursor-pointer">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </span>
            </div>
        </button>

        <!-- Corpo colapsável -->
        <div id="avisoPontoCorpo" class="overflow-hidden transition-all duration-300 ease-in-out bg-amber-50" style="max-height: 200px; opacity: 1;">
            <div class="px-6 pb-3">
                <div class="flex items-start gap-4 flex-wrap md:flex-nowrap">

                    <!-- Divisor vertical -->
                    <div class="hidden md:block w-px bg-amber-300/60 self-stretch flex-shrink-0"></div>

                    <!-- Texto descritivo -->
                    <div class="text-xs text-amber-900 leading-relaxed font-medium flex-1 min-w-[200px] max-w-lg space-y-1" style="hyphens: auto; word-break: break-word;">
                        <p class="text-justify">Ficou definido que os gestores serão responsáveis por <strong>deferir</strong> ou <strong>indeferir</strong> diretamente os registros de faltas ou atrasos dos funcionários.</p>
                        <p class="text-justify text-amber-800">Ao <strong>deferir</strong> a solicitação, o ponto será abonado automaticamente e encaminhado ao CRH. Em caso de <strong>indeferimento</strong>, será exibido um campo para preenchimento do motivo; após a confirmação, o registro também será encaminhado ao CRH.</p>
                    </div>

                    <!-- Divisor vertical -->
                    <div class="hidden md:block w-px bg-amber-300/60 self-stretch flex-shrink-0"></div>

                    <!-- Badges DEFERIDO / INDEFERIDO -->
                    <div class="flex items-stretch gap-4 flex-shrink-0 flex-wrap">
                        <!-- Deferido -->
                        <div class="flex items-center gap-4 bg-green-100 border-2 border-green-300/80 rounded-2xl px-5 py-4 hover:bg-green-200/60 transition-colors duration-150 shadow-sm min-w-[160px]">
                            <div class="flex-shrink-0 w-9 h-9 rounded-full bg-green-500 flex items-center justify-center shadow-md">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-black text-green-800 uppercase tracking-wide">Deferido</p>
                                <p class="text-xs text-green-700 leading-snug mt-1">Ponto <strong>abonado</strong> e<br>encaminhado ao CRH.</p>
                            </div>
                        </div>
                        <!-- Indeferido -->
                        <div class="flex items-center gap-4 bg-red-100 border-2 border-red-300/80 rounded-2xl px-5 py-4 hover:bg-red-200/60 transition-colors duration-150 shadow-sm min-w-[160px]">
                            <div class="flex-shrink-0 w-9 h-9 rounded-full bg-red-500 flex items-center justify-center shadow-md">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-black text-red-800 uppercase tracking-wide">Indeferido</p>
                                <p class="text-xs text-red-700 leading-snug mt-1">Informa <strong>motivo</strong> e<br>encaminha ao CRH.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
    (function(){
        if (sessionStorage.getItem('avisoFechadoPonto') === '1') {
            document.getElementById('avisoCardPonto').style.display = 'none';
        }
        if (sessionStorage.getItem('avisoColapsadoPonto') === '1') {
            const corpo = document.getElementById('avisoPontoCorpo');
            const chevron = document.getElementById('avisoPontoChevron');
            const label = document.getElementById('avisoPontoLabel');
            if (corpo) { corpo.style.maxHeight = '0'; corpo.style.opacity = '0'; }
            if (chevron) chevron.style.transform = 'rotate(-90deg)';
            if (label) label.textContent = 'Clique para expandir';
        }
    })();

    function toggleAvisoPonto() {
        const corpo = document.getElementById('avisoPontoCorpo');
        const chevron = document.getElementById('avisoPontoChevron');
        const label = document.getElementById('avisoPontoLabel');
        const colapsado = corpo.style.maxHeight === '0px' || corpo.style.maxHeight === '0';
        if (colapsado) {
            corpo.style.maxHeight = '200px';
            corpo.style.opacity = '1';
            chevron.style.transform = 'rotate(0deg)';
            if (label) label.textContent = 'Clique para recolher';
            sessionStorage.removeItem('avisoColapsadoPonto');
        } else {
            corpo.style.maxHeight = '0';
            corpo.style.opacity = '0';
            chevron.style.transform = 'rotate(-90deg)';
            if (label) label.textContent = 'Clique para expandir';
            sessionStorage.setItem('avisoColapsadoPonto', '1');
        }
    }

    function fecharAvisoPonto(e) {
        e.stopPropagation();
        const card = document.getElementById('avisoCardPonto');
        card.style.opacity = '0';
        card.style.maxHeight = '0';
        setTimeout(() => card.style.display = 'none', 300);
        sessionStorage.setItem('avisoFechadoPonto', '1');
    }
    </script>

    <!-- Stats Bar -->
    <div id="statsBar" class="px-6 py-4 bg-slate-50 border-b border-slate-100 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 transition-all duration-300">
        <!-- Card 1: Pendentes na Base -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/60 shadow-sm flex items-center gap-3 hover:shadow-md hover:scale-[1.01] transition-all duration-300">
            <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center font-bold text-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pendentes Base</div>
                <div class="text-xl font-extrabold text-slate-800 font-mono mt-0.5" id="statPendentesBase">0</div>
            </div>
        </div>

        <!-- Card 2: Recebidos CRH -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/60 shadow-sm flex items-center gap-3 hover:shadow-md hover:scale-[1.01] transition-all duration-300">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Enviados CRH</div>
                <div class="text-xl font-extrabold text-slate-800 font-mono mt-0.5" id="statRecebidosCrh">0</div>
            </div>
        </div>

        <!-- Card 3: Ações Necessárias -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/60 shadow-sm flex items-center gap-3 hover:shadow-md hover:scale-[1.01] transition-all duration-300">
            <div class="w-10 h-10 rounded-lg bg-red-50 text-red-600 flex items-center justify-center font-bold text-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sem Tratamento</div>
                <div class="text-xl font-extrabold text-slate-800 font-mono mt-0.5" id="statAcoesNecessarias">0</div>
            </div>
        </div>

        <!-- Card 4: Justificados Servidor -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/60 shadow-sm flex items-center gap-3 hover:shadow-md hover:scale-[1.01] transition-all duration-300">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Justificativas PWA</div>
                <div class="text-xl font-extrabold text-slate-800 font-mono mt-0.5" id="statJustificados">0</div>
            </div>
        </div>
    </div>

    <div class="p-6 flex-1 overflow-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-xs sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-3 rounded-l-lg border-b border-slate-100">Data</th>
                    <th class="px-4 py-3 border-b border-slate-100">Colaborador</th>
                    <th class="px-4 py-3 text-center border-b border-slate-100">Batidas / Falhas</th>
                    <th class="px-4 py-3 border-b border-slate-100 max-w-[280px] w-[280px]">Status Geral</th>
                    <th class="px-4 py-3 text-center border-b border-slate-100">Situação CRH</th>
                    <th class="px-4 py-3 text-right rounded-r-lg border-b border-slate-100">Tratar Acesso</th>
                </tr>
            </thead>
            <tbody id="pontoBody" class="divide-y divide-slate-100">
                <tr>
                    <td colspan="6" class="text-center py-20 text-slate-400">Carregando dados...</td>
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

    /* Premium UI & Micro-animations */
    .hover-row-card {
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .hover-row-card:hover {
        background-color: rgb(248 250 252 / 0.85) !important;
        transform: translateY(-0.5px) scale(1.0005);
        box-shadow: 0 6px 16px -4px rgb(0 0 0 / 0.04), 0 4px 6px -2px rgb(0 0 0 / 0.02) !important;
    }
    
    /* Premium custom SweetAlert popup style */
    .premium-swal {
        border-radius: 1.5rem !important;
        padding: 2.25rem !important;
        border: 1px solid rgba(226, 232, 240, 0.9) !important;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.15) !important;
        font-family: inherit !important;
    }
    .premium-title {
        font-size: 1.35rem !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        letter-spacing: -0.025em !important;
        border-bottom: 1px solid #f1f5f9 !important;
        padding-bottom: 1rem !important;
        margin-bottom: 1.25rem !important;
    }
    .premium-confirm {
        background-color: #0ea5e9 !important;
        border-radius: 0.75rem !important;
        font-size: 0.8125rem !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        padding: 0.625rem 1.5rem !important;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.25) !important;
        transition: all 0.2s !important;
    }
    .premium-confirm:hover {
        background-color: #0284c7 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 16px rgba(14, 165, 233, 0.35) !important;
    }
    .premium-cancel {
        background-color: #64748b !important;
        border-radius: 0.75rem !important;
        font-size: 0.8125rem !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        padding: 0.625rem 1.5rem !important;
        transition: all 0.2s !important;
    }
    .premium-cancel:hover {
        background-color: #475569 !important;
        transform: translateY(-1px) !important;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Remove barra de rolagem horizontal dos textareas do SweetAlert2 */
    .swal2-textarea {
        resize: none !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
        box-sizing: border-box !important;
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



    async function loadPontoData() {
        console.log("Iniciando loadPontoData...");
        const tbody = document.getElementById('pontoBody');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-20 text-slate-500"><svg class="animate-spin h-8 w-8 mx-auto mb-3 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Buscando registros...</td></tr>';

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
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-20 text-red-500">${esc(json.message)}</td></tr>`;
                return;
            }

            if (!json.data || json.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-20 text-slate-400">Nenhum ponto encontrado para estes filtros.</td></tr>';
                return;
            }

            window._pontoData = {};
            tbody.innerHTML = '';

            let countPendentesBase = 0;
            let countRecebidosCrh = 0;
            let countAcoesNecessarias = 0;
            let countJustificados = 0;

            json.data.forEach(r => {
                window._pontoData[r.id] = r;

                const tr = document.createElement('tr');
                tr.className = 'hover-row-card border-b border-slate-100/80 transition-all duration-300';

                const isEnviado = r.enviado_crh && r.enviado_crh !== 'false' && r.enviado_crh !== '0' && r.enviado_crh !== 'f' && r.enviado_crh !== false;
                const isFinalizado = (isEnviado || r.status_crh === 'deferido' || r.status_crh === 'indeferido') && !IS_SUPER;

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
                
                const hasFuncJust = r.justificativas_funcionario && Object.keys(r.justificativas_funcionario).some(k => {
                    const j = r.justificativas_funcionario[k];
                    return j && j.texto && String(j.texto).trim() !== '' && j.texto !== 'null';
                });
                
                               const hasJust = hasFuncJust ||
                                (r.tipo_justificativa && r.tipo_justificativa !== 'null' && String(r.tipo_justificativa).trim() !== '') || 
                                (r.justificativa && r.justificativa !== 'null' && String(r.justificativa).trim() !== '') || 
                                (r.just_ent1 && r.just_ent1 !== 'null' && String(r.just_ent1).trim() !== '') ||
                                (r.just_sai1 && r.just_sai1 !== 'null' && String(r.just_sai1).trim() !== '') ||
                                (r.just_ent2 && r.just_ent2 !== 'null' && String(r.just_ent2).trim() !== '') ||
                                (r.just_sai2 && r.just_sai2 !== 'null' && String(r.just_sai2).trim() !== '') ||
                                (r.anexo_justificativa && r.anexo_justificativa !== 'null' && r.anexo_justificativa !== '[]' && r.anexo_justificativa !== '') ||
                                (r.anexo_comunicado && r.anexo_comunicado !== 'null' && r.anexo_comunicado !== '[]' && r.anexo_comunicado !== '') ||
                                (r.comunicado && r.comunicado !== 'null' && String(r.comunicado).trim() !== '');

                const hasGestorJust = 
                                (r.tipo_justificativa && r.tipo_justificativa !== 'null' && String(r.tipo_justificativa).trim() !== '') || 
                                (r.justificativa && r.justificativa !== 'null' && String(r.justificativa).trim() !== '') || 
                                (r.just_ent1 && r.just_ent1 !== 'null' && String(r.just_ent1).trim() !== '') ||
                                (r.just_sai1 && r.just_sai1 !== 'null' && String(r.just_sai1).trim() !== '') ||
                                (r.just_ent2 && r.just_ent2 !== 'null' && String(r.just_ent2).trim() !== '') ||
                                (r.just_sai2 && r.just_sai2 !== 'null' && String(r.just_sai2).trim() !== '') ||
                                (r.anexo_justificativa && r.anexo_justificativa !== 'null' && r.anexo_justificativa !== '[]' && r.anexo_justificativa !== '');

                const isTreated = isEnviado || r.status_crh === 'deferido' || r.status_crh === 'indeferido';
                const needsAction = hasError && !hasGestorJust && !isTreated && !r.em_ferias;

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
                    const isAtrasoBool = (isAtraso === true || isAtraso === 1 || isAtraso === 'true' || isAtraso === 't' || isAtraso === '1');
                    const isIndeferido = statusCrh === 'indeferido';
                    const isDeferido = statusCrh === 'deferido';
                    const fieldNameMap = { 'E1': 'primeiro_ponto', 'S1': 'segundo_ponto', 'E2': 'terceiro_ponto', 'S2': 'quarto_ponto' };
                    const fieldName = fieldNameMap[label];
                    const justFunc = r.justificativas_funcionario ? r.justificativas_funcionario[fieldName] : null;
                    const hasJust = (justIndividual && justIndividual !== 'null' && String(justIndividual).trim() !== '') ||
                                    (justFunc != null && justFunc.texto && String(justFunc.texto).trim() !== '' && justFunc.texto !== 'null');
                    const hasGlobalJust = tipoJustificativa && tipoJustificativa !== 'null' && String(tipoJustificativa).trim() !== '';
                    
                    // Se houver qualquer justificativa individual, a global não deve "auto-justificar" as outras batidas sozinhas
                    const hasAnyIndiv = (r.just_ent1 && String(r.just_ent1).trim() !== '' && r.just_ent1 !== 'null') || 
                                        (r.just_sai1 && String(r.just_sai1).trim() !== '' && r.just_sai1 !== 'null') || 
                                        (r.just_ent2 && String(r.just_ent2).trim() !== '' && r.just_ent2 !== 'null') || 
                                        (r.just_sai2 && String(r.just_sai2).trim() !== '' && r.just_sai2 !== 'null');

                    const hasAnyJust = hasJust || (hasGlobalJust && !hasAnyIndiv && (isAtrasoBool || isFaltaAuto));
                    
                    const h = isFaltaAuto ? 'Falta' : String(hora).substring(0, 5);
                    const valJust = justificativaGlobal ? justificativaGlobal.replace(/'/g, "\\'").replace(/"/g, "&quot;").replace(/\n/g, " ") : '';

                    const isJustFuncDeferido = justFunc && justFunc.status === 'deferido';
                    const isJustFuncIndeferido = justFunc && justFunc.status === 'indeferido';

                    const isIndividualDeferido = justIndividual === 'Deferido';
                    const isIndividualIndeferido = justIndividual === 'Indeferido';

                    const isDeferidoFinal = (isIndividualDeferido || (isDeferido && hasAnyJust) || isJustFuncDeferido) && !isIndividualIndeferido;
                    const isIndeferidoFinal = (isIndividualIndeferido || (isIndeferido && hasAnyJust) || isJustFuncIndeferido) && !isIndividualDeferido;

                    // 1. DEFERIDO (Verde)
                    if (isDeferidoFinal && hasAnyJust) {
                        const gestorInfo = r.gestor_setor ? `Gestor do Setor - ${r.gestor_setor}` : (r.gestor_nome ? `Gestor - ${r.gestor_nome}` : 'Gestor do Setor');
                        return `<span onclick="decidirStatusJustificativa('${r.id}', '${label}')" class="text-[10px] bg-emerald-100 text-emerald-800 font-bold rounded px-1.5 py-0.5 border border-emerald-300 mr-1 cursor-pointer hover:opacity-80 transition-all" title="Justificativa aprovada pelo ${gestorInfo}. Clique para alterar.">${label}: ${h} (Justificado)</span>`;
                    }

                    // 2. INDEFERIDO (Vermelho com Motivo)
                    if (isIndeferidoFinal && hasAnyJust) {
                        return `<span onclick="decidirStatusJustificativa('${r.id}', '${label}')" class="text-[10px] font-bold bg-red-100 text-red-800 px-1.5 py-0.5 rounded border border-red-300 mr-1 cursor-pointer hover:bg-red-200 transition-colors" title="Justificativa Recusada. Clique para alterar.">${label}: ${h} (Recusado)</span>`;
                    }

                    // 3. JUSTIFICADO PENDENTE (Azul)
                    if (hasAnyJust) {
                        return `<span onclick="decidirStatusJustificativa('${r.id}', '${label}')" class="text-[10px] bg-blue-50 text-blue-700 font-bold rounded px-1.5 py-0.5 border border-blue-200 mr-1 cursor-pointer hover:bg-blue-100 transition-colors" title="Pendente de Aprovação. Clique para decidir.">${label}: ${h} (Justificado)</span>`;
                    }

                    // 4. ATRASO OU FALTA AUTOMÁTICA (Vermelho Padrão)
                    if (isAtrasoBool && !isFaltaAuto) {
                        return `<span onclick="decidirStatusJustificativa('${r.id}', '${label}')" class="text-[10px] bg-red-50 text-red-600 font-bold rounded px-1.5 py-0.5 border border-red-100 mr-1 cursor-pointer hover:bg-red-100 transition-colors" title="Atraso sem justificativa. Clique para decidir.">${label}: ${h}</span>`;
                    }
                    if (isFaltaAuto) {
                        return `<span onclick="decidirStatusJustificativa('${r.id}', '${label}')" class="text-[10px] bg-red-50 text-red-700 font-bold rounded px-1.5 py-0.5 border border-red-300 mr-1 cursor-pointer hover:bg-red-100 transition-colors" title="Falta automática. Clique para decidir.">${label}: Falta</span>`;
                    }

                    // 5. NORMAL OK (Verde)
                    return `<span onclick="decidirStatusJustificativa('${r.id}', '${label}')" class="text-[10px] bg-emerald-50 text-emerald-600 font-bold rounded px-1.5 py-0.5 border border-emerald-50 mr-1 cursor-pointer hover:opacity-80 transition-all" title="Clique para decidir.">${label}: ${h}</span>`;
                };
                
                let resHtml = '';
                resHtml += formatResumo(r.primeiro_ponto, !!r.primeiro_horario, 'E1', r.atrasou_primeiro_ponto, r.just_ent1, r.status_crh, r.tipo_justificativa, r.justificativa);
                resHtml += formatResumo(r.segundo_ponto, !!r.segundo_horario, 'S1', r.atrasou_segundo_ponto, r.just_sai1, r.status_crh, r.tipo_justificativa, r.justificativa);
                resHtml += formatResumo(r.terceiro_ponto, !!r.terceiro_horario, 'E2', r.atrasou_terceiro_ponto, r.just_ent2, r.status_crh, r.tipo_justificativa, r.justificativa);
                resHtml += formatResumo(r.quarto_ponto, !!r.quarto_horario, 'S2', r.atrasou_quarto_ponto, r.just_sai2, r.status_crh, r.tipo_justificativa, r.justificativa);
                

                const tdBat = document.createElement('td');
                tdBat.className = 'px-4 py-4 text-center border-b border-slate-100';
                tdBat.innerHTML = resHtml || '<span class="text-slate-300">-</span>';
                tr.appendChild(tdBat);

                // Status Geral e Justificativa
                const tdSt = document.createElement('td');
                tdSt.className = 'px-4 py-4 border-b border-slate-100 max-w-[280px] w-[280px]';
                let justifyContent = '<span class="text-[11px] font-bold text-slate-500 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-lg">Sem Justificativa</span>';
                
                if (hasJust) {
                    const badgeText = r.tipo_justificativa ? esc(r.tipo_justificativa) : (r.comunicado ? 'Aviso do Funcionário' : (hasFuncJust ? 'Justificado pelo Colaborador' : 'Justificado'));
                    const descText = r.justificativa ? esc(r.justificativa) : '';
                    
                    let attachmentIcons = '';
                    let allAnexos = [];
                    if (r.anexo_justificativa) {
                        try {
                            const paths = JSON.parse(r.anexo_justificativa);
                            if (Array.isArray(paths)) allAnexos = allAnexos.concat(paths);
                            else allAnexos.push(r.anexo_justificativa);
                        } catch(e) {
                            allAnexos.push(r.anexo_justificativa);
                        }
                    }
                    if (r.anexo_comunicado) {
                        try {
                            const paths = JSON.parse(r.anexo_comunicado);
                            if (Array.isArray(paths)) {
                                paths.forEach(p => {
                                    if (!allAnexos.includes(p)) allAnexos.push(p);
                                });
                            } else {
                                if (!allAnexos.includes(r.anexo_comunicado)) allAnexos.push(r.anexo_comunicado);
                            }
                        } catch(e) {
                            if (!allAnexos.includes(r.anexo_comunicado)) allAnexos.push(r.anexo_comunicado);
                        }
                    }
                    if (r.justificativas_funcionario) {
                        Object.keys(r.justificativas_funcionario).forEach(c => {
                            const j = r.justificativas_funcionario[c];
                            if (j.anexos) {
                                try {
                                    const paths = JSON.parse(j.anexos);
                                    if (Array.isArray(paths)) {
                                        paths.forEach(p => {
                                            if (!allAnexos.includes(p)) allAnexos.push(p);
                                        });
                                    }
                                } catch(e) {}
                            }
                        });
                    }
                    
                    if (allAnexos.length > 0) {
                        attachmentIcons = allAnexos.map(p => `
                            <a href="../../${esc(p)}" target="_blank" class="inline-block p-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition-colors" title="Ver Comprovante">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            </a>
                        `).join(' ');
                    }
                    
                    justifyContent = `
                        <div class="flex flex-wrap items-center gap-1">
                            <span class="text-[11px] font-bold bg-blue-50 text-blue-700 px-2 py-0.5 rounded-lg border border-blue-200" title="${esc(r.justificativa || '')}">${badgeText}</span>
                            ${attachmentIcons}
                        </div>
                    `;
                }
                
                // Mostrar Justificativas do Funcionário (funadponto)
                if (r.justificativas_funcionario && Object.keys(r.justificativas_funcionario).length > 0) {
                    let funcJustHtml = '';
                    Object.keys(r.justificativas_funcionario).forEach(campo => {
                        const j = r.justificativas_funcionario[campo];
                        const labels = {
                            'primeiro_ponto': 'Entrada 1',
                            'segundo_ponto': 'Saída 1',
                            'terceiro_ponto': 'Entrada 2',
                            'quarto_ponto': 'Saída 2'
                        };
                        const labelCampo = labels[campo] || campo;
                        
                        let pathListHtml = '';
                        if (j.anexos) {
                            try {
                                const paths = JSON.parse(j.anexos);
                                if (Array.isArray(paths)) {
                                    paths.forEach((p, idx) => {
                                        pathListHtml += `
                                            <a href="../../${esc(p)}" target="_blank" class="flex items-center gap-1 mt-1 text-orange-700 font-bold hover:underline hover:text-orange-950 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                Ver Comprovante ${paths.length > 1 ? idx + 1 : ''}
                                            </a>
                                        `;
                                    });
                                }
                            } catch(e) {}
                        }
                        
                        funcJustHtml += `
                            <div class="mt-2 p-2.5 bg-orange-50 border border-orange-200 rounded-lg text-[10px] leading-relaxed text-orange-950 shadow-sm relative group" title="${esc(j.texto)}">
                                <span class="font-black uppercase text-[9px] text-orange-700 block mb-1 tracking-wider">Justificativa do Servidor (${labelCampo} - ${esc(j.tipo_justificativa)}):</span>
                                <div class="font-medium line-clamp-2 overflow-hidden">"${esc(j.texto)}"</div>
                                ${pathListHtml}
                            </div>
                        `;
                    });
                    
                    if (justifyContent.includes('Sem Justificativa')) {
                        justifyContent = funcJustHtml;
                    } else {
                        justifyContent += funcJustHtml;
                    }
                }
                
                // Mostrar Comunicado (Aviso do Funcionário)
                if (r.comunicado) {
                    justifyContent += `
                        <div class="mt-2 p-2 bg-amber-50 border border-amber-100 rounded text-[10px] leading-tight text-amber-800 italic relative group" title="${esc(r.comunicado)}">
                            <span class="font-bold uppercase text-[9px] block mb-1 opacity-70 not-italic">Aviso do Funcionário:</span>
                            <span class="line-clamp-2 block">"${esc(r.comunicado)}"</span>
                            ${(() => {
                                const anexoCom = r.anexo_comunicado;
                                if (!anexoCom || anexoCom === 'null' || anexoCom === '[]' || anexoCom === '') return '';
                                
                                let paths = [];
                                try {
                                    const parsed = JSON.parse(anexoCom);
                                    paths = Array.isArray(parsed) ? parsed : [anexoCom];
                                } catch (e) {
                                    paths = [anexoCom];
                                }
                                
                                paths = paths.filter(p => p && String(p).trim() !== '' && String(p) !== 'null');
                                if (paths.length === 0) return '';

                                return paths.map((p, i) => `
                                    <a href="../../${esc(p)}" target="_blank" class="flex items-center gap-1 mt-1 text-emerald-700 font-bold hover:underline">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                        Anexo ${paths.length > 1 ? i+1 : ''}
                                    </a>
                                `).join('');
                            })()}
                        </div>
                    `;
                }
                if (r.aviso) {
                    justifyContent += `<div class="mt-2 text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-200 rounded px-2 py-1 flex items-center gap-1 line-clamp-2" title="${esc(r.aviso)}"><svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg><span class="line-clamp-2"> Aviso: ${esc(r.aviso)}</span></div>`;
                }

                tdSt.innerHTML = justifyContent;
                tr.appendChild(tdSt);

                // Situacao CRH
                const tdCrh = document.createElement('td');
                tdCrh.className = 'px-4 py-4 text-center border-b border-slate-100';
                if (r.status_crh === 'indeferido') {
                    let m = 'Motivo não informado';
                    if (r.justificativa && String(r.justificativa).includes('[INDEFERIDO PELO CRH]:')) {
                        m = String(r.justificativa).split('[INDEFERIDO PELO CRH]:')[1].trim();
                    } else if (r.crh_history) {
                        try {
                            const hist = JSON.parse(r.crh_history);
                            const lastIndef = hist.reverse().find(h => h.action === 'indeferido');
                            if (lastIndef && lastIndef.motivo) m = lastIndef.motivo;
                        } catch(e) {}
                    }
                    tdCrh.innerHTML = `<span class="text-[11px] font-bold text-red-700 bg-red-100 border border-red-300 px-3 py-1 rounded-full uppercase tracking-wider flex items-center gap-1 inline-flex"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Indeferido</span>
                                       <div class="mt-2 text-[10px] text-red-600 max-w-[180px] mx-auto text-left leading-tight bg-red-50 p-1.5 rounded border border-red-100 cursor-help transition-all hover:bg-red-100" onclick="mostrarMotivo('${esc(m).replace(/['"\\]/g, '\\$&').replace(/\n/g, '\\n').replace(/\r/g, '')}')" title="Clique para detalhes"><strong>Motivo:</strong> ${esc(m)}</div>`;
                } else if (r.status_crh === 'deferido') {
                    tdCrh.innerHTML = `<span class="text-[11px] font-bold text-emerald-700 bg-emerald-100 border border-emerald-400 px-3 py-1 rounded-full uppercase tracking-wider flex items-center gap-1 inline-flex"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Deferido</span>`;
                } else if (isEnviado) {
                    tdCrh.innerHTML = `<span class="text-[11px] font-bold text-blue-700 bg-blue-100 border border-blue-300 px-3 py-1 rounded-full uppercase tracking-wider flex items-center gap-1 inline-flex"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg> Recebido CRH</span>`;
                } else {
                    tdCrh.innerHTML = `<span class="text-[11px] font-bold text-orange-700 bg-orange-100 border border-orange-300 px-3 py-1 rounded-full uppercase tracking-wider flex items-center gap-1 inline-flex"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Pendente Base</span>`;
                }
                tr.appendChild(tdCrh);

                // Ação Tabela
                const tdAc = document.createElement('td'); 
                tdAc.className = 'px-4 py-4 text-right border-b border-slate-100';
                
                const btnClass = isFinalizado 
                    ? "p-2 text-slate-400 bg-slate-50 cursor-not-allowed opacity-50 font-bold text-xs uppercase tracking-wider rounded-lg border border-slate-200 flex items-center inline-flex gap-1 ml-auto"
                    : "p-2 text-brand-600 hover:bg-brand-100 font-bold text-xs uppercase tracking-wider rounded-lg border border-transparent hover:border-brand-200 transition-all flex items-center inline-flex gap-1 ml-auto";
                
                const btnOnClick = isFinalizado ? "" : `onclick="abrirTratamentoPonto('${r.id}')"`;

                const btnTratarHtml = IS_SUPER ? `
                    <button ${btnOnClick} class="${btnClass}" ${isFinalizado ? 'disabled title="Registro finalizado pelo CRH - Não pode ser alterado"' : ''}>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Tratar
                    </button>
                ` : '';

                tdAc.innerHTML = `
                    <div class="flex flex-col items-end gap-1.5">
                        ${btnTratarHtml}
                        <button onclick="abrirAfastamento('${r.id}')" class="p-2 text-amber-600 hover:bg-amber-100 font-bold text-[10px] uppercase tracking-wider rounded-lg border border-transparent hover:border-amber-200 transition-all flex items-center inline-flex gap-1" ${isFinalizado ? 'disabled' : ''}>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zM10 13l4 4m0-4l-4 4" /></svg>
                            Afastar
                        </button>
                    </div>`;
                tr.appendChild(tdAc);

                if (isEnviado || r.status_crh === 'deferido' || r.status_crh === 'indeferido') {
                    countRecebidosCrh++;
                } else {
                    countPendentesBase++;
                }
                if (needsAction) {
                    countAcoesNecessarias++;
                }
                if (hasFuncJust) {
                    countJustificados++;
                }

                tbody.appendChild(tr);
            });

            document.getElementById('statPendentesBase').innerText = countPendentesBase;
            document.getElementById('statRecebidosCrh').innerText = countRecebidosCrh;
            document.getElementById('statAcoesNecessarias').innerText = countAcoesNecessarias;
            document.getElementById('statJustificados').innerText = countJustificados;

        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-20 text-red-500 font-semibold">Erro ao carregar dados.</td></tr>`;
            console.error(err);
        }
    }

    async function abrirTratamentoPonto(id) {
        try {
            console.log('abrirTratamentoPonto chamado para id:', id);
            const r = (window._pontoData || {})[id];
            console.log('Dados do registro:', r);
            if (!r) {
                console.error('Registro não encontrado no _pontoData para o id:', id);
                Swal.fire('Erro', 'Registro não encontrado localmente.', 'error');
                return;
            }

            let anexosExistentes = [];
            if (r.anexo_justificativa) {
                try {
                    const parse = JSON.parse(r.anexo_justificativa);
                    anexosExistentes = Array.isArray(parse) ? parse : [r.anexo_justificativa];
                } catch(e) {
                    anexosExistentes = [r.anexo_justificativa];
                }
            }
            
            if (r.justificativas_funcionario) {
                Object.keys(r.justificativas_funcionario).forEach(c => {
                    const j = r.justificativas_funcionario[c];
                    if (j.anexos) {
                        try {
                            const paths = JSON.parse(j.anexos);
                            if (Array.isArray(paths)) {
                                paths.forEach(p => {
                                    if (!anexosExistentes.includes(p)) {
                                        anexosExistentes.push(p);
                                    }
                                });
                            }
                        } catch(e) {}
                    }
                });
            }

            // Filtrar caminhos válidos não nulos ou vazios
            anexosExistentes = anexosExistentes.filter(p => p && String(p).trim() !== '' && String(p) !== 'null' && String(p) !== 'undefined');

            const removedPaths = new Set();

            // Garantir que comPaths seja um array seguro
            let comPaths = [];
            if (r.anexo_comunicado) {
                try {
                    const parsedCom = JSON.parse(r.anexo_comunicado);
                    comPaths = Array.isArray(parsedCom) ? parsedCom : [r.anexo_comunicado];
                } catch(e) {
                    comPaths = [r.anexo_comunicado];
                }
            }
            comPaths = comPaths.filter(p => p && String(p).trim() !== '' && String(p) !== 'null' && String(p) !== 'undefined');

            const { value: formValues } = await Swal.fire({
                title: 'Tratamento de Ponto',
                html: `<div class="text-left space-y-3 mt-2">
                    ${r.comunicado ? `
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs">
                            <label class="text-[10px] font-bold text-amber-700 uppercase block mb-1">Aviso do Funcionário</label>
                            <div class="italic text-amber-900">"${esc(r.comunicado)}"</div>
                            
                            ${(() => {
                                const anexoCom = r.anexo_comunicado;
                                if (!anexoCom || anexoCom === 'null' || anexoCom === '[]' || anexoCom === '') return '';
                                let paths = [];
                                try {
                                    const parsed = JSON.parse(anexoCom);
                                    paths = Array.isArray(parsed) ? parsed : [anexoCom];
                                } catch (e) {
                                    paths = [anexoCom];
                                }
                                paths = paths.filter(p => p && String(p).trim() !== '' && String(p) !== 'null');
                                if (paths.length === 0) return '';
                                
                                return `
                                    <div class="mt-2 space-y-1">
                                        <label class="text-[9px] font-bold text-amber-700 uppercase block mt-2">Anexos do Funcionário (Marcados para manter)</label>
                                        ${paths.map((p, i) => `
                                            <div class="flex items-center justify-between p-1.5 bg-white/50 border border-amber-200 rounded text-[10px]">
                                                <div class="flex items-center gap-2">
                                                    <input type="checkbox" id="sw-usar-comunicado-${i}" data-path="${esc(p)}" class="sw-check-comunicado w-3 h-3 rounded border-amber-300 text-amber-600 focus:ring-amber-500" checked>
                                                    <span class="font-bold text-amber-800">Anexo ${paths.length > 1 ? i+1 : ''}</span>
                                                </div>
                                                <a href="../../${esc(p)}" target="_blank" class="text-[9px] bg-amber-200 hover:bg-amber-300 px-1.5 py-0.5 rounded text-amber-800 font-bold transition-colors">Ver Original</a>
                                            </div>
                                        `).join('')}
                                    </div>
                                `;
                            })()}
                        </div>
                    ` : ''}

                    ${(() => {
                        if (r.justificativas_funcionario && Object.keys(r.justificativas_funcionario).length > 0) {
                            let html = '<div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs space-y-2 shadow-inner">';
                            html += '<label class="text-[10px] font-black text-amber-800 uppercase tracking-widest block border-b border-amber-200/60 pb-1 flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> Justificativa(s) Enviada(s) pelo Servidor</label>';
                            
                            Object.keys(r.justificativas_funcionario).forEach(campo => {
                                const j = r.justificativas_funcionario[campo];
                                const labels = {
                                    'primeiro_ponto': 'Entrada 1',
                                    'segundo_ponto': 'Saída 1',
                                    'terceiro_ponto': 'Entrada 2',
                                    'quarto_ponto': 'Saída 2'
                                };
                                const labelCampo = labels[campo] || campo;
                                
                                let pathsHtml = '';
                                if (j.anexos) {
                                    try {
                                        const paths = JSON.parse(j.anexos);
                                        if (Array.isArray(paths)) {
                                            paths.forEach((p, idx) => {
                                                pathsHtml += `
                                                    <div class="flex items-center justify-between p-1.5 bg-white/50 border border-amber-200 rounded text-[10px] mt-1">
                                                        <div class="flex items-center gap-2">
                                                            <input type="checkbox" id="sw-usar-just-func-${campo}-${idx}" data-path="${esc(p)}" class="sw-check-comunicado w-3 h-3 rounded border-amber-300 text-amber-600 focus:ring-amber-500" checked>
                                                            <span class="font-bold text-amber-800">Comprovante (${labelCampo})</span>
                                                        </div>
                                                        <a href="../../${esc(p)}" target="_blank" class="text-[9px] bg-amber-200 hover:bg-amber-300 px-1.5 py-0.5 rounded text-amber-800 font-bold transition-colors">Ver Original</a>
                                                    </div>
                                                `;
                                            });
                                        }
                                    } catch(e) {}
                                }
                                
                                html += `
                                    <div class="mb-2 pb-2 border-b border-amber-200/50 last:border-b-0 last:pb-0 last:mb-0">
                                        <div class="font-bold text-amber-800">${labelCampo} (${esc(j.tipo_justificativa)}):</div>
                                        <div class="italic text-amber-950">"${esc(j.texto)}"</div>
                                        ${pathsHtml}
                                    </div>
                                `;
                            });
                            
                            html += '</div>';
                            return html;
                        }
                        return '';
                    })()}

                    <div class="mb-4">
                        <label class="text-xs font-bold text-slate-600">Justificativas por Marcação</label>
                        <div class="grid grid-cols-2 gap-2 mt-2 text-xs">
                            ${['jent1', 'jsai1', 'jent2', 'jsai2'].map(id => {
                                const labels = { 'jent1': 'Turno 1 - Entrada', 'jsai1': 'Turno 1 - Saída', 'jent2': 'Turno 2 - Entrada', 'jsai2': 'Turno 2 - Saída' };
                                const dbField = id.replace('j', 'just_');
                                const fieldMap = { 'jent1': 'primeiro_ponto', 'jsai1': 'segundo_ponto', 'jent2': 'terceiro_ponto', 'jsai2': 'quarto_ponto' };
                                const fieldName = fieldMap[id];
                                const justFunc = r.justificativas_funcionario ? r.justificativas_funcionario[fieldName] : null;
                                
                                let val = r[dbField] || '';
                                if (!val && justFunc) {
                                    val = 'Outros';
                                }
                                
                                const stdOpts = ['', 'Saúde', 'Folga eleitoral', 'Ponto facultativo', 'Liberação interna (setor)', 'Liberação institucional (RH)', 'Feriado', 'Audiência'];
                                
                                const isOther = val !== '' && !stdOpts.includes(val);
                                const selectVal = isOther ? 'Outros' : val;

                                let outrosText = isOther ? val : '';
                                if (!r[dbField] && justFunc) {
                                    outrosText = justFunc.tipo_justificativa + ': ' + justFunc.texto;
                                }

                                return `<div>
                                    <label class="text-[10px] font-bold text-slate-500 uppercase">${labels[id]}</label>
                                    <select id="sw-${id}" class="swal2-select w-full mt-1 m-0 h-8 px-2 text-xs bg-slate-50 border border-slate-200 rounded" onchange="window._toggleOutrosMarca('${id}', this.value)">
                                        ${stdOpts.map(o => `<option value="${o}" ${selectVal === o ? 'selected' : ''}>${o || 'Selecione...'}</option>`).join('')}
                                        <option value="Outros" ${selectVal === 'Outros' ? 'selected' : ''}>Outros</option>
                                    </select>
                                    <div id="div-outros-${id}" class="mt-1 ${selectVal === 'Outros' ? '' : 'hidden'}">
                                        <input id="sw-val-outros-${id}" class="swal2-input w-full m-0 h-7 px-2 text-[10px]" placeholder="Descreva o motivo..." value="${esc(outrosText)}">
                                    </div>
                                </div>`;
                            }).join('')}
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="text-xs font-bold text-slate-600 uppercase flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                            Observação Geral
                        </label>
                        <textarea id="sw-justificativa" class="swal2-textarea w-full mt-1 text-sm m-0 p-2 bg-slate-50 border-slate-200" rows="2" placeholder="Descreva observações gerais sobre este tratamento...">${(() => {
                            let defaultJustText = r.justificativa || '';
                            if (!defaultJustText && r.justificativas_funcionario) {
                                const texts = [];
                                Object.keys(r.justificativas_funcionario).forEach(c => {
                                    texts.push(r.justificativas_funcionario[c].texto);
                                });
                                defaultJustText = texts.join(' | ');
                            }
                            return esc(defaultJustText);
                        })()}</textarea>
                    </div>

                    <div class="mt-3 p-3 border border-dashed border-slate-300 rounded-lg bg-slate-50 flex flex-col gap-2">
                        <label class="text-xs font-bold text-slate-600 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            Anexar Documentos Comprobatórios (Máx: 3MB por arquivo)
                        </label>
                        
                        <div id="sw-existentes-list" class="space-y-1">
                            ${anexosExistentes.filter(p => !comPaths.includes(p)).map(p => `
                                <div class="flex items-center justify-between p-1.5 bg-white border border-slate-200 rounded text-[10px] group" id="item-${btoa(encodeURIComponent(p)).replace(/=/g,'')}">
                                    <div class="flex items-center gap-2 truncate pr-4">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <a href="../../${esc(p)}" target="_blank" class="text-blue-600 font-bold truncate hover:underline">${esc(p.split('/').pop())}</a>
                                    </div>
                                    <button type="button" onclick="window._removerAnexoGlobal('${p}', '${btoa(encodeURIComponent(p)).replace(/=/g,'')}')" class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            `).join('')}
                        </div>

                        <div id="sw-novos-list" class="space-y-1 empty:hidden mt-2">
                            <!-- Novos arquivos aparecerão aqui -->
                        </div>

                        <div class="relative mt-2">
                            <input type="file" id="sw-anexo" class="hidden" accept=".pdf,.jpg,.jpeg" multiple onchange="window._handleNovosArquivos(this)">
                            <label for="sw-anexo" class="flex items-center justify-center gap-2 w-full py-2 px-4 bg-white border-2 border-dashed border-slate-300 rounded-lg text-xs font-bold text-slate-600 hover:border-brand-400 hover:text-brand-600 cursor-pointer transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Adicionar Novos Documentos
                            </label>
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
                customClass: {
                    popup: 'premium-swal',
                    title: 'premium-title',
                    confirmButton: 'premium-confirm',
                    cancelButton: 'premium-cancel'
                },
                didOpen: () => {
                    window._pendingFiles = [];

                    window._removerAnexoGlobal = (path, idHash) => {
                        removedPaths.add(path);
                        const el = document.getElementById('item-' + idHash);
                        if (el) el.classList.add('hidden');
                    };
                    window._toggleOutrosMarca = (id, val) => {
                        const div = document.getElementById('div-outros-' + id);
                        if (div) div.classList.toggle('hidden', val !== 'Outros');
                    };

                    window._handleNovosArquivos = (input) => {
                        for (let i = 0; i < input.files.length; i++) {
                            const file = input.files[i];
                            if (file.size > 3 * 1024 * 1024) {
                                Swal.showValidationMessage(`O arquivo "${file.name}" excede 3MB.`);
                                continue;
                            }
                            window._pendingFiles.push(file);
                        }
                        input.value = ''; // Limpa para permitir re-seleção do mesmo
                        window._renderNovosArquivos();
                    };

                    window._renderNovosArquivos = () => {
                        const list = document.getElementById('sw-novos-list');
                        list.innerHTML = window._pendingFiles.map((f, i) => `
                            <div class="flex items-center justify-between p-1.5 bg-brand-50 border border-brand-100 rounded text-[10px] group animate-in fade-in slide-in-from-top-1">
                                <div class="flex items-center gap-2 truncate pr-4 text-brand-700 font-bold">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    <span class="truncate">${esc(f.name)}</span>
                                </div>
                                <button type="button" onclick="window._removeNovosArquivo(${i})" class="text-red-400 hover:text-red-600 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        `).join('');
                    };

                    window._removeNovosArquivo = (idx) => {
                        window._pendingFiles.splice(idx, 1);
                        window._renderNovosArquivos();
                    };
                },
                preConfirm: () => {
                    const checksComunicado = document.querySelectorAll('.sw-check-comunicado');
                    let usarComunicado = false;
                    checksComunicado.forEach(chk => {
                        const path = chk.getAttribute('data-path');
                        if (chk.checked) {
                            usarComunicado = true;
                            if (!anexosExistentes.includes(path)) anexosExistentes.push(path);
                        } else {
                            removedPaths.add(path);
                            anexosExistentes = anexosExistentes.filter(p => p !== path);
                        }
                    });

                    const getJustVal = (id) => {
                        const sel = document.getElementById('sw-' + id).value;
                        if (sel === 'Outros') return document.getElementById('sw-val-outros-' + id).value;
                        return sel;
                    };

                    return {
                        tipo_justificativa: '',
                        justificativa: document.getElementById('sw-justificativa').value,
                        just_ent1: getJustVal('jent1'),
                        just_sai1: getJustVal('jsai1'),
                        just_ent2: getJustVal('jent2'),
                        just_sai2: getJustVal('jsai2'),
                        aviso: document.getElementById('sw-aviso').value,
                        new_files: window._pendingFiles,
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
        } catch (err) {
            console.error("Erro em abrirTratamentoPonto:", err);
            Swal.fire({
                icon: 'error',
                title: 'Erro ao abrir tratamento',
                text: 'Não foi possível carregar a tela de tratamento. Detalhes: ' + err.message,
                confirmButtonText: 'Entendido'
            });
        }
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
            confirmButtonText: 'Sim, Encaminhar',
            cancelButtonText: 'Revisar Mais',
            customClass: {
                popup: 'premium-swal',
                title: 'premium-title',
                confirmButton: 'premium-confirm',
                cancelButton: 'premium-cancel'
            }
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

    async function abrirAfastamento(id) {
        const r = (window._pontoData || {})[id];
        if (!r) return;
        
        // Mostra o comunicado do funcionário primeiro para consulta
        if (r.comunicado) {
            const result = await Swal.fire({
                title: 'Justificativa do Funcionário',
                html: `
                    <div class="text-left p-4 bg-amber-50 rounded-xl border border-amber-200 shadow-inner">
                        <p class="text-sm italic text-amber-900 leading-relaxed">"${esc(r.comunicado)}"</p>
                        ${(() => {
                            const anexoCom = r.anexo_comunicado;
                            if (!anexoCom || anexoCom === 'null' || anexoCom === '[]' || anexoCom === '') return '';
                            let paths = [];
                            try {
                                const parsed = JSON.parse(anexoCom);
                                paths = Array.isArray(parsed) ? parsed : [anexoCom];
                            } catch (e) {
                                paths = [anexoCom];
                            }
                            paths = paths.filter(p => p && String(p).trim() !== '' && String(p) !== 'null');
                            if (paths.length === 0) return '';
                            
                            return `
                                <div class="mt-4 space-y-2">
                                    <label class="text-[10px] font-black text-amber-700 uppercase tracking-widest block">Documentos Comprobatórios</label>
                                    ${paths.map((p, i) => `
                                        <a href="../../${esc(p)}" target="_blank" class="flex items-center justify-between p-2 bg-white border border-amber-200 rounded-lg text-xs hover:bg-amber-100 transition-colors">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                <span class="font-bold text-amber-800">Visualizar Documento ${paths.length > 1 ? i+1 : ''}</span>
                                            </div>
                                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        </a>
                                    `).join('')}
                                </div>
                            `;
                        })()}
                    </div>
                    <p class="mt-4 text-[11px] text-slate-500 text-center font-medium italic">Deseja converter esta justificativa em um registro oficial de afastamento?</p>
                `,
                confirmButtonText: 'Sim, Prosseguir',
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                customClass: {
                    popup: 'premium-swal',
                    title: 'premium-title',
                    confirmButton: 'premium-confirm',
                    cancelButton: 'premium-cancel'
                }
            });

            if (result.isConfirmed) {
                prosseguirParaAfastamento(r);
            }
        } else {
            prosseguirParaAfastamento(r);
        }
    }

    function prosseguirParaAfastamento(r) {
        if (typeof abrirFerias === 'function') {
            // Passa o anexo_comunicado (3º) e o comunicado (4º)
            abrirFerias(r.id_funcionario, r.nome, r.anexo_comunicado, r.comunicado);
            
            // Preenche a data do registro como sugestão inicial
            const dataPonto = r.data ? r.data.split('T')[0] : '';
            if (dataPonto) {
                document.getElementById('ferias_inicio').value = dataPonto;
                document.getElementById('ferias_fim').value = dataPonto;
            }
            if (r.comunicado) {
                document.getElementById('ferias_observacao').value = r.comunicado;
            }
        } else {
            Swal.fire('Erro', 'O módulo de afastamentos não foi carregado corretamente.', 'error');
        }
    }

    async function decidirStatusJustificativa(id, label) {
        const r = (window._pontoData || {})[id];
        if (!r) return;
        
        const dataStr = r.data ? String(r.data).split('T')[0].split('-').reverse().join('/') : '';
        const modalTitle = label ? `Decidir Justificativa [${label}]` : 'Decidir Justificativa';
        
        const result = await Swal.fire({
            title: modalTitle,
            html: `
                <div class="text-left space-y-2 mt-2">
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        <div class="mb-1"><span class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Colaborador</span></div>
                        <div class="text-slate-800 font-extrabold text-sm mb-3">${esc(r.nome)}</div>
                        
                        <div class="mb-1"><span class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Data do Ponto</span></div>
                        <div class="text-slate-800 font-bold text-xs mb-3">${dataStr}</div>

                        ${r.justificativa ? `
                            <div class="mb-1"><span class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Observação do Gestor</span></div>
                            <div class="italic text-slate-700 bg-white p-2 rounded border border-slate-100 text-[11px] mb-3 leading-relaxed">"${esc(r.justificativa)}"</div>
                        ` : ''}

                        ${r.comunicado ? `
                            <div class="mb-1"><span class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Aviso do Funcionário</span></div>
                            <div class="italic text-slate-700 bg-white p-2 rounded border border-slate-100 text-[11px] leading-relaxed">"${esc(r.comunicado)}"</div>
                        ` : ''}
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonColor: '#10b981',
            denyButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<div class="flex items-center gap-1.5"><svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> Deferir</div>',
            denyButtonText: '<div class="flex items-center gap-1.5"><svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Indeferir</div>',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'premium-swal',
                title: 'premium-title',
                confirmButton: 'premium-confirm',
                denyButton: 'premium-confirm bg-red-600 hover:bg-red-700',
                cancelButton: 'premium-cancel'
            }
        });

        if (result.isConfirmed) {
            // DEFERIR
            const confirmDefer = await Swal.fire({
                title: 'Confirmar Deferimento',
                text: "O ponto será mantido como justificado no cartão de ponto.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Sim, Deferir',
                cancelButtonText: 'Cancelar'
            });

            if (confirmDefer.isConfirmed) {
                processarStatusPonto(id, 'deferir_crh', '', label);
            }
        } else if (result.isDenied) {
            // INDEFERIR
            const { value: motivo } = await Swal.fire({
                title: 'Indeferir Justificativa',
                html: `<p class="text-sm text-slate-500 mb-3 block">Forneça o motivo do indeferimento. O turno voltará a ser descontado como <b class="text-red-600">Falta</b>.</p>
                       <textarea id="swal-motivo" class="w-full mt-0 text-sm p-3 font-semibold text-slate-800 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-200 focus:border-red-400 transition-all shadow-sm" rows="3" placeholder="Motivo da recusa pelo CRH..." style="resize: none; overflow-x: hidden !important; overflow-y: auto; box-sizing: border-box; width: 100%; max-width: 100%;"></textarea>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Indeferir e Reverter para Falta',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const m = document.getElementById('swal-motivo').value;
                    if (!m) Swal.showValidationMessage("Você precisa informar o motivo do indeferimento!");
                    return m;
                }
            });

            if (motivo) {
                processarStatusPonto(id, 'indeferir_crh', motivo, label);
            }
        }
    }

    async function processarStatusPonto(id, actionCode, motivo, horario) {
        Swal.fire({ title: 'Processando...', didOpen: () => { Swal.showLoading(); }, allowOutsideClick: false });
        
        try {
            const formData = new FormData();
            formData.append('action', actionCode);
            formData.append('id', id);
            if (motivo) formData.append('motivo', motivo);
            if (horario) formData.append('horario', horario);

            const res = await fetch('../../api/relatorios.php', { method: 'POST', body: formData });
            const data = await res.json();
            
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Salvo!', text: data.message, timer: 1500, showConfirmButton: false });
                loadPontoData();
            } else {
                Swal.fire('Erro', data.message, 'error');
            }
        } catch(e) {
            Swal.fire('Erro', 'Falha estrutural na requisição ao servidor.', 'error');
        }
    }
</script>

<script>
    const isAdminOrCRH = <?php echo $is_super ? 'true' : 'false'; ?>;
</script>
<?php include 'ferias_modal.php'; ?>

<?php include 'layout/footer.php'; ?>
