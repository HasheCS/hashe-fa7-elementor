<?php
/* Isolated behavior test: never boots or modifies the Amari WordPress site. */
define( 'ABSPATH', __DIR__ . '/' );
define( 'HOUR_IN_SECONDS', 3600 );
$transients = array();
$kit_id = '';
$fixture_js = '';
$fixture_css = '';
function add_filter() {}
function add_action() {}
function plugin_dir_path( $file ) { return dirname( $file ) . '/'; }
function plugin_dir_url( $file ) { return 'https://example.test/wp-content/plugins/hashe-fa7-elementor/'; }
function get_option( $name, $fallback = '' ) { global $kit_id; return $kit_id; }
function get_transient( $key ) { global $transients; return $transients[$key] ?? false; }
function set_transient( $key, $value, $ttl ) { global $transients; $transients[$key] = $value; }
function is_wp_error( $v ) { return false; }
function wp_remote_get( $url, $options ) {
    global $fixture_js, $fixture_css;
    if ( strpos( $url, 'kit.fontawesome.com/' ) !== false ) {
        return array( 'response' => array( 'code' => 200 ), 'body' => $fixture_js );
    }
    if ( strpos( $url, 'ka-p.fontawesome.com/assets/' ) !== false ) {
        return array( 'response' => array( 'code' => 200 ), 'body' => $fixture_css );
    }
    throw new Exception( 'Unexpected host: ' . $url );
}
function wp_remote_retrieve_response_code( $res ) { return $res['response']['code']; }
function wp_remote_retrieve_body( $res ) { return $res['body']; }
require __DIR__ . '/../hashe-fa7-elementor/hashe-fa7-elementor.php';
function check_names( $expected, $title ) {
    $tabs = hashe_fa7_register_icon_tabs( array() );
    $names = array_keys( $tabs );
    if ( $names !== $expected ) {
        fwrite( STDERR, 'FAIL ' . $title . ' got ' . json_encode( $names ) . PHP_EOL );
        exit( 1 );
    }
    echo 'PASS ' . $title . ' ' . implode( ',', $names ) . PHP_EOL;
}
$kit_id = '';
check_names( array( 'hashe-fa7-free-solid', 'hashe-fa7-free-regular', 'hashe-fa7-free-brands' ), 'No Kit = unchanged Free fallback' );
$kit_id = '23f6a60c28';
$fixture_js = isset( $argv[1] ) ? file_get_contents( $argv[1] ) :
    'window.FontAwesomeKitConfig = {"token":"23f6a60c28","method":"css","subsetPath":"assets/23f6a60c28/112386267/kit.css"};';
$fixture_css = isset( $argv[2] ) ? file_get_contents( $argv[2] ) :
    '@font-face{src:url(https://ka-p.fontawesome.com/releases/v7.3.1/webfonts/pro-fa-brands-400-0.woff2)}' .
    '@font-face{src:url(https://ka-p.fontawesome.com/releases/v7.3.1/webfonts/pro-fa-sharp-duotone-regular-400-0.woff2)}';
check_names( array( 'hashe-fa7-fab', 'hashe-fa7-fasdr' ), 'Actual 2-style Kit = exactly 2 libraries' );
$selected = hashe_fa7_kit_styles( $kit_id );
if ( count( $selected ) !== 2 || ! in_array( 'fab', $selected, true ) || ! in_array( 'fasdr', $selected, true ) ) {
    throw new Exception( 'Wrong exact styles: ' . json_encode( $selected ) );
}
$kit_id = '7ba505d0c6';
$fixture_js = isset( $argv[3] ) ? file_get_contents( $argv[3] ) :
    'window.FontAwesomeKitConfig = {"token":"7ba505d0c6","method":"js","startupFilename":"pro.min.js"};';
$transients = array();
$names = array_keys( hashe_fa7_register_icon_tabs( array() ) );
if ( count( $names ) !== 17 || ! in_array( 'hashe-fa7-fas', $names, true ) || ! in_array( 'hashe-fa7-fasdr', $names, true ) ) {
    throw new Exception( 'Full-library JS baseline behavior changed: ' . json_encode( $names ) );
}
echo 'PASS Existing full SVG Kit = full supported library' . PHP_EOL;
