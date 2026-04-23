<?php include 'layout/header.php'; ?>

<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Liberação de Ponto</h1>
            <p class="text-sm font-medium text-slate-500 mt-1">Autorize batidas de ponto "Normais" a partir de um horário específico para o dia selecionado.</p>
        </div>
        <button onclick="abrirModalNovaLiberacao()" class="flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl transition-all shadow-sm active:scale-95">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nova Liberação
        </button>
    </div>

    <!-- Tabela de Liberações -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6">
            <table id="tabelaLiberacoes" class="w-full text-left border-collapse display nowrap" style="width:100%">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Data e Hora da Liberação</th>
                        <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Setor Alvo</th>
                        <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Descrição</th>
                        <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Status</th>
                        <th class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody id="listaLiberacoes"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nova Liberação -->
<div id="modalNovaLiberacao" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="fecharModalNovaLiberacao()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-100 p-0">
                
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-xl font-bold text-slate-800">Agendar Liberação de Ponto</h3>
                    <button onclick="fecharModalNovaLiberacao()" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-200 rounded-xl transition-all">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-8 space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Data e Hora que será liberado</label>
                        <input type="datetime-local" id="lib_data_hora" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all font-bold text-slate-700 outline-none">
                        <p class="text-[10px] text-brand-600 font-bold mt-2 uppercase">A liberação será válida desde este horário até o fim do dia informado.</p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Público Alvo (Setor)</label>
                        <select id="lib_setor" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all font-bold text-slate-700 outline-none cursor-pointer">
                            <option value="TODOS">TODOS OS SETORES</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Motivo / Descrição</label>
                        <input type="text" id="lib_descricao" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all text-slate-700 outline-none placeholder:text-slate-400" placeholder="Ex: Evento de Capacitação, Mutirão de Atendimento...">
                    </div>
                </div>

                <div class="px-8 py-5 border-t border-slate-100 bg-slate-50 flex justify-end gap-3 rounded-b-3xl">
                    <button onclick="fecharModalNovaLiberacao()" class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:bg-slate-200 rounded-xl transition-all border border-slate-200 bg-white">Cancelar</button>
                    <button onclick="salvarLiberacao()" class="px-8 py-2.5 text-sm font-bold text-white bg-brand-600 hover:bg-brand-500 rounded-xl transition-all shadow-md active:scale-95 shadow-brand-500/20">Salvar Agendamento</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let tableLib;

    $(document).ready(function() {
        carregarSetores();
        
        tableLib = $('#tabelaLiberacoes').DataTable({
            ajax: {
                url: '../../api/ponto_liberado.php',
                dataSrc: 'data'
            },
            columns: [
                { 
                    data: 'data_hora',
                    render: (d) => new Date(d.replace(' ', 'T')).toLocaleString('pt-BR')
                },
                { 
                    data: 'setor',
                    render: (d) => `<span class="px-2 py-1 rounded text-[10px] font-black tracking-tighter uppercase ${d === 'TODOS' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-slate-100 text-slate-600 border border-slate-200'}">${d}</span>`
                },
                { data: 'descricao' },
                {
                    data: null,
                    render: function(row) {
                        const agora = new Date();
                        const libTs = new Date(row.data_hora.replace(' ', 'T'));
                        
                        // Mesma data?
                        const mesmodia = agora.toDateString() === libTs.toDateString();
                        
                        if (mesmodia && agora >= libTs) {
                            return '<span class="flex items-center gap-1.5 text-emerald-600 font-black text-[10px] uppercase tracking-widest animate-pulse"><div class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></div> Ativo Agora</span>';
                        } else if (agora > libTs) {
                            return '<span class="text-slate-400 font-bold text-[10px] uppercase tracking-widest">Expirado (Dia Anterior)</span>';
                        } else {
                            return '<span class="text-brand-500 font-bold text-[10px] uppercase tracking-widest">Agendado</span>';
                        }
                    }
                },
                {
                    data: 'id',
                    className: 'text-right',
                    render: function(id) {
                        return `<button onclick="deletarLiberacao(${id})" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-100" title="Apagar">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>`;
                    }
                }
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' },
            order: [[0, 'desc']]
        });
    });

    function carregarSetores() {
        fetch('../../api/ponto_liberado.php?action=get_setores')
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    const select = document.getElementById('lib_setor');
                    res.data.forEach(setor => {
                        const opt = document.createElement('option');
                        opt.value = setor;
                        opt.textContent = setor.toUpperCase();
                        select.appendChild(opt);
                    });
                }
            });
    }

    function abrirModalNovaLiberacao() {
        document.getElementById('modalNovaLiberacao').classList.remove('hidden');
    }

    function fecharModalNovaLiberacao() {
        document.getElementById('modalNovaLiberacao').classList.add('hidden');
        document.getElementById('lib_data_hora').value = '';
        document.getElementById('lib_descricao').value = '';
        document.getElementById('lib_setor').value = 'TODOS';
    }

    async function salvarLiberacao() {
        const payload = {
            data_hora: document.getElementById('lib_data_hora').value,
            descricao: document.getElementById('lib_descricao').value,
            setor: document.getElementById('lib_setor').value
        };

        if (!payload.data_hora) {
            Swal.fire('Aviso', 'Data e hora são obrigatórias.', 'warning');
            return;
        }

        Swal.fire({ title: 'Salvando...', didOpen: () => { Swal.showLoading(); } });

        try {
            const res = await fetch('../../api/ponto_liberado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire('Sucesso!', data.message, 'success');
                fecharModalNovaLiberacao();
                tableLib.ajax.reload();
            } else {
                Swal.fire('Erro', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Erro', 'Falha na comunicação.', 'error');
        }
    }

    function deletarLiberacao(id) {
        Swal.fire({
            title: 'Excluir Agendamento?',
            text: "Esta liberação será removida e as travas de horário voltarão ao normal.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('../../api/ponto_liberado.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Excluído!', data.message, 'success');
                        tableLib.ajax.reload();
                    } else {
                        Swal.fire('Erro', data.message, 'error');
                    }
                });
            }
        });
    }
</script>

<?php include 'layout/footer.php'; ?>
