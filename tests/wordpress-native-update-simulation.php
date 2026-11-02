<?php
/**
 * WordPress-core upgrader integration on an isolated disposable QA site.
 * Uses a synthetic 1.1.4 ZIP ONLY inside GitHub Actions, never published.
 * Tests the approved metadata offer, checksum rejection, and real WP unzip.
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    fwrite( STDERR, "Only WP-CLI on isolated test site is permitted.\n" );
    exit( 2 );
}
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

$base = 'hashe-fa7-elementor';
$plugin = $base . '/' . $base . '.php';
$installed_file = WP_PLUGIN_DIR . '/' . $plugin;
$zip = getenv( 'HASHE_QA_SYNTHETIC_ZIP' );
if ( ! is_string( $zip ) || ! is_file( $zip ) ) {
    WP_CLI::error( 'Isolated synthetic ZIP not found' );
}
$before = get_plugin_data( $installed_file, false, false );
if ( $before['Version'] !== '1.1.3' ) {
    WP_CLI::error( 'Updater simulation must begin at candidate v1.1.3' );
}
$allowed = 'https://github.com/HasheCS/hashe-fa7-elementor/releases/download/v1.1.4/hashe-fa7-elementor-v1.1.4.zip';
$endpoint = 'https://plugins.hashe.com/api/v1/updates/hashe-fa7-elementor';
$manifest = array(
    'slug' => $base,
    'status' => 'stable',
    'approved' => true,
    'prerelease' => false,
    'version' => '1.1.4',
    'tag' => 'v1.1.4',
    'sha256' => hash_file( 'sha256', $zip ),
    'details_url' => 'https://github.com/HasheCS/hashe-fa7-elementor/releases/tag/v1.1.4',
    'package_url' => $allowed,
    'tested' => '7.1',
    'requires_php' => '7.4',
);
$sha = $manifest['sha256'];
$checked = 0;
$metadata_requests = 0;
$mock = static function ( $pre, $args, $url ) use ( &$checked, &$metadata_requests, $zip, $allowed, $endpoint ) {
    if ( $url === $endpoint ) {
        ++$metadata_requests;
        return new WP_Error( 'qa_service_offline', 'Synthetic test never contacts production service' );
    }
    if ( $url !== $allowed ) {
        return $pre;
    }
    ++$checked;
    if ( empty( $args['stream'] ) || empty( $args['filename'] ) ) {
        return new WP_Error( 'qa_not_streaming', 'Expected WordPress stream-to-file download' );
    }
    if ( ! copy( $zip, $args['filename'] ) ) {
        return new WP_Error( 'qa_copy_failed', 'Could not populate temporary ZIP stream' );
    }
    return array(
        'response' => array( 'code' => 200, 'message' => 'OK' ),
        'headers' => array( 'content-type' => 'application/zip' ),
        'body' => '',
        'cookies' => array(),
        'filename' => $args['filename'],
    );
};
add_filter( 'pre_http_request', $mock, 10, 3 );
set_site_transient( 'hashe_v1_update_' . $base, $manifest, 900 );
$available = apply_filters(
    'update_plugins_plugins.hashe.com',
    false,
    array( 'UpdateURI' => 'https://plugins.hashe.com/hashe-fa7-elementor', 'Version' => '1.1.3' ),
    $plugin,
    array( 'en_US' )
);
if ( ! is_array( $available ) || ( $available['version'] ?? '' ) !== '1.1.4' ||
    ( $available['package'] ?? '' ) !== $allowed ) {
    WP_CLI::error( 'Native updater did not offer the synthetic approved stable version' );
}
$upgrader = new Plugin_Upgrader( new WP_Upgrader_Skin() );
$args = array(
    'package' => $allowed,
    'destination' => WP_PLUGIN_DIR,
    'clear_destination' => true,
    'clear_working' => true,
    'is_multi' => false,
    'hook_extra' => array( 'plugin' => $plugin, 'type' => 'plugin', 'action' => 'update' ),
);
$before_hash = hash_file( 'sha256', $installed_file );
$manifest['sha256'] = str_repeat( 'f', 64 );
set_site_transient( 'hashe_v1_update_' . $base, $manifest, 900 );
$failed = $upgrader->run( $args );
if ( ! is_wp_error( $failed ) ||
    $failed->get_error_code() !== 'hashe_fa7_checksum_failed' ) {
    WP_CLI::error( 'Tampered package was not rejected with the expected error' );
}
if ( ! file_exists( $installed_file ) ||
    hash_file( 'sha256', $installed_file ) !== $before_hash ) {
    WP_CLI::error( 'Failed update modified the installed plugin' );
}
echo "WP_CORE_REJECTS_BAD_CHECKSUM=PASS\n";

$manifest['sha256'] = $sha;
set_site_transient( 'hashe_v1_update_' . $base, $manifest, 900 );
$upgrader2 = new Plugin_Upgrader( new WP_Upgrader_Skin() );
$success = $upgrader2->run( $args );
if ( is_wp_error( $success ) || ! $success ) {
    $reason = is_wp_error( $success ) ? $success->get_error_message() : 'False result';
    WP_CLI::error( 'WP upgrader rejected approved ZIP: ' . $reason );
}
$after = get_plugin_data( $installed_file, false, false );
if ( $after['Version'] !== '1.1.4' ) {
    WP_CLI::error( 'WordPress upgrader did not install the synthetic newer plugin' );
}
if ( $metadata_requests !== 0 || $checked !== 2 ) {
    WP_CLI::error( 'Unexpected network metadata access or ZIP retrieval count' );
}
if ( get_option( 'hashe_fa7_optional_reporting_v1', false ) !== false ) {
    WP_CLI::error( 'Native update unexpectedly created installation analytics consent' );
}
remove_filter( 'pre_http_request', $mock, 10 );
echo "WP_NATIVE_APPROVED_VERSION_OFFER=PASS\n";
echo "WP_CORE_UPGRADER_INSTALLED_VERIFIED_ZIP=PASS\n";
echo "WP_UPDATER_NO_SITE_METADATA_OR_CONSENT=PASS\n";
echo "NOTE=Synthetic version 1.1.4 is a CI fixture, NOT a GitHub release\n";
