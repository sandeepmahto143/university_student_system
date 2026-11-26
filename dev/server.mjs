/**
 * OPTIONAL development preview server  (NOT needed for XAMPP / WAMP)
 * ------------------------------------------------------------------
 * Runs the PHP project with PHP compiled to WebAssembly so it can be
 * previewed on a machine without Apache/PHP/MySQL. It automatically
 * switches the app to SQLite demo mode by creating
 * includes/config.local.php (git-ignored).
 *
 *   cd dev && npm install && npm start      ->  http://localhost:8080
 */
import http from 'node:http';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { PHP, PHPRequestHandler, setPhpIniEntries, FileLockManagerInMemory } from '@php-wasm/universal';
import { loadNodeRuntime, createNodeFsMountHandler } from '@php-wasm/node';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, '..');
const PORT = Number(process.env.PORT || 8080);
const HOST = process.env.HOST || '0.0.0.0';
const DOC_ROOT = '/var/www/html';

// Switch to SQLite demo mode (no MySQL available here)
const localCfg = path.join(projectRoot, 'includes', 'config.local.php');
if (!fs.existsSync(localCfg)) {
    fs.copyFileSync(path.join(projectRoot, 'includes', 'config.local.php.example'), localCfg);
    console.log('Created includes/config.local.php (SQLite demo mode)');
}

const php = new PHP(
    await loadNodeRuntime('8.3', {
        fileLockManager: new FileLockManagerInMemory(),
        emscriptenOptions: { processId: 1 },
    })
);
await php.mount(DOC_ROOT, createNodeFsMountHandler(projectRoot));
const handler = new PHPRequestHandler({
    php,
    documentRoot: DOC_ROOT,
    absoluteUrl: `http://localhost:${PORT}`,
    cookieStore: false, // pass browser cookies straight through
});
// Store PHP sessions on the real disk (data/sessions) so they survive the
// periodic PHP runtime rotation that php-wasm performs every few hundred requests.
fs.mkdirSync(path.join(projectRoot, 'data', 'sessions'), { recursive: true });
await setPhpIniEntries(php, {
    display_errors: '1',
    error_reporting: 'E_ALL',
    'session.save_path': `${DOC_ROOT}/data/sessions`,
    'session.gc_maxlifetime': '86400',
    'date.timezone': 'Asia/Kolkata',
});

/**
 * The live preview is shown inside an iframe on another site, where browsers
 * refuse to send normal (SameSite=Lax) cookies. Rewrite the PHP session cookie
 * to SameSite=None; Secure; Partitioned so the login session is kept.
 * (Not needed on XAMPP, where the app is opened directly in the browser.)
 */
function crossSiteCookie(cookie) {
    const parts = cookie
        .split(';')
        .map((p) => p.trim())
        .filter((p) => p && !/^(samesite|secure|partitioned)(=|$)/i.test(p));
    return [...parts, 'SameSite=None', 'Secure', 'Partitioned'].join('; ');
}

let queue = Promise.resolve();

const server = http.createServer((req, res) => {
    const chunks = [];
    req.on('data', (c) => chunks.push(c));
    req.on('end', () => {
        // Serialize requests: one PHP instance handles them one at a time
        queue = queue.then(async () => {
            try {
                let url = req.url || '/';
                const pathname = url.split('?')[0];
                if (pathname === '/' ) url = '/index.php' + url.slice(1);

                const headers = {};
                for (const [k, v] of Object.entries(req.headers)) {
                    if (k === 'host') continue;
                    headers[k] = Array.isArray(v) ? v.join(', ') : String(v);
                }
                const response = await handler.request({
                    method: req.method,
                    url,
                    headers,
                    body: chunks.length ? new Uint8Array(Buffer.concat(chunks)) : undefined,
                });

                for (const [k, values] of Object.entries(response.headers)) {
                    if (k.toLowerCase() === 'content-length') continue;
                    if (k.toLowerCase() === 'set-cookie') {
                        res.setHeader(k, values.map(crossSiteCookie));
                        continue;
                    }
                    res.setHeader(k, values);
                }
                // Same MIME types Apache uses, so the XSLT renders in the browser
                if (/\.(xsl|dtd)$/.test(pathname)) {
                    res.setHeader('content-type', pathname.endsWith('.xsl') ? 'text/xsl' : 'application/xml-dtd');
                }
                res.statusCode = response.httpStatusCode;
                res.end(Buffer.from(response.bytes));
                console.log(req.method, req.url, response.httpStatusCode, req.headers.cookie ? '[cookie]' : '[no cookie]');
            } catch (err) {
                console.error(err);
                res.statusCode = 500;
                res.end('Internal error: ' + err.message);
            }
        });
    });
});

server.listen(PORT, HOST, () => {
    console.log(`UniSIS preview running at http://localhost:${PORT}  (login: admin / admin123)`);
});
