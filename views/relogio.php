<?php
require_once '../config/Database.php';
include 'layout/header.php';
?>

<style>
    /* Ocultar barra de rolagem mas manter funcionalidade */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Micro-interações e Scanner */
    @keyframes scan-glow {

        0%,
        100% {
            opacity: 0.3;
            transform: scaleY(1);
        }

        50% {
            opacity: 0.8;
            transform: scaleY(1.2);
        }
    }

    @keyframes particle-flow {
        0% {
            transform: translateY(-100%) translateX(0);
            opacity: 0;
        }

        20% {
            opacity: 1;
        }

        80% {
            opacity: 1;
        }

        100% {
            transform: translateY(1000%) translateX(20px);
            opacity: 0;
        }
    }

    .scanner-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        overflow: hidden;
    }

    .scanner-beam {
        position: absolute;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, transparent, rgba(6, 182, 212, 0.8), transparent);
        box-shadow: 0 0 15px rgba(6, 182, 212, 0.5);
        z-index: 20;
        animation: scan 3s ease-in-out infinite;
    }

    .scanner-particle {
        position: absolute;
        width: 2px;
        height: 8px;
        background: rgba(6, 182, 212, 0.6);
        border-radius: 4px;
        animation: particle-flow 2s linear infinite;
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-5px);
        }

        50% {
            transform: translateX(5px);
        }

        75% {
            transform: translateX(-5px);
        }
    }

    .animate-shake {
        animation: shake 0.4s cubic-bezier(.36, .07, .19, .97) both;
    }

    @keyframes pulse-white {

        0%,
        100% {
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
        }

        50% {
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 0 20px 0 rgba(255, 255, 255, 0.6);
        }
    }

    .pulse-white {
        animation: pulse-white 2s infinite;
    }

    @keyframes ripple-green {
        0% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6), inset 0 0 0 0 rgba(16, 185, 129, 0.6);
        }

        100% {
            box-shadow: 0 0 0 40px rgba(16, 185, 129, 0), inset 0 0 0 20px rgba(16, 185, 129, 0);
            border-color: #10b981;
        }
    }

    .ripple-green {
        animation: ripple-green 1s ease-out;
    }

    /* Estilos Glassmorphism Premium */
    .glass-status {
        background: rgba(15, 23, 42, 0.7) !important;
        backdrop-filter: blur(12px) saturate(160%) !important;
        -webkit-backdrop-filter: blur(12px) saturate(160%) !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3) !important;
    }

    .glass-status-success {
        background: rgba(16, 185, 129, 0.5) !important;
        backdrop-filter: blur(10px) saturate(180%) !important;
        -webkit-backdrop-filter: blur(10px) saturate(180%) !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        box-shadow: 0 8px 32px 0 rgba(16, 185, 129, 0.2) !important;
    }

    .glass-status-error {
        background: rgba(239, 68, 68, 0.5) !important;
        backdrop-filter: blur(10px) saturate(180%) !important;
        -webkit-backdrop-filter: blur(10px) saturate(180%) !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        box-shadow: 0 8px 32px 0 rgba(239, 68, 68, 0.2) !important;
    }

    .glass-status-info {
        background: rgba(14, 165, 233, 0.5) !important;
        backdrop-filter: blur(10px) saturate(180%) !important;
        -webkit-backdrop-filter: blur(10px) saturate(180%) !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        box-shadow: 0 8px 32px 0 rgba(14, 165, 233, 0.2) !important;
    }

    @keyframes pulse-cyan {
        0% {
            box-shadow: 0 0 0 0 rgba(6, 182, 212, 0.4);
            border-color: rgba(6, 182, 212, 0.4);
        }

        70% {
            box-shadow: 0 0 0 15px rgba(6, 182, 212, 0);
            border-color: rgba(6, 182, 212, 0.8);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(6, 182, 212, 0);
            border-color: rgba(6, 182, 212, 0.4);
        }
    }

    .neon-pulse-cyan {
        animation: pulse-cyan 2s infinite;
    }
</style>

<!-- Fundo interativo do relógio -->
<div
    class="flex-1 flex items-center justify-center relative overflow-hidden bg-gradient-to-br from-brand-900 via-brand-600 to-sky-500">

    <!-- Design Elements em background -->
    <div
        class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCI+PHBhdGggZD0iTTAgMGgyeTR2MjRIMGoiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wMSkiLz48L3N2Zz4=')] opacity-20">
    </div>

    <div class="w-full max-w-lg relative z-10 px-4">
        <div
            class="glass rounded-3xl p-10 shadow-2xl relative overflow-hidden border border-white/20 min-h-[580px] flex flex-col pt-16">
            <!-- Header Card -->
            <div class="text-center">
                <h1
                    class="text-4xl font-extrabold text-slate-800 tracking-tight gap-2 flex justify-center items-center">
                    <svg class="w-8 h-8 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Ponto Digital
                </h1>
                <p class="text-slate-500 text-sm mt-2 font-medium">FUNAD &bull; Controle de Acesso</p>
            </div>

            <title>Relógio de Ponto - Biometria</title>
            <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

            <!-- Central Content (Relógio + Ações) -->
            <div class="flex-1 flex flex-col justify-center gap-6 mt-6 mb-6">
                <div class="text-center">
                    <div id="liveClock"
                        class="text-6xl sm:text-7xl font-black text-brand-600 tracking-tighter tabular-nums drop-shadow-sm transition-all">
                        00:00:00</div>
                    <div id="liveDate" class="text-sm font-medium text-slate-500 mt-2 uppercase tracking-widest">
                        Segunda, 01
                        de Janeiro</div>
                </div>

                <!-- Input de Matrícula -->
                <form id="pontoForm" onsubmit="registrarPonto(event)" class="space-y-6 hidden">
                    <!-- Campo de Matrícula oculto conforme solicitação (exclusivo biometria/identificação automática) -->
                    <input type="hidden" id="matricula" name="matricula" value="">
                    <div id="matriculaSugestoes" class="hidden"></div>

                    <div id="containerSenhaRelogio" class="mb-6 hidden">
                        <label for="senha" class="sr-only">Senha</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
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
                    <div class="relative flex py-4 items-center">
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
            </div> <!-- Fim do Central Content -->




            <!-- Links de Rodapé -->
            <div class="mt-auto pt-6 flex items-center justify-center gap-6 border-t border-slate-100">
                <a href="#" onclick="abrirMeuAcesso(event)"
                    class="text-sm font-bold text-brand-600 hover:text-brand-700 transition-colors flex items-center justify-center gap-1.5 px-3 py-1.5 bg-brand-50 rounded-lg border border-brand-100 shadow-sm hover:shadow-md active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Meus Pontos
                </a>
                <a href="#" onclick="pedirAcessoAdmin(event)"
                    class="text-sm font-semibold text-slate-500 hover:text-slate-700 transition-colors flex items-center justify-center gap-1">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    // Sincronização com o horário do BANCO DE DADOS (para garantir paridade com o registro)
    <?php
    $dbConn = Config\Database::getConnection();
    $dbRes = $dbConn->query("SELECT (EXTRACT(EPOCH FROM NOW()) * 1000) as ts")->fetch();
    $dbTimestamp = $dbRes['ts'];
    ?>
    const _serverTimeAtLoad = <?php echo $dbTimestamp; ?>;
    const _clientTimeAtLoad = Date.now();
    const _serverTimeOffset = _serverTimeAtLoad - _clientTimeAtLoad;

    function updateClock() {
        // Calcula o horário UTC atual baseado no offset do servidor
        const now = new Date(Date.now() + _serverTimeOffset);

        // Formata para o fuso horário oficial (Brasília)
        const dFormat = new Intl.DateTimeFormat('pt-BR', {
            dateStyle: 'full',
            timeZone: 'America/Sao_Paulo'
        }).format(now);
        const tFormat = new Intl.DateTimeFormat('pt-BR', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
            timeZone: 'America/Sao_Paulo'
        }).formatToParts(now);

        const parts = {};
        tFormat.forEach(p => parts[p.type] = p.value);

        const timeStr = `${parts.hour}:${parts.minute}:${parts.second}`;
        document.getElementById('liveClock').textContent = timeStr;
        document.getElementById('liveDate').textContent = dFormat;

        // Atualizar relógio no modal de biometria
        const bioClock = document.getElementById('bioClock');
        if (bioClock) bioClock.textContent = timeStr;
        const bioDate = document.getElementById('bioDate');
        if (bioDate) bioDate.textContent = now.toLocaleDateString('pt-BR');
    }

    // ---- Autocomplete Matrícula ----
    let _todosFunc = null;
    let listaHorarios = [];
    async function _carregarHorarios() {
        try {
            const res = await fetch('../api/horarios.php');
            const json = await res.json();
            if (json.success) listaHorarios = json.data;
        } catch (e) {
            console.error("Erro carregando horarios", e);
        }
    }

    window.selecionarHorarioRelogio = function(select) {
        const id = select.value;
        const day = select.dataset.updDay;
        if (!id) return;

        const horario = listaHorarios.find(h => h.id == id);
        if (horario) {
            const p1 = document.querySelector(`input[data-upd-day="${day}"][data-upd-p="p1"]`);
            const p2 = document.querySelector(`input[data-upd-day="${day}"][data-upd-p="p2"]`);
            const p3 = document.querySelector(`input[data-upd-day="${day}"][data-upd-p="p3"]`);
            const p4 = document.querySelector(`input[data-upd-day="${day}"][data-upd-p="p4"]`);

            if (p1) p1.value = horario.primeiro_horario ? horario.primeiro_horario.substring(0, 5) : '';
            if (p2) p2.value = horario.segundo_horario ? horario.segundo_horario.substring(0, 5) : '';
            if (p3) p3.value = horario.terceiro_horario ? horario.terceiro_horario.substring(0, 5) : '';
            if (p4) p4.value = horario.quarto_horario ? horario.quarto_horario.substring(0, 5) : '';

            // Feedback
            const card = document.querySelector(`[data-upd-day-card="${day}"]`);
            if (card) {
                card.classList.add('ring-2', 'ring-brand-500/50');
                setTimeout(() => card.classList.remove('ring-2', 'ring-brand-500/50'), 1000);
            }
        }
    };

    async function _carregarFuncionarios() {
        if (_todosFunc) return _todosFunc;
        // Inicia carregamento dos modelos em background enquanto carrega funcionários
        loadFaceModels();
        try {
            const res = await fetch('../api/funcionarios.php');
            const json = await res.json();
            if (json.success) _todosFunc = json.data;
        } catch (e) {
            _todosFunc = [];
        }
        return _todosFunc || [];
    }

    async function buscarMatriculas(valor) {
        const dropdown = document.getElementById('matriculaSugestoes');
        valor = valor.toUpperCase().trim();
        if (valor.length < 1) {
            dropdown.classList.add('hidden');
            dropdown.innerHTML = '';
            return;
        }

        const lista = await _carregarFuncionarios();
        const filtrados = lista.filter(f =>
            f.matricula.toUpperCase().includes(valor) || f.nome.toUpperCase().includes(valor)
        ).slice(0, 8);

        if (filtrados.length === 0) {
            dropdown.classList.add('hidden');
            dropdown.innerHTML = '';
            return;
        }

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
        registrarPonto({
            preventDefault: () => {}
        }, true);
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

            // Tenta primeiro com alta precisão (GPS), depois fallback para rede/wifi
            const optionsHigh = {
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 60000
            };
            const optionsLow = {
                enableHighAccuracy: false,
                timeout: 15000,
                maximumAge: 60000
            };

            const success = (pos) => {
                const loc = {
                    lat: pos.coords.latitude,
                    lng: pos.coords.longitude
                };
                _geoCache = loc;
                console.log("GPS OK:", loc.lat + ", " + loc.lng + " (Precisão: " + pos.coords.accuracy + "m)");
                resolve(loc);
            };

            const error = (err) => {
                console.warn("GPS Erro [" + err.code + "]: " + err.message);
                // Fallback para localização por rede/wifi se o satélite demorar
                navigator.geolocation.getCurrentPosition(success, (err2) => {
                    console.warn("GPS Fallback Erro [" + err2.code + "]: " + err2.message);
                    resolve(null);
                }, optionsLow);
            };

            navigator.geolocation.getCurrentPosition(success, error, optionsHigh);
        });
    }

    function haversineDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // Raio da Terra em metros
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    let currentFlow = 'identificacao'; // 'identificacao' ou 'autenticacao'
    let metodosPermitidos = [];
    let temWebAuthn = false;
    window.currentFuncionarioNome = ''; // Global para guardar o nome após identificação

    window.getMsgComNome = function(msg) {
        if (!window.currentFuncionarioNome || !msg) return msg;
        const nomeCurto = window.formatarNomeCurto(window.currentFuncionarioNome);

        // Evita duplicidade se o nome já estiver no início da mensagem
        const msgClean = msg.trim();
        if (msgClean.startsWith(nomeCurto) || msgClean.startsWith(window.currentFuncionarioNome)) {
            return msgClean;
        }

        return nomeCurto + ", " + msgClean;
    };

    window.toggleSenhaVisualRelogio = function() {
        const input = document.getElementById('senha');
        input.type = input.type === 'password' ? 'text' : 'password';
    };

    // Resetar fluxo se a matrícula mudar
    document.getElementById('matricula').addEventListener('input', function() {
        if (currentFlow === 'autenticacao') {
            resetarFluxo();
        }
    });

    function resetarFluxo() {
        currentFlow = 'identificacao';
        window.currentFuncionarioNome = ''; // Limpar nome para evitar persistência
        document.getElementById('containerSenhaRelogio').classList.add('hidden');
        document.getElementById('containerBiometria').classList.add('hidden');
        const btn = document.getElementById('btnSubmit');
        btn.innerHTML = `<span class="relative z-10 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Iniciar Identificação
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
            // window.abrirModalBiometria(); // Oculto conforme solicitação
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
                        // window.abrirModalBiometria(); // Oculto conforme solicitação
                    }
                }

                if (metodosPermitidos.length === 1 && metodosPermitidos.includes('facial')) {
                    btn.innerHTML = `<span class="relative z-10 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Registrar via Facial
                    </span>`;
                } else {
                    btn.innerHTML = `<span class="relative z-10 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Confirmar Ponto
                    </span>`;
                }

                if (!mostrouAlgo) {
                    Swal.fire('Atenção!', 'Nenhum método de acesso configurado.', 'warning');
                    resetarFluxo();
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

    window.obterDadosFuncionario = async function(matricula) {
        if (!matricula) return false;
        try {
            const res = await fetch('../api/ponto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'get_metodos',
                    matricula
                })
            });
            const data = await res.json();

            // Tenta capturar o nome do funcionário mesmo em caso de erro (ex: todos pontos batidos)
            if (data.funcionario || data.nome) {
                window.currentFuncionarioNome = data.funcionario || data.nome;
            }

            if (data.success) {
                // Verificar se a ficha está incompleta
                if (data.ficha_incompleta) {
                    const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
                    const msgFicha = isLocal ?
                        'O ponto não pode ser batido nos Quiosques de Ponto Fixo da FUNAD. Use outro meio de acesso para completar sua ficha.' :
                        'Sua ficha está incompleta. Clique em atualizar para abrir o formulário de atualização.';

                    if (isLocal) {
                        await Swal.fire({
                            title: window.getMsgComNome('Atualização Necessária'),
                            text: msgFicha,
                            icon: 'warning',
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#94a3b8'
                        });
                        currentFlow = 'identificacao';
                        resetarFluxo();
                        return false;
                    }

                    await Swal.fire({
                        title: 'Atualização Cadastral',
                        text: msgFicha,
                        icon: 'info',
                        confirmButtonText: 'Atualizar',
                        confirmButtonColor: '#0ea5e9'
                    }).then(() => window.fecharModalBiometria());
                    const atualizou = await window.abrirModalFichaIncompleta(data);
                    if (!atualizou) {
                        currentFlow = 'identificacao';
                        resetarFluxo();
                        return false;
                    }
                }

                metodosPermitidos = data.metodos;
                temWebAuthn = !!data.has_webauthn;
                window.currentFuncionarioNome = data.nome || data.funcionario || '';

                // ---- Validação de Geofencing (Novo) ----
                if (data.geofencing) {
                    const loc = _geoCache || await obterLocalizacao();
                    const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';

                    if (!loc && !isLocal) {
                        // Bypass de GPS se o reconhecimento foi muito preciso (distancia < 0.22)
                        // Isso ajuda em máquinas de mesa (Quiosque) sem sinal GPS
                        if (data.distancia && data.distancia < 0.22) {
                            console.warn("GPS falhou, mas reconhecimento é de alta confiança. Permitindo...");
                        } else {
                            const nomeCurto = window.formatarNomeCurto(window.currentFuncionarioNome);
                            const msg = `${nomeCurto}, não conseguimos localizar seu sinal de GPS. Por favor, verifique se a localização está permitida nas configurações do seu navegador (no ícone do cadeado).`;
                            Swal.fire({
                                title: 'Sinal de GPS não encontrado',
                                text: msg,
                                icon: 'warning',
                                confirmButtonColor: '#0ea5e9'
                            });
                            await window.falarTexto(msg);
                            resetarFluxo();
                            return false;
                        }
                    }

                    if (loc) {
                        const dist = haversineDistance(data.geofencing.lat, data.geofencing.lng, loc.lat, loc.lng);

                        if (dist > data.geofencing.dist && !isLocal) {
                            const nomeCurto = window.formatarNomeCurto(window.currentFuncionarioNome);
                            const distExibir = dist > 1000 ? (dist / 1000).toFixed(1) + "km" : Math.round(dist) + " metros";
                            const msg = `${nomeCurto}, você está a ${distExibir} do centro de trabalho, mas o limite é ${data.geofencing.dist} metros. Por favor, locomova-se para mais próximo do centro de sua área de trabalho.`;

                            Swal.fire({
                                title: 'Fora do Raio',
                                html: `<b>${nomeCurto}</b>, você está a <b>${distExibir}</b> do centro de trabalho, mas o limite é <b>${data.geofencing.dist} metros</b>. Por favor, locomova-se para mais próximo do centro de sua área de trabalho.`,
                                icon: 'error',
                                confirmButtonColor: '#0ea5e9'
                            });
                            await window.falarTexto(msg);
                            resetarFluxo();
                            return false;
                        }
                        console.log(`Geofencing OK: ${Math.round(dist)}m de ${data.geofencing.nome}`);
                    }
                }

                currentFlow = 'autenticacao';
                return true;
            } else {
                Swal.fire({
                    title: window.getMsgComNome('Ops!'),
                    text: data.message,
                    icon: 'error',
                    confirmButtonColor: '#0ea5e9'
                });
                return false;
            }
        } catch (err) {
            console.error("Erro ao obter dados:", err);
            return false;
        }
    };

    window.abrirModalFichaIncompleta = async function(data) {
        if (listaHorarios.length === 0) await _carregarHorarios();

        return new Promise(async (resolve) => {
            const func = data;


            const {
                value: formValues
            } = await Swal.fire({
                title: 'Atualização Necessária',
                html: `
                    <p class="text-[11px] text-slate-500 mb-6 uppercase tracking-wider font-bold">Por favor, complete sua ficha para conformidade com o E-Social.</p>
                    <div class="text-left max-h-[70vh] overflow-y-auto px-2 custom-scrollbar space-y-8">
                        
                        <!-- Seção 1: Dados Pessoais -->
                        <div class="border border-slate-100 rounded-2xl overflow-hidden bg-white/50 shadow-sm transition-all duration-300">
                            <button type="button" onclick="window.toggleSeccaoRelogio('upd_sec_1')" class="w-full flex items-center justify-between p-4 bg-slate-50/50 hover:bg-slate-50 transition-colors text-left outline-none">
                                <div class="flex items-center gap-3">
                                    <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                                    <h3 class="text-[10px] font-black text-slate-700 uppercase tracking-widest">1. Identificação Pessoal</h3>
                                </div>
                                <svg id="upd_icon_1" class="w-4 h-4 text-slate-400 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div id="upd_sec_1" class="p-4 space-y-4">
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
                        <div class="border border-slate-100 rounded-2xl overflow-hidden bg-white/50 shadow-sm transition-all duration-300">
                            <button type="button" onclick="window.toggleSeccaoRelogio('upd_sec_2')" class="w-full flex items-center justify-between p-4 bg-slate-50/50 hover:bg-slate-50 transition-colors text-left outline-none">
                                <div class="flex items-center gap-3">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    <h3 class="text-[10px] font-black text-slate-700 uppercase tracking-widest">2. Origem e Nacionalidade</h3>
                                </div>
                                <svg id="upd_icon_2" class="w-4 h-4 text-slate-400 rotate-0 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div id="upd_sec_2" class="p-4 space-y-4 hidden">
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
                        </div>

                        <!-- Seção 3: Documentação -->
                        <div class="border border-slate-100 rounded-2xl overflow-hidden bg-white/50 shadow-sm transition-all duration-300">
                            <button type="button" onclick="window.toggleSeccaoRelogio('upd_sec_3')" class="w-full flex items-center justify-between p-4 bg-slate-50/50 hover:bg-slate-50 transition-colors text-left outline-none">
                                <div class="flex items-center gap-3">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                    <h3 class="text-[10px] font-black text-slate-700 uppercase tracking-widest">3. Documentação Social e Profissional</h3>
                                </div>
                                <svg id="upd_icon_3" class="w-4 h-4 text-slate-400 rotate-0 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div id="upd_sec_3" class="p-4 space-y-4 hidden">
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
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">CTPS NÂº</label>
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
                            <div class="grid grid-cols-3 gap-2">
                                <div class="col-span-2 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Cert. Reservista</label>
                                    <input id="upd_reservista" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none" value="${func.reservista_numero || ''}">
                                </div>
                                <div class="col-span-1 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Série</label>
                                    <input id="upd_reservista_serie" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center" value="${func.reservista_serie || ''}">
                                </div>
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
                        </div>

                        <!-- Seção 4: Endereço e Contato -->
                        <div class="border border-slate-100 rounded-2xl overflow-hidden bg-white/50 shadow-sm transition-all duration-300">
                            <button type="button" onclick="window.toggleSeccaoRelogio('upd_sec_4')" class="w-full flex items-center justify-between p-4 bg-slate-50/50 hover:bg-slate-50 transition-colors text-left outline-none">
                                <div class="flex items-center gap-3">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <h3 class="text-[10px] font-black text-slate-700 uppercase tracking-widest">4. Residência & Contato</h3>
                                </div>
                                <svg id="upd_icon_4" class="w-4 h-4 text-slate-400 rotate-0 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div id="upd_sec_4" class="p-4 space-y-4 hidden">
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
                                <div class="col-span-1 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Complemento</label>
                                    <input id="upd_complemento" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-medium outline-none text-center" value="${func.endereco_complemento || ''}" placeholder="Apto, Bloco...">
                                </div>
                                <div class="col-span-2 group">
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
                        </div>

                        <!-- Seção 5: Família -->
                        <div class="border border-slate-100 rounded-2xl overflow-hidden bg-white/50 shadow-sm transition-all duration-300">
                            <button type="button" onclick="window.toggleSeccaoRelogio('upd_sec_5')" class="w-full flex items-center justify-between p-4 bg-slate-50/50 hover:bg-slate-50 transition-colors text-left outline-none">
                                <div class="flex items-center gap-3">
                                    <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                                    <h3 class="text-[10px] font-black text-slate-700 uppercase tracking-widest">5. Filiação & Família</h3>
                                </div>
                                <svg id="upd_icon_5" class="w-4 h-4 text-slate-400 rotate-0 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div id="upd_sec_5" class="p-4 space-y-4 hidden">
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
                            <div class="grid grid-cols-3 gap-2">
                                <div class="col-span-2 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Naturalidade Cônjuge</label>
                                    <input id="upd_conjuge_nat" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-xs font-medium outline-none" value="${func.naturalidade_conjuge || ''}" placeholder="Cidade Natal">
                                </div>
                                <div class="col-span-1 group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">UF Natal</label>
                                    <input id="upd_conjuge_nat_uf" maxlength="2" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center uppercase" value="${func.naturalidade_uf_conjuge || ''}" placeholder="PB">
                                </div>
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
                        </div>

                        <!-- Seção 6: Financeiro -->
                        <div class="border border-slate-100 rounded-2xl overflow-hidden bg-white/50 shadow-sm transition-all duration-300">
                            <button type="button" onclick="window.toggleSeccaoRelogio('upd_sec_6')" class="w-full flex items-center justify-between p-4 bg-slate-50/50 hover:bg-slate-50 transition-colors text-left outline-none">
                                <div class="flex items-center gap-3">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <h3 class="text-[10px] font-black text-slate-700 uppercase tracking-widest">6. Dados Bancários</h3>
                                </div>
                                <svg id="upd_icon_6" class="w-4 h-4 text-slate-400 rotate-0 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div id="upd_sec_6" class="p-4 space-y-4 hidden">
                                <div class="group">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Banco</label>
                                    <input id="upd_banco" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-medium outline-none" value="${func.banco_nome || ''}" placeholder="Nome do Banco">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="group">
                                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Agência</label>
                                        <input id="upd_agencia" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center" value="${func.banco_agencia || ''}" placeholder="0000">
                                    </div>
                                    <div class="group">
                                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Conta (com dígito)</label>
                                        <input id="upd_conta" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none text-center" value="${func.banco_conta || ''}" placeholder="00000-0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Seção 7: Termo de Ciência e Autorização -->
                        <div class="border border-slate-100 rounded-2xl overflow-hidden bg-white/50 shadow-sm transition-all duration-300">
                            <button type="button" onclick="window.toggleSeccaoRelogio('upd_sec_7')" class="w-full flex items-center justify-between p-4 bg-slate-50/50 hover:bg-slate-50 transition-colors text-left outline-none">
                                <div class="flex items-center gap-3">
                                    <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                                    <h3 class="text-[10px] font-black text-slate-700 uppercase tracking-widest">7. Termo de Ciência e Consentimento</h3>
                                </div>
                                <svg id="upd_icon_7" class="w-4 h-4 text-slate-400 rotate-0 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div id="upd_sec_7" class="p-4 space-y-4">
                                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                                    <p class="text-[11px] text-justify leading-relaxed text-slate-600">
                                        Declaro que fui informado(a) de forma clara sobre o tratamento dos meus dados biométricos faciais no âmbito deste órgão público. Estou ciente de que o tratamento poderá ocorrer independentemente do consentimento, quando fundamentado em obrigação legal ou interesse público, conforme a legislação vigente.
                                    </p>
                                </div>
                                
                                <div class="space-y-3">
                                    <div class="group">
                                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Órgão/Entidade</label>
                                        <input id="upd_termo_orgao" class="w-full px-4 py-2 bg-white border border-slate-100 rounded-xl text-xs font-bold outline-none" value="${(() => { try { const t = typeof func.termo_dados === 'string' ? JSON.parse(func.termo_dados) : func.termo_dados; return t?.orgao || 'FUNAD – CENTRO INTEGRADO DE APOIO À PESSOA COM DEFICIÊNCIA'; } catch (e) { return 'FUNAD – CENTRO INTEGRADO DE APOIO À PESSOA COM DEFICIÊNCIA'; } })()}">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="group">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">CNPJ</label>
                                            <input id="upd_termo_cnpj" class="w-full px-4 py-2 bg-white border border-slate-100 rounded-xl text-xs font-bold outline-none" value="${(() => { try { const t = typeof func.termo_dados === 'string' ? JSON.parse(func.termo_dados) : func.termo_dados; return t?.cnpj || '24.507.865/0001-07'; } catch (e) { return '24.507.865/0001-07'; } })()}">
                                        </div>
                                        <div class="group">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Contato DPO</label>
                                            <input id="upd_termo_dpo_contato" class="w-full px-4 py-2 bg-white border border-slate-100 rounded-xl text-[10px] font-bold outline-none" value="${(() => { try { const t = typeof func.termo_dados === 'string' ? JSON.parse(func.termo_dados) : func.termo_dados; return t?.dpo_contato || 'lgpd@funad.pb.gov.br'; } catch (e) { return 'lgpd@funad.pb.gov.br'; } })()}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2 pt-2">
                                    <input type="checkbox" id="upd_aceite_termo" checked class="w-4 h-4 text-brand-600 rounded">
                                    <label for="upd_aceite_termo" class="text-[10px] font-bold text-slate-500 uppercase">Li e concordo com os termos descritos</label>
                                </div>
                            </div>
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
                preConfirm: async () => {
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
                        reservista_serie: document.getElementById('upd_reservista_serie').value,
                        escolaridade: document.getElementById('upd_escolaridade').value,
                        endereco_cep: document.getElementById('upd_cep').value,
                        endereco: document.getElementById('upd_endereco').value,
                        endereco_numero: document.getElementById('upd_numero').value,
                        endereco_complemento: document.getElementById('upd_complemento').value,
                        endereco_bairro: document.getElementById('upd_bairro').value,
                        endereco_municipio: document.getElementById('upd_municipio').value,
                        endereco_uf: document.getElementById('upd_uf').value.toUpperCase(),
                        celular: document.getElementById('upd_celular').value,
                        endereco_email: document.getElementById('upd_email').value,
                        nome_mae: document.getElementById('upd_nome_mae').value,
                        nome_pai: document.getElementById('upd_nome_pai').value,
                        nome_conjuge: document.getElementById('upd_conjuge').value,
                        naturalidade_conjuge: document.getElementById('upd_conjuge_nat').value,
                        naturalidade_uf_conjuge: document.getElementById('upd_conjuge_nat_uf').value.toUpperCase(),
                        filhos_menores: Array.from({
                            length: parseInt(document.getElementById('upd_filhos_menores').value) || 0
                        }, () => ({
                            nome: '',
                            nasc: '',
                            cpf: ''
                        })),
                        dependentes_ir: Array.from({
                            length: parseInt(document.getElementById('upd_dependentes_ir').value) || 0
                        }, () => ({
                            nome: '',
                            nasc: '',
                            cpf: ''
                        })),
                        banco_nome: document.getElementById('upd_banco').value,
                        banco_agencia: document.getElementById('upd_agencia').value,
                        banco_conta: document.getElementById('upd_conta').value,
                        termo_dados: {
                            orgao: document.getElementById('upd_termo_orgao').value,
                            cnpj: document.getElementById('upd_termo_cnpj').value,
                            dpo_contato: document.getElementById('upd_termo_dpo_contato').value,
                            aceite_em: new Date().toISOString(),
                            aceite_ip: '<?php echo $_SERVER["REMOTE_ADDR"]; ?>'
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

                    const confirmBtn = Swal.getConfirmButton();
                    window.setLoading(confirmBtn, true);

                    try {
                        const updRes = await fetch('../api/funcionarios.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                action: 'update_ficha',
                                id: data.id,
                                ...fields
                            })
                        });
                        const updData = await updRes.json();
                        window.setLoading(confirmBtn, false);

                        if (updData.success) {
                            return true;
                        } else {
                            Swal.showValidationMessage(`Erro: ${updData.message}`);
                            return false;
                        }
                    } catch (e) {
                        window.setLoading(confirmBtn, false);
                        Swal.showValidationMessage(`Erro de conexão: ${e.message}`);
                        return false;
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Ficha Atualizada!',
                        text: 'Seus dados foram salvos. Agora você pode registrar seu ponto.',
                        icon: 'success',
                        confirmButtonColor: '#0ea5e9'
                    });
                    resolve(true);
                } else {
                    resolve(false);
                }
            });
        });
    };

    window.consultarCEPRelogio = async function() {
        const cepInput = document.getElementById('upd_cep');
        if (!cepInput) return;

        let cep = cepInput.value.replace(/\D/g, '');
        if (cep.length !== 8) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: 'CEP inválido',
                showConfirmButton: false,
                timer: 3000
            });
            return;
        }

        cepInput.classList.add('ring-2', 'ring-brand-500/50');

        try {
            const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await res.json();

            if (!data.erro) {
                document.getElementById('upd_endereco').value = data.logradouro || '';
                document.getElementById('upd_bairro').value = data.bairro || '';
                document.getElementById('upd_municipio').value = data.localidade || '';
                document.getElementById('upd_uf').value = data.uf || '';

                // Feedback visual
                ['upd_endereco', 'upd_bairro', 'upd_municipio', 'upd_uf'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.classList.add('ring-2', 'ring-emerald-500/50');
                        setTimeout(() => el.classList.remove('ring-2', 'ring-emerald-500/50'), 2000);
                    }
                });

                document.getElementById('upd_numero').focus();
            } else {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'CEP não encontrado',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        } catch (e) {
            console.error("Erro ao buscar CEP:", e);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'Falha na conexão',
                showConfirmButton: false,
                timer: 3000
            });
        } finally {
            cepInput.classList.remove('ring-2', 'ring-brand-500/50');
        }
    };

    window.replicarSegundaRelogio = function() {
        const p1 = document.querySelector('input[data-upd-day="segunda"][data-upd-p="p1"]').value;
        const p2 = document.querySelector('input[data-upd-day="segunda"][data-upd-p="p2"]').value;
        const p3 = document.querySelector('input[data-upd-day="segunda"][data-upd-p="p3"]').value;
        const p4 = document.querySelector('input[data-upd-day="segunda"][data-upd-p="p4"]').value;
        const hId = document.querySelector('select[data-upd-day="segunda"]').value;

        document.querySelectorAll('.upd-time-input[data-upd-p="p1"]').forEach(el => el.value = p1);
        document.querySelectorAll('.upd-time-input[data-upd-p="p2"]').forEach(el => el.value = p2);
        document.querySelectorAll('.upd-time-input[data-upd-p="p3"]').forEach(el => el.value = p3);
        document.querySelectorAll('.upd-time-input[data-upd-p="p4"]').forEach(el => el.value = p4);
        document.querySelectorAll('.upd-horario-select').forEach(el => el.value = hId);

        // Feedback
        const cards = document.querySelectorAll('[data-upd-day-card]');
        cards.forEach(c => c.classList.add('ring-2', 'ring-brand-500/50'));
        setTimeout(() => {
            cards.forEach(c => c.classList.remove('ring-2', 'ring-brand-500/50'));
        }, 1000);
    };

    window.toggleSeccaoRelogio = function(id) {
        const el = document.getElementById(id);
        if (!el) return;

        const isHidden = el.classList.contains('hidden');
        const icon = document.getElementById('upd_icon_' + id.split('_').pop());

        // Oculta todas as outras se forem do mesmo container (opcional, vamos fazer apenas toggle por enquanto)

        if (isHidden) {
            el.classList.remove('hidden');
            if (icon) icon.classList.add('rotate-180');
        } else {
            el.classList.add('hidden');
            if (icon) icon.classList.remove('rotate-180');
        }
    };

    async function executarRegistroPonto(matricula, senha) {
        const btn = document.getElementById('btnSubmit');
        setLoading(btn, true);

        try {
            const loc = await _geoCache ? Promise.resolve(_geoCache) : obterLocalizacao();
            const res = await fetch('../api/ponto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'ponto_matricula',
                    matricula,
                    senha,
                    lat: loc?.lat,
                    lng: loc?.lng
                })
            });
            const data = await res.json();

            if (data.success) {
                const msgSucesso = `Ponto registrado com sucesso.`;
                await window.falarTexto(msgSucesso);
                Swal.fire({
                    title: 'Marcado!',
                    html: `<b>${data.funcionario}</b><br>${msgSucesso} em <b>${data.data_ponto}</b> às <b>${data.hora}</b>`,
                    icon: 'success',
                    showConfirmButton: true,
                    confirmButtonText: 'Fechar',
                    confirmButtonColor: '#0ea5e9'
                });
                document.getElementById('matricula').value = '';
                document.getElementById('senha').value = '';
                resetarFluxo();
            } else {
                if (data.ficha_incompleta) {
                    const msg = data.message;
                    await window.falarTexto(msg);
                    Swal.fire({
                        title: window.getMsgComNome('Ficha Incompleta'),
                        text: msg,
                        icon: 'warning',
                        confirmButtonText: 'Atualizar Agora',
                        confirmButtonColor: '#0ea5e9'
                    }).then(() => {
                        window.abrirModalFichaIncompleta(data);
                    });
                } else {
                    const nomeCurto = window.formatarNomeCurto(window.currentFuncionarioNome);
                    const personalizedMsg = `${nomeCurto}, ${data.message}`;
                    await window.falarTexto(personalizedMsg);
                    Swal.fire({
                        title: 'Aviso de Registro',
                        text: personalizedMsg,
                        icon: 'error',
                        confirmButtonColor: '#0ea5e9'
                    });
                }
            }
        } catch (err) {
            Swal.fire('Aviso', 'Falha na comunicação com o servidor.', 'error');
        } finally {
            setLoading(btn, false);
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

    // Novas variáveis para o Smart Reset (Reset automático ao mudar de pessoa)
    window.lastSuccessDescriptor = null;
    window.isStatusCooldown = false;
    window.cooldownTimer = null;

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
            console.log("Modelos Face-API (Detecção e Reconhecimento) carregados.");
        } catch (e) {
            console.error("Erro ao carregar modelos Face-API:", e);
            const statusEl = document.querySelector('#faceStatus span');
            if (statusEl) statusEl.innerText = "Erro ao carregar IA";
        }
    }

    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

    window.abrirModalBiometria = async function() {
        return; // Desativado conforme solicitação
        window.falarBiometria("Iniciando câmera, por favor aguarde", true);
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

        // Se metodosPermitidos estiver vazio (identificação inicial), permite apenas facial
        const podeFacial = true;
        const podeDigital = false;

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
        _geoCache ? Promise.resolve(_geoCache) : obterLocalizacao().then(loc => {
            if (loc) _geoCache = loc;
        });
    };

    window.fecharModalBiometria = function() {
        document.getElementById('modalBiometria').classList.add('hidden');
        detectionLoopActive = false;
        isProcessingFacial = false;
        landmarkHistory = [];
        isLivenessOk = false;
        window.currentFuncionarioNome = ''; // Limpar nome ao fechar
        const matInput = document.getElementById('matricula');
        const bioMatInput = document.getElementById('bio_matricula');
        if (matInput) matInput.value = '';
        if (bioMatInput) bioMatInput.value = '';

        window.pararStreamVideo();
        window.speechSynthesis.cancel();
        if (window.dpSocketClock) window.dpSocketClock.close();
    };

    window.mudarModoBiometria = function(modo) {
        document.getElementById('bio_modo').value = modo;

        // UI Tabs
        const tFacial = document.getElementById('tabFacial');
        const tDigital = document.getElementById('tabDigital');
        const aFacial = document.getElementById('areaFacial');
        const aDigital = document.getElementById('areaDigital');

        if (modo === 'facial') {
            if (tFacial) tFacial.className = "flex-1 py-2 text-xs font-bold rounded-xl transition-all border border-transparent bg-brand-50 text-brand-600 border-brand-100";
            if (tDigital) tDigital.className = "flex-1 py-2 text-xs font-bold rounded-xl transition-all border border-transparent text-slate-500 hover:bg-slate-50";
            if (aFacial) aFacial.classList.remove('hidden');
            if (aDigital) aDigital.classList.add('hidden');
            window.iniciarStreamVideo();
        } else {
            detectionLoopActive = false; // Parar loop se mudar para digital
            if (tDigital) tDigital.className = "flex-1 py-2 text-xs font-bold rounded-xl transition-all border border-transparent bg-emerald-50 text-emerald-600 border-emerald-100";
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

    // Utilitário de Vibração
    window.vibrarDispositivo = function(padrao) {
        if ("vibrate" in navigator) {
            try {
                navigator.vibrate(padrao);
            } catch (e) {}
        }
    };

    // Formatação de nome curto para fala (Primeiro + Segundo ou Primeiro + Preposição + Segundo)
    window.formatarNomeCurto = function(nomeFull) {
        if (!nomeFull) return "";
        const partes = nomeFull.trim().split(/\s+/);
        if (partes.length <= 1) return partes[0];

        const preposicoes = ["da", "de", "do", "das", "dos"];
        const p2 = partes[1].toLowerCase();

        if (preposicoes.includes(p2) && partes.length >= 3) {
            return partes[0] + " " + partes[1] + " " + partes[2];
        }

        return partes[0] + " " + partes[1];
    };

    // Utilitário de Sintese de Voz
    let ultimaFala = "";
    let ultimaVezFalada = 0;

    const getBestPTVoice = (synth) => {
        const voices = synth.getVoices();
        // Prioridade: Daniel (comum masculina PT-BR), Google PT-BR (geralmente tem versão masculina), qualquer PT-BR
        return voices.find(v => v.lang.includes('pt-BR') && (v.name.includes('Daniel') || v.name.includes('Male') || v.name.includes('Masculino'))) ||
            voices.find(v => v.lang.includes('pt-BR') && v.name.includes('Google')) ||
            voices.find(v => v.lang.includes('pt-BR'));
    };

    window.falarBiometria = function(texto, force = false) {
        return new Promise((resolve) => {
            if (!texto) {
                resolve();
                return;
            }

            const agora = Date.now();
            // Aumentado debounce para 20 segundos para mensagens de status repetitivas
            const tempoDebounce = (texto.includes("Buscando") || texto.includes("Reconhecendo")) ? 20000 : 3000;

            if (!force && texto === ultimaFala && (agora - ultimaVezFalada < tempoDebounce)) {
                resolve();
                return;
            }

            ultimaFala = texto;
            ultimaVezFalada = agora;

            const synth = window.speechSynthesis;
            const utter = new SpeechSynthesisUtterance(texto);
            utter.lang = 'pt-BR';
            utter.rate = 1.15;
            utter.pitch = 1.0;

            utter.onend = () => resolve();
            utter.onerror = (e) => {
                console.warn("SpeechSynthesis error:", e);
                resolve();
            };

            const ptVoice = getBestPTVoice(synth);
            if (ptVoice) utter.voice = ptVoice;

            if (force) synth.cancel();
            synth.speak(utter);
            setTimeout(() => resolve(), 5000);
        });
    };

    /**
     * Utilitário de Síntese de Voz com suporte a Promise
     */
    window.falarTexto = function(texto) {
        return new Promise((resolve) => {
            const synth = window.speechSynthesis;
            const utter = new SpeechSynthesisUtterance(texto);
            utter.lang = 'pt-BR';
            utter.rate = 1.05;

            utter.onend = () => resolve();
            utter.onerror = (e) => {
                console.warn("SpeechSynthesis error:", e);
                resolve();
            };

            const ptVoice = getBestPTVoice(synth);
            if (ptVoice) utter.voice = ptVoice;

            synth.speak(utter);
            setTimeout(() => resolve(), 10000);
        });
    };

    /**
     * Utilitário de Reconhecimento de Voz (Sim/Não)
     */
    window.ouvirComandoConsentimento = function() {
        return new Promise((resolve) => {
            const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!Recognition) {
                console.warn("Navegador não suporta SpeechRecognition.");
                return resolve(null);
            }

            const rec = new Recognition();
            rec.lang = 'pt-BR';
            rec.interimResults = false;
            rec.maxAlternatives = 1;

            rec.onresult = (event) => {
                const result = event.results[0][0].transcript.toLowerCase();
                console.log("Voz capturada:", result);

                if (result.includes('sim') || result.includes('aceito') || result.includes('concordo') || result.includes('positivo')) {
                    resolve('sim');
                } else if (result.includes('não') || result.includes('nao') || result.includes('nego') || result.includes('cancelar') || result.includes('negativo')) {
                    resolve('nao');
                } else {
                    resolve(null);
                }
            };

            rec.onerror = (err) => {
                console.error("Erro no reconhecimento de voz:", err);
                resolve(null);
            };

            try {
                rec.start();
            } catch (e) {
                resolve(null);
            }
        });
    };

    window.iniciarStreamVideo = async function() {
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
                video: {
                    facingMode: 'user'
                }
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

    window.pararStreamVideo = function() {
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
        }
    };


    async function startFaceDetectionLoop() {
        const modal = document.getElementById('modalBiometria');
        if (!modal || modal.classList.contains('hidden')) {
            detectionLoopActive = false;
            return;
        }

        const statusEl = document.querySelector('#faceStatus span');
        const container = document.getElementById('videoContainer');
        const video = document.getElementById('videoFeed');

        if (!modelsLoaded) {
            if (statusEl) statusEl.innerText = "Carregando IA...";
            if (detectionLoopActive) setTimeout(() => requestAnimationFrame(startFaceDetectionLoop), 500);
            return;
        }

        // Permitir que o loop continue se estiver em COOLDOWN para detectar mudança de pessoa
        if (!detectionLoopActive) return;

        // Se estiver processando e não estiver em cooldown, apenas aguarda um pouco e tenta de novo
        if (isProcessingFacial && !window.isStatusCooldown) {
            setTimeout(() => requestAnimationFrame(startFaceDetectionLoop), 500);
            return;
        }

        if (!video || video.paused || video.ended) {
            if (detectionLoopActive) requestAnimationFrame(startFaceDetectionLoop);
            return;
        }

        try {
            // Se tivermos um descritor de sucesso anterior, verificamos se a pessoa ainda está lá
            if (window.lastSuccessDescriptor) {
                const detectionWithDesc = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({
                    inputSize: 224,
                    scoreThreshold: 0.5
                })).withFaceLandmarks().withFaceDescriptor();

                if (detectionWithDesc && window.lastSuccessDescriptor) {
                    window._lastFaceDetectedTime = Date.now(); // Atualizar tempo da última detecção
                    const distance = faceapi.euclideanDistance(detectionWithDesc.descriptor, window.lastSuccessDescriptor);

                    // Se for a MESMA pessoa
                    if (distance <= 0.45) {
                        // Se ainda estiver no cooldown (4s iniciais), apenas espera
                        if (window.isStatusCooldown) {
                            if (detectionLoopActive) setTimeout(() => requestAnimationFrame(startFaceDetectionLoop), 500);
                            return;
                        }

                        // Se já passou o cooldown mas a pessoa não saiu da frente, avisa e espera ela sair
                        if (statusEl && !isProcessingFacial) {
                            statusEl.textContent = "Aguardando outra face";
                            statusEl.className = "glass-status text-white text-[9px] font-bold px-6 py-2.5 rounded-full uppercase tracking-widest transition-all shadow-xl";
                        }
                        // Resetar elementos visuais para estado neutro
                        if (oval) oval.className = "w-[85%] h-[80%] border-2 border-white/30 rounded-[50%] transition-all duration-300";
                        if (container) container.className = "relative w-full max-w-[280px] aspect-[4/5] bg-slate-900 rounded-[32px] overflow-hidden shadow-2xl border-2 border-white/5 transition-colors duration-300";
                        if (scanner) scanner.classList.add('hidden');

                        if (detectionLoopActive) setTimeout(() => requestAnimationFrame(startFaceDetectionLoop), 1000);
                        return;
                    } else {
                        // É uma pessoa DIFERENTE! Resetar e permitir nova batida
                        console.log("[SmartReset] Nova pessoa detectada. Liberando.");
                        window.resetarInterfacePosBatida();
                        // Importante: resetarInterfacePosBatida agora não limpa o descritor se isStatusCooldown for falso? 
                        // Na verdade, aqui queremos limpar para permitir a nova pessoa.
                        window.lastSuccessDescriptor = null;
                    }
                } else {
                    // Ninguém na frente da câmera! 
                    // Esperar 2 segundos de "vazio" antes de limpar o descritor de sucesso (evita revalidação acidental)
                    const agora = Date.now();
                    if (!window._lastFaceDetectedTime) window._lastFaceDetectedTime = agora;

                    if (agora - window._lastFaceDetectedTime > 2000) {
                        console.log("[SmartReset] Espaço vazio detectado por 2s. Limpando memória facial.");
                        window.lastSuccessDescriptor = null;
                        if (!window.isStatusCooldown) window.resetarInterfacePosBatida();
                    }
                }
            }

            // Fluxo normal de detecção
            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({
                inputSize: 224,
                scoreThreshold: 0.4
            }));

            const oval = document.getElementById('faceOvalBorder');
            const scanner = document.getElementById('scannerLine');
            const progressBar = document.getElementById('progressBar');

            if (detection && detection.score > 0.55 && !isProcessingFacial) {
                const box = detection.box;
                const centerX = box.x + box.width / 2;
                const centerY = box.y + box.height / 2;

                // Dimensões do vídeo
                const vWidth = video.videoWidth;
                const vHeight = video.videoHeight;

                // Verificar se o centro do rosto está na região central (tolerância de 15%)
                const isCenteredX = Math.abs(centerX - vWidth / 2) < (vWidth * 0.15);
                const isCenteredY = Math.abs(centerY - vHeight / 2) < (vHeight * 0.15);

                if (!isCenteredX || !isCenteredY) {
                    if (statusEl) {
                        statusEl.textContent = "Centralize seu rosto";
                        statusEl.className = "bg-slate-900/90 text-white text-[9px] font-black px-5 py-2 rounded-full uppercase tracking-widest border border-white/10 shadow-2xl transition-all";
                    }
                    if (oval) oval.className = "w-[85%] h-[80%] border-[3px] border-dashed border-white/50 rounded-[50%] transition-all duration-500 pulse-white";
                    window._stabilityCount = 0;
                    if (detectionLoopActive) setTimeout(() => requestAnimationFrame(startFaceDetectionLoop), 100);
                    return;
                }

                // Face detectada, CENTRALIZADA e ALINHADA -> Incrementar estabilidade
                window._stabilityCount = (window._stabilityCount || 0) + 1;

                if (scanner) scanner.classList.remove('hidden');
                if (container) {
                    container.className = "relative w-full max-w-[280px] aspect-[4/5] bg-slate-50 rounded-[32px] overflow-hidden shadow-lg border-4 border-cyan-500 transition-colors duration-300";
                }
                if (oval) {
                    oval.className = "w-[85%] h-[80%] border-2 border-cyan-400 rounded-[50%] transition-all duration-300 neon-pulse-cyan";
                }

                // Progresso visual da estabilidade
                if (progressBar) progressBar.style.width = (window._stabilityCount * 33) + '%';

                if (statusEl) {
                    statusEl.textContent = window.getMsgComNome("Mantenha o rosto parado...");
                    statusEl.className = "glass-status-info text-white text-[10px] font-bold px-6 py-2.5 rounded-full uppercase tracking-widest transition-all shadow-lg";
                }

                if (window._stabilityCount >= 3) {
                    if (scanner) scanner.classList.add('hidden');
                    if (container) {
                        container.className = "relative w-full max-w-[280px] aspect-[4/5] bg-slate-50 rounded-[32px] overflow-hidden shadow-lg border-4 border-emerald-500 transition-colors duration-300";
                    }
                    if (oval) {
                        oval.className = "w-[85%] h-[80%] border-[3px] border-solid border-emerald-500 rounded-[50%] transition-all duration-300 shadow-[0_0_30px_rgba(16,185,129,0.4)]";
                    }
                    if (progressBar) progressBar.style.width = '100%';

                    if (statusEl) {
                        statusEl.textContent = window.getMsgComNome("Validando...");
                        statusEl.className = "glass-status-success text-white text-[10px] font-bold px-6 py-2.5 rounded-full uppercase tracking-widest transition-all shadow-[0_0_20px_rgba(16,185,129,0.3)]";
                        window.falarBiometria("Validando", true);
                    }

                    // IMPORTANTE: Agendar o próximo frame ANTES de chamar tirarFotoFacial 
                    // para manter o loop vivo em estado de espera (isProcessingFacial)
                    if (detectionLoopActive) setTimeout(() => requestAnimationFrame(startFaceDetectionLoop), 500);

                    window.tirarFotoFacial(null);
                    return;
                }
            } else if (detection) {
                // Detectou mas com score menor, apenas mostra que está reconhecendo
                if (scanner) scanner.classList.remove('hidden');
                if (container) {
                    container.className = "relative w-full max-w-[280px] aspect-[4/5] bg-slate-50 rounded-[32px] overflow-hidden shadow-lg border-4 border-cyan-500 transition-colors duration-300";
                }
                if (oval) {
                    oval.className = "w-[85%] h-[80%] border-[3px] border-dashed border-cyan-500 rounded-[50%] transition-all duration-300";
                }
                if (progressBar) progressBar.style.width = '50%';

                if (statusEl) {
                    statusEl.textContent = window.getMsgComNome("Reconhecendo...");
                    statusEl.className = "glass-status-info text-white text-[10px] font-bold px-6 py-2.5 rounded-full uppercase tracking-widest transition-all shadow-lg";
                    window.falarBiometria("Reconhecendo face");
                }
            } else {
                if (scanner) scanner.classList.remove('hidden');
                if (oval) oval.className = "w-[85%] h-[80%] border-[3px] border-dashed rounded-[50%] transition-all duration-500 pulse-white";
                if (container) {
                    container.className = "relative w-full max-w-[280px] aspect-[4/5] bg-slate-50 rounded-[32px] overflow-hidden shadow-lg border-4 border-slate-50 transition-colors duration-300";
                }
                if (progressBar) progressBar.style.width = '0%';

                if (statusEl) {
                    window.currentFuncionarioNome = ''; // Reset nome enquanto busca
                    statusEl.textContent = "Buscando Face...";
                    statusEl.className = "bg-black/60 backdrop-blur-md text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest border border-white/10";
                    window.falarBiometria("Buscando face");
                }
            }
        } catch (e) {
            console.error("Erro no loop de detecção:", e);
        }

        if (detectionLoopActive) {
            setTimeout(() => requestAnimationFrame(startFaceDetectionLoop), 100);
        }
    }

    function iniciarContadorOval(callback) {
        const overlay = document.getElementById('countdownOval');
        const numEl = document.getElementById('countdownNumber');
        const ring = document.getElementById('countdownRing');
        if (!overlay || !numEl) {
            setTimeout(callback, 3000);
            return;
        }

        const circumference = 304.7; // 2 * PI * 48.5
        overlay.classList.remove('hidden');

        numEl.textContent = '3';
        numEl.style.transform = 'scale(1)';
        numEl.style.opacity = '1';

        if (ring) {
            ring.style.transition = 'none';
            ring.style.strokeDashoffset = '0';
            ring.getBoundingClientRect(); // força reflow para a transição funcionar
            ring.style.transition = 'stroke-dashoffset 3s linear';
            ring.style.strokeDashoffset = String(circumference);
        }

        // Troca animada dos números a cada segundo
        [{
            t: 1000,
            n: '2'
        }, {
            t: 2000,
            n: '1'
        }].forEach(({
            t,
            n
        }) => {
            setTimeout(() => {
                numEl.style.transform = 'scale(1.6)';
                numEl.style.opacity = '0.2';
                setTimeout(() => {
                    numEl.textContent = n;
                    numEl.style.transform = 'scale(1)';
                    numEl.style.opacity = '1';
                }, 130);
            }, t);
        });

        setTimeout(() => {
            overlay.classList.add('hidden');
            callback();
        }, 3000);
    }

    window.tirarFotoFacial = async function(descriptorPreExtraido) {
        if (!videoStream || isProcessingFacial) return;

        // Se estiver no modo 1:N (sem matrícula prévia), limpa o nome para evitar persistência do usuário anterior
        if (!document.getElementById('bio_matricula').value) {
            window.currentFuncionarioNome = '';
        }

        const video = document.getElementById('videoFeed');
        const canvas = document.getElementById('videoCanvas');
        const btn = document.getElementById('btnCapturarFacial');

        isProcessingFacial = true; // Bloqueia múltiplas execuções

        const MAX_WIDTH = 640;
        const scale = video.videoWidth > MAX_WIDTH ? MAX_WIDTH / video.videoWidth : 1;
        canvas.width = video.videoWidth * scale;
        canvas.height = video.videoHeight * scale;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

        if (btn) {
            btn.innerHTML = "Mapeando Face...";
            btn.disabled = true;
        }

        const oval = document.getElementById('faceOvalBorder');
        const container = document.getElementById('videoContainer');
        if (oval) oval.className = "w-[85%] h-[80%] border-[3px] border-solid border-emerald-500 rounded-[50%] transition-all duration-300 ripple-green";

        const statusEl = document.querySelector('#faceStatus span');
        if (statusEl) statusEl.textContent = window.getMsgComNome("Aguarde...");

        try {
            // Extração de detecção para o recorte inteligente
            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();

            if (detection) {
                // Armazenar o descritor para detecção de mudança de pessoa (Smart Reset)
                window.lastSuccessDescriptor = detection.descriptor;

                // --- RECORTE INTELIGENTE ---
                const box = detection.detection.box;
                const pad = 0.25; // 25% de margem extra

                // Calcular coordenadas do recorte com padding
                let cropX = box.x - (box.width * pad);
                let cropY = box.y - (box.height * pad);
                let cropW = box.width * (1 + pad * 2);
                let cropH = box.height * (1 + pad * 2);

                // Garantir que o recorte está dentro dos limites do vídeo
                cropX = Math.max(0, cropX);
                cropY = Math.max(0, cropY);
                cropW = Math.min(video.videoWidth - cropX, cropW);
                cropH = Math.min(video.videoHeight - cropY, cropH);

                // Redimensionar o canvas para o tamanho do recorte (mantendo qualidade para o DeepFace)
                // Usamos um tamanho padrão de 300px para garantir alta precisão no backend
                canvas.width = 300;
                canvas.height = 300;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, cropX, cropY, cropW, cropH, 0, 0, 300, 300);
            } else {
                // Fallback caso a detecção falhe no exato momento do clique (usa o frame inteiro)
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
            }

            // Congelar imagem: Esconder vídeo e mostrar canvas
            video.classList.add('hidden');
            canvas.classList.remove('hidden');

            let base64Image = canvas.toDataURL('image/jpeg', 0.85); // Aumentada qualidade para compesar o tamanho menor


            if (btn) btn.innerHTML = "Validando Face...";
            const loc = _geoCache ? _geoCache : await obterLocalizacao();

            const response = await fetch('../api/ponto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'ponto_facial', // Forçar ação de ponto
                    matricula: document.getElementById('bio_matricula').value,
                    image_data: base64Image,
                    lat: loc?.lat,
                    lng: loc?.lng
                })
            });

            const data = await response.json();

            if (data.success) {
                if (data.funcionario) window.currentFuncionarioNome = data.funcionario;
                const nomeCurto = window.formatarNomeCurto(data.funcionario || "");

                if (statusEl) {
                    statusEl.textContent = `Reconhecido: ${nomeCurto}`;
                    statusEl.className = "glass-status-success text-white text-[10px] font-bold px-6 py-2.5 rounded-full uppercase tracking-widest transition-all shadow-[0_0_20px_rgba(16,185,129,0.3)]";
                }

                await window.falarBiometria(`${nomeCurto}, reconhecido.`, true);

                window.lastCaptureWasError = false;
                if (data.termo_pendente) {
                    exibirTermoUsoImagem(data, base64Image, null, loc);
                } else {
                    processarSucessoRegistro(data);
                }
            } else {
                if (data.funcionario) window.currentFuncionarioNome = data.funcionario;
                console.warn("[Facial] Servidor retornou falha:", data.message);

                video.classList.remove('hidden');
                canvas.classList.add('hidden');

                const erroReconhecimento = !data.message || data.message.startsWith('Face não reconhecida');

                if (erroReconhecimento) {
                    // Falha de reconhecimento: orientar o usuário a reposicionar e tentar de novo
                    const msg = "Não foi possível identificar. Posicione o rosto centralizado e bem iluminado.";
                    window.lastCaptureWasError = true;
                    if (statusEl) {
                        statusEl.textContent = msg;
                        statusEl.className = "glass-status-error text-white text-[10px] font-bold px-6 py-2.5 rounded-full uppercase tracking-widest transition-all animate-bounce shadow-[0_0_20px_rgba(239,68,68,0.3)]";
                    }
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = 'Aguarde...';
                    }
                    if (oval) oval.className = "w-[85%] h-[80%] border-[3px] border-dashed border-red-500 rounded-[50%] transition-all duration-300";
                    if (container) container.className = "relative w-full max-w-[280px] aspect-[4/5] bg-slate-50 rounded-[32px] overflow-hidden shadow-lg border-4 border-red-500 transition-colors duration-300";

                    await window.falarBiometria(msg);

                    window.isStatusCooldown = true;
                    iniciarContadorOval(() => {
                        window.resetarInterfacePosBatida();
                        if (btn) {
                            btn.innerHTML = 'Capturar Facial';
                            btn.disabled = false;
                        }
                    });
                } else {
                    // Falha de negócio (ponto já registrado, GPS, método, etc.)
                    const nomeCurto = window.formatarNomeCurto(window.currentFuncionarioNome);
                    const msgNegocio = nomeCurto ? `${nomeCurto}, ${data.message}` : data.message;
                    const isErroNegocio = data.message && (data.message.includes('já foram registrados') || data.message.includes('ponto já registrado'));
                    const isIntervaloMinimo = data.message && data.message.includes('antes de 15 minutos');
                    // Mantém a face bloqueada apenas quando o servidor reconheceu e rejeitou por intervalo mínimo
                    window.lastCaptureWasError = !isIntervaloMinimo;

                    if (statusEl) {
                        statusEl.textContent = msgNegocio;
                        statusEl.className = isErroNegocio ?
                            "bg-amber-500/80 backdrop-blur-md text-white text-[10px] font-bold px-6 py-2.5 rounded-full uppercase tracking-widest border border-white/20 shadow-lg transition-all" :
                            "bg-red-600/80 backdrop-blur-md text-white text-[10px] font-bold px-6 py-2.5 rounded-full uppercase tracking-widest border border-white/20 shadow-lg transition-all";
                    }
                    if (oval) oval.className = "w-[85%] h-[80%] border-[3px] border-dashed border-amber-400 rounded-[50%] transition-all duration-300";
                    if (container) container.className = "relative w-full max-w-[280px] aspect-[4/5] bg-slate-50 rounded-[32px] overflow-hidden shadow-lg border-4 border-amber-400 transition-colors duration-300";
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = 'Aguarde...';
                    }
                    await window.falarBiometria(msgNegocio);
                    window.isStatusCooldown = true;
                    iniciarContadorOval(() => {
                        window.resetarInterfacePosBatida();
                        if (btn) {
                            btn.innerHTML = 'Capturar Facial';
                            btn.disabled = false;
                        }
                    });
                }
            }
        } catch (err) {
            console.error("Erro no processamento facial:", err);
            isProcessingFacial = false;
            if (statusEl) statusEl.textContent = "Erro IA/Conexão";
            if (btn) {
                btn.innerHTML = "Capturar Facial";
                btn.disabled = false;
            }
            video.classList.remove('hidden');
            canvas.classList.add('hidden');
            if (detectionLoopActive) startFaceDetectionLoop();
        }
    };

    /**
     * Exibe o modal informativo do Termo de Consentimento de Imagem
     */
    window.exibirTermoUsoImagem = async function(funcionario, base64Image, descriptor, loc) {
        let voiceActive = false;

        const swalModal = Swal.fire({
            title: '<div class="text-left"><p class="text-[10px] font-black text-brand-500 uppercase tracking-widest m-0">Termo de Consentimento</p> <p class="text-lg font-black text-slate-800 m-0">Uso de Imagem e Biometria</p></div>',
            html: `
                <div class="text-left text-[11px] leading-relaxed max-h-[50vh] overflow-y-auto px-1 custom-scrollbar space-y-4 py-2">
                    <p class="font-medium text-slate-600">Eu, <b>${funcionario.nome}</b> (Matrícula: ${funcionario.matricula}), declaro estar ciente e concordo com o tratamento dos meus dados biométricos faciais para fins exclusivos de controle de ponto.</p>
                    
                    <div class="space-y-2">
                        <p class="font-black text-[9px] text-slate-400 uppercase tracking-widest">1. FINALIDADE</p>
                        <p class="text-slate-500">As imagens faciais e templates matemáticos serão utilizados apenas para identificação segura na jornada de trabalho e prevenção de fraudes.</p>
                    </div>

                    <div class="space-y-2">
                        <p class="font-black text-[9px] text-slate-400 uppercase tracking-widest">2. PRIVACIDADE E LGPD</p>
                        <p class="text-slate-500">O tratamento de dados segue estritamente a Lei nº 13.709/2018 (LGPD), garantindo a segurança e o sigilo das informações coletadas.</p>
                    </div>

                    <div id="voice-indicator" class="hidden bg-blue-50 p-3 rounded-xl border border-blue-100 border-dashed text-blue-800 font-bold mt-4 flex items-center gap-3 animate-pulse">
                        <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                        Ouvindo sua resposta... Diga "Sim" ou "Não"
                    </div>

                    <div id="consent-footer" class="bg-amber-50 p-3 rounded-xl border border-amber-100 border-dashed text-amber-800 italic mt-4">
                        Ao clicar em <b>"Aceito e Registrar"</b>, você confirma sua ciência e autoriza este registro específico de ponto por biometria facial.
                    </div>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Aceito e Registrar',
            cancelButtonText: 'Não Aceito',
            confirmButtonColor: '#0ea5e9',
            cancelButtonColor: '#94a3b8',
            allowOutsideClick: false,
            reverseButtons: true,
            customClass: {
                container: 'z-[10000]'
            },
            didOpen: async () => {
                voiceActive = true;
                const nomeCurto = window.formatarNomeCurto(funcionario.nome);
                const textoLeitura = `Termo de Consentimento. Eu, ${nomeCurto}, concordo com o tratamento dos meus dados biométricos para controle de ponto, conforme a Lei Geral de Proteção de Dados. Você consente? Diga sim ou não.`;

                await window.falarTexto(textoLeitura);

                if (voiceActive && Swal.isVisible()) {
                    const indicator = document.getElementById('voice-indicator');
                    if (indicator) indicator.classList.remove('hidden');

                    const resposta = await window.ouvirComandoConsentimento();
                    if (voiceActive && Swal.isVisible()) {
                        if (resposta === 'sim') {
                            Swal.clickConfirm();
                        } else if (resposta === 'nao') {
                            Swal.clickCancel();
                        }
                    }
                }
            },
            willClose: () => {
                voiceActive = false;
                if (window.speechSynthesis.speaking) window.speechSynthesis.cancel();
            }
        });

        const {
            isConfirmed: accepted
        } = await swalModal;

        if (accepted) {
            // Salvar o aceite do termo no banco de dados para não repetir no futuro
            try {
                await fetch('../api/ponto.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'aceitar_termo',
                        matricula: funcionario.matricula
                    })
                });
            } catch (e) {
                console.error("Erro ao salvar aceite do termo:", e);
            }

            executarRegistroFinalFacial(funcionario.matricula, base64Image, descriptor, loc);
        } else {
            Swal.fire({
                title: window.getMsgComNome('Registro Cancelado'),
                text: 'O ponto não foi registrado. Por favor, procure o CRH para regularizar seu aceite.',
                icon: 'warning',
                confirmButtonColor: '#f59e0b'
            });
            isProcessingFacial = false;
            window.fecharModalBiometria(); // Fecha tudo após recusa
        }
    }



    /**
     * Centraliza o processamento visual e auditivo de um registro de ponto bem-sucedido
     */
    function processarSucessoRegistro(data) {
        window.isStatusCooldown = true;
        detectionLoopActive = true;

        const statusEl = document.querySelector('#faceStatus span');
        const video = document.getElementById('videoFeed');
        const canvas = document.getElementById('videoCanvas');
        const nomeCurto = window.formatarNomeCurto(data.funcionario);
        const msgBase = `seu ponto foi batido com sucesso em ${data.data_ponto} às ${data.hora}.`;
        const msgFinal = window.getMsgComNome(msgBase);

        // Mantemos a câmera rodando para detectar a próxima pessoa
        if (video) video.classList.remove('hidden');
        if (canvas) canvas.classList.add('hidden');

        if (statusEl) {
            statusEl.innerHTML = `${data.funcionario}<br>PONTO BATIDO!<br><span class="text-[8px] opacity-80">${data.data_ponto} às ${data.hora}</span>`;
            statusEl.className = "glass-status-success text-white text-[10px] font-bold px-6 py-2 rounded-full uppercase tracking-widest transition-all leading-tight text-center shadow-[0_0_25px_rgba(16,185,129,0.4)]";
            window.falarBiometria(msgFinal, true);
        }

        // Feedback de sucesso curto e reinício automático
        let segundos = 4;
        window.cooldownTimer = setInterval(() => {
            segundos--;
            if (statusEl) {
                statusEl.textContent = `Próximo Registro em ${segundos}s...`;
                statusEl.className = "bg-slate-700/80 backdrop-blur-md text-white text-[10px] font-bold px-6 py-2.5 rounded-full uppercase tracking-widest border border-white/20 shadow-lg transition-all";
            }
            if (segundos <= 0) {
                clearInterval(window.cooldownTimer);
                window.resetarInterfacePosBatida();
            }
        }, 1000);
    }

    window.resetarInterfacePosBatida = function() {
        if (window.cooldownTimer) clearInterval(window.cooldownTimer);
        window.isStatusCooldown = false;
        isProcessingFacial = false; // Forçar liberação imediata

        // Se foi erro, limpamos o descritor para permitir nova tentativa imediata da mesma pessoa
        if (window.lastCaptureWasError) {
            window.lastSuccessDescriptor = null;
        }
        detectionLoopActive = true;
        window.currentFuncionarioNome = '';
        window._stabilityCount = 0;
        const matInput = document.getElementById('matricula');
        const bioMatInput = document.getElementById('bio_matricula');
        if (matInput) matInput.value = '';
        if (bioMatInput) bioMatInput.value = '';
        const video = document.getElementById('videoFeed');
        const canvas = document.getElementById('videoCanvas');
        if (video) video.classList.remove('hidden');
        if (canvas) canvas.classList.add('hidden');
        const statusEl = document.querySelector('#faceStatus span');
        if (statusEl) {
            statusEl.textContent = "Buscando Face...";
            statusEl.className = "bg-black/60 backdrop-blur-md text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest border border-white/10";
        }

        // Garantir que o loop de detecção esteja ativo
        if (!detectionLoopActive) {
            detectionLoopActive = true;
            startFaceDetectionLoop();
        }
    };

    /**
     * Executa a batida final após o aceite do termo
     */
    async function executarRegistroFinalFacial(matricula, base64Image, descriptor, loc) {
        // Se for modo 1:N, garante que o nome está limpo antes de receber a resposta do servidor
        if (!matricula) window.currentFuncionarioNome = '';

        const statusEl = document.querySelector('#faceStatus span');
        if (statusEl) {
            statusEl.textContent = window.getMsgComNome("Registrando Ponto...");
            statusEl.className = "bg-brand-500 text-white text-[10px] font-black px-6 py-2.5 rounded-full uppercase tracking-widest border border-white/20 shadow-xl transition-all animate-pulse";
            window.falarBiometria(window.getMsgComNome("Registrando ponto, aguarde"), true);
        }

        try {
            const res = await fetch('../api/ponto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'ponto_facial',
                    matricula: matricula,
                    image_data: base64Image,
                    facial_descriptor: descriptor,
                    lat: loc?.lat,
                    lng: loc?.lng
                })
            });
            const data = await res.json();
            if (data.funcionario) window.currentFuncionarioNome = data.funcionario;

            if (data.success) {
                processarSucessoRegistro(data);
            } else {
                const statusEl = document.querySelector('#faceStatus span');
                const msgErr = data.message || "Erro no registro";

                // Garantir captura do nome para personalização
                if (data.funcionario || data.nome) {
                    window.currentFuncionarioNome = data.funcionario || data.nome;
                }
                const nomeCurto = window.formatarNomeCurto(window.currentFuncionarioNome);
                const personalizedMsg = `${nomeCurto}, ${msgErr}`;

                if (statusEl) {
                    statusEl.textContent = personalizedMsg;
                    statusEl.className = "bg-red-600 text-white text-[10px] font-black px-6 py-2.5 rounded-full uppercase tracking-widest border border-white/20 shadow-xl transition-all animate-pulse";
                }
                await window.falarTexto(personalizedMsg);

                if (data.ficha_incompleta) {
                    setTimeout(() => {
                        window.fecharModalBiometria();
                        window.abrirModalFichaIncompleta(data);
                    }, 5000);
                } else {
                    // Aguarda 2 segundos (ajustado a pedido do usuário)
                    let segErro = 2;
                    const intervalErro = setInterval(() => {
                        segErro--;
                        if (statusEl) {
                            statusEl.textContent = `${msgErr} (${segErro}s)`;
                        }
                        if (segErro <= 0) {
                            clearInterval(intervalErro);

                            isProcessingFacial = false;
                            detectionLoopActive = true;
                            window._stabilityCount = 0;
                            window.currentFuncionarioNome = ''; // Limpar nome após exibir erro para não vazar para o próximo

                            if (video) video.classList.remove('hidden');
                            if (canvas) canvas.classList.add('hidden');

                            window.iniciarStreamVideo();
                            setTimeout(() => {
                                startFaceDetectionLoop();
                            }, 500);
                        }
                    }, 1000);
                }
            }
        } catch (e) {
            const statusEl = document.querySelector('#faceStatus span');
            if (statusEl) {
                statusEl.textContent = "Falha no Registro";
                await window.falarBiometria("Falha ao registrar ponto. Tente novamente.");
            }
        } finally {
            isProcessingFacial = false;
        }
    }

    window.togglePassword = function(inputId, btn) {
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
    window.iniciarCapturaDigitalClock = async function() {
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
                    const t = setTimeout(() => {
                        s.close();
                        reject();
                    }, 1500);
                    s.onopen = () => {
                        clearTimeout(t);
                        window.dpSocketClock = s;
                        s.send(JSON.stringify({
                            Type: "Capture",
                            Method: "Start"
                        }));
                        resolve(true);
                    };
                    s.onmessage = async (e) => {
                        const d = JSON.parse(e.data);
                        if (d && d.Event === "SamplesReady" && d.Samples.length > 0) {
                            s.send(JSON.stringify({
                                Type: "Capture",
                                Method: "Stop"
                            }));
                            const hash = d.Samples[d.Samples.length - 1].Data;
                            processarPontoDigital(hash);
                        }
                    };
                    s.onerror = () => reject();
                });
                if (connected) {
                    ok = true;
                    break;
                }
            } catch (e) {}
        }

        if (!ok) {
            btn.disabled = false;
            btn.innerHTML = "Sensor não encontrado";
            if (ring) ring.classList.add('opacity-0');
            Swal.fire('Aviso Sensor', 'Não foi possível conectar ao leitor USB.', 'warning');
        }
    };

    async function processarPontoDigital(hash) {
        const btn = document.getElementById('btnAtivarSensor');
        btn.innerHTML = "Validando Digital...";

        try {
            const loc = await _geoCache ? Promise.resolve(_geoCache) : obterLocalizacao();
            const res = await fetch('../api/ponto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    biometria: hash,
                    lat: loc?.lat,
                    lng: loc?.lng
                })
            });
            const data = await res.json();
            if (data.success) {
                const msgSucesso = `Ponto registrado com sucesso via digital.`;
                await window.falarTexto(msgSucesso);
                Swal.fire({
                    title: 'Marcado!',
                    html: `<b>${data.funcionario}</b><br>${msgSucesso}<br>Registrado em <b>${data.data_ponto}</b> às <b>${data.hora}</b>`,
                    icon: 'success',
                    showConfirmButton: true,
                    confirmButtonText: 'Fechar',
                    confirmButtonColor: '#0ea5e9'
                });
                window.fecharModalBiometria();
            } else if (data.ficha_incompleta) {
                const msgIncompleta = 'Sua ficha está incompleta. Por favor, atualize seus dados.';
                await window.falarTexto(msgIncompleta);
                await Swal.fire({
                    title: 'Atualização Cadastral',
                    text: msgIncompleta,
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
                const msgErr = data.message || "Digital não reconhecida";
                await window.falarTexto(msgErr);
                Swal.fire({
                    title: window.getMsgComNome('Digital Desconhecida'),
                    text: msgErr,
                    icon: 'error'
                }).then(() => window.fecharModalBiometria());
                resetarBotaoDigital();
            }
        } catch (e) {
            Swal.fire('Aviso', 'Falha na comunicação com o servidor.', 'error');
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
    window.baterPontoWebAuthn = async function(silent = false) {
        if (!window.PublicKeyCredential) return;

        try {
            // 1. Obter desafio do servidor
            const resOpt = await fetch('../api/webauthn.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'get_assertion_options'
                })
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
                headers: {
                    'Content-Type': 'application/json'
                },
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
                Swal.fire({
                    title: 'Marcado!',
                    html: `<b>${data.funcionario}</b><br>Ponto via Mobile em <b>${data.data_ponto}</b> às <b>${data.hora}</b>`,
                    icon: 'success'
                });
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
                        title: window.getMsgComNome('Não Permitido'),
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'Fechar',
                        confirmButtonColor: '#0ea5e9'
                    }).then(() => window.fecharModalBiometria());
                }
                return false;
            }

        } catch (err) {
            console.error(err);
            if (!silent && err.name !== 'NotAllowedError') { // Ignora se o usuário cancelou ou se for modo silencioso
                Swal.fire('Aviso', 'Falha na biometria do celular: ' + err.message, 'error');
            }
            return false;
        }
    };

    // Trava de Acesso Administrativo para evitar saída da tela
    // Redireciona diretamente para o painel administrativo
    function pedirAcessoAdmin(e) {
        if (e) e.preventDefault();
        window.location.href = 'admin/index.php';
    }

    // --- MEU ACESSO (Individual) ---
    async function abrirMeuAcesso(e) {
        if (e) e.preventDefault();
        const matriculaPrevia = ""; // Sempre inicia vazio para segurança e clareza

        const today = new Date();
        const y = today.getFullYear();
        const m = String(today.getMonth() + 1).padStart(2, '0');
        const lastDay = new Date(y, today.getMonth() + 1, 0).getDate();
        const defaultStart = `${y}-${m}-01`;
        const defaultEnd = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;

        const {
            value: loginData
        } = await Swal.fire({
            title: 'Meu Cartão de Ponto',
            text: 'Identifique-se e escolha o período do extrato.',
            html: `
                <div class="space-y-4 py-2">
                    <div class="text-left">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Matrícula</label>
                        <input id="sw-m" type="text" autocomplete="off" name="m_${Date.now()}" class="swal2-input !w-full !m-0 mt-1 !text-center !rounded-xl !border-slate-200 !text-sm font-bold tracking-widest" placeholder="99.999-9" value="">
                    </div>
                    <div class="text-left">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sua Senha</label>
                        <input id="sw-s" type="password" autocomplete="new-password" name="s_${Date.now()}" class="swal2-input !w-full !m-0 mt-1 !text-center !rounded-xl !border-slate-200 !text-sm font-bold tracking-widest" placeholder="******" value="">
                    </div>

                    <div class="relative flex py-3 items-center">
                        <div class="flex-grow border-t border-slate-100"></div>
                        <span class="flex-shrink-0 mx-4 text-slate-300 text-[10px] font-black uppercase tracking-widest">ou</span>
                        <div class="flex-grow border-t border-slate-100"></div>
                    </div>

                    <div class="flex flex-col gap-2" ></div>
                    <div class="flex gap-3">
                        <div class="flex-1 text-left">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Data Inicial</label>
                            <input id="sw-dt-ini" type="date" value="${defaultStart}" class="swal2-input !w-full !m-0 mt-1 !text-center !rounded-xl !border-slate-200 !text-sm font-bold font-mono">
                        </div>
                        <div class="flex-1 text-left">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Data Final</label>
                            <input id="sw-dt-fim" type="date" value="${defaultEnd}" class="swal2-input !w-full !m-0 mt-1 !text-center !rounded-xl !border-slate-200 !text-sm font-bold font-mono">
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Ver Cartão',
            cancelButtonText: 'Voltar',
            confirmButtonColor: '#0ea5e9',
            preConfirm: () => {
                const m = document.getElementById('sw-m').value.trim();
                const s = document.getElementById('sw-s').value;
                const dtIni = document.getElementById('sw-dt-ini').value;
                const dtFim = document.getElementById('sw-dt-fim').value;
                if (!m || !s) {
                    Swal.showValidationMessage('Preencha matrícula e senha');
                    return false;
                }
                if (!dtIni || !dtFim) {
                    Swal.showValidationMessage('Selecione o período completo');
                    return false;
                }
                return {
                    m,
                    s,
                    dtIni,
                    dtFim
                };
            }
        });

        if (!loginData) return;

        Swal.fire({
            title: 'Carregando...',
            didOpen: () => {
                Swal.showLoading();
            },
            allowOutsideClick: false
        });

        try {
            const res = await fetch('../api/ponto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'get_meu_ponto',
                    matricula: loginData.m,
                    senha: loginData.s,
                    start_date: loginData.dtIni,
                    end_date: loginData.dtFim
                })
            });

            const json = await res.json();
            if (!json.success) {
                Swal.fire('Aviso', json.message, 'error');
                return;
            }

            window._ultimaMatricula = loginData.m;
            window._ultimaSenha = loginData.s;

            // Salva no session storage para persistência em caso de F5
            sessionStorage.setItem('extrato_pendente_dados', JSON.stringify(json.data));
            sessionStorage.setItem('extrato_pendente_nome', json.funcionario);
            sessionStorage.setItem('extrato_pendente_mat', loginData.m);
            sessionStorage.setItem('extrato_pendente_senha', loginData.s);
            sessionStorage.setItem('extrato_pendente_ini', loginData.dtIni);
            sessionStorage.setItem('extrato_pendente_fim', loginData.dtFim);

            renderizarCartaoIndividual(json.data, json.funcionario, loginData.dtIni, loginData.dtFim);
        } catch (e) {
            Swal.fire('Aviso', 'Falha na comunicação com o servidor.', 'error');
        }
    }

    // Função global para busca inline no modal
    window.recarregarExtrato = async function() {
        const dtIni = document.getElementById('inline-dt-ini')?.value || sessionStorage.getItem('extrato_pendente_ini') || new Date().toISOString().split('T')[0].substring(0, 8) + '01';
        const dtFim = document.getElementById('inline-dt-fim')?.value || sessionStorage.getItem('extrato_pendente_fim') || new Date().toISOString().split('T')[0];

        const mat = window._ultimaMatricula || sessionStorage.getItem('extrato_pendente_mat');
        const sen = window._ultimaSenha || sessionStorage.getItem('extrato_pendente_senha');

        if (!mat || !sen) {
            console.warn("Sessão individual não encontrada para recarga.");
            return;
        }

        Swal.fire({
            title: 'Buscando período...',
            didOpen: () => {
                Swal.showLoading();
            },
            allowOutsideClick: false
        });

        try {
            const res = await fetch('../api/ponto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'get_meu_ponto',
                    matricula: mat,
                    senha: sen,
                    start_date: dtIni,
                    end_date: dtFim
                })
            });

            const json = await res.json();
            if (!json.success) {
                Swal.fire('Aviso', json.message, 'error');
                return;
            }

            sessionStorage.setItem('extrato_pendente_dados', JSON.stringify(json.data));
            sessionStorage.setItem('extrato_pendente_ini', dtIni);
            sessionStorage.setItem('extrato_pendente_fim', dtFim);

            renderizarCartaoIndividual(json.data, json.funcionario, dtIni, dtFim);
        } catch (e) {
            Swal.fire('Aviso', 'Falha ao buscar intervalo de datas.', 'error');
        }
    };

    function renderizarCartaoIndividual(dados, nome, dtIni, dtFim) {
        const esc = (s) => (s || '').toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');

        const pontoHtml = (hora, atrasou, faltaAuto, justIndividual, statusCrh, tipoJustificativa, justificativaGlobal, horarioProgramado, emFerias, motivo, r, pIndex) => {
            if (emFerias) {
                const isPendente = String(r.status_afastamento).toLowerCase() === 'pendente';
                const badgeClass = isPendente ? 'bg-amber-400 text-white border-amber-500' : 'bg-amber-500 text-white border-amber-600';
                const label = isPendente ? `${esc(motivo)} (Pendente)` : esc(motivo);
                return `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black ${badgeClass} border uppercase tracking-tighter" title="${esc(motivo)}">📅 ${label}</span>`;
            }

            const isFaltaAuto = (hora === 'FALTA' || hora === 'falta' || !hora);

            // --- Lógica de Ponto Liberado (Feriado, Facultativo, Liberação Antecipada) ---
            if (r.liberacao && isFaltaAuto) {
                const desc = String(r.liberacao.descricao || '').toUpperCase();
                const isFeriado = desc.includes('FERIADO');
                const isFacultativo = desc.includes('FACULTATIVO');

                if (isFeriado || isFacultativo) {
                    if (horarioProgramado) {
                        const badgeClass = isFeriado ? 'bg-rose-500' : 'bg-violet-500';
                        return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black ${badgeClass} text-white uppercase tracking-tighter shadow-sm" title="${esc(r.liberacao.descricao)}">${esc(r.liberacao.descricao)}</span>`;
                    }
                } else if (r.liberacao.data_hora && horarioProgramado) {
                    const libTime = r.liberacao.data_hora.split(' ')[1].substring(0, 5);
                    const progTime = horarioProgramado.substring(0, 5);
                    if (libTime <= progTime && (pIndex === 2 || pIndex === 4)) {
                        return `<div class="flex flex-col items-center leading-none">
                                    <span class="text-[8px] font-black text-brand-600 uppercase tracking-tighter mb-0.5">Liberado</span>
                                    <span class="text-[9px] font-bold bg-brand-50 text-brand-700 px-1.5 py-0.5 rounded border border-brand-200" title="${esc(r.liberacao.descricao)}">${esc(r.liberacao.descricao)}</span>
                                </div>`;
                    }
                }
            }

            const isIndeferido = statusCrh === 'indeferido';
            const isDeferido = statusCrh === 'deferido';
            const hasJust = justIndividual && justIndividual !== 'null' && String(justIndividual).trim() !== '';
            const hasGlobalJust = tipoJustificativa && tipoJustificativa !== 'null' && String(tipoJustificativa).trim() !== '';

            if (!horarioProgramado && !hora) {
                return '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black text-slate-300">---</span>';
            }

            const hasAnyIndiv = (r.just_ent1 && String(r.just_ent1).trim() !== '' && r.just_ent1 !== 'null') ||
                (r.just_sai1 && String(r.just_sai1).trim() !== '' && r.just_sai1 !== 'null') ||
                (r.just_ent2 && String(r.just_ent2).trim() !== '' && r.just_ent2 !== 'null') ||
                (r.just_sai2 && String(r.just_sai2).trim() !== '' && r.just_sai2 !== 'null');

            const hasAnyJust = hasJust || (hasGlobalJust && !hasAnyIndiv && (atrasou || isFaltaAuto || faltaAuto));

            const time = isFaltaAuto ? 'Falta' : String(hora).substring(0, 5);
            const valJust = justificativaGlobal ? justificativaGlobal.replace(/'/g, "\\'").replace(/"/g, "&quot;").replace(/\n/g, " ") : '';

            // 1. DEFERIDO (Estilo Premium)
            if (isDeferido && hasAnyJust) {
                return `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200"><svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg> Justificado</span>`;
            }

            // 2. INDEFERIDO
            if (isIndeferido && hasAnyJust) {
                const label = isFaltaAuto ? `Falta` : `Falta (${esc(time)})`;
                return `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-black bg-red-50 text-red-700 border border-red-300 cursor-pointer hover:bg-red-100 transition-colors" 
                              onclick="Swal.fire({title:'Justificativa Recusada', text:'${valJust}', icon:'info'})">
                              <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> ${label}</span>`;
            }

            // 3. PENDENTE (Estilo Premium Blue)
            if (hasAnyJust) {
                return `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-black bg-blue-50 text-blue-700 border border-blue-200">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg> Justificado
                        </span>`;
            }

            // 4. ATRASO (Estilo Imagem: Tempo Laranja e Rótulo abaixo)
            if (atrasou && atrasou !== 'false' && atrasou !== false && !isFaltaAuto) {
                return `<div class="flex flex-col items-center leading-tight">
                            <span class="text-[10px] font-black text-amber-600">${esc(time)}</span>
                            <span class="text-[7px] font-black text-amber-500 uppercase tracking-tighter">Atraso</span>
                        </div>`;
            }

            // 5. FALTA (Estilo Imagem: Círculo com Traço)
            if (isFaltaAuto) {
                return `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-black bg-red-50 text-red-700 border border-red-200">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Falta
                        </span>`;
            }

            // 6. NORMAL
            return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-slate-100 text-slate-700">${esc(time)}</span>`;
        };

        const statusHtml = (rec) => {
            if (rec.em_ferias) {
                const isPendente = String(rec.status_afastamento).toLowerCase() === 'pendente';
                const label = isPendente ? `${esc(rec.motivo_afastamento)} (Pendente)` : esc(rec.motivo_afastamento);
                return `<span class="text-[9px] font-black ${isPendente ? 'text-amber-400' : 'text-amber-600'} uppercase">${label}</span>`;
            }

            const toMin = v => {
                if (!v || v === 'FALTA' || v === 'falta') return null;
                const p = String(v).substring(0, 5).split(':');
                if (p.length < 2) return null;
                return parseInt(p[0]) * 60 + parseInt(p[1]);
            };

            const h1 = !!rec.primeiro_horario,
                h2 = !!rec.segundo_horario;
            const h3 = !!rec.terceiro_horario,
                h4 = !!rec.quarto_horario;
            const p1 = !!rec.primeiro_ponto && rec.primeiro_ponto !== 'FALTA' && rec.primeiro_ponto !== 'falta';
            const p2 = !!rec.segundo_ponto && rec.segundo_ponto !== 'FALTA' && rec.segundo_ponto !== 'falta';
            const p3 = !!rec.terceiro_ponto && rec.terceiro_ponto !== 'FALTA' && rec.terceiro_ponto !== 'falta';
            const p4 = !!rec.quarto_ponto && rec.quarto_ponto !== 'FALTA' && rec.quarto_ponto !== 'falta';

            const isFeriadoFacultativo = rec.liberacao && (String(rec.liberacao.descricao).toUpperCase().includes('FERIADO') || String(rec.liberacao.descricao).toUpperCase().includes('FACULTATIVO'));
            const incompleto = !isFeriadoFacultativo && ((h1 && !p1) || (h2 && !p2) || (h3 && !p3) || (h4 && !p4));

            let esp = 0,
                trab = 0;
            const eh1 = toMin(rec.primeiro_horario),
                eh2 = toMin(rec.segundo_horario);
            if (eh1 !== null && eh2 !== null) esp += eh2 - eh1;
            const eh3 = toMin(rec.terceiro_horario),
                eh4 = toMin(rec.quarto_horario);
            if (eh3 !== null && eh4 !== null) esp += eh4 - eh3;

            const r1 = toMin(rec.primeiro_ponto),
                r2 = toMin(rec.segundo_ponto);
            if (r1 !== null && r2 !== null) trab += r2 - r1;
            const r3 = toMin(rec.terceiro_ponto),
                r4 = toMin(rec.quarto_ponto);
            if (r3 !== null && r4 !== null) trab += r4 - r3;

            if (incompleto) return '<span class="text-[9px] font-black text-orange-600 uppercase">Incompleto</span>';

            if (isFeriadoFacultativo) return `<span class="text-[9px] font-black text-indigo-600 uppercase">✓ ${rec.liberacao.descricao}</span>`;

            if (esp > 0 && Math.abs(esp - trab) < 15) {
                return '<span class="text-[9px] font-black text-emerald-600 uppercase flex items-center justify-center gap-1"><svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg> OK</span>';
            }
            if (!p1 && !p2 && !p3 && !p4) return '';
            return '<span class="text-[9px] font-black text-slate-500 uppercase">Pendente</span>';
        };

        const resumo = {
            total: dados.length,
            ok: 0,
            atrasos: 0,
            faltas: 0,
            incompletos: 0
        };

        const rows = renderizarLinhasExtrato(dados, resumo, pontoHtml, statusHtml, esc);

        Swal.fire({
            title: `
                <div class="flex items-center justify-between w-full pr-8">
                    <div class="flex items-center gap-4 text-left">
                        <div class="w-12 h-12 rounded-2xl bg-brand-600 flex items-center justify-center text-white shadow-lg shadow-brand-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-brand-600 uppercase tracking-[0.2em] m-0 leading-none mb-1">Folha de Ponto Digital</p>
                            <h2 class="text-xl font-black text-slate-800 m-0 leading-tight">${esc(nome)}</h2>
                        </div>
                    </div>
                </div>
            `,
            width: '900px',
            padding: '2rem',
            showCloseButton: true,
            html: `
                <div id="extrato-pdf-content" class="text-slate-600">
                    <!-- Resumo Dashboard -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6" data-html2canvas-ignore="true">
                        <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex flex-col">
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Dias</span>
                            <span class="text-xl font-black text-slate-800">${resumo.total}</span>
                        </div>
                        <div class="bg-emerald-50/50 p-3 rounded-2xl border border-emerald-100 shadow-sm flex flex-col">
                            <span class="text-[8px] font-black text-emerald-600 uppercase tracking-widest mb-1">Dias OK</span>
                            <span class="text-xl font-black text-emerald-700">${resumo.ok}</span>
                        </div>
                        <div class="bg-amber-50/50 p-3 rounded-2xl border border-amber-100 shadow-sm flex flex-col">
                            <span class="text-[8px] font-black text-amber-600 uppercase tracking-widest mb-1">Atrasos</span>
                            <span class="text-xl font-black text-amber-700">${resumo.atrasos}</span>
                        </div>
                        <div class="bg-rose-50/50 p-3 rounded-2xl border border-rose-100 shadow-sm flex flex-col">
                            <span class="text-[8px] font-black text-rose-600 uppercase tracking-widest mb-1">Faltas</span>
                            <span class="text-xl font-black text-rose-700">${resumo.faltas}</span>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="flex flex-col md:flex-row items-center justify-between mb-4 p-4 bg-slate-50/80 backdrop-blur-sm border border-slate-100 rounded-2xl gap-4" data-html2canvas-ignore="true">
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <div class="flex items-center bg-white p-1 rounded-xl border border-slate-200 shadow-sm flex-1">
                                <input type="date" id="inline-dt-ini" value="${dtIni}" class="px-2 py-1 bg-transparent text-xs font-bold font-mono text-slate-700 outline-none border-none w-full">
                                <span class="px-2 text-[10px] font-black text-slate-300">➜</span>
                                <input type="date" id="inline-dt-fim" value="${dtFim}" class="px-2 py-1 bg-transparent text-xs font-bold font-mono text-slate-700 outline-none border-none w-full">
                            </div>
                            <button type="button" onclick="window.recarregarExtrato()" class="h-9 px-5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-md shadow-brand-100 flex items-center gap-2 active:scale-95">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                Atualizar
                            </button>
                            
                            <div class="flex items-center bg-white p-1 rounded-xl border border-slate-200 shadow-sm">
                                <select id="sw-sort-order" onchange="window.mudarOrdenacaoExtrato(this.value)" class="bg-transparent text-[10px] font-black uppercase tracking-widest text-slate-600 outline-none border-none px-2 py-1 cursor-pointer">
                                    <option value="desc" ${window._extratoSortOrder === 'desc' ? 'selected' : ''}>Mais Recentes</option>
                                    <option value="asc" ${window._extratoSortOrder === 'asc' ? 'selected' : ''}>Mais Antigos</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 w-full md:w-auto">
                             <button onclick="exportarExtratoPDF('${esc(nome)}', '${dtIni}', '${dtFim}')" class="h-9 px-5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-md flex items-center gap-2 active:scale-95 flex-1 md:flex-none justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                PDF
                            </button>
                        </div>
                    </div>

                    <!-- Tabela -->
                    <div class="md:border border-slate-100 md:rounded-3xl overflow-hidden bg-transparent md:bg-white md:shadow-xl md:shadow-slate-200/50">
                        <table class="w-full text-left border-collapse block md:table">
                            <thead class="hidden md:table-header-group">
                                <tr class="bg-slate-800 text-white">
                                    <th class="py-4 px-4 text-[9px] font-black uppercase tracking-widest w-28 border-r border-slate-700/50">Data / Dia</th>
                                    <th class="py-4 px-1 text-[9px] font-black uppercase tracking-widest text-center border-r border-slate-700/50">Entrada 1</th>
                                    <th class="py-4 px-1 text-[9px] font-black uppercase tracking-widest text-center border-r border-slate-700/50">Saída 1</th>
                                    <th class="py-4 px-1 text-[9px] font-black uppercase tracking-widest text-center border-r border-slate-700/50">Entrada 2</th>
                                    <th class="py-4 px-1 text-[9px] font-black uppercase tracking-widest text-center border-r border-slate-700/50">Saída 2</th>
                                    <th class="py-4 px-1 text-[9px] font-black uppercase tracking-widest text-center border-r border-slate-700/50">Status</th>
                                    <th class="py-4 px-4 text-[9px] font-black uppercase tracking-widest text-left">Observações</th>
                                </tr>
                            </thead>
                            <tbody class="block md:table-row-group">
                                ${rows || '<tr><td colspan="7" class="py-20 text-center"><div class="flex flex-col items-center gap-2"><svg class="w-12 h-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="text-slate-400 font-bold italic">Nenhum registro encontrado.</span></div></td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Ações de Rodapé -->
                <div class="mt-8 grid grid-cols-2 gap-4">
                    <button onclick="comunicarAtrasoFalta('${esc(nome)}')" class="flex items-center justify-center gap-3 px-6 py-4 bg-amber-500 hover:bg-amber-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all shadow-lg shadow-amber-200 active:scale-95 group">
                        <svg class="w-5 h-5 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Justificar Falta/Atraso
                    </button>
                    <button onclick="trocarSenhaFuncionario()" class="flex items-center justify-center gap-3 px-6 py-4 bg-slate-800 hover:bg-slate-900 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all shadow-lg shadow-slate-200 active:scale-95 group">
                        <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                        Alterar Senha
                    </button>
                </div>

                <div class="mt-6 p-5 bg-gradient-to-br from-slate-50 to-white rounded-2xl border border-slate-100 flex gap-4 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-brand-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="text-left">
                        <p class="text-[12px] font-black text-slate-800 leading-none mb-1">Dúvidas ou Divergências?</p>
                        <p class="text-[10px] text-slate-500 leading-relaxed font-medium">Caso identifique erros em suas batidas, entre em contato com o gestor do seu setor ou com o RH para regularização imediata.</p>
                    </div>
                </div>
            `,
            showConfirmButton: true,
            confirmButtonText: 'Sair e Logout',
            confirmButtonColor: '#1e293b',
            allowOutsideClick: false,
            customClass: {
                popup: 'rounded-[2rem]',
                confirmButton: 'rounded-xl px-8 py-3 text-xs font-black uppercase tracking-widest',
                closeButton: 'top-4 right-4 text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 border-none rounded-lg'
            },
            didClose: () => {
                // Logout Completo ao fechar o modal (Limpa sessão no backend e frontend)
                fetch('../api/ponto.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'logout_meu_ponto'
                    })
                });

                sessionStorage.removeItem('extrato_pendente_dados');
                sessionStorage.removeItem('extrato_pendente_nome');
                sessionStorage.removeItem('extrato_pendente_mat');
                sessionStorage.removeItem('extrato_pendente_senha');
                location.reload();
            }
        });
    }

    // Gerenciamento de Ordenação no Extrato Individual
    window._extratoSortOrder = 'asc';
    window.mudarOrdenacaoExtrato = function(order) {
        window._extratoSortOrder = order;
        const rawData = sessionStorage.getItem('extrato_pendente_dados');
        const nome = sessionStorage.getItem('extrato_pendente_nome');
        const dtIni = sessionStorage.getItem('extrato_pendente_ini');
        const dtFim = sessionStorage.getItem('extrato_pendente_fim');
        if (rawData && nome) {
            renderizarCartaoIndividual(JSON.parse(rawData), nome, dtIni, dtFim);
        }
    };

    function renderizarLinhasExtrato(dados, resumo, pontoHtml, statusHtml, esc) {
        const sortedData = [...dados].sort((a, b) => {
            return window._extratoSortOrder === 'desc' ?
                b.data.localeCompare(a.data) :
                a.data.localeCompare(b.data);
        });

        return sortedData.map(r => {
            const dataObj = new Date(r.data + 'T00:00:00');
            const dataFmt = dataObj.toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit'
            });
            const diaSemana = dataObj.toLocaleDateString('pt-BR', {
                weekday: 'short'
            }).replace('.', '').toUpperCase();

            const p1 = pontoHtml(r.primeiro_ponto, r.atrasou_primeiro_ponto, r.falta_turno1_entrada, r.just_ent1, r.status_crh, r.tipo_justificativa, r.justificativa, r.primeiro_horario, r.em_ferias, r.motivo_afastamento, r, 1);
            const p2 = pontoHtml(r.segundo_ponto, r.atrasou_segundo_ponto, r.falta_turno1_saida, r.just_sai1, r.status_crh, r.tipo_justificativa, r.justificativa, r.segundo_horario, r.em_ferias, r.motivo_afastamento, r, 2);
            const p3 = pontoHtml(r.terceiro_ponto, r.atrasou_terceiro_ponto, r.falta_turno2_entrada, r.just_ent2, r.status_crh, r.tipo_justificativa, r.justificativa, r.terceiro_horario, r.em_ferias, r.motivo_afastamento, r, 3);
            const p4 = pontoHtml(r.quarto_ponto, r.atrasou_quarto_ponto, r.falta_turno2_saida, r.just_sai2, r.status_crh, r.tipo_justificativa, r.justificativa, r.quarto_horario, r.em_ferias, r.motivo_afastamento, r, 4);

            const stHtmlStr = statusHtml(r);
            if (stHtmlStr.includes('OK')) resumo.ok++;
            if (stHtmlStr.includes('Falta')) resumo.faltas++;
            if (stHtmlStr.includes('Incompleto')) resumo.incompletos++;
            if (r.atrasou_primeiro_ponto || r.atrasou_segundo_ponto || r.atrasou_terceiro_ponto || r.atrasou_quarto_ponto) resumo.atrasos++;

            const com = (r.comunicado && r.comunicado !== 'null') ? r.comunicado : '';

            let avisoRow = '';
            if (r.aviso && String(r.aviso).trim() !== '' && r.aviso !== 'null') {
                avisoRow = `<tr class="bg-rose-50/30 flex flex-col md:table-row"><td colspan="7" class="py-2 px-4 text-[10px] font-bold text-rose-600 italic border-b border-rose-100/50 leading-tight"><span class="inline-flex mr-1 items-center justify-center w-4 h-4 bg-rose-100 rounded-full text-rose-600 not-italic">!</span> Recado: ${esc(r.aviso)}</td></tr>`;
            }

            const isHoje = new Date().toISOString().split('T')[0] === r.data;

            return `
                <tr class="group border-b border-slate-50 last:border-0 hover:bg-slate-50/80 transition-all ${isHoje ? 'bg-brand-50/20' : ''} flex flex-col md:table-row mb-4 md:mb-0 rounded-2xl md:rounded-none border md:border-0 border-slate-100 bg-white md:bg-transparent shadow-sm md:shadow-none overflow-hidden">
                    <td class="py-3 px-4 md:px-3 flex justify-between items-center md:table-cell border-b md:border-0 border-slate-50 bg-slate-50/30 md:bg-transparent">
                        <span class="md:hidden text-[9px] font-black text-slate-400 uppercase tracking-widest">Data</span>
                        <div class="flex flex-col leading-none text-right md:text-left">
                            <span class="text-[11px] font-black text-slate-800">${dataFmt}</span>
                            <span class="text-[8px] font-bold text-slate-400 uppercase mt-0.5">${diaSemana}</span>
                        </div>
                    </td>
                    <td class="py-3 px-4 md:px-1 flex justify-between items-center md:table-cell border-b md:border-0 border-slate-50">
                        <span class="md:hidden text-[9px] font-black text-slate-400 uppercase tracking-widest">Entrada 1</span>
                        <div class="text-right md:text-center">${p1}</div>
                    </td>
                    <td class="py-3 px-4 md:px-1 flex justify-between items-center md:table-cell border-b md:border-0 border-slate-50">
                        <span class="md:hidden text-[9px] font-black text-slate-400 uppercase tracking-widest">Saída 1</span>
                        <div class="text-right md:text-center">${p2}</div>
                    </td>
                    <td class="py-3 px-4 md:px-1 flex justify-between items-center md:table-cell border-b md:border-0 border-slate-50">
                        <span class="md:hidden text-[9px] font-black text-slate-400 uppercase tracking-widest">Entrada 2</span>
                        <div class="text-right md:text-center">${p3}</div>
                    </td>
                    <td class="py-3 px-4 md:px-1 flex justify-between items-center md:table-cell border-b md:border-0 border-slate-50">
                        <span class="md:hidden text-[9px] font-black text-slate-400 uppercase tracking-widest">Saída 2</span>
                        <div class="text-right md:text-center">${p4}</div>
                    </td>
                    <td class="py-3 px-4 md:px-1 flex justify-between items-center md:table-cell border-b md:border-0 border-slate-50">
                        <span class="md:hidden text-[9px] font-black text-slate-400 uppercase tracking-widest">Status</span>
                        <div class="text-right md:text-center">${stHtmlStr}</div>
                    </td>
                    <td class="py-3 px-4 md:px-3 flex justify-between items-center md:table-cell">
                         <span class="md:hidden text-[9px] font-black text-slate-400 uppercase tracking-widest">Obs</span>
                         <p class="text-[9px] text-slate-500 italic truncate max-w-[150px] m-0 text-right md:text-left" title="${esc(com)}">${esc(com)}</p>
                    </td>
                </tr>
                ${avisoRow}
            `;
        }).join('');
    }

    window.exportarExtratoPDF = function(nome, dtIni, dtFim) {
        const rawData = sessionStorage.getItem('extrato_pendente_dados');
        if (!rawData) {
            Swal.fire('Erro', 'Dados não encontrados na memória.', 'error');
            return;
        }
        const dados = JSON.parse(rawData);

        var bgFullBase64 = "data:image/png;base64,<?php echo base64_encode(file_get_contents('../public/img/timbre_bg.png')); ?>";
        var logoBgBase64 = "data:image/png;base64,<?php echo base64_encode(file_get_contents('../public/img/timbre_brasao.png')); ?>";
        var logoGovpbBase64 = "data:image/png;base64,<?php echo base64_encode(file_get_contents('../public/img/timbre_header.png')); ?>";
        var logoFunadBase64 = "data:image/jpeg;base64,<?php echo base64_encode(file_get_contents('../public/img/timbre_funad.jpeg')); ?>";

        let pdfBody = [];

        // Cabeçalho da Tabela
        pdfBody.push([{
                text: 'Data',
                style: 'th',
                alignment: 'left'
            },
            {
                text: 'E1',
                style: 'th',
                alignment: 'center'
            },
            {
                text: 'S1',
                style: 'th',
                alignment: 'center'
            },
            {
                text: 'E2',
                style: 'th',
                alignment: 'center'
            },
            {
                text: 'S2',
                style: 'th',
                alignment: 'center'
            },
            {
                text: 'Status',
                style: 'th',
                alignment: 'center'
            },
            {
                text: 'Aviso / Comunicados',
                style: 'th',
                alignment: 'left'
            }
        ]);

        dados.forEach(r => {
            const dataFmt = new Date(r.data + 'T00:00:00').toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit'
            }) + ' (' + new Date(r.data + 'T00:00:00').toLocaleDateString('pt-BR', {
                weekday: 'short'
            }).replace('.', '').toUpperCase() + ')';

            const formatBat = (h, hp, pIdx) => {
                const isFaltaAuto = (!h || h.toLowerCase() === 'falta');
                if (r.liberacao && isFaltaAuto) {
                    const desc = String(r.liberacao.descricao || '').toUpperCase();
                    if (desc.includes('FERIADO') || desc.includes('FACULTATIVO')) {
                        return hp ? desc : '';
                    }
                    if (r.liberacao.data_hora && hp) {
                        const libTime = r.liberacao.data_hora.split(' ')[1].substring(0, 5);
                        const progTime = hp.substring(0, 5);
                        if (libTime <= progTime && (pIdx === 2 || pIdx === 4)) {
                            return 'LIBERADO';
                        }
                    }
                }
                return (h && h.toLowerCase() !== 'falta') ? h.substring(0, 5) : '-';
            };

            const e1 = formatBat(r.primeiro_ponto, r.primeiro_horario, 1);
            const s1 = formatBat(r.segundo_ponto, r.segundo_horario, 2);
            const e2 = formatBat(r.terceiro_ponto, r.terceiro_horario, 3);
            const s2 = formatBat(r.quarto_ponto, r.quarto_horario, 4);

            let status = 'Regular';
            const incompleto = (r.primeiro_horario && (!r.primeiro_ponto || r.primeiro_ponto.toLowerCase() === 'falta')) || (r.segundo_horario && (!r.segundo_ponto || r.segundo_ponto.toLowerCase() === 'falta')) || (r.terceiro_horario && (!r.terceiro_ponto || r.terceiro_ponto.toLowerCase() === 'falta')) || (r.quarto_horario && (!r.quarto_ponto || r.quarto_ponto.toLowerCase() === 'falta'));

            if (incompleto) status = 'Incompleto';
            if (r.falta_turno1_entrada === 'S' || r.falta_turno1_saida === 'S' || r.falta_turno2_entrada === 'S' || r.falta_turno2_saida === 'S' || (r.primeiro_ponto && r.primeiro_ponto.toLowerCase() === 'falta')) {
                status = 'Falta';
            }
            if (r.justificativa || r.just_ent1 || r.just_sai1 || r.just_ent2 || r.just_sai2) {
                status = 'Justificado';
            }
            if (r.em_ferias === 'S' || r.motivo_afastamento) {
                status = 'Afastamento';
            }

            // Prioridade para status de liberação no PDF
            if (r.liberacao) {
                const d = String(r.liberacao.descricao).toUpperCase();
                if (d.includes('FERIADO')) status = 'Feriado';
                else if (d.includes('FACULTATIVO')) status = 'Facultativo';
                else status = 'Liberado';
            }

            const aviso = (r.comunicado && String(r.comunicado) !== 'null') ? r.comunicado : ((r.aviso && String(r.aviso) !== 'null') ? r.aviso : ((r.liberacao && r.liberacao.descricao) ? r.liberacao.descricao : ''));

            pdfBody.push([{
                    text: dataFmt,
                    fontSize: 8
                },
                {
                    text: e1,
                    fontSize: 8,
                    alignment: 'center'
                },
                {
                    text: s1,
                    fontSize: 8,
                    alignment: 'center'
                },
                {
                    text: e2,
                    fontSize: 8,
                    alignment: 'center'
                },
                {
                    text: s2,
                    fontSize: 8,
                    alignment: 'center'
                },
                {
                    text: status,
                    fontSize: 8,
                    alignment: 'center'
                },
                {
                    text: aviso,
                    fontSize: 8
                }
            ]);
        });

        if (dados.length === 0) {
            pdfBody.push([{
                text: 'Nenhum registro encontrado este mês.',
                colSpan: 7,
                alignment: 'center',
                margin: [0, 20, 0, 20],
                color: '#64748b',
                italics: true
            }, {}, {}, {}, {}, {}, {}]);
        }

        var docDefinition = {
            pageSize: 'A4',
            pageOrientation: 'landscape',
            pageMargins: [40, 80, 40, 70],
            background: function() {
                return [{
                        image: bgFullBase64,
                        width: 842,
                        height: 595,
                        absolutePosition: {
                            x: 0,
                            y: 0
                        }
                    },
                    {
                        image: logoBgBase64,
                        width: 450,
                        absolutePosition: {
                            x: (842 / 2) - 225,
                            y: (595 / 2) - 225
                        },
                        opacity: 0.15
                    }
                ];
            },
            header: {
                margin: [40, 20, 40, 0],
                columns: [{
                        width: 250,
                        columns: [{
                                image: logoFunadBase64,
                                width: 60,
                                margin: [0, 8, 15, 0]
                            },
                            {
                                image: logoGovpbBase64,
                                width: 140
                            }
                        ]
                    },
                    {
                        text: 'EXTRATO INDIVIDUAL DE PONTO\nFuncionário: ' + nome + '\nPeríodo: ' + dtIni.split('-').reverse().join('/') + ' a ' + dtFim.split('-').reverse().join('/') + '\nEmitido em: ' + new Date().toLocaleString('pt-BR'),
                        alignment: 'right',
                        fontSize: 9,
                        bold: true,
                        color: '#000000',
                        margin: [0, 10, 0, 0]
                    }
                ]
            },
            footer: function(currentPage, pageCount) {
                return {
                    margin: [40, 0, 40, 20],
                    columns: [{
                            text: 'Pág ' + currentPage.toString() + ' / ' + pageCount,
                            alignment: 'left',
                            fontSize: 7,
                            color: '#000000',
                            width: 60,
                            margin: [0, 25, 0, 0]
                        },
                        {
                            text: 'SECRETARIA DE ESTADO DA EDUCAÇÃO\nFUNAD – FUNDAÇÃO CENTRO INTEGRADO DE APOIO À PESSOA COM DEFICIÊNCIA\nCER IV – CENTRO ESPECIALIZADO EM REABILITAÇÃO\nRua Dr. Orestes Lisboa, S/N - Pedro Gondim - CEP 58031-090 - João Pessoa/PB\nCNPJ: 24.507.065/0001-07 Email: funad@funad.pb.gov.br\nTel.: (83) 3214-7879 / (83) 3244-1542 / (83) 3243-8446 / (83) 3243-3765',
                            alignment: 'center',
                            fontSize: 6,
                            color: '#000000',
                            width: '*',
                            bold: true
                        },
                        {
                            text: '',
                            width: 60
                        }
                    ]
                };
            },
            styles: {
                th: {
                    fontSize: 9,
                    bold: true,
                    color: '#000000',
                    margin: [0, 3, 0, 3]
                }
            },
            content: [{
                table: {
                    headerRows: 1,
                    // Data(14%) E1(9%) S1(9%) E2(9%) S2(9%) Status(15%) Aviso(Restante)
                    widths: ['14%', '9%', '9%', '9%', '9%', '15%', '*'],
                    body: pdfBody
                },
                layout: {
                    hLineWidth: function(i, node) {
                        return 0.5;
                    },
                    vLineWidth: function(i, node) {
                        return 0.5;
                    },
                    hLineColor: function(i, node) {
                        return '#cbd5e1';
                    },
                    vLineColor: function(i, node) {
                        return '#cbd5e1';
                    },
                    paddingLeft: function(i, node) {
                        return 6;
                    },
                    paddingRight: function(i, node) {
                        return 6;
                    },
                    paddingTop: function(i, node) {
                        return 4;
                    },
                    paddingBottom: function(i, node) {
                        return 4;
                    }
                }
            }]
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
            pdfMake.createPdf(docDefinition).download(`Extrato_Ponto_${nome.replace(/\s+/g, '_')}.pdf`);
        } catch (e) {
            console.error(e);
            Swal.fire('Erro Técnico', 'Detalhes: ' + (e.message || String(e)), 'error');
        }
    }

    // --- Gestão de Anexos no Comunicado (Múltiplos Arquivos) ---
    window._pendingComunicadoFiles = [];
    window._renderComunicadoFiles = () => {
        const list = document.getElementById('sw-comunicado-list');
        if (!list) return;
        list.innerHTML = window._pendingComunicadoFiles.map((file, i) => `
            <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200 text-[10px] shadow-sm group hover:border-brand-300 transition-all">
                <div class="flex items-center gap-2 truncate">
                    <div class="w-6 h-6 rounded bg-slate-100 flex items-center justify-center text-slate-500">
                        ${file.type.includes('pdf') ? '📄' : '🖼️'}
                    </div>
                    <span class="font-bold text-slate-700 truncate" title="${file.name}">${file.name}</span>
                </div>
                <button type="button" onclick="window._removerAnexoComunicado(${i})" class="p-1.5 text-slate-300 hover:text-red-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        `).join('');
    };

    window._removerAnexoComunicado = (index) => {
        window._pendingComunicadoFiles.splice(index, 1);
        window._renderComunicadoFiles();
    };

    window.comunicarAtrasoFalta = async function(nome) {
        window._pendingComunicadoFiles = [];

        const {
            value: formValues
        } = await Swal.fire({
            title: 'Comunicar Atraso ou Falta',
            html: `
                <div id="sw-step-form" class="text-left space-y-4 pt-2">
                    <div class="bg-amber-50 p-3 rounded-lg border border-amber-200 text-[11px] text-amber-800 leading-snug">
                        <strong>Importante:</strong> Este comunicado serve para avisar antecipadamente ao seu gestor e ao RH. Não substitui a necessidade de justificativa posterior se necessário.
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Data do Ocorrido</label>
                        <input type="date" id="sw-data-comunicado" class="swal2-input w-full m-0 mt-1 text-sm h-11 bg-white" value="${new Date().toISOString().split('T')[0]}">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Mensagem do Comunicado</label>
                        <textarea id="sw-mensagem-comunicado" class="swal2-textarea w-full m-0 mt-1 text-sm p-3 border-2 focus:border-amber-400" rows="3" placeholder="Ex: Tive um imprevisto com o transporte e vou me atrasar 20 min..."></textarea>
                    </div>
                    
                    <div class="pt-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 block">Documentos Comprobatórios (Opcional)</label>
                        
                        <div id="sw-comunicado-list" class="space-y-1.5 mb-3 empty:hidden"></div>

                        <div id="sw-anexo-container" class="flex items-center gap-3">
                            <button type="button" onclick="window._exibirPasso('select')" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-brand-50 hover:bg-brand-100 text-brand-600 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all border-2 border-dashed border-brand-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Incluir Novo Documento
                            </button>
                        </div>
                    </div>
                </div>

                <div id="sw-step-select" class="hidden flex flex-col gap-3 py-6">
                    <p class="text-sm font-bold text-slate-700 mb-2">Como deseja incluir o documento?</p>
                    <button type="button" onclick="document.getElementById('sw-file-input').click()" class="w-full flex items-center justify-center gap-3 p-4 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl border border-blue-200 transition-all font-bold shadow-sm">
                        <span class="text-2xl">📂</span> Escolher Arquivo ou PDF
                    </button>
                    <button type="button" onclick="window._exibirPasso('camera')" class="w-full flex items-center justify-center gap-3 p-4 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-xl border border-emerald-200 transition-all font-bold shadow-sm">
                        <span class="text-2xl">📷</span> Tirar Foto Agora
                    </button>
                    <button type="button" onclick="window._exibirPasso('form')" class="mt-4 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-600">Voltar</button>
                    <input type="file" id="sw-file-input" class="hidden" accept="image/*,application/pdf" multiple>
                </div>

                <div id="sw-step-camera" class="hidden flex flex-col gap-4">
                    <p class="text-sm font-bold text-slate-700">Capture a Foto do Documento</p>
                    <div class="relative bg-black rounded-2xl overflow-hidden aspect-[3/4] shadow-lg border-4 border-slate-800">
                        <video id="sw-cam-video" class="w-full h-full object-cover" autoplay playsinline></video>
                        <canvas id="sw-cam-canvas" class="hidden"></canvas>
                        <div class="absolute inset-0 border-2 border-white/20 pointer-events-none rounded-xl m-4"></div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="window._tirarFotoComunicado()" class="flex-1 py-4 bg-emerald-600 text-white rounded-2xl font-black uppercase tracking-widest shadow-lg hover:bg-emerald-500 active:scale-95 transition-all text-xs">Capturar Foto</button>
                        <button type="button" onclick="window._exibirPasso('select')" class="px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition-all text-xs">Voltar</button>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Enviar Comunicado',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d97706',
            width: '450px',
            didOpen: () => {
                const stepForm = document.getElementById('sw-step-form');
                const stepSelect = document.getElementById('sw-step-select');
                const stepCamera = document.getElementById('sw-step-camera');
                const actions = Swal.getActions();

                window._exibirPasso = (step) => {
                    stepForm.classList.add('hidden');
                    stepSelect.classList.add('hidden');
                    stepCamera.classList.add('hidden');
                    actions.classList.add('hidden');

                    if (step === 'form') {
                        stepForm.classList.remove('hidden');
                        actions.classList.remove('hidden');
                        window._pararCameraComunicado();
                        window._renderComunicadoFiles();
                    } else if (step === 'select') {
                        stepSelect.classList.remove('hidden');
                    } else if (step === 'camera') {
                        stepCamera.classList.remove('hidden');
                        window._iniciarCameraComunicado();
                    }
                };

                window._iniciarCameraComunicado = async () => {
                    const video = document.getElementById('sw-cam-video');
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({
                            video: {
                                facingMode: 'environment'
                            }
                        });
                        video.srcObject = stream;
                        window._swCamStream = stream;
                    } catch (e) {
                        Swal.fire('Erro', 'Não foi possível acessar a câmera: ' + e.message, 'error');
                        window._exibirPasso('select');
                    }
                };

                window._pararCameraComunicado = () => {
                    if (window._swCamStream) {
                        window._swCamStream.getTracks().forEach(t => t.stop());
                        window._swCamStream = null;
                    }
                };

                window._tirarFotoComunicado = () => {
                    const video = document.getElementById('sw-cam-video');
                    const canvas = document.getElementById('sw-cam-canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);

                    const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                    const file = {
                        name: 'Foto_' + new Date().toLocaleTimeString().replace(/:/g, '-') + '.jpg',
                        type: 'image/jpeg',
                        data: dataUrl
                    };
                    window._pendingComunicadoFiles.push(file);
                    window._exibirPasso('form');
                };

                document.getElementById('sw-file-input').onchange = (e) => {
                    const files = Array.from(e.target.files);
                    let processed = 0;
                    files.forEach(file => {
                        const reader = new FileReader();
                        reader.onload = (ev) => {
                            window._pendingComunicadoFiles.push({
                                name: file.name,
                                type: file.type,
                                data: ev.target.result
                            });
                            processed++;
                            if (processed === files.length) window._exibirPasso('form');
                        };
                        reader.readAsDataURL(file);
                    });
                };
            },
            willClose: () => {
                window._pararCameraComunicado();
            },
            preConfirm: () => {
                const msg = document.getElementById('sw-mensagem-comunicado').value;
                if (!msg || msg.trim().length < 5) {
                    Swal.showValidationMessage('Por favor, descreva brevemente o motivo.');
                    return false;
                }
                return {
                    data: document.getElementById('sw-data-comunicado').value,
                    mensagem: msg,
                    anexos: window._pendingComunicadoFiles.map(f => f.data)
                }
            }
        });

        if (formValues) {
            Swal.fire({
                title: 'Enviando Comunicado...',
                html: 'Aguarde um momento enquanto processamos seus arquivos.',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false
            });

            if (!window._ultimaMatricula || !window._ultimaSenha) {
                console.error("Erro de sessão: Matrícula ou Senha ausentes.", {
                    mat: window._ultimaMatricula,
                    sen: window._ultimaSenha
                });
                Swal.fire('Sessão Expirada', 'Por favor, identifique-se novamente no "Meu Acesso".', 'error');
                return;
            }

            try {
                const res = await fetch('../api/ponto.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'salvar_comunicado',
                        matricula: window._ultimaMatricula,
                        senha: window._ultimaSenha,
                        ...formValues
                    })
                });
                const data = await res.json();

                if (data.success) {
                    Swal.fire({
                        title: 'Enviado!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#059669'
                    }).then(() => {
                        if (window.recarregarExtrato) window.recarregarExtrato();
                    });
                } else {
                    Swal.fire('Erro no Envio', data.message, 'error').then(() => {
                        if (window.recarregarExtrato) window.recarregarExtrato();
                    });
                }
            } catch (e) {
                Swal.fire('Falha de Conexão', 'Não foi possível completar o envio. Verifique sua internet.', 'error').then(() => {
                    if (window.recarregarExtrato) window.recarregarExtrato();
                });
            }
        } else {
            if (window.recarregarExtrato) window.recarregarExtrato();
        }
    }

    window.trocarSenhaFuncionario = async function() {
        if (!window._ultimaMatricula || !window._ultimaSenha) {
            Swal.fire('Aviso', 'Sessão expirada. Por favor, identifique-se novamente no "Meu Acesso".', 'error');
            return;
        }

        const {
            value: formValues
        } = await Swal.fire({
            title: 'Alterar Senha de Acesso',
            html: `
                <div class="text-left space-y-4 pt-2">
                    <div class="bg-blue-50 p-3 rounded-lg border border-blue-200 text-[11px] text-blue-800 leading-snug">
                        <strong>Segurança:</strong> Use uma senha forte de pelo menos 6 caracteres que você não utilize em outros sites.
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Senha Atual</label>
                        <input type="password" id="sw-senha-antiga" class="swal2-input w-full m-0 mt-1 text-sm h-11 bg-slate-50" placeholder="******">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Nova Senha</label>
                        <input type="password" id="sw-senha-nova" class="swal2-input w-full m-0 mt-1 text-sm h-11 bg-white border-2" placeholder="Mínimo 6 caracteres">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Confirmar Nova Senha</label>
                        <input type="password" id="sw-senha-confirma" class="swal2-input w-full m-0 mt-1 text-sm h-11 bg-white border-2" placeholder="Repita a nova senha">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Atualizar Senha',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0ea5e9',
            preConfirm: () => {
                const antiga = document.getElementById('sw-senha-antiga').value;
                const nova = document.getElementById('sw-senha-nova').value;
                const confirma = document.getElementById('sw-senha-confirma').value;

                if (!antiga) {
                    Swal.showValidationMessage('Informe sua senha atual');
                    return false;
                }
                if (nova.length < 6) {
                    Swal.showValidationMessage('A nova senha deve ter pelo menos 6 caracteres');
                    return false;
                }
                if (nova !== confirma) {
                    Swal.showValidationMessage('As novas senhas não coincidem');
                    return false;
                }

                return {
                    antiga,
                    nova
                };
            }
        });

        if (formValues) {
            Swal.fire({
                title: 'Processando...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false
            });

            try {
                const res = await fetch('../api/ponto.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'change_password',
                        matricula: window._ultimaMatricula,
                        senha_antiga: formValues.antiga,
                        nova_senha: formValues.nova
                    })
                });
                const data = await res.json();

                if (data.success) {
                    // Atualiza a senha na memória da sessão para que outras ações (como comunicado) continuem funcionando
                    window._ultimaSenha = formValues.nova;
                    sessionStorage.setItem('extrato_pendente_senha', formValues.nova);

                    Swal.fire('Sucesso!', 'Senha alterada com sucesso.', 'success').then(() => {
                        if (window.recarregarExtrato) window.recarregarExtrato();
                    });
                } else {
                    Swal.fire('Aviso', data.message, 'error').then(() => {
                        if (window.recarregarExtrato) window.recarregarExtrato();
                    });
                }
            } catch (e) {
                Swal.fire('Aviso', 'Falha na comunicação com o servidor.', 'error').then(() => {
                    if (window.recarregarExtrato) window.recarregarExtrato();
                });
            }
        } else {
            // Se cancelou o modal de senha, volta para o extrato
            if (window.recarregarExtrato) window.recarregarExtrato();
        }
    }
    // Prevenção de fechamento acidental de janelas (F5 ou Recarregar)
    window.addEventListener('keydown', (e) => {
        // 116 é a tecla F5
        if ((e.which || e.keyCode) === 116 && typeof Swal !== 'undefined' && Swal.isVisible()) {
            e.preventDefault();
            console.log('F5 bloqueado pois há um modal aberto.');
        }
    });

    window.addEventListener('beforeunload', (e) => {
        if (typeof Swal !== 'undefined' && Swal.isVisible()) {
            e.preventDefault();
            e.returnValue = ''; // Aciona o aviso padrão do navegador
        }
    });

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

        // RESTAURAÇÃO DE ESTADO (F5)
        const pendenteDados = sessionStorage.getItem('extrato_pendente_dados');
        const pendenteNome = sessionStorage.getItem('extrato_pendente_nome');
        if (pendenteDados && pendenteNome) {
            window._ultimaMatricula = sessionStorage.getItem('extrato_pendente_mat');
            window._ultimaSenha = sessionStorage.getItem('extrato_pendente_senha');
            renderizarCartaoIndividual(JSON.parse(pendenteDados), pendenteNome);
        }
    };

    // --- NOVO MÓDULO: ACESSO BIOMÉTRICO AOS MEUS PONTOS ---
    window.abrirAcessoFacialIndividual = async function() {
        const matPrevia = ''; // Facial Individual sempre opera em modo 1:N global para 'Meus Pontos'
        const dtIni = document.getElementById('sw-dt-ini')?.value;
        const dtFim = document.getElementById('sw-dt-fim')?.value;

        // Se o face-api não estiver carregado, tenta carregar
        if (typeof faceapi === 'undefined' || !faceapi.nets.tinyFaceDetector.params) {
            Swal.fire({
                title: 'Carregando Modelos...',
                didOpen: () => Swal.showLoading()
            });
            await loadFaceModels();
        }

        Swal.fire({
            title: 'Acesso via Reconhecimento Facial',
            html: `
                <div class="flex flex-col items-center gap-4 py-2">
                    <p class="text-[11px] text-slate-500 uppercase font-black tracking-widest">Posicione seu rosto frente à câmera</p>
                    <div class="relative w-64 h-64 rounded-full overflow-hidden border-4 border-brand-500 shadow-xl bg-black" id="extrato-container">
                        <video id="extrato-video" class="w-full h-full object-cover" autoplay playsinline muted style="transform: scaleX(-1);"></video>
                        <canvas id="extrato-canvas" class="w-full h-full object-cover hidden" style="transform: scaleX(-1);"></canvas>
                        <div id="extrato-overlay" class="absolute inset-0 border-[12px] border-brand-500/20 rounded-full animate-pulse pointer-events-none"></div>
                    </div>
                    <div id="extrato-status" class="text-xs font-bold text-brand-600 animate-pulse">Iniciando câmera...</div>
                </div>
            `,
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: async () => {
                await window.falarBiometria("Iniciando câmera, por favor aguarde");
                const video = document.getElementById('extrato-video');
                const status = document.getElementById('extrato-status');
                let stream = null;
                let isClosing = false;
                let framesWellFramed = 0;
                let isAuthenticating = false;

                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user'
                        }
                    });
                    video.srcObject = stream;

                    const detectLoop = async () => {
                        if (isClosing || !video.srcObject || video.paused || video.ended || isAuthenticating) return;

                        const container = document.getElementById('extrato-container');

                        try {
                            // Aumentado scoreThreshold para 0.6 para evitar falsos positivos
                            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({
                                    inputSize: 320,
                                    scoreThreshold: 0.6
                                }))
                                .withFaceLandmarks()
                                .withFaceDescriptor();

                            if (detection) {
                                const box = detection.detection.box;

                                // 1. Verificar Enquadramento (Cores do Círculo)
                                const faceWidth = box.width;
                                const videoWidth = video.videoWidth || 640;
                                const videoHeight = video.videoHeight || 480;
                                const faceX = box.x + box.width / 2;
                                const faceY = box.y + box.height / 2;

                                // Centralização e Tamanho (Mesma lógica do registro)
                                const isCentered = Math.abs(faceX - videoWidth / 2) < (videoWidth * 0.12) &&
                                    Math.abs(faceY - videoHeight / 2) < (videoHeight * 0.15);
                                const isCorrectSize = faceWidth > (videoWidth * 0.35) && faceWidth < (videoWidth * 0.65);

                                const isWellFramed = isCentered && isCorrectSize;

                                if (container) {
                                    if (isWellFramed) {
                                        container.classList.remove('border-brand-500', 'border-amber-500');
                                        container.classList.add('border-emerald-500');
                                        framesWellFramed++;
                                    } else {
                                        container.classList.remove('border-brand-500', 'border-emerald-500', 'border-brand-400');
                                        container.classList.add('border-amber-500');
                                        framesWellFramed = 0;
                                    }
                                }

                                // 2. Autenticação apenas se houver estabilidade (10 frames enquadrados)
                                if (!isWellFramed) {
                                    let msg = "Aguardando...";
                                    if (!isCentered) msg = "Centralize seu rosto na moldura";
                                    else if (faceWidth < (videoWidth * 0.35)) msg = "Aproxime-se um pouco mais da câmera";
                                    else msg = "Afaste-se um pouco da câmera";

                                    status.textContent = msg;
                                    status.className = "text-xs font-bold text-amber-600 animate-pulse";
                                    window.falarBiometria(msg);
                                } else if (framesWellFramed >= 10) {
                                    // ENQUADRADO E ESTÁVEL: AUTENTICAR
                                    isAuthenticating = true;
                                    status.textContent = "Face Validada! Autenticando...";
                                    status.className = "text-xs font-bold text-emerald-600";
                                    window.falarBiometria("Aguarde a validação", true);

                                    // Congelar a imagem
                                    const canvas = document.getElementById('extrato-canvas');
                                    if (canvas && video) {
                                        canvas.width = video.videoWidth;
                                        canvas.height = video.videoHeight;
                                        canvas.getContext('2d').drawImage(video, 0, 0);
                                        video.classList.add('hidden');
                                        canvas.classList.remove('hidden');
                                    }

                                    const res = await fetch('../api/ponto.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            action: 'get_meu_ponto',
                                            matricula: null,
                                            facial_descriptor: Array.from(detection.descriptor),
                                            start_date: dtIni,
                                            end_date: dtFim
                                        })
                                    });
                                    const json = await res.json();

                                    if (json.success && !isClosing) {
                                        isClosing = true;
                                        window._ultimaMatricula = json.matricula || '';
                                        window._ultimaSenha = 'facial_authenticated';

                                        // Salva no session storage para persistência (Importante para evitar "Sessão Expirada")
                                        sessionStorage.setItem('extrato_pendente_dados', JSON.stringify(json.data));
                                        sessionStorage.setItem('extrato_pendente_nome', json.funcionario);
                                        sessionStorage.setItem('extrato_pendente_mat', window._ultimaMatricula);
                                        sessionStorage.setItem('extrato_pendente_senha', window._ultimaSenha);
                                        sessionStorage.setItem('extrato_pendente_ini', dtIni || '');
                                        sessionStorage.setItem('extrato_pendente_fim', dtFim || '');

                                        window.falarBiometria("Acesso autorizado. Bem-vindo!", true);
                                        status.textContent = "Acesso Autorizado!";
                                        status.className = "text-xs font-bold text-emerald-700";

                                        setTimeout(() => {
                                            pararCamera();
                                            Swal.close();
                                            renderizarCartaoIndividual(json.data, json.funcionario, dtIni, dtFim);
                                        }, 1500);
                                        return;
                                    } else if (!isClosing) {
                                        const msg = json.message || "Face não reconhecida";
                                        status.textContent = msg;
                                        status.className = "text-xs font-bold text-red-600 animate-pulse";
                                        window.falarBiometria(msg, true);

                                        setTimeout(() => {
                                            if (!isClosing) {
                                                isAuthenticating = false;
                                                framesWellFramed = 0;

                                                // Retomar o vídeo
                                                const canvas = document.getElementById('extrato-canvas');
                                                if (canvas && video) {
                                                    canvas.classList.add('hidden');
                                                    video.classList.remove('hidden');
                                                }

                                                detectLoop();
                                            }
                                        }, 3500);
                                        return;
                                    }
                                }
                                if (!isClosing) requestAnimationFrame(detectLoop);
                            } else {
                                status.textContent = "Ajuste sua posição...";
                                status.className = "text-xs font-bold text-brand-600 animate-pulse";
                                if (container) {
                                    container.classList.remove('border-emerald-500', 'border-amber-500');
                                    container.classList.add('border-brand-500');
                                }
                                if (!isClosing) requestAnimationFrame(detectLoop);
                            }
                        } catch (e) {
                            console.error("Erro no loop do extrato:", e);
                            if (!isClosing) requestAnimationFrame(detectLoop);
                        }
                    };

                    video.onplay = () => detectLoop();

                } catch (err) {
                    status.textContent = "Erro ao acessar câmera.";
                    console.error(err);
                }

                function pararCamera() {
                    isClosing = true;
                    if (stream) {
                        stream.getTracks().forEach(t => t.stop());
                        video.srcObject = null;
                    }
                }
                window._pararCameraExtrato = pararCamera;
            },
            willClose: () => {
                if (window._pararCameraExtrato) window._pararCameraExtrato();
            }
        });
    };
</script>


<!-- Modal Biometria Unificado (Premium UI - Global Overlay) -->
<div id="modalBiometria" class="fixed inset-0 z-[9999] hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/95 backdrop-blur-xl transition-opacity"
        onclick="fecharModalBiometria()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto no-scrollbar">
        <div class="flex min-h-full items-center justify-center p-2 text-center">
            <div class="relative transform overflow-hidden rounded-[40px] bg-white text-left shadow-2xl transition-all w-full max-w-sm border border-white/20 p-0 flex flex-col my-4">

                <!-- Header Premium (Fixed) -->
                <div class="px-6 py-4 flex items-center justify-between bg-white border-b border-slate-50 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600 shadow-inner">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path>
                            </svg>
                        </div>
                        <div class="text-left">
                            <h3 class="text-base font-black text-slate-800 m-0 leading-tight">Acesso Biométrico</h3>
                            <p class="text-[9px] font-bold text-slate-400 m-0 uppercase tracking-widest">Registrar Ponto</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end mr-4">
                        <div id="bioClock" class="text-lg font-black text-brand-600 leading-tight">--:--:--</div>
                        <div id="bioDate" class="text-[8px] font-bold text-slate-400 uppercase tracking-widest leading-none">--/--/----</div>
                    </div>
                    <button onclick="fecharModalBiometria()" class="p-2 text-slate-300 hover:text-slate-600 hover:bg-slate-50 rounded-xl transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <input type="hidden" id="bio_modo" value="facial">
                <input type="hidden" id="bio_matricula" value="">

                <!-- Área Central (Scrollable if needed) -->
                <div class="overflow-y-auto no-scrollbar flex-1">
                    <div id="areaFacial" class="p-6 flex flex-col items-center">
                        <div class="relative w-full max-w-[280px] aspect-[4/5] bg-slate-50 rounded-[32px] overflow-hidden shadow-lg border-4 border-slate-50 transition-all duration-500" id="videoContainer">
                            <video id="videoFeed" autoplay playsinline muted class="absolute inset-0 h-full w-full object-cover z-10 hidden" style="transform: scaleX(-1);"></video>
                            <canvas id="videoCanvas" class="absolute inset-0 h-full w-full object-cover z-10 hidden" style="transform: scaleX(-1);"></canvas>

                            <!-- Scanner Overlay (Partículas Digitais) -->
                            <div id="scannerLine" class="hidden absolute inset-0 z-20 pointer-events-none overflow-hidden">
                                <div class="scanner-beam"></div>
                                <div class="scanner-particle" style="left: 15%; animation-duration: 1.5s; animation-delay: 0.2s;"></div>
                                <div class="scanner-particle" style="left: 35%; animation-duration: 2.1s; animation-delay: 0.5s;"></div>
                                <div class="scanner-particle" style="left: 55%; animation-duration: 1.8s; animation-delay: 0.1s;"></div>
                                <div class="scanner-particle" style="left: 75%; animation-duration: 2.5s; animation-delay: 0.8s;"></div>
                                <div class="scanner-particle" style="left: 90%; animation-duration: 1.7s; animation-delay: 0.3s;"></div>
                                <div class="scanner-particle" style="left: 5%; animation-duration: 2.3s; animation-delay: 1.2s;"></div>
                            </div>

                            <!-- Overlay Circular -->
                            <div id="guideFacial" class="absolute inset-0 z-20 pointer-events-none flex flex-col items-center justify-center">
                                <div id="faceOvalBorder" class="w-[85%] h-[80%] border-[3px] border-dashed border-white/50 rounded-[50%] transition-all duration-500 shadow-[0_0_80px_rgba(0,0,0,0.3)]"></div>

                                <!-- Countdown de nova tentativa -->
                                <div id="countdownOval" class="hidden absolute inset-0 z-30 flex items-center justify-center">
                                    <div class="relative flex items-center justify-center w-[85%] h-[80%] rounded-[50%] bg-black/75">
                                        <svg class="absolute inset-0 w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                                            <ellipse cx="50" cy="50" rx="48.5" ry="48.5" fill="none" stroke="rgba(239,68,68,0.25)" stroke-width="3" />
                                            <ellipse id="countdownRing" cx="50" cy="50" rx="48.5" ry="48.5" fill="none"
                                                stroke="#ef4444" stroke-width="3"
                                                stroke-dasharray="304.7" stroke-dashoffset="0"
                                                transform="rotate(-90 50 50)" />
                                        </svg>
                                        <div id="countdownNumber" class="relative z-10 text-white font-black select-none"
                                            style="font-size:4.5rem;line-height:1;text-shadow:0 2px 24px rgba(239,68,68,0.9);transition:transform 0.12s ease,opacity 0.12s ease;"></div>
                                    </div>
                                </div>

                                <!-- Badge de Status Flutuante -->
                                <div id="faceStatus" class="absolute bottom-6 left-0 right-0 flex justify-center">
                                    <span class="bg-slate-900/90 text-white text-[9px] font-black px-5 py-2 rounded-full uppercase tracking-widest border border-white/10 shadow-2xl transition-all">Aguardando Face...</span>
                                </div>
                            </div>

                            <!-- Loading State -->
                            <div id="camLoading" class="absolute inset-0 z-0 flex flex-col items-center justify-center bg-slate-50">
                                <div class="w-10 h-10 border-4 border-brand-100 border-t-brand-600 rounded-full animate-spin"></div>
                            </div>
                        </div>

                        <!-- Instruções e Progresso -->
                        <div class="mt-6 flex flex-col items-center gap-3 w-full max-w-[280px]">
                            <div class="flex items-center gap-2 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <p id="instrucaoTexto" class="text-[9px] font-black uppercase tracking-widest m-0">Olhe para a câmera</p>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div id="progressBar" class="h-full bg-emerald-500 w-0 transition-all duration-300"></div>
                            </div>
                        </div>
                    </div>
                </div>



            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>