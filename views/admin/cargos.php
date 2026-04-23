<?php 
include 'layout/header.php'; 

// Lógica de Autorização para Ações Administrativas (Adicionar/Editar/Excluir)
$user_level = $_SESSION['user_level'] ?? '3';
$user_setor = strtolower(trim($_SESSION['user_setor'] ?? ''));
$user_name  = strtolower(trim($_SESSION['user_name'] ?? ''));

// Apenas Admin (1) ou usuários de CRH/CORSIN podem gerenciar cargos
$is_authorized = ($user_level == '1' || $user_setor == 'crh' || $user_setor == 'corsin' || $user_name == 'crh' || $user_name == 'corsin');
?>

<div
    class="max-w-7xl mx-auto flex flex-col h-full bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Cargos e Setores</h1>
            <p class="text-sm text-slate-500 mt-1">Gerencie a nomenclatura dos cargos da instituição.</p>
        </div>
        <?php if ($is_authorized): ?>
        <button onclick="openModal()"
            class="flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-xl transition-all shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Novo Cargo
        </button>
        <?php endif; ?>
    </div>

    <div class="p-6 flex-1 overflow-auto">
        <table id="tabelaCargos" class="w-full text-left border-collapse" style="width:100%">
            <thead>
                <tr class="bg-slate-50">
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider rounded-l-lg">ID
                    </th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Descrição do
                        Cargo</th>
                    <th
                        class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right rounded-r-lg">
                        Ações</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal Cargo -->
<div id="modalCargo" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">

                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-800" id="modalTitle">Configurar Cargo</h3>
                    <button onclick="closeModal()"
                        class="text-slate-400 hover:bg-slate-100 p-2 rounded-lg border shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="formCargo" onsubmit="salvarCargo(event)" class="px-6 py-6 space-y-5">
                    <input type="hidden" id="cargo_id">

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nome do Cargo</label>
                        <input type="text" id="desc_cargo" required
                            class="w-full px-4 py-2 border rounded-xl focus:ring-1 focus:ring-brand-500 bg-slate-50">
                    </div>

                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeModal()"
                            class="px-5 py-2.5 border rounded-xl font-semibold">Cancelar</button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-brand-600 text-white rounded-xl font-semibold">Salvar</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
    const isAuthorized = <?php echo $is_authorized ? 'true' : 'false'; ?>;
    let tableCargo;

    $(document).ready(function () {
        tableCargo = $('#tabelaCargos').DataTable({
            ajax: { url: '../../api/cargos.php', dataSrc: 'data' },
            columns: [
                { data: 'id' },
                { data: 'cargo', className: 'font-bold text-slate-700' },
                {
                    data: null, className: 'text-right', orderable: false,
                    render: (d, t, r) => {
                        if (!isAuthorized) return '<span class="text-xs text-slate-400 italic">Somente leitura</span>';
                        return `
                            <button onclick="editar(${r.id})" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                            <button onclick="deletar(${r.id})" class="p-1.5 text-red-600 hover:bg-red-50 rounded"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                        `;
                    }
                }
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' }
        });
    });

    const modal = document.getElementById('modalCargo');
    const form = document.getElementById('formCargo');

    function openModal() {
        form.reset();
        document.getElementById('cargo_id').value = '';
        modal.classList.remove('hidden');
    }
    function closeModal() { modal.classList.add('hidden'); }

    async function editar(id) {
        const res = await fetch(`../../api/cargos.php?id=${id}`);
        const { data } = await res.json();
        document.getElementById('cargo_id').value = data.id;
        document.getElementById('desc_cargo').value = data.cargo;
        modal.classList.remove('hidden');
    }

    async function salvarCargo(e) {
        e.preventDefault();
        const id = document.getElementById('cargo_id').value;
        const payload = {
            id,
            cargo: document.getElementById('desc_cargo').value
        };

        const res = await fetch('../../api/cargos.php', {
            method: id ? 'PUT' : 'POST',
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
            closeModal(); tableCargo.ajax.reload();
        } else {
            Swal.fire('Erro', data.message, 'error');
        }
    }

    async function deletar(id) {
        const result = await Swal.fire({
            title: 'Excluir Cargo?',
            text: "Excluir este cargo?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            const res = await fetch('../../api/cargos.php', { method: 'DELETE', body: JSON.stringify({ id }) });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
                tableCargo.ajax.reload();
            } else {
                Swal.fire('Erro', data.message, 'error');
            }
        }
    }
</script>

<?php include 'layout/footer.php'; ?>