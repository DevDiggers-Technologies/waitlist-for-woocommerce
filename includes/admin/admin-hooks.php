<?php
/**
 * Admin hooks class
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Includes\Admin;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Admin_Hooks' ) ) {
	/**
	 * Admin hooks class
	 */
	class DDWCWL_Admin_Hooks {
		/**
		 * Construct
		 */
		public function __construct() {
			$function_handler = new DDWCWL_Admin_Functions();

			add_action( 'admin_init', [ $function_handler, 'ddwcwl_register_settings' ] );

			// Waitlist demand column on the WooCommerce Products screen.
			add_filter( 'manage_edit-product_columns', [ $function_handler, 'ddwcwl_add_product_column' ], 20 );
			add_action( 'manage_product_posts_custom_column', [ $function_handler, 'ddwcwl_render_product_column' ], 10, 2 );
			add_action( 'admin_enqueue_scripts', [ $function_handler, 'ddwcwl_product_column_style' ], 20 );

			// List screen actions.
			add_action( 'admin_init', [ $function_handler, 'ddwcwl_handle_list_actions' ] );

			// Email preview and test sends.
			add_action( 'admin_post_ddwcwl_preview_email', [ $function_handler, 'ddwcwl_preview_email' ] );
			add_action( 'wp_ajax_ddwcwl_send_test_email', [ $function_handler, 'ddwcwl_send_test_email' ] );
		}
	}
}
