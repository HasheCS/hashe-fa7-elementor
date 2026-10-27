<?php
/**
 * Plugin Name: Hashe Font Awesome 7 Icon Library for Elementor
 * Description: Makes Font Awesome 7 Free icons available in Elementor's icon picker, with additional Pro catalogs when an existing Font Awesome Kit is configured.
 * Version: 1.1.0-beta.4
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

    // Pass Elementor's own server-registered picker tabs as the authoritative
    // baseline. This includes native and third-party libraries and gives the
    // editor a safe way to recover when async callbacks corrupt its tab array.
    if ( class_exists( '\\Elementor\\Icons_Manager' ) ) {
        $registered = \Elementor\Icons_Manager::get_icon_manager_tabs();
        if ( is_array( $registered ) && $registered ) {
            $expected = array();
            $seen = array();
            foreach ( $registered as $tab ) {
                if ( ! is_array( $tab ) || ! is_string( $tab['name'] ?? null ) ||
                     isset( $seen[ $tab['name'] ] ) ) {
                    continue;
                }
                $seen[ $tab['name'] ] = true;
                $expected[] = $tab;
            }
            if ( $expected ) {
                wp_add_inline_script(
                    'hashe-fa7-editor-callback-guard',
                    'window.hasheFa7ExpectedTabs=' . wp_json_encode( $expected, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';',
                    'before'
                );
            }
        }
    }
}
add_action( 'elementor/editor/after_enqueue_scripts', 'hashe_fa7_enqueue_editor_callback_guard', 20 );

