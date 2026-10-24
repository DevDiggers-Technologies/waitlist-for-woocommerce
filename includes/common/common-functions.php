<?php
/**
 * Common functions class
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Includes\Common;

use DDWCWaitingList\Helper\Subscribers\DDWCWL_Subscribers_Helper;
use DDWCWaitingList\Includes\DDWCWL_Notifier;
use DDWCWaitingList\Includes\Emails;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Common_Functions' ) ) {
	/**
	 * Common functions
	 */
	class DDWCWL_Common_Functions {
		/**
		 * Register the My Account endpoint.
		 *
		 * @return void
		 */
		public function ddwcwl_add_endpoints() {
			global $ddwcwl_waiting_list;

			add_rewrite_endpoint( $ddwcwl_waiting_list['waiting_list_endpoint'], EP_ROOT | EP_PAGES );
		}

		/**
		 * Icons for the admin menus, tabs and dashboard cards (Lucide, ISC license).
		 *
		 * @param array $icons Framework icons.
		 * @param array $args  size, stroke_color, stroke_width, fill.
		 * @return array
		 */
		public function ddwcwl_add_svg_icons( $icons, $args ) {
			$open  = '<svg xmlns="http://www.w3.org/2000/svg" width="' . esc_attr( $args['size'] ?? '24' ) . '" height="' . esc_attr( $args['size'] ?? '24' ) . '" viewBox="0 0 24 24" fill="' . esc_attr( $args['fill'] ?? 'none' ) . '" stroke="' . esc_attr( $args['stroke_color'] ?? 'currentColor' ) . '" stroke-width="' . esc_attr( $args['stroke_width'] ?? '2' ) . '" stroke-linecap="round" stroke-linejoin="round">';
			$paths = [
				'layout'        => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 8h10"/><path d="M7 12h10"/><path d="M7 16h5"/>',
				'restock'       => '<path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/>',
				'coupon'        => '<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="m9 15 6-6"/><path d="M9 9h.01"/><path d="M15 15h.01"/>',
				'webhook'       => '<path d="M18 16.98h-5.99c-1.1 0-1.95.94-2.48 1.9A4 4 0 0 1 2 17c.01-.7.2-1.4.57-2"/><path d="m6 17 3.13-5.78c.53-.97.1-2.18-.5-3.1a4 4 0 1 1 6.89-4.06"/><path d="m12 6 3.13 5.73C15.66 12.7 16.9 13 18 13a4 4 0 0 1 0 8"/>',
				'shortcodes'    => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
				'mail-check'    => '<path d="M22 13V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h8"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/><path d="m16 19 2 2 4-4"/>',
				'bell-ring'     => '<path d="M10.268 21a2 2 0 0 0 3.464 0"/><path d="M22 8c0-2.3-.8-4.3-2-6"/><path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"/><path d="M4 2C2.8 3.7 2 5.7 2 8"/>',
				'user-check'    => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/>',
				'mail-question' => '<path d="M22 10.5V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h12.5"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/><path d="M18 15.28c.2-.4.5-.8.9-1a2.1 2.1 0 0 1 2.6.4c.3.4.5.8.5 1.3 0 1.3-2 2-2 2"/><path d="M20 22v.01"/>',
				'store'         => '<path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2 2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"/>',
				'palette'       => '<circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>',
				'log'           => '<path d="M15 12h-5"/><path d="M15 8h-5"/><path d="M19 17V5a2 2 0 0 0-2-2H4"/><path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/>',
				'users'         => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
				'package'       => '<path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12"/><polyline points="3.29 7 12 12 20.71 7"/><path d="m7.5 4.27 9 5.15"/>',
			];

			foreach ( $paths as $name => $path ) {
				$icons[ $name ] = $open . $path . '</svg>';
			}

			return $icons;
		}

		/**
		 * Register email classes.
		 *
		 * @param array $email_classes Email classes.
		 * @return array
		 */
		public function ddwcwl_add_email_classes( $email_classes ) {
			$email_classes['DDWCWL_Back_In_Stock_Email']             = new Emails\DDWCWL_Back_In_Stock_Email();
			$email_classes['DDWCWL_Subscription_Confirmation_Email'] = new Emails\DDWCWL_Subscription_Confirmation_Email();
			$email_classes['DDWCWL_Admin_New_Subscriber_Email']      = new Emails\DDWCWL_Admin_New_Subscriber_Email();

			return $email_classes;
		}

		/**
		 * Stock status changed.
		 *
		 * @param int         $product_id   Product ID.
		 * @param string      $stock_status New status.
		 * @param \WC_Product $product      Product.
		 * @return void
		 */
		public function ddwcwl_handle_stock_status_change( $product_id, $stock_status, $product = null ) {
			if ( 'instock' === $stock_status ) {
				( new DDWCWL_Notifier() )->maybe_queue( $product ? $product : $product_id );
			}
		}

		/**
		 * Stock quantity changed, for products that track stock and go from 0 to a positive count.
		 *
		 * @param \WC_Product $product Product.
		 * @return void
		 */
		public function ddwcwl_handle_stock_change( $product ) {
			( new DDWCWL_Notifier() )->maybe_queue( $product );
		}

		/**
		 * Process one background batch.
		 *
		 * @param int $product_id Product ID.
		 * @return void
		 */
		public function ddwcwl_process_restock_batch( $product_id ) {
			( new DDWCWL_Notifier() )->process_batch( absint( $product_id ) );
		}

		/**
		 * After a signup: welcome the customer and alert the store team.
		 *
		 * @param int         $subscriber_id Subscriber ID.
		 * @param \WC_Product $product       Product.
		 * @return void
		 */
		public function ddwcwl_send_signup_emails( $subscriber_id, $product ) {
			$subscriber = ( new DDWCWL_Subscribers_Helper() )->get( $subscriber_id );
			$emails     = WC()->mailer()->get_emails();

			if ( ! $subscriber ) {
				return;
			}

			foreach ( [ 'DDWCWL_Subscription_Confirmation_Email', 'DDWCWL_Admin_New_Subscriber_Email' ] as $email_class ) {
				if ( ! empty( $emails[ $email_class ] ) ) {
					$emails[ $email_class ]->trigger( $subscriber, $product );
				}
			}
		}

		/**
		 * Remove subscribers when a product or variation is permanently deleted.
		 *
		 * @param int $post_id Post ID.
		 * @return void
		 */
		public function ddwcwl_delete_product_subscribers( $post_id ) {
			if ( in_array( get_post_type( $post_id ), [ 'product', 'product_variation' ], true ) ) {
				( new DDWCWL_Subscribers_Helper() )->delete_by_products( [ $post_id ] );
			}
		}

		/**
		 * Register the personal data exporter.
		 *
		 * @param array $exporters Exporters.
		 * @return array
		 */
		public function ddwcwl_register_privacy_exporter( $exporters ) {
			$exporters['ddwcwl'] = [
				'exporter_friendly_name' => esc_html__( 'Waiting List Subscriptions', 'devdiggers-waitlist-for-woocommerce' ),
				'callback'               => [ $this, 'ddwcwl_privacy_export' ],
			];

			return $exporters;
		}

		/**
		 * Register the personal data eraser.
		 *
		 * @param array $erasers Erasers.
		 * @return array
		 */
		public function ddwcwl_register_privacy_eraser( $erasers ) {
			$erasers['ddwcwl'] = [
				'eraser_friendly_name' => esc_html__( 'Waiting List Subscriptions', 'devdiggers-waitlist-for-woocommerce' ),
				'callback'             => [ $this, 'ddwcwl_privacy_erase' ],
			];

			return $erasers;
		}

		/**
		 * Export personal data.
		 *
		 * @param string $email Email.
		 * @return array
		 */
		public function ddwcwl_privacy_export( $email ) {
			$labels = DDWCWL_Subscribers_Helper::get_status_labels();
			$data   = [];

			foreach ( ( new DDWCWL_Subscribers_Helper() )->get_all_for_email( $email ) as $row ) {
				$data[] = [
					'group_id'    => 'ddwcwl-subscriptions',
					'group_label' => esc_html__( 'Waiting List Subscriptions', 'devdiggers-waitlist-for-woocommerce' ),
					'item_id'     => 'ddwcwl-' . $row->id,
					'data'        => [
						[
							'name'  => esc_html__( 'Product', 'devdiggers-waitlist-for-woocommerce' ),
							'value' => get_the_title( $row->product_id ),
						],
						[
							'name'  => esc_html__( 'Status', 'devdiggers-waitlist-for-woocommerce' ),
							'value' => $labels[ $row->status ] ?? $row->status,
						],
						[
							'name'  => esc_html__( 'Joined On', 'devdiggers-waitlist-for-woocommerce' ),
							'value' => $row->created_at,
						],
					],
				];
			}

			return [
				'data' => $data,
				'done' => true,
			];
		}

		/**
		 * Erase personal data.
		 *
		 * @param string $email Email.
		 * @return array
		 */
		public function ddwcwl_privacy_erase( $email ) {
			$removed = ( new DDWCWL_Subscribers_Helper() )->delete_for_email( $email );

			return [
				'items_removed'  => $removed > 0,
				'items_retained' => false,
				'messages'       => [],
				'done'           => true,
			];
		}
	}
}
