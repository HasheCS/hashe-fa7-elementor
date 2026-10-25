<?php
// WP CLI read-only regression test for installed Font Awesome 7 plugin.
if ( ! function_exists( 'set_current_screen' ) ) {
    require_once ABSPATH . 'wp-admin/includes/screen.php';
}
set_current_screen( 'dashboard' );
$tabs = apply_filters( 'elementor/icons_manager/additional_tabs', array() );
$kit = trim( (string) get_option( 'elementor_font_awesome_pro_kit_id', '' ) );
$checks = array(
    'free-solid' => 2001,
    'free-regular' => 273,
    'free-brands' => 609,
);
if ( '' !== $kit ) {
    $manifest = json_decode(
        file_get_contents( WP_PLUGIN_DIR . '/hashe-fa7-elementor/data/manifest.json' ),
        true
    );
    foreach ( $manifest['styles'] as $prefix => $expected ) {
        if ( 'fab' === $prefix ) {
            continue; // Brands are already covered by the Free Brands catalog.
        }
        $checks[ $prefix ] = (int) $expected;
    }
}
foreach ( $checks as $suffix => $expected ) {
    $key = 'hashe-fa7-' . $suffix;
    $tab = $tabs[ $key ] ?? null;
    $actual = is_array( $tab['icons'] ?? null ) ? count( $tab['icons'] ) : -1;
    $valid = $actual === $expected;
    echo 'ICON_TAB ' . $suffix . ' expected=' . $expected . ' actual=' . $actual . ' ' . ( $valid ? 'PASS' : 'FAIL' ) . PHP_EOL;
    if ( ! $valid ) {
        exit( 1 );
    }
}
$size = strlen( (string) wp_json_encode( array_values( $tabs ) ) );
echo 'EDITOR_PAYLOAD_BYTES=' . $size . PHP_EOL;
echo 'EDITOR_MEMORY_PEAK_BYTES=' . memory_get_peak_usage( true ) . PHP_EOL;
