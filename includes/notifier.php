<?php
/**
 * Restock notifier. Detects restocks, queues background batches and sends emails.
 *
 * @package DevDiggers Waitlist for WooCommerce
 */

namespace DDWCWaitingList\Includes;

use DDWCWaitingList\Helper\Subscribers\DDWCWL_Subscribers_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Notifier' ) ) {
	/**
	 * Notifier class.
	 */
	class DDWCWL_Notifier {
		/**
		 * Action Scheduler hook processing one restock batch.
		 *
		 * @var string
		 */
		const BATCH_HOOK = 'ddwcwl_process_restock';

		/**
		 * Emails sent per background batch, so large lists never hit mail server limits.
		 *
		 * @var int
		 */
		const BATCH_SIZE = 50;

		/**
		 * Subscribers helper.
		 *
		 * @var DDWCWL_Subscribers_Helper
		 */
		protected $subscribers;

		/**
		 * Construct.
		 */
		public function __construct() {
			$this->subscribers = new DDWCWL_Subscribers_Helper();
		}

		/**
		 * Whether a product is in stock and published, so its waiting list can be emailed.
		 *
		 * @param \WC_Product|false $product Product.
		 * @return bool
		 */
		public function is_restocked( $product ) {
			if ( ! $product || 'instock' !== $product->get_stock_status() || 'publish' !== get_post_status( $product->get_parent_id() ? $product->get_parent_id() : $product->get_id() ) ) {
				return false;
			}

			return ! $product->managing_stock() || (int) $product->get_stock_quantity() > 0;
		}

		/**
		 * Queue a background batch when a product with waiting customers is restocked.
		 *
		 * @param \WC_Product|int $product Product or ID.
		 * @param bool            $force   Manual "email all": skips the automatic-mail setting.
		 * @return bool Whether a batch was queued.
		 */
		public function maybe_queue( $product, $force = false ) {
			global $ddwcwl_waiting_list;

			$product = $product instanceof \WC_Product ? $product : wc_get_product( $product );

			if ( ! $product || ( ! $force && 'yes' !== $ddwcwl_waiting_list['mail_automatically'] ) ) {
				return false;
			}

			if ( ! $this->is_restocked( $product ) || ! $this->subscribers->count_waiting( $product->get_id() ) ) {
				return false;
			}

			if ( ! function_exists( 'as_enqueue_async_action' ) ) {
				$this->process_batch( $product->get_id() );
				return true;
			}

			$args = [ 'product_id' => $product->get_id() ];

			if ( ! as_has_scheduled_action( self::BATCH_HOOK, $args, 'ddwcwl' ) ) {
				as_enqueue_async_action( self::BATCH_HOOK, $args, 'ddwcwl' );
			}

			return true;
		}

		/**
		 * Send one batch, then queue the next while customers are still waiting and stock remains.
		 *
		 * @param int $product_id Product ID.
		 * @return void
		 */
		public function process_batch( $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! $this->is_restocked( $product ) ) {
				return;
			}

			$batch = $this->subscribers->get_waiting_batch( $product_id, self::BATCH_SIZE );

			foreach ( $batch as $subscriber ) {
				$this->notify( $subscriber, $product );
			}

			if ( count( $batch ) && $this->subscribers->count_waiting( $product_id ) && function_exists( 'as_enqueue_async_action' ) ) {
				as_enqueue_async_action( self::BATCH_HOOK, [ 'product_id' => absint( $product_id ) ], 'ddwcwl' );
			}
		}

		/**
		 * Email one subscriber and mark them notified.
		 *
		 * @param object           $subscriber Subscriber row.
		 * @param \WC_Product|null $product    Product.
		 * @return bool
		 */
		public function notify( $subscriber, $product = null ) {
			$product = $product ? $product : wc_get_product( $subscriber->product_id );

			if ( ! $product || ! in_array( $subscriber->status, [ 'waiting', 'notified' ], true ) ) {
				return false;
			}

			// Mark first, so a slow mailer or a parallel batch can never email the same person twice.
			$changes = [
				'status'      => 'notified',
				'notified_at' => current_time( 'mysql' ),
			];

			$this->subscribers->update( $subscriber->id, $changes );

			// Keep the row in memory in step, so the email and the ddwcwl_subscriber_notified listeners see its new status.
			foreach ( $changes as $key => $value ) {
				$subscriber->$key = $value;
			}

			$emails = WC()->mailer()->get_emails();

			if ( ! empty( $emails['DDWCWL_Back_In_Stock_Email'] ) ) {
				$emails['DDWCWL_Back_In_Stock_Email']->trigger( $subscriber, $product );
			}

			/**
			 * Fires after a waiting customer was emailed that the product is back.
			 *
			 * @since 1.0.0
			 *
			 * @param object      $subscriber Subscriber row.
			 * @param \WC_Product $product    Product.
			 */
			do_action( 'ddwcwl_subscriber_notified', $subscriber, $product );

			return true;
		}
	}
}
