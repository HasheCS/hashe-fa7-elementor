const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../hashe-fa7-elementor/assets/js/editor-callback-guard.js'), 'utf8');

const quotaBytes = 1200000;
const raw = new Map();
const unrelated = { name: 'site-settings', data: 'preserve this value' };
const stale = {};
stale['elementor_hashe-fa7-fas_icons'] = { icons: Array(5000).fill('previously-cached-icon') };
stale['elementor_hashe-fa7-fat_icons'] = { icons: Array(5000).fill('previously-cached-icon') };
stale['elementor_fa-solid_icons'] = unrelated;
stale['__expiration'] = {'elementor_hashe-fa7-fat_icons': Date.now() + 100000};
raw.set('elementor', JSON.stringify(stale));

const localStorage = {
    getItem(key) { return raw.get(key) ?? null; },
    setItem(key, value) {
        const nextBytes = [...raw.entries()].filter(([name]) => name !== key)
            .reduce((bytes, [name, content]) => bytes + name.length + content.length, key.length + value.length);
        if (nextBytes > quotaBytes) {
            const error = new Error('The quota has been exceeded.');
            error.name = 'QuotaExceededError';
            throw error;
        }
        raw.set(key, value);
    }
};

const store = {
    getKey(lib) { return 'elementor_' + (lib.name || lib) + '_icons'; },
    get(key) { return JSON.parse(localStorage.getItem('elementor'))[key]; },
    save(lib) {
        const saved = JSON.parse(localStorage.getItem('elementor'));
        saved[this.getKey(lib)] = lib;
        localStorage.setItem('elementor', JSON.stringify(saved));
    },
    getIcons(lib) { return this.get(this.getKey(lib))?.icons || false; }
};

const library = {
    notifyCallback: null,
    loaded: {},
    initIconType(settings, callback) {
        this.notifyCallback = callback;
        if (this.loaded[settings.name]) {
            return this.runCallback({...settings, icons: store.getIcons(settings)});
        }
        setTimeout(() => {
            const icons = Object.fromEntries(Array.from({length: settings.name.startsWith('hashe-fa7-') ? 3781 : 24}, (_,i) => [
                'icon-' + i, {prefix:'fa-', selector:'fa-icon-' + i, name:'Icon ' + i,
                    filter:'icon-' + i, displayPrefix:'fa-thin'}
            ]));
            const normalized = {...settings, icons};
            store.save(normalized);
            this.loaded[settings.name] = true;
            this.runCallback(normalized);
        }, settings.delay || 0);
    },
    runCallback(settings) {
        if (typeof this.notifyCallback === 'function') {
            return this.notifyCallback(settings);
        }
    }
};

const expectedTabs = [
    {name:'fa-regular', label:'Font Awesome - Regular Pro',delay:34},
    {name:'fa-solid', label:'Font Awesome - Solid Pro',delay:6},
    {name:'fa-brands', label:'Font Awesome - Brands Pro',delay:45},
    {name:'fa-light', label:'Font Awesome - Light Pro',delay:21},
    {name:'fa-duotone', label:'Font Awesome - Duotone Pro',delay:12},
    {name:'hashe-fa7-fab', label:'Font Awesome 7.3.1 Kit - Brands',delay:28},
    {name:'hashe-fa7-fasdr', label:'Font Awesome 7.3.1 Kit - Sharp Duotone Regular',delay:1}
];
const config = {icons:{libraries:[
    ...expectedTabs.slice(0,5),
    ...Array.from({length:12}, (_,i) => ({
        name:'hashe-fa7-bogus-' + i,label:'Font Awesome 7.3.1 Kit - Brands',delay:i+1
    }))
]}};
const manager = {library,store,
    // Mirrors Elementor 4.3.5 onPickerShow() closure: asynchronous callbacks
    // replace config.icons.libraries[index] after render.
    onPickerShow() {
        const icons = config.icons.libraries;
        icons.forEach((tab,index) => {
            library.initIconType({...tab}, lib => { icons[index] = lib; });
        });
    }
};
const w = {elementor:{iconManager:manager,config},hasheFa7ExpectedTabs:expectedTabs,
    localStorage,setInterval,clearInterval};
vm.runInNewContext(source, {window:w});
assert.equal(library.__hasheFa7CallbackGuard, true);

async function runTest() {
    assert.equal(config.icons.libraries.length,7,
        'Guard must restore exact seven canonical entries before any popup appears');
    assert.equal(new Set(config.icons.libraries.map(x=>x.name)).size,7);
    assert.deepEqual(config.icons.libraries.map(x=>x.name),expectedTabs.map(x=>x.name));
    assert.equal(config.icons.libraries[6].label,
        'Font Awesome 7.3.1 Kit - Sharp Duotone Regular');
    console.log('PASS: Repeated Brands labels are replaced by 5 native plus 2 correct Kit identities.');

    // Corruption can recur when modal has already been opened. Every
    // subsequent actual onPickerShow must restore the server tab identity.
    config.icons.libraries.splice(0,config.icons.libraries.length,
        ...Array.from({length:12},()=>({name:'hashe-fa7-fab',label:'Font Awesome 7.3.1 Kit - Brands'})));
    manager.onPickerShow();
    assert.equal(config.icons.libraries.length,7);
    await new Promise(resolve=>setTimeout(resolve,90));
    for(let i=0;i<7;i++){
        assert.equal(config.icons.libraries[i].name,expectedTabs[i].name,
            'Async response must not replace an unrelated tab with Brands');
        assert.equal(config.icons.libraries[i].label,expectedTabs[i].label);
    }
    console.log('PASS: A real picker-opening cycle restores exactly 7 distinct tabs despite out-of-order loads.');

    const cleaned = JSON.parse(raw.get('elementor'));
    assert.ok(!Object.keys(cleaned).some(name => name.startsWith('elementor_hashe-fa7-')));
    assert.equal(cleaned['elementor_fa-solid_icons'].data, unrelated.data);
    assert.equal(Object.keys(cleaned.__expiration).length, 0);
    console.log('PASS: Only stale Hashe icon caches are removed; Elementor settings remain intact.');

    const prefixes = ['fas','far','fal','fat','fad','fadr','fadl','fadt','fass',
        'fasr','fasl','fast','fasds','fasdr','fasdl','fasdt'];
    const received = [];
    const tasks = prefixes.map((name, i) => new Promise((resolve, reject) => {
        const fullName = 'hashe-fa7-' + name;
        try {
            library.initIconType({name: fullName, delay:(i * 17) % 61}, item => {
                received.push([fullName, item.name]);
                resolve();
            });
        } catch (e) { reject(e); }
    }));
    await Promise.race([Promise.all(tasks), new Promise((_,reject) =>
        setTimeout(()=>reject(new Error('Icon callbacks timed out')), 2000))]);
    for (const [expected,actual] of received) assert.equal(actual,expected);
    assert.equal(received.length, 16);
    assert.ok(raw.get('elementor').length < quotaBytes);
    console.log('PASS: All 16 concurrent Pro catalogs load without browser storage quota errors.');

    const result = store.getIcons({name:'hashe-fa7-fat'});
    assert.equal(Object.keys(result).length,3781);
    console.log('PASS: Loaded catalog remains accessible in memory to Elementor.');

    let reopened = false;
    library.initIconType({name:'hashe-fa7-fat'}, item => {
        reopened = !!item.icons && Object.keys(item.icons).length === 3781;
    });
    assert.equal(reopened, true);
    console.log('PASS: Reopening an already-loaded category reads its in-memory catalog.');

    // This reproduced the real native-library regression: Elementor 4.x uses
    // one mutable notifyCallback for every concurrent fetch. When the last
    // initIconType call belongs to a Hashe library, its callback must not
    // consume native responses arriving afterward.
    const interleaved = [
        {name: 'fa-solid', delay: 48},
        {name: 'fa-regular', delay: 9},
        {name: 'hashe-fa7-faps', delay: 30},
        {name: 'fa-brands', delay: 60},
        {name: 'hashe-fa7-fasds', delay: 4},
        {name: 'fa-light', delay: 21},
        {name: 'fa-duotone', delay: 36}
    ];
    const callbackCalls = Object.create(null);
    const callbacksDone = interleaved.map(settings => new Promise((resolve, reject) => {
        library.initIconType(settings, payload => {
            try {
                assert.equal(payload.name, settings.name,
                    'Must deliver response to the correct native or Hashe callback');
                assert.ok(Object.keys(payload.icons).length > 0);
                callbackCalls[settings.name] = (callbackCalls[settings.name] || 0) + 1;
                resolve();
            } catch (error) { reject(error); }
        });
    }));
    await Promise.race([Promise.all(callbacksDone), new Promise((_,reject) =>
        setTimeout(()=>reject(new Error('Native/Hashe interleaved callbacks timed out')), 2000))]);
    assert.equal(Object.keys(callbackCalls).length, 7);
    assert.ok(interleaved.every(x => callbackCalls[x.name] === 1));
    assert.ok(store.getIcons('fa-light'), 'Native library stored through the original Elementor storage');
    assert.ok(store.getIcons('fa-duotone'), 'Every native library keeps its correct asynchronous callback');
    assert.ok(!raw.get('elementor').includes('elementor_hashe-fa7-faps_icons'),
        'Large Hashe catalog never persisted into native localStorage');
    console.log('PASS: Five original Elementor Pro libraries and new Hashe tabs coexist with out-of-order asynchronous callbacks.');

    const native = {name:'fa-solid', icons:{star:{selector:'fa-star'}}};
    store.save(native);
    assert.ok(store.getIcons(native).star);
    assert.ok(JSON.parse(raw.get('elementor'))['elementor_fa-solid_icons']);
    console.log('PASS: Elementor built-in icon storage is not modified.');

    const php = fs.readFileSync(path.join(__dirname,'../hashe-fa7-elementor/hashe-fa7-elementor.php'),'utf8');
    assert.ok(!php.includes('hashe_fa7_editor_icon_names'));
    assert.ok(!php.includes("'icons'         =>"));
    console.log('PASS: No large inline editor icon catalogs.');
}
runTest().catch(error => {console.error(error);process.exitCode=1});
