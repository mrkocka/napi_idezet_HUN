<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function niel_register_quote_type() {
    register_post_status( 'napi_idezet_archived', array(
        'label'    => __( 'Archived quote', 'napi-idezet-elementor' ),
        'public'   => false,
        'internal' => true,
    ) );

    register_post_type( 'napi_idezet', array(
        'label'           => __( 'Quotes', 'napi-idezet-elementor' ),
        'public'          => false,
        'show_ui'         => false,
        'rewrite'         => false,
        'query_var'       => false,
        'capability_type' => array( 'napi_idezet_quote', 'napi_idezet_quotes' ),
        'map_meta_cap'    => true,
        'capabilities'    => array( 'create_posts' => 'napi_idezet_create' ),
    ) );
}
add_action( 'init', 'niel_register_quote_type' );

function niel_get_active_quote_ids() {
    $ids = get_transient( 'napi_idezet_active_quote_ids' );
    if ( false === $ids ) {
        $ids = get_posts( array(
            'post_type'      => 'napi_idezet',
            'post_status'    => 'private',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'orderby'        => 'none',
        ) );
        set_transient( 'napi_idezet_active_quote_ids', $ids, 5 * MINUTE_IN_SECONDS );
    }
    return $ids;
}

function niel_invalidate_quote_ids( $post_id, $post ) {
    if ( 'napi_idezet' === $post->post_type ) {
        delete_transient( 'napi_idezet_active_quote_ids' );
    }
}
add_action( 'save_post_napi_idezet', 'niel_invalidate_quote_ids', 10, 2 );
add_action( 'deleted_post', 'niel_invalidate_quote_ids', 10, 2 );

function niel_install_capabilities() {
    if ( '3' === get_option( 'napi_idezet_capability_version' ) ) {
        return;
    }

    $administrator = get_role( 'administrator' );
    if ( ! $administrator ) {
        return;
    }

    $administrator->add_cap( 'napi_idezet_access' );
    $administrator->add_cap( 'napi_idezet_create' );
    $administrator->add_cap( 'edit_napi_idezet_quotes' );
    $administrator->add_cap( 'edit_others_napi_idezet_quotes' );
    $administrator->add_cap( 'edit_private_napi_idezet_quotes' );
    $administrator->add_cap( 'read_private_napi_idezet_quotes' );
    $administrator->add_cap( 'napi_idezet_archive' );
    update_option( 'napi_idezet_capability_version', '3' );
    wp_get_current_user()->get_role_caps();
}
add_action( 'admin_init', 'niel_install_capabilities' );

function niel_get_editable_quote( $id ) {
    $post = $id > 0 ? get_post( $id ) : null;
    if ( ! $post || 'napi_idezet' !== $post->post_type || 'private' !== $post->post_status
        || ! current_user_can( 'edit_post', $id ) ) {
        return null;
    }
    return $post;
}

function niel_save_quote() {
    if ( ! current_user_can( 'napi_idezet_access' ) ) {
        wp_die( esc_html__( 'You do not have permission to save quotes.', 'napi-idezet-elementor' ), '', array( 'response' => 403 ) );
    }

    if ( 'POST' !== sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
        wp_die( esc_html__( 'Saving requires a POST request.', 'napi-idezet-elementor' ), '', array( 'response' => 405 ) );
    }

    $id = filter_var( isset( $_POST['quote_id'] ) && is_string( $_POST['quote_id'] ) ? sanitize_text_field( wp_unslash( $_POST['quote_id'] ) ) : '0', FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 0 ) ) );
    if ( false === $id ) {
        wp_die( esc_html__( 'Invalid quote ID.', 'napi-idezet-elementor' ), '', array( 'response' => 400 ) );
    }

    if ( $id ? ! niel_get_editable_quote( $id ) : ! current_user_can( 'napi_idezet_create' ) ) {
        wp_die( esc_html__( 'You do not have permission to save quotes.', 'napi-idezet-elementor' ), '', array( 'response' => 403 ) );
    }

    check_admin_referer( 'napi_idezet_save_' . $id, 'napi_idezet_nonce' );

    $quote = isset( $_POST['idezet_szoveg'] ) && is_string( $_POST['idezet_szoveg'] )
        ? sanitize_textarea_field( wp_unslash( $_POST['idezet_szoveg'] ) )
        : '';
    $author = isset( $_POST['idezet_szerzo'] ) && is_string( $_POST['idezet_szerzo'] )
        ? sanitize_text_field( wp_unslash( $_POST['idezet_szerzo'] ) )
        : '';

    $status = 'empty';
    if ( '' !== $quote ) {
        $fields = array(
            'post_content' => $quote,
            'meta_input'   => array( '_napi_idezet_attribution' => $author ),
        );
        if ( $id ) {
            $fields['ID'] = $id;
            $result = wp_update_post( wp_slash( $fields ), true );
        } else {
            $result = wp_insert_post( wp_slash( $fields + array(
                'post_type'    => 'napi_idezet',
                'post_status'  => 'private',
                'post_author'  => get_current_user_id(),
            ) ), true );
        }
        $status = $result && ! is_wp_error( $result ) ? ( $id ? 'updated' : 'saved' ) : 'error';
    }

    $url = admin_url( 'admin.php?page=napi-idezet&quote_status=' . $status );
    if ( $id && 'updated' !== $status ) {
        $url = add_query_arg( 'edit_quote', $id, $url );
    }
    wp_safe_redirect( $url, 303 );
    exit;
}
add_action( 'admin_post_napi_idezet_save', 'niel_save_quote' );

function niel_archive_quote() {
    if ( ! current_user_can( 'napi_idezet_access' ) || ! current_user_can( 'napi_idezet_archive' ) ) {
        wp_die( esc_html__( 'You do not have permission to archive quotes.', 'napi-idezet-elementor' ), '', array( 'response' => 403 ) );
    }

    if ( 'POST' !== sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
        wp_die( esc_html__( 'Archiving requires a POST request.', 'napi-idezet-elementor' ), '', array( 'response' => 405 ) );
    }

    $id = filter_var( isset( $_POST['quote_id'] ) && is_string( $_POST['quote_id'] ) ? sanitize_text_field( wp_unslash( $_POST['quote_id'] ) ) : '', FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
    if ( false === $id ) {
        wp_die( esc_html__( 'Invalid quote ID.', 'napi-idezet-elementor' ), '', array( 'response' => 400 ) );
    }

    check_admin_referer( 'napi_idezet_archive_' . $id, 'napi_idezet_nonce' );
    if ( ! niel_get_editable_quote( $id ) ) {
        wp_die( esc_html__( 'This quote cannot be archived.', 'napi-idezet-elementor' ), '', array( 'response' => 403 ) );
    }

    $result = wp_update_post( array(
        'ID'          => $id,
        'post_status' => 'napi_idezet_archived',
    ), true );

    $status = $result && ! is_wp_error( $result ) ? 'archived' : 'archive_error';
    wp_safe_redirect( admin_url( 'admin.php?page=napi-idezet&quote_status=' . $status ), 303 );
    exit;
}
add_action( 'admin_post_napi_idezet_archive', 'niel_archive_quote' );
