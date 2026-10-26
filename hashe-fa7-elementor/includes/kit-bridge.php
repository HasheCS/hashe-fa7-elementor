<?php
/**
 * Kit-aware catalog discovery for the Amari beta.
 *
 * Uses Elementor's EXISTING Kit ID only. Font Awesome hosts licensed assets.
 * We retrieve public Kit metadata and catalog names, never Pro font files.
 */
defined( 'ABSPATH' ) || exit;

/** Bounded public HTTPS request. Never follow arbitrary URLs from Kit content. */
function hashe_fa7_public_get( $url, $max_bytes = 4000000 ) {
    $response = wp_remote_get( $url, array(
        'timeout' => 16,
        'redirection' => 0,
        'limit_response_size' => $max_bytes + 1,
        'headers' => array( 'Accept' => '*/*' ),
    ) );
    if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
        return false;
    }
    $body = wp_remote_retrieve_body( $response );
    return is_string( $body ) && strlen( $body ) <= $max_bytes ? $body : false;
}

/** Read the already configured Elementor Kit; no new user setting. */
function hashe_fa7_kit_snapshot( $kit_id ) {
    if ( ! preg_match( '/^[a-f0-9]{10}$/D', $kit_id ) ) {
        return false;
    }
    $key = 'hashe_fa7_kit_' . md5( $kit_id );
    $cached = get_transient( $key );
    if ( is_array( $cached ) ) {
        return $cached;
    }

    $script = hashe_fa7_public_get( 'https://kit.fontawesome.com/' . $kit_id . '.js', 2000000 );
    if ( false === $script || ! preg_match( '/window\.FontAwesomeKitConfig\s*=\s*(\{[^;\r\n]+\})\s*;/s', $script, $matches ) ) {
        return false;
    }
    $config = json_decode( $matches[1], true );
    if ( ! is_array( $config ) || $kit_id !== ( $config['token'] ?? '' ) ||
         ! preg_match( '/^7\.[0-9]+\.[0-9]+$/D', (string) ( $config['version'] ?? '' ) ) ) {
        return false;
    }
    $method = (string) ( $config['method'] ?? '' );
    $snapshot = array(
        'version' => $config['version'],
        'method' => $method,
        'family_styles' => array(),
        'error' => '',
    );

    // A By Style Web Fonts Kit exposes its actual selection through the hosted
    // subset stylesheet's @font-face declarations. Generic CSS class names
    // DO NOT represent its selected styles.
    $base = (string) ( $config['baseUrl'] ?? '' );
    $path = (string) ( $config['subsetPath'] ?? '' );
    if ( 'css' !== $method || ! in_array( $base, array( 'https://ka-p.fontawesome.com', 'https://ka-f.fontawesome.com' ), true ) ||
         ! preg_match( '#^assets/' . preg_quote( $kit_id, '#' ) . '/[0-9]+/kit\.css$#D', $path ) ) {
        $snapshot['error'] = 'Kit delivery mode is not yet supported by this beta.';
        set_transient( $key, $snapshot, 300 );
        return $snapshot;
    }
    $css = hashe_fa7_public_get( $base . '/' . $path . '?token=' . $kit_id );
    if ( false === $css ) {
        $snapshot['error'] = 'Could not retrieve selected styles from the Kit CDN.';
        set_transient( $key, $snapshot, 300 );
        return $snapshot;
    }

    preg_match_all( '/@font-face\s*\{([^}]+)\}/i', $css, $blocks );
    $enabled = array();
    $weights = array( '100' => 'thin', '300' => 'light', '400' => 'regular', '600' => 'semibold', '900' => 'solid' );
    foreach ( $blocks[1] as $block ) {
        if ( ! preg_match( '/font-family\s*:\s*["\x27]?Font Awesome [0-9]+ ([a-zA-Z -]+)/i', $block, $fm ) ||
             ! preg_match( '/font-weight\s*:\s*([0-9]+)/i', $block, $wm ) ) {
            continue;
        }
        $family = strtolower( trim( preg_replace( '/[[:space:]]+/', '-', $fm[1] ) ) );
        $style = 'brands' === $family ? 'brands' : ( $weights[$wm[1]] ?? '' );
        if ( '' !== $style ) {
            $enabled[$family . ':' . $style] = true;
        }
    }
    if ( ! $enabled ) {
        $snapshot['error'] = 'Kit stylesheet did not expose supported font families.';
        set_transient( $key, $snapshot, 300 );
        return $snapshot;
    }

    $version = $snapshot['version'];
    $response = hashe_fa7_public_get( 'https://api.fontawesome.com/releases/' . $version . '/family-styles', 700000 );
    $metadata = false === $response ? null : json_decode( $response, true );
    if ( ! is_array( $metadata ) || ! is_array( $metadata['familyStyles'] ?? null ) ) {
        $snapshot['error'] = 'Font Awesome family-style metadata unavailable.';
        set_transient( $key, $snapshot, 300 );
        return $snapshot;
    }
    foreach ( $metadata['familyStyles'] as $entry ) {
        if ( ! is_array( $entry ) || ! is_string( $entry['prefix'] ?? null ) ||
             ! preg_match( '/^[a-z0-9]+$/D', $entry['prefix'] ) ) {
            continue;
        }
        $family = (string) ( $entry['family'] ?? '' );
        $style = (string) ( $entry['style'] ?? '' );
        if ( 'classic' === $family && 'brands' === $style ) {
            $family = 'brands';
        }
        if ( ! isset( $enabled[$family . ':' . $style] ) ) {
            continue;
        }
        $snapshot['family_styles'][$entry['prefix']] = array(
            'family' => $family,
            'style' => $style,
            'shorthand' => (string) ( $entry['shorthand'] ?? '' ),
        );
    }
    if ( ! $snapshot['family_styles'] ) {
        $snapshot['error'] = 'No recognized family styles matched the Kit stylesheet.';
        set_transient( $key, $snapshot, 300 );
        return $snapshot;
    }
    // Cache a brief period so edits to a Kit appear in Elementor after refresh.
    set_transient( $key, $snapshot, 900 );
    return $snapshot;
}

function hashe_fa7_display_prefix( $spec ) {
    $family = $spec['family'];
    $style = $spec['style'];
    if ( 'brands' === $style ) {
        return 'fa-brands';
    }
    return ( 'classic' === $family ? '' : 'fa-' . $family . ' ' ) . 'fa-' . $style;
}

function hashe_fa7_register_kit_tabs( $tabs, $kit_id ) {
    $kit = hashe_fa7_kit_snapshot( $kit_id );
    if ( ! is_array( $kit ) || ! empty( $kit['error'] ) ) {
        // Fail closed: never present Pro icons that are not known to be in Kit.
        return $tabs;
    }
    foreach ( $kit['family_styles'] as $prefix => $spec ) {
        $name = 'hashe-fa7-' . $prefix;
        $tabs[$name] = array(
            'name' => $name,
            'label' => 'Font Awesome ' . $kit['version'] . ' Kit - ' . ucwords( str_replace( '-', ' ', $spec['family'] ) ) . ' ' . ucfirst( $spec['style'] ),
            'url' => false,
            'enqueue' => false,
            'prefix' => 'fa-',
            'displayPrefix' => hashe_fa7_display_prefix( $spec ),
            'labelIcon' => 'fas fa-icons',
            'ver' => $kit['version'],
            'fetchJson' => admin_url( 'admin-ajax.php?action=hashe_fa7_catalog&style=' . rawurlencode( $prefix ) ),
            'native' => true,
        );
    }
    return $tabs;
}

/** Get names for a style using the matching public release metadata. */
function hashe_fa7_names_for_style( $version, $prefix ) {
    $key = 'hashe_fa7_names_' . md5( $version . ':' . $prefix );
    $cached = get_transient( $key );
    if ( is_array( $cached ) ) {
        return $cached;
    }
    // 7.3.1 ships with verified, names-only catalogs; this avoids API delays
    // on the initial test Kit. Future releases and Pro Plus use public API.
    if ( HASHE_FA7_VERSION === $version ) {
        $local = plugin_dir_path( __FILE__ ) . '../data/' . $prefix . '.json';
        if ( is_readable( $local ) ) {
            $data = json_decode( (string) file_get_contents( $local ), true );
            if ( is_array( $data['icons'] ?? null ) ) {
                return $data['icons'];
            }
        }
    }

    $icons = array();
    for ( $page = 1; $page <= 100; $page++ ) {
        $url = 'https://api.fontawesome.com/releases/' . $version . '/icons-minimal?page=' . $page . '&page_size=500';
        $raw = hashe_fa7_public_get( $url, 1500000 );
        $batch = false === $raw ? null : json_decode( $raw, true );
        if ( ! is_array( $batch ) || ! is_array( $batch['icons'] ?? null ) ||
             ! is_numeric( $batch['totalPageCount'] ?? null ) ) {
            return false;
        }
        foreach ( $batch['icons'] as $icon ) {
            if ( ! is_array( $icon ) || ! is_string( $icon['id'] ?? null ) ||
                 ! preg_match( '/^[a-z0-9-]+$/D', $icon['id'] ) ) {
                continue;
            }
            foreach ( array( 'free', 'pro' ) as $license ) {
                foreach ( (array) ( $icon['familyStylesByLicense'][$license] ?? array() ) as $variant ) {
                    if ( is_array( $variant ) && $prefix === ( $variant['prefix'] ?? '' ) ) {
                        $icons[$icon['id']] = true;
                    }
                }
            }
        }
        if ( $page >= (int) $batch['totalPageCount'] ) {
            break;
        }
    }
    if ( empty( $icons ) || $page > 100 ) {
        return false;
    }
    $names = array_keys( $icons );
    sort( $names, SORT_STRING );
    set_transient( $key, $names, DAY_IN_SECONDS );
    return $names;
}

/** Called by Elementor's per-category fetchJson request. */
function hashe_fa7_ajax_catalog() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Permission denied', 403 );
    }
    $kit_id = trim( (string) get_option( 'elementor_font_awesome_pro_kit_id', '' ) );
    $style = isset( $_GET['style'] ) ? sanitize_key( wp_unslash( $_GET['style'] ) ) : '';
    $kit = hashe_fa7_kit_snapshot( $kit_id );
    if ( ! is_array( $kit ) || ! isset( $kit['family_styles'][$style] ) ) {
        wp_send_json_error( 'Style not included in this Kit', 404 );
    }
    $names = hashe_fa7_names_for_style( $kit['version'], $style );
    if ( false === $names ) {
        wp_send_json_error( 'Icon catalog temporarily unavailable', 503 );
    }
    wp_send_json( array( 'icons' => $names ) );
}
add_action( 'wp_ajax_hashe_fa7_catalog', 'hashe_fa7_ajax_catalog' );
