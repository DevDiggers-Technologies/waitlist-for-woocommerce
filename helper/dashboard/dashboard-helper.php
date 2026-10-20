<?php
/**
 * Dashboard data.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Helper\Dashboard;

use DDWCWaitingList\Helper\Subscribers\DDWCWL_Subscribers_Helper;
use DevDiggers\Framework\Includes\DDFW_Dashboard_Data;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Dashboard_Helper' ) ) {
	/**
	 * Dashboard helper class.
	 */
	class DDWCWL_Dashboard_Helper {
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
		 * All dashboard data for the selected date range.
		 *
		 * @return array
		 */
		public function get_dashboard_data() {
			$range    = DDFW_Dashboard_Data::get_date_range();
			$previous = $this->get_previous_range( $range['from'], $range['to'] );
			$current  = $this->get_range_totals( $range['from'], $range['to'] );
			$before   = $this->get_range_totals( $previous['from'], $previous['to'] );
			$counts   = $this->subscribers->get_status_counts();

			$summary = [
				'waiting'  => [
					'value'       => $counts['waiting'],
					'change'      => 0,
					'is_positive' => true,
				],
				'products' => [
					'value'       => count( $this->subscribers->get_products_with_waiting() ),
					'change'      => 0,
					'is_positive' => true,
				],
			];

			foreach ( $current as $key => $value ) {
				$summary[ $key ] = [
					'value'       => $value,
					'change'      => $this->get_change( $value, $before[ $key ] ),
					'is_positive' => $value >= $before[ $key ],
				];
			}

			return [
				'date_range'    => $range,
				'summary'       => $summary,
				'status_counts' => $counts,
				'trend'         => DDFW_Dashboard_Data::build_time_series(
					$range['from'],
					$range['to'],
					[
						'signups'  => $this->subscribers->get_daily_totals( 'created_at', $range['from'], $range['to'] ),
						'notified' => $this->subscribers->get_daily_totals( 'notified_at', $range['from'], $range['to'] ),
					]
				),
				'top_demanded'  => $this->get_top_demanded(),
				'opportunities' => $this->get_restock_opportunities(),
				'recent'        => $this->subscribers->get_recent( 6 ),
			];
		}

		/**
		 * Totals inside a range.
		 *
		 * @param string $from Y-m-d.
		 * @param string $to   Y-m-d.
		 * @return array
		 */
		protected function get_range_totals( $from, $to ) {
			return [
				'signups'  => (int) $this->subscribers->get_range_total( 'created_at', $from, $to ),
				'notified' => (int) $this->subscribers->get_range_total( 'notified_at', $from, $to ),
			];
		}

		/**
		 * Same-length range right before the given one.
		 *
		 * @param string $from Y-m-d.
		 * @param string $to   Y-m-d.
		 * @return array
		 */
		protected function get_previous_range( $from, $to ) {
			$start = strtotime( $from );
			$days  = max( 1, (int) round( ( strtotime( $to ) - $start ) / DAY_IN_SECONDS ) + 1 );

			return [
				'from' => gmdate( 'Y-m-d', $start - $days * DAY_IN_SECONDS ),
				'to'   => gmdate( 'Y-m-d', $start - DAY_IN_SECONDS ),
			];
		}

		/**
		 * Percent change.
		 *
		 * @param float $current  Current.
		 * @param float $previous Previous.
		 * @return float
		 */
		protected function get_change( $current, $previous ) {
			if ( $previous <= 0 ) {
				return $current > 0 ? 100 : 0;
			}

			return round( ( $current - $previous ) / $previous * 100, 1 );
		}

		/**
		 * Most wanted products.
		 *
		 * @return array
		 */
		protected function get_top_demanded() {
			$rows = [];

			foreach ( $this->subscribers->get_top_demanded( 5 ) as $row ) {
				$product = wc_get_product( $row->product_id );

				if ( $product ) {
					$rows[] = [
						'product' => $product,
						'waiting' => (int) $row->waiting,
					];
				}
			}

			return $rows;
		}

		/**
		 * In-stock products that still have waiting customers: sales waiting to be made.
		 *
		 * @return array
		 */
		protected function get_restock_opportunities() {
			$rows = [];

			// ponytail: loads products one by one until 5 hits; fine for hundreds of waitlisted products, join the stock lookup table if it grows to thousands.
			foreach ( $this->subscribers->get_products_with_waiting() as $product_id ) {
				$product = wc_get_product( $product_id );

				if ( $product && $product->is_in_stock() && 'onbackorder' !== $product->get_stock_status() ) {
					$rows[] = [
						'product' => $product,
						'waiting' => $this->subscribers->count_waiting( $product_id ),
					];
				}

				if ( count( $rows ) >= 5 ) {
					break;
				}
			}

			return $rows;
		}
	}
}
