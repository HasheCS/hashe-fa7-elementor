<?php
/**
 * Read-only authenticated WordPress AJAX catalog canary via WP CLI.
 * Does not create an actual browser login session or change user records.
 */
defined( 'ABSPATH' ) || exit;
$style = isset( $args[0] ) ? (string) $args[0] : '';
if ( ! in_array( $style, array( 'fab', 'fasdr', 'fas' ), true ) ) {
    fwrite( STDERR, "BAD_STYLE\n" ); exit( 2 );
}
if ( ! function_exists( 'hashe_fa7_ajax_catalog' ) ) {
    fwrite( STDERR, "INSTALLED_PLUGIN_NOT_LOADED\n" ); exit( 3 );
}
$editors = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
if ( ! $editors || ! user_can( $editors[0]->ID, 'edit_posts' ) ) {
    fwrite( STDERR, "NO_EDITOR_CAPABILITY\n" ); exit( 4 );
}
wp_set_current_user( $editors[0]->ID );
$_GET['style'] = $style;
$_GET['_wpnonce'] = wp_create_nonce( 'hashe_fa7_catalog' );
$_REQUEST['_wpnonce'] = $_GET['_wpnonce'];
hashe_fa7_ajax_catalog();
