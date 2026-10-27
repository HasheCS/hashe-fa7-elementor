<?php
/**
 * Real WordPress AJAX contract canary, invoked using WP-CLI rather than HTTP.
 * Never changes Elementor's saved Kit and outputs the same JSON that the
 * logged-in picker fetchJson would see.
 *
 * wp --skip-plugins=hashe-fa7-elementor eval-file tests/wp-site-ajax.php <style>
 */
defined( 'ABSPATH' ) || exit;
$style = isset( $args[0] ) ? (string) $args[0] : '';
if ( ! in_array( $style, array( 'fab', 'fasdr', 'fas' ), true ) ) {
    fwrite( STDERR, "Unsupported test style\n" ); exit( 3 );
}
$admins = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
if ( ! $admins || ! user_can( $admins[0]->ID, 'edit_posts' ) ) {
    fwrite( STDERR, "Authorized editor unavailable\n" ); exit( 4 );
}
wp_set_current_user( $admins[0]->ID );
$_GET['style'] = $style;
$_GET['_wpnonce'] = wp_create_nonce( 'hashe_fa7_catalog' );
$_REQUEST['_wpnonce'] = $_GET['_wpnonce'];
require_once dirname( __DIR__ ) . '/hashe-fa7-elementor/hashe-fa7-elementor.php';
hashe_fa7_ajax_catalog();
