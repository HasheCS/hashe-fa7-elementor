/* Real read-only Font Awesome SVG+JS hosted Kit browser-rendering check.
 * Tests public Mandviwalla Mauser site in isolated headless Chromium.
 * Never changes WordPress, a Kit, or any user account and never packages Pro SVGs.
 */
'use strict';
const assert = require('node:assert/strict');
const {spawn} = require('node:child_process');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');

const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));
const directory = fs.mkdtempSync(path.join(os.tmpdir(), 'fa7-public-kit-svg-'));
const binary = process.env.CHROME_BINARY ||
    process.env.BROWSER_BINARY || '/usr/bin/google-chrome';
const chrome = spawn(binary, [
    '--headless=new','--no-sandbox','--disable-dev-shm-usage',
    '--remote-debugging-port=0','--user-data-dir=' + directory,'about:blank'
], {stdio:['ignore','ignore','pipe']});
let stderr = '', socket;
chrome.stderr.on('data', data => { stderr += data.toString().slice(0,1200); });

async function run() {
    try {
        for (let i=0;i<75 && !stderr.includes('DevTools listening');i++) {
            await sleep(200);
        }
        const port = stderr.match(/127\.0\.0\.1:(\d+)/)?.[1];
        assert.ok(port, 'Isolated Chrome must expose a DevTools endpoint');
        const pages = await (await fetch('http://127.0.0.1:' + port + '/json/list')).json();
        socket = new WebSocket(pages.find(page => page.type === 'page').webSocketDebuggerUrl);
        await new Promise((resolve,reject)=>{
            socket.onopen = resolve;
            socket.onerror = reject;
        });
        let sequence = 0;
        const waiting = new Map();
        const assets = [];
        socket.onmessage = event => {
            const message = JSON.parse(event.data);
            if (message.id && waiting.has(message.id)) {
                waiting.get(message.id)(message);
                waiting.delete(message.id);
            }
            if (message.method === 'Network.responseReceived' &&
                message.params?.response?.url.includes('fontawesome.com')) {
                const response = message.params.response;
                assets.push({path:new URL(response.url).pathname,status:response.status});
            }
        };
        const cdp = (method,params={}) => new Promise((resolve,reject)=>{
            const id = ++sequence;
            waiting.set(id,resolve);
            socket.send(JSON.stringify({id,method,params}));
            setTimeout(()=>{
                if (waiting.has(id)) {
                    waiting.delete(id);
                    reject(new Error('Chrome CDP timeout: ' + method));
                }
            },25000);
        });
        const evaluate = async expression => {
            const out = await cdp('Runtime.evaluate',{
                expression,awaitPromise:true,returnByValue:true
            });
            assert.ok(!out.result?.exceptionDetails,
                JSON.stringify(out.result?.exceptionDetails||'no JS exception'));
            return out.result.result.value;
        };
        await cdp('Page.enable');
        await cdp('Network.enable');
        await cdp('Runtime.enable');
        await cdp('Page.navigate',{url:'https://mandviwallamauser.com/'});
        await sleep(3500);
        const page = await evaluate('location.hostname');
        assert.equal(page,'mandviwallamauser.com');

        // WP Rocket intentionally delays the Kit. Explicitly inject the site's
        // *existing public Kit ID* in a disposable browser, never site storage.
        const state = await evaluate(`new Promise(resolve=>{
            const script=document.createElement('script');
            script.src='https://kit.fontawesome.com/7ba505d0c6.js';
            script.onload=()=>resolve('loaded');
            script.onerror=()=>resolve('failed');
            document.head.appendChild(script);
            setTimeout(()=>resolve('timed-out'),15000);
        })`);
        assert.equal(state,'loaded','Domain-authorized public SVG Kit must load');
        await sleep(4500);
        const kit = JSON.parse(await evaluate(
            'JSON.stringify({method:window.FontAwesomeKitConfig?.method,' +
            'version:window.FontAwesomeKitConfig?.version})'
        ));
        assert.equal(kit.method,'js','SVG technology is independent of JS embed');
        assert.match(kit.version,/^7\./);

        const result = JSON.parse(await evaluate(`new Promise(resolve=>{
            const holder=document.createElement('div');
            holder.id='fa7-isolated-svg-qa';
            for(const classNames of ['fa-solid fa-house','fa-brands fa-github']){
                const el=document.createElement('i');
                el.className=classNames;
                holder.appendChild(el);
            }
            document.body.appendChild(holder);
            let polls=0;
            const timer=setInterval(()=>{
                const svg=[...holder.querySelectorAll('svg.svg-inline--fa')];
                if(svg.length===2||++polls>100){
                    clearInterval(timer);
                    resolve(JSON.stringify({count:svg.length,
                        prefixes:svg.map(x=>x.getAttribute('data-prefix')),
                        icons:svg.map(x=>x.getAttribute('data-icon'))}));
                }
            },100);
        })`));
        assert.equal(result.count,2,'Full SVG Pro Kit must create both inline SVG icons');
        assert.deepEqual(result.prefixes,['fas','fab']);
        assert.deepEqual(result.icons,['house','github']);
        for(const asset of ['7ba505d0c6.js','/js/pro.min.js',
                               '/svgs/solid/house.svg','/svgs/brands/github.svg']) {
            assert.ok(assets.some(x=>x.path.endsWith(asset) && x.status===200),
                'Official Kit asset must be retrieved HTTP 200: '+asset);
        }
        console.log('PASS: Actual SVG+JS Pro Kit rendered solid house and Brands GitHub as inline SVG; licensed assets supplied by Font Awesome.');
    } finally {
        try { socket?.close(); } catch (_) {}
        chrome.kill();
        await sleep(300);
        try { fs.rmSync(directory,{recursive:true,force:true,maxRetries:3}); } catch (_) {}
    }
}
run().catch(error=>{console.error(error);process.exitCode=1});
