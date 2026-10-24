<?php
/**
 * Subscribers helper. Owns every read/write on the subscribers table.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Helper\Subscribers;

use DDWCWaitingList\Includes\DDWCWL_Install;

defined( 'ABSPATH' ) || exit();

// phpcs:disable WordPress.DB.DirectDatabaseQuery -- Custom table; every query is prepared and results are request-scoped.
// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name and whitelisted ORDER BY are interpolated, values are prepared.
// phpcs:disable WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- IN () placeholders are generated from the ID count.

if ( ! class_exists( 'DDWCWL_Subscribers_Helper' ) ) {
	/**
	 * Subscribers helper class.
	 */
	class DDWCWL_Subscribers_Helper {
		/**
		 * Subscriber statuses.
		 *
		 * @var string[]
		 */
		const STATUSES = [ 'waiting', 'notified', 'unsubscribed' ];

		/**
		 * Table name.
		 *
		 * @var string
		 */
		protected $table;

		/**
		 * Construct.
		 */
		public function __construct() {
			$this->table = DDWCWL_Install::get_table_name();
		}

		/**
		 * Translated status labels.
		 *
		 * @return array
		 */
		public static function get_status_labels() {
			return [
				'waiting'      => esc_html__( 'Waiting', 'devdiggers-waitlist-for-woocommerce' ),
				'notified'     => esc_html__( 'Notified', 'devdiggers-waitlist-for-woocommerce' ),
				'unsubscribed' => esc_html__( 'Unsubscribed', 'devdiggers-waitlist-for-woocommerce' ),
			];
		}

		/**
		 * Public one-click unsubscribe URL for an email footer.
		 *
		 * @param object $subscriber Subscriber row.
		 * @return string
		 */
		public static function get_unsubscribe_url( $subscriber ) {
			return add_query_arg(
				[
					'ddwcwl_action' => 'unsubscribe',
					'ddwcwl_token'  => $subscriber->token,
				],
				home_url( '/' )
			);
		}

		/**
		 * Get a subscriber by ID.
		 *
		 * @param int $id Subscriber ID.
		 * @return object|null
		 */
		public function get( $id ) {
			global $wpdb;

			return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id ) );
		}

		/**
		 * Get a subscriber by its email token.
		 *
		 * @param string $token Token.
		 * @return object|null
		 */
		public function get_by_token( $token ) {
			global $wpdb;

			if ( empty( $token ) || ! preg_match( '/^[A-Za-z0-9]{20,64}$/', $token ) ) {
				return null;
			}

			return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE token = %s", $token ) );
		}

		/**
		 * Get a subscriber for a product/email pair.
		 *
		 * @param int    $product_id Product ID.
		 * @param string $email      Email.
		 * @return object|null
		 */
		public function get_by_product_email( $product_id, $email ) {
			global $wpdb;

			return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE product_id = %d AND email = %s", $product_id, $email ) );
		}

		/**
		 * Add an email to a product waiting list. A previously notified or unsubscribed
		 * entry is reset to waiting, so returning customers can join again.
		 *
		 * @param int    $product_id Product or variation ID.
		 * @param string $email      Email.
		 * @param int    $user_id    User ID, 0 for guests.
		 * @return array { status: added|exists|invalid, id: int }
		 */
		public function add( $product_id, $email, $user_id = 0 ) {
			global $wpdb;

			$email   = strtolower( sanitize_email( $email ) );
			$product = wc_get_product( $product_id );

			if ( ! is_email( $email ) || ! $product ) {
				return [
					'status' => 'invalid',
					'id'     => 0,
				];
			}

			$existing = $this->get_by_product_email( $product->get_id(), $email );

			if ( $existing && 'waiting' === $existing->status ) {
				return [
					'status' => 'exists',
					'id'     => (int) $existing->id,
				];
			}

			$data = [
				'product_id'  => $product->get_id(),
				'parent_id'   => $product->get_parent_id() ? $product->get_parent_id() : $product->get_id(),
				'email'       => $email,
				'user_id'     => absint( $user_id ),
				'status'      => 'waiting',
				'token'       => wp_generate_password( 32, false ),
				'created_at'  => current_time( 'mysql' ),
				'notified_at' => null,
			];

			if ( $existing ) {
				$wpdb->update( $this->table, $data, [ 'id' => $existing->id ] );
				$id = (int) $existing->id;
			} else {
				$wpdb->insert( $this->table, $data );
				$id = (int) $wpdb->insert_id;
			}

			/**
			 * Fires after a customer joined a waiting list.
			 *
			 * @since 1.0.0
			 *
			 * @param int         $id      Subscriber ID.
			 * @param \WC_Product $product Product.
			 * @param string      $email   Email.
			 */
			do_action( 'ddwcwl_subscriber_added', $id, $product, $email );

			return [
				'status' => 'added',
				'id'     => $id,
			];
		}

		/**
		 * Unsubscribe an open entry.
		 *
		 * @param object $subscriber Subscriber row.
		 * @return bool
		 */
		public function unsubscribe( $subscriber ) {
			if ( ! $subscriber || ! in_array( $subscriber->status, [ 'waiting', 'notified' ], true ) ) {
				return false;
			}

			return $this->update( $subscriber->id, [ 'status' => 'unsubscribed' ] );
		}

		/**
		 * Place in line among waiting customers for the same product, first come first served.
		 *
		 * @param object $subscriber Subscriber row.
		 * @return int 1-based position, 0 when not waiting.
		 */
		public function get_queue_position( $subscriber ) {
			global $wpdb;

			if ( ! $subscriber || 'waiting' !== $subscriber->status ) {
				return 0;
			}

			$ahead = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$this->table} WHERE product_id = %d AND status = 'waiting' AND ( created_at < %s OR ( created_at = %s AND id < %d ) )", $subscriber->product_id, $subscriber->created_at, $subscriber->created_at, $subscriber->id ) );

			return $ahead + 1;
		}

		/**
		 * Update a subscriber.
		 *
		 * @param int   $id   Subscriber ID.
		 * @param array $data Columns.
		 * @return bool
		 */
		public function update( $id, $data ) {
			global $wpdb;

			return false !== $wpdb->update( $this->table, $data, [ 'id' => absint( $id ) ] );
		}

		/**
		 * Delete subscribers.
		 *
		 * @param int[] $ids Subscriber IDs.
		 * @return int Rows deleted.
		 */
		public function delete( $ids ) {
			global $wpdb;

			$ids = array_filter( array_map( 'absint', (array) $ids ) );

			if ( empty( $ids ) ) {
				return 0;
			}

			$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

			return (int) $wpdb->query( $wpdb->prepare( "DELETE FROM {$this->table} WHERE id IN ({$placeholders})", $ids ) );
		}

		/**
		 * Delete every subscriber for products (parent or variation IDs).
		 *
		 * @param int[] $product_ids Product IDs.
		 * @return int Rows deleted.
		 */
		public function delete_by_products( $product_ids ) {
			global $wpdb;

			$ids = array_filter( array_map( 'absint', (array) $product_ids ) );

			if ( empty( $ids ) ) {
				return 0;
			}

			$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

			return (int) $wpdb->query( $wpdb->prepare( "DELETE FROM {$this->table} WHERE product_id IN ({$placeholders})", $ids ) );
		}

		/**
		 * Build the WHERE clause for list queries.
		 *
		 * @param array $args Query args: status, product_id, parent_id, email, user_id, search.
		 * @return string Prepared SQL fragment starting with WHERE.
		 */
		protected function build_where( $args ) {
			global $wpdb;

			$where = [ '1=1' ];

			if ( ! empty( $args['status'] ) && in_array( $args['status'], self::STATUSES, true ) ) {
				$where[] = $wpdb->prepare( 's.status = %s', $args['status'] );
			}

			if ( ! empty( $args['product_id'] ) ) {
				$where[] = $wpdb->prepare( '(s.product_id = %d OR s.parent_id = %d)', $args['product_id'], $args['product_id'] );
			}

			if ( ! empty( $args['email'] ) ) {
				$where[] = $wpdb->prepare( 's.email = %s', strtolower( $args['email'] ) );
			}

			if ( ! empty( $args['search'] ) ) {
				$like    = '%' . $wpdb->esc_like( $args['search'] ) . '%';
				$where[] = $wpdb->prepare( '(s.email LIKE %s OR p.post_title LIKE %s)', $like, $like );
			}

			return 'WHERE ' . implode( ' AND ', $where );
		}

		/**
		 * Query subscribers.
		 *
		 * @param array $args Query args, plus per_page, page, orderby, order.
		 * @return array { items: object[], total: int }
		 */
		public function query( $args = [] ) {
			global $wpdb;

			$args = wp_parse_args(
				$args,
				[
					'per_page' => 20,
					'page'     => 1,
					'orderby'  => 'created_at',
					'order'    => 'DESC',
				]
			);

			$orderby  = in_array( $args['orderby'], [ 'id', 'email', 'status', 'created_at', 'notified_at' ], true ) ? $args['orderby'] : 'created_at';
			$order    = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';
			$where    = $this->build_where( $args );
			$join     = "LEFT JOIN {$wpdb->posts} p ON p.ID = s.parent_id";
			$per_page = max( 1, absint( $args['per_page'] ) );
			$offset   = ( max( 1, absint( $args['page'] ) ) - 1 ) * $per_page;

			$items = $wpdb->get_results( $wpdb->prepare( "SELECT s.* FROM {$this->table} s {$join} {$where} ORDER BY s.{$orderby} {$order}, s.id DESC LIMIT %d OFFSET %d", $per_page, $offset ) ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter -- WHERE is built from $wpdb->prepare() fragments; column names come from a fixed whitelist.
			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table} s {$join} {$where}" ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter -- WHERE is built from $wpdb->prepare() fragments; column names come from a fixed whitelist.

			return [
				'items' => $items,
				'total' => $total,
			];
		}

		/**
		 * Count subscribers per status for the given filters.
		 *
		 * @param array $args Query args (status is ignored).
		 * @return array status => count, plus "all".
		 */
		public function get_status_counts( $args = [] ) {
			global $wpdb;

			unset( $args['status'] );

			$where  = $this->build_where( $args );
			$rows   = $wpdb->get_results( "SELECT s.status, COUNT(*) AS total FROM {$this->table} s LEFT JOIN {$wpdb->posts} p ON p.ID = s.parent_id {$where} GROUP BY s.status" ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter -- WHERE is built from $wpdb->prepare() fragments; column names come from a fixed whitelist.
			$counts = array_fill_keys( self::STATUSES, 0 );
			$all    = 0;

			foreach ( $rows as $row ) {
				$counts[ $row->status ] = (int) $row->total;
				$all                   += (int) $row->total;
			}

			$counts['all'] = $all;

			return $counts;
		}

		/**
		 * Per-product totals for the Products screen.
		 *
		 * @param array $args search, per_page, page, orderby, order.
		 * @return array { items: object[], total: int }
		 */
		public function get_product_summaries( $args = [] ) {
			global $wpdb;

			$args = wp_parse_args(
				$args,
				[
					'search'   => '',
					'per_page' => 20,
					'page'     => 1,
					'orderby'  => 'waiting',
					'order'    => 'DESC',
				]
			);

			$orderby  = in_array( $args['orderby'], [ 'waiting', 'notified', 'total', 'last_signup' ], true ) ? $args['orderby'] : 'waiting';
			$order    = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';
			$where    = $this->build_where( [ 'search' => $args['search'] ] );
			$per_page = max( 1, absint( $args['per_page'] ) );
			$offset   = ( max( 1, absint( $args['page'] ) ) - 1 ) * $per_page;

			$items = $wpdb->get_results( // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter -- WHERE is built from $wpdb->prepare() fragments; column names come from a fixed whitelist.
				$wpdb->prepare(
					"SELECT s.product_id,
						SUM(s.status = 'waiting') AS waiting,
						SUM(s.status = 'notified') AS notified,
						COUNT(*) AS total,
						MAX(s.created_at) AS last_signup
					FROM {$this->table} s LEFT JOIN {$wpdb->posts} p ON p.ID = s.parent_id {$where}
					GROUP BY s.product_id ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
					$per_page,
					$offset
				)
			);

			$total = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT s.product_id) FROM {$this->table} s LEFT JOIN {$wpdb->posts} p ON p.ID = s.parent_id {$where}" ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter -- WHERE is built from $wpdb->prepare() fragments; column names come from a fixed whitelist.

			return [
				'items' => $items,
				'total' => $total,
			];
		}

		/**
		 * Waiting subscribers for a product, oldest first (fair queue).
		 *
		 * @param int $product_id Product ID.
		 * @param int $limit      Batch size.
		 * @return object[]
		 */
		public function get_waiting_batch( $product_id, $limit ) {
			global $wpdb;

			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE product_id = %d AND status = 'waiting' ORDER BY created_at ASC, id ASC LIMIT %d", $product_id, max( 1, absint( $limit ) ) ) );
		}

		/**
		 * Count waiting subscribers for a product or variation.
		 *
		 * @param int $product_id Product ID.
		 * @return int
		 */
		public function count_waiting( $product_id ) {
			global $wpdb;

			return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$this->table} WHERE product_id = %d AND status = 'waiting'", $product_id ) );
		}

		/**
		 * Waiting counts grouped by parent product, for list columns. One query per screen.
		 *
		 * @param int[] $parent_ids Parent product IDs.
		 * @return array parent_id => count
		 */
		public function count_waiting_by_parents( $parent_ids ) {
			global $wpdb;

			$ids = array_filter( array_map( 'absint', (array) $parent_ids ) );

			if ( empty( $ids ) ) {
				return [];
			}

			$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
			$rows         = $wpdb->get_results( $wpdb->prepare( "SELECT parent_id, COUNT(*) AS total FROM {$this->table} WHERE status = 'waiting' AND parent_id IN ({$placeholders}) GROUP BY parent_id", $ids ) );

			return wp_list_pluck( $rows, 'total', 'parent_id' );
		}

		/**
		 * Product IDs that have waiting subscribers.
		 *
		 * @return int[]
		 */
		public function get_products_with_waiting() {
			global $wpdb;

			return array_map( 'absint', $wpdb->get_col( "SELECT DISTINCT product_id FROM {$this->table} WHERE status = 'waiting'" ) );
		}

		/**
		 * Get every row for an email, used by the privacy exporter/eraser.
		 *
		 * @param string $email Email.
		 * @return object[]
		 */
		public function get_all_for_email( $email ) {
			global $wpdb;

			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE email = %s ORDER BY id ASC", strtolower( $email ) ) );
		}

		/**
		 * Delete every row for an email.
		 *
		 * @param string $email Email.
		 * @return int Rows deleted.
		 */
		public function delete_for_email( $email ) {
			global $wpdb;

			return (int) $wpdb->delete( $this->table, [ 'email' => strtolower( $email ) ] );
		}

		/**
		 * Count rows grouped by local day for a date column within a range.
		 *
		 * @param string $column Date column: created_at or notified_at.
		 * @param string $from   Y-m-d.
		 * @param string $to     Y-m-d.
		 * @return array Y-m-d => value
		 */
		public function get_daily_totals( $column, $from, $to ) {
			global $wpdb;

			if ( ! in_array( $column, [ 'created_at', 'notified_at' ], true ) ) {
				return [];
			}

			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT DATE({$column}) AS day, COUNT(*) AS total FROM {$this->table} WHERE {$column} BETWEEN %s AND %s GROUP BY day", $from . ' 00:00:00', $to . ' 23:59:59' ) ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter -- WHERE is built from $wpdb->prepare() fragments; column names come from a fixed whitelist.

			return wp_list_pluck( $rows, 'total', 'day' );
		}

		/**
		 * Total rows with a date column inside a range.
		 *
		 * @param string $column Date column.
		 * @param string $from   Y-m-d.
		 * @param string $to     Y-m-d.
		 * @return int
		 */
		public function get_range_total( $column, $from, $to ) {
			return (int) array_sum( $this->get_daily_totals( $column, $from, $to ) );
		}

		/**
		 * Products with the most waiting customers.
		 *
		 * @param int $limit Limit.
		 * @return object[]
		 */
		public function get_top_demanded( $limit = 5 ) {
			global $wpdb;

			return $wpdb->get_results( $wpdb->prepare( "SELECT product_id, COUNT(*) AS waiting FROM {$this->table} WHERE status = 'waiting' GROUP BY product_id ORDER BY waiting DESC LIMIT %d", $limit ) );
		}

		/**
		 * Latest signups.
		 *
		 * @param int $limit Limit.
		 * @return object[]
		 */
		public function get_recent( $limit = 6 ) {
			global $wpdb;

			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table} ORDER BY created_at DESC, id DESC LIMIT %d", $limit ) );
		}
	}
}
