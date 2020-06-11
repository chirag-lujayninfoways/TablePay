<?php
function quick_view_order_details() {
 $cat_id = $_GET['cat_id'];
  $order = rpress_get_payment( absint( $_GET['order_id'] ) );
  if ( $order ) {
    $order_response = order_preview_get_order_details( $order ,$cat_id);

    wp_send_json_success( $order_response );
  }
  rpress_die();
}

add_action( 'wp_ajax_rp_get_order_details', 'quick_view_order_details' );


function order_preview_get_order_details( $payment , $cat_id ) {

  if ( ! $payment ) {
    return array();
  }

  $gateway = $payment->gateway;

  if ( !empty( $gateway ) ) {
    $payment_via = rpress_get_gateway_admin_label( $gateway );
  }

  if ( !empty( $payment->customer_id ) ) {
    $customer  = new RPRESS_Customer( $payment->customer_id );
    $customer_name = $customer->name;
    $customer_email = $customer->email;
    $payment_meta = $payment->get_meta();
    $delivery_address_meta = get_post_meta( $payment->ID, '_rpress_delivery_address', true );
    $phone  = !empty( $payment_meta['phone'] ) ? $payment_meta['phone'] : (!empty( $delivery_address_meta['phone'] ) ? $delivery_address_meta['phone'] :  '');
    $flat   = !empty( $delivery_address_meta['flat'] ) ? $delivery_address_meta['flat'] : '';
    $city = !empty( $delivery_address_meta['city'] ) ? $delivery_address_meta['city'] : '';
    $postcode = !empty( $delivery_address_meta['postcode'] ) ? $delivery_address_meta['postcode'] : '';
    $customer_address = !empty( $delivery_address_meta['address'] ) ? $delivery_address_meta['address'] : '';

    $customer_details = array(
      'phone'      => $phone,
      'flat'       => $flat,
      'postcode'   => $postcode,
      'city'       => $city,
      'address'    => $customer_address
    );
  }

  $user_info      = $payment->user_info;
  $billing_address = isset( $user_info['address'] ) ? $user_info['address'] : '';
  $service_type = rpress_get_service_type( $payment->ID );
  $service_date = $payment->get_meta( '_rpress_delivery_date' );
  $service_date = !empty( $service_date ) ? rpress_local_date( $service_date ) : '';
  $service_time = $payment->get_meta( '_rpress_delivery_time' );

  return apply_filters(
    'rpress_admin_order_preview_get_order_details',
    array(
      'id'                        => $payment->ID,
      'service_type'              => rpress_service_label( $service_type ),
      'service_date'              => $service_date,
      'service_type_slug'         => $service_type,
      'status'                    => rpress_get_order_status( $payment->ID ),
      'payment_via'               => $payment_via,
      'service_time'              => $service_time,
      'customer_name'             => $customer_name,
      'customer_email'            => $customer_email,
      'customer_details'          => $customer_details,
      'customer_billing_details'  => $user_info,
      'item_html'                 => get_ordered_items( $payment, $cat_id ),
      'actions_html'              => get_order_preview_actions_html( $payment ),
      'formatted_billing_address' => $billing_address,
    ), $payment
  );
}

/**
  *
  * Get ordered item(s) in the tab;e
  *
  * @since 1.0
  * @return mixed
  */
  function get_ordered_items( $payment , $cat_id ) {
   
    $order_items = $payment->cart_details;
    $output = '';   
   
    if ( is_array( $order_items ) &&  !empty( $order_items )  ) {
      ob_start();
      ?>
      <div class="rp-order-preview-table-wrapper">
        <table cellspacing="0" class="rp-order-preview-table">
          <thead>
            <tr>
              <th class="rp-order-preview-table__column--product">
                <?php esc_html_e( 'FoodItem(s)', 'restropress' ); ?>
              </th>
              <th class="rp-order-preview-table__column--price-quantity">
                <?php esc_html_e( 'Price & Quantity', 'restropress' ); ?>
              </th>

              <?php if ( rpress_use_taxes() ) : ?>
                <th class="rp-order-preview-table__column--tax">
                  <?php esc_html_e( 'Tax', 'restropress' ); ?>
                </th>
              <?php endif; ?>

              <th class="rp-order-preview-table__column--price">
                <?php esc_html_e( 'Total', 'restropress' ); ?>
              </th>
            </tr>
          </thead>
          <tbody>
            <?php
           
            foreach( $order_items as $fooditems ) :
              /***
               *  custom filter to hide non category food item value
               */
              if($cat_id != 'all'):
                  global $wpdb;
                  $foodItemId = $fooditems['id']; 
                  $matchCatWithfoodID = $wpdb->get_results("SELECT * FROM `wp_term_relationships` WHERE  `object_id` = $foodItemId AND `term_taxonomy_id`=".$cat_id);
                
                  if(empty($matchCatWithfoodID)):
                    $style = 'display:none;';
                  else:
                    $style = '';
                  endif; 
              endif; 
              /***
               *  End here
               */

              $special_instruction = isset( $fooditems['instruction'] ) ? $fooditems['instruction'] : '';
              if ( isset( $fooditems['name'] ) ) :
                $item_tax   = isset( $fooditems['tax'] ) ? $fooditems['tax'] : 0;
                $price      = isset( $fooditems['price'] ) ? $fooditems['price'] : false;
              ?>
            <tr class="rp-order-preview-table" style="<?php echo $style; ?>">
              <td class="rp-order-preview-table__column--product">
                <?php echo $fooditems['name']; ?>
              </td>
              <td class="rp-order-preview-table__column--quantity">
                <?php
                echo rpress_currency_filter( rpress_format_amount( $fooditems['item_price'] ) ) . ' X ' . $fooditems['quantity']; ?>
              </td>

              <?php if ( rpress_use_taxes() ) : ?>
                <td class="rp-order-preview-table__column--tax">
                  <?php echo rpress_currency_filter(rpress_format_amount( $item_tax )); ?>
                </td>
              <?php endif; ?>


              <td class="rp-order-preview-table__column--price">
                <?php echo rpress_currency_filter(rpress_format_amount( $price )); ?>
              </td>
            </tr>

            <?php if ( !empty( $special_instruction ) ) : ?>
              <tr class="rp-order-preview-table special-instruction">
                <td colspan="3">
                  <?php printf( __( 'Special Instruction : %s', 'rp_quick_view'), $special_instruction ); ?>
                </td>
              </tr>
            <?php endif; ?>

            <?php
              if ( is_array( $fooditems['item_number']['options'] ) ) :
                foreach( $fooditems['item_number']['options'] as $addon_items ) :
                  if( is_array( $addon_items ) ) :
                    $addon_name = $addon_items['addon_item_name'];
                    $addon_price = $addon_items['price'];
                  ?>
                    <tr class="rp-order-addons">
                      <td> - <?php echo $addon_name; ?></td>
                      <td>
                        <?php
                         echo rpress_currency_filter( rpress_format_amount( $addon_price ), rpress_get_payment_currency_code( $payment->ID ) ) . ' X ' . $fooditems['quantity']; ?>
                      </td>

                      <?php if ( rpress_use_taxes() ) : ?>
                        <td>
                         <?php echo rpress_currency_filter( rpress_format_amount( '0' )); ?>
                        </td>
                      <?php endif; ?>
                      <td>
                        <?php echo rpress_currency_filter( rpress_format_amount( $addon_price )); ?>
                      </td>
                    </tr>
                    <?php
                  endif;
                endforeach;
              endif;
              endif;
            endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php
      $output = ob_get_contents();
      ob_clean();
    }
    return $output;
  }

  /**
  *
  * Create actions for buttons
  *
  * @since 1.0
  * @return mixed
  */
  function get_order_preview_actions_html( $payment ) {
    $actions        = array();
    $status_actions = array();

    $payment_status = rpress_get_order_status( $payment->ID );

    if ( $payment_status == 'pending' ) {
      $status_actions['processing'] = array(
        'name'        => __( 'Processing', 'restropress' ),
        'payment_id'  => $payment->ID,
        'action'      => 'processing',
        'url'         => wp_nonce_url( admin_url( 'admin-ajax.php?action=rpress_update_order_status&status=processing&current_status=' . $payment_status . '&payment_id=' . $payment->ID ), 'rpress-mark-order-status' ),
      );
    }


    if ( ( $payment_status == 'processing' || $payment_status == 'pending' ) ) {
      $status_actions['completed'] = array(
        'name'        => __( 'Completed', 'restropress' ),
        'payment_id'  => $payment->ID,
        'action'      => 'completed',
        'url'         => wp_nonce_url( admin_url( 'admin-ajax.php?action=rpress_update_order_status&status=completed&current_status=' . $payment_status. '&payment_id=' . $payment->ID ), 'rpress-mark-order-status' ),
      );
    }

    if ( $status_actions ) {
      $actions['status'] = array(
        'group'   => __( 'Change order status: ', 'restropress' ),
        'actions' => $status_actions,
      );
    }

    return rp_render_action_buttons( apply_filters( 'restropress_admin_order_preview_actions', $actions, $payment ) );
  }

  /**
  * Get HTML for some action buttons. Used in list tables.
  *
  * @since 1.0
  * @param array $actions Actions to output.
  * @return string
  */
  function rp_render_action_buttons( $actions ) {
    $actions_html = '';

    if ( !empty( $actions ) ) {
      foreach( $actions as $action ) {
        if ( isset( $action['group'] ) ) {
          $actions_html .= '<div class="rp-action-button-group"><label>' . $action['group'] . '</label> <span class="rp-action-button-group__items">' . rp_render_action_buttons( $action['actions'] ) . '</span></div>';
        }
        elseif( isset( $action['action'], $action['name'] ) ) {
          $actions_html .= sprintf( '<a class="button rp-action-button rp-action-button-%1$s %1$s" data-update-status="%1$s"  aria-label="%2$s" data-payment="%3$s" data-action="rpress_update_order_status" title="%2$s" href="%4$s">%2$s</a>', esc_attr( $action['action'] ), esc_html( $action['name'] ), $action[ 'payment_id' ], $action['url'] );
        }
      }
    }

    return $actions_html;
  }

  /**
  *
  * quick view modal template
  *
  * @since 1.0
  * @return html
  */
  function order_preview_template() {
    ?>
    <script type="text/template" id="tmpl-rp-modal-view-order">
      <div class="rp-backbone-modal rp-order-preview">
        <div class="rp-backbone-modal-content">
          <section class="rp-backbone-modal-main" role="main">
            <header class="rp-backbone-modal-header">
              <mark class="order-status status-{{ data.status }}"><span>{{ data.status }}</span></mark>

              <?php /* translators: %s: order ID */ ?>
              <h1><?php echo esc_html( sprintf( __( 'Order #%s', 'restropress' ), '{{ data.id }}' ) ); ?></h1>

              <# if ( data.service_type_slug !== '' ) { #>
                <mark class="service-type badge-{{ data.service_type_slug }}"><span>{{ data.service_type }}</span></mark>
              <# } #>

              <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                <span class="screen-reader-text"><?php esc_html_e( 'Close modal panel', 'restropress' ); ?></span>
              </button>
            </header>

            <?php esc_html_e( get_post_status( '{{data.id}}' ) ); ?>

            <article>
              <?php do_action( 'rpress_admin_order_preview_start' ); ?>
              <div class="rp-order-preview-wrapper">
                <div class="rp-order-preview">
                  <# if ( data.customer_details.address ) { #>
                    <div class="rp-order-preview-address">
                      <h2><?php esc_html_e( sprintf( __( '%s address', 'restropress' ), '{{ data.service_type }}' ) ); ?></h2>
                        {{ data.customer_details.address }}<br />
                        {{ data.customer_details.flat }}<br />
                        {{ data.customer_details.city }} {{ data.customer_details.postcode }}
                    </div>
                  <# } #>
                  <div class="rp-order-preview-customer-details">
                    <h2><?php esc_html_e( 'Customer details', 'restropress' ); ?></h2>
                    <# if ( data.customer_name ) { #>
                      <strong><?php esc_html_e( 'Customer name', 'restropress' ); ?></strong>
                    : <span>{{ data.customer_name }}</span>
                      <br/>
                    <# } #>

                    <# if ( data.customer_email ) { #>
                      <strong><?php esc_html_e( 'Email', 'restropress' ); ?></strong>
                      : <a href="mailto:{{ data.customer_email }}">{{ data.customer_email }}</a>
                      <br/>
                    <# } #>

                    <# if ( data.customer_details.phone ) { #>
                      <strong><?php esc_html_e( 'Phone', 'restropress' ); ?></strong>
                      : <a href="tel:{{{ data.customer_details.phone }}}">{{{ data.customer_details.phone }}}</a>
                      <br/>
                    <# } #>
                  </div>

                  <div class="rp-clear-fix"></div>

                  <div class="order-service-meta">

                    <# if ( data.payment_via ) { #>
                      <span>
                        <strong><?php esc_html_e( 'Payment via', 'restropress' ); ?></strong> :
                        {{{ data.payment_via }}}
                      </span>
                    <# } #>

                    <# if ( data.service_date ) { #>
                      <span>
                      <strong><?php esc_html_e( 'Service date', 'restropress' ); ?></strong> :
                      {{{ data.service_date }}}
                      </span>
                    <# } #>

                    <# if ( data.service_time ) { #>
                      <span>
                        <strong><?php esc_html_e( 'Service time', 'restropress' ); ?></strong> :
                      {{{ data.service_time }}}
                    <# } #>
                    </span>
                  </div>

                </div>
                <?php do_action( 'rpress_admin_order_preview_before_fooditems' ); ?>
                <br/>
                <# if ( data.item_html ) { #>
                  <div class="fooditems">
                    {{{ data.item_html }}}
                  </div>
                <# } #>

              </div>

              <?php do_action( 'rpress_admin_order_preview_end' ); ?>
            </article>

            <footer>
              <div class="inner">

                <div class="rpress-action-button-group">
                 {{{ data.actions_html }}}
                </div>

                <a class="button button-primary button-large" aria-label="<?php esc_attr_e( 'Edit this order', 'restropress' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=rpress-payment-history&view=view-order-details' ) ); ?>&id={{ data.id }}"><?php esc_html_e( 'Edit', 'restropress' ); ?></a>

              </div>
            </footer>

          </section>
        </div>
      </div>
      <div class="rp-backbone-modal-backdrop modal-close"></div>
    </script>
    <?php
  }

  add_action( 'admin_footer', 'order_preview_template' );

  function quick_view_update_order_status() {
    if ( isset( $_GET['status'] )
      && isset( $_GET['payment_id'] ) ) {

      $payment_id = $_GET['payment_id'];
      $new_status = $_GET['status'];

      $status = sanitize_text_field( wp_unslash( $new_status ) );
      $statuses = rpress_get_order_statuses();

      $statuses = array_keys( $statuses );

      if ( in_array( $status, $statuses ) ) {
        rpress_update_order_status( $payment_id, $status );
      }

      wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=rpress-payment-history' ) );
      exit;

    }
  }

  add_action( 'wp_ajax_rpress_update_order_status', 'quick_view_update_order_status' );
