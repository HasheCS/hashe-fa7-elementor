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
    // The Kit bootstrap supplies data, never destinations. Reject any other
    // host, protocol, credentials, fragment or unexpected URL shape before
    // WordPress makes an outbound request. Do not follow redirects.
    if ( ! is_string( $url ) || strlen( $url ) > 500 ||
         ! is_int( $max_bytes ) || $max_bytes < 1024 || $max_bytes > 6000000 ) {
        return false;
    }
    $parsed = parse_url( $url );
    if ( ! is_array( $parsed ) || 'https' !== ( $parsed['scheme'] ?? '' ) ||
         isset( $parsed['user'] ) || isset( $parsed['pass'] ) ||
         isset( $parsed['port'] ) || isset( $parsed['fragment'] ) ) {
        return false;
    }
    $host = (string) ( $parsed['host'] ?? '' );
    $path = (string) ( $parsed['path'] ?? '' );
    $query = (string) ( $parsed['query'] ?? '' );
    $allowed = false;
    if ( 'kit.fontawesome.com' === $host ) {
        $allowed = (bool) preg_match( '#^/[a-f0-9]{10}\\.js$#D', $path ) && '' === $query;
    } elseif ( 'ka-p.fontawesome.com' === $host || 'ka-f.fontawesome.com' === $host ) {
        $allowed = (bool) preg_match( '#^/assets/([a-f0-9]{10})/[0-9]+/kit\\.css$#D', $path, $matches ) &&
            'token=' . $matches[1] === $query;
    } elseif ( 'api.fontawesome.com' === $host ) {
        $allowed = (bool) preg_match(
            '#^/releases(?:/7\\.[0-9]+\\.[0-9]+/(?:family-styles|icons-minimal))?$#D',
            $path
        ) && ( '' === $query ||
            (bool) preg_match( '/^page=[1-9][0-9]*&page_size=500$/D', $query ) );
    }
    if ( ! $allowed ) {
        return false;
    }
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

    // Intersect the public release's style catalog with actual icon selectors
    // emitted by this customer's Web Fonts Kit. Includes comma-grouped aliases.
    preg_match_all( '/((?:\.fa-[a-z0-9-]+\s*,?\s*)+)\{\s*--fa\s*:/', $css, $icon_rules );
    $kit_icons = array();
    foreach ( $icon_rules[1] as $selectors ) {
        preg_match_all( '/\.fa-([a-z0-9-]+)/', $selectors, $names );
        foreach ( $names[1] as $icon_name ) {
            $kit_icons[$icon_name] = true;
        }
    }
    if ( ! $kit_icons ) {
        $snapshot['error'] = 'Kit stylesheet did not expose an icon-name inventory.';
        set_transient( $key, $snapshot, 300 );
        return $snapshot;
    }
    $snapshot['icon_names'] = $kit_icons;

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
            'label' => 'Font Awesome ' . $kit['version'] . ' Kit - ' . ( 'brands' === $spec['style'] ? 'Brands' : ucwords( str_replace( '-', ' ', $spec['family'] ) ) . ' ' . ucfirst( $spec['style'] ) ),
            'url' => false,
            'enqueue' => false,
            'prefix' => 'fa-',
            'displayPrefix' => hashe_fa7_display_prefix( $spec ),
            'labelIcon' => 'eicon-font-awesome',
            'ver' => $kit['version'],
            'fetchJson' => admin_url( 'admin-ajax.php?action=hashe_fa7_catalog&style=' . rawurlencode( $prefix ) ),
            'native' => true,
        );
    }
    return $tabs;
}

/** Get names for a style using the matching public release metadata. */
/**
 * Build a full, release-specific names-only catalog from official public
 * Font Awesome metadata. Reuse it across all selected Kit styles.
 *
 * The old 7.3.1 bundled Pro JSON files listed the same general names for
 * numerous different styles. They are not authoritative style membership.
 * Do NOT use those JSON files in Kit mode.
 *
 * Publish the complete, validated catalog only after all pages succeed.
 * Failure leaves the previous cached complete catalog unchanged.
 */
function hashe_fa7_release_style_catalog( $version ) {
    if ( ! is_string( $version ) || ! preg_match( '/^7\\.[0-9]+\\.[0-9]+$/D', $version ) ) {
        return false;
    }

    $key = 'hashe_fa7_release_' . md5( $version );
    $cached = get_transient( $key );
    if ( is_array( $cached ) && ! empty( $cached ) ) {
        return $cached;
    }

    $family_response = hashe_fa7_public_get( 'https://api.fontawesome.com/releases/' . $version . '/family-styles', 700000 );
    $family_json = false === $family_response ? null : json_decode( $family_response, true );
    if ( ! is_array( $family_json ) || ! is_array( $family_json['familyStyles'] ?? null ) || ! $family_json['familyStyles'] ) {
        return false;
    }

    $families = array();
    foreach ( $family_json['familyStyles'] as $item ) {
        $prefix = is_array( $item ) ? ( $item['prefix'] ?? '' ) : '';
        if ( ! is_string( $prefix ) || ! preg_match( '/^[a-z0-9]+$/D', $prefix ) || isset( $families[$prefix] ) ) {
            return false;
        }
        $families[$prefix] = array();
    }

    $found = false;
    $total_pages = null;
    for ( $page = 1; $page <= 250; $page++ ) {
        $path = 'https://api.fontawesome.com/releases/' . $version . '/icons-minimal?page=' . $page . '&page_size=500';
        $response = hashe_fa7_public_get( $path, 1500000 );
        $body = false === $response ? null : json_decode( $response, true );
        if ( ! is_array( $body ) || ! is_array( $body['icons'] ?? null ) ||
             ! is_numeric( $body['totalPageCount'] ?? null ) || ! $body['icons'] ) {
            return false;
        }
        $reported_pages = (int) $body['totalPageCount'];
        if ( $reported_pages < 1 || $reported_pages > 250 ||
             ( null !== $total_pages && $total_pages !== $reported_pages ) ) {
            return false;
        }
        $total_pages = $reported_pages;
        foreach ( $body['icons'] as $icon ) {
            if ( ! is_array( $icon ) || ! is_string( $icon['id'] ?? null ) ||
                 ! preg_match( '/^[a-z0-9-]+$/D', $icon['id'] ) ||
                 ! is_array( $icon['familyStylesByLicense'] ?? null ) ) {
                return false;
            }
            foreach ( array( 'free', 'pro' ) as $license ) {
                if ( ! is_array( $icon['familyStylesByLicense'][$license] ?? null ) ) {
                    return false;
                }
                foreach ( $icon['familyStylesByLicense'][$license] as $variant ) {
                    $prefix = is_array( $variant ) ? ( $variant['prefix'] ?? '' ) : '';
                    if ( ! is_string( $prefix ) || ! array_key_exists( $prefix, $families ) ) {
                        return false;
                    }
                    $families[$prefix][$icon['id']] = true;
                }
            }
            $found = true;
        }
        if ( $page >= $total_pages ) {
            break;
        }
    }
    if ( ! $found || $page > 250 ) {
        return false;
    }

    $catalog = array();
    foreach ( $families as $prefix => $names ) {
        if ( ! $names ) {
            continue;
        }
        $values = array_keys( $names );
        sort( $values, SORT_STRING );
        $catalog[$prefix] = $values;
    }
    if ( ! $catalog ) {
        return false;
    }
    set_transient( $key, $catalog, DAY_IN_SECONDS );
    return $catalog;
}

/** Match Kit-approved style with exact public release membership. */
function hashe_fa7_names_for_style( $version, $prefix ) {
    if ( ! is_string( $prefix ) || ! preg_match( '/^[a-z0-9]+$/D', $prefix ) ) {
        return false;
    }
    $catalog = hashe_fa7_release_style_catalog( $version );
    return is_array( $catalog ) && isset( $catalog[$prefix] ) ? $catalog[$prefix] : false;
}

/**
 * Restrict candidate names to this Kit's hosted Web Fonts icon inventory.
 * Future SVG/JS inventory discovery must use its separately verified source.
 */
function hashe_fa7_filter_names_to_kit( $names, $kit ) {
    if ( ! is_array( $names ) || ! is_array( $kit ) ||
         ! is_array( $kit['icon_names'] ?? null ) ) {
        return false;
    }
    $filtered = array();
    foreach ( $names as $name ) {
        if ( isset( $kit['icon_names'][$name] ) ) {
            $filtered[] = $name;
        }
    }
    return $filtered;
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
    $names = hashe_fa7_filter_names_to_kit( $names, $kit );
    if ( false === $names ) {
        wp_send_json_error( 'Kit icon inventory unavailable', 503 );
    }
    wp_send_json( array( 'icons' => $names ) );
}
add_action( 'wp_ajax_hashe_fa7_catalog', 'hashe_fa7_ajax_catalog' );
