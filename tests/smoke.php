<?php
define( 'ABSPATH', __DIR__ );
$GLOBALS['test_kit_id'] = '';
$GLOBALS['registered_filter'] = null;
function add_filter( $name, $callback, $priority = 10 ) { $GLOBALS['registered_filter'] = array( $name, $callback, $priority ); }
function plugin_dir_path( $file ) { return dirname( $file ) . DIRECTORY_SEPARATOR; }
function plugin_dir_url( $file ) { return 'https://example.test/wp-content/plugins/' . basename( dirname( $file ) ) . '/'; }
function get_option( $key, $default = '' ) { return 'elementor_font_awesome_pro_kit_id' === $key ? $GLOBALS['test_kit_id'] : $default; }
$GLOBALS['test_is_admin'] = true;
function is_admin() { return $GLOBALS['test_is_admin']; }
require_once __DIR__ . '/../hashe-fa7-elementor/hashe-fa7-elementor.php';
function check( $passed, $message ) { if ( ! $passed ) { fwrite( STDERR, 'FAIL: ' . $message . PHP_EOL ); exit( 1 ); } }
check( 'elementor/icons_manager/additional_tabs' === $GLOBALS['registered_filter'][0], 'Correct Elementor filter' );
$existing = array( 'existing-library' => array( 'name' => 'existing-library' ) );
$free = hashe_fa7_register_icon_tabs( $existing );
check( 4 === count( $free ), 'No Kit: three Free families and original family' );
check( ! isset( $free['hashe-fa7-fas'] ), 'No Pro without Kit' );
check( ! empty( $free['hashe-fa7-free-solid']['url'] ), 'Bundled Free stylesheet' );
check( 2001 === count( $free['hashe-fa7-free-solid']['icons'] ), 'Free Solid inline icon names eliminate race' );
check( isset( $free['existing-library'] ), 'Original library preserved' );
$dir = dirname( __DIR__ ) . '/hashe-fa7-elementor/';
foreach ( array( 'solid' => 2001, 'regular' => 273, 'brands' => 609 ) as $family => $expected ) {
    $catalog = json_decode( file_get_contents( $dir . 'data/free-' . $family . '.json' ), true );
    check( $expected === count( $catalog['icons'] ), 'Free catalog count: ' . $family );
    check( is_file( $dir . 'assets/css/' . $family . '.min.css' ), 'Free CSS: ' . $family );
}
foreach ( array( 'fa-solid-900', 'fa-regular-400', 'fa-brands-400' ) as $font ) {
    check( is_file( $dir . 'assets/webfonts/' . $font . '.woff2' ), 'Bundled Free font: ' . $font );
}
$GLOBALS['test_kit_id'] = 'demo-kit-id';
$pro = hashe_fa7_register_icon_tabs( $existing );
check( 20 === count( $pro ), 'With Kit: three Free, sixteen Pro, and original' );
check( isset( $pro['hashe-fa7-fasds'] ), 'Sharp Duotone Pro is registered' );
check( false === $pro['hashe-fa7-free-solid']['url'], 'Kit renders Free' );
check( false === $pro['hashe-fa7-fas']['url'], 'Kit renders Pro; no bundled Pro assets' );
check( 3781 === count( json_decode( file_get_contents( $dir . 'data/fas.json' ), true )['icons'] ), 'Pro names available' );
check( 3781 === count( $pro['hashe-fa7-fat']['icons'] ), 'Classic Thin inline list prevents empty category' );
check( 3781 === count( $pro['hashe-fa7-fasds']['icons'] ), 'Sharp Duotone inline list prevents empty category' );
$GLOBALS['test_is_admin'] = false;
$frontend = hashe_fa7_register_icon_tabs( $existing );
check( 0 === count( $frontend['hashe-fa7-fat']['icons'] ), 'Public frontend does not inline large catalogs' );
check( ! empty( $frontend['hashe-fa7-fat']['fetchJson'] ), 'Fetch JSON available as fallback' );
echo "PASS: Free and Pro-Kit modes, catalogs, fonts, and library preservation." . PHP_EOL;
