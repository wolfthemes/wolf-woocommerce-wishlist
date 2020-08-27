<?php
/**
 * WooCommerce Wishlist Shortcode.
 *
 * @class WWW_Shortcodes
 * @author WolfThemes
 * @category Core
 * @package WolfWooCommerceWishlist/Shortcode
 * @version 1.1.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WWW_Shortcodes class.
 */
class WWW_Shortcodes {
	/**
	 * Constructor
	 */
	public function __construct() {

		add_shortcode( 'wolf_wishlist', array( $this, 'wishlist_shortcode' ) );
		add_shortcode( 'wolf_woocommerce_wishlist', array( $this, 'wishlist_shortcode' ) );
		add_shortcode( 'wolf_add_to_wishlist', array( $this, 'add_to_wishlist_shortcode' ) );
	}

	/**
	 * Render wishlist shortcode
	 */
	public function wishlist_shortcode() {

		ob_start();
		wolf_woocommerce_wishlist();
		return ob_get_clean();
	}

	/**
	 * Render wishlist shortcode
	 */
	public function add_to_wishlist_shortcode() {

		ob_start();
		wolf_add_to_wishlist();
		return ob_get_clean();
	}

	/**
	 * Helper method to determine if a shortcode attribute is true or false.
	 *
	 * @since 1.0.0
	 * @param string|int|bool $var Attribute value.
	 * @return bool
	 */
	protected function shortcode_bool( $var ) {
		$falsey = array( 'false', '0', 'no', 'n' );
		return ( ! $var || in_array( strtolower( $var ), $falsey, true ) ) ? false : true;
	}

} // end class

return new WWW_Shortcodes();