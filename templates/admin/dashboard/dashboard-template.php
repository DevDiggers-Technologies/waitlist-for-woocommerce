<?php
/**
 * Dashboard template. Supplies data and config to the framework dashboard builder.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Templates\Admin\Dashboard;

use DDWCWaitingList\Helper\Dashboard\DDWCWL_Dashboard_Helper;
use DDWCWaitingList\Helper\Subscribers\DDWCWL_Subscribers_Helper;
use DDWCWaitingList\Includes\DDWCWL_Admin_Dashboard;
use DevDiggers\Framework\Includes\DDFW_Dashboard;
use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Dashboard_Template' ) ) {
	/**
	 * Dashboard template class.
	 */
	class DDWCWL_Dashboard_Template {
		/**
		 * Dashboard data.
		 *
		 * @var array
		 */
		protected $data;

		/**
		 * Construct.
		 */
		public function __construct() {
			if ( ! class_exists( DDFW_Dashboard::class ) ) {
				return;
			}

			$this->data = ( new DDWCWL_Dashboard_Helper() )->get_dashboard_data();
			$this->render();
		}

		/**
		 * Summary card config.
		 *
		 * @param string $title Title.
		 * @param string $key   Summary key.
		 * @param string $icon  Icon SVG.
		 * @param string $note  Bottom line for a "right now" figure that has no period comparison.
		 * @return array
		 */
		protected function card( $title, $key, $icon, $note = '' ) {
			$item = $this->data['summary'][ $key ];
			$card = [
				'title'      => $title,
				'value'      => $item['value'],
				'value_type' => 'number',
				'icon'       => $icon,
			];

			if ( $note ) {
				return $card + [ 'note' => $note ];
			}

			return $card + [
				'change'      => $item['change'],
				'is_positive' => $item['is_positive'],
				'previous'    => number_format_i18n( (float) $item['previous'] ),
			];
		}

		/**
		 * Render.
		 *
		 * @return void
		 */
		protected function render() {
			$labels = DDWCWL_Subscribers_Helper::get_status_labels();
			$status = [];

			foreach ( $labels as $key => $label ) {
				$status[] = [
					'label' => $label,
					'value' => $this->data['status_counts'][ $key ],
				];
			}

			new DDFW_Dashboard(
				[
					'columns'       => 4,
					'header'        => [
						/* translators: %s: admin display name. */
						'welcome'    => esc_html__( 'Welcome back, %s! 👋🏻', 'devdiggers-waitlist-for-woocommerce' ),
						'subtitle'   => esc_html__( 'See who is waiting, what they want most, and which products are ready to sell again.', 'devdiggers-waitlist-for-woocommerce' ),
						'date_range' => $this->data['date_range'],
					],
					'summary_cards' => [
						$this->card( esc_html__( 'Waiting Now', 'devdiggers-waitlist-for-woocommerce' ), 'waiting', DDFW_SVG::get_svg_icon( 'users' ), esc_html__( 'Right now, from any date', 'devdiggers-waitlist-for-woocommerce' ) ),
						$this->card( esc_html__( 'New Signups', 'devdiggers-waitlist-for-woocommerce' ), 'signups', DDFW_SVG::get_svg_icon( 'bell-ring' ) ),
						$this->card( esc_html__( 'Emails Sent', 'devdiggers-waitlist-for-woocommerce' ), 'notified', DDFW_SVG::get_svg_icon( 'mail-check' ) ),
						$this->card( esc_html__( 'Products in Demand', 'devdiggers-waitlist-for-woocommerce' ), 'products', DDFW_SVG::get_svg_icon( 'package' ), esc_html__( 'With customers waiting now', 'devdiggers-waitlist-for-woocommerce' ) ),
					],
					'charts'        => [
						[
							'id'         => 'ddwcwl-trend',
							'title'      => esc_html__( 'Waiting List Activity', 'devdiggers-waitlist-for-woocommerce' ),
							'date_label' => $this->data['date_range']['label'],
							'type'       => 'line',
							'data'       => $this->data['trend'],
							'x_key'      => 'date',
							'series'     => [
								[
									'key'   => 'signups',
									'label' => esc_html__( 'Signups', 'devdiggers-waitlist-for-woocommerce' ),
									'color' => '#0256ff',
								],
								[
									'key'   => 'notified',
									'label' => esc_html__( 'Emails Sent', 'devdiggers-waitlist-for-woocommerce' ),
									'color' => '#f59e0b',
								],
							],
							'empty'      => [
								'title' => esc_html__( 'No waiting list activity in this period', 'devdiggers-waitlist-for-woocommerce' ),
								'desc'  => esc_html__( 'Signups and restock emails will be charted here.', 'devdiggers-waitlist-for-woocommerce' ),
							],
						],
						[
							'id'         => 'ddwcwl-status-mix',
							'title'      => esc_html__( 'Subscribers by Status', 'devdiggers-waitlist-for-woocommerce' ),
							// Everyone's status today, whatever the date range: the label must not claim the range.
							'date_label' => esc_html__( 'All time', 'devdiggers-waitlist-for-woocommerce' ),
							'type'       => 'doughnut',
							'data'       => $status,
							'label_key'  => 'label',
							'value_key'  => 'value',
							'empty'      => [
								'title' => esc_html__( 'No subscribers yet', 'devdiggers-waitlist-for-woocommerce' ),
								'desc'  => esc_html__( 'Statuses appear once customers join a waiting list.', 'devdiggers-waitlist-for-woocommerce' ),
							],
						],
					],
					'widgets'       => [
						[
							'title'  => esc_html__( 'Most Wanted Products', 'devdiggers-waitlist-for-woocommerce' ),
							'width'  => 'half',
							'render' => [ $this, 'render_top_demanded' ],
						],
						[
							'title'  => esc_html__( 'Ready to Sell', 'devdiggers-waitlist-for-woocommerce' ),
							'width'  => 'half',
							'render' => [ $this, 'render_opportunities' ],
						],
						[
							'title'  => esc_html__( 'Recent Signups', 'devdiggers-waitlist-for-woocommerce' ),
							'width'  => 'half',
							'render' => [ $this, 'render_recent' ],
						],
						[
							'title'  => esc_html__( 'Recovered Sales', 'devdiggers-waitlist-for-woocommerce' ),
							'width'  => 'half',
							'render' => [ $this, 'render_pro_card' ],
						],
					],
				]
			);
		}

		/**
		 * Upgrade card in place of the recovered sales funnel.
		 *
		 * @return void
		 */
		public function render_pro_card() {
			ddfw_upgrade_to_pro_section(
				DDWCWL_Admin_Dashboard::ddwcwl_get_upgrade_args(
					'analytics',
					esc_html__( 'See exactly how much revenue your waiting list brings back', 'devdiggers-waitlist-for-woocommerce' ),
					esc_html__( 'Pro links every order to the restock email that won it, so you know which products to reorder first.', 'devdiggers-waitlist-for-woocommerce' ),
					[
						esc_html__( 'Recovered orders and revenue, compared with the previous period', 'devdiggers-waitlist-for-woocommerce' ),
						esc_html__( 'Email to click to purchase funnel', 'devdiggers-waitlist-for-woocommerce' ),
						esc_html__( 'Revenue per product on the Products screen', 'devdiggers-waitlist-for-woocommerce' ),
					]
				)
			);
		}

		/**
		 * Product list widget body.
		 *
		 * @param array  $rows       Rows with product and waiting.
		 * @param string $empty_text Empty text.
		 * @param bool   $with_cta   Show the "email now" action.
		 * @return void
		 */
		protected function render_product_rows( $rows, $empty_text, $with_cta = false ) {
			if ( empty( $rows ) ) {
				$this->empty_state( $empty_text, 'package' );
				return;
			}

			$items = [];

			foreach ( $rows as $row ) {
				$items[] = [
					'url'   => admin_url( 'admin.php?page=' . DDWCWL_DASHBOARD_PAGE . '&menu=subscribers&status=waiting&product_id=' . $row['product']->get_id() ),
					'title' => wp_strip_all_tags( $row['product']->get_formatted_name() ),
					/* translators: %s: waiting customers. */
					'meta'  => sprintf( _n( '%s waiting', '%s waiting', $row['waiting'], 'devdiggers-waitlist-for-woocommerce' ), number_format_i18n( $row['waiting'] ) ),
					'image' => $row['product']->get_image_id() ? $row['product']->get_image( [ 72, 72 ] ) : '',
					'cta'   => $with_cta ? wp_nonce_url( admin_url( 'admin.php?page=' . DDWCWL_DASHBOARD_PAGE . '&menu=products&ddwcwl_action=notify_all&product[]=' . $row['product']->get_id() ), 'ddwcwl_list_action', 'ddwcwl_nonce' ) : '',
				];
			}

			if ( ! $with_cta ) {
				DDFW_Dashboard::render_list( $items, $empty_text );
				return;
			}

			// Same markup as DDFW_Dashboard::render_list(), with an action button the linked row cannot hold.
			?>
			<div class="ddfw-dash-list">
				<?php foreach ( $items as $item ) : ?>
					<div class="ddfw-dash-list-item">
						<?php if ( $item['image'] ) : ?>
							<span class="ddfw-dash-list-avatar is-square"><?php echo wp_kses_post( $item['image'] ); ?></span>
						<?php else : ?>
							<span class="ddfw-dash-list-placeholder" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.09-3.09a2 2 0 0 0-2.82 0L6 21"/></svg></span>
						<?php endif; ?>
						<span class="ddfw-dash-list-info">
							<a class="ddfw-dash-list-title" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
							<span class="ddfw-dash-list-meta"><?php echo esc_html( $item['meta'] ); ?></span>
						</span>
						<a class="button button-small" href="<?php echo esc_url( $item['cta'] ); ?>"><?php esc_html_e( 'Email now', 'devdiggers-waitlist-for-woocommerce' ); ?></a>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
		}

		/**
		 * Most wanted widget.
		 *
		 * @return void
		 */
		public function render_top_demanded() {
			$this->render_product_rows( $this->data['top_demanded'], esc_html__( 'Nobody is waiting right now. Your most requested products will rank here.', 'devdiggers-waitlist-for-woocommerce' ) );
		}

		/**
		 * In stock with waiting customers widget.
		 *
		 * @return void
		 */
		public function render_opportunities() {
			$this->render_product_rows( $this->data['opportunities'], esc_html__( 'All caught up. In-stock products with customers still waiting appear here, ready to email.', 'devdiggers-waitlist-for-woocommerce' ), true );
		}

		/**
		 * Recent signups widget.
		 *
		 * @return void
		 */
		public function render_recent() {
			if ( empty( $this->data['recent'] ) ) {
				$this->empty_state( esc_html__( 'New signups show up here the moment a customer joins.', 'devdiggers-waitlist-for-woocommerce' ), 'bell-ring', esc_html__( 'No signups yet', 'devdiggers-waitlist-for-woocommerce' ) );
				return;
			}

			$labels = DDWCWL_Subscribers_Helper::get_status_labels();
			$items  = [];

			foreach ( $this->data['recent'] as $row ) {
				$product = wc_get_product( $row->product_id );
				/* translators: %s: time since the signup, e.g. "5 days". */
				$ago     = sprintf( __( '%s ago', 'devdiggers-waitlist-for-woocommerce' ), human_time_diff( strtotime( $row->created_at ), current_time( 'timestamp' ) ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Compared with a local-time column.
				$items[] = [
					'title' => $row->email,
					'meta'  => ( $product ? wp_strip_all_tags( $product->get_name() ) . ' · ' : '' ) . $ago,
					'image' => get_avatar_url( $row->email, [ 'size' => 72 ] ),
					'shape' => 'round',
					'value' => '<mark class="ddwcwl-status ddwcwl-status-' . esc_attr( $row->status ) . '">' . esc_html( $labels[ $row->status ] ?? $row->status ) . '</mark>',
				];
			}

			DDFW_Dashboard::render_list( $items );
		}

		/**
		 * Compact framework empty state inside a dashboard widget.
		 *
		 * @param string $text  Description.
		 * @param string $icon  Icon name.
		 * @param string $title Title.
		 * @return void
		 */
		protected function empty_state( $text, $icon, $title = '' ) {
			echo '<div class="ddwcwl-widget-empty">';
			ddfw_print_empty_state(
				[
					'title'       => $title ? $title : esc_html__( 'Nothing here yet', 'devdiggers-waitlist-for-woocommerce' ),
					'description' => $text,
					'icon'        => DDFW_SVG::get_svg_icon(
						$icon,
						true,
						[
							'size'         => 32,
							'stroke_width' => 1.5,
						]
					),
				]
			);
			echo '</div>';
		}
	}
}
