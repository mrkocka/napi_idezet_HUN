<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only notice selection; no state change.
$niel_status = isset( $_GET['quote_status'] ) && is_string( $_GET['quote_status'] )
    ? sanitize_key( wp_unslash( $_GET['quote_status'] ) )
    : '';
// phpcs:enable WordPress.Security.NonceVerification.Recommended
$niel_notices = array(
    'saved' => array( 'success', __( 'Quote saved.', 'napi-idezet-elementor' ) ),
    'updated' => array( 'success', __( 'Quote updated.', 'napi-idezet-elementor' ) ),
    'archived' => array( 'success', __( 'Quote archived.', 'napi-idezet-elementor' ) ),
    'archive_error' => array( 'error', __( 'Could not archive the quote. Please try again.', 'napi-idezet-elementor' ) ),
    'empty' => array( 'error', __( 'Please enter the quote text.', 'napi-idezet-elementor' ) ),
    'error' => array( 'error', __( 'Could not save the quote. Please try again.', 'napi-idezet-elementor' ) ),
);
$niel_page_url = admin_url( 'admin.php?page=napi-idezet' );
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only selection; access checked per record and writes require a nonce.
$niel_edit_id = filter_var( isset( $_GET['edit_quote'] ) && is_string( $_GET['edit_quote'] ) ? sanitize_text_field( wp_unslash( $_GET['edit_quote'] ) ) : '0', FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 0 ) ) );
// phpcs:enable WordPress.Security.NonceVerification.Recommended
$niel_editing = niel_get_editable_quote( $niel_edit_id );
$niel_form_id = $niel_editing ? $niel_editing->ID : 0;
$niel_query = array(
    'post_type'      => 'napi_idezet',
    'post_status'    => 'private',
    'posts_per_page' => -1,
    'orderby'        => 'ID',
    'order'          => 'DESC',
);
if ( ! current_user_can( 'read_private_napi_idezet_quotes' ) ) {
    $niel_query['author'] = get_current_user_id();
}
$niel_quotes = get_posts( $niel_query );
?>
<div class="wrap idezet-admin">
  <h1><?php esc_html_e( 'Quotes', 'napi-idezet-elementor' ); ?></h1>

  <?php if ( isset( $niel_notices[ $niel_status ] ) ) : ?>
    <div class="notice notice-<?php echo esc_attr( $niel_notices[ $niel_status ][0] ); ?>">
      <p><?php echo esc_html( $niel_notices[ $niel_status ][1] ); ?></p>
    </div>
  <?php endif; ?>

  <?php if ( $niel_editing || current_user_can( 'napi_idezet_create' ) ) : ?>
    <section class="idezet-admin__panel" aria-labelledby="uj-idezet">
      <h2 id="uj-idezet"><?php echo esc_html( $niel_editing ? __( 'Edit quote', 'napi-idezet-elementor' ) : __( 'New quote', 'napi-idezet-elementor' ) ); ?></h2>
      <form class="idezet-admin__mezok" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="napi_idezet_save">
        <input type="hidden" name="quote_id" value="<?php echo esc_attr( $niel_form_id ); ?>">
        <?php wp_nonce_field( 'napi_idezet_save_' . $niel_form_id, 'napi_idezet_nonce', false ); ?>

        <label for="idezet-szoveg"><?php esc_html_e( 'Quote text', 'napi-idezet-elementor' ); ?></label>
        <textarea id="idezet-szoveg" name="idezet_szoveg" rows="4" required><?php echo esc_textarea( $niel_editing ? $niel_editing->post_content : '' ); ?></textarea>

        <label for="idezet-szerzo"><?php esc_html_e( 'Author', 'napi-idezet-elementor' ); ?> <span><?php esc_html_e( '(optional)', 'napi-idezet-elementor' ); ?></span></label>
        <input id="idezet-szerzo" name="idezet_szerzo" type="text" value="<?php echo esc_attr( $niel_editing ? get_post_meta( $niel_form_id, '_napi_idezet_attribution', true ) : '' ); ?>">

        <button type="submit" class="button button-primary"><?php echo esc_html( $niel_editing ? __( 'Save changes', 'napi-idezet-elementor' ) : __( 'Save quote', 'napi-idezet-elementor' ) ); ?></button>
        <?php if ( $niel_editing ) : ?>
          <a href="<?php echo esc_url( $niel_page_url ); ?>"><?php esc_html_e( 'Cancel', 'napi-idezet-elementor' ); ?></a>
        <?php endif; ?>
      </form>
    </section>
  <?php endif; ?>

  <section class="idezet-admin__panel" aria-labelledby="mentett-idezetek">
    <h2 id="mentett-idezetek"><?php esc_html_e( 'Saved quotes', 'napi-idezet-elementor' ); ?></h2>
    <?php if ( ! $niel_quotes ) : ?>
      <p><?php esc_html_e( 'No quotes have been saved yet.', 'napi-idezet-elementor' ); ?></p>
    <?php else : ?>
      <div class="idezet-admin__tablazat-keret" role="region" aria-labelledby="mentett-idezetek" tabindex="0">
        <table class="widefat striped idezet-admin__tablazat" aria-labelledby="mentett-idezetek">
          <thead>
            <tr>
              <th scope="col"><?php esc_html_e( 'Quote', 'napi-idezet-elementor' ); ?></th>
              <th scope="col"><?php esc_html_e( 'Author', 'napi-idezet-elementor' ); ?></th>
              <th scope="col"><?php esc_html_e( 'Actions', 'napi-idezet-elementor' ); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( $niel_quotes as $niel_quote ) : ?>
              <?php $niel_author = get_post_meta( $niel_quote->ID, '_napi_idezet_attribution', true ); ?>
              <tr>
                <td><p class="idezet-admin__idezet"><?php echo esc_html( $niel_quote->post_content ); ?></p></td>
                <td><?php echo '' !== $niel_author ? esc_html( $niel_author ) : '–'; ?></td>
                <td>
                  <div class="idezet-admin__muveletek">
                    <?php if ( current_user_can( 'edit_post', $niel_quote->ID ) ) : ?>
                      <a class="button button-small" href="<?php echo esc_url( add_query_arg( 'edit_quote', $niel_quote->ID, $niel_page_url ) . '#uj-idezet' ); ?>"><?php esc_html_e( 'Edit', 'napi-idezet-elementor' ); ?></a>
                      <?php if ( current_user_can( 'napi_idezet_archive' ) ) : ?>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                          <input type="hidden" name="action" value="napi_idezet_archive">
                          <input type="hidden" name="quote_id" value="<?php echo esc_attr( $niel_quote->ID ); ?>">
                          <?php wp_nonce_field( 'napi_idezet_archive_' . $niel_quote->ID, 'napi_idezet_nonce', false ); ?>
                          <button type="submit" class="button button-small"><?php esc_html_e( 'Archive', 'napi-idezet-elementor' ); ?></button>
                        </form>
                      <?php endif; ?>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</div>
