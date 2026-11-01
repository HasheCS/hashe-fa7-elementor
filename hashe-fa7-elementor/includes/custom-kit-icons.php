<?php
/**
 * Prototype: discover user-uploaded custom icon names from a published Kit stylesheet.
 * No private API token, downloaded paid assets or Elementor modifications.
 * FAIL-CLOSED: not wired into the plugin until tested against a real custom Kit.
 */
defined('ABSPATH') || defined('HASHE_FA7_CUSTOM_FIXTURE') || exit;

function hashe_fa7_custom_icons_from_kit_css($css) {
    $empty = array('fak' => array(), 'fakd' => array());
    if (!is_string($css) || strlen($css) > 2097152 || strlen($css) < 20) {
        return $empty;
    }
    $css = preg_replace('~/\*.*?\*/~s', '', $css);
    if (!is_string($css)) {
        return $empty;
    }
    // A published stylesheet must actually provide a Kit custom font.
    $has_regular = stripos($css, 'Font Awesome Kit') !== false;
    $has_duotone = stripos($css, 'Font Awesome Kit Duotone') !== false;
    if (!$has_regular) {
        return $empty;
    }
    if (!preg_match_all('~([^{}]{1,12000})\{([^{}]{1,12000})\}~s', $css, $rules, PREG_SET_ORDER) ||
        count($rules) > 25000) {
        return $empty;
    }
    $found = array('fak' => array(), 'fakd' => array());
    foreach ($rules as $rule) {
        $declarations = $rule[2];
        if (!preg_match('~(?:^|;)\s*(?:content|--fa|--fa-content|--fa-primary)\s*:~i', $declarations)) {
            continue;
        }
        foreach (explode(',', $rule[1]) as $selector) {
            $duo = preg_match('~\.fa-kit-duotone(?=[\s.:#\[])~i', $selector);
            $mono = !$duo && preg_match('~\.fa-kit(?=[\s.:#\[])~i', $selector);
            if (!$duo && !$mono) {
                continue;
            }
            if ($duo && !$has_duotone) {
                continue;
            }
            $prefix = $duo ? 'fakd' : 'fak';
            if (preg_match_all('~\.fa-([a-z][a-z0-9-]{0,100})(?![a-z0-9-])~i', $selector, $matches)) {
                foreach ($matches[1] as $icon) {
                    $icon = strtolower($icon);
                    if ($icon !== 'kit' && $icon !== 'kit-duotone' &&
                        !in_array($icon, array('primary', 'secondary'), true)) {
                        $found[$prefix][$icon] = true;
                    }
                }
            }
        }
    }
    foreach ($found as $style => $names) {
        $empty[$style] = array_keys($names);
        sort($empty[$style], SORT_STRING);
    }
    return $empty;
}

/**
 * Full SVG+JS Kit uploads are published as individual Kit-owned SVGs, not a
 * companion kit.css (that endpoint is 404 for full SVG Kits).
 * This parser reads SVG structure ONLY, never stores, rehosts, or outputs SVG.
 * NULL means indeterminate; false means validated single-color; true Duotone.
 */
function hashe_fa7_custom_svg_is_duotone( $svg ) {
    if ( ! is_string( $svg ) || strlen( $svg ) < 60 ||
        strlen( $svg ) > 262144 || ! class_exists( 'DOMDocument' ) ||
        stripos( $svg, '<!DOCTYPE' ) !== false ||
        stripos( $svg, '<!ENTITY' ) !== false ) {
        return null;
    }
    $old = libxml_use_internal_errors( true );
    $doc = new DOMDocument();
    $ok = $doc->loadXML( $svg, LIBXML_NONET | LIBXML_COMPACT );
    libxml_clear_errors();
    libxml_use_internal_errors( $old );
    if ( ! $ok || ! $doc->documentElement ||
        $doc->documentElement->namespaceURI !== 'http://www.w3.org/2000/svg' ||
        strtolower( $doc->documentElement->localName ) !== 'svg' ) {
        return null;
    }
    $paths = $doc->getElementsByTagName( 'path' );
    if ( $paths->length < 1 ) {
        return null;
    }
    $partial = false;
    $opaque = false;
    foreach ( $paths as $path ) {
        $opacity = trim( (string) $path->getAttribute( 'opacity' ) );
        if ( '' === $opacity && preg_match(
            '/(?:^|;)\\s*opacity\\s*:\\s*([0-9.]+)/i',
            (string) $path->getAttribute( 'style' ), $m
        ) ) {
            $opacity = $m[1];
        }
        $class = (string) $path->getAttribute( 'class' );
        if ( preg_match( '/(?:^|\\s)fa-secondary(?:\\s|$)/', $class ) ||
            ( '' !== $opacity && is_numeric( $opacity ) &&
                (float) $opacity > 0 && (float) $opacity < 1 ) ) {
            $partial = true;
        } else {
            $opaque = true;
        }
    }
    return $paths->length >= 2 && $partial && $opaque;
}

/**
 * Verify each published upload against the SAME customer's Kit SVG endpoint.
 * Only public icon names and version numbers are taken from Kit bootstrap.
 * Paid SVG contents are never saved or exposed by our plugin.
 */
function hashe_fa7_custom_icons_from_full_js_kit( $kit_id, $uploads, $request ) {
    $result = array( 'fak' => array(), 'fakd' => array() );
    if ( ! preg_match( '/^[a-f0-9]{10}$/D', (string) $kit_id ) ||
        ! is_array( $uploads ) || ! is_array( $request ) ) {
        return $result;
    }
    foreach ( $uploads as $name => $data ) {
        if ( ! is_string( $name ) ||
            ! preg_match( '/^[a-z0-9][a-z0-9-]{0,100}$/D', $name ) ||
            ! is_array( $data ) || ! isset( $data['v'] ) ||
            ! is_numeric( $data['v'] ) ||
            (int) $data['v'] < 1 || (int) $data['v'] > 999999999 ) {
            continue;
        }
        $version = (int) $data['v'];
        $url = 'https://kit-uploads.fontawesome.com/' . $kit_id .
            '/icons/' . rawurlencode( $name ) . '-' . $version .
            '.svg?token=' . $kit_id;
        $args = $request;
        $args['timeout'] = 4;
        $args['redirection'] = 0;
        $args['limit_response_size'] = 262145;
        $args['headers']['Accept'] = 'image/svg+xml';
        $response = wp_remote_get( $url, $args );
        if ( is_wp_error( $response ) ||
            200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
            continue;
        }
        $is_duotone = hashe_fa7_custom_svg_is_duotone(
            (string) wp_remote_retrieve_body( $response )
        );
        if ( null === $is_duotone ) {
            continue; // Ambiguous/unavailable data must not invent icons.
        }
        $result[ $is_duotone ? 'fakd' : 'fak' ][] = $name;
    }
    foreach ( $result as &$names ) {
        $names = array_values( array_unique( $names ) );
        sort( $names, SORT_STRING );
    }
    unset( $names );
    return $result;
}
