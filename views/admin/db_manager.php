<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteção de Rota e Nível: Apenas Administrador (Nível 1)
if (!isset($_SESSION['user_id']) || ($_SESSION['user_level'] ?? '') != '1') {
    header('Location: index.php');
    exit;
}

include 'layout/header.php';
?>

<div class="max-w-6xl mx-auto flex flex-col gap-6">

    <!-- Header -->
    <div
        class="bg-white rounded-2xl shadow-sm border border-slate-100 px-6 py-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582 4-8 4" />
                </svg>
                Gerenciamento de Banco de Dados
            </h1>
            <p class="text-sm text-slate-500 mt-1">Exporte, importe e sincronize os dados entre ambientes (local ↔
                produção).</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <span id="statusLocal"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                <span class="w-2 h-2 rounded-full bg-slate-400"></span> Local: Não configurado
            </span>
            <span id="statusProd"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                <span class="w-2 h-2 rounded-full bg-slate-400"></span> Produção: Não configurado
            </span>
        </div>
    </div>

    <!-- Tabs (opcional para organizar) -->
    <div class="flex gap-2 border-b border-slate-200">
        <button onclick="switchTab('db')" id="tab-db" class="px-6 py-3 font-semibold text-sm border-b-2 border-brand-600 text-brand-600 transition-all">
            Banco de Dados
        </button>
        <button onclick="switchTab('files')" id="tab-files" class="px-6 py-3 font-semibold text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-all">
            Ferramenta de Deploy
        </button>
    </div>

    <div id="section-db" class="flex flex-col gap-6">


    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Coluna Esquerda: Tabelas -->
        <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex flex-col gap-3">
            <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-1 flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4" />
                </svg>
                Tabelas para Sincronização
            </h2>
            <p class="text-xs text-slate-400 -mt-2">Selecione para sincronizar de/para o banco.</p>

            <div class="flex flex-col gap-4 flex-1">
                <!-- Origem Local -->
                <div class="flex flex-col gap-2">
                    <h3 class="text-xs font-bold text-blue-600 border-b border-slate-100 pb-1 flex justify-between items-center">
                        Origem: Local (<span id="schemaLabelLocal">...</span>)
                        <div class="flex gap-2 text-[10px] font-normal">
                             <button type="button" onclick="carregarInfo()" class="hover:text-blue-500" title="Atualizar listas">↻</button>
                             <button type="button" onclick="selectAll('local', true)" class="bg-slate-100 px-1.5 py-0.5 rounded hover:bg-slate-200">Todos</button>
                             <button type="button" onclick="selectAll('local', false)" class="bg-slate-100 px-1.5 py-0.5 rounded hover:bg-slate-200">Nenhum</button>
                        </div>
                    </h3>
                    <div id="listLocal" class="space-y-1.5 max-h-[220px] overflow-y-auto pr-2">
                        <div class="text-slate-400 text-sm italic animate-pulse">Carregando tabelas...</div>
                    </div>
                </div>

                <!-- Origem Prod -->
                <div class="flex flex-col gap-2">
                    <h3 class="text-xs font-bold text-emerald-600 border-b border-slate-100 pb-1 flex justify-between items-center">
                        Origem: Produção (<span id="schemaLabelProd">...</span>)
                        <div class="flex gap-2 text-[10px] font-normal">
                             <button type="button" onclick="carregarInfo()" class="hover:text-emerald-500" title="Atualizar listas">↻</button>
                             <button type="button" onclick="selectAll('prod', true)" class="bg-slate-100 px-1.5 py-0.5 rounded hover:bg-slate-200">Todos</button>
                             <button type="button" onclick="selectAll('prod', false)" class="bg-slate-100 px-1.5 py-0.5 rounded hover:bg-slate-200">Nenhum</button>
                        </div>
                    </h3>
                    <div id="listProd" class="space-y-1.5 max-h-[220px] overflow-y-auto pr-2">
                        <div class="text-slate-400 text-sm italic animate-pulse">Carregando tabelas...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Central: Ações -->
        <div class="lg:col-span-2 flex flex-col gap-4">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Config Trabalho (Local) -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                    <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                        Trabalho (Local)
                    </h2>
                    <form onsubmit="salvarConfig(event, 'local')" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Host / IP</label>
                            <input type="text" id="local_host" placeholder="ex: 127.0.0.1 ou localhost"
                                class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-1 focus:ring-brand-500 outline-none bg-slate-50">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nome do Banco</label>
                            <input type="text" id="local_dbname" placeholder="sis-ponto"
                                class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-1 focus:ring-brand-500 outline-none bg-slate-50">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Porta</label>
                            <input type="text" id="local_port" value="5432"
                                class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-1 focus:ring-brand-500 outline-none bg-slate-50">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Esquema (Schema)</label>
                            <input type="text" id="local_schema" placeholder="ponto" onblur="if(this.dataset.old !== this.value) carregarInfo(); this.dataset.old = this.value;"
                                class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-1 focus:ring-brand-500 outline-none bg-slate-50">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Usuário</label>
                            <input type="text" id="local_user" placeholder="postgres"
                                class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-1 focus:ring-brand-500 outline-none bg-slate-50">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Senha</label>
                            <div class="relative">
                                <input type="password" id="local_password" placeholder="••••••••"
                                    class="w-full pl-3 pr-10 py-2 border border-slate-200 rounded-xl text-sm focus:ring-1 focus:ring-brand-500 outline-none bg-slate-50">
                                <button type="button" onclick="togglePassword('local_password', this)"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-brand-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="sm:col-span-2 flex justify-between gap-2 mt-2">
                            <button type="button" onclick="testarConexao('local')"
                                class="flex px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                                Testar
                            </button>
                            <button type="submit"
                                class="flex px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-lg transition-colors">
                                Salvar Local
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Config Produção -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                    <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                        Produção
                    </h2>
                    <form onsubmit="salvarConfig(event, 'prod')" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Host / IP</label>
                            <input type="text" id="prod_host" placeholder="ex: meusite.com"
                                class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-1 focus:ring-brand-500 outline-none bg-slate-50">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nome do Banco</label>
                            <input type="text" id="prod_dbname" placeholder="sis-ponto"
                                class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-1 focus:ring-brand-500 outline-none bg-slate-50">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Porta</label>
                            <input type="text" id="prod_port" value="5432"
                                class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-1 focus:ring-brand-500 outline-none bg-slate-50">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Esquema (Schema)</label>
                            <input type="text" id="prod_schema" placeholder="ponto" onblur="if(this.dataset.old !== this.value) carregarInfo(); this.dataset.old = this.value;"
                                class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-1 focus:ring-brand-500 outline-none bg-slate-50">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Usuário</label>
                            <input type="text" id="prod_user" placeholder="postgres"
                                class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-1 focus:ring-brand-500 outline-none bg-slate-50">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Senha</label>
                            <div class="relative">
                                <input type="password" id="prod_password" placeholder="••••••••"
                                    class="w-full pl-3 pr-10 py-2 border border-slate-200 rounded-xl text-sm focus:ring-1 focus:ring-brand-500 outline-none bg-slate-50">
                                <button type="button" onclick="togglePassword('prod_password', this)"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-brand-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="sm:col-span-2 flex justify-between gap-2 mt-2">
                            <button type="button" onclick="testarConexao('prod')"
                                class="flex px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                                Testar
                            </button>
                            <button type="submit"
                                class="flex px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg transition-colors">
                                Salvar Produção
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Export / Import -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-4">
                <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Exportar / Importar
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="javascript:void(0)" onclick="exportarSQL()"
                        class="flex items-center gap-3 p-4 bg-indigo-50 hover:bg-indigo-100 rounded-xl border border-indigo-100 transition-colors group cursor-pointer">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-slate-800 text-sm">Exportar SQL</div>
                            <div class="text-xs text-slate-500">Download do banco local em .sql</div>
                        </div>
                    </a>

                    <label
                        class="flex items-center gap-3 p-4 bg-amber-50 hover:bg-amber-100 rounded-xl border border-amber-100 transition-colors group cursor-pointer">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-slate-800 text-sm">Importar SQL</div>
                            <div class="text-xs text-slate-500 truncate">Executa SQL no banco local</div>
                        </div>
                        <input type="file" accept=".sql" class="hidden" onchange="importarSQL(this)">
                    </label>
                </div>
            </div>

            <!-- Sync -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl shadow-lg p-6 text-white text-center">
                <h2 class="text-base font-bold uppercase tracking-wider mb-2 flex items-center justify-center gap-2 text-white/90">
                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Sincronização Bidirecional
                </h2>
                <p class="text-xs text-white/60 mb-6 max-w-lg mx-auto">Sincronize as tabelas selecionadas. De Local para Produção, ou de Produção para o banco Local. Apenas registros que não existem serão inseridos e atualizados.</p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button onclick="sincronizar('to_prod')"
                        class="flex flex-col items-center justify-center gap-2 py-4 px-4 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl transition-all shadow-md transform hover:-translate-y-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                        </svg>
                        <div class="text-sm border-b border-brand-400 pb-1 w-full text-center">Local → Produção</div>
                        <div class="text-[10px] text-brand-100 font-normal">Enviar os dados para produção</div>
                    </button>
                    
                    <button onclick="sincronizar('to_local')"
                        class="flex flex-col items-center justify-center gap-2 py-4 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl transition-all shadow-md transform hover:-translate-y-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transform: rotate(180deg);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                        </svg>
                        <div class="text-sm border-b border-emerald-400 pb-1 w-full text-center">Produção → Local</div>
                        <div class="text-[10px] text-emerald-100 font-normal">Baixar os dados da produção</div>
                    </button>
                </div>

                <!-- Progress Bar Container -->
                <div id="syncProgressContainer" class="hidden mt-8 text-left">
                    <div class="flex justify-between items-end mb-2">
                        <div class="flex flex-col">
                            <span id="syncStatusText" class="text-sm font-bold text-white">Iniciando...</span>
                            <span id="syncDetailText" class="text-[10px] text-white/50 lowercase">Aguarde...</span>
                        </div>
                        <span id="syncPercentText" class="text-2xl font-black text-brand-400">0%</span>
                    </div>
                    <div class="w-full h-3 bg-white/10 rounded-full overflow-hidden border border-white/5 p-0.5">
                        <div id="syncProgressBar" class="h-full bg-gradient-to-r from-brand-500 to-emerald-500 rounded-full transition-all duration-300 shadow-[0_0_10px_rgba(20,184,166,0.5)]" style="width: 0%"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</div>

    <!-- Seção de Deploy de Arquivos -->
    <div id="section-files" class="hidden flex flex-col gap-6">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Sidebar: Config e Legenda -->
            <div class="lg:col-span-1 flex flex-col gap-6">
                <!-- Config de Caminhos -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                    <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        Configuração
                    </h2>
                    <form onsubmit="salvarDeployConfig(event)" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Caminho de Trabalho</label>
                            <input type="text" value="<?php echo realpath(__DIR__ . '/../..'); ?>" readonly 
                                class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs bg-slate-50 text-slate-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Caminho de Produção</label>
                            <input type="text" id="deploy_prod_path" placeholder="ex: C:\xampp\htdocs\producao"
                                class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-brand-500 outline-none bg-white">
                            <p class="text-[10px] text-slate-400 mt-1 italic">Pasta onde os arquivos serão copiados.</p>
                        </div>
                        <button type="submit" class="w-full py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-lg transition-colors shadow-sm">
                            Salvar Caminho
                        </button>
                    </form>
                </div>

                <!-- Legenda -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                    <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Legenda
                    </h2>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded bg-emerald-500"></span>
                            <span class="text-xs text-slate-600"><b>Novo:</b> Não existe no destino</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded bg-amber-500"></span>
                            <span class="text-xs text-slate-600"><b>Alterado:</b> Conteúdo diferente</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded bg-slate-200"></span>
                            <span class="text-xs text-slate-600"><b>Igual:</b> Mesmo conteúdo</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded bg-red-100 border border-red-200"></span>
                            <span class="text-xs text-slate-600"><b>Protegido:</b> Não pode ser enviado</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Listagem de Arquivos -->
            <div class="lg:col-span-3 flex flex-col gap-4">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col h-[600px]">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
                        <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Arquivos do Projeto
                        </h2>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" id="filter_changed" checked onchange="renderFileList()" class="w-4 h-4 rounded text-brand-600 border-slate-300">
                                    <span class="text-xs text-slate-500">Somente alterados/novos</span>
                                </label>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="compararArquivos()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                                    Comparar
                                </button>
                                <button onclick="selectAllFilesInList(true)" class="px-3 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-semibold rounded-lg border border-slate-200">
                                    Todos
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-y-auto flex-1 custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-5 py-3 w-10">#</th>
                                    <th class="px-2 py-3">Arquivo</th>
                                    <th class="px-2 py-3">Status</th>
                                    <th class="px-2 py-3 text-right">Tamanho</th>
                                    <th class="px-5 py-3 text-right">Modificado em</th>
                                </tr>
                            </thead>
                            <tbody id="fileListBody" class="text-sm">
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-slate-400 italic">
                                        Configure o caminho de produção e clique em "Comparar" para listar os arquivos.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <div class="text-xs text-slate-500">
                            Selecionados: <span id="selectedFileCount" class="font-bold text-brand-600">0</span>
                        </div>
                        <button onclick="realizarDeploy()" id="btnDeploy" disabled
                            class="px-6 py-2 bg-brand-600 hover:bg-brand-500 disabled:bg-slate-300 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl transition-all shadow-md transform hover:-translate-y-0.5 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Enviar Selecionados para Produção
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('svg');
        if (input.type === 'password') {
            input.type = 'text';
        } else {
            input.type = 'password';
        }
    }

    function getSelectedTables(env) {
        return [...document.querySelectorAll(`.check-${env}:checked`)].map(cb => cb.value);
    }

    function selectAll(env, state) {
        document.querySelectorAll(`.check-${env}`).forEach(cb => cb.checked = state);
    }

    async function carregarInfo() {
        const res = await fetch('../../api/db_manager.php?action=info');
        const data = await res.json();
        if (!data.success) return;

        // Tabelas Local
        const listLocal = document.getElementById('listLocal');
        listLocal.innerHTML = '';
        if(data.tables_local) {
            for (const [tabela, count] of Object.entries(data.tables_local)) {
                const div = document.createElement('label');
                div.className = 'flex items-center justify-between gap-2 p-1.5 rounded hover:bg-slate-50 cursor-pointer text-sm border-b border-slate-50';
                div.innerHTML = `
                    <div class="flex items-center gap-2">
                        <input type="checkbox" class="check-local w-4 h-4 rounded text-blue-600 border-slate-300" value="${tabela}" checked>
                        <span class="text-slate-700 leading-none">${tabela}</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded-full">${count} rows</span>
                `;
                listLocal.appendChild(div);
            }
        } else {
            listLocal.innerHTML = '<div class="text-xs text-slate-400">Nenhuma tabela encontrada.</div>';
        }

        // Tabelas Prod
        const listProd = document.getElementById('listProd');
        listProd.innerHTML = '';
        if(data.tables_prod) {
            for (const [tabela, count] of Object.entries(data.tables_prod)) {
                const div = document.createElement('label');
                div.className = 'flex items-center justify-between gap-2 p-1.5 rounded hover:bg-slate-50 cursor-pointer text-sm border-b border-slate-50';
                div.innerHTML = `
                    <div class="flex items-center gap-2">
                        <input type="checkbox" class="check-prod w-4 h-4 rounded text-emerald-600 border-slate-300" value="${tabela}" checked>
                        <span class="text-slate-700 leading-none">${tabela}</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded-full">${count} rows</span>
                `;
                listProd.appendChild(div);
            }
        } else {
            listProd.innerHTML = '<div class="text-xs text-slate-400">Nenhuma tabela encontrada.</div>';
        }

        ['local', 'prod'].forEach(env => {
            if (data[env] && data[env].host) {
                document.getElementById(`${env}_host`).value = data[env].host || '';
                document.getElementById(`${env}_port`).value = data[env].port || '5432';
                document.getElementById(`${env}_dbname`).value = data[env].dbname || '';
                document.getElementById(`${env}_user`).value = data[env].user || '';
                document.getElementById(`${env}_password`).value = data[env].password || '';
                const schemaValue = data[env].schema || 'ponto';
                document.getElementById(`${env}_schema`).value = schemaValue;
                document.getElementById(`${env}_schema`).dataset.old = schemaValue;
                document.getElementById(`schemaLabel${env === 'local' ? 'Local' : 'Prod'}`).innerText = schemaValue;

                const color = env === 'local' ? 'blue' : 'emerald';
                const label = env === 'local' ? 'Local' : 'Produção';

                document.getElementById(`status${env === 'local' ? 'Local' : 'Prod'}`).innerHTML = `
                    <span class="w-2 h-2 rounded-full bg-${color}-400"></span> ${label}: ${data[env].host}
                `;
                document.getElementById(`status${env === 'local' ? 'Local' : 'Prod'}`).className = `inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-${color}-50 text-${color}-700 border border-${color}-200`;
            }
        });
    }

    async function salvarConfig(e, env) {
        e.preventDefault();
        const payload = {
            host: document.getElementById(`${env}_host`).value,
            port: document.getElementById(`${env}_port`).value,
            dbname: document.getElementById(`${env}_dbname`).value,
            user: document.getElementById(`${env}_user`).value,
            password: document.getElementById(`${env}_password`).value,
            schema: document.getElementById(`${env}_schema`).value,
        };

        const res = await fetch(`../../api/db_manager.php?action=save_${env}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
            carregarInfo();
        } else {
            Swal.fire('Erro', data.message, 'error');
        }
    }

    async function testarConexao(env) {
        Swal.fire({ title: 'Testando conexão...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        const payload = {
            host: document.getElementById(`${env}_host`).value,
            port: document.getElementById(`${env}_port`).value,
            dbname: document.getElementById(`${env}_dbname`).value,
            user: document.getElementById(`${env}_user`).value,
            password: document.getElementById(`${env}_password`).value,
            schema: document.getElementById(`${env}_schema`).value,
        };

        const saveRes = await fetch(`../../api/db_manager.php?action=save_${env}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const res = await fetch(`../../api/db_manager.php?action=test_conexao&env=${env}`, {
            method: 'GET'
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire('Conexão OK!', `O servidor ${env === 'local' ? 'Local' : 'de Produção'} respondeu com sucesso!`, 'success');
            carregarInfo();
        } else {
            Swal.fire('Erro', data.message, 'error');
        }
    }

    async function sincronizar(direction) {
        const envOrigem = direction === 'to_prod' ? 'local' : 'prod';
        const msgOrigem = direction === 'to_prod' ? 'Local' : 'Produção';
        const msgDestino = direction === 'to_prod' ? 'Produção' : 'Local';
        
        const tabelas = getSelectedTables(envOrigem);
        if (tabelas.length === 0) {
            Swal.fire('Atenção', `Selecione ao menos uma tabela na lista "Origem: ${msgOrigem}" para sincronizar.`, 'warning');
            return;
        }

        const confirm = await Swal.fire({
            title: `Sincronizar [${msgOrigem} → ${msgDestino}]?`,
            html: `Tabelas selecionadas da origem: <b>${tabelas.length}</b><br><br>Os dados serão enviados sequencialmente com acompanhamento em tempo real.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Iniciar Sincronização',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: direction === 'to_prod' ? '#0284c7' : '#059669'
        });
        if (!confirm.isConfirmed) return;

        // Reset e Mostrar UI de Progresso
        const progressContainer = document.getElementById('syncProgressContainer');
        const progressBar = document.getElementById('syncProgressBar');
        const statusText = document.getElementById('syncStatusText');
        const detailText = document.getElementById('syncDetailText');
        const percentText = document.getElementById('syncPercentText');

        progressContainer.classList.remove('hidden');
        progressBar.style.width = '0%';
        percentText.innerText = '0%';
        statusText.innerText = 'Preparando sincronização...';
        detailText.innerText = 'Conectando aos servidores...';

        const action = direction === 'to_prod' ? 'sync_to_prod' : 'sync_to_local';
        const results = [];
        let successCount = 0;
        let totalRecords = 0;

        // Desativar botões durante a sincronização
        const buttons = document.querySelectorAll('button[onclick^="sincronizar"]');
        buttons.forEach(b => b.disabled = true);

        for (let i = 0; i < tabelas.length; i++) {
            const tabela = tabelas[i];
            const currentPercent = Math.round((i / tabelas.length) * 100);
            
            progressBar.style.width = `${currentPercent}%`;
            percentText.innerText = `${currentPercent}%`;
            statusText.innerText = `Sincronizando ${i + 1} de ${tabelas.length}`;
            detailText.innerText = `Processando tabela: ${tabela}...`;

            try {
                const res = await fetch(`../../api/db_manager.php?action=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tabelas: [tabela] }) // Envia apenas uma tabela por vez
                });
                const data = await res.json();
                
                if (data.success) {
                    successCount++;
                    // Tenta extrair quantidade de registros da mensagem se houver
                    const match = data.message.match(/(\d+) registros/);
                    if (match) totalRecords += parseInt(match[1]);
                    results.push(`<span class="text-emerald-600 font-bold">✓ ${tabela}:</span> Sucesso`);
                } else {
                    results.push(`<span class="text-red-600 font-bold">✗ ${tabela}:</span> ${data.message}`);
                }
            } catch (e) {
                results.push(`<span class="text-red-600 font-bold">✗ ${tabela}:</span> Erro de conexão (${e.message})`);
            }
        }

        // Finalizar UI
        progressBar.style.width = '100%';
        percentText.innerText = '100%';
        statusText.innerText = 'Sincronização Concluída';
        detailText.innerText = `Total: ${successCount} tabelas com sucesso.`;
        buttons.forEach(b => b.disabled = false);

        Swal.fire({
            icon: successCount === tabelas.length ? 'success' : 'warning',
            title: successCount === tabelas.length ? 'Finalizado com Sucesso!' : 'Finalizado com Observações',
            html: `
                <div class="text-left mt-2 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                    <div class="mb-4 p-3 bg-slate-50 rounded-lg border border-slate-100 flex justify-around text-center">
                        <div>
                            <div class="text-[10px] text-slate-400 uppercase font-bold">Tabelas</div>
                            <div class="text-lg font-black text-slate-800">${successCount}/${tabelas.length}</div>
                        </div>
                        <div class="border-l border-slate-200"></div>
                        <div>
                            <div class="text-[10px] text-slate-400 uppercase font-bold">Novos Registros</div>
                            <div class="text-lg font-black text-emerald-600">${totalRecords}</div>
                        </div>
                    </div>
                    ${results.join('<br>')}
                </div>
            `,
            confirmButtonColor: '#0284c7',
            width: '600px'
        });

        carregarInfo();
    }

    function exportarSQL() {
        const schema = document.getElementById('local_schema').value || 'ponto';
        window.open(`../../api/db_manager.php?action=export&schema=${schema}`, '_blank');
    }

    async function importarSQL(input) {
        const file = input.files[0];
        if (!file) return;

        const confirm = await Swal.fire({
            title: 'Importar SQL?',
            html: `Arquivo: <b>${file.name}</b><br>Os comandos SQL serão executados no banco <b>local</b>. Confirme antes de prosseguir.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Importar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#f59e0b'
        });
        if (!confirm.isConfirmed) { input.value = ''; return; }

        Swal.fire({ title: 'Importando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        const fd = new FormData();
        fd.append('action', 'import');
        fd.append('sql_file', file);

        const res = await fetch('../../api/db_manager.php', { method: 'POST', body: fd });
        const data = await res.json();

        Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Importado!' : 'Erro', text: data.message });
        input.value = '';
    }

    // --- FILE DEPLOY LOGIC ---
    let allFiles = [];

    function switchTab(tab) {
        document.getElementById('section-db').classList.toggle('hidden', tab !== 'db');
        document.getElementById('section-files').classList.toggle('hidden', tab !== 'files');
        
        document.getElementById('tab-db').className = tab === 'db' 
            ? "px-6 py-3 font-semibold text-sm border-b-2 border-brand-600 text-brand-600 transition-all"
            : "px-6 py-3 font-semibold text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-all";
            
        document.getElementById('tab-files').className = tab === 'files' 
            ? "px-6 py-3 font-semibold text-sm border-b-2 border-brand-600 text-brand-600 transition-all"
            : "px-6 py-3 font-semibold text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-all";

        if (tab === 'files' && allFiles.length === 0) {
            carregarDeployConfig();
        }
    }

    async function carregarDeployConfig() {
        const res = await fetch('../../api/file_deploy.php?action=get_config');
        const data = await res.json();
        if (data.success && data.config.prod_path) {
            document.getElementById('deploy_prod_path').value = data.config.prod_path;
            compararArquivos();
        }
    }

    async function salvarDeployConfig(e) {
        e.preventDefault();
        const prodPath = document.getElementById('deploy_prod_path').value;
        const res = await fetch('../../api/file_deploy.php?action=save_config', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prod_path: prodPath })
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
            compararArquivos();
        } else {
            Swal.fire('Erro', data.message, 'error');
        }
    }

    async function compararArquivos() {
        Swal.fire({ title: 'Comparando arquivos...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        try {
            const res = await fetch('../../api/file_deploy.php?action=compare');
            const data = await res.json();
            if (data.success) {
                allFiles = data.files;
                renderFileList();
                Swal.close();
            } else {
                Swal.fire('Erro', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Erro', 'Falha ao conectar na API de deploy.', 'error');
        }
    }

    function renderFileList() {
        const body = document.getElementById('fileListBody');
        const showOnlyChanged = document.getElementById('filter_changed').checked;
        body.innerHTML = '';

        const filtered = allFiles.filter(f => !showOnlyChanged || f.status === 'novo' || f.status === 'alterado');

        if (filtered.length === 0) {
            body.innerHTML = `<tr><td colspan="5" class="px-5 py-10 text-center text-slate-400 italic">Nenhum arquivo encontrado com os filtros atuais.</td></tr>`;
            updateSelectedCount();
            return;
        }

        filtered.forEach(f => {
            const tr = document.createElement('tr');
            tr.className = "hover:bg-slate-50 border-b border-slate-50 group";
            
            let statusBadge = '';
            let rowClass = '';
            if (f.protected) {
                statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] bg-red-100 text-red-700 font-bold uppercase">Protegido</span>';
                rowClass = 'opacity-60 grayscale-[0.5]';
            } else {
                switch(f.status) {
                    case 'novo': statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 text-emerald-700 font-bold uppercase">Novo</span>'; break;
                    case 'alterado': statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-700 font-bold uppercase">Alterado</span>'; break;
                    case 'igual': statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 text-slate-500 font-bold uppercase">Igual</span>'; break;
                }
            }

            const fileSize = (f.size / 1024).toFixed(1) + ' KB';
            
            tr.innerHTML = `
                <td class="px-5 py-2.5">
                    <input type="checkbox" class="file-check w-4 h-4 rounded text-brand-600 border-slate-300" 
                        value="${f.path}" ${f.protected ? 'disabled' : (f.status !== 'igual' ? 'checked' : '')} onchange="updateSelectedCount()">
                </td>
                <td class="px-2 py-2.5 font-mono text-xs ${f.protected ? 'text-slate-400' : 'text-slate-700'}">${f.path}</td>
                <td class="px-2 py-2.5">${statusBadge}</td>
                <td class="px-2 py-2.5 text-right text-xs text-slate-400">${fileSize}</td>
                <td class="px-5 py-2.5 text-right text-[10px] text-slate-400">${f.mtime}</td>
            `;
            body.appendChild(tr);
        });

        updateSelectedCount();
    }

    function selectAllFilesInList(state) {
        document.querySelectorAll('.file-check:not(:disabled)').forEach(cb => cb.checked = state);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const selected = document.querySelectorAll('.file-check:checked').length;
        document.getElementById('selectedFileCount').innerText = selected;
        document.getElementById('btnDeploy').disabled = selected === 0;
    }

    async function realizarDeploy() {
        const selectedFiles = [...document.querySelectorAll('.file-check:checked')].map(cb => cb.value);
        if (selectedFiles.length === 0) return;

        const confirm = await Swal.fire({
            title: 'Confirmar Deploy?',
            html: `Você está prestes a copiar <b>${selectedFiles.length}</b> arquivos para o ambiente de produção.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, Realizar Deploy',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0ea5e9'
        });

        if (!confirm.isConfirmed) return;

        Swal.fire({ title: 'Realizando deploy...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            const res = await fetch('../../api/file_deploy.php?action=deploy', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ files: selectedFiles })
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Deploy Concluído!',
                    html: `<div class="text-left text-xs max-h-[300px] overflow-y-auto mt-4 p-2 bg-slate-50 rounded border">${data.log.join('<br>')}</div>`,
                    confirmButtonColor: '#0ea5e9'
                });
                compararArquivos();
            } else {
                Swal.fire('Erro', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Erro', 'Falha ao processar deploy.', 'error');
        }
    }

    carregarInfo();
</script>


<?php include 'layout/footer.php'; ?>