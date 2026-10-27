/* Read-only hosted two-style Web Fonts Kit rendering in isolated Chromium.
 * The Kit remains on its authorized public Amari page; no site changes.
 */
'use strict';
const assert=require('node:assert/strict');
const {spawn}=require('node:child_process');
const fs=require('node:fs'),os=require('node:os'),path=require('node:path');
const sleep=t=>new Promise(resolve=>setTimeout(resolve,t));
const directory=fs.mkdtempSync(path.join(os.tmpdir(),'fa7-css-live-'));
const binary=process.env.CHROME_BINARY||'/usr/bin/google-chrome';
const chrome=spawn(binary,['--headless=new','--no-sandbox','--disable-dev-shm-usage',
    '--remote-debugging-port=0','--user-data-dir='+directory,'about:blank'],
    {stdio:['ignore','ignore','pipe']});
let stderr='',socket;
chrome.stderr.on('data',data=>{stderr+=data.toString().slice(0,1000)});

async function run(){
    try{
        for(let i=0;i<75&&!stderr.includes('DevTools listening');i++)await sleep(200);
        const port=stderr.match(/127\.0\.0\.1:(\d+)/)?.[1];
        assert.ok(port,'Chrome debugging port unavailable');
        const pages=await(await fetch('http://127.0.0.1:'+port+'/json/list')).json();
        socket=new WebSocket(pages.find(page=>page.type==='page').webSocketDebuggerUrl);
        await new Promise((resolve,reject)=>{socket.onopen=resolve;socket.onerror=reject});
        let next=0;const waiters=new Map(),assets=[];
        socket.onmessage=event=>{
            const msg=JSON.parse(event.data);
            if(msg.id&&waiters.has(msg.id)){waiters.get(msg.id)(msg);waiters.delete(msg.id)}
            if(msg.method==='Network.responseReceived'&&
                msg.params?.response?.url.includes('fontawesome.com')){
                const info=msg.params.response;
                assets.push({path:new URL(info.url).pathname,status:info.status});
            }
        };
        const cdp=(method,params={})=>new Promise((resolve,reject)=>{
            const id=++next;waiters.set(id,resolve);
            socket.send(JSON.stringify({id,method,params}));
            setTimeout(()=>{
                if(waiters.has(id)){waiters.delete(id);reject(new Error('CDP timeout '+method))}
            },25000);
        });
        const evalJS=async expression=>{
            const response=await cdp('Runtime.evaluate',{expression,awaitPromise:true,returnByValue:true});
            assert.ok(!response.result?.exceptionDetails,
                JSON.stringify(response.result?.exceptionDetails||'unexpected exception'));
            return response.result.result.value;
        };
        await cdp('Page.enable');await cdp('Network.enable');await cdp('Runtime.enable');
        await cdp('Page.navigate',{url:'https://dev.sasseagency.com/amari/'});
        await sleep(3500);
        assert.equal(await evalJS('location.hostname'),'dev.sasseagency.com');
        const kitLoaded=await evalJS(`new Promise(resolve=>{
            const script=document.createElement('script');
            script.src='https://kit.fontawesome.com/23f6a60c28.js';
            script.onload=()=>resolve('loaded');
            script.onerror=()=>resolve('failed');
            document.head.appendChild(script);
            setTimeout(()=>resolve('timed-out'),15000);
        })`);
        assert.equal(kitLoaded,'loaded','Existing Web Fonts Kit script load');
        await sleep(3200);
        const result=JSON.parse(await evalJS(`(async()=>{
            const pairs=[
                ['brands','fa-brands fa-github','Font Awesome 7 Brands'],
                ['sharp','fa-sharp-duotone fa-regular fa-house','Font Awesome 7 Sharp Duotone']
            ];
            const holder=document.createElement('div');
            holder.id='fa7-css-qa-only';
            document.body.appendChild(holder);
            const checked=[];
            for(const [name,classes,font] of pairs){
                const el=document.createElement('i');
                el.className=classes;
                holder.appendChild(el);
                await document.fonts.load('400 24px "'+font+'"');
                const pseudo=getComputedStyle(el,'::before');
                checked.push({name,font:pseudo.fontFamily,weight:pseudo.fontWeight,
                    content:pseudo.content,
                    loaded:document.fonts.check('400 24px "'+font+'"')});
            }
            return JSON.stringify({method:window.FontAwesomeKitConfig?.method,checked});
        })()`));
        assert.equal(result.method,'css','Web Fonts tech; the embed still uses JavaScript');
        assert.equal(result.checked.length,2);
        for(const item of result.checked){
            assert.ok(item.loaded,'Font face loaded for '+item.name);
            assert.equal(item.weight,'400');
            assert.ok(item.font.includes('Font Awesome 7'));
            assert.ok(item.content!=='none'&&item.content!=='normal'&&item.content!=='""');
        }
        assert.ok(assets.some(item=>item.path.endsWith('/kit.css')&&item.status===200));
        assert.ok(assets.some(item=>item.path.includes('fa-brands')&&item.path.endsWith('.woff2')&&item.status===200));
        assert.ok(assets.some(item=>item.path.includes('sharp-duotone-regular')&&item.path.endsWith('.woff2')&&item.status===200));
        console.log('PASS: Real two-style Web Fonts Kit renders Brands and Sharp Duotone Regular with licensed Font Awesome WOFF2 files.');
    } finally {
        try{socket?.close()}catch(_){}
        chrome.kill();await sleep(300);
        try{fs.rmSync(directory,{recursive:true,force:true,maxRetries:3})}catch(_){}
    }
}
run().catch(e=>{console.error(e);process.exitCode=1});
