<?php
/** HP-PLUG-001 updater regression: isolated WordPress stubs (PHP 7.4+). */
define( 'ABSPATH', __DIR__ );
define( 'HOUR_IN_SECONDS', 3600 );
$GLOBALS['qa_http_calls'] = array();
$GLOBALS['qa_cache'] = array();
$GLOBALS['qa_response'] = null;
$GLOBALS['qa_filters'] = array();

function add_filter( $name, $callback, $priority = 10, $accepted_args = 1 ) {
    $GLOBALS['qa_filters'][ $name ] = array( $callback, $priority, $accepted_args );
}
function get_site_transient( $key ) {
    return $GLOBALS['qa_cache'][ $key ] ?? false;
}
function set_site_transient( $key, $value, $ttl ) {
    $GLOBALS['qa_cache'][ $key ] = $value;
    return true;
}
function wp_remote_get( $url, $args ) {
    $GLOBALS['qa_http_calls'][] = array( 'url' => $url, 'args' => $args );
    return $GLOBALS['qa_response'];
}
function is_wp_error( $value ) {
    return false;
}
function wp_remote_retrieve_response_code( $response ) {
    return $response['code'] ?? 0;
}
function wp_remote_retrieve_body( $response ) {
    return $response['body'] ?? '';
}
function qa_assert( $yes, $description ) {
    if ( ! $yes ) {
        fwrite( STDERR, 'FAIL ' . $description . PHP_EOL );
        exit( 2 );
    }
    echo 'PASS ' . $description . PHP_EOL;
}
function qa_reset( $response ) {
    $GLOBALS['qa_cache'] = array();
    $GLOBALS['qa_http_calls'] = array();
    $GLOBALS['qa_response'] = $response;
}
$slug = 'hashe-fa7-elementor';
$uri = 'https://plugins.hashe.com/hashe-fa7-elementor';
$endpoint = 'https://plugins.hashe.com/api/v1/updates/hashe-fa7-elementor';
$ver = '1.1.4';
$manifest = array(
    'slug' => $slug,
    'status' => 'stable',
    'approved' => true,
    'prerelease' => false,
    'version' => $ver,
    'tag' => 'v' . $ver,
    'sha256' => str_repeat( 'a', 64 ),
    'details_url' => 'https://github.com/HasheCS/' . $slug . '/releases/tag/v' . $ver,
    'package_url' => 'https://github.com/HasheCS/' . $slug . '/releases/download/v' . $ver . '/' . $slug . '-v' . $ver . '.zip',
    'tested' => '7.1',
    'requires_php' => '7.4',
);
$client_file = dirname( __DIR__ ) . '/hashe-fa7-elementor/includes/hashe-core/v1/update-client.php';
require $client_file;
$client = new \Hashe\PluginTools\V1\UpdateClient(
    $slug, $slug . '/' . $slug . '.php', $uri, $endpoint
);
$client->register();
qa_assert( isset( $GLOBALS['qa_filters']['update_plugins_plugins.hashe.com'] ), 'native WordPress update hook registered' );
$headers = array( 'UpdateURI' => $uri, 'Version' => '1.1.3' );
qa_reset( array( 'code' => 200, 'body' => json_encode( $manifest ) ) );
$result = $client->check( false, $headers, $slug . '/' . $slug . '.php', array( 'en_US' ) );
qa_assert( is_array( $result ) && $result['version'] === $ver, 'approved stable update offered' );
qa_assert( $result['package'] === $manifest['package_url'], 'exact approved GitHub release asset offered' );
qa_assert( $result['autoupdate'] === false, 'no forced automatic updates' );
qa_assert( count( $GLOBALS['qa_http_calls'] ) === 1, 'one metadata request' );
$req = $GLOBALS['qa_http_calls'][0];
qa_assert( $req['url'] === $endpoint, 'no domain or installation ID added to update request' );
qa_assert( $req['args']['sslverify'] === true && $req['args']['redirection'] === 0, 'HTTPS transport and redirects constrained' );
$result = $client->check( false, $headers, $slug . '/' . $slug . '.php', array() );
qa_assert( count( $GLOBALS['qa_http_calls'] ) === 1, 'approved manifest cached' );
qa_assert( $client->check( false, array( 'UpdateURI' => $uri, 'Version' => $ver ), $slug . '/' . $slug . '.php', array() ) === false, 'installed latest version not offered' );
qa_assert( $client->check( 'unmodified', $headers, 'other-plugin/plugin.php', array() ) === 'unmodified', 'no interference with other plugins' );
foreach ( array(
    'unapproved' => array( 'approved' => false ),
    'prerelease' => array( 'prerelease' => true ),
    'wrong_slug' => array( 'slug' => 'wrong' ),
    'foreign_package' => array( 'package_url' => 'https://other-host.example/plugin.zip' ),
    'wrong_tag' => array( 'tag' => 'v0.0.1' ),
    'missing_sha' => array( 'sha256' => '' ),
) as $description => $changes ) {
    $modified = array_merge( $manifest, $changes );
    qa_reset( array( 'code' => 200, 'body' => json_encode( $modified ) ) );
    qa_assert( $client->check( false, $headers, $slug . '/' . $slug . '.php', array() ) === false, $description . ' rejected' );
}
qa_reset( array( 'code' => 503, 'body' => '' ) );
qa_assert( $client->check( false, $headers, $slug . '/' . $slug . '.php', array() ) === false, 'offline or unavailable service fail-closed' );
qa_reset( array( 'code' => 200, 'body' => '{not json' ) );
qa_assert( $client->check( false, $headers, $slug . '/' . $slug . '.php', array() ) === false, 'malformed metadata fail-closed' );
echo "HASHE_UPDATE_CLIENT_TESTS=PASS\n";
