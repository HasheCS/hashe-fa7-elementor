<?php
/**
 * Live, read-only integration check for the user-provided public two-style Kit.
 * Runs only in CI; never changes WordPress or any Kit.
 */
define( 'ABSPATH', __DIR__ );
define( 'DAY_IN_SECONDS', 86400 );
$GLOBALS['test_kit_id'] = '7ba505d0c6';
$GLOBALS['hashe_transients'] = array();
function add_filter( $name, $callback, $priority = 10 ) {}
function add_action( $name, $callback, $priority = 10 ) {}
function plugin_dir_path( $file ) { return dirname( $file ) . '/'; }
function plugin_dir_url( $file ) { return 'https://example.test/plugins/' . basename( dirname( $file ) ) . '/'; }
function plugins_url( $path, $file ) { return plugin_dir_url( $file ) . $path; }
function wp_enqueue_script( $handle, $src, $deps, $ver, $footer ) {}
$GLOBALS['hashe_mock_options'] = array();
function get_option( $key, $default = '' ) {
    return 'elementor_font_awesome_pro_kit_id' === $key
        ? $GLOBALS['test_kit_id']
        : ( $GLOBALS['hashe_mock_options'][$key] ?? $default );
}
function add_option( $key, $value, $deprecated = '', $autoload = 'yes' ) {
    if ( array_key_exists( $key, $GLOBALS['hashe_mock_options'] ) ) { return false; }
    $GLOBALS['hashe_mock_options'][$key] = $value;
    return true;
}
function delete_option( $key ) { unset( $GLOBALS['hashe_mock_options'][$key] ); return true; }
function get_transient( $key ) { return $GLOBALS['hashe_transients'][$key] ?? false; }
function set_transient( $key, $value, $ttl ) { $GLOBALS['hashe_transients'][$key] = $value; return true; }
function wp_create_nonce( $action ) { return 'unit-only-nonce'; }
function check_ajax_referer( $action, $query_arg = false ) { return true; }
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
$kit = hashe_fa7_kit_snapshot( '7ba505d0c6' );
assert_kit( is_array( $kit ) && empty( $kit['error'] ), 'Real public original SVG Pro Kit resolved' );
assert_kit( 'js' === $kit['method'], 'Kit uses SVG technology despite JS embedding' );
assert_kit( 'full' === $kit['subset_mode'], 'SVG Kit is full bundle without subsetPath' );
assert_kit( 'pro' === $kit['license'], 'Kit reports ordinary Pro license' );
assert_kit( '7.3.1' === $kit['version'], 'Test fixture version is currently 7.3.1; update fixture as Kit changes' );
foreach ( array( 'fab', 'fas', 'far', 'fal', 'fad', 'fass', 'fasdr' ) as $prefix ) {
    assert_kit( isset( $kit['family_styles'][$prefix] ), 'SVG full Kit includes standard style: ' . $prefix );
}
assert_kit( ! isset( $kit['family_styles']['fans'] ), 'Ordinary Pro SVG bundle does not claim Pro Plus Notdog' );
$existing = array( 'existing' => array( 'name' => 'existing' ) );
$tabs = hashe_fa7_register_icon_tabs( $existing );
assert_kit( 1 + count( $kit['family_styles'] ) === count( $tabs ), 'All verified SVG standard categories registered without modifying native entries' );
echo "PASS: live public real SVG/JS full-kit bootstrap and standard family-style metadata.\\n";
