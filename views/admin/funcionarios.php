<?php include 'layout/header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<?php
// Identificação de Super Usuário (Admin ou CRH/CORSIN)
$user_name = $_SESSION['user_name'] ?? '';
$user_setor = $_SESSION['user_setor'] ?? '';
$user_level = $_SESSION['user_level'] ?? 3;
$isSuper = ($user_level == 1) || in_array(strtolower(trim($user_name)), ['corsin', 'crh']) || in_array(strtolower(trim($user_setor)), ['corsin', 'crh']);
?>

<div
    class="max-w-7xl mx-auto flex flex-col h-full bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <!-- Topbar Interna -->
    <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Gestão de Funcionários</h1>
            <p class="text-sm text-slate-500 mt-1">Gerencie os cadastros, matrículas e horários.</p>
        </div>
        <div class="flex items-center gap-3">
            <?php if ($isSuper): ?>
            <button id="btnBulkDelete" onclick="bulkDelete()" class="hidden items-center gap-2 px-4 py-2 bg-red-50 text-red-600 hover:bg-red-100 font-semibold rounded-xl border border-red-200 transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Excluir Selecionados (<span id="selectedCount">0</span>)
            </button>
            <a href="funcionario_form.php"
                class="flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-xl focus:ring-4 focus:ring-brand-500/20 transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Novo Funcionário
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Abas de Status -->
    <div class="px-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-1 overflow-x-auto no-scrollbar">
        <button onclick="tabFilter('ativos')" id="tab_ativos" 
            class="px-5 py-3 text-sm font-bold border-b-2 border-brand-500 text-brand-600 transition-all whitespace-nowrap">
            Ativos (<span id="count_ativos">0</span>)
        </button>
        <button onclick="tabFilter('afastados')" id="tab_afastados" 
            class="px-5 py-3 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100/50 transition-all whitespace-nowrap">
            Afastados (<span id="count_afastados">0</span>)
        </button>
        <button onclick="tabFilter('exonerados')" id="tab_exonerados" 
            class="px-5 py-3 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100/50 transition-all whitespace-nowrap">
            Exonerados (<span id="count_exonerados">0</span>)
        </button>
    </div>

    <!-- Tabela (DataTables) -->
    <div class="p-6 flex-1 overflow-auto relative">
        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="absolute inset-0 z-10 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center transition-opacity duration-500">
            <div class="flex flex-col items-center gap-4">
                <div class="relative">
                    <div class="w-12 h-12 rounded-full border-4 border-slate-200 border-t-brand-500 animate-spin"></div>
                </div>
                <p class="text-sm font-semibold text-slate-500 animate-pulse">Carregando funcionários...</p>
            </div>
            <!-- Skeleton Rows -->
            <div class="w-full max-w-4xl mt-8 space-y-3 px-4">
                <div class="h-10 bg-slate-100 rounded-lg animate-pulse"></div>
                <div class="h-10 bg-slate-50 rounded-lg animate-pulse delay-75"></div>
                <div class="h-10 bg-slate-100 rounded-lg animate-pulse delay-150"></div>
                <div class="h-10 bg-slate-50 rounded-lg animate-pulse delay-200"></div>
                <div class="h-10 bg-slate-100 rounded-lg animate-pulse delay-300"></div>
            </div>
        </div>
        <table id="tabelaFuncionarios" class="w-full text-left border-collapse display nowrap" style="width:100%">
            <thead>
                <tr class="bg-slate-50">
                    <th class="px-4 py-3 rounded-l-lg w-10">
                        <input type="checkbox" id="selectAll" class="w-4 h-4 text-brand-600 border-slate-300 rounded focus:ring-brand-500 transition-all cursor-pointer">
                    </th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">ID
                    </th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Nome</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Matrícula</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Setor</th>
                    <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Horário
                        Vinculado</th>
                    <th
                        class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3 text-right rounded-r-lg">
                        Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- Carregado via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Funcionario removido - agora em funcionario_form.php -->



<!-- Modal Câmera Multifunção -->
<div id="modalCamera" class="fixed inset-0 z-[999] hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="fecharCamera()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div
                class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-sm border border-slate-100 p-0 flex flex-col">

                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Câmera do Sistema
                    </h3>
                    <button onclick="fecharCamera()"
                        class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-5 flex flex-col items-center">
                    <p class="text-sm font-medium text-slate-600 mb-4 text-center w-full" id="camSubtitle">
                        Selecione a Ação para <span id="camNomeFunc" class="font-bold text-indigo-600"></span>
                    </p>

                    <!-- Camera Tabs -->
                    <div class="flex space-x-1 p-1 bg-slate-100 rounded-xl mb-4 w-full overflow-x-auto scroolbar-hide">
                        <button onclick="mudarModoCamera('foto')" id="tabFoto"
                            class="flex-none sm:flex-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white shadow-sm text-slate-800 transition-all whitespace-nowrap">Foto
                            Perfil</button>
                        <button onclick="mudarModoCamera('qr')" id="tabQr"
                            class="flex-none sm:flex-1 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-500 hover:text-slate-700 transition-all whitespace-nowrap">Ler
                            QR Code</button>
                        <button onclick="mudarModoCamera('facial')" id="tabFacial"
                            class="flex-none sm:flex-1 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-500 hover:text-slate-700 transition-all whitespace-nowrap">Biometria
                            Facial</button>
                        <button onclick="mudarModoCamera('digital')" id="tabDigital"
                            class="flex-none sm:flex-1 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-500 hover:text-slate-700 transition-all whitespace-nowrap">Biometria
                            Digital</button>
                    </div>

                    <input type="hidden" id="cam_func_id">
                    <input type="hidden" id="cam_modo" value="foto">

                    <!-- Video Viewfinder -->
                    <div class="relative w-full aspect-[4/3] max-h-[50vh] bg-black rounded-2xl overflow-hidden shadow-inner mb-4 flex items-center justify-center transition-all duration-300"
                        id="videoContainer">
                        <video id="videoFeed" autoplay playsinline muted
                            class="absolute h-full w-full object-cover z-10 hidden"></video>
                        <canvas id="videoCanvas" class="hidden"></canvas>

                        <!-- Overlay Guides -->
                        <div id="guideFacial"
                            class="absolute inset-0 z-20 pointer-events-none hidden items-center justify-center">
                            <div
                                class="w-48 h-64 border-2 border-dashed border-indigo-400 rounded-[50%] opacity-80 shadow-[0_0_0_9999px_rgba(0,0,0,0.5)]">
                            </div>
                        </div>
                        <div id="guideQr"
                            class="absolute inset-0 z-20 pointer-events-none hidden items-center justify-center">
                            <div
                                class="w-48 h-48 border-2 border-dashed border-emerald-400 rounded-xl opacity-80 shadow-[0_0_0_9999px_rgba(0,0,0,0.5)]">
                            </div>
                        </div>
                        <!-- Icon Digital Fingerprint -->
                        <div id="digitalCanvas"
                            class="absolute inset-0 z-20 hidden flex-col items-center justify-center bg-slate-900 border-4 border-teal-500 rounded-2xl">
                            <div
                                class="w-20 h-20 bg-teal-900/50 text-teal-400 rounded-full flex items-center justify-center mb-4 relative">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4">
                                    </path>
                                </svg>
                                <div id="bioScanRingCam"
                                    class="absolute inset-0 rounded-full border-4 border-teal-500 opacity-20 hidden">
                                </div>
                            </div>
                            <span class="text-teal-400 font-semibold text-sm">Aguardando Captura</span>

                            <!-- Mobile WebAuthn Support -->
                            <button id="btnWebAuthn" onclick="registrarWebAuthn()"
                                class="mt-4 hidden px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-lg border border-white/20 transition-all">
                                Vincular Biometria do Celular
                            </button>
                        </div>

                        <div id="camLoading"
                            class="absolute z-0 flex flex-col items-center justify-center text-slate-400">
                            <svg class="animate-spin h-6 w-6 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span class="text-xs">Iniciando câmera...</span>
                        </div>
                    </div>

                    <div class="w-full flex gap-3">
                        <button id="btnSwitchCam" onclick="inverterCamera()"
                            class="p-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-semibold transition-colors flex items-center justify-center"
                            title="Inverter Câmera (Frontal/Traseira)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                        </button>
                        <button id="btnCapturarCamera" onclick="tirarFoto()"
                            class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-semibold shadow-sm transition-colors flex justify-center items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                </path>
                            </svg>
                            Capturar Imagem
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let tableFunc;
    let currentTab = 'ativos';
    const isAdminOrCRH = <?php echo $isSuper ? 'true' : 'false'; ?>;

    window.tabFilter = function(tab) {
        if (!tableFunc) return;
        currentTab = tab;

        // Update tab UI styles
        document.querySelectorAll('#tab_ativos, #tab_afastados, #tab_exonerados').forEach(function(btn) {
            btn.classList.remove('border-brand-500', 'text-brand-600', 'font-bold');
            btn.classList.add('border-transparent', 'text-slate-500', 'font-semibold');
        });
        var activeBtn = document.getElementById('tab_' + tab);
        if (activeBtn) {
            activeBtn.classList.add('border-brand-500', 'text-brand-600', 'font-bold');
            activeBtn.classList.remove('border-transparent', 'text-slate-500', 'font-semibold');
        }

        // Reload table with new status filter
        tableFunc.ajax.url('../../api/funcionarios.php?status=' + tab).load();

        // Clean checkboxes
        $('#selectAll').prop('checked', false);
        updateBulkDeleteButton();
    };

    function updateCounts(counts) {
        if (counts) {
            $('#count_ativos').text(counts.total_ativos || 0);
            $('#count_afastados').text(counts.total_afastados || 0);
            $('#count_exonerados').text(counts.total_exonerados || 0);
        }
    }

    $(document).ready(function () {
        // Mostra o overlay ao iniciar
        const overlay = document.getElementById('loadingOverlay');

        tableFunc = $('#tabelaFuncionarios').DataTable({
            ajax: {
                url: '../../api/funcionarios.php?status=ativos',
                dataSrc: function(json) {
                    updateCounts(json.counts);
                    return json.data || [];
                }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    className: 'px-4 py-3',
                    render: function (data, type, row) {
                        return `<input type="checkbox" class="row-checkbox w-4 h-4 text-brand-600 border-slate-300 rounded focus:ring-brand-500 transition-all cursor-pointer" value="${row.id}">`;
                    }
                },
                { data: 'id', width: '50px' },
                {
                    data: 'nome',
                    render: function (data, type, row) {
                        let icons = '';
                        // Digital (Física)
                        icons += row.tem_biometria == 1
                            ? `<svg class="w-4 h-4 text-emerald-500 inline ml-1" title="Digital Cadastrada" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path></svg>`
                            : '';

                        // Foto de Perfil
                        icons += row.tem_foto == 1
                            ? `<svg class="w-4 h-4 text-sky-500 inline ml-1" title="Foto de Perfil" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>`
                            : '';

                        // Biometria Facial
                        icons += row.tem_facial == 1
                            ? `<svg class="w-4 h-4 text-indigo-500 inline ml-1" title="Facial Cadastrada" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
                            : '';

                        // QR Code
                        icons += row.tem_qr == 1
                            ? `<svg class="w-4 h-4 text-amber-500 inline ml-1" title="QR Code Ativo" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>`
                            : '';

                        let afastadoBadge = '';
                        if (row.data_fim_afastamento) {
                            const dtReturn = new Date(row.data_fim_afastamento + 'T12:00:00');
                            dtReturn.setDate(dtReturn.getDate() + 1);
                            const returnStr = dtReturn.toLocaleDateString('pt-BR');
                            afastadoBadge = `<span class="ml-2 px-1.5 py-0.5 text-[9px] font-black uppercase tracking-tighter bg-amber-500 text-white rounded-md shadow-sm animate-pulse border border-amber-600" title="Retorno previsto para: ${returnStr}">Afastado</span>`;
                        }

                        let nameClass = (row.is_exonerado == 1 || row.is_exonerado === true || row.is_exonerado === 't') ? 'text-red-600 font-extrabold' : 'text-slate-800 font-bold';
                        return `<div class="${nameClass} flex items-center">${data} ${afastadoBadge} ${icons}</div>`;
                    }
                },
                { data: 'matricula', className: 'font-mono text-slate-600' },
                {
                    data: 'setor',
                    render: function (data, type, row) {
                        let res = data || '';
                        if (row.setor2 && row.setor2.trim() !== '') {
                            res += (res ? ' / ' : '') + row.setor2;
                        }
                        return res || '<span class="text-slate-400 text-sm">N/I</span>';
                    }
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        if (row.primeiro_horario && row.quarto_horario) {
                            let text = `${row.primeiro_horario.substring(0, 5)} às ${row.quarto_horario.substring(0, 5)}`;
                            if (row.segundo_horario && row.terceiro_horario) {
                                text = `${row.primeiro_horario.substring(0, 5)} às ${row.segundo_horario.substring(0, 5)} | ${row.terceiro_horario.substring(0, 5)} às ${row.quarto_horario.substring(0, 5)}`;
                            }
                            return `<div class="text-xs bg-slate-100 text-slate-600 font-medium px-2 py-1 rounded border inline-block whitespace-nowrap">${text}</div>`;
                        } else {
                            return `<span class="text-slate-400 text-sm italic">Livre</span>`;
                        }
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-right',
                    render: function (data, type, row) {
                        let actions = `
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="abrirFerias(${row.id}, '${row.nome.replace(/'/g, "\\\'")}')" class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg transition-colors border border-transparent hover:border-amber-100" title="Motivo Afastamento">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zM10 13l4 4m0-4l-4 4" /></svg>
                                </button>`;
                        
                        if (isAdminOrCRH) {
                            actions += `
                                <a href="funcionario_form.php?id=${row.id}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors border border-transparent hover:border-indigo-100" title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <button onclick="deletarFuncionario(${row.id})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-100" title="Excluir">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                                ${(row.is_exonerado != 1 && row.is_exonerado !== true && row.is_exonerado !== 't') ? `
                                <button onclick="exonerarFuncionario(${row.id}, '${row.nome.replace(/'/g, "\\\'")}')" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-100" title="Exonerar Funcionário">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                </button>
                                ` : `
                                <button onclick="reintegrarFuncionario(${row.id}, '${row.nome.replace(/'/g, "\\\'")}')" class="p-2 text-emerald-500 hover:bg-emerald-50 rounded-lg transition-colors border border-transparent hover:border-emerald-100" title="Reintegrar ao Quadro">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </button>
                                `}`;
                        }

                        actions += `</div>`;
                        return actions;
                    }
                },
                // (no hidden columns needed - status filtered server-side)
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' },
            dom: '<"flex flex-col md:flex-row justify-between items-center mb-4"lf>rt<"flex flex-col md:flex-row justify-between items-center mt-4"ip>',
            pageLength: 10,
            responsive: true,
            initComplete: function () {
                if (overlay) {
                    overlay.style.opacity = '0';
                    setTimeout(() => { overlay.style.display = 'none'; }, 500);
                }
            },
            drawCallback: function (settings) {
                updateBulkDeleteButton();
            }
        });

    // Lógica de Seleção
    $('#selectAll').on('click', function() {
        $('.row-checkbox').prop('checked', this.checked);
        updateBulkDeleteButton();
    });

    $('#tabelaFuncionarios tbody').on('change', '.row-checkbox', function() {
        const allChecked = $('.row-checkbox:checked').length === $('.row-checkbox').length;
        $('#selectAll').prop('checked', allChecked);
        updateBulkDeleteButton();
    });

    function updateBulkDeleteButton() {
        const selected = $('.row-checkbox:checked').length;
        const btn = $('#btnBulkDelete');
        if (selected > 0) {
            btn.css('display', 'flex').removeClass('hidden');
            $('#selectedCount').text(selected);
        } else {
            btn.css('display', 'none').addClass('hidden');
        }
    }

    window.bulkDelete = function() {
        const ids = [];
        $('.row-checkbox:checked').each(function() {
            ids.push($(this).val());
        });

        if (ids.length === 0) return;

        Swal.fire({
            title: 'Excluir Selecionados?',
            text: `Você está prestes a excluir ${ids.length} funcionários. Esta ação não pode ser desfeita!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sim, Excluir Todos',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const confirmBtn = Swal.getConfirmButton();
                setLoading(confirmBtn, true);

                fetch('../../api/funcionarios.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ids: ids })
                })
                .then(res => res.json())
                .then(data => {
                    setLoading(confirmBtn, false);
                    if (data.success) {
                        Swal.fire('Excluídos!', data.message, 'success');
                        tableFunc.ajax.reload(null, false);
                        $('#selectAll').prop('checked', false);
                    } else {
                        Swal.fire('Erro', data.message, 'error');
                    }
                })
                .catch(err => {
                    setLoading(confirmBtn, false);
                    Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
                });
            }
        });
    }

    window.deletarFuncionario = function (id) {
        Swal.fire({
            title: 'Excluir Funcionário?',
            text: "Deseja realmente remover este cadastro? Esta ação não pode ser desfeita!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sim, Excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const confirmBtn = Swal.getConfirmButton();
                setLoading(confirmBtn, true);

                fetch('../../api/funcionarios.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                })
                    .then(res => res.json())
                    .then(data => {
                        setLoading(confirmBtn, false);
                        if (data.success) {
                            Swal.fire('Excluído!', data.message, 'success');
                            tableFunc.ajax.reload(null, false);
                        } else {
                            Swal.fire('Erro', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        setLoading(confirmBtn, false);
                        Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
                    });
            }
        });
    };

    // Tailwind forms nos inputs do DT
    $('.dataTables_filter input').addClass('border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 ml-2 shadow-sm');
    $('.dataTables_length select').addClass('border border-slate-200 rounded-lg px-2 py-1.5 focus:outline-none shadow-sm mx-1');

    window.exonerarFuncionario = function (id, nome) {
        Swal.fire({
            title: 'Motivo da Exoneração',
            text: `Informe o motivo para efetivar a exoneração de ${nome}:`,
            input: 'textarea',
            inputPlaceholder: 'Digite o motivo aqui...',
            inputAttributes: {
                'aria-label': 'Digite o motivo aqui'
            },
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Confirmar Exoneração',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) {
                    return 'Você precisa informar um motivo!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const confirmBtn = Swal.getConfirmButton();
                setLoading(confirmBtn, true);

                fetch('../../api/funcionarios.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'exonerate', id: id, motivo: result.value })
                })
                    .then(res => res.json())
                    .then(data => {
                        setLoading(confirmBtn, false);
                        if (data.success) {
                            Swal.fire('Exonerado!', data.message, 'success');
                            tableFunc.ajax.reload(null, false);
                        } else {
                            Swal.fire('Erro', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
                    });
            }
        });
    };

    window.reintegrarFuncionario = function (id, nome) {
        Swal.fire({
            title: 'Reintegrar Funcionário?',
            text: `Deseja realmente reintegrar ${nome} ao quadro de funcionários? O acesso ao ponto será restaurado imediatamente.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sim, Reintegrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const confirmBtn = Swal.getConfirmButton();
                setLoading(confirmBtn, true);

                fetch('../../api/funcionarios.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'reintegrate', id: id })
                })
                    .then(res => res.json())
                    .then(data => {
                        setLoading(confirmBtn, false);
                        if (data.success) {
                            Swal.fire('Reintegrado!', data.message, 'success');
                            tableFunc.ajax.reload(null, false);
                        } else {
                            Swal.fire('Erro', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
                    });
            }
        });
    };
});

    // Funções de Modal removidas - agora em funcionario_form.php

    // -- Flow Câmera Mobile (Foto, QR, Facial) --
    let videoStream = null;
    let currentFacingMode = 'user'; // 'user' (frontal) ou 'environment' (traseira)
    let autoCaptureTimer = null;
    let modelsLoaded = false;

    async function loadFaceModels() {
        if (modelsLoaded) return;
        const MODEL_URL = 'https://justadudewhohacks.github.io/face-api.js/models';
        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);
            modelsLoaded = true;
            console.log("Modelos Face-API carregados.");
        } catch (e) {
            console.error("Erro ao carregar modelos Face-API:", e);
        }
    }

    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

    window.abrirCamera = async function (id, nome) {
        try {
            document.getElementById('cam_func_id').value = id;
            document.getElementById('camNomeFunc').textContent = nome;
            document.getElementById('modalCamera').classList.remove('hidden');
            window.mudarModoCamera('foto'); // Default
            window.iniciarStreamVideo();
            loadFaceModels(); // Carrega modelos em background
        } catch (e) {
            console.error("Erro abrirCamera: ", e);
        }
    };

    window.fecharCamera = function () {
        document.getElementById('modalCamera').classList.add('hidden');
        window.pararStreamVideo();
    };

    window.mudarModoCamera = function (modo) {
        document.getElementById('cam_modo').value = modo;

        // Reset Tabs styling
        ['tabFoto', 'tabQr', 'tabFacial', 'tabDigital'].forEach(tab => {
            let el = document.getElementById(tab);
            if (el) el.className = "flex-none sm:flex-1 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-500 hover:text-slate-700 transition-all whitespace-nowrap";
        });

        // Remover guias pontilhadas antigas se existirem
        document.getElementById('guideFacial').classList.add('hidden');
        document.getElementById('guideQr').classList.add('hidden');
        document.getElementById('digitalCanvas').classList.add('hidden');

        const videoContainer = document.getElementById('videoContainer');

        // Remove dimension and border classes
        videoContainer.classList.remove('w-full', 'aspect-[4/3]', 'max-h-[50vh]', 'rounded-2xl', 'w-56', 'h-56', 'h-72', 'rounded-xl', 'rounded-[50%]', 'border-4', 'border-emerald-400', 'border-indigo-400', 'border-teal-400', 'mx-auto');

        document.getElementById('btnSwitchCam').classList.remove('hidden'); // default
        document.getElementById('btnCapturarCamera').setAttribute('onclick', 'tirarFoto()');

        if (modo === 'foto') {
            videoContainer.classList.add('w-full', 'aspect-[4/3]', 'max-h-[50vh]', 'rounded-2xl');

            document.getElementById('tabFoto').className = "flex-none sm:flex-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white shadow-sm text-slate-800 transition-all whitespace-nowrap";
            document.getElementById('btnCapturarCamera').innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg> Tirar Foto Perfil`;
        } else if (modo === 'qr') {
            videoContainer.classList.add('w-56', 'h-56', 'mx-auto', 'rounded-xl', 'border-4', 'border-emerald-400');

            document.getElementById('tabQr').className = "flex-none sm:flex-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white shadow-sm text-slate-800 transition-all whitespace-nowrap";
            document.getElementById('btnCapturarCamera').innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg> Escanear QR Code`;
        } else if (modo === 'facial') {
            videoContainer.classList.add('w-56', 'h-72', 'mx-auto', 'rounded-[50%]', 'border-4', 'border-indigo-400');

            document.getElementById('tabFacial').className = "flex-none sm:flex-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white shadow-sm text-slate-800 transition-all whitespace-nowrap";
            document.getElementById('btnCapturarCamera').innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Capturar Biometria Facial`;
        } else if (modo === 'digital') {
            videoContainer.classList.add('w-full', 'aspect-[4/3]', 'max-h-[50vh]', 'rounded-2xl', 'mx-auto');
            document.getElementById('tabDigital').className = "flex-none sm:flex-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white shadow-sm text-slate-800 transition-all whitespace-nowrap";
            document.getElementById('btnSwitchCam').classList.add('hidden');
            document.getElementById('digitalCanvas').classList.remove('hidden');
            document.getElementById('digitalCanvas').classList.add('flex');

            // Esconder o vídeo durante a biometria digital
            document.getElementById('videoFeed').classList.add('hidden');
            document.getElementById('camLoading').classList.add('hidden');

            document.getElementById('btnCapturarCamera').setAttribute('onclick', 'iniciarCapturaDigital()');
            document.getElementById('btnCapturarCamera').innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path></svg> Iniciar Sensor Digital`;

            if (isMobile && window.PublicKeyCredential) {
                document.getElementById('btnWebAuthn').classList.remove('hidden');
            } else {
                document.getElementById('btnWebAuthn').classList.add('hidden');
            }
        }

        if (autoCaptureTimer) clearTimeout(autoCaptureTimer);

        if (modo === 'facial' && isMobile) {
            document.getElementById('camSubtitle').innerHTML = `Posicione o rosto. Captura em <span id="timerSec" class="text-red-500 font-bold">3</span>s...`;
            autoCaptureTimer = setTimeout(() => {
                let sec = 3;
                let interval = setInterval(() => {
                    sec--;
                    if (document.getElementById('timerSec')) document.getElementById('timerSec').textContent = sec;
                    if (sec <= 0) {
                        clearInterval(interval);
                        tirarFoto();
                    }
                }, 1000);
            }, 500);
        } else {
            document.getElementById('camSubtitle').innerHTML = `Selecione a Ação para <span id="camNomeFunc" class="font-bold text-indigo-600"></span>`;
        }
    };

    window.iniciarStreamVideo = async function () {
        const video = document.getElementById('videoFeed');
        const camLoading = document.getElementById('camLoading');

        camLoading.classList.remove('hidden');
        camLoading.innerHTML = `
                <svg class="animate-spin h-6 w-6 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 008-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-xs">Iniciando câmera...</span>`;

        if (!video.classList.contains('hidden')) video.classList.add('hidden');

        window.pararStreamVideo();

        try {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error("Seu navegador não suporta acesso à câmera.");
            }

            videoStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: currentFacingMode }
            });

            video.srcObject = videoStream;

            // Usar evento 'playing' ou 'loadedmetadata' de forma resiliente
            const onVideoReady = () => {
                camLoading.classList.add('hidden');
                video.classList.remove('hidden');
            };

            video.onloadedmetadata = async () => {
                try {
                    await video.play();
                    onVideoReady();
                } catch (e) {
                    console.error("Erro ao dar play no vídeo:", e);
                }
            };

        } catch (err) {
            console.error("Erro ao acessar câmera: ", err);
            let errorMsg = "Permissão negada ou câmera inacessível.";
            if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                errorMsg = "O navegador exige conexão HTTPS para usar a câmera.";
            } else if (err.name === 'NotAllowedError') {
                errorMsg = "Acesso à câmera bloqueado pelo usuário/navegador.";
            } else if (err.name === 'NotFoundError') {
                errorMsg = "Nenhuma câmera encontrada no dispositivo.";
            }

            camLoading.innerHTML = `<span class="text-xs text-red-400 font-bold w-full px-4 text-center">${errorMsg}</span>`;
        }
    };

    window.pararStreamVideo = function () {
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
        }
    };

    window.inverterCamera = function () {
        currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
        window.iniciarStreamVideo();
    };

    window.tirarFoto = function () {
        if (!videoStream) return;
        const video = document.getElementById('videoFeed');
        const canvas = document.getElementById('videoCanvas');
        const modo = document.getElementById('cam_modo').value;
        const funcId = document.getElementById('cam_func_id').value;

        // Define target aspect ratio based on the visual video container's sizes
        const container = document.getElementById('videoContainer');
        const targetRatio = container.clientWidth / container.clientHeight;

        const vW = video.videoWidth;
        const vH = video.videoHeight;
        const vRatio = vW / vH;

        let sWidth = vW;
        let sHeight = vH;
        let sX = 0;
        let sY = 0;

        if (vRatio > targetRatio) {
            // Video is wider relative to target. Crop the width.
            sWidth = vH * targetRatio;
            sX = (vW - sWidth) / 2;
        } else {
            // Video is taller relative to target. Crop the height.
            sHeight = vW / targetRatio;
            sY = (vH - sHeight) / 2;
        }

        canvas.width = sWidth;
        canvas.height = sHeight;
        canvas.getContext('2d').drawImage(video, sX, sY, sWidth, sHeight, 0, 0, sWidth, sHeight);


        let base64Image = canvas.toDataURL('image/jpeg', 0.8);
        document.getElementById('btnCapturarCamera').innerHTML = "Processando...";
        document.getElementById('btnCapturarCamera').disabled = true;

        const processarSalvamento = async (descriptor = null) => {
            let actionForm = '';
            if (modo === 'foto') actionForm = 'save_foto_perfil';
            if (modo === 'qr') actionForm = 'save_codigo_qr';
            if (modo === 'facial') actionForm = 'save_biometria_facial';

            fetch('../../api/funcionarios.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: actionForm,
                    id: funcId,
                    image_data: base64Image,
                    facial_descriptor: descriptor
                })
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 3000 });
                    tableFunc.ajax.reload(null, false);
                    window.fecharCamera();
                } else {
                    Swal.fire('Ops!', data.message, 'error');
                    window.mudarModoCamera(modo);
                }
            }).catch(err => {
                Swal.fire('Erro', 'Falha ao salvar imagem', 'error');
                window.mudarModoCamera(modo);
            }).finally(() => {
                document.getElementById('btnCapturarCamera').disabled = false;
            });
        };

        if (modo === 'facial') {
            document.getElementById('btnCapturarCamera').innerHTML = "Mapeando Face...";
            // Extrai descritor
            const img = new Image();
            img.src = base64Image;
            img.onload = async () => {
                const detection = await faceapi.detectSingleFace(img, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
                if (detection) {
                    processarSalvamento(Array.from(detection.descriptor));
                } else {
                    Swal.fire('Face não detectada', 'Certifique-se de que seu rosto está bem visível.', 'warning');
                    window.mudarModoCamera(modo);
                    document.getElementById('btnCapturarCamera').disabled = false;
                    document.getElementById('btnCapturarCamera').innerHTML = "Capturar Novamente";
                }
            };
        } else {
            processarSalvamento();
        }
    };

    // Função "Placeholder" que vai acionar o Driver/API Local do Leitor Biométrico
    async function iniciarCapturaDigital() {
        document.getElementById('bioScanRingCam').classList.remove('hidden');
        document.getElementById('btnCapturarCamera').innerHTML = "Procurando Leitor...";
        document.getElementById('btnCapturarCamera').disabled = true;

        const funcId = document.getElementById('cam_func_id').value;

        if (window.dpSocket) {
            window.dpSocket.close();
        }

        // Tenta se conectar às portas padrões do Serviço HID DigitalPersona
        // (Geralmente 15270 para sem-SSL ou 52181 para SSL)
        const endpoints = [
            "wss://localhost:52181/API",
            "ws://localhost:15270/API",
            "ws://127.0.0.1:15270/API"
        ];

        let connectedEndpoint = null;

        for (const endpoint of endpoints) {
            try {
                const connected = await tentarConectar(endpoint, funcId);
                if (connected) {
                    connectedEndpoint = endpoint;
                    break;
                }
            } catch (e) {
                console.warn(`Falha ao conectar em ${endpoint}:`, e);
            }
        }

        if (!connectedEndpoint) {
            tratarFallbackLeitor();
        }
    }

    function tentarConectar(url, funcId) {
        return new Promise((resolve, reject) => {
            let socket = null;
            try {
                socket = new WebSocket(url);
            } catch (e) {
                return reject(e);
            }

            // Timeout de 2 segundos para não atrasar a fila
            const timeout = setTimeout(() => {
                if (socket.readyState !== WebSocket.OPEN) {
                    socket.close();
                    reject("Timeout");
                }
            }, 2000);

            socket.onopen = function () {
                clearTimeout(timeout);
                window.dpSocket = socket;

                document.getElementById('btnCapturarCamera').innerHTML = "Lendo digital... (Aguardando Toque)";

                // Inicia o leitor
                socket.send(JSON.stringify({
                    Type: "Capture",
                    Method: "Start"
                }));
                resolve(true);
            };

            socket.onmessage = function (event) {
                try {
                    const data = JSON.parse(event.data);
                    if (data && data.Event === "SamplesReady") {
                        socket.send(JSON.stringify({ Type: "Capture", Method: "Stop" }));
                        if (data.Samples && data.Samples.length > 0) {
                            const sampleData = data.Samples[data.Samples.length - 1].Data;
                            salvarBiometriaNoBanco(funcId, sampleData);
                        } else {
                            throw new Error("Sinal recebido, mas os dados base64 da digital estavam vazios.");
                        }
                    }
                } catch (err) {
                    console.error("Erro no Parse do JSON:", err);
                }
            };

            socket.onerror = function (err) {
                clearTimeout(timeout);
                reject(err);
            };
        });
    }

    function tratarFallbackLeitor() {
        document.getElementById('bioScanRingCam').classList.add('hidden');
        document.getElementById('btnCapturarCamera').disabled = false;
        document.getElementById('btnCapturarCamera').innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path></svg> Tentar Novamente`;

        Swal.fire({
            title: 'Leitor Não Detectado',
            html: `O <i>'Serviço Local HID'</i> negou a conexão.<br><br>
                   <b>Dicas p/ o Administrador do Windows:</b><br>
                   1. Confirme se o serviço <b>DigitalPersona SDK / Lite Client</b> está rodando.<br>
                   2. Desative o AdBlock no Chrome se estiver usando HTTP (localhost)<br>
                   3. Algumas redes bloqueiam os WebSockets nas portas <code>15270</code> e <code>52181</code>.`,
            icon: 'warning',
            confirmButtonColor: '#f97316'
        });
    }

    async function salvarBiometriaNoBanco(funcId, hashBiometria) {
        document.getElementById('bioScanRingCam').classList.add('hidden');
        document.getElementById('btnCapturarCamera').innerHTML = "Lido com Sucesso!";

        try {
            const res = await fetch('../../api/funcionarios.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save_biometria',
                    id: funcId,
                    biometria: hashBiometria
                })
            });
            const data = await res.json();

            if (data.success) {
                window.fecharCamera();
                tableFunc.ajax.reload();

                Swal.fire({
                    title: 'Digital Cadastrada!',
                    html: `A digital física foi lida e vinculada ao funcionário no sistema!`,
                    icon: 'success',
                    confirmButtonText: 'Bom Trabalho',
                    confirmButtonColor: '#0ea5e9',
                });
            } else {
                Swal.fire('Ops!', data.message, 'error');
                reporBotao();
            }
        } catch (e) {
            Swal.fire('Erro BD', 'A digital foi lida pelo Scanner, mas o sistema falhou ao salvar.', 'error');
            reporBotao();
        }
    }

    function reporBotao() {
        if (window.dpSocket) window.dpSocket.close();
        document.getElementById('btnCapturarCamera').disabled = false;
        document.getElementById('btnCapturarCamera').innerHTML = "Tentar Novamente";
    }

    function simularCapturaDigital(funcId) {
        Swal.fire({
            title: 'Simulação Adicionada',
            text: 'Como não havia hardware físico disponível, geramos e vinculamos um hash biométrico fictício para este funcionário.',
            icon: 'success'
        }).then(() => {
            tableFunc.ajax.reload(null, false);
            window.fecharCamera();
        });
    }
    // -- Fim Flow Biometria --

    // -- Lógica WebAuthn Mobile --
    window.registrarWebAuthn = async function () {
        if (!window.PublicKeyCredential) {
            Swal.fire('Não Suportado', 'Este navegador não suporta biometria nativa.', 'warning');
            return;
        }

        const funcId = document.getElementById('cam_func_id').value;
        const btn = document.getElementById('btnWebAuthn');
        btn.disabled = true;
        btn.textContent = "Aguardando Celular...";

        try {
            // 1. Obter opções do backend
            const resOpt = await fetch('../../api/webauthn.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_registration_options', id: funcId })
            });
            const opt = await resOpt.json();

            if (!opt.success) throw new Error(opt.message);

            // 2. Criar credencial no navegador
            const challenge = Uint8Array.from(atob(btoa(opt.challenge)), c => c.charCodeAt(0));
            const userId = Uint8Array.from(atob(opt.user.id), c => c.charCodeAt(0));

            const creationOptions = {
                publicKey: {
                    challenge,
                    rp: { name: "Sys Ponto FUNAD", id: window.location.hostname },
                    user: {
                        id: userId,
                        name: opt.user.name,
                        displayName: opt.user.displayName
                    },
                    pubKeyCredParams: [{ alg: -7, type: "public-key" }, { alg: -257, type: "public-key" }],
                    timeout: 60000,
                    attestation: "direct",
                    authenticatorSelection: {
                        authenticatorAttachment: "platform",
                        userVerification: "required"
                    }
                }
            };

            const credential = await navigator.credentials.create(creationOptions);

            // 3. Enviar para o backend
            const regRes = await fetch('../../api/webauthn.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'register_credential',
                    id: funcId,
                    credentialId: btoa(String.fromCharCode(...new Uint8Array(credential.rawId))),
                    publicKey: btoa(String.fromCharCode(...new Uint8Array(credential.response.getPublicKey())))
                })
            });
            const regJson = await regRes.json();

            if (regJson.success) {
                Swal.fire('Sucesso!', 'Celular vinculado com biometria nativa!', 'success');
                tableFunc.ajax.reload(null, false);
                window.fecharCamera();
            } else {
                throw new Error(regJson.message);
            }

        } catch (err) {
            console.error(err);
            Swal.fire('Erro', 'Falha ao vincular biometria: ' + err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = "Vincular Biometria do Celular";
        }
    };
    // -- Fim Flow Câmera Mobile --

    // Operações CRUD individuais (salvar/editar) movidas para funcionario_form.php

    async function deletarFuncionario(id) {
        Swal.fire({
            title: 'O que deseja excluir?',
            text: "Escolha entre remover o cadastro completo ou apenas as biometrias cadastradas.",
            icon: 'question',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonColor: '#ef4444',
            denyButtonColor: '#f59e0b',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Excluir Perfil Completo',
            denyButtonText: 'Limpar Apenas Biometrias',
            cancelButtonText: 'Cancelar'
        }).then(async (result) => {
            if (result.isConfirmed) {
                // EXCLUIR PERFIL COMPLETO
                try {
                    const res = await fetch('../../api/funcionarios.php', {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    const data = await res.json();

                    if (data.success) {
                        Swal.fire('Excluído!', data.message, 'success');
                        tableFunc.ajax.reload();
                    } else {
                        Swal.fire('Erro', data.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('Erro', 'Houve um problema ao excluir.', 'error');
                }
            } else if (result.isDenied) {
                // LIMPAR APENAS BIOMETRIAS
                try {
                    const res = await fetch('../../api/funcionarios.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'clear_biometrics', id: id })
                    });
                    const data = await res.json();

                    if (data.success) {
                        Swal.fire('Biometrias Limpas!', data.message, 'success');
                        tableFunc.ajax.reload();
                    } else {
                        Swal.fire('Erro', data.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('Erro', 'Houve um problema ao limpar biometrias.', 'error');
                }
            }
        });
    }
    function capturarLocalizacaoAdmin(event) {
        if (!navigator.geolocation) {
            Swal.fire("Erro", "Seu navegador não suporta geolocalização.", "error");
            return;
        }
        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = "Obtendo...";
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                document.getElementById("func_lat").value = pos.coords.latitude.toFixed(8);
                document.getElementById("func_lng").value = pos.coords.longitude.toFixed(8);
                btn.disabled = false;
                btn.innerHTML = originalText;
                Swal.fire({ toast: true, position: "top-end", icon: "success", title: "Localização capturada!", showConfirmButton: false, timer: 2000 });
            },
            (err) => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                Swal.fire("Erro", "Erro ao obter localização: " + err.message, "error");
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }

    async function exonerarFuncionario(id, nome) {
        const { value: motivo } = await Swal.fire({
            title: 'Exonerar ' + nome,
            input: 'textarea',
            inputLabel: 'Motivo da Exoneração',
            inputPlaceholder: 'Digite o motivo...',
            inputAttributes: { 'aria-label': 'Motivo da exoneração' },
            showCancelButton: true,
            confirmButtonText: 'Confirmar Exoneração',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ef4444'
        });

        if (motivo !== undefined) {
             if (motivo === '') {
                Swal.fire('Aviso', 'Por favor, informe um motivo.', 'warning');
                return;
            }
            try {
                const res = await fetch('../../api/funcionarios.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'exonerate', id, motivo })
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire('Sucesso!', data.message, 'success');
                    tableFunc.ajax.reload(null, false);
                    setTimeout(updateCounts, 1000); // Small delay to ensure table data is updated
                } else {
                    Swal.fire('Erro', data.message, 'error');
                }
            } catch (e) {
                Swal.fire('Erro', 'Falha ao processar exoneração.', 'error');
            }
        }
    }

    async function reintegrarFuncionario(id, nome) {
        const result = await Swal.fire({
            title: 'Reintegrar ' + nome + '?',
            text: "O funcionário voltará a ter acesso ao sistema.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, Reintegrar',
            cancelButtonText: 'Não, Cancelar',
            confirmButtonColor: '#10b981'
        });

        if (result.isConfirmed) {
            try {
                const res = await fetch('../../api/funcionarios.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'reintegrate', id })
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire('Sucesso!', data.message, 'success');
                    tableFunc.ajax.reload(null, false);
                    setTimeout(updateCounts, 1000);
                } else {
                    Swal.fire('Erro', data.message, 'error');
                }
            } catch (e) {
                Swal.fire('Erro', 'Falha ao processar reintegração.', 'error');
            }
        }
    }
</script>

<?php include 'ferias_modal.php'; ?>
<?php include 'layout/footer.php'; ?>
