<?php
	$cart_quantity = rpress_get_cart_quantity();
	$display       = $cart_quantity > 0 ? '' : ' style="display:none;"';
	$color = rpress_get_option( 'checkout_color', 'red' );
?>
<li class="cart_item empty"><?php echo rpress_empty_cart_message(); ?></li>

<li class="cart_item rpress-cart-meta rpress_total" style="display:none;">
	<?php _e( 'Total (', 'restropress' ); ?>
	<span class="rpress-cart-quantity" <?php echo $display; ?>>
		<?php echo $cart_quantity; ?>
	</span> <?php _e( ' Items)', 'restropress' ); ?>
	<span class="cart-total <?php echo $color; ?>">
		<?php echo rpress_currency_filter( rpress_format_amount( rpress_get_cart_total() ) ); ?>
	</span>
</li>

<li class="delivery-items-options" style="display:none">
	<?php echo get_delivery_options( true ); ?>
</li>

<li class="cart_item rpress_checkout" style="display:none;"><a href="<?php echo rpress_get_checkout_uri(); ?>"><?php _e( 'Checkout', 'restropress' ); ?></a></li>
