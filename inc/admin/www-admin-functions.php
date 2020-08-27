<?php
/**
 * WooCommerce Wishlist admin functions
 *
 * Functions available on admin
 *
 * @author WolfThemes
 * @category Core
 * @package WolfWooCommerceWishlist/Core
 * @version 1.1.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update page options key
 */
function www_update() {
	update_option( '_wolf_wishlist_needs_page', true );
	update_option( '_wolf_wishlist_page_id', get_option( '_wolf_woocommerce_wishlist_page_id' ) );
}
// add_action( 'wolf_woocommerce_wishlist_do_update', 'www_update' );

/**
 * Display archive page state
 *
 * @param array $states
 * @param object $post
 * @return array $states
 */
function www_custom_post_states( $states, $post ) { 

	if ( 'page' == get_post_type( $post->ID ) && absint( $post->ID ) === wolf_wishlist_get_page_id() ) {

		$states[] = esc_html__( 'Wishlist Page' );
	} 

	return $states;
}
add_filter( 'display_post_states', 'www_custom_post_states', 10, 2 );