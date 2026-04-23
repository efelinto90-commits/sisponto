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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

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
</script>

<?php include 'layout/footer.php'; ?>