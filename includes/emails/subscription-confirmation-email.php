<?php
/**
 * Confirmation email sent when a customer joins a waiting list.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Includes\Emails;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Subscription_Confirmation_Email' ) ) {
	/**
	 * Confirmation email sent when a customer joins a waiting list.
	 */
	class DDWCWL_Subscription_Confirmation_Email extends DDWCWL_Email_Base {
		/**
		 * Construct.
		 */
		public function __construct() {
			$this->id             = 'ddwcwl_subscription_confirmation';
			$this->ddwcwl_type    = 'subscribed';
			$this->customer_email = true;
			$this->title          = esc_html__( 'Waiting List: Signup Confirmation', 'devdiggers-waitlist-for-woocommerce' );
			$this->description    = esc_html__( 'Sent right after a customer joins a waiting list, with their place in line and a one-click leave link.', 'devdiggers-waitlist-for-woocommerce' );

			parent::__construct();
		}

		/**
		 * Leave link wording.
		 *
		 * @return array
		 */
		public function get_footer_link() {
			$link         = parent::get_footer_link();
			$link['text'] = esc_html__( 'Changed your mind? Leave the waiting list', 'devdiggers-waitlist-for-woocommerce' );

			return $link;
		}
	}
}
