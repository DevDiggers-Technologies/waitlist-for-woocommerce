<?php
/**
 * Products with waiting customers list table.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Templates\Admin\Products;

use DDWCWaitingList\Helper\Subscribers\DDWCWL_Subscribers_Helper;
use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'DDWCWL_Products_List_Template' ) ) {
	/**
	 * Products list table class.
	 */
	class DDWCWL_Products_List_Template extends \WP_List_Table {
		/**
		 * Construct.
		 */
		public function __construct() {
			parent::__construct(
				[
					'singular' => 'product',
					'plural'   => 'products',
					'ajax'     => false,
				]
			);
		}

		/**
		 * Prepare items.
		 *
		 * @return void
		 */
		public function prepare_items() {
			$per_page = $this->get_items_per_page( 'ddwcwl_products_per_page', 20 );

			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only list filters.
			$result = ( new DDWCWL_Subscribers_Helper() )->get_product_summaries(
				[
					'search'   => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
					'orderby'  => isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'waiting',
					'order'    => isset( $_GET['order'] ) ? sanitize_key( wp_unslash( $_GET['order'] ) ) : 'desc',
					'per_page' => $per_page,
					'page'     => $this->get_pagenum(),
				]
			);
			// phpcs:enable WordPress.Security.NonceVerification.Recommended

			$this->items           = $result['items'];
			$this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];

			$this->set_pagination_args(
				[
					'total_items' => $result['total'],
					'per_page'    => $per_page,
				]
			);
		}

		/**
		 * Columns.
		 *
		 * @return array
		 */
		public function get_columns() {
			return [
				'cb'          => '<input type="checkbox" />',
				'product'     => esc_html__( 'Product', 'devdiggers-waitlist-for-woocommerce' ),
				'stock'       => esc_html__( 'Stock', 'devdiggers-waitlist-for-woocommerce' ),
				'waiting'     => esc_html__( 'Waiting', 'devdiggers-waitlist-for-woocommerce' ),
				'notified'    => esc_html__( 'Notified', 'devdiggers-waitlist-for-woocommerce' ),
				'revenue'     => '<span class="ddfw-upgrade-to-pro-tag-wrapper ddwcwl-locked-column">' . esc_html__( 'Recovered Revenue', 'devdiggers-waitlist-for-woocommerce' ) . ' ' . ddfw_get_pro_tag() . '</span>',
				'last_signup' => esc_html__( 'Last Signup', 'devdiggers-waitlist-for-woocommerce' ),
			];
		}

		/**
		 * Sortable columns.
		 *
		 * @return array
		 */
		protected function get_sortable_columns() {
			return [
				'waiting'     => [ 'waiting', true ],
				'notified'    => [ 'notified', false ],
				'last_signup' => [ 'last_signup', false ],
			];
		}

		/**
		 * Bulk actions.
		 *
		 * @return array
		 */
		protected function get_bulk_actions() {
			return [
				'notify_all' => esc_html__( 'Email all waiting customers', 'devdiggers-waitlist-for-woocommerce' ),
				'clear'      => esc_html__( 'Clear waiting list', 'devdiggers-waitlist-for-woocommerce' ),
			];
		}

		/**
		 * Checkbox column.
		 *
		 * @param object $item Row.
		 * @return string
		 */
		protected function column_cb( $item ) {
			return sprintf( '<input type="checkbox" name="product[]" value="%d" />', absint( $item->product_id ) );
		}

		/**
		 * Row action URL.
		 *
		 * @param string $action     Action.
		 * @param int    $product_id Product ID.
		 * @return string
		 */
		protected function action_url( $action, $product_id ) {
			return wp_nonce_url(
				add_query_arg(
					[
						'page'          => DDWCWL_DASHBOARD_PAGE,
						'menu'          => 'products',
						'ddwcwl_action' => $action,
						'product'       => [ $product_id ],
					],
					admin_url( 'admin.php' )
				),
				'ddwcwl_list_action',
				'ddwcwl_nonce'
			);
		}

		/**
		 * Product column.
		 *
		 * @param object $item Row.
		 * @return string
		 */
		protected function column_product( $item ) {
			$product = wc_get_product( $item->product_id );

			if ( ! $product ) {
				return '<em>' . esc_html__( 'Product deleted', 'devdiggers-waitlist-for-woocommerce' ) . '</em>';
			}

			$subscribers_url = admin_url( 'admin.php?page=' . DDWCWL_DASHBOARD_PAGE . '&menu=subscribers&product_id=' . $product->get_id() );
			$actions         = [
				'view'   => '<a href="' . esc_url( $subscribers_url ) . '">' . esc_html__( 'View subscribers', 'devdiggers-waitlist-for-woocommerce' ) . '</a>',
				'edit'   => '<a href="' . esc_url( get_edit_post_link( $product->get_parent_id() ? $product->get_parent_id() : $product->get_id() ) ) . '">' . esc_html__( 'Edit product', 'devdiggers-waitlist-for-woocommerce' ) . '</a>',
				'notify' => '<a href="' . esc_url( $this->action_url( 'notify_all', $product->get_id() ) ) . '" class="ddwcwl-confirm-notify">' . esc_html__( 'Email waiting customers', 'devdiggers-waitlist-for-woocommerce' ) . '</a>',
				'clear'  => '<a href="' . esc_url( $this->action_url( 'clear', $product->get_id() ) ) . '" class="ddwcwl-confirm-delete submitdelete">' . esc_html__( 'Clear list', 'devdiggers-waitlist-for-woocommerce' ) . '</a>',
			];

			return sprintf(
				'<div class="ddwcwl-product-cell">%1$s<div><strong><a href="%2$s">%3$s</a></strong>%4$s</div></div>',
				wp_kses_post( $product->get_image( [ 40, 40 ] ) ),
				esc_url( $subscribers_url ),
				esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ),
				$this->row_actions( $actions )
			);
		}

		/**
		 * Stock column.
		 *
		 * @param object $item Row.
		 * @return string
		 */
		protected function column_stock( $item ) {
			$product = wc_get_product( $item->product_id );

			if ( ! $product ) {
				return '';
			}

			return $product->is_in_stock()
				? '<span class="ddwcwl-stock ddwcwl-instock">' . esc_html__( 'In stock', 'devdiggers-waitlist-for-woocommerce' ) . ( $product->managing_stock() ? ' (' . esc_html( wc_stock_amount( $product->get_stock_quantity() ) ) . ')' : '' ) . '</span>'
				: '<span class="ddwcwl-stock ddwcwl-outofstock">' . esc_html__( 'Out of stock', 'devdiggers-waitlist-for-woocommerce' ) . '</span>';
		}

		/**
		 * Count columns.
		 *
		 * @param object $item        Row.
		 * @param string $column_name Column.
		 * @return string
		 */
		protected function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'waiting':
					return '<strong>' . esc_html( number_format_i18n( $item->waiting ) ) . '</strong>';
				case 'notified':
					return esc_html( number_format_i18n( $item->notified ) );
				case 'revenue':
					return '<span aria-hidden="true">&ndash;</span>';
				case 'last_signup':
					return esc_html( date_i18n( get_option( 'date_format' ), strtotime( $item->last_signup ) ) );
			}

			return '';
		}

		/**
		 * Empty state.
		 *
		 * @return void
		 */
		public function no_items() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only search flag.
			$searching = ! empty( $_GET['s'] );

			ddfw_print_empty_state(
				[
					'title'       => $searching ? esc_html__( 'No matching products', 'devdiggers-waitlist-for-woocommerce' ) : esc_html__( 'No demand yet', 'devdiggers-waitlist-for-woocommerce' ),
					'description' => $searching ? esc_html__( 'No product with a waiting list matches your search.', 'devdiggers-waitlist-for-woocommerce' ) : esc_html__( 'Products show up here with their waiting customers as soon as someone joins a waiting list.', 'devdiggers-waitlist-for-woocommerce' ),
					'icon'        => DDFW_SVG::get_svg_icon(
						'package',
						true,
						[
							'size'         => 48,
							'stroke_width' => 1.5,
						]
					),
					'button_url'  => $searching ? admin_url( 'admin.php?page=' . DDWCWL_DASHBOARD_PAGE . '&menu=products' ) : admin_url( 'edit.php?post_type=product&stock_status=outofstock' ),
					'button_text' => $searching ? esc_html__( 'Show all products', 'devdiggers-waitlist-for-woocommerce' ) : esc_html__( 'View sold out products', 'devdiggers-waitlist-for-woocommerce' ),
				]
			);
		}
	}
}
