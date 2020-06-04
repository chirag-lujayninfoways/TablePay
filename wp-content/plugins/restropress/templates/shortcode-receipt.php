<?php
/**
 * This template is used to display the purchase summary with [rpress_receipt]
 */
global $rpress_receipt_args;

$payment   = get_post( $rpress_receipt_args['id'] );

if( empty( $payment ) ) : ?>

	<div class="rpress_errors rpress-alert rpress-alert-error">
		<?php _e( 'The specified receipt ID appears to be invalid', 'restropress' ); ?>
	</div>

<?php
return;
endif;

$meta      			= rpress_get_payment_meta( $payment->ID );
$service_time 	= rpress_get_payment_meta( $payment->ID, '_rpress_delivery_time' );
$service_date   = rpress_get_payment_meta( $payment->ID, '_rpress_delivery_date', true );
$cart      			= rpress_get_payment_meta_cart_details( $payment->ID, true );
$user      			= rpress_get_payment_meta_user_info( $payment->ID );
$email     			= rpress_get_payment_user_email( $payment->ID );
$payment_status = rpress_get_payment_status( $payment, true );
$order_status 	= rpress_get_order_status( $payment->ID );
$order_note	  	= rpress_get_payment_meta( $payment->ID, '_rpress_order_note', true );
$service_type 	= rpress_get_payment_meta( $payment->ID, '_rpress_delivery_type' );
$service_label 	= rpress_service_label( $service_type );
$phone 					= !empty( $meta['phone'] ) ? $meta['phone'] : ( !empty( $user['phone'] ) ? $user['phone'] : '' );
$firstname 			= isset( $user['first_name'] ) ? $user['first_name'] : '';
$lastname 			= isset( $user['last_name'] ) ? $user['last_name'] : '';
$address_info		= get_post_meta( $payment->ID, '_rpress_delivery_address', true );
$address = !empty( $address_info['address'] ) ? $address_info['address'] . ', ' : '';
$address	.= !empty( $address_info['flat'] ) ? $address_info['flat'] . ', ' : '';
$address	.= !empty( $address_info['city'] ) ? $address_info['city'] . ', ' : '';
$address	.= !empty( $address_info['postcode'] ) ? $address_info['postcode']  : '';

?>
<?php do_action( 'rpress_before_payment_receipt', $payment, $rpress_receipt_args ); ?>

<div class="container-fluid rp-header">
	<div class="rp-row rp-customer-receipt">
		<div class="rp-col-sm-12">
			<p class="rp-center rp-tick"></p>
	    <h3 class="rp-center rp-order-head-text"><?php _e( "We've received your order", 'restropress' ); ?></h3>
	    <h4 class="rp-center rp-order-no-text"><?php _e( 'Order: ', 'restropress');  ?> <span>#<?php echo rpress_get_payment_number( $payment->ID ); ?></span></h4>
	    <p class="rp-center rp-order-message-text">
	    	<?php _e( 'A copy of your receipt has been sent to', 'restropress' ); ?>
	    	 <span><?php echo $email; ?></span></p>
		</div>
	</div>
</div>

<div id="rp-order-details">
	<div class="rp-row">
		<div class="rp-col-lg-6 rp-col-md-6 rp-col-sm-12">
    	<div class="rp-order-section">
	      <h3><?php echo sprintf( __( '%s details' ), ucfirst( $service_label ) );?>
	      </h3>
	      <div class="rp-detils-content">
	        <p><?php _e( 'Name', 'restropress' ); ?> : <span><?php echo $firstname . ' ' . $lastname; ?></span></p>
	        <p><?php _e( 'Phone Number', 'restropress' ); ?> : <span><?php echo $phone; ?></span></p>
	        <p>
	        	<?php echo sprintf( __( '%s Date' ), ucfirst( $service_label ) );
          	?> :
          	<span><?php echo rpress_local_date( $service_date ); ?></span>
        	</p>
	        <p><?php echo sprintf( __( '%s Time' ), ucfirst( $service_label ) );
          	?> : <span><?php echo $service_time; ?></span></p>
	      </div>
      </div>
			<?php if( $service_type == 'delivery' ) : ?>
		    <div class="rp-order-section rp-delivery-address">
		      <h3><?php _e( 'Address', 'restropress' ); ?></h3>
		      <div class="rp-detils-content">
						<?php echo $address; ?>
		      </div>
		    </div>
			<?php endif; ?>

    </div>

    <div class="rp-col-lg-6 rp-col-md-6 rp-col-sm-12">

	    <div class="rp-order-section">
	    	<?php if ( filter_var( $rpress_receipt_args['date'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
	      <h3><?php _e( 'Order details', 'restropress' ); ?></h3>
	      <div class="rp-detils-content">
	        <p><?php _e( 'Order Status', 'restropress' ); ?> : <span><?php echo ucfirst( $order_status ); ?></span></p>
	        <p><?php _e( 'Order Date', 'restropress' ); ?> : <span><?php echo date_i18n( get_option( 'date_format' ), strtotime( $meta['date'] ) ); ?></span></p>
	      </div>
	      <?php endif; ?>
	    </div>

	    <div class="rp-order-section">
	      <h3><?php _e( 'Payment Details', 'restropress' ); ?></h3>
	      <div class="rp-detils-content">
	        <p><?php _e( 'Payment Method', 'restropress' ); ?> : <span><?php echo rpress_get_gateway_checkout_label( rpress_get_payment_gateway( $payment->ID ) ); ?></span></p>
	        <p><?php _e( 'Payment Status', 'restropress' ); ?> : <span><?php echo $payment_status; ?></span></p>
	      </div>
	    </div>
	  </div>

    <div class="clear"></div>
	</div>
</div>

<div class="rp-row">
	<div class="rp-col-sm-12">
  	<div class="rp-order-summary-main">
    	<h3><?php _e( 'Order summary', 'restropress' ); ?></h3>
      <table id="rp-order-summary" width="100%">
        <thead>
	        <tr>
	          <th class="rp-tb-left">
	          	<?php _e( 'Item', 'restropress' ); ?>
	          </th>
	          <th class="rp-center">
	          	<?php _e( 'Quantity', 'restropress' ); ?>
	          </th>
	          <th class="rp-tb-right">
	          	<?php _e( 'Amount', 'restropress' ); ?>
	          </th>
	        </tr>
        </thead>

        <tbody>

        <?php

        if ( $cart ) :

					foreach ( $cart as $key => $item ) :

 				 		if( ! apply_filters( 'rpress_user_can_view_receipt_item', true, $item ) ) :
 				 			continue;
						endif;

						if ( empty( $item['in_bundle'] ) ) : ?>

							<tr>
								<td>
								<?php
									$price_id       = rpress_get_cart_item_price_id( $item );
									$special_instruction = isset( $item['instruction'] ) ? $item['instruction'] : '';
								?>

								<div class="rpress_purchase_receipt_product_name">
								<?php echo esc_html( $item['name'] ); ?>
								<?php
									if( is_array($item['item_number']['options']) &&
									!empty($item['item_number']['options']) ) {

										foreach( $item['item_number']['options'] as $k => $v ) {

											if( !empty($v['addon_item_name']) ) {
											?>
												<br/>&nbsp;&nbsp;
												<small class="rpress-receipt-addon-item"><?php echo $v['addon_item_name']; ?> (<?php echo rpress_currency_filter(rpress_format_amount($v['price'])); ?>)
												</small>

											<?php
											}
										}
									}
								?>
								<br/><br/>
							<?php if ( !empty( $special_instruction ) ) : ?>
								<span> <?php _e( 'Special Instructions', 'restropress'); ?> : </span>
								<small><?php echo $special_instruction; ?></small>
							<?php endif; ?>
						</div>
					</td>
					<td class="rp-center"><?php echo $item['quantity']; ?></td>
					<td class="rp-tb-right">
						<?php if( empty( $item['in_bundle'] ) ) : // Only show price when product is not part of a bundle ?>
							<?php echo rpress_currency_filter( rpress_format_amount( $item[ 'price' ] ) ); ?>
						<?php endif; ?>
					</td>
				</tr>
					<?php endif; ?>
				<?php endforeach; ?>
				<?php endif; ?>
        </tbody>

        <tfoot>
          <tr class="rpress_cart_footer_row rpress_cart_subtotal_row">
            <td colspan="2" class="rp-tb-right"><?php _e( 'Subtotal', 'restropress' ); ?>:</td>
            <td class="rp-tb-right rp-amount-right">
            	<?php echo rpress_payment_subtotal( $payment->ID ); ?>
            </td>
          </tr>

          <?php
          	if ( ( $fees = rpress_get_payment_fees( $payment->ID, 'fee' ) ) ) :

      				foreach( $fees as $fee ) : ?>
      					<tr class="rpress_cart_footer_row rpress_cart_delivery_row">
            			<td colspan="2" class="rp-tb-right"><?php echo esc_html( $fee['label'] ); ?>:</td>
            			<td class="rp-tb-right rp-amount-right"><?php echo rpress_currency_filter( rpress_format_amount( $fee['amount'] ) ); ?></td>
          			</tr>
      				<?php endforeach; ?>

    				<?php endif; ?>


          	<?php if( rpress_use_taxes() ) : ?>
		          <tr class="rpress_cart_footer_row rpress_cart_tax_row">
		            <td colspan="2" class="rp-tb-right">
		            	<?php _e( 'Tax', 'restropress' ); ?>:
		            </td>
		            <td class="rp-tb-right rp-amount-right">
		            	<?php echo rpress_payment_tax( $payment->ID ); ?>
		            </td>
		          </tr>
        		<?php endif; ?>

        		<?php if ( filter_var( $rpress_receipt_args['discount'], FILTER_VALIDATE_BOOLEAN ) && isset( $user['discount'] ) && $user['discount'] != 'none' ) : ?>
			        	<tr class="rpress_cart_footer_row rpress_cart_discount_row">
			            <td colspan="2"class="rp-tb-right">
			            	<?php _e( 'Discount(s)', 'restropress' ); ?>:
			            </td>
			            <td class="rp-tb-right rp-amount-right">
			            	<?php echo $user['discount']; ?>
			            </td>
			          </tr>
      			<?php endif; ?>


          	<?php if ( filter_var( $rpress_receipt_args['price'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
		          <tr class="rpress_cart_footer_row rpress_cart_total_row">
		            <td colspan="2" class="rp-tb-right rp-bold">
		            	<?php _e( 'Total', 'restropress' ); ?>:</td>
		            <td class="rp-tb-right rp-amount-right rp-bold">
		            	<?php echo rpress_payment_amount( $payment->ID ); ?>
		            </td>
		          </tr>
        		<?php endif; ?>

        </tfoot>

      </table>
			<?php do_action( 'rpress_payment_receipt_after_table', $payment, $rpress_receipt_args ); ?>
      </div>
    </div>
  </div>

<?php do_action( 'rpress_after_payment_receipt', $payment, $rpress_receipt_args ); ?>
