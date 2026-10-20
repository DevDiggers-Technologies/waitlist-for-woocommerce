<?php
/**
 * Join waiting list form, shared by the product page and the shortcode.
 *
 * @package DevDiggers Waitlist for WooCommerce
 * @version 2.0.0
 */

namespace DDWCWaitingList\Templates\Front\Product;

use DDWCWaitingList\Helper\Error\DDWCWL_Error_Helper;
use DDWCWaitingList\Helper\Products\DDWCWL_Products_Helper;
use DDWCWaitingList\Helper\Subscribers\DDWCWL_Subscribers_Helper;
use DDWCWaitingList\Includes\Front\DDWCWL_Front_Functions;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWL_Join_Waiting_List_Template' ) ) {
	/**
	 * Join waiting list template class.
	 */
	class DDWCWL_Join_Waiting_List_Template {
		use DDWCWL_Error_Helper;

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
		 * Inline icon. Kept tiny and stroke based so it follows the theme text color.
		 *
		 * @param string $name bell, calendar, users, check, mail, lock.
		 * @return string
		 */
		public static function icon( $name ) {
			$paths = [
				'bell'  => '<path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3H4a4 4 0 0 0 2-3v-3a7 7 0 0 1 4-6"/><path d="M9 17v1a3 3 0 0 0 6 0v-1"/>',
				'users' => '<path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0-8 0"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75M21 21v-2a4 4 0 0 0-3-3.85"/>',
				'mail'  => '<path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/><path d="m3 7 9 6 9-6"/>',
			];

			return '<svg class="ddwcwl-svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ( $paths[ $name ] ?? '' ) . '</svg>';
		}

		/**
		 * Allowed SVG markup for icon().
		 *
		 * @return array
		 */
		public static function svg_kses() {
			return [
				'svg'  => [
					'class'           => true,
					'width'           => true,
					'height'          => true,
					'viewbox'         => true,
					'fill'            => true,
					'stroke'          => true,
					'stroke-width'    => true,
					'stroke-linecap'  => true,
					'stroke-linejoin' => true,
					'aria-hidden'     => true,
					'focusable'       => true,
				],
				'path' => [ 'd' => true ],
			];
		}

		/**
		 * Per-product state: waiting count and the customer's entry.
		 *
		 * @param \WC_Product $product Product or variation.
		 * @return array
		 */
		public function get_state( $product ) {
			$row = is_user_logged_in() ? $this->subscribers->get_by_product_email( $product->get_id(), strtolower( wp_get_current_user()->user_email ) ) : null;

			$joined = $row && 'waiting' === $row->status;

			return [
				'count'    => $this->subscribers->count_waiting( $product->get_id() ),
				'joined'   => $joined,
				'position' => $joined ? $this->subscribers->get_queue_position( $row ) : 0,
			];
		}

		/**
		 * Render the form for a product. Variable products render one form that the product page
		 * script points at the chosen variation.
		 *
		 * @param \WC_Product $product Product.
		 * @return void
		 */
		public function ddwcwl_get_join_waiting_list_template( $product ) {
			global $ddwcwl_waiting_list;

			if ( ! $product instanceof \WC_Product ) {
				return;
			}

			$variations = [];

			if ( $product->is_type( 'variable' ) ) {
				foreach ( DDWCWL_Products_Helper::get_eligible_variations( $product ) as $variation ) {
					$variations[ $variation->get_id() ] = $this->get_state( $variation );
				}

				if ( empty( $variations ) ) {
					return;
				}

				$state = array_fill_keys( [ 'count', 'joined', 'position' ], '' );
			} elseif ( DDWCWL_Products_Helper::is_eligible( $product ) ) {
				$state = $this->get_state( $product );
			} else {
				return;
			}

			wp_enqueue_style( 'ddwcwl-front-style' );
			wp_enqueue_script( 'ddwcwl-front-script' );

			$config       = $ddwcwl_waiting_list;
			$is_variable  = ! empty( $variations );
			$logged_in    = is_user_logged_in();
			$needs_login  = 'yes' === $config['require_login'] && ! $logged_in;
			$uid          = wp_unique_id( 'ddwcwl-' );
			$button_style = '';

			if ( sanitize_hex_color( $config['button_bg_color'] ) ) {
				$button_style .= '--ddwcwl-button-bg:' . sanitize_hex_color( $config['button_bg_color'] ) . ';';
			}

			if ( sanitize_hex_color( $config['button_text_color'] ) ) {
				$button_style .= '--ddwcwl-button-color:' . sanitize_hex_color( $config['button_text_color'] ) . ';';
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only notice code set by our own redirect.
			$notice  = ! empty( $_GET['ddwcwl_notice'] ) ? sanitize_key( wp_unslash( $_GET['ddwcwl_notice'] ) ) : '';
			$message = $notice ? DDWCWL_Front_Functions::ddwcwl_get_message( $notice ) : '';
			$hidden  = $is_variable && ! $message;
			?>
			<div
				class="ddwcwl-waiting-list ddwcwl-context-product ddwcwl-style-card<?php echo esc_attr( $state['joined'] ? ' ddwcwl-joined' : '' ); ?>"
				style="<?php echo esc_attr( $button_style ); ?>"
				data-variations="<?php echo esc_attr( $is_variable ? wp_json_encode( $variations ) : '' ); ?>"
				data-state="<?php echo esc_attr( $is_variable ? '' : wp_json_encode( $state ) ); ?>"
				data-show-count="<?php echo esc_attr( 'yes' === $config['show_subscriber_count'] ? '1' : '' ); ?>"
				data-show-position="<?php echo esc_attr( 'yes' === $config['show_queue_position'] ? '1' : '' ); ?>"
				data-custom-notice="<?php echo esc_attr( 'yes' === $config['use_custom_notices'] ? self::ddwcwl_get_custom_notice_style() : '' ); ?>"
				<?php echo $hidden ? 'hidden' : ''; ?>
			>
				<div class="ddwcwl-head">
					<?php if ( 'yes' === $config['show_form_icon'] ) : ?>
						<span class="ddwcwl-head-icon"><?php echo wp_kses( self::icon( 'bell' ), self::svg_kses() ); ?></span>
					<?php endif; ?>
					<div class="ddwcwl-head-text">
						<p class="ddwcwl-title"><?php echo esc_html( $config['form_title'] ); ?></p>
						<?php if ( $config['form_description'] ) : ?>
							<p class="ddwcwl-desc"><?php echo esc_html( $config['form_description'] ); ?></p>
						<?php endif; ?>
					</div>
				</div>

				<ul class="ddwcwl-facts" <?php echo ( 'yes' === $config['show_subscriber_count'] && $state['count'] && ! $state['joined'] ) ? '' : 'hidden'; ?>>
					<li class="ddwcwl-fact ddwcwl-fact-count" <?php echo ( 'yes' === $config['show_subscriber_count'] && $state['count'] ) ? '' : 'hidden'; ?>>
						<?php echo wp_kses( self::icon( 'users' ), self::svg_kses() ); ?>
						<span class="ddwcwl-count">
							<?php
							/* translators: %s: number of waiting customers. */
							echo esc_html( sprintf( _n( '%s person is waiting for this product.', '%s people are waiting for this product.', (int) $state['count'], 'devdiggers-waitlist-for-woocommerce' ), number_format_i18n( (int) $state['count'] ) ) );
							?>
						</span>
					</li>
				</ul>

				<div class="ddwcwl-notice-area" aria-live="polite" <?php echo $message ? '' : 'hidden'; ?>>
					<?php
					if ( $message ) {
						$this->ddwcwl_print_notification( $message, in_array( $notice, [ 'added', 'left', 'unsubscribed' ], true ) ? 'success' : 'error' );
					}
					?>
				</div>

				<?php if ( $needs_login ) : ?>
					<p class="ddwcwl-login-required">
						<a class="ddwcwl-button ddwcwl-button-block" href="<?php echo esc_url( add_query_arg( 'redirect_to', rawurlencode( $product->get_permalink() ), wc_get_page_permalink( 'myaccount' ) ) ); ?>"><?php esc_html_e( 'Log in to get notified', 'devdiggers-waitlist-for-woocommerce' ); ?></a>
					</p>
				<?php else : ?>
					<form class="ddwcwl-form ddwcwl-join-form" method="post" novalidate>
						<input type="hidden" name="ddwcwl_form_action" value="join" />
						<input type="hidden" name="ddwcwl_nonce" value="<?php echo esc_attr( wp_create_nonce( 'ddwcwl-nonce' ) ); ?>" />
						<?php wp_referer_field(); ?>
						<input type="hidden" name="ddwcwl_product_id" class="ddwcwl-product-id" value="<?php echo esc_attr( $is_variable ? '' : $product->get_id() ); ?>" />
						<p class="ddwcwl-hp" aria-hidden="true">
							<label><?php esc_html_e( 'Leave this field empty', 'devdiggers-waitlist-for-woocommerce' ); ?><input type="text" name="ddwcwl_website" value="" tabindex="-1" autocomplete="off" /></label>
						</p>

						<?php if ( $logged_in ) : ?>
							<button type="submit" class="ddwcwl-button ddwcwl-button-block ddwcwl-join-button"><?php echo wp_kses( self::icon( 'bell' ), self::svg_kses() ); ?><span><?php echo esc_html( $config['join_button_text'] ); ?></span></button>
							<p class="ddwcwl-note">
								<?php
								/* translators: %s: customer email. */
								printf( esc_html__( 'We will email %s.', 'devdiggers-waitlist-for-woocommerce' ), '<strong>' . esc_html( wp_get_current_user()->user_email ) . '</strong>' );
								?>
							</p>
						<?php else : ?>
							<label class="screen-reader-text" for="<?php echo esc_attr( $uid ); ?>-email"><?php esc_html_e( 'Email address', 'devdiggers-waitlist-for-woocommerce' ); ?></label>
							<div class="ddwcwl-combo">
								<?php echo wp_kses( self::icon( 'mail' ), self::svg_kses() ); ?>
								<input type="email" id="<?php echo esc_attr( $uid ); ?>-email" class="ddwcwl-email" name="ddwcwl_user_email" placeholder="<?php echo esc_attr( $config['email_placeholder'] ); ?>" autocomplete="email" inputmode="email" required />
								<button type="submit" class="ddwcwl-button ddwcwl-join-button"><span><?php echo esc_html( $config['join_button_text'] ); ?></span></button>
							</div>
							<?php if ( $config['form_note'] ) : ?>
								<p class="ddwcwl-note"><?php echo esc_html( $config['form_note'] ); ?></p>
							<?php endif; ?>
						<?php endif; ?>

						<?php if ( 'yes' === $config['privacy_consent'] ) : ?>
							<p class="ddwcwl-consent">
								<label>
									<input type="checkbox" name="ddwcwl_consent" value="1" required />
									<span><?php echo wp_kses_post( $config['privacy_consent_text'] ); ?></span>
								</label>
							</p>
						<?php endif; ?>
					</form>

					<div class="ddwcwl-done" tabindex="-1">
						<svg class="ddwcwl-done-icon" width="28" height="28" viewBox="0 0 28 28" fill="none" aria-hidden="true" focusable="false"><circle class="ddwcwl-done-ring" cx="14" cy="14" r="12.25" stroke="currentColor" stroke-width="1.5"/><path class="ddwcwl-done-tick" d="m9.25 14.25 3.25 3.25 6.25-6.75" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
						<div class="ddwcwl-done-text">
							<p class="ddwcwl-done-title"><?php echo esc_html( $config['joined_title'] ); ?></p>
							<p class="ddwcwl-position" <?php echo ( 'yes' === $config['show_queue_position'] && $state['position'] ) ? '' : 'hidden'; ?>>
								<?php
								$position = '<strong class="ddwcwl-position-value">' . esc_html( number_format_i18n( (int) $state['position'] ) ) . '</strong>';
								echo wp_kses_post( str_replace( '{position}', $position, esc_html( $config['joined_message'] ) ) );
								?>
							</p>
							<p class="ddwcwl-done-message"></p>
							<?php if ( $logged_in ) : ?>
								<form class="ddwcwl-form ddwcwl-leave-form" method="post">
									<input type="hidden" name="ddwcwl_form_action" value="leave" />
									<input type="hidden" name="ddwcwl_nonce" value="<?php echo esc_attr( wp_create_nonce( 'ddwcwl-nonce' ) ); ?>" />
									<?php wp_referer_field(); ?>
									<input type="hidden" name="ddwcwl_product_id" class="ddwcwl-product-id" value="<?php echo esc_attr( $is_variable ? '' : $product->get_id() ); ?>" />
									<button type="submit" class="ddwcwl-link-button ddwcwl-leave-button"><?php echo esc_html( $config['leave_button_text'] ); ?></button>
								</form>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}
	}
}
