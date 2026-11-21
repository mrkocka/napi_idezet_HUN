<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
function niel_register_admin_page() {
    $page_hook = add_menu_page(
        __( 'Idézetek', 'napi-idezet-elementor' ),
        __( 'Idézetek', 'napi-idezet-elementor' ),
        'napi_idezet_access',
        'napi-idezet',
        'niel_render_admin_page',
        'dashicons-format-quote'
    );

    add_action( 'admin_enqueue_scripts', static function ( $current_page ) use ( $page_hook ) {
        if ( $current_page !== $page_hook ) {
            return;
        }

        wp_enqueue_style(
            'napi-idezet-admin',
            plugin_dir_url( __FILE__ ) . 'admin.css',
            array(),
            '1.1.0'
        );
    } );
}
add_action( 'admin_menu', 'niel_register_admin_page' );

function niel_render_admin_page() {
    if ( ! current_user_can( 'napi_idezet_access' ) ) {
        return;
    }

    require __DIR__ . '/admin-view.php';
}
