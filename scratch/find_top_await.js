
const fs = require('fs');
const content = fs.readFileSync('c:\\xampp\\htdocs\\sisponto\\views\\relogio.php', 'utf8');
const scriptMatch = content.match(/<script>([\s\S]*?)<\/script>/);
if (scriptMatch) {
    const js = scriptMatch[1];
    const lines = js.split('\n');
    let braceLevel = 0;
    let inString = null;
    let templateBraceLevels = [];

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        
        // Simple brace counting (ignoring comments and strings for better accuracy if possible)
        // But for a quick check, let's just look for 'await' when braceLevel is 0
        
        const refinedLine = line.replace(/\/\/.*$/, ''); // ignore single line comments
        
        // Update brace level (very naive, but might work for finding top-level)
        for (const char of refinedLine) {
            if (char === '{') braceLevel++;
            if (char === '}') braceLevel--;
        }

        if (line.includes('await') && braceLevel === 0) {
            // Check if it's really top level or just naive counting error
            console.log(`Potential top-level await at index line ${i + 260}: ${line.trim()}`);
        }
    }
}
