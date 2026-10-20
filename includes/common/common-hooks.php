<?php
/**
 * Common hooks class
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Includes\Common;

use DDWCWaitingList\Includes\DDWCWL_Notifier;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Common_Hooks' ) ) {
	/**
	 * Common hooks class. Everything here must run in admin, storefront, REST and cron alike.
	 */
	class DDWCWL_Common_Hooks {
		/**
		 * Construct
		 */
		public function __construct() {
			$function_handler = new DDWCWL_Common_Functions();

			add_action( 'init', [ $function_handler, 'ddwcwl_add_endpoints' ] );
			add_filter( 'woocommerce_email_classes', [ $function_handler, 'ddwcwl_add_email_classes' ] );
			add_filter( 'ddfw_modify_svg_icons', [ $function_handler, 'ddwcwl_add_svg_icons' ], 10, 2 );

			// Restock detection: fires for admin saves, REST/CSV imports, refunds and order cancellations alike.
			add_action( 'woocommerce_product_set_stock_status', [ $function_handler, 'ddwcwl_handle_stock_status_change' ], 10, 3 );
			add_action( 'woocommerce_variation_set_stock_status', [ $function_handler, 'ddwcwl_handle_stock_status_change' ], 10, 3 );
			add_action( 'woocommerce_product_set_stock', [ $function_handler, 'ddwcwl_handle_stock_change' ] );
			add_action( 'woocommerce_variation_set_stock', [ $function_handler, 'ddwcwl_handle_stock_change' ] );
			add_action( DDWCWL_Notifier::BATCH_HOOK, [ $function_handler, 'ddwcwl_process_restock_batch' ] );

			// Signup emails.
			add_action( 'ddwcwl_subscriber_added', [ $function_handler, 'ddwcwl_send_signup_emails' ], 10, 2 );

			// Housekeeping.
			add_action( 'before_delete_post', [ $function_handler, 'ddwcwl_delete_product_subscribers' ] );

			// Privacy tools.
			add_filter( 'wp_privacy_personal_data_exporters', [ $function_handler, 'ddwcwl_register_privacy_exporter' ] );
			add_filter( 'wp_privacy_personal_data_erasers', [ $function_handler, 'ddwcwl_register_privacy_eraser' ] );
		}
	}
}
