<?php
/** FA7 client must not collide with another Hashe plugin's symbols in either load order. */
define( 'ABSPATH', __DIR__ );
$order = $argv[1] ?? 'legacy-first';
if ( ! in_array( $order, array( 'legacy-first', 'fa7-first' ), true ) ) {
    fwrite( STDERR, 'Invalid order' . PHP_EOL ); exit( 2 );
}
$root = dirname( __DIR__ ) . '/hashe-fa7-elementor/includes/hashe-core/v1/';
$legacy = __DIR__ . '/fixtures/legacy-shared-v1.php';
if ( 'legacy-first' === $order ) { require_once $legacy; }
require_once $root . 'update-client.php';
require_once $root . 'consent-client.php';
if ( 'fa7-first' === $order ) { require_once $legacy; }
foreach ( array( 'UpdateClient', 'ConsentClient' ) as $name ) {
    if ( ! class_exists( 'Hashe\\PluginTools\\V1\\' . $name, false ) ||
         ! class_exists( 'Hashe\\FA7\\PluginTools\\V1\\' . $name, false ) ) {
        fwrite( STDERR, 'Collision: ' . $name . PHP_EOL ); exit( 2 );
    }
}
echo 'FA7_NAMESPACE_COEXISTENCE_' . $order . '=PASS' . PHP_EOL;
