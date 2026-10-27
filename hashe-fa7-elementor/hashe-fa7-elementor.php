<?php
/**
 * Plugin Name: Hashe Font Awesome 7 Icon Library for Elementor
 * Description: Makes Font Awesome 7 Free icons available in Elementor's icon picker, with additional Pro catalogs when an existing Font Awesome Kit is configured.
 * Version: 1.1.0-qa.20261027
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
 * Safely fix mixed-content catalog paths from Elementor Pro on HTTPS sites.
 *
 * Amari's actual Elementor Pro registration reports all FIVE native
 * fetchJson addresses as http://dev.sasseagency.com/amari/... while the
 * Elementor editor is loaded through HTTPS. Browsers block those requests,
 * producing empty native icon tabs despite correct library registration.
 *
 * The same legacy WordPress home URL may turn Hashe's Kit admin-ajax
 * fetchJson into an HTTP request. Convert ONLY this WordPress installation's
 * own HTTP URLs into same-origin root-relative paths. We don't change the
 * site URL, Kit, native CSS, icon IDs, third-party destinations or database.
 */
function hashe_fa7_normalize_local_icon_fetch_urls( $tabs ) {
    if ( ! is_array( $tabs ) ) {
        return $tabs;
    }
    $home = wp_parse_url( home_url( '/' ) );
    if ( ! is_array( $home ) || empty( $home['host'] ) ) {
        return $tabs;
    }
    $host = strtolower( $home['host'] );
    $base_path = rtrim( (string) ( $home['path'] ?? '/' ), '/' ) . '/';
    foreach ( $tabs as $name => $tab ) {
        if ( ! is_array( $tab ) ||
             ! is_string( $tab['fetchJson'] ?? null ) ) {
            continue;
        }
        $url = wp_parse_url( $tab['fetchJson'] );
        if ( ! is_array( $url ) ||
             'http' !== ( $url['scheme'] ?? '' ) ||
             $host !== strtolower( (string) ( $url['host'] ?? '' ) ) ||
             isset( $url['user'] ) || isset( $url['pass'] ) ||
             isset( $url['port'] ) || isset( $url['fragment'] ) ||
             ! is_string( $url['path'] ?? null ) ||
             0 !== strpos( $url['path'], $base_path ) ) {
            continue;
        }
        // Root-relative URLs inherit the actual editor's HTTPS origin and
        // work correctly when WordPress is in a subdirectory such as /amari/.
        $tab['fetchJson'] = $url['path'] .
            ( isset( $url['query'] ) ? '?' . $url['query'] : '' );
        $tabs[$name] = $tab;
    }
    return $tabs;
}
// Cover Elementor's initial Free/native tabs as well as Elementor Pro's
// additional five and our own Kit catalogs. Run late after registration.
add_filter(
    'elementor/icons_manager/native',
    'hashe_fa7_normalize_local_icon_fetch_urls',
    99999
);
add_filter(
    'elementor/icons_manager/additional_tabs',
    'hashe_fa7_normalize_local_icon_fetch_urls',
    99999
);


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


/**
 * Keep the version-scoped catalog warm for active customers without asking
 * them to configure another token or manually update a list of styles.
 *
 * WP-Cron is a best-effort prewarmer: editor AJAX remains the reliable
 * on-demand fallback when sites disable cron or have sparse traffic.
 * This job never updates installed Free fonts or the customer's Kit settings.
 */
function hashe_fa7_refresh_catalog_job() {
    $kit_id = trim( (string) get_option( 'elementor_font_awesome_pro_kit_id', '' ) );
    if ( ! preg_match( '/^[a-f0-9]{10}$/D', $kit_id ) ) {
        return;
    }
    $kit = hashe_fa7_kit_snapshot( $kit_id );
    if ( ! is_array( $kit ) || ! empty( $kit['error'] ) ||
         empty( $kit['family_styles'] ) ) {
        return;
    }
    // Exactly one full release index is built/reused for all selected tabs.
    hashe_fa7_release_style_catalog( $kit['version'] );
}
add_action( 'hashe_fa7_refresh_catalog', 'hashe_fa7_refresh_catalog_job' );

function hashe_fa7_schedule_refresh() {
    if ( ! wp_next_scheduled( 'hashe_fa7_refresh_catalog' ) ) {
        wp_schedule_event( time() + 120, 'hourly', 'hashe_fa7_refresh_catalog' );
    }
}
add_action( 'init', 'hashe_fa7_schedule_refresh' );
register_activation_hook( __FILE__, 'hashe_fa7_schedule_refresh' );

function hashe_fa7_unschedule_refresh() {
    wp_clear_scheduled_hook( 'hashe_fa7_refresh_catalog' );
}
register_deactivation_hook( __FILE__, 'hashe_fa7_unschedule_refresh' );
