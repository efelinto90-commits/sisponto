<?php
require_once 'layout/header.php';
$isAdmin = (($_SESSION['user_level'] ?? '3') == '1');
?>

<div class="space-y-6">
    <!-- Cabeçalho Principal -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-8 rounded-3xl shadow-sm border border-slate-200/60">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="p-2 bg-brand-50 text-brand-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </span>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Geolocalização Inteligente</h1>
            </div>
            <p class="text-slate-500 font-medium">Gestão centralizada de perímetros e cercas digitais (Geofencing).</p>
        </div>
        <button onclick="window.history.back()" class="group flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 rounded-2xl border border-slate-200 transition-all active:scale-95 shadow-sm">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Voltar ao Painel
        </button>
    </div>

    <!-- Container Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Lado Esquerdo: Configurações -->
        <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-6">
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-200/60 overflow-hidden transition-all hover:shadow-2xl hover:shadow-slate-300/40">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <div class="p-1.5 bg-brand-500 text-white rounded-lg shadow-lg shadow-brand-500/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.350a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                        </div>
                        Configuração Base
                    </h2>
                </div>
                
                <div class="p-6 space-y-5">
                    <!-- Presets -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/60 space-y-3">
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest">Favoritos / Presets</label>
                        <select id="preset_select" onchange="selecionarPreset()" <?php echo $isAdmin ? '' : 'disabled'; ?> class="w-full px-4 py-3 bg-white rounded-xl border border-slate-200 focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition-all outline-none text-sm font-semibold cursor-pointer shadow-sm">
                            <option value="">-- Selecione uma área salva --</option>
                        </select>
                    </div>

                    <div class="space-y-4">
                        <div class="relative group">
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Nome da Área</label>
                            <input type="text" id="bulk_area" placeholder="Ex: Sede Principal, Unidade II" <?php echo $isAdmin ? '' : 'disabled'; ?>
                                class="w-full pl-11 pr-4 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition-all outline-none font-medium text-slate-700 bg-slate-50/30">
                            <svg class="w-5 h-5 absolute left-4 bottom-3.5 text-slate-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="relative group">
                                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Latitude</label>
                                <input type="text" id="bulk_lat" placeholder="-0.0000" <?php echo $isAdmin ? '' : 'disabled'; ?>
                                    class="w-full pl-4 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition-all outline-none font-medium text-slate-700 bg-slate-50/30">
                            </div>
                            <div class="relative group">
                                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Longitude</label>
                                <input type="text" id="bulk_lng" placeholder="-0.0000" <?php echo $isAdmin ? '' : 'disabled'; ?>
                                    class="w-full pl-4 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition-all outline-none font-medium text-slate-700 bg-slate-50/30">
                            </div>
                        </div>

                        <div class="relative group">
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Raio Máximo (Metros)</label>
                            <input type="number" id="bulk_dist" value="200" <?php echo $isAdmin ? '' : 'disabled'; ?>
                                class="w-full pl-11 pr-4 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition-all outline-none font-bold text-slate-800 bg-slate-50/30">
                            <svg class="w-5 h-5 absolute left-4 bottom-3.5 text-slate-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.483V4.517a2 2 0 011.553-1.943L9 1l6 2.553L20.447 1a2 2 0 012.553 1.943v10.966a2 2 0 01-1.553 1.943L15 20l-6-2.553L9 20z"></path></svg>
                        </div>
                    </div>
                    
                    <?php if ($isAdmin): ?>
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <button onclick="capturarLocalBulk()" class="flex flex-col items-center justify-center gap-2 px-4 py-4 bg-emerald-50 text-emerald-700 font-bold rounded-2xl border border-emerald-100 hover:bg-emerald-100 transition-all active:scale-95 group shadow-sm hover:shadow-md">
                            <svg class="w-6 h-6 transform group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            <span class="text-xs">GPS Atual</span>
                        </button>
                        <button onclick="gravarPreset()" class="flex flex-col items-center justify-center gap-2 px-4 py-4 bg-blue-50 text-blue-700 font-bold rounded-2xl border border-blue-100 hover:bg-blue-100 transition-all active:scale-95 group shadow-sm hover:shadow-md">
                            <svg class="w-6 h-6 transform group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            <span class="text-xs">Gravar Área</span>
                        </button>
                    </div>
                    
                    <div class="pt-6 border-t border-slate-100 space-y-4">
                        <button onclick="aplicarBulk('all')" class="w-full px-4 py-4 bg-brand-600 text-white font-black rounded-2xl hover:bg-brand-700 shadow-xl shadow-brand-500/30 transition-all active:scale-[0.98] uppercase tracking-widest text-xs">
                            Aplicar a Toda a Lista
                        </button>
                        <button onclick="aplicarBulk('selected')" class="w-full px-4 py-4 bg-slate-900 text-white font-black rounded-2xl hover:bg-black transition-all active:scale-[0.98] uppercase tracking-widest text-xs">
                            Aplicar aos Selecionados
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100">
                        <p class="text-xs font-bold text-amber-700 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Apenas Visualização
                        </p>
                        <p class="text-[10px] text-amber-600 mt-1">Sua conta não possui permissão para alterar cercas digitais.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Lado Direito: Tabela de Funcionários -->
        <div class="lg:col-span-8 bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-200/60 overflow-hidden min-h-[600px]">
            <div class="p-8 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/30">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-white rounded-2xl shadow-sm border border-slate-200/60">
                        <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Equipe de Funcionários</h2>
                        <p class="text-sm text-slate-500 font-medium">Selecione quem receberá as novas coordenadas</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3 p-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm">
                        <select id="filterArea" onchange="filtrarPorArea()" class="text-sm font-bold text-slate-700 bg-transparent border-none focus:ring-0 cursor-pointer">
                            <option value="">Todas as Áreas</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-white rounded-2xl border border-slate-200/80 shadow-sm transition-all hover:border-brand-200">
                        <input type="checkbox" id="selectAll" onclick="toggleSelectAll()" <?php echo $isAdmin ? '' : 'disabled'; ?> class="w-5 h-5 rounded-md border-slate-300 text-brand-600 focus:ring-brand-500 transition-all cursor-pointer">
                        <label for="selectAll" class="text-sm font-bold text-slate-700 cursor-pointer select-none">Selecionar Tudo</label>
                    </div>
                </div>
            </div>
            
            <div class="p-0 table-responsive-wrapper">
                <table id="tableGeoBulk" class="w-full">
                    <thead class="bg-slate-50/80 border-b border-slate-100">
                        <tr>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] w-12">IDC</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">IDENTIFICAÇÃO</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">MATRÍCULA</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">COORDENADAS ATUAIS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <!-- Conteúdo via DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    let tableBulk;

    $(document).ready(function() {
        tableBulk = $('#tableGeoBulk').DataTable({
            ajax: '../../api/funcionarios.php',
            columns: [
                {
                    data: 'id',
                    render: function(data) {
                        const disabledAttr = <?php echo $isAdmin ? '""' : '"disabled"'; ?>;
                        return `<input type="checkbox" ${disabledAttr} class="emp-checkbox cursor-pointer rounded border-slate-300 text-brand-600 focus:ring-brand-500" value="${data}">`;
                    }
                },
                {
                    data: 'nome',
                    render: function(data, type, row) {
                        return `<div class="font-semibold text-slate-900">${data}</div><div class="text-xs text-slate-500">${row.setor}</div>`;
                    }
                },
                { data: 'matricula' },
                {
                    data: null,
                    render: function(data, type, row) {
                        if (row.lat_permitida && row.long_permitida) {
                            const areaLabel = row.area_geofencing ? `<div class="font-bold text-[10px] uppercase text-sky-800 mb-0.5">${row.area_geofencing}</div>` : '';
                            return `<div class="flex flex-col">
                                ${areaLabel}
                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-sky-50 text-sky-700 text-xs font-medium border border-sky-100">
                                    ${row.lat_permitida}, ${row.long_permitida} (${row.distancia_max_permitida}m)
                                </span>
                            </div>`;
                        }
                        return `<span class="text-slate-400 italic text-xs">Não configurado</span>`;
                    }
                }
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' },
            pageLength: 25,
            responsive: true,
            dom: '<"p-4 border-b border-slate-100"f>rt<"p-4 border-t border-slate-100"ip>'
        });

        carregarPresets();
        carregarListaAreas();
    });

    async function carregarListaAreas() {
        try {
            const res = await fetch('../../api/funcionarios.php?action=get_areas');
            const data = await res.json();
            if (data.success) {
                const select = document.getElementById('filterArea');
                data.data.forEach(a => {
                    const opt = document.createElement('option');
                    opt.value = a;
                    opt.textContent = a;
                    select.appendChild(opt);
                });
            }
        } catch (e) { console.error("Erro ao carregar áreas"); }
    }

    function filtrarPorArea() {
        const area = document.getElementById('filterArea').value;
        if (area) {
            tableBulk.column(3).search(area).draw();
        } else {
            tableBulk.column(3).search('').draw();
        }
    }

    let presetsData = [];

    async function carregarPresets() {
        try {
            const res = await fetch('../../api/geofencing_presets.php');
            const data = await res.json();
            if (data.success) {
                presetsData = data.data;
                const select = document.getElementById('preset_select');
                // Manter apenas a primeira opção
                select.innerHTML = '<option value="">-- Selecione uma área salva --</option>';
                presetsData.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.nome_area;
                    select.appendChild(opt);
                });
            }
        } catch (e) { console.error("Erro ao carregar presets:", e); }
    }

    function selecionarPreset() {
        const id = document.getElementById('preset_select').value;
        if (!id) return;
        
        const preset = presetsData.find(p => p.id == id);
        if (preset) {
            document.getElementById('bulk_area').value = preset.nome_area;
            document.getElementById('bulk_lat').value = preset.latitude;
            document.getElementById('bulk_lng').value = preset.longitude;
            document.getElementById('bulk_dist').value = preset.distancia_max;
        }
    }

    async function gravarPreset() {
        const area = document.getElementById('bulk_area').value;
        const lat = document.getElementById('bulk_lat').value;
        const lng = document.getElementById('bulk_lng').value;
        const dist = document.getElementById('bulk_dist').value;

        if (!area || !lat || !lng) {
            Swal.fire('Ops!', 'Preencha o Nome da Área, Latitude e Longitude para gravar.', 'warning');
            return;
        }

        try {
            const res = await fetch('../../api/geofencing_presets.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    nome_area: area,
                    latitude: lat,
                    longitude: lng,
                    distancia_max: dist
                })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ title: 'Salvo!', text: data.message, icon: 'success', timer: 1500, showConfirmButton: false });
                carregarPresets();
            } else {
                Swal.fire('Erro', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Erro', 'Falha ao salvar preset.', 'error');
        }
    }

    function toggleSelectAll() {
        const checked = document.getElementById('selectAll').checked;
        // Selecionar apenas as linhas visíveis (filtradas)
        tableBulk.rows({ search: 'applied' }).every(function() {
            const node = this.node();
            $(node).find('.emp-checkbox').prop('checked', checked);
        });
    }

    function capturarLocalBulk() {
        if (!navigator.geolocation) {
            Swal.fire('Erro', 'Geolocalização não suportada no seu navegador.', 'error');
            return;
        }

        Swal.fire({
            title: 'Obtendo localização...',
            text: 'Por favor, aguarde.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                document.getElementById('bulk_lat').value = pos.coords.latitude.toFixed(8);
                document.getElementById('bulk_lng').value = pos.coords.longitude.toFixed(8);
                Swal.fire({ title: 'Sucesso!', text: 'Localização capturada.', icon: 'success', timer: 1500, showConfirmButton: false });
            },
            (err) => {
                Swal.fire('Erro', 'Não foi possível obter sua localização: ' + err.message, 'error');
            }
        );
    }

    async function aplicarBulk(mode) {
        const area = document.getElementById('bulk_area').value;
        const lat = document.getElementById('bulk_lat').value;
        const lng = document.getElementById('bulk_lng').value;
        const dist = document.getElementById('bulk_dist').value;

        if (!lat || !lng) {
            Swal.fire('Ops!', 'Preencha a Latitude e Longitude desejada.', 'warning');
            return;
        }

        let ids = [];
        if (mode === 'selected') {
            $('.emp-checkbox:checked').each(function() {
                ids.push($(this).val());
            });
            if (ids.length === 0) {
                Swal.fire('Ops!', 'Selecione ao menos um funcionário da lista.', 'warning');
                return;
            }
        } else {
            ids = 'all';
        }

        const confirmText = mode === 'all' 
            ? "Isso aplicará as coordenadas a TODOS os funcionários do sistema." 
            : `Isso aplicará as coordenadas aos ${ids.length} funcionários selecionados.`;

        const result = await Swal.fire({
            title: 'Tem certeza?',
            text: confirmText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, aplicar!',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            try {
                Swal.fire({ title: 'Aplicando...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                
                const res = await fetch('../../api/funcionarios.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'bulk_geofencing',
                        ids: ids,
                        area: area,
                        lat: lat,
                        lng: lng,
                        dist: dist
                    })
                });
                
                const data = await res.json();
                if (data.success) {
                    Swal.fire('Sucesso!', data.message, 'success');
                    tableBulk.ajax.reload();
                    document.getElementById('selectAll').checked = false;
                } else {
                    Swal.fire('Erro', data.message, 'error');
                }
            } catch (e) {
                Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
            }
        }
    }
</script>

<?php require_once 'layout/footer.php'; ?>
