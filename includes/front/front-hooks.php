<?php
/**
 * Front hooks
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Includes\Front;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Front_Hooks' ) ) {
	/**
	 * Front hooks class
	 */
	class DDWCWL_Front_Hooks {
		/**
		 * Construct
		 */
		public function __construct() {
			global $ddwcwl_waiting_list;

			$function_handler = new DDWCWL_Front_Functions();
			$endpoint         = $ddwcwl_waiting_list['waiting_list_endpoint'];

			add_filter( 'woocommerce_account_menu_items', [ $function_handler, 'ddwcwl_add_woocommerce_menu' ] );
			add_action( 'woocommerce_account_' . $endpoint . '_endpoint', [ $function_handler, 'ddwcwl_add_waiting_list_content_on_my_account_page' ] );
			add_filter( 'woocommerce_endpoint_' . $endpoint . '_title', [ $function_handler, 'ddwcwl_get_endpoint_title' ] );

			add_action( 'woocommerce_single_product_summary', [ $function_handler, 'ddwcwl_add_content_in_single_product_page' ], 35 );
			// Block themes render the product page from blocks, where the summary hook never fires.
			add_filter( 'render_block_woocommerce/add-to-cart-form', [ $function_handler, 'ddwcwl_append_to_add_to_cart_block' ] );
			add_action( 'wp_enqueue_scripts', [ $function_handler, 'ddwcwl_register_front_scripts' ] );

			// No-JS form fallback and one-click unsubscribe links.
			add_action( 'wp_loaded', [ $function_handler, 'ddwcwl_handle_form_post' ] );
			add_action( 'template_redirect', [ $function_handler, 'ddwcwl_handle_email_action' ] );

			add_shortcode( $function_handler->ddwcwl_validate_shortcode( $ddwcwl_waiting_list['waiting_list_template_shortcode'] ), [ $function_handler, 'ddwcwl_get_waiting_list_template_shortcode_content' ] );
		}
	}
}
