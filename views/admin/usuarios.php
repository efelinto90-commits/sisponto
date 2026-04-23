<?php include 'layout/header.php'; ?>

<?php
// Garantir que a pessoa atual tenha permissão realística de abrir essa view
$is_crh = (in_array(strtolower(trim($_SESSION['user_name'] ?? '')), ['corsin', 'crh']) || in_array(strtolower(trim($_SESSION['user_setor'] ?? '')), ['corsin', 'crh']));
if (!hasPerm('usuarios') && !$is_crh && $_SESSION['user_level'] != '1') {
    echo "<div class='p-8 text-center text-red-500 font-bold'>Você não tem permissão para acessar esta página.</div>";
    include 'layout/footer.php';
    exit;
}

require_once dirname(__DIR__, 2) . '/config/Database.php';
try {
    $conn = \Config\Database::getConnection();
    $stmt = $conn->query("SELECT DISTINCT setor FROM funcionarios WHERE setor IS NOT NULL AND TRIM(setor) != '' ORDER BY setor ASC");
    $setores = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $setores = [];
}
?>

<div
    class="max-w-7xl mx-auto flex flex-col h-full bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Usuários do Sistema</h1>
            <p class="text-sm text-slate-500 mt-1">Gerencie os administradores e suas obrigações (permissões de acesso).
            </p>
        </div>
        <div class="flex items-center gap-3">
            <?php if (in_array($_SESSION['user_level'], ['1', '2']) || $is_crh): ?>
                <button onclick="openPresetsModal()"
                    class="flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition-all shadow-sm border border-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                        </path>
                    </svg>
                    Permissões por Categoria
                </button>
                <button onclick="openModal()"
                    class="flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-xl transition-all shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Novo Usuário
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="p-6 flex-1 overflow-auto">
        <table id="tabelaUsuarios" class="w-full text-left border-collapse" style="width:100%">
            <thead>
                <tr class="bg-slate-50">
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider rounded-l-lg">ID
                    </th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Usuário</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Setor</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Categoria</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Permissões
                        (Obrigações)</th>
                    <th
                        class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right rounded-r-lg">
                        Ações</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal Usuario -->
<div id="modalUsuario" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-100">

                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-800" id="modalTitle">Configurar Usuário</h3>
                    <button onclick="closeModal()"
                        class="text-slate-400 hover:bg-slate-100 p-2 rounded-lg border shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="formUsuario" onsubmit="salvarUsuario(event)" class="px-6 py-6 space-y-6">
                    <input type="hidden" id="user_id">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nome de Usuário</label>
                            <input type="text" id="user_name" required
                                class="w-full px-4 py-2 border rounded-xl focus:ring-1 focus:ring-brand-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Categoria (Nível)</label>
                            <select id="user_level" onchange="applyPreset(this.value)"
                                class="w-full px-4 py-2 border rounded-xl focus:ring-1 focus:ring-brand-500 bg-slate-50">
                                <option value="1" <?php echo $_SESSION['user_level'] != '1' ? 'disabled' : ''; ?>>Administrador</option>
                                <option value="2">Gestor</option>
                                <option value="3" selected>Usuário Comum</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Setor Associado</label>
                        <select id="user_setor"
                            class="w-full px-4 py-2 border rounded-xl focus:ring-1 focus:ring-brand-500 bg-slate-50">
                            <option value="">Acesso Geral (Todos os Setores)</option>
                            <?php foreach ($setores as $setor): ?>
                                <option value="<?= htmlspecialchars($setor) ?>"><?= htmlspecialchars($setor) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Senha <span id="hintSenha"
                                class="text-xs text-slate-400 font-normal"></span></label>
                        <div class="relative">
                            <input type="password" id="user_password"
                                class="w-full pl-4 pr-10 py-2 border rounded-xl focus:ring-1 focus:ring-brand-500 bg-slate-50 transition-all">
                            <button type="button" onclick="togglePassword('user_password', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-brand-600 transition-colors focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-slate-700 mb-3 border-b pb-2">Lista de Obrigações (Acesso
                            Restrito ao Menu)</h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2" id="boxPermissoes">
                            <label
                                class="flex items-center gap-3 p-3 border rounded-xl bg-slate-50 hover:bg-brand-50 cursor-pointer transition-colors">
                                <input type="checkbox" value="funcionarios"
                                    class="perm-checkbox w-5 h-5 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                                <span class="text-sm font-medium text-slate-800">Gerir Funcionários</span>
                            </label>

                            <label
                                class="flex items-center gap-3 p-3 border rounded-xl bg-slate-50 hover:bg-brand-50 cursor-pointer transition-colors">
                                <input type="checkbox" value="relatorios"
                                    class="perm-checkbox w-5 h-5 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                                <span class="text-sm font-medium text-slate-800">Visualizar Relatórios</span>
                            </label>

                            <label
                                class="flex items-center gap-3 p-3 border rounded-xl bg-slate-50 hover:bg-brand-50 cursor-pointer transition-colors">
                                <input type="checkbox" value="horarios"
                                    class="perm-checkbox w-5 h-5 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                                <span class="text-sm font-medium text-slate-800">Configurar Horários</span>
                            </label>

                            <label
                                class="flex items-center gap-3 p-3 border rounded-xl bg-slate-50 hover:bg-brand-50 cursor-pointer transition-colors">
                                <input type="checkbox" value="cargos"
                                    class="perm-checkbox w-5 h-5 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                                <span class="text-sm font-medium text-slate-800">Gerir Cargos</span>
                            </label>

                            <label
                                class="flex items-center gap-3 p-3 border rounded-xl bg-red-50 hover:bg-red-100 cursor-pointer transition-colors border-red-200">
                                <input type="checkbox" value="usuarios"
                                    class="perm-checkbox w-5 h-5 text-red-600 rounded border-red-300 focus:ring-red-500">
                                <span class="text-sm font-medium text-red-800">Cadastro de Usuários do Sistema</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeModal()"
                            class="px-5 py-2.5 border rounded-xl font-semibold">Cancelar</button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-brand-600 text-white rounded-xl font-semibold shadow-sm">Salvar
                            Usuário</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- Modal Presets de Categoria -->
<div id="modalPresets" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closePresetsModal()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-slate-100">

                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-800">Acessos Padrão por Categoria</h3>
                    <button onclick="closePresetsModal()"
                        class="text-slate-400 hover:bg-slate-100 p-2 rounded-lg border shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-6 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Módulo</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Administrador</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Gestor</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Usuário Comum</th>
                            </tr>
                        </thead>
                        <tbody id="bodyPresets">
                            <!-- Inserido via JS -->
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex justify-end gap-3 p-6 border-t border-slate-100">
                    <button type="button" onclick="closePresetsModal()"
                        class="px-5 py-2.5 border rounded-xl font-semibold">Cancelar</button>
                    <button type="button" onclick="salvarPresets()"
                        class="px-5 py-2.5 bg-brand-600 text-white rounded-xl font-semibold shadow-sm">Salvar Padrões</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let tableUser;

    // Nomes simpáticos para as permissões na tabela
    const permNames = {
        'funcionarios': 'Funcionários',
        'relatorios': 'Relatórios',
        'horarios': 'Horários',
        'cargos': 'Cargos',
        'usuarios': 'Usuários (Admin)'
    };

    const levelNames = {
        '1': '<span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-bold uppercase tracking-wider">Administrador</span>',
        '2': '<span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-[10px] font-bold uppercase tracking-wider">Gestor</span>',
        '3': '<span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-full text-[10px] font-bold uppercase tracking-wider">Usuário</span>'
    };

    let permissionPresets = {};

    $(document).ready(function () {
        tableUser = $('#tabelaUsuarios').DataTable({
            ajax: { url: '../../api/usuarios.php', dataSrc: 'data' },
            columns: [
                { data: 'id' },
                { data: 'name', className: 'font-bold text-slate-700' },
                { 
                    data: 'setor', 
                    render: (d) => d ? `<span class="px-2 py-1 bg-slate-100 border text-slate-600 text-[11px] rounded uppercase font-semibold">${d}</span>` : '<span class="text-xs text-slate-400 italic">Acesso Geral</span>' 
                },
                {
                    data: 'level',
                    render: (d) => levelNames[d] || d
                },
                {
                    data: 'permissoes',
                    render: function (data) {
                        try {
                            const perms = JSON.parse(data || '[]');
                            if (perms.length === 0) return '<span class="text-slate-400 text-sm">Apenas Dashboard</span>';

                            return perms.map(p =>
                                `<span class="inline-block px-2 py-1 bg-slate-100 border text-slate-600 text-xs rounded shadow-sm mr-1 mb-1">${permNames[p] || p}</span>`
                            ).join('');
                        } catch (e) { return '-'; }
                    }
                },
                {
                    data: null, className: 'text-right', orderable: false,
                    render: (d, t, r) => {
                        const currentUserLevel = "<?php echo $_SESSION['user_level']; ?>";
                        const isCrh = <?php echo $is_crh ? 'true' : 'false'; ?>;
                        if (['1', '2'].indexOf(currentUserLevel) === -1 && !isCrh) return '-';
                        
                        // Se for Admin e eu não for Admin, não posso mexer
                        if (r.level == '1' && currentUserLevel != '1') return '<span class="text-[10px] text-slate-400 italic">Restrito</span>';

                        return `
                            <button onclick="editar(${r.id})" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded" title="Editar Usuário"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                            <button onclick="deletar(${r.id})" class="p-1.5 text-red-600 hover:bg-red-50 rounded" title="Remover Usuário"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                        `;
                    }
                }
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' }
        });

        loadPresets();
    });

    const modal = document.getElementById('modalUsuario');
    const modalPresets = document.getElementById('modalPresets');
    const form = document.getElementById('formUsuario');
    const hint = document.getElementById('hintSenha');

    async function loadPresets() {
        const res = await fetch('../../api/usuarios.php?action=get_presets');
        const { data } = await res.json();
        permissionPresets = {};
        data.forEach(p => {
            permissionPresets[p.level] = JSON.parse(p.permissoes || '[]');
        });
    }

    function openPresetsModal() {
        const body = document.getElementById('bodyPresets');
        body.innerHTML = '';

        Object.keys(permNames).forEach(pKey => {
            let row = `<tr class="border-b last:border-0">
                <td class="px-4 py-3 text-sm font-medium text-slate-700">${permNames[pKey]}</td>`;

            [1, 2, 3].forEach(lvl => {
                const checked = permissionPresets[lvl] && permissionPresets[lvl].includes(pKey) ? 'checked' : '';
                row += `<td class="px-4 py-3 text-center">
                    <input type="checkbox" class="preset-check w-5 h-5 text-brand-600 rounded border-slate-300" 
                    data-level="${lvl}" data-perm="${pKey}" ${checked}>
                </td>`;
            });

            row += `</tr>`;
            body.innerHTML += row;
        });

        modalPresets.classList.remove('hidden');
    }

    function closePresetsModal() { modalPresets.classList.add('hidden'); }

    async function salvarPresets() {
        const presets = [
            { level: 1, permissoes: [] },
            { level: 2, permissoes: [] },
            { level: 3, permissoes: [] }
        ];

        $('.preset-check:checked').each(function () {
            const lvl = $(this).data('level');
            const perm = $(this).data('perm');
            const pObj = presets.find(x => x.level == lvl);
            if (pObj) pObj.permissoes.push(perm);
        });

        const res = await fetch('../../api/usuarios.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'save_presets', presets })
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
            closePresetsModal();
            loadPresets();
        } else {
            Swal.fire('Erro', data.message, 'error');
        }
    }

    function applyPreset(level) {
        if (!permissionPresets[level]) return;
        $('.perm-checkbox').prop('checked', false);
        permissionPresets[level].forEach(p => {
            $(`.perm-checkbox[value="${p}"]`).prop('checked', true);
        });
    }

    function openModal() {
        form.reset();
        document.getElementById('user_id').value = '';
        document.getElementById('user_level').value = '3';
        document.getElementById('user_setor').value = '';
        document.getElementById('user_password').required = true;
        hint.textContent = '(Obrigatório para novo usuário)';
        $('.perm-checkbox').prop('checked', false);
        modal.classList.remove('hidden');
    }

    function closeModal() { modal.classList.add('hidden'); }

    async function editar(id) {
        const res = await fetch(`../../api/usuarios.php?id=${id}`);
        const { data } = await res.json();

        document.getElementById('user_id').value = data.id;
        document.getElementById('user_name').value = data.name;
        document.getElementById('user_level').value = data.level || '3';
        document.getElementById('user_setor').value = data.setor || '';
        document.getElementById('user_password').required = false;
        hint.textContent = '(Deixe em branco para manter a atual)';

        // Marcar os checkboxes
        $('.perm-checkbox').prop('checked', false);
        try {
            const perms = JSON.parse(data.permissoes || '[]');
            perms.forEach(p => {
                $(`.perm-checkbox[value="${p}"]`).prop('checked', true);
            });
        } catch (e) { }

        modal.classList.remove('hidden');
    }

    async function salvarUsuario(e) {
        e.preventDefault();
        const id = document.getElementById('user_id').value;

        // Coletar as permissões checadas
        const selectedPerms = [];
        $('.perm-checkbox:checked').each(function () {
            selectedPerms.push($(this).val());
        });

        const payload = {
            id,
            name: document.getElementById('user_name').value,
            password: document.getElementById('user_password').value,
            level: document.getElementById('user_level').value,
            setor: document.getElementById('user_setor').value,
            permissoes: selectedPerms
        };

        const res = await fetch('../../api/usuarios.php', {
            method: id ? 'PUT' : 'POST',
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
            closeModal(); tableUser.ajax.reload();
        } else {
            Swal.fire('Erro', data.message, 'error');
        }
    }

    async function deletar(id) {
        const result = await Swal.fire({
            title: 'Excluir Usuário?',
            text: "Atenção: A exclusão de usuários é permanente. Confirmar?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            const res = await fetch('../../api/usuarios.php', { method: 'DELETE', body: JSON.stringify({ id }) });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
                tableUser.ajax.reload();
            } else {
                Swal.fire('Erro', data.message, 'error');
            }
        }
    }
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('svg');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />`;
        } else {
            input.type = 'password';
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
        }
    }
</script>

<?php include 'layout/footer.php'; ?>