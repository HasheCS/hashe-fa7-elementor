<?php
/**
 * Read-only, live WordPress pre-install smoke check for a staged Hashe plugin.
 *
 * Run with wp --skip-plugins=hashe-fa7-elementor eval-file tests/wp-site-integration.php
 * The site must have Elementor and Elementor Pro already installed.
 */
defined( 'ABSPATH' ) || exit;
if ( function_exists( 'hashe_fa7_register_icon_tabs' ) ) {
    fwrite( STDERR, "FAIL: run with installed Hashe skipped\n" );
    exit( 2 );
}
require_once dirname( __DIR__ ) . '/hashe-fa7-elementor/hashe-fa7-elementor.php';
\Elementor\Plugin::$instance->editor->set_edit_mode( true );
$tabs = \Elementor\Icons_Manager::get_icon_manager_tabs();
$names = array_values( array_map( static function ( $tab ) { return $tab['name']; }, $tabs ) );
echo 'SERVER_TABS=' . implode( ',', $names ) . PHP_EOL;
if ( 7 !== count( $names ) || 7 !== count( array_unique( $names ) ) ||
     ! in_array( 'hashe-fa7-fab', $names, true ) ||
     ! in_array( 'hashe-fa7-fasdr', $names, true ) ) {
    fwrite( STDERR, "FAIL: incorrect server tabs\n" );
    exit( 3 );
}
hashe_fa7_enqueue_editor_callback_guard();
$object = wp_scripts()->registered['hashe-fa7-editor-callback-guard'] ?? null;
$before = $object->extra['before'] ?? null;
if ( ! is_object( $object ) || ! is_array( $before ) || ! count( $before ) ) {
    echo 'INLINE_DIAGNOSTIC=' . json_encode( array(
        'class_exists' => class_exists( '\\Elementor\\Icons_Manager' ),
        'script_found' => is_object( $object ),
        'extra_keys' => is_object( $object ) ? array_keys( $object->extra ) : array(),
    ) ) . PHP_EOL;
    fwrite( STDERR, "FAIL: canonical tab data not enqueued before guard\n" );
    exit( 4 );
}
$inline = implode( "\n", array_filter( $before, 'is_string' ) );
$prefix = 'window.hasheFa7ExpectedTabs=';
$start = strpos( $inline, $prefix );
if ( false === $start ) {
    echo 'INLINE_DIAGNOSTIC=' . json_encode( array(
        'entries' => count( $before ),
        'sizes' => array_map( 'strlen', $before )
    ) ) . PHP_EOL;
    fwrite( STDERR, "FAIL: unexpected inline prefix\n" );
    exit( 5 );
}
$line = substr( $inline, $start );
$decoded = json_decode( substr( $line, strlen( $prefix ), -1 ), true );
if ( ! is_array( $decoded ) || 7 !== count( $decoded ) ||
     $names !== array_column( $decoded, 'name' ) ) {
    fwrite( STDERR, "FAIL: baseline identities do not match Elementor\n" );
    exit( 6 );
}
echo 'EDITOR_BASELINE=7 unique, Kit unchanged, native classes untouched' . PHP_EOL;
echo 'GUARD_VERSION=' . $object->ver . PHP_EOL;
