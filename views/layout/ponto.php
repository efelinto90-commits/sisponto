<?php 
require_once '../config/Database.php';
include 'layout/header.php'; 
?>

<!-- Fundo interativo do relógio -->
<div class="flex-1 flex items-center justify-center relative overflow-hidden bg-gradient-to-br from-brand-900 via-brand-600 to-sky-500">
    <!-- Design Elements em background -->
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCI+PHBhdGggZD0iTTAgMGgyeTR2MjRIMGoiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wMSkiLz48L3N2Zz4=')] opacity-20"></div>

    <div class="w-full max-w-md relative z-10 px-4">
        <div class="backdrop-blur-md bg-white/80 rounded-3xl p-6 shadow-2xl relative overflow-hidden border border-white/30 transition-all duration-300">
            <title>Relógio de Ponto - Biometria</title>
            <script src="../public/js/face-api.min.js"></script>
            <script src="../public/js/html2pdf.bundle.min.js"></script>

            <!-- Relógio em destaque -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center gap-1.5 text-[10px] font-black text-brand-600/80 uppercase tracking-[0.25em] mb-3">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    FUNAD · Ponto Digital
                </div>
                <div id="liveClock" class="text-7xl font-black text-slate-800 tracking-tighter tabular-nums leading-none drop-shadow-sm">
                    00:00:00
                </div>
                <div id="liveDate" class="text-xs font-semibold text-slate-500 mt-2 uppercase tracking-widest"></div>
            </div>

            <!-- Divider sutil -->
            <div class="w-12 h-0.5 bg-gradient-to-r from-brand-400 to-sky-400 rounded-full mx-auto mb-6"></div>

            <!-- Input de Matrícula -->
            <form id="pontoForm" onsubmit="registrarPonto(event)" class="space-y-4">
                <div class="space-y-1.5">
                    <label for="matricula" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Matrícula</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none z-10">
                            <svg class="h-4.5 w-4.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                            </svg>
                        </div>
                        <input type="text" id="matricula" name="matricula"
                            class="block w-full pl-10 pr-3 py-3.5 border border-slate-200 rounded-2xl bg-white/90 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-400/60 focus:border-brand-400 text-base font-bold transition-all shadow-sm text-center tracking-[0.2em] uppercase"
                            placeholder="000000" autocomplete="off" autocorrect="off" autocapitalize="off"
                            spellcheck="false" oninput="buscarMatriculas(this.value)"
                            onblur="setTimeout(fecharSugestoes, 200)">
                        <!-- Dropdown de sugestões -->
                        <div id="matriculaSugestoes"
                            class="absolute left-0 right-0 top-full mt-1.5 z-50 hidden bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden max-h-52 overflow-y-auto">
                        </div>
                    </div>
                </div>

                <div id="containerSenhaRelogio" class="hidden transition-all duration-300">
                    <label for="senha" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Senha</label>
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4.5 w-4.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input type="password" id="senha" name="senha"
                            class="block w-full pl-10 pr-12 py-3.5 border border-slate-200 rounded-2xl bg-white/90 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-400/60 focus:border-brand-400 text-base font-bold transition-all shadow-sm text-center tracking-widest"
                            placeholder="••••••••" autocomplete="current-password">
                        <button type="button" onclick="togglePassword('senha', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-slate-400 hover:text-brand-500 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-400 rounded-lg"
                            aria-label="Mostrar/Esconder Senha">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" id="btnSubmit"
                    class="w-full flex justify-center py-3.5 px-4 rounded-2xl shadow-lg text-base font-black text-white bg-brand-600 hover:bg-brand-500 active:scale-[.98] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-400 transition-all relative overflow-hidden group">
                    <span class="relative z-10 flex items-center gap-2">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Registrar Ponto
                    </span>
                    <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-10 transition-opacity"></div>
                </button>
            </form>

            <div id="containerBiometria" class="hidden transition-all duration-300">
                <div class="relative flex py-4 items-center">
                    <div class="flex-grow border-t border-slate-100"></div>
                    <span class="flex-shrink-0 mx-3 text-slate-300 text-[10px] font-black uppercase tracking-widest">ou</span>
                    <div class="flex-grow border-t border-slate-100"></div>
                </div>

                <button type="button" id="btnBiometria" onclick="abrirModalBiometria()"
                    class="w-full flex justify-center py-3.5 px-4 border-2 border-brand-200 rounded-2xl text-base font-black text-brand-600 bg-brand-50/80 hover:bg-brand-100 hover:border-brand-300 active:scale-[.98] focus:outline-none focus:ring-2 focus:ring-brand-400 transition-all">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/>
                        </svg>
                        Usar Biometria
                    </span>
                </button>
            </div>

            <!-- Modal Biometria Unificado -->
            <div id="modalBiometria" class="fixed inset-0 z-[999] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="fecharModalBiometria()"></div>
                <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                    <div class="relative overflow-hidden rounded-3xl bg-white text-left shadow-2xl w-full max-w-sm border border-slate-100 p-0 flex flex-col" style="height:calc(100dvh - 2rem);max-height:640px">
                        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 flex-shrink-0">
                            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4">
                                    </path>
                                </svg>
                                Acesso Biométrico
                            </h3>
                            <button onclick="fecharModalBiometria()" aria-label="Fechar Modal"
                                class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-200 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-slate-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Tabs -->
                        <div class="px-4 pt-3 flex-shrink-0">
                            <div class="flex gap-1 bg-slate-100 rounded-2xl p-1">
                                <button id="tabFacial" onclick="mudarModoBiometria('facial')"
                                    class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-bold rounded-xl transition-all bg-white shadow-sm text-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-400">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Facial
                                </button>
                                <button id="tabDigital" onclick="mudarModoBiometria('digital')"
                                    class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-bold rounded-xl transition-all text-slate-400 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-400">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/>
                                    </svg>
                                    Digital
                                </button>
                            </div>
                        </div>

                        <input type="hidden" id="bio_modo" value="facial">
                        <input type="hidden" id="bio_matricula" value="">

                        <div class="flex-1 min-h-0 flex flex-col">
                            <!-- Area Facial -->
                            <div id="areaFacial" class="flex-1 min-h-0 flex flex-col">
                                <!-- Camera — cresce para preencher o espaço disponível -->
                                <div class="flex-1 min-h-0 px-4 pt-3">
                                    <div class="relative w-full h-full bg-slate-900 rounded-2xl overflow-hidden shadow-inner" id="videoContainer">
                                        <video id="videoFeed" autoplay playsinline muted
                                            class="absolute inset-0 w-full h-full object-cover hidden"
                                            style="transform:scaleX(-1)"></video>
                                        <canvas id="videoCanvas"
                                            class="absolute inset-0 w-full h-full object-cover hidden"
                                            style="transform:scaleX(-1)"></canvas>

                                        <!-- Loading state -->
                                        <div id="camLoading" class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-slate-900 text-white z-20">
                                            <div class="relative w-10 h-10">
                                                <div class="absolute inset-0 rounded-full border-2 border-slate-700"></div>
                                                <div class="absolute inset-0 rounded-full border-2 border-brand-500 border-t-transparent animate-spin"></div>
                                            </div>
                                            <span class="text-slate-300 text-[10px] font-black uppercase tracking-widest">Iniciando Câmera...</span>
                                        </div>

                                        <!-- Overlay: máscara + oval SVG + arco de confiança -->
                                        <div id="guideFacial" class="absolute inset-0 z-20 pointer-events-none">
                                            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 320 240" preserveAspectRatio="xMidYMid slice">
                                                <defs>
                                                    <mask id="ovalMask">
                                                        <rect width="320" height="240" fill="white"/>
                                                        <ellipse cx="160" cy="118" rx="84" ry="108" fill="black"/>
                                                    </mask>
                                                </defs>
                                                <rect width="320" height="240" fill="rgba(0,0,0,0.45)" mask="url(#ovalMask)"/>
                                                <ellipse id="faceOvalBorder" cx="160" cy="118" rx="84" ry="108" fill="none" stroke="rgba(148,163,184,0.6)" stroke-width="1.5" stroke-dasharray="5 4"/>
                                                <ellipse id="confidenceArc" cx="160" cy="118" rx="84" ry="108" fill="none" stroke="transparent" stroke-width="4" stroke-dasharray="576" stroke-dashoffset="576" stroke-linecap="round" transform="rotate(-90 160 118)" style="transition:stroke-dashoffset .2s ease,stroke .2s"/>
                                            </svg>

                                            <!-- Badge de status -->
                                            <div id="faceStatus" class="absolute bottom-4 left-0 right-0 flex justify-center">
                                                <span class="bg-black/70 backdrop-blur-md text-white text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest border border-white/15 shadow-lg transition-all" style="min-width:11rem;text-align:center">
                                                    Aguardando Face...
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Flash branco no momento da captura -->
                                        <div id="captureFlash" class="absolute inset-0 bg-white opacity-0 z-40 pointer-events-none transition-opacity duration-150"></div>
                                    </div>
                                </div>

                                <!-- Rodapé sempre visível: instrução + botão -->
                                <div class="px-4 py-3 flex flex-col gap-2.5">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span id="blinkGuideText" class="text-[11px] font-semibold text-slate-500">Olhe para a câmera e pisque</span>
                                        </div>
                                        <div id="faceScoreBar" class="opacity-0 transition-opacity duration-300">
                                            <div class="h-1 w-full bg-slate-100 rounded-full overflow-hidden">
                                                <div id="faceScoreFill" class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-500 transition-all duration-200" style="width:0%"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <button onclick="tirarFotoFacial()" id="btnCapturarFacial"
                                        class="w-full py-3.5 bg-brand-600 hover:bg-brand-500 active:scale-95 text-white rounded-xl font-bold shadow-md shadow-brand-200/60 transition-all flex justify-center items-center gap-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0118.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Capturar Facial
                                    </button>
                                </div>
                            </div>

                            <!-- Area Digital -->
                            <div id="areaDigital" class="w-full flex-col items-center hidden">
                                <div class="w-48 h-60 bg-slate-50 rounded-2xl border-4 border-slate-100 flex flex-col items-center justify-center mb-6 relative overflow-hidden group shadow-inner">
                                    <!-- Ring Animation -->
                                    <div id="bioScanRing" class="absolute inset-0 border-[6px] border-emerald-400 rounded-2xl opacity-0 scale-90 transition-all duration-700 pointer-events-none"></div>
                                    <div class="text-slate-300 group-hover:text-slate-400 transition-colors">
                                        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path>
                                        </svg>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-400 mt-4 uppercase tracking-tighter">Aguardando Sensor...</span>
                                </div>
                                <button onclick="iniciarCapturaDigitalClock()" id="btnAtivarSensor"
                                    class="w-full py-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl font-bold shadow-lg shadow-emerald-100 transition-all flex justify-center items-center gap-2 focus:outline-none focus:ring-2 focus:ring-emerald-400 active:scale-95">
                                    <span id="btnAtivarSensorText">Iniciar Sensor</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-center gap-6">
                <a href="#" onclick="abrirMeuAcesso(event)"
                    class="text-sm font-bold text-brand-600 hover:text-brand-700 transition-colors flex items-center justify-center gap-1.5 px-3 py-1.5 bg-brand-50 rounded-lg border border-brand-100 shadow-sm hover:shadow-md active:scale-95 transition-all focus:outline-none focus:ring-2 focus:ring-brand-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Meu Acesso
                </a>
                <a href="#" onclick="pedirAcessoAdmin(event)"
                    class="text-sm font-semibold text-slate-500 hover:text-slate-700 transition-colors flex items-center justify-center gap-1 focus:outline-none focus:ring-2 focus:ring-brand-400 rounded-lg p-1">
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
    // --- Constantes e Variáveis Globais ---
    let _todosFunc = null;
    let listaHorarios = [];
    let currentFlow = 'identificacao';
    let metodosPermitidos = [];
    let temWebAuthn = false;
    let videoStream = null;
    let modelsLoaded = false;
    let isProcessingFacial = false;
    let detectionLoopActive = false;
    let blinkCount = 0;
    let prevEarBelowThreshold = false;
    let isLivenessOk = false;
    let faceDetectedSince = null;
    let earBaseline = null;
    let earCalibBuf = [];
    let _faceMatcher = null;
    let _geoCache = null;

    const BLINKS_REQUIRED = 1;
    const LIVENESS_AUTO_MS = 2500;
    const LEFT_EYE_IDX  = [36, 37, 38, 39, 40, 41];
    const RIGHT_EYE_IDX = [42, 43, 44, 45, 46, 47];
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

    // --- Funções Auxiliares UI ---
    <?php 
        $dbConn = Config\Database::getConnection();
        $dbRes = $dbConn->query("SELECT (EXTRACT(EPOCH FROM NOW()) * 1000) as ts")->fetch();
        $dbTimestamp = $dbRes['ts'];
    ?>
    const _serverTimeAtLoad = <?php echo $dbTimestamp; ?>;
    const _clientTimeAtLoad = Date.now();
    const _serverTimeOffset = _serverTimeAtLoad - _clientTimeAtLoad;

    function updateClock() {
        const now = new Date(Date.now() + _serverTimeOffset);
        const dFormat = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'full', timeZone: 'America/Sao_Paulo' }).format(now);
        const tFormat = new Intl.DateTimeFormat('pt-BR', { 
            hour: '2-digit', minute: '2-digit', second: '2-digit', 
            hour12: false, timeZone: 'America/Sao_Paulo' 
        }).format(now);

        document.getElementById('liveClock').textContent = tFormat;
        document.getElementById('liveDate').textContent = dFormat;
    }
    setInterval(updateClock, 1000);
    updateClock();

    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    function resetarFluxo() {
        currentFlow = 'identificacao';
        document.getElementById('containerSenhaRelogio').classList.add('hidden');
        document.getElementById('containerBiometria').classList.add('hidden');
        const btn = document.getElementById('btnSubmit');
        btn.innerHTML = `<span class="relative z-10 flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Identificar Matrícula</span>`;
        document.getElementById('matricula').value = '';
        document.getElementById('senha').value = '';
    }

    function fecharSugestoes() { document.getElementById('matriculaSugestoes')?.classList.add('hidden'); }

    // --- Geolocalização ---
    async function obterLocalizacao() {
        if (_geoCache) return _geoCache;
        return new Promise((resolve) => {
            if (!navigator.geolocation) { resolve(null); return; }
            navigator.geolocation.getCurrentPosition(
                (pos) => { _geoCache = { lat: pos.coords.latitude, lng: pos.coords.longitude }; resolve(_geoCache); },
                (err) => { console.warn("Erro GPS:", err.message); resolve(null); },
                { enableHighAccuracy: true, timeout: 5000 }
            );
        });
    }

    // --- API Calls ---
    async function _carregarHorarios() {
        try {
            const res = await fetch('../api/horarios.php');
            const json = await res.json();
            if (json.success) listaHorarios = json.data;
        } catch (e) { console.error("Erro carregando horarios", e); }
    }
    _carregarHorarios();

    async function _carregarFuncionarios() {
        if (_todosFunc) return _todosFunc;
        try {
            const res = await fetch('../api/funcionarios.php');
            const json = await res.json();
            _todosFunc = json.success ? json.data : [];
        } catch (e) { _todosFunc = []; }
        return _todosFunc;
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
                if (data.ficha_incompleta) {
                    await Swal.fire({ title: 'Atualização Cadastral', text: 'Sua ficha está incompleta. Clique em atualizar.', icon: 'info', confirmButtonText: 'Atualizar', confirmButtonColor: '#0ea5e9' });
                    const atualizou = await window.abrirModalFichaIncompleta(data);
                    if (!atualizou) { resetarFluxo(); return false; }
                }
                metodosPermitidos = data.metodos;
                temWebAuthn = !!data.has_webauthn;
                currentFlow = 'autenticacao';
                return true;
            } else {
                Swal.fire({ title: 'Ops!', text: data.message, icon: 'error', confirmButtonColor: '#0ea5e9' });
                return false;
            }
        } catch (err) { console.error(err); return false; }
    };

    async function executarRegistroPonto(matricula, senha) {
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = `
            <span class="relative z-10 flex items-center gap-2">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Registrando...
            </span>
        `;
        try {
            const loc = await obterLocalizacao();
            const res = await fetch('../api/ponto.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'ponto_matricula', matricula, senha, lat: loc?.lat, lng: loc?.lng })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ title: 'Marcado!', html: `<b>${data.funcionario}</b><br>Seu ponto foi registrado às ${data.hora}`, icon: 'success', confirmButtonText: 'Fechar', confirmButtonColor: '#0ea5e9' });
                resetarFluxo();
            } else {
                Swal.fire({ title: 'Ops!', text: data.message, icon: 'error', confirmButtonColor: '#0ea5e9' });
            }
        } catch (err) {
            Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
        } finally {
            btn.disabled = false;
            if (currentFlow === 'autenticacao') btn.innerHTML = `<span class="relative z-10 flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Confirmar Ponto</span>`;
            else resetarFluxo();
        }
    }

    // --- Autocomplete Funcionários ---
    async function buscarMatriculas(valor) {
        const dropdown = document.getElementById('matriculaSugestoes');
        valor = valor.toUpperCase().trim();
        if (valor.length < 1) { dropdown.classList.add('hidden'); dropdown.innerHTML = ''; return; }
        const lista = await _carregarFuncionarios();
        const filtrados = lista.filter(f => f.matricula.includes(valor) || f.nome.toUpperCase().includes(valor)).slice(0, 8);
        if (!filtrados.length) { dropdown.classList.add('hidden'); return; }
        dropdown.innerHTML = filtrados.map(f => `<button type="button" onclick="selecionarMatricula('${f.matricula.replace(/'/g, "\\'")}', '${f.nome.replace(/'/g, "\\'")}')" class="w-full flex items-center gap-3 px-4 py-3 hover:bg-brand-50 transition-colors text-left border-b border-slate-50 last:border-0 group"><div class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center flex-shrink-0 text-sm font-bold">${f.nome.charAt(0).toUpperCase()}</div><div class="flex-1 min-w-0"><div class="text-sm font-bold text-slate-800 truncate">${f.nome}</div><div class="text-xs font-mono text-brand-600 font-semibold">${f.matricula}</div></div><svg class="w-4 h-4 text-slate-300 group-hover:text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>`).join('');
        dropdown.classList.remove('hidden');
    }

    function selecionarMatricula(matricula, nome) {
        document.getElementById('matricula').value = matricula;
        fecharSugestoes();
        registrarPonto({ preventDefault: () => {} }, true);
    }

    // --- Fluxo Principal de Registro ---
    async function registrarPonto(e, isAutoBiometric = false) {
        if (e && e.preventDefault) e.preventDefault();
        const inputMatricula = document.getElementById('matricula');
        const matricula = inputMatricula.value.trim();
        const senha = document.getElementById('senha').value;
        const btn = document.getElementById('btnSubmit');

        if (!matricula && currentFlow === 'identificacao') {
            window.abrirModalBiometria();
            return;
        }

        if (currentFlow === 'identificacao') {
            btn.disabled = true;
            btn.innerHTML = '<span class="relative z-10 flex items-center gap-2"><svg class="animate-spin h-5 w-5 text-white" ...>Verificando...</span>';
            
            const success = await window.obterDadosFuncionario(matricula);
            btn.disabled = false;

            if (success) {
                let mostrouAlgo = false;
                if (metodosPermitidos.includes('senha')) {
                    document.getElementById('containerSenhaRelogio').classList.remove('hidden');
                    document.getElementById('senha').focus();
                    mostrouAlgo = true;
                }
                const temBiometria = metodosPermitidos.some(m => ['facial', 'biometria', 'digital', 'webauthn'].includes(m));
                if (temBiometria) {
                    document.getElementById('containerBiometria').classList.remove('hidden');
                    mostrouAlgo = true;
                    if (metodosPermitidos.includes('facial')) window.abrirModalBiometria();
                }
                btn.innerHTML = `<span class="relative z-10 flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Confirmar Ponto</span>`;
                if (!mostrouAlgo) { Swal.fire('Atenção!', 'Nenhum método de acesso configurado.', 'warning'); resetarFluxo(); }
            }
        } else {
            if (metodosPermitidos.includes('senha') && !senha) { Swal.fire('Atenção', 'Digite sua senha.', 'warning'); return; }
            await executarRegistroPonto(matricula, senha);
        }
    }

    // --- Biometria Facial (Face-API) ---
    function computeEAR(positions, idx) {
        const p = idx.map(i => positions[i]);
        const v1 = Math.hypot(p[1].x - p[5].x, p[1].y - p[5].y);
        const v2 = Math.hypot(p[2].x - p[4].x, p[2].y - p[4].y);
        const h  = Math.hypot(p[0].x - p[3].x, p[0].y - p[3].y);
        return h > 0 ? (v1 + v2) / (2.0 * h) : 0.3;
    }

    function updateEarBaseline(ear) {
        earCalibBuf.push(ear);
        if (earCalibBuf.length > 12) earCalibBuf.shift();
        const top = [...earCalibBuf].sort((a,b)=>b-a).slice(0, Math.ceil(earCalibBuf.length * 0.6));
        earBaseline = top.reduce((s,v)=>s+v,0)/top.length;
    }

    function _setConfidenceArc(pct, livenessOk) {
        const arc = document.getElementById('confidenceArc');
        const border = document.getElementById('faceOvalBorder');
        const container = document.getElementById('videoContainer');
        if (!arc) return;
        const clamped = Math.min(Math.max(pct, 0), 1);
        arc.setAttribute('stroke-dashoffset', 576 * (1 - clamped));
        if (clamped > 0.05) {
            const color = livenessOk ? 'rgba(16,185,129,0.95)' : 'rgba(251,191,36,0.9)';
            arc.setAttribute('stroke', color);
            if (border) border.setAttribute('stroke', livenessOk ? 'rgba(16,185,129,0.35)' : 'rgba(251,191,36,0.3)');
            if (container) container.style.boxShadow = livenessOk ? '0 0 0 3px rgba(16,185,129,0.5), 0 8px 24px rgba(16,185,129,0.15)' : '0 0 0 2px rgba(251,191,36,0.45)';
        } else {
            arc.setAttribute('stroke', 'transparent');
            if (border) border.setAttribute('stroke', 'rgba(148,163,184,0.5)');
            if (container) container.style.boxShadow = '';
        }
    }

    function _resetCameraUI() {
        const video = document.getElementById('videoFeed');
        const canvas = document.getElementById('videoCanvas');
        if(video) video.classList.remove('hidden');
        if(canvas) canvas.classList.add('hidden');
        _setConfidenceArc(0, false);
        const scoreBar = document.getElementById('faceScoreBar');
        if(scoreBar) scoreBar.style.opacity = '0';
    }

    async function loadFaceModels() {
        if (modelsLoaded) return;
        const statusEl = document.querySelector('#faceStatus span');
        try { await faceapi.tf.setBackend('webgl'); await faceapi.tf.ready(); } catch(e) { console.warn('WebGL indisponível:', e); }
        const LOCAL_URL = '../models/face-api';
        const CDN_URL   = 'https://justadudewhohacks.github.io/face-api.js/models';
        const loadFrom = (url) => Promise.all([faceapi.nets.tinyFaceDetector.loadFromUri(url), faceapi.nets.faceLandmark68Net.loadFromUri(url), faceapi.nets.faceRecognitionNet.loadFromUri(url)]);
        try { await loadFrom(LOCAL_URL); console.log("Modelos carregados localmente."); } catch(e) {
            console.warn("Usando CDN...");
            try { await loadFrom(CDN_URL); console.log("Modelos carregados do CDN."); } catch(e2) { console.error("Erro ao carregar modelos:", e2); if(statusEl) statusEl.innerText = "Erro ao carregar IA"; return; }
        }
        modelsLoaded = true;
        preloadDescriptors();
    }

    async function preloadDescriptors() {
        try {
            const resp = await fetch('../api/get_descriptors.php');
            if (!resp.ok) return;
            const data = await resp.json();
            if (data.descriptors && data.descriptors.length) {
                const labeled = data.descriptors.map(e => new faceapi.LabeledFaceDescriptors(e.id + '|' + e.matricula, [new Float32Array(e.descriptor)]));
                _faceMatcher = new faceapi.FaceMatcher(labeled, 0.5);
                console.log(`${data.descriptors.length} descritores pré-carregados.`);
            }
        } catch(e) { console.warn("Pré-carga descritores falhou:", e); }
    }

    async function startFaceDetectionLoop() {
        const statusEl = document.querySelector('#faceStatus span');
        if (!modelsLoaded) { if(detectionLoopActive) setTimeout(startFaceDetectionLoop, 300); return; }
        if (!detectionLoopActive || isProcessingFacial) { if(detectionLoopActive) setTimeout(startFaceDetectionLoop, 66); return; }
        const video = document.getElementById('videoFeed');
        if (!video || video.paused || video.ended) { if(detectionLoopActive) setTimeout(startFaceDetectionLoop, 66); return; }
        try {
            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.75 }))
                .withFaceLandmarks()
                .withFaceDescriptor();
            if (detection) {
                const positions = detection.landmarks.positions;
                const earL = computeEAR(positions, LEFT_EYE_IDX);
                const earR = computeEAR(positions, RIGHT_EYE_IDX);
                const ear = (earL + earR) / 2;
                updateEarBaseline(ear);
                const threshold = earBaseline ? earBaseline * 0.70 : 0.20;
                const eyeClosed = ear < threshold;
                if (eyeClosed && !prevEarBelowThreshold) { blinkCount++; prevEarBelowThreshold = true; }
                else if (!eyeClosed) prevEarBelowThreshold = false;
                if (blinkCount >= BLINKS_REQUIRED) isLivenessOk = true;
                if (!faceDetectedSince) faceDetectedSince = Date.now();
                if (!isLivenessOk && (Date.now() - faceDetectedSince) >= LIVENESS_AUTO_MS) isLivenessOk = true;
                const score = detection.detection.score;
                _setConfidenceArc(score / 0.85, isLivenessOk);
                const scoreBar = document.getElementById('faceScoreBar');
                const scoreFill = document.getElementById('faceScoreFill');
                if (scoreBar) scoreBar.style.opacity = '1';
                if (scoreFill) scoreFill.style.width = `${Math.min(score / 0.85, 1) * 100}%`;
                // 1. Verificar Enquadramento e Centralização
                const box = detection.detection.box;
                const videoWidth = video.videoWidth || 640;
                const videoHeight = video.videoHeight || 480;
                const faceWidth = box.width;
                const faceX = box.x + box.width / 2;
                const faceY = box.y + box.height / 2;
                
                const isCentered = Math.abs(faceX - videoWidth / 2) < (videoWidth * 0.15) && 
                                   Math.abs(faceY - videoHeight / 2) < (videoHeight * 0.20);
                const isCorrectSize = faceWidth > (videoWidth * 0.35) && faceWidth < (videoWidth * 0.65);
                const isWellFramed = isCentered && isCorrectSize;

                // Feedback visual no Oval
                const ovalBorder = document.getElementById('faceOvalBorder');
                if (ovalBorder) {
                    if (isWellFramed) {
                        ovalBorder.setAttribute('stroke', '#10b981'); // Emerald 500
                        ovalBorder.setAttribute('stroke-dasharray', '0');
                    } else {
                        ovalBorder.setAttribute('stroke', '#f59e0b'); // Amber 500
                        ovalBorder.setAttribute('stroke-dasharray', '5 4');
                    }
                }

                if (statusEl) {
                    if (!isWellFramed) {
                        let msg = "Aguardando...";
                        if (!isCentered) msg = "Centralize seu Rosto";
                        else if (faceWidth < (videoWidth * 0.35)) msg = "Aproxime seu Rosto";
                        else msg = "Afaste seu Rosto";
                        
                        statusEl.textContent = msg;
                        statusEl.className = "bg-amber-500/80 backdrop-blur-md text-white text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest border border-white/10 shadow-md";
                        window.falarBiometria(msg);
                        isLivenessOk = false; // Reset liveness if framing is lost
                    } else if (isLivenessOk) {
                        statusEl.textContent = "Face Validada ✓"; 
                        statusEl.className = "bg-emerald-500/80 backdrop-blur-md text-white text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest border border-white/10 shadow-md";
                        window.falarBiometria("Face validada");
                    } else {
                        const remaining = Math.max(0, Math.ceil((LIVENESS_AUTO_MS - (Date.now() - faceDetectedSince)) / 1000));
                        statusEl.textContent = remaining > 0 ? `Pisque ou aguarde ${remaining}s` : "Validando…";
                        statusEl.className = "bg-brand-500/80 backdrop-blur-md text-white text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest border border-white/10 shadow-md";
                    }
                }
                
                if (score > 0.65 && !isProcessingFacial && isLivenessOk && isWellFramed) { window.tirarFotoFacial(detection.descriptor); return; }
            } else {
                faceDetectedSince = null;
                if (statusEl) { statusEl.textContent = "Posicione seu Rosto"; statusEl.className = "bg-black/65 backdrop-blur-md text-white text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest border border-white/10 shadow-md"; }
                _setConfidenceArc(0, false);
                const scoreBar = document.getElementById('faceScoreBar');
                if (scoreBar) scoreBar.style.opacity = '0';
                prevEarBelowThreshold = false;
            }
        } catch(e) { console.error("Loop detecção:", e); }
        if (detectionLoopActive) setTimeout(startFaceDetectionLoop, 66);
    }

    window.pararStreamVideo = function () {
        if (videoStream) { videoStream.getTracks().forEach(track => track.stop()); videoStream = null; }
        detectionLoopActive = false;
    };

    // Utilitário de Sintese de Voz
    let ultimaFala = "";
    let timeoutFala = null;
    window.falarBiometria = function (texto) {
        if (!texto || texto === ultimaFala) return;
        ultimaFala = texto;
        if (timeoutFala) clearTimeout(timeoutFala);
        timeoutFala = setTimeout(() => { ultimaFala = ""; }, 2500);

        const synth = window.speechSynthesis;
        if (synth.speaking) synth.cancel();
        const utter = new SpeechSynthesisUtterance(texto);
        utter.lang = 'pt-BR';
        utter.rate = 1.1;
        const ptVoice = synth.getVoices().find(v => v.lang.includes('pt-BR'));
        if (ptVoice) utter.voice = ptVoice;
        synth.speak(utter);
    };

    window.iniciarStreamVideo = async function () {
        const video = document.getElementById('videoFeed');
        const camLoading = document.getElementById('camLoading');
        const statusEl = document.querySelector('#faceStatus span');
        if (camLoading) camLoading.classList.remove('hidden');
        if (video) video.classList.add('hidden');
        if (statusEl) statusEl.innerText = !modelsLoaded ? "Carregando IA..." : "Iniciando Cam...";
        try {
            window.pararStreamVideo();
            videoStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 }, frameRate: { ideal: 15 } } });
            video.srcObject = videoStream;
            await video.play();
            if (camLoading) camLoading.classList.add('hidden');
            if (video) video.classList.remove('hidden');
            if (statusEl) statusEl.innerText = !modelsLoaded ? "Carregando IA..." : "Aguardando Face...";
            if (document.getElementById('bio_modo').value === 'facial') {
                detectionLoopActive = true;
                isProcessingFacial = false;
                blinkCount = 0; prevEarBelowThreshold = false; isLivenessOk = false; faceDetectedSince = null; earBaseline = null; earCalibBuf = [];
                startFaceDetectionLoop();
            }
        } catch (err) { console.error(err); if (camLoading) camLoading.innerHTML = `<span class="text-red-500 text-xs">Erro: ${err.message}</span>`; if(statusEl) statusEl.innerText = "Falha na Câmera"; }
    };

    window.tirarFotoFacial = async function (providedDescriptor = null) {
        if (!videoStream || isProcessingFacial || !isLivenessOk) { if(!isLivenessOk) Swal.fire('Atenção', 'Pisque os olhos em frente à câmera.', 'warning'); return; }
        const video = document.getElementById('videoFeed');
        const canvas = document.getElementById('videoCanvas');
        const btn = document.getElementById('btnCapturarFacial');
        const statusEl = document.querySelector('#faceStatus span');
        isProcessingFacial = true;
        canvas.width = video.videoWidth; canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        video.classList.add('hidden'); canvas.classList.remove('hidden');
        const flash = document.getElementById('captureFlash');
        if (flash) { flash.style.opacity = '0.75'; setTimeout(() => flash.style.opacity = '0', 180); }
        if (btn) { btn.innerHTML = 'Analisando…'; btn.disabled = true; }
        if (statusEl) statusEl.textContent = 'Analisando Face…';
        const [detection, loc] = await Promise.all([
            providedDescriptor ? Promise.resolve({ descriptor: providedDescriptor }) : 
                                faceapi.detectSingleFace(canvas, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.4 })).withFaceLandmarks().withFaceDescriptor(),
            obterLocalizacao()
        ]);
        if (!detection) {
            isProcessingFacial = false;
            if (statusEl) statusEl.textContent = 'Face não encontrada';
            if (btn) { btn.innerHTML = 'Capturar Facial'; btn.disabled = false; }
            _resetCameraUI();
            if (detectionLoopActive) startFaceDetectionLoop();
            return;
        }
        if (statusEl) statusEl.textContent = 'Validando…';
        if (btn) btn.innerHTML = 'Validando…';
        const base64Image = canvas.toDataURL('image/jpeg', 0.75);
        let resolvedMatricula = document.getElementById('bio_matricula').value;
        if (!resolvedMatricula && _faceMatcher) {
            const clientMatch = _faceMatcher.findBestMatch(detection.descriptor);
            if (clientMatch.distance < 0.5) resolvedMatricula = clientMatch.label.split('|')[1] || '';
        }
        fetch('../api/ponto.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'ponto_facial', matricula: resolvedMatricula, image_data: base64Image, facial_descriptor: Array.from(detection.descriptor), lat: loc?.lat, lng: loc?.lng })
        }).then(res => res.json()).then(async data => {
            if (data.success) {
                detectionLoopActive = false;
                Swal.fire({ 
                    title: 'Ponto Registrado!', 
                    html: `<b>${data.funcionario}</b><br>Facial · <b>${data.data_ponto}</b> às <b>${data.hora}</b>`, 
                    icon: 'success', 
                    confirmButtonText: 'Fechar', 
                    confirmButtonColor: '#0ea5e9' 
                });
                window.fecharModalBiometria();
            } else if (data.ficha_incompleta) {
                await Swal.fire({ title: 'Atualização Cadastral', text: 'Sua ficha está incompleta. Clique em atualizar.', icon: 'info', confirmButtonText: 'Atualizar', confirmButtonColor: '#0ea5e9' });
                if (await window.abrirModalFichaIncompleta(data)) Swal.fire('Dados Atualizados', 'Capture novamente para registrar.', 'success');
                else window.fecharModalBiometria();
                isProcessingFacial = false; _resetCameraUI(); if (detectionLoopActive) startFaceDetectionLoop();
            } else { 
                Swal.fire({ 
                    title: 'Não Permitido', 
                    text: data.message, 
                    icon: 'error', 
                    confirmButtonText: 'Fechar',
                    timer: 4000,
                    timerProgressBar: true
                }).then(() => { 
                    isProcessingFacial = false; 
                    if(btn) btn.innerHTML = 'Capturar Facial'; 
                    btn.disabled = false; 
                    _resetCameraUI(); 
                    if (detectionLoopActive) startFaceDetectionLoop(); 
                }); 
            }
        }).catch(() => { Swal.fire('Erro', 'Falha na validação facial.', 'error'); isProcessingFacial = false; _resetCameraUI(); if(detectionLoopActive) startFaceDetectionLoop(); }).finally(() => { if(btn) { btn.innerHTML = 'Capturar Facial'; btn.disabled = false; } });
    };

    // --- Biometria Digital (Sensor) & WebAuthn ---
    window.iniciarCapturaDigitalClock = async function () {
        if (isMobile && window.PublicKeyCredential && temWebAuthn) { window.baterPontoWebAuthn(); return; }
        const btn = document.getElementById('btnAtivarSensor');
        const ring = document.getElementById('bioScanRing');
        btn.disabled = true; btn.innerHTML = "Escaneie o Dedo...";
        if (ring) { ring.classList.remove('opacity-0', 'scale-90'); ring.classList.add('opacity-100', 'scale-100', 'animate-pulse'); }
        if (window.dpSocketClock) window.dpSocketClock.close();
        const endpoints = ["wss://localhost:52181/API", "ws://localhost:15270/API", "ws://127.0.0.1:15270/API"];
        let ok = false;
        for (const url of endpoints) {
            try {
                const connected = await new Promise((resolve, reject) => {
                    const s = new WebSocket(url);
                    const t = setTimeout(() => { s.close(); reject(); }, 1500);
                    s.onopen = () => { clearTimeout(t); window.dpSocketClock = s; s.send(JSON.stringify({ Type: "Capture", Method: "Start" })); resolve(true); };
                    s.onmessage = async (e) => { const d = JSON.parse(e.data); if (d?.Event === "SamplesReady" && d.Samples.length) { s.send(JSON.stringify({ Type: "Capture", Method: "Stop" })); const hash = d.Samples[d.Samples.length-1].Data; processarPontoDigital(hash); } };
                    s.onerror = () => reject();
                });
                if (connected) { ok = true; break; }
            } catch(e) {}
        }
        if (!ok) { btn.disabled = false; btn.innerHTML = "Sensor não encontrado"; if(ring) ring.classList.add('opacity-0'); Swal.fire('Erro Sensor', 'Não foi possível conectar ao leitor USB.', 'warning'); }
    };

    async function processarPontoDigital(hash) {
        const btn = document.getElementById('btnAtivarSensor');
        btn.innerHTML = "Validando Digital...";
        try {
            const loc = await obterLocalizacao();
            const res = await fetch('../api/ponto.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ biometria: hash, lat: loc?.lat, lng: loc?.lng })
            });
            const data = await res.json();
            if (data.success) { 
                Swal.fire({ 
                    title: 'Marcado!', 
                    html: `<b>${data.funcionario}</b><br>Ponto via Digital em <b>${data.data_ponto}</b> às <b>${data.hora}</b>`, 
                    icon: 'success',
                    showConfirmButton: true,
                    confirmButtonText: 'Fechar',
                    confirmButtonColor: '#0ea5e9'
                }); 
                window.fecharModalBiometria(); 
            }
            else if (data.ficha_incompleta) { await Swal.fire({ title: 'Atualização Cadastral', text: 'Sua ficha está incompleta.', icon: 'info', confirmButtonText: 'Atualizar' }); if(await window.abrirModalFichaIncompleta(data)) Swal.fire('Dados Atualizados', 'Use o sensor novamente.', 'success'); else window.fecharModalBiometria(); resetarBotaoDigital(); }
            else { Swal.fire('Digital Desconhecida', data.message, 'error'); resetarBotaoDigital(); }
        } catch(e) { Swal.fire('Erro', 'Falha na comunicação.', 'error'); resetarBotaoDigital(); }
    }

    function resetarBotaoDigital() {
        const btn = document.getElementById('btnAtivarSensor');
        const ring = document.getElementById('bioScanRing');
        btn.disabled = false; btn.innerHTML = (isMobile && window.PublicKeyCredential && temWebAuthn) ? "Usar Biometria do Celular" : "Tentar Novamente";
        if (ring) { ring.classList.remove('animate-pulse'); ring.classList.add('opacity-0'); }
    }

    window.baterPontoWebAuthn = async function (silent = false) {
        if (!window.PublicKeyCredential) return;
        try {
            const resOpt = await fetch('../api/webauthn.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'get_assertion_options' }) });
            const opt = await resOpt.json();
            if (!opt.success) throw new Error(opt.message);
            const challenge = Uint8Array.from(atob(btoa(opt.challenge)), c => c.charCodeAt(0));
            const assertionOptions = { publicKey: { challenge, timeout: 60000, userVerification: "required" } };
            const assertion = await navigator.credentials.get(assertionOptions);
            const loc = await obterLocalizacao();
            const verifyRes = await fetch('../api/ponto.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'ponto_webauthn', credentialId: btoa(String.fromCharCode(...new Uint8Array(assertion.rawId))), lat: loc?.lat, lng: loc?.lng }) });
            const data = await verifyRes.json();
            if (data.success) { 
                Swal.fire({ 
                    title: 'Marcado!', 
                    html: `<b>${data.funcionario}</b><br>Ponto via Mobile em <b>${data.data_ponto}</b> às <b>${data.hora}</b>`, 
                    icon: 'success' 
                }); 
                window.fecharModalBiometria(); 
                return true; 
            }
            else if (data.ficha_incompleta) { await Swal.fire({ title: 'Atualização Cadastral', text: 'Sua ficha está incompleta.', icon: 'info', confirmButtonText: 'Atualizar' }); if(await window.abrirModalFichaIncompleta(data)) Swal.fire('Dados Atualizados', 'Use a biometria novamente.', 'success'); else window.fecharModalBiometria(); return false; }
            else { if (!silent) Swal.fire({ title: 'Não Permitido', text: data.message, icon: 'error' }); return false; }
        } catch(err) { if (!silent && err.name !== 'NotAllowedError') Swal.fire('Erro', 'Falha na biometria do celular: ' + err.message, 'error'); return false; }
    };

    // --- Modal Biometria Unificado ---
    window.abrirModalBiometria = async function () {
        const inputMatricula = document.getElementById('matricula');
        const matricula = inputMatricula.value.trim();
        document.getElementById('bio_matricula').value = matricula;
        if (matricula && currentFlow === 'identificacao') { if (!await window.obterDadosFuncionario(matricula)) return; }
        if (isMobile && window.PublicKeyCredential && temWebAuthn) { if (await window.baterPontoWebAuthn(true)) return; }
        const podeFacial = metodosPermitidos.length === 0 || metodosPermitidos.includes('facial');
        const podeDigital = metodosPermitidos.length === 0 || metodosPermitidos.includes('biometria');
        document.getElementById('tabFacial').style.display = podeFacial ? 'flex' : 'none';
        document.getElementById('tabDigital').style.display = podeDigital ? 'flex' : 'none';
        document.getElementById('modalBiometria').classList.remove('hidden');
        if(podeFacial) window.mudarModoBiometria('facial');
        else if(podeDigital) window.mudarModoBiometria('digital');
        const tabsContainer = document.getElementById('tabFacial')?.parentElement;
        if(tabsContainer) tabsContainer.style.display = (podeFacial && podeDigital) ? 'flex' : 'none';
        loadFaceModels();
        obterLocalizacao();
    };

    window.fecharModalBiometria = function () {
        document.getElementById('modalBiometria').classList.add('hidden');
        detectionLoopActive = false; isProcessingFacial = false; blinkCount = 0; prevEarBelowThreshold = false; isLivenessOk = false; faceDetectedSince = null; earBaseline = null; earCalibBuf = [];
        _setConfidenceArc(0, false);
        const scoreBar = document.getElementById('faceScoreBar'); if(scoreBar) scoreBar.style.opacity = '0';
        window.pararStreamVideo();
        if (window.dpSocketClock) window.dpSocketClock.close();
    };

    window.mudarModoBiometria = function (modo) {
        document.getElementById('bio_modo').value = modo;
        const tFacial = document.getElementById('tabFacial');
        const tDigital = document.getElementById('tabDigital');
        const aFacial = document.getElementById('areaFacial');
        const aDigital = document.getElementById('areaDigital');
        if (modo === 'facial') {
            tFacial.className = "flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-bold rounded-xl transition-all bg-white shadow-sm text-brand-600";
            tDigital.className = "flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-bold rounded-xl transition-all text-slate-400 hover:text-slate-600";
            aFacial.classList.remove('hidden'); aDigital.classList.add('hidden');
            window.iniciarStreamVideo();
        } else {
            detectionLoopActive = false;
            tDigital.className = "flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-bold rounded-xl transition-all bg-white shadow-sm text-emerald-600";
            tFacial.className = "flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-bold rounded-xl transition-all text-slate-400 hover:text-slate-600";
            aDigital.classList.remove('hidden'); aFacial.classList.add('hidden');
            window.pararStreamVideo();
            const btnText = document.getElementById('btnAtivarSensorText');
            if(btnText) btnText.innerText = isMobile ? (temWebAuthn ? 'Usar Biometria do Celular' : 'Vincular Aparelho no RH') : 'Iniciar Sensor';
            if (isMobile && window.PublicKeyCredential && temWebAuthn) setTimeout(() => window.baterPontoWebAuthn(false), 300);
        }
        blinkCount = 0; isLivenessOk = false; faceDetectedSince = null; earBaseline = null; earCalibBuf = [];
    };

    // --- Acesso Administrativo & Meu Acesso ---
    function pedirAcessoAdmin(e) { if(e) e.preventDefault(); window.location.href = 'admin/index.php'; }

    async function abrirMeuAcesso(e) {
        if(e) e.preventDefault();
        const inputMatricula = document.getElementById('matricula');
        const matriculaPrevia = inputMatricula.value.trim();
        const today = new Date();
        const defaultStart = `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2,'0')}-01`;
        const defaultEnd = `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2,'0')}-${String(new Date(today.getFullYear(), today.getMonth()+1, 0).getDate()).padStart(2,'0')}`;
        const { value: loginData } = await Swal.fire({
            title: 'Meu Cartão de Ponto', text: 'Identifique-se e escolha o período.', html: `<div class="space-y-4 py-2"><div class="text-left"><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Matrícula</label><input id="sw-m" class="swal2-input !w-full !m-0 mt-1 !text-center !rounded-xl" placeholder="99.999-9" value="${matriculaPrevia}"></div><div class="text-left"><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sua Senha</label><input id="sw-s" type="password" class="swal2-input !w-full !m-0 mt-1 !text-center !rounded-xl" placeholder="******"></div><div class="flex gap-3"><div class="flex-1 text-left"><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Data Inicial</label><input id="sw-dt-ini" type="date" value="${defaultStart}" class="swal2-input !w-full !m-0 mt-1 !text-center"></div><div class="flex-1 text-left"><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Data Final</label><input id="sw-dt-fim" type="date" value="${defaultEnd}" class="swal2-input !w-full !m-0 mt-1 !text-center"></div></div></div>`,
            showCancelButton: true, confirmButtonText: 'Ver Cartão', cancelButtonText: 'Voltar', confirmButtonColor: '#0ea5e9',
            preConfirm: () => { const m = document.getElementById('sw-m').value.trim(); const s = document.getElementById('sw-s').value; const dtIni = document.getElementById('sw-dt-ini').value; const dtFim = document.getElementById('sw-dt-fim').value; if(!m || !s) { Swal.showValidationMessage('Preencha matrícula e senha'); return false; } if(!dtIni || !dtFim) { Swal.showValidationMessage('Selecione o período completo'); return false; } return { m, s, dtIni, dtFim }; }
        });
        if (!loginData) return;
        Swal.fire({ title: 'Carregando...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });
        try {
            const res = await fetch('../api/ponto.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'get_meu_ponto', matricula: loginData.m, senha: loginData.s, start_date: loginData.dtIni, end_date: loginData.dtFim }) });
            const json = await res.json();
            if (!json.success) { Swal.fire('Erro', json.message, 'error'); return; }
            window._ultimaMatricula = loginData.m; window._ultimaSenha = loginData.s;
            sessionStorage.setItem('extrato_pendente_dados', JSON.stringify(json.data)); sessionStorage.setItem('extrato_pendente_nome', json.funcionario); sessionStorage.setItem('extrato_pendente_mat', loginData.m); sessionStorage.setItem('extrato_pendente_senha', loginData.s); sessionStorage.setItem('extrato_pendente_ini', loginData.dtIni); sessionStorage.setItem('extrato_pendente_fim', loginData.dtFim);
            renderizarCartaoIndividual(json.data, json.funcionario, loginData.dtIni, loginData.dtFim);
        } catch(e) { Swal.fire('Erro', 'Falha na comunicação.', 'error'); }
    }

    function renderizarCartaoIndividual(dados, nome, dtIni, dtFim) {
        const esc = (s) => (s || '').toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        const pontoHtml = (hora, atrasou, faltaAuto, justIndividual, statusCrh, tipoJustificativa, justificativaGlobal, horarioProgramado, emFerias, motivo, r) => {
            if (emFerias) return `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-500 text-white border border-amber-600 uppercase" title="${esc(motivo)}">📅 ${esc(motivo)}</span>`;
            const isFaltaAuto = (!hora || hora === 'FALTA' || faltaAuto);
            const isIndeferido = statusCrh === 'indeferido';
            const hasAnyJust = !!((justIndividual && justIndividual !== 'null') || (tipoJustificativa && tipoJustificativa !== 'null' && (atrasou || isFaltaAuto)));
            if (!horarioProgramado && !hora) return '<span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black text-slate-300">---</span>';
            const time = isFaltaAuto ? 'Falta' : String(hora).substring(0,5);
            if (isIndeferido && hasAnyJust) return `<span class="inline-flex px-2 py-0.5 rounded text-[9px] font-black bg-red-50 text-red-700 border border-red-300 cursor-pointer" onclick="Swal.fire({title:'Recusada', text:'${esc(justificativaGlobal)}', icon:'info'})">⛔ ${time}</span>`;
            if (hasAnyJust) return `<span class="inline-flex px-2 py-0.5 rounded text-[9px] font-black bg-blue-50 text-blue-700 border border-blue-200">✔ Justificado</span>`;
            if (atrasou && !isFaltaAuto) return `<div class="flex flex-col items-center"><span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black bg-amber-50 text-amber-700">${time}</span><span class="text-[8px] font-black text-amber-600">Atraso</span></div>`;
            if (isFaltaAuto) return `<span class="inline-flex px-2 py-0.5 rounded text-[9px] font-black bg-red-50 text-red-700 border border-red-200">⛔ ${time}</span>`;
            return `<span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black bg-slate-100 text-slate-700">${time}</span>`;
        };
        const rows = dados.map(r => {
            const dataFmt = new Date(r.data + 'T00:00:00').toLocaleDateString('pt-BR', { day:'2-digit', month:'2-digit', weekday:'short' }).replace('.','');
            const p1 = pontoHtml(r.primeiro_ponto, r.atrasou_primeiro_ponto, r.falta_turno1_entrada, r.just_ent1, r.status_crh, r.tipo_justificativa, r.justificativa, r.primeiro_horario, r.em_ferias, r.motivo_afastamento, r);
            const p2 = pontoHtml(r.segundo_ponto,  r.atrasou_segundo_ponto,  r.falta_turno1_saida,   r.just_sai1, r.status_crh, r.tipo_justificativa, r.justificativa, r.segundo_horario, r.em_ferias, r.motivo_afastamento, r);
            const p3 = pontoHtml(r.terceiro_ponto, r.atrasou_terceiro_ponto, r.falta_turno2_entrada, r.just_ent2, r.status_crh, r.tipo_justificativa, r.justificativa, r.terceiro_horario, r.em_ferias, r.motivo_afastamento, r);
            const p4 = pontoHtml(r.quarto_ponto,   r.atrasou_quarto_ponto,   r.falta_turno2_saida,   r.just_sai2, r.status_crh, r.tipo_justificativa, r.justificativa, r.quarto_horario, r.em_ferias, r.motivo_afastamento, r);
            let avisoRow = '';
            if(r.aviso && r.aviso !== 'null') avisoRow = `<tr class="bg-red-50/20"><td colspan="7" class="py-1 px-3 text-[9px] font-bold text-red-600 italic">Recado: ${esc(r.aviso)}</td></tr>`;
            return `<tr class="border-b border-slate-50 hover:bg-slate-50/50"><td class="py-3 px-2 text-[10px] font-black text-slate-500 uppercase">${dataFmt}</td><td class="py-3 px-1 text-center">${p1}</td><td class="py-3 px-1 text-center">${p2}</td><td class="py-3 px-1 text-center">${p3}</td><td class="py-3 px-1 text-center">${p4}</td><td class="py-3 px-1 text-center"><span class="text-[9px] font-black text-emerald-600 uppercase">✓ OK</span></td><td class="py-3 px-2 text-[9px] text-slate-500 italic truncate max-w-[120px]" title="${esc(r.comunicado)}">${esc(r.comunicado)}</td></tr>${avisoRow}`;
        }).join('');
        Swal.fire({
            title: `<div class="text-left"><p class="text-[10px] font-black text-brand-500 uppercase">Extrato Individual</p><p class="text-lg font-black text-slate-800">${esc(nome)}</p></div>`,
            width: '850px',
            html: `<div id="extrato-pdf-content"><div class="mt-2 border rounded-2xl overflow-hidden"><table class="w-full"><thead><tr class="bg-slate-50"><th class="py-3 px-3 text-[9px] font-black text-slate-400 w-24">Data</th><th class="py-3 px-1 text-[9px] font-black text-slate-400 text-center">E1</th><th class="py-3 px-1 text-[9px] font-black text-slate-400 text-center">S1</th><th class="py-3 px-1 text-[9px] font-black text-slate-400 text-center">E2</th><th class="py-3 px-1 text-[9px] font-black text-slate-400 text-center">S2</th><th class="py-3 px-1 text-[9px] font-black text-slate-400 text-center">Status</th><th class="py-3 px-3 text-[9px] font-black text-slate-400 text-left">Aviso</th></tr></thead><tbody>${rows || '<tr><td colspan="7" class="py-12 text-center text-slate-400">Nenhum registro encontrado.</td></tr>'}</tbody></table></div></div><div class="mt-4 flex gap-2"><button onclick="comunicarAtrasoFalta()" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-amber-600 text-white rounded-xl text-xs font-black uppercase hover:bg-amber-500">Comunicar Atraso/Falta</button><button onclick="trocarSenhaFuncionario()" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-slate-800 text-white rounded-xl text-xs font-black uppercase hover:bg-slate-700">Alterar Senha</button></div>`,
            showDenyButton: true, denyButtonText: 'Salvar PDF', denyButtonColor: '#0c4a6e', confirmButtonText: 'Fechar Extrato', confirmButtonColor: '#0ea5e9', allowOutsideClick: false
        }).then((result) => { if(result.isDenied) exportarExtratoPDF(nome, dtIni, dtFim); else if(result.isConfirmed) sessionStorage.removeItem('extrato_pendente_dados'); });
    }

    function exportarExtratoPDF(nome, dtIni, dtFim) {
        const element = document.getElementById('extrato-pdf-content');
        if(!element) return;
        const opt = { margin: 10, filename: `Extrato_${nome.replace(/\s+/g, '_')}.pdf`, image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2 }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' } };
        const header = document.createElement('div');
        header.innerHTML = `<div style="text-align:center; margin-bottom:20px;"><h1 style="color:#0284c7;">Extrato Individual de Ponto</h1><h2>${nome}</h2><h3>Período: ${dtIni.split('-').reverse().join('/')} até ${dtFim.split('-').reverse().join('/')}</h3><hr></div>`;
        const clone = element.cloneNode(true);
        const container = document.createElement('div');
        container.appendChild(header); container.appendChild(clone);
        html2pdf().set(opt).from(container).save();
    }

    async function comunicarAtrasoFalta() {
        if (!window._ultimaMatricula || !window._ultimaSenha) { Swal.fire('Erro', 'Sessão expirada. Use "Meu Acesso" novamente.', 'error'); return; }
        const { value: formValues } = await Swal.fire({
            title: 'Comunicar Atraso ou Falta',
            html: `<div class="text-left"><div class="bg-amber-50 p-3 rounded-lg text-[11px] text-amber-800"><strong>Importante:</strong> Aviso antecipado ao gestor/RH.</div><div class="mt-4"><label class="text-[10px] font-black text-slate-500">Mensagem</label><textarea id="sw-mensagem-comunicado" class="swal2-textarea w-full mt-1" rows="3" placeholder="Ex: Tive um imprevisto..."></textarea></div></div>`,
            showCancelButton: true, confirmButtonText: 'Enviar', confirmButtonColor: '#d97706',
            preConfirm: () => { const msg = document.getElementById('sw-mensagem-comunicado').value; if(!msg || msg.length<5) { Swal.showValidationMessage('Descreva o motivo.'); return false; } return { mensagem: msg, data: new Date().toISOString().split('T')[0] }; }
        });
        if(!formValues) return;
        Swal.fire({ title: 'Enviando...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });
        try {
            const res = await fetch('../api/ponto.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'salvar_comunicado', matricula: window._ultimaMatricula, senha: window._ultimaSenha, ...formValues }) });
            const data = await res.json();
            if(data.success) Swal.fire('Sucesso!', data.message, 'success');
            else Swal.fire('Erro', data.message, 'error');
        } catch(e) { Swal.fire('Erro', 'Falha na comunicação.', 'error'); }
    }

    async function trocarSenhaFuncionario() {
        if (!window._ultimaMatricula || !window._ultimaSenha) { Swal.fire('Erro', 'Sessão expirada.', 'error'); return; }
        const { value: formValues } = await Swal.fire({
            title: 'Alterar Senha de Acesso',
            html: `<div class="space-y-4"><div><label class="text-[10px] font-black text-slate-500">Senha Atual</label><input type="password" id="sw-senha-antiga" class="swal2-input w-full"></div><div><label class="text-[10px] font-black text-slate-500">Nova Senha</label><input type="password" id="sw-senha-nova" class="swal2-input w-full"></div><div><label class="text-[10px] font-black text-slate-500">Confirmar Senha</label><input type="password" id="sw-senha-confirma" class="swal2-input w-full"></div></div>`,
            showCancelButton: true, confirmButtonText: 'Atualizar Senha', confirmButtonColor: '#0ea5e9',
            preConfirm: () => { const antiga = document.getElementById('sw-senha-antiga').value; const nova = document.getElementById('sw-senha-nova').value; const confirma = document.getElementById('sw-senha-confirma').value; if(!antiga) { Swal.showValidationMessage('Informe a senha atual'); return false; } if(nova.length<6) { Swal.showValidationMessage('Mínimo 6 caracteres'); return false; } if(nova !== confirma) { Swal.showValidationMessage('Senhas não coincidem'); return false; } return { antiga, nova }; }
        });
        if(!formValues) return;
        Swal.fire({ title: 'Processando...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });
        try {
            const res = await fetch('../api/ponto.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'change_password', matricula: window._ultimaMatricula, senha_antiga: formValues.antiga, nova_senha: formValues.nova }) });
            const data = await res.json();
            if(data.success) { window._ultimaSenha = formValues.nova; sessionStorage.setItem('extrato_pendente_senha', formValues.nova); Swal.fire('Sucesso!', 'Senha alterada.', 'success'); }
            else Swal.fire('Erro', data.message, 'error');
        } catch(e) { Swal.fire('Erro', 'Falha na comunicação.', 'error'); }
    }

    window.onload = () => {
        document.getElementById('matricula').value = '';
        document.getElementById('senha').value = '';
        _carregarFuncionarios();
        loadFaceModels();
        const pendenteDados = sessionStorage.getItem('extrato_pendente_dados');
        const pendenteNome = sessionStorage.getItem('extrato_pendente_nome');
        if(pendenteDados && pendenteNome) {
            window._ultimaMatricula = sessionStorage.getItem('extrato_pendente_mat');
            window._ultimaSenha = sessionStorage.getItem('extrato_pendente_senha');
            renderizarCartaoIndividual(JSON.parse(pendenteDados), pendenteNome);
        }
    };
</script>

<?php include 'layout/footer.php'; ?>