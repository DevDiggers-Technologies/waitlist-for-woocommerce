<?php
/**
 * Shared email settings: brand button text and color.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Templates\Admin\Emails;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Email_Settings_Template' ) ) {
	/**
	 * Email settings template class.
	 */
	class DDWCWL_Email_Settings_Template {
		/**
		 * Construct.
		 */
		public function __construct() {
			global $ddwcwl_waiting_list;

			$args = [
				[
					'header' => [
						'heading'     => esc_html__( 'Email Settings', 'devdiggers-waitlist-for-woocommerce' ),
						/* translators: %s: link to WooCommerce email settings. */
						'description' => sprintf( esc_html__( 'Applies to every waiting list email. Logo, colors, footer and sender name come from your %s, so these emails match the rest of your store.', 'devdiggers-waitlist-for-woocommerce' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=email' ) ) . '">' . esc_html__( 'WooCommerce email settings', 'devdiggers-waitlist-for-woocommerce' ) . '</a>' ),
					],
					'fields' => [
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Button Text', 'devdiggers-waitlist-for-woocommerce' ),
							'description' => esc_html__( 'Call to action in the back-in-stock email. Default: Shop Now.', 'devdiggers-waitlist-for-woocommerce' ),
							'id'          => 'ddwcwl-email-button-text',
							'value'       => $ddwcwl_waiting_list['email_button_text'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Accent Color', 'devdiggers-waitlist-for-woocommerce' ),
							'description' => esc_html__( 'Brand color of the email button.', 'devdiggers-waitlist-for-woocommerce' ),
							'id'          => 'ddwcwl-email-button-color',
							'value'       => $ddwcwl_waiting_list['email_button_color'],
						],
					],
				],
			];

			( new DDFW_Layout() )->get_form_section_layout( $args, 'ddwcwl-email-settings-fields' );
		}
	}
}
