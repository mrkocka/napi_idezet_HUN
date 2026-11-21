<?php
/**
 * Run with wp eval-file on a disposable WordPress installation only.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    exit;
}

function niel_test_check( $condition, $message ) {
    if ( ! $condition ) {
        throw new RuntimeException( $message );
    }
}

class NIEL_Test_Redirect extends RuntimeException {}
class NIEL_Test_Denied extends RuntimeException {}

wp_set_current_user( 1 );
niel_install_capabilities();
update_option( 'timezone_string', 'Europe/Budapest' );
niel_test_check( current_user_can( 'napi_idezet_archive' ), 'Administrator capability migration failed.' );

add_filter( 'wp_redirect', static function ( $location ) {
    throw new NIEL_Test_Redirect( $location );
}, 1 );
add_filter( 'wp_die_handler', static function () {
    return static function () { throw new NIEL_Test_Denied(); };
} );

function niel_test_request( $action, $id, $text = '', $author = '' ) {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = array(
        'quote_id' => (string) $id,
        'idezet_szoveg' => wp_slash( $text ),
        'idezet_szerzo' => wp_slash( $author ),
        'napi_idezet_nonce' => wp_create_nonce( $action . '_' . $id ),
    );
    $_REQUEST = $_POST;
}

$before = get_posts( array( 'post_type' => 'napi_idezet', 'post_status' => 'private', 'fields' => 'ids', 'posts_per_page' => -1 ) );
niel_test_request( 'napi_idezet_save', 0, 'A "quote" with an apostrophe: it\'s safe.', 'Teszt szerző' );
try {
    niel_save_quote();
} catch ( NIEL_Test_Redirect $e ) {
    niel_test_check( false !== strpos( $e->getMessage(), 'quote_status=saved' ), 'Creation failed.' );
}
$after = get_posts( array( 'post_type' => 'napi_idezet', 'post_status' => 'private', 'fields' => 'ids', 'posts_per_page' => -1 ) );
$new_ids = array_values( array_diff( $after, $before ) );
niel_test_check( 1 === count( $new_ids ), 'Expected exactly one new quote.' );
$id = $new_ids[0];
niel_test_check( 'A "quote" with an apostrophe: it\'s safe.' === get_post( $id )->post_content, 'Text was not preserved.' );
niel_test_check( 'Teszt szerző' === get_post_meta( $id, '_napi_idezet_attribution', true ), 'Attribution missing.' );

update_option( 'niel_daily_selection', array( 'date' => wp_date( 'Y-m-d' ), 'id' => $id ) );
niel_test_check( $id === niel_daily_quote()->ID, 'Daily selection is not stable.' );

$widget = \Elementor\Plugin::instance()->widgets_manager->get_widget_types( 'niel_daily_quote' );
niel_test_check( $widget instanceof \Elementor\Widget_Base, 'Elementor widget was not registered.' );
foreach ( array( 'direction', 'gap', 'text_color', 'author_color', 'text_typography_typography', 'author_typography_typography' ) as $control ) {
    niel_test_check( null !== $widget->get_controls( $control ), 'Missing control: ' . $control );
}
niel_test_check( ! empty( $widget->get_controls( 'direction' )['is_responsive'] ), 'Layout is not responsive.' );
$render = new ReflectionMethod( $widget, 'render' );
ob_start();
$render->invoke( $widget );
$html = ob_get_clean();
niel_test_check( false !== strpos( $html, 'Teszt szerző' ) && false === strpos( $html, '<button' ), 'Widget HTML is invalid.' );

niel_test_request( 'napi_idezet_save', $id, '<script>alert(1)</script> Edited', 'Updated author' );
try {
    niel_save_quote();
} catch ( NIEL_Test_Redirect $e ) {
    niel_test_check( false !== strpos( $e->getMessage(), 'quote_status=updated' ), 'Editing failed.' );
}
niel_test_check( false === strpos( get_post( $id )->post_content, '<script>' ), 'HTML was not sanitized.' );

$subscriber = wp_insert_user( array( 'user_login' => 'niel-test-' . wp_generate_password( 8, false ), 'user_pass' => wp_generate_password(), 'role' => 'subscriber' ) );
niel_test_check( ! is_wp_error( $subscriber ), 'Could not create test subscriber.' );
wp_set_current_user( $subscriber );
niel_test_request( 'napi_idezet_archive', $id );
$denied = false;
try { niel_archive_quote(); } catch ( NIEL_Test_Denied $e ) { $denied = true; }
niel_test_check( $denied && 'private' === get_post( $id )->post_status, 'Subscriber modified a quote.' );

wp_set_current_user( 1 );
niel_test_request( 'napi_idezet_archive', $id );
$_REQUEST['napi_idezet_nonce'] = 'invalid';
$denied = false;
try { niel_archive_quote(); } catch ( NIEL_Test_Denied $e ) { $denied = true; }
niel_test_check( $denied && 'private' === get_post( $id )->post_status, 'Invalid nonce was accepted.' );

$original = get_post( $id );
$attribution = get_post_meta( $id, '_napi_idezet_attribution', true );
niel_test_request( 'napi_idezet_archive', $id );
try {
    niel_archive_quote();
} catch ( NIEL_Test_Redirect $e ) {
    niel_test_check( false !== strpos( $e->getMessage(), 'quote_status=archived' ), 'Archival failed.' );
}
$archived = get_post( $id );
niel_test_check( 'napi_idezet_archived' === $archived->post_status, 'Wrong archive status.' );
niel_test_check( $archived->post_content === $original->post_content && $archived->post_author === $original->post_author, 'Archival changed original data.' );
niel_test_check( $attribution === get_post_meta( $id, '_napi_idezet_attribution', true ), 'Archival lost attribution.' );
$daily = niel_daily_quote();
niel_test_check( ! $daily || $daily->ID !== $id, 'Archived quote still selected.' );

WP_CLI::success( 'Creation, editing, daily selection, Elementor controls/rendering, denied requests and archival preservation passed.' );
