<?php
/**
 * Font Awesome public icon-name metadata provider.
 *
 * Only identifiers and family/style availability are cached on this WordPress
 * installation. No Pro font, SVG, CSS, glyph data, or private account token
 * is downloaded, included, or redistributed by the plugin.
 */
defined( 'ABSPATH' ) || exit;

/** Strictly version-scoped public API: unknown major versions fail closed. */
function hashe_fa7_api_supported_version( $version ) {
    return is_string( $version ) &&
        (bool) preg_match( '/^7\.[0-9]{1,3}\.[0-9]{1,3}$/D', $version );
}

function hashe_fa7_public_metadata_get( $version, $page ) {
    if ( ! hashe_fa7_api_supported_version( $version ) || $page < 1 || $page > 30 ) {
        return false;
    }
    $url = 'https://api.fontawesome.com/releases/' . $version .
        '/icons-minimal?page=' . (int) $page . '&page_size=500';
    $response = wp_remote_get( $url, array(
        'timeout' => 8,
        'redirection' => 0,
        'limit_response_size' => 1048576,
        'headers' => array( 'Accept' => 'application/json' ),
        'user-agent' => 'HasheFA7Elementor/1.1.1 (WordPress; metadata-only)',
    ) );
    if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
        return false;
    }
    $body = (string) wp_remote_retrieve_body( $response );
    if ( strlen( $body ) < 20 || strlen( $body ) >= 1048576 ) {
        return false;
    }
    $json = json_decode( $body, true );
    return is_array( $json ) ? $json : false;
}

/**
 * Returns per-style icon names, keyed by free/pro then FA prefix.
 * Fresh cache: seven days. Last successful cache: 30 days for API outages.
 * Cache is shared across Kits using the same official release (never Kit IDs).
 */
function hashe_fa7_public_catalogs( $version ) {
    if ( ! hashe_fa7_api_supported_version( $version ) ) {
        return false;
    }
    $prefix = 'hashe_fa7_metadata_v4_' . md5( $version );
    $fresh = get_transient( $prefix );
    if ( hashe_fa7_valid_catalog_cache( $fresh, $version ) ) {
        return $fresh['catalogs'];
    }
    $last = get_option( $prefix . '_last', false );
    $stale = hashe_fa7_valid_catalog_cache( $last, $version ) &&
        ( time() - (int) $last['updated'] ) <= 30 * DAY_IN_SECONDS
        ? $last['catalogs'] : false;
    if ( get_transient( $prefix . '_retry' ) ) {
        return $stale;
    }

    // An option-insert is atomic for concurrent WordPress editor requests.
    $lock_key = $prefix . '_lock';
    $lock = (int) get_option( $lock_key, 0 );
    if ( $lock && time() - $lock > 120 ) {
        delete_option( $lock_key );
    }
    if ( ! add_option( $lock_key, time(), '', 'no' ) ) {
        return $stale;
    }

    $catalogs = false;
    try {
        $catalogs = hashe_fa7_download_public_catalogs( $version );
        if ( $catalogs ) {
            $data = array( 'version' => $version, 'updated' => time(), 'catalogs' => $catalogs );
            set_transient( $prefix, $data, 7 * DAY_IN_SECONDS );
            update_option( $prefix . '_last', $data, false );
        } else {
            set_transient( $prefix . '_retry', 1, 5 * MINUTE_IN_SECONDS );
        }
    } finally {
        delete_option( $lock_key );
    }
    return $catalogs ? $catalogs : $stale;
}

function hashe_fa7_valid_catalog_cache( $data, $version ) {
    if ( ! is_array( $data ) || $version !== ( $data['version'] ?? '' ) ||
        ! is_int( $data['updated'] ?? null ) ||
        ! is_array( $data['catalogs'] ?? null ) ) {
        return false;
    }
    foreach ( array( 'free', 'pro' ) as $license ) {
        if ( ! is_array( $data['catalogs'][ $license ] ?? null ) ) {
            return false;
        }
    }
    return true;
}

function hashe_fa7_download_public_catalogs( $version ) {
    $styles = array_fill_keys( array_keys( hashe_fa7_additive_specs( $version ) ), true );
    $result = array( 'free' => array(), 'pro' => array() );
    foreach ( array_keys( $styles ) as $style ) {
        $result['free'][ $style ] = array();
        $result['pro'][ $style ] = array();
    }
    $expected_pages = 0;
    $expected_icons = 0;
    $seen = array();
    $page_count = 0;
    for ( $page = 1; $page <= 30; $page++ ) {
        $json = hashe_fa7_public_metadata_get( $version, $page );
        if ( ! $json || ! is_array( $json['icons'] ?? null ) ||
            $page !== (int) ( $json['page'] ?? 0 ) ||
            500 !== (int) ( $json['pageSize'] ?? 0 ) ) {
            return false;
        }
        $total = (int) ( $json['totalIconCount'] ?? 0 );
        $pages = (int) ( $json['totalPageCount'] ?? 0 );
        if ( $pages < 1 || $pages > 30 || $total < 1 || $total > 15000 ||
            $pages !== (int) ceil( $total / 500 ) ) {
            return false;
        }
        if ( 1 === $page ) {
            $expected_pages = $pages;
            $expected_icons = $total;
        } elseif ( $pages !== $expected_pages || $total !== $expected_icons ) {
            return false; // Inconsistent pagination: do not cache a partial catalog.
        }
        foreach ( $json['icons'] as $icon ) {
            if ( ! is_array( $icon ) ||
                ! preg_match( '/^[a-z0-9][a-z0-9-]{0,100}$/D', (string) ( $icon['id'] ?? '' ) ) ||
                ! is_array( $icon['familyStylesByLicense'] ?? null ) ) {
                return false;
            }
            $name = $icon['id'];
            if ( isset( $seen[ $name ] ) ) {
                return false;
            }
            $seen[ $name ] = true;
            foreach ( array( 'free', 'pro' ) as $license ) {
                $variants = $icon['familyStylesByLicense'][ $license ] ?? null;
                if ( ! is_array( $variants ) ) {
                    return false;
                }
                foreach ( $variants as $variant ) {
                    $style = is_array( $variant ) ? (string) ( $variant['prefix'] ?? '' ) : '';
                    if ( isset( $styles[ $style ] ) ) {
                        $result[ $license ][ $style ][ $name ] = true;
                    }
                }
            }
        }
        $page_count++;
        if ( $page === $expected_pages ) {
            break;
        }
    }
    if ( $page_count !== $expected_pages || count( $seen ) !== $expected_icons ) {
        return false;
    }
    foreach ( $result as $license => $by_style ) {
        foreach ( $by_style as $style => $names ) {
            // PHP casts purely numeric string array keys to integers.
            // Elementor 4.3.5 calls iconName.trim(), so a single numeric
            // ID ("0" to "9") otherwise crashes the entire icon picker.
            $result[ $license ][ $style ] = array_map( 'strval', array_keys( $names ) );
            sort( $result[ $license ][ $style ], SORT_STRING );
        }
    }
    return $result;
}

/**
 * Separate publicly licensed Free catalogs. These remain usable without an
 * API connection or Font Awesome account and match the bundled Free font.
 */
function hashe_fa7_free_catalogs() {
    static $cached = null;
    if ( null !== $cached ) {
        return $cached;
    }
    $cached = array();
    foreach ( array( 'fas' => 'solid', 'far' => 'regular', 'fab' => 'brands' ) as $style => $family ) {
        $path = dirname( __DIR__ ) . '/data/free-' . $family . '.json';
        if ( ! is_readable( $path ) ) {
            continue;
        }
        $data = json_decode( (string) file_get_contents( $path ), true );
        if ( is_array( $data ) && ! empty( $data['icons'] ) &&
            is_array( $data['icons'] ) ) {
            $cached[ $style ] = array_values( array_filter( $data['icons'], 'is_string' ) );
        }
    }
    return $cached;
}
