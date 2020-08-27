<?php
/**
 * WooCommerce Wishlist AJAX Functions
 *
 *
 * @author WolfThemes
 * @category Ajax
 * @package WolfWooCommerceWishlist/Functions
 * @version 1.1.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * update wishlist user meta
 *
 * @since 1.0.0
 */
function www_ajax_update_wishlist() {

	extract( $_POST );

	if ( isset( $_POST['userId'] ) ) {
		
		$product_ids = $_POST['wishlistIds'];
		$user_id = absint( $_POST['userId'] );
		$cookie_name = www_get_site_slug() . '_wc_wishlist';

		// Clean product ids
		$product_ids = www_clean_wishlist_product_ids( $product_ids );

		//debug( $product_ids );

		// if user is logged in, we store the wishlist in the user meta
		if ( $user_id  ) {
			update_user_meta( $user_id, $cookie_name, $product_ids );
		}

		if ( array() === $product_ids ) {
			echo 'empty';
		}
	}

	exit;
}
add_action( 'wp_ajax_www_ajax_update_wishlist', 'www_ajax_update_wishlist' );
add_action( 'wp_ajax_nopriv_www_ajax_update_wishlist', 'www_ajax_update_wishlist' );