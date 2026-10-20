<?php
/**
 * My Account waiting list.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Templates\Front\MyAccount;

use DDWCWaitingList\Helper\Error\DDWCWL_Error_Helper;
use DDWCWaitingList\Helper\Subscribers\DDWCWL_Subscribers_Helper;
use DDWCWaitingList\Includes\Front\DDWCWL_Front_Functions;
use DDWCWaitingList\Templates\Front\Product\DDWCWL_Join_Waiting_List_Template as Join_Template;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Waiting_List_Template' ) ) {
	/**
	 * My Account waiting list template class.
	 */
	class DDWCWL_Waiting_List_Template {
		use DDWCWL_Error_Helper;

		/**
		 * Rows per page.
		 *
		 * @var int
		 */
		const PER_PAGE = 10;

		/**
		 * Construct.
		 *
		 * @param int $current_page Page number.
		 */
		public function __construct( $current_page = 1 ) {
			$this->render( $current_page );
		}

		/**
		 * Render.
		 *
		 * @param int $current_page Page number.
		 * @return void
		 */
		protected function render( $current_page ) {
			global $ddwcwl_waiting_list;

			wp_enqueue_style( 'ddwcwl-front-style' );

			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only search and notice parameters.
			$search = ! empty( $_GET['ddwcwl_search'] ) ? sanitize_text_field( wp_unslash( $_GET['ddwcwl_search'] ) ) : '';
			$notice = ! empty( $_GET['ddwcwl_notice'] ) ? sanitize_key( wp_unslash( $_GET['ddwcwl_notice'] ) ) : '';
			// phpcs:enable WordPress.Security.NonceVerification.Recommended

			if ( $notice && DDWCWL_Front_Functions::ddwcwl_get_message( $notice ) ) {
				$this->ddwcwl_print_notification( DDWCWL_Front_Functions::ddwcwl_get_message( $notice ), in_array( $notice, [ 'left', 'unsubscribed' ], true ) ? 'success' : 'error' );
			}

			$helper = new DDWCWL_Subscribers_Helper();
			$result = $helper->query(
				[
					'email'    => wp_get_current_user()->user_email,
					'search'   => $search,
					'per_page' => self::PER_PAGE,
					'page'     => $current_page,
				]
			);

			$labels      = DDWCWL_Subscribers_Helper::get_status_labels();
			$total_pages = (int) ceil( $result['total'] / self::PER_PAGE );
			$endpoint    = $ddwcwl_waiting_list['waiting_list_endpoint'];
			?>
			<div class="ddwcwl-account-list">
				<div class="ddwcwl-account-toolbar">
					<h3><?php esc_html_e( 'Products you are waiting for', 'devdiggers-waitlist-for-woocommerce' ); ?></h3>
					<form class="ddwcwl-account-search" method="get" action="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" role="search">
						<label class="screen-reader-text" for="ddwcwl-account-search"><?php esc_html_e( 'Search products', 'devdiggers-waitlist-for-woocommerce' ); ?></label>
						<input type="search" id="ddwcwl-account-search" name="ddwcwl_search" placeholder="<?php esc_attr_e( 'Search products', 'devdiggers-waitlist-for-woocommerce' ); ?>" value="<?php echo esc_attr( $search ); ?>" />
						<button type="submit" class="button"><?php esc_html_e( 'Search', 'devdiggers-waitlist-for-woocommerce' ); ?></button>
					</form>
				</div>

				<?php if ( empty( $result['items'] ) ) : ?>
					<div class="ddwcwl-account-empty">
						<span class="ddwcwl-head-icon"><?php echo wp_kses( Join_Template::icon( 'bell' ), Join_Template::svg_kses() ); ?></span>
						<p class="ddwcwl-account-empty-title"><?php echo esc_html( $search ? __( 'No matches', 'devdiggers-waitlist-for-woocommerce' ) : __( 'Nothing on your list yet', 'devdiggers-waitlist-for-woocommerce' ) ); ?></p>
						<p><?php echo esc_html( $search ? __( 'No waiting list entries match your search. Try another product name.', 'devdiggers-waitlist-for-woocommerce' ) : __( 'When a product you love is sold out, tap "Notify Me" on its page and we will email you as soon as it is back.', 'devdiggers-waitlist-for-woocommerce' ) ); ?></p>
						<a class="button" href="<?php echo esc_url( $search ? wc_get_account_endpoint_url( $endpoint ) : wc_get_page_permalink( 'shop' ) ); ?>"><?php echo esc_html( $search ? __( 'Clear search', 'devdiggers-waitlist-for-woocommerce' ) : __( 'Browse the shop', 'devdiggers-waitlist-for-woocommerce' ) ); ?></a>
					</div>
				<?php else : ?>
					<ul class="ddwcwl-account-items">
						<?php
						foreach ( $result['items'] as $row ) :
							$product = wc_get_product( $row->product_id );

							if ( ! $product ) {
								continue;
							}

							$in_stock = $product->is_in_stock();
							?>
							<li class="ddwcwl-account-item">
								<a href="<?php echo esc_url( $product->get_permalink() ); ?>" tabindex="-1" aria-hidden="true"><?php echo wp_kses_post( $product->get_image( 'woocommerce_gallery_thumbnail' ) ); ?></a>
								<div>
									<p><a class="ddwcwl-account-name" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></p>
									<p class="ddwcwl-account-meta">
										<span class="ddwcwl-stock <?php echo esc_attr( $in_stock ? 'ddwcwl-instock' : 'ddwcwl-outofstock' ); ?>"><?php echo esc_html( $in_stock ? __( 'In stock', 'devdiggers-waitlist-for-woocommerce' ) : __( 'Out of stock', 'devdiggers-waitlist-for-woocommerce' ) ); ?></span>
										<span><?php echo esc_html( $labels[ $row->status ] ?? $row->status ); ?></span>
										<?php if ( 'waiting' === $row->status && 'yes' === $ddwcwl_waiting_list['show_queue_position'] ) : ?>
											<span>
												<?php
												/* translators: %s: place in line. */
												echo esc_html( sprintf( __( 'Number %s in line', 'devdiggers-waitlist-for-woocommerce' ), number_format_i18n( $helper->get_queue_position( $row ) ) ) );
												?>
											</span>
										<?php endif; ?>
										<span>
											<?php
											/* translators: %s: date the customer joined. */
											echo esc_html( sprintf( __( 'Joined %s', 'devdiggers-waitlist-for-woocommerce' ), date_i18n( get_option( 'date_format' ), strtotime( $row->created_at ) ) ) );
											?>
										</span>
									</p>
								</div>
								<div class="ddwcwl-account-actions">
									<?php if ( 'waiting' === $row->status ) : ?>
										<form method="post">
											<input type="hidden" name="ddwcwl_form_action" value="leave" />
											<input type="hidden" name="ddwcwl_nonce" value="<?php echo esc_attr( wp_create_nonce( 'ddwcwl-nonce' ) ); ?>" />
											<?php wp_referer_field(); ?>
											<input type="hidden" name="ddwcwl_product_id" value="<?php echo esc_attr( $row->product_id ); ?>" />
											<button type="submit" class="button"><?php echo esc_html( $ddwcwl_waiting_list['leave_button_text'] ); ?></button>
										</form>
									<?php elseif ( $in_stock && $product->is_purchasable() ) : ?>
										<a class="button alt" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php esc_html_e( 'Buy now', 'devdiggers-waitlist-for-woocommerce' ); ?></a>
									<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>

					<?php if ( $total_pages > 1 ) : ?>
						<div class="woocommerce-pagination woocommerce-Pagination">
							<?php if ( $current_page > 1 ) : ?>
								<a class="woocommerce-button woocommerce-button--previous button" href="<?php echo esc_url( add_query_arg( 'ddwcwl_search', $search, wc_get_account_endpoint_url( $endpoint ) . ( $current_page - 1 ) ) ); ?>"><?php esc_html_e( 'Previous', 'devdiggers-waitlist-for-woocommerce' ); ?></a>
							<?php endif; ?>
							<?php if ( $current_page < $total_pages ) : ?>
								<a class="woocommerce-button woocommerce-button--next button" href="<?php echo esc_url( add_query_arg( 'ddwcwl_search', $search, wc_get_account_endpoint_url( $endpoint ) . ( $current_page + 1 ) ) ); ?>"><?php esc_html_e( 'Next', 'devdiggers-waitlist-for-woocommerce' ); ?></a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
			<?php
		}
	}
}
