<?php
/**
 * WooCommerce Wishlist frontend functions
 *
 * General functions available on frontend
 *
 * @author WolfThemes
 * @category Core
 * @package WolfWooCommerceWishlist/Frontend
 * @version 1.1.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Output wishlist table
 */
function wolf_woocommerce_wishlist() {

	do_action( 'www_before_wishlist' );

	include( WWW_DIR . '/templates/wishlist.php' );

	do_action( 'www_after_wishlist' );
}

/**
 * Add to wishlist button
 *
 * @since 1.0.0
 */
function wolf_add_to_wishlist() {
	$wishlist = www_get_wishlist_product_ids();

	$product_id = get_the_ID();
	$is_in_wishlist = ( $wishlist ) ? ( in_array( $product_id, $wishlist ) ) : false;

	$class = ( $is_in_wishlist ) ? 'wolf_in_wishlist' : '';
	
	$text = ( $is_in_wishlist ) ? esc_html__( 'Remove from wishlist', 'wolf-woocommerce-wishlist' ) : esc_html__( 'Add to wishlist', 'wolf-woocommerce-wishlist' );
	do_action( 'www_before_add_to_wishlist' );

	$class .= apply_filters( 'wolf_add_to_wishlist_class', ' wolf_add_to_wishlist button' );

	?>
	<a
	class="<?php echo esc_attr( $class ); ?>"
	href="?add_to_wishlist=<?php the_ID(); ?>"
	title="<?php echo esc_attr( $text ); ?>"
	rel="nofollow"
	data-product-title="<?php echo esc_attr( get_the_title() ); ?>"
	data-product-id="<?php the_ID(); ?>"><span class="wolf_add_to_wishlist_heart"></span></a>
	<?php
	do_action( 'www_after_add_to_wishlist' );
}

/**
 * Hook woocommerce_template_loop_add_to_cart to output our button
 */
function www_output_add_to_wishlist_button() {
	wolf_add_to_wishlist();
}
add_action( 'woocommerce_after_shop_loop_item', 'www_output_add_to_wishlist_button', 15 );
add_action( 'woocommerce_after_add_to_cart_button', 'www_output_add_to_wishlist_button' );

/**
 * Clean up product ids
 *
 * Remove ids of product that don't exist
 *
 * @param array $product_ids
 * @return array $product_ids
 */
function www_clean_wishlist_product_ids( $product_ids = array() ) {

	$clean_product_ids = array();

	if ( is_array( $product_ids ) ) {

		foreach ( $product_ids as $product_id ) {

			if ( 'publish' === get_post_status ( $product_id ) ) {
				$clean_product_ids[] = $product_id;
			}
		}
	}

	return $clean_product_ids;
}

/**
 * Set cookie on first page load
 */
function www_set_default_cookie() {

	$product_ids = array();
	$cookie_name = www_get_site_slug() . '_wc_wishlist';
	$cookie = ( isset( $_COOKIE[ $cookie_name ] ) ) ? $_COOKIE[ $cookie_name ] : null;

	$user_id = get_current_user_id();
	$user_meta = get_user_meta( $user_id, $cookie_name, true );

	if ( $user_meta ) {
		$product_ids = www_clean_wishlist_product_ids( $user_meta );
		setcookie( $cookie_name, www_array_to_list( $product_ids ), 0, '/' );
	}
}
//add_action( 'init', 'www_set_default_cookie' );

/**
 * Get a PHP array of products in the wishlist
 *
 * Retrieve from user data if user is logged in
 *
 * @since 1.0.0
 */
function www_get_wishlist_product_ids() {

	$product_ids = array();

	$cookie_name = www_get_site_slug() . '_wc_wishlist';
	$cookie = ( isset( $_COOKIE[ $cookie_name ] ) ) ? $_COOKIE[ $cookie_name ] : null;

	$user_id = get_current_user_id();
	$user_meta = get_user_meta( $user_id, $cookie_name, true );

	// If we can get the user meta we use it as starting point, always
	if ( $user_meta || is_user_logged_in() ) {

		$product_ids = www_clean_wishlist_product_ids( $user_meta );

		//debug( $user_meta );

		$_COOKIE[ $cookie_name ] = www_array_to_list( $product_ids );

		//debug( $_COOKIE[ $cookie_name ] );

	// if the user is not logged in, we use the cookie value
	} elseif ( $cookie ) {
		$product_ids = array_unique( json_decode( '[' . $cookie . ']' ) );
	}

	$product_ids = www_clean_wishlist_product_ids( $product_ids ); // cleaned up

	return apply_filters( 'wolf_wishlist_product_ids', $product_ids );
}

/**
 * Get site name slug
 *
 * @return string
 */
function www_get_site_slug() {
	return str_replace( '-', '_', sanitize_title_with_dashes( get_bloginfo('name' ) ) );
}

/**
 * Remove all double spaces
 *
 * This function is mainly used to clean up inline CSS
 *
 * @param string $css
 * @return string
 */
function www_clean_spaces( $string, $hard = true ) {
	return preg_replace( '/\s+/', ' ', $string );
}

/**
 * Convert list of IDs to array
 *
 * @since 1.0.0
 * @param string $list
 * @return array
 */
function www_list_to_array( $list, $separator = ',' ) {
	return ( $list ) ? explode( ',', trim( www_clean_spaces( www_clean_list( $list ) ) ) ) : array();
}

/**
 * Convert array of ids to list
 *
 * @since 1.0.0
 * @param string $list
 * @return array
 */
function www_array_to_list( $array ) {
	
	$list = '';

	if ( is_array( $array ) ) {
		$list = rtrim( implode( ',',  $array ), ',' );
	}

	return www_clean_list( $list );
}

/**
 * Clean a list
 *
 * Remove first and last comma of a list and remove spaces before and after separator
 *
 * @param string $list
 * @return string $list
 */
function www_clean_list( $list, $separator = ',' ) {
	$list = str_replace( array( $separator . ' ', ' ' . $separator ), $separator, $list );
	$list = ltrim( $list, $separator );
	$list = rtrim( $list, $separator );
	return $list;
}

/**
 * Get User IP
 */
function www_get_visitor_id() {

	$ip = null;

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {

		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];

	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {

		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	//$ip = '654';

	return apply_filters( 'www_get_visitor_id', $ip );
}

/**
 * Enqeue styles and scripts
 *
 * @param array $classes
 * @return array $classes
 */
function www_add_body_class( $classes ) {

	if ( is_page( wolf_wishlist_get_page_id() ) ) {
		$classes[] =  'wolf-wishlist-page';
	}

	return $classes;
}
add_filter( 'body_class', 'www_add_body_class' );

/**
 * Enqeue styles and scripts
 *
 * @since 1.0.0
 */
function www_enqueue_scripts() {

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' );
	$version = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ?  time() : WWW_VERSION );

	// Styles
	wp_enqueue_style( 'wolf-woocommerce-wishlist', WWW_CSS . '/wishlist' . $suffix . '.css', array(), WWW_VERSION, 'all' );


	// Scripts
	wp_enqueue_script( 'js-cookie', WWW_JS . '/lib/js.cookie' . $suffix . '.js', array(), '2.1.4', true ); // should be already enqueued by WooCommerce
	wp_enqueue_script( 'wolf-woocommerce-wishlist', WWW_JS . '/wishlist' . $suffix . '.js', array( 'jquery' ), WWW_VERSION, true );

	// Add JS global variables
	wp_localize_script(
		'wolf-woocommerce-wishlist', 'WolfWooCommerceWishlistJSParams', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'siteUrl' => site_url( '/' ),
			'siteSlug' => www_get_site_slug(),
			'userId' => get_current_user_id(),
			'language' => get_locale(),
			'l10n' => array(
				'addToWishlist' => esc_html__( 'Add to wishlist', 'wolf-woocommerce-wishlist' ),
				'removeFromWishlist' => esc_html__( 'Remove from wishlist', 'wolf-woocommerce-wishlist' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts',  'www_enqueue_scripts', 20 );