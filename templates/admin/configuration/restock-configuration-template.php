<?php
/**
 * Restock rules configuration template.
 *
 * @package DevDiggers Waitlist for WooCommerce
 */

namespace DDWCWaitingList\Templates\Admin\Configuration;

use DDWCWaitingList\Includes\DDWCWL_Admin_Dashboard;
use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Restock_Configuration_Template' ) ) {
	/**
	 * Restock rules configuration template class.
	 */
	class DDWCWL_Restock_Configuration_Template {
		/**
		 * Construct.
		 */
		public function __construct() {
			global $ddwcwl_waiting_list;

			$locked = function ( $key, $label, $description, $value ) {
				return ddfw_locked_field(
					[
						'type'              => 'number',
						'label'             => $label,
						'description'       => $description,
						'id'                => str_replace( '_', '-', $key ),
						'value'             => $value,
						'custom_attributes' => [ 'step' => '1' ],
					],
					'ddwcwl'
				);
			};

			$args = [
				[
					'header' => [
						'heading'     => esc_html__( 'Restock Emails', 'devdiggers-waitlist-for-woocommerce' ),
						'description' => esc_html__( 'When stock returns, waiting customers are emailed in the background in batches of 50, first come first served. Works for every stock change: product edits, imports, the REST API, refunds and cancelled orders.', 'devdiggers-waitlist-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Automatic Emails', 'devdiggers-waitlist-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Email waiting customers automatically when stock returns', 'devdiggers-waitlist-for-woocommerce' ),
							'description'    => esc_html__( 'Turn off to send restock emails yourself from the Products screen.', 'devdiggers-waitlist-for-woocommerce' ),
							'id'             => 'ddwcwl-mail-automatically',
							'value'          => $ddwcwl_waiting_list['mail_automatically'],
						],
						$locked( 'min_stock_threshold', esc_html__( 'Minimum Stock', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Wait until at least this many units are available before emailing anyone.', 'devdiggers-waitlist-for-woocommerce' ), 1 ),
						$locked( 'notify_ratio', esc_html__( 'Customers per Unit', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Email only this many customers for every unit that comes back, so 3 units never trigger 200 emails. The rest stay in line for the next restock.', 'devdiggers-waitlist-for-woocommerce' ), 0 ),
						$locked( 'batch_size', esc_html__( 'Emails per Batch', 'devdiggers-waitlist-for-woocommerce' ), esc_html__( 'Tune the background batch size to your mail server limits.', 'devdiggers-waitlist-for-woocommerce' ), 50 ),
					],
				],
				[
					'header'            => [
						'heading'     => esc_html__( 'Early Access Window', 'devdiggers-waitlist-for-woocommerce' ),
						'description' => esc_html__( 'Reward the people who waited.', 'devdiggers-waitlist-for-woocommerce' ),
					],
					'after_header_html' => ddfw_get_upgrade_to_pro_section(
						DDWCWL_Admin_Dashboard::ddwcwl_get_upgrade_args(
							'early-access',
							esc_html__( 'Give your waiting list first dibs on every restock', 'devdiggers-waitlist-for-woocommerce' ),
							esc_html__( 'Pro reserves the product for notified customers for the hours you choose. Everyone else can still join the waiting list, then the product opens to all automatically.', 'devdiggers-waitlist-for-woocommerce' ),
							[
								esc_html__( 'Only customers who got the email can buy during the window', 'devdiggers-waitlist-for-woocommerce' ),
								esc_html__( 'A clear "Reserved until" notice on the product page', 'devdiggers-waitlist-for-woocommerce' ),
								esc_html__( 'Opens to everyone on its own when the window ends', 'devdiggers-waitlist-for-woocommerce' ),
							]
						)
					),
				],
			];

			( new DDFW_Layout() )->get_form_section_layout( $args, 'ddwcwl-restock-configuration-fields' );
		}
	}
}
