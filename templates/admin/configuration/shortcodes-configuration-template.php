<?php
/**
 * Shortcodes configuration template.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Shortcodes_Configuration_Template' ) ) {
	/**
	 * Shortcodes configuration template class.
	 */
	class DDWCWL_Shortcodes_Configuration_Template {
		/**
		 * Construct.
		 */
		public function __construct() {
			global $ddwcwl_waiting_list;

			$tag = str_replace( [ '[', ']', ' ' ], '', $ddwcwl_waiting_list['waiting_list_template_shortcode'] );

			$args = [
				[
					'header' => [
						'heading'     => esc_html__( 'Shortcode Settings', 'devdiggers-waitlist-for-woocommerce' ),
						'description' => esc_html__( 'Place the signup form anywhere: page builders, custom product templates or landing pages.', 'devdiggers-waitlist-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Form Shortcode', 'devdiggers-waitlist-for-woocommerce' ),
							'description' => esc_html__( 'Default: [ddwcwl_waiting_list_template_shortcode]. Change it only if it clashes with another plugin.', 'devdiggers-waitlist-for-woocommerce' ),
							'id'          => 'ddwcwl-waiting-list-template-shortcode',
							'value'       => $ddwcwl_waiting_list['waiting_list_template_shortcode'],
						],
						[
							'type'  => 'field_html',
							'label' => esc_html__( 'Usage', 'devdiggers-waitlist-for-woocommerce' ),
							'html'  => '<p><code>[' . esc_html( $tag ) . ']</code> ' . esc_html__( 'on a single product page, or', 'devdiggers-waitlist-for-woocommerce' ) . '</p><p><code>[' . esc_html( $tag ) . ' product_id="123"]</code> ' . esc_html__( 'anywhere else. The form only appears while the product is out of stock.', 'devdiggers-waitlist-for-woocommerce' ) . '</p>',
						],
					],
				],
			];

			( new DDFW_Layout() )->get_form_section_layout( $args, 'ddwcwl-shortcodes-configuration-fields' );
		}
	}
}
