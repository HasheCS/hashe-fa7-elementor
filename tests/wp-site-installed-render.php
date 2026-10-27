<?php
/**
 * WP-CLI installed-plugin rendered-icon acceptance: read-only.
 */
defined( 'ABSPATH' ) || exit;
if ( ! function_exists( 'hashe_fa7_register_icon_tabs' ) ) {
    fwrite( STDERR, "PLUGIN_MISSING\n" ); exit( 1 );
}
\Elementor\Plugin::$instance->editor->set_edit_mode( true );
$cases = array(
    array( 'library' => 'hashe-fa7-fab', 'value' => 'fa-brands fa-github', 'class' => 'fa-brands fa-github' ),
    array( 'library' => 'hashe-fa7-fasdr', 'value' => 'fa-sharp-duotone fa-regular fa-house', 'class' => 'fa-sharp-duotone fa-regular fa-house' ),
);
foreach ( $cases as $case ) {
    ob_start();
    \Elementor\Icons_Manager::render_icon( array(
        'library' => $case['library'],
        'value' => $case['value']
    ) );
    $html = (string) ob_get_clean();
    if ( ! is_string( $html ) || '' === $html ||
         false === strpos( $html, $case['class'] ) ) {
        fwrite( STDERR, 'ICON_RENDER_MISMATCH=' . $case['library'] . '=' . (string) $html . "\n" );
        exit( 5 );
    }
    echo "RENDERED=" . $case['library'] . " " . $html . "\n";
}
echo "PASS: Real installed Elementor icon rendering retains both actual Kit styles and class names.\n";
