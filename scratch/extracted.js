
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
    let listaHorarios = [];
    async function _carregarHorarios() {
        try {
            const res = await fetch('../api/horarios.php');
            const json = await res.json();
            if (json.success) listaHorarios = json.data;
        } catch (e) { console.error("Erro carregando horarios", e); }
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
                    Swal.fire('Atenção!', 'Nenhum método de acesso configurado.', 'warning'); resetarFluxo();
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
            

            const { value: formValues } = await Swal.fire({
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
                                        <input id="upd_termo_orgao" class="w-full px-4 py-2 bg-white border border-slate-100 rounded-xl text-xs font-bold outline-none" value="${(() => { try { const t = typeof func.termo_dados === 'string' ? JSON.parse(func.termo_dados) : func.termo_dados; return t?.orgao || 'FUNAD – CENTRO INTEGRADO DE APOIO À PESSOA COM DEFICIÊNCIA'; } catch(e) { return 'FUNAD – CENTRO INTEGRADO DE APOIO À PESSOA COM DEFICIÊNCIA'; } })()}">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="group">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">CNPJ</label>
                                            <input id="upd_termo_cnpj" class="w-full px-4 py-2 bg-white border border-slate-100 rounded-xl text-xs font-bold outline-none" value="${(() => { try { const t = typeof func.termo_dados === 'string' ? JSON.parse(func.termo_dados) : func.termo_dados; return t?.cnpj || '24.507.865/0001-07'; } catch(e) { return '24.507.865/0001-07'; } })()}">
                                        </div>
                                        <div class="group">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Contato DPO</label>
                                            <input id="upd_termo_dpo_contato" class="w-full px-4 py-2 bg-white border border-slate-100 rounded-xl text-[10px] font-bold outline-none" value="${(() => { try { const t = typeof func.termo_dados === 'string' ? JSON.parse(func.termo_dados) : func.termo_dados; return t?.dpo_contato || 'lgpd@funad.pb.gov.br'; } catch(e) { return 'lgpd@funad.pb.gov.br'; } })()}">
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

                        </div>\n                `,
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
                        filhos_menores: Array.from({length: parseInt(document.getElementById('upd_filhos_menores').value) || 0}, () => ({nome: '', nasc: '', cpf: ''})),
                        dependentes_ir: Array.from({length: parseInt(document.getElementById('upd_dependentes_ir').value) || 0}, () => ({nome: '', nasc: '', cpf: ''})),
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
                            headers: { 'Content-Type': 'application/json' },
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
            Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'CEP inválido', showConfirmButton: false, timer: 3000 });
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
                Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'CEP não encontrado', showConfirmButton: false, timer: 3000 });
            }
        } catch (e) {
            console.error("Erro ao buscar CEP:", e);
            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Falha na conexão', showConfirmButton: false, timer: 3000 });
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
                if (data.ficha_incompleta) {
                    Swal.fire({
                        title: 'Ficha Incompleta',
                        text: data.message,
                        icon: 'warning',
                        confirmButtonText: 'Atualizar Agora',
                        confirmButtonColor: '#0ea5e9'
                    }).then(() => {
                        window.abrirModalFichaIncompleta(data);
                    });
                } else {
                    Swal.fire({ title: 'Aviso', text: data.message, icon: 'error', confirmButtonColor: '#0ea5e9' });
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
                    action: 'validar_facial',
                    matricula: document.getElementById('bio_matricula').value,
                    image_data: base64Image,
                    facial_descriptor: Array.from(detection.descriptor)
                })
            }).then(res => res.json()).then(async data => {
                if (data.success) {
                    // Face reconhecida! Agora mostra o termo de consentimento
                    exibirTermoUsoImagem(data, base64Image, Array.from(detection.descriptor), loc);
                } else {
                    Swal.fire({
                        title: 'Não Reconhecido',
                        text: data.message,
                        icon: 'error',
                        confirmButtonColor: '#0ea5e9'
                    }).then(() => {
                        isProcessingFacial = false;
                        if (btn) {
                            btn.innerHTML = "Capturar Facial";
                            btn.disabled = false;
                        }
                        video.classList.remove('hidden');
                        canvas.classList.add('hidden');
                        if (detectionLoopActive) startFaceDetectionLoop();
                    });
                }
            }).catch((err) => {
                console.error(err);
                Swal.fire('Aviso', 'Falha na validação facial.', 'error');
                isProcessingFacial = false;
                if (detectionLoopActive) startFaceDetectionLoop();
            }).finally(() => {
                if (btn) {
                    btn.innerHTML = "Capturar Facial";
                    btn.disabled = false;
                }
            });
        };
    };

    /**
     * Exibe o modal informativo do Termo de Consentimento de Imagem
     */
    window.exibirTermoUsoImagem = async function(funcionario, base64Image, descriptor, loc) {
        const { value: accepted } = await Swal.fire({
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

                    <div class="bg-amber-50 p-3 rounded-xl border border-amber-100 border-dashed text-amber-800 italic mt-4">
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
            }
        });

        if (accepted) {
            executarRegistroFinalFacial(funcionario.matricula, base64Image, descriptor, loc);
        } else {
            Swal.fire({
                title: 'Registro Cancelado',
                text: 'O ponto não foi registrado. Por favor, procure o CRH (Recursos Humanos) para regularizar seu aceite de uso de imagem.',
                icon: 'warning',
                confirmButtonColor: '#f59e0b'
            });
            isProcessingFacial = false;
            window.fecharModalBiometria(); // Fecha tudo após recusa
        }
    }

    /**
     * Executa a batida final após o aceite do termo
     */
    async function executarRegistroFinalFacial(matricula, base64Image, descriptor, loc) {
        Swal.fire({
            title: 'Registrando Ponto...',
            didOpen: () => { Swal.showLoading(); },
            allowOutsideClick: false
        });

        try {
            const res = await fetch('../api/ponto.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
            
            if (data.success) {
                detectionLoopActive = false;
                Swal.fire({ 
                    title: 'Marcado!', 
                    html: `<b>${data.funcionario}</b><br>Ponto via Facial às ${data.hora}`, 
                    icon: 'success', 
                    showConfirmButton: true,
                    confirmButtonText: 'Concluir',
                    confirmButtonColor: '#0ea5e9'
                });
                window.fecharModalBiometria();
            } else {
                if (data.ficha_incompleta) {
                    Swal.fire({
                        title: 'Ficha Incompleta',
                        text: data.message,
                        icon: 'warning',
                        confirmButtonText: 'Atualizar Agora',
                        confirmButtonColor: '#0ea5e9'
                    }).then(() => {
                        window.fecharModalBiometria();
                        window.abrirModalFichaIncompleta(data);
                    });
                } else {
                    Swal.fire('Aviso', data.message, 'error').then(() => window.fecharModalBiometria());
                }
            }
        } catch (e) {
            Swal.fire('Aviso', 'Falha ao registrar ponto. Tente novamente.', 'error');
        } finally {
            isProcessingFacial = false;
        }
    }

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
                Swal.fire('Digital Desconhecida', data.message, 'error').then(() => window.fecharModalBiometria());
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
        const inputMatricula = document.getElementById('matricula');
        const matriculaPrevia = inputMatricula.value.trim();

        const today = new Date();
        const y = today.getFullYear();
        const m = String(today.getMonth() + 1).padStart(2, '0');
        const lastDay = new Date(y, today.getMonth() + 1, 0).getDate();
        const defaultStart = `${y}-${m}-01`;
        const defaultEnd = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;

        const { value: loginData } = await Swal.fire({
            title: 'Meu Cartão de Ponto',
            text: 'Identifique-se e escolha o período do extrato.',
            html: `
                <div class="space-y-4 py-2">
                    <div class="text-left">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Matrícula</label>
                        <input id="sw-m" class="swal2-input !w-full !m-0 mt-1 !text-center !rounded-xl !border-slate-200 !text-sm font-bold tracking-widest" placeholder="99.999-9" value="${matriculaPrevia}">
                    </div>
                    <div class="text-left">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sua Senha</label>
                        <input id="sw-s" type="password" class="swal2-input !w-full !m-0 mt-1 !text-center !rounded-xl !border-slate-200 !text-sm font-bold tracking-widest" placeholder="******">
                    </div>
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
                if (!m || !s) { Swal.showValidationMessage('Preencha matrícula e senha'); return false; }
                if (!dtIni || !dtFim) { Swal.showValidationMessage('Selecione o período completo'); return false; }
                return { m, s, dtIni, dtFim };
            }
        });

        if (!loginData) return;

        Swal.fire({ title: 'Carregando...', didOpen: () => { Swal.showLoading(); }, allowOutsideClick: false });

        try {
            const res = await fetch('../api/ponto.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_meu_ponto', matricula: loginData.m, senha: loginData.s, start_date: loginData.dtIni, end_date: loginData.dtFim })
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

        Swal.fire({ title: 'Buscando período...', didOpen: () => { Swal.showLoading(); }, allowOutsideClick: false });

        try {
            const res = await fetch('../api/ponto.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_meu_ponto', matricula: mat, senha: sen, start_date: dtIni, end_date: dtFim })
            });

            const json = await res.json();
            if (!json.success) { Swal.fire('Aviso', json.message, 'error'); return; }

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
        
        const pontoHtml = (hora, atrasou, faltaAuto, justIndividual, statusCrh, tipoJustificativa, justificativaGlobal, horarioProgramado, emFerias, motivo, r) => {
            if (emFerias) {
                return `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-500 text-white border border-amber-600 uppercase tracking-tighter" title="${esc(motivo)}">📅 ${esc(motivo)}</span>`;
            }

            const isFaltaAuto = (hora === 'FALTA' || hora === 'falta' || faltaAuto);
            const isIndeferido = statusCrh === 'indeferido';
            const isDeferido = statusCrh === 'deferido';
            const hasJust = justIndividual && justIndividual !== 'null' && String(justIndividual).trim() !== '';
            const hasGlobalJust = tipoJustificativa && tipoJustificativa !== 'null' && String(tipoJustificativa).trim() !== '';
            
            // Se não há horário programado e não há batida, não é falta.
            if (!horarioProgramado && !hora) {
                return '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black text-slate-300">---</span>';
            }

            // Se houver qualquer justificativa individual na linha, a global não deve "auto-justificar" as outras batidas sozinhas
            const hasAnyIndiv = (r.just_ent1 && String(r.just_ent1).trim() !== '' && r.just_ent1 !== 'null') || 
                                (r.just_sai1 && String(r.just_sai1).trim() !== '' && r.just_sai1 !== 'null') || 
                                (r.just_ent2 && String(r.just_ent2).trim() !== '' && r.just_ent2 !== 'null') || 
                                (r.just_sai2 && String(r.just_sai2).trim() !== '' && r.just_sai2 !== 'null');

            const hasAnyJust = hasJust || (hasGlobalJust && !hasAnyIndiv && (atrasou || isFaltaAuto));
            
            const time = isFaltaAuto ? 'Falta' : String(hora).substring(0, 5);
            const valJust = justificativaGlobal ? justificativaGlobal.replace(/'/g, "\\'").replace(/"/g, "&quot;").replace(/\n/g, " ") : '';

            // 1. DEFERIDO
            if (isDeferido && hasAnyJust) {
                return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">✔ Justificado</span>`;
            }

            // 2. INDEFERIDO (Vermelho com Motivo)
            if (isIndeferido && hasAnyJust) {
                const label = isFaltaAuto ? `⛔ Falta` : `⛔ Falta (${esc(time)})`;
                return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black bg-red-50 text-red-700 border border-red-300 cursor-pointer hover:bg-red-100 transition-colors" 
                              onclick="Swal.fire({title:'Justificativa Recusada', text:'${valJust}', icon:'info'})"
                              title="Clique para ver o motivo">${label}</span>`;
            }

            // 3. PENDENTE
            if (hasAnyJust) {
                return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black bg-blue-50 text-blue-700 border border-blue-200">✔ Justificado</span>`;
            }

            // 4. ATRASO (Selo abaixo)
            if (atrasou && atrasou !== 'false' && atrasou !== false && !isFaltaAuto) {
                return `<div class="flex flex-col items-center gap-0.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-amber-50 text-amber-700 border border-amber-200">${esc(time)}</span>
                            <span class="text-[8px] font-black text-amber-600 uppercase tracking-tighter">Atraso</span>
                        </div>`;
            }

            // 5. FALTA
            if (isFaltaAuto) {
                return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black bg-red-50 text-red-700 border border-red-200">⛔ ${time}</span>`;
            }

            // 6. NORMAL
            return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-slate-100 text-slate-700">${esc(time)}</span>`;
        };

        const statusHtml = (rec) => {
            if (rec.em_ferias) return `<span class="text-[9px] font-black text-amber-600 uppercase">Afastado</span>`;
            
            const toMin = v => {
                if (!v || v === 'FALTA' || v === 'falta') return null;
                const p = String(v).substring(0, 5).split(':');
                if (p.length < 2) return null;
                return parseInt(p[0]) * 60 + parseInt(p[1]);
            };

            const h1 = !!rec.primeiro_horario, h2 = !!rec.segundo_horario;
            const h3 = !!rec.terceiro_horario, h4 = !!rec.quarto_horario;
            const p1 = !!rec.primeiro_ponto && rec.primeiro_ponto !== 'FALTA' && rec.primeiro_ponto !== 'falta';
            const p2 = !!rec.segundo_ponto  && rec.segundo_ponto  !== 'FALTA' && rec.segundo_ponto  !== 'falta';
            const p3 = !!rec.terceiro_ponto && rec.terceiro_ponto !== 'FALTA' && rec.terceiro_ponto !== 'falta';
            const p4 = !!rec.quarto_ponto   && rec.quarto_ponto   !== 'FALTA' && rec.quarto_ponto   !== 'falta';

            const incompleto = (h1 && !p1) || (h2 && !p2) || (h3 && !p3) || (h4 && !p4);

            let esp = 0, trab = 0;
            const eh1 = toMin(rec.primeiro_horario), eh2 = toMin(rec.segundo_horario);
            if (eh1 !== null && eh2 !== null) esp += eh2 - eh1;
            const eh3 = toMin(rec.terceiro_horario), eh4 = toMin(rec.quarto_horario);
            if (eh3 !== null && eh4 !== null) esp += eh4 - eh3;

            const r1 = toMin(rec.primeiro_ponto), r2 = toMin(rec.segundo_ponto);
            if (r1 !== null && r2 !== null) trab += r2 - r1;
            const r3 = toMin(rec.terceiro_ponto), r4 = toMin(rec.quarto_ponto);
            if (r3 !== null && r4 !== null) trab += r4 - r3;

            if (incompleto) return '<span class="text-[9px] font-black text-orange-600 uppercase">Incompleto</span>';

            if (esp > 0) {
                return '<span class="text-[9px] font-black text-emerald-600 uppercase">✓ OK</span>';
            }
            if (!p1 && !p2 && !p3 && !p4) return '';
            return '<span class="text-[9px] font-black text-emerald-600 uppercase">✓ OK</span>';
        };

        const rows = dados.map(r => {
            const dataFmt = new Date(r.data + 'T00:00:00').toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', weekday: 'short' }).replace('.', '');
            
            const p1 = pontoHtml(r.primeiro_ponto, r.atrasou_primeiro_ponto, r.falta_turno1_entrada, r.just_ent1, r.status_crh, r.tipo_justificativa, r.justificativa, r.primeiro_horario, r.em_ferias, r.motivo_afastamento, r);
            const p2 = pontoHtml(r.segundo_ponto,  r.atrasou_segundo_ponto,  r.falta_turno1_saida,   r.just_sai1, r.status_crh, r.tipo_justificativa, r.justificativa, r.segundo_horario, r.em_ferias, r.motivo_afastamento, r);
            const p3 = pontoHtml(r.terceiro_ponto, r.atrasou_terceiro_ponto, r.falta_turno2_entrada, r.just_ent2, r.status_crh, r.tipo_justificativa, r.justificativa, r.terceiro_horario, r.em_ferias, r.motivo_afastamento, r);
            const p4 = pontoHtml(r.quarto_ponto,   r.atrasou_quarto_ponto,   r.falta_turno2_saida,   r.just_sai2, r.status_crh, r.tipo_justificativa, r.justificativa, r.quarto_horario, r.em_ferias, r.motivo_afastamento, r);

            const st = statusHtml(r);
            const com = (r.comunicado && r.comunicado !== 'null') ? r.comunicado : '';

            let avisoRow = '';
            if (r.aviso && String(r.aviso).trim() !== '' && r.aviso !== 'null') {
                avisoRow = `<tr class="bg-red-50/20"><td colspan="7" class="py-1 px-3 text-[9px] font-bold text-red-600 italic border-b border-red-50/30 leading-tight">Recado: ${esc(r.aviso)}</td></tr>`;
            }

            return `
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                    <td class="py-3 px-2 text-[10px] font-black text-slate-500 uppercase">${dataFmt}</td>
                    <td class="py-3 px-1 text-center">${p1}</td>
                    <td class="py-3 px-1 text-center">${p2}</td>
                    <td class="py-3 px-1 text-center">${p3}</td>
                    <td class="py-3 px-1 text-center">${p4}</td>
                    <td class="py-3 px-1 text-center">${st}</td>
                    <td class="py-3 px-2 text-[9px] text-slate-500 italic truncate max-w-[120px]" title="${esc(com)}">${esc(com)}</td>
                </tr>
                ${avisoRow}
            `;
        }).join('');

        Swal.fire({
            title: `<div class="text-left flex flex-col gap-1"><p class="text-[10px] font-black text-brand-500 uppercase tracking-widest m-0">Extrato Individual</p><p class="text-lg font-black text-slate-800 m-0">${esc(nome)}</p></div>`,
            width: '850px',
            html: `
                <div id="extrato-pdf-content" class="p-1">
                    <div class="flex items-center justify-between mt-2 mb-2 p-3 bg-slate-50 border border-slate-100 rounded-xl relative" data-html2canvas-ignore="true">
                        <div class="flex flex-wrap items-center gap-3 w-full">
                            <div class="flex items-center gap-2">
                                <input type="date" id="inline-dt-ini" value="${dtIni}" class="px-2 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold font-mono text-slate-700 outline-none focus:border-brand-500 transition-colors shadow-sm">
                                <span class="text-xs font-black text-slate-400 uppercase">até</span>
                                <input type="date" id="inline-dt-fim" value="${dtFim}" class="px-2 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold font-mono text-slate-700 outline-none focus:border-brand-500 transition-colors shadow-sm">
                            </div>
                            <button type="button" onclick="window.recarregarExtrato()" class="px-4 py-1.5 bg-brand-600 hover:bg-brand-500 text-white rounded-lg text-xs font-black uppercase tracking-widest transition-all shadow-sm flex items-center gap-1 active:scale-95 ml-auto">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                Filtrar
                            </button>
                        </div>
                    </div>
                    <div class="mt-2 border border-slate-100 rounded-2xl overflow-hidden bg-white shadow-inner">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="py-3 px-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-24">Data</th>
                                    <th class="py-3 px-1 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">E1</th>
                                    <th class="py-3 px-1 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">S1</th>
                                    <th class="py-3 px-1 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">E2</th>
                                    <th class="py-3 px-1 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">S2</th>
                                    <th class="py-3 px-1 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                    <th class="py-3 px-3 text-[9px] font-black text-slate-400 uppercase tracking-widest text-left">Aviso</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                ${rows || '<tr><td colspan="5" class="py-12 text-center text-slate-400 font-medium italic">Nenhum registro encontrado este mês.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-4 flex flex-col gap-2">
                    <button onclick="comunicarAtrasoFalta('${esc(nome)}')" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-amber-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-amber-500 transition-all shadow-md active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Comunicar meu Atraso / Falta
                    </button>
                    <button onclick="trocarSenhaFuncionario()" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-slate-800 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-700 transition-all shadow-md active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                        Alterar Minha Senha
                    </button>
                </div>
                <div class="mt-6 p-4 bg-amber-50 rounded-xl border border-amber-100 flex gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="text-left">
                        <p class="text-[11px] font-bold text-amber-800 leading-tight">Dúvidas ou Divergências?</p>
                        <p class="text-[10px] text-amber-700/80 mt-1">Caso encontre algum erro nas batidas, procure imediatamente o gestor do seu setor ou o RH para regularização.</p>
                    </div>
                </div>
            `,
            showDenyButton: true,
            denyButtonText: 'Salvar PDF',
            denyButtonColor: '#0c4a6e',
            confirmButtonText: 'Fechar Extrato',
            confirmButtonColor: '#0ea5e9',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                sessionStorage.removeItem('extrato_pendente_dados');
                sessionStorage.removeItem('extrato_pendente_nome');
                sessionStorage.removeItem('extrato_pendente_mat');
                sessionStorage.removeItem('extrato_pendente_senha');
            } else if (result.isDenied) {
                exportarExtratoPDF(nome, dtIni, dtFim);
                renderizarCartaoIndividual(dados, nome, dtIni, dtFim);
            }
        });
    }

    window.exportarExtratoPDF = function(nome, dtIni, dtFim) {
        const element = document.getElementById('extrato-pdf-content');
        if (!element) return;

        const opt = {
            margin:       10,
            filename:     `Extrato_Ponto_${nome.replace(/\s+/g, '_')}.pdf`,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, letterRendering: true },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        // Adiciona um título temporário para o PDF
        const header = document.createElement('div');
        header.innerHTML = `
            <div style="text-align: center; margin-bottom: 20px; font-family: sans-serif;">
                <h1 style="color: #0284c7; margin-bottom: 5px;">Extrato Individual de Ponto</h1>
                <h2 style="color: #334155; margin-top: 0; margin-bottom: 5px;">${nome}</h2>
                <h3 style="color: #64748b; margin-top: 0; font-size: 14px; font-weight: normal;">Período: ${dtIni.split('-').reverse().join('/')} até ${dtFim.split('-').reverse().join('/')}</h3>
                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
            </div>
        `;
        
        const clone = element.cloneNode(true);
        const container = document.createElement('div');
        container.appendChild(header);
        container.appendChild(clone);

        html2pdf().set(opt).from(container).save();
    }

    window.comunicarAtrasoFalta = async function(nome) {
        let anexoData = null;

        const { value: formValues } = await Swal.fire({
            title: 'Comunicar Atraso ou Falta',
            html: `
                <div id="sw-step-form" class="text-left space-y-4 pt-2">
                    <div class="bg-amber-50 p-3 rounded-lg border border-amber-200 text-[11px] text-amber-800 leading-snug">
                        <strong>Importante:</strong> Este comunicado serve para avisar antecipadamente ao seu gestor e ao RH. Não substitui a necessidade de justificativa posterior se necessário.
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Data do Ocorrido</label>
                        <input type="date" id="sw-data-comunicado" class="swal2-input w-full m-0 mt-1 text-sm h-11 bg-slate-50 cursor-not-allowed" value="${new Date().toISOString().split('T')[0]}" readonly>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Mensagem do Comunicado</label>
                        <textarea id="sw-mensagem-comunicado" class="swal2-textarea w-full m-0 mt-1 text-sm p-3" rows="3" placeholder="Ex: Tive um imprevisto com o transporte e vou me atrasar 20 min..."></textarea>
                    </div>
                    
                    <div class="pt-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 block">Documento (Opcional)</label>
                        <div id="sw-anexo-container" class="flex items-center gap-3">
                            <button type="button" onclick="window._exibirPasso('select')" class="flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-bold transition-all border border-slate-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                Incluir Documento
                            </button>
                            <div id="sw-anexo-preview" class="hidden items-center gap-2 px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg border border-emerald-100 text-[10px] font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Documento Anexado</span>
                                <button type="button" onclick="window._removerAnexoComunicado()" class="ml-1 text-red-500 hover:text-red-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="sw-step-select" class="hidden flex flex-col gap-3 py-6">
                    <p class="text-sm font-bold text-slate-700 mb-2">Como deseja incluir o documento?</p>
                    <button type="button" onclick="document.getElementById('sw-file-input').click()" class="w-full flex items-center justify-center gap-3 p-4 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl border border-blue-200 transition-all font-bold">
                        <span class="text-2xl">📂</span> Escolher Arquivo ou PDF
                    </button>
                    <button type="button" onclick="window._exibirPasso('camera')" class="w-full flex items-center justify-center gap-3 p-4 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-xl border border-emerald-200 transition-all font-bold">
                        <span class="text-2xl">📷</span> Tirar Foto Agora
                    </button>
                    <button type="button" onclick="window._exibirPasso('form')" class="mt-4 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-600">Voltar</button>
                    <input type="file" id="sw-file-input" class="hidden" accept="image/*,application/pdf">
                </div>

                <div id="sw-step-camera" class="hidden flex flex-col gap-4">
                    <p class="text-sm font-bold text-slate-700">Capture a Foto do Documento</p>
                    <div class="relative bg-black rounded-2xl overflow-hidden aspect-video shadow-lg">
                        <video id="sw-cam-video" class="w-full h-full object-cover" autoplay playsinline></video>
                        <canvas id="sw-cam-canvas" class="hidden"></canvas>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="window._tirarFotoComunicado()" class="flex-1 py-3 bg-emerald-600 text-white rounded-xl font-bold shadow-md hover:bg-emerald-500 active:scale-95 transition-all">Capturar Foto</button>
                        <button type="button" onclick="window._exibirPasso('select')" class="px-6 py-3 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition-all">Cancelar</button>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Enviar Comunicado',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d97706',
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
                    } else if (step === 'select') {
                        stepSelect.classList.remove('hidden');
                    } else if (step === 'camera') {
                        stepCamera.classList.remove('hidden');
                        window._iniciarCameraComunicado();
                    }
                };

                window._removerAnexoComunicado = () => {
                    anexoData = null;
                    document.getElementById('sw-file-input').value = '';
                    document.getElementById('sw-anexo-preview').classList.add('hidden');
                    document.querySelector('#sw-anexo-container button').classList.remove('hidden');
                };

                window._iniciarCameraComunicado = async () => {
                    const video = document.getElementById('sw-cam-video');
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                        video.srcObject = stream;
                        window._swCamStream = stream;
                    } catch (e) {
                        alert('Erro ao acessar câmera: ' + e.message);
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
                    anexoData = canvas.toDataURL('image/jpeg', 0.8);
                    
                    document.getElementById('sw-anexo-preview').classList.remove('hidden');
                    document.querySelector('#sw-anexo-container button').classList.add('hidden');
                    window._exibirPasso('form');
                };

                document.getElementById('sw-file-input').onchange = (e) => {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            anexoData = e.target.result;
                            document.getElementById('sw-anexo-preview').classList.remove('hidden');
                            document.querySelector('#sw-anexo-container button').classList.add('hidden');
                            window._exibirPasso('form');
                        };
                        reader.readAsDataURL(file);
                    }
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
                    anexo: anexoData
                }
            }
        });

        if (formValues) {
            Swal.fire({ title: 'Enviando...', didOpen: () => { Swal.showLoading(); }, allowOutsideClick: false });
            
            if (!window._ultimaMatricula || !window._ultimaSenha) {
                Swal.fire('Aviso', 'Sessão expirada. Por favor, identifique-se novamente no "Meu Acesso".', 'error');
                return;
            }

            try {
                const res = await fetch('../api/ponto.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'salvar_comunicado',
                        matricula: window._ultimaMatricula,
                        senha: window._ultimaSenha,
                        ...formValues
                    })
                });
                const data = await res.json();
                
                if (data.success) {
                    Swal.fire('Sucesso!', data.message, 'success').then(() => {
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
            // Se cancelou o modal de comunicado, volta para o extrato
            if (window.recarregarExtrato) window.recarregarExtrato();
        }
    }

    window.trocarSenhaFuncionario = async function() {
        if (!window._ultimaMatricula || !window._ultimaSenha) {
            Swal.fire('Aviso', 'Sessão expirada. Por favor, identifique-se novamente no "Meu Acesso".', 'error');
            return;
        }

        const { value: formValues } = await Swal.fire({
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

                if (!antiga) { Swal.showValidationMessage('Informe sua senha atual'); return false; }
                if (nova.length < 6) { Swal.showValidationMessage('A nova senha deve ter pelo menos 6 caracteres'); return false; }
                if (nova !== confirma) { Swal.showValidationMessage('As novas senhas não coincidem'); return false; }

                return { antiga, nova };
            }
        });

        if (formValues) {
            Swal.fire({ title: 'Processando...', didOpen: () => { Swal.showLoading(); }, allowOutsideClick: false });

            try {
                const res = await fetch('../api/ponto.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
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
