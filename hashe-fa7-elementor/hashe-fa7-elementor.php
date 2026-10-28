<?php
/**
 * Plugin Name: Hashe Font Awesome 7 Icon Library for Elementor
 * Description: Enhances Elementor's native icon picker with Font Awesome 7 catalogs, using styles supplied by the configured Font Awesome Kit.
 * Version: 1.1.1
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Requires Plugins: elementor
 * Author: Hashe Computer Solutions
 * Author URI: https://hashe.com/
 * License: GPL-2.0-or-later
 * Text Domain: hashe-fa7-elementor
 */
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/includes/catalog-provider.php';

/**
 * Register Kit-owned tabs through Elementor's additional_tabs API.
 * Preserve the native picker and Elementor's native tab definitions.
 * A separate approved Kit-only editor cache adapter scopes storage hooks
 * strictly to our own added catalog keys.
 */
function hashe_fa7_additive_specs() {
    return array(
        'fab'   => array( 'Brands', 'fa-brands' ),
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
}

/** Get Kit's version and publicly served family styles. Cached by Kit ID. */
function hashe_fa7_additive_kit() {
    static $memo = null;
    if ( null !== $memo ) {
        return $memo;
    }
    $memo = false;
    $id = trim( (string) get_option( 'elementor_font_awesome_pro_kit_id', '' ) );
    if ( ! preg_match( '/^[a-f0-9]{10}$/D', $id ) ) {
        return false;
    }

    $cache_key = 'hashe_fa7_additive_kit_' . $id;
    $cached = get_transient( $cache_key );

    // Re-check the active Kit's published revision during Elementor editor
    // initialization. Kit IDs stay constant when styles change, so an
    // hour-long ID-only transient otherwise gives obsolete sidebar entries.
    // Frontend requests retain the fast cached path.
    $editor_reload = did_action( 'elementor/editor/before_enqueue_scripts' ) ||
        doing_action( 'elementor/editor/before_enqueue_scripts' ) ||
        did_action( 'elementor/editor/after_enqueue_scripts' );
    if ( ! $editor_reload && is_array( $cached ) && ! empty( $cached['styles'] ) ) {
        $memo = $cached;
        return $memo;
    }

    $headers = array( 'Referer' => home_url( '/' ) );
    $request = array(
        'timeout'     => 8,
        'redirection' => 2,
        'headers'     => $headers,
        'user-agent'  => 'Mozilla/5.0 (compatible; HasheFA7NativePicker/1.0)',
    );
    $response = wp_remote_get( 'https://kit.fontawesome.com/' . $id . '.js', $request );
    if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
        return false;
    }

    $script = wp_remote_retrieve_body( $response );
    if ( ! preg_match( '/window\.FontAwesomeKitConfig\s*=\s*(\{.{1,12000}?\})\s*;/s', $script, $match ) ) {
        return false;
    }
    $config = json_decode( $match[1], true );
    if ( ! is_array( $config ) || $id !== ( $config['token'] ?? null ) ) {
        return false;
    }

    $version = (string) ( $config['version'] ?? '' );
    // Kit IDs are user-owned; the metadata API is selected by this Kit's
    // published release version, never a plugin-bundled 7.3.1 manifest.
    if ( ! hashe_fa7_api_supported_version( $version ) ) {
        return false;
    }

    $styles = array();
    $method = (string) ( $config['method'] ?? '' );
    $subset = (string) ( $config['subsetPath'] ?? '' );

    // Compare the actual live Kit bootstrap: a changed subsetPath means the
    // Kit owner published a new selection, even if the Kit ID is unchanged.
    $revision = hash( 'sha256', wp_json_encode( array(
        'token' => $id,
        'version' => $version,
        'method' => $method,
        'subsetPath' => $subset,
        'startupFilename' => (string) ( $config['startupFilename'] ?? '' ),
        'license' => (string) ( $config['license'] ?? '' ),
    ) ) );
    if (
        is_array( $cached ) &&
        ! empty( $cached['styles'] ) &&
        ( $cached['revision'] ?? '' ) === $revision
    ) {
        $memo = $cached;
        return $memo;
    }

    // An SVG+JS Kit without a subset loads Font Awesome's full Pro entrypoint.
    // Do not equate method=js alone with every style being available: verify
    // the explicit full-Pro startup filename, no subset, license, version and
    // successfully served CDN script before registering the full catalog.
    if (
        'js' === $method &&
        '' === $subset &&
        'pro' === (string) ( $config['license'] ?? '' ) &&
        'pro.min.js' === (string) ( $config['startupFilename'] ?? '' )
    ) {
        $full_url = 'https://ka-p.fontawesome.com/releases/v' . $version .
            '/js/pro.min.js?token=' . $id;
        $full_response = wp_remote_get( $full_url, $request );
        if (
            ! is_wp_error( $full_response ) &&
            200 === (int) wp_remote_retrieve_response_code( $full_response ) &&
            strlen( (string) wp_remote_retrieve_body( $full_response ) ) > 50000
        ) {
            $styles = array_keys( hashe_fa7_additive_specs() );
        }
    } elseif (
        'css' === $method &&
        preg_match( '~^assets/' . preg_quote( $id, '~' ) . '/[0-9]+/kit\.css$~D', $subset )
    ) {
        // A subsetted Web Fonts Kit: styles are identifiable from its font files,
        // never from common CSS class names (those are shared across styles).
        $css_response = wp_remote_get(
            'https://ka-p.fontawesome.com/' . $subset . '?token=' . $id,
            $request
        );
        if ( is_wp_error( $css_response ) || 200 !== wp_remote_retrieve_response_code( $css_response ) ) {
            return false;
        }
        $css = wp_remote_retrieve_body( $css_response );
        $font_names = array(
            'brands' => 'fab', 'solid' => 'fas', 'regular' => 'far',
            'light' => 'fal', 'thin' => 'fat',
            'duotone-solid' => 'fad', 'duotone' => 'fad',
            'duotone-regular' => 'fadr', 'duotone-light' => 'fadl',
            'duotone-thin' => 'fadt',
            'sharp-solid' => 'fass', 'sharp-regular' => 'fasr',
            'sharp-light' => 'fasl', 'sharp-thin' => 'fast',
            'sharp-duotone-solid' => 'fasds',
            'sharp-duotone-regular' => 'fasdr',
            'sharp-duotone-light' => 'fasdl',
            'sharp-duotone-thin' => 'fasdt',
        );
        if ( preg_match_all( '~(?:pro-|free-)?fa-([a-z-]+)-[0-9]+(?:-[0-9]+)?\.woff2~', $css, $matches ) ) {
            foreach ( $matches[1] as $font ) {
                if ( isset( $font_names[ $font ] ) ) {
                    $styles[ $font_names[ $font ] ] = true;
                }
            }
        }
        $styles = array_keys( $styles );
    }

    if ( ! $styles ) {
        return false; // Keep Elementor's existing integration unchanged.
    }
    $memo = array(
        'id' => $id, 'version' => $version, 'revision' => $revision,
        'styles' => $styles, 'method' => $method,
    );
    set_transient( $cache_key, $memo, HOUR_IN_SECONDS );
    return $memo;
}

/**
 * Kit libraries are distinct from Elementor Core/Pro native libraries.
 * They must appear BELOW My Libraries, never above it.
 */
function hashe_fa7_register_kit_additional_tabs( $tabs ) {
    if ( ! is_array( $tabs ) ) {
        return $tabs;
    }
    $kit_id = trim( (string) get_option( 'elementor_font_awesome_pro_kit_id', '' ) );
    if ( '' === $kit_id ) {
        // Elementor's normal Free native libraries are untouched. Add our
        // separate, licensed Font Awesome 7.3.1 Free icon catalogs below them.
        $free = hashe_fa7_free_catalogs();
        foreach ( array(
            'fas' => 'Solid', 'far' => 'Regular', 'fab' => 'Brands',
        ) as $style => $name ) {
            $id = 'hashe-fa7-free-' . $style;
            if ( isset( $tabs[ $id ] ) || empty( $free[ $style ] ) ) {
                continue;
            }
            $tabs[ $id ] = array(
                'name'          => $id,
                'label'         => 'Font Awesome Free - ' . $name,
                'labelIcon'     => 'fab' === $style ? 'fab fa-font-awesome-flag' : $style . ' fa-flag',
                'url'           => false,
                'enqueue'       => false,
                'prefix'        => 'fa-',
                'displayPrefix' => $style,
                'ver'           => '7.3.1',
                'icons'         => $free[ $style ],
                'native'        => false,
            );
        }
        return $tabs;
    }
    $kit = hashe_fa7_additive_kit();
    if ( ! $kit || empty( $kit['styles'] ) ) {
        // Configured but invalid/unreachable Kits are not secretly treated
        // as Free: Elementor Pro still owns its configured Kit integration.
        return $tabs;
    }
    $specs = hashe_fa7_additive_specs();
    $names = hashe_fa7_public_catalogs( $kit['version'] );
    if ( ! $names ) {
        return $tabs; // No invented Pro libraries if API/cache unavailable.
    }
    foreach ( $kit['styles'] as $style ) {
        if ( ! isset( $specs[ $style ] ) ) {
            continue;
        }
        $id = 'hashe-fa7-kit-' . $style;
        if ( isset( $tabs[ $id ] ) ) {
            continue;
        }
        // License-grouped API metadata describes names, not entitlement;
        // the published Kit CSS/JS remains authoritative for style access.
        $icons = array_values( array_unique( array_merge(
            $names['free'][ $style ] ?? array(),
            $names['pro'][ $style ] ?? array()
        ) ) );
        if ( ! $icons ) {
            continue;
        }
        $tabs[ $id ] = array(
            'name'          => $id,
            'label'         => 'Font Awesome Kit - ' . $specs[ $style ][0],
            'labelIcon'     => 'fab' === $style ? 'fab fa-font-awesome-flag' : $style . ' fa-flag',
            'url'           => false,
            'enqueue'       => false,
            'prefix'        => 'fa-',
            'displayPrefix' => $style,
            'ver'           => $kit['version'],
            'icons'         => $icons,
            'native'        => false,
        );
    }
    return $tabs;
}
add_filter( 'elementor/icons_manager/additional_tabs', 'hashe_fa7_register_kit_additional_tabs' );
/**
 * No direct Pro font-shard recovery: Font Awesome Kit style selection is the
 * entitlement boundary. Native Elementor definitions are preserved as-is;
 * font delivery is delegated to Elementor and the customer's licensed Kit.
 */

/**
 * Full-library Kits exceed Elementor 4.3.5's browser localStorage quota when
 * all 17 Kit catalogs are normalized. A version-scoped, Kit-only editor
 * adapter keeps ONLY our add-on catalog records in memory.
 *
 * The two-style Kit already works normally and is intentionally unchanged.
 * Elementor native library persistence, selectors, preview, and callbacks
 * are never overridden.
 */
function hashe_fa7_enqueue_kit_memory_cache() {
    $kit = hashe_fa7_additive_kit();
    if ( ! $kit || count( (array) $kit['styles'] ) <= 2 ) {
        return;
    }
    wp_enqueue_script(
        'hashe-fa7-kit-memory-cache',
        plugins_url( 'assets/js/editor-kit-memory.js', __FILE__ ),
        array( 'elementor-editor' ),
        '1.1.1',
        true
    );
}
add_action( 'elementor/editor/after_enqueue_scripts', 'hashe_fa7_enqueue_kit_memory_cache', 100 );
/**
 * Elementor Pro with a By-Style Kit suppresses Elementor Core's bundled native
 * Font Awesome stylesheets. Native labelIcon entries (untouched by Hashe)
 * use the standard 'fab' class, which needs the Brands font even if the
 * selected Kit contains only Sharp styles.
 *
 * Deliver Font Awesome's ORIGINAL unmodified Brands 7.3.1 stylesheet,
 * already bundled with the plugin, only when the valid CSS Kit omits Brands.
 * No native tab filters, custom label selectors, icon content overrides,
 * forced styling, or changes to Elementor files.
 */
function hashe_fa7_supply_standard_brands_asset_if_missing() {
    $kit = hashe_fa7_additive_kit();
    if (
        ! is_array( $kit ) ||
        'css' !== ( $kit['method'] ?? '' ) ||
        '7.3.1' !== ( $kit['version'] ?? '' ) ||
        in_array( 'fab', (array) ( $kit['styles'] ?? array() ), true )
    ) {
        return;
    }
    wp_enqueue_style(
        'hashe-fa7-standard-brands-asset',
        plugins_url( 'assets/css/brands.min.css', __FILE__ ),
        array(),
        '7.3.1'
    );
}
add_action( 'elementor/editor/after_enqueue_styles', 'hashe_fa7_supply_standard_brands_asset_if_missing', 120 );

/**
 * No-Kit: use licensed, locally bundled Font Awesome Free 7.3.1 styles/fonts.
 * Elementor Pro's existing integration owns font loading when any Kit ID is
 * present; we never inject Free CSS that could masquerade as Kit entitlement.
 */
function hashe_fa7_enqueue_no_kit_free_assets() {
    if ( '' !== trim( (string) get_option( 'elementor_font_awesome_pro_kit_id', '' ) ) ) {
        return;
    }
    $base = plugins_url( 'assets/css/', __FILE__ );
    wp_enqueue_style( 'hashe-fa7-free-core', $base . 'fontawesome.min.css', array(), '7.3.1' );
    foreach ( array( 'solid', 'regular', 'brands' ) as $family ) {
        wp_enqueue_style(
            'hashe-fa7-free-' . $family,
            $base . $family . '.min.css',
            array( 'hashe-fa7-free-core' ),
            '7.3.1'
        );
    }
}
add_action( 'elementor/editor/after_enqueue_styles', 'hashe_fa7_enqueue_no_kit_free_assets', 130 );
add_action( 'elementor/frontend/after_enqueue_styles', 'hashe_fa7_enqueue_no_kit_free_assets', 130 );


/**
 * QA14 — Font Awesome 7 CSS Kit native preview fallback.
 *
 * Elementor Pro's five native icon tabs deliberately stay as defined by
 * Elementor. If a customer selects only Sharp styles, however, the Kit does
 * not deliver font faces for Elementor's pre-existing Classic Pro tabs.
 *
 * Offer a mono Sharp visual fallback using *only* font faces already selected
 * and served by the customer's own Kit. No new Pro font request, glyph URL,
 * third-party file download, or alteration of native icon definitions.
 *
 * Native Classic/Duotone names render using the chosen Sharp weight, so this
 * is explicitly a visual compatibility fallback, not a claim that Classic
 * or Duotone assets were licensed/enabled in the Kit.
 */
function hashe_fa7_enqueue_native_sharp_fallback() {
    $kit = hashe_fa7_additive_kit();
    if ( ! is_array( $kit ) || 'css' !== ( $kit['method'] ?? '' ) ) {
        return;
    }
    $enabled = array_fill_keys( (array) ( $kit['styles'] ?? array() ), true );
    $preferences = array(
        'fas' => array( 'fass' => 900, 'fasr' => 400, 'fasl' => 300 ),
        'far' => array( 'fasr' => 400, 'fass' => 900, 'fasl' => 300 ),
        'fal' => array( 'fasl' => 300, 'fasr' => 400, 'fass' => 900 ),
        'fad' => array( 'fass' => 900, 'fasr' => 400, 'fasl' => 300 ),
    );
    $css = '';
    foreach ( $preferences as $native => $fallbacks ) {
        if ( isset( $enabled[ $native ] ) ) {
            continue; // Never change an actually enabled native family.
        }
        foreach ( $fallbacks as $sharp => $weight ) {
            if ( ! isset( $enabled[ $sharp ] ) ) {
                continue;
            }
            // This family is declared in the customer's official Sharp CSS Kit.
            $css .= '.' . $native .
                '{--fa-family:var(--fa-family-sharp,"Font Awesome 7 Sharp");' .
                '--fa-style:' . (int) $weight . ';}';
            if ( 'fad' === $native ) {
                // A mono Sharp font has no licensed second Duotone layer.
                $css .= '.fad::after{content:none!important;}';
            }
            break;
        }
    }
    if ( '' === $css ) {
        return;
    }
    wp_register_style( 'hashe-fa7-native-sharp-compat', false, array(), $kit['version'] );
    wp_enqueue_style( 'hashe-fa7-native-sharp-compat' );
    wp_add_inline_style( 'hashe-fa7-native-sharp-compat', $css );
}
add_action( 'elementor/editor/after_enqueue_styles', 'hashe_fa7_enqueue_native_sharp_fallback', 140 );
add_action( 'elementor/frontend/after_enqueue_styles', 'hashe_fa7_enqueue_native_sharp_fallback', 140 );

// Native Brands use Font Awesome Free regardless of CSS Kit selection; the
// original unmodified Free Brands asset is also safe on the frontend.
add_action( 'elementor/frontend/after_enqueue_styles', 'hashe_fa7_supply_standard_brands_asset_if_missing', 120 );
