<?php
/** Release candidate regression: a separate process for every scenario. */
define( 'ABSPATH', __DIR__ );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'DAY_IN_SECONDS', 86400 );
define( 'MINUTE_IN_SECONDS', 60 );
$GLOBALS['scenario'] = $argv[1] ?? 'no-kit';
$GLOBALS['options'] = array();
$GLOBALS['transients'] = array();
$GLOBALS['filters'] = array();
$GLOBALS['actions'] = array();
$GLOBALS['enqueued_styles'] = array();
$GLOBALS['enqueued_scripts'] = array();
$GLOBALS['requests'] = array();
$GLOBALS['source_version'] = '7.3.1';
$scenario = $GLOBALS['scenario'];
$allowed = array(
    'no-kit', 'invalid', 'css-small', 'css-brands', 'css-one', 'full-js',
    'css-new-version', 'css-api-offline', 'css-stale-cache'
);
if ( ! in_array( $scenario, $allowed, true ) ) { die( "bad scenario\n" ); }
$GLOBALS['kit_id'] = 'no-kit' === $scenario ? '' : ( 'invalid' === $scenario ? 'bad-id' : '0123456789' );
if ( 'css-new-version' === $scenario ) { $GLOBALS['source_version'] = '7.4.0'; }
function check( $truth, $label ) {
    if ( ! $truth ) { fwrite( STDERR, "FAIL: $label\n" ); exit( 1 ); }
}
function add_filter( $hook, $callback, $priority = 10 ) { $GLOBALS['filters'][$hook][] = $callback; }
function add_action( $hook, $callback, $priority = 10 ) { $GLOBALS['actions'][$hook][] = $callback; }
function get_option( $name, $default = false ) {
    if ( 'elementor_font_awesome_pro_kit_id' === $name ) { return $GLOBALS['kit_id']; }
    return $GLOBALS['options'][$name] ?? $default;
}
function add_option( $name, $value, $deprecated = '', $autoload = 'no' ) {
    if ( array_key_exists( $name, $GLOBALS['options'] ) ) { return false; }
    $GLOBALS['options'][$name] = $value; return true;
}
function update_option( $name, $value, $autoload = null ) { $GLOBALS['options'][$name] = $value; return true; }
function delete_option( $name ) { unset( $GLOBALS['options'][$name] ); }
function get_transient( $name ) { return $GLOBALS['transients'][$name] ?? false; }
function set_transient( $name, $value, $time = 0 ) { $GLOBALS['transients'][$name] = $value; return true; }
function did_action( $name ) { return 'elementor/editor/before_enqueue_scripts' === $name ? 1 : 0; }
function doing_action( $name ) { return false; }
function home_url( $path = '' ) { return 'https://example.test/' . $path; }
function wp_json_encode( $obj ) { return json_encode( $obj ); }
function is_wp_error( $value ) { return false; }
function wp_remote_retrieve_response_code( $response ) { return $response['code']; }
function wp_remote_retrieve_body( $response ) { return $response['body']; }
function plugins_url( $path, $file ) { return 'https://example.test/plugins/hashe-fa7-elementor/' . $path; }
function esc_url_raw( $url ) { return $url; }
function wp_enqueue_script( $handle, $src, $deps = array(), $ver = '', $foot = false ) {
    $GLOBALS['enqueued_scripts'][$handle] = $src;
}
function wp_register_style( $handle, $src, $deps = array(), $ver = '' ) {}
function wp_add_inline_style( $handle, $css ) {}
function wp_enqueue_style( $handle, $src = null, $deps = array(), $ver = '' ) {
    $GLOBALS['enqueued_styles'][$handle] = $src;
}
function wp_remote_get( $url, $args = array() ) {
    $GLOBALS['requests'][] = $url;
    $kit = '0123456789';
    if ( strpos( $url, 'https://kit.fontawesome.com/' . $kit . '.js' ) === 0 ) {
        $method = 'full-js' === $GLOBALS['scenario'] ? 'js' : 'css';
        $config = array(
            'token' => $kit, 'version' => $GLOBALS['source_version'],
            'method' => $method, 'license' => 'pro',
            'subsetPath' => 'js' === $method ? '' : 'assets/' . $kit . '/124/kit.css',
            'startupFilename' => 'js' === $method ? 'pro.min.js' : '',
        );
        return array( 'code'=>200, 'body'=>'window.FontAwesomeKitConfig=' . json_encode($config) . ';' );
    }
    if ( strpos( $url, 'ka-p.fontawesome.com/assets/' . $kit ) !== false ) {
        $css = 'url("pro-fa-sharp-solid-900-0.woff2")';
        if ( 'css-one' !== $GLOBALS['scenario'] ) {
            $css .= ' url("pro-fa-sharp-regular-400-0.woff2") url("pro-fa-sharp-light-300-0.woff2")';
        }
        if ( 'css-brands' === $GLOBALS['scenario'] ) {
            $css .= ' url("free-fa-brands-400-0.woff2")';
        }
        return array( 'code'=>200, 'body'=>$css );
    }
    if ( strpos( $url, 'ka-p.fontawesome.com/releases/v' ) !== false ) {
        return array( 'code'=>200, 'body'=>str_repeat( 'X', 51000 ) );
    }
    if ( strpos( $url, 'https://api.fontawesome.com/releases/' ) === 0 ) {
        if ( 'css-api-offline' === $GLOBALS['scenario'] || 'css-stale-cache' === $GLOBALS['scenario'] ) {
            return array( 'code'=>503, 'body'=>'' );
        }
        // Four sample icon IDs validate both license maps and style filtering.
        $all = array();
        foreach ( array(
            array('house',array('fas'),array('fas','far','fal','fad','fass','fasr','fasl')),
            array('flag',array('fab'),array('fab','far','fas')),
            array('address-book',array('far'),array('far','fas','fal')),
            array('arrow',array(),array_keys(hashe_fa7_additive_specs())),
            array('0',array('fas'),array_keys(hashe_fa7_additive_specs())),
            array('1',array('far'),array_keys(hashe_fa7_additive_specs())),
            array('9',array('fab'),array_keys(hashe_fa7_additive_specs())),
            array('42',array('fab'),array_keys(hashe_fa7_additive_specs())),
        ) as $record ) {
            $variant = array();
            foreach ( array('free'=>$record[1],'pro'=>$record[2]) as $license=>$list ) {
                $variant[$license] = array_map( function($p) { return array('prefix'=>$p); }, $list );
            }
            $all[] = array( 'id'=>$record[0], 'familyStylesByLicense'=>$variant );
        }
        return array( 'code'=>200, 'body'=>json_encode( array(
            'icons'=>$all, 'page'=>1, 'pageSize'=>500, 'totalIconCount'=>8, 'totalPageCount'=>1,
        ) ) );
    }
    return array( 'code'=>404, 'body'=>'' );
}
require __DIR__ . '/../hashe-fa7-elementor/hashe-fa7-elementor.php';
check( isset( $GLOBALS['filters']['elementor/icons_manager/additional_tabs'] ), 'additional tabs hook' );
$native=array();
foreach(array('fa-regular','fa-solid','fa-brands','fa-light','fa-duotone') as $name) {
    $native[$name]=array('name'=>$name,'native'=>true,'labelIcon'=>'native-label','icons'=>array('keep'));
}
$before=json_encode($native);
if ('css-stale-cache' === $scenario) {
    $key='hashe_fa7_metadata_v3_' . md5($GLOBALS['source_version']) . '_last';
    $data=array('version'=>$GLOBALS['source_version'],'updated'=>time(),'catalogs'=>array(
        'free'=>array('fass'=>array('house','0'),'fasr'=>array('house','0'),'fasl'=>array('house','0')),
        'pro'=>array('fass'=>array('arrow','0'),'fasr'=>array('arrow','0'),'fasl'=>array('arrow','0'))
    ));
    update_option($key,$data,false);
}
$tabs=hashe_fa7_register_kit_additional_tabs($native);
$expected=array(
    'no-kit'=>3, 'invalid'=>0,'css-small'=>3,'css-brands'=>4,
    'css-one'=>1,'full-js'=>17,'css-new-version'=>3,
    'css-api-offline'=>0,'css-stale-cache'=>3
)[$scenario];
check( count($tabs)===count($native)+$expected,'expected tab count '.$expected.' got '.(count($tabs)-count($native)) );
check( json_encode($native)===$before, 'native definitions are byte-identical' );
foreach($native as $name=>$config){check($tabs[$name]===$config,'native entry unmodified '.$name);}
$kit=0;$free=0;
foreach($tabs as $name=>$data){
    if(strpos($name,'hashe-fa7-kit-')===0){
        $kit++;
        check(count($data['icons'])>0,'valid Kit icons present');
        foreach($data['icons'] as $iconName){check(is_string($iconName),'all Kit icon IDs must be strings: '.gettype($iconName));}
        check(in_array('0',$data['icons'],true),'numeric Font Awesome IDs preserve string type');
    }
    if(strpos($name,'hashe-fa7-free-')===0){
        $free++;
        check(count($data['icons'])>0,'Free icons present');
        if('hashe-fa7-free-fab'===$name){check($data['labelIcon']==='fab fa-font-awesome-flag','Brands navigation glyph is valid');}
    }
}
check($free === ('no-kit'===$scenario ? 3 : 0),'Free mode exclusive to no Kit');
check($kit === ('no-kit'===$scenario ? 0 : $expected),'Kit metadata correct');
hashe_fa7_enqueue_no_kit_free_assets();
check(isset($GLOBALS['enqueued_styles']['hashe-fa7-free-core']) === ('no-kit'===$scenario),'local Free CSS only in no Kit');
hashe_fa7_supply_standard_brands_asset_if_missing();
check(isset($GLOBALS['enqueued_styles']['hashe-fa7-standard-brands-asset']) ===
    (in_array($scenario,array('css-small','css-one','css-api-offline','css-stale-cache'),true)),
    'conditional original Brands recovery');
hashe_fa7_enqueue_kit_memory_cache();
check(isset($GLOBALS['enqueued_scripts']['hashe-fa7-kit-memory-cache']) ===
    (in_array($scenario,array('css-small','css-brands','full-js','css-new-version','css-api-offline','css-stale-cache'),true)),
    'Kit-only memory adapter');
$all_requests=implode(' ',$GLOBALS['requests']);
check(strpos($all_requests,'/webfonts/pro-fa-')===false,'no paid font-shard bypass requests');
check(!is_file(__DIR__.'/../hashe-fa7-elementor/data/native-pro-unicode.json'),'no Pro unicode/shard map shipped');
foreach(array_keys(hashe_fa7_additive_specs()) as $name){
    check(!is_file(__DIR__.'/../hashe-fa7-elementor/data/'.$name.'.json'),'no bulk Pro names shipped: '.$name);
}
echo "PASS $scenario: $expected extra tabs, native unchanged, licensing boundaries intact.\n";
