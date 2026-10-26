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
            const icons = Object.fromEntries(Array.from({length: 3781}, (_,i) => [
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

const w = {elementor:{iconManager:{library,store}}, localStorage, setInterval, clearInterval};
vm.runInNewContext(source, {window:w});
assert.equal(library.__hasheFa7CallbackGuard, true);

async function runTest() {
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
