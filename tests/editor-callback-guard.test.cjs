const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname,'../hashe-fa7-elementor/assets/js/editor-callback-guard.js'),'utf8');

function makeLibrary() {
    return {
        notifyCallback:null,
        initIconType(settings, callback) {
            this.notifyCallback=callback;
            setTimeout(()=>this.runCallback(settings), settings.delay);
        },
        runCallback(settings) {
            if (typeof this.notifyCallback === 'function') return this.notifyCallback(settings);
        }
    };
}

async function runTest() {
    const library=makeLibrary();
    const w={elementor:{iconManager:{library}},setInterval,clearInterval};
    vm.runInNewContext(source,{window:w});
    assert.equal(library.__hasheFa7CallbackGuard,true);

    const received = [];
    const promises = [
        ['hashe-fa7-fas',75],
        ['hashe-fa7-fat',10],
        ['hashe-fa7-fasds',40],
    ].map(([name,delay])=>new Promise(resolve=>{
        library.initIconType({name,delay},loaded=>{
            received.push([name,loaded.name]);
            resolve();
        });
    }));
    await Promise.race([Promise.all(promises),new Promise((_,reject)=>setTimeout(()=>reject(new Error('callback race timeout')),600))]);
    for(const [expected,actual] of received) assert.equal(actual,expected);
    assert.equal(received.length,3);
    console.log('PASS: Three concurrent plugin catalogs resolve to their correct callbacks.');

    const existing=[];
    library.initIconType({name:'fa-solid',delay:10},settings=>existing.push(settings.name));
    await new Promise(resolve=>setTimeout(resolve,45));
    assert.deepEqual(existing,['fa-solid']);
    console.log('PASS: Elementor native callbacks are untouched.');

    const names = fs.readFileSync(path.join(__dirname,'../hashe-fa7-elementor/hashe-fa7-elementor.php'),'utf8');
    assert.ok(!names.includes("hashe_fa7_editor_icon_names"));
    assert.ok(!names.includes("'icons'         =>"));
    console.log('PASS: v1.0.1 synchronous catalog injection is absent.');
}
runTest().catch(error=>{console.error(error);process.exitCode=1});
