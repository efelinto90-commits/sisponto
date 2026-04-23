
const fs = require('fs');
const content = fs.readFileSync('c:\\xampp\\htdocs\\sisponto\\views\\relogio.php', 'utf8');
const scriptMatch = content.match(/<script>([\s\S]*?)<\/script>/);
if (scriptMatch) {
    fs.writeFileSync('c:\\xampp\\htdocs\\sisponto\\scratch\\extracted.js', scriptMatch[1]);
    console.log("Extracted JS to sisponto/scratch/extracted.js");
}
