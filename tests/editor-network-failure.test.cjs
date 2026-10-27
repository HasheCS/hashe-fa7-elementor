'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

const source = fs.readFileSync(path.join(__dirname,
    '../hashe-fa7-elementor/assets/js/editor-callback-guard.js'), 'utf8');
const tabs = [
    {name:'fa-solid', label:'Native Solid', fetchJson:'/native'},
    {name:'fa-brands', label:'Native Brands', fetchJson:'/native'},
    {name:'hashe-fa7-fab', label:'Kit Brands', fetchJson:'/503'},
    {name:'hashe-fa7-fasdr', label:'Kit Sharp Duotone Regular', fetchJson:'/broken-json'},
    {name:'hashe-fa7-fas', label:'Kit Solid', fetchJson:'/network-error'},
    {name:'hashe-fa7-far', label:'Kit Regular', fetchJson:'/valid'}
];
const loaded = new Map(), calls = [];
const body = JSON.stringify({'elementor_fa-solid_icons':{name:'fa-solid',icons:['native-before']}});
const storage = {elementor:body};
const store = {
    getKey(settings) {return 'elementor_'+settings.name+'_icons';},
    get(name) { return JSON.parse(storage.elementor)[name]; },
    getIcons(settings) { return loaded.get(settings.name) || false; },
    save(settings) {
        loaded.set(settings.name,settings.icons);
        const existing=JSON.parse(storage.elementor);
        existing[this.getKey(settings)]=settings;
        storage.elementor=JSON.stringify(existing);
    }
};
const responses = {
    '/native': {ok:true,json:async()=>({icons:['native-test']})},
    '/503': {ok:false,status:503,json:async()=>({success:false,data:'Not available'})},
    '/broken-json': {ok:true,json:async()=>{throw Error('Unparseable HTML or invalid JSON')}},
    '/valid': {ok:true,json:async()=>({icons:['github','apple']})}
};
const mockedFetch = path => path==='/network-error' ?
    Promise.reject(new Error('Offline')) : Promise.resolve(responses[path]);

const library = {
    loaded:{}, notifyCallback:null,
    initIconType(settings,callback) {
        this.notifyCallback=callback;
        return this.fetchIcons(settings);
    },
    fetchIcons(settings) {
        return mockedFetch(settings.fetchJson).then(r=>r.json()).then(json=>{
            settings.icons=json.icons;
            return this.normalizeIconList(settings);
        });
    },
    normalizeIconList(settings) {
        const arr=settings.icons;
        if(!Array.isArray(arr))throw Error('Malformed source data');
        settings.icons=Object.fromEntries(arr.map(s=>[s,{name:s}]));
        this.loaded[settings.name]=true;
        store.save(settings);
        return this.runCallback(settings);
    },
    runCallback(settings) {
        return this.notifyCallback?.(settings);
    }
};
const manager={
    library,store,
    onPickerShow(){
        const libraries=windowValue.elementor.config.icons.libraries;
        return Promise.all(libraries.map((tab,index)=>library.initIconType({...tab},obj=>{
            calls.push(obj.name);
            libraries[index]=obj;
        })));
    }
};
const windowValue={
    localStorage:{
        getItem(k){return storage[k]||null},
        setItem(k,v){storage[k]=v}
    },
    hasheFa7ExpectedTabs:tabs,
    fetch:mockedFetch,
    elementor:{config:{icons:{libraries:tabs.map(x=>({...x}))}},iconManager:manager},
    setInterval,clearInterval
};
vm.runInNewContext(source,{window:windowValue});
assert.equal(library.__hasheFa7CallbackGuard,true);

async function test() {
    await manager.onPickerShow();
    assert.equal(calls.length,tabs.length,'Every tab receives its own callback on success or failure');
    for(let i=0;i<tabs.length;i++){
        const a=windowValue.elementor.config.icons.libraries[i];
        assert.equal(a.name,tabs[i].name,'No name overwritten by another library');
        assert.equal(a.label,tabs[i].label,'Native and Kit category labels unchanged');
        assert.ok(a.icons && !Array.isArray(a.icons),'All icon payloads safely normalized');
        if(a.name.startsWith('hashe-fa7-')&&a.fetchJson!=='/valid'){
            assert.equal(Object.keys(a.icons).length,0,'No bogus paid icons after metadata/nonce/network failure');
        }
    }
    const native=windowValue.elementor.config.icons.libraries[0];
    assert.equal(Object.keys(native.icons).length,1,'Native fetch uses original Elementor behavior');
    const brands=windowValue.elementor.config.icons.libraries[5];
    assert.equal(Object.keys(brands.icons).length,2,'Healthy Kit returns real available icon names');
    assert.ok(!storage.elementor.includes('elementor_hashe-fa7-'),
        'Failed/healthy Kit catalogs never persist into Elementor localStorage');
    console.log('PASS: Simultaneous native + Kit loads survive HTTP 503, invalid JSON, offline fetch, and successful catalog.');
    console.log('PASS: Native libraries preserve labels, valid Hashe icons render, empty failures never corrupt picker.');
}
test().catch(e=>{console.error(e);process.exitCode=1});
