<?php
/**
 * Live, read-only integration check for the user-provided public two-style Kit.
 * Runs only in CI; never changes WordPress or any Kit.
 */
define( 'ABSPATH', __DIR__ );
define( 'DAY_IN_SECONDS', 86400 );
$GLOBALS['test_kit_id'] = '23f6a60c28';
$GLOBALS['hashe_transients'] = array();
function add_filter( $name, $callback, $priority = 10 ) {}
function add_action( $name, $callback, $priority = 10 ) {}
function plugin_dir_path( $file ) { return dirname( $file ) . '/'; }
function plugin_dir_url( $file ) { return 'https://example.test/plugins/' . basename( dirname( $file ) ) . '/'; }
function plugins_url( $path, $file ) { return plugin_dir_url( $file ) . $path; }
function wp_enqueue_script( $handle, $src, $deps, $ver, $footer ) {}
function get_option( $key, $default = '' ) { return 'elementor_font_awesome_pro_kit_id' === $key ? $GLOBALS['test_kit_id'] : $default; }
function get_transient( $key ) { return $GLOBALS['hashe_transients'][$key] ?? false; }
function set_transient( $key, $value, $ttl ) { $GLOBALS['hashe_transients'][$key] = $value; return true; }
function admin_url( $path ) { return 'https://example.test/wp-admin/' . $path; }
function is_wp_error( $value ) { return false; }
function wp_remote_retrieve_response_code( $value ) { return $value['status']; }
function wp_remote_retrieve_body( $value ) { return $value['body']; }
function wp_remote_get( $url, $options ) {
    $headers = array( 'User-Agent: Hashe FA7 Amari beta integration-test' );
    $ctx = stream_context_create( array( 'http' => array( 'timeout' => 18, 'ignore_errors' => true, 'header' => implode( "\r\n", $headers ) ) ) );
    $raw = @file_get_contents( $url, false, $ctx );
    $status = 0;
    if ( isset( $http_response_header[0] ) && preg_match( '/\s([0-9]{3})\s/', $http_response_header[0], $matches ) ) {
        $status = (int) $matches[1];
    }
    return array( 'status' => $status, 'body' => false === $raw ? '' : $raw );
}
require_once dirname( __DIR__ ) . '/hashe-fa7-elementor/hashe-fa7-elementor.php';

function assert_kit( $condition, $message ) {
    if ( ! $condition ) {
        fwrite( STDERR, "FAIL: " . $message . "\n" );
        exit( 1 );
    }
}
$base = array( 'existing-library' => array( 'name' => 'existing-library' ) );
$kit = hashe_fa7_kit_snapshot( '23f6a60c28' );
assert_kit( is_array( $kit ) && empty( $kit['error'] ), 'Kit response and stylesheet discovery' );
assert_kit( 'css' === $kit['method'], 'Web Fonts mode identified' );
$prefixes = array_keys( $kit['family_styles'] );
sort( $prefixes );
echo 'Discovered only Kit prefixes: ' . implode( ', ', $prefixes ) . "\n";
assert_kit( $prefixes === array( 'fab', 'fasdr' ), 'Exactly Brands and Sharp Duotone Regular selected' );
$tabs = hashe_fa7_register_icon_tabs( $base );
assert_kit( 3 === count( $tabs ), 'Only two added Kit tabs, existing library unchanged' );
foreach ( $prefixes as $prefix ) {
    assert_kit( isset( $tabs['hashe-fa7-' . $prefix] ), 'Expected tab: ' . $prefix );
    $names = hashe_fa7_names_for_style( $kit['version'], $prefix );
    assert_kit( is_array( $names ) && count( $names ) > 100, 'Icon names for ' . $prefix );
}
$GLOBALS['test_kit_id'] = '';
$tabs = hashe_fa7_register_icon_tabs( $base );
assert_kit( 4 === count( $tabs ), 'No Kit: only the three bundled Free tabs' );
echo "PASS: live public two-style Kit Web Fonts discovery, per-style names, Free fallback.\n";
