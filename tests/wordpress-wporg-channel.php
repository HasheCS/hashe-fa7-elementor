<?php
/** Disposable real WordPress.org-channel install and privacy independence QA. */
if (!defined('WP_CLI') || !WP_CLI) {
    fwrite(STDERR, "QA_WORDPRESS_CLI_ONLY\n");
    exit(2);
}
require_once ABSPATH . 'wp-admin/includes/plugin.php';
$basename='hashe-fa7-elementor/hashe-fa7-elementor.php';
$main=WP_PLUGIN_DIR . '/' . $basename;
if (!is_plugin_active($basename) || !is_file($main)) {
    WP_CLI::error('Directory candidate not installed and active');
}
$meta=get_plugin_data($main,false,false);
if (($meta['Version'] ?? '') !== '1.1.3') {
    WP_CLI::error('Directory candidate version mismatch');
}
if (!empty($meta['UpdateURI'])) {
    WP_CLI::error('WordPress.org edition must not advertise independent Update URI');
}
if (has_filter('update_plugins_plugins.hashe.com') !== false ||
    class_exists('Hashe\\FA7\\PluginTools\\V1\\UpdateClient',false)) {
    WP_CLI::error('Competing Hashe updater reached WordPress.org edition');
}
if (!class_exists('Hashe\\FA7\\PluginTools\\V1\\ConsentClient',false)) {
    WP_CLI::error('Mandatory optional consent module missing from directory edition');
}
if (get_option('hashe_fa7_optional_reporting_v1',false) !== false ||
    wp_next_scheduled('hashe_fa7_optional_reporting_tick')) {
    WP_CLI::error('Installation opted in or scheduled optional telemetry');
}
$seen=[];
$deny=static function($pre,$args,$url) use (&$seen) {
    if (strpos($url,'plugins.hashe.com/api/v1/telemetry/') !== false) {
        $seen[]=$url;
        return new WP_Error('qa_telemetry_forbidden','No optional reporting without consent');
    }
    return $pre;
};
add_filter('pre_http_request',$deny,10,3);
do_action('hashe_fa7_optional_reporting_tick');
$tabs=apply_filters('elementor/icons_manager/additional_tabs',[]);
remove_filter('pre_http_request',$deny,10);
if ($seen) {
    WP_CLI::error('Unconsented telemetry attempted');
}
foreach (['fas'=>1422,'far'=>169,'fab'=>572] as $style=>$count) {
    if (count($tabs['hashe-fa7-free-'.$style]['icons'] ?? []) !== $count) {
        WP_CLI::error('FA7 Free icon library incomplete: '.$style);
    }
}
wp_set_current_user(1);
require_once ABSPATH . 'wp-admin/includes/template.php';
$client=new \Hashe\FA7\PluginTools\V1\ConsentClient('hashe-fa7-elementor',$basename);
ob_start();
$client->settings_page();
$html=ob_get_clean();
if (strpos($html,'Skip / Continue without sharing') === false ||
    strpos($html,'Allow & Continue') !== false ||
    strpos($html,'Revoke permission') !== false) {
    WP_CLI::error('Directory edition privacy controls do not reflect disabled backend');
}
echo "WPORG_CHANNEL_MAIN_HEADER=PASS\n";
echo "WPORG_USES_DIRECTORY_NATIVE_UPDATES_ONLY=PASS\n";
echo "WPORG_CONSENT_MODULE_PRESENT_DEFAULT_OFF=PASS\n";
echo "WPORG_NO_OUTBOUND_TELEMETRY=PASS\n";
echo "WPORG_ICON_LIBRARIES_3=PASS\n";
echo "WPORG_DISPOSABLE_WORDPRESS_ACCEPTANCE=PASS\n";
