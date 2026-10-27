<?php
/**
 * Read-only installed-plugin acceptance executed inside real Amari WordPress.
 * Does not load a second plugin copy, change options or create sessions.
 *
 * wp --path=/amari eval-file tests/wp-site-installed.php
 */
defined( 'ABSPATH' ) || exit;
if ( ! function_exists( 'hashe_fa7_register_icon_tabs' ) ) {
    fwrite( STDERR, "INTEGRATED_PLUGIN_NOT_LOADED\n" );
    exit( 41 );
}
\Elementor\Plugin::$instance->editor->set_edit_mode( true );
$tabs = \Elementor\Icons_Manager::get_icon_manager_tabs();
$expected = array(
    'fa-regular', 'fa-solid', 'fa-brands', 'fa-light', 'fa-duotone',
    'hashe-fa7-fab', 'hashe-fa7-fasdr'
);
$names = array_keys( $tabs );
if ( $expected !== $names || count( $names ) !== count( array_unique( $names ) ) ) {
    fwrite( STDERR, 'BAD_INSTALLED_EDITOR_TAB_IDENTITIES=' . json_encode( $names ) . "\n" );
    exit( 42 );
}
foreach ( $tabs as $name => $tab ) {
    $uri = isset( $tab['fetchJson'] ) ? (string) $tab['fetchJson'] : '';
    if ( '' === $uri || 0 === strpos( $uri, 'http://' ) ) {
        fwrite( STDERR, 'INSECURE_OR_MISSING_CATALOG=' . $name . "\n" );
        exit( 43 );
    }
}
if ( '23f6a60c28' !== get_option( 'elementor_font_awesome_pro_kit_id' ) ) {
    fwrite( STDERR, "EXISTING_KIT_SETTING_CHANGED\n" );
    exit( 44 );
}
$kit = hashe_fa7_kit_snapshot( '23f6a60c28' );
if ( ! is_array( $kit ) || ! empty( $kit['error'] ) ||
     ! isset( $kit['family_styles']['fab'] ) ||
     ! isset( $kit['family_styles']['fasdr'] ) ||
     2 !== count( $kit['family_styles'] ) ) {
    fwrite( STDERR, 'CURRENT_KIT_DETECTION_FAILED' . "\n" );
    exit( 45 );
}
echo "INSTALLED_TABS=" . implode( ',', $names ) . "\n";
echo "KIT_VERSION=" . $kit['version'] . " DELIVERY=" . $kit['method'] .
    " SUBSET_MODE=" . ( $kit['subset_mode'] ?? 'none' ) . "\n";
foreach ( array_slice( $expected, 0, 5 ) as $native ) {
    echo "NATIVE=" . $native . " URL=" . $tabs[$native]['fetchJson'] . "\n";
}
echo "PASS: Amari installed integrated plugin has five original native plus two Kit categories and secure same-origin JSON, with Kit unchanged.\n";
