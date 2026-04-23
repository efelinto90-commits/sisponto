
const fs = require('fs');
const content = fs.readFileSync('c:\\xampp\\htdocs\\sisponto\\views\\relogio.php', 'utf8');
const scriptMatch = content.match(/<script>([\s\S]*?)<\/script>/);
if (scriptMatch) {
    const js = scriptMatch[1];
    const lines = js.split('\n');
    let functions = [];
    let currentFunction = null;

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        const lineNumber = i + 260;

        // Detect function start
        const funcMatch = line.match(/(async\s+)?function\s+([a-zA-Z0-9_]+)?\s*\(/) || 
                          line.match(/([a-zA-Z0-9_.]+)\s*=\s*(async\s+)?function\s*\(/) ||
                          line.match(/([a-zA-Z0-9_.]+)\s*=\s*(async\s+)?\(/);
        
        if (funcMatch) {
            currentFunction = {
                name: funcMatch[2] || funcMatch[1] || 'anonymous',
                isAsync: line.includes('async'),
                startLine: lineNumber
            };
            functions.push(currentFunction);
        }

        if (line.includes('await')) {
            console.log(`Checking await at line ${lineNumber}: ${line.trim()}`);
            // Check if it's inside any async function found so far
            // This is naive because it doesn't track closing braces, 
            // but let's see which functions they are near.
        }
    }
}
