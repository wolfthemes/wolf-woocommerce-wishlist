<?php
/**
 * WooCommerce Wishlist core functions
 *
 * General core functions available on admin and frontend
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
 * Get wishlist page id
 *
 * @param string $value
 * @param string $default
 * @return string
 */
function wolf_wishlist_get_page_id() {
	$page_id = -1;

	if ( -1 != get_option( '_wolf_wishlist_page_id' ) && get_option( '_wolf_wishlist_page_id' ) ) {

		$page_id = get_option( '_wolf_wishlist_page_id' );
	}

	if ( -1 != $page_id ) {
		$page_id = apply_filters( 'wpml_object_id', absint( $page_id ), 'page', false ); // filter for WPML
	}

	return $page_id;
}

if ( ! function_exists( 'wolf_get_wishlist_url' ) ) {
	/**
	 * Returns the URL of the wishlist page
	 */
	function wolf_get_wishlist_url() {

		$page_id = wolf_wishlist_get_page_id();

		if ( -1 != $page_id ) {
			return get_permalink( $page_id );
		}
	}
}

/**
 * Get options
 *
 * @param string $value
 * @param string $default
 * @return string
 */
function wolf_woocommerce_wishlist_get_option( $value, $default = null ) {

	$wolf_woocommerce_wishlist_settings = get_option( 'wolf_woocommerce_wishlist_settings' );
	
	if ( isset( $wolf_woocommerce_wishlist_settings[ $value ] ) && '' != $wolf_woocommerce_wishlist_settings[ $value ] ) {
		
		return $wolf_woocommerce_wishlist_settings[ $value ];
	
	} elseif ( $default ) {

		return $default;
	}
}