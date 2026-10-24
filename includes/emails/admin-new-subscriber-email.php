<?php
/**
 * Admin alert sent when a customer joins a waiting list.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Includes\Emails;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Admin_New_Subscriber_Email' ) ) {
	/**
	 * Admin alert sent when a customer joins a waiting list.
	 */
	class DDWCWL_Admin_New_Subscriber_Email extends DDWCWL_Email_Base {
		/**
		 * Construct.
		 */
		public function __construct() {
			$this->id          = 'ddwcwl_admin_new_subscriber';
			$this->ddwcwl_type = 'admin_signup';
			$this->title       = esc_html__( 'Waiting List: New Subscriber (Admin)', 'devdiggers-waitlist-for-woocommerce' );
			$this->description = esc_html__( 'Sent to the store team when a customer joins a waiting list, with the current demand for that product.', 'devdiggers-waitlist-for-woocommerce' );

			parent::__construct();
		}

		/**
		 * Recipients from the Emails screen, falling back to the site admin.
		 *
		 * @param object      $subscriber Subscriber row.
		 * @param \WC_Product $product    Product.
		 * @return void
		 */
		protected function setup( $subscriber, $product ) {
			parent::setup( $subscriber, $product );

			$this->recipient = $this->get_setting( 'recipient' ) ? $this->get_setting( 'recipient' ) : get_option( 'admin_email' );
		}

		/**
		 * Link to the product's subscribers.
		 *
		 * @return array
		 */
		public function get_button() {
			return [
				'text' => esc_html__( 'View subscribers', 'devdiggers-waitlist-for-woocommerce' ),
				'url'  => admin_url( 'admin.php?page=' . DDWCWL_DASHBOARD_PAGE . '&menu=subscribers&product_id=' . $this->object->get_id() ),
			];
		}

		/**
		 * No unsubscribe link for the store team.
		 *
		 * @return array
		 */
		public function get_footer_link() {
			return [
				'text' => '',
				'url'  => '',
			];
		}
	}
}
