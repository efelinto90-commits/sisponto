
    $(function() {
        // Sincronização de campos duplicados entre abas
        $('#func_nome').on('input', function() { $('#func_nome_2').val($(this).val()); });
        $('#func_nome_2').on('input', function() { $('#func_nome').val($(this).val()); });
        $('#func_matricula').on('input', function() { $('#func_matricula_2').val($(this).val()); });
        $('#func_matricula_2').on('input', function() { $('#func_matricula').val($(this).val()); });
        
        // Bloqueio de campos de matrícula/cpf duplicados
        $('#func_cpf').on('blur', function() { verificarDuplicado('cpf', $(this).val(), 'feedback_cpf'); });
        $('#func_matricula').on('blur', function() { verificarDuplicado('matricula', $(this).val(), 'feedback_matricula'); });
    });

    function toggleDeficienciaFields() {
        const isPCD = $('#func_deficiencia').val() === 'true';
        if (isPCD) $('#wrapper_deficiencia').removeClass('hidden');
        else $('#wrapper_deficiencia').addClass('hidden');
    }

    function switchTab(tabId) {
        console.log('Alternando para aba:', tabId);
        $('.tab-content').addClass('hidden');
        const targetTab = $('#' + tabId);
        
        if (targetTab.length === 0) {
            console.error('ERRO: Elemento da aba não encontrado:', tabId);
        } else {
            targetTab.removeClass('hidden');
            console.log('Aba exibida com sucesso:', tabId);
        }
        
        // Update buttons
        $('#btn-tab-acesso, #btn-tab-ficha, #btn-tab-termo').removeClass('border-brand-600 text-brand-600').addClass('border-transparent text-slate-400');
        if (tabId === 'tab-acesso') $('#btn-tab-acesso').addClass('border-brand-600 text-brand-600').removeClass('border-transparent text-slate-400');
        else if (tabId === 'tab-ficha') $('#btn-tab-ficha').addClass('border-brand-600 text-brand-600').removeClass('border-transparent text-slate-400');
        else if (tabId === 'tab-termo') $('#btn-tab-termo').addClass('border-brand-600 text-brand-600').removeClass('border-transparent text-slate-400');
    }

    // Flags de bloqueio de duplicidade
    window._duplicadoCpf = false;
    window._duplicadoMatricula = false;

    async function verificarDuplicado(campo, valor, feedbackId) {
        const fb = $('#' + feedbackId);
        if (!valor || valor.trim() === '') {
            fb.addClass('hidden').html('');
            return;
        }

        const excludeId = $('#func_id').val() || '';
        const url = `../../api/funcionarios.php?check_duplicado=1&campo=${campo}&valor=${encodeURIComponent(valor)}&exclude_id=${excludeId}`;

        // Estado: verificando
        fb.removeClass('hidden').html(`
            <svg class="w-3.5 h-3.5 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-slate-400">Verificando...</span>
        `);

        try {
            const res = await fetch(url);
            const data = await res.json();

            if (data.exists) {
                // Duplicado encontrado
                fb.html(`
                    <svg class="w-3.5 h-3.5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <span class="text-red-600">Já cadastrado: <strong>${data.nome}</strong> — <a href="funcionario_form.php?id=${data.id}" class="underline hover:text-red-800" target="_blank">Ver cadastro</a></span>
                `);
                // Marcar campo como erro
                const input = campo === 'cpf' ? $('#func_cpf') : $('#func_matricula');
                input.addClass('border-red-400 ring-2 ring-red-400/20').removeClass('border-slate-200');
                if (campo === 'cpf') window._duplicadoCpf = true;
                else window._duplicadoMatricula = true;
            } else {
                // Disponível
                fb.html(`
                    <svg class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-emerald-600">Disponível</span>
                `);
                const input = campo === 'cpf' ? $('#func_cpf') : $('#func_matricula');
                input.removeClass('border-red-400 ring-2 ring-red-400/20').addClass('border-slate-200');
                if (campo === 'cpf') window._duplicadoCpf = false;
                else window._duplicadoMatricula = false;
            }
        } catch (e) {
            fb.addClass('hidden').html('');
        }
    }

    function imprimirTermo() {
        // Obter os valores atuais dos campos editáveis para passar para o print
        const orgao = $('#termo_orgao').val();
        const cnpj = $('#termo_cnpj').val();
        const orgaoExtra = $('#termo_orgao_extra').val();
        const dpoNome = $('#termo_dpo_nome').val();
        const dpoContato = $('#termo_dpo_contato').val();
        const dpoExtra = $('#termo_dpo_extra').val();
        const cienciaTexto = $('#termo_ciencia_texto').val();
        const cienciaExtra = $('#termo_ciencia_extra').val();

        // Signature block
        const localData = $('#termo_local_data').val();
        const servidorNome = $('#termo_servidor_nome').val();
        const servidorCargo = $('#termo_servidor_cargo').val();
        const respNome = $('#termo_responsavel_nome').val();
        const respCargo = $('#termo_responsavel_cargo').val();
        const crhNome = $('#termo_crh_nome').val();
        const crhCargo = $('#termo_crh_cargo').val();
        const showLines = $('#exibir_linha_assinatura').is(':checked');

        // Photo data
        const photoData = $('#termo_foto_preview').attr('src');

        // Basic data
        const nome = $('#termo_nome').text();
        const cpf = $('#termo_cpf').text();
        const matricula = $('#termo_matricula').text();

        const printWindow = window.open('', '_blank');
        
        // Estilos para impressão
        const styles = `
            body { font-family: sans-serif; padding: 40px; line-height: 1.6; color: #000; font-size: 13px; }
            h2, h3 { text-align: center; margin-bottom: 5px; text-transform: uppercase; }
            h4 { margin-top: 20px; border-bottom: 2px solid #000; padding-bottom: 5px; font-weight: bold; font-size: 11px; }
            ul { margin-top: 5px; margin-bottom: 15px; }
            p { margin: 8px 0; }
            .font-bold { font-weight: bold; }
            .mt-8 { margin-top: 40px; }
            .text-center { text-align: center; }
            .text-justify { text-align: justify; }
            .space-y-6 > * + * { margin-top: 24px; }
            .field-line { border-bottom: 1px dashed #333; display: inline-block; font-weight: bold; padding: 0 4px; }
            .block-field { border-bottom: 1px dashed #333; width: 100%; display: block; min-height: 1.5em; margin-top: 4px; }
            .signature-block { margin-top: 40px; width: 100%; border-top: 1px dashed #000; padding-top: 5px; text-align: center; }
            .box { padding: 15px; border: 1px solid #ccc; background: #fff; margin-bottom: 10px; position: relative; }
            .photo-badge { position: absolute; right: 15px; top: 15px; width: 100px; height: 100px; border: 1px solid #000; padding: 2px; }
        `;

        let content = `
            <div class="text-center mb-10">
                <h2>TERMO DE CIÊNCIA E CONSENTIMENTO PARA TRATAMENTO DE DADOS BIOMÉTRICOS</h2>
                <h3>SISTEMA WEB DE CONTROLE DE PONTO – ADMINISTRAÇÃO PÚBLICA</h3>
            </div>
            
            <div class="space-y-6 text-justify">
                <div class="box">
                    ${photoData ? `<img src="${photoData}" class="photo-badge">` : ''}
                    <h4>1. IDENTIFICAÇÃO DO TITULAR (SERVIDOR)</h4>
                    <p>Nome: <span class="field-line">${nome}</span></p>
                    <p>CPF: <span class="field-line">${cpf}</span></p>
                    <p>Matrícula: <span class="field-line">${matricula}</span></p>
                    ${photoData ? '<p style="font-size: 10px; color: #666; margin-top: 10px;">[Assinatura Biométrica Facial Registrada]</p>' : ''}
                    <div style="clear: both;"></div>
                </div>

                <div>
                    <h4>2. IDENTIFICAÇÃO DO ÓRGÃO PÚBLICO (CONTROLADOR)</h4>
                    <p>Órgão/Entidade: <span class="field-line">${orgao}</span></p>
                    <p>CNPJ: <span class="field-line">${cnpj || ''}</span></p>
                    <div class="block-field">${orgaoExtra || ''}</div>
                </div>

                <div>
                    <h4>3. FINALIDADE DO TRATAMENTO</h4>
                    <p>Os dados biométricos faciais serão utilizados para:</p>
                    <ul>
                        <li>Registro e controle da jornada de trabalho de servidores e colaboradores;</li>
                        <li>Identificação segura no acesso ao sistema;</li>
                        <li>Prevenção de fraudes no registro de ponto;</li>
                        <li>Atendimento ao interesse público e à execução de políticas administrativas.</li>
                    </ul>
                </div>

                <div>
                    <h4>4. BASE LEGAL</h4>
                    <p>O tratamento de dados pessoais e biométricos será realizado em conformidade com a Lei nº 13.709/2018 (LGPD), com fundamento em: Cumprimento de obrigação legal ou regulatória pela Administração Pública; Execução de políticas públicas; Interesse público; Quando aplicável, consentimento do titular.</p>
                </div>

                <div>
                    <h4>5. DADOS COLETADOS</h4>
                    <p>Serão coletadas imagens faciais, podendo ser convertidas em dados biométricos (templates matemáticos) para autenticação no sistema, não sendo necessariamente armazenadas como imagem convencional.</p>
                </div>

                <div>
                    <h4>6. FORMA DE UTILIZAÇÃO</h4>
                    <p>Os dados serão utilizados exclusivamente no âmbito do sistema de controle de ponto institucional, sendo acessados apenas por servidores autorizados.</p>
                </div>

                <div>
                    <h4>7. COMPARTILHAMENTO DE DADOS</h4>
                    <p>Os dados poderão ser compartilhados entre órgãos e entidades da Administração Pública, quando necessário; Para cumprimento de obrigações legais ou judiciais; Com órgãos de controle e fiscalização.</p>
                </div>

                <div>
                    <h4>8. ARMAZENAMENTO E SEGURANÇA</h4>
                    <p>Serão adotadas medidas técnicas e administrativas para garantir a proteção dos dados pessoais contra acessos não autorizados, perda, alteração ou divulgação indevida.</p>
                </div>

                <div>
                    <h4>9. PRAZO DE RETENÇÃO</h4>
                    <p>Os dados serão mantidos pelo tempo necessário ao cumprimento de sua finalidade e conforme as normas de gestão documental da Administração Pública.</p>
                </div>

                <div>
                    <h4>10. DIREITOS DO TITULAR</h4>
                    <p>Nos termos da LGPD, o titular poderá: Confirmar a existência de tratamento; Acessar seus dados; Solicitar correção de dados incompletos; Obter informações sobre o tratamento; Solicitar anonimização ou eliminação, quando aplicável.</p>
                </div>

                <div>
                    <h4>11. ENCARREGADO PELO TRATAMENTO DE DADOS (DPO)</h4>
                    <p>Contato do Encarregado (DPO): <span class="field-line">${dpoNome || ''}</span></p>
                    <p>E-mail/Canal: <span class="field-line">${dpoContato || ''}</span></p>
                    <div class="block-field">${dpoExtra || ''}</div>
                </div>

                <div>
                    <h4>12. CIÊNCIA E CONSENTIMENTO</h4>
                    <p>${cienciaTexto.replace(/\n/g, '<br>')}</p>
                    <div class="block-field">${cienciaExtra || ''}</div>
                </div>

                <div class="mt-8 pt-6">
                    <p><span class="font-bold">Local e data:</span> <span class="field-line" style="min-width: 300px;">${localData}</span></p>
                    
                    <div style="margin-top: 50px; display: flex; flex-direction: column; gap: 40px;">
                        <div class="text-center">
                            ${showLines ? '<div style="width: 400px; border-top: 1px solid #000; margin: 0 auto 5px;"></div>' : ''}
                            <p class="font-bold">Assinatura do(a) servidor(a)</p>
                            <p style="font-size: 12px;">${servidorNome || nome} ${servidorCargo ? ' - ' + servidorCargo : ''}</p>
                        </div>
                        
                        <div class="text-center">
                            ${showLines ? '<div style="width: 400px; border-top: 1px solid #000; margin: 0 auto 5px;"></div>' : ''}
                            <p class="font-bold">Assinatura do responsável - DPO</p>
                            <p style="font-size: 12px;">${respNome} ${respCargo ? ' - ' + respCargo : ''}</p>
                        </div>
                        
                        <div class="text-center">
                            ${showLines ? '<div style="width: 400px; border-top: 1px solid #000; margin: 0 auto 5px;"></div>' : ''}
                            <p class="font-bold">Encarregado/Chefe do CRH</p>
                            <p style="font-size: 12px;">${crhNome} ${crhCargo ? ' - ' + crhCargo : ''}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        printWindow.document.write('<html><head><title>Termo de Autorização - Biometria</title>');
        printWindow.document.write('<style>' + styles + '</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(content);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    }

    function replicarHorarioPrimeiroDia() {
        const p1 = $('input[data-day="segunda"][data-ponto="p1"]').val();
        const p2 = $('input[data-day="segunda"][data-ponto="p2"]').val();
        const p3 = $('input[data-day="segunda"][data-ponto="p3"]').val();
        const p4 = $('input[data-day="segunda"][data-ponto="p4"]').val();

        $('.time-grade-input[data-ponto="p1"]').val(p1);
        $('.time-grade-input[data-ponto="p2"]').val(p2);
        $('.time-grade-input[data-ponto="p3"]').val(p3);
        $('.time-grade-input[data-ponto="p4"]').val(p4);
        
        // Feedback visual
        $('.time-grade-input').addClass('ring-2 ring-indigo-500/50');
        setTimeout(() => {
            $('.time-grade-input').removeClass('ring-2 ring-indigo-500/50');
        }, 1000);
    }

    function limparDia(day) {
        $(`.time-grade-input[data-day="${day}"]`).val('');
        $(`div[data-day-card="${day}"]`).addClass('bg-red-50');
        setTimeout(() => {
            $(`div[data-day-card="${day}"]`).removeClass('bg-red-50');
        }, 500);
    }

    $(document).ready(async function() {
        // Carregamento inicial robusto
        try { await carregarHorarios(); } catch(e) { console.error("Erro ao carregar horários:", e); }
        try { await loadTiposContratacao(); } catch(e) { console.error("Erro ao carregar tipos de contratação:", e); }
        try { await carregarAreasPreset(); } catch(e) { console.error("Erro ao carregar áreas:", e); }
        
        const id = $('#func_id').val();
        if (id) {
            try {
                await carregarFuncionario(id);
            } catch(e) {
                console.error("Erro ao carregar funcionário:", e);
                if (typeof Swal !== 'undefined') {
                    Swal.fire("Erro", "Falha crítica ao carregar os dados do funcionário.", "error");
                }
            }
        }

        // Sync fields between tabs and Termo
        $('#func_nome').on('input', function() { 
            $('#func_nome_2').val(this.value); 
            $('#termo_nome').text(this.value || '________________________________');
            $('#termo_servidor_nome').val(this.value);
        });
        $('#func_nome_2').on('input', function() { 
            $('#func_nome').val(this.value); 
            $('#termo_nome').text(this.value || '________________________________');
            $('#termo_servidor_nome').val(this.value);
        });
        $('#func_matricula').on('input', function() { 
            $('#func_matricula_2').val(this.value); 
            $('#termo_matricula').text(this.value || '___________________________');
        });
        $('#func_matricula_2').on('input', function() { 
            $('#func_matricula').val(this.value); 
            $('#termo_matricula').text(this.value || '___________________________');
        });
        $('#func_cpf').on('input', function() {
            $('#termo_cpf').text(this.value || '___________________________');
        });

        // Verificação de duplicidade ao sair do campo
        $('#func_cpf').on('blur', function() {
            verificarDuplicado('cpf', $(this).val(), 'feedback_cpf');
        });
        $('#func_matricula').on('blur', function() {
            verificarDuplicado('matricula', $(this).val(), 'feedback_matricula');
        });
        
        // Extra sync for editable server name
        $('#termo_servidor_nome').on('input', function() {
            $('#termo_nome').text(this.value || '________________________________');
        });

        // Autopreenchimento do Endereço via ViaCEP
        $('#func_endereco_cep').on('blur', async function() {
            let cep = $(this).val().replace(/\D/g, '');
            if (cep.length === 8) {
                // Feedback visual de carregamento
                $(this).addClass('ring-2 ring-indigo-500/50');
                try {
                    const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                    const data = await res.json();
                    
                    if (!data.erro) {
                        $('#func_endereco').val(data.logradouro || '');
                        $('#func_endereco_bairro').val(data.bairro || '');
                        $('#func_endereco_municipio').val(data.localidade || '');
                        $('#func_endereco_uf').val(data.uf || '');
                        
                        // Feedback visual de sucesso nos campos
                        $('#func_endereco, #func_endereco_bairro, #func_endereco_municipio, #func_endereco_uf').addClass('ring-2 ring-emerald-500/50');
                        setTimeout(() => {
                            $('#func_endereco, #func_endereco_bairro, #func_endereco_municipio, #func_endereco_uf').removeClass('ring-2 ring-emerald-500/50');
                        }, 2000);
                        
                        // Foca no número
                        $('#func_endereco_numero').focus();
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'CEP não encontrado', showConfirmButton: false, timer: 3000 });
                        }
                    }
                } catch (e) {
                    console.error("Erro ao buscar CEP:", e);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Erro ao buscar CEP', showConfirmButton: false, timer: 3000 });
                    }
                } finally {
                    $(this).removeClass('ring-2 ring-indigo-500/50');
                }
            }
        });

        // Eventos para auto-preenchimento de geofencing via preset
        $('#func_area_geofencing').on('input', function() {
            const val = $(this).val();
            const preset = window.geofencing_presets?.find(p => p.nome_area === val);
            if (preset) {
                $('#func_lat').val(preset.latitude);
                $('#func_lng').val(preset.longitude);
                $('#func_dist_max').val(preset.distancia_max);
                
                // Feedback visual de que foi auto-preenchido
                $('#func_lat, #func_lng, #func_dist_max').addClass('ring-2 ring-emerald-500/50');
                setTimeout(() => {
                    $('#func_lat, #func_lng, #func_dist_max').removeClass('ring-2 ring-emerald-500/50');
                }, 2000);
            }
        });

        $('#func_area_geofencing_2').on('input', function() {
            const val = $(this).val();
            const preset = window.geofencing_presets?.find(p => p.nome_area === val);
            if (preset) {
                $('#func_lat_2').val(preset.latitude);
                $('#func_lng_2').val(preset.longitude);
                $('#func_dist_max_2').val(preset.distancia_max);
                
                // Feedback visual de que foi auto-preenchido
                $('#func_lat_2, #func_lng_2, #func_dist_max_2').addClass('ring-2 ring-indigo-500/50');
                setTimeout(() => {
                    $('#func_lat_2, #func_lng_2, #func_dist_max_2').removeClass('ring-2 ring-indigo-500/50');
                }, 2000);
            }
        });
    });

    async function carregarAreasPreset() {
        try {
            const res = await fetch('../../api/geofencing_presets.php');
            const data = await res.json();
            if (data.success) {
                const datalist = $('#areas_list');
                datalist.empty();
                window.geofencing_presets = data.data;
                data.data.forEach(p => {
                    datalist.append(`<option value="${p.nome_area}">`);
                });
            }
        } catch (e) {
            console.error("Erro ao carregar áreas:", e);
        }
    }

    async function carregarHorarios() {
        const res = await fetch('../../api/horarios.php');
        const data = await res.json();
        if (data.success) {
            window.horarios_data = data.data; // Armazena globalmente para busca rápida
            const selectPrincipal = $('#func_horario');
            const selectsGrade = $('.select-horario-grade');
            
            data.data.forEach(h => {
                const start = h.primeiro_horario ? h.primeiro_horario.substring(0, 5) : '--:--';
                const end = h.quarto_horario ? h.quarto_horario.substring(0, 5) : '--:--';
                let text = `${h.nome} (${start} - ${end})`;
                
                const option = `<option value="${h.id}">${text}</option>`;
                selectPrincipal.append(option);
                selectsGrade.append(option);
            });
            
            // Após carregar os horários, se tivermos dados da grade pendentes, aplicamos
            if (window.pending_grade) {
                aplicarGradeHorarios(window.pending_grade);
            }
        }
    }

    // Event listener para mudança de horário na grade
    $(document).on('change', '.select-horario-grade', function() {
        const id = $(this).val();
        const day = $(this).data('day');
        if (!id) return;
        
        if (!window.horarios_data) return;
        const horario = window.horarios_data.find(h => h.id == id);
        
        if (horario) {
            $(`input[data-day="${day}"][data-ponto="p1"]`).val(horario.primeiro_horario ? horario.primeiro_horario.substring(0, 5) : '');
            $(`input[data-day="${day}"][data-ponto="p2"]`).val(horario.segundo_horario ? horario.segundo_horario.substring(0, 5) : '');
            $(`input[data-day="${day}"][data-ponto="p3"]`).val(horario.terceiro_horario ? horario.terceiro_horario.substring(0, 5) : '');
            $(`input[data-day="${day}"][data-ponto="p4"]`).val(horario.quarto_horario ? horario.quarto_horario.substring(0, 5) : '');
            
            // Feedback visual de auto-preenchimento
            $(`[data-day-card="${day}"]`).addClass('ring-2 ring-indigo-500/50');
            setTimeout(() => {
                $(`[data-day-card="${day}"]`).removeClass('ring-2 ring-indigo-500/50');
            }, 1000);
        }
    });

    function aplicarGradeHorarios(grade) {
        if (!grade) return;
        Object.keys(grade).forEach(day => {
            const dayData = grade[day];
            if (Array.isArray(dayData)) {
                $(`input[data-day="${day}"][data-ponto="p1"]`).val(dayData[0] || '');
                $(`input[data-day="${day}"][data-ponto="p2"]`).val(dayData[1] || '');
                $(`input[data-day="${day}"][data-ponto="p3"]`).val(dayData[2] || '');
                $(`input[data-day="${day}"][data-ponto="p4"]`).val(dayData[3] || '');
            } else if (typeof dayData === 'object' && dayData !== null) {
                // Suporte para formato de objeto
                $(`input[data-day="${day}"][data-ponto="p1"]`).val(dayData.p1 || '');
                $(`input[data-day="${day}"][data-ponto="p2"]`).val(dayData.p2 || '');
                $(`input[data-day="${day}"][data-ponto="p3"]`).val(dayData.p3 || '');
                $(`input[data-day="${day}"][data-ponto="p4"]`).val(dayData.p4 || '');
                
                // Aplicar o ID do horário no select, se existir
                if (dayData.horario_id) {
                    $(`.select-horario-grade[data-day="${day}"]`).val(dayData.horario_id);
                }
            } else if (typeof dayData === 'string' || typeof dayData === 'number') {
                // Fallback para legado
                console.log(`Dados legados detectados para ${day}: ${dayData}`);
                $(`.select-horario-grade[data-day="${day}"]`).val(dayData);
            }
        });
    }

    async function carregarFuncionario(id) {
        try {
            const res = await fetch(`../../api/funcionarios.php?id=${id}`);
            const data = await res.json();
            if (data.success && data.data) {
                const func = data.data;
                $('#pageTitle').text('Editar: ' + func.nome);
                
                // Aba 1
                $('#func_nome').val(func.nome);
                $('#func_matricula').val(func.matricula);
                $('#func_setor').val(func.setor);
                $('#func_setor2').val(func.setor2 || '');
                $('#func_cpf').val(func.cpf);
                $('#func_celular').val(func.celular || '');
                $('#func_horario').val(func.id_horario || '');
                $('#func_lat').val(func.lat_permitida || '');
                $('#func_lng').val(func.long_permitida || '');
                $('#func_area_geofencing').val(func.area_geofencing || '');
                $('#func_dist_max').val(func.distancia_max_permitida || '200');
                
                $('#func_turno1_inicio').val(func.turno1_inicio ? func.turno1_inicio.substring(0, 5) : '');
                $('#func_turno1_fim').val(func.turno1_fim ? func.turno1_fim.substring(0, 5) : '');
                
                $('#func_turno2_inicio').val(func.turno2_inicio ? func.turno2_inicio.substring(0, 5) : '');
                $('#func_turno2_fim').val(func.turno2_fim ? func.turno2_fim.substring(0, 5) : '');
                $('#func_area_geofencing_2').val(func.area_geofencing_2 || '');
                $('#func_lat_2').val(func.lat_permitida_2 || '');
                $('#func_lng_2').val(func.long_permitida_2 || '');
                $('#func_dist_max_2').val(func.distancia_max_permitida_2 || '200');

                if (func.lat_permitida_2 || func.area_geofencing_2 || func.turno2_inicio) {
                    $('#toggle_local_2').prop('checked', true);
                    $('#container_local_2').removeClass('hidden');
                } else {
                    $('#toggle_local_2').prop('checked', false);
                    $('#container_local_2').addClass('hidden');
                }

                $('#labelSenhaInfo').text('(Deixe em branco para manter a atual)');

                if (func.metodos_acesso) {
                    try {
                        const metodos = typeof func.metodos_acesso === 'string' ? JSON.parse(func.metodos_acesso) : func.metodos_acesso;
                        if (Array.isArray(metodos)) {
                            metodos.forEach(m => {
                                $(`input[name="metodos"][value="${m}"]`).prop('checked', true);
                                if (m === 'senha') $('#containerSenha').removeClass('hidden');
                            });
                        }
                    } catch (e) {
                        console.error("Erro ao processar metodos_acesso:", e);
                    }
                }
                $('#func_is_exonerado').prop('checked', func.is_exonerado == 1);

                const photoData = func.foto_perfil || func.biometria_facial;
                if (photoData) {
                    $('#func_foto_preview').attr('src', photoData).removeClass('hidden');
                    $('#func_foto_placeholder').addClass('hidden');
                    
                    // Mostrar controles de edição para foto existente
                    $('#post_capture_controls').removeClass('hidden');
                    $('#pre_capture_controls').addClass('hidden');
                    $('#btn_ativar_camera').addClass('hidden');

                    // Sync with Termo tab
                    $('#termo_foto_preview').attr('src', photoData).removeClass('hidden');
                    $('#termo_foto_placeholder').addClass('hidden');
                }

                // Aba 2
                $('#func_nome_2').val(func.nome);
                $('#func_matricula_2').val(func.matricula);
                $('#func_nome_social').val(func.nome_social || '');
                $('#func_unidade_trabalho').val(func.unidade_trabalho || '');
                $('#func_tipo_contratacao').val(func.tipo_contratacao || '');
                $('#func_cargo_funcao').val(func.cargo_funcao || '');
                $('#func_data_admissao').val(func.data_admissao || '');
                $('#func_data_exercicio').val(func.data_exercicio || '');
                $('#func_carga_horaria').val(func.carga_horaria || '');
                $('#func_turno_trabalho').val(func.turno_trabalho || 'Diurno');
                $('#func_endereco').val(func.endereco || '');
                $('#func_endereco_numero').val(func.endereco_numero || '');
                $('#func_endereco_complemento').val(func.endereco_complemento || '');
                $('#func_endereco_bairro').val(func.endereco_bairro || '');
                $('#func_endereco_cep').val(func.endereco_cep || '');
                $('#func_endereco_municipio').val(func.endereco_municipio || '');
                $('#func_endereco_uf').val(func.endereco_uf || '');
                $('#func_telefone_fixo').val(func.telefone_fixo || '');
                $('#func_endereco_email').val(func.endereco_email || '');
                $('#func_data_nascimento').val(func.data_nascimento || '');
                $('#func_naturalidade').val(func.naturalidade || '');
                $('#func_naturalidade_uf').val(func.naturalidade_uf || '');
                $('#func_nacionalidade').val(func.nacionalidade || '');
                $('#func_estado_civil').val(func.estado_civil || 'Solteiro(a)');
                $('#func_sexo').val(func.sexo || 'M');
                $('#func_raca_cor').val(func.raca_cor || 'Branca');
                $('#func_deficiencia').val(func.deficiencia ? 'true' : 'false');
                $('#func_deficiencia_grau').val(func.deficiencia_grau || '');
                $('#func_deficiencia_tipo').val(func.deficiencia_tipo || '');
                $('#func_deficiencia_cid').val(func.deficiencia_cid || '');
                toggleDeficienciaFields();
                $('#func_tipo_sanguineo').val(func.tipo_sanguineo || '');
                $('#func_pis_pasep').val(func.pis_pasep || '');
                $('#func_carteira_conselheiro').val(func.carteira_conselheiro || '');
                $('#func_rg_numero').val(func.rg_numero || '');
                $('#func_rg_orgao').val(func.rg_orgao || '');
                $('#func_rg_data_emissao').val(func.rg_data_emissao || '');
                $('#func_cnh_numero').val(func.cnh_numero || '');
                $('#func_cnh_categoria').val(func.cnh_categoria || '');
                $('#func_cnh_validade').val(func.cnh_validade || '');
                $('#func_titulo_eleitor_numero').val(func.titulo_eleitor_numero || '');
                $('#func_titulo_eleitor_zona').val(func.titulo_eleitor_zona || '');
                $('#func_titulo_eleitor_secao').val(func.titulo_eleitor_secao || '');
                $('#func_ctps_numero').val(func.ctps_numero || '');
                $('#func_ctps_serie').val(func.ctps_serie || '');
                $('#func_ctps_uf').val(func.ctps_uf || '');
                $('#func_ctps_data_emissao').val(func.ctps_data_emissao || '');
                $('#func_reservista_numero').val(func.reservista_numero || '');
                $('#func_reservista_serie').val(func.reservista_serie || '');
                $('#func_escolaridade').val(func.escolaridade || '');
                $('#func_nome_pai').val(func.nome_pai || '');
                $('#func_nome_mae').val(func.nome_mae || '');
                $('#func_nome_conjuge').val(func.nome_conjuge || '');
                $('#func_data_nascimento_conjuge').val(func.data_nascimento_conjuge || '');
                $('#func_nacionalidade_conjuge').val(func.nacionalidade_conjuge || '');
                $('#func_naturalidade_conjuge').val(func.naturalidade_conjuge || '');
                $('#func_naturalidade_uf_conjuge').val(func.naturalidade_uf_conjuge || '');
                $('#func_banco_nome').val(func.banco_nome || '');
                $('#func_banco_agencia').val(func.banco_agencia || '');
                $('#func_banco_conta').val(func.banco_conta || '');
                $('#func_observacoes_complementares').val(func.observacoes_complementares || '');

                // Filhos
                if (func.filhos_menores) {
                    try {
                        const filhos = typeof func.filhos_menores === 'string' ? JSON.parse(func.filhos_menores) : func.filhos_menores;
                        if (Array.isArray(filhos)) {
                            filhos.forEach((f, idx) => {
                                if (idx < 3) {
                                    $(`#container-filhos > div:eq(${idx}) .filho-nome`).val(f.nome || '');
                                    $(`#container-filhos > div:eq(${idx}) .filho-nasc`).val(f.nasc || '');
                                    $(`#container-filhos > div:eq(${idx}) .filho-cpf`).val(f.cpf || '');
                                }
                            });
                        }
                    } catch (e) {
                        console.error("Erro ao processar filhos:", e);
                    }
                }

                // Dependentes
                if (func.dependentes_ir) {
                    try {
                        const deps = typeof func.dependentes_ir === 'string' ? JSON.parse(func.dependentes_ir) : func.dependentes_ir;
                        if (Array.isArray(deps)) {
                            deps.forEach((d, idx) => {
                                if (idx < 3) {
                                    $(`#container-dependentes > div:eq(${idx}) .dep-nome`).val(d.nome || '');
                                    $(`#container-dependentes > div:eq(${idx}) .dep-nasc`).val(d.nasc || '');
                                    $(`#container-dependentes > div:eq(${idx}) .dep-cpf`).val(d.cpf || '');
                                }
                            });
                        }
                    } catch (e) {
                        console.error("Erro ao processar dependentes:", e);
                    }
                }

                // Sync Termo Spans - Envolvido em try-catch para segurança extra
                try {
                    console.log('Preenchendo dados básicos do Termo...');
                    $('#termo_nome').text(func.nome || '');
                    $('#termo_cpf').text(func.cpf || '');
                    $('#termo_matricula').text(func.matricula || '');
                    $('#termo_servidor_nome').val(func.nome || '');
                    $('#termo_servidor_cargo').val(func.cargo_funcao || '');

                    // Carregar dados específicos do Termo (JSON)
                    if (func.termo_dados) {
                        let termo = func.termo_dados;
                        console.log('Processando termo_dados técnico...');
                        
                        // Tratamento robusto para codificação JSON múltipla ou simples
                        while (typeof termo === 'string' && (termo.trim().startsWith('{') || termo.trim().startsWith('['))) {
                            try { termo = JSON.parse(termo); } catch(e) { break; }
                        }
                        
                        if (termo && typeof termo === 'object') {
                            if (termo.orgao) $('#termo_orgao').val(termo.orgao);
                            if (termo.cnpj) $('#termo_cnpj').val(termo.cnpj);
                            if (termo.orgao_extra) $('#termo_orgao_extra').val(termo.orgao_extra);
                            if (termo.dpo_nome) $('#termo_dpo_nome').val(termo.dpo_nome);
                            if (termo.dpo_contato) $('#termo_dpo_contato').val(termo.dpo_contato);
                            if (termo.dpo_extra) $('#termo_dpo_extra').val(termo.dpo_extra);
                            if (termo.ciencia_texto) $('#termo_ciencia_texto').val(termo.ciencia_texto);
                            if (termo.ciencia_extra) $('#termo_ciencia_extra').val(termo.ciencia_extra);
                            if (termo.local_data) $('#termo_local_data').val(termo.local_data);
                            if (termo.servidor_nome) $('#termo_servidor_nome').val(termo.servidor_nome);
                            if (termo.servidor_cargo) $('#termo_servidor_cargo').val(termo.servidor_cargo);
                            if (termo.responsavel_nome) $('#termo_responsavel_nome').val(termo.responsavel_nome);
                            if (termo.responsavel_cargo) $('#termo_responsavel_cargo').val(termo.responsavel_cargo);
                            if (termo.crh_nome) $('#termo_crh_nome').val(termo.crh_nome);
                            if (termo.crh_cargo) $('#termo_crh_cargo').val(termo.crh_cargo);
                            if (termo.exibir_assinatura !== undefined) $('#exibir_linha_assinatura').prop('checked', termo.exibir_assinatura);
                        }
                    }
                } catch (e) {
                    console.error("Erro ao processar dados da aba Termo:", e);
                }

                // Grade de Horários
                if (func.grade_horarios) {
                    try {
                        const grade = typeof func.grade_horarios === 'string' ? JSON.parse(func.grade_horarios) : func.grade_horarios;
                        window.pending_grade = grade; // Armazena para aplicar após carregar selects
                        aplicarGradeHorarios(grade);
                    } catch (e) {
                        console.error("Erro ao processar grade_horarios:", e);
                    }
                }

            }
        } catch (e) {
            Swal.fire('Erro', 'Falha ao carregar dados do funcionário.', 'error');
        }
    }

    // --- Lógica da Câmera Inline ---
    let videoStreamForm = null;
    let modelsLoadedForm = false;

    async function loadFaceModelsForm() {
        if (modelsLoadedForm) return;
        const MODEL_URL = 'https://justadudewhohacks.github.io/face-api.js/models';
        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);
            modelsLoadedForm = true;
            console.log("Modelos Face-API carregados no form.");
        } catch (e) {
            console.error("Erro ao carregar modelos Face-API:", e);
        }
    }

    async function iniciarCameraForm() {
        const video = document.getElementById('videoFeedForm');
        document.getElementById('func_foto_preview').classList.add('hidden');
        document.getElementById('func_foto_placeholder').classList.add('hidden');
        document.getElementById('btn_ativar_camera').classList.add('hidden');
        
        document.getElementById('guideFacialForm').classList.remove('hidden');
        document.getElementById('guideFacialForm').classList.add('flex');
        
        document.getElementById('post_capture_controls').classList.add('hidden');
        document.getElementById('pre_capture_controls').classList.add('hidden');
        document.getElementById('cam_controls_form').classList.remove('hidden');
        document.getElementById('camFeedbackForm').innerText = "Iniciando câmera e modelos...";

        loadFaceModelsForm();

        try {
            videoStreamForm = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
            video.srcObject = videoStreamForm;
            video.classList.remove('hidden');
            
            video.onloadedmetadata = async () => {
                await video.play();
                document.getElementById('camFeedbackForm').innerText = "Posicione o rosto e clique em Capturar";
            };
        } catch (err) {
            console.error(err);
            document.getElementById('camFeedbackForm').innerText = "Erro ao acessar a câmera.";
        }
    }

    function pararCameraForm() {
        if (videoStreamForm) {
            videoStreamForm.getTracks().forEach(track => track.stop());
            videoStreamForm = null;
        }
        document.getElementById('videoFeedForm').classList.add('hidden');
        document.getElementById('guideFacialForm').classList.add('hidden');
        document.getElementById('guideFacialForm').classList.remove('flex');
        document.getElementById('cam_controls_form').classList.add('hidden');
        document.getElementById('camFeedbackForm').innerText = "";
        
        // Show preview if exists, else placeholder
        if ($('#func_foto_preview').attr('src') && $('#func_foto_preview').attr('src') !== '') {
            $('#func_foto_preview').removeClass('hidden');
        } else {
            $('#func_foto_placeholder').removeClass('hidden');
        }
        document.getElementById('btn_ativar_camera').classList.remove('hidden');
        document.getElementById('pre_capture_controls').classList.remove('hidden');
        document.getElementById('post_capture_controls').classList.add('hidden');
    }

    async function capturarFacialForm() {
        if (!videoStreamForm) return;
        const video = document.getElementById('videoFeedForm');
        const canvas = document.getElementById('videoCanvasForm');
        
        const container = document.getElementById('camera_container_form');
        const targetRatio = container.clientWidth / container.clientHeight;

        const vW = video.videoWidth;
        const vH = video.videoHeight;
        const vRatio = vW / vH;

        let sWidth = vW;
        let sHeight = vH;
        let sX = 0;
        let sY = 0;

        // Redimensionamento inteligente: máximo 640px mantendo proporção
        const MAX_WIDTH = 640;
        const MAX_HEIGHT = 640;
        let finalWidth = sWidth;
        let finalHeight = sHeight;

        if (finalWidth > MAX_WIDTH) {
            finalWidth = MAX_WIDTH;
            finalHeight = (sHeight * MAX_WIDTH) / sWidth;
        }
        if (finalHeight > MAX_HEIGHT) {
            finalHeight = MAX_HEIGHT;
            finalWidth = (sWidth * MAX_HEIGHT) / sHeight;
        }

        canvas.width = finalWidth;
        canvas.height = finalHeight;
        
        const ctx = canvas.getContext('2d');
        // Espelhar a imagem corretamente
        ctx.translate(finalWidth, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, sX, sY, sWidth, sHeight, 0, 0, finalWidth, finalHeight);
        ctx.setTransform(1, 0, 0, 1, 0, 0); // Reset transform

        let base64Image = canvas.toDataURL('image/jpeg', 0.7);
        document.getElementById('btnCapturarFacial').innerText = "Analisando Face...";
        document.getElementById('btnCapturarFacial').disabled = true;

        const img = new Image();
        img.src = base64Image;
        img.onload = async () => {
            if (!modelsLoadedForm) {
                document.getElementById('camFeedbackForm').innerText = "Aguarde os modelos carregarem...";
                document.getElementById('btnCapturarFacial').innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Capturar Face`;
                document.getElementById('btnCapturarFacial').disabled = false;
                return;
            }

            const detection = await faceapi.detectSingleFace(img, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
            if (detection) {
                // Sucesso
                $('#func_foto_base64').val(base64Image);
                $('#func_facial_descriptor').val(JSON.stringify(Array.from(detection.descriptor)));
                
                $('#func_foto_preview').attr('src', base64Image);
                $('#termo_foto_preview').attr('src', base64Image).removeClass('hidden');
                $('#termo_foto_placeholder').addClass('hidden');
                $('#termo_foto_feedback').removeClass('hidden');

                document.getElementById('camFeedbackForm').innerText = "Face capturada e mapeada temporariamente! Salve o formulário para efetivar.";
                
                pararCameraForm();
                document.getElementById('post_capture_controls').classList.remove('hidden');
                document.getElementById('pre_capture_controls').classList.add('hidden');
                document.getElementById('btn_ativar_camera').classList.add('hidden');
            } else {
                // Falha
                document.getElementById('camFeedbackForm').innerText = "Rosto não detectado. Tente novamente.";
                document.getElementById('btnCapturarFacial').innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Tentar Novamente`;
                document.getElementById('btnCapturarFacial').disabled = false;
            }
        };
    }

    // --- NOVA LÓGICA DE RECORTE (CROPPER.JS) ---
    let cropperInstance = null;

    function handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            iniciarCrop(e.target.result);
        };
        reader.readAsDataURL(file);
        // Limpa o input para permitir selecionar o mesmo arquivo novamente se necessário
        event.target.value = '';
    }

    function iniciarCropManual() {
        const currentImg = $('#func_foto_preview').attr('src');
        if (currentImg) {
            iniciarCrop(currentImg);
        } else {
            Swal.fire("Aviso", "Capture ou selecione uma foto primeiro.", "warning");
        }
    }

    function iniciarCrop(base64) {
        const modal = document.getElementById('modalCropper');
        const img = document.getElementById('cropperImage');
        
        img.src = base64;
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (cropperInstance) {
            cropperInstance.destroy();
        }

        cropperInstance = new Cropper(img, {
            aspectRatio: 1, // Quadrado perfeito para faces
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.8,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
        });
    }

    function fecharCrop() {
        const modal = document.getElementById('modalCropper');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
    }

    async function confirmarCrop() {
        if (!cropperInstance) return;

        const btn = document.getElementById('btnConfirmarCrop');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processando...';

        const canvas = cropperInstance.getCroppedCanvas({
            width: 600,
            height: 600
        });

        const croppedBase64 = canvas.toDataURL('image/jpeg', 0.8);

        // Atualiza Preview e Input
        $('#func_foto_base64').val(croppedBase64);
        $('#func_foto_preview').attr('src', croppedBase64).removeClass('hidden');
        $('#func_foto_placeholder').addClass('hidden');
        
        // Sincroniza com Termo
        $('#termo_foto_preview').attr('src', croppedBase64).removeClass('hidden');
        $('#termo_foto_placeholder').addClass('hidden');

        // Mostra controles de pós-captura
        $('#post_capture_controls').removeClass('hidden');
        $('#pre_capture_controls').addClass('hidden');
        $('#btn_ativar_camera').addClass('hidden');

        // ANALISAR FACE AUTOMATICAMENTE APÓS O CROP
        await analisarFaceRecortada(croppedBase64);

        btn.disabled = false;
        btn.innerHTML = originalHtml;
        fecharCrop();
    }

    async function analisarFaceRecortada(base64) {
        if (!modelsLoadedForm) {
            await loadFaceModelsForm();
        }

        const img = new Image();
        img.src = base64;
        await new Promise(resolve => img.onload = resolve);

        const detection = await faceapi.detectSingleFace(img, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
        
        if (detection) {
            $('#func_facial_descriptor').val(JSON.stringify(Array.from(detection.descriptor)));
            $('#camFeedbackForm').html('<span class="text-emerald-600">✓ Biometria facial mapeada com sucesso!</span>');
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Face mapeada com sucesso!',
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            $('#func_facial_descriptor').val('');
            $('#camFeedbackForm').html('<span class="text-red-600">✗ Rosto não detectado no recorte. Tente ajustar a área.</span>');
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Não conseguimos detectar um rosto na área selecionada. Por favor, recorte novamente focando bem no rosto.',
                confirmButtonColor: '#0284c7'
            });
        }
    }
    // ----------------------------

    window.toggleHorariosCollapse = function() {
        const container = document.getElementById('container_horarios_grade');
        const icon = document.getElementById('icon_horarios_toggle');
        const label = document.getElementById('label_status_horarios');
        
        if (container.classList.contains('hidden')) {
            container.classList.remove('hidden');
            icon.classList.add('rotate-180');
            label.textContent = 'Aberto';
            label.classList.remove('text-slate-400', 'bg-slate-50');
            label.classList.add('text-indigo-600', 'bg-indigo-50');
        } else {
            container.classList.add('hidden');
            icon.classList.remove('rotate-180');
            label.textContent = 'Oculto';
            label.classList.remove('text-indigo-600', 'bg-indigo-50');
            label.classList.add('text-slate-400', 'bg-slate-50');
        }
    };


    async function salvarFuncionario(e) {
        e.preventDefault();
        const btnSave = $(e.currentTarget).find('button[type="submit"]');
        setLoading(btnSave, true);

        const id = $('#func_id').val();
        const method = id ? 'PUT' : 'POST';

        // Bloquear se houver duplicidade detectada
        if (window._duplicadoCpf || window._duplicadoMatricula) {
            const campo = window._duplicadoCpf ? 'CPF' : 'Matrícula';
            Swal.fire({
                title: 'Cadastro Duplicado',
                text: `O ${campo} informado já pertence a outro funcionário cadastrado. Verifique os campos destacados em vermelho.`,
                icon: 'error',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Entendido'
            });
            setLoading(btnSave, false);
            return;
        }

        // Início do processamento dos campos


        // Validação da Grade de Horários (Obrigatória)
        const grade = {};
        const dias = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
        dias.forEach(d => {
            const p1 = $(`input[data-day="${d}"][data-ponto="p1"]`).val();
            const p2 = $(`input[data-day="${d}"][data-ponto="p2"]`).val();
            const p3 = $(`input[data-day="${d}"][data-ponto="p3"]`).val();
            const p4 = $(`input[data-day="${d}"][data-ponto="p4"]`).val();
            const h_id = $(`.select-horario-grade[data-day="${d}"]`).val();
            
            if (p1 || p2 || p3 || p4 || h_id) {
                grade[d] = {
                    p1: p1 || "",
                    p2: p2 || "",
                    p3: p3 || "",
                    p4: p4 || "",
                    horario_id: h_id || null
                };
            }
        });

        // Grade é opcional - apenas avisa se estiver vazia, mas não bloqueia

        const payload = {
            id: id,
            nome: $('#func_nome').val(),
            matricula: $('#func_matricula').val(),
            setor: $('#func_setor').val(),
            setor2: $('#func_setor2').val(),
            cpf: $('#func_cpf').val(),
            celular: $('#func_celular').val(),
            id_horario: $('#func_horario').val(),
            metodos_acesso: $('input[name="metodos"]:checked').map(function() { return this.value; }).get(),
            senha: $('#func_senha').val(),
            lat_permitida: $('#func_lat').val(),
            long_permitida: $('#func_lng').val(),
            area_geofencing: $('#func_area_geofencing').val(),
            distancia_max_permitida: $('#func_dist_max').val(),
            
            turno1_inicio: $('#func_turno1_inicio').val(),
            turno1_fim: $('#func_turno1_fim').val(),
            turno2_inicio: $('#func_turno2_inicio').val(),
            turno2_fim: $('#func_turno2_fim').val(),
            lat_permitida_2: $('#func_lat_2').val(),
            long_permitida_2: $('#func_lng_2').val(),
            area_geofencing_2: $('#func_area_geofencing_2').val(),
            distancia_max_permitida_2: $('#func_dist_max_2').val(),
            
            grade_horarios: grade,
            is_exonerado: $('#func_is_exonerado').is(':checked') ? 1 : 0,
            
            foto_base64: $('#func_foto_base64').val(),
            facial_descriptor: $('#func_facial_descriptor').val(),
            
            // Ficha Cadastral Tab
            nome_social: $('#func_nome_social').val(),
            unidade_trabalho: $('#func_unidade_trabalho').val(),
            tipo_contratacao: $('#func_tipo_contratacao').val(),
            cargo_funcao: $('#func_cargo_funcao').val(),
            data_admissao: $('#func_data_admissao').val(),
            data_exercicio: $('#func_data_exercicio').val(),
            carga_horaria: $('#func_carga_horaria').val(),
            turno_trabalho: $('#func_turno_trabalho').val(),
            endereco: $('#func_endereco').val(),
            endereco_numero: $('#func_endereco_numero').val(),
            endereco_complemento: $('#func_endereco_complemento').val(),
            endereco_bairro: $('#func_endereco_bairro').val(),
            endereco_cep: $('#func_endereco_cep').val(),
            endereco_municipio: $('#func_endereco_municipio').val(),
            endereco_uf: $('#func_endereco_uf').val(),
            telefone_fixo: $('#func_telefone_fixo').val(),
            endereco_email: $('#func_endereco_email').val(),
            data_nascimento: $('#func_data_nascimento').val(),
            naturalidade: $('#func_naturalidade').val(),
            naturalidade_uf: $('#func_naturalidade_uf').val(),
            nacionalidade: $('#func_nacionalidade').val(),
            estado_civil: $('#func_estado_civil').val(),
            sexo: $('#func_sexo').val(),
            raca_cor: $('#func_raca_cor').val(),
            deficiencia: $('#func_deficiencia').val(),
            deficiencia_grau: $('#func_deficiencia_grau').val(),
            deficiencia_tipo: $('#func_deficiencia_tipo').val(),
            deficiencia_cid: $('#func_deficiencia_cid').val(),
            tipo_sanguineo: $('#func_tipo_sanguineo').val(),
            pis_pasep: $('#func_pis_pasep').val(),
            carteira_conselheiro: $('#func_carteira_conselheiro').val(),
            rg_numero: $('#func_rg_numero').val(),
            rg_orgao: $('#func_rg_orgao').val(),
            rg_data_emissao: $('#func_rg_data_emissao').val(),
            cnh_numero: $('#func_cnh_numero').val(),
            cnh_categoria: $('#func_cnh_categoria').val(),
            cnh_validade: $('#func_cnh_validade').val(),
            titulo_eleitor_numero: $('#func_titulo_eleitor_numero').val(),
            titulo_eleitor_zona: $('#func_titulo_eleitor_zona').val(),
            titulo_eleitor_secao: $('#func_titulo_eleitor_secao').val(),
            ctps_numero: $('#func_ctps_numero').val(),
            ctps_serie: $('#func_ctps_serie').val(),
            ctps_uf: $('#func_ctps_uf').val(),
            ctps_data_emissao: $('#func_ctps_data_emissao').val(),
            reservista_numero: $('#func_reservista_numero').val(),
            reservista_serie: $('#func_reservista_serie').val(),
            escolaridade: $('#func_escolaridade').val(),
            nome_pai: $('#func_nome_pai').val(),
            nome_mae: $('#func_nome_mae').val(),
            nome_conjuge: $('#func_nome_conjuge').val(),
            data_nascimento_conjuge: $('#func_data_nascimento_conjuge').val(),
            nacionalidade_conjuge: $('#func_nacionalidade_conjuge').val(),
            naturalidade_conjuge: $('#func_naturalidade_conjuge').val(),
            naturalidade_uf_conjuge: $('#func_naturalidade_uf_conjuge').val(),
            banco_nome: $('#func_banco_nome').val(),
            banco_agencia: $('#func_banco_agencia').val(),
            banco_conta: $('#func_banco_conta').val(),
            observacoes_complementares: $('#func_observacoes_complementares').val(),

            // JSON fields for children and dependents
            filhos_menores: $('#container-filhos > div').map(function() {
                const nome = $(this).find('.filho-nome').val();
                if (!nome) return null;
                return {
                    nome: nome,
                    nasc: $(this).find('.filho-nasc').val(),
                    cpf: $(this).find('.filho-cpf').val()
                };
            }).get().filter(x => x !== null),

            dependentes_ir: $('#container-dependentes > div').map(function() {
                const nome = $(this).find('.dep-nome').val();
                if (!nome) return null;
                return {
                    nome: nome,
                    nasc: $(this).find('.dep-nasc').val(),
                    cpf: $(this).find('.dep-cpf').val()
                };
            }).get().filter(x => x !== null),

            // Termo de Autorização
                termo_dados: {
                    orgao: $('#termo_orgao').val(),
                    cnpj: $('#termo_cnpj').val(),
                    orgao_extra: $('#termo_orgao_extra').val(),
                    dpo_nome: $('#termo_dpo_nome').val(),
                    dpo_contato: $('#termo_dpo_contato').val(),
                    dpo_extra: $('#termo_dpo_extra').val(),
                    ciencia_texto: $('#termo_ciencia_texto').val(),
                    ciencia_extra: $('#termo_ciencia_extra').val(),
                    local_data: $('#termo_local_data').val(),
                    servidor_nome: $('#termo_servidor_nome').val(),
                    servidor_cargo: $('#termo_servidor_cargo').val(),
                    responsavel_nome: $('#termo_responsavel_nome').val(),
                    responsavel_cargo: $('#termo_responsavel_cargo').val(),
                    crh_nome: $('#termo_crh_nome').val(),
                    crh_cargo: $('#termo_crh_cargo').val(),
                    exibir_assinatura: $('#exibir_linha_assinatura').is(':checked')
                },

                // Grade de Horários (Já calculada acima)
                grade_horarios: grade
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
                    icon: 'success',
                    title: 'Sucesso!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    if (method === 'POST' && data.id) {
                        window.location.href = 'funcionario_form.php?id=' + data.id;
                    }
                    // Se for PUT, não redireciona, permanece na página conforme solicitado.
                });
            } else {
                Swal.fire('Ops!', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
        } finally {
            setLoading(btnSave, false);
        }

    }

    $('input[name="metodos"]').on('change', function() {
        if ($(this).val() === 'senha') {
            if (this.checked) $('#containerSenha').removeClass('hidden');
            else $('#containerSenha').addClass('hidden');
        }
    });

    function toggleSenhaVisual(btn) {
        const input = document.getElementById('func_senha');
        const icon = btn.querySelector('svg');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />`;
        } else {
            input.type = 'password';
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
        }
    }

    function capturarLocalizacaoAdmin(e) {
        e.preventDefault();
        const btn = $(e.currentTarget);
        btn.prop('disabled', true).html('Obtendo...');
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                $('#func_lat').val(pos.coords.latitude.toFixed(8));
                $('#func_lng').val(pos.coords.longitude.toFixed(8));
                btn.prop('disabled', false).html('<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Usar Meu Local');
            },
            (err) => {
                btn.prop('disabled', false).html('<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Usar Meu Local');
                Swal.fire("Erro", "Erro ao obter localização: " + err.message, "error");
            }
        );
    }

    function capturarLocalizacao2(e) {
        e.preventDefault();
        const btn = $(e.currentTarget);
        btn.prop('disabled', true).html('Obtendo...');
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                $('#func_lat_2').val(pos.coords.latitude.toFixed(8));
                $('#func_lng_2').val(pos.coords.longitude.toFixed(8));
                btn.prop('disabled', false).html('Usar Meu Local');
            },
            (err) => {
                btn.prop('disabled', false).html('Usar Meu Local');
                Swal.fire("Erro", "Erro ao obter localização: " + err.message, "error");
            }
        );
    }

    window.toggleLocal2 = function() {
        const checkbox = document.getElementById('toggle_local_2');
        const container = document.getElementById('container_local_2');
        if (checkbox.checked) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
            // Opcional: limpar os campos do 2º turno se o usuário desativar. (Deixaremos manter para evitar perda de dados por clique acidental)
        }
    };

    window.consultarCEPAdmin = async function() {
        const cepInput = document.getElementById('func_endereco_cep');
        const cep = cepInput.value.replace(/\D/g, '');
        
        if (cep.length !== 8) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'CEP inválido (8 dígitos)', showConfirmButton: false, timer: 3000 });
            return;
        }

        cepInput.classList.add('ring-2', 'ring-brand-500/50');
        
        try {
            const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await res.json();
            
            if (!data.erro) {
                $('#func_endereco').val(data.logradouro || '');
                $('#func_endereco_bairro').val(data.bairro || '');
                $('#func_endereco_municipio').val(data.localidade || '');
                $('#func_endereco_uf').val(data.uf || '');
                
                // Feedback visual
                $('#func_endereco, #func_endereco_bairro, #func_endereco_municipio, #func_endereco_uf').addClass('ring-2 ring-emerald-500/50');
                setTimeout(() => {
                    $('#func_endereco, #func_endereco_bairro, #func_endereco_municipio, #func_endereco_uf').removeClass('ring-2 ring-emerald-500/50');
                }, 2000);
                
                $('#func_endereco_numero').focus();
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

    window.limparDia = function(day) {
        $(`input[data-day="${day}"]`).val('');
        $(`.select-horario-grade[data-day="${day}"]`).val('');
    };

    window.replicarHorarioPrimeiroDia = function() {
        const p1 = $(`input[data-day="segunda"][data-ponto="p1"]`).val();
        const p2 = $(`input[data-day="segunda"][data-ponto="p2"]`).val();
        const p3 = $(`input[data-day="segunda"][data-ponto="p3"]`).val();
        const p4 = $(`input[data-day="segunda"][data-ponto="p4"]`).val();
        const h_id = $(`.select-horario-grade[data-day="segunda"]`).val();

        ['terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'].forEach(d => {
            $(`input[data-day="${d}"][data-ponto="p1"]`).val(p1);
            $(`input[data-day="${d}"][data-ponto="p2"]`).val(p2);
            $(`input[data-day="${d}"][data-ponto="p3"]`).val(p3);
            $(`input[data-day="${d}"][data-ponto="p4"]`).val(p4);
            $(`.select-horario-grade[data-day="${d}"]`).val(h_id);
        });
        
        // Feedback visual
        $('[data-day-card]').addClass('ring-2 ring-indigo-500/50');
        setTimeout(() => {
            $('[data-day-card]').removeClass('ring-2 ring-indigo-500/50');
        }, 1000);
    };

    async function loadTiposContratacao(selectedVal = null) {
        try {
            const res = await fetch('../../api/tipos_contratacao.php');
            const { data } = await res.json();
            const select = $('#func_tipo_contratacao');
            select.empty().append('<option value="">Selecione...</option>');
            data.forEach(item => {
                select.append(`<option value="${item.nome}">${item.nome}</option>`);
            });
            if (selectedVal) select.val(selectedVal);
        } catch (error) {
            console.error('Erro ao carregar tipos de contratação:', error);
        }
    }

    async function novoTipoContratacao() {
        const { value: novoTipo } = await Swal.fire({
            title: 'Novo Tipo de Contratação',
            input: 'text',
            inputLabel: 'Digite o nome do novo tipo',
            inputPlaceholder: 'Ex: Terceirizado',
            showCancelButton: true,
            confirmButtonColor: '#0ea5e9',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Cadastrar',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) return 'Você precisa digitar um nome!';
            }
        });

        if (novoTipo) {
            const confirmBtn = Swal.getConfirmButton();
            setLoading(confirmBtn, true);
            try {
                const res = await fetch('../../api/tipos_contratacao.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nome: novoTipo })
                });
                const data = await res.json();
                setLoading(confirmBtn, false);
                if (data.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
                    await loadTiposContratacao(novoTipo);
                } else {
                    Swal.fire('Erro', data.message, 'error');
                }
            } catch (error) {
                setLoading(confirmBtn, false);
                Swal.fire('Erro', 'Falha ao cadastrar novo tipo.', 'error');
            }
        }
    }


    window.toggleDeficienciaFields = function() {
        const isDeficiente = $('#func_deficiencia').val() === 'true';
        if (isDeficiente) {
            $('#wrapper_deficiencia').removeClass('hidden');
        } else {
            $('#wrapper_deficiencia').addClass('hidden');
            $('#func_deficiencia_grau').val('');
            $('#func_deficiencia_tipo').val('');
            $('#func_deficiencia_cid').val('');
        }
    };
