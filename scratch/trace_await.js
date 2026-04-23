
const fs = require('fs');
const content = fs.readFileSync('c:\\xampp\\htdocs\\sisponto\\views\\relogio.php', 'utf8');
const scriptMatch = content.match(/<script>([\s\S]*?)<\/script>/);
if (scriptMatch) {
    const js = scriptMatch[1];
    const lines = js.split('\n');
    let scopeStack = []; // stores whether the current scope is async

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        const lineNumber = i + 260;

        // Check for function start
        if (line.match(/(async\s+)?(function|=>|\([^)]*\)\s*=>)/)) {
            scopeStack.push(line.includes('async'));
        }
        
        // This is still naive but let's try to track braces
        for (let char of line) {
            if (char === '}') {
                scopeStack.pop();
            }
        }

        if (line.includes('await')) {
            const isInsideAsync = scopeStack.length > 0 && scopeStack[scopeStack.length - 1];
            if (!isInsideAsync) {
                console.log(`POTENTIAL ERROR: 'await' at line ${lineNumber} might be outside an async function.`);
                console.log(`Line context: ${line.trim()}`);
            }
        }
    }
}
