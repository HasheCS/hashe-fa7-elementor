<?php
/**
 * Plugin Name: Hashe Font Awesome 7 Icon Library for Elementor
 * Description: Makes Font Awesome 7 Free icons available in Elementor's icon picker, with additional Pro catalogs when an existing Font Awesome Kit is configured.
 * Version: 1.0.2
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
            'labelIcon'     => 'fas fa-icons',
            'ver'           => HASHE_FA7_VERSION,
            'fetchJson'     => $url . 'data/free-' . $family . '.json',
            'native'        => true,
        );
    }

    if ( ! $has_kit ) {
        return $tabs;
    }

    /*
     * A Kit ID indicates that Elementor is configured to load a Kit. It does
     * not establish a Pro license. The Kit must itself support the selected
     * Pro fonts; this plugin does not distribute or unlock commercial assets.
     */
    $manifest_path = $dir . 'data/manifest.json';
    if ( ! is_readable( $manifest_path ) ) {
        return $tabs;
    }
    $manifest = json_decode( (string) file_get_contents( $manifest_path ), true );
    if (
        ! is_array( $manifest )
        || HASHE_FA7_VERSION !== ( $manifest['version'] ?? '' )
        || ! is_array( $manifest['styles'] ?? null )
    ) {
        return $tabs;
    }

    $pro_families = array(
        'fas'   => array( 'Classic Solid', 'fa-solid' ),
        'far'   => array( 'Classic Regular', 'fa-regular' ),
        'fal'   => array( 'Classic Light', 'fa-light' ),
        'fat'   => array( 'Classic Thin', 'fa-thin' ),
        'fad'   => array( 'Duotone Solid', 'fa-duotone fa-solid' ),
        'fadr'  => array( 'Duotone Regular', 'fa-duotone fa-regular' ),
        'fadl'  => array( 'Duotone Light', 'fa-duotone fa-light' ),
        'fadt'  => array( 'Duotone Thin', 'fa-duotone fa-thin' ),
        'fass'  => array( 'Sharp Solid', 'fa-sharp fa-solid' ),
        'fasr'  => array( 'Sharp Regular', 'fa-sharp fa-regular' ),
        'fasl'  => array( 'Sharp Light', 'fa-sharp fa-light' ),
        'fast'  => array( 'Sharp Thin', 'fa-sharp fa-thin' ),
        'fasds' => array( 'Sharp Duotone Solid', 'fa-sharp-duotone fa-solid' ),
        'fasdr' => array( 'Sharp Duotone Regular', 'fa-sharp-duotone fa-regular' ),
        'fasdl' => array( 'Sharp Duotone Light', 'fa-sharp-duotone fa-light' ),
        'fasdt' => array( 'Sharp Duotone Thin', 'fa-sharp-duotone fa-thin' ),
    );

    foreach ( $pro_families as $prefix => $spec ) {
        $count = (int) ( $manifest['styles'][ $prefix ] ?? 0 );
        $path  = 'data/' . $prefix . '.json';
        if ( $count < 1 || ! is_readable( $dir . $path ) ) {
            continue;
        }

        $name = 'hashe-fa7-' . $prefix;
        $tabs[ $name ] = array(
            'name'          => $name,
            'label'         => 'Font Awesome 7 Pro - ' . $spec[0] . ' (' . number_format( $count ) . ')',
            'url'           => false,
            'enqueue'       => false,
            'prefix'        => 'fa-',
            'displayPrefix' => $spec[1],
            'labelIcon'     => 'fas fa-icons',
            'ver'           => HASHE_FA7_VERSION,
            'fetchJson'     => $url . $path,
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
        '1.0.2',
        true
    );
}
add_action( 'elementor/editor/after_enqueue_scripts', 'hashe_fa7_enqueue_editor_callback_guard', 20 );
