<?php
/**
 * Real WordPress/Elementor, isolated candidate code, no DB option changes.
 * Usage:
 *   wp --path=/amari --skip-plugins=hashe-fa7-elementor \
 *       eval-file tests/wp-site-modes.php -- '<mode>'
 * where mode is 'kit', 'free', or 'invalid' (WP_CLI in argv).
 */
defined( 'ABSPATH' ) || exit;
$mode = isset( $args[0] ) ? (string) $args[0] : 'kit';
if ( ! in_array( $mode, array( 'kit', 'free', 'invalid' ), true ) ) {
    fwrite( STDERR, "Bad mode\n" );exit( 2 );
}
$initial = get_option( 'elementor_font_awesome_pro_kit_id' );
if ( 'free' === $mode ) {
    add_filter( 'pre_option_elementor_font_awesome_pro_kit_id', static function() { return ''; }, 10000 );
} elseif ( 'invalid' === $mode ) {
    add_filter( 'pre_option_elementor_font_awesome_pro_kit_id', static function() { return 'invalid'; }, 10000 );
}
require_once dirname( __DIR__ ) . '/hashe-fa7-elementor/hashe-fa7-elementor.php';
\Elementor\Plugin::$instance->editor->set_edit_mode( true );
$tabs = \Elementor\Icons_Manager::get_icon_manager_tabs();
$names = array_keys( $tabs );
$hashe = array_values( array_filter( $names, static function($x) {
    return 0 === strpos( $x, 'hashe-fa7-' );
} ) );
$expected = array(
    'kit' => array( 'hashe-fa7-fab', 'hashe-fa7-fasdr' ),
    'free' => array( 'hashe-fa7-free-solid', 'hashe-fa7-free-regular', 'hashe-fa7-free-brands' ),
    'invalid' => array()
);
$expected_hashe = $expected[$mode];
sort( $hashe );
sort( $expected_hashe );
if ( $hashe !== $expected_hashe ) {
    echo "MODE=" . $mode . " GOT=" . json_encode( $hashe ) . PHP_EOL;
    fwrite( STDERR, "Unexpected Hashe catalogs\n" );exit( 3 );
}
foreach ( array( 'fa-solid', 'fa-regular', 'fa-brands' ) as $name ) {
    if ( ! isset( $tabs[$name] ) ) {
        fwrite( STDERR, "Original Elementor native tab missing: " . $name . PHP_EOL );
        exit( 4 );
    }
    $source = (string) ( $tabs[$name]['fetchJson'] ?? '' );
    if ( 0 === strpos( $source, 'http://' ) ) {
        fwrite( STDERR, 'Mixed-content native HTTP icon catalog: ' . $name . PHP_EOL );
        exit( 9 );
    }
}
if ( 'kit' === $mode ) {
    foreach ( array( 'fa-light', 'fa-duotone' ) as $name ) {
        if ( ! isset( $tabs[$name] ) ) {
            fwrite( STDERR, "Native Elementor Pro library removed: " . $name . PHP_EOL );
            exit( 5 );
        }
    }
}
if ( 'kit' === $mode ) {
    foreach ( $hashe as $name ) {
        $source = (string) ( $tabs[$name]['fetchJson'] ?? '' );
        if ( 0 === strpos( $source, 'http://' ) ) {
            fwrite( STDERR, 'Mixed-content Kit AJAX endpoint: ' . $name . PHP_EOL );
            exit( 10 );
        }
    }
}
if ( count( $names ) !== count( array_unique( $names ) ) ) {
    fwrite( STDERR, "Duplicate tab keys\n" );exit( 6 );
}
if ( 'free' === $mode ) {
    foreach ( $hashe as $name ) {
        $tab = $tabs[$name];
        if ( ! is_string( $tab['url'] ) || ! is_array( $tab['enqueue'] ) ||
             ! is_string( $tab['fetchJson'] ) ) {
            fwrite( STDERR, "Free catalog lacked its own CSS/JSON\n" );exit( 7 );
        }
    }
}
echo 'MODE=' . $mode . ' NATIVE_AND_OTHER=' . ( count( $names ) - count( $hashe ) ) .
    ' HASHE=' . implode( ',', $hashe ) . ' KIT_SETTING_UNCHANGED=' . $initial . PHP_EOL;
