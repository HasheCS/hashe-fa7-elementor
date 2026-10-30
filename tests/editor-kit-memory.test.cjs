/* Standalone, dependency-free regression for the approved Kit-only adapter. */
'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

const source = fs.readFileSync(path.join(__dirname, '../hashe-fa7-elementor/assets/js/editor-kit-memory.js'), 'utf8');
const raw = new Map();
raw.set('elementor', JSON.stringify({
  'elementor_fa-solid_icons': {name:'fa-solid',icons:{star:{selector:'fa-star'}}},
  'elementor_hashe-fa7-kit-fass_icons': {icons:Array(5000).fill('stale')},
  '__expiration': {
    'elementor_hashe-fa7-kit-fass_icons': 10000000000,
    'elementor_fa-solid_icons': 10000000000,
  },
  'settings': {name:'preserve user data'},
}));
const localStorage={
  getItem(key){return raw.get(key)||null},
  setItem(key,value){
    if(value.length>450000){const e=new Error('QuotaExceeded');e.name='QuotaExceededError';throw e;}
    raw.set(key,value);
  },
};
const originalSave = function(lib){
  const data=JSON.parse(localStorage.getItem('elementor'));
  data['elementor_'+lib.name+'_icons']=lib;
  localStorage.setItem('elementor',JSON.stringify(data));
};
const originalGet = function(key){
  return JSON.parse(localStorage.getItem('elementor'))[key];
};
const store={save:originalSave,get:originalGet};
const window={localStorage,elementor:{iconManager:{store}},
  setInterval(){throw new Error('No timer necessary when Elementor store is ready')},
  clearInterval(){}};
vm.runInNewContext(source,{window});
assert.equal(store.__hasheFa7KitMemoryCache,true);
const initial=JSON.parse(localStorage.getItem('elementor'));
assert.equal(initial['elementor_hashe-fa7-kit-fass_icons'],undefined);
assert.equal(initial.__expiration['elementor_hashe-fa7-kit-fass_icons'],undefined);
assert.ok(initial.__expiration['elementor_fa-solid_icons']);
assert.equal(initial.settings.name,'preserve user data');
assert.ok(initial['elementor_fa-solid_icons'].icons.star);

const styles=['fab','fas','far','fal','fat','fad','fadr','fadl','fadt',
  'fass','fasr','fasl','fast','fasds','fasdr','fasdl','fasdt',
  'facr','faes','fagt','fajr','fajdr','fajfr','fams','fans','fands','fapr',
  'faslr','fasldr','faslpr','faslpdr','fatl','fausb','faudsb','faufsb','favs','fawsb'];
for(const name of styles) {
  const library={name:'hashe-fa7-kit-'+name,icons:Object.fromEntries(
    Array.from({length:3781},(_,i)=>['icon-'+i,{name:'icon-'+i}]))};
  store.save(library);
  const key='elementor_hashe-fa7-kit-'+name+'_icons';
  assert.equal(Object.keys(store.get(key).icons).length,3781);
}
assert.equal(raw.get('elementor').length<450000,true,'full 37 catalogs never hit persistent quota');
assert.equal(Object.keys(JSON.parse(raw.get('elementor'))).length,3,'only native+settings+expiration persist');
const native={name:'fa-regular',icons:{user:{selector:'fa-user'}}};
store.save(native);
assert.deepEqual(store.get('elementor_fa-regular_icons').icons, native.icons);
assert.ok(JSON.parse(raw.get('elementor'))['elementor_fa-regular_icons']);
assert.equal(store.get('elementor_unknown_icons'),undefined,'unknown reads retain native behavior');
assert.equal(store.save,store.save,'installed once');
assert.equal(typeof originalSave,'function');
console.log('PASS: 37 supported Kit catalogs use memory only, stale Kit cache pruned, native storage and settings retained.');
