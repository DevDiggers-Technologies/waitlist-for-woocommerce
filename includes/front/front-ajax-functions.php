<?php
/**
 * Front ajax functions class
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Includes\Front;

use DDWCWaitingList\Helper\Subscribers\DDWCWL_Subscribers_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Front_Ajax_Functions' ) ) {
	/**
	 * Front ajax functions class
	 */
	class DDWCWL_Front_Ajax_Functions {
		/**
		 * Join a waiting list.
		 *
		 * @return void
		 */
		public function ddwcwl_join_waiting_list() {
			if ( ! check_ajax_referer( 'ddwcwl-nonce', 'nonce', false ) ) {
				wp_send_json_error( [ 'message' => DDWCWL_Front_Functions::ddwcwl_get_message( 'security' ) ] );
			}

			$result = ( new DDWCWL_Front_Functions() )->ddwcwl_process_join(
				[
					'ddwcwl_product_id' => absint( $_POST['ddwcwl_product_id'] ?? 0 ),
					'ddwcwl_user_email' => sanitize_email( wp_unslash( $_POST['ddwcwl_user_email'] ?? '' ) ),
					'ddwcwl_consent'    => ! empty( $_POST['ddwcwl_consent'] ),
					'ddwcwl_website'    => sanitize_text_field( wp_unslash( $_POST['ddwcwl_website'] ?? '' ) ),
				]
			);

			if ( $result['success'] ) {
				wp_send_json_success( $result );
			}

			wp_send_json_error( $result );
		}

		/**
		 * Leave a waiting list (logged-in customers).
		 *
		 * @return void
		 */
		public function ddwcwl_leave_waiting_list() {
			if ( ! check_ajax_referer( 'ddwcwl-nonce', 'nonce', false ) ) {
				wp_send_json_error( [ 'message' => DDWCWL_Front_Functions::ddwcwl_get_message( 'security' ) ] );
			}

			$product_id = absint( $_POST['ddwcwl_product_id'] ?? 0 );

			if ( ( new DDWCWL_Front_Functions() )->ddwcwl_process_leave( $product_id ) ) {
				wp_send_json_success(
					[
						'message' => DDWCWL_Front_Functions::ddwcwl_get_message( 'left' ),
						'count'   => ( new DDWCWL_Subscribers_Helper() )->count_waiting( $product_id ),
					]
				);
			}

			wp_send_json_error( [ 'message' => DDWCWL_Front_Functions::ddwcwl_get_message( 'unavailable' ) ] );
		}
	}
}
