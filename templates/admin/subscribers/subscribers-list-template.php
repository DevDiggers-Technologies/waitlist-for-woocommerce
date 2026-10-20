<?php
/**
 * Subscribers list table.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Templates\Admin\Subscribers;

use DDWCWaitingList\Helper\Subscribers\DDWCWL_Subscribers_Helper;
use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'DDWCWL_Subscribers_List_Template' ) ) {
	/**
	 * Subscribers list table class.
	 */
	class DDWCWL_Subscribers_List_Template extends \WP_List_Table {
		/**
		 * Subscribers helper.
		 *
		 * @var DDWCWL_Subscribers_Helper
		 */
		protected $helper;

		/**
		 * Current filters.
		 *
		 * @var array
		 */
		protected $filters;

		/**
		 * Status labels.
		 *
		 * @var array
		 */
		protected $labels;

		/**
		 * Construct.
		 */
		public function __construct() {
			parent::__construct(
				[
					'singular' => 'subscriber',
					'plural'   => 'subscribers',
					'ajax'     => false,
				]
			);

			$this->helper = new DDWCWL_Subscribers_Helper();
			$this->labels = DDWCWL_Subscribers_Helper::get_status_labels();

			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only list filters.
			$this->filters = [
				'status'     => isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '',
				'product_id' => isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0,
				'search'     => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
				'orderby'    => isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'created_at',
				'order'      => isset( $_GET['order'] ) ? sanitize_key( wp_unslash( $_GET['order'] ) ) : 'desc',
			];
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Prepare items.
		 *
		 * @return void
		 */
		public function prepare_items() {
			$per_page = $this->get_items_per_page( 'ddwcwl_subscribers_per_page', 20 );
			$result   = $this->helper->query(
				array_merge(
					$this->filters,
					[
						'per_page' => $per_page,
						'page'     => $this->get_pagenum(),
					]
				)
			);

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
				'cb'         => '<input type="checkbox" />',
				'email'      => esc_html__( 'Customer', 'devdiggers-waitlist-for-woocommerce' ),
				'product'    => esc_html__( 'Product', 'devdiggers-waitlist-for-woocommerce' ),
				'status'     => esc_html__( 'Status', 'devdiggers-waitlist-for-woocommerce' ),
				'created_at' => esc_html__( 'Joined', 'devdiggers-waitlist-for-woocommerce' ),
				'notified'   => esc_html__( 'Notified', 'devdiggers-waitlist-for-woocommerce' ),
			];
		}

		/**
		 * Sortable columns.
		 *
		 * @return array
		 */
		protected function get_sortable_columns() {
			return [
				'email'      => [ 'email', false ],
				'status'     => [ 'status', false ],
				'created_at' => [ 'created_at', true ],
				'notified'   => [ 'notified_at', false ],
			];
		}

		/**
		 * Status views with counts.
		 *
		 * @return array
		 */
		protected function get_views() {
			$counts = $this->helper->get_status_counts( $this->filters );
			$base   = remove_query_arg( [ 'status', 'paged', 'ddwcwl_message', 'ddwcwl_done' ] );
			$views  = [];

			foreach ( array_merge( [ 'all' => esc_html__( 'All', 'devdiggers-waitlist-for-woocommerce' ) ], $this->labels ) as $status => $label ) {
				$current          = ( 'all' === $status && ! $this->filters['status'] ) || $status === $this->filters['status'];
				$views[ $status ] = sprintf(
					'<a href="%1$s"%2$s>%3$s <span class="count">(%4$s)</span></a>',
					esc_url( 'all' === $status ? $base : add_query_arg( 'status', $status, $base ) ),
					$current ? ' class="current" aria-current="page"' : '',
					esc_html( $label ),
					esc_html( number_format_i18n( $counts[ $status ] ) )
				);
			}

			return $views;
		}

		/**
		 * Print status views as the framework segmented control, without WordPress' " |" separators.
		 *
		 * @return void
		 */
		public function views() {
			echo '<ul class="subsubsub">';

			foreach ( $this->get_views() as $status => $view ) {
				echo '<li class="' . esc_attr( $status ) . '">' . wp_kses_post( $view ) . '</li>';
			}

			echo '</ul>';
		}

		/**
		 * Bulk actions.
		 *
		 * @return array
		 */
		protected function get_bulk_actions() {
			return [
				'notify' => esc_html__( 'Send back-in-stock email', 'devdiggers-waitlist-for-woocommerce' ),
				'reset'  => esc_html__( 'Move back to waiting', 'devdiggers-waitlist-for-woocommerce' ),
				'delete' => esc_html__( 'Delete', 'devdiggers-waitlist-for-woocommerce' ),
			];
		}

		/**
		 * Product filter shown above the table.
		 *
		 * @param string $which top|bottom.
		 * @return void
		 */
		protected function extra_tablenav( $which ) {
			if ( 'top' !== $which || ! $this->filters['product_id'] ) {
				return;
			}

			$product = wc_get_product( $this->filters['product_id'] );
			?>
			<div class="alignleft actions ddwcwl-active-filter">
				<?php
				/* translators: %s: product name. */
				printf( esc_html__( 'Showing subscribers for %s', 'devdiggers-waitlist-for-woocommerce' ), '<strong>' . esc_html( $product ? wp_strip_all_tags( $product->get_formatted_name() ) : '#' . $this->filters['product_id'] ) . '</strong>' );
				?>
				<a class="button" href="<?php echo esc_url( remove_query_arg( [ 'product_id', 'paged' ] ) ); ?>"><?php esc_html_e( 'Clear filter', 'devdiggers-waitlist-for-woocommerce' ); ?></a>
			</div>
			<?php
		}

		/**
		 * Checkbox column.
		 *
		 * @param object $item Row.
		 * @return string
		 */
		protected function column_cb( $item ) {
			return sprintf( '<input type="checkbox" name="subscriber[]" value="%d" />', absint( $item->id ) );
		}

		/**
		 * Row action URL.
		 *
		 * @param string $action Action.
		 * @param int    $id     Subscriber ID.
		 * @return string
		 */
		protected function action_url( $action, $id ) {
			return wp_nonce_url(
				add_query_arg(
					[
						'page'          => DDWCWL_DASHBOARD_PAGE,
						'menu'          => 'subscribers',
						'ddwcwl_action' => $action,
						'subscriber'    => [ $id ],
					],
					admin_url( 'admin.php' )
				),
				'ddwcwl_list_action',
				'ddwcwl_nonce'
			);
		}

		/**
		 * Customer column with row actions.
		 *
		 * @param object $item Row.
		 * @return string
		 */
		protected function column_email( $item ) {
			$user = $item->user_id ? get_userdata( $item->user_id ) : false;
			$meta = $user
				? '<a href="' . esc_url( get_edit_user_link( $user->ID ) ) . '">' . esc_html( $user->display_name ) . '</a>'
				: '<span class="ddwcwl-guest">' . esc_html__( 'Guest', 'devdiggers-waitlist-for-woocommerce' ) . '</span>';

			$actions = [];

			if ( in_array( $item->status, [ 'waiting', 'notified' ], true ) ) {
				$actions['notify'] = '<a href="' . esc_url( $this->action_url( 'notify', $item->id ) ) . '" class="ddwcwl-confirm-notify">' . esc_html__( 'Send email', 'devdiggers-waitlist-for-woocommerce' ) . '</a>';
			}

			if ( 'waiting' !== $item->status ) {
				$actions['reset'] = '<a href="' . esc_url( $this->action_url( 'reset', $item->id ) ) . '">' . esc_html__( 'Move to waiting', 'devdiggers-waitlist-for-woocommerce' ) . '</a>';
			}

			$actions['delete'] = '<a href="' . esc_url( $this->action_url( 'delete', $item->id ) ) . '" class="ddwcwl-confirm-delete submitdelete">' . esc_html__( 'Delete', 'devdiggers-waitlist-for-woocommerce' ) . '</a>';

			return '<strong><a href="mailto:' . esc_attr( $item->email ) . '">' . esc_html( $item->email ) . '</a></strong><br /><small>' . $meta . '</small>' . $this->row_actions( $actions );
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

			$stock = $product->is_in_stock()
				? '<span class="ddwcwl-stock ddwcwl-instock">' . esc_html__( 'In stock', 'devdiggers-waitlist-for-woocommerce' ) . '</span>'
				: '<span class="ddwcwl-stock ddwcwl-outofstock">' . esc_html__( 'Out of stock', 'devdiggers-waitlist-for-woocommerce' ) . '</span>';

			return sprintf(
				'<div class="ddwcwl-product-cell">%1$s<div><a href="%2$s">%3$s</a><br /><small>%4$s</small></div></div>',
				wp_kses_post( $product->get_image( [ 40, 40 ] ) ),
				esc_url( get_edit_post_link( $product->get_parent_id() ? $product->get_parent_id() : $product->get_id() ) ),
				esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ),
				wp_kses_post( $stock )
			);
		}

		/**
		 * Status column.
		 *
		 * @param object $item Row.
		 * @return string
		 */
		protected function column_status( $item ) {
			$output = '<mark class="ddwcwl-status ddwcwl-status-' . esc_attr( $item->status ) . '">' . esc_html( $this->labels[ $item->status ] ?? $item->status ) . '</mark>';

			if ( 'waiting' === $item->status ) {
				/* translators: %s: place in line. */
				$output .= '<br /><small class="ddwcwl-muted">' . esc_html( sprintf( __( 'Number %s in line', 'devdiggers-waitlist-for-woocommerce' ), number_format_i18n( $this->helper->get_queue_position( $item ) ) ) ) . '</small>';
			}

			return $output;
		}

		/**
		 * Joined column.
		 *
		 * @param object $item Row.
		 * @return string
		 */
		protected function column_created_at( $item ) {
			return esc_html( $this->format_date( $item->created_at ) );
		}

		/**
		 * Notified column.
		 *
		 * @param object $item Row.
		 * @return string
		 */
		protected function column_notified( $item ) {
			if ( ! $item->notified_at ) {
				return '<span aria-hidden="true">&ndash;</span>';
			}

			return esc_html( $this->format_date( $item->notified_at ) );
		}

		/**
		 * Human date.
		 *
		 * @param string $date MySQL date.
		 * @return string
		 */
		protected function format_date( $date ) {
			return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date ) );
		}

		/**
		 * Empty state.
		 *
		 * @return void
		 */
		public function no_items() {
			$filtered = array_filter( [ $this->filters['status'], $this->filters['product_id'], $this->filters['search'] ] );

			ddfw_print_empty_state(
				[
					'title'       => $filtered ? esc_html__( 'No matching subscribers', 'devdiggers-waitlist-for-woocommerce' ) : esc_html__( 'No subscribers yet', 'devdiggers-waitlist-for-woocommerce' ),
					'description' => $filtered ? esc_html__( 'Nobody matches these filters. Try another status or clear the search.', 'devdiggers-waitlist-for-woocommerce' ) : esc_html__( 'Customers appear here as soon as they join a waiting list on a sold out product. You can also add them by hand.', 'devdiggers-waitlist-for-woocommerce' ),
					'icon'        => DDFW_SVG::get_svg_icon(
						'users',
						true,
						[
							'size'         => 48,
							'stroke_width' => 1.5,
						]
					),
					'button_url'  => $filtered ? admin_url( 'admin.php?page=' . DDWCWL_DASHBOARD_PAGE . '&menu=subscribers' ) : admin_url( 'admin.php?page=' . DDWCWL_DASHBOARD_PAGE . '&menu=configuration&tab=form' ),
					'button_text' => $filtered ? esc_html__( 'Show all subscribers', 'devdiggers-waitlist-for-woocommerce' ) : esc_html__( 'Customize the signup form', 'devdiggers-waitlist-for-woocommerce' ),
				]
			);
		}
	}
}
