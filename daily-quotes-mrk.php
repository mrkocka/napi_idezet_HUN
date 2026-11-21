<?php
/**
 * Plugin Name: Daily Quotes MRK
 * Description: Display a daily quote in Elementor and manage quotes in the WordPress dashboard.
 * Version: 1.1.3
 * Plugin URI: https://github.com/mrkocka/napi_idezet_HUN
 * Author: mrkocka
 * Author URI: https://github.com/mrkocka
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: elementor
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Text Domain: daily-quotes-mrk
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'plugins_loaded', static function () {
    if ( ! function_exists( 'napi_idezet_register_quote_type' ) ) {
        require __DIR__ . '/quote-storage.php';
        require __DIR__ . '/admin.php';
    }

    if ( ! did_action( 'elementor/loaded' ) ) {
        add_action( 'admin_notices', static function () {
            if ( current_user_can( 'activate_plugins' ) ) {
                echo '<div class="notice notice-warning"><p>' . esc_html__( 'Activate Elementor to use the Daily Quotes MRK widget.', 'daily-quotes-mrk' ) . '</p></div>';
            }
        } );
    }
} );

add_action( 'elementor/widgets/register', static function ( $manager ) {
    if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
        return;
    }
    require_once __DIR__ . '/widget.php';
    $manager->register( new NIEL_Daily_Quote_Widget() );
} );

add_action( 'init', static function () {
    wp_register_style( 'niel-widget', plugin_dir_url( __FILE__ ) . 'widget.css', array(), '1.1.0' );
} );

function niel_daily_quote() {
    $date = wp_date( 'Y-m-d' );
    $selection = get_option( 'niel_daily_selection', array() );
    if ( is_array( $selection ) && ( $selection['date'] ?? '' ) === $date ) {
        $quote = get_post( $selection['id'] ?? 0 );
        if ( $quote && 'napi_idezet' === $quote->post_type && 'private' === $quote->post_status ) {
            return $quote;
        }
    }

    $ids = get_posts( array(
        'post_type'      => 'napi_idezet',
        'post_status'    => 'private',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'orderby'        => 'ID',
        'order'          => 'ASC',
    ) );
    if ( ! $ids ) {
        return null;
    }

    $index = hexdec( substr( hash( 'sha256', $date ), 0, 7 ) ) % count( $ids );
    $id = (int) $ids[ $index ];
    update_option( 'niel_daily_selection', array( 'date' => $date, 'id' => $id ), false );
    return get_post( $id );
}
