<?php
/**
 * Template to render the wishlit table.
 *
 * @author WolfThemes
 * @category Core
 * @package WolfWooCommerceWishlist/Admin
 * @version 1.1.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<h2 class="wishlist-page-title"><?php apply_filters( 'www_wishlist_title', esc_html_e( 'My Wishlist', 'wolf-woocommerce-wishlist' ) ); ?></h2>

<?php
$product_ids = www_get_wishlist_product_ids();

if ( array() != $product_ids ) : ?>

<table class="wolf-woocommerce-wishlist-table cart">
	<thead>
		<tr>
			<th class="product-remove">&nbsp;</th>
			<th class="product-thumbnail">&nbsp;</th>
			<th class="product-name"><?php esc_html_e( 'Product', 'wolf-woocommerce-wishlist' ); ?></th>
			<th class="product-price"><?php esc_html_e( 'Price', 'wolf-woocommerce-wishlist' ); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php do_action( 'www_before_wishlist_contents' ); ?>

		<?php

		foreach ( $product_ids as $product_id ) {

			$product = wc_get_product( $product_id );

			if ( $product && $product->exists() ) {
				$permalink = get_permalink( $product_id );
				?>
				<tr class="wolf-woocommerce-wishlist-product">

					<td class="product-remove">
						<a href="#"
						class="remove www-remove"
						title="<?php esc_html_e( 'Remove this item', 'wolf-woocommerce-wishlist' ); ?>"
						data-product-id="<?php echo absint( $product_id ); ?>">
							&times;
						</a>
					</td>

					<td class="product-thumbnail">
						<a href="<?php echo esc_url( $permalink ); ?>">
							<?php echo $product->get_image(); ?>
						</a>
					</td>

					<td class="product-name" data-title="<?php esc_html_e( 'Product', 'wolf-woocommerce-wishlist' ); ?>">
						<a href="<?php echo esc_url( $permalink ); ?>">
							<?php echo get_the_title( $product_id ); ?>
						</a>
					</td>

					<td class="product-price" data-title="<?php esc_html_e( 'Price', 'wolf-woocommerce-wishlist' ); ?>">
						<a href="<?php echo esc_url( $permalink ); ?>">
							<?php
							if ( $product->get_price() != '0' ) {
								echo wp_kses_post( $product->get_price_html() );
							}
							?>
						</a>
					</td>
					<td class="product-stock-status">
					<?php
						$availability = $product->get_availability();
						$stock_status = $availability['class'];

						if( $stock_status == 'out-of-stock' ) {
							$stock_status = 'Out';
							echo '<span class="wishlist-out-of-stock">' . esc_html__( 'Out of Stock', 'wolf-woocommerce-wishlist' ) . '</span>';
						} else {
							$stock_status = 'In';
							echo '<span class="wishlist-in-stock">' . esc_html__( 'In Stock', 'wolf-woocommerce-wishlist' ) . '</span>';
						}
					?>
					</td>
				</tr>
				<?php
			}
		}

		do_action( 'www_after_wishlist_contents' ); ?>
	</tbody>

</table>

<?php else : ?>

<p><?php esc_html_e( 'No product in your wishlist yet.', 'wolf-woocommerce-wishlist' ); ?></p>

<?php endif; ?>
