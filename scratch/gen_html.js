const fs = require('fs');
let html = '<html><body>';
['image1.png','image2.png','image3.jpeg','image4.png'].forEach(f => {
    try {
        const b64 = fs.readFileSync('timbre/extracted/word/media/' + f).toString('base64');
        const mime = f.endsWith('png') ? 'image/png' : 'image/jpeg';
        html += '<h2>' + f + '</h2><img src="data:' + mime + ';base64,' + b64 + '" style="max-width:500px; border:1px solid #ccc;"/><br/>';
    } catch(e) {}
});
html += '</body></html>';
fs.writeFileSync('scratch/view_images.html', html);
