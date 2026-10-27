<?php
/**
 * Plugin Name: Hashe Font Awesome 7 Icon Library for Elementor
 * Description: Makes Font Awesome 7 Free icons available in Elementor's icon picker, with additional Pro catalogs when an existing Font Awesome Kit is configured.
 * Version: 1.0.4-qa.1
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
 * Read the styles the configured Font Awesome Kit actually serves.
 *
 * Uses the public Kit embed assets, not a second API credential. A subsetted
 * Web Fonts Kit exposes exact family/style choices in its WOFF2 font URLs.
 * Keep a short cache so opening the Elementor picker never waits on the CDN.
 *
 * @return string[] Selected Font Awesome prefixes, or [] if not verifiable.
 */
function hashe_fa7_kit_styles( $kit_id ) {
    if ( ! preg_match( '/^[a-f0-9]{10}$/D', $kit_id ) ) {
        return array();
    }
    $cache_key = 'hashe_fa7_kit_styles_' . $kit_id;
    $cached = get_transient( $cache_key );
    if ( is_array( $cached ) ) {
        return $cached;
    }
    $response = wp_remote_get(
        'https://kit.fontawesome.com/' . $kit_id . '.js',
        array( 'timeout' => 7, 'redirection' => 2 )
    );
    if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
        return array();
    }
    $script = wp_remote_retrieve_body( $response );
    if ( ! preg_match( '/window\.FontAwesomeKitConfig\s*=\s*(\{.{1,12000}?\})\s*;/s', $script, $match ) ) {
        return array();
    }
    $config = json_decode( $match[1], true );
    if ( ! is_array( $config ) || $kit_id !== ( $config['token'] ?? '' ) ) {
        return array();
    }

    // A full-library SVG+JS Kit declares no CSS subset; retain the original
    // full-library behavior only for that explicit technology.
    if ( 'js' === ( $config['method'] ?? '' ) && empty( $config['subsetPath'] ) ) {
        return array( 'fas', 'far', 'fal', 'fat', 'fad', 'fadr',
            'fadl', 'fadt', 'fass', 'fasr', 'fasl', 'fast',
            'fasds', 'fasdr', 'fasdl', 'fasdt', 'fab' );
    }

    $subset = (string) ( $config['subsetPath'] ?? '' );
    if ( 'css' !== ( $config['method'] ?? '' ) ||
         ! preg_match( '~^assets/' . preg_quote( $kit_id, '~' ) . '/[0-9]+/kit\.css$~D', $subset ) ) {
        return array();
    }
    $css_response = wp_remote_get(
        'https://ka-p.fontawesome.com/' . $subset . '?token=' . $kit_id,
        array( 'timeout' => 10, 'redirection' => 2 )
    );
    if ( is_wp_error( $css_response ) || 200 !== wp_remote_retrieve_response_code( $css_response ) ) {
        return array();
    }
    $css = wp_remote_retrieve_body( $css_response );
    $fonts = array(
        'brands' => 'fab', 'solid' => 'fas', 'regular' => 'far',
        'light' => 'fal', 'thin' => 'fat', 'duotone' => 'fad',
        'duotone-solid' => 'fad', 'duotone-regular' => 'fadr',
        'duotone-light' => 'fadl', 'duotone-thin' => 'fadt',
        'sharp-solid' => 'fass', 'sharp-regular' => 'fasr',
        'sharp-light' => 'fasl', 'sharp-thin' => 'fast',
        'sharp-duotone-solid' => 'fasds', 'sharp-duotone-regular' => 'fasdr',
        'sharp-duotone-light' => 'fasdl', 'sharp-duotone-thin' => 'fasdt',
    );
    $selected = array();
    if ( preg_match_all( '~(?:pro-|free-)?fa-([a-z-]+)-[0-9]+(?:-[0-9]+)?\.woff2~', $css, $matches ) ) {
        foreach ( $matches[1] as $font ) {
            if ( isset( $fonts[$font] ) ) {
                $selected[$fonts[$font]] = true;
            }
        }
    }
    $styles = array_keys( $selected );
    // Cache only verified inventories. A temporary CDN error must not turn
    // into a persistent empty library.
    if ( $styles ) {
        set_transient( $cache_key, $styles, HOUR_IN_SECONDS );
    }
    return $styles;
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
    $kit_styles = $has_kit ? hashe_fa7_kit_styles( $kit_id ) : array();

    foreach ( $free_families as $family => $spec ) {
        // With a Kit, register only the families/styles it actually serves.
        if ( $has_kit ) {
            continue;
        }
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

    // Brands is selected by the Kit independently of the Pro-only styles.
    if ( in_array( 'fab', $kit_styles, true ) && is_readable( $dir . 'data/fab.json' ) ) {
        $tabs['hashe-fa7-fab'] = array(
            'name' => 'hashe-fa7-fab',
            'label' => 'Font Awesome 7 Kit - Brands',
            'url' => false,
            'enqueue' => false,
            'prefix' => 'fa-',
            'displayPrefix' => 'fa-brands',
            'labelIcon' => 'eicon-font-awesome',
            'ver' => HASHE_FA7_VERSION,
            'fetchJson' => $url . 'data/fab.json',
            'native' => true,
        );
    }

    foreach ( $pro_families as $prefix => $spec ) {
        if ( ! in_array( $prefix, $kit_styles, true ) ) {
            continue;
        }
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
        '1.0.3',
        true
    );
}
add_action( 'elementor/editor/after_enqueue_scripts', 'hashe_fa7_enqueue_editor_callback_guard', 20 );
