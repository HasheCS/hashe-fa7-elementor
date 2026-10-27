<?php
defined( 'ABSPATH' ) || exit;
\Elementor\Plugin::$instance->editor->set_edit_mode( true );
$tabs = \Elementor\Icons_Manager::get_icon_manager_tabs();
foreach ( $tabs as $key => $tab ) {
    if ( 0 === strpos( $key, 'hashe-fa7' ) ) {
        continue;
    }
    $url = $tab['url'] ?? '';
    $enqueue = $tab['enqueue'] ?? array();
    echo 'NATIVE ' . json_encode( array(
        'name' => $key,
        'prefix' => $tab['prefix'] ?? null,
        'displayPrefix' => $tab['displayPrefix'] ?? null,
        'labelIcon' => $tab['labelIcon'] ?? null,
        'url' => is_string( $url ) ? $url : $url,
        'enqueue' => $enqueue,
        'fetchJson' => $tab['fetchJson'] ?? null,
        'ver' => $tab['ver'] ?? null
    ), JSON_UNESCAPED_SLASHES ) . PHP_EOL;
}
