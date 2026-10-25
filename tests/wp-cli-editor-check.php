<?php
// Read-only WordPress-side verification of all plugin tabs and editor script hook.
$tabs = apply_filters( 'elementor/icons_manager/additional_tabs', array() );
$kit  = trim( (string) get_option( 'elementor_font_awesome_pro_kit_id', '' ) );
$checks = array( 'free-solid' => 2001, 'free-regular' => 273, 'free-brands' => 609 );
if ( '' !== $kit ) {
    $manifest = json_decode( file_get_contents( WP_PLUGIN_DIR . '/hashe-fa7-elementor/data/manifest.json' ), true );
    foreach ( $manifest['styles'] as $prefix => $expected ) {
        if ( 'fab' !== $prefix ) {
            $checks[ $prefix ] = (int) $expected;
        }
    }
}
foreach ( $checks as $suffix => $expected ) {
    $entry = $tabs[ 'hashe-fa7-' . $suffix ] ?? null;
    if ( ! $entry || ! empty( $entry['icons'] ) || empty( $entry['fetchJson'] ) ) {
        echo 'FAIL: library incorrectly configured: ' . $suffix . PHP_EOL;
        exit( 1 );
    }
    $path = WP_PLUGIN_DIR . '/hashe-fa7-elementor/data/' . ( strpos( $suffix, 'free-' ) === 0 ? $suffix : $suffix ) . '.json';
    $actual = count( json_decode( file_get_contents( $path ), true )['icons'] ?? array() );
    if ( $expected !== $actual ) {
        echo 'FAIL: catalog mismatch: ' . $suffix . ' ' . $actual . PHP_EOL;
        exit( 1 );
    }
}
echo 'PASS: All ' . count( $checks ) . ' categories use JSON fetching, not editor-embedded lists.' . PHP_EOL;
do_action( 'elementor/editor/after_enqueue_scripts' );
$scripts = wp_scripts();
$script = $scripts->registered['hashe-fa7-editor-callback-guard'] ?? null;
if ( ! $script || ! wp_script_is( 'hashe-fa7-editor-callback-guard', 'enqueued' ) || ! in_array( 'elementor-editor', $script->deps, true ) ) {
    echo 'FAIL: Elementor-only guard script not enqueued.' . PHP_EOL;
    exit( 1 );
}
echo 'PASS: Editor callback guard is enqueued with Elementor editor dependency.' . PHP_EOL;
echo 'GUARD_SCRIPT=' . $script->src . PHP_EOL;
