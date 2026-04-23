
const fs = require('fs');
const content = fs.readFileSync('c:\\xampp\\htdocs\\sisponto\\views\\relogio.php', 'utf8');
const scriptMatch = content.match(/<script>([\s\S]*?)<\/script>/);
if (scriptMatch) {
    const js = scriptMatch[1];
    try {
        new Function(js);
        console.log("No syntax errors found.");
    } catch (e) {
        console.log("ERROR_MESSAGE:" + e.message);
        // We can't easily get the line number for new Function() errors in Node.js
        // But we can try to evaluate it part by part to narrow down.
        console.log("Stack:");
        console.log(e.stack);
    }
}
