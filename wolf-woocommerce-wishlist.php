<?php
/**
 * Plugin Name: WooCommerce Wishlist
 * Plugin URI: https://github.com/wolfthemes/wolf-woocommerce-wishlist
 * Description: A simple and lightweight wishlist feature for WooCommerce.
 * Version: 1.1.6
 * Author: WolfThemes
 * Author URI: http://wolfthemes.com
 * Requires at least: 5.0
 * Tested up to: 5.5
 *
 * Text Domain: wolf-woocommerce-wishlist
 * Domain Path: /languages/
 *
 * WC requires at least: 3.0
 * WC tested up to: 4.0
 *
 * @package WolfWooCommerceWishlist
 * @category Core
 * @author WolfThemes
 *
 * Being a free product, this plugin is distributed as-is without official support.
 * Verified customers however, who have purchased a premium theme
 * at https://themeforest.net/user/Wolf-Themes/portfolio?ref=Wolf-Themes
 * will have access to support for this plugin in the forums
 * https://wolfthemes.ticksy.com/
 *
 * Copyright (C) 2013 Constantin Saguin
 * This WordPress Plugin is a free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * It is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * See https://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Wolf_WooCommerce_Wishlist' ) ) {
	/**
	 * Main Wolf_WooCommerce_Wishlist Class
	 *
	 * Contains the main functions for Wolf_WooCommerce_Wishlist
	 *
	 * @class Wolf_WooCommerce_Wishlist
	 * @version 1.1.6
	 * @since 1.0.0
	 */
	class Wolf_WooCommerce_Wishlist {

		/**
		 * @var string
		 */
		private $required_php_version = '5.4.0';

		/**
		 * @var string
		 */
		public $version = '1.1.6';

		/**
		 * @var WooCommerce Wishlist The single instance of the class
		 */
		protected static $_instance = null;

		/**
		 * @var string
		 */
		private $update_url = 'https://plugins.wolfthemes.com/update';

		/**
		 * @var the support forum URL
		 */
		private $support_url = 'https://docs.wolfthemes.com/';

		/**
		 * @var string
		 */
		public $template_url;

		/**
		 * Main WooCommerce Wishlist Instance
		 *
		 * Ensures only one instance of WooCommerce Wishlist is loaded or can be loaded.
		 *
		 * @static
		 * @see WE()
		 * @return WooCommerce Wishlist - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * WooCommerce Wishlist Constructor.
		 */
		public function __construct() {

			/* Don't do anything if WC is not activated */
			if ( ! $this->is_woocommerce_active() ) {
				//return;
			}


			if ( phpversion() < $this->required_php_version ) {
				add_action( 'admin_notices', array( $this, 'warning_php_version' ) );
				return;
			}

			$this->define_constants();
			$this->includes();
			$this->init_hooks();

			do_action( 'www_loaded' );
		}

		/**
		 * Check if WooCommerce is active
		 *
		 * @see https://docs.woocommerce.com/document/create-a-plugin/
		 * @return bool
		 */
		public function is_woocommerce_active() {
			return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
		}

		/**
		 * Display error notice if PHP version is too low
		 */
		public function warning_php_version() {
			?>
			<div class="notice notice-error">
				<p><?php

				printf(
					esc_html__( '%1$s needs at least PHP %2$s installed on your server. You have version %3$s currently installed. Please contact your hosting service provider if you\'re not able to update PHP by yourself.', 'wolf-woocommerce-wishlist' ),
					'WooCommerce Wishlist',
					$this->required_php_version,
					phpversion()
				);
				?></p>
			</div>
			<?php
		}

		/**
		 * Hook into actions and filters
		 */
		private function init_hooks() {
			add_action( 'init', array( $this, 'init' ), 0 );
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
		}

		/**
		 * Activation function
		 */
		public function activate() {

			add_option( '_wolf_wishlist_needs_page', true );
		}

		/**
		 * Define WR Constants
		 */
		private function define_constants() {

			$constants = array(
				'WWW_DEV' => false,
				'WWW_DIR' => $this->plugin_path(),
				'WWW_URI' => $this->plugin_url(),
				'WWW_CSS' => $this->plugin_url() . '/assets/css',
				'WWW_JS' => $this->plugin_url() . '/assets/js',
				'WWW_SLUG' => plugin_basename( dirname( __FILE__ ) ),
				'WWW_PATH' => plugin_basename( __FILE__ ),
				'WWW_VERSION' => $this->version,
				'WWW_UPDATE_URL' => $this->update_url,
				'WWW_SUPPORT_URL' => $this->support_url,
				'WWW_DOC_URI' => 'https://docs.wolfthemes.com/documentation/plugins/' . plugin_basename( dirname( __FILE__ ) ),
				'WWW_WOLF_DOMAIN' => 'wolfthemes.com',
			);

			foreach ( $constants as $name => $value ) {
				$this->define( $name, $value );
			}
		}

		/**
		 * Define constant if not already set
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * What type of request is this?
		 * string $type ajax, frontend or admin
		 * @return bool
		 */
		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin' :
					return is_admin();
				case 'ajax' :
					return defined( 'DOING_AJAX' );
				case 'cron' :
					return defined( 'DOING_CRON' );
				case 'frontend' :
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function includes() {

			/**
			 * Functions used in frontend and admin
			 */
			include_once( 'inc/www-core-functions.php' );

			if ( $this->is_request( 'admin' ) ) {
				include_once( 'inc/admin/class-www-admin.php' );
			}

			if ( $this->is_request( 'ajax' ) ) {
				include_once( 'inc/ajax/www-ajax-functions.php' );
			}

			if ( $this->is_request( 'frontend' ) ) {
				include_once( 'inc/frontend/www-functions.php' );
				include_once( 'inc/frontend/class-www-shortcodes.php' );
			}
		}

		/**
		 * Init WooCommerce Wishlist when WordPress Initialises.
		 */
		public function init() {

			// Set up localisation
			$this->load_plugin_textdomain();
		}

		/**
		 * Loads the plugin text domain for translation
		 */
		public function load_plugin_textdomain() {

			$domain = 'wolf-woocommerce-wishlist';
			$locale = apply_filters( 'wolf-woocommerce-wishlist', get_locale(), $domain );
			load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
			load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Get the plugin url.
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Get the template path.
		 * @return string
		 */
		public function template_path() {
			return apply_filters( 'www_template_path', 'wolf-woocommerce-wishlist/' );
		}
	} // end class
} // end class check

/**
 * Returns the main instance of WWW to prevent the need to use globals.
 *
 * @return Wolf_WooCommerce_Wishlist
 */
function WWW() {
	return Wolf_WooCommerce_Wishlist::instance();
}

WWW(); // Go
