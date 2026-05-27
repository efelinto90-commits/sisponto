<?php include 'layout/header.php'; 
$isAdmin = ($_SESSION['user_level'] ?? 3) == 1;
$isSuper = $isAdmin || in_array(strtolower(trim($_SESSION['user_name'] ?? '')), ['corsin', 'crh']) || in_array(strtolower(trim($_SESSION['user_setor'] ?? '')), ['corsin', 'crh']);
?>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<!-- Cropper.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<div class="max-w-[1600px] mx-auto px-4 pb-8">
    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        
        <!-- Header da Página -->
        <div class="px-10 py-8 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight" id="pageTitle">Cadastrar Funcionário</h1>
                <p class="text-base text-slate-500 mt-1">Gestão completa de registros e ficha funcional E-Social.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="funcionarios.php" class="group flex items-center gap-2 px-4 py-2 text-slate-500 hover:text-brand-600 transition-all bg-white hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-200 shadow-sm">
                    <svg class="h-5 w-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span class="text-sm font-bold">Fechar</span>
                </a>
            </div>
        </div>

        <!-- Abas de Navegação -->
        <div class="px-10 border-b border-slate-100 flex gap-10 bg-white">
            <button type="button" onclick="switchTab('tab-acesso')" id="btn-tab-acesso"
                class="py-5 px-1 text-sm font-black border-b-2 border-brand-600 text-brand-600 transition-all uppercase tracking-widest leading-none">
                Informações de Acesso
            </button>
            <button type="button" onclick="switchTab('tab-ficha')" id="btn-tab-ficha"
                class="py-5 px-1 text-sm font-black border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all uppercase tracking-widest leading-none">
                Ficha Cadastral (E-Social)
            </button>
            <button type="button" onclick="switchTab('tab-termo')" id="btn-tab-termo"
                class="py-5 px-1 text-sm font-black border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all uppercase tracking-widest leading-none">
                Termo de Autorização
            </button>
            <button type="button" onclick="switchTab('tab-ferias')" id="btn-tab-ferias"
                class="py-5 px-1 text-sm font-black border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all uppercase tracking-widest leading-none">
                Controle de Férias
            </button>
            <button type="button" onclick="switchTab('tab-folga')" id="btn-tab-folga"
                class="py-5 px-1 text-sm font-black border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all uppercase tracking-widest leading-none">
                Controle de Folga Eleitoral
            </button>
        </div>

        <form id="formFuncionario" onsubmit="salvarFuncionario(event)" class="px-8 py-8 space-y-6">
            <input type="hidden" id="func_id" value="<?php echo $_GET['id'] ?? ''; ?>">

            <!-- Conteúdo Aba 1: Acesso -->
            <div id="tab-acesso" class="tab-content">
                <div class="px-10 py-10 space-y-12">
                    
                    <!-- Bloco Perfil e Identificação -->
                    <section>
                        <div class="flex flex-col lg:flex-row gap-12 items-start">
                            <div class="flex-none mx-auto lg:mx-0 flex flex-col items-center">
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-4 text-center">Foto / Biometria Facial</label>
                                <div id="camera_container_form" class="w-48 h-48 rounded-[2.5rem] bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden shadow-inner group relative transition-all hover:border-brand-300 mb-2">
                                    <video id="videoFeedForm" autoplay playsinline muted class="absolute inset-0 w-full h-full object-cover hidden z-10" style="transform: scaleX(-1);"></video>
                                    <img id="func_foto_preview" src="" class="w-full h-full object-cover hidden z-20 relative">
                                    <div id="func_foto_placeholder" class="text-slate-300 z-0 relative">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div id="btn_ativar_camera" class="absolute inset-0 bg-brand-600/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center z-30 cursor-pointer" onclick="iniciarCameraForm()">
                                        <span class="text-xs font-bold text-brand-600 bg-white px-3 py-1 rounded-full shadow-sm">Ativar Câmera</span>
                                    </div>
                                    
                                    <div id="guideFacialForm" class="absolute inset-0 z-20 pointer-events-none hidden items-center justify-center">
                                        <div class="w-32 h-40 border-2 border-dashed border-indigo-400 rounded-[50%] opacity-80 shadow-[0_0_0_9999px_rgba(0,0,0,0.5)]"></div>
                                    </div>
                                </div>
                                <div id="termo_foto_feedback" class="text-[9px] text-brand-600 font-bold mt-1 hidden italic">Foto sincronizada com o termo.</div>

                                <div id="cam_controls_form" class="hidden flex flex-col gap-2 w-full mt-2">
                                    <button type="button" id="btnCapturarFacial" onclick="capturarFacialForm()" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-bold shadow-sm transition-all text-xs flex justify-center items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Capturar Face
                                    </button>
                                    <button type="button" onclick="pararCameraForm()" class="w-full px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold transition-all text-xs flex justify-center items-center gap-2">
                                        Cancelar
                                    </button>
                                </div>

                                <div id="post_capture_controls" class="hidden flex flex-col gap-2 w-full mt-2">
                                    <button type="button" onclick="iniciarCropManual()" class="w-full px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl font-bold shadow-sm transition-all text-xs flex justify-center items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg> Editar / Recortar
                                    </button>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" onclick="iniciarCameraForm()" class="px-2 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold transition-all text-[10px] flex justify-center items-center gap-1">
                                            Nova Foto
                                        </button>
                                        <button type="button" onclick="$('#input_foto_file').click()" class="px-2 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold transition-all text-[10px] flex justify-center items-center gap-1">
                                            Fazer Upload
                                        </button>
                                    </div>
                                </div>

                                <div id="pre_capture_controls" class="flex flex-col gap-2 w-full mt-2">
                                    <input type="file" id="input_foto_file" class="hidden" accept="image/*" onchange="handleFileUpload(event)">
                                    <button type="button" onclick="$('#input_foto_file').click()" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 hover:bg-white hover:border-brand-300 text-slate-600 rounded-xl font-bold transition-all text-xs flex justify-center items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-5-8l-5-5m0 0l-5 5m5-5v12"></path></svg> Fazer Upload
                                    </button>
                                </div>

                                <p id="camFeedbackForm" class="text-[10px] font-bold text-center mt-2 text-slate-500 w-48"></p>

                                <input type="hidden" id="func_foto_base64">
                                <input type="hidden" id="func_facial_descriptor">
                                <canvas id="videoCanvasForm" class="hidden"></canvas>
                            </div>
                            
                            <div class="flex-1 w-full space-y-8">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                    <div class="md:col-span-4">
                                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Nome Completo</label>
                                        <input type="text" id="func_nome" required
                                            class="w-full px-5 py-4 bg-slate-50/50 border border-slate-200 rounded-2xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none placeholder:text-slate-400" placeholder="Nome completo do colaborador">
                                    </div>
                                    <div class="md:col-span-2">
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">Matrícula</label>
                                            <button type="button" id="btn_gerar_matricula" onclick="gerarMatricula(this)" class="text-[10px] font-bold text-brand-600 hover:text-brand-700 uppercase tracking-tighter transition-colors flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                                Gerar
                                            </button>
                                        </div>
                                        <input type="text" id="func_matricula" required
                                            class="w-full px-5 py-4 bg-slate-50/50 border border-slate-200 rounded-2xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none uppercase placeholder:text-slate-400" placeholder="ID-000">
                                        <div id="feedback_matricula" class="hidden mt-1.5 flex items-center gap-1.5 text-xs font-bold"></div>
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Setor Principal</label>
                                        <input type="text" id="func_setor" required
                                            class="w-full px-5 py-4 bg-slate-50/50 border border-slate-200 rounded-2xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none placeholder:text-slate-400" placeholder="Setor/Departamento">
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Segundo Setor</label>
                                        <input type="text" id="func_setor2"
                                            class="w-full px-5 py-4 bg-slate-50/50 border border-slate-200 rounded-2xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none placeholder:text-slate-400" placeholder="Setor Secundário (Opcional)">
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">CPF <span class="text-[10px] font-bold text-slate-300 ml-1">(APENAS NÚMEROS)</span></label>
                                        <input type="text" id="func_cpf" required maxlength="11" minlength="11"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);"
                                            class="w-full px-5 py-4 bg-slate-50/50 border border-slate-200 rounded-2xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none placeholder:text-slate-400"
                                            placeholder="000.000.000-00">
                                        <div id="feedback_cpf" class="hidden mt-1.5 flex items-center gap-1.5 text-xs font-bold"></div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Celular / WhatsApp</label>
                                        <input type="text" id="func_celular" required maxlength="12" minlength="12"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12);"
                                            class="w-full px-5 py-4 bg-slate-50/50 border border-slate-200 rounded-2xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none placeholder:text-slate-400"
                                            placeholder="(00) 0 0000-0000">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Horário e Segurança -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        <section class="space-y-6">
                            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                                <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center text-brand-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <h2 class="text-sm font-black text-slate-700 uppercase tracking-widest">Jornada de Trabalho</h2>
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Horário Vinculado <span class="text-[10px] text-slate-300 ml-1">(OPCIONAL)</span></label>
                                <select id="func_horario"
                                    class="w-full px-5 py-4 bg-slate-50/50 border border-slate-200 rounded-2xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none cursor-pointer hover:bg-slate-100/50 appearance-none">
                                    <option value="">Livre / Sem horário fixo</option>
                                </select>
                            </div>
                        </section>
                        
                        <section class="space-y-6">
                            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                                <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center text-brand-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                </div>
                                <h2 class="text-sm font-black text-slate-700 uppercase tracking-widest">Controles de Acesso</h2>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <?php
                                $metodos = [
                                    ['val' => 'senha', 'label' => 'Senha', 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                                    ['val' => 'facial', 'label' => 'Facial', 'icon' => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                ];
                                foreach ($metodos as $m):
                                ?>
                                <label class="relative flex flex-col items-center gap-3 p-4 bg-white border border-slate-200 rounded-2xl cursor-pointer transition-all hover:bg-slate-50 hover:border-brand-200 has-[:checked]:bg-brand-50/50 has-[:checked]:border-brand-500 has-[:checked]:ring-1 has-[:checked]:ring-brand-500 shadow-sm group">
                                    <input type="checkbox" name="metodos" value="<?php echo $m['val']; ?>" class="absolute top-3 right-3 w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                                    <svg class="w-6 h-6 text-slate-400 group-hover:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="<?php echo $m['icon']; ?>"></path>
                                    </svg>
                                    <span class="text-xs font-black text-slate-700 uppercase tracking-wider"><?php echo $m['label']; ?></span>
                                </label>
                                <?php endforeach; ?>

                                <label class="relative flex flex-col items-center gap-3 p-4 bg-white border border-slate-200 rounded-2xl cursor-pointer transition-all hover:bg-slate-50 hover:border-red-200 has-[:checked]:bg-red-50/50 has-[:checked]:border-red-500 has-[:checked]:ring-1 has-[:checked]:ring-red-500 shadow-sm group <?php echo !$isSuper ? 'opacity-60 cursor-not-allowed pointer-events-none' : ''; ?>">
                                    <input type="checkbox" id="func_is_exonerado" onchange="toggleExoneracaoFields()" <?php echo !$isSuper ? 'disabled' : ''; ?> class="absolute top-3 right-3 w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                                    <svg class="w-6 h-6 text-slate-400 group-hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                    </svg>
                                    <span class="text-xs font-black text-slate-700 uppercase tracking-wider text-center">Exonerado / Desativado</span>
                                </label>
                            </div>

                            <div id="wrapper_exoneracao" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4 p-5 bg-red-50/30 border border-red-100 rounded-2xl animate-fade-in mt-2">
                                <div>
                                    <label class="block text-[10px] font-black text-red-600 uppercase tracking-widest mb-1.5 ml-1">Data da Exoneração</label>
                                    <input type="date" id="func_data_exoneracao" class="w-full px-4 py-3 bg-white rounded-xl border border-red-100 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none text-sm font-bold text-slate-700 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-red-600 uppercase tracking-widest mb-1.5 ml-1">Motivo / Observação</label>
                                    <textarea id="func_motivo_exoneracao" rows="1" class="w-full px-4 py-3 bg-white rounded-xl border border-red-100 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none text-sm font-bold text-slate-700 shadow-sm placeholder:text-slate-400" placeholder="Motivo da saída..."></textarea>
                                </div>
                            </div>

                            <div id="containerSenha" class="hidden">
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Definir Senha Numérica</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-5 h-5 transition-colors group-focus-within:text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    </div>
                                    <input type="password" id="func_senha" maxlength="8"
                                        class="w-full pl-14 pr-16 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold tracking-[0.5em] outline-none"
                                        placeholder="******">
                                    <button type="button" onclick="toggleSenhaVisual(this)" class="absolute inset-y-0 right-0 pr-5 flex items-center text-slate-400 hover:text-brand-600 transition-colors z-10">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </button>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-2 font-bold uppercase tracking-widest" id="labelSenhaInfo">Senha de 4 a 8 dígitos numéricos.</p>
                            </div>
                        </section>
                    </div>

                    <!-- Geolocalização -->
                    <section class="space-y-6 pt-6 border-t border-slate-100" id="container_geofencing_fields">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <h2 class="text-sm font-black text-slate-700 uppercase tracking-widest">Geolocalização Permitida</h2>
                            </div>
                        </div>

                        <!-- Checkbox para ativar 2º turno -->
                        <?php if ($isSuper): ?>
                        <div class="flex items-center gap-3 bg-white border border-slate-200 p-4 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors" onclick="document.getElementById('toggle_local_2').click()">
                            <input type="checkbox" id="toggle_local_2" class="w-5 h-5 text-indigo-600 rounded bg-slate-50 border-slate-300 accent-indigo-600" onclick="event.stopPropagation(); toggleLocal2()">
                            <label for="toggle_local_2" class="text-sm font-bold text-slate-700 cursor-pointer pointer-events-none">Adicionar validação de Geofencing para um 2º Turno/Setor</label>
                        </div>
                        <?php endif; ?>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Localidade 1 -->
                            <div class="p-6 bg-slate-50 border border-slate-200 rounded-2xl space-y-4 w-full h-full flex flex-col">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 pb-4">
                                    <h3 class="text-xs font-black text-emerald-600 uppercase tracking-widest flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                        Localidade Principal (1º Turno)
                                    </h3>
                                    <?php if ($isSuper): ?>
                                    <button type="button" onclick="capturarLocalizacaoAdmin(event)" class="px-5 py-2 bg-white hover:bg-emerald-50 text-emerald-600 rounded-xl font-bold transition-all text-xs flex items-center gap-2 border border-emerald-200 shadow-sm">
                                        DETECTAR MEU LOCAL ATUAL
                                    </button>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Início do Turno</label>
                                        <input type="time" id="func_turno1_inicio" <?php echo !$isSuper ? 'disabled' : ''; ?> class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Fim do Turno</label>
                                        <input type="time" id="func_turno1_fim" <?php echo !$isSuper ? 'disabled' : ''; ?> class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none">
                                    </div>
                                </div>

                                <div class="space-y-4 pt-2">
                                    <div class="w-full">
                                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Identificação da Área</label>
                                        <input type="text" id="func_area_geofencing" list="areas_list" <?php echo !$isSuper ? 'disabled' : ''; ?> class="w-full px-5 py-4 bg-white border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none placeholder:text-slate-400" placeholder="Ex: Sede Principal / Almoxarifado">
                                        <datalist id="areas_list"></datalist>
                                    </div>
                                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                        <div class="lg:col-span-1 border-t lg:border-t-0 pt-4 lg:pt-0">
                                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Raio/Dist.<span class="text-[10px] text-slate-300 ml-1">(m)</span></label>
                                            <input type="number" id="func_dist_max" value="200" <?php echo !$isSuper ? 'disabled' : ''; ?> class="w-full px-5 py-4 bg-white border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none">
                                        </div>
                                        <div class="grid grid-cols-2 gap-4 lg:col-span-2 border-t lg:border-t-0 pt-4 lg:pt-0">
                                            <div>
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">Latitude</label>
                                                <input type="text" id="func_lat" class="w-full px-3 py-4 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 font-mono text-xs text-center outline-none" readonly>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">Longitude</label>
                                                <input type="text" id="func_lng" class="w-full px-3 py-4 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 font-mono text-xs text-center outline-none" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Localidade 2 -->
                            <div id="container_local_2" class="p-6 bg-indigo-50/50 border border-indigo-100 rounded-2xl space-y-4 hidden transition-all w-full h-full flex flex-col">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-indigo-200/50 pb-4">
                                    <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                        Localidade Secundária (2º Turno)
                                    </h3>
                                    <?php if ($isSuper): ?>
                                    <button type="button" onclick="capturarLocalizacao2(event)" class="px-5 py-2 bg-white hover:bg-indigo-50 text-indigo-600 rounded-xl font-bold transition-all text-xs flex items-center gap-2 border border-indigo-200 shadow-sm">
                                        DETECTAR MEU LOCAL ATUAL
                                    </button>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-4 rounded-xl border border-indigo-100 shadow-sm">
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Início do Turno</label>
                                        <input type="time" id="func_turno2_inicio" <?php echo !$isSuper ? 'disabled' : ''; ?> class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all text-slate-800 font-bold outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Fim do Turno</label>
                                        <input type="time" id="func_turno2_fim" <?php echo !$isSuper ? 'disabled' : ''; ?> class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-4 focus:indigo-500/10 transition-all text-slate-800 font-bold outline-none">
                                    </div>
                                </div>

                                <div class="space-y-4 pt-2">
                                    <div class="w-full">
                                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Identificação da Área</label>
                                        <input type="text" id="func_area_geofencing_2" list="areas_list" <?php echo !$isSuper ? 'disabled' : ''; ?> class="w-full px-5 py-4 bg-white border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all text-slate-800 font-medium outline-none placeholder:text-slate-400" placeholder="Ex: Setor Externo">
                                    </div>
                                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                        <div class="lg:col-span-1 border-t lg:border-t-0 pt-4 lg:pt-0">
                                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Raio/Dist.<span class="text-[10px] text-slate-300 ml-1">(m)</span></label>
                                            <input type="number" id="func_dist_max_2" value="200" <?php echo !$isSuper ? 'disabled' : ''; ?> class="w-full px-5 py-4 bg-white border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all text-slate-800 font-bold outline-none">
                                        </div>
                                        <div class="grid grid-cols-2 gap-4 lg:col-span-2 border-t lg:border-t-0 pt-4 lg:pt-0">
                                            <div>
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">Latitude</label>
                                                <input type="text" id="func_lat_2" class="w-full px-3 py-4 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 font-mono text-xs text-center outline-none" readonly>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">Longitude</label>
                                                <input type="text" id="func_lng_2" class="w-full px-3 py-4 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 font-mono text-xs text-center outline-none" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div> <!-- Fim Aba 1 -->

            <!-- Conteúdo Aba 2: Ficha Cadastral -->
            <div id="tab-ficha" class="tab-content hidden">
                <div class="px-10 py-10 space-y-12">
                    
                    <!-- Card: Dados Funcionais / Sociais -->
                    <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-8 py-5 bg-slate-50/50 border-b border-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest">Informações Funcionais & Sociais</h3>
                        </div>
                        <div class="p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="lg:col-span-3">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nome Completo</label>
                                <input type="text" id="func_nome_2" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none">
                            </div>
                            <div class="lg:col-span-1">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Matrícula</label>
                                <input type="text" id="func_matricula_2" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none text-center">
                            </div>
                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nome Social</label>
                                <input type="text" id="func_nome_social" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none">
                            </div>
                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Unidade de Trabalho</label>
                                <input type="text" id="func_unidade_trabalho" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tipo de Contratação</label>
                                <div class="flex gap-2">
                                    <select id="func_tipo_contratacao" class="flex-1 px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none appearance-none">
                                        <option value="">Carregando...</option>
                                    </select>
                                    <button type="button" onclick="novoTipoContratacao()" class="px-4 py-3 bg-brand-50 text-brand-600 hover:bg-brand-100 rounded-xl transition-all font-bold border border-brand-100 flex items-center justify-center" title="Adicionar Novo Tipo">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Cargo / Função</label>
                                <input type="text" id="func_cargo_funcao" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Data de Admissão</label>
                                <input type="date" id="func_data_admissao" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Data Exercício</label>
                                <input type="date" id="func_data_exercicio" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Carga Horária</label>
                                <select id="func_carga_horaria" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none appearance-none">
                                    <option value="">Selecione...</option>
                                    <option value="20h">20h</option>
                                    <option value="30h">30h</option>
                                    <option value="40h">40h</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Turno</label>
                                <select id="func_turno_trabalho" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none appearance-none">
                                    <option value="Diurno">Diurno</option>
                                    <option value="Noturno">Noturno</option>
                                    <option value="Revezamento">Revezamento</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <!-- Card: Localização e Contato -->
                    <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-8 py-5 bg-slate-50/50 border-b border-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest">Localização de Residência & Contato</h3>
                        </div>
                        <div class="p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6">
                            <div class="lg:col-span-4">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Logradouro (Rua/Avenida/Praça)</label>
                                <input type="text" id="func_endereco" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none" placeholder="Ex: Rua das Flores, 123">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Número</label>
                                <input type="text" id="func_endereco_numero" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none text-center">
                            </div>
                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">CEP <span class="text-[10px] font-bold text-slate-300 ml-1">(SOMENTE NÚMEROS)</span></label>
                                <div class="flex gap-2">
                                    <input type="text" id="func_endereco_cep" maxlength="8" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);" class="flex-1 px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none text-center" placeholder="00000000">
                                    <button type="button" onclick="window.consultarCEPAdmin()" class="px-4 bg-brand-50 text-brand-600 rounded-xl hover:bg-brand-100 transition-colors border border-brand-100 flex items-center justify-center" title="Buscar CEP">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Complemento</label>
                                <input type="text" id="func_endereco_complemento" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none">
                            </div>
                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Bairro</label>
                                <input type="text" id="func_endereco_bairro" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none">
                            </div>
                            <div class="lg:col-span-1">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">UF</label>
                                <input type="text" id="func_endereco_uf" maxlength="2" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none uppercase text-center">
                            </div>
                            <div class="lg:col-span-1">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Município</label>
                                <input type="text" id="func_endereco_municipio" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none">
                            </div>
                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Telefone Fixo</label>
                                <input type="text" id="func_telefone_fixo" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none">
                            </div>
                            <div class="lg:col-span-4">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">E-mail Pessoal</label>
                                <input type="email" id="func_endereco_email" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none lowercase">
                            </div>
                        </div>
                    </section>

                    <!-- Card: Dados Pessoais & Inclusão -->
                    <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-8 py-5 bg-slate-50/50 border-b border-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center text-orange-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest">Caracterização Pessoal & Diversidade</h3>
                        </div>
                        <div class="p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Data de Nascimento</label>
                                <input type="date" id="func_data_nascimento" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none">
                            </div>
                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Naturalidade (Cidade onde nasceu)</label>
                                <input type="text" id="func_naturalidade" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Pátria (Nacionalidade)</label>
                                <input type="text" id="func_nacionalidade" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">UF Nascto.</label>
                                <input type="text" id="func_naturalidade_uf" maxlength="2" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none uppercase text-center">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Estado Civil</label>
                                <select id="func_estado_civil" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none appearance-none">
                                    <option value="Solteiro(a)">Solteiro(a)</option>
                                    <option value="Casado(a)">Casado(a)</option>
                                    <option value="Divorciado(a)">Divorciado(a)</option>
                                    <option value="Viúvo(a)">Viúvo(a)</option>
                                    <option value="União Estável">União Estável</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Sexo Biológico</label>
                                <select id="func_sexo" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none appearance-none">
                                    <option value="M">Masculino</option>
                                    <option value="F">Feminino</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Raça / Cor</label>
                                <select id="func_raca_cor" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none appearance-none">
                                    <option value="Branca">Branca</option>
                                    <option value="Preta">Preta</option>
                                    <option value="Amarela">Amarela</option>
                                    <option value="Parda">Parda</option>
                                    <option value="Indígena">Indígena</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tipo Sanguíneo</label>
                                <input type="text" id="func_tipo_sanguineo" placeholder="Ex: AB+" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none text-center uppercase">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Portador de Deficiência?</label>
                                <select id="func_deficiencia" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none appearance-none" onchange="toggleDeficienciaFields()">
                                    <option value="false">NÃO</option>
                                    <option value="true">SIM</option>
                                </select>
                            </div>
                            <div id="wrapper_deficiencia" class="lg:col-span-4 grid grid-cols-1 md:grid-cols-3 gap-6 hidden">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Grau</label>
                                    <select id="func_deficiencia_grau" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none appearance-none">
                                        <option value="">Selecione...</option>
                                        <option value="Leve">Leve</option>
                                        <option value="Moderada">Moderada</option>
                                        <option value="Grave">Grave</option>
                                    </select>
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tipo de Deficiência</label>
                                    <input type="text" id="func_deficiencia_tipo" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none" placeholder="Ex: Auditiva, Visual...">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nº do CID</label>
                                    <input type="text" id="func_deficiencia_cid" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none" placeholder="H54.0">
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Card: Documentação Exigida (E-Social) -->
                    <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-8 py-5 bg-slate-50/50 border-b border-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest">Documentação Civil & Escolaridade</h3>
                        </div>
                        <div class="p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="lg:col-span-1">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">PIS / PASEP / NIT</label>
                                <input type="text" id="func_pis_pasep" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none">
                            </div>
                            <div class="lg:col-span-1">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Carteira Conselheiro</label>
                                <input type="text" id="func_carteira_conselheiro" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Identidade (RG)</label>
                                <input type="text" id="func_rg_numero" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none uppercase" placeholder="Número">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Órgão Emissor / UF</label>
                                <input type="text" id="func_rg_orgao" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none uppercase" placeholder="Ex: SSP/PB">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Data de Emissão (RG)</label>
                                <input type="date" id="func_rg_data_emissao" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">CNH (Número)</label>
                                <input type="text" id="func_cnh_numero" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">Cat.</label>
                                    <input type="text" id="func_cnh_categoria" maxlength="2" class="w-full px-3 py-3 bg-slate-50 border border-slate-100 rounded-xl text-slate-800 font-bold uppercase text-center outline-none focus:border-brand-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">Validade</label>
                                    <input type="date" id="func_cnh_validade" class="w-full px-3 py-3 bg-slate-50 border border-slate-100 rounded-xl text-slate-800 font-bold outline-none focus:border-brand-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Título de Eleitor</label>
                                <input type="text" id="func_titulo_eleitor_numero" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">Zona</label>
                                    <input type="text" id="func_titulo_eleitor_zona" class="w-full px-3 py-3 bg-slate-50 border border-slate-100 rounded-xl text-slate-800 font-bold text-center outline-none focus:border-brand-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">Seção</label>
                                    <input type="text" id="func_titulo_eleitor_secao" class="w-full px-3 py-3 bg-slate-50 border border-slate-100 rounded-xl text-slate-800 font-bold text-center outline-none focus:border-brand-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Carteira de Trabalho (CTPS)</label>
                                <input type="text" id="func_ctps_numero" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none" placeholder="Número">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">Série</label>
                                    <input type="text" id="func_ctps_serie" class="w-full px-3 py-3 bg-slate-50 border border-slate-100 rounded-xl text-slate-800 font-bold text-center outline-none focus:border-brand-500 transition-colors">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">UF</label>
                                    <input type="text" id="func_ctps_uf" maxlength="2" class="w-full px-3 py-3 bg-slate-50 border border-slate-100 rounded-xl text-slate-800 font-bold text-center outline-none focus:border-brand-500 transition-colors uppercase">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Data Emissão (CTPS)</label>
                                <input type="date" id="func_ctps_data_emissao" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">Reservista Nº</label>
                                    <input type="text" id="func_reservista_numero" class="w-full px-3 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">Série</label>
                                    <input type="text" id="func_reservista_serie" class="w-full px-3 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none">
                                </div>
                            </div>
                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Escolaridade / Nível de Instrução</label>
                                <select id="func_escolaridade" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none appearance-none">
                                    <option value="">Selecione...</option>
                                    <option value="Fundamental Incompleto">Fundamental Incompleto</option>
                                    <option value="Fundamental Completo">Fundamental Completo</option>
                                    <option value="Médio Incompleto">Médio Incompleto</option>
                                    <option value="Médio Completo">Médio Completo</option>
                                    <option value="Superior Incompleto">Superior Incompleto</option>
                                    <option value="Superior Completo">Superior Completo</option>
                                    <option value="Pós-Graduação">Pós-Graduação</option>
                                    <option value="Mestrado/Doutorado">Mestrado/Doutorado</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <!-- Card: Relações Familiares -->
                    <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-8 py-5 bg-slate-50/50 border-b border-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-pink-50 flex items-center justify-center text-pink-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest">Filiação & Cônjuge</h3>
                        </div>
                        <div class="p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-x-10 gap-y-6">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nome do Pai</label>
                                    <input type="text" id="func_nome_pai" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nome da Mãe</label>
                                    <input type="text" id="func_nome_mae" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none">
                                </div>
                            </div>
                            <div class="space-y-6 md:border-l md:border-slate-100 md:pl-10">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nome do Cônjuge</label>
                                    <input type="text" id="func_nome_conjuge" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Data Nasc. Cônjuge</label>
                                        <input type="date" id="func_data_nascimento_conjuge" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nacionalidade</label>
                                            <input type="text" id="func_nacionalidade_conjuge" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none">
                                        </div>
                                        <div class="lg:col-span-1">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Naturalidade</label>
                                            <input type="text" id="func_naturalidade_conjuge" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none">
                                        </div>
                                        <div class="lg:col-span-1">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">UF</label>
                                            <input type="text" id="func_naturalidade_uf_conjuge" maxlength="2" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none uppercase text-center">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Card: Filhos Menores -->
                    <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-8 py-5 bg-slate-50/50 border-b border-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-cyan-50 flex items-center justify-center text-cyan-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest">Filhos Menores de 14 Anos (E-Social)</h3>
                        </div>
                        <div class="p-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="container-filhos">
                                <?php for($i=1; $i<=3; $i++): ?>
                                <div class="p-6 bg-slate-50/50 rounded-2xl border border-slate-100 relative group transition-all hover:bg-white hover:shadow-md">
                                    <span class="absolute -top-3 -left-3 w-8 h-8 bg-white border border-slate-100 text-slate-400 shadow-sm rounded-xl flex items-center justify-center text-xs font-black group-hover:text-brand-500 transition-colors"><?php echo $i; ?></span>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Nome Completo</label>
                                            <input type="text" class="filho-nome w-full px-4 py-2 bg-white border border-slate-100 rounded-xl text-xs font-medium outline-none focus:border-brand-500 transition-colors">
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Nascimento</label>
                                                <input type="date" class="filho-nasc w-full px-3 py-2 bg-white border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:border-brand-500 transition-colors">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">CPF (>8 anos)</label>
                                                <input type="text" class="filho-cpf w-full px-3 py-2 bg-white border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:border-brand-500 transition-colors">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </section>

                    <!-- Card: Dependentes IR -->
                    <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-8 py-5 bg-slate-50/50 border-b border-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center text-teal-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest">Dependentes para Imposto de Renda</h3>
                        </div>
                        <div class="p-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="container-dependentes">
                                <?php for($i=1; $i<=3; $i++): ?>
                                <div class="p-6 bg-slate-50/50 rounded-2xl border border-slate-100 relative group transition-all hover:bg-white hover:shadow-md">
                                    <span class="absolute -top-3 -left-3 w-8 h-8 bg-white border border-slate-100 text-slate-400 shadow-sm rounded-xl flex items-center justify-center text-xs font-black group-hover:text-brand-500 transition-colors"><?php echo $i; ?></span>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Nome do Dependente</label>
                                            <input type="text" class="dep-nome w-full px-4 py-2 bg-white border border-slate-100 rounded-xl text-xs font-medium outline-none focus:border-brand-500 transition-colors">
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Nascimento</label>
                                                <input type="date" class="dep-nasc w-full px-3 py-2 bg-white border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:border-brand-500 transition-colors">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Documento CPF</label>
                                                <input type="text" class="dep-cpf w-full px-3 py-2 bg-white border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:border-brand-500 transition-colors">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </section>

                    <!-- Card: Dados Bancários & Observações -->
                    <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-8 py-5 bg-slate-50/50 border-b border-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest">Financeiro & Notas do RH</h3>
                        </div>
                        <div class="p-8 grid grid-cols-1 lg:grid-cols-3 gap-10">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Instituição Bancária</label>
                                    <input type="text" id="func_banco_nome" placeholder="Ex: Banco do Brasil" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-bold outline-none">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">Agência</label>
                                        <input type="text" id="func_banco_agencia" class="w-full px-3 py-3 bg-slate-50 border border-slate-100 rounded-xl text-slate-800 font-bold text-center outline-none focus:border-brand-500 transition-colors">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">Conta</label>
                                        <input type="text" id="func_banco_conta" class="w-full px-3 py-3 bg-slate-50 border border-slate-100 rounded-xl text-slate-800 font-bold text-center outline-none focus:border-brand-500 transition-colors">
                                    </div>
                                </div>
                            </div>
                            <div class="lg:col-span-2 lg:border-l lg:border-slate-100 lg:pl-10">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Observações Complementares / Histórico Interno</label>
                                <textarea id="func_observacoes_complementares" rows="6" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-800 font-medium outline-none resize-none placeholder:text-slate-300" placeholder="Anotações internas sobre o colaborador..."></textarea>
                            </div>
                        </div>
                    </section>
                    <!-- Quadro de Horários Semanal (Mandatório) -->
                    <div class="pt-10 border-t border-slate-100">
                        <section class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100 transition-all duration-300">
                            <button type="button" onclick="window.toggleHorariosCollapse()" class="w-full flex items-center justify-between outline-none group mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <div class="text-left">
                                        <h3 class="text-lg font-bold text-slate-800">Quadro de Horários Semanal <span class="text-red-500 ml-1">*</span></h3>
                                        <p class="text-sm text-slate-500">Configure as 4 batidas diárias para o E-Social</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span id="label_status_horarios" class="text-xs font-bold text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-1 rounded-full">Oculto</span>
                                    <svg id="icon_horarios_toggle" class="w-6 h-6 text-slate-400 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </button>

                            <div id="container_horarios_grade" class="hidden space-y-6 pt-6 border-t border-slate-50">
                                <div class="flex items-center justify-between gap-3 pb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Edição Semanal</span>
                                    </div>
                                    <button type="button" onclick="replicarHorarioPrimeiroDia()" class="text-[10px] font-black text-brand-600 hover:text-brand-700 uppercase tracking-widest flex items-center gap-1 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                        Replicar Segunda para todos
                                    </button>
                                </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="grade_horarios_container">
                                <?php
                                $dias_lista = [
                                    'segunda' => 'Segunda',
                                    'terca' => 'Terça',
                                    'quarta' => 'Quarta',
                                    'quinta' => 'Quinta',
                                    'sexta' => 'Sexta',
                                    'sabado' => 'Sábado',
                                    'domingo' => 'Domingo'
                                ];
                                foreach ($dias_lista as $key => $label):
                                ?>
                                <div class="p-6 bg-slate-50/50 border border-slate-200 rounded-3xl space-y-4 transition-all hover:bg-white hover:shadow-xl group border-l-4 border-l-transparent hover:border-l-indigo-500 relative" data-day-card="<?php echo $key; ?>">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-slate-300 group-hover:bg-indigo-500 transition-colors"></span>
                                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest group-hover:text-slate-800 transition-colors"><?php echo $label; ?></span>
                                        </div>
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all">
                                            <button type="button" onclick="abrirModalGeolocalizacao('<?php echo $key; ?>')" class="p-1.5 text-sky-500 hover:bg-sky-50 rounded-lg transition-all" title="Definir Local de Trabalho">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            </button>
                                            <button type="button" onclick="limparDia('<?php echo $key; ?>')" class="p-1.5 text-slate-400 hover:text-red-500 transition-all" title="Limpar Dia">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Indicador de Área de Geolocalização -->
                                    <div class="hidden mb-3" data-day-area-container="<?php echo $key; ?>">
                                        <div class="flex items-center gap-1.5 px-2 py-1 bg-sky-50 text-sky-700 rounded-lg border border-sky-100 text-[9px] font-black uppercase truncate">
                                            <svg class="w-2.5 h-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            <span data-day-area-name="<?php echo $key; ?>" class="truncate text-ellipsis">Nenhum local</span>
                                            <input type="hidden" data-day-area-input="<?php echo $key; ?>" value="">
                                            <button type="button" onclick="removerAreaDia('<?php echo $key; ?>')" class="ml-auto text-sky-400 hover:text-sky-600 transition-colors">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="block text-[8px] font-black text-indigo-400 uppercase tracking-widest mb-1 ml-1">Modelo de Horário</label>
                                        <div class="relative">
                                            <select class="select-horario-grade w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-[10px] font-bold outline-none border-l-4 border-l-indigo-400 focus:border-indigo-500 transition-all appearance-none" data-day="<?php echo $key; ?>">
                                                <option value="">Manual / Nenhum</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-4">
                                        <div class="space-y-1.5">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-tighter">Entrada 1</label>
                                            <input type="time" data-day="<?php echo $key; ?>" data-ponto="p1" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all time-grade-input">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-tighter">Saída 1</label>
                                            <input type="time" data-day="<?php echo $key; ?>" data-ponto="p2" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all time-grade-input">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-tighter">Entrada 2</label>
                                            <input type="time" data-day="<?php echo $key; ?>" data-ponto="p3" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all time-grade-input">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-tighter">Saída 2</label>
                                            <input type="time" data-day="<?php echo $key; ?>" data-ponto="p4" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all time-grade-input">
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                </div>

            </div> <!-- Fim Aba 2 -->
        </div> 

            <!-- Conteúdo Aba 3: Termo de Autorização -->
            <div id="tab-termo" class="tab-content hidden">
                <div class="px-10 py-10 space-y-8">
                    <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden p-8" id="termoPrintArea">
                        <div class="text-center mb-8 pb-4">
                            <h2 class="text-xl font-bold uppercase">TERMO DE CIÊNCIA E CONSENTIMENTO PARA TRATAMENTO DE DADOS BIOMÉTRICOS</h2>
                            <h3 class="text-lg font-semibold uppercase mt-2">SISTEMA WEB DE CONTROLE DE PONTO – ADMINISTRAÇÃO PÚBLICA</h3>
                        </div>
                        
                        <div class="flex flex-col md:flex-row gap-8 items-start mb-10">
                            <div class="flex-1 space-y-6 text-sm text-justify leading-relaxed">
                                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100">
                                    <h4 class="font-bold mb-3 uppercase text-xs text-slate-500 tracking-widest">1. IDENTIFICAÇÃO DO TITULAR</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <p>Nome: <span id="termo_nome" class="font-bold border-b border-slate-300 min-w-[200px] inline-block">________________________________</span></p>
                                        <p>CPF: <span id="termo_cpf" class="font-bold border-b border-slate-300 min-w-[150px] inline-block">___________________________</span></p>
                                    </div>
                                    <p class="mt-3">Matrícula: <span id="termo_matricula" class="font-bold border-b border-slate-300 min-w-[150px] inline-block">___________________________</span></p>
                                </div>
                            </div>
                            <div class="flex-none flex flex-col items-center p-4 bg-slate-50 rounded-2xl border border-slate-100 border-dashed">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Foto Biometria</span>
                                <div class="w-32 h-32 rounded-2xl bg-white border border-slate-200 overflow-hidden flex items-center justify-center shadow-sm">
                                    <img id="termo_foto_preview" src="" class="w-full h-full object-cover hidden">
                                    <div id="termo_foto_placeholder" class="text-slate-200">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6 text-sm text-justify leading-relaxed">
                            <div>
                                <h4 class="font-bold uppercase text-xs text-slate-500 tracking-widest mb-3">2. IDENTIFICAÇÃO DO ÓRGÃO PÚBLICO (CONTROLADOR)</h4>
                                <div class="space-y-3 p-4 bg-slate-50 rounded-xl border border-slate-100">
                                    <div class="flex flex-col md:flex-row md:items-center gap-2">
                                        <span class="text-xs font-bold whitespace-nowrap">Órgão/Entidade:</span>
                                        <input type="text" id="termo_orgao" class="flex-1 bg-transparent border-b border-slate-300 outline-none font-bold text-slate-700" value="FUNAD – CENTRO INTEGRADO DE APOIO À PESSOA COM DEFICIÊNCIA">
                                    </div>
                                    <div class="flex flex-col md:flex-row md:items-center gap-2">
                                        <span class="text-xs font-bold whitespace-nowrap">CNPJ:</span>
                                        <input type="text" id="termo_cnpj" class="flex-1 bg-transparent border-b border-slate-300 outline-none font-bold text-slate-700" value="24.507.865/0001-07" placeholder="Digite o CNPJ">
                                    </div>
                                    <input type="text" id="termo_orgao_extra" class="w-full bg-transparent border-b border-slate-300 outline-none text-slate-700 mt-2" placeholder="Informações adicionais...">
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="font-bold">3. FINALIDADE DO TRATAMENTO</h4>
                                <p class="mt-2">Os dados biométricos faciais serão utilizados para:</p>
                                <ul class="list-disc pl-6 mt-1 space-y-1">
                                    <li>Registro e controle da jornada de trabalho de servidores e colaboradores;</li>
                                    <li>Identificação segura no acesso ao sistema;</li>
                                    <li>Prevenção de fraudes no registro de ponto;</li>
                                    <li>Atendimento ao interesse público e à execução de políticas administrativas.</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="font-bold">4. BASE LEGAL</h4>
                                <p class="mt-2">O tratamento de dados pessoais e biométricos será realizado em conformidade com a Lei nº 13.709/2018 (LGPD), com fundamento em:</p>
                                <ul class="list-disc pl-6 mt-1 space-y-1">
                                    <li>Cumprimento de obrigação legal ou regulatória pela Administração Pública;</li>
                                    <li>Execução de políticas públicas;</li>
                                    <li>Interesse público;</li>
                                    <li>Quando aplicável, consentimento do titular.</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="font-bold">5. DADOS COLETADOS</h4>
                                <p class="mt-2">Serão coletadas imagens faciais, podendo ser convertidas em dados biométricos (templates matemáticos) para autenticação no sistema, não sendo necessariamente armazenadas como imagem convencional.</p>
                            </div>
                            
                            <div>
                                <h4 class="font-bold">6. FORMA DE UTILIZAÇÃO</h4>
                                <p class="mt-2">Os dados serão utilizados exclusivamente no âmbito do sistema de controle de ponto institucional, sendo acessados apenas por servidores autorizados.</p>
                            </div>
                            
                            <div>
                                <h4 class="font-bold">7. COMPARTILHAMENTO DE DADOS</h4>
                                <p class="mt-2">Os dados poderão ser compartilhados:</p>
                                <ul class="list-disc pl-6 mt-1 space-y-1">
                                    <li>Entre órgãos e entidades da Administração Pública, quando necessário;</li>
                                    <li>Para cumprimento de obrigações legais ou judiciais;</li>
                                    <li>Com órgãos de controle e fiscalização.</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="font-bold">8. ARMAZENAMENTO E SEGURANÇA</h4>
                                <p class="mt-2">Serão adotadas medidas técnicas e administrativas para garantir a proteção dos dados pessoais contra acessos não autorizados, perda, alteração ou divulgação indevida.</p>
                            </div>
                            
                            <div>
                                <h4 class="font-bold">9. PRAZO DE RETENÇÃO</h4>
                                <p class="mt-2">Os dados serão mantidos pelo tempo necessário ao cumprimento de sua finalidade e conforme as normas de gestão documental da Administração Pública.</p>
                            </div>
                            
                            <div>
                                <h4 class="font-bold">10. DIREITOS DO TITULAR</h4>
                                <p class="mt-2">Nos termos da LGPD, o titular poderá:</p>
                                <ul class="list-disc pl-6 mt-1 space-y-1">
                                    <li>Confirmar a existência de tratamento;</li>
                                    <li>Acessar seus dados;</li>
                                    <li>Solicitar correção de dados incompletos;</li>
                                    <li>Obter informações sobre o tratamento;</li>
                                    <li>Solicitar anonimização ou eliminação, quando aplicável.</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="font-bold uppercase text-xs text-slate-500 tracking-widest mb-3">11. ENCARREGADO PELO TRATAMENTO DE DADOS (DPO)</h4>
                                <div class="space-y-3 p-4 bg-slate-50 rounded-xl border border-slate-100">
                                    <div class="flex flex-col md:flex-row md:items-center gap-2">
                                        <span class="text-xs font-bold whitespace-nowrap">Contato do Encarregado (DPO):</span>
                                        <input type="text" id="termo_dpo_nome" class="flex-1 bg-transparent border-b border-slate-300 outline-none font-bold text-slate-700" value="Liara Brito Monteiro" placeholder="Nome do Responsável">
                                    </div>
                                    <div class="flex flex-col md:flex-row md:items-center gap-2">
                                        <span class="text-xs font-bold whitespace-nowrap">E-mail/Canal:</span>
                                        <input type="text" id="termo_dpo_contato" class="flex-1 bg-transparent border-b border-slate-300 outline-none font-bold text-slate-700" value="lgpd@funad.pb.gov.br" placeholder="Email ou Telefone">
                                    </div>
                                    <input type="text" id="termo_dpo_extra" class="w-full bg-transparent border-b border-slate-300 outline-none text-slate-700 mt-2" placeholder="Informações adicionais...">
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="font-bold uppercase text-xs text-slate-500 tracking-widest mb-3">12. CIÊNCIA E CONSENTIMENTO</h4>
                                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 space-y-3">
                                    <textarea id="termo_ciencia_texto" rows="3" class="w-full bg-transparent border-none outline-none text-sm text-justify leading-relaxed font-medium text-slate-700 resize-none">Declaro que fui informado(a) de forma clara sobre o tratamento dos meus dados biométricos faciais no âmbito deste órgão público.
Estou ciente de que o tratamento poderá ocorrer independentemente do consentimento, quando fundamentado em obrigação legal ou interesse público, conforme a legislação vigente.</textarea>
                                    <input type="text" id="termo_ciencia_extra" class="w-full bg-transparent border-b border-slate-300 outline-none text-slate-700" placeholder="________________________________________">
                                </div>
                            </div>
                            
                            <div class="mt-8 p-6 bg-slate-50 rounded-2xl border border-slate-100 space-y-4">
                                <h4 class="font-bold uppercase text-xs text-slate-500 tracking-widest mb-2">Assinaturas e Finalização</h4>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Local e data</label>
                                    <input type="text" id="termo_local_data" class="w-full bg-white px-4 py-2 border border-slate-200 rounded-xl outline-none font-bold text-slate-700" value="João Pessoa - PB, <?php echo date('d/m/Y'); ?>">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                                    <div class="space-y-3">
                                        <h5 class="text-[10px] font-black text-brand-600 uppercase tracking-widest">Servidor(a) / Titular</h5>
                                        <div>
                                            <label class="block text-[9px] text-slate-400 uppercase mb-1">Nome Completo</label>
                                            <input type="text" id="termo_servidor_nome" class="w-full bg-white px-3 py-1.5 border border-slate-200 rounded-lg outline-none font-bold text-slate-700 text-sm" placeholder="Nome do Servidor">
                                        </div>
                                        <div>
                                            <label class="block text-[9px] text-slate-400 uppercase mb-1">Cargo/Função</label>
                                            <input type="text" id="termo_servidor_cargo" class="w-full bg-white px-3 py-1.5 border border-slate-200 rounded-lg outline-none font-bold text-slate-700 text-sm" placeholder="Cargo do Servidor">
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <h5 class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Responsável pelo Cadastro - DPO</h5>
                                        <div>
                                            <label class="block text-[9px] text-slate-400 uppercase mb-1">Nome Completo</label>
                                            <input type="text" id="termo_responsavel_nome" class="w-full bg-white px-3 py-1.5 border border-slate-200 rounded-lg outline-none font-bold text-slate-700 text-sm" value="Liara Brito Monteiro" placeholder="Nome do Responsável">
                                        </div>
                                        <div>
                                            <label class="block text-[9px] text-slate-400 uppercase mb-1">Cargo/Função</label>
                                            <input type="text" id="termo_responsavel_cargo" class="w-full bg-white px-3 py-1.5 border border-slate-200 rounded-lg outline-none font-bold text-slate-700 text-sm" value="Encarregada DPO" placeholder="Cargo do Responsável">
                                        </div>
                                    </div>
                                    <div class="space-y-3 border-l md:border-l border-slate-100 md:pl-4">
                                        <h5 class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Encarregado/Chefe do CRH</h5>
                                        <div>
                                            <label class="block text-[9px] text-slate-400 uppercase mb-1">Nome Completo</label>
                                            <input type="text" id="termo_crh_nome" class="w-full bg-white px-3 py-1.5 border border-slate-200 rounded-lg outline-none font-bold text-slate-700 text-sm" placeholder="Nome do Chefe do CRH">
                                        </div>
                                        <div>
                                            <label class="block text-[9px] text-slate-400 uppercase mb-1">Cargo/Função</label>
                                            <input type="text" id="termo_crh_cargo" class="w-full bg-white px-3 py-1.5 border border-slate-200 rounded-lg outline-none font-bold text-slate-700 text-sm" placeholder="Cargo (Ex: Chefe do CRH)">
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 pt-4 border-t border-slate-200 mt-2">
                                    <input type="checkbox" id="exibir_linha_assinatura" checked class="w-4 h-4 text-brand-600 rounded">
                                    <label for="exibir_linha_assinatura" class="text-xs font-bold text-slate-600">Exibir linhas para assinatura manual no impresso</label>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <div class="mt-6 flex justify-end">
                        <button type="button" onclick="imprimirTermo()" class="px-6 py-3 bg-slate-800 hover:bg-slate-700 text-white rounded-xl font-bold transition-all shadow-md flex items-center gap-2 text-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Imprimir Termo
                        </button>
                    </div>
                </div>
            </div> <!-- Fim Aba 3 -->

            <!-- Rodapé do Formulário -->
            <div class="mt-10 pt-8 border-t border-slate-100 flex justify-between items-center">
                <a href="funcionarios.php"
                    class="px-8 py-3 bg-white border border-slate-300 rounded-xl text-slate-700 hover:bg-slate-50 font-bold transition-all text-sm">
                    Voltar para Listagem
                </a>
                <?php if ($isSuper): ?>
                <button type="submit"
                    class="px-10 py-3 bg-brand-600 hover:bg-brand-500 text-white rounded-xl font-bold shadow-lg shadow-brand-200 transition-all flex items-center gap-2 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Salvar Funcionário
                </button>
                <?php endif; ?>
            </div>

            <!-- Aba 4: Controle de Férias -->
            <div id="tab-ferias" class="tab-content hidden">
                <div class="px-10 py-10">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800">Histórico de Férias</h3>
                            <p class="text-sm text-slate-500">Acompanhamento de períodos de descanso e afastamentos por férias.</p>
                        </div>
                    </div>

                    <!-- Seção: Períodos Aquisitivos (Direito) -->
                    <section class="mb-12">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h4 class="text-sm font-black text-slate-700 uppercase tracking-widest flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                                    Períodos Aquisitivos (Direito)
                                </h4>
                                <p class="text-[11px] text-slate-500 mt-1">Registre aqui os períodos que dão direito ao gozo de férias.</p>
                            </div>
                            <button type="button" onclick="adicionarLinhaPeriodo()" class="px-4 py-2 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-xl font-bold transition-all text-xs flex items-center gap-2 border border-amber-200 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                Adicionar Período
                            </button>
                        </div>
                        
                        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/50 border-b border-slate-100">
                                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Período Aquisitivo</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center w-32">Dias de Férias</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest w-40">Status</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center w-24">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="lista-periodos-corpo">
                                    <!-- Linhas dinâmicas via JS -->
                                </tbody>
                            </table>
                            <datalist id="periodos-presets">
                                <option value="2018/2019"></option>
                                <option value="2019/2020"></option>
                                <option value="2020/2021"></option>
                                <option value="2021/2022"></option>
                                <option value="2022/2023"></option>
                                <option value="2023/2024"></option>
                                <option value="2024/2025"></option>
                                <option value="2025/2026"></option>
                                <option value="2026/2027"></option>
                            </datalist>
                            <div id="msg-periodos-vazio" class="py-12 text-center text-slate-400 text-sm hidden">
                                Nenhum período aquisitivo registrado.
                            </div>
                        </div>
                    </section>

                    <div class="flex items-center justify-between mb-8 border-t border-slate-100 pt-10">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800">Período de Gozo (Histórico)</h3>
                            <p class="text-sm text-slate-500">Histórico de afastamentos e férias efetivamente gozadas.</p>
                        </div>
                        <button type="button" onclick="abrirFerias($('#func_id').val(), $('#func_nome').val()); setTimeout(() => $('#ferias_tipo').val('ferias').trigger('change'), 100)"
                            class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold shadow-sm transition-all text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            Registrar Férias
                        </button>
                    </div>
                    
                    <div id="lista-ferias-tab" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Carregado via AJAX -->
                        <div class="col-span-full py-12 text-center bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200">
                            <p class="text-slate-400 font-medium">Nenhum registro encontrado.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aba 5: Controle de Folga Eleitoral -->
            <div id="tab-folga" class="tab-content hidden">
                <div class="px-10 py-10">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800">Folgas Eleitorais</h3>
                            <p class="text-sm text-slate-500">Gestão de compensações por serviços prestados à Justiça Eleitoral.</p>
                        </div>
                        <button type="button" onclick="abrirFerias($('#func_id').val(), $('#func_nome').val()); setTimeout(() => $('#ferias_tipo').val('folga eleitoral').trigger('change'), 100)"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-sm transition-all text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            Registrar Folga
                        </button>
                    </div>

                    <div class="mb-10 p-6 bg-slate-50 rounded-2xl border border-slate-200">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-black text-slate-600 uppercase tracking-widest">Gerenciar Pleitos</h4>
                            <button type="button" onclick="abrirModalPleitosPresets()" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg font-bold transition-all text-[10px] uppercase tracking-wider flex items-center gap-1.5 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Configurar Pleitos
                            </button>
                        </div>
                        <div class="flex items-end gap-4">
                            <div class="flex-1">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nome do Pleito</label>
                                <input type="text" id="novo_pleito_nome" class="w-full h-10 px-3 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-700" placeholder="Ex: Eleições 2024" list="pleitos-presets">
                                <datalist id="pleitos-presets"></datalist>
                            </div>
                            <div class="w-32">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Dias Adquiridos</label>
                                <input type="number" id="novo_pleito_dias" class="w-full h-10 px-3 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-700" min="1" step="1">
                            </div>
                            <button type="button" onclick="salvarPleito()" class="h-10 px-5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-sm shadow-sm transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Adicionar
                            </button>
                        </div>
                        <div class="mt-6">
                            <table class="w-full text-left text-sm" id="tabela-pleitos">
                                <thead>
                                    <tr class="border-b-2 border-slate-200 text-slate-400 uppercase tracking-widest text-[10px] font-black">
                                        <th class="py-2">Pleito</th>
                                        <th class="py-2 text-center w-32">Adquiridos</th>
                                        <th class="py-2 text-center w-32">Gozados</th>
                                        <th class="py-2 text-center w-32">Saldo</th>
                                        <th class="py-2 text-right w-20">Ação</th>
                                    </tr>
                                </thead>
                                <tbody id="lista-pleitos-body" class="divide-y divide-slate-100">
                                    <!-- JS ira preencher -->
                                </tbody>
                            </table>
                            <div id="msg-pleitos-vazio" class="py-8 text-center text-slate-400 text-sm hidden">
                                Nenhum pleito cadastrado para este servidor.
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between mb-8 border-t border-slate-100 pt-10">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800">Histórico de Gozo</h3>
                            <p class="text-sm text-slate-500">Histórico das folgas eleitorais efetivamente gozadas.</p>
                        </div>
                    </div>

                    <div id="lista-folga-tab" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Carregado via AJAX -->
                        <div class="col-span-full py-12 text-center bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200">
                            <p class="text-slate-400 font-medium">Nenhum registro encontrado.</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'ferias_modal.php'; ?>
<script>
    const isAdminOrCRH = <?php echo $isSuper ? 'true' : 'false'; ?>;
    const loggedInOperator = <?php echo json_encode($_SESSION['user_name'] ?? 'Operador'); ?>;
</script>

<script>
    $(function() {
        // Sincronização de campos duplicados entre abas
        $('#func_nome').on('input', function() { $('#func_nome_2').val($(this).val()); });
        $('#func_nome_2').on('input', function() { $('#func_nome').val($(this).val()); });
        $('#func_matricula').on('input', function() { $('#func_matricula_2').val($(this).val()); });
        $('#func_matricula_2').on('input', function() { $('#func_matricula').val($(this).val()); });
        
        // Bloqueio de campos de matrícula/cpf duplicados
        $('#func_cpf').on('blur', function() { verificarDuplicado('cpf', $(this).val(), 'feedback_cpf'); });
        $('#func_matricula').on('blur', function() { verificarDuplicado('matricula', $(this).val(), 'feedback_matricula'); });

        // Carregar os presets de pleitos eleitorais dinamicamente
        carregarPleitosPresets();
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
        $('#btn-tab-acesso, #btn-tab-ficha, #btn-tab-termo, #btn-tab-ferias, #btn-tab-folga').removeClass('border-brand-600 text-brand-600').addClass('border-transparent text-slate-400');
        if (tabId === 'tab-acesso') $('#btn-tab-acesso').addClass('border-brand-600 text-brand-600').removeClass('border-transparent text-slate-400');
        else if (tabId === 'tab-ficha') $('#btn-tab-ficha').addClass('border-brand-600 text-brand-600').removeClass('border-transparent text-slate-400');
        else if (tabId === 'tab-termo') $('#btn-tab-termo').addClass('border-brand-600 text-brand-600').removeClass('border-transparent text-slate-400');
        else if (tabId === 'tab-ferias') {
            $('#btn-tab-ferias').addClass('border-brand-600 text-brand-600').removeClass('border-transparent text-slate-400');
            if ($('#func_id').val()) carregarHistoricoTab('ferias', 'lista-ferias-tab');
        }
        else if (tabId === 'tab-folga') {
            $('#btn-tab-folga').addClass('border-brand-600 text-brand-600').removeClass('border-transparent text-slate-400');
            if ($('#func_id').val()) carregarHistoricoTab('folga eleitoral', 'lista-folga-tab');
        }
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

    async function carregarHistoricoTab(tipo, containerId) {
        const container = $('#' + containerId);
        container.html('<div class="col-span-full py-12 text-center"><div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 border-t-brand-600"></div></div>');

        try {
            const funcId = $('#func_id').val();
            const res = await fetch(`../../api/ferias.php?func_id=${funcId}`);
            const data = await res.json();

            if (data.success) {
                const filtered = data.data.filter(f => f.tipo_afastamento === tipo);
                
                if (filtered.length === 0) {
                    container.html('<div class="col-span-full py-12 text-center bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200"><p class="text-slate-400 font-medium">Nenhum registro encontrado para esta categoria.</p></div>');
                    return;
                }

                container.html(filtered.map(f => {
                    const dtIni = f.data_inicio.split('-').reverse().join('/');
                    const dtFim = f.data_fim.split('-').reverse().join('/');
                    
                    let statusClass = 'bg-amber-100 text-amber-700 border-amber-200';
                    if (f.status === 'deferido') statusClass = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                    if (f.status === 'indeferido') statusClass = 'bg-rose-100 text-rose-700 border-rose-200';

                    return `
                        <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm hover:shadow-md transition-all group">
                            <div class="flex justify-between items-start mb-3">
                                <span class="px-2 py-1 text-[10px] font-black uppercase rounded border ${statusClass}">${f.status}</span>
                                <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" onclick="editarFerias(${f.id}, '${f.data_inicio}', '${f.data_fim}', '${f.tipo_afastamento}', '${(f.motivo_especifico || '').replace(/'/g, "\\'")}', '${(f.anexo || '').replace(/'/g, "\\'")}', '${(f.observacao || '').replace(/'/g, "\\'")}')" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="text-lg font-bold text-slate-800 mb-1">${dtIni} - ${dtFim}</div>
                            ${f.periodo_aquisitivo ? `<p class="text-xs font-semibold text-slate-600 mb-1">Período: ${f.periodo_aquisitivo}</p>` : ''}
                            ${f.gestor_solicitante ? `<p class="text-[10px] text-slate-400 mb-1">Solicitado por: ${f.gestor_solicitante} ${f.gestor_setor ? `- ${f.gestor_setor}` : ''}</p>` : ''}
                            <p class="text-xs text-slate-500 line-clamp-2 mt-2">${f.observacao || 'Sem observações'}</p>
                        </div>
                    `;
                }).join(''));
            }
        } catch (e) {
            container.html('<div class="col-span-full py-12 text-center text-red-500 font-bold">Erro ao carregar dados.</div>');
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

    function adicionarLinhaPeriodo(dados = null) {
        const corpo = $('#lista-periodos-corpo');
        $('#msg-periodos-vazio').addClass('hidden');
        
        const valPeriodo = dados ? (dados.periodo || ((dados.inicio && dados.fim) ? (dados.inicio.substring(0, 4) + '/' + dados.fim.substring(0, 4)) : (dados.inicio || ''))) : '';
        const valDias = dados ? dados.dias : '30';
        const valOperador = dados ? (dados.operador || 'Sistema') : loggedInOperator;
        
        const row = $(`
            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors group">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <input type="text" list="periodos-presets" class="periodo-aquisitivo w-full bg-white border border-slate-200 rounded-lg text-sm font-bold text-slate-700 px-3 py-1.5 focus:border-amber-500 outline-none" placeholder="Ex: 2022/2023" value="${valPeriodo}">
                        <span class="text-[11px] font-semibold text-slate-400 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-md whitespace-nowrap flex items-center gap-1 shadow-sm shrink-0" title="Operador que cadastrou o período">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            ${valOperador}
                        </span>
                        <input type="hidden" class="periodo-operador-val" value="${valOperador}">
                    </div>
                </td>
                <td class="px-6 py-4 text-center">
                    <input type="number" class="periodo-dias w-24 bg-white border border-slate-200 rounded-lg text-center text-xs font-bold py-1.5 focus:border-amber-500 outline-none" value="${valDias}">
                </td>
                <td class="px-6 py-4">
                    <select class="periodo-status bg-white border border-slate-200 rounded-lg text-[10px] font-black uppercase px-2 py-1.5 outline-none focus:border-amber-500 transition-all w-full">
                        <option value="aberto" ${dados && dados.status === 'aberto' ? 'selected' : ''}>Aberto</option>
                        <option value="vencido" ${dados && dados.status === 'vencido' ? 'selected' : ''}>Vencido</option>
                        <option value="quitado" ${dados && dados.status === 'quitado' ? 'selected' : ''}>Quitado</option>
                    </select>
                </td>
                <td class="px-6 py-4 text-center">
                    <button type="button" onclick="$(this).closest('tr').remove(); if($('#lista-periodos-corpo tr').length === 0) $('#msg-periodos-vazio').removeClass('hidden');" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </td>
            </tr>
        `);
        corpo.append(row);
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

                // Aplicar a área de geolocalização, se existir
                if (dayData.area) {
                    $(`input[data-day-area-input="${day}"]`).val(dayData.area);
                    $(`span[data-day-area-name="${day}"]`).text(dayData.area);
                    $(`div[data-day-area-container="${day}"]`).removeClass('hidden');
                }
            } else if (typeof dayData === 'string' || typeof dayData === 'number') {
                // Fallback para legado
                console.log(`Dados legados detectados para ${day}: ${dayData}`);
                $(`.select-horario-grade[data-day="${day}"]`).val(dayData);
            }
        });
    }

    async function gerarMatricula(btn) {
        try {
            if (!btn) btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            btn.disabled = true;

            const res = await fetch('../../api/funcionarios.php?action=generate_matricula');
            const data = await res.json();

            if (data.success) {
                $('#func_matricula').val(data.matricula);
                $('#func_matricula_2').val(data.matricula);
                $('#termo_matricula').text(data.matricula);
                
                // Feedback visual
                $('#func_matricula').addClass('ring-4 ring-emerald-500/20 border-emerald-500');
                setTimeout(() => {
                    $('#func_matricula').removeClass('ring-4 ring-emerald-500/20 border-emerald-500');
                }, 1000);

                // Forçar verificação de duplicidade por segurança
                verificarDuplicado('matricula', data.matricula, 'feedback_matricula');
            }

            btn.innerHTML = originalHtml;
            btn.disabled = false;
        } catch (e) {
            console.error("Erro ao gerar matrícula:", e);
            Swal.fire('Erro', 'Falha ao gerar nova matrícula.', 'error');
        }
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
                if (func.matricula) {
                    $('#btn_gerar_matricula').addClass('hidden');
                } else {
                    $('#btn_gerar_matricula').removeClass('hidden');
                }
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
                $('#func_is_exonerado').prop('checked', func.is_exonerado == 1 || func.is_exonerado === true || func.is_exonerado === 't');
                $('#func_data_exoneracao').val(func.data_exoneracao || '');
                $('#func_motivo_exoneracao').val(func.motivo_exoneracao || '');
                toggleExoneracaoFields();

                let photoData = func.foto_perfil || func.biometria_facial;
                if (photoData) {
                    // Se for caminho em disco (e não base64), prefixa para sair de views/admin/
                    let displayPath = photoData;
                    if (photoData.startsWith('uploads/')) {
                        displayPath = '../../' + photoData;
                    }

                    // Testa se a imagem existe; se não, mostra placeholder limpo
                    const img = new Image();
                    img.onload = function() {
                        $('#func_foto_preview').attr('src', displayPath).removeClass('hidden');
                        $('#func_foto_placeholder').addClass('hidden');
                        $('#termo_foto_preview').attr('src', displayPath).removeClass('hidden');
                        $('#termo_foto_placeholder').addClass('hidden');
                        $('#post_capture_controls').removeClass('hidden');
                        $('#pre_capture_controls').addClass('hidden');
                        $('#btn_ativar_camera').addClass('hidden');
                    };
                    img.onerror = function() {
                        // Arquivo não encontrado localmente — exibe placeholder
                        $('#func_foto_preview').addClass('hidden');
                        $('#func_foto_placeholder').removeClass('hidden');
                        $('#termo_foto_preview').addClass('hidden');
                        $('#termo_foto_placeholder').removeClass('hidden');
                        $('#post_capture_controls').addClass('hidden');
                        $('#pre_capture_controls').removeClass('hidden');
                        $('#btn_ativar_camera').removeClass('hidden');
                    };
                    img.src = displayPath;
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

                // Férias - Períodos Aquisitivos
                if (func.ferias_periodos) {
                    try {
                        const periodos = typeof func.ferias_periodos === 'string' ? JSON.parse(func.ferias_periodos) : func.ferias_periodos;
                        if (Array.isArray(periodos)) {
                            $('#lista-periodos-corpo').empty();
                            if (periodos.length > 0) {
                                periodos.forEach(p => adicionarLinhaPeriodo(p));
                            } else {
                                $('#msg-periodos-vazio').removeClass('hidden');
                            }
                        }
                    } catch (e) {
                        console.error("Erro ao processar ferias_periodos:", e);
                    }
                } else {
                    $('#msg-periodos-vazio').removeClass('hidden');
                }

                // Pleitos de Folga Eleitoral
                carregarPleitosUI(func.folgas_eleitorais || '[]');

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
            const area = $(`input[data-day-area-input="${d}"]`).val();
            
            if (p1 || p2 || p3 || p4 || h_id || area) {
                grade[d] = {
                    p1: p1 || "",
                    p2: p2 || "",
                    p3: p3 || "",
                    p4: p4 || "",
                    horario_id: h_id || null,
                    area: area || null
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
            data_exoneracao: $('#func_data_exoneracao').val(),
            motivo_exoneracao: $('#func_motivo_exoneracao').val(),
            
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
                grade_horarios: grade,

                // Períodos Aquisitivos de Férias
                ferias_periodos: $('#lista-periodos-corpo tr').map(function() {
                    return {
                        periodo: $(this).find('.periodo-aquisitivo').val(),
                        dias: $(this).find('.periodo-dias').val(),
                        status: $(this).find('.periodo-status').val(),
                        operador: $(this).find('.periodo-operador-val').val() || 'Sistema'
                    };
                }).get().filter(p => p.periodo)
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

    window.toggleSenhaVisual = function(btn) {
        const input = document.getElementById('func_senha');
        if (!input) return;
        const icon = btn.querySelector('svg');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />`;
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
            }
        }
    };

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
        removerAreaDia(day);
    };

    window.removerAreaDia = function(day) {
        $(`input[data-day-area-input="${day}"]`).val('');
        $(`div[data-day-area-container="${day}"]`).addClass('hidden');
    };

    let diaSelecionadoGeo = null;
    let presetsData = [];

    window.abrirModalGeolocalizacao = async function(dia) {
        diaSelecionadoGeo = dia;
        const modal = document.getElementById('modalGeoSemanal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Carregar presets se ainda não carregados
        if (presetsData.length === 0) {
            const select = document.getElementById('geo_preset_select');
            select.innerHTML = '<option value="">Carregando...</option>';
            try {
                const res = await fetch('../../api/geofencing_presets.php');
                const data = await res.json();
                if (data.success) {
                    presetsData = data.data;
                    select.innerHTML = '<option value="">-- Selecione uma Área --</option>';
                    presetsData.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.nome_area;
                        opt.textContent = p.nome_area;
                        select.appendChild(opt);
                    });
                }
            } catch (e) {
                console.error("Erro ao carregar áreas:", e);
                select.innerHTML = '<option value="">Erro ao carregar</option>';
            }
        }
        
        // Pre-selecionar se já houver valor
        const atual = $(`input[data-day-area-input="${dia}"]`).val();
        $('#geo_preset_select').val(atual || '');
    };

    window.fecharModalGeo = function() {
        document.getElementById('modalGeoSemanal').classList.add('hidden');
        document.getElementById('modalGeoSemanal').classList.remove('flex');
    };

    window.confirmarGeolocalizacao = function() {
        const area = document.getElementById('geo_preset_select').value;
        if (area) {
            $(`input[data-day-area-input="${diaSelecionadoGeo}"]`).val(area);
            $(`span[data-day-area-name="${diaSelecionadoGeo}"]`).text(area);
            $(`div[data-day-area-container="${diaSelecionadoGeo}"]`).removeClass('hidden');
        } else {
            removerAreaDia(diaSelecionadoGeo);
        }
        fecharModalGeo();
    };

    window.replicarHorarioPrimeiroDia = function() {
        const p1 = $(`input[data-day="segunda"][data-ponto="p1"]`).val();
        const p2 = $(`input[data-day="segunda"][data-ponto="p2"]`).val();
        const p3 = $(`input[data-day="segunda"][data-ponto="p3"]`).val();
        const p4 = $(`input[data-day="segunda"][data-ponto="p4"]`).val();
        const h_id = $(`.select-horario-grade[data-day="segunda"]`).val();
        const area = $(`input[data-day-area-input="segunda"]`).val();

        ['terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'].forEach(d => {
            $(`input[data-day="${d}"][data-ponto="p1"]`).val(p1);
            $(`input[data-day="${d}"][data-ponto="p2"]`).val(p2);
            $(`input[data-day="${d}"][data-ponto="p3"]`).val(p3);
            $(`input[data-day="${d}"][data-ponto="p4"]`).val(p4);
            $(`.select-horario-grade[data-day="${d}"]`).val(h_id);
            
            if (area) {
                $(`input[data-day-area-input="${d}"]`).val(area);
                $(`span[data-day-area-name="${d}"]`).text(area);
                $(`div[data-day-area-container="${d}"]`).removeClass('hidden');
            } else {
                removerAreaDia(d);
            }
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

    window.toggleExoneracaoFields = function() {
        const isExonerado = $('#func_is_exonerado').is(':checked');
        if (isExonerado) {
            $('#wrapper_exoneracao').removeClass('hidden');
            if (!$('#func_data_exoneracao').val()) {
                $('#func_data_exoneracao').val(new Date().toISOString().split('T')[0]);
            }
        } else {
            $('#wrapper_exoneracao').addClass('hidden');
        }
    };

    // --- LÓGICA DE PLEITOS (FOLGA ELEITORAL) ---
    window._pleitosCarregados = [];

    window.carregarPleitosUI = function(pleitosStr) {
        let pleitos = [];
        try {
            pleitos = typeof pleitosStr === 'string' ? JSON.parse(pleitosStr || '[]') : (pleitosStr || []);
        } catch(e) {}
        
        window._pleitosCarregados = pleitos;
        const tbody = document.getElementById('lista-pleitos-body');
        const emptyMsg = document.getElementById('msg-pleitos-vazio');
        
        tbody.innerHTML = '';
        if (pleitos.length === 0) {
            emptyMsg.classList.remove('hidden');
        } else {
            emptyMsg.classList.add('hidden');
            pleitos.forEach(p => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50 transition-colors group';
                
                const saldoClass = p.saldo > 0 ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : 'text-slate-500 bg-slate-100 border-slate-200';
                
                tr.innerHTML = `
                    <td class="py-3 font-bold text-slate-700">${esc(p.nome)}</td>
                    <td class="py-3 text-center font-bold text-slate-600">${p.dias}</td>
                    <td class="py-3 text-center font-bold text-slate-500">${p.dias_gozados || 0}</td>
                    <td class="py-3 text-center">
                        <span class="inline-flex items-center justify-center min-w-[32px] h-6 px-2 text-[11px] font-black rounded-md border ${saldoClass}">${p.saldo}</span>
                    </td>
                    <td class="py-3 text-right">
                        <button type="button" onclick="excluirPleito('${esc(p.nome).replace(/'/g, "\\'")}')" class="text-slate-300 hover:text-red-500 transition-colors p-1" title="Excluir Pleito">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    };

    window.salvarPleito = async function() {
        const id = document.getElementById('func_id').value;
        if (!id) {
            Swal.fire('Atenção', 'Salve o funcionário primeiro antes de adicionar pleitos.', 'warning');
            return;
        }
        const nome = document.getElementById('novo_pleito_nome').value.trim();
        const dias = document.getElementById('novo_pleito_dias').value;
        
        if (!nome || !dias || dias <= 0) {
            Swal.fire('Atenção', 'Preencha o nome do pleito e a quantidade de dias (> 0).', 'warning');
            return;
        }
        
        try {
            const res = await fetch('../../api/funcionarios.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'salvar_pleito', id, nome, dias })
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('novo_pleito_nome').value = '';
                document.getElementById('novo_pleito_dias').value = '';
                // Recarregar os dados do funcionário para atualizar o saldo
                carregarFuncionario(id);
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Pleito salvo!', showConfirmButton: false, timer: 1500 });
            } else {
                Swal.fire('Erro', data.message, 'error');
            }
        } catch(e) {
            Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
        }
    };

    window.excluirPleito = async function(nome) {
        const id = document.getElementById('func_id').value;
        const res = await Swal.fire({
            title: 'Excluir pleito?',
            text: `Deseja excluir o pleito "${nome}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        });
        
        if (res.isConfirmed) {
            try {
                const res = await fetch('../../api/funcionarios.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'excluir_pleito', id, nome })
                });
                const data = await res.json();
                if (data.success) {
                    carregarFuncionario(id);
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Excluído!', showConfirmButton: false, timer: 1500 });
                } else {
                    Swal.fire('Erro', data.message, 'error');
                }
            } catch(e) {
                Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
            }
        }
    };

    window.abrirModalPleitosPresets = function() {
        document.getElementById('modalPleitosPresets').classList.remove('hidden');
        carregarPleitosPresets();
    };

    window.fecharModalPleitosPresets = function() {
        document.getElementById('modalPleitosPresets').classList.add('hidden');
    };

    window.carregarPleitosPresets = async function() {
        try {
            const res = await fetch('../../api/pleitos_presets.php');
            const data = await res.json();
            if (data.success) {
                // Preenche o datalist
                const datalist = document.getElementById('pleitos-presets');
                if (datalist) {
                    datalist.innerHTML = data.data.map(p => `<option value="${esc(p.nome)}"></option>`).join('');
                }
                
                // Preenche a lista no modal
                const container = document.getElementById('lista-presets-container');
                if (container) {
                    if (data.data.length === 0) {
                        container.innerHTML = `<div class="text-sm text-slate-400 text-center py-6 bg-slate-50 border border-dashed border-slate-200 rounded-2xl">Nenhum preset cadastrado.</div>`;
                    } else {
                        container.innerHTML = data.data.map(p => {
                            const nameEscaped = p.nome.replace(/'/g, "\\'").replace(/"/g, "&quot;");
                            return `
                            <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200/60 rounded-xl hover:border-slate-300 hover:bg-slate-100/50 transition-all">
                                <span class="text-sm font-bold text-slate-700 truncate max-w-[240px]">${esc(p.nome)}</span>
                                <div class="flex gap-1.5">
                                    <button type="button" onclick="editarPresetPleito(${p.id}, '${nameEscaped}')" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg border border-transparent hover:border-indigo-100 transition-colors shadow-sm bg-white" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <button type="button" onclick="excluirPresetPleito(${p.id}, '${nameEscaped}')" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg border border-transparent hover:border-rose-100 transition-colors shadow-sm bg-white" title="Excluir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                            `;
                        }).join('');
                    }
                }
            } else {
                console.error('Falha ao carregar presets:', data.message);
            }
        } catch(e) {
            console.error('Erro de conexão ao buscar presets:', e);
        }
    };

    window.salvarPresetPleito = async function(event) {
        event.preventDefault();
        const nome = document.getElementById('preset_nome').value.trim();
        if (!nome) return;

        try {
            const res = await fetch('../../api/pleitos_presets.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nome })
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('preset_nome').value = '';
                await carregarPleitosPresets();
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
            } else {
                Swal.fire('Erro', data.message, 'error');
            }
        } catch(e) {
            Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
        }
    };

    window.editarPresetPleito = async function(id, currentNome) {
        const { value: novoNome } = await Swal.fire({
            title: 'Editar Nome do Pleito',
            input: 'text',
            inputValue: currentNome,
            showCancelButton: true,
            confirmButtonText: 'Salvar',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value || !value.trim()) {
                    return 'O nome do pleito não pode ser vazio!';
                }
            }
        });

        if (novoNome && novoNome.trim() !== currentNome) {
            try {
                const res = await fetch('../../api/pleitos_presets.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, nome: novoNome.trim() })
                });
                const data = await res.json();
                if (data.success) {
                    await carregarPleitosPresets();
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
                } else {
                    Swal.fire('Erro', data.message, 'error');
                }
            } catch(e) {
                Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
            }
        }
    };

    window.excluirPresetPleito = async function(id, nome) {
        const res = await Swal.fire({
            title: 'Excluir Preset?',
            text: `Deseja realmente excluir o preset "${nome}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        });

        if (res.isConfirmed) {
            try {
                const res = await fetch('../../api/pleitos_presets.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                const data = await res.json();
                if (data.success) {
                    await carregarPleitosPresets();
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
                } else {
                    Swal.fire('Erro', data.message, 'error');
                }
            } catch(e) {
                Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
            }
        }
    };
</script>

<!-- Modal Cropper -->
<div id="modalCropper" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800">Recortar Foto Biométrica</h3>
            <button type="button" onclick="fecharCrop()" class="text-slate-400 hover:text-slate-600 p-2 rounded-lg transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-6">
            <div class="relative bg-slate-100 rounded-2xl overflow-hidden max-h-[60vh] flex items-center justify-center">
                <img id="cropperImage" src="" class="max-w-full block">
            </div>
            <p class="mt-4 text-xs text-slate-500 text-center italic">Arraste e redimensione para enquadrar o rosto dentro do quadrado central.</p>
        </div>
        <div class="px-6 py-4 bg-slate-50 flex justify-end gap-3">
            <button type="button" onclick="fecharCrop()" class="px-6 py-2.5 rounded-xl font-bold text-slate-600 hover:bg-slate-200 transition-all border border-slate-200">Cancelar</button>
            <button type="button" id="btnConfirmarCrop" onclick="confirmarCrop()" class="px-8 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl font-bold shadow-lg shadow-brand-200 transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                Confirmar Recorte
            </button>
        </div>
    </div>
</div>

<!-- Modal Seleção de Geolocalização Semanal -->
<div id="modalGeoSemanal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Local de Trabalho
            </h3>
            <button type="button" onclick="fecharModalGeo()" class="text-slate-400 hover:text-slate-600 p-2 rounded-lg transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-8 space-y-6">
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Área de Geolocalização</label>
                <select id="geo_preset_select" class="w-full px-4 py-3.5 bg-slate-50 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 transition-all outline-none text-sm font-bold text-slate-700 cursor-pointer shadow-sm">
                    <option value="">-- Selecione uma Área --</option>
                </select>
                <p class="mt-3 text-xs text-slate-500 leading-relaxed italic">
                    As áreas listadas são configuradas em "Geolocalização Inteligente". O ponto será validado conforme o perímetro desta área no dia selecionado.
                </p>
            </div>
        </div>
        <div class="px-6 py-4 bg-slate-50 flex justify-end gap-3 border-t border-slate-100">
            <button type="button" onclick="fecharModalGeo()" class="px-6 py-2.5 rounded-xl font-bold text-slate-600 hover:bg-slate-200 transition-all border border-slate-200">Cancelar</button>
            <button type="button" onclick="confirmarGeolocalizacao()" class="px-8 py-2.5 bg-sky-600 hover:bg-sky-500 text-white rounded-xl font-bold shadow-lg shadow-sky-200 transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                Confirmar Local
            </button>
        </div>
    </div>
</div>

<!-- Modal de Pleitos Presets -->
<div id="modalPleitosPresets" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="fecharModalPleitosPresets()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
        <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-md border border-slate-100">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Configurar Pleitos Presets
                </h3>
                <button onclick="fecharModalPleitosPresets()"
                    class="text-slate-400 hover:text-slate-600 transition-colors bg-white hover:bg-slate-100 p-2 rounded-lg border border-slate-200 shadow-sm">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <form id="formAdicionarPreset" onsubmit="salvarPresetPleito(event)" class="space-y-4 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Novo Nome do Pleito</label>
                        <div class="flex gap-2">
                            <input type="text" id="preset_nome" required
                                class="flex-1 h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold text-slate-700 focus:bg-white focus:border-indigo-500 outline-none transition-all"
                                placeholder="Ex: Eleições 2026">
                            <button type="submit"
                                class="h-10 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold text-sm shadow-sm transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Adicionar
                            </button>
                        </div>
                    </div>
                </form>

                <div class="border-t border-slate-100 pt-4">
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Pleitos Cadastrados</h4>
                    <div class="max-h-[300px] overflow-y-auto pr-1 space-y-2" id="lista-presets-container">
                        <!-- Renderizado via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
