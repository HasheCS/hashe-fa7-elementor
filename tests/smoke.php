<?php
define( 'ABSPATH', __DIR__ );
define( 'DAY_IN_SECONDS', 86400 );
$GLOBALS['test_kit_id'] = '';
$GLOBALS['registered_filter'] = null;
$GLOBALS['hashe_transients'] = array();
function is_wp_error( $response ) { return false; }
function get_transient( $key ) { return $GLOBALS['hashe_transients'][$key] ?? false; }
function set_transient( $key, $value, $ttl ) { $GLOBALS['hashe_transients'][$key] = $value; return true; }
function admin_url( $path ) { return 'https://example.test/wp-admin/' . $path; }
function wp_remote_retrieve_response_code( $response ) { return $response['code']; }
function wp_remote_retrieve_body( $response ) { return $response['body']; }
$GLOBALS['mock_catalog_fail_page'] = 0;
$GLOBALS['mock_catalog_requests'] = 0;
$GLOBALS['mock_kit_method'] = 'css';
$GLOBALS['mock_kit_css'] = '@font-face{font-family:"Font Awesome 7 Brands";font-weight:400}@font-face{font-family:"Font Awesome 7 Sharp Duotone";font-weight:400}.fa-github{--fa:"X"}.fa-leaf{--fa:"Y"}.fa-rocket{--fa:"Z"}.fa-home,.fa-house{--fa:"H"}';
function wp_remote_get( $url, $options ) {
    if ( 'https://kit.fontawesome.com/23f6a60c28.js' === $url ) {
        return array( 'code' => 200, 'body' => 'window.FontAwesomeKitConfig = {"token":"23f6a60c28","version":"7.3.1","method":"' . $GLOBALS['mock_kit_method'] . '","baseUrl":"https://ka-p.fontawesome.com","subsetPath":"assets/23f6a60c28/112386267/kit.css"};' );
    }
    if ( 'https://ka-p.fontawesome.com/assets/23f6a60c28/112386267/kit.css?token=23f6a60c28' === $url ) {
        return array( 'code' => 200, 'body' => $GLOBALS['mock_kit_css'] );
    }
    if ( 'https://api.fontawesome.com/releases/7.3.1/family-styles' === $url ) {
        return array( 'code' => 200, 'body' => json_encode( array( 'familyStyles' => array(
            array( 'family' => 'classic', 'style' => 'brands', 'shorthand' => 'brands', 'prefix' => 'fab' ),
            array( 'family' => 'sharp-duotone', 'style' => 'regular', 'shorthand' => 'sharp-duotone-regular', 'prefix' => 'fasdr' ),
            array( 'family' => 'classic', 'style' => 'light', 'shorthand' => 'light', 'prefix' => 'fal' ),
            array( 'family' => 'classic', 'style' => 'solid', 'shorthand' => 'solid', 'prefix' => 'fas' )
        ) ) ) );
    }
    if ( preg_match( '#^https://api\\.fontawesome\\.com/releases/7\\.3\\.1/icons-minimal\\?page=([0-9]+)&page_size=500$#D', $url, $matches ) ) {
        $page = (int) $matches[1];
        $GLOBALS['mock_catalog_requests']++;
        if ( $GLOBALS['mock_catalog_fail_page'] === $page ) {
            return array( 'code' => 503, 'body' => '' );
        }
        $brands = array( 'prefix' => 'fab' );
        $sharp_regular = array( 'prefix' => 'fasdr' );
        $classic_solid = array( 'prefix' => 'fas' );
        $empty = array();
        $icons = 1 === $page ? array(
            array( 'id' => 'github', 'familyStylesByLicense' => array( 'free' => array( $brands ), 'pro' => $empty ) ),
            array( 'id' => 'leaf', 'familyStylesByLicense' => array( 'free' => $empty, 'pro' => array( $sharp_regular ) ) ),
        ) : array(
            array( 'id' => 'rocket', 'familyStylesByLicense' => array( 'free' => $empty, 'pro' => array( $sharp_regular ) ) ),
            array( 'id' => 'star', 'familyStylesByLicense' => array( 'free' => $empty, 'pro' => array( $classic_solid ) ) ),
        );
        return 1 === $page || 2 === $page
            ? array( 'code' => 200, 'body' => json_encode( array( 'totalPageCount' => 2, 'icons' => $icons ) ) )
            : array( 'code' => 404, 'body' => '' );
    }
    return array( 'code' => 404, 'body' => '' );
}
function add_filter( $name, $callback, $priority = 10 ) { $GLOBALS['registered_filter'] = array( $name, $callback, $priority ); }
function add_action( $name, $callback, $priority = 10 ) { $GLOBALS['registered_action'] = array( $name, $callback, $priority ); }
function plugins_url( $path, $file ) { return plugin_dir_url( $file ) . $path; }
function wp_enqueue_style( $handle, $src, $deps, $ver ) { $GLOBALS['style_enqueued'] = compact( 'handle', 'src', 'deps', 'ver' ); }
function wp_add_inline_style( $handle, $css ) { $GLOBALS['style_inline'] = compact( 'handle', 'css' ); }
function wp_enqueue_script( $handle, $src, $deps, $ver, $footer ) { $GLOBALS['editor_script'] = compact( 'handle', 'src', 'deps', 'ver', 'footer' ); }
function plugin_dir_path( $file ) { return dirname( $file ) . DIRECTORY_SEPARATOR; }
function plugin_dir_url( $file ) { return 'https://example.test/wp-content/plugins/' . basename( dirname( $file ) ) . '/'; }
function get_option( $key, $default = '' ) { return 'elementor_font_awesome_pro_kit_id' === $key ? $GLOBALS['test_kit_id'] : $default; }
require_once __DIR__ . '/../hashe-fa7-elementor/hashe-fa7-elementor.php';
function check( $passed, $message ) { if ( ! $passed ) { fwrite( STDERR, 'FAIL: ' . $message . PHP_EOL ); exit( 1 ); } }
check( 'elementor/icons_manager/additional_tabs' === $GLOBALS['registered_filter'][0], 'Correct Elementor filter' );
$existing = array( 'existing-library' => array( 'name' => 'existing-library' ) );
$free = hashe_fa7_register_icon_tabs( $existing );
check( 4 === count( $free ), 'No Kit: three Free families and original family' );
check( ! isset( $free['hashe-fa7-fas'] ), 'No Pro without Kit' );
check( ! empty( $free['hashe-fa7-free-solid']['url'] ), 'Bundled Free stylesheet' );
check( isset( $free['existing-library'] ), 'Original library preserved' );
check( 'eicon-font-awesome' === $free['hashe-fa7-free-solid']['labelIcon'], 'Free category symbol comes from Elementor, not the Kit' );
$dir = dirname( __DIR__ ) . '/hashe-fa7-elementor/';
foreach ( array( 'solid' => 2001, 'regular' => 273, 'brands' => 609 ) as $family => $expected ) {
    $catalog = json_decode( file_get_contents( $dir . 'data/free-' . $family . '.json' ), true );
    check( $expected === count( $catalog['icons'] ), 'Free catalog count: ' . $family );
    check( is_file( $dir . 'assets/css/' . $family . '.min.css' ), 'Free CSS: ' . $family );
}
foreach ( array( 'fa-solid-900', 'fa-regular-400', 'fa-brands-400' ) as $font ) {
    check( is_file( $dir . 'assets/webfonts/' . $font . '.woff2' ), 'Bundled Free font: ' . $font );
}
$GLOBALS['test_kit_id'] = '23f6a60c28';
$pro = hashe_fa7_register_icon_tabs( $existing );
check( 3 === count( $pro ), 'With two-style Kit: two selected Kit tabs and original' );
check( isset( $pro['hashe-fa7-fasdr'] ), 'Sharp Duotone Regular selected by Kit' );
check( isset( $pro['hashe-fa7-fab'] ), 'Brands selected by Kit' );
check( ! isset( $pro['hashe-fa7-fas'] ), 'Classic Solid omitted when not selected' );
check( ! function_exists( 'hashe_fa7_native_compat_rules' ), 'Do not globally override Elementor native font classes' );
check( ! function_exists( 'hashe_fa7_enqueue_native_compat' ), 'No site-wide Classic Pro fallback overrides' );
echo "PASS: Kit catalogs, native libraries, safe editor integration without global styling.\n";
