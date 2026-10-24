<?php
/**
 * Front ajax hooks class
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Includes\Front;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Front_Ajax_Hooks' ) ) {
	/**
	 * Front ajax hooks class
	 */
	class DDWCWL_Front_Ajax_Hooks {
		/**
		 * Construct
		 */
		public function __construct() {
			$function_handler = new DDWCWL_Front_Ajax_Functions();

			add_action( 'wp_ajax_ddwcwl_join_waiting_list', [ $function_handler, 'ddwcwl_join_waiting_list' ] );
			add_action( 'wp_ajax_nopriv_ddwcwl_join_waiting_list', [ $function_handler, 'ddwcwl_join_waiting_list' ] );
			add_action( 'wp_ajax_ddwcwl_leave_waiting_list', [ $function_handler, 'ddwcwl_leave_waiting_list' ] );
		}
	}
}
