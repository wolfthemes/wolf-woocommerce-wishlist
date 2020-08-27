<?php
/**
 * WooCommerce Wishlist Options.
 *
 * @class WWW_Options
 * @author WolfThemes
 * @category Admin
 * @package WolfWooCommerceWishlist/Admin
 * @version 1.1.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WWW_Options class.
 */
class WWW_Options {
	/**
	 * Constructor
	 */
	public function __construct() {

		// Admin init hooks
		$this->admin_init_hooks();
	}

	/**
	 * Admin init
	 */
	public function admin_init_hooks() {

		// Set default options
		add_action( 'admin_init', array( $this, 'default_options' ) );

		// Register settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Add options menu
		add_action( 'admin_menu', array( $this, 'add_options_menu' ) );
	}

	/**
	 * Add options menu
	 */
	public function add_options_menu() {

		add_options_page( esc_html__( 'Wishlist', 'wolf-woocommerce-wishlist' ), esc_html__( 'Wishlist', 'wolf-woocommerce-wishlist' ), 'edit_plugins', 'wolf-woocommerce-wishlist-settings', array( $this, 'options_form' ) );
	}

	/**
	 * Register options
	 */
	public function register_settings() {
		register_setting( 'wolf-woocommerce-wishlist-settings', 'wolf_woocommerce_wishlist_settings', array( $this, 'settings_validate' ) );
		add_settings_section( 'wolf-woocommerce-wishlist-settings', '', array( $this, 'section_intro' ), 'wolf-woocommerce-wishlist-settings' );
		add_settings_field( 'page_id', esc_html__( 'Wishlist Page', 'wolf-woocommerce-wishlist' ), array( $this, 'setting_page_id' ), 'wolf-woocommerce-wishlist-settings', 'wolf-woocommerce-wishlist-settings' );
		add_settings_field( 'instructions', esc_html__( 'Instructions', 'wolf-woocommerce-wishlist' ), array( $this, 'setting_instructions' ), 'wolf-woocommerce-wishlist-settings', 'wolf-woocommerce-wishlist-settings' );
	}

	/**
	 * Validate options
	 *
	 * @param array $input
	 * @return array $input
	 */
	public function settings_validate( $input ) {

		if ( isset( $input['page_id'] ) ) {
			update_option( '_wolf_wishlist_page_id', intval( $input['page_id'] ) );
			unset( $input['page_id'] );
		}

		return $input;
	}

	/**
	 * Debug section
	 */
	public function section_intro() {
		// debug
		//global $options;
		// var_dump(get_option( '_wolf_events_page_id' ));
	}

	/**
	 * Page settings
	 *
	 * @return string
	 */
	public function setting_page_id() {
		$page_option = array( '' => esc_html__( '- Disabled -', 'wolf-woocommerce-wishlist' ) );
		$pages = get_pages();

		foreach ( $pages as $page ) {

			if ( get_post_field( 'post_parent', $page->ID ) ) {
				$page_option[ absint( $page->ID ) ] = '&nbsp;&nbsp;&nbsp; ' . sanitize_text_field( $page->post_title );
			} else {
				$page_option[ absint( $page->ID ) ] = sanitize_text_field( $page->post_title );
			}
		}
		?>
		<select name="wolf_woocommerce_wishlist_settings[page_id]">
			<option value="-1"><?php esc_html_e( 'Select a page...', 'wolf-woocommerce-wishlist' ); ?></option>
			<?php foreach ( $page_option as $k => $v ) : ?>
				<option <?php selected( absint( $k ), get_option( '_wolf_wishlist_page_id' ) ); ?> value="<?php echo intval( $k ); ?>"><?php echo sanitize_text_field( $v ); ?></option>
			<?php endforeach; ?>
		</select>
		<p>
			<label for="wolf_woocommerce_wishlist_settings[page_id]">
				<?php printf( esc_html__( 'It is recommended to set your wishlist page here, so themes and plugins can access its URL with the %s function.', 'wolf-woocommerce-wishlist' ), 'wolf_get_wishlist_url()' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Display additional instructions
	 */
	public function setting_instructions() {
		?>
		<p><?php printf( esc_html__( 'You can use the %s shortcode to display your wishlist in your page.', 'wolf-woocommerce-wishlist' ), '[wolf_wishlist]' ) ?></p>
		<p><?php printf( esc_html__( 'To display an "Add to wishlist" button anywhere use %s.', 'wolf-woocommerce-wishlist' ), '[wolf_add_to_wishlist]' ) ?></p>
		<?php
	}

	/**
	 * Options form
	 */
	public function options_form() {
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Wishlist Options', 'wolf-woocommerce-wishlist' ); ?></h2>
			<form action="options.php" method="post">
				<?php settings_fields( 'wolf-woocommerce-wishlist-settings' ); ?>
				<?php do_settings_sections( 'wolf-woocommerce-wishlist-settings' ); ?>
				<p class="submit"><input name="save" type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'wolf-woocommerce-wishlist' ); ?>" /></p>
			</form>
		</div>
		<?php
	}

	/**
	 * Set default options
	 */
	public function default_options() {

		// delete_option( 'wolf_woocommerce_wishlist_settings' );

		if ( false === get_option( 'wolf_woocommerce_wishlist_settings' )  ) {

			$default = array(

			);

			add_option( 'wolf_woocommerce_wishlist_settings', $default );
		}
	}
} // end class

return new WWW_Options();