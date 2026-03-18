const http = require('http');
const fs   = require('fs');
const path = require('path');
const url  = require('url');

const PORT         = 3333;
const ROOT         = __dirname;
const CONTENT_FILE = path.join(ROOT, 'content.json');
const SECRET_TOKEN = 'lokaal';   // lokaal wachtwoord — op Hostinger save.php gebruiken
const MAX_VERSIONS = 5;

const MIME = {
    '.html': 'text/html; charset=utf-8',
    '.json': 'application/json; charset=utf-8',
    '.css':  'text/css',
    '.js':   'text/javascript',
    '.php':  'text/plain',   // PHP niet uitvoeren — via dit script afgehandeld
};

const server = http.createServer((req, res) => {
    const parsed   = url.parse(req.url, true);
    const pathname = parsed.pathname;

    // CORS
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    if (req.method === 'OPTIONS') { res.writeHead(204); res.end(); return; }

    // ── Save endpoint (vervangt save.php lokaal) ────────────────────────────
    if (pathname === '/save.php' && req.method === 'POST') {
        const auth = req.headers['authorization'] ?? '';
        if (auth !== 'Bearer ' + SECRET_TOKEN) {
            res.writeHead(401, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ error: 'Niet geautoriseerd' }));
            return;
        }

        let body = '';
        req.on('data', chunk => body += chunk);
        req.on('end', () => {
            try {
                const input   = JSON.parse(body);
                const action  = input.action;
                const field   = input.field;
                const data    = JSON.parse(fs.readFileSync(CONTENT_FILE, 'utf8'));

                if (!data.content[field]) {
                    res.writeHead(400, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ error: `Onbekend veld: ${field}` }));
                    return;
                }

                if (action === 'save') {
                    data.content[field].versions.unshift({
                        value:     input.value ?? '',
                        timestamp: new Date().toISOString(),
                        note:      input.note ?? ''
                    });
                    data.content[field].versions =
                        data.content[field].versions.slice(0, MAX_VERSIONS);
                    const envs = ['productie','staging'];
                    envs.forEach(env => {
                        const cur = (data.content[field].active_index && data.content[field].active_index[env]) || 0;
                        data.content[field].active_index[env] = Math.min(cur + 1, MAX_VERSIONS - 1);
                    });
                    data.last_updated = new Date().toISOString();

                } else if (action === 'activate') {
                    const env   = input.env || 'productie';
                    const index = parseInt(input.index, 10);
                    const max   = data.content[field].versions.length - 1;
                    if (index < 0 || index > max) {
                        res.writeHead(400, { 'Content-Type': 'application/json' });
                        res.end(JSON.stringify({ error: `Ongeldige index: ${index}` }));
                        return;
                    }
                    if (!data.content[field].active_index || typeof data.content[field].active_index !== 'object') {
                        data.content[field].active_index = { productie: 0, staging: 0 };
                    }
                    data.content[field].active_index[env] = index;
                    data.last_updated = new Date().toISOString();

                } else {
                    res.writeHead(400, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ error: `Onbekende actie: ${action}` }));
                    return;
                }

                fs.writeFileSync(
                    CONTENT_FILE,
                    JSON.stringify(data, null, 2),
                    'utf8'
                );

                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ success: true, data }));

            } catch (e) {
                res.writeHead(500, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: e.message }));
            }
        });
        return;
    }

    // ── Statische bestanden ─────────────────────────────────────────────────
    let filePath = path.join(ROOT, pathname === '/' ? 'index.html' : pathname);
    const ext    = path.extname(filePath);

    fs.readFile(filePath, (err, data) => {
        if (err) {
            res.writeHead(404, { 'Content-Type': 'text/plain' });
            res.end('Niet gevonden: ' + pathname);
            return;
        }
        res.writeHead(200, { 'Content-Type': MIME[ext] ?? 'text/plain' });
        res.end(data);
    });
});

server.listen(PORT, () => {
    console.log(`\n✅  Beheer interface actief op: http://localhost:${PORT}`);
    console.log(`🔑  Lokaal wachtwoord: lokaal`);
    console.log(`📄  Content bestand:   ${CONTENT_FILE}`);
    console.log(`\n    Stop de server met Ctrl+C\n`);
});
