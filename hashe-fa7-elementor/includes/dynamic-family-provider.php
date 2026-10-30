<?php
/**
 * Dynamically enumerate official Font Awesome family-styles and subscription
 * collections for the configured Kit's exact release. Public metadata only:
 * no Pro font assets, credentials or icon SVGs are stored or redistributed.
 */
defined( 'ABSPATH' ) || exit;

function hashe_fa7_release_inventory( $version ) {
    static $memo = array();
    if ( isset( $memo[ $version ] ) ) {
        return $memo[ $version ];
    }
    if ( ! hashe_fa7_api_supported_version( $version ) ) {
        return false;
    }
    $key = 'hashe_fa7_release_inventory_v1_' . md5( $version );
    $cache = get_transient( $key );
    if ( is_array( $cache ) && ! empty( $cache['specs'] ) &&
        ! empty( $cache['collections']['pro-collection'] ) &&
        ! empty( $cache['collections']['pro-plus-collection'] ) ) {
        $memo[ $version ] = $cache;
        return $cache;
    }

    $endpoint = 'https://api.fontawesome.com/releases/' . rawurlencode( $version ) . '/';
    $request = array(
        'timeout' => 12,
        'redirection' => 0,
        'limit_response_size' => 2097152,
        'headers' => array( 'Accept' => 'application/json' ),
        'user-agent' => 'HasheFA7Elementor/1.1.2 (WordPress; public metadata)',
    );
    $documents = array();
    foreach ( array( 'family-styles', 'icon-packs' ) as $section ) {
        $response = wp_remote_get( $endpoint . $section, $request );
        if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }
        $raw = (string) wp_remote_retrieve_body( $response );
        if ( strlen( $raw ) < 40 || strlen( $raw ) >= 2097152 ) {
            return false;
        }
        $documents[ $section ] = json_decode( $raw, true );
        if ( ! is_array( $documents[ $section ] ) ) {
            return false;
        }
    }
    $rows = $documents['family-styles']['familyStyles'] ?? null;
    $packs = $documents['icon-packs']['iconPacks'] ?? null;
    $collections = $documents['icon-packs']['iconCollections'] ?? null;
    if ( ! is_array( $rows ) || ! is_array( $packs ) || ! is_array( $collections ) ||
        count( $rows ) < 3 || count( $rows ) > 100 ) {
        return false;
    }
    $specs = array();
    $pairs = array();
    foreach ( $rows as $row ) {
        if ( ! is_array( $row ) ) {
            return false;
        }
        $prefix = (string) ( $row['prefix'] ?? '' );
        $family = (string) ( $row['family'] ?? '' );
        $style = (string) ( $row['style'] ?? '' );
        $shorthand = (string) ( $row['shorthand'] ?? '' );
        if ( ! preg_match( '/^fa[a-z]{1,12}$/D', $prefix ) ||
            ! preg_match( '/^[a-z]+(?:-[a-z]+)*$/D', $family ) ||
            ! preg_match( '/^[a-z]+$/D', $style ) ||
            ! preg_match( '/^[a-z]+(?:-[a-z]+)*$/D', $shorthand ) ||
            isset( $specs[ $prefix ] ) ) {
            return false;
        }
        $label = 'brands' === $style ? 'Brands' :
            ucwords( str_replace( '-', ' ', $family ) ) . ' ' . ucfirst( $style );
        $specs[ $prefix ] = array(
            $label, $prefix, $shorthand,
            'family' => $family, 'style' => $style
        );
        $pairs[ $family . '/' . $style ] = $prefix;
    }
    $pack_prefixes = array();
    foreach ( $packs as $pack ) {
        if ( ! is_array( $pack ) || ! is_string( $pack['name'] ?? null ) ||
            ! is_array( $pack['familyStyles'] ?? null ) ) {
            return false;
        }
        $names = array();
        foreach ( $pack['familyStyles'] as $member ) {
            $pair = (string) ( $member['family'] ?? '' ) . '/' .
                (string) ( $member['style'] ?? '' );
            if ( isset( $pairs[ $pair ] ) ) {
                $names[ $pairs[ $pair ] ] = true;
            }
        }
        $pack_prefixes[ $pack['name'] ] = $names;
    }
    $available = array();
    foreach ( $collections as $collection ) {
        if ( ! is_array( $collection ) || ! is_string( $collection['name'] ?? null ) ||
            ! is_array( $collection['iconPacks'] ?? null ) ) {
            return false;
        }
        $names = array();
        foreach ( $collection['iconPacks'] as $pack_name ) {
            if ( ! isset( $pack_prefixes[ $pack_name ] ) ) {
                return false;
            }
            $names += $pack_prefixes[ $pack_name ];
        }
        $available[ $collection['name'] ] = array_keys( $names );
    }
    if ( empty( $available['pro-collection'] ) || empty( $available['pro-plus-collection'] ) ) {
        return false;
    }
    $inventory = array( 'specs' => $specs, 'collections' => $available );
    set_transient( $key, $inventory, 7 * DAY_IN_SECONDS );
    $memo[ $version ] = $inventory;
    return $inventory;
}

function hashe_fa7_additive_specs( $version = '' ) {
    $inventory = hashe_fa7_release_inventory( $version );
    return $inventory ? $inventory['specs'] : array();
}

function hashe_fa7_release_collection_prefixes( $version, $collection ) {
    $inventory = hashe_fa7_release_inventory( $version );
    return $inventory['collections'][ $collection ] ?? array();
}
