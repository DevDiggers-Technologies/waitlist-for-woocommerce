<?php
/**
 * Back in stock email sent to waiting customers.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Includes\Emails;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Back_In_Stock_Email' ) ) {
	/**
	 * Back in stock email sent to waiting customers.
	 */
	class DDWCWL_Back_In_Stock_Email extends DDWCWL_Email_Base {
		/**
		 * Construct.
		 */
		public function __construct() {
			// The id is kept from 1.x so the saved email format carries over.
			$this->id             = 'ddwcwl_notification';
			$this->ddwcwl_type    = 'back_in_stock';
			$this->customer_email = true;
			$this->title          = esc_html__( 'Waiting List: Back in Stock', 'devdiggers-waitlist-for-woocommerce' );
			$this->description    = esc_html__( 'Sent to waiting customers when a product they asked about is back in stock.', 'devdiggers-waitlist-for-woocommerce' );

			parent::__construct();
		}

		/**
		 * Shop button linking to the product.
		 *
		 * @return array
		 */
		public function get_button() {
			global $ddwcwl_waiting_list;

			return [
				'text' => $ddwcwl_waiting_list['email_button_text'],
				'url'  => $this->object->get_permalink(),
			];
		}
	}
}
