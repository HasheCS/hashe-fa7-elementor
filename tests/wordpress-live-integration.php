<?php
/**
 * Isolated WordPress+Elementor integration checks.
 * Runs via WP-CLI after a clean install or an upgrade, never in production.
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    fwrite( STDERR, "ERROR: RUN_USING_WP_CLI\n" );
    exit( 1 );
}
$expected = '1.1.3';
$file = WP_PLUGIN_DIR . '/hashe-fa7-elementor/hashe-fa7-elementor.php';
if ( ! file_exists( $file ) ) {
    WP_CLI::error( 'Plugin main file not present' );
}
require_once ABSPATH . 'wp-admin/includes/plugin.php';
$meta = get_plugin_data( $file, false, false );
if ( $meta['Version'] !== $expected ) {
    WP_CLI::error( 'Expected version 1.1.3, got ' . $meta['Version'] );
}
if ( $meta['UpdateURI'] !== 'https://plugins.hashe.com/hashe-fa7-elementor' ) {
    WP_CLI::error( 'Expected native Update URI' );
}
if ( ! is_plugin_active( 'elementor/elementor.php' ) ||
    ! is_plugin_active( 'hashe-fa7-elementor/hashe-fa7-elementor.php' ) ) {
    WP_CLI::error( 'Elementor and the Hashe plugin must both be active' );
}
if ( ! function_exists( 'hashe_fa7_register_kit_additional_tabs' ) ||
    ! function_exists( 'hashe_fa7_render_namespaced_free_icon' ) ) {
    WP_CLI::error( 'Expected plugin functions not loaded' );
}

$expect = array( 'fas' => 1422, 'far' => 169, 'fab' => 572 );
$free = array();
foreach ( $expect as $style => $count ) {
    $free[ 'hashe-fa7-free-' . $style ] = $count;
}

$kit_state = (string) get_option( 'elementor_font_awesome_pro_kit_id', '' );
if ( ! in_array( $kit_state, array( '', 'not-a-kit' ), true ) ) {
    WP_CLI::error( 'Unexpected Kit option in isolated integration test' );
}
$tabs = apply_filters( 'elementor/icons_manager/additional_tabs', array() );
foreach ( $free as $name => $count ) {
    if ( ! isset( $tabs[ $name ] ) ||
        count( $tabs[ $name ]['icons'] ) !== $count ) {
        WP_CLI::error( 'Free catalog missing or incomplete: ' . $name );
    }
}
$old = hashe_fa7_render_namespaced_free_icon(
    array( 'library' => 'hashe-fa7-free-fas', 'value' => 'fas fa-star' )
);
$new = hashe_fa7_render_namespaced_free_icon(
    array( 'library' => 'hashe-fa7-free-fas', 'value' => 'hfas fa-star' )
);
if ( strpos( $old, 'hfas fa-star' ) === false ||
    strpos( $new, 'hfas fa-star' ) === false ) {
    WP_CLI::error( 'Legacy/new icon values do not render' );
}

if ( has_action( 'admin_post_hashe_fa7_privacy_choice' ) === false ||
    has_action( 'hashe_fa7_optional_reporting_tick' ) === false ) {
    WP_CLI::error( 'Optional reporting controls were not registered' );
}
$privacy = get_option( 'hashe_fa7_optional_reporting_v1', false );
if ( false !== $privacy && ! is_array( $privacy ) ) {
    WP_CLI::error( 'Unexpected optional reporting option structure' );
}
if ( is_array( $privacy ) && 'allowed' === ( $privacy['status'] ?? '' ) ) {
    WP_CLI::error( 'Optional reporting silently opted in during install/upgrade' );
}
if ( wp_next_scheduled( 'hashe_fa7_optional_reporting_tick' ) ) {
    WP_CLI::error( 'Unconsented optional reporting scheduled during install/upgrade' );
}
if ( has_filter( 'update_plugins_plugins.hashe.com' ) === false ) {
    WP_CLI::error( 'Native updater hook not registered' );
}

// No optional telemetry consent or reporting is currently enabled:
// check the registration script is not doing any outbound reporting.
$seen = array();
$intercept = static function ( $pre, $args, $url ) use ( &$seen ) {
    if ( strpos( $url, 'plugins.hashe.com' ) !== false ) {
        $seen[] = $url;
        return new WP_Error( 'qa_offline', 'Offline integration test' );
    }
    return $pre;
};
add_filter( 'pre_http_request', $intercept, 10, 3 );
$tabs_again = apply_filters( 'elementor/icons_manager/additional_tabs', array() );
remove_filter( 'pre_http_request', $intercept, 10 );
if ( count( $tabs_again['hashe-fa7-free-fas']['icons'] ?? array() ) !== 1422 ||
    $seen !== array() ) {
    WP_CLI::error( 'Free functionality depended on Hashe service' );
}

echo "WORDPRESS_VERSION=" . get_bloginfo( 'version' ) . PHP_EOL;
echo "PHP_VERSION=" . PHP_VERSION . PHP_EOL;
echo "ELEMENTOR_VERSION=" . ( defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '(unknown)' ) . PHP_EOL;
echo "PLUGIN_VERSION=" . $meta['Version'] . PHP_EOL;
echo "KIT_STATE=" . ( $kit_state === '' ? 'NO_KIT' : 'INVALID_KIT' ) . PHP_EOL;
echo "FREE_LIBRARIES=3" . PHP_EOL;
echo "LEGACY_RENDER=PASS" . PHP_EOL;
echo "NATIVE_UPDATE_HOOK=PASS" . PHP_EOL;
echo "OPTIONAL_REPORTING_DEFAULT_OFF=PASS" . PHP_EOL;
echo "NO_SERVICE_DEPENDENCY=PASS" . PHP_EOL;
echo "ISOLATED_WORDPRESS_INTEGRATION=PASS" . PHP_EOL;
