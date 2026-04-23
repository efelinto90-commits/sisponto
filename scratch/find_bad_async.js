
const fs = require('fs');
const content = fs.readFileSync('c:\\xampp\\htdocs\\sisponto\\views\\relogio.php', 'utf8');
const scriptMatch = content.match(/<script>([\s\S]*?)<\/script>/);
if (scriptMatch) {
    const js = scriptMatch[1];
    const lines = js.split('\n');
    
    // Find all blocks of code that look like functions
    // We'll use a naive approach: find function start and try to find where it ends by brace counting
    for (let i = 0; i < lines.length; i++) {
        if (lines[i].includes('function') || lines[i].includes('=>')) {
            let start = i;
            let end = -1;
            let count = 0;
            let foundOpen = false;
            for (let j = i; j < lines.length; j++) {
                for (let char of lines[j]) {
                    if (char === '{') { count++; foundOpen = true; }
                    if (char === '}') { count--; }
                }
                if (foundOpen && count === 0) {
                    end = j;
                    break;
                }
            }
            if (end !== -1) {
                const block = lines.slice(start, end + 1).join('\n');
                try {
                    // We wrap it in async to avoid "await only in async" if it IS async
                    // But we want to see if an oridinary function has await
                    if (!block.includes('async')) {
                        if (block.includes('await')) {
                            console.log(`ERROR: Non-async function block starting at line ${start + 260} contains await!`);
                            console.log(block);
                        }
                    }
                } catch (e) {}
                i = end; // Skip to end of function
            }
        }
    }
}
