<?php
/* Standalone, network-free release smoke tests of actual plugin hooks. */
define('ABSPATH', __DIR__);
define('HOUR_IN_SECONDS', 3600);
$scenario = $argv[1] ?? 'css';
$valid = array('css', 'css-brands', 'full', 'invalid');
if (!in_array($scenario, $valid, true)) { fwrite(STDERR, "Bad mode\n"); exit(2); }
$GLOBALS['scenario'] = $scenario;
$GLOBALS['filters'] = array();
$GLOBALS['actions'] = array();
$GLOBALS['styles'] = array();
$GLOBALS['scripts'] = array();
$GLOBALS['inline'] = array();
function check($condition, $message) {
    if (!$condition) { fwrite(STDERR, "FAIL: " . $message . "\n"); exit(1); }
}
function add_filter($name, $callback, $priority = 10) { $GLOBALS['filters'][$name][] = $callback; }
function add_action($name, $callback, $priority = 10) { $GLOBALS['actions'][$name][] = $callback; }
function did_action($name) { return $name === 'elementor/editor/before_enqueue_scripts' ? 1 : 0; }
function doing_action($name) { return false; }
function get_option($key, $default = '') {
    if ($key !== 'elementor_font_awesome_pro_kit_id') {return $default;}
    return $GLOBALS['scenario'] === 'invalid' ? 'invalid-kit' : '0123456789';
}
function get_transient($key) { return false; }
function set_transient($key, $val, $expiration) { return true; }
function home_url($path = '') { return 'https://example.test/' . ltrim($path, '/'); }
function wp_json_encode($value) { return json_encode($value); }
function wp_remote_get($url, $options) {
    $method = $GLOBALS['scenario'] === 'full' ? 'js' : 'css';
    if (strpos($url, 'https://kit.fontawesome.com/0123456789.js') === 0) {
        return array('response'=>array('code'=>200), 'body'=>
            'window.FontAwesomeKitConfig=' . json_encode(array(
                'token'=>'0123456789','version'=>'7.3.1','method'=>$method,
                'license'=>'pro',
                'subsetPath'=>$method === 'css' ? 'assets/0123456789/123/kit.css' : '',
                'startupFilename'=>$method === 'js' ? 'pro.min.js' : '',
            )) . ';');
    }
    if (strpos($url, '/kit.css?token=') !== false) {
        $css='url(pro-fa-sharp-solid-900-0.woff2) url(pro-fa-sharp-regular-400-0.woff2) url(pro-fa-sharp-light-300-0.woff2)';
        if ($GLOBALS['scenario'] === 'css-brands') { $css.=' url(pro-fa-brands-400-0.woff2)'; }
        return array('response'=>array('code'=>200), 'body'=>$css);
    }
    if (strpos($url, '/js/pro.min.js?token=') !== false) {
        return array('response'=>array('code'=>200), 'body'=>str_repeat('f', 51000));
    }
    return array('response'=>array('code'=>404), 'body'=>'');
}
function is_wp_error($obj) { return false; }
function wp_remote_retrieve_response_code($res) { return $res['response']['code']; }
function wp_remote_retrieve_body($res) { return $res['body']; }
function plugins_url($path, $file) {
    return 'https://example.test/plugins/' . basename(dirname($file)) . '/' . $path;
}
function wp_register_style($handle, $src, $deps = array(), $version = false) {
    $GLOBALS['styles'][$handle]=array('src'=>$src,'enqueued'=>false);
}
function wp_enqueue_style($handle, $src = null, $deps = array(), $version = false) {
    if (!isset($GLOBALS['styles'][$handle])) {
        $GLOBALS['styles'][$handle]=array('src'=>$src,'enqueued'=>false);
    }
    $GLOBALS['styles'][$handle]['enqueued']=true;
}
function wp_add_inline_style($handle, $css) { $GLOBALS['inline'][$handle]=$css; }
function wp_enqueue_script($handle, $src, $deps = array(), $version = false, $footer = false) {
    $GLOBALS['scripts'][$handle]=$src;
}
function esc_url_raw($value) { return $value; }
require __DIR__ . '/../hashe-fa7-elementor/hashe-fa7-elementor.php';
check(isset($GLOBALS['filters']['elementor/icons_manager/additional_tabs']), 'uses official additional_tabs hook');
check(!isset($GLOBALS['filters']['elementor/icons_manager/native']), 'must never replace Elementor native libraries');
$native = array();
foreach (array('fa-regular','fa-solid','fa-brands','fa-light','fa-duotone') as $id) {
    $native[$id]=array('name'=>$id,'native'=>true,'icons'=>array('retained'));
}
$before = json_encode($native);
$tabs = hashe_fa7_register_kit_additional_tabs($native);
$expected = $scenario==='full' ? 17 : ($scenario==='css-brands' ? 4 : ($scenario==='css' ? 3 : 0));
check(count($tabs)===count($native)+$expected, 'only expected supported Kit style count');
foreach ($native as $id=>$value) {
    check(isset($tabs[$id]) && $tabs[$id]===$value, 'original native definitions unchanged: '.$id);
}
check(json_encode($native)===$before, 'input native array unchanged');
foreach ($tabs as $id=>$tab) {
    if (strpos($id, 'hashe-fa7-kit-')===0) {
        check($tab['native']===false, 'Kit tab is custom');
        check($tab['icons'] && is_array($tab['icons']), 'Kit catalog is populated');
    }
}
hashe_fa7_supply_standard_brands_asset_if_missing();
$brands = isset($GLOBALS['styles']['hashe-fa7-standard-brands-asset']) &&
    $GLOBALS['styles']['hashe-fa7-standard-brands-asset']['enqueued'];
check($brands===($scenario==='css'), 'original Free Brands dependency scoped to CSS Kit missing Brands');
hashe_fa7_enqueue_kit_memory_cache();
$memory = isset($GLOBALS['scripts']['hashe-fa7-kit-memory-cache']);
check($memory===($expected>2), 'large Kit scoped memory adapter');
$dir = dirname(__DIR__).'/hashe-fa7-elementor/';
check(is_file($dir.'assets/css/brands.min.css'), 'original Brands stylesheet included');
check(is_file($dir.'assets/webfonts/fa-brands-400.woff2'), 'original Brands font included');
check(!is_file($dir.'assets/css/native-sidebar-labels.css'), 'no custom QA10 native label override');
$php=file_get_contents($dir.'hashe-fa7-elementor.php');
$readme=file_get_contents($dir.'readme.txt');
check(strpos($php, 'Version: 1.1.0')!==false, 'released PHP version');
check(strpos($readme, 'Stable tag: 1.1.0')!==false, 'readme stable tag matches release');
echo "PASS: ".$scenario." scenario, 5 unchanged native tabs, ".$expected." additional Kit styles, scoped Brands and storage.\n";
