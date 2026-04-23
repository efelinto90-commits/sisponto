
const fs = require('fs');
const content = fs.readFileSync('c:\\xampp\\htdocs\\sisponto\\views\\admin\\funcionario_form.php', 'utf8');
const scriptMatch = content.match(/<script>([\s\S]*?)<\/script>/); // Simplistic match for first script block
if (scriptMatch) {
    const js = scriptMatch[1];
    fs.writeFileSync('c:\\xampp\\htdocs\\sisponto\\scratch\\extracted_form.js', js);
    console.log("Extracted JS to sisponto/scratch/extracted_form.js");
}
