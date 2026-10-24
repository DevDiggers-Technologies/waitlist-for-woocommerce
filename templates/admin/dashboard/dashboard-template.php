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
		 * @return array
		 */
		protected function card( $title, $key, $icon ) {
			$item = $this->data['summary'][ $key ];

			return [
				'title'       => $title,
				'value'       => $item['value'],
				'value_type'  => 'number',
				'change'      => $item['change'],
				'is_positive' => $item['is_positive'],
				'icon'        => $icon,
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
						$this->card( esc_html__( 'Waiting Now', 'devdiggers-waitlist-for-woocommerce' ), 'waiting', DDFW_SVG::get_svg_icon( 'users' ) ),
						$this->card( esc_html__( 'New Signups', 'devdiggers-waitlist-for-woocommerce' ), 'signups', DDFW_SVG::get_svg_icon( 'bell-ring' ) ),
						$this->card( esc_html__( 'Emails Sent', 'devdiggers-waitlist-for-woocommerce' ), 'notified', DDFW_SVG::get_svg_icon( 'mail-check' ) ),
						$this->card( esc_html__( 'Products in Demand', 'devdiggers-waitlist-for-woocommerce' ), 'products', DDFW_SVG::get_svg_icon( 'package' ) ),
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
		 * @param array  $rows     Rows with product and waiting.
		 * @param string $empty_text Empty text.
		 * @param bool   $with_cta Show the "email now" action.
		 * @return void
		 */
		protected function render_product_rows( $rows, $empty_text, $with_cta = false ) {
			if ( empty( $rows ) ) {
				$this->empty_state( $empty_text, 'package' );
				return;
			}
			?>
			<div class="ddwcwl-dash-items">
				<?php foreach ( $rows as $row ) : ?>
					<div class="ddwcwl-dash-item">
						<a class="ddwcwl-dash-item-info" href="<?php echo esc_url( admin_url( 'admin.php?page=' . DDWCWL_DASHBOARD_PAGE . '&menu=subscribers&status=waiting&product_id=' . $row['product']->get_id() ) ); ?>">
							<?php echo wp_kses_post( $row['product']->get_image( [ 36, 36 ] ) ); ?>
							<span>
								<strong><?php echo esc_html( wp_strip_all_tags( $row['product']->get_formatted_name() ) ); ?></strong>
								<small>
									<?php
									/* translators: %s: waiting customers. */
									echo esc_html( sprintf( _n( '%s waiting', '%s waiting', $row['waiting'], 'devdiggers-waitlist-for-woocommerce' ), number_format_i18n( $row['waiting'] ) ) );
									?>
								</small>
							</span>
						</a>
						<?php if ( $with_cta ) : ?>
							<a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=' . DDWCWL_DASHBOARD_PAGE . '&menu=products&ddwcwl_action=notify_all&product[]=' . $row['product']->get_id() ), 'ddwcwl_list_action', 'ddwcwl_nonce' ) ); ?>"><?php esc_html_e( 'Email now', 'devdiggers-waitlist-for-woocommerce' ); ?></a>
						<?php endif; ?>
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
			?>
			<div class="ddwcwl-dash-items">
				<?php
				foreach ( $this->data['recent'] as $row ) :
					$product = wc_get_product( $row->product_id );
					?>
					<div class="ddwcwl-dash-item">
						<span class="ddwcwl-dash-item-info">
							<span>
								<strong><?php echo esc_html( $row->email ); ?></strong>
								<small><?php echo esc_html( ( $product ? wp_strip_all_tags( $product->get_name() ) : '' ) . ' · ' . human_time_diff( strtotime( $row->created_at ), current_time( 'timestamp' ) ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Compared with a local-time column. ?></small>
							</span>
						</span>
						<mark class="ddwcwl-status ddwcwl-status-<?php echo esc_attr( $row->status ); ?>"><?php echo esc_html( $labels[ $row->status ] ?? $row->status ); ?></mark>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
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
