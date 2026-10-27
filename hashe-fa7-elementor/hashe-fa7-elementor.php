<?php
/**
 * Plugin Name: Hashe Font Awesome 7 Icon Library for Elementor
 * Description: Makes Font Awesome 7 Free icons available in Elementor's icon picker, with additional Pro catalogs when an existing Font Awesome Kit is configured.
 * Version: 1.1.0-beta.3
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Requires Plugins: elementor
 * Author: Hashe Computer Solutions
 * Author URI: https://hashe.com/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hashe-fa7-elementor
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'HASHE_FA7_VERSION' ) ) {
    define( 'HASHE_FA7_VERSION', '7.3.1' );
}

require_once __DIR__ . '/includes/kit-bridge.php';

/**
 * Extend Elementor's normal icon picker rather than replacing its built-in tabs.
 *
 * Font Awesome Free font files and CSS are included under their own license.
 * Pro icons are never bundled: Pro tabs only describe icon names and use the
 * site's existing Kit for licensed rendering.
 *
 * @param array $tabs Existing extra Elementor icon libraries.
 * @return array
 */
function hashe_fa7_register_icon_tabs( $tabs ) {
    if ( ! is_array( $tabs ) ) {
        return $tabs;
    }

    $dir = plugin_dir_path( __FILE__ );
    $url = plugin_dir_url( __FILE__ );

    $free_families = array(
        'solid'   => array( 'Free - Solid', 'fa-solid', 'fa-solid-900.woff2' ),
        'regular' => array( 'Free - Regular', 'fa-regular', 'fa-regular-400.woff2' ),
        'brands'  => array( 'Free - Brands', 'fa-brands', 'fa-brands-400.woff2' ),
    );

    /*
     * When Elementor has a Kit ID, let that Kit provide the font families.
     * Without a Kit, load Font Awesome Free v7 CSS/webfonts locally.
     */
    $kit_id  = trim( (string) get_option( 'elementor_font_awesome_pro_kit_id', '' ) );
    $has_kit = '' !== $kit_id;
    if ( $has_kit ) {
        return hashe_fa7_register_kit_tabs( $tabs, $kit_id );
    }

    foreach ( $free_families as $family => $spec ) {
        $catalog = $dir . 'data/free-' . $family . '.json';
        if ( ! is_readable( $catalog ) ) {
            continue;
        }

        $name = 'hashe-fa7-free-' . $family;
        $tabs[ $name ] = array(
            'name'          => $name,
            'label'         => 'Font Awesome 7 ' . $spec[0],
            'url'           => $has_kit ? false : $url . 'assets/css/' . $family . '.min.css',
            'enqueue'       => $has_kit ? false : array( $url . 'assets/css/fontawesome.min.css' ),
            'prefix'        => 'fa-',
            'displayPrefix' => $spec[1],
            'labelIcon'     => 'eicon-font-awesome',
            'ver'           => HASHE_FA7_VERSION,
            'fetchJson'     => $url . 'data/free-' . $family . '.json',
            'native'        => true,
        );
    }

    return $tabs;
}
add_filter( 'elementor/icons_manager/additional_tabs', 'hashe_fa7_register_icon_tabs', 20 );

/**
 * Only Elementor's editor needs the async callback guard.
 * Preserve the standard per-tab JSON loading and normal frontend behavior.
 */
function hashe_fa7_enqueue_editor_callback_guard() {
    wp_enqueue_script(
        'hashe-fa7-editor-callback-guard',
        plugins_url( 'assets/js/editor-callback-guard.js', __FILE__ ),
        array( 'elementor-editor' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'assets/js/editor-callback-guard.js' ),
        true
    );
}
add_action( 'elementor/editor/after_enqueue_scripts', 'hashe_fa7_enqueue_editor_callback_guard', 20 );

/**
 * Retain Elementor Pro's five native icon tabs when a Kit enables only part
 * of the Font Awesome family range.
 *
 * The plugin does NOT modify the customer's Kit or change the saved icons.
 * Already-enabled Classic families remain untouched.
 *
 * For missing native Solid/Regular, prefer the legally bundled Free font and
 * use the Kit-selected Sharp Duotone only for Pro-only glyphs not in Free.
 * A missing Light/Duotone can use Sharp Duotone as an explicitly documented
 * approximate visual fallback; this cannot reproduce those exact Pro styles.
 */
function hashe_fa7_native_compat_rules( $kit ) {
    if ( ! is_array( $kit ) || 'css' !== ( $kit['method'] ?? '' ) ||
         ! empty( $kit['error'] ) || ! is_array( $kit['family_styles'] ?? null ) ) {
        return array();
    }

    $available = $kit['family_styles'];
    $has_sharp_duotone = isset( $available['fasdr'] );
    $fallback_stack = $has_sharp_duotone ? ', "Font Awesome 7 Sharp Duotone"' : '';
    $rules = array();

    if ( ! isset( $available['fas'] ) ) {
        $rules['solid'] = '.fas:not(.fa-sharp-duotone):not(.fa-sharp) {' .
            'font-family:"Hashe Elementor Native Solid"' . $fallback_stack . ' !important;' .
            'font-weight:900 !important;}';
    }
    if ( ! isset( $available['far'] ) ) {
        $rules['regular'] = '.far:not(.fa-sharp-duotone):not(.fa-sharp) {' .
            'font-family:"Hashe Elementor Native Regular"' . $fallback_stack . ' !important;' .
            'font-weight:400 !important;}';
    }
    if ( $has_sharp_duotone && ! isset( $available['fal'] ) ) {
        $rules['light-fallback'] = '.fal:not(.fa-sharp-duotone):not(.fa-sharp) {' .
            'font-family:"Font Awesome 7 Sharp Duotone" !important;' .
            'font-weight:400 !important;}';
    }
    if ( $has_sharp_duotone && ! isset( $available['fad'] ) ) {
        $rules['duotone-fallback'] = '.fad:not(.fa-sharp-duotone):not(.fa-sharp) {' .
            'font-family:"Font Awesome 7 Sharp Duotone" !important;' .
            'font-weight:400 !important;} ' .
            '.fad:not(.fa-sharp-duotone):not(.fa-sharp)::after {' .
            'content:none !important;display:none !important;}';
    }
    return $rules;
}

function hashe_fa7_enqueue_native_compat() {
    $kit_id = trim( (string) get_option( 'elementor_font_awesome_pro_kit_id', '' ) );
    if ( ! preg_match( '/^[a-f0-9]{10}$/D', $kit_id ) ) {
        return;
    }
    $kit = hashe_fa7_kit_snapshot( $kit_id );
    $rules = hashe_fa7_native_compat_rules( $kit );
    if ( ! $rules ) {
        return;
    }
    wp_enqueue_style(
        'hashe-fa7-native-compat',
        plugins_url( 'assets/css/native-pro-compat.css', __FILE__ ),
        array(),
        HASHE_FA7_VERSION
    );
    wp_add_inline_style( 'hashe-fa7-native-compat', implode( "\n", $rules ) );
}
add_action( 'elementor/editor/after_enqueue_styles', 'hashe_fa7_enqueue_native_compat', 25 );
add_action( 'elementor/frontend/after_enqueue_styles', 'hashe_fa7_enqueue_native_compat', 25 );
