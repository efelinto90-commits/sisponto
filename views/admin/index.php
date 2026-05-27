<?php include 'layout/header.php'; ?>

<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div
        class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Dashboard Principal</h1>
            <p class="text-sm font-medium text-slate-500 mt-1">Resumo das atividades e registros de ponto de hoje.</p>
        </div>
        <div class="flex items-center gap-3 bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
            <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span id="hojeLabel" class="text-sm font-bold text-slate-700">Carregando data...</span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">

        <!-- Total de Funcionários -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-start gap-4">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-500">Funcionários Ativos</p>
                <h3 id="statFuncionarios" class="text-3xl font-black text-slate-800 mt-1">--</h3>
            </div>
        </div>

        <!-- Presentes Hoje -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-start gap-4 ring-1 ring-emerald-50 relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-50 rounded-full mix-blend-multiply opacity-50">
            </div>
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl relative z-10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="relative z-10">
                <p class="text-sm font-semibold text-slate-500">Registraram Ponto Hoje</p>
                <h3 id="statRegistrosHoje" class="text-3xl font-black text-slate-800 mt-1">--</h3>
            </div>
        </div>

        <!-- Atrasos Detectados -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-start gap-4">
            <div class="p-3 bg-orange-50 text-orange-500 rounded-xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-500">Atrasos Detectados</p>
                <h3 id="statAtrasos" class="text-3xl font-black text-slate-800 mt-1">--</h3>
            </div>
        </div>

        <!-- Funcionários Afastados -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-start relative overflow-hidden transition-all duration-300 h-fit" id="cardAfastados">
            <div class="flex items-start justify-between gap-2 cursor-pointer" onclick="toggleAfastadosDetalles()">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Funcionários Afastados</p>
                        <h3 id="statAfastados" class="text-3xl font-black text-slate-800 mt-1">--</h3>
                    </div>
                </div>
                <div class="text-slate-400 hover:text-slate-600 transition-colors p-1" id="chevronAfastados">
                    <svg class="w-5 h-5 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Expandable Panel -->
            <div id="afastadosListContainer" class="max-h-0 overflow-hidden transition-all duration-500 ease-in-out">
                <div id="afastadosList" class="space-y-3 mt-3 text-xs max-h-60 overflow-y-auto pr-1">
                    <p class="text-slate-400 italic text-center py-2 bg-slate-50 rounded-lg animate-pulse">Carregando...</p>
                </div>
            </div>
        </div>

        <!-- Justificativas com Anexo -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-start relative overflow-hidden transition-all duration-300 h-fit" id="cardJustificativas">
            <div class="flex items-start justify-between gap-2 cursor-pointer" onclick="toggleJustificativasDetalles()">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Justificativas com Anexo</p>
                        <h3 id="statJustificativas" class="text-3xl font-black text-slate-800 mt-1">--</h3>
                    </div>
                </div>
                <div class="text-slate-400 hover:text-slate-600 transition-colors p-1" id="chevronJustificativas">
                    <svg class="w-5 h-5 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Expandable Panel -->
            <div id="justificativasListContainer" class="max-h-0 overflow-hidden transition-all duration-500 ease-in-out">
                <div id="justificativasList" class="space-y-3 mt-3 text-xs max-h-60 overflow-y-auto pr-1">
                    <p class="text-slate-400 italic text-center py-2 bg-slate-50 rounded-lg animate-pulse">Carregando...</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Recent Activity Table Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-800">Últimos Registros (Hoje)</h3>
            <button onclick="carregarDashboard()"
                class="text-brand-600 hover:text-brand-800 transition-colors p-2 bg-brand-50 rounded-lg hover:bg-brand-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
            </button>
        </div>
        <div class="p-6 overflow-x-auto">
            <table class="w-full text-left border-collapse" id="ultimosRegistrosTable">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th
                            class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider rounded-l-lg border-b border-slate-200">
                            Funcionário</th>
                        <th
                            class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            Matrícula</th>
                        <th
                            class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            Status Atraso (Hoje)</th>
                        <th
                            class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider rounded-r-lg border-b border-slate-200 text-right">
                            Momentos Ponto</th>
                    </tr>
                </thead>
                <tbody id="registrosList" class="divide-y divide-slate-100 bg-white">
                    <!-- Dinâmico via JS -->
                    <tr>
                        <td colspan="4" class="text-center py-6 text-slate-400">Carregando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {

        // Formatar label de hoje
        const today = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'full' }).format(new Date());
        document.getElementById('hojeLabel').textContent = today.charAt(0).toUpperCase() + today.slice(1);

        carregarDashboard();
    });

    async function carregarDashboard() {
        try {
            const res = await fetch('../../api/dashboard.php');
            const data = await res.json();

            if (data.success) {
                document.getElementById('statFuncionarios').textContent = data.stats.total_funcionarios;
                document.getElementById('statRegistrosHoje').textContent = data.stats.total_registros_hoje;
                document.getElementById('statAtrasos').textContent = data.stats.total_atrasos_hoje;

                // Atualizar card de afastados
                document.getElementById('statAfastados').textContent = data.stats.total_afastados_hoje ?? 0;
                
                const afastadosList = document.getElementById('afastadosList');
                afastadosList.innerHTML = '';
                
                if (!data.afastados_hoje || data.afastados_hoje.length === 0) {
                    afastadosList.innerHTML = '<p class="text-slate-400 italic text-center py-2 bg-slate-50/50 border border-slate-100 rounded-lg">Nenhum afastado hoje.</p>';
                } else {
                    data.afastados_hoje.forEach(af => {
                        const motivo = af.tipo_afastamento === 'outros' ? (af.motivo_especifico || 'Outros') : (af.tipo_afastamento || 'Não especificado');
                        const dtIniParts = af.data_inicio.split('-');
                        const dtFimParts = af.data_fim.split('-');
                        const dtIniStr = `${dtIniParts[2]}/${dtIniParts[1]}/${dtIniParts[0]}`;
                        const dtFimStr = `${dtFimParts[2]}/${dtFimParts[1]}/${dtFimParts[0]}`;
                        
                        afastadosList.innerHTML += `
                            <div class="p-2.5 bg-slate-50/70 border border-slate-100 hover:bg-amber-50/20 hover:border-amber-100 rounded-xl transition-all flex flex-col gap-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-slate-800 truncate" title="${af.nome}">${af.nome}</span>
                                    <span class="px-2 py-0.5 text-[9px] font-black uppercase bg-amber-50 text-amber-700 rounded border border-amber-200 shrink-0">${motivo}</span>
                                </div>
                                <span class="text-[10px] text-slate-500 font-medium">Período: ${dtIniStr} - ${dtFimStr}</span>
                            </div>
                        `;
                    });
                }

                // Atualizar card de justificativas com anexo
                window.currentJustificativasAnexo = data.justificativas_anexo ?? [];
                document.getElementById('statJustificativas').textContent = data.stats.total_justificativas_anexo ?? 0;
                
                const justificativasList = document.getElementById('justificativasList');
                justificativasList.innerHTML = '';
                
                if (!data.justificativas_anexo || data.justificativas_anexo.length === 0) {
                    justificativasList.innerHTML = '<p class="text-slate-400 italic text-center py-2 bg-slate-50/50 border border-slate-100 rounded-lg">Nenhuma justificativa pendente.</p>';
                } else {
                    data.justificativas_anexo.forEach(j => {
                        const dataParts = j.data.split('-');
                        const dataStr = `${dataParts[2]}/${dataParts[1]}/${dataParts[0]}`;
                        
                        let anexosHtml = '';
                        if (j.anexos && j.anexos.length > 0) {
                            j.anexos.forEach((path, idx) => {
                                anexosHtml += `
                                    <a href="../../${path}" target="_blank" class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-indigo-50 text-indigo-600 border border-indigo-100 rounded text-[9px] font-black hover:bg-indigo-100 transition-colors" title="Ver anexo ${idx + 1}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        Doc ${idx + 1}
                                    </a>
                                `;
                            });
                        }
                        
                        justificativasList.innerHTML += `
                            <div class="p-2.5 bg-slate-50/70 border border-slate-100 hover:bg-indigo-50/10 hover:border-indigo-100 rounded-xl transition-all flex flex-col gap-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-slate-800 truncate" title="${j.nome}">${j.nome}</span>
                                    <span class="px-2 py-0.5 text-[9px] font-black uppercase bg-indigo-50 text-indigo-700 rounded border border-indigo-200 shrink-0">${j.tipo_justificativa}</span>
                                </div>
                                <div class="flex justify-between items-center gap-2 mt-0.5">
                                    <span class="text-[10px] text-slate-500 font-medium">${dataStr}</span>
                                    <div class="flex gap-1 shrink-0">
                                        ${anexosHtml}
                                    </div>
                                </div>
                                ${j.justificativa ? `<p class="text-[10px] text-slate-600 bg-white/50 p-1.5 rounded-lg border border-slate-100 italic mt-0.5 leading-tight truncate" title="${j.justificativa}">${j.justificativa}</p>` : ''}
                            </div>
                        `;
                    });

                    // Add full view button at the bottom of the list
                    justificativasList.innerHTML += `
                        <div class="pt-2 border-t border-slate-100/50">
                            <button onclick="abrirModalJustificativas()" class="w-full text-center py-2 text-xs font-bold text-indigo-600 bg-indigo-50/50 hover:bg-indigo-100 hover:text-indigo-700 rounded-xl transition-all flex items-center justify-center gap-1.5 border border-indigo-100/40">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Visualização Plena
                            </button>
                        </div>
                    `;
                }

                const tbody = document.getElementById('registrosList');
                tbody.innerHTML = '';

                if (data.ultimos_registros.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-slate-400 font-medium bg-slate-50 rounded-xl">Nenhum registro encontrado para hoje.</td></tr>';
                } else {
                    data.ultimos_registros.forEach(r => {

                        // Verificar se há algum atraso
                        const temAtraso = r.atrasou_primeiro_ponto || r.atrasou_segundo_ponto || r.atrasou_terceiro_ponto || r.atrasou_quarto_ponto;
                        const statusBadge = temAtraso
                            ? `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Com Atraso</span>`
                            : `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Pontual</span>`;

                        const horinhas = [r.primeiro_ponto, r.segundo_ponto, r.terceiro_ponto, r.quarto_ponto]
                            .filter(Boolean)
                            .map(h => `<span class="inline-block bg-slate-100 text-slate-700 px-2 py-1 rounded-md text-sm font-semibold m-0.5">${h.substring(0, 5)}</span>`)
                            .join('');

                        tbody.innerHTML += `
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs ring-2 ring-white">
                                            ${r.nome.substring(0, 2).toUpperCase()}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-bold text-slate-800">${r.nome}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-mono text-slate-600">${r.matricula}</td>
                                <td class="px-4 py-4 whitespace-nowrap">${statusBadge}</td>
                                <td class="px-4 py-4 text-right">${horinhas || '-'}</td>
                            </tr>
                        `;
                    });
                }
            }

        } catch (e) {
            console.error(e);
            Swal.fire('Opa', 'Erro ao carregar os dados do dashboard.', 'error');
        }
    }

    function toggleAfastadosDetalles() {
        const container = document.getElementById('afastadosListContainer');
        const chevron = document.getElementById('chevronAfastados').querySelector('svg');
        
        if (container.classList.contains('max-h-0')) {
            container.classList.remove('max-h-0');
            container.classList.add('max-h-96', 'mt-3', 'pt-3', 'border-t', 'border-slate-100');
            chevron.classList.add('rotate-180');
        } else {
            container.classList.remove('max-h-96', 'mt-3', 'pt-3', 'border-t', 'border-slate-100');
            container.classList.add('max-h-0');
            chevron.classList.remove('rotate-180');
        }
    }

    function toggleJustificativasDetalles() {
        const container = document.getElementById('justificativasListContainer');
        const chevron = document.getElementById('chevronJustificativas').querySelector('svg');
        
        if (container.classList.contains('max-h-0')) {
            container.classList.remove('max-h-0');
            container.classList.add('max-h-96', 'mt-3', 'pt-3', 'border-t', 'border-slate-100');
            chevron.classList.add('rotate-180');
        } else {
            container.classList.remove('max-h-96', 'mt-3', 'pt-3', 'border-t', 'border-slate-100');
            container.classList.add('max-h-0');
            chevron.classList.remove('rotate-180');
        }
    }

    function abrirModalJustificativas() {
        if (!window.currentJustificativasAnexo || window.currentJustificativasAnexo.length === 0) {
            Swal.fire({
                title: 'Nenhuma justificativa',
                text: 'Não há justificativas com anexo pendentes para hoje.',
                icon: 'info',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        let rowsHtml = '';
        window.currentJustificativasAnexo.forEach(j => {
            const dataParts = j.data.split('-');
            const dataStr = `${dataParts[2]}/${dataParts[1]}/${dataParts[0]}`;
            
            let anexosHtml = '';
            if (j.anexos && j.anexos.length > 0) {
                j.anexos.forEach((path, idx) => {
                    anexosHtml += `
                        <a href="../../${path}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 hover:text-indigo-800 border border-indigo-200 rounded-lg text-xs font-black transition-colors" title="Abrir Documento ${idx + 1}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                            </svg>
                            Anexo ${idx + 1}
                        </a>
                    `;
                });
            }

            rowsHtml += `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-4 whitespace-normal">
                        <div class="flex items-center">
                            <div class="h-9 w-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs ring-2 ring-white">
                                ${j.nome.substring(0, 2).toUpperCase()}
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-bold text-slate-800">${j.nome}</p>
                                <p class="text-[10px] text-slate-400 font-mono">Matrícula: ${j.matricula}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-slate-600">${dataStr}</td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <span class="px-2 py-0.5 text-[10px] font-black uppercase bg-indigo-100 text-indigo-700 rounded-md border border-indigo-200">${j.tipo_justificativa}</span>
                    </td>
                    <td class="px-4 py-4 text-slate-600 text-xs font-medium italic">
                        <div class="bg-slate-50/70 p-3 rounded-xl border border-slate-100 leading-relaxed whitespace-normal break-words">
                            "${j.justificativa}"
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="flex flex-wrap gap-1.5">
                            ${anexosHtml || '<span class="text-slate-300 italic text-xs">Sem documentos</span>'}
                        </div>
                    </td>
                </tr>
            `;
        });

        Swal.fire({
            title: `
                <div class="flex items-center gap-3 text-left pb-4 border-b border-slate-100">
                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Visualização Plena das Justificativas</h2>
                        <p class="text-xs text-slate-400 font-semibold mt-0.5">Listagem detalhada das justificativas com anexos recebidas hoje.</p>
                    </div>
                </div>
            `,
            html: `
                <div class="overflow-x-auto max-h-[60vh] mt-4 border border-slate-100 rounded-2xl">
                    <table class="w-full text-left border-collapse table-fixed min-w-[1100px]">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200">
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider w-[26%]">Colaborador</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider w-[10%]">Data do Ponto</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider w-[11%]">Tipo</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider w-[38%]">Explicação/Texto</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider w-[15%]">Documentos Anexados</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            ${rowsHtml}
                        </tbody>
                    </table>
                </div>
            `,
            width: '90%',
            showConfirmButton: true,
            confirmButtonText: 'Fechar Painel',
            confirmButtonColor: '#4f46e5',
            customClass: {
                popup: 'rounded-3xl shadow-2xl p-6 bg-white border border-slate-100 max-w-[1350px]',
                confirmButton: 'rounded-xl px-6 py-2.5 font-bold text-sm transition-all hover:bg-indigo-700'
            }
        });
    }
</script>

<?php include 'layout/footer.php'; ?>