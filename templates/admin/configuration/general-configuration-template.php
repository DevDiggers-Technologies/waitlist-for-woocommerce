<?php
/**
 * General configuration template.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Templates\Admin\Configuration;

use DDWCWaitingList\Includes\DDWCWL_Admin_Dashboard;
use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_General_Configuration_Template' ) ) {
	/**
	 * General configuration template class.
	 */
	class DDWCWL_General_Configuration_Template {
		/**
		 * Construct.
		 */
		public function __construct() {
			global $ddwcwl_waiting_list;

			// The My Account endpoint lives on this tab, so rebuild rewrite rules after saving.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only flag set by the Settings API redirect.
			if ( ! empty( $_GET['settings-updated'] ) ) {
				flush_rewrite_rules();
			}

			$args = [
				[
					'header'            => [
						'heading'     => esc_html__( 'General', 'devdiggers-waitlist-for-woocommerce' ),
						'description' => esc_html__( 'Choose who can join a waiting list and which products accept signups. Out of stock products accept signups by default.', 'devdiggers-waitlist-for-woocommerce' ),
					],
					// Hidden empties so clearing every product/category actually saves an empty list.
					'after_header_html' => '<input type="hidden" name="_ddwcwl_excluded_products[]" value="" /><input type="hidden" name="_ddwcwl_excluded_categories[]" value="" />',
					'fields'            => [
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Enable/Disable', 'devdiggers-waitlist-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Show the waiting list on your store', 'devdiggers-waitlist-for-woocommerce' ),
							'description'    => esc_html__( 'When off, the signup form and My Account page are hidden. Subscribers and restock emails are kept.', 'devdiggers-waitlist-for-woocommerce' ),
							'id'             => 'ddwcwl-enabled',
							'value'          => $ddwcwl_waiting_list['enabled'],
						],
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Require Login', 'devdiggers-waitlist-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Only logged-in customers can join', 'devdiggers-waitlist-for-woocommerce' ),
							'description'    => esc_html__( 'Guests see a login button instead of the email field. Useful for B2B or member-only stores.', 'devdiggers-waitlist-for-woocommerce' ),
							'id'             => 'ddwcwl-require-login',
							'value'          => $ddwcwl_waiting_list['require_login'],
						],
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Backorder Products', 'devdiggers-waitlist-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Also show the form on products available on backorder', 'devdiggers-waitlist-for-woocommerce' ),
							'description'    => esc_html__( 'Customers who prefer not to backorder can wait until the product is physically in stock.', 'devdiggers-waitlist-for-woocommerce' ),
							'id'             => 'ddwcwl-include-backorders',
							'value'          => $ddwcwl_waiting_list['include_backorders'],
						],
						[
							'type'              => 'products',
							'label'             => esc_html__( 'Excluded Products', 'devdiggers-waitlist-for-woocommerce' ),
							'description'       => esc_html__( 'Products and variations that never show the waiting list form, for example discontinued items.', 'devdiggers-waitlist-for-woocommerce' ),
							'name'              => '_ddwcwl_excluded_products[]',
							'id'                => 'ddwcwl-excluded-products',
							'value'             => $ddwcwl_waiting_list['excluded_products'],
							'custom_attributes' => [ 'multiple' => 'multiple' ],
						],
						[
							'type'              => 'categories',
							'label'             => esc_html__( 'Excluded Categories', 'devdiggers-waitlist-for-woocommerce' ),
							'description'       => esc_html__( 'Every product in these categories is excluded from the waiting list.', 'devdiggers-waitlist-for-woocommerce' ),
							'name'              => '_ddwcwl_excluded_categories[]',
							'id'                => 'ddwcwl-excluded-categories',
							'value'             => $ddwcwl_waiting_list['excluded_categories'],
							'custom_attributes' => [ 'multiple' => 'multiple' ],
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'My Account Endpoint', 'devdiggers-waitlist-for-woocommerce' ),
							'description' => esc_html__( 'URL slug of the "My Waiting List" page in My Account. Default: waitlist.', 'devdiggers-waitlist-for-woocommerce' ),
							'id'          => 'ddwcwl-waiting-list-endpoint',
							'value'       => $ddwcwl_waiting_list['waiting_list_endpoint'],
						],
					],
				],
				[
					'header'            => [
						'heading'     => esc_html__( 'Data', 'devdiggers-waitlist-for-woocommerce' ),
						'description' => esc_html__( 'Keep the subscribers table lean and privacy friendly.', 'devdiggers-waitlist-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						DDWCWL_Admin_Dashboard::ddwcwl_get_upgrade_args(
							'data-retention',
							esc_html__( 'Clean up old entries automatically', 'devdiggers-waitlist-for-woocommerce' ),
							esc_html__( 'Pro deletes notified, purchased and unsubscribed entries after the number of days you set. Waiting customers are never touched.', 'devdiggers-waitlist-for-woocommerce' ),
							[
								esc_html__( 'Your own retention period in days', 'devdiggers-waitlist-for-woocommerce' ),
								esc_html__( 'Runs quietly in the background every hour', 'devdiggers-waitlist-for-woocommerce' ),
							]
						)
					),
				],
			];

			( new DDFW_Layout() )->get_form_section_layout( $args, 'ddwcwl-general-configuration-fields' );
		}
	}
}
