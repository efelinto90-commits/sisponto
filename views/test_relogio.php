<?php include 'layout/header.php'; ?>

<!-- Fundo interativo do relógio -->
<div
    class="flex-1 flex items-center justify-center relative overflow-hidden bg-gradient-to-br from-brand-900 via-brand-600 to-sky-500">

    <!-- Design Elements em background -->
    <div
        class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCI+PHBhdGggZD0iTTAgMGgyeTR2MjRIMGoiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wMSkiLz48L3N2Zz4=')] opacity-20">
    </div>

    <div class="w-full max-w-md relative z-10 px-4">

        <div class="glass rounded-3xl p-8 shadow-2xl relative overflow-hidden border border-white/20">
            <!-- Header Card -->
            <div class="text-center mb-10">
                <h1
                    class="text-4xl font-extrabold text-slate-800 tracking-tight gap-2 flex justify-center items-center">
                    <svg class="w-8 h-8 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Ponto Digital
                </h1>
                <p class="text-slate-500 text-sm mt-2 font-medium">FUNAD • Controle de Acesso</p>
            </div>

            <title>Relógio de Ponto - Biometria</title>
            <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
            <div class="text-center mb-8">
                <div id="liveClock"
                    class="text-6xl font-black text-brand-600 tracking-tighter tabular-nums drop-shadow-sm transition-all">
                    00:00:00</div>
                <div id="liveDate" class="text-sm font-medium text-slate-500 mt-1 uppercase tracking-widest">Segunda, 01
                    de Janeiro</div>
            </div>

            <!-- Input de Matrícula -->
            <form id="pontoForm" onsubmit="registrarPonto(event)" class="space-y-6">
                <div class="space-y-2">
                    <label for="matricula" class="block text-sm font-bold text-slate-700">Número da Matrícula</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                            </svg>
                        </div>
                        <input type="text" id="matricula" name="matricula"
                            class="block w-full pl-10 pr-3 py-4 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 sm:text-lg font-medium transition-colors shadow-sm text-center tracking-widest uppercase"
                            placeholder="SUA MATRÍCULA" autocomplete="off" oninput="buscarMatriculas(this.value)"
                            onblur="setTimeout(fecharSugestoes, 200)">

                        <!-- Dropdown de sugestões -->
                        <div id="matriculaSugestoes"
                            class="absolute left-0 right-0 top-full mt-1 z-50 hidden bg-white rounded-xl shadow-xl border border-slate-100 overflow-hidden max-h-52 overflow-y-auto">
                        </div>
                    </div>
                </div>

                <div id="containerSenhaRelogio" class="mb-6 hidden">
                    <label for="senha" class="sr-only">Senha</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                </path>
                            </svg>
                        </div>
                        <input type="password" id="senha" name="senha"
                            class="block w-full pl-10 pr-12 py-4 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 sm:text-lg font-medium transition-colors shadow-sm text-center tracking-widest"
                            placeholder="SUA SENHA" autocomplete="current-password">
                        <button type="button" onclick="togglePassword('senha', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-slate-400 hover:text-brand-600 transition-colors focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" id="btnSubmit"
                    class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-md text-lg font-bold text-white bg-brand-600 hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all hover:-translate-y-0.5 transform active:translate-y-0 relative overflow-hidden group">
                    <span class="relative z-10 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Registrar Ponto
                    </span>
                    <div
                        class="absolute inset-0 h-full w-full opacity-0 group-hover:opacity-20 bg-white transition-opacity">
                    </div>
                </button>
            </form>

            <div id="containerBiometria" class="hidden">
                <div class="relative flex py-5 items-center">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span
                        class="flex-shrink-0 mx-4 text-slate-400 text-xs font-semibold uppercase tracking-widest">ou</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>

                <button type="button" id="btnBiometria" onclick="abrirModalBiometria()"
                    class="w-full flex justify-center py-4 px-4 border border-brand-500 rounded-xl shadow-sm text-lg font-bold text-brand-600 bg-brand-50 hover:bg-brand-100 focus:outline-none transition-all outline-none">
                    <span class="flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4">
                            </path>
                        </svg>
                        Usar Biometria
                    </span>
                </button>
            </div>

            <!-- Modal Biometria Unificado -->
            <div id="modalBiometria" class="fixed inset-0 z-[999] hidden" aria-labelledby="modal-title" role="dialog"
                aria-modal="true">
                <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity"
                    onclick="fecharModalBiometria()"></div>
                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <div
                            class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-sm border border-slate-100 p-0 flex flex-col">

                            <div
                                class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4">
                                        </path>
                                    </svg>
                                    Acesso Biométrico
                                </h3>
                                <button onclick="fecharModalBiometria()"
                                    class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Tabs -->
                            <div class="px-5 pt-4 flex gap-2">
                                <button id="tabFacial" onclick="mudarModoBiometria('facial')"
                                    class="flex-1 py-2 text-xs font-bold rounded-xl transition-all border border-transparent bg-brand-50 text-brand-600 border-brand-100">
                                    Facial
                                </button>
                                <button id="tabDigital" onclick="mudarModoBiometria('digital')"
                                    class="flex-1 py-2 text-xs font-bold rounded-xl transition-all border border-transparent text-slate-500 hover:bg-slate-50">
                                    Digital (Sensor)
                                </button>
                            </div>

                            <input type="hidden" id="bio_modo" value="facial">
                            <input type="hidden" id="bio_matricula" value="">

                            <div class="p-5 flex flex-col items-center">
                                <!-- Area Facial -->
                                <div id="areaFacial" class="w-full flex flex-col items-center">
                                    <div class="relative w-48 h-60 bg-black rounded-[50%] overflow-hidden shadow-inner mb-6 flex items-center justify-center border-4 border-brand-400"
                                        id="videoContainer">
                                        <video id="videoFeed" autoplay playsinline muted
                                            class="absolute h-full w-full object-cover z-10 hidden" style="transform: scaleX(-1);"></video>
                                        <canvas id="videoCanvas" class="absolute h-full w-full object-cover z-10 hidden" style="transform: scaleX(-1);"></canvas>
                                        <div id="guideFacial"
                                            class="absolute inset-0 z-20 pointer-events-none flex items-center justify-center">
                                            <div
                                                class="w-32 h-48 border-[3px] border-dashed border-brand-400/60 rounded-[50%] animate-pulse shadow-[0_0_20px_rgba(14,165,233,0.3)]">
                                            </div>
                                            <!-- Novos indicadores de precisão -->
                                            <div id="faceStatus" class="absolute bottom-4 left-0 right-0 text-center">
                                                <span class="bg-black/60 backdrop-blur-md text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest border border-white/10">Aguardando Face...</span>
                                            </div>
                                        </div>
                                        <div id="camLoading"
                                            class="absolute z-0 flex flex-col items-center justify-center text-slate-400">
                                            <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg"
                                                fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <button onclick="tirarFotoFacial()" id="btnCapturarFacial"
                                        class="w-full py-4 bg-brand-600 hover:bg-brand-500 text-white rounded-2xl font-bold shadow-lg shadow-brand-200 transition-all flex justify-center items-center gap-2">
                                        Capturar Facial
                                    </button>
                                </div>

                                <!-- Area Digital -->
                                <div id="areaDigital" class="w-full flex-col items-center hidden">
                                    <div
                                        class="w-48 h-60 bg-slate-50 rounded-2xl border-4 border-slate-100 flex flex-col items-center justify-center mb-6 relative overflow-hidden group">
                                        <!-- Ring Animation -->
                                        <div id="bioScanRing"
                                            class="absolute inset-0 border-[6px] border-emerald-400 rounded-2xl opacity-0 scale-90 transition-all duration-700 pointer-events-none">
                                        </div>

                                        <div class="text-slate-300 group-hover:text-slate-400 transition-colors">
                                            <svg class="w-20 h-20" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                    d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4">
                                                </path>
                                            </svg>
                                        </div>
                                        <span
                                            class="text-[10px] font-bold text-slate-400 mt-4 uppercase tracking-tighter">Aguardando
                                            Sensor...</span>
                                    </div>
                                    <button onclick="iniciarCapturaDigitalClock()" id="btnAtivarSensor"
                                        class="w-full py-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl font-bold shadow-lg shadow-emerald-100 transition-all flex justify-center items-center gap-2">
                                        <span id="btnAtivarSensorText">Iniciar Sensor</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center">
                <a href="#" onclick="pedirAcessoAdmin(event)"
                    class="text-sm font-semibold text-slate-500 hover:text-brand-600 transition-colors flex items-center justify-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                    Acesso Administrativo
                </a>
            </div>

        </div>
    </div>
</div>

<script>
    // Atualização em Tempo Real do Relógio
    function updateClock() {
        const now = new Date();
        const dFormat = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'full' }).format(now);

        let hh = String(now.getHours()).padStart(2, '0');
        let mm = String(now.getMinutes()).padStart(2, '0');
        let ss = String(now.getSeconds()).padStart(2, '0');

        document.getElementById('liveClock').textContent = `${hh}:${mm}:${ss}`;
        document.getElementById('liveDate').textContent = dFormat;
    }

    // ---- Autocomplete Matrícula ----
    let _todosFunc = null;

    async function _carregarFuncionarios() {
        if (_todosFunc) return _todosFunc;
        // Inicia carregamento dos modelos em background enquanto carrega funcionários
        loadFaceModels();
        try {
            const res = await fetch('../api/funcionarios.php');
            const json = await res.json();
            if (json.success) _todosFunc = json.data;
        } catch (e) { _todosFunc = []; }
        return _todosFunc || [];
    }

    async function buscarMatriculas(valor) {
        const dropdown = document.getElementById('matriculaSugestoes');
        valor = valor.toUpperCase().trim();
        if (valor.length < 1) { dropdown.classList.add('hidden'); dropdown.innerHTML = ''; return; }

        const lista = await _carregarFuncionarios();
        const filtrados = lista.filter(f =>
            f.matricula.toUpperCase().includes(valor) || f.nome.toUpperCase().includes(valor)
        ).slice(0, 8);

        if (filtrados.length === 0) { dropdown.classList.add('hidden'); dropdown.innerHTML = ''; return; }

        dropdown.innerHTML = filtrados.map(f => `
            <button type="button"
                onclick="selecionarMatricula('${f.matricula.replace(/'/g, "\\'")}', '${f.nome.replace(/'/g, "\\'")}')"
                class="w-full flex items-center gap-3 px-4 py-3 hover:bg-brand-50 transition-colors text-left border-b border-slate-50 last:border-0 group">
                <div class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center flex-shrink-0 text-sm font-bold group-hover:bg-brand-200 transition-colors">
                    ${f.nome.charAt(0).toUpperCase()}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-slate-800 truncate">${f.nome}</div>
                    <div class="text-xs font-mono text-brand-600 font-semibold">${f.matricula}</div>
                </div>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-brand-500 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        `).join('');
        dropdown.classList.remove('hidden');
    }

    function selecionarMatricula(matricula) {
        const input = document.getElementById('matricula');
        input.value = matricula;
        fecharSugestoes();
        // Dispara verificação dos métodos de acesso
        registrarPonto({ preventDefault: () => { } }, true);
    }

    function fecharSugestoes() {
        document.getElementById('matriculaSugestoes').classList.add('hidden');
    }
    // ---- Fim Autocomplete ----

    setInterval(updateClock, 1000);
    updateClock();

    // -- Lógica de Interação UI --
    // -- Geofencing: Captura de Localização --
    async function obterLocalizacao() {
        return new Promise((resolve) => {
            if (!navigator.geolocation) {
                console.warn("Geolocalização não suportada");
                resolve(null);
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (pos) => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
                (err) => {
                    console.warn("Erro ao obter localização:", err.message);
                    resolve(null);
                },
                { enableHighAccuracy: true, timeout: 5000 }
            );
        });
    }

    let currentFlow = 'identificacao'; // 'identificacao' ou 'autenticacao'
    let metodosPermitidos = [];
    let temWebAuthn = false;

    window.toggleSenhaVisualRelogio = function () {
        const input = document.getElementById('senha');
        input.type = input.type === 'password' ? 'text' : 'password';
    };

    // Resetar fluxo se a matrícula mudar
    document.getElementById('matricula').addEventListener('input', function () {
        if (currentFlow === 'autenticacao') {
            resetarFluxo();
        }
    });

    function resetarFluxo() {
        currentFlow = 'identificacao';
        document.getElementById('containerSenhaRelogio').classList.add('hidden');
        document.getElementById('containerBiometria').classList.add('hidden');
        const btn = document.getElementById('btnSubmit');
        btn.innerHTML = `<span class="relative z-10 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Identificar Matrícula
                    </span>`;
    }

    // Lógica para registrar o Ponto via Fetch API
    async function registrarPonto(e) {
        if (e && e.preventDefault) e.preventDefault();
        const inputMatricula = document.getElementById('matricula');
        const matricula = inputMatricula.value.trim();
        const senha = document.getElementById('senha').value;
        const btn = document.getElementById('btnSubmit');

        // Se matrícula vazia e não estamos em um fluxo de autenticação, abre biometria direto
        if (!matricula && currentFlow === 'identificacao') {
            window.abrirModalBiometria();
            return;
        }

        if (currentFlow === 'identificacao') {
            btn.disabled = true;
            btn.innerHTML = "Verificando...";
            
            const success = await window.obterDadosFuncionario(matricula);
            btn.disabled = false;

            if (success) {
                let mostrouAlgo = false;
                if (metodosPermitidos.includes('senha')) {
                    document.getElementById('containerSenhaRelogio').classList.remove('hidden');
                    document.getElementById('senha').focus();
                    mostrouAlgo = true;
                }

                const temBiometria = metodosPermitidos.some(m => ['facial', 'digital', 'biometria', 'qrcode'].includes(m));
                if (temBiometria) {
                    document.getElementById('containerBiometria').classList.remove('hidden');
                    mostrouAlgo = true;
                    if (metodosPermitidos.includes('facial')) {
                        window.abrirModalBiometria();
                    }
                }

                btn.innerHTML = `<span class="relative z-10 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Confirmar Ponto
                </span>`;

                if (!mostrouAlgo) {
                    Swal.fire('Aten��o!', 'Nenhum m�todo de acesso configurado.', 'warning'); resetarFluxo();
                }
            }
        } else {
            if (metodosPermitidos.includes('senha') && !senha) {
                Swal.fire('Atenção', 'Por favor, digite sua senha de acesso.', 'warning');
                return;
            }
            await executarRegistroPonto(matricula, senha);
        }
    }

    window.obterDadosFuncionario = async function (matricula) {
        if (!matricula) return false;
        try {
            const res = await fetch('../api/ponto.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_metodos', matricula })
            });
            const data = await res.json();
            if (data.success) {
                // Verificar se a ficha está incompleta
                if (data.ficha_incompleta) {
                    await Swal.fire({
                        title: 'Atualização Cadastral',
                        text: 'Sua ficha está incompleta. Clique em atualizar para abrir o formulário de atualização.',
                        icon: 'info',
                        confirmButtonText: 'Atualizar',
                        confirmButtonColor: '#0ea5e9'
                    });
                    const atualizou = await window.abrirModalFichaIncompleta(data);
                    if (!atualizou) {
                        currentFlow = 'identificacao';
                        resetarFluxo();
                        return false;
                    }
                }

                metodosPermitidos = data.metodos;
                temWebAuthn = !!data.has_webauthn;
                currentFlow = 'autenticacao';
                return true;
            } else {
                Swal.fire({ title: 'Ops!', text: data.message, icon: 'error', confirmButtonColor: '#0ea5e9' });
                return false;
            }
        } catch (err) {
            console.error("Erro ao obter dados:", err);
            return false;
        }
    };

    window.abrirModalFichaIncompleta = async function (data) {
        if (listaHorarios.length === 0) await _carregarHorarios();
        
        return new Promise(async (resolve) => {
            const func = data;
            
            const diasSemana = {
                segunda: 'Segunda',
                terca: 'Terça',
                quarta: 'Quarta',
                quinta: 'Quinta',
                sexta: 'Sexta',
                sabado: 'Sábado',
                domingo: 'Domingo'
            };

            const gridHtmlHorarios = Object.entries(diasSemana).map(([key, label]) => {
                const currentVal = func.grade_horarios ? (typeof func.grade_horarios === 'string' ? JSON.parse(func.grade_horarios)[key] : func.grade_horarios[key]) : '';
                return `
                    <div class="p-2 bg-white/50 border border-slate-100 rounded-xl space-y-1">
                        <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">${label}</label>
                        <select id="upd_grade_${key}" class="w-full px-2 py-1.5 bg-white border border-slate-200 rounded-lg text-[10px] font-bold outline-none focus:border-brand-500 transition-all">
                            <option value="">Livre</option>
                            ${listaHorarios.map(h => `<option value="${h.id}" ${currentVal == h.id ? 'selected' : ''}>${h.nome}</option>`).join('')}
                        </select>
                    </div>
                `;
            }).join(''); // Contém f.* vindo do ponto.php
            const { value: formValues } = await Swal.fire({
                title: 'Atualização Necessária',
                html: `
                    <p class="text-[11px] text-slate-500 mb-6 uppercase tracking-wider font-bold">Por favor, complete sua ficha para conformidade com o E-Social.</p>
                    <div class="text-left max-h-[70vh] overflow-y-auto px-2 custom-scrollbar space-y-8">
                        
                        <!-- Seção 1: Dados Pessoais -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                                <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                                <h3 class="text-[10px] font-black text-slate-700 uppercase tracking-widest">1. Identificação Pessoal</h3>
                            </div>
                            <div class="group">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Nome Completo *</label>
                                <input id="upd_nome" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-medium outline-none" value="${func.nome || ''}">
                            </div>
                            <div class="group">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Nome Social</label>
                                <input id="upd_nome_social" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-medium outline-none" value="${func.nome_social || ''}">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">CPF *</label>
                                    <input id="upd_cpf" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center" value="${func.cpf || ''}" placeholder="000.000.000-00">
                                </div>
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Data Nasc. *</label>
                                    <input id="upd_nascimento" type="date" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none" value="${func.data_nascimento || ''}">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Sexo *</label>
                                    <select id="upd_sexo" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none">
                                        <option value="M" ${func.sexo === 'M' ? 'selected' : ''}>Masculino</option>
                                        <option value="F" ${func.sexo === 'F' ? 'selected' : ''}>Feminino</option>
                                        <option value="Outro" ${func.sexo === 'Outro' ? 'selected' : ''}>Outro</option>
                                    </select>
                                </div>
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Estado Civil *</label>
                                    <select id="upd_estado_civil" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none">
                                        <option value="Solteiro(a)" ${func.estado_civil === 'Solteiro(a)' ? 'selected' : ''}>Solteiro(a)</option>
                                        <option value="Casado(a)" ${func.estado_civil === 'Casado(a)' ? 'selected' : ''}>Casado(a)</option>
                                        <option value="União Estável" ${func.estado_civil === 'União Estável' ? 'selected' : ''}>União Estável</option>
                                        <option value="Divorciado(a)" ${func.estado_civil === 'Divorciado(a)' ? 'selected' : ''}>Divorciado(a)</option>
                                        <option value="Viúvo(a)" ${func.estado_civil === 'Viúvo(a)' ? 'selected' : ''}>Viúvo(a)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Raça / Cor</label>
                                    <select id="upd_raca" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none">
                                        <option value="Branca" ${func.raca_cor === 'Branca' ? 'selected' : ''}>Branca</option>
                                        <option value="Parda" ${func.raca_cor === 'Parda' ? 'selected' : ''}>Parda</option>
                                        <option value="Preta" ${func.raca_cor === 'Preta' ? 'selected' : ''}>Preta</option>
                                        <option value="Amarela" ${func.raca_cor === 'Amarela' ? 'selected' : ''}>Amarela</option>
                                        <option value="Indígena" ${func.raca_cor === 'Indígena' ? 'selected' : ''}>Indígena</option>
                                    </select>
                                </div>
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">PCD / Deficiência</label>
                                    <select id="upd_deficiencia" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none" onchange="window.toggleDeficienciaRelogio()">
                                        <option value="false" ${!func.deficiencia ? 'selected' : ''}>NÃO</option>
                                        <option value="true" ${func.deficiencia ? 'selected' : ''}>SIM</option>
                                    </select>
                                </div>
                            </div>
                            <div id="wrapper_upd_deficiencia" class="grid grid-cols-3 gap-2 ${func.deficiencia ? '' : 'hidden'}">
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Grau</label>
                                    <select id="upd_deficiencia_grau" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none">
                                        <option value="" ${!func.deficiencia_grau ? 'selected' : ''}>Média</option>
                                        <option value="Leve" ${func.deficiencia_grau === 'Leve' ? 'selected' : ''}>Leve</option>
                                        <option value="Moderada" ${func.deficiencia_grau === 'Moderada' ? 'selected' : ''}>Moderada</option>
                                        <option value="Grave" ${func.deficiencia_grau === 'Grave' ? 'selected' : ''}>Grave</option>
                                    </select>
                                </div>
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Tipo</label>
                                    <input id="upd_deficiencia_tipo" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-xs font-medium outline-none" value="${func.deficiencia_tipo || ''}" placeholder="Visual...">
                                </div>
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">CID</label>
                                    <input id="upd_deficiencia_cid" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-xs font-bold outline-none" value="${func.deficiencia_cid || ''}" placeholder="H54.0">
                                </div>
                            </div>
                            <div class="group">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Tipo Sanguíneo</label>
                                <input id="upd_sangue" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center" value="${func.tipo_sanguineo || ''}" placeholder="Ex: A+">
                            </div>
                        </div>

                        <!-- Seção 2: Origem -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                <h3 class="text-[10px] font-black text-slate-700 uppercase tracking-widest">2. Origem e Nacionalidade</h3>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="col-span-2 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Naturalidade (Cidade)</label>
                                    <input id="upd_naturalidade" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-medium outline-none" value="${func.naturalidade || ''}" placeholder="Cidade Natal">
                                </div>
                                <div class="col-span-1 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">UF Natal</label>
                                    <input id="upd_naturalidade_uf" maxlength="2" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center uppercase" value="${func.naturalidade_uf || ''}" placeholder="PB">
                                </div>
                            </div>
                            <div class="group">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Nacionalidade</label>
                                <input id="upd_nacionalidade" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-medium outline-none" value="${func.nacionalidade || 'Brasileira'}">
                            </div>
                        </div>

                        <!-- Seção 3: Documentação -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                <h3 class="text-[10px] font-black text-slate-700 uppercase tracking-widest">3. Documentação Social e Profissional</h3>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="col-span-1 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">RG *</label>
                                    <input id="upd_rg" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center" value="${func.rg_numero || ''}" placeholder="Número">
                                </div>
                                <div class="col-span-1 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Órgão *</label>
                                    <input id="upd_rg_orgao" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center uppercase" value="${func.rg_orgao || ''}" placeholder="SSP/PB">
                                </div>
                                <div class="col-span-1 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Emissão *</label>
                                    <input id="upd_rg_data" type="date" class="w-full px-2 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-xs font-bold outline-none" value="${func.rg_data_emissao || ''}">
                                </div>
                            </div>
                            <div class="group">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">PIS / PASEP</label>
                                <input id="upd_pis" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none" value="${func.pis_pasep || ''}">
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="col-span-1 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">CNH</label>
                                    <input id="upd_cnh" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center" value="${func.cnh_numero || ''}">
                                </div>
                                <div class="col-span-1 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Cat.</label>
                                    <input id="upd_cnh_cat" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center uppercase" value="${func.cnh_categoria || ''}" placeholder="AB">
                                </div>
                                <div class="col-span-1 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Validade</label>
                                    <input id="upd_cnh_val" type="date" class="w-full px-2 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-xs font-bold outline-none" value="${func.cnh_validade || ''}">
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="col-span-1 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Título Eleitor</label>
                                    <input id="upd_titulo" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center" value="${func.titulo_eleitor_numero || ''}">
                                </div>
                                <div class="col-span-1 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Zona</label>
                                    <input id="upd_titulo_zona" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center" value="${func.titulo_eleitor_zona || ''}">
                                </div>
                                <div class="col-span-1 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Seção</label>
                                    <input id="upd_titulo_secao" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center" value="${func.titulo_eleitor_secao || ''}">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">CTPS Nº</label>
                                    <input id="upd_ctps" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none" value="${func.ctps_numero || ''}" placeholder="0000000">
                                </div>
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Série</label>
                                    <input id="upd_ctps_serie" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none" value="${func.ctps_serie || ''}" placeholder="000-0">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Emissão CTPS</label>
                                    <input id="upd_ctps_data" type="date" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none" value="${func.ctps_data_emissao || ''}">
                                </div>
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">UF CTPS</label>
                                    <input id="upd_ctps_uf" maxlength="2" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center uppercase" value="${func.ctps_uf || ''}" placeholder="PB">
                                </div>
                            </div>
                            <div class="group">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Cert. Reservista</label>
                                <input id="upd_reservista" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none" value="${func.reservista_numero || ''}">
                            </div>
                            <div class="group">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Nível de Instrução / Escolaridade *</label>
                                <select id="upd_escolaridade" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none">
                                    <option value="" ${!func.escolaridade ? 'selected' : ''}>Selecione...</option>
                                    <option value="Médio Completo" ${func.escolaridade === 'Médio Completo' ? 'selected' : ''}>Médio Completo</option>
                                    <option value="Superior Incompleto" ${func.escolaridade === 'Superior Incompleto' ? 'selected' : ''}>Superior Incompleto</option>
                                    <option value="Superior Completo" ${func.escolaridade === 'Superior Completo' ? 'selected' : ''}>Superior Completo</option>
                                    <option value="Pós-Graduação" ${func.escolaridade === 'Pós-Graduação' ? 'selected' : ''}>Pós-Graduação</option>
                                </select>
                            </div>
                        </div>

                        <!-- Seção 4: Endereço e Contato -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <h3 class="text-[10px] font-black text-slate-700 uppercase tracking-widest">4. Residência & Contato</h3>
                            </div>
                            <div class="grid grid-cols-4 gap-4">
                                <div class="col-span-3 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">CEP *</label>
                                    <input id="upd_cep" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center" value="${func.endereco_cep || ''}" placeholder="00000-000">
                                </div>
                                <button type="button" onclick="window.consultarCEPRelogio()" class="col-span-1 mt-4 flex items-center justify-center bg-brand-50 text-brand-600 rounded-xl hover:bg-brand-100 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </button>
                            </div>
                            <div class="group">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Logradouro *</label>
                                <input id="upd_endereco" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-medium outline-none" value="${func.endereco || ''}" placeholder="Rua, Avenida, etc.">
                            </div>
                            <div class="grid grid-cols-4 gap-4">
                                <div class="col-span-1 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Nº *</label>
                                    <input id="upd_numero" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center" value="${func.endereco_numero || ''}">
                                </div>
                                <div class="col-span-3 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Bairro *</label>
                                    <input id="upd_bairro" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-medium outline-none" value="${func.endereco_bairro || ''}">
                                </div>
                            </div>
                            <div class="grid grid-cols-4 gap-4">
                                <div class="col-span-3 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Cidade *</label>
                                    <input id="upd_municipio" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-medium outline-none" value="${func.endereco_municipio || ''}">
                                </div>
                                <div class="col-span-1 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">UF *</label>
                                    <input id="upd_uf" maxlength="2" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center uppercase" value="${func.endereco_uf || ''}" placeholder="PB">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">WhatsApp / Celular *</label>
                                    <input id="upd_celular" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center" value="${func.celular || ''}" placeholder="(00) 0 0000-0000">
                                </div>
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">E-mail</label>
                                    <input id="upd_email" type="email" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-medium outline-none" value="${func.endereco_email || ''}" placeholder="exemplo@email.com">
                                </div>
                            </div>
                        </div>

                        <!-- Seção 5: Família -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                                <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                                <h3 class="text-[10px] font-black text-slate-700 uppercase tracking-widest">5. Filiação & Família</h3>
                            </div>
                            <div class="group">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Nome da Mãe *</label>
                                <input id="upd_nome_mae" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-medium outline-none" value="${func.nome_mae || ''}" placeholder="Nome Completo da Mãe">
                            </div>
                            <div class="group">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Nome do Pai</label>
                                <input id="upd_nome_pai" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-medium outline-none" value="${func.nome_pai || ''}" placeholder="Nome Completo do Pai">
                            </div>
                            <div class="group">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Nome do Cônjuge</label>
                                <input id="upd_conjuge" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-medium outline-none" value="${func.nome_conjuge || ''}">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Filhos Menores</label>
                                    <input id="upd_filhos_menores" type="number" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center" value="${Array.isArray(func.filhos_menores) ? func.filhos_menores.length : (parseInt(func.filhos_menores) || 0)}">
                                </div>
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Dependentes IR</label>
                                    <input id="upd_dependentes_ir" type="number" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center" value="${Array.isArray(func.dependentes_ir) ? func.dependentes_ir.length : (parseInt(func.dependentes_ir) || 0)}">
                                </div>
                            </div>
                        </div>

                        <!-- Seção 6: Financeiro -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <h3 class="text-[10px] font-black text-slate-700 uppercase tracking-widest">6. Dados Bancários</h3>
                            </div>
                            <div class="group">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Banco</label>
                                <input id="upd_banco" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none" value="${func.banco_nome || ''}" placeholder="Ex: Banco do Brasil">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Agência</label>
                                    <input id="upd_agencia" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center" value="${func.banco_agencia || ''}">
                                </div>
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Conta</label>
                                    <input id="upd_conta" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center" value="${func.banco_conta || ''}">
                                </div>
                            </div>
                        </div>

                        <!-- Quadro de Horários Semanal (Mandatório) -->
                        <div class="bg-indigo-50/30 p-4 rounded-2xl border border-indigo-100/50 space-y-4">
                            <div class="flex items-center gap-2 border-b border-indigo-100 pb-2">
                                <div class="w-6 h-6 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <h3 class="text-[10px] font-black text-indigo-900 uppercase tracking-widest">Quadro de Horários Semanal *</h3>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                ${gridHtmlHorarios}
                            </div>
                        </div>
                    </div>
                `,
                width: '600px',
                confirmButtonText: 'Salvar Ficha e Continuar',
                confirmButtonColor: '#0ea5e9',
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                allowOutsideClick: false,
                focusConfirm: false,
                preConfirm: () => {
                    const fields = {
                        nome: document.getElementById('upd_nome').value,
                        nome_social: document.getElementById('upd_nome_social').value,
                        cpf: document.getElementById('upd_cpf').value,
                        data_nascimento: document.getElementById('upd_nascimento').value,
                        sexo: document.getElementById('upd_sexo').value,
                        estado_civil: document.getElementById('upd_estado_civil').value,
                        raca_cor: document.getElementById('upd_raca').value,
                        deficiencia: document.getElementById('upd_deficiencia').value,
                        deficiencia_grau: document.getElementById('upd_deficiencia_grau').value,
                        deficiencia_tipo: document.getElementById('upd_deficiencia_tipo').value,
                        deficiencia_cid: document.getElementById('upd_deficiencia_cid').value,
                        tipo_sanguineo: document.getElementById('upd_sangue').value,
                        naturalidade: document.getElementById('upd_naturalidade').value,
                        naturalidade_uf: document.getElementById('upd_naturalidade_uf').value.toUpperCase(),
                        nacionalidade: document.getElementById('upd_nacionalidade').value,
                        rg_numero: document.getElementById('upd_rg').value,
                        rg_orgao: document.getElementById('upd_rg_orgao').value,
                        rg_data_emissao: document.getElementById('upd_rg_data').value,
                        pis_pasep: document.getElementById('upd_pis').value,
                        cnh_numero: document.getElementById('upd_cnh').value,
                        cnh_categoria: document.getElementById('upd_cnh_cat').value,
                        cnh_validade: document.getElementById('upd_cnh_val').value,
                        titulo_eleitor_numero: document.getElementById('upd_titulo').value,
                        titulo_eleitor_zona: document.getElementById('upd_titulo_zona').value,
                        titulo_eleitor_secao: document.getElementById('upd_titulo_secao').value,
                        ctps_numero: document.getElementById('upd_ctps').value,
                        ctps_serie: document.getElementById('upd_ctps_serie').value,
                        ctps_data_emissao: document.getElementById('upd_ctps_data').value,
                        ctps_uf: document.getElementById('upd_ctps_uf').value.toUpperCase(),
                        reservista_numero: document.getElementById('upd_reservista').value,
                        escolaridade: document.getElementById('upd_escolaridade').value,
                        endereco_cep: document.getElementById('upd_cep').value,
                        endereco: document.getElementById('upd_endereco').value,
                        endereco_numero: document.getElementById('upd_numero').value,
                        endereco_bairro: document.getElementById('upd_bairro').value,
                        endereco_municipio: document.getElementById('upd_municipio').value,
                        endereco_uf: document.getElementById('upd_uf').value.toUpperCase(),
                        celular: document.getElementById('upd_celular').value,
                        endereco_email: document.getElementById('upd_email').value,
                        nome_mae: document.getElementById('upd_nome_mae').value,
                        nome_pai: document.getElementById('upd_nome_pai').value,
                        nome_conjuge: document.getElementById('upd_conjuge').value,
                        filhos_menores: Array.from({length: parseInt(document.getElementById('upd_filhos_menores').value) || 0}, () => ({nome: '', nasc: '', cpf: ''})),
                        dependentes_ir: Array.from({length: parseInt(document.getElementById('upd_dependentes_ir').value) || 0}, () => ({nome: '', nasc: '', cpf: ''})),
                        banco_nome: document.getElementById('upd_banco').value,
                        banco_agencia: document.getElementById('upd_agencia').value,
                        banco_conta: document.getElementById('upd_conta').value,
                        grade_horarios: {
                            segunda: document.getElementById('upd_grade_segunda').value,
                            terca: document.getElementById('upd_grade_terca').value,
                            quarta: document.getElementById('upd_grade_quarta').value,
                            quinta: document.getElementById('upd_grade_quinta').value,
                            sexta: document.getElementById('upd_grade_sexta').value,
                            sabado: document.getElementById('upd_grade_sabado').value,
                            domingo: document.getElementById('upd_grade_domingo').value
                        }
                    };

                    // Validação simplificada (apenas os principais)
                    const obrigatorios = ['nome', 'cpf', 'data_nascimento', 'rg_numero', 'rg_orgao', 'rg_data_emissao', 'endereco_cep', 'endereco', 'endereco_numero', 'endereco_bairro', 'endereco_municipio', 'endereco_uf', 'celular', 'nome_mae'];
                    for (const key of obrigatorios) {
                        if (!fields[key]) {
                            Swal.showValidationMessage(`O campo ${key.replace('_', ' ')} é essencial.`);
                            return false;
                        }
                    }

                    // Validação da Grade de Horários (Mandatório)
                    const hasHorario = Object.values(fields.grade_horarios).some(v => v !== "");
                    if (!hasHorario) {
                        Swal.showValidationMessage(`Você deve selecionar seu horário semanal.`);
                        return false;
                    }
                    }
                    return fields;
                }
            });

            if (formValues) {
                try {
                    const updRes = await fetch('../api/funcionarios.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'update_ficha',
                            id: data.id,
                            ...formValues
                        })
                    });
                    const updData = await updRes.json();
                    if (updData.success) {
                        Swal.fire({
                            title: 'Ficha Atualizada!',
                            text: 'Seus dados foram salvos. Agora você pode registrar seu ponto.',
                            icon: 'success',
                            showConfirmButton: true,
                            confirmButtonText: 'Entendi',
                            confirmButtonColor: '#0ea5e9'
                        });
                        resolve(true);
                    } else {
                        Swal.fire('Erro', updData.message, 'error');
                        resolve(false);
                    }
                } catch (e) {
                    Swal.fire('Erro', 'Falha ao salvar dados.', 'error');
                    resolve(false);
                }
            } else {
                resolve(false);
            }
        });
    };

    window.consultarCEPRelogio = async function() {
        const cep = document.getElementById('upd_cep').value.replace(/\D/g, '');
        if (cep.length !== 8) {
            Swal.showValidationMessage('CEP deve ter 8 dígitos');
            return;
        }
        
        try {
            const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const d = await res.json();
            if (!d.erro) {
                document.getElementById('upd_endereco').value = d.logradouro;
                document.getElementById('upd_bairro').value = d.bairro;
                document.getElementById('upd_municipio').value = d.localidade;
                document.getElementById('upd_uf').value = d.uf;
                document.getElementById('upd_numero').focus();
            } else {
                Swal.showValidationMessage('CEP não encontrado');
            }
        } catch (e) {
            Swal.showValidationMessage('Erro ao consultar CEP');
        }
    };

    async function executarRegistroPonto(matricula, senha) {
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = "Registrando...";

        try {
            const loc = await _geoCache ? Promise.resolve(_geoCache) : obterLocalizacao();
            const res = await fetch('../api/ponto.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'ponto_matricula', matricula, senha, lat: loc?.lat, lng: loc?.lng })
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    title: 'Marcado!',
                    html: `<b>${data.funcionario}</b><br>Seu ponto foi registrado às ${data.hora}`,
                    icon: 'success',
                    showConfirmButton: true,
                    confirmButtonText: 'Fechar',
                    confirmButtonColor: '#0ea5e9'
                });
                document.getElementById('matricula').value = '';
                document.getElementById('senha').value = '';
                resetarFluxo();
            } else {
                Swal.fire({ title: 'Ops!', text: data.message, icon: 'error', confirmButtonColor: '#0ea5e9' });
            }
        } catch (err) {
            Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
        } finally {
            btn.disabled = false;
            if (currentFlow === 'autenticacao') {
                btn.innerHTML = `<span class="relative z-10 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Confirmar Ponto
                </span>`;
            } else {
                resetarFluxo();
            }
        }
    }

    // -- Lógica para Biometria Unificada (Facial e Digital) --
    let videoStream = null;
    let modelsLoaded = false;
    let isProcessingFacial = false;
    let detectionLoopActive = false;
    
    // Variáveis para detecção de vivacidade (Liveness)
    let landmarkHistory = [];
    const MAX_HISTORY = 5; // Reduzido: menos frames para confirmar vivacidade
    const MOVEMENT_THRESHOLD = 0.5; // Reduzido: movimento menor já valida
    let isLivenessOk = false;
    let livenessAttempts = 0;
    let _geoCache = null; // Cache de geolocalização antecipada

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
            const statusEl = document.querySelector('#faceStatus span');
            if (statusEl) statusEl.innerText = "Erro ao carregar IA";
        }
    }

    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

    window.abrirModalBiometria = async function () {
        const inputMatricula = document.getElementById('matricula');
        const matricula = inputMatricula.value.trim();
        document.getElementById('bio_matricula').value = matricula;

        // Se houver matrícula e ainda não carregamos os dados, carrega agora
        // Isso garante que o status temWebAuthn seja atualizado antes de tentar o WebAuthn
        if (matricula && currentFlow === 'identificacao') {
            const ok = await window.obterDadosFuncionario(matricula);
            if (!ok) return; // Se der erro na matrícula, não abre o modal
        }

        // SE FOR MOBILE: Tentar integrar direto ao leitor do celular (WebAuthn)
        if (isMobile && window.PublicKeyCredential && temWebAuthn) {
            console.log("Mobile detectado + Chave vinculada: Tentando biometria nativa...");
            const success = await window.baterPontoWebAuthn(true);
            if (success) return;
        }

        // Filtrar abas baseadas nos métodos permitidos
        const tFacial = document.getElementById('tabFacial');
        const tDigital = document.getElementById('tabDigital');
        
        // Se metodosPermitidos estiver vazio (identificação inicial), permite ambos
        const podeFacial = metodosPermitidos.length === 0 || metodosPermitidos.includes('facial');
        const podeDigital = metodosPermitidos.length === 0 || metodosPermitidos.includes('biometria'); 

        if (tFacial) tFacial.style.display = podeFacial ? 'block' : 'none';
        if (tDigital) tDigital.style.display = podeDigital ? 'block' : 'none';

        document.getElementById('modalBiometria').classList.remove('hidden');
        
        // Selecionar o primeiro método disponível, priorizando facial
        if (podeFacial) {
            window.mudarModoBiometria('facial');
        } else if (podeDigital) {
            window.mudarModoBiometria('digital');
        } else {
            // Fallback total se algo falhar na lógica de métodos
            window.mudarModoBiometria('facial');
        }

        // Se apenas um estiver ativo (e soubermos qual é o funcionário), esconder a barra de abas
        const tabsContainer = tFacial?.parentElement;
        if (tabsContainer) {
            const matriculaIdentificada = document.getElementById('matricula').value.trim() !== "";
            tabsContainer.style.display = (podeFacial && podeDigital && !matriculaIdentificada) || (podeFacial && podeDigital) ? 'flex' : 'none';
        }

        loadFaceModels(); // Background load
        // Iniciar coleta de geolocalização em background
        _geoCache ? Promise.resolve(_geoCache) : obterLocalizacao().then(loc => { if (loc) _geoCache = loc; });
    };

    window.fecharModalBiometria = function () {
        document.getElementById('modalBiometria').classList.add('hidden');
        detectionLoopActive = false;
        isProcessingFacial = false;
        landmarkHistory = [];
        isLivenessOk = false;
        window.pararStreamVideo();
        if (window.dpSocketClock) window.dpSocketClock.close();
    };

    window.mudarModoBiometria = function (modo) {
        document.getElementById('bio_modo').value = modo;

        // UI Tabs
        const tFacial = document.getElementById('tabFacial');
        const tDigital = document.getElementById('tabDigital');
        const aFacial = document.getElementById('areaFacial');
        const aDigital = document.getElementById('areaDigital');

        if (modo === 'facial') {
            tFacial.className = "flex-1 py-2 text-xs font-bold rounded-xl transition-all border border-transparent bg-brand-50 text-brand-600 border-brand-100";
            tDigital.className = "flex-1 py-2 text-xs font-bold rounded-xl transition-all border border-transparent text-slate-500 hover:bg-slate-50";
            if (aFacial) aFacial.classList.remove('hidden');
            if (aDigital) aDigital.classList.add('hidden');
            window.iniciarStreamVideo();
        } else {
            detectionLoopActive = false; // Parar loop se mudar para digital
            tDigital.className = "flex-1 py-2 text-xs font-bold rounded-xl transition-all border border-transparent bg-emerald-50 text-emerald-600 border-emerald-100";
            if (tFacial) tFacial.className = "flex-1 py-2 text-xs font-bold rounded-xl transition-all border border-transparent text-slate-500 hover:bg-slate-50";
            if (aDigital) aDigital.classList.remove('hidden');
            if (aFacial) aFacial.classList.add('hidden');
            window.pararStreamVideo();

            // Atualizar texto do botão conforme o estado
            const btnText = document.getElementById('btnAtivarSensorText');
            if (btnText) {
                btnText.innerText = isMobile ? (temWebAuthn ? 'Usar Biometria do Celular' : 'Vincular este Aparelho no RH') : 'Iniciar Sensor';
            }

            if (isMobile && window.PublicKeyCredential && temWebAuthn) {
                // Pequeno delay para garantir que o modal atualizou
                setTimeout(() => window.baterPontoWebAuthn(false), 300);
            }
        }
        landmarkHistory = [];
        isLivenessOk = false;
    };

    window.iniciarStreamVideo = async function () {
        const video = document.getElementById('videoFeed');
        const camLoading = document.getElementById('camLoading');
        const statusEl = document.querySelector('#faceStatus span');
        
        if (camLoading) {
            camLoading.classList.remove('hidden');
        }
        if (video) video.classList.add('hidden');
        const canvas = document.getElementById('videoCanvas');
        if (canvas) canvas.classList.add('hidden');
        if (statusEl) statusEl.innerText = !modelsLoaded ? "Carregando IA..." : "Iniciando Cam...";

        try {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error("Navegador não suporta acesso à câmera.");
            }
            
            window.pararStreamVideo();

            videoStream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'user' } 
            });
            
            video.srcObject = videoStream;
            
            // Força o play e tenta lidar com restrições do navegador
            const playVideo = async () => {
                try {
                    await video.play();
                    if (camLoading) camLoading.classList.add('hidden');
                    if (video) video.classList.remove('hidden');
                    if (statusEl) statusEl.innerText = !modelsLoaded ? "Carregando IA..." : "Aguardando Face...";
                    
                    if (document.getElementById('bio_modo').value === 'facial') {
                        detectionLoopActive = true;
                        isProcessingFacial = false;
                        startFaceDetectionLoop();
                    }
                    return true;
                } catch (e) {
                    console.warn("Retrying video play...");
                    return false;
                }
            };

            if (await playVideo()) return true;

            // Fallback: Aguarda interação ou evento de carregamento
            return new Promise((resolve) => {
                const retryPlay = setInterval(async () => {
                    if (await playVideo()) {
                        clearInterval(retryPlay);
                        resolve(true);
                    }
                }, 1000);
                
                // Timeout de 10 segundos para desistir
                setTimeout(() => {
                    clearInterval(retryPlay);
                    if (statusEl) statusEl.innerText = "Erro ao iniciar vídeo";
                    resolve(false);
                }, 10000);
            });
        } catch (err) {
            console.error("Erro ao abrir câmera:", err);
            if (camLoading) camLoading.innerHTML = `<span class="text-[10px] text-red-500 font-bold px-2 text-center">Erro: ${err.message}</span>`;
            if (statusEl) statusEl.innerText = "Falha na Câmera";
            return false;
        }
    };

    window.pararStreamVideo = function () {
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
        }
    };

    async function startFaceDetectionLoop() {
        const statusEl = document.querySelector('#faceStatus span');
        if (!modelsLoaded) {
            if (statusEl) statusEl.innerText = "Carregando IA...";
            if (detectionLoopActive) setTimeout(() => requestAnimationFrame(startFaceDetectionLoop), 500);
            return;
        }
        
        if (!detectionLoopActive || isProcessingFacial) {
            if (detectionLoopActive) requestAnimationFrame(startFaceDetectionLoop);
            return;
        }

        const video = document.getElementById('videoFeed');
        if (!video || video.paused || video.ended) {
            if (detectionLoopActive) requestAnimationFrame(startFaceDetectionLoop);
            return;
        }

        try {
            // Usamos TinyFaceDetector + landmarks para verificar movimento
            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 })).withFaceLandmarks();

            if (detection) {
                // Lógica de Vivacidade (Liveness)
                const landmarks = detection.landmarks.positions;
                // Pegamos alguns pontos chave: nariz, olhos, boca
                const checkPoints = [
                    landmarks[30], // Ponta do nariz
                    landmarks[36], // Olho esquerdo
                    landmarks[45], // Olho direito
                    landmarks[48], // Canto boca esquerdo
                    landmarks[54]  // Canto boca direito
                ];
                
                landmarkHistory.push(checkPoints);
                if (landmarkHistory.length > MAX_HISTORY) landmarkHistory.shift();

                if (landmarkHistory.length >= MAX_HISTORY) {
                    let totalMovement = 0;
                    for (let i = 1; i < landmarkHistory.length; i++) {
                        for (let j = 0; j < checkPoints.length; j++) {
                            const p1 = landmarkHistory[i-1][j];
                            const p2 = landmarkHistory[i][j];
                            totalMovement += Math.sqrt(Math.pow(p2.x - p1.x, 2) + Math.pow(p2.y - p1.y, 2));
                        }
                    }
                    
                    const avgMovement = totalMovement / (MAX_HISTORY * checkPoints.length);
                    // console.log("Movimento médio:", avgMovement);
                    
                    if (avgMovement > MOVEMENT_THRESHOLD) {
                        isLivenessOk = true;
                    }
                }

                if (statusEl) {
                    if (isLivenessOk) {
                        statusEl.textContent = "Face Validada";
                        statusEl.className = "bg-emerald-500/80 backdrop-blur-md text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest border border-white/10";
                    } else {
                        statusEl.textContent = "Mova a Cabeça Levemente";
                        statusEl.className = "bg-amber-500/80 backdrop-blur-md text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest border border-white/10";
                    }
                }
                
                // Só dispara captura automática se a face for estável E houver vivacidade detectada
                if (detection.detection.score > 0.85 && !isProcessingFacial && isLivenessOk) {
                    console.log("Confiança alta e vivacidade detectada.");
                    window.tirarFotoFacial();
                    return;
                }
            } else {
                if (statusEl) {
                    statusEl.textContent = "Ajuste sua Face";
                    statusEl.className = "bg-black/60 backdrop-blur-md text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest border border-white/10";
                }
                isLivenessOk = false;
                landmarkHistory = [];
            }
        } catch (e) {
            console.error("Erro no loop de detecção:", e);
        }

        if (detectionLoopActive) {
            setTimeout(() => requestAnimationFrame(startFaceDetectionLoop), 100); // 10fps
        }
    }

    window.tirarFotoFacial = async function () {
        if (!videoStream || isProcessingFacial) return;
        
        if (!isLivenessOk) {
            Swal.fire({
                title: 'Atenção',
                text: 'Por favor, mova-se levemente em frente à câmera para validar que é uma imagem ao vivo.',
                icon: 'warning',
                showConfirmButton: true,
                confirmButtonText: 'Entendi',
                confirmButtonColor: '#0ea5e9'
            });
            return;
        }

        const video = document.getElementById('videoFeed');
        const canvas = document.getElementById('videoCanvas');
        const btn = document.getElementById('btnCapturarFacial');

        isProcessingFacial = true; // Bloqueia múltiplas execuções

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        // Congelar imagem: Esconder vídeo e mostrar canvas
        video.classList.add('hidden');
        canvas.classList.remove('hidden');

        let base64Image = canvas.toDataURL('image/jpeg', 0.8);
        if (btn) {
            btn.innerHTML = "Mapeando Face...";
            btn.disabled = true;
        }

        const img = new Image();
        img.src = base64Image;
        img.onload = async () => {
            const statusEl = document.querySelector('#faceStatus span');
            if (statusEl) statusEl.textContent = "Analisando Face...";

            // Captura final com SsdMobilenetv1 para máxima precisão na extração de características
            const [detection, loc] = await Promise.all([
                faceapi.detectSingleFace(img, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.4 })).withFaceLandmarks().withFaceDescriptor(),
                _geoCache ? Promise.resolve(_geoCache) : obterLocalizacao()
            ]);

            if (!detection) {
                console.warn("Face perdida no processamento de alta precisão.");
                isProcessingFacial = false;
                if (statusEl) statusEl.textContent = "Erro na Leitura";
                if (btn) {
                    btn.innerHTML = "Capturar Facial";
                    btn.disabled = false;
                }
                
                // Retomar vídeo se falhar
                video.classList.remove('hidden');
                canvas.classList.add('hidden');

                if (detectionLoopActive) startFaceDetectionLoop();
                return;
            }

            if (btn) btn.innerHTML = "Validando Face...";

            fetch('../api/ponto.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'ponto_facial',
                    matricula: document.getElementById('bio_matricula').value,
                    image_data: base64Image,
                    facial_descriptor: Array.from(detection.descriptor),
                    lat: loc?.lat,
                    lng: loc?.lng
                })
            }).then(res => res.json()).then(async data => {
                if (data.success) {
                    detectionLoopActive = false; // Sucesso, para tudo
                    Swal.fire({ 
                        title: 'Marcado!', 
                        html: `<b>${data.funcionario}</b><br>Ponto via Facial às ${data.hora}`, 
                        icon: 'success', 
                        showConfirmButton: true,
                        confirmButtonText: 'Fechar',
                        confirmButtonColor: '#0ea5e9'
                    });
                    window.fecharModalBiometria();
                } else if (data.ficha_incompleta) {
                    await Swal.fire({
                        title: 'Atualização Cadastral',
                        text: 'Sua ficha está incompleta. Clique em atualizar para abrir o formulário de atualização.',
                        icon: 'info',
                        confirmButtonText: 'Atualizar',
                        confirmButtonColor: '#0ea5e9'
                    });
                    const atualizou = await window.abrirModalFichaIncompleta(data);
                    if (atualizou) {
                        // Se atualizou, tenta bater de novo ou apenas fecha e pede pra bater de novo
                        Swal.fire('Dados Atualizados', 'Sua ficha foi completada. Por favor, capture sua face novamente para registrar o ponto.', 'success');
                        isProcessingFacial = false;
                        
                        // Retomar vídeo
                        video.classList.remove('hidden');
                        canvas.classList.add('hidden');

                        if (detectionLoopActive) startFaceDetectionLoop();
                    } else {
                        window.fecharModalBiometria();
                    }
                } else {
                    Swal.fire({
                        title: 'Não Permitido',
                        text: data.message,
                        icon: 'error',
                        showConfirmButton: true,
                        confirmButtonText: 'Fechar',
                        confirmButtonColor: '#0ea5e9'
                    }).then(() => {
                        isProcessingFacial = false;
                        if (btn) {
                            btn.innerHTML = "Capturar Facial";
                            btn.disabled = false;
                        }

                        // Retomar vídeo para nova tentativa
                        video.classList.remove('hidden');
                        canvas.classList.add('hidden');

                        if (detectionLoopActive) startFaceDetectionLoop(); // Retoma loop após erro
                    });
                }
            }).catch(() => {
                Swal.fire('Erro', 'Falha na validação facial.', 'error');
                isProcessingFacial = false;
                if (detectionLoopActive) startFaceDetectionLoop();
            })
                .finally(() => {
                    if (btn) {
                        btn.innerHTML = "Capturar Facial";
                        btn.disabled = false;
                    }
                });
        };
    };

    window.togglePassword = function (inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('svg');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />`;
        } else {
            input.type = 'password';
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
        }
    };

    // -- Lógica Digital Persona (Sensor Digital) --
    window.iniciarCapturaDigitalClock = async function () {
        if (isMobile && window.PublicKeyCredential) {
            window.baterPontoWebAuthn();
            return;
        }

        const btn = document.getElementById('btnAtivarSensor');
        const ring = document.getElementById('bioScanRing');

        btn.disabled = true;
        btn.innerHTML = "Escaneie o Dedo...";
        if (ring) {
            ring.classList.remove('opacity-0', 'scale-90');
            ring.classList.add('opacity-100', 'scale-100', 'animate-pulse');
        }

        if (window.dpSocketClock) window.dpSocketClock.close();

        const endpoints = ["wss://localhost:52181/API", "ws://localhost:15270/API", "ws://127.0.0.1:15270/API"];
        let ok = false;

        for (const url of endpoints) {
            try {
                const connected = await new Promise((resolve, reject) => {
                    const s = new WebSocket(url);
                    const t = setTimeout(() => { s.close(); reject(); }, 1500);
                    s.onopen = () => {
                        clearTimeout(t);
                        window.dpSocketClock = s;
                        s.send(JSON.stringify({ Type: "Capture", Method: "Start" }));
                        resolve(true);
                    };
                    s.onmessage = async (e) => {
                        const d = JSON.parse(e.data);
                        if (d && d.Event === "SamplesReady" && d.Samples.length > 0) {
                            s.send(JSON.stringify({ Type: "Capture", Method: "Stop" }));
                            const hash = d.Samples[d.Samples.length - 1].Data;
                            processarPontoDigital(hash);
                        }
                    };
                    s.onerror = () => reject();
                });
                if (connected) { ok = true; break; }
            } catch (e) { }
        }

        if (!ok) {
            btn.disabled = false;
            btn.innerHTML = "Sensor não encontrado";
            if (ring) ring.classList.add('opacity-0');
            Swal.fire('Erro Sensor', 'Não foi possível conectar ao leitor USB.', 'warning');
        }
    };

    async function processarPontoDigital(hash) {
        const btn = document.getElementById('btnAtivarSensor');
        btn.innerHTML = "Validando Digital...";

        try {
            const loc = await _geoCache ? Promise.resolve(_geoCache) : obterLocalizacao();
            const res = await fetch('../api/ponto.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ biometria: hash, lat: loc?.lat, lng: loc?.lng })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ 
                    title: 'Marcado!', 
                    html: `<b>${data.funcionario}</b><br>Ponto via Digital às ${data.hora}`, 
                    icon: 'success',
                    showConfirmButton: true,
                    confirmButtonText: 'Fechar',
                    confirmButtonColor: '#0ea5e9'
                });
                window.fecharModalBiometria();
            } else if (data.ficha_incompleta) {
                await Swal.fire({
                    title: 'Atualização Cadastral',
                    text: 'Sua ficha está incompleta. Clique em atualizar para abrir o formulário de atualização.',
                    icon: 'info',
                    confirmButtonText: 'Atualizar',
                    confirmButtonColor: '#0ea5e9'
                });
                const atualizou = await window.abrirModalFichaIncompleta(data);
                if (atualizou) {
                    Swal.fire('Dados Atualizados', 'Sua ficha foi completada. Por favor, use o sensor digital novamente.', 'success');
                    resetarBotaoDigital();
                } else {
                    window.fecharModalBiometria();
                }
            } else {
                Swal.fire('Digital Desconhecida', data.message, 'error');
                resetarBotaoDigital();
            }
        } catch (e) {
            Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
            resetarBotaoDigital();
        }
    }

    function resetarBotaoDigital() {
        const btn = document.getElementById('btnAtivarSensor');
        const ring = document.getElementById('bioScanRing');
        btn.disabled = false;
        btn.innerHTML = (isMobile && window.PublicKeyCredential) ? "Usar Biometria do Celular" : "Tentar Novamente";
        if (ring) {
            ring.classList.remove('animate-pulse');
            ring.classList.add('opacity-0');
        }
    }

    // -- Lógica WebAuthn (Autenticação Mobile) --
    window.baterPontoWebAuthn = async function (silent = false) {
        if (!window.PublicKeyCredential) return;

        try {
            // 1. Obter desafio do servidor
            const resOpt = await fetch('../api/webauthn.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_assertion_options' })
            });
            const opt = await resOpt.json();
            if (!opt.success) throw new Error(opt.message);

            // 2. Solicitar assinatura (Biometria do Celular)
            const challenge = Uint8Array.from(atob(btoa(opt.challenge)), c => c.charCodeAt(0));
            const assertionOptions = {
                publicKey: {
                    challenge,
                    timeout: 60000,
                    userVerification: "required"
                }
            };

            const assertion = await navigator.credentials.get(assertionOptions);

            // 3. Validar no backend
            const loc = await _geoCache ? Promise.resolve(_geoCache) : obterLocalizacao();
            const verifyRes = await fetch('../api/ponto.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'ponto_webauthn',
                    credentialId: btoa(String.fromCharCode(...new Uint8Array(assertion.rawId))),
                    lat: loc?.lat,
                    lng: loc?.lng
                    // Em produção, enviaríamos também assertion.response para validar assinatura
                })
            });
            const data = await verifyRes.json();

            if (data.success) {
                Swal.fire({ title: 'Marcado!', html: `<b>${data.funcionario}</b><br>Ponto via Mobile às ${data.hora}`, icon: 'success' });
                window.fecharModalBiometria();
                return true;
            } else if (data.ficha_incompleta) {
                await Swal.fire({
                    title: 'Atualização Cadastral',
                    text: 'Sua ficha está incompleta. Clique em atualizar para abrir o formulário de atualização.',
                    icon: 'info',
                    confirmButtonText: 'Atualizar',
                    confirmButtonColor: '#0ea5e9'
                });
                const atualizou = await window.abrirModalFichaIncompleta(data);
                if (atualizou) {
                    Swal.fire('Dados Atualizados', 'Sua ficha foi completada. Use a biometria do celular novamente.', 'success');
                } else {
                    window.fecharModalBiometria();
                }
                return false;
            } else {
                if (!silent) {
                    Swal.fire({
                        title: 'Não Permitido',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'Fechar',
                        confirmButtonColor: '#0ea5e9'
                    });
                }
                return false;
            }

        } catch (err) {
            console.error(err);
            if (!silent && err.name !== 'NotAllowedError') { // Ignora se o usuário cancelou ou se for modo silencioso
                Swal.fire('Erro', 'Falha na biometria do celular: ' + err.message, 'error');
            }
            return false;
        }
    };

    // Trava de Acesso Administrativo para evitar saída da tela
    async function pedirAcessoAdmin(e) {
        e.preventDefault();

        const { value: formValues } = await Swal.fire({
            title: 'Acesso Restrito',
            html:
                '<p class="text-sm text-slate-500 mb-4">Insira credenciais para liberar a tela administrativa.</p>' +
                '<input id="swal-input1" class="swal2-input w-[80%] max-w-full mx-auto" placeholder="Usuário Administrativo">' +
                '<div class="relative w-[80%] mx-auto mt-2">' +
                '  <input id="swal-input2" type="password" class="swal2-input w-full m-0" placeholder="Senha">' +
                '  <button type="button" onclick="togglePassword(\'swal-input2\', this)" class="absolute right-3 top-1/2 -translate-y-1/2 p-2 text-slate-400 hover:text-brand-600 focus:outline-none">' +
                '    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>' +
                '  </button>' +
                '</div>',
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Liberar Acesso',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0ea5e9',
            preConfirm: () => {
                return [
                    document.getElementById('swal-input1').value,
                    document.getElementById('swal-input2').value
                ]
            }
        });

        if (formValues) {
            const username = formValues[0];
            const password = formValues[1];

            if (!username || !password) {
                Swal.fire('Erro', 'Preencha usuário e senha para liberar.', 'error');
                return;
            }

            // Efetua o login real e cria a sessão para evitar a tela de login duplo
            const res = await fetch('../api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'login', username, password })
            });
            const data = await res.json();

            if (data.success) {
                window.location.href = 'admin/index.php';
            } else {
                Swal.fire({
                    title: 'Acesso Negado',
                    text: 'Credenciais inválidas. Tela bloqueada.',
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            }
        }
    }
    // Inicia o carregamento dos modelos e funcionários ao carregar a página
    window.onload = () => {
        const matInput = document.getElementById('matricula');
        const bioMatInput = document.getElementById('bio_matricula');
        const senhaInput = document.getElementById('senha');
        if (matInput) matInput.value = '';
        if (bioMatInput) bioMatInput.value = '';
        if (senhaInput) senhaInput.value = '';
        _carregarFuncionarios();
        loadFaceModels(); // Carregar modelos imediatamente para evitar espera no momento do ponto
    };
</script>

<?php include 'layout/footer.php'; ?>
