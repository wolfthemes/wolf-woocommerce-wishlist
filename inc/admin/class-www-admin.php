<?php
/**
 * WooCommerce Wishlist Admin.
 *
 * @class WWW_Admin
 * @author WolfThemes
 * @category Admin
 * @package WolfWooCommerceWishlist/Admin
 * @version 1.1.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WWW_Admin class.
 */
class WWW_Admin {
	/**
	 * Constructor
	 */
	public function __construct() {

		// Includes files
		$this->includes();

		// Admin init hooks
		$this->admin_init_hooks();
	}

	/**
	 * Perform actions on updating the theme id needed
	 */
	public function update() {

		if ( ! defined( 'IFRAME_REQUEST' ) && ! defined( 'DOING_AJAX' ) && ( get_option( 'wolf_woocommerce_wishlist_version' ) != WWW_VERSION ) ) {

			// Update hook
			do_action( 'wolf_woocommerce_wishlist_do_update' );

			// Update version
			delete_option( 'wolf_woocommerce_wishlist_version' );
			add_option( 'wolf_woocommerce_wishlist_version', WWW_VERSION );

			// After update hook
			do_action( 'wolf_woocommerce_wishlist_updated' );
		}
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {

		include_once( 'class-www-options.php' );
		include_once( 'www-admin-functions.php' );
	}

	/**
	 * Admin init
	 */
	public function admin_init_hooks() {

		// row meta
		add_filter( 'plugin_action_links_' . plugin_basename( WWW_PATH ), array( $this, 'settings_action_links' ) );

		// Update version and perform stuf if needed
		add_action( 'admin_init', array( $this, 'update' ), 0 );

		// Plugin update notifications
		add_action( 'admin_init', array( $this, 'plugin_update' ) );

		// Create page notice
		add_action( 'admin_notices', array( $this, 'check_page' ) );
		add_action( 'admin_notices', array( $this, 'create_page' ) );
	}

	/**
	 * Check albums page
	 *
	 * Display a notification if we can't get the albums page id
	 *
	 */
	public function check_page() {

		$output    = '';
		$theme_dir = get_template_directory();

		// update_option( '_wolf_wishlist_needs_page', true );
		// delete_option( '_wolf_wishlist_no_needs_page', true );
		// delete_option( '_wolf_wishlist_page_id' );

		//dd( wolf_wishlist_get_page_id() );

		if ( get_option( '_wolf_wishlist_no_needs_page' ) ) {
			return;
		}

		if ( ! get_option( '_wolf_wishlist_needs_page' ) ) {
			return;
		}

		//dd( wolf_wishlist_get_page_id() );

		if ( -1 == wolf_wishlist_get_page_id() && ! isset( $_GET['wolf_wishlist_create_page'] ) ) {


			if ( isset( $_GET['skip_wolf_wishlist_setup'] ) ) {
				delete_option( '_wolf_wishlist_needs_page' );
				return;
			}

			update_option( '_wolf_wishlist_needs_page', true );

			$message = '<strong>Wolf Wishlist</strong> ' . sprintf(
					wp_kses(
						__( 'says : <em>Almost done! you need to <a href="%1$s">create a page</a> for your wishlist or <a href="%2$s">select an existing page</a> in the plugin settings</em>.', 'wolf-woocommerce-wishlist' ),
						array(
							'a' => array(
								'href' => array(),
								'class' => array(),
								'title' => array(),
							),
							'br' => array(),
							'em' => array(),
							'strong' => array(),
						)
					),
					esc_url( admin_url( '?wolf_wishlist_create_page=true' ) ),
					esc_url( admin_url( 'options-general.php?page=wolf-woocommerce-wishlist-settings' ) )
			);

			$message .= sprintf(
				wp_kses(
					__( '<br><br>
					<a href="%1$s" class="button button-primary">Create a page</a>
					&nbsp;
					<a href="%2$s" class="button button-primary">Select an existing page</a>
					&nbsp;
					<a href="%3$s" class="button">Skip setup</a>', 'wolf-woocommerce-wishlist' ),

					array(
							'a' => array(
								'href' => array(),
								'class' => array(),
								'title' => array(),
							),
							'br' => array(),
							'em' => array(),
							'strong' => array(),
						)
				),
					esc_url( admin_url( '?wolf_wishlist_create_page=true' ) ),
					esc_url( admin_url( 'options-general.php?page=wolf-woocommerce-wishlist-settings' ) ),
					esc_url( admin_url( '?skip_wolf_wishlist_setup=true' ) )
			);

			$output = '<div class="updated wolf-admin-notice wolf-plugin-admin-notice"><p>';

				$output .= $message;

			$output .= '</p></div>';

			echo $output;
		} else {

			delete_option( '_wolf_wishlist_needs_page' );
		}

		return false;
	}

	/**
	 * Create albums page
	 */
	public function create_page() {

		if ( isset( $_GET['wolf_wishlist_create_page'] ) && $_GET['wolf_wishlist_create_page'] == 'true' ) {

			$output = '';

			// Create post object
			$post = array(
				'post_title'  => esc_html__( 'Wishlist', 'wolf-woocommerce-wishlist' ),
				'post_content' => '[wolf_wishlist]',
				'post_type'   => 'page',
				'post_status' => 'publish',
			);

			// Insert the post into the database
			$post_id = wp_insert_post( $post );

			if ( $post_id ) {

				update_option( '_wolf_wishlist_page_id', $post_id );
				update_post_meta( $post_id, '_wpb_status', 'off' ); // disable page builder mode for this page

				$message = esc_html__( 'Your wishlist page has been created succesfully', 'wolf-woocommerce-wishlist' );

				$output = '<div class="updated"><p>';

				$output .= $message;

				$output .= '</p></div>';

				echo $output;
			}

		}

		return false;
	}

	/**
	 * Add settings link in plugin page
	 */
	public function settings_action_links( $links ) {
		$setting_link = array(
			'<a href="' . admin_url( 'options-general.php?page=wolf-woocommerce-wishlist-settings' ) . '">' . esc_html__( 'Settings', 'wolf-woocommerce-wishlist' ) . '</a>',
		);
		return array_merge( $links, $setting_link );
	}

	/**
	 * Plugin update
	 */
	public function plugin_update() {

		$plugin_slug = WWW_SLUG;
		$plugin_path = WWW_PATH;
		$remote_path = WWW_UPDATE_URL . '/' . $plugin_slug;
		$plugin_data = get_plugin_data( WWW_DIR . '/' . WWW_SLUG . '.php' );
		$current_version = $plugin_data['Version'];
		include_once( 'class-www-update.php');
		new WWW_Update( $current_version, $remote_path, $plugin_path );
	}
}

return new WWW_Admin();