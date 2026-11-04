<?php
/** HP-I-004: real two-plugin WordPress coexistence, draft channels only. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    fwrite( STDERR, "RUN_ONLY_IN_DISPOSABLE_WP_CLI\n" );
    exit( 2 );
}
$phase = getenv( 'FA7_COEXISTENCE_PHASE' ) ?: 'both';
$fa7 = 'hashe-fa7-elementor/hashe-fa7-elementor.php';
$acf = 'hashe-elementor-acf-editor/hashe-elementor-acf-editor.php';
if ( 'both' === $phase ) {
    if ( ! is_plugin_active( $fa7 ) || ! is_plugin_active( $acf ) ) {
        WP_CLI::error( 'Both independent Hashe plugins must be active' );
    }
    foreach ( array(
        'Hashe\\FA7\\PluginTools\\V1\\UpdateClient',
        'Hashe\\FA7\\PluginTools\\V1\\ConsentClient',
        'Hashe\\PluginTools\\V1\\UpdateClient',
        'Hashe\\PluginTools\\V1\\ConsentClient',
    ) as $fqcn ) {
        if ( ! class_exists( $fqcn ) ) {
            WP_CLI::error( 'Missing or overwritten embedded client: ' . $fqcn );
        }
    }
    $update_hook = $GLOBALS['wp_filter']['update_plugins_plugins.hashe.com'] ?? null;
    $registered = 0;
    if ( $update_hook instanceof WP_Hook ) {
        foreach ( $update_hook->callbacks as $callbacks ) {
            $registered += count( $callbacks );
        }
    }
    if ( $registered < 2 ) {
        WP_CLI::error( 'Two independent native update filters not registered' );
    }
    if ( false !== get_option( 'hashe_fa7_optional_reporting_v1', false ) ||
        false !== get_option( 'hashe_acf_optional_reporting_v1', false ) ) {
        WP_CLI::error( 'Consent was silently enabled or shared on activation' );
    }
    $tabs = apply_filters( 'elementor/icons_manager/additional_tabs', array() );
    foreach ( array( 'fas' => 1422, 'far' => 169, 'fab' => 572 ) as $style => $expected ) {
        $key = 'hashe-fa7-free-' . $style;
        if ( count( $tabs[$key]['icons'] ?? array() ) !== $expected ) {
            WP_CLI::error( 'Coexistence altered Free catalog ' . $key );
        }
    }
    $telemetry = array();
    add_filter( 'pre_http_request', static function ( $pre, $args, $url ) use ( &$telemetry ) {
        if ( strpos( $url, 'plugins.hashe.com/api/v1/telemetry/' ) !== false ) {
            $telemetry[] = $url;
            return new WP_Error( 'qa_blocked', 'No telemetry in QA' );
        }
        return $pre;
    }, 10, 3 );
    do_action( 'hashe_fa7_optional_reporting_tick' );
    do_action( 'hashe_acf_optional_reporting_tick' );
    if ( $telemetry ) {
        WP_CLI::error( 'Unconsented telemetry attempted during coexistence' );
    }
    update_option( 'hashe_acf_optional_reporting_v1',
        array( 'status' => 'declined', 'qa_marker' => 'acf-only-sentinel' ), false );
    if ( false !== get_option( 'hashe_fa7_optional_reporting_v1', false ) ) {
        WP_CLI::error( 'ACF opt-out crossed into FA7 state' );
    }
    echo "COEXISTENCE_ACTIVE_BOTH_NAMESPACES_UPDATERS=PASS\n";
    echo "COEXISTENCE_FA7_FREE_CATALOGS=PASS\n";
    echo "COEXISTENCE_TELEMETRY_DEFAULT_OFF=PASS\n";
    echo "COEXISTENCE_PER_PLUGIN_OPTIONS=PASS\n";
} elseif ( 'after-fa7-uninstall' === $phase ) {
    if ( is_plugin_active( $fa7 ) || ! is_plugin_active( $acf ) ||
        get_option( 'hashe_acf_optional_reporting_v1', array() )['qa_marker'] !== 'acf-only-sentinel' ) {
        WP_CLI::error( 'FA7 uninstall touched ACF plugin/state' );
    }
    echo "FA7_UNINSTALL_ACF_STATE_UNCHANGED=PASS\n";
} elseif ( 'after-acf-uninstall' === $phase ) {
    if ( is_plugin_active( $acf ) || ! is_plugin_active( $fa7 ) ) {
        WP_CLI::error( 'ACF uninstall touched FA7 installation' );
    }
    if ( false !== get_option( 'hashe_acf_optional_reporting_v1', false ) ) {
        WP_CLI::error( 'ACF uninstall left ACF optional consent state' );
    }
    echo "ACF_UNINSTALL_FA7_UNCHANGED=PASS\n";
} else {
    WP_CLI::error( 'Unknown coexistence QA phase' );
}