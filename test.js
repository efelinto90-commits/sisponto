
        let tableFunc;

        $(document).ready(function () {
            tableFunc = $('#tabelaFuncionarios').DataTable({
                ajax: {
                    url: '../../api/funcionarios.php',
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'id', width: '50px' },
                    {
                        data: 'nome',
                        render: function (data, type, row) {
                            let icon = row.tem_biometria == 1
                                ? `<svg class="w-4 h-4 text-emerald-500 inline ml-2" title="Biometria Cadastrada" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path></svg>`
                                : `<svg class="w-4 h-4 text-slate-300 inline ml-2" title="Sem Biometria" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path></svg>`;

                            return `<div class="font-bold text-slate-800 flex items-center">${data} ${icon}</div>`;
                        }
                    },
                    { data: 'matricula', className: 'font-mono text-slate-600' },
                    { data: 'setor', defaultContent: '<span class="text-slate-400 text-sm">N/I</span>' },
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
                            return `
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="window.abrirCamera(${row.id}, '${row.nome.replace(/'/g, "\\'")}')" class="p-2 text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors border border-transparent hover:border-indigo-100" title="Câmera (Foto/QR)">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </button>
                                <button onclick="abrirBiometria(${row.id}, '${row.nome.replace(/'/g, "\\'")}')" class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg transition-colors border border-transparent hover:border-teal-100" title="Cadastrar Digital">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path></svg>
                                </button>
                                <button onclick="editarFuncionario(${row.id})" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors border border-transparent hover:border-indigo-100" title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <button onclick="deletarFuncionario(${row.id})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-100" title="Excluir">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        `;
                        }
                    }
                ],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' },
                dom: '<"flex flex-col md:flex-row justify-between items-center mb-4"lf>rt<"flex flex-col md:flex-row justify-between items-center mt-4"ip>',
                pageLength: 10,
                responsive: true,
                drawCallback: function (settings) {
                    // Preenche o Select de Horários assim que a API de funcionários (que também carrega os horários no Get All) retornar
                    if (settings.json && settings.json.horarios) {
                        const select = document.getElementById('func_horario');
                        // Evita duplicar options
                        if (select.options.length <= 1) {
                            settings.json.horarios.forEach(h => {
                                const opt = document.createElement('option');
                                opt.value = h.id;
                                let text = `Das ${h.primeiro_horario.substring(0, 5)} às ${h.quarto_horario.substring(0, 5)}`;
                                if (h.segundo_horario && h.terceiro_horario) {
                                    text = `Das ${h.primeiro_horario.substring(0, 5)} às ${h.segundo_horario.substring(0, 5)} e ${h.terceiro_horario.substring(0, 5)} às ${h.quarto_horario.substring(0, 5)}`;
                                }
                                opt.textContent = text;
                                select.appendChild(opt);
                            });
                        }
                    }
                }
            });

            // Tailwind forms nos inputs do DT
            $('.dataTables_filter input').addClass('border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 ml-2 shadow-sm');
            $('.dataTables_length select').addClass('border border-slate-200 rounded-lg px-2 py-1.5 focus:outline-none shadow-sm mx-1');
        });

        const modal = document.getElementById('modalFuncionario');
        const form = document.getElementById('formFuncionario');

        function openModal() {
            form.reset();
            document.getElementById('func_id').value = '';
            document.getElementById('modalTitle').textContent = 'Cadastrar Funcionário';
            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        // -- Flow Câmera Mobile (Foto, QR, Facial) --
        let videoStream = null;
        let currentFacingMode = 'user'; // 'user' (frontal) ou 'environment' (traseira)

        window.abrirCamera = function (id, nome) {
            try {
                document.getElementById('cam_func_id').value = id;
                document.getElementById('camNomeFunc').textContent = nome;
                document.getElementById('modalCamera').classList.remove('hidden');
                window.mudarModoCamera('foto'); // Default
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
            ['tabFoto', 'tabQr', 'tabFacial'].forEach(tab => {
                let el = document.getElementById(tab);
                if (el) el.className = "flex-1 py-1.5 text-xs font-semibold rounded-lg text-slate-500 hover:text-slate-700 transition-all";
            });

            // Remover guias pontilhadas antigas se existirem
            document.getElementById('guideFacial').classList.add('hidden');
            document.getElementById('guideQr').classList.add('hidden');
            
            const videoContainer = document.getElementById('videoContainer');
            
            // Remove dimension and border classes
            videoContainer.classList.remove('w-full', 'aspect-[4/3]', 'max-h-[50vh]', 'rounded-2xl', 'w-56', 'h-56', 'h-72', 'rounded-xl', 'rounded-[50%]', 'border-4', 'border-emerald-400', 'border-indigo-400', 'mx-auto');

            if (modo === 'foto') {
                videoContainer.classList.add('w-full', 'aspect-[4/3]', 'max-h-[50vh]', 'rounded-2xl');
                
                document.getElementById('tabFoto').className = "flex-1 py-1.5 text-xs font-semibold rounded-lg bg-white shadow-sm text-slate-800 transition-all";
                document.getElementById('btnCapturarCamera').innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg> Tirar Foto Perfil`;
            } else if (modo === 'qr') {
                videoContainer.classList.add('w-56', 'h-56', 'mx-auto', 'rounded-xl', 'border-4', 'border-emerald-400');
                
                document.getElementById('tabQr').className = "flex-1 py-1.5 text-xs font-semibold rounded-lg bg-white shadow-sm text-slate-800 transition-all";
                document.getElementById('btnCapturarCamera').innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg> Escanear QR Code`;
            } else if (modo === 'facial') {
                videoContainer.classList.add('w-56', 'h-72', 'mx-auto', 'rounded-[50%]', 'border-4', 'border-indigo-400');
                
                document.getElementById('tabFacial').className = "flex-1 py-1.5 text-xs font-semibold rounded-lg bg-white shadow-sm text-slate-800 transition-all";
                document.getElementById('btnCapturarCamera').innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Capturar Biometria Facial`;
            }

            window.iniciarStreamVideo();
        };

        window.iniciarStreamVideo = async function () {
            const video = document.getElementById('videoFeed');
            document.getElementById('camLoading').classList.remove('hidden');
            document.getElementById('camLoading').innerHTML = `
                <svg class="animate-spin h-6 w-6 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-xs">Iniciando câmera...</span>`;
            video.classList.add('hidden');

            window.pararStreamVideo();

            try {
                videoStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: currentFacingMode }
                });
                video.srcObject = videoStream;
                video.onloadedmetadata = () => {
                    video.play();
                    document.getElementById('camLoading').classList.add('hidden');
                    video.classList.remove('hidden');
                };
            } catch (err) {
                console.error("Erro ao acessar câmera: ", err);
                let errorMsg = "Permissão negada ou câmera inacessível.";
                if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                    errorMsg = "O navegador exige conexão HTTPS segura para usar a câmera.";
                }
                document.getElementById('camLoading').innerHTML = `<span class="text-xs text-red-400 font-bold w-full px-4 text-center">${errorMsg}</span>`;
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
                    image_data: base64Image
                })
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 3000 });
                    window.fecharCamera();
                } else {
                    Swal.fire('Ops!', data.message, 'error');
                    window.mudarModoCamera(modo);
                }
            }).catch(err => {
                Swal.fire('Erro', 'Falha ao salvar imagem', 'error');
                window.mudarModoCamera(modo);
            });
        };

        // -- Flow Biometria --
        const modalBio = document.getElementById('modalBiometria');

        function abrirBiometria(id, nome) {
            document.getElementById('bio_func_id').value = id;
            document.getElementById('bioSubtitle').innerHTML = `Posicione o dedo de <strong>${nome}</strong> no leitor biométrico.`;

            // Reset state
            document.getElementById('bioIconContainer').className = "w-16 h-16 bg-teal-50 text-teal-500 rounded-full flex items-center justify-center mb-4 relative";
            document.getElementById('btnCapturar').innerHTML = "Iniciar Captura";
            document.getElementById('btnCapturar').disabled = false;
            document.getElementById('bioScanRing').classList.add('hidden');

            modalBio.classList.remove('hidden');
        }

        function fecharBiometria() {
            modalBio.classList.add('hidden');
        }

        // Função "Placeholder" que vai acionar o Driver/API Local do Leitor Biométrico
        async function iniciarCapturaDigital() {
            document.getElementById('bioScanRing').classList.remove('hidden');
            document.getElementById('btnCapturar').innerHTML = "Procurando Leitor...";
            document.getElementById('btnCapturar').disabled = true;

            const funcId = document.getElementById('bio_func_id').value;

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

                    document.getElementById('btnCapturar').innerHTML = "Lendo digital... (Aguardando Toque)";

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
            document.getElementById('bioScanRing').classList.add('hidden');
            document.getElementById('btnCapturar').disabled = false;
            document.getElementById('btnCapturar').innerHTML = "Tentar Novamente";

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
            document.getElementById('bioScanRing').classList.add('hidden');
            document.getElementById('bioIconContainer').classList.replace('text-teal-500', 'text-emerald-500');
            document.getElementById('bioIconContainer').classList.replace('bg-teal-50', 'bg-emerald-50');
            document.getElementById('btnCapturar').innerHTML = "Lido com Sucesso!";

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
                    fecharBiometria();
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
            document.getElementById('btnCapturar').disabled = false;
            document.getElementById('btnCapturar').innerHTML = "Tentar Novamente";
        }
        // -- Fim Flow Biometria --

        async function editarFuncionario(id) {
            try {
                const res = await fetch(`../../api/funcionarios.php?id=${id}`);
                const json = await res.json();
                if (json.success) {
                    const data = json.data;
                    document.getElementById('func_id').value = data.id;
                    document.getElementById('func_nome').value = data.nome || '';
                    document.getElementById('func_matricula').value = data.matricula || '';
                    document.getElementById('func_setor').value = data.setor || '';
                    document.getElementById('func_cpf').value = data.cpf || '';
                    document.getElementById('func_celular').value = data.celular || '';
                    document.getElementById('func_horario').value = data.id_horario || '';

                    document.getElementById('modalTitle').textContent = 'Editar Funcionário';
                    modal.classList.remove('hidden');
                }
            } catch (e) {
                Swal.fire('Erro', 'Não foi possível carregar os dados.', 'error');
            }
        }

        async function salvarFuncionario(e) {
            e.preventDefault();
            const id = document.getElementById('func_id').value;
            const method = id ? 'PUT' : 'POST';

            const celularVal = document.getElementById('func_celular').value;
            const cpfVal = document.getElementById('func_cpf').value;

            if (!/^0[0-9]{2}9[0-9]{8}$/.test(celularVal)) {
                Swal.fire('Formato Inválido', 'O número de celular deve seguir o formato 0XX9XXXXXXXX.', 'warning');
                return;
            }

            const payload = {
                id: id,
                nome: document.getElementById('func_nome').value,
                matricula: document.getElementById('func_matricula').value,
                setor: document.getElementById('func_setor').value,
                cpf: cpfVal,
                celular: celularVal,
                id_horario: document.getElementById('func_horario').value
            };

            try {
                const res = await fetch('../../api/funcionarios.php', {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (data.success) {
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 3000
                    });
                    closeModal();
                    tableFunc.ajax.reload();
                } else {
                    Swal.fire('Ops!', data.message, 'error');
                }
            } catch (e) {
                Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
            }
        }

        async function deletarFuncionario(id) {
            Swal.fire({
                title: 'Você tem certeza?',
                text: "Não será possível reverter esta ação!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
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
                }
            });
        }
    
