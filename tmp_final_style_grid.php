<?php
$f = 'c:/xampp/htdocs/sisponto/views/relogio.php';
$c = file_get_contents($f);

// 1. Fix Section numbering and apply styling
// Renumber "5. Dados BancÃ¡rios" to Section 6
$c = str_replace('<!-- SeÃ§Ã£o 5: Dados BancÃ¡rios -->', '<!-- SeÃ§Ã£o 6: Dados BancÃ¡rios -->', $c);
$c = str_replace('5. Dados BancÃ¡rios</h3>', '6. Dados BancÃ¡rios</h3>', $c);

// Renumber "4. Quadro de HorÃ¡rios Semanal" to Section 5
// AND apply the E-Social styling to it
$newSection5 = '<!-- SeÃ§Ã£o 5: Quadro de HorÃ¡rios Semanal (Estilo E-Social) -->
                        <div class="pt-6 border-t border-slate-100">
                            <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3 mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest">5. Quadro de HorÃ¡rios Semanal <span class="text-red-500 ml-1">*</span></h3>
                                </div>
                                <button type="button" onclick="replicarSegundaRelogio()" class="text-[9px] font-black text-brand-600 hover:text-brand-700 uppercase tracking-widest flex items-center gap-1 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                    Replicar Segunda
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="upd_grid_container">
                                ${["segunda", "terca", "quarta", "quinta", "sexta", "sabado", "domingo"].map(dia => {
                                    const labels = {segunda:"Segunda", terca:"TerÃ§a", quarta:"Quarta", quinta:"Quinta", sextas:"Sexta", sabado:"SÃ¡bado", domingo:"Domingo"};
                                    const currentVal = func.grade_horarios ? func.grade_horarios[dia] : "";
                                    return `
                                        <div class="group">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">${labels[dia] || dia.charAt(0).toUpperCase() + dia.slice(1)}</label>
                                            <select id="upd_grade_${dia}" class="w-full px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold outline-none focus:border-brand-500 transition-all">
                                                <option value="">-- Selecione --</option>
                                                \${listaHorarios.map(h => \`<option value="\${h.id}" \${currentVal == h.id ? "selected" : ""}>\${h.nome}</option>\`).join("")}
                                            </select>
                                        </div>
                                    `;
                                }).join("")}
                            </div>
                        </div>';

// Need to match the OLD Section 4 (Quadro) block precisely to replace it
// Use "4. Quadro de Hor" to locate it.
$quadroStartPat = '4. Quadro de Hor';
$quadroPos = strpos($c, $quadroStartPat);
if ($quadroPos !== false) {
    $realStart = strrpos(substr($c, 0, $quadroPos), '<!--');
    $nextCommentPos = strpos($c, '<!--', $quadroPos);
    
    if ($realStart !== false && $nextCommentPos !== false) {
        $oldQuadroBlock = substr($c, $realStart, $nextCommentPos - $realStart);
        $c = str_replace($oldQuadroBlock, $newSection5 . "\n\n                        ", $c);
    }
}

// 2. Add Replicate Function logic
if (strpos($c, 'window.replicarSegundaRelogio') === false) {
    $funcCode = "
    window.replicarSegundaRelogio = function() {
        const seg = document.getElementById('upd_grade_segunda').value;
        if (!seg) {
            Swal.showValidationMessage('Selecione primeiro o horÃ¡rio de Segunda-feira.');
            return;
        }
        ['terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'].forEach(dia => {
            const el = document.getElementById('upd_grade_' + dia);
            if (el) el.value = seg;
        });
    };";
    $c = str_replace('window.consultarCEPRelogio = async function() {', $funcCode . "\n    window.consultarCEPRelogio = async function() {", $c);
}

file_put_contents($f, $c);
echo "Renumbering and E-Social styling finalized in relogio.php.\n";
?>
